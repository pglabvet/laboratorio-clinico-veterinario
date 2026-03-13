<div>
    <x-toast type="success" :message="session('mensaje')" />
    <x-toast type="error" :message="session('error')" />

    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Gestion de Veterinarias</flux:heading>
        <flux:subheading>Administra las veterinarias del sistema</flux:subheading>
    </div>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <div class="w-full sm:w-96">
                <flux:input
                    wire:model.live.debounce.300ms="buscar"
                    icon="magnifying-glass"
                    placeholder="Buscar veterinarias..."
                    class="w-full"
                />
            </div>

            @if($buscar)
                <div class="flex items-center">
                    <flux:button wire:click="limpiarBuscar" variant="ghost" icon="x-mark">
                        Limpiar
                    </flux:button>
                </div>
            @endif
        </div>

        @can('crear-veterinarias')
            <flux:button wire:click="crear" icon="plus" variant="primary">
                Nueva Veterinaria
            </flux:button>
        @endcan
    </div>

    <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-md ring-1 ring-neutral-200/50 dark:border-neutral-700 dark:bg-neutral-800 dark:ring-neutral-700/50">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            <button wire:click="ordenarPor('nombre')" class="flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                <span>NOMBRE</span>
                                @if($sortBy === 'nombre')
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            <button wire:click="ordenarPor('responsable')" class="flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                <span>RESPONSABLE</span>
                                @if($sortBy === 'responsable')
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            <button wire:click="ordenarPor('email')" class="flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                <span>EMAIL</span>
                                @if($sortBy === 'email')
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            TELEFONO PRINCIPAL
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            <button wire:click="ordenarPor('estado')" class="flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                <span>ESTADO</span>
                                @if($sortBy === 'estado')
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            ACCIONES
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                    @forelse ($veterinarias as $veterinaria)
                        @php
                            $telefonoPrincipal = $veterinaria->telefonos->firstWhere('es_principal', true) ?? $veterinaria->telefonos->first();
                        @endphp
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50" wire:key="veterinaria-{{ $veterinaria->id }}-{{ $veterinaria->estado ? 'activa' : 'inactiva' }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                {{ $veterinaria->nombre }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $veterinaria->responsable }}
                            </td>
                            <td class="px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ $veterinaria->email }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ $telefonoPrincipal?->telefono ?? 'Sin telefono' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                @can('editar-veterinarias')
                                    <button type="button" wire:click="confirmarCambiarEstado({{ $veterinaria->id }})" class="cursor-pointer group outline-none focus:outline-none">
                                        <div class="pointer-events-none">
                                            <flux:switch :checked="$veterinaria->estado" wire:key="switch-veterinaria-{{ $veterinaria->id }}-{{ $veterinaria->estado ? 'active' : 'inactive' }}" />
                                        </div>
                                    </button>
                                @else
                                    <flux:badge :color="$veterinaria->estado ? 'green' : 'red'" size="sm">
                                        {{ $veterinaria->estado ? 'Activa' : 'Inactiva' }}
                                    </flux:badge>
                                @endcan
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    @can('mostrar-detalle-veterinaria')
                                        <flux:button
                                            wire:click="ver({{ $veterinaria->id }})"
                                            variant="ghost"
                                            size="sm"
                                            icon="eye"
                                            color="neutral"
                                            title="Ver detalles"
                                        />
                                    @endcan
                                    @can('editar-veterinarias')
                                        <flux:button
                                            wire:click="editar({{ $veterinaria->id }})"
                                            variant="ghost"
                                            size="sm"
                                            icon="pencil"
                                            color="cyan"
                                            title="Editar"
                                        />
                                    @endcan
                                    @can('eliminar-veterinarias')
                                        <flux:button
                                            wire:click="confirmarEliminar({{ $veterinaria->id }})"
                                            variant="ghost"
                                            size="sm"
                                            icon="trash"
                                            color="red"
                                            title="Eliminar"
                                        />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <flux:icon.building-office-2 class="mb-3 h-12 w-12 text-neutral-400 dark:text-neutral-600" />
                                    <flux:heading size="lg" class="mb-1">No hay veterinarias</flux:heading>
                                    <flux:subheading>
                                        @if ($buscar)
                                            No se encontraron veterinarias con el termino "{{ $buscar }}"
                                        @else
                                            Comienza creando tu primera veterinaria
                                        @endif
                                    </flux:subheading>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($veterinarias->hasPages())
            <div class="border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-700 dark:bg-neutral-900">
                {{ $veterinarias->links() }}
            </div>
        @endif
    </div>

    <flux:modal wire:model="modalAbierto" class="w-full max-w-3xl">
        <form wire:submit.prevent="guardar">
            <flux:heading size="lg" class="mb-2">
                {{ $modoEdicion ? 'Editar Veterinaria' : 'Nueva Veterinaria' }}
            </flux:heading>
            <flux:subheading class="mb-6">
                {{ $modoEdicion ? 'Actualiza la informacion de la veterinaria' : 'Ingresa los datos de la nueva veterinaria' }}
            </flux:subheading>

            <div class="space-y-6">
                <flux:input wire:model="nombre" label="Nombre de la Veterinaria" required :error="$errors->first('nombre')" />
                <flux:input wire:model="responsable" label="Responsable" required :error="$errors->first('responsable')" />
                <flux:input wire:model="email" label="Email" type="email" required :error="$errors->first('email')" />
                <flux:textarea wire:model="direccion" label="Direccion" rows="3" required :error="$errors->first('direccion')" />

                <div class="space-y-3 rounded-lg border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-900/50">
                    <div>
                        <h4 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Telefonos</h4>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400">Agrega numero y nombre del contacto.</p>
                    </div>

                    <div class="space-y-2">
                        @foreach ($telefonos as $indice => $tel)
                            <div class="rounded-lg bg-white p-3 dark:bg-neutral-800" wire:key="telefono-edit-{{ $indice }}-{{ $tel['id'] ?? 'new' }}">
                                <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                                    <flux:input wire:model="telefonos.{{ $indice }}.telefono" placeholder="Telefono" :error="$errors->first('telefonos.'.$indice.'.telefono')" />
                                    <flux:input wire:model="telefonos.{{ $indice }}.nombre_contacto" placeholder="Nombre del contacto" />
                                    <div class="flex items-center gap-2">
                                        @if (!($tel['es_principal'] ?? false))
                                            <flux:button type="button" wire:click="hacerPrincipal({{ $indice }})" size="sm" variant="ghost">Principal</flux:button>
                                        @else
                                            <flux:badge color="blue" size="sm">Principal</flux:badge>
                                        @endif

                                        @if (count($telefonos) > 1)
                                            <flux:button type="button" wire:click="eliminarTelefono({{ $indice }})" size="sm" variant="ghost" icon="trash" color="red" />
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 gap-2 md:grid-cols-4">
                        <div class="md:col-span-2">
                            <flux:input wire:model="nuevoTelefono" placeholder="Nuevo telefono" />
                        </div>
                        <div>
                            <flux:input wire:model="nuevoNombreContacto" placeholder="Nombre" />
                        </div>
                        <div>
                            <flux:button type="button" wire:click="agregarTelefono" icon="plus" variant="primary">Agregar</flux:button>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-neutral-200 bg-neutral-50 p-3 dark:border-neutral-700 dark:bg-neutral-900/50">
                    <flux:checkbox
                        wire:model="estado"
                        label="Veterinaria activa"
                        description="Define si la veterinaria estara disponible en el sistema"
                    />
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <flux:button type="button" wire:click="cerrarModal" variant="outline" class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950">
                    Cancelar
                </flux:button>
                @can('guardar-veterinaria')
                    <flux:button type="submit" variant="primary">{{ $modoEdicion ? 'Actualizar' : 'Guardar' }}</flux:button>
                @endcan
            </div>
        </form>
    </flux:modal>

    <flux:modal wire:model="modalVer" class="w-full max-w-2xl">
        @if($veterinariaAVer)
            @php
                $estadoBadge = [
                    true => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
                    false => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
                ];
            @endphp
            <div class="space-y-5">
                <div class="border-b border-neutral-200 pb-4 dark:border-neutral-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-semibold text-neutral-900 dark:text-neutral-100">{{ $veterinariaAVer->nombre }}</h2>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estadoBadge[$veterinariaAVer->estado] ?? 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/20 dark:text-neutral-400' }}">
                                {{ $veterinariaAVer->estado ? 'Activa' : 'Inactiva' }}
                            </span>
                        </div>
                    </div>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Registro desde: <span class="font-medium">{{ $veterinariaAVer->created_at->format('d/m/Y') }}</span>
                    </p>
                </div>

                <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white divide-y divide-neutral-200 dark:border-neutral-700 dark:bg-neutral-800/50 dark:divide-neutral-700">
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <flux:icon.user class="mt-0.5 h-5 w-5 shrink-0 text-indigo-500 dark:text-indigo-400" />
                        <div class="min-w-0 flex-1">
                            <p class="mb-0.5 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Responsable</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $veterinariaAVer->responsable }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <flux:icon.envelope class="mt-0.5 h-5 w-5 shrink-0 text-blue-500 dark:text-blue-400" />
                        <div class="min-w-0 flex-1">
                            <p class="mb-0.5 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Email</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $veterinariaAVer->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <flux:icon.phone class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500 dark:text-emerald-400" />
                        <div class="min-w-0 flex-1">
                            <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Telefonos</p>
                            <div class="flex flex-col gap-1.5">
                                @forelse ($veterinariaAVer->telefonos as $tel)
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $tel->telefono }}</p>
                                        @if($tel->es_principal)
                                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">Principal</span>
                                        @endif
                                        @if($tel->nombre_contacto)
                                            <span class="text-xs text-neutral-600 dark:text-neutral-400">({{ $tel->nombre_contacto }})</span>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Sin telefonos registrados</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white divide-y divide-neutral-200 dark:border-neutral-700 dark:bg-neutral-800/50 dark:divide-neutral-700">
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <flux:icon.map-pin class="mt-0.5 h-5 w-5 shrink-0 text-violet-500 dark:text-violet-400" />
                        <div class="min-w-0 flex-1">
                            <p class="mb-0.5 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Direccion</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $veterinariaAVer->direccion }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <flux:icon.calendar-days class="mt-0.5 h-5 w-5 shrink-0 text-amber-500 dark:text-amber-400" />
                        <div class="min-w-0 flex-1">
                            <p class="mb-0.5 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Ultima actualizacion</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $veterinariaAVer->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <flux:button type="button" wire:click="cerrarModalVer" variant="outline" class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950">
                        Cerrar
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    <flux:modal wire:model="modalEliminar" class="w-full max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">Eliminar Veterinaria</flux:heading>
            <flux:subheading>Esta accion no se puede deshacer.</flux:subheading>
            <div class="flex justify-end gap-2">
                <flux:button type="button" wire:click="cancelarEliminar" variant="outline" class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950">Cancelar</flux:button>
                <flux:button type="button" wire:click="eliminar" variant="danger">Eliminar</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model="modalCambiarEstado" class="w-full max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">Cambiar Estado</flux:heading>
            <flux:subheading>Se cambiara el estado de la veterinaria seleccionada.</flux:subheading>
            <div class="flex justify-end gap-2">
                <flux:button type="button" wire:click="cancelarCambiarEstado" variant="outline" class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950">Cancelar</flux:button>
                <flux:button type="button" wire:click="cambiarEstado" variant="primary">Confirmar</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
