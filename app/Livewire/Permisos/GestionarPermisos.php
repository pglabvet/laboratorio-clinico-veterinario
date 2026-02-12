<?php

namespace App\Livewire\Permisos;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

class GestionarPermisos extends Component
{
    use WithPagination;

    // Propiedades del formulario
    public $permission_id;
    public $name;
    public $guard_name = 'web';

    // Propiedades de control
    public $modalAbierto = false;
    public $modalEliminar = false;
    public $modalVer = false;
    public $permisoAEliminar = null;
    public $permisoAVer = null;
    public $buscar = '';
    public $modoEdicion = false;

    // Propiedades de ordenamiento
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    // Reglas de validación
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:permissions,name,' . ($this->permission_id ?? 'NULL'),
            'guard_name' => 'required|string',
        ];
    }

    // Mensajes de validación personalizados
    protected $messages = [
        'name.required' => 'El nombre del permiso es obligatorio.',
        'name.unique' => 'Ya existe un permiso con este nombre.',
        'guard_name.required' => 'El guard es obligatorio.',
    ];

    /**
     * Abrir modal para crear nuevo permiso
     */
    public function crear()
    {
        $this->resetearFormulario();
        $this->modoEdicion = false;
        $this->modalAbierto = true;
    }

    /**
     * Abrir modal para ver detalles del permiso
     */
    public function ver($id)
    {
        $this->permisoAVer = Permission::with('roles')->findOrFail($id);
        $this->modalVer = true;
    }

    /**
     * Cerrar modal de ver
     */
    public function cerrarModalVer()
    {
        $this->modalVer = false;
        $this->permisoAVer = null;
    }

    /**
     * Abrir modal para editar permiso existente
     */
    public function editar($id)
    {
        $permission = Permission::findOrFail($id);
        
        $this->permission_id = $permission->id;
        $this->name = $permission->name;
        $this->guard_name = $permission->guard_name;
        
        $this->modoEdicion = true;
        $this->modalAbierto = true;
    }

    /**
     * Guardar permiso (crear o actualizar)
     */
    public function guardar()
    {
        $this->validate();

        try {
            if ($this->modoEdicion) {
                $permission = Permission::findOrFail($this->permission_id);
                $permission->update([
                    'name' => $this->name,
                    'guard_name' => $this->guard_name,
                ]);

                session()->flash('mensaje', 'Permiso actualizado exitosamente.');
            } else {
                Permission::create([
                    'name' => $this->name,
                    'guard_name' => $this->guard_name,
                ]);

                session()->flash('mensaje', 'Permiso creado exitosamente.');
            }

            $this->cerrarModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar el permiso: ' . $e->getMessage());
        }
    }

    /**
     * Abrir modal de confirmación para eliminar
     */
    public function confirmarEliminar($id)
    {
        $this->permisoAEliminar = $id;
        $this->modalEliminar = true;
    }

    /**
     * Cancelar eliminación
     */
    public function cancelarEliminar()
    {
        $this->modalEliminar = false;
        $this->permisoAEliminar = null;
    }

    /**
     * Eliminar permiso
     */
    public function eliminar()
    {
        try {
            if (!$this->permisoAEliminar) {
                return;
            }

            $permission = Permission::findOrFail($this->permisoAEliminar);
            
            // Verificar si está asignado a algún rol
            if ($permission->roles()->count() > 0) {
                session()->flash('error', 'No se puede eliminar el permiso porque está asignado a uno o más roles.');
                $this->modalEliminar = false;
                $this->permisoAEliminar = null;
                return;
            }

            $permission->delete();
            session()->flash('mensaje', 'Permiso eliminado exitosamente.');
            
            $this->modalEliminar = false;
            $this->permisoAEliminar = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el permiso: ' . $e->getMessage());
            $this->modalEliminar = false;
            $this->permisoAEliminar = null;
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
        $this->permission_id = null;
        $this->name = '';
        $this->guard_name = 'web';
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
        $permisos = Permission::query()
            ->withCount('roles')
            ->when($this->buscar, function ($query) {
                $query->where('name', 'ilike', '%' . $this->buscar . '%');
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.permisos.gestionar-permisos', [
            'permisos' => $permisos,
        ]);
    }
}
