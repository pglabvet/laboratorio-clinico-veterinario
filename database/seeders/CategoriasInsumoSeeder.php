<?php

namespace Database\Seeders;

use App\Models\CategoriaInsumo;
use Illuminate\Database\Seeder;

class CategoriasInsumoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            // Reactivos y químicos
            [
                'nombre' => 'Reactivos Químicos',
                'descripcion' => 'Sustancias químicas para análisis de laboratorio',
                'estado' => true,
            ],
            [
                'nombre' => 'Colorantes y Tinciones',
                'descripcion' => 'Colorantes para tinción de muestras microscópicas',
                'estado' => true,
            ],
            [
                'nombre' => 'Medios de Cultivo',
                'descripcion' => 'Medios para cultivo microbiológico',
                'estado' => true,
            ],
            
            // Material de laboratorio
            [
                'nombre' => 'Material Desechable',
                'descripcion' => 'Tubos, pipetas, guantes y material de un solo uso',
                'estado' => true,
            ],
            [
                'nombre' => 'Vidriería',
                'descripcion' => 'Material de vidrio reutilizable para laboratorio',
                'estado' => true,
            ],
            
            // Kits de diagnóstico
            [
                'nombre' => 'Kits Diagnósticos',
                'descripcion' => 'Kits comerciales para pruebas diagnósticas rápidas',
                'estado' => true,
            ],
            [
                'nombre' => 'Kits ELISA',
                'descripcion' => 'Kits para análisis por método ELISA',
                'estado' => true,
            ],
            
            // Consumibles generales
            [
                'nombre' => 'Consumibles Generales',
                'descripcion' => 'Productos de consumo general del laboratorio',
                'estado' => true,
            ],
            [
                'nombre' => 'Anticoagulantes',
                'descripcion' => 'Sustancias para prevenir coagulación de muestras',
                'estado' => true,
            ],
            [
                'nombre' => 'Soluciones Buffer',
                'descripcion' => 'Soluciones tamponadas para estabilizar pH',
                'estado' => true,
            ],
        ];

        foreach ($categorias as $categoria) {
            CategoriaInsumo::firstOrCreate(
                ['nombre' => $categoria['nombre']],
                $categoria
            );
        }
    }
}
