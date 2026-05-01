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

/**
 * Seeder para generar datos masivos de muestras.
 * 
 * Uso: php artisan db:seed --class=MuestrasStressTestSeeder
 * 
 * Genera cientos de muestras con análisis asociados para probar
 * el rendimiento de la exportación a PDF.
 */
class MuestrasStressTestSeeder extends Seeder
{
    /**
     * Cantidad de muestras a generar.
     * Cada muestra tendrá 1-3 análisis, así que 500 muestras ≈ 1000 análisis.
     */
    private int $cantidadMuestras = 500;

    public function run(): void
    {
        $this->command->info('🔄 Generando datos masivos de muestras...');

        $especies = Especie::all();
        $veterinarias = Veterinaria::all();
        $sucursales = Sucursal::where('estado', true)->get();
        $tiposAnalisis = TipoAnalisis::all();
        $usuarios = User::all();

        if ($especies->isEmpty() || $veterinarias->isEmpty() || $sucursales->isEmpty() || $tiposAnalisis->isEmpty() || $usuarios->isEmpty()) {
            $this->command->error('❌ Faltan datos básicos. Ejecute primero los seeders de especies, veterinarias, sucursales, tipos de análisis y usuarios.');
            return;
        }

        $this->command->info("📦 Especies: {$especies->count()} | Veterinarias: {$veterinarias->count()} | Sucursales: {$sucursales->count()}");
        $this->command->info("📊 Generando {$this->cantidadMuestras} muestras...");

        // Obtener un número base seguro para generar códigos únicos
        $ultimoId = Muestra::max('id') ?? 0;
        $baseNum = $ultimoId + 1000; // Offset para evitar colisiones

        $razasPerro = ['Golden Retriever', 'Labrador', 'Pastor Alemán', 'Bulldog Francés', 'Beagle', 'Chihuahua', 'Poodle', 'Rottweiler', 'Dálmata', 'Husky Siberiano', 'Boxer', 'Yorkshire', 'Schnauzer', 'Cocker Spaniel', 'Doberman'];
        $razasGato = ['Persa', 'Siamés', 'Maine Coon', 'Bengala', 'Ragdoll', 'Británico de pelo corto', 'Abisinio', 'Sphynx', 'Scottish Fold', 'Birmano'];
        $razasOtros = ['Mestizo', 'Sin raza definida', null];
        $colores = ['Blanco', 'Negro', 'Marrón', 'Gris', 'Dorado', 'Tricolor', 'Manchado', 'Atigrado', 'Crema', 'Rojizo'];
        $sexos = ['M', 'H'];
        $tiposMuestra = ['Sangre', 'Orina', 'Heces', 'Tejido', 'Fluido', 'Raspado', 'Biopsia', 'Hisopado'];

        $nombresMascotas = [
            'Max', 'Luna', 'Rocky', 'Bella', 'Charlie', 'Coco', 'Simba', 'Nala', 'Thor', 'Mia',
            'Toby', 'Lola', 'Rex', 'Kira', 'Bruno', 'Maya', 'Zeus', 'Daisy', 'Duke', 'Chloe',
            'Lucky', 'Milo', 'Rosie', 'Jack', 'Maggie', 'Buddy', 'Sadie', 'Tucker', 'Ginger', 'Bear',
            'Firulais', 'Pelusa', 'Canela', 'Copito', 'Manchas', 'Panchito', 'Chispas', 'Negrito',
        ];

        $nombresPropietarios = [
            'Juan Pérez', 'María García', 'Carlos López', 'Ana Martínez', 'Pedro Rodríguez',
            'Sofía González', 'Diego Hernández', 'Valentina Torres', 'Miguel Ramírez', 'Camila Flores',
            'Roberto Sánchez', 'Laura Morales', 'Andrés Castro', 'Daniela Vargas', 'Fernando Silva',
            'Lucía Mendoza', 'Gabriel Rojas', 'Isabella Ortiz', 'José Gutiérrez', 'Mariana Díaz',
            'Ricardo Paredes', 'Natalia Quispe', 'Alejandro Mamani', 'Patricia Condori', 'Eduardo Choque',
        ];

        $observaciones = [
            null, null, null, null, null, null, null, // 70% sin observaciones
            'Muestra en condiciones óptimas',
            'Paciente en ayunas de 12 horas',
            'Muestra ligeramente hemolizada',
            'Requiere análisis urgente',
            'Segunda muestra del paciente',
            'Paciente bajo tratamiento con antibióticos',
            'Muestra tomada post-cirugía',
        ];

        $estadosMuestra = [Muestra::ESTADO_PENDIENTE, Muestra::ESTADO_EN_PROCESO, Muestra::ESTADO_COMPLETADO, Muestra::ESTADO_ENVIADO];
        $estadosAnalisis = [Analisis::ESTADO_PENDIENTE, Analisis::ESTADO_EN_REVISION, Analisis::ESTADO_APROBADO, Analisis::ESTADO_ENVIADO];

        $totalAnalisis = 0;
        $bar = $this->command->getOutput()->createProgressBar($this->cantidadMuestras);
        $bar->start();

        for ($i = 1; $i <= $this->cantidadMuestras; $i++) {
            $especie = $especies->random();
            $nombreEspecie = strtolower($especie->nombre);

            // Seleccionar raza según especie
            if (str_contains($nombreEspecie, 'perro') || str_contains($nombreEspecie, 'canino') || str_contains($nombreEspecie, 'can')) {
                $raza = $razasPerro[array_rand($razasPerro)];
            } elseif (str_contains($nombreEspecie, 'gato') || str_contains($nombreEspecie, 'felino') || str_contains($nombreEspecie, 'fel')) {
                $raza = $razasGato[array_rand($razasGato)];
            } else {
                $raza = $razasOtros[array_rand($razasOtros)];
            }

            // Distribuir fechas en los últimos 6 meses
            $diasAtras = rand(0, 180);
            $fechaRecepcion = Carbon::now()->subDays($diasAtras);

            // Estado realista según antigüedad
            if ($diasAtras <= 2) {
                // Recientes: mayormente pendientes
                $estadoMuestra = rand(1, 10) <= 7 ? Muestra::ESTADO_PENDIENTE : Muestra::ESTADO_EN_PROCESO;
            } elseif ($diasAtras <= 7) {
                // 1 semana: en proceso o completado
                $estadoMuestra = $estadosMuestra[rand(0, 2)];
            } else {
                // Antiguas: mayormente completadas/enviadas
                $estadoMuestra = rand(1, 10) <= 8
                    ? ($estadosMuestra[rand(2, 3)])
                    : $estadosMuestra[array_rand($estadosMuestra)];
            }

            $codigoNum = $baseNum + $i;
            $codigoMuestra = 'ST' . str_pad($codigoNum, 6, '0', STR_PAD_LEFT);

            $muestra = Muestra::create([
                'codigo_muestra' => $codigoMuestra,
                'paciente_nombre' => $nombresMascotas[array_rand($nombresMascotas)],
                'especie_id' => $especie->id,
                'raza' => $raza,
                'edad' => rand(0, 18) . ' años',
                'sexo' => $sexos[array_rand($sexos)],
                'color' => $colores[array_rand($colores)],
                'propietario_nombre' => $nombresPropietarios[array_rand($nombresPropietarios)],
                'veterinaria_id' => $veterinarias->random()->id,
                'sucursal_id' => $sucursales->random()->id,
                'tipo_muestra' => $tiposMuestra[array_rand($tiposMuestra)],
                'fecha_recepcion' => $fechaRecepcion,
                'estado' => $estadoMuestra,
                'observaciones' => $observaciones[array_rand($observaciones)],
            ]);

            // Crear 1-3 análisis por muestra
            $numAnalisis = rand(1, 3);
            $tiposUsados = [];

            for ($j = 0; $j < $numAnalisis; $j++) {
                // Evitar tipos duplicados en la misma muestra
                do {
                    $tipoAnalisis = $tiposAnalisis->random();
                } while (in_array($tipoAnalisis->id, $tiposUsados) && count($tiposUsados) < $tiposAnalisis->count());
                $tiposUsados[] = $tipoAnalisis->id;

                // Estado coherente con la muestra
                if ($estadoMuestra === Muestra::ESTADO_PENDIENTE) {
                    $estadoAnalisis = Analisis::ESTADO_PENDIENTE;
                } elseif ($estadoMuestra === Muestra::ESTADO_EN_PROCESO) {
                    $estadoAnalisis = rand(1, 10) <= 6 ? Analisis::ESTADO_PENDIENTE : Analisis::ESTADO_EN_REVISION;
                } elseif ($estadoMuestra === Muestra::ESTADO_COMPLETADO) {
                    $estadoAnalisis = rand(1, 10) <= 8 ? Analisis::ESTADO_APROBADO : Analisis::ESTADO_EN_REVISION;
                } else {
                    $estadoAnalisis = Analisis::ESTADO_ENVIADO;
                }

                $fechaInicio = $estadoAnalisis !== Analisis::ESTADO_PENDIENTE
                    ? $fechaRecepcion->copy()->addHours(rand(1, 48))
                    : null;
                $fechaFin = in_array($estadoAnalisis, [Analisis::ESTADO_APROBADO, Analisis::ESTADO_ENVIADO]) && $fechaInicio
                    ? $fechaInicio->copy()->addHours(rand(2, 72))
                    : null;
                $fechaAprobacion = in_array($estadoAnalisis, [Analisis::ESTADO_APROBADO, Analisis::ESTADO_ENVIADO]) && $fechaFin
                    ? $fechaFin->copy()->addHours(rand(1, 24))
                    : null;

                Analisis::create([
                    'muestra_id' => $muestra->id,
                    'tipo_analisis_id' => $tipoAnalisis->id,
                    'plantilla_formulario_id' => null,
                    'bioquimico_id' => $usuarios->random()->id,
                    'aprobador_id' => $fechaAprobacion ? $usuarios->random()->id : null,
                    'estado' => $estadoAnalisis,
                    'observaciones_bioquimico' => $fechaInicio && rand(1, 10) <= 3 ? 'Análisis en proceso normal' : null,
                    'observaciones_aprobador' => $fechaAprobacion && rand(1, 10) <= 3 ? 'Resultados dentro de parámetros esperados' : null,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_finalizacion' => $fechaFin,
                    'fecha_aprobacion' => $fechaAprobacion,
                ]);

                $totalAnalisis++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✅ Generadas {$this->cantidadMuestras} muestras con {$totalAnalisis} análisis.");
        $this->command->info('📌 Ahora podés probar la exportación a PDF de muestras.');
    }
}
