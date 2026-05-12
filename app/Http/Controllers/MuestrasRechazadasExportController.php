<?php

namespace App\Http\Controllers;

use App\Exports\MuestrasRechazadasExport;
use App\Models\MuestraRechazada;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MuestrasRechazadasExportController extends Controller
{
    /**
     * Obtener datos filtrados de muestras rechazadas.
     */
    private function obtenerDatos(Request $request): array
    {
        $fechaDesde = $request->fecha_desde ?: null;
        $fechaHasta = $request->fecha_hasta ?: null;
        $filtroMotivo = $request->motivo ?: null;
        $filtroVeterinaria = $request->veterinaria_id ?: null;
        $filtroSucursal = $request->sucursal_id ?: null;

        $muestras = MuestraRechazada::query()
            ->with(['especie', 'veterinaria', 'sucursal', 'registradoPor'])
            ->when($filtroMotivo, function ($query) use ($filtroMotivo) {
                $query->where('motivo_rechazo', $filtroMotivo);
            })
            ->when($filtroVeterinaria, function ($query) use ($filtroVeterinaria) {
                $query->where('veterinaria_id', $filtroVeterinaria);
            })
            ->when($filtroSucursal, function ($query) use ($filtroSucursal) {
                $query->where('sucursal_id', $filtroSucursal);
            })
            ->when($fechaDesde, function ($query) use ($fechaDesde) {
                $query->whereDate('fecha_rechazo', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function ($query) use ($fechaHasta) {
                $query->whereDate('fecha_rechazo', '<=', $fechaHasta);
            })
            ->orderBy('fecha_rechazo', 'desc')
            ->get();

        // Estadísticas por motivo
        $porMotivo = $muestras->groupBy('motivo_rechazo')->map(fn ($items) => $items->count())->sortDesc();

        $titulo = 'Reporte de Muestras Rechazadas';

        return compact(
            'muestras',
            'porMotivo',
            'titulo',
            'fechaDesde',
            'fechaHasta',
            'filtroMotivo',
        );
    }

    /**
     * Genera un nombre de archivo descriptivo.
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
     * Exportar a Excel (.xlsx)
     */
    public function exportarExcel(Request $request)
    {
        $datos = $this->obtenerDatos($request);
        $filename = $this->generarNombreArchivo($datos, 'xlsx');

        return Excel::download(new MuestrasRechazadasExport($datos), $filename);
    }

    /**
     * Exportar a PDF
     */
    public function exportarPdf(Request $request)
    {
        $datos = $this->obtenerDatos($request);

        $pdf = Pdf::loadView('exports.muestras-rechazadas-pdf', $datos)
            ->setPaper('a4', 'landscape')
            ->setOption(['margin-left' => 50, 'margin-right' => 50]);

        $filename = $this->generarNombreArchivo($datos, 'pdf');

        return $pdf->stream($filename);
    }
}
