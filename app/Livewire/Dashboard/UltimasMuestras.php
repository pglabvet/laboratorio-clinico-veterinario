<?php

namespace App\Livewire\Dashboard;

use App\Models\Muestra;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Livewire\Component;

class UltimasMuestras extends Component
{
    public function placeholder()
    {
        return <<<'HTML'
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div class="h-5 w-52 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                    <div class="h-4 w-16 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                </div>
            </div>
            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @for ($i = 0; $i < 5; $i++)
                <div class="flex items-start gap-4 p-4">
                    <div class="h-10 w-10 shrink-0 animate-pulse rounded-full bg-zinc-200 dark:bg-zinc-700"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 w-32 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                        <div class="h-3 w-48 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                        <div class="h-3 w-40 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                    </div>
                </div>
                @endfor
            </div>
        </div>
        HTML;
    }

    public function render()
    {
        /** @var User $user */
        $user = Auth::user();

        // Obtener las últimas 5 muestras registradas
        $query = Muestra::with(['especie', 'veterinaria', 'analisis'])
            ->latest()
            ->limit(5);

        // Si no es admin, filtrar por sucursal del usuario automáticamente
        if (!$user->can('ver-estadisticas-completas') && $user->sucursal_id) {
            $query->where('sucursal_id', $user->sucursal_id);
        }

        $muestras = $query->get();

        return view('livewire.dashboard.ultimas-muestras', [
            'muestras' => $muestras
        ]);
    }

    /**
     * Obtener badge de estado usando los colores del modelo Muestra
     */
    public function getEstadoBadge($muestra)
    {
        $color = $muestra->getColorEstado();
        
        return match($color) {
            'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
            'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
            'green' => 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400',
            'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400',
            default => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300',
        };
    }
}
