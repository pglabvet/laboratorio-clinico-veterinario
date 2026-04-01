<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MuestrasResumenSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    private int $totalRows = 0;

    public function __construct(
        private array $datos,
    ) {}

    public function title(): string
    {
        return 'Resumen';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = [$this->datos['titulo']];
        $rows[] = ['Generado: ' . now()->format('d/m/Y H:i')];

        if ($this->datos['fechaDesde'] || $this->datos['fechaHasta']) {
            $desde = $this->datos['fechaDesde']
                ? \Carbon\Carbon::parse($this->datos['fechaDesde'])->format('d/m/Y')
                : 'Inicio';
            $hasta = $this->datos['fechaHasta']
                ? \Carbon\Carbon::parse($this->datos['fechaHasta'])->format('d/m/Y')
                : now()->format('d/m/Y');
            $rows[] = ["Periodo: {$desde} al {$hasta}"];
        }

        if ($this->datos['filtroEstado']) {
            $rows[] = ['Estado: ' . $this->datos['filtroEstado']];
        }

        $rows[] = [];
        $rows[] = ['RESUMEN GENERAL'];
        $rows[] = ['Total Veterinarias', $this->datos['totalVeterinarias']];
        $rows[] = ['Total Muestras',     $this->datos['totalMuestras']];
        $rows[] = ['Total Analisis',     $this->datos['totalAnalisis']];
        $rows[] = [];

        $rows[] = ['Veterinaria', 'Muestras', 'Analisis', 'Pendientes', 'En Proceso', 'Completados', 'Enviados'];

        foreach ($this->datos['agrupadoPorVeterinaria'] as $grupo) {
            $rows[] = [
                $grupo['veterinaria']->nombre ?? 'Sin veterinaria',
                $grupo['total_muestras'],
                $grupo['total_analisis'],
                $grupo['estados']['Pendiente'],
                $grupo['estados']['En proceso'],
                $grupo['estados']['Completado'],
                $grupo['estados']['Enviado'],
            ];
        }

        $this->totalRows = count($rows);
        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:G1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E3A5F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F4FA']],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(22);

                $headerRow = 0;
                for ($i = 1; $i <= $this->totalRows; $i++) {
                    if ($sheet->getCell("A{$i}")->getValue() === 'Veterinaria') {
                        $headerRow = $i;
                        break;
                    }
                }

                if ($headerRow) {
                    $sheet->getStyle("A{$headerRow}:G{$headerRow}")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    for ($r = $headerRow + 1; $r <= $this->totalRows; $r++) {
                        if ($sheet->getCell("A{$r}")->getValue()) {
                            $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D5DD']]],
                            ]);
                            if (($r - $headerRow) % 2 === 0) {
                                $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F7FA']],
                                ]);
                            }
                        }
                    }
                }
            },
        ];
    }
}