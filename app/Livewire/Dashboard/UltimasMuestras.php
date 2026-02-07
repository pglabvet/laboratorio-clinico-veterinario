<?php

namespace App\Livewire\Dashboard;

use App\Models\Muestra;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Livewire\Component;

class UltimasMuestras extends Component
{
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
