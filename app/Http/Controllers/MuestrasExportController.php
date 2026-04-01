<?php

namespace App\Http\Controllers;

use App\Exports\MuestrasVeterinariaExport;
use App\Models\Muestra;
use App\Models\Veterinaria;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MuestrasExportController extends Controller
{
    /**
     * Obtener los datos agrupados por veterinaria según los filtros aplicados.
     */
    private function obtenerDatos(Request $request): array
    {
        $fechaDesde = $request->fecha_desde ?: null;
        $fechaHasta = $request->fecha_hasta ?: null;
        $filtroEstado = $request->estado ?: null;
        $filtroEspecie = $request->especie_id ?: null;
        $filtroVeterinaria = $request->veterinaria_id ?: null;
        $filtroSucursal = $request->sucursal_id ?: null;

        // Consultar muestras con sus relaciones
        $muestras = Muestra::query()
            ->with(['especie', 'veterinaria', 'sucursal', 'analisis.tipoAnalisis'])
            // Filtrar por sucursal del usuario si no tiene vista general
            ->when(!auth()->user()->can('vista-general-sistema'), function ($query) {
                $query->where('sucursal_id', auth()->user()->sucursal_id);
            })
            ->when($filtroEstado, function ($query) use ($filtroEstado) {
                $query->where('estado', $filtroEstado);
            })
            ->when($filtroEspecie, function ($query) use ($filtroEspecie) {
                $query->where('especie_id', $filtroEspecie);
            })
            ->when($filtroVeterinaria, function ($query) use ($filtroVeterinaria) {
                $query->where('veterinaria_id', $filtroVeterinaria);
            })
            ->when($filtroSucursal, function ($query) use ($filtroSucursal) {
                $query->where('sucursal_id', $filtroSucursal);
            })
            ->when($fechaDesde, function ($query) use ($fechaDesde) {
                $query->whereDate('fecha_recepcion', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function ($query) use ($fechaHasta) {
                $query->whereDate('fecha_recepcion', '<=', $fechaHasta);
            })
            ->orderBy('veterinaria_id')
            ->orderBy('fecha_recepcion', 'desc')
            ->get();

        // Agrupar por veterinaria
        $agrupadoPorVeterinaria = $muestras->groupBy('veterinaria_id')->map(function ($muestrasVet) {
            $veterinaria = $muestrasVet->first()->veterinaria;
            $totalAnalisis = $muestrasVet->sum(fn($m) => $m->analisis->count());

            return [
                'veterinaria' => $veterinaria,
                'muestras' => $muestrasVet,
                'total_muestras' => $muestrasVet->count(),
                'total_analisis' => $totalAnalisis,
                'estados' => [
                    'Pendiente' => $muestrasVet->where('estado', 'Pendiente')->count(),
                    'En proceso' => $muestrasVet->where('estado', 'En proceso')->count(),
                    'Completado' => $muestrasVet->where('estado', 'Completado')->count(),
                    'Enviado' => $muestrasVet->where('estado', 'Enviado')->count(),
                ],
            ];
        })->sortByDesc('total_muestras')->values();

        // Totales generales
        $totalMuestras = $muestras->count();
        $totalAnalisis = $muestras->sum(fn($m) => $m->analisis->count());
        $totalVeterinarias = $agrupadoPorVeterinaria->count();

        // Construir título
        $titulo = 'Reporte de Muestras por Veterinaria';
        if ($filtroVeterinaria) {
            $vet = Veterinaria::find($filtroVeterinaria);
            if ($vet) {
                $titulo = 'Reporte de Muestras - ' . $vet->nombre;
            }
        }

        return compact(
            'agrupadoPorVeterinaria',
            'titulo',
            'totalMuestras',
            'totalAnalisis',
            'totalVeterinarias',
            'fechaDesde',
            'fechaHasta',
            'filtroEstado',
        );
    }

    /**
     * Genera un nombre de archivo descriptivo
     */
    private function generarNombreArchivo(array $datos, string $extension): string
    {
        $nombre = $datos['titulo'];
        if ($datos['fechaDesde'] || $datos['fechaHasta']) {
            $desde = $datos['fechaDesde'] ? \Carbon\Carbon::parse($datos['fechaDesde'])->format('d-m-Y') : 'inicio';
            $hasta = $datos['fechaHasta'] ? \Carbon\Carbon::parse($datos['fechaHasta'])->format('d-m-Y') : now()->format('d-m-Y');
            $nombre .= " ({$desde} al {$hasta})";
        }
        $nombre = preg_replace('/[^A-Za-z0-9áéíóúñÁÉÍÓÚÑ\s\-_()]/u', '', $nombre);
        $nombre = preg_replace('/\s+/', '-', trim($nombre));

        return "{$nombre}.{$extension}";
    }

    /**
     * Exportar a Excel (.xlsx) con maatwebsite/excel
     */
    public function exportarExcel(Request $request)
    {
        $datos = $this->obtenerDatos($request);
        $filename = $this->generarNombreArchivo($datos, 'xlsx');

        return Excel::download(new MuestrasVeterinariaExport($datos), $filename);
    }

    /**
     * Exportar a PDF
     */
    public function exportarPdf(Request $request)
    {
        $datos = $this->obtenerDatos($request);

        $pdf = Pdf::loadView('exports.muestras-pdf', [
            'agrupadoPorVeterinaria' => $datos['agrupadoPorVeterinaria'],
            'titulo' => $datos['titulo'],
            'totalMuestras' => $datos['totalMuestras'],
            'totalAnalisis' => $datos['totalAnalisis'],
            'totalVeterinarias' => $datos['totalVeterinarias'],
            'fechaDesde' => $datos['fechaDesde'],
            'fechaHasta' => $datos['fechaHasta'],
            'filtroEstado' => $datos['filtroEstado'],
        ])
        ->setPaper('a4', 'landscape')
        ->setOption(['margin-left' => 50, 'margin-right' => 50]);

        $filename = $this->generarNombreArchivo($datos, 'pdf');

        return $pdf->stream($filename);
    }
}
