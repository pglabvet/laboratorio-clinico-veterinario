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
    public $costo_unitario = '';
    public $codigo_lote = '';
    public $fecha_vencimiento = '';
    public $motivo = '';
    public $observacion = '';

    // Datos relacionados
    public $insumoSeleccionado = null;

    // Filtros para entradas recientes
    public $filtroSucursalEntradas = '';
    public $fechaDesdeEntradas = '';
    public $fechaHastaEntradas = '';
    public $busquedaEntradas = '';

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
            'costo_unitario' => 'required|numeric|min:0.01',
            'codigo_lote' => 'nullable|string|max:50',
            'fecha_vencimiento' => 'nullable|date|after:today',
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
        'costo_unitario.required' => 'El costo unitario es obligatorio.',
        'costo_unitario.numeric' => 'El costo unitario debe ser un número.',
        'costo_unitario.min' => 'El costo unitario debe ser mayor a 0.',
        'codigo_lote.max' => 'El código de lote no puede exceder 50 caracteres.',
        'fecha_vencimiento.date' => 'La fecha de vencimiento no es válida.',
        'fecha_vencimiento.after' => 'La fecha de vencimiento debe ser posterior a hoy.',
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
            // Usar el servicio PEPS para registrar la entrada
            $service = app(\App\Services\PepsInventarioService::class);

            $service->registrarEntrada(
                insumoId: (int) $this->insumo_id,
                sucursalId: (int) $this->sucursal_id,
                cantidad: (float) $this->cantidad,
                costoUnitario: (float) $this->costo_unitario,
                motivo: $this->motivo,
                observacion: $this->observacion,
                usuarioId: Auth::id(),
                codigoLote: $this->codigo_lote ?: null,
                fechaVencimiento: $this->fecha_vencimiento ?: null
            );

            // Mensaje de éxito
            $insumo = Insumo::find($this->insumo_id);
            $sucursal = Sucursal::find($this->sucursal_id);

            session()->flash('mensaje', "Entrada registrada exitosamente: {$this->cantidad} {$insumo->unidadMedida->abreviatura} de {$insumo->nombre} en {$sucursal->nombre}.");

            // Limpiar formulario
            $this->limpiarFormulario();

        } catch (\Exception $e) {
            session()->flash('error', 'Error al registrar la entrada: ' . $e->getMessage());
        }
    }

    /**
     * Limpiar formulario de entrada
     */
    public function limpiarFormulario()
    {
        $this->reset(['sucursal_id', 'filtro_categoria', 'insumo_id', 'cantidad', 'costo_unitario', 'codigo_lote', 'fecha_vencimiento', 'motivo', 'observacion', 'insumoSeleccionado']);
        $this->resetValidation();
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

    public function updatingFechaDesdeEntradas()
    {
        $this->resetPage();
    }

    public function updatingFechaHastaEntradas()
    {
        $this->resetPage();
    }

    public function updatingBusquedaEntradas()
    {
        $this->resetPage();
    }

    public function limpiarFiltrosHistorial()
    {
        $this->reset(['busquedaEntradas', 'filtroSucursalEntradas', 'fechaDesdeEntradas', 'fechaHastaEntradas']);
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
            $insumosQuery->where('categoria_id', $this->filtro_categoria);
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

        // Aplicar filtro de fecha desde
        if ($this->fechaDesdeEntradas) {
            $entradasQuery->whereDate('fecha', '>=', $this->fechaDesdeEntradas);
        }

        // Aplicar filtro de fecha hasta
        if ($this->fechaHastaEntradas) {
            $entradasQuery->whereDate('fecha', '<=', $this->fechaHastaEntradas);
        }

        // Aplicar filtro de búsqueda
        if ($this->busquedaEntradas) {
            $busqueda = $this->busquedaEntradas;
            $entradasQuery->where(function ($q) use ($busqueda) {
                $q->whereHas('insumo', function ($qi) use ($busqueda) {
                    $qi->where('nombre', 'ilike', "%{$busqueda}%");
                })
                ->orWhere('motivo', 'ilike', "%{$busqueda}%")
                ->orWhere('observacion', 'ilike', "%{$busqueda}%");
            });
        }

        $entradasRecientes = $entradasQuery->paginate(10);

        // Obtener deudas pendientes para la sucursal activa
        $deudasPendientes = collect();
        if ($this->sucursal_id) {
            $deudasPendientes = \App\Models\ConsumoPendiente::with(['insumo.unidadMedida', 'usuario'])
                ->where('sucursal_id', $this->sucursal_id)
                ->where('estado', \App\Models\ConsumoPendiente::ESTADO_PENDIENTE)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('livewire.insumos.registrar-entrada-insumos', [
            'insumos' => $insumos,
            'entradasRecientes' => $entradasRecientes,
            'motivosDisponibles' => self::MOTIVOS,
            'deudasPendientes' => $deudasPendientes,
        ]);
    }
}