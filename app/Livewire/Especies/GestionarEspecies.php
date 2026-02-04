<?php

namespace App\Livewire\Especies;

use App\Models\Especie;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarEspecies extends Component
{
    use WithPagination;

    // Propiedades del formulario
    public $especie_id;
    public $nombre;
    public $descripcion;
    public $estado = true;

    // Propiedades de control
    public $modalAbierto = false;
    public $modalEliminar = false;
    public $modalCambiarEstado = false;
    public $modalVer = false;
    public $especieAEliminar = null;
    public $especieACambiar = null;
    public $especieAVer = null;
    public $estadoActual = null;
    public $buscar = '';
    public $modoEdicion = false;

    // Propiedades de ordenamiento
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    // Reglas de validación
    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'estado' => 'boolean',
        ];
    }

    // Mensajes de validación personalizados
    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
        'descripcion.max' => 'La descripción no puede exceder 1000 caracteres.',
    ];

    /**
     * Abrir modal para crear nueva especie
     */
    public function crear()
    {
        $this->resetearFormulario();
        $this->modoEdicion = false;
        $this->modalAbierto = true;
    }

    /**
     * Abrir modal para ver detalles de especie
     */
    public function ver($id)
    {
        $this->especieAVer = Especie::withCount(['muestras', 'rangosReferencia'])->findOrFail($id);
        $this->modalVer = true;
    }

    /**
     * Cerrar modal de ver
     */
    public function cerrarModalVer()
    {
        $this->modalVer = false;
        $this->especieAVer = null;
    }

    /**
     * Abrir modal para editar especie existente
     */
    public function editar($id)
    {
        $especie = Especie::findOrFail($id);
        
        $this->especie_id = $especie->id;
        $this->nombre = $especie->nombre;
        $this->descripcion = $especie->descripcion;
        $this->estado = $especie->estado;
        
        $this->modoEdicion = true;
        $this->modalAbierto = true;
    }

    /**
     * Guardar especie (crear o actualizar)
     */
    public function guardar()
    {
        $this->validate();

        try {
            if ($this->modoEdicion) {
                $especie = Especie::findOrFail($this->especie_id);
                $especie->update([
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    'estado' => $this->estado,
                ]);

                session()->flash('mensaje', 'Especie actualizada exitosamente.');
            } else {
                Especie::create([
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    'estado' => $this->estado,
                ]);

                session()->flash('mensaje', 'Especie creada exitosamente.');
            }

            $this->cerrarModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar la especie: ' . $e->getMessage());
        }
    }

    /**
     * Abrir modal de confirmación para eliminar
     */
    public function confirmarEliminar($id)
    {
        $this->especieAEliminar = $id;
        $this->modalEliminar = true;
    }

    /**
     * Cancelar eliminación
     */
    public function cancelarEliminar()
    {
        $this->modalEliminar = false;
        $this->especieAEliminar = null;
    }

    /**
     * Eliminar especie
     */
    public function eliminar()
    {
        try {
            if (!$this->especieAEliminar) {
                return;
            }

            $especie = Especie::findOrFail($this->especieAEliminar);
            
            // Verificar si tiene muestras o rangos de referencia asociados
            if ($especie->muestras()->count() > 0) {
                session()->flash('error', 'No se puede eliminar la especie porque tiene muestras asociadas.');
                $this->modalEliminar = false;
                $this->especieAEliminar = null;
                return;
            }

            if ($especie->rangosReferencia()->count() > 0) {
                session()->flash('error', 'No se puede eliminar la especie porque tiene rangos de referencia asociados.');
                $this->modalEliminar = false;
                $this->especieAEliminar = null;
                return;
            }

            $especie->delete();
            session()->flash('mensaje', 'Especie eliminada exitosamente.');
            
            $this->modalEliminar = false;
            $this->especieAEliminar = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar la especie: ' . $e->getMessage());
            $this->modalEliminar = false;
            $this->especieAEliminar = null;
        }
    }

    /**
     * Abrir modal de confirmación para cambiar estado
     */
    public function confirmarCambiarEstado($id)
    {
        $especie = Especie::findOrFail($id);
        $this->especieACambiar = $id;
        $this->estadoActual = $especie->estado;
        $this->modalCambiarEstado = true;
    }

    /**
     * Hook que se ejecuta cuando cambia la propiedad modalCambiarEstado
     */
    public function updatedModalCambiarEstado($value)
    {
        if (!$value) {
            $this->especieACambiar = null;
            $this->estadoActual = null;
        }
    }

    /**
     * Cancelar cambio de estado
     */
    public function cancelarCambiarEstado()
    {
        $this->modalCambiarEstado = false;
        $this->especieACambiar = null;
        $this->estadoActual = null;
    }

    /**
     * Cambiar estado de la especie
     */
    public function cambiarEstado()
    {
        try {
            if (!$this->especieACambiar) {
                return;
            }

            $especie = Especie::findOrFail($this->especieACambiar);
            $especie->update(['estado' => !$especie->estado]);
            
            $mensaje = $especie->estado ? 'Especie activada exitosamente.' : 'Especie desactivada exitosamente.';
            session()->flash('mensaje', $mensaje);

            $this->modalCambiarEstado = false;
            $this->especieACambiar = null;
            $this->estadoActual = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cambiar el estado: ' . $e->getMessage());
            $this->modalCambiarEstado = false;
            $this->especieACambiar = null;
            $this->estadoActual = null;
        }
    }

    /**
     * Cerrar modal
     */
    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->resetearFormulario();
        $this->resetValidation();
    }

    /**
     * Resetear formulario
     */
    private function resetearFormulario()
    {
        $this->especie_id = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->estado = true;
    }

    /**
     * Resetear búsqueda
     */
    public function updatingBuscar()
    {
        $this->resetPage();
    }

    /**
     * Cambiar ordenamiento
     */
    public function ordenarPor($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Renderizar componente
     */
    public function render()
    {
        $especies = Especie::query()
            ->when($this->buscar, function ($query) {
                $query->where('nombre', 'like', '%' . $this->buscar . '%')
                    ->orWhere('descripcion', 'like', '%' . $this->buscar . '%');
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.especies.gestionar-especies', [
            'especies' => $especies,
        ]);
    }
}
