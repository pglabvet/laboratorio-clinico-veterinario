<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MuestrasVeterinariaExport implements WithMultipleSheets
{
    public function __construct(
        private array $datos,
    ) {}

    public function sheets(): array
    {
        $sheets = [
            new MuestrasResumenSheet($this->datos),
        ];

        // Una hoja por cada veterinaria
        foreach ($this->datos['agrupadoPorVeterinaria'] as $grupo) {
            $sheets[] = new MuestrasDetalleSheet($grupo, $this->datos);
        }

        return $sheets;
    }
}