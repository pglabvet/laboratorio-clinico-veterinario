<?php

namespace Database\Seeders;

use App\Models\UnidadMedida;
use Illuminate\Database\Seeder;

class UnidadesMedidaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unidades = [
            // Volumen
            ['nombre' => 'Mililitros', 'abreviatura' => 'ml', 'estado' => true],
            ['nombre' => 'Litros', 'abreviatura' => 'L', 'estado' => true],
            ['nombre' => 'Microlitros', 'abreviatura' => 'μl', 'estado' => true],
            
            // Peso
            ['nombre' => 'Gramos', 'abreviatura' => 'g', 'estado' => true],
            ['nombre' => 'Kilogramos', 'abreviatura' => 'kg', 'estado' => true],
            ['nombre' => 'Miligramos', 'abreviatura' => 'mg', 'estado' => true],
            
            // Cantidad
            ['nombre' => 'Unidades', 'abreviatura' => 'unid', 'estado' => true],
            ['nombre' => 'Piezas', 'abreviatura' => 'pza', 'estado' => true],
            ['nombre' => 'Cajas', 'abreviatura' => 'caja', 'estado' => true],
            
            // Concentración
            ['nombre' => 'Molar', 'abreviatura' => 'M', 'estado' => true],
            ['nombre' => 'Porcentaje', 'abreviatura' => '%', 'estado' => true],
        ];

        foreach ($unidades as $unidad) {
            UnidadMedida::create($unidad);
        }
    }
}
