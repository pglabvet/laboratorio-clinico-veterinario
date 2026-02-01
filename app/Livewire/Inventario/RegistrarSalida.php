<?php

namespace App\Livewire\Inventario;

use App\Models\Insumo;
use App\Models\Sucursal;
use App\Models\InventarioSucursal;
use App\Models\MovimientoInventario;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class RegistrarSalida extends Component
{
    use WithPagination;

    // Propiedades del formulario
    public $sucursal_id = '';
    public $insumo_id = '';
    public $cantidad = '';
    public $motivo = '';
    public $observacion = '';

    // Datos de contexto
    public $stockActual = null;
    public $unidadMedida = '';
    public $insumoNombre = '';
    public $sucursalNombre = '';

    // Control del modal de confirmación
    public $modalConfirmacion = false;

    // Búsqueda y filtros
    public $buscar = '';
    public $filtroSucursal = '';

    // Opciones de motivo
    public $motivosDisponibles = [
        'MERMA' => 'Merma',
        'VENCIMIENTO' => 'Vencimiento',
        'USO_EXTRAORDINARIO' => 'Uso Extraordinario',
        'OTRO' => 'Otro',
    ];

    // Reglas de validación
    protected function rules()
    {
        return [
            'sucursal_id' => 'required|exists:sucursales,id',
            'insumo_id' => 'required|exists:insumos,id',
            'cantidad' => [
                'required',
                'numeric',
                'gt:0',
                function ($attribute, $value, $fail) {
                    if ($this->stockActual !== null && $value > $this->stockActual) {
                        $fail('La cantidad no puede ser mayor al stock disponible (' . $this->stockActual . ' ' . $this->unidadMedida . ').');
                    }
                },
            ],
            'motivo' => 'required|in:MERMA,VENCIMIENTO,USO_EXTRAORDINARIO,OTRO',
            'observacion' => 'required|string|min:10|max:1000',
        ];
    }

    protected $messages = [
        'sucursal_id.required' => 'Debe seleccionar una sucursal.',
        'insumo_id.required' => 'Debe seleccionar un insumo.',
        'cantidad.required' => 'La cantidad es obligatoria.',
        'cantidad.numeric' => 'La cantidad debe ser un número.',
        'cantidad.gt' => 'La cantidad debe ser mayor a 0.',
        'motivo.required' => 'Debe seleccionar un motivo.',
        'observacion.required' => 'La observación es obligatoria.',
        'observacion.min' => 'La observación debe tener al menos 10 caracteres.',
        'observacion.max' => 'La observación no puede exceder 1000 caracteres.',
    ];

    /**
     * Actualizar stock actual cuando cambia el insumo o sucursal
     */
    public function updated($propertyName)
    {
        if (in_array($propertyName, ['insumo_id', 'sucursal_id'])) {
            $this->cargarStockActual();
        }

        if ($propertyName === 'cantidad') {
            $this->validateOnly('cantidad');
        }
    }

    /**
     * Cargar stock actual del insumo en la sucursal seleccionada
     */
    public function cargarStockActual()
    {
        $this->stockActual = null;
        $this->unidadMedida = '';
        $this->insumoNombre = '';
        $this->sucursalNombre = '';

        if ($this->insumo_id && $this->sucursal_id) {
            $inventario = InventarioSucursal::where('insumo_id', $this->insumo_id)
                ->where('sucursal_id', $this->sucursal_id)
                ->with(['insumo.unidadMedida', 'sucursal'])
                ->first();

            if ($inventario) {
                // Verificar que el insumo esté activo
                if (!$inventario->insumo->estado) {
                    $this->addError('insumo_id', 'El insumo seleccionado está inactivo.');
                    return;
                }

                $this->stockActual = $inventario->stock_actual;
                $this->unidadMedida = $inventario->insumo->unidadMedida->abreviatura ?? '';
                $this->insumoNombre = $inventario->insumo->nombre;
                $this->sucursalNombre = $inventario->sucursal->nombre;
            } else {
                $this->addError('insumo_id', 'El insumo no existe en el inventario de esta sucursal.');
            }
        }
    }

    /**
     * Abrir modal de confirmación
     */
    public function abrirConfirmacion()
    {
        $this->validate();

        $this->modalConfirmacion = true;
    }

    /**
     * Cerrar modal de confirmación
     */
    public function cerrarConfirmacion()
    {
        $this->modalConfirmacion = false;
    }

    /**
     * Registrar la salida manual
     */
    public function registrarSalida()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // 1. Actualizar inventario por sucursal (descontar stock)
            $inventario = InventarioSucursal::where('insumo_id', $this->insumo_id)
                ->where('sucursal_id', $this->sucursal_id)
                ->lockForUpdate()
                ->first();

            if (!$inventario) {
                throw new \Exception('Inventario no encontrado.');
            }

            // Validación adicional de stock
            if ($inventario->stock_actual < $this->cantidad) {
                throw new \Exception('Stock insuficiente. Stock actual: ' . $inventario->stock_actual);
            }

            // Descontar stock
            $inventario->stock_actual -= $this->cantidad;
            $inventario->save();

            // 2. Registrar movimiento de inventario
            MovimientoInventario::create([
                'insumo_id' => $this->insumo_id,
                'sucursal_id' => $this->sucursal_id,
                'tipo_movimiento' => 'SALIDA_MANUAL',
                'cantidad' => -$this->cantidad, // Negativo para salidas
                'motivo' => $this->motivo,
                'observacion' => $this->observacion,
                'usuario_id' => auth()->id(),
                'fecha' => now(),
            ]);

            DB::commit();

            // Verificar si quedó por debajo del stock mínimo
            $mensaje = 'Salida registrada exitosamente.';
            if ($inventario->stock_actual < $inventario->stock_minimo) {
                $mensaje .= ' ⚠️ ALERTA: El stock quedó por debajo del mínimo (' . $inventario->stock_minimo . ' ' . $this->unidadMedida . ').';
            }

            session()->flash('mensaje', $mensaje);
            session()->flash('tipo', $inventario->stock_actual < $inventario->stock_minimo ? 'warning' : 'success');

            $this->resetearFormulario();
            $this->cerrarConfirmacion();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al registrar la salida: ' . $e->getMessage());
        }
    }

    /**
     * Resetear el formulario
     */
    public function resetearFormulario()
    {
        $this->reset([
            'sucursal_id',
            'insumo_id',
            'cantidad',
            'motivo',
            'observacion',
            'stockActual',
            'unidadMedida',
            'insumoNombre',
            'sucursalNombre',
        ]);
        $this->resetValidation();
    }

    /**
     * Render del componente
     */
    public function render()
    {
        // Obtener sucursales activas
        $sucursales = Sucursal::where('estado', true)
            ->orderBy('nombre')
            ->get();

        // Obtener insumos activos con su inventario
        $insumosQuery = Insumo::with(['unidadMedida', 'inventarios.sucursal'])
            ->where('estado', true);

        if ($this->buscar) {
            $insumosQuery->where('nombre', 'like', '%' . $this->buscar . '%');
        }

        $insumos = $insumosQuery->orderBy('nombre')->get();

        // Obtener historial reciente de movimientos
        $movimientosQuery = MovimientoInventario::with(['insumo.unidadMedida', 'sucursal', 'usuario'])
            ->where('tipo_movimiento', 'SALIDA_MANUAL')
            ->orderBy('fecha', 'desc');

        if ($this->filtroSucursal) {
            $movimientosQuery->where('sucursal_id', $this->filtroSucursal);
        }

        $movimientos = $movimientosQuery->paginate(10);

        return view('livewire.inventario.registrar-salida', [
            'sucursales' => $sucursales,
            'insumos' => $insumos,
            'movimientos' => $movimientos,
        ]);
    }
}
