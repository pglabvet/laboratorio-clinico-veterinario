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

class CobrosExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    private int $totalRows = 0;

    private int $headerRow = 0;

    private const LAST_COL = 'M';

    private const COLOR = '14532D';

    public function __construct(
        private array $datos,
    ) {}

    public function title(): string
    {
        return 'Cobros';
    }

    public function array(): array
    {
        $rows = [];

        // Título
        $rows[] = [$this->datos['titulo']];

        // Fecha de generación y período
        $periodo = '';
        if ($this->datos['fechaDesde'] || $this->datos['fechaHasta']) {
            $desde = $this->datos['fechaDesde']
                ? \Carbon\Carbon::parse($this->datos['fechaDesde'])->format('d/m/Y')
                : 'Inicio';
            $hasta = $this->datos['fechaHasta']
                ? \Carbon\Carbon::parse($this->datos['fechaHasta'])->format('d/m/Y')
                : now()->format('d/m/Y');
            $periodo = "Período: {$desde} al {$hasta}";
        }
        $rows[] = ['Generado: '.now()->format('d/m/Y H:i'), '', '', '', '', '', $periodo];

        // Resumen
        $resumen = $this->datos['resumen'];
        $rows[] = [
            "Cobros: {$resumen['cantidad']}",
            '',
            'Total: Bs '.number_format($resumen['total'], 2),
            '',
            'Pagado: Bs '.number_format($resumen['pagado'], 2),
            '',
            'Saldo: Bs '.number_format($resumen['saldo'], 2),
        ];
        $rows[] = collect($this->datos['porMetodo'])
            ->map(fn ($monto, $metodo) => "{$metodo}: Bs ".number_format($monto, 2))
            ->values()
            ->flatMap(fn ($texto) => [$texto, ''])
            ->all();

        // Header de columnas
        $this->headerRow = count($rows) + 1;
        $rows[] = [
            'Fecha',
            'Código',
            'Paciente',
            'Propietario',
            'Veterinaria',
            'Sucursal',
            'Examen',
            'Total (Bs)',
            'Pagado (Bs)',
            'Saldo (Bs)',
            'Método',
            'Estado',
            'Observación',
        ];

        // Datos
        foreach ($this->datos['cobros'] as $cobro) {
            $rows[] = [
                $cobro->muestra->fecha_recepcion?->format('d/m/Y H:i'),
                $cobro->muestra->codigo_muestra,
                $cobro->muestra->paciente_nombre,
                $cobro->muestra->propietario_nombre,
                $cobro->muestra->veterinaria->nombre ?? 'N/A',
                $cobro->muestra->sucursal->nombre ?? 'N/A',
                $cobro->examen ?: '-',
                (float) $cobro->total,
                (float) $cobro->total_pagado,
                $cobro->exonerado ? 0 : $cobro->saldo,
                $cobro->metodo ?? '-',
                $cobro->estado,
                $cobro->observacion ?: '-',
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
                $lastCol = self::LAST_COL;

                // Título (fila 1)
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => self::COLOR]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FDF4']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                // Info generación (fila 2)
                $sheet->getStyle('A2:G2')->applyFromArray([
                    'font' => ['size' => 9, 'color' => ['rgb' => '555555']],
                ]);

                // Resumen (filas 3 y 4)
                $sheet->getStyle('A3:G4')->applyFromArray([
                    'font' => ['size' => 9, 'bold' => true, 'color' => ['rgb' => self::COLOR]],
                ]);

                // Header de columnas
                $hr = $this->headerRow;
                $sheet->getStyle("A{$hr}:{$lastCol}{$hr}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR]],
                    ],
                ]);

                // Datos con bordes
                for ($r = $hr + 1; $r <= $this->totalRows; $r++) {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D5DD']]],
                        'font' => ['size' => 9],
                    ]);
                }

                // Montos con 2 decimales
                $sheet->getStyle("H{$hr}:J{$this->totalRows}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                // Centrar columnas cortas
                foreach (['A', 'B', 'F', 'K', 'L'] as $col) {
                    $sheet->getStyle("{$col}{$hr}:{$col}{$this->totalRows}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}
