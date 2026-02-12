<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LimpiarArchivosAntiguos extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cleanup:archivos-antiguos 
                            {--meses=2 : Antigüedad mínima en meses para eliminar archivos}
                            {--dry-run : Simular la eliminación sin borrar archivos}';

    /**
     * The console command description.
     */
    protected $description = 'Elimina PDFs y gráficas de charts con más de N meses de antigüedad';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $meses = (int) $this->option('meses');
        $dryRun = $this->option('dry-run');
        $fechaLimite = Carbon::now()->subMonths($meses);

        $this->info("🧹 Limpieza de archivos con más de {$meses} meses de antigüedad");
        $this->info("   Fecha límite: {$fechaLimite->format('d/m/Y H:i')}");

        if ($dryRun) {
            $this->warn('   ⚠️  Modo simulación (dry-run): no se eliminarán archivos');
        }

        $this->newLine();

        // Limpiar PDFs
        $pdfsEliminados = $this->limpiarDirectorio('pdfs', $fechaLimite, $dryRun);

        // Limpiar Charts (nueva estructura año/mes)
        $chartsEliminados = $this->limpiarDirectorio('charts', $fechaLimite, $dryRun);

        // Limpiar Charts (estructura antigua plana)
        $chartsPlanos = $this->limpiarArchivosPlanos('charts', $fechaLimite, $dryRun);

        $this->newLine();
        $this->info('📊 Resumen:');
        $this->table(
            ['Tipo', 'Archivos eliminados'],
            [
                ['PDFs', $pdfsEliminados],
                ['Charts (año/mes)', $chartsEliminados],
                ['Charts (planos)', $chartsPlanos],
                ['Total', $pdfsEliminados + $chartsEliminados + $chartsPlanos],
            ]
        );

        // Limpiar carpetas vacías
        if (!$dryRun) {
            $this->limpiarCarpetasVacias('pdfs');
            $this->limpiarCarpetasVacias('charts');
            $this->info('🗂️  Carpetas vacías eliminadas.');
        }

        $this->newLine();
        $this->info('✅ Limpieza completada.');

        return Command::SUCCESS;
    }

    /**
     * Limpia archivos dentro de la estructura año/mes (ej: pdfs/2024/01/)
     */
    private function limpiarDirectorio(string $directorio, Carbon $fechaLimite, bool $dryRun): int
    {
        $eliminados = 0;
        $disk = Storage::disk('public');

        // Obtener todos los archivos recursivamente
        $archivos = $disk->allFiles($directorio);

        foreach ($archivos as $archivo) {
            // Solo procesar archivos dentro de subdirectorios año/mes
            if (!preg_match('#^' . $directorio . '/\d{4}/\d{2}/#', $archivo)) {
                continue;
            }

            $ultimaModificacion = Carbon::createFromTimestamp($disk->lastModified($archivo));

            if ($ultimaModificacion->lt($fechaLimite)) {
                $tamaño = $this->formatearTamaño($disk->size($archivo));

                if ($dryRun) {
                    $this->line("   [SIMULAR] Eliminar: {$archivo} ({$tamaño}) - {$ultimaModificacion->format('d/m/Y')}");
                } else {
                    $disk->delete($archivo);
                    $this->line("   [ELIMINADO] {$archivo} ({$tamaño})");
                }

                $eliminados++;
            }
        }

        $accion = $dryRun ? 'encontrados' : 'eliminados';
        $this->info("📁 {$directorio}/ (año/mes): {$eliminados} archivos {$accion}");

        return $eliminados;
    }

    /**
     * Limpia archivos planos en el directorio raíz (estructura antigua de charts)
     */
    private function limpiarArchivosPlanos(string $directorio, Carbon $fechaLimite, bool $dryRun): int
    {
        $eliminados = 0;
        $disk = Storage::disk('public');

        $archivos = $disk->files($directorio);

        foreach ($archivos as $archivo) {
            $ultimaModificacion = Carbon::createFromTimestamp($disk->lastModified($archivo));

            if ($ultimaModificacion->lt($fechaLimite)) {
                $tamaño = $this->formatearTamaño($disk->size($archivo));

                if ($dryRun) {
                    $this->line("   [SIMULAR] Eliminar: {$archivo} ({$tamaño}) - {$ultimaModificacion->format('d/m/Y')}");
                } else {
                    $disk->delete($archivo);
                    $this->line("   [ELIMINADO] {$archivo} ({$tamaño})");
                }

                $eliminados++;
            }
        }

        $accion = $dryRun ? 'encontrados' : 'eliminados';
        $this->info("📁 {$directorio}/ (planos): {$eliminados} archivos {$accion}");

        return $eliminados;
    }

    /**
     * Elimina carpetas vacías de año/mes que quedaron después de la limpieza
     */
    private function limpiarCarpetasVacias(string $directorio): void
    {
        $disk = Storage::disk('public');
        $directorios = $disk->allDirectories($directorio);

        // Ordenar de más profundo a menos profundo para eliminar hijos primero
        $directorios = collect($directorios)->sortByDesc(fn ($d) => substr_count($d, '/'))->values();

        foreach ($directorios as $dir) {
            if (count($disk->files($dir)) === 0 && count($disk->directories($dir)) === 0) {
                $disk->deleteDirectory($dir);
            }
        }
    }

    /**
     * Formatea el tamaño del archivo para mostrar
     */
    private function formatearTamaño(int $bytes): string
    {
        $unidades = ['B', 'KB', 'MB', 'GB'];
        $indice = 0;
        $tamaño = $bytes;

        while ($tamaño >= 1024 && $indice < count($unidades) - 1) {
            $tamaño /= 1024;
            $indice++;
        }

        return round($tamaño, 2) . ' ' . $unidades[$indice];
    }
}
