<?php

namespace App\Livewire\Veterinarias;

use App\Models\Veterinaria;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarVeterinarias extends Component
{
    use WithPagination;

    // Propiedades del formulario
    public $veterinaria_id;
    public $nombre;
    public $responsable;
    public $telefono;
    public $email;
    public $direccion;
    public $estado = true;

    // Propiedades de control
    public $modalAbierto = false;
    public $modalEliminar = false;
    public $modalCambiarEstado = false;
    public $modalVer = false;
    public $veterinariaAEliminar = null;
    public $veterinariaACambiar = null;
    public $veterinariaAVer = null;
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
            'responsable' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'direccion' => 'required|string|max:500',
            'estado' => 'boolean',
        ];
    }

    // Mensajes de validación personalizados
    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'responsable.required' => 'El responsable es obligatorio.',
        'telefono.required' => 'El teléfono es obligatorio.',
        'email.required' => 'El email es obligatorio.',
        'email.email' => 'El email debe ser válido.',
        'direccion.required' => 'La dirección es obligatoria.',
    ];

    /**
     * Abrir modal para crear nueva veterinaria
     */
    public function crear()
    {
        $this->resetearFormulario();
        $this->modoEdicion = false;
        $this->modalAbierto = true;
    }

    /**
     * Abrir modal para ver detalles de veterinaria
     */
    public function ver($id)
    {
        $this->veterinariaAVer = Veterinaria::findOrFail($id);
        $this->modalVer = true;
    }

    /**
     * Cerrar modal de ver
     */
    public function cerrarModalVer()
    {
        $this->modalVer = false;
        $this->veterinariaAVer = null;
    }

    /**
     * Abrir modal para editar veterinaria existente
     */
    public function editar($id)
    {
        $veterinaria = Veterinaria::findOrFail($id);
        
        $this->veterinaria_id = $veterinaria->id;
        $this->nombre = $veterinaria->nombre;
        $this->responsable = $veterinaria->responsable;
        $this->telefono = $veterinaria->telefono;
        $this->email = $veterinaria->email;
        $this->direccion = $veterinaria->direccion;
        $this->estado = $veterinaria->estado;
        
        $this->modoEdicion = true;
        $this->modalAbierto = true;
    }

    /**
     * Guardar veterinaria (crear o actualizar)
     */
    public function guardar()
    {
        $this->validate();

        try {
            if ($this->modoEdicion) {
                $veterinaria = Veterinaria::findOrFail($this->veterinaria_id);
                $veterinaria->update([
                    'nombre' => $this->nombre,
                    'responsable' => $this->responsable,
                    'telefono' => $this->telefono,
                    'email' => $this->email,
                    'direccion' => $this->direccion,
                    'estado' => $this->estado,
                ]);

                session()->flash('mensaje', 'Veterinaria actualizada exitosamente.');
            } else {
                Veterinaria::create([
                    'nombre' => $this->nombre,
                    'responsable' => $this->responsable,
                    'telefono' => $this->telefono,
                    'email' => $this->email,
                    'direccion' => $this->direccion,
                    'estado' => $this->estado,
                ]);

                session()->flash('mensaje', 'Veterinaria creada exitosamente.');
            }

            $this->cerrarModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar la veterinaria: ' . $e->getMessage());
        }
    }

    /**
     * Abrir modal de confirmación para eliminar
     */
    public function confirmarEliminar($id)
    {
        $this->veterinariaAEliminar = $id;
        $this->modalEliminar = true;
    }

    /**
     * Cancelar eliminación
     */
    public function cancelarEliminar()
    {
        $this->modalEliminar = false;
        $this->veterinariaAEliminar = null;
    }

    /**
     * Eliminar veterinaria
     */
    public function eliminar()
    {
        try {
            if (!$this->veterinariaAEliminar) {
                return;
            }

            $veterinaria = Veterinaria::findOrFail($this->veterinariaAEliminar);
            
            // Verificar si tiene muestras asociadas
            if ($veterinaria->muestras()->count() > 0) {
                session()->flash('error', 'No se puede eliminar la veterinaria porque tiene muestras asociadas.');
                $this->modalEliminar = false;
                $this->veterinariaAEliminar = null;
                return;
            }

            $veterinaria->delete();
            session()->flash('mensaje', 'Veterinaria eliminada exitosamente.');
            
            $this->modalEliminar = false;
            $this->veterinariaAEliminar = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar la veterinaria: ' . $e->getMessage());
            $this->modalEliminar = false;
            $this->veterinariaAEliminar = null;
        }
    }

    /**
     * Abrir modal de confirmación para cambiar estado
     */
    public function confirmarCambiarEstado($id)
    {
        $veterinaria = Veterinaria::findOrFail($id);
        $this->veterinariaACambiar = $id;
        $this->estadoActual = $veterinaria->estado;
        $this->modalCambiarEstado = true;
    }

    /**
     * Hook que se ejecuta cuando cambia la propiedad modalCambiarEstado
     */
    public function updatedModalCambiarEstado($value)
    {
        if (!$value) {
            $this->veterinariaACambiar = null;
            $this->estadoActual = null;
        }
    }

    /**
     * Cancelar cambio de estado
     */
    public function cancelarCambiarEstado()
    {
        $this->modalCambiarEstado = false;
        $this->veterinariaACambiar = null;
        $this->estadoActual = null;
    }

    /**
     * Cambiar estado de la veterinaria
     */
    public function cambiarEstado()
    {
        try {
            if (!$this->veterinariaACambiar) {
                return;
            }

            $veterinaria = Veterinaria::findOrFail($this->veterinariaACambiar);
            $nuevoEstado = !$veterinaria->estado;
            $veterinaria->estado = $nuevoEstado;
            $veterinaria->save();
            
            $mensaje = $nuevoEstado ? 'Veterinaria activada exitosamente.' : 'Veterinaria desactivada exitosamente.';
            session()->flash('mensaje', $mensaje);

            $this->modalCambiarEstado = false;
            $this->veterinariaACambiar = null;
            $this->estadoActual = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cambiar el estado: ' . $e->getMessage());
            $this->modalCambiarEstado = false;
            $this->veterinariaACambiar = null;
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
        $this->veterinaria_id = null;
        $this->nombre = '';
        $this->responsable = '';
        $this->telefono = '';
        $this->email = '';
        $this->direccion = '';
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
     * Limpiar búsqueda
     */
    public function limpiarBuscar()
    {
        $this->buscar = '';
        $this->resetPage();
    }

    /**
     * Renderizar componente
     */
    public function render()
    {
        $veterinarias = Veterinaria::query()
            ->when($this->buscar, function ($query) {
                $query->where('nombre', 'ilike', '%' . $this->buscar . '%')
                    ->orWhere('responsable', 'ilike', '%' . $this->buscar . '%')
                    ->orWhere('email', 'ilike', '%' . $this->buscar . '%')
                    ->orWhere('telefono', 'ilike', '%' . $this->buscar . '%')
                    ->orWhere('direccion', 'ilike', '%' . $this->buscar . '%');
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.veterinarias.gestionar-veterinarias', [
            'veterinarias' => $veterinarias,
        ]);
    }
}
 //este es un comentario para saber si los cambios se guardan en esta rama 