<?php

namespace App\Livewire\Veterinarias;

use App\Models\Veterinaria;
use App\Models\VeterinariaTelefono;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarVeterinarias extends Component
{
    use WithPagination;

    // Propiedades del formulario

    public $veterinaria_id;

    public $nombre;

    public $responsable;

    public $email;

    public $direccion;

    public $estado = true;

    // Propiedades para teléfonos

    public $telefonos = [];

    public $nuevoTelefono = '';

    public $nuevoNombreContacto = '';

    public $esNuevoPrincipal = false;

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

            'telefonos' => 'required|array|min:1',

            'telefonos.*.telefono' => 'required|string|max:20',

            'telefonos.*.nombre_contacto' => 'nullable|string|max:100',

            'telefonos.*.es_principal' => 'boolean',

            'email' => 'required|email|max:255',

            'direccion' => 'required|string|max:500',

            'estado' => 'boolean',

        ];

    }

    // Mensajes de validación personalizados

    protected $messages = [

        'nombre.required' => 'El nombre es obligatorio.',

        'responsable.required' => 'El responsable es obligatorio.',

        'telefonos.required' => 'Debe agregar al menos un teléfono.',

        'telefonos.*.telefono.required' => 'El teléfono es obligatorio.',

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

        $this->veterinariaAVer = Veterinaria::with('telefonos')->findOrFail($id);

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

        $veterinaria = Veterinaria::with('telefonos')->findOrFail($id);

        $this->veterinaria_id = $veterinaria->id;

        $this->nombre = $veterinaria->nombre;

        $this->responsable = $veterinaria->responsable;

        $this->email = $veterinaria->email;

        $this->direccion = $veterinaria->direccion;

        $this->estado = $veterinaria->estado;

        // Cargar teléfonos con su información completa

        $this->telefonos = $veterinaria->telefonos

            ->map(fn ($t) => [

                'id' => $t->id,

                'telefono' => $t->telefono,

                'nombre_contacto' => $t->nombre_contacto ?? '',

                'es_principal' => $t->es_principal,

            ])

            ->toArray();

        $this->modoEdicion = true;

        $this->modalAbierto = true;

    }

    /**
     * Guardar veterinaria (crear o actualizar)
     */
    public function guardar()
    {

        $this->normalizarTelefonosPrincipales();

        $this->validate();

        try {

            if ($this->modoEdicion) {

                $veterinaria = Veterinaria::findOrFail($this->veterinaria_id);

                $veterinaria->update([

                    'nombre' => $this->nombre,

                    'responsable' => $this->responsable,

                    'email' => $this->email,

                    'direccion' => $this->direccion,

                    'estado' => $this->estado,

                ]);

                // Actualizar teléfonos

                // Primero, eliminar teléfonos que no están en el formulario

                $idsTeléfonosFormulario = array_filter(

                    array_map(fn ($t) => $t['id'] ?? null, $this->telefonos)

                );

                $veterinaria->telefonos()

                    ->whereNotIn('id', $idsTeléfonosFormulario)

                    ->delete();

                // Luego, crear o actualizar teléfonos

                foreach ($this->telefonos as $index => $telefonoData) {

                    if (! empty($telefonoData['telefono'])) {

                        if (isset($telefonoData['id'])) {

                            // Actualizar teléfono existente

                            VeterinariaTelefono::findOrFail($telefonoData['id'])->update([

                                'telefono' => $telefonoData['telefono'],

                                'nombre_contacto' => $telefonoData['nombre_contacto'],

                                'es_principal' => $telefonoData['es_principal'],

                            ]);

                        } else {

                            // Crear nuevo teléfono

                            $veterinaria->telefonos()->create([

                                'telefono' => $telefonoData['telefono'],

                                'nombre_contacto' => $telefonoData['nombre_contacto'],

                                'es_principal' => $telefonoData['es_principal'],

                            ]);

                        }

                    }

                }

                session()->flash('mensaje', 'Veterinaria actualizada exitosamente.');

            } else {

                $veterinaria = Veterinaria::create([

                    'nombre' => $this->nombre,

                    'responsable' => $this->responsable,

                    'email' => $this->email,

                    'direccion' => $this->direccion,

                    'estado' => $this->estado,

                ]);

                // Crear teléfonos iniciales

                foreach ($this->telefonos as $telefonoData) {

                    if (! empty($telefonoData['telefono'])) {

                        $veterinaria->telefonos()->create([

                            'telefono' => $telefonoData['telefono'],

                            'nombre_contacto' => $telefonoData['nombre_contacto'],

                            'es_principal' => $telefonoData['es_principal'],

                        ]);

                    }

                }

                session()->flash('mensaje', 'Veterinaria creada exitosamente.');

            }

            $this->cerrarModal();

        } catch (\Exception $e) {

            session()->flash('error', 'Error al guardar la veterinaria: '.$e->getMessage());

        }

    }

    /**
     * Agregar un nuevo teléfono a la lista
     */
    public function agregarTelefono()
    {

        if (! empty($this->nuevoTelefono)) {

            $yaExistePrincipal = collect($this->telefonos)
                ->contains(fn ($telefono) => (bool) ($telefono['es_principal'] ?? false));

            $nuevoEsPrincipal = $yaExistePrincipal ? (bool) $this->esNuevoPrincipal : true;

            $this->telefonos[] = [

                'telefono' => $this->nuevoTelefono,

                'nombre_contacto' => $this->nuevoNombreContacto,

                'es_principal' => $nuevoEsPrincipal,

            ];

            // Si es principal, desmarcar los otros

            if ($nuevoEsPrincipal) {

                foreach ($this->telefonos as $i => &$t) {

                    if ($i !== count($this->telefonos) - 1) {

                        $t['es_principal'] = false;

                    }

                }

            }

            $this->nuevoTelefono = '';

            $this->nuevoNombreContacto = '';

            $this->esNuevoPrincipal = false;

        }

    }

    /**
     * Eliminar un teléfono de la lista
     */
    public function eliminarTelefono($indice)
    {

        if (isset($this->telefonos[$indice]['id'])) {

            VeterinariaTelefono::destroy($this->telefonos[$indice]['id']);

        }

        unset($this->telefonos[$indice]);

        $this->telefonos = array_values($this->telefonos);

    }

    /**
     * Hacer que un teléfono sea el principal
     */
    public function hacerPrincipal($indice)
    {

        foreach ($this->telefonos as &$t) {

            $t['es_principal'] = false;

        }

        $this->telefonos[$indice]['es_principal'] = true;

    }

    /**
     * Garantiza que solo exista un telefono principal en el formulario.
     */
    private function normalizarTelefonosPrincipales(): void
    {

        $principalAsignado = false;

        foreach ($this->telefonos as $indice => $telefono) {

            $esPrincipal = (bool) ($telefono['es_principal'] ?? false);

            if ($esPrincipal && ! $principalAsignado) {

                $this->telefonos[$indice]['es_principal'] = true;

                $principalAsignado = true;

                continue;

            }

            $this->telefonos[$indice]['es_principal'] = false;

        }

        if (! $principalAsignado && count($this->telefonos) > 0) {

            $this->telefonos[0]['es_principal'] = true;

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

            if (! $this->veterinariaAEliminar) {

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

            session()->flash('error', 'Error al eliminar la veterinaria: '.$e->getMessage());

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

        if (! $value) {

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

            if (! $this->veterinariaACambiar) {

                return;

            }

            $veterinaria = Veterinaria::findOrFail($this->veterinariaACambiar);

            $nuevoEstado = ! $veterinaria->estado;

            $veterinaria->estado = $nuevoEstado;

            $veterinaria->save();

            $mensaje = $nuevoEstado ? 'Veterinaria activada exitosamente.' : 'Veterinaria desactivada exitosamente.';

            session()->flash('mensaje', $mensaje);

            $this->modalCambiarEstado = false;

            $this->veterinariaACambiar = null;

            $this->estadoActual = null;

        } catch (\Exception $e) {

            session()->flash('error', 'Error al cambiar el estado: '.$e->getMessage());

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

        $this->telefonos = [[

            'telefono' => '',

            'nombre_contacto' => '',

            'es_principal' => true,

        ]];

        $this->nuevoTelefono = '';

        $this->nuevoNombreContacto = '';

        $this->esNuevoPrincipal = false;

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

            ->with('telefonos')

            ->when($this->buscar, function ($query) {

                $buscar = '%'.$this->buscar.'%';

                $query->where(function ($q) use ($buscar) {

                    $q->whereRaw('unaccent(nombre) ilike unaccent(?)', [$buscar])

                        ->orWhereRaw('unaccent(responsable) ilike unaccent(?)', [$buscar])

                        ->orWhereRaw('unaccent(email) ilike unaccent(?)', [$buscar])

                        ->orWhereRaw('unaccent(direccion) ilike unaccent(?)', [$buscar]);

                });

            })

            ->orderBy($this->sortBy, $this->sortDirection)

            ->paginate(10);

        return view('livewire.veterinarias.gestionar-veterinarias', [

            'veterinarias' => $veterinarias,

        ]);

    }
}
