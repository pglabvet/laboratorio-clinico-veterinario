<?php

namespace App\Livewire\UnidadesMedida;

use App\Models\UnidadMedida;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarUnidadesMedida extends Component
{
    use WithPagination;

    // Propiedades del formulario
    public $unidad_id;
    public $nombre;
    public $abreviatura;
    public $estado = true;

    // Propiedades de control
    public $modalAbierto = false;
    public $modalCambiarEstado = false;
    public $modalEliminar = false;
    public $unidadACambiar = null;
    public $unidadAEliminar = null;
    public $estadoActual = null;
    public $buscar = '';
    public $modoEdicion = false;

    // Propiedades de ordenamiento
    public $sortBy = 'nombre';
    public $sortDirection = 'asc';

    // Reglas de validación
    protected function rules()
    {
        $rules = [
            'nombre' => 'required|string|max:100',
            'abreviatura' => 'required|string|max:10',
            'estado' => 'boolean',
        ];

        return $rules;
    }

    // Mensajes de validación personalizados
    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
        'abreviatura.required' => 'La abreviatura es obligatoria.',
        'abreviatura.max' => 'La abreviatura no puede exceder 10 caracteres.',
    ];

    /**
     * Abrir modal para crear nueva unidad de medida
     */
    public function crear()
    {
        $this->resetearFormulario();
        $this->modoEdicion = false;
        $this->modalAbierto = true;
    }

    /**
     * Abrir modal para editar unidad de medida existente
     */
    public function editar($id)
    {
        $unidad = UnidadMedida::findOrFail($id);
        
        $this->unidad_id = $unidad->id;
        $this->nombre = $unidad->nombre;
        $this->abreviatura = $unidad->abreviatura;
        $this->estado = $unidad->estado;
        
        $this->modoEdicion = true;
        $this->modalAbierto = true;
    }

    /**
     * Guardar unidad de medida (crear o actualizar)
     */
    public function guardar()
    {
        $this->validate();

        try {
            if ($this->modoEdicion) {
                $unidad = UnidadMedida::findOrFail($this->unidad_id);
                $unidad->update([
                    'nombre' => $this->nombre,
                    'abreviatura' => $this->abreviatura,
                    'estado' => $this->estado,
                ]);

                session()->flash('mensaje', 'Unidad de medida actualizada exitosamente.');
            } else {
                UnidadMedida::create([
                    'nombre' => $this->nombre,
                    'abreviatura' => $this->abreviatura,
                    'estado' => $this->estado,
                ]);

                session()->flash('mensaje', 'Unidad de medida creada exitosamente.');
            }

            $this->cerrarModal();
            $this->resetearFormulario();
        } catch (\Exception $e) {
            session()->flash('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    /**
     * Confirmar cambio de estado
     */
    public function confirmarCambiarEstado($id)
    {
        $this->unidadACambiar = UnidadMedida::findOrFail($id);
        $this->estadoActual = $this->unidadACambiar->estado;
        $this->modalCambiarEstado = true;
    }

    /**
     * Cambiar estado de la unidad de medida
     */
    public function cambiarEstado()
    {
        try {
            if ($this->unidadACambiar) {
                $this->unidadACambiar->estado = !$this->estadoActual;
                $this->unidadACambiar->save();

                $estado = $this->unidadACambiar->estado ? 'activada' : 'desactivada';
                session()->flash('mensaje', "Unidad de medida {$estado} exitosamente.");
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
        $this->unidadACambiar = null;
        $this->estadoActual = null;
    }

    /**
     * Confirmar eliminación de la unidad de medida
     */
    public function confirmarEliminar($id)
    {
        $this->unidadAEliminar = UnidadMedida::findOrFail($id);
        $this->modalEliminar = true;
    }

    /**
     * Eliminar unidad de medida
     */
    public function eliminar()
    {
        try {
            if ($this->unidadAEliminar) {
                $this->unidadAEliminar->delete();
                session()->flash('mensaje', 'Unidad de medida eliminada exitosamente.');
            }

            $this->cerrarModalEliminar();
        } catch (\Exception $e) {
            session()->flash('error', 'No se puede eliminar la unidad de medida: ' . $e->getMessage());
        }
    }

    /**
     * Cerrar modal de eliminar
     */
    public function cerrarModalEliminar()
    {
        $this->modalEliminar = false;
        $this->unidadAEliminar = null;
    }

    /**
     * Resetear formulario
     */
    private function resetearFormulario()
    {
        $this->unidad_id = null;
        $this->nombre = '';
        $this->abreviatura = '';
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
        $unidades = UnidadMedida::query()
            ->when($this->buscar, function ($query) {
                $query->where(function ($q) {
                    $q->where('nombre', 'like', '%' . $this->buscar . '%')
                      ->orWhere('abreviatura', 'like', '%' . $this->buscar . '%');
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.unidades-medida.gestionar-unidades-medida', [
            'unidades' => $unidades,
        ]);
    }
}
