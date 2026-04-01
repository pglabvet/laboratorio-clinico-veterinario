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

class MuestrasRechazadasExport implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    private int $totalRows = 0;
    private int $headerRow = 0;

    private const LAST_COL = 'N';

    public function __construct(
        private array $datos,
    ) {}

    public function title(): string
    {
        return 'Muestras Rechazadas';
    }

    public function array(): array
    {
        $rows = [];

        // Título
        $rows[] = [$this->datos['titulo']];

        // Fecha de generación
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
        $rows[] = ['Generado: ' . now()->format('d/m/Y H:i'), '', '', '', '', '', $periodo];

        // Resumen por motivo
        $rows[] = ['Total: ' . $this->datos['muestras']->count() . ' muestras rechazadas'];

        // Header de columnas
        $this->headerRow = count($rows) + 1;
        $rows[] = [
            'Código',
            'Paciente',
            'Propietario',
            'Especie',
            'Raza',
            'Edad',
            'Sexo',
            'Veterinaria',
            'Sucursal',
            'Tipo Muestra',
            'Motivo de Rechazo',
            'Observaciones',
            'Fecha Rechazo',
            'Registrado por',
        ];

        // Datos
        foreach ($this->datos['muestras'] as $muestra) {
            $rows[] = [
                $muestra->codigo_muestra,
                $muestra->paciente_nombre,
                $muestra->propietario_nombre,
                $muestra->especie->nombre ?? 'N/A',
                $muestra->raza ?: '-',
                $muestra->edad ?? '-',
                $muestra->sexo === 'M' ? 'Macho' : 'Hembra',
                $muestra->veterinaria->nombre ?? 'N/A',
                $muestra->sucursal->nombre ?? 'N/A',
                $muestra->tipo_muestra,
                $muestra->motivo_rechazo,
                $muestra->observaciones ?: '-',
                $muestra->fecha_rechazo->format('d/m/Y H:i'),
                $muestra->registradoPor->name ?? '-',
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
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '8B0000']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF0F0']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                // Info generación (fila 2)
                $sheet->getStyle('A2:G2')->applyFromArray([
                    'font' => ['size' => 9, 'color' => ['rgb' => '555555']],
                ]);

                // Total (fila 3)
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['size' => 9, 'bold' => true, 'color' => ['rgb' => '8B0000']],
                ]);

                // Header de columnas
                $hr = $this->headerRow;
                $sheet->getStyle("A{$hr}:{$lastCol}{$hr}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8B0000']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '8B0000']],
                    ],
                ]);

                // Datos con bordes
                for ($r = $hr + 1; $r <= $this->totalRows; $r++) {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D5DD']]],
                        'font' => ['size' => 9],
                    ]);
                }

                // Centrar columnas cortas
                foreach (['D', 'E', 'F', 'G', 'I', 'M', 'N'] as $col) {
                    $sheet->getStyle("{$col}{$hr}:{$col}{$this->totalRows}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}
