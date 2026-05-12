<?php

namespace App\Livewire\Sucursales;

use App\Models\Sucursal;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarSucursales extends Component
{
    use WithPagination;

    // Propiedades del formulario
    public $sucursal_id;

    public $nombre;

    public $codigo;

    public $direccion;

    public $telefono;

    public $telefono_2;

    public $estado = true;

    // Propiedades de control
    public $modalAbierto = false;

    public $modalEliminar = false;

    public $modalCambiarEstado = false;

    public $modalVer = false;

    public $sucursalAEliminar = null;

    public $sucursalACambiar = null;

    public $sucursalAVer = null;

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
            'direccion' => 'required|string|max:500',
            'telefono' => 'required|string|max:20',
            'telefono_2' => 'nullable|string|max:20',
            'estado' => 'boolean',
        ];
    }

    // Mensajes de validación personalizados
    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'direccion.required' => 'La dirección es obligatoria.',
        'telefono.required' => 'El teléfono es obligatorio.',
    ];

    /**
     * Abrir modal para crear nueva sucursal
     */
    public function crear()
    {
        $this->resetearFormulario();
        $this->modoEdicion = false;
        $this->modalAbierto = true;
    }

    /**
     * Abrir modal para ver detalles de sucursal
     */
    public function ver($id)
    {
        $this->sucursalAVer = Sucursal::with('users')->findOrFail($id);
        $this->modalVer = true;
    }

    /**
     * Cerrar modal de ver
     */
    public function cerrarModalVer()
    {
        $this->modalVer = false;
        $this->sucursalAVer = null;
    }

    /**
     * Abrir modal para editar sucursal existente
     */
    public function editar($id)
    {
        $sucursal = Sucursal::findOrFail($id);

        $this->sucursal_id = $sucursal->id;
        $this->nombre = $sucursal->nombre;
        $this->codigo = $sucursal->codigo;
        $this->direccion = $sucursal->direccion;
        $this->telefono = $sucursal->telefono;
        $this->telefono_2 = $sucursal->telefono_2;
        $this->estado = $sucursal->estado;

        $this->modoEdicion = true;
        $this->modalAbierto = true;
    }

    /**
     * Guardar sucursal (crear o actualizar)
     */
    public function guardar()
    {
        $this->validate();

        try {
            if ($this->modoEdicion) {
                $sucursal = Sucursal::findOrFail($this->sucursal_id);
                $sucursal->update([
                    'nombre' => $this->nombre,
                    'direccion' => $this->direccion,
                    'telefono' => $this->telefono,
                    'telefono_2' => $this->telefono_2,
                    'estado' => $this->estado,
                ]);

                session()->flash('mensaje', 'Sucursal actualizada exitosamente.');
            } else {
                // Generar código automáticamente
                $this->codigo = $this->generarCodigo();

                Sucursal::create([
                    'nombre' => $this->nombre,
                    'codigo' => $this->codigo,
                    'direccion' => $this->direccion,
                    'telefono' => $this->telefono,
                    'telefono_2' => $this->telefono_2,
                    'estado' => $this->estado,
                ]);

                session()->flash('mensaje', 'Sucursal creada exitosamente.');
            }

            $this->cerrarModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar la sucursal: '.$e->getMessage());
        }
    }

    /**
     * Generar código único para la sucursal
     */
    private function generarCodigo()
    {
        // Obtener el último código numérico
        $ultimaSucursal = Sucursal::orderBy('id', 'desc')->first();
        $numero = $ultimaSucursal ? $ultimaSucursal->id + 1 : 1;

        // Formatear con ceros a la izquierda y agregar sufijo -SC (ej: 001-SC, 002-SC, 003-SC)
        return str_pad($numero, 3, '0', STR_PAD_LEFT).'-SC';
    }

    /**
     * Abrir modal de confirmación para eliminar
     */
    public function confirmarEliminar($id)
    {
        $this->sucursalAEliminar = $id;
        $this->modalEliminar = true;
    }

    /**
     * Cancelar eliminación
     */
    public function cancelarEliminar()
    {
        $this->modalEliminar = false;
        $this->sucursalAEliminar = null;
    }

    /**
     * Eliminar sucursal
     */
    public function eliminar()
    {
        try {
            if (! $this->sucursalAEliminar) {
                return;
            }

            $sucursal = Sucursal::findOrFail($this->sucursalAEliminar);

            // Verificar si tiene usuarios asignados
            if ($sucursal->users()->count() > 0) {
                session()->flash('error', 'No se puede eliminar la sucursal porque tiene usuarios asignados.');
                $this->modalEliminar = false;
                $this->sucursalAEliminar = null;

                return;
            }

            $sucursal->delete();
            session()->flash('mensaje', 'Sucursal eliminada exitosamente.');

            $this->modalEliminar = false;
            $this->sucursalAEliminar = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar la sucursal: '.$e->getMessage());
            $this->modalEliminar = false;
            $this->sucursalAEliminar = null;
        }
    }

    /**
     * Abrir modal de confirmación para cambiar estado
     */
    public function confirmarCambiarEstado($id)
    {
        $sucursal = Sucursal::findOrFail($id);
        $this->sucursalACambiar = $id;
        $this->estadoActual = $sucursal->estado;
        $this->modalCambiarEstado = true;
    }

    /**
     * Hook que se ejecuta cuando cambia la propiedad modalCambiarEstado
     */
    public function updatedModalCambiarEstado($value)
    {
        if (! $value) {
            $this->sucursalACambiar = null;
            $this->estadoActual = null;
        }
    }

    /**
     * Cancelar cambio de estado
     */
    public function cancelarCambiarEstado()
    {
        $this->modalCambiarEstado = false;
        $this->sucursalACambiar = null;
        $this->estadoActual = null;
    }

    /**
     * Cambiar estado de la sucursal
     */
    public function cambiarEstado()
    {
        try {
            if (! $this->sucursalACambiar) {
                return;
            }

            $sucursal = Sucursal::findOrFail($this->sucursalACambiar);
            
            // Validar si intenta desactivar y hay usuarios asignados
            if ($sucursal->estado && $sucursal->users()->count() > 0) {
                $usuariosAsignados = $sucursal->users->pluck('name')->take(5)->implode(', ');
                $totalUsuarios = $sucursal->users()->count();
                $mensaje = "⚠️ No se puede desactivar esta sucursal. Hay {$totalUsuarios} usuario(s) asignado(s): {$usuariosAsignados}";
                if ($totalUsuarios > 5) {
                    $mensaje .= " y " . ($totalUsuarios - 5) . " más";
                }
                $mensaje .= ". Por favor, reasigna estos usuarios a otra sucursal antes de desactivar.";
                session()->flash('error', $mensaje);
                
                $this->modalCambiarEstado = false;
                $this->sucursalACambiar = null;
                $this->estadoActual = null;
                return;
            }
            
            $sucursal->update(['estado' => !$sucursal->estado]);
            
            $mensaje = $sucursal->estado ? 'Sucursal activada exitosamente.' : 'Sucursal desactivada exitosamente.';
            session()->flash('mensaje', $mensaje);

            $this->modalCambiarEstado = false;
            $this->sucursalACambiar = null;
            $this->estadoActual = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cambiar el estado: '.$e->getMessage());
            $this->modalCambiarEstado = false;
            $this->sucursalACambiar = null;
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
        $this->sucursal_id = null;
        $this->nombre = '';
        $this->codigo = '';
        $this->direccion = '';
        $this->telefono = '';
        $this->telefono_2 = '';
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
     * Limpiar búsqueda
     */
    public function limpiarBuscar()
    {
        $this->buscar = '';
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
        $sucursales = Sucursal::query()
            ->when($this->buscar, function ($query) {
                $buscar = '%'.$this->buscar.'%';
                $query->whereRaw('unaccent(nombre) ilike unaccent(?)', [$buscar])
                    ->orWhereRaw('unaccent(codigo) ilike unaccent(?)', [$buscar])
                    ->orWhereRaw('unaccent(direccion) ilike unaccent(?)', [$buscar])
                    ->orWhereRaw('unaccent(telefono) ilike unaccent(?)', [$buscar])
                    ->orWhereRaw('unaccent(telefono_2) ilike unaccent(?)', [$buscar]);
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.sucursales.gestionar-sucursales', [
            'sucursales' => $sucursales,
        ]);
    }
}
