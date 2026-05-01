<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Sucursal;
use App\Models\CategoriaInsumo;
use App\Services\PepsInventarioService;
use Illuminate\Http\Request;

class KardexExportController extends Controller
{
    public function __construct(
        private PepsInventarioService $pepsService
    ) {
    }

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

        } else {
            // Todos los insumos o filtrados por categoría
            if ($categoriaId) {
                $categoria = CategoriaInsumo::findOrFail($categoriaId);
                $titulo = 'Inventario - Categoria: ' . $categoria->nombre;
            } else {
                $titulo = 'Inventario General';
            }
            $mostrarColumnaInsumo = true;

            $queryInsumos = Insumo::where('estado', true);
            if ($categoriaId) {
                $queryInsumos->where('categoria_id', $categoriaId);
            }
            $insumosListado = $queryInsumos->orderBy('nombre')->get();

            foreach ($insumosListado as $insumo) {
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
            'registros',
            'titulo',
            'sucursal',
            'saldoFinalCantidad',
            'saldoFinalCosto',
            'mostrarColumnaInsumo',
            'fechaDesde',
            'fechaHasta'
        );
    }

    /**
     * Genera un nombre de archivo descriptivo basado en el título y sucursal
     */
    private function generarNombreArchivo(array $datos, string $extension): string
    {
        $nombre = $datos['titulo'] . ' - ' . $datos['sucursal']->nombre;
        // Limpiar caracteres no válidos para nombres de archivo
        $nombre = preg_replace('/[^A-Za-z0-9áéíóúñÁÉÍÓÚÑ\s\-_]/', '', $nombre);
        $nombre = preg_replace('/\s+/', '-', trim($nombre));

        return "{$nombre}.{$extension}";
    }

    /**
     * Exportar Kardex a Excel (CSV compatible con Excel)
     */
    public function exportarExcel(Request $request)
    {
        $datos = $this->obtenerDatos($request);
        $filename = $this->generarNombreArchivo($datos, 'csv');

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
                'Ini. Cant.',
                'Entrada Cant.',
                'Salida Cant.',
                'Saldo Cant.',
                'Ini. Costo (Bs)',
                'Entrada Costo (Bs)',
                'Salida Costo (Bs)',
                'Saldo Costo (Bs)',
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
                '',
                '',
                '',
                number_format($datos['saldoFinalCantidad'], 2, ',', '.'),
                '',
                '',
                '',
                number_format($datos['saldoFinalCosto'], 2, ',', '.'),
            ]);
            fputcsv($handle, $totalsRow, ';');

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar Kardex a PDF usando TCPDF
     */
    public function exportarPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 120);

        $datos = $this->obtenerDatos($request);

        $registros = $datos['registros'];
        $sucursal = $datos['sucursal'];
        $titulo = $datos['titulo'];
        $mostrarColumnaInsumo = $datos['mostrarColumnaInsumo'];
        $saldoFinalCantidad = $datos['saldoFinalCantidad'];
        $saldoFinalCosto = $datos['saldoFinalCosto'];
        $fechaDesde = $datos['fechaDesde'];
        $fechaHasta = $datos['fechaHasta'];

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->AddPage();

        // Título
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 6, mb_strtoupper($titulo), 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, 'Sucursal: ' . $sucursal->nombre, 0, 1, 'C');

        if ($fechaDesde || $fechaHasta) {
            $desde = $fechaDesde ? \Carbon\Carbon::parse($fechaDesde)->format('d-m-Y') : 'Inicio';
            $hasta = $fechaHasta ? \Carbon\Carbon::parse($fechaHasta)->format('d-m-Y') : now()->format('d-m-Y');
            $pdf->Cell(0, 4, "Del {$desde} Al {$hasta}", 0, 1, 'C');
        }

        $pdf->SetFont('helvetica', 'I', 7);
        $pdf->Cell(0, 4, '(Expresado en Bolivianos)', 0, 1, 'C');
        $pdf->Ln(2);

        // Anchos de columnas
        $colsInfo = $mostrarColumnaInsumo
            ? [25, 50, 40]
            : [25, 65];

        $colsData = [17, 17, 17, 20, 17, 17, 17, 20];
        $allCols = array_merge($colsInfo, $colsData);
        $tableWidth = array_sum($allCols);

        $pageWidth = $pdf->getPageWidth() - 20;
        $xStart = 10 + ($pageWidth - $tableWidth) / 2;

        // Encabezado
        $this->dibujarEncabezadoPdf($pdf, $xStart, $allCols, $colsInfo, $mostrarColumnaInsumo);

        // Filas
        $pdf->SetFont('helvetica', '', 7);
        foreach ($registros as $reg) {
            if ($pdf->GetY() > $pdf->getPageHeight() - 20) {
                $pdf->AddPage();
                $this->dibujarEncabezadoPdf($pdf, $xStart, $allCols, $colsInfo, $mostrarColumnaInsumo);
                $pdf->SetFont('helvetica', '', 7);
            }

            $pdf->SetX($xStart);
            $pdf->Cell($allCols[0], 4, $reg['fecha'], 1, 0, 'L');
            $pdf->Cell($allCols[1], 4, $this->truncarTexto($reg['detalle'], $mostrarColumnaInsumo ? 30 : 40), 1, 0, 'L');
            $colIdx = 2;
            if ($mostrarColumnaInsumo) {
                $pdf->Cell($allCols[$colIdx], 4, $this->truncarTexto($reg['insumo_nombre'] ?? '', 25), 1, 0, 'L');
                $colIdx++;
            }

            $pdf->Cell($allCols[$colIdx++], 4, number_format($reg['inicio_cantidad'], 2), 1, 0, 'R');
            $pdf->Cell($allCols[$colIdx++], 4, $reg['entrada_cantidad'] !== null ? number_format($reg['entrada_cantidad'], 2) : '', 1, 0, 'R');
            $pdf->Cell($allCols[$colIdx++], 4, $reg['salida_cantidad'] !== null ? number_format($reg['salida_cantidad'], 2) : '', 1, 0, 'R');
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->Cell($allCols[$colIdx++], 4, number_format($reg['saldo_cantidad'], 2), 1, 0, 'R');
            $pdf->SetFont('helvetica', '', 7);

            $pdf->Cell($allCols[$colIdx++], 4, number_format($reg['inicio_costo'], 2), 1, 0, 'R');
            $pdf->Cell($allCols[$colIdx++], 4, $reg['entrada_costo'] !== null ? number_format($reg['entrada_costo'], 2) : '', 1, 0, 'R');
            $pdf->Cell($allCols[$colIdx++], 4, $reg['salida_costo'] !== null ? number_format($reg['salida_costo'], 2) : '', 1, 0, 'R');
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->Cell($allCols[$colIdx], 4, number_format($reg['saldo_costo'], 2), 1, 1, 'R');
            $pdf->SetFont('helvetica', '', 7);
        }

        // Totales
        if (count($registros) > 0) {
            if ($pdf->GetY() > $pdf->getPageHeight() - 20) {
                $pdf->AddPage();
            }

            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetFillColor(232, 232, 232);
            $pdf->SetX($xStart);

            $infoWidth = array_sum($colsInfo);
            $pdf->Cell($infoWidth, 5, 'TOTALES FINALES', 1, 0, 'L', true);
            $colIdx = count($colsInfo);
            $pdf->Cell($allCols[$colIdx++], 5, '', 1, 0, 'R', true);
            $pdf->Cell($allCols[$colIdx++], 5, '', 1, 0, 'R', true);
            $pdf->Cell($allCols[$colIdx++], 5, '', 1, 0, 'R', true);
            $pdf->Cell($allCols[$colIdx++], 5, number_format($saldoFinalCantidad, 2), 1, 0, 'R', true);
            $pdf->Cell($allCols[$colIdx++], 5, '', 1, 0, 'R', true);
            $pdf->Cell($allCols[$colIdx++], 5, '', 1, 0, 'R', true);
            $pdf->Cell($allCols[$colIdx++], 5, '', 1, 0, 'R', true);
            $pdf->Cell($allCols[$colIdx], 5, number_format($saldoFinalCosto, 2), 1, 1, 'R', true);
        }

        // Pie
        $pdf->Ln(3);
        $pdf->SetFont('helvetica', '', 6);
        $pdf->Cell(0, 3, 'Laboratorio Clinico Veterinario - ' . now()->format('d/m/Y H:i'), 0, 1, 'R');

        $filename = $this->generarNombreArchivo($datos, 'pdf');

        return response($pdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    private function dibujarEncabezadoPdf(\TCPDF $pdf, float $xStart, array $allCols, array $colsInfo, bool $mostrarInsumo): void
    {
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetFillColor(232, 232, 232);
        $pdf->SetX($xStart);

        $headers = $mostrarInsumo ? ['FECHA', 'DETALLE', 'INSUMO'] : ['FECHA', 'DETALLE'];
        foreach ($headers as $i => $h) {
            $pdf->Cell($colsInfo[$i], 8, $h, 1, 0, 'C', true);
        }

        $ci = count($colsInfo);
        $cantWidth = $allCols[$ci] + $allCols[$ci + 1] + $allCols[$ci + 2] + $allCols[$ci + 3];
        $costoWidth = $allCols[$ci + 4] + $allCols[$ci + 5] + $allCols[$ci + 6] + $allCols[$ci + 7];

        $pdf->Cell($cantWidth, 4, 'CANTIDADES', 1, 0, 'C', true);
        $pdf->Cell($costoWidth, 4, 'COSTOS (BS)', 1, 1, 'C', true);

        $pdf->SetX($xStart + array_sum($colsInfo));
        $subHeaders = ['Ini', 'Ent', 'Sal', 'Saldo', 'Ini', 'Ent', 'Sal', 'Saldo'];
        $colIdx = $ci;
        foreach ($subHeaders as $sh) {
            $pdf->Cell($allCols[$colIdx++], 4, $sh, 1, 0, 'C', true);
        }
        $pdf->Ln();
    }

    private function truncarTexto(string $texto, int $max): string
    {
        return mb_strlen($texto) > $max ? mb_substr($texto, 0, $max - 2) . '..' : $texto;
    }
}