<?php

namespace App\Livewire\TiposAnalisis;

use App\Models\TipoAnalisis;
use App\Models\PlantillaFormulario;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarTiposAnalisis extends Component
{
    use WithPagination;

    // Propiedades del formulario
    public $tipo_analisis_id;
    public $nombre;
    public $descripcion;
    public $estado = true;

    // Propiedades de control
    public $modalAbierto = false;
    public $modalEliminar = false;
    public $modalCambiarEstado = false;
    public $modalVer = false;
    public $tipoAnalisisAEliminar = null;
    public $tipoAnalisisACambiar = null;
    public $tipoAnalisisAVer = null;
    public $estadoActual = null;
    public $buscar = '';
    public $modoEdicion = false;

    // Propiedades de ordenamiento
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    // Reglas de validación
    protected function rules()
    {
        $rules = [
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tipos_analisis', 'nombre')->ignore($this->tipo_analisis_id),
            ],
            'descripcion' => 'nullable|string|max:1000',
            'estado' => 'boolean',
        ];

        return $rules;
    }

    // Mensajes de validación personalizados
    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.unique' => 'Ya existe un tipo de análisis con este nombre.',
        'descripcion.max' => 'La descripción no puede exceder los 1000 caracteres.',
    ];

    /**
     * Abrir modal para crear nuevo tipo de análisis
     */
    public function crear()
    {
        $this->resetearFormulario();
        $this->modoEdicion = false;
        $this->modalAbierto = true;
    }

    /**
     * Abrir modal para ver detalles de tipo de análisis
     */
    public function ver($id)
    {
        $this->tipoAnalisisAVer = TipoAnalisis::with(['analisis', 'plantillas'])->findOrFail($id);
        $this->modalVer = true;
    }

    /**
     * Cerrar modal de ver
     */
    public function cerrarModalVer()
    {
        $this->modalVer = false;
        $this->tipoAnalisisAVer = null;
    }

    /**
     * Abrir modal para editar tipo de análisis existente
     */
    public function editar($id)
    {
        $tipoAnalisis = TipoAnalisis::findOrFail($id);
        
        $this->tipo_analisis_id = $tipoAnalisis->id;
        $this->nombre = $tipoAnalisis->nombre;
        $this->descripcion = $tipoAnalisis->descripcion;
        $this->estado = $tipoAnalisis->estado;
        
        $this->modoEdicion = true;
        $this->modalAbierto = true;
    }

    /**
     * Guardar tipo de análisis (crear o actualizar)
     */
    public function guardar()
    {
        $this->validate();

        try {
            if ($this->modoEdicion) {
                $tipoAnalisis = TipoAnalisis::findOrFail($this->tipo_analisis_id);
                $tipoAnalisis->update([
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    'estado' => $this->estado,
                ]);

                session()->flash('mensaje', 'Tipo de análisis actualizado exitosamente.');
            } else {
                TipoAnalisis::create([
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    'estado' => $this->estado,
                ]);

                session()->flash('mensaje', 'Tipo de análisis creado exitosamente.');
            }

            $this->cerrarModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar el tipo de análisis: ' . $e->getMessage());
        }
    }

    /**
     * Abrir modal de confirmación para eliminar
     */
    public function confirmarEliminar($id)
    {
        $this->tipoAnalisisAEliminar = $id;
        $this->modalEliminar = true;
    }

    /**
     * Cancelar eliminación
     */
    public function cancelarEliminar()
    {
        $this->modalEliminar = false;
        $this->tipoAnalisisAEliminar = null;
    }

    /**
     * Eliminar tipo de análisis
     */
    public function eliminar()
    {
        try {
            if (!$this->tipoAnalisisAEliminar) {
                return;
            }

            $tipoAnalisis = TipoAnalisis::findOrFail($this->tipoAnalisisAEliminar);
            
            // Verificar si tiene análisis o parámetros asociados
            if ($tipoAnalisis->analisis()->count() > 0) {
                session()->flash('error', 'No se puede eliminar el tipo de análisis porque tiene análisis asociados.');
                $this->modalEliminar = false;
                $this->tipoAnalisisAEliminar = null;
                return;
            }

            if ($tipoAnalisis->parametros()->count() > 0) {
                session()->flash('error', 'No se puede eliminar el tipo de análisis porque tiene parámetros asociados.');
                $this->modalEliminar = false;
                $this->tipoAnalisisAEliminar = null;
                return;
            }

            $tipoAnalisis->delete();
            session()->flash('mensaje', 'Tipo de análisis eliminado exitosamente.');
            
            $this->modalEliminar = false;
            $this->tipoAnalisisAEliminar = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el tipo de análisis: ' . $e->getMessage());
            $this->modalEliminar = false;
            $this->tipoAnalisisAEliminar = null;
        }
    }

    /**
     * Abrir modal de confirmación para cambiar estado
     */
    public function confirmarCambiarEstado($id)
    {
        $tipoAnalisis = TipoAnalisis::findOrFail($id);
        $this->tipoAnalisisACambiar = $id;
        $this->estadoActual = $tipoAnalisis->estado;
        $this->modalCambiarEstado = true;
    }

    /**
     * Hook que se ejecuta cuando cambia la propiedad modalCambiarEstado
     */
    public function updatedModalCambiarEstado($value)
    {
        if (!$value) {
            $this->tipoAnalisisACambiar = null;
            $this->estadoActual = null;
        }
    }

    /**
     * Cancelar cambio de estado
     */
    public function cancelarCambiarEstado()
    {
        $this->modalCambiarEstado = false;
        $this->tipoAnalisisACambiar = null;
        $this->estadoActual = null;
    }

    /**
     * Cambiar estado del tipo de análisis
     */
    public function cambiarEstado()
    {
        try {
            if (!$this->tipoAnalisisACambiar) {
                return;
            }

            $tipoAnalisis = TipoAnalisis::findOrFail($this->tipoAnalisisACambiar);
            $tipoAnalisis->update(['estado' => !$tipoAnalisis->estado]);
            
            $mensaje = $tipoAnalisis->estado ? 'Tipo de análisis activado exitosamente.' : 'Tipo de análisis desactivado exitosamente.';
            session()->flash('mensaje', $mensaje);

            $this->modalCambiarEstado = false;
            $this->tipoAnalisisACambiar = null;
            $this->estadoActual = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cambiar el estado: ' . $e->getMessage());
            $this->modalCambiarEstado = false;
            $this->tipoAnalisisACambiar = null;
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
        $this->tipo_analisis_id = null;
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
        $tiposAnalisis = TipoAnalisis::query()
            ->withCount('plantillas')
            ->when($this->buscar, function ($query) {
                $query->where('nombre', 'ilike', '%' . $this->buscar . '%')
                    ->orWhere('descripcion', 'ilike', '%' . $this->buscar . '%');
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.tipos-analisis.gestionar-tipos-analisis', [
            'tiposAnalisis' => $tiposAnalisis,
        ]);
    }
}
