<?php

namespace App\Livewire\Insumos;

use App\Models\Insumo;
use App\Models\UnidadMedida;
use App\Models\Sucursal;
use App\Models\InventarioSucursal;
use App\Models\CategoriaInsumo;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class GestionarInsumos extends Component
{
    use WithPagination;

    // Propiedades del formulario
    public $insumo_id;
    public $nombre;
    public $categoria_id;
    public $unidad_medida_id;
    public $estado = true;

    // Inventarios por sucursal
    public $inventarios = [];

    // Propiedades de control
    public $modalAbierto = false;
    public $modalCambiarEstado = false;
    public $modalEliminar = false;
    public $insumoACambiar = null;
    public $insumoAEliminar = null;
    public $estadoActual = null;
    public $buscar = '';
    public $modoEdicion = false;

    // Filtros
    public $filtroSucursal = '';
    public $mostrarSoloStockBajo = false;

    // Propiedades de ordenamiento
    public $sortBy = 'nombre';
    public $sortDirection = 'asc';

    // Reglas de validación
    protected function rules()
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'nullable|exists:categorias_insumo,id',
            'unidad_medida_id' => 'required|exists:unidades_medida,id',
            'estado' => 'boolean',
        ];

        // Validar stock mínimo solo si hay inventarios
        if (!empty($this->inventarios)) {
            $rules['inventarios.*.stock_minimo'] = 'required|numeric|min:0';
        }

        return $rules;
    }

    // Mensajes de validación personalizados
    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
        'categoria_id.exists' => 'La categoría seleccionada no es válida.',
        'unidad_medida_id.required' => 'La unidad de medida es obligatoria.',
        'unidad_medida_id.exists' => 'La unidad de medida seleccionada no es válida.',
        'inventarios.*.stock_minimo.required' => 'El stock mínimo es obligatorio para cada sucursal.',
        'inventarios.*.stock_minimo.numeric' => 'El stock mínimo debe ser un número.',
        'inventarios.*.stock_minimo.min' => 'El stock mínimo debe ser mayor o igual a 0.',
    ];

    /**
     * Montar componente
     */
    public function mount()
    {
        $this->inicializarInventarios();
    }

    /**
     * Inicializar inventarios para todas las sucursales activas
     */
    private function inicializarInventarios()
    {
        $sucursales = Sucursal::where('estado', true)->get();
        
        foreach ($sucursales as $sucursal) {
            $this->inventarios[$sucursal->id] = [
                'sucursal_id' => $sucursal->id,
                'sucursal_nombre' => $sucursal->nombre,
                'stock_minimo' => 0,
            ];
        }
    }

    /**
     * Abrir modal para crear nuevo insumo
     */
    public function crear()
    {
        $this->resetearFormulario();
        $this->inicializarInventarios();
        $this->modoEdicion = false;
        $this->modalAbierto = true;
    }

    /**
     * Abrir modal para editar insumo existente
     */
    public function editar($id)
    {
        $insumo = Insumo::with('inventarios')->findOrFail($id);
        
        $this->insumo_id = $insumo->id;
        $this->nombre = $insumo->nombre;
        $this->categoria_id = $insumo->categoria_id;
        $this->unidad_medida_id = $insumo->unidad_medida_id;
        $this->estado = $insumo->estado;
        
        // Cargar inventarios existentes
        $this->inicializarInventarios();
        foreach ($insumo->inventarios as $inventario) {
            $this->inventarios[$inventario->sucursal_id] = [
                'sucursal_id' => $inventario->sucursal_id,
                'sucursal_nombre' => $inventario->sucursal->nombre,
                'stock_minimo' => $inventario->stock_minimo,
            ];
        }
        
        $this->modoEdicion = true;
        $this->modalAbierto = true;
    }

    /**
     * Guardar insumo (crear o actualizar)
     */
    public function guardar()
    {
        // Verificar que hay sucursales antes de guardar
        if (empty($this->inventarios) && !$this->modoEdicion) {
            session()->flash('error', 'No hay sucursales activas. Por favor, crea al menos una sucursal antes de crear insumos.');
            return;
        }

        $this->validate();

        // Verificar si ya existe un insumo con el mismo nombre
        $existe = Insumo::where('nombre', $this->nombre)
            ->when($this->insumo_id, fn($q) => $q->where('id', '!=', $this->insumo_id))
            ->exists();

        if ($existe) {
            session()->flash('error', 'Ya existe un insumo con el nombre "' . $this->nombre . '". Por favor, usa un nombre diferente.');
            return;
        }

        DB::beginTransaction();
        try {
            if ($this->modoEdicion) {
                $insumo = Insumo::findOrFail($this->insumo_id);
                $insumo->update([
                    'nombre' => $this->nombre,
                    'categoria_id' => $this->categoria_id,
                    'unidad_medida_id' => $this->unidad_medida_id,
                    'estado' => $this->estado,
                ]);

                // Actualizar inventarios
                foreach ($this->inventarios as $inventario) {
                    InventarioSucursal::updateOrCreate(
                        [
                            'insumo_id' => $insumo->id,
                            'sucursal_id' => $inventario['sucursal_id'],
                        ],
                        [
                            'stock_minimo' => $inventario['stock_minimo'],
                        ]
                    );
                }

                session()->flash('mensaje', 'Insumo actualizado exitosamente.');
            } else {
                $insumo = Insumo::create([
                    'nombre' => $this->nombre,
                    'categoria_id' => $this->categoria_id,
                    'unidad_medida_id' => $this->unidad_medida_id,
                    'estado' => $this->estado,
                ]);

                // Crear inventarios para cada sucursal
                foreach ($this->inventarios as $inventario) {
                    InventarioSucursal::create([
                        'insumo_id' => $insumo->id,
                        'sucursal_id' => $inventario['sucursal_id'],
                        'stock_actual' => 0,
                        'stock_minimo' => $inventario['stock_minimo'],
                    ]);
                }

                session()->flash('mensaje', 'Insumo creado exitosamente.');
            }

            DB::commit();
            $this->cerrarModal();
            $this->resetearFormulario();
            $this->dispatch('insumo-guardado'); // Evento para refrescar componente
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error: ' . $e->getMessage());
            \Log::error('Error al guardar insumo: ' . $e->getMessage());
        }
    }

    /**
     * Confirmar cambio de estado
     */
    public function confirmarCambiarEstado($id)
    {
        $this->insumoACambiar = Insumo::findOrFail($id);
        $this->estadoActual = $this->insumoACambiar->estado;
        $this->modalCambiarEstado = true;
    }

    /**
     * Cambiar estado del insumo
     */
    public function cambiarEstado()
    {
        try {
            if ($this->insumoACambiar) {
                $this->insumoACambiar->estado = !$this->estadoActual;
                $this->insumoACambiar->save();

                $estado = $this->insumoACambiar->estado ? 'activado' : 'desactivado';
                session()->flash('mensaje', "Insumo {$estado} exitosamente.");
            }

            $this->cerrarModalCambiarEstado();
        } catch (\Exception $e) {
            session()->flash('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    /**
     * Cerrar modal de formulario
     */
    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->resetearFormulario();
    }

    /**
     * Cerrar modal de cambiar estado
     */
    public function cerrarModalCambiarEstado()
    {
        $this->modalCambiarEstado = false;
        $this->insumoACambiar = null;
        $this->estadoActual = null;
    }

    /**
     * Confirmar eliminación del insumo
     */
    public function confirmarEliminar($id)
    {
        $this->insumoAEliminar = Insumo::findOrFail($id);
        $this->modalEliminar = true;
    }

    /**
     * Eliminar insumo
     */
    public function eliminar()
    {
        try {
            if ($this->insumoAEliminar) {
                $this->insumoAEliminar->delete();
                session()->flash('mensaje', 'Insumo eliminado exitosamente.');
            }

            $this->cerrarModalEliminar();
        } catch (\Exception $e) {
            session()->flash('error', 'No se puede eliminar el insumo: ' . $e->getMessage());
        }
    }

    /**
     * Cerrar modal de eliminar
     */
    public function cerrarModalEliminar()
    {
        $this->modalEliminar = false;
        $this->insumoAEliminar = null;
    }

    /**
     * Resetear formulario
     */
    private function resetearFormulario()
    {
        $this->insumo_id = null;
        $this->nombre = '';
        $this->categoria_id = null;
        $this->unidad_medida_id = null;
        $this->estado = true;
        $this->inicializarInventarios();
        $this->resetValidation();
    }

    /**
     * Ordenar por columna
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
     * Resetear paginación al buscar o filtrar
     */
    public function updatingBuscar()
    {
        $this->resetPage();
    }

    public function updatingFiltroSucursal()
    {
        $this->resetPage();
    }

    public function updatingMostrarSoloStockBajo()
    {
        $this->resetPage();
    }

    /**
     * Limpiar todos los filtros
     */
    public function limpiarFiltros()
    {
        $this->buscar = '';
        $this->filtroSucursal = '';
        $this->mostrarSoloStockBajo = false;
        $this->resetPage();
    }

    #[Computed]
    public function unidadesMedida()
    {
        return UnidadMedida::where('estado', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function categorias()
    {
        return CategoriaInsumo::where('estado', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function sucursales()
    {
        return Sucursal::where('estado', true)->orderBy('nombre')->get();
    }

    /**
     * Renderizar componente
     */
    public function render()
    {
        $query = Insumo::with(['categoria', 'unidadMedida', 'inventarios.sucursal']);

        // Búsqueda por nombre
        if ($this->buscar) {
            $query->where('nombre', 'ilike', '%' . $this->buscar . '%');
        }

        // Filtro por sucursal
        if ($this->filtroSucursal) {
            $query->whereHas('inventarios', function ($q) {
                $q->where('sucursal_id', $this->filtroSucursal);
            });
        }

        // Filtro por stock bajo
        if ($this->mostrarSoloStockBajo && $this->filtroSucursal) {
            $query->whereHas('inventarios', function ($q) {
                $q->where('sucursal_id', $this->filtroSucursal)
                  ->whereColumn('stock_actual', '<', 'stock_minimo');
            });
        }

        $insumos = $query->orderBy($this->sortBy, $this->sortDirection)->paginate(10);

        return view('livewire.insumos.gestionar-insumos', [
            'insumos' => $insumos,
        ]);
    }
}
