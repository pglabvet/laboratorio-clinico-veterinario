<?php

namespace App\Livewire\Usuarios;

use App\Models\User;
use App\Models\Sucursal;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class GestionarUsuarios extends Component
{
    use WithPagination;

    public $search = '';
    public $filtroSucursal = '';
    public $modalAbierto = false;
    public $modoEdicion = false;
    public $modalEliminar = false;
    public $usuarioAEliminar = null;
    
    // Datos del formulario
    public $usuarioId;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $rol_id;
    public $sucursal_id;
    public $estado = true;

    protected function rules()
    {
        $userId = $this->usuarioId ?: 'NULL';
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userId,
            'rol_id' => 'required|exists:roles,id',
            'sucursal_id' => 'nullable|exists:sucursales,id',
            'estado' => 'required|boolean',
        ];

        if (!$this->modoEdicion) {
            $rules['password'] = 'required|string|min:8|confirmed';
            $rules['password_confirmation'] = 'required';
        } elseif ($this->password) {
            $rules['password'] = 'string|min:8|confirmed';
            $rules['password_confirmation'] = 'required_with:password';
        }

        return $rules;
    }

    protected $messages = [
        'name.required' => 'El nombre es obligatorio',
        'email.required' => 'El email es obligatorio',
        'email.email' => 'El email debe ser válido',
        'email.unique' => 'Este email ya está registrado',
        'password.required' => 'La contraseña es obligatoria',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres',
        'password.confirmed' => 'Las contraseñas no coinciden',
        'rol_id.required' => 'Debe seleccionar un rol',
        'rol_id.exists' => 'El rol seleccionado no existe',
        'sucursal_id.exists' => 'La sucursal seleccionada no existe',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroSucursal()
    {
        $this->resetPage();
    }

    public function limpiarFiltro()
    {
        $this->search = '';
        $this->filtroSucursal = '';
        $this->resetPage();
    }

    public function abrirModal()
    {
        $this->resetearFormulario();
        $this->modalAbierto = true;
    }

    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->resetearFormulario();
    }

    public function resetearFormulario()
    {
        $this->reset([
            'usuarioId',
            'name',
            'email',
            'password',
            'password_confirmation',
            'rol_id',
            'sucursal_id',
            'estado',
            'modoEdicion'
        ]);
        $this->estado = true;
        $this->resetErrorBag();
    }

    public function editar($id)
    {
        $usuario = User::findOrFail($id);
        
        $this->usuarioId = $usuario->id;
        $this->name = $usuario->name;
        $this->email = $usuario->email;
        $this->rol_id = $usuario->roles->first()?->id;
        $this->sucursal_id = $usuario->sucursal_id;
        $this->estado = $usuario->estado ?? true;
        $this->modoEdicion = true;
        $this->modalAbierto = true;
    }

    public function guardar()
    {
        $this->validate();

        try {
            if ($this->modoEdicion) {
                $usuario = User::findOrFail($this->usuarioId);
                
                $usuario->update([
                    'name' => $this->name,
                    'email' => $this->email,
                    'sucursal_id' => $this->sucursal_id,
                    'estado' => $this->estado,
                ]);

                if ($this->password) {
                    $usuario->update(['password' => Hash::make($this->password)]);
                }

                $mensaje = 'Usuario actualizado correctamente';
            } else {
                $usuario = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                    'sucursal_id' => $this->sucursal_id,
                    'estado' => $this->estado,
                ]);

                $mensaje = 'Usuario creado correctamente';
            }

            // Asignar rol
            $rol = Role::findOrFail($this->rol_id);
            $usuario->syncRoles([$rol->name]);

            session()->flash('mensaje', $mensaje);
            $this->cerrarModal();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar el usuario: ' . $e->getMessage());
        }
    }

    public function confirmarEliminar($id)
    {
        $this->usuarioAEliminar = $id;
        $this->modalEliminar = true;
    }

    public function cancelarEliminar()
    {
        $this->modalEliminar = false;
        $this->usuarioAEliminar = null;
    }

    public function eliminar()
    {
        try {
            if (!$this->usuarioAEliminar) {
                return;
            }

            $usuario = User::findOrFail($this->usuarioAEliminar);
            
            // No permitir eliminar al usuario actual
            if ($usuario->id === auth()->id()) {
                session()->flash('error', 'No puedes eliminar tu propio usuario');
                $this->modalEliminar = false;
                $this->usuarioAEliminar = null;
                return;
            }

            $usuario->delete();
            session()->flash('mensaje', 'Usuario eliminado correctamente');
            $this->resetPage();
            
            $this->modalEliminar = false;
            $this->usuarioAEliminar = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el usuario: ' . $e->getMessage());
            $this->modalEliminar = false;
            $this->usuarioAEliminar = null;
        }
    }

    public function toggleEstado($id)
    {
        try {
            $usuario = User::findOrFail($id);
            
            if ($usuario->id === auth()->id()) {
                session()->flash('error', 'No puedes desactivar tu propio usuario');
                return;
            }

            $usuario->update([
                'estado' => !$usuario->estado
            ]);

            session()->flash('mensaje', 'Estado actualizado correctamente');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cambiar el estado: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $usuarios = User::with(['roles', 'sucursal'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filtroSucursal, function ($query) {
                $query->where('sucursal_id', $this->filtroSucursal);
            })
            ->orderBy('name')
            ->paginate(10);

        $roles = Role::all();
        $sucursales = Sucursal::orderBy('nombre')->get();

        return view('livewire.usuarios.gestionar-usuarios', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'sucursales' => $sucursales,
        ]);
    }
}
