<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class GestionarRoles extends Component
{
    use WithPagination;

    // Propiedades del formulario
    public $role_id;
    public $name;
    public $permissions = [];
    public $allPermissions = [];

    // Propiedades de control
    public $modalAbierto = false;
    public $modalEliminar = false;
    public $modalVer = false;
    public $roleAEliminar = null;
    public $roleAVer = null;
    public $buscar = '';
    public $modoEdicion = false;

    // Propiedades de ordenamiento
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    public function mount()
    {
        $this->allPermissions = Permission::all();
    }

    // Reglas de validación
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . ($this->role_id ?? 'NULL'),
            'permissions' => 'array',
        ];
    }

    // Mensajes de validación personalizados
    protected $messages = [
        'name.required' => 'El nombre del rol es obligatorio.',
        'name.unique' => 'Ya existe un rol con este nombre.',
    ];

    /**
     * Abrir modal para crear nuevo rol
     */
    public function crear()
    {
        $this->resetearFormulario();
        $this->modoEdicion = false;
        $this->modalAbierto = true;
    }

    /**
     * Abrir modal para ver detalles del rol
     */
    public function ver($id)
    {
        $this->roleAVer = Role::with('permissions')->findOrFail($id);
        $this->modalVer = true;
    }

    /**
     * Cerrar modal de ver
     */
    public function cerrarModalVer()
    {
        $this->modalVer = false;
        $this->roleAVer = null;
    }

    /**
     * Abrir modal para editar rol existente
     */
    public function editar($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        
        $this->role_id = $role->id;
        $this->name = $role->name;
        $this->permissions = $role->permissions->pluck('id')->toArray();
        
        $this->modoEdicion = true;
        $this->modalAbierto = true;
    }

    /**
     * Guardar rol (crear o actualizar)
     */
    public function guardar()
    {
        $this->validate();

        try {
            if ($this->modoEdicion) {
                $role = Role::findOrFail($this->role_id);
                $role->update([
                    'name' => $this->name,
                ]);
                // Sincronizar permisos usando los modelos de Permission
                $permissionsToSync = Permission::whereIn('id', $this->permissions)->get();
                $role->syncPermissions($permissionsToSync);

                session()->flash('mensaje', 'Rol actualizado exitosamente.');
            } else {
                $role = Role::create([
                    'name' => $this->name,
                ]);
                // Sincronizar permisos usando los modelos de Permission
                $permissionsToSync = Permission::whereIn('id', $this->permissions)->get();
                $role->syncPermissions($permissionsToSync);

                session()->flash('mensaje', 'Rol creado exitosamente.');
            }

            $this->cerrarModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar el rol: ' . $e->getMessage());
        }
    }

    /**
     * Abrir modal de confirmación para eliminar
     */
    public function confirmarEliminar($id)
    {
        $this->roleAEliminar = $id;
        $this->modalEliminar = true;
    }

    /**
     * Cancelar eliminación
     */
    public function cancelarEliminar()
    {
        $this->modalEliminar = false;
        $this->roleAEliminar = null;
    }

    /**
     * Eliminar rol
     */
    public function eliminar()
    {
        try {
            if (!$this->roleAEliminar) {
                return;
            }

            $role = Role::findOrFail($this->roleAEliminar);
            
            // Verificar si tiene usuarios asignados
            if ($role->users()->count() > 0) {
                session()->flash('error', 'No se puede eliminar el rol porque tiene usuarios asignados.');
                $this->modalEliminar = false;
                $this->roleAEliminar = null;
                return;
            }

            $role->delete();
            session()->flash('mensaje', 'Rol eliminado exitosamente.');
            
            $this->modalEliminar = false;
            $this->roleAEliminar = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el rol: ' . $e->getMessage());
            $this->modalEliminar = false;
            $this->roleAEliminar = null;
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
        $this->role_id = null;
        $this->name = '';
        $this->permissions = [];
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
        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->when($this->buscar, function ($query) {
                $query->where('name', 'like', '%' . $this->buscar . '%');
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.roles.gestionar-roles', [
            'roles' => $roles,
        ]);
    }
}
