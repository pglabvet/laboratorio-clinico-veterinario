<?php

namespace App\Livewire\Dashboard;

use App\Models\Analisis;
use App\Models\Muestra;
use App\Models\Insumo;
use App\Models\Sucursal;
use App\Models\Veterinaria;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EstadisticasPrincipales extends Component
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
        
        // Base query
        $baseQuery = Analisis::query();
        
        if (!$user->can('ver-estadisticas-completas')) {
            $baseQuery->where('bioquimico_id', $user->id);
        }
        
        // Aplicar filtros de fecha
        if ($this->fechaInicio && $this->fechaFin) {
            $baseQuery->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin]);
        }
        
        // Aplicar filtro de sucursal
        if ($this->sucursalId && $user->can('filtrar-por-sucursal')) {
            $baseQuery->whereHas('muestra', function($q) {
                $q->where('sucursal_id', $this->sucursalId);
            });
        }
        
        // Estadísticas desglosadas por estado
        $analisisPendientes = (clone $baseQuery)->where('estado', 'pendiente')->count();
        $analisisEnProceso = (clone $baseQuery)->where('estado', 'en_proceso')->count();
        $analisisFinalizados = (clone $baseQuery)->where('estado', 'finalizado')->count();
        $analisisAprobados = (clone $baseQuery)->where('estado', 'aprobado')->count();
        
        // Muestras recibidas hoy
        $queryMuestras = Muestra::query();
        
        if ($this->fechaInicio && $this->fechaFin) {
            $queryMuestras->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin]);
        } else {
            $queryMuestras->whereDate('created_at', Carbon::today());
        }
        
        if ($this->sucursalId && $user->can('filtrar-por-sucursal')) {
            $queryMuestras->where('sucursal_id', $this->sucursalId);
        }
        
        $muestrasHoy = $queryMuestras->count();
        
        // Insumos con stock bajo
        $insumosStockBajo = 0;
        if ($user->can('ver-alertas-inventario')) {
            $queryInventario = DB::table('inventario_sucursal')
                ->where('stock_actual', '<=', DB::raw('stock_minimo'));
            
            // Aplicar filtro de sucursal
            if ($this->sucursalId) {
                $queryInventario->where('sucursal_id', $this->sucursalId);
            }
            
            $insumosStockBajo = $queryInventario->count();
        }

        // Total de sucursales y veterinarias
        $totalSucursales = 0;
        $totalVeterinarias = 0;
        $totalUsuarios = 0;
        if ($user->can('ver-estadisticas-completas')) {
            $totalSucursales = Sucursal::count();
            $totalVeterinarias = Veterinaria::count();
            $totalUsuarios = User::count();
        }

        return view('livewire.dashboard.estadisticas-principales', [
            'analisisPendientes' => $analisisPendientes,
            'analisisEnProceso' => $analisisEnProceso,
            'analisisFinalizados' => $analisisFinalizados,
            'analisisAprobados' => $analisisAprobados,
            'muestrasHoy' => $muestrasHoy,
            'insumosStockBajo' => $insumosStockBajo,
            'totalSucursales' => $totalSucursales,
            'totalVeterinarias' => $totalVeterinarias,
            'totalUsuarios' => $totalUsuarios,
        ]);
    }
}
