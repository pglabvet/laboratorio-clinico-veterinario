<?php

namespace App\Livewire\Inventario;

use App\Models\MovimientoInventario;
use App\Models\Sucursal;
use App\Models\Insumo;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class HistorialMovimientos extends Component
{
    use WithPagination;

    // Filtros
    public $filtroSucursal = '';
    public $filtroInsumo = '';
    public $filtroTipoMovimiento = '';
    public $filtroMotivo = '';
    public $filtroFechaDesde = '';
    public $filtroFechaHasta = '';
    public $buscar = '';

    // Ordenamiento
    public $sortBy = 'fecha';
    public $sortDirection = 'desc';

    // Opciones para filtros
    public $tiposMovimiento = [
        'ENTRADA' => 'Entrada',
        'SALIDA_MANUAL' => 'Salida Manual',
        'CONSUMO_ANALISIS' => 'Consumo Análisis',
        'AJUSTE' => 'Ajuste',
    ];

    public $motivos = [
        'MERMA' => 'Merma',
        'VENCIMIENTO' => 'Vencimiento',
        'USO_EXTRAORDINARIO' => 'Uso Extraordinario',
        'CONSUMO_ANALISIS' => 'Consumo Análisis',
        'AJUSTE_INVENTARIO' => 'Ajuste Inventario',
        'COMPRA' => 'Compra',
        'DONACION' => 'Donación',
        'OTRO' => 'Otro',
    ];

    /**
     * Resetear paginación cuando cambian los filtros
     */
    public function updatingFiltroSucursal()
    {
        $this->resetPage();
    }

    public function updatingFiltroInsumo()
    {
        $this->resetPage();
    }

    public function updatingFiltroTipoMovimiento()
    {
        $this->resetPage();
    }

    public function updatingFiltroMotivo()
    {
        $this->resetPage();
    }

    public function updatingBuscar()
    {
        $this->resetPage();
    }

    /**
     * Cambiar ordenamiento
     */
    public function ordenarPor($campo)
    {
        if ($this->sortBy === $campo) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $campo;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Limpiar todos los filtros
     */
    public function limpiarFiltros()
    {
        $this->reset([
            'filtroSucursal',
            'filtroInsumo',
            'filtroTipoMovimiento',
            'filtroMotivo',
            'filtroFechaDesde',
            'filtroFechaHasta',
            'buscar',
        ]);
        $this->resetPage();
    }

    /**
     * Exportar a CSV (placeholder)
     */
    public function exportarCSV()
    {
        // Implementar exportación si es necesario
        session()->flash('mensaje', 'Función de exportación en desarrollo');
    }

    /**
     * Render del componente
     */
    public function render()
    {
        // Query base
        $movimientosQuery = MovimientoInventario::with([
            'insumo.unidadMedida',
            'sucursal',
            'usuario'
        ]);

        // Aplicar filtros
        if ($this->filtroSucursal) {
            $movimientosQuery->where('sucursal_id', $this->filtroSucursal);
        }

        if ($this->filtroInsumo) {
            $movimientosQuery->where('insumo_id', $this->filtroInsumo);
        }

        if ($this->filtroTipoMovimiento) {
            $movimientosQuery->where('tipo_movimiento', $this->filtroTipoMovimiento);
        }

        if ($this->filtroMotivo) {
            $movimientosQuery->where('motivo', $this->filtroMotivo);
        }

        if ($this->filtroFechaDesde) {
            $movimientosQuery->whereDate('fecha', '>=', $this->filtroFechaDesde);
        }

        if ($this->filtroFechaHasta) {
            $movimientosQuery->whereDate('fecha', '<=', $this->filtroFechaHasta);
        }

        // Búsqueda por texto (en nombre de insumo)
        if ($this->buscar) {
            $movimientosQuery->whereHas('insumo', function ($query) {
                $query->where('nombre', 'ilike', '%' . $this->buscar . '%');
            });
        }

        // Ordenamiento
        $movimientosQuery->orderBy($this->sortBy, $this->sortDirection);

        // Paginación
        $movimientos = $movimientosQuery->paginate(20);

        // Estadísticas rápidas
        $estadisticas = [
            'total_movimientos' => MovimientoInventario::count(),
            'salidas_mes_actual' => MovimientoInventario::where('tipo_movimiento', 'SALIDA_MANUAL')
                ->whereMonth('fecha', now()->month)
                ->whereYear('fecha', now()->year)
                ->count(),
            'entradas_mes_actual' => MovimientoInventario::where('tipo_movimiento', 'ENTRADA')
                ->whereMonth('fecha', now()->month)
                ->whereYear('fecha', now()->year)
                ->count(),
        ];

        return view('livewire.inventario.historial-movimientos', [
            'movimientos' => $movimientos,
            'estadisticas' => $estadisticas,
        ]);
    }

    #[Computed]
    public function sucursales()
    {
        return Sucursal::where('estado', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function insumos()
    {
        return Insumo::where('estado', true)->orderBy('nombre')->get();
    }
}
