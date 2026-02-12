<?php

namespace App\Livewire\Insumos;

use App\Models\Insumo;
use App\Models\Sucursal;
use App\Models\InventarioSucursal;
use App\Models\MovimientoInventario;
use App\Models\CategoriaInsumo;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RegistrarEntradaInsumos extends Component
{
    use WithPagination;

    // Propiedades del formulario
    public $sucursal_id = '';
    public $filtro_categoria = '';
    public $insumo_id = '';
    public $cantidad = '';
    public $motivo = '';
    public $observacion = '';

    // Datos relacionados
    public $insumoSeleccionado = null;

    // Filtros para entradas recientes
    public $filtroSucursalEntradas = '';
    public $filtroFechaEntradas = '';

    // Opciones de motivo
    public const MOTIVOS = [
        'COMPRA' => 'Compra',
        'DEVOLUCION' => 'Devolución',
        'AJUSTE_INVENTARIO' => 'Ajuste de Inventario',
        'OTRO' => 'Otro',
    ];

    // Reglas de validación
    protected function rules()
    {
        return [
            'sucursal_id' => 'required|exists:sucursales,id',
            'insumo_id' => 'required|exists:insumos,id',
            'cantidad' => 'required|numeric|min:0.01',
            'motivo' => 'required|in:COMPRA,DEVOLUCION,AJUSTE_INVENTARIO,OTRO',
            'observacion' => 'nullable|string|max:1000',
        ];
    }

    // Mensajes de validación personalizados
    protected $messages = [
        'sucursal_id.required' => 'Debe seleccionar una sucursal.',
        'sucursal_id.exists' => 'La sucursal seleccionada no es válida.',
        'insumo_id.required' => 'Debe seleccionar un insumo.',
        'insumo_id.exists' => 'El insumo seleccionado no es válido.',
        'cantidad.required' => 'La cantidad es obligatoria.',
        'cantidad.numeric' => 'La cantidad debe ser un número.',
        'cantidad.min' => 'La cantidad debe ser mayor a 0.',
        'motivo.required' => 'Debe seleccionar un motivo.',
        'motivo.in' => 'El motivo seleccionado no es válido.',
        'observacion.max' => 'La observación no puede exceder 1000 caracteres.',
    ];

    /**
     * Resetear insumo cuando cambia la categoría
     */
    public function updatedFiltroCategoria()
    {
        $this->reset(['insumo_id', 'insumoSeleccionado']);
    }

    /**
     * Actualizar información del insumo seleccionado
     */
    public function updatedInsumoId($value)
    {
        if ($value) {
            $this->insumoSeleccionado = Insumo::with('unidadMedida', 'categoria')
                ->find($value);
        } else {
            $this->insumoSeleccionado = null;
        }
    }

    /**
     * Registrar entrada de insumo
     */
    public function registrarEntrada()
    {
        // Validar datos
        $this->validate();

        try {
            DB::beginTransaction();

            // Buscar o crear registro en inventario_sucursal
            $inventario = InventarioSucursal::firstOrCreate(
                [
                    'insumo_id' => $this->insumo_id,
                    'sucursal_id' => $this->sucursal_id,
                ],
                [
                    'stock_actual' => 0,
                    'stock_minimo' => 0,
                ]
            );

            // Actualizar stock actual (sumar la cantidad)
            $inventario->stock_actual += $this->cantidad;
            $inventario->save();

            // Registrar movimiento en el historial
            MovimientoInventario::create([
                'insumo_id' => $this->insumo_id,
                'sucursal_id' => $this->sucursal_id,
                'tipo_movimiento' => 'ENTRADA',
                'cantidad' => $this->cantidad,
                'motivo' => $this->motivo,
                'observacion' => $this->observacion,
                'usuario_id' => Auth::id(),
                'fecha' => now(),
            ]);

            DB::commit();

            // Mensaje de éxito
            $insumo = Insumo::find($this->insumo_id);
            $sucursal = Sucursal::find($this->sucursal_id);
            
            session()->flash('mensaje', "Entrada registrada exitosamente: {$this->cantidad} {$insumo->unidadMedida->abreviatura} de {$insumo->nombre} en {$sucursal->nombre}.");

            // Limpiar formulario
            $this->reset(['sucursal_id', 'filtro_categoria', 'insumo_id', 'cantidad', 'motivo', 'observacion', 'insumoSeleccionado']);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al registrar la entrada: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar y volver al historial
     */
    public function cancelar()
    {
        return $this->redirect(route('inventario.historial'), navigate: true);
    }

    /**
     * Resetear paginación al cambiar filtros
     */
    public function updatingFiltroSucursalEntradas()
    {
        $this->resetPage();
    }

    public function updatingFiltroFechaEntradas()
    {
        $this->resetPage();
    }

    #[Computed]
    public function sucursales()
    {
        return Sucursal::where('estado', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function categorias()
    {
        return CategoriaInsumo::where('estado', true)->orderBy('nombre')->get();
    }

    /**
     * Renderizar vista
     */
    public function render()
    {
        // Obtener insumos activos (filtrados por categoría si está seleccionada)
        $insumosQuery = Insumo::with('unidadMedida', 'categoria')
            ->where('estado', true);
        
        if ($this->filtro_categoria) {
            $insumosQuery->where('categoria_insumo_id', $this->filtro_categoria);
        }
        
        $insumos = $insumosQuery->orderBy('nombre')->get();

        // Obtener entradas recientes
        $entradasQuery = MovimientoInventario::with(['insumo.unidadMedida', 'sucursal', 'usuario'])
            ->where('tipo_movimiento', 'ENTRADA')
            ->orderBy('fecha', 'desc');

        // Aplicar filtro de sucursal
        if ($this->filtroSucursalEntradas) {
            $entradasQuery->where('sucursal_id', $this->filtroSucursalEntradas);
        }

        // Aplicar filtro de período
        if ($this->filtroFechaEntradas) {
            $now = now();
            
            switch ($this->filtroFechaEntradas) {
                case 'hoy':
                    $entradasQuery->whereDate('fecha', $now->toDateString());
                    break;
                case 'ayer':
                    $entradasQuery->whereDate('fecha', $now->subDay()->toDateString());
                    break;
                case 'ultimos_7_dias':
                    $entradasQuery->where('fecha', '>=', $now->subDays(7)->startOfDay());
                    break;
                case 'esta_semana':
                    $entradasQuery->whereBetween('fecha', [
                        $now->startOfWeek()->startOfDay(),
                        $now->endOfWeek()->endOfDay()
                    ]);
                    break;
                case 'este_mes':
                    $entradasQuery->whereMonth('fecha', $now->month)
                                  ->whereYear('fecha', $now->year);
                    break;
                case 'este_año':
                    $entradasQuery->whereYear('fecha', $now->year);
                    break;
            }
        }

        $entradasRecientes = $entradasQuery->paginate(10);

        return view('livewire.insumos.registrar-entrada-insumos', [
            'insumos' => $insumos,
            'entradasRecientes' => $entradasRecientes,
            'motivosDisponibles' => self::MOTIVOS,
        ]);
    }
}
