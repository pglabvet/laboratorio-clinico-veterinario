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
            'muestra.sucursal',
            'tipoAnalisis'
        ]);
        
        if (!$user->can('ver-estadisticas-completas')) {
            // Solo análisis asignados al usuario
            $query->where('bioquimico_id', $user->id);
        }
        
        // Aplicar filtros de fecha
        if ($this->fechaInicio && $this->fechaFin) {
            $query->whereBetween('analisis.created_at', [$this->fechaInicio, $this->fechaFin]);
        }
        
        // Aplicar filtro de sucursal
        if ($this->sucursalId && $user->can('filtrar-por-sucursal')) {
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
            Analisis::ESTADO_PENDIENTE => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
            Analisis::ESTADO_EN_REVISION => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
            Analisis::ESTADO_APROBADO => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            Analisis::ESTADO_ENVIADO => 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
        };
    }

    public function getEstadoTexto($estado)
    {
        return match($estado) {
            Analisis::ESTADO_PENDIENTE => 'Pendiente',
            Analisis::ESTADO_EN_REVISION => 'En Revisión',
            Analisis::ESTADO_APROBADO => 'Aprobado',
            Analisis::ESTADO_ENVIADO => 'Enviado',
            default => ucfirst($estado),
        };
    }
}
