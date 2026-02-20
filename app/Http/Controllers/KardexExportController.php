<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Sucursal;
use App\Models\CategoriaInsumo;
use App\Services\PepsInventarioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class KardexExportController extends Controller
{
    public function __construct(
        private PepsInventarioService $pepsService
    ) {}

    private function obtenerDatos(Request $request): array
    {
        $sucursalId = (int) $request->sucursal_id;
        $insumoId = (int) $request->insumo_id;
        $categoriaId = (int) $request->filtro_categoria;
        $fechaDesde = $request->fecha_desde ?: null;
        $fechaHasta = $request->fecha_hasta ?: null;

        $sucursal = Sucursal::findOrFail($sucursalId);
        $mostrarColumnaInsumo = false;
        $registros = [];
        $saldoFinalCantidad = 0;
        $saldoFinalCosto = 0;
        $titulo = 'Inventario';

        if ($insumoId) {
            $insumo = Insumo::findOrFail($insumoId);
            $titulo = 'Inventario: ' . $insumo->nombre;

            $kardex = $this->pepsService->generarKardex(
                insumoId: $insumoId,
                sucursalId: $sucursalId,
                fechaDesde: $fechaDesde,
                fechaHasta: $fechaHasta,
            );

            foreach ($kardex['registros'] as &$registro) {
                $registro['insumo_nombre'] = $insumo->nombre;
            }
            unset($registro);

            $registros = $kardex['registros'];
            $saldoFinalCantidad = $kardex['saldo_final_cantidad'];
            $saldoFinalCosto = $kardex['saldo_final_costo'];

        } elseif ($categoriaId) {
            $categoria = CategoriaInsumo::findOrFail($categoriaId);
            $titulo = 'Inventario - Categoria: ' . $categoria->nombre;
            $mostrarColumnaInsumo = true;

            $insumosCategoria = Insumo::where('estado', true)
                ->where('categoria_id', $categoriaId)
                ->orderBy('nombre')
                ->get();

            foreach ($insumosCategoria as $insumo) {
                $kardex = $this->pepsService->generarKardex(
                    insumoId: $insumo->id,
                    sucursalId: $sucursalId,
                    fechaDesde: $fechaDesde,
                    fechaHasta: $fechaHasta,
                );

                foreach ($kardex['registros'] as $registro) {
                    $registro['insumo_nombre'] = $insumo->nombre;
                    $registros[] = $registro;
                }

                $saldoFinalCantidad += $kardex['saldo_final_cantidad'];
                $saldoFinalCosto += $kardex['saldo_final_costo'];
            }

            usort($registros, function ($a, $b) {
                $dateA = \Carbon\Carbon::createFromFormat('d/m/Y', $a['fecha']);
                $dateB = \Carbon\Carbon::createFromFormat('d/m/Y', $b['fecha']);
                return $dateA->timestamp - $dateB->timestamp;
            });
        }

        return compact(
            'registros', 'titulo', 'sucursal',
            'saldoFinalCantidad', 'saldoFinalCosto',
            'mostrarColumnaInsumo', 'fechaDesde', 'fechaHasta'
        );
    }

    /**
     * Exportar Kardex a Excel (CSV compatible con Excel)
     */
    public function exportarExcel(Request $request)
    {
        $datos = $this->obtenerDatos($request);
        $filename = 'kardex-peps-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($datos) {
            $handle = fopen('php://output', 'w');

            // BOM for UTF-8 Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Metadata rows
            fputcsv($handle, [$datos['titulo']], ';');
            fputcsv($handle, ['Sucursal: ' . $datos['sucursal']->nombre], ';');
            fputcsv($handle, ['Generado: ' . now()->format('d/m/Y H:i')], ';');
            fputcsv($handle, [], ';'); // empty row

            // Headers
            $headerRow = ['Fecha', 'Detalle'];
            if ($datos['mostrarColumnaInsumo']) {
                $headerRow[] = 'Insumo';
            }
            $headerRow = array_merge($headerRow, [
                'Ini. Cant.', 'Entrada Cant.', 'Salida Cant.', 'Saldo Cant.',
                'Ini. Costo (Bs)', 'Entrada Costo (Bs)', 'Salida Costo (Bs)', 'Saldo Costo (Bs)',
            ]);
            fputcsv($handle, $headerRow, ';');

            // Data rows
            foreach ($datos['registros'] as $registro) {
                $row = [
                    $registro['fecha'],
                    $registro['detalle'],
                ];

                if ($datos['mostrarColumnaInsumo']) {
                    $row[] = $registro['insumo_nombre'] ?? '';
                }

                $row = array_merge($row, [
                    number_format($registro['inicio_cantidad'], 2, ',', '.'),
                    $registro['entrada_cantidad'] !== null ? number_format($registro['entrada_cantidad'], 2, ',', '.') : '-',
                    $registro['salida_cantidad'] !== null ? number_format($registro['salida_cantidad'], 2, ',', '.') : '-',
                    number_format($registro['saldo_cantidad'], 2, ',', '.'),
                    number_format($registro['inicio_costo'], 2, ',', '.'),
                    $registro['entrada_costo'] !== null ? number_format($registro['entrada_costo'], 2, ',', '.') : '-',
                    $registro['salida_costo'] !== null ? number_format($registro['salida_costo'], 2, ',', '.') : '-',
                    number_format($registro['saldo_costo'], 2, ',', '.'),
                ]);

                fputcsv($handle, $row, ';');
            }

            // Totals row
            fputcsv($handle, [], ';');
            $totalsRow = ['TOTALES', ''];
            if ($datos['mostrarColumnaInsumo']) {
                $totalsRow[] = '';
            }
            $totalsRow = array_merge($totalsRow, [
                '', '', '',
                number_format($datos['saldoFinalCantidad'], 2, ',', '.'),
                '', '', '',
                number_format($datos['saldoFinalCosto'], 2, ',', '.'),
            ]);
            fputcsv($handle, $totalsRow, ';');

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar Kardex a PDF
     */
    public function exportarPdf(Request $request)
    {
        $datos = $this->obtenerDatos($request);

        $pdf = Pdf::loadView('exports.kardex-pdf', [
            'registros' => $datos['registros'],
            'titulo' => $datos['titulo'],
            'sucursalNombre' => $datos['sucursal']->nombre,
            'saldoFinalCantidad' => $datos['saldoFinalCantidad'],
            'saldoFinalCosto' => $datos['saldoFinalCosto'],
            'mostrarColumnaInsumo' => $datos['mostrarColumnaInsumo'],
            'fechaDesde' => $datos['fechaDesde'],
            'fechaHasta' => $datos['fechaHasta'],
        ])
        ->setPaper('a4', 'landscape')
        ->setOption(['margin-left' => 50, 'margin-right' => 50]);

        $filename = 'kardex-peps-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }
}