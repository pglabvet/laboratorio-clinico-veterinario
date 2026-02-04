<?php

namespace Database\Seeders;

use App\Models\Analisis;
use App\Models\Muestra;
use App\Models\TipoAnalisis;
use App\Models\User;
use App\Models\Veterinaria;
use App\Models\Especie;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AnalisisSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener datos necesarios
        $veterinarias = Veterinaria::all();
        $especies = Especie::all();
        $bioquimico = User::where('email', 'bioquimico@labvet.com')->first();
        $sucursales = \App\Models\Sucursal::all();

        if ($veterinarias->isEmpty() || $especies->isEmpty() || !$bioquimico || $sucursales->isEmpty()) {
            $this->command->error('Necesitas ejecutar primero los seeders de Veterinarias, Especies, Usuarios y Sucursales');
            return;
        }

        // Crear tipos de análisis si no existen
        $tiposAnalisis = [
            ['nombre' => 'Hemograma Completo', 'descripcion' => 'Análisis completo de sangre'],
            ['nombre' => 'Perfil Bioquímico', 'descripcion' => 'Análisis bioquímico completo'],
            ['nombre' => 'Urianálisis', 'descripcion' => 'Análisis de orina'],
            ['nombre' => 'Coprológico', 'descripcion' => 'Análisis de heces'],
            ['nombre' => 'Perfil Hepático', 'descripcion' => 'Función hepática'],
        ];

        foreach ($tiposAnalisis as $tipo) {
            TipoAnalisis::firstOrCreate(
                ['nombre' => $tipo['nombre']],
                $tipo
            );
        }

        $tiposAnalisisCreados = TipoAnalisis::all();

        // Crear muestras y análisis de ejemplo
        $estados = ['pendiente', 'en_proceso', 'finalizado', 'aprobado'];
        
        for ($i = 1; $i <= 15; $i++) {
            $veterinaria = $veterinarias->random();
            $especie = $especies->random();
            $sucursal = $sucursales->random();
            
            // Crear muestra
            $muestra = Muestra::create([
                'codigo_muestra' => 'M-' . date('Y') . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'veterinaria_id' => $veterinaria->id,
                'sucursal_id' => $sucursal->id,
                'especie_id' => $especie->id,
                'paciente_nombre' => 'Paciente ' . $i,
                'propietario_nombre' => 'Propietario ' . $i,
                'tipo_muestra' => ['Sangre', 'Orina', 'Heces', 'Suero'][array_rand(['Sangre', 'Orina', 'Heces', 'Suero'])],
                'estado' => 'recibida',
                'fecha_recepcion' => now()->subDays(rand(0, 7)),
            ]);

            // Crear 1-2 análisis por muestra
            $numAnalisis = rand(1, 2);
            for ($j = 0; $j < $numAnalisis; $j++) {
                $tipoAnalisis = $tiposAnalisisCreados->random();
                $estado = $estados[array_rand($estados)];
                
                $analisis = Analisis::create([
                    'muestra_id' => $muestra->id,
                    'tipo_analisis_id' => $tipoAnalisis->id,
                    'bioquimico_id' => $bioquimico->id,
                    'estado' => $estado,
                    'fecha_inicio' => now()->subDays(rand(0, 5)),
                ]);

                // Si está finalizado o aprobado, agregar fecha de finalización
                if (in_array($estado, ['finalizado', 'aprobado'])) {
                    $analisis->update([
                        'fecha_finalizacion' => now()->subDays(rand(0, 3)),
                    ]);
                }
            }
        }

        $this->command->info('Se crearon 15 muestras con análisis de ejemplo');
    }
}
