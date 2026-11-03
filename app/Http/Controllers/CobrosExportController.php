<?php

namespace App\Http\Controllers;

use App\Exports\CobrosExport;
use App\Models\Cobro;
use App\Models\PagoCobro;
use App\Services\CobroService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CobrosExportController extends Controller
{
    /**
     * Obtener cobros con los mismos filtros de la tabla y su resumen.
     */
    private function obtenerDatos(Request $request, CobroService $cobroService): array
    {
        $fechaDesde = $request->fecha_desde ?: null;
        $fechaHasta = $request->fecha_hasta ?: null;
        $filtroEstado = $request->estado ?: null;
        $filtroMetodo = $request->metodo ?: null;

        $cobros = $cobroService->consulta([
            'buscar' => $request->buscar,
            'estado' => $filtroEstado,
            'metodo' => $filtroMetodo,
            'veterinaria_id' => $request->veterinaria_id,
            'sucursal_id' => $request->sucursal_id,
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
        ], $request->user())
            ->with(['muestra.veterinaria', 'muestra.sucursal', 'pagosVigentes'])
            ->get();

        // Los exonerados no cuentan como deuda ni como monto a cobrar
        $cobrables = $cobros->where('estado', '!=', Cobro::ESTADO_EXONERADO);

        $resumen = [
            'cantidad' => $cobros->count(),
            'total' => $cobrables->sum(fn ($c) => (float) $c->total),
            'pagado' => $cobros->sum(fn ($c) => (float) $c->total_pagado),
            'saldo' => $cobrables->sum(fn ($c) => $c->saldo),
        ];

        $porEstado = collect(Cobro::ESTADOS)
            ->mapWithKeys(fn ($estado) => [$estado => $cobros->where('estado', $estado)->count()]);

        $porMetodo = collect(PagoCobro::METODOS)
            ->mapWithKeys(fn ($metodo) => [
                $metodo => $cobros->sum(fn ($c) => (float) $c->pagosVigentes->where('metodo', $metodo)->sum('monto')),
            ]);

        $titulo = 'Reporte de Cobros';

        return compact(
            'cobros',
            'resumen',
            'porEstado',
            'porMetodo',
            'titulo',
            'fechaDesde',
            'fechaHasta',
            'filtroEstado',
            'filtroMetodo',
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
    public function exportarExcel(Request $request, CobroService $cobroService)
    {
        $datos = $this->obtenerDatos($request, $cobroService);

        return Excel::download(new CobrosExport($datos), $this->generarNombreArchivo($datos, 'xlsx'));
    }

    /**
     * Exportar a PDF
     */
    public function exportarPdf(Request $request, CobroService $cobroService)
    {
        $datos = $this->obtenerDatos($request, $cobroService);

        $pdf = Pdf::loadView('exports.cobros-pdf', $datos)
            ->setPaper('a4', 'landscape')
            ->setOption(['margin-left' => 50, 'margin-right' => 50]);

        return $pdf->stream($this->generarNombreArchivo($datos, 'pdf'));
    }
}
