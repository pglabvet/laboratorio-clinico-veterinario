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

class MuestrasDetalleSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    private int $totalRows = 0;
    private int $headerRow = 0;

    private const LAST_COL = 'F';

    public function __construct(
        private array $grupo,
        private array $datosGenerales,
    ) {}

    public function title(): string
    {
        $nombre = $this->grupo['veterinaria']->nombre ?? 'Sin veterinaria';

        // Excel limita nombres de hoja a 31 caracteres
        return mb_substr($nombre, 0, 31);
    }

    public function array(): array
    {
        $vet = $this->grupo['veterinaria'];
        $rows = [];

        // Encabezado de la veterinaria
        $rows[] = [$vet->nombre ?? 'Sin veterinaria'];
        $rows[] = [
            'Responsable: ' . ($vet->responsable ?? 'N/A'),
            '',
            '',
            'Total muestras: ' . $this->grupo['total_muestras'],
            '',
            'Total análisis: ' . $this->grupo['total_analisis'],
        ];

        // Desglose de estados
        $est = $this->grupo['estados'];
        $rows[] = [
            'Pendientes: ' . $est['Pendiente'],
            'En proceso: ' . $est['En proceso'],
            'Completados: ' . $est['Completado'],
            'Enviados: ' . $est['Enviado'],
        ];

        // Header de columnas
        $this->headerRow = count($rows) + 1;
        $rows[] = [
            'Código',
            'Paciente',
            'Propietario',
            'Fecha Recepción',
            'Sucursal',
            'Tipo de Análisis',
        ];

        // Datos de muestras
        foreach ($this->grupo['muestras'] as $muestra) {
            $base = [
                $muestra->codigo_muestra,
                $muestra->paciente_nombre,
                $muestra->propietario_nombre,
                $muestra->fecha_recepcion->format('d/m/Y'),
                $muestra->sucursal->nombre ?? 'N/A',
            ];

            if ($muestra->analisis->isEmpty()) {
                $rows[] = array_merge($base, ['Sin análisis']);
            } else {
                foreach ($muestra->analisis as $analisis) {
                    $rows[] = array_merge($base, [
                        $analisis->tipoAnalisis->nombre ?? 'Sin tipo',
                    ]);
                }
            }
        }

        $this->totalRows = count($rows);

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = self::LAST_COL;

                // Título de la veterinaria (fila 1)
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E3A5F']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F4FA']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                // Info del responsable (fila 2)
                $sheet->getStyle('A2:F2')->applyFromArray([
                    'font' => ['size' => 9, 'color' => ['rgb' => '555555']],
                ]);

                // Desglose de estados (fila 3)
                $sheet->getStyle('A3:D3')->applyFromArray([
                    'font' => ['size' => 9, 'bold' => true, 'color' => ['rgb' => '1E3A5F']],
                ]);

                // Header de columnas
                $hr = $this->headerRow;
                $sheet->getStyle("A{$hr}:{$lastCol}{$hr}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1E3A5F']],
                    ],
                ]);

                // Datos con bordes
                for ($r = $hr + 1; $r <= $this->totalRows; $r++) {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D5DD']]],
                        'font' => ['size' => 9],
                    ]);
                }

                // Centrar columnas de Fecha Recepción, Sucursal y Tipo de Análisis
                foreach (['D', 'E', 'F'] as $col) {
                    $sheet->getStyle("{$col}{$hr}:{$col}{$this->totalRows}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}