<?php

namespace App\Console\Commands;

use App\Models\Analisis;
use App\Models\Muestra;
use Illuminate\Console\Command;

class SincronizarEstadosMuestras extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'muestras:sincronizar-estados
                          {--dry-run : Mostrar los cambios sin aplicarlos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar estados de muestras basándose en los estados de sus análisis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Modo DRY RUN - No se aplicarán cambios');
        }
        
        $this->info('Sincronizando estados de muestras...');
        $this->newLine();
        
        $muestras = Muestra::with('analisis')->get();
        $cambios = 0;
        
        foreach ($muestras as $muestra) {
            $estadoAnterior = $muestra->estado;
            
            // Calcular nuevo estado
            $analisis = $muestra->analisis;
            
            if ($analisis->isEmpty()) {
                $nuevoEstado = Muestra::ESTADO_PENDIENTE;
            } else {
                $totalAnalisis = $analisis->count();
                $pendientes = $analisis->where('estado', Analisis::ESTADO_PENDIENTE)->count();
                $enRevision = $analisis->where('estado', Analisis::ESTADO_EN_REVISION)->count();
                $aprobados = $analisis->where('estado', Analisis::ESTADO_APROBADO)->count();
                $enviados = $analisis->where('estado', Analisis::ESTADO_ENVIADO)->count();
                
                if ($enviados === $totalAnalisis) {
                    $nuevoEstado = Muestra::ESTADO_ENVIADO;
                } elseif ($aprobados === $totalAnalisis) {
                    $nuevoEstado = Muestra::ESTADO_COMPLETADO;
                } elseif ($enRevision > 0 || $aprobados > 0 || $enviados > 0) {
                    $nuevoEstado = Muestra::ESTADO_EN_PROCESO;
                } else {
                    $nuevoEstado = Muestra::ESTADO_PENDIENTE;
                }
            }
            
            if ($estadoAnterior !== $nuevoEstado) {
                $this->line("📋 {$muestra->codigo_muestra}: <fg=yellow>{$estadoAnterior}</> → <fg=green>{$nuevoEstado}</>");
                
                if (!$dryRun) {
                    $muestra->estado = $nuevoEstado;
                    $muestra->saveQuietly();
                }
                
                $cambios++;
            }
        }
        
        $this->newLine();
        
        if ($cambios === 0) {
            $this->info('✓ No se encontraron muestras con estados desincronizados');
        } else {
            if ($dryRun) {
                $this->warn("Se encontraron {$cambios} muestras con estados desincronizados");
                $this->info('Ejecuta sin --dry-run para aplicar los cambios');
            } else {
                $this->info("✓ Se sincronizaron {$cambios} muestras correctamente");
            }
        }
        
        return Command::SUCCESS;
    }
}
