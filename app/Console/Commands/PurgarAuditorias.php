<?php

namespace App\Console\Commands;

use App\Models\Auditoria;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PurgarAuditorias extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'auditorias:purgar
                            {--meses=6 : Antigüedad mínima en meses para eliminar registros}
                            {--dry-run : Simular la eliminación sin borrar registros}';

    /**
     * The console command description.
     */
    protected $description = 'Elimina registros de auditoría con más de N meses de antigüedad';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $meses = (int) $this->option('meses');
        $dryRun = $this->option('dry-run');
        $fechaLimite = Carbon::now()->subMonths($meses);

        $this->info("🧹 Purga de auditorías con más de {$meses} meses de antigüedad");
        $this->info("   Fecha límite: {$fechaLimite->format('d/m/Y H:i')}");

        if ($dryRun) {
            $this->warn('   ⚠️  Modo simulación (dry-run): no se eliminarán registros');
        }

        $this->newLine();

        // Contar registros a eliminar
        $query = Auditoria::where('created_at', '<', $fechaLimite);
        $total = $query->count();

        if ($total === 0) {
            $this->info('✅ No hay registros de auditoría para purgar.');

            return Command::SUCCESS;
        }

        $this->info("📊 Registros encontrados: {$total}");

        if (! $dryRun) {
            // Eliminar en lotes para no sobrecargar la memoria
            $eliminados = Auditoria::where('created_at', '<', $fechaLimite)->delete();

            $this->newLine();
            $this->info("✅ Purga completada: {$eliminados} registros eliminados.");
        } else {
            $this->newLine();
            $this->info("✅ Simulación completada: {$total} registros se eliminarían.");
        }

        return Command::SUCCESS;
    }
}
