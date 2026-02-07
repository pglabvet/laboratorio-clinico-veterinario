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
        
        // Si no es admin, filtrar por sucursal del usuario automáticamente
        if (!$user->can('ver-estadisticas-completas') && $user->sucursal_id) {
            $baseQuery->whereHas('muestra', function($q) use ($user) {
                $q->where('sucursal_id', $user->sucursal_id);
            });
        }
        
        // Aplicar filtros de fecha
        if ($this->fechaInicio && $this->fechaFin) {
            $baseQuery->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin]);
        }
        
        // Aplicar filtro de sucursal (para admin)
        if ($this->sucursalId && $user->can('filtrar-por-sucursal')) {
            $baseQuery->whereHas('muestra', function($q) {
                $q->where('sucursal_id', $this->sucursalId);
            });
        }
        
        // Muestras Pendientes (estado pendiente)
        $muestrasPendientesQuery = \App\Models\Muestra::query()
            ->where('estado', 'Pendiente');
        
        // Si no es admin, filtrar por sucursal del usuario automáticamente
        if (!$user->can('ver-estadisticas-completas') && $user->sucursal_id) {
            $muestrasPendientesQuery->where('sucursal_id', $user->sucursal_id);
        }
        
        // Si es admin y tiene filtro de sucursal seleccionado
        if ($this->sucursalId && $user->can('filtrar-por-sucursal')) {
            $muestrasPendientesQuery->where('sucursal_id', $this->sucursalId);
        }
        
        // Aplicar filtros de fecha
        if ($this->fechaInicio && $this->fechaFin) {
            $muestrasPendientesQuery->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin]);
        }
        
        $muestrasPendientes = $muestrasPendientesQuery->count();
        
        // Estadísticas desglosadas por estado
        $analisisPendientes = (clone $baseQuery)->where('estado', 'pendiente')->count();
        
        // Muestras recibidas hoy
        $queryMuestras = Muestra::query();
        
        if ($this->fechaInicio && $this->fechaFin) {
            $queryMuestras->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin]);
        } else {
            $queryMuestras->whereDate('created_at', Carbon::today());
        }
        
        // Si no es admin, filtrar por sucursal del usuario automáticamente
        if (!$user->can('ver-estadisticas-completas') && $user->sucursal_id) {
            $queryMuestras->where('sucursal_id', $user->sucursal_id);
        }
        
        // Si es admin y tiene filtro de sucursal seleccionado
        if ($this->sucursalId && $user->can('filtrar-por-sucursal')) {
            $queryMuestras->where('sucursal_id', $this->sucursalId);
        }
        
        $muestrasHoy = $queryMuestras->count();
        
        // Insumos con stock bajo
        $insumosStockBajo = 0;
        if ($user->can('ver-alertas-inventario')) {
            $queryInventario = DB::table('inventario_sucursal')
                ->where('stock_actual', '<=', DB::raw('stock_minimo'));
            
            // Si no es admin, filtrar por sucursal del usuario automáticamente
            if (!$user->can('ver-estadisticas-completas') && $user->sucursal_id) {
                $queryInventario->where('sucursal_id', $user->sucursal_id);
            }
            
            // Si es admin y tiene filtro de sucursal seleccionado
            if ($this->sucursalId && $user->can('filtrar-por-sucursal')) {
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
            'muestrasPendientes' => $muestrasPendientes,
            'analisisPendientes' => $analisisPendientes,
            'muestrasHoy' => $muestrasHoy,
            'insumosStockBajo' => $insumosStockBajo,
            'totalSucursales' => $totalSucursales,
            'totalVeterinarias' => $totalVeterinarias,
            'totalUsuarios' => $totalUsuarios,
        ]);
    }
}
