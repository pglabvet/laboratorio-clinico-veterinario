<?php

namespace App\Livewire\Inventario;

use App\Models\Insumo;
use App\Models\Sucursal;
use App\Models\InventarioSucursal;
use App\Models\MovimientoInventario;
use App\Models\CategoriaInsumo;
use App\Services\PepsInventarioService;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RegistrarSalida extends Component
{
    use WithPagination;

    // Propiedades del formulario
    public $sucursal_id = '';
    public $filtro_categoria = '';
    public $insumo_id = '';
    public $cantidad = '';
    public $motivo = '';
    public $observacion = '';

    // Datos de contexto
    public $stockActual = null;
    public $unidadMedida = '';
    public $insumoNombre = '';
    public $sucursalNombre = '';

    // PEPS cost preview
    public $costoEstimado = null;
    public $detalleLotes = [];

    // Control del modal de confirmacion
    public $modalConfirmacion = false;

    // Busqueda y filtros
    public $buscar = '';
    public $filtroSucursal = '';
    public $fechaDesdeSalidas = '';
    public $fechaHastaSalidas = '';
    public $busquedaSalidas = '';

    // Opciones de motivo
    public $motivosDisponibles = [
        'MERMA' => 'Merma',
        'VENCIMIENTO' => 'Vencimiento',
        'USO_EXTRAORDINARIO' => 'Uso Extraordinario',
        'OTRO' => 'Otro',
    ];

    // Reglas de validacion
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
        'cantidad.numeric' => 'La cantidad debe ser un numero.',
        'cantidad.gt' => 'La cantidad debe ser mayor a 0.',
        'motivo.required' => 'Debe seleccionar un motivo.',
        'observacion.required' => 'La observacion es obligatoria.',
        'observacion.min' => 'La observacion debe tener al menos 10 caracteres.',
        'observacion.max' => 'La observacion no puede exceder 1000 caracteres.',
    ];

    public function updatedFiltroCategoria()
    {
        $this->reset(['insumo_id', 'stockActual', 'unidadMedida', 'insumoNombre']);
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['insumo_id', 'sucursal_id'])) {
            $this->cargarStockActual();
            $this->costoEstimado = null;
            $this->detalleLotes = [];
        }

        if ($propertyName === 'cantidad') {
            $this->validateOnly('cantidad');
            $this->calcularCostoPeps();
        }
    }

    public function calcularCostoPeps()
    {
        $this->costoEstimado = null;
        $this->detalleLotes = [];

        if (!$this->insumo_id || !$this->sucursal_id || !$this->cantidad || $this->cantidad <= 0) {
            return;
        }

        try {
            $service = app(PepsInventarioService::class);
            $resultado = $service->calcularCostoPeps(
                (int) $this->insumo_id,
                (int) $this->sucursal_id,
                (float) $this->cantidad
            );

            if ($resultado['stock_suficiente']) {
                $this->costoEstimado = $resultado;
                $this->detalleLotes = $resultado['detalle_lotes'];
            }
        } catch (\Exception $e) {
            // Silently fail for preview
        }
    }

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
                if (!$inventario->insumo->estado) {
                    $this->addError('insumo_id', 'El insumo seleccionado esta inactivo.');
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

    public function abrirConfirmacion()
    {
        $this->validate();
        $this->modalConfirmacion = true;
    }

    public function cerrarConfirmacion()
    {
        $this->modalConfirmacion = false;
    }

    public function registrarSalida()
    {
        $this->validate();

        try {
            $service = app(PepsInventarioService::class);

            $movimiento = $service->registrarSalida(
                insumoId: (int) $this->insumo_id,
                sucursalId: (int) $this->sucursal_id,
                cantidad: (float) $this->cantidad,
                motivo: $this->motivo,
                observacion: $this->observacion,
                usuarioId: Auth::id(),
            );

            $inventario = InventarioSucursal::where('insumo_id', $this->insumo_id)
                ->where('sucursal_id', $this->sucursal_id)
                ->first();

            $costoInfo = $movimiento->costo_total > 0
                ? " Costo PEPS: Bs " . number_format($movimiento->costo_total, 2)
                : '';

            $mensaje = 'Salida registrada exitosamente.' . $costoInfo;
            $tipo = 'success';

            if ($inventario && $inventario->stock_actual < $inventario->stock_minimo) {
                $mensaje .= ' ALERTA: Stock por debajo del minimo (' . $inventario->stock_minimo . ' ' . $this->unidadMedida . ').';
                $tipo = 'warning';
            }

            session()->flash('mensaje', $mensaje);
            session()->flash('tipo', $tipo);

            $this->resetearFormulario();
            $this->cerrarConfirmacion();

        } catch (\Exception $e) {
            session()->flash('error', 'Error al registrar la salida: ' . $e->getMessage());
        }
    }

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
            'costoEstimado',
            'detalleLotes',
        ]);
        $this->resetValidation();
    }

    public function limpiarFiltrosHistorial()
    {
        $this->reset(['busquedaSalidas', 'filtroSucursal', 'fechaDesdeSalidas', 'fechaHastaSalidas']);
        $this->resetPage();
    }

    public function cancelar()
    {
        return $this->redirect(route('inventario.historial'), navigate: true);
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

    public function render()
    {
        $insumosQuery = Insumo::with(['unidadMedida', 'inventarios.sucursal'])
            ->where('estado', true);

        if ($this->buscar) {
            $insumosQuery->where('nombre', 'ilike', '%' . $this->buscar . '%');
        }

        if ($this->filtro_categoria) {
            $insumosQuery->where('categoria_id', $this->filtro_categoria);
        }

        $insumos = $insumosQuery->orderBy('nombre')->get();

        $movimientosQuery = MovimientoInventario::with(['insumo.unidadMedida', 'sucursal', 'usuario'])
            ->where('tipo_movimiento', 'SALIDA_MANUAL')
            ->orderBy('fecha', 'desc');

        if ($this->filtroSucursal) {
            $movimientosQuery->where('sucursal_id', $this->filtroSucursal);
        }

        // Aplicar filtro de fecha desde
        if ($this->fechaDesdeSalidas) {
            $movimientosQuery->whereDate('fecha', '>=', $this->fechaDesdeSalidas);
        }

        // Aplicar filtro de fecha hasta
        if ($this->fechaHastaSalidas) {
            $movimientosQuery->whereDate('fecha', '<=', $this->fechaHastaSalidas);
        }

        // Aplicar filtro de búsqueda
        if ($this->busquedaSalidas) {
            $busqueda = $this->busquedaSalidas;
            $movimientosQuery->where(function ($q) use ($busqueda) {
                $q->whereHas('insumo', function ($qi) use ($busqueda) {
                    $qi->where('nombre', 'like', "%{$busqueda}%");
                })
                ->orWhere('motivo', 'like', "%{$busqueda}%")
                ->orWhere('observacion', 'like', "%{$busqueda}%");
            });
        }

        $movimientos = $movimientosQuery->paginate(10);

        return view('livewire.inventario.registrar-salida', [
            'insumos' => $insumos,
            'movimientos' => $movimientos,
        ]);
    }
}