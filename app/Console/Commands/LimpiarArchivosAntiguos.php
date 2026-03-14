<?php

namespace App\Console\Commands;

use App\Models\Pdf;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

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

        // === 1. PDFs: usar fecha_generacion de la base de datos ===
        $resultadoPdfs = $this->limpiarPdfsDesdeBaseDeDatos($fechaLimite, $dryRun);

        // === 2. PDFs huérfanos: archivos en disco sin registro en BD ===
        $pdfsHuerfanos = $this->limpiarPdfsHuerfanos($fechaLimite, $dryRun);

        // === 3. Registros huérfanos: tuplas en BD sin archivo en disco ===
        $registrosHuerfanos = $this->limpiarRegistrosHuerfanos($dryRun);

        // === 4. Charts: usar filemtime (no tienen tabla en BD) ===
        $chartsEliminados = $this->limpiarChartsPorFecha('charts', $fechaLimite, $dryRun);
        $chartsPlanos = $this->limpiarArchivosPlanos('charts', $fechaLimite, $dryRun);

        $this->newLine();
        $this->info('📊 Resumen:');
        $this->table(
            ['Tipo', 'Archivos eliminados', 'Registros BD'],
            [
                ['PDFs (por fecha_generacion)', $resultadoPdfs['archivos'], $resultadoPdfs['registros']],
                ['PDFs huérfanos (sin registro BD)', $pdfsHuerfanos, '-'],
                ['Registros huérfanos (sin archivo)', '-', $registrosHuerfanos],
                ['Charts (año/mes)', $chartsEliminados, '-'],
                ['Charts (planos)', $chartsPlanos, '-'],
                [
                    'TOTAL',
                    $resultadoPdfs['archivos'] + $pdfsHuerfanos + $chartsEliminados + $chartsPlanos,
                    $resultadoPdfs['registros'] + $registrosHuerfanos,
                ],
            ]
        );

        // Limpiar carpetas vacías
        if (! $dryRun) {
            $this->limpiarCarpetasVacias('pdfs');
            $this->limpiarCarpetasVacias('charts');
            $this->info('🗂️  Carpetas vacías eliminadas.');
        }

        $this->newLine();
        $this->info('✅ Limpieza completada.');

        return Command::SUCCESS;
    }

    /**
     * Limpia PDFs usando la fecha_generacion de la tabla pdfs (fuente confiable).
     */
    private function limpiarPdfsDesdeBaseDeDatos(Carbon $fechaLimite, bool $dryRun): array
    {
        $disk = Storage::disk('public');
        $archivosEliminados = 0;
        $registrosEliminados = 0;

        $pdfsAntiguos = Pdf::where('fecha_generacion', '<', $fechaLimite)->get();

        foreach ($pdfsAntiguos as $pdf) {
            $ruta = $pdf->ruta_archivo;
            $existeArchivo = $disk->exists($ruta);
            $tamaño = $existeArchivo ? $this->formatearTamaño($disk->size($ruta)) : 'N/A';
            $fecha = $pdf->fecha_generacion->format('d/m/Y H:i');

            if ($dryRun) {
                $estado = $existeArchivo ? 'archivo + registro' : 'solo registro (archivo no existe)';
                $this->line("   [SIMULAR] {$ruta} ({$tamaño}) - Generado: {$fecha} - {$estado}");
            } else {
                // Eliminar archivo físico si existe
                if ($existeArchivo) {
                    $disk->delete($ruta);
                    $archivosEliminados++;
                }

                // Eliminar registro de la BD siempre
                $pdf->delete();
                $registrosEliminados++;

                $this->line("   [ELIMINADO] {$ruta} ({$tamaño}) - Generado: {$fecha}");
            }

            if ($dryRun) {
                $archivosEliminados += $existeArchivo ? 1 : 0;
                $registrosEliminados++;
            }
        }

        $accion = $dryRun ? 'encontrados' : 'eliminados';
        $this->info("📁 PDFs (base de datos): {$registrosEliminados} registros {$accion}");

        return ['archivos' => $archivosEliminados, 'registros' => $registrosEliminados];
    }

    /**
     * Limpia archivos PDF en disco que NO tienen registro en la tabla pdfs.
     * Usa filemtime como fallback ya que no hay fecha en BD.
     */
    private function limpiarPdfsHuerfanos(Carbon $fechaLimite, bool $dryRun): int
    {
        $disk = Storage::disk('public');
        $eliminados = 0;

        // Obtener todas las rutas registradas en BD
        $rutasEnBd = Pdf::pluck('ruta_archivo')->toArray();

        // Obtener todos los archivos PDF en disco
        $archivosEnDisco = $disk->allFiles('pdfs');

        foreach ($archivosEnDisco as $archivo) {
            // Solo procesar archivos dentro de estructura año/mes
            if (! preg_match('#^pdfs/\d{4}/\d{2}/#', $archivo)) {
                continue;
            }

            // Si tiene registro en BD, ya fue procesado arriba
            if (in_array($archivo, $rutasEnBd)) {
                continue;
            }

            // Es huérfano: usar filemtime como fallback
            $ultimaModificacion = Carbon::createFromTimestamp($disk->lastModified($archivo));

            if ($ultimaModificacion->lt($fechaLimite)) {
                $tamaño = $this->formatearTamaño($disk->size($archivo));

                if ($dryRun) {
                    $this->line("   [SIMULAR] Huérfano: {$archivo} ({$tamaño}) - Modificado: {$ultimaModificacion->format('d/m/Y')}");
                } else {
                    $disk->delete($archivo);
                    $this->line("   [ELIMINADO] Huérfano: {$archivo} ({$tamaño})");
                }

                $eliminados++;
            }
        }

        $accion = $dryRun ? 'encontrados' : 'eliminados';
        $this->info("📁 PDFs huérfanos (sin registro BD): {$eliminados} archivos {$accion}");

        return $eliminados;
    }

    /**
     * Limpia registros en la tabla pdfs cuyo archivo físico no existe en disco.
     */
    private function limpiarRegistrosHuerfanos(bool $dryRun): int
    {
        $disk = Storage::disk('public');
        $eliminados = 0;

        $todosPdfs = Pdf::all();

        foreach ($todosPdfs as $pdf) {
            if (! $disk->exists($pdf->ruta_archivo)) {
                if ($dryRun) {
                    $this->line("   [SIMULAR] Registro huérfano ID={$pdf->id}: {$pdf->ruta_archivo} (archivo no existe)");
                } else {
                    $pdf->delete();
                    $this->line("   [ELIMINADO] Registro huérfano ID={$pdf->id}: {$pdf->ruta_archivo}");
                }

                $eliminados++;
            }
        }

        $accion = $dryRun ? 'encontrados' : 'eliminados';
        $this->info("🗃️  Registros huérfanos (sin archivo): {$eliminados} {$accion}");

        return $eliminados;
    }

    /**
     * Limpia charts dentro de la estructura año/mes usando filemtime.
     */
    private function limpiarChartsPorFecha(string $directorio, Carbon $fechaLimite, bool $dryRun): int
    {
        $eliminados = 0;
        $disk = Storage::disk('public');

        $archivos = $disk->allFiles($directorio);

        foreach ($archivos as $archivo) {
            // Solo procesar archivos dentro de subdirectorios año/mes
            if (! preg_match('#^' . $directorio . '/\d{4}/\d{2}/#', $archivo)) {
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
     * Limpia archivos planos en el directorio raíz (estructura antigua de charts).
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
     * Elimina carpetas vacías que quedaron después de la limpieza.
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
     * Formatea el tamaño del archivo para mostrar.
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
