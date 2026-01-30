<?php

namespace App\Livewire\Dashboard;

use App\Models\Analisis;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ActividadReciente extends Component
{
    public $fechaInicio = null;
    public $fechaFin = null;
    public $sucursalId = null;

    protected $listeners = ['filtrosActualizados'];

    public function filtrosActualizados($filtros)
    {
        $this->fechaInicio = $filtros['fechaInicio'] ?? null;
        $this->fechaFin = $filtros['fechaFin'] ?? null;
        $this->sucursalId = $filtros['sucursalId'] ?? null;
    }

    public function render()
    {
        /** @var User $user */
        $user = Auth::user();
        
        $query = Analisis::with([
            'muestra.veterinaria',
            'tipoAnalisis',
            'bioquimico'
        ]);
        
        if ($user->hasRole('Bioquímico')) {
            // Solo análisis asignados al bioquímico
            $query->where('bioquimico_id', $user->id);
        }
        
        // Aplicar filtros de fecha
        if ($this->fechaInicio && $this->fechaFin) {
            $query->whereBetween('analisis.created_at', [$this->fechaInicio, $this->fechaFin]);
        }
        
        // Aplicar filtro de sucursal
        if ($this->sucursalId && $user->hasRole('Administrador')) {
            $query->whereHas('muestra', function($q) {
                $q->where('sucursal_id', $this->sucursalId);
            });
        }
        
        $analisisRecientes = $query->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.dashboard.actividad-reciente', [
            'analisisRecientes' => $analisisRecientes,
        ]);
    }

    public function getEstadoBadge($estado)
    {
        return match($estado) {
            'pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
            'en_proceso' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
            'finalizado' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
            'aprobado' => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            'rechazado' => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
        };
    }

    public function getEstadoTexto($estado)
    {
        return match($estado) {
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En Proceso',
            'finalizado' => 'Finalizado',
            'aprobado' => 'Aprobado',
            'rechazado' => 'Rechazado',
            default => ucfirst($estado),
        };
    }
}
