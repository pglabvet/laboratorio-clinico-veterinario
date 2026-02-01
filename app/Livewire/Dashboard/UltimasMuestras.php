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

        // Si no es admin, filtrar por muestras asignadas o pendientes
        if (!$user->can('ver-estadisticas-completas')) {
            $query->where(function($q) use ($user) {
                $q->whereHas('analisis', function ($subQ) use ($user) {
                    $subQ->where('bioquimico_id', $user->id);
                })
                ->orWhereDoesntHave('analisis'); // Incluye muestras sin análisis aún
            });
        }

        $muestras = $query->get();

        return view('livewire.dashboard.ultimas-muestras', [
            'muestras' => $muestras
        ]);
    }

    /**
     * Obtener el estado de la muestra
     */
    public function getEstadoMuestra($muestra)
    {
        $analisis = $muestra->analisis->first();
        return $analisis?->estado ?? 'pendiente';
    }

    /**
     * Obtener badge de estado
     */
    public function getEstadoBadge($estado)
    {
        return match($estado) {
            'pendiente' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300',
            'en_proceso' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400',
            'finalizado' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400',
            'aprobado' => 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400',
            'rechazado' => 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400',
            default => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300',
        };
    }

    /**
     * Obtener texto del estado
     */
    public function getEstadoTexto($estado)
    {
        return match($estado) {
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En Proceso',
            'finalizado' => 'En Revisión',
            'aprobado' => 'Aprobado',
            'rechazado' => 'Rechazado',
            default => 'Desconocido',
        };
    }
}
