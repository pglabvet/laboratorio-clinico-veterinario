<?php

namespace App\Livewire\Insumos;

use App\Models\CategoriaInsumo;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarCategoriasInsumo extends Component
{
    use WithPagination;

    // Propiedades del formulario
    public $categoria_id;
    public $nombre;
    public $descripcion;
    public $estado = true;

    // Propiedades de control
    public $modalAbierto = false;
    public $modalCambiarEstado = false;
    public $modalEliminar = false;
    public $categoriaACambiar = null;
    public $categoriaAEliminar = null;
    public $estadoActual = null;
    public $buscar = '';
    public $modoEdicion = false;

    // Propiedades de ordenamiento
    public $sortBy = 'nombre';
    public $sortDirection = 'asc';

    // Reglas de validación
    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:500',
            'estado' => 'boolean',
        ];
    }

    // Mensajes de validación personalizados
    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
        'descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
    ];

    /**
     * Abrir modal para crear nueva categoría
     */
    public function crear()
    {
        $this->resetearFormulario();
        $this->modoEdicion = false;
        $this->modalAbierto = true;
    }

    /**
     * Abrir modal para editar categoría existente
     */
    public function editar($id)
    {
        $categoria = CategoriaInsumo::findOrFail($id);
        
        $this->categoria_id = $categoria->id;
        $this->nombre = $categoria->nombre;
        $this->descripcion = $categoria->descripcion;
        $this->estado = $categoria->estado;
        
        $this->modoEdicion = true;
        $this->modalAbierto = true;
    }

    /**
     * Guardar categoría (crear o actualizar)
     */
    public function guardar()
    {
        $this->validate();

        try {
            if ($this->modoEdicion) {
                $categoria = CategoriaInsumo::findOrFail($this->categoria_id);
                $categoria->update([
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    'estado' => $this->estado,
                ]);

                session()->flash('mensaje', 'Categoría actualizada exitosamente.');
            } else {
                CategoriaInsumo::create([
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    'estado' => $this->estado,
                ]);

                session()->flash('mensaje', 'Categoría creada exitosamente.');
            }

            $this->cerrarModal();
            $this->resetearFormulario();
        } catch (\Exception $e) {
            session()->flash('error', 'Ocurrió un error: ' . $e->getMessage());
            \Log::error('Error al guardar categoría: ' . $e->getMessage());
        }
    }

    /**
     * Confirmar cambio de estado
     */
    public function confirmarCambiarEstado($id)
    {
        $this->categoriaACambiar = CategoriaInsumo::findOrFail($id);
        $this->estadoActual = $this->categoriaACambiar->estado;
        $this->modalCambiarEstado = true;
    }

    /**
     * Cambiar estado de la categoría
     */
    public function cambiarEstado()
    {
        try {
            if ($this->categoriaACambiar) {
                $this->categoriaACambiar->estado = !$this->estadoActual;
                $this->categoriaACambiar->save();

                $estado = $this->categoriaACambiar->estado ? 'activada' : 'desactivada';
                session()->flash('mensaje', "Categoría {$estado} exitosamente.");
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
        $this->categoriaACambiar = null;
        $this->estadoActual = null;
    }

    /**
     * Confirmar eliminación de la categoría
     */
    public function confirmarEliminar($id)
    {
        $this->categoriaAEliminar = CategoriaInsumo::findOrFail($id);
        $this->modalEliminar = true;
    }

    /**
     * Eliminar categoría
     */
    public function eliminar()
    {
        try {
            if ($this->categoriaAEliminar) {
                $this->categoriaAEliminar->delete();
                session()->flash('mensaje', 'Categoría eliminada exitosamente.');
            }

            $this->cerrarModalEliminar();
        } catch (\Exception $e) {
            session()->flash('error', 'No se puede eliminar la categoría: ' . $e->getMessage());
        }
    }

    /**
     * Cerrar modal de eliminar
     */
    public function cerrarModalEliminar()
    {
        $this->modalEliminar = false;
        $this->categoriaAEliminar = null;
    }

    /**
     * Resetear formulario
     */
    private function resetearFormulario()
    {
        $this->categoria_id = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->estado = true;
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
     * Resetear paginación al buscar
     */
    public function updatingBuscar()
    {
        $this->resetPage();
    }

    /**
     * Renderizar componente
     */
    public function render()
    {
        $query = CategoriaInsumo::query();

        // Búsqueda por nombre y descripción
        if ($this->buscar) {
            $buscar = '%' . $this->buscar . '%';
            $query->where(function ($q) use ($buscar) {
                $q->whereRaw('unaccent(nombre) ilike unaccent(?)', [$buscar])
                  ->orWhereRaw('unaccent(descripcion) ilike unaccent(?)', [$buscar]);
            });
        }

        $categorias = $query->orderBy($this->sortBy, $this->sortDirection)->paginate(10);

        return view('livewire.insumos.gestionar-categorias-insumo', [
            'categorias' => $categorias,
        ]);
    }
}
