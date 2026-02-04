<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TiposAnalisisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposAnalisis = [
            [
                'nombre' => 'Hematología',
                'descripcion' => 'Estudio completo de la sangre que incluye hemograma, recuento celular y diferencial leucocitario',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Bioquímica Sanguínea',
                'descripcion' => 'Análisis de componentes químicos en sangre como glucosa, urea, creatinina, enzimas hepáticas y proteínas',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Urianálisis',
                'descripcion' => 'Análisis completo de orina que incluye examen físico, químico y microscópico',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('tipos_analisis')->insert($tiposAnalisis);
    }
}
