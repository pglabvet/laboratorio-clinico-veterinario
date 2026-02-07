<?php

namespace App\Livewire\Dashboard;

use App\Models\Analisis;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GraficosEstadisticas extends Component
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
        
        // Análisis por estado
        $queryEstado = Analisis::select('estado', DB::raw('count(*) as total'));
        
        if (!$user->can('ver-estadisticas-completas')) {
            $queryEstado->where('bioquimico_id', $user->id);
        }
        
        // Aplicar filtros de fecha
        if ($this->fechaInicio && $this->fechaFin) {
            $queryEstado->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin]);
        }
        
        // Aplicar filtro de sucursal
        if ($this->sucursalId && $user->can('filtrar-por-sucursal')) {
            $queryEstado->whereHas('muestra', function($q) {
                $q->where('sucursal_id', $this->sucursalId);
            });
        }
        
        $analisisPorEstado = $queryEstado->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        // Ordenar estados en orden lógico
        $ordenEstados = [Analisis::ESTADO_PENDIENTE, Analisis::ESTADO_EN_REVISION, Analisis::ESTADO_APROBADO, Analisis::ESTADO_ENVIADO];
        $analisisOrdenado = [];
        foreach ($ordenEstados as $estado) {
            if (isset($analisisPorEstado[$estado])) {
                $analisisOrdenado[$estado] = $analisisPorEstado[$estado];
            }
        }
        $analisisPorEstado = $analisisOrdenado;

        // Análisis por especie
        $queryEspecie = Analisis::join('muestras', 'analisis.muestra_id', '=', 'muestras.id')
            ->join('especies', 'muestras.especie_id', '=', 'especies.id');
        
        // Aplicar filtro de fecha o últimos 30 días por defecto
        if ($this->fechaInicio && $this->fechaFin) {
            $queryEspecie->whereBetween('analisis.created_at', [$this->fechaInicio, $this->fechaFin]);
        } else {
            $queryEspecie->where('analisis.created_at', '>=', now()->subDays(30));
        }
            
        if (!$user->can('ver-estadisticas-completas')) {
            $queryEspecie->where('analisis.bioquimico_id', $user->id);
        }
        
        // Aplicar filtro de sucursal
        if ($this->sucursalId && $user->can('filtrar-por-sucursal')) {
            $queryEspecie->where('muestras.sucursal_id', $this->sucursalId);
        }
        
        $analisisPorEspecie = $queryEspecie->select('especies.nombre', DB::raw('count(*) as total'))
            ->groupBy('especies.nombre')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->pluck('total', 'nombre')
            ->toArray();

        return view('livewire.dashboard.graficos-estadisticas', [
            'analisisPorEstado' => $analisisPorEstado,
            'analisisPorEspecie' => $analisisPorEspecie,
        ]);
    }

    public function getEstadoColor($estado)
    {
        return match($estado) {
            Analisis::ESTADO_PENDIENTE => '#eab308', // yellow
            Analisis::ESTADO_EN_REVISION => '#3b82f6', // blue
            Analisis::ESTADO_APROBADO => '#22c55e', // green
            Analisis::ESTADO_ENVIADO => '#a855f7', // purple
            default => '#6b7280', // gray
        };
    }

    public function getEstadoLabel($estado)
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
