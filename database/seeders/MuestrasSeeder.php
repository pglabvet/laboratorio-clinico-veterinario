<?php

namespace Database\Seeders;

use App\Models\Analisis;
use App\Models\Especie;
use App\Models\Muestra;
use App\Models\Sucursal;
use App\Models\TipoAnalisis;
use App\Models\User;
use App\Models\Veterinaria;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MuestrasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener datos necesarios
        $especies = Especie::all();
        $veterinarias = Veterinaria::all();
        $sucursales = Sucursal::all();
        $tiposAnalisis = TipoAnalisis::all();
        $usuarios = User::all();

        if ($especies->isEmpty() || $veterinarias->isEmpty() || $sucursales->isEmpty() || $tiposAnalisis->isEmpty() || $usuarios->isEmpty()) {
            $this->command->error('Error: Faltan datos básicos. Ejecute primero los otros seeders.');
            return;
        }

        // Datos de prueba para muestras
        $razasPerro = ['Golden Retriever', 'Labrador', 'Pastor Alemán', 'Bulldog', 'Beagle', 'Chihuahua', 'Poodle'];
        $razasGato = ['Persa', 'Siamés', 'Maine Coon', 'Bengala', 'Ragdoll', 'Británico de pelo corto'];
        $colores = ['Blanco', 'Negro', 'Marrón', 'Gris', 'Dorado', 'Tricolor', 'Manchado'];
        $sexos = ['M', 'H'];
        $tiposMuestra = ['Sangre', 'Orina', 'Heces', 'Tejido', 'Fluido'];
        $nombresMascotas = ['Max', 'Luna', 'Rocky', 'Bella', 'Charlie', 'Coco', 'Simba', 'Nala', 'Thor', 'Mia'];
        $nombresPropietarios = [
            'Juan Pérez', 'María García', 'Carlos López', 'Ana Martínez', 'Pedro Rodríguez',
            'Sofía González', 'Diego Hernández', 'Valentina Torres', 'Miguel Ramírez', 'Camila Flores'
        ];

        $estadosMuestra = [Muestra::ESTADO_PENDIENTE, Muestra::ESTADO_EN_PROCESO, Muestra::ESTADO_COMPLETADO, Muestra::ESTADO_ENVIADO];
        $estadosAnalisis = ['pendiente', 'en_proceso', 'finalizado', 'aprobado'];

        // Crear 30 muestras con sus análisis
        for ($i = 1; $i <= 30; $i++) {
            $especie = $especies->random();
            $esPerro = stripos($especie->nombre, 'perro') !== false || stripos($especie->nombre, 'canino') !== false;
            $razas = $esPerro ? $razasPerro : $razasGato;

            // Determinar fecha de recepción (últimos 15 días + hoy)
            $diasAtras = rand(0, 15);
            $fechaRecepcion = Carbon::now()->subDays($diasAtras);

            // Muestras de hoy tienen más probabilidad de estar pendientes
            if ($diasAtras === 0) {
                $estadoMuestra = rand(1, 10) <= 7 ? Muestra::ESTADO_PENDIENTE : Muestra::ESTADO_EN_PROCESO;
            } else {
                $estadoMuestra = $estadosMuestra[array_rand($estadosMuestra)];
            }

            $muestra = Muestra::create([
                'codigo_muestra' => 'M' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'paciente_nombre' => $nombresMascotas[array_rand($nombresMascotas)],
                'especie_id' => $especie->id,
                'raza' => $razas[array_rand($razas)],
                'edad' => rand(1, 15) . ' años',
                'sexo' => $sexos[array_rand($sexos)],
                'color' => $colores[array_rand($colores)],
                'propietario_nombre' => $nombresPropietarios[array_rand($nombresPropietarios)],
                'veterinaria_id' => $veterinarias->random()->id,
                'sucursal_id' => $sucursales->random()->id,
                'tipo_muestra' => $tiposMuestra[array_rand($tiposMuestra)],
                'fecha_recepcion' => $fechaRecepcion,
                'estado' => $estadoMuestra,
                'observaciones' => rand(1, 10) <= 3 ? 'Muestra en condiciones óptimas para análisis' : null,
            ]);

            // Crear 1-3 análisis por muestra
            $numAnalisis = rand(1, 3);
            for ($j = 0; $j < $numAnalisis; $j++) {
                // El estado del análisis depende del estado de la muestra
                if ($estadoMuestra === Muestra::ESTADO_PENDIENTE) {
                    $estadoAnalisis = 'pendiente';
                } elseif ($estadoMuestra === Muestra::ESTADO_EN_PROCESO) {
                    $estadoAnalisis = rand(1, 10) <= 7 ? 'pendiente' : 'en_proceso';
                } else {
                    $estadoAnalisis = $estadosAnalisis[array_rand($estadosAnalisis)];
                }

                $fechaInicio = $estadoAnalisis !== 'pendiente' ? $fechaRecepcion->copy()->addHours(rand(1, 24)) : null;
                $fechaFinalizacion = in_array($estadoAnalisis, ['finalizado', 'aprobado']) && $fechaInicio 
                    ? $fechaInicio->copy()->addHours(rand(2, 48)) 
                    : null;
                $fechaAprobacion = in_array($estadoAnalisis, ['aprobado']) && $fechaFinalizacion 
                    ? $fechaFinalizacion->copy()->addHours(rand(1, 12)) 
                    : null;

                Analisis::create([
                    'muestra_id' => $muestra->id,
                    'tipo_analisis_id' => $tiposAnalisis->random()->id,
                    'plantilla_formulario_id' => null, // Se asignará cuando se creen resultados
                    'bioquimico_id' => $usuarios->random()->id, // Siempre se asigna un bioquímico
                    'aprobador_id' => $fechaAprobacion ? $usuarios->random()->id : null,
                    'estado' => $estadoAnalisis,
                    'observaciones_bioquimico' => $fechaInicio && rand(1, 10) <= 4 ? 'Análisis en proceso normal' : null,
                    'observaciones_aprobador' => $fechaAprobacion && rand(1, 10) <= 3 ? 'Resultados dentro de parámetros esperados' : null,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_finalizacion' => $fechaFinalizacion,
                    'fecha_aprobacion' => $fechaAprobacion,
                ]);
            }
        }

        $this->command->info('✓ Se crearon 30 muestras con sus análisis asociados');
    }
}
