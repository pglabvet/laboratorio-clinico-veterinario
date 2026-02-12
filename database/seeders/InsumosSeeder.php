<?php

namespace Database\Seeders;

use App\Models\CategoriaInsumo;
use App\Models\Insumo;
use App\Models\UnidadMedida;
use Illuminate\Database\Seeder;

class InsumosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener categorías y unidades por nombre
        $getCat = fn($nombre) => CategoriaInsumo::where('nombre', $nombre)->first()?->id;
        $getUni = fn($abrev) => UnidadMedida::where('abreviatura', $abrev)->first()?->id;

        $insumos = [
            // Reactivos Químicos
            ['nombre' => 'Ácido Clorhídrico 37%', 'categoria' => 'Reactivos Químicos', 'unidad' => 'ml'],
            ['nombre' => 'Ácido Sulfúrico Concentrado', 'categoria' => 'Reactivos Químicos', 'unidad' => 'ml'],
            ['nombre' => 'Hidróxido de Sodio', 'categoria' => 'Reactivos Químicos', 'unidad' => 'g'],
            
            // Colorantes y Tinciones
            ['nombre' => 'Tinción de Gram', 'categoria' => 'Colorantes y Tinciones', 'unidad' => 'ml'],
            ['nombre' => 'Azul de Metileno', 'categoria' => 'Colorantes y Tinciones', 'unidad' => 'ml'],
            ['nombre' => 'Tinción de Wright', 'categoria' => 'Colorantes y Tinciones', 'unidad' => 'ml'],
            
            // Medios de Cultivo
            ['nombre' => 'Agar Sangre', 'categoria' => 'Medios de Cultivo', 'unidad' => 'g'],
            ['nombre' => 'Agar MacConkey', 'categoria' => 'Medios de Cultivo', 'unidad' => 'g'],
            ['nombre' => 'Agar Sabouraud', 'categoria' => 'Medios de Cultivo', 'unidad' => 'g'],
            
            // Material Desechable
            ['nombre' => 'Tubos EDTA 5ml', 'categoria' => 'Material Desechable', 'unidad' => 'unid'],
            ['nombre' => 'Tubos Tapa Roja 10ml', 'categoria' => 'Material Desechable', 'unidad' => 'unid'],
            ['nombre' => 'Tubos Tapa Amarilla 5ml', 'categoria' => 'Material Desechable', 'unidad' => 'unid'],
            
            // Vidriería
            ['nombre' => 'Probeta 100ml', 'categoria' => 'Vidriería', 'unidad' => 'unid'],
            ['nombre' => 'Probeta 500ml', 'categoria' => 'Vidriería', 'unidad' => 'unid'],
            ['nombre' => 'Matraz Erlenmeyer 250ml', 'categoria' => 'Vidriería', 'unidad' => 'unid'],
            
            // Kits Diagnósticos
            ['nombre' => 'Kit Parvovirosis Canina', 'categoria' => 'Kits Diagnósticos', 'unidad' => 'unid'],
            ['nombre' => 'Kit Moquillo Canino', 'categoria' => 'Kits Diagnósticos', 'unidad' => 'unid'],
            ['nombre' => 'Kit Leucemia Felina', 'categoria' => 'Kits Diagnósticos', 'unidad' => 'unid'],
            
            // Kits ELISA
            ['nombre' => 'Kit ELISA Cortisol', 'categoria' => 'Kits ELISA', 'unidad' => 'unid'],
            ['nombre' => 'Kit ELISA Progesterona', 'categoria' => 'Kits ELISA', 'unidad' => 'unid'],
            ['nombre' => 'Kit ELISA T4', 'categoria' => 'Kits ELISA', 'unidad' => 'unid'],
            
            // Anticoagulantes
            ['nombre' => 'EDTA Tripotásico', 'categoria' => 'Anticoagulantes', 'unidad' => 'g'],
            ['nombre' => 'Citrato de Sodio 3.8%', 'categoria' => 'Anticoagulantes', 'unidad' => 'ml'],
            ['nombre' => 'Heparina Sódica', 'categoria' => 'Anticoagulantes', 'unidad' => 'ml'],
            
            // Soluciones Buffer
            ['nombre' => 'Buffer PBS pH 7.4', 'categoria' => 'Soluciones Buffer', 'unidad' => 'ml'],
            ['nombre' => 'Buffer Fosfato pH 7.2', 'categoria' => 'Soluciones Buffer', 'unidad' => 'ml'],
            ['nombre' => 'Buffer Tris-HCl', 'categoria' => 'Soluciones Buffer', 'unidad' => 'ml'],
            
            // Consumibles Generales
            ['nombre' => 'Agua Destilada', 'categoria' => 'Consumibles Generales', 'unidad' => 'L'],
            ['nombre' => 'Aceite de Inmersión', 'categoria' => 'Consumibles Generales', 'unidad' => 'ml'],
            ['nombre' => 'Papel Filtro', 'categoria' => 'Consumibles Generales', 'unidad' => 'unid'],

        ];

        foreach ($insumos as $insumo) {
            $categoriaId = $getCat($insumo['categoria']);
            $unidadId = $getUni($insumo['unidad']);
            
            if ($categoriaId && $unidadId) {
                Insumo::firstOrCreate(
                    ['nombre' => $insumo['nombre']],
                    [
                        'nombre' => $insumo['nombre'],
                        'categoria_id' => $categoriaId,
                        'unidad_medida_id' => $unidadId,
                        'estado' => true,
                    ]
                );
            }
        }
    }
}
