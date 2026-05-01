<?php

namespace App\Http\Controllers;

use App\Exports\MuestrasVeterinariaExport;
use App\Models\Muestra;
use App\Models\Veterinaria;
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
     * Exportar a PDF usando TCPDF
     */
    public function exportarPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 120);

        $datos = $this->obtenerDatos($request);

        $agrupadoPorVet = $datos['agrupadoPorVeterinaria'];
        $titulo = $datos['titulo'];
        $totalMuestras = $datos['totalMuestras'];
        $totalAnalisis = $datos['totalAnalisis'];
        $totalVeterinarias = $datos['totalVeterinarias'];
        $fechaDesde = $datos['fechaDesde'];
        $fechaHasta = $datos['fechaHasta'];
        $filtroEstado = $datos['filtroEstado'];

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->SetMargins(12, 10, 12);
        $pdf->AddPage();

        // === TÍTULO ===
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->SetTextColor(30, 58, 95);
        $pdf->Cell(0, 6, mb_strtoupper($titulo), 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->Cell(0, 4, 'Laboratorio Clinico Veterinario', 0, 1, 'C');

        if ($fechaDesde || $fechaHasta) {
            $desde = $fechaDesde ? \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') : 'Inicio';
            $hasta = $fechaHasta ? \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') : now()->format('d/m/Y');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor(85, 85, 85);
            $pdf->Cell(0, 4, "Periodo: {$desde} al {$hasta}", 0, 1, 'C');
        }
        if ($filtroEstado) {
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(0, 4, "Estado: {$filtroEstado}", 0, 1, 'C');
        }

        // Línea separadora
        $pdf->SetDrawColor(30, 58, 95);
        $pdf->SetLineWidth(0.4);
        $pdf->Line(12, $pdf->GetY() + 2, $pdf->getPageWidth() - 12, $pdf->GetY() + 2);
        $pdf->Ln(5);

        // === RESUMEN GENERAL ===
        $pdf->SetFillColor(248, 249, 250);
        $pdf->SetDrawColor(208, 213, 221);
        $y = $pdf->GetY();
        $w = $pdf->getPageWidth() - 24;
        $pdf->RoundedRect(12, $y, $w, 12, 1, '1111', 'DF');

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(30, 58, 95);
        $pdf->SetXY(16, $y + 1);
        $pdf->Cell(0, 4, 'RESUMEN GENERAL', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(85, 85, 85);
        $pdf->SetXY(16, $y + 6);
        $pdf->Cell(30, 4, 'Total Veterinarias:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(26, 26, 26);
        $pdf->Cell(20, 4, (string) $totalVeterinarias, 0, 0, 'L');

        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(85, 85, 85);
        $pdf->Cell(30, 4, 'Total Muestras:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(26, 26, 26);
        $pdf->Cell(20, 4, (string) $totalMuestras, 0, 0, 'L');

        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(85, 85, 85);
        $pdf->Cell(30, 4, 'Total Analisis:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(26, 26, 26);
        $pdf->Cell(20, 4, (string) $totalAnalisis, 0, 1, 'L');

        $pdf->SetY($y + 14);

        // === Anchos de columnas ===
        $cols = [22, 30, 38, 22, 22, 22, 22, 28, 70]; // Código, Paciente, Propietario, Especie, Tipo, Estado, Fecha, Sucursal, Análisis
        $tableWidth = array_sum($cols);
        $xStart = 12;

        // === GRUPOS POR VETERINARIA ===
        foreach ($agrupadoPorVet as $grupo) {
            $vet = $grupo['veterinaria'];
            $muestras = $grupo['muestras'];

            // Verificar espacio para header + al menos 2 filas
            if ($pdf->GetY() > $pdf->getPageHeight() - 35) {
                $pdf->AddPage();
            }

            // --- Header de veterinaria ---
            $pdf->SetFillColor(30, 58, 95);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetX($xStart);
            $pdf->Cell($tableWidth, 6, mb_strtoupper($vet->nombre ?? 'Sin veterinaria'), 0, 1, 'L', true);

            $pdf->SetX($xStart);
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetTextColor(184, 201, 224);
            $statsText = "{$grupo['total_muestras']} muestras  ·  {$grupo['total_analisis']} analisis";
            if ($vet && $vet->responsable) {
                $statsText .= "  ·  Responsable: {$vet->responsable}";
            }
            $pdf->Cell($tableWidth, 4, $statsText, 0, 1, 'L', true);

            // --- Encabezado de tabla ---
            $pdf->SetFillColor(238, 242, 247);
            $pdf->SetTextColor(30, 58, 95);
            $pdf->SetFont('helvetica', 'B', 6.5);
            $pdf->SetDrawColor(30, 58, 95);

            $headers = ['CODIGO', 'PACIENTE', 'PROPIETARIO', 'ESPECIE', 'TIPO', 'ESTADO', 'FECHA', 'SUCURSAL', 'ANALISIS'];
            $pdf->SetX($xStart);
            foreach ($headers as $i => $h) {
                $pdf->Cell($cols[$i], 5, $h, 'B', 0, 'L', true);
            }
            $pdf->Ln();

            // --- Filas de datos ---
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetDrawColor(224, 224, 224);
            $rowNum = 0;

            foreach ($muestras as $muestra) {
                if ($pdf->GetY() > $pdf->getPageHeight() - 20) {
                    $pdf->AddPage();
                    // Repetir header de vet + tabla
                    $pdf->SetFillColor(30, 58, 95);
                    $pdf->SetTextColor(255, 255, 255);
                    $pdf->SetFont('helvetica', 'B', 9);
                    $pdf->SetX($xStart);
                    $pdf->Cell($tableWidth, 6, mb_strtoupper($vet->nombre ?? 'Sin veterinaria') . ' (cont.)', 0, 1, 'L', true);
                    // Header tabla
                    $pdf->SetFillColor(238, 242, 247);
                    $pdf->SetTextColor(30, 58, 95);
                    $pdf->SetFont('helvetica', 'B', 6.5);
                    $pdf->SetX($xStart);
                    foreach ($headers as $i => $h) {
                        $pdf->Cell($cols[$i], 5, $h, 'B', 0, 'L', true);
                    }
                    $pdf->Ln();
                    $pdf->SetFont('helvetica', '', 7);
                    $rowNum = 0;
                }

                // Fondo alterno
                if ($rowNum % 2 === 0) {
                    $pdf->SetFillColor(250, 251, 252);
                } else {
                    $pdf->SetFillColor(255, 255, 255);
                }

                $pdf->SetTextColor(26, 26, 26);
                $pdf->SetX($xStart);

                // Código (bold)
                $pdf->SetFont('helvetica', 'B', 7);
                $pdf->Cell($cols[0], 4, $muestra->codigo_muestra, 'B', 0, 'L', true);

                $pdf->SetFont('helvetica', '', 7);
                $pdf->Cell($cols[1], 4, $this->truncar($muestra->paciente_nombre, 18), 'B', 0, 'L', true);
                $pdf->Cell($cols[2], 4, $this->truncar($muestra->propietario_nombre, 22), 'B', 0, 'L', true);
                $pdf->Cell($cols[3], 4, $this->truncar($muestra->especie->nombre ?? 'N/A', 12), 'B', 0, 'L', true);
                $pdf->Cell($cols[4], 4, $this->truncar($muestra->tipo_muestra, 12), 'B', 0, 'L', true);

                // Estado con color
                $this->dibujarEstado($pdf, $muestra->estado, $cols[5]);

                $pdf->Cell($cols[6], 4, $muestra->fecha_recepcion->format('d/m/Y'), 'B', 0, 'L', true);
                $pdf->Cell($cols[7], 4, $this->truncar($muestra->sucursal->nombre ?? 'N/A', 16), 'B', 0, 'L', true);

                // Análisis
                $analisisTexto = $muestra->analisis->map(fn($a) => ($a->tipoAnalisis->nombre ?? 'N/A') . ' (' . $a->estado . ')')->implode(', ');
                $pdf->SetFont('helvetica', '', 6);
                $pdf->Cell($cols[8], 4, $this->truncar($analisisTexto ?: 'Sin analisis', 45), 'B', 1, 'L', true);
                $pdf->SetFont('helvetica', '', 7);

                $rowNum++;
            }

            // --- Subtotales de veterinaria ---
            $pdf->SetFillColor(238, 242, 247);
            $pdf->SetDrawColor(208, 213, 221);
            $pdf->SetTextColor(85, 85, 85);
            $pdf->SetFont('helvetica', '', 6.5);
            $pdf->SetX($xStart);
            $pdf->Cell($tableWidth / 4, 4, "Pendientes: {$grupo['estados']['Pendiente']}", 1, 0, 'C', true);
            $pdf->Cell($tableWidth / 4, 4, "En proceso: {$grupo['estados']['En proceso']}", 1, 0, 'C', true);
            $pdf->Cell($tableWidth / 4, 4, "Completados: {$grupo['estados']['Completado']}", 1, 0, 'C', true);
            $pdf->Cell($tableWidth / 4, 4, "Enviados: {$grupo['estados']['Enviado']}", 1, 1, 'C', true);
            $pdf->Ln(4);
        }

        if ($agrupadoPorVet->isEmpty()) {
            $pdf->SetFont('helvetica', 'I', 9);
            $pdf->SetTextColor(153, 153, 153);
            $pdf->Cell(0, 20, 'No se encontraron muestras con los filtros seleccionados.', 0, 1, 'C');
        }

        // === PIE ===
        $pdf->Ln(3);
        $pdf->SetFont('helvetica', '', 6);
        $pdf->SetTextColor(153, 153, 153);
        $pdf->Cell(0, 3, 'Laboratorio Clinico Veterinario - ' . now()->format('d/m/Y H:i'), 0, 1, 'R');

        $filename = $this->generarNombreArchivo($datos, 'pdf');

        return response($pdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Dibuja el estado con colores de fondo/texto apropiados.
     */
    private function dibujarEstado(\TCPDF $pdf, string $estado, float $width): void
    {
        $colores = match ($estado) {
            'Pendiente' => ['bg' => [254, 243, 199], 'text' => [146, 64, 14]],
            'En proceso' => ['bg' => [219, 234, 254], 'text' => [30, 64, 175]],
            'Completado' => ['bg' => [209, 250, 229], 'text' => [6, 95, 70]],
            'Enviado' => ['bg' => [233, 213, 255], 'text' => [107, 33, 168]],
            default => ['bg' => [240, 240, 240], 'text' => [80, 80, 80]],
        };

        $pdf->SetFillColor(...$colores['bg']);
        $pdf->SetTextColor(...$colores['text']);
        $pdf->SetFont('helvetica', 'B', 6);
        $pdf->Cell($width, 4, $estado, 'B', 0, 'C', true);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(26, 26, 26);
    }

    private function truncar(string $texto, int $max): string
    {
        return mb_strlen($texto) > $max ? mb_substr($texto, 0, $max - 2) . '..' : $texto;
    }
}
