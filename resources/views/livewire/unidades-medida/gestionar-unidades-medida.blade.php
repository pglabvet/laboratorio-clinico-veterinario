<div>
    {{-- Mensajes toast en esquina superior derecha --}}
    <x-toast type="success" :message="session('mensaje')" />
    <x-toast type="error" :message="session('error')" />

    {{-- Header de la página --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Gestión de Unidades de Medida</flux:heading>
        <flux:subheading>Administra las unidades de medida utilizadas en los insumos del laboratorio</flux:subheading>
    </div>

    {{-- Barra de acciones --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        {{-- Búsqueda --}}
        <div class="w-full sm:w-96">
            <flux:input 
                wire:model.live.debounce.300ms="buscar"
                icon="magnifying-glass"
                placeholder="Buscar unidades de medida..."
                class="w-full"
            />
        </div>

        {{-- Botón crear --}}
        <flux:button 
            wire:click="crear"
            icon="plus"
            variant="primary"
        >
            Nueva Unidad de Medida
        </flux:button>
    </div>

    {{-- Tabla de unidades de medida --}}
    <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-md ring-1 ring-neutral-200/50 dark:border-neutral-700 dark:bg-neutral-800 dark:ring-neutral-700/50">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            <button wire:click="ordenarPor('nombre')" class="flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                <span>Nombre</span>
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
                            <button wire:click="ordenarPor('abreviatura')" class="flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                <span>Abreviatura</span>
                                @if($sortBy === 'abreviatura')
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
                            <button wire:click="ordenarPor('estado')" class="flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                <span>Estado</span>
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
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                    @forelse ($unidades as $unidad)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50" wire:key="unidad-{{ $unidad->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                {{ $unidad->nombre }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ $unidad->abreviatura }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <button type="button" wire:click="confirmarCambiarEstado({{ $unidad->id }})" class="cursor-pointer group outline-none focus:outline-none">
                                    <div class="pointer-events-none">
                                        <flux:switch 
                                            :checked="$unidad->estado"
                                            wire:key="switch-{{ $unidad->id }}-{{ $unidad->estado ? 'active' : 'inactive' }}"
                                        />
                                    </div>
                                </button>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    {{-- Botón editar --}}
                                    <flux:button
                                        wire:click="editar({{ $unidad->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil"
                                        color="cyan"
                                        title="Editar"
                                    />
                                    
                                    {{-- Botón eliminar --}}
                                    <flux:button
                                        wire:click="confirmarEliminar({{ $unidad->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="trash"
                                        color="red"
                                        title="Eliminar"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="mb-3 h-12 w-12 text-neutral-400 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                    </svg>
                                    <flux:heading size="lg" class="mb-1">No hay unidades de medida</flux:heading>
                                    <flux:subheading>
                                        @if ($buscar)
                                            No se encontraron unidades de medida con el término "{{ $buscar }}"
                                        @else
                                            Comienza creando una nueva unidad de medida
                                        @endif
                                    </flux:subheading>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($unidades->hasPages())
            <div class="border-t border-neutral-200 bg-neutral-50 px-6 py-3 dark:border-neutral-700 dark:bg-neutral-900">
                {{ $unidades->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Crear/Editar --}}
    <flux:modal wire:model="modalAbierto" class="md:w-96">
        <form wire:submit.prevent="guardar">
            <div>
                <flux:heading size="lg">{{ $modoEdicion ? 'Editar' : 'Nueva' }} Unidad de Medida</flux:heading>
                <flux:subheading class="mb-4">
                    {{ $modoEdicion ? 'Modifica los datos de la unidad de medida' : 'Completa los datos para crear una nueva unidad de medida' }}
                </flux:subheading>
            </div>

            <flux:input.group label="Nombre" :error="$errors->first('nombre')" required>
                <flux:input wire:model="nombre" placeholder="Ej: Mililitros" />
            </flux:input.group>

            <flux:input.group label="Abreviatura" :error="$errors->first('abreviatura')" required>
                <flux:input wire:model="abreviatura" placeholder="Ej: ml" />
            </flux:input.group>

            <flux:checkbox wire:model="estado" label="Activo" />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button type="button" wire:click="cerrarModal" variant="ghost">Cancelar</flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $modoEdicion ? 'Actualizar' : 'Guardar' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Modal Cambiar Estado --}}
    <flux:modal wire:model="modalCambiarEstado" class="md:w-96">
        <div>
            <flux:heading size="lg">Cambiar Estado</flux:heading>
            <flux:subheading class="mb-4">
                ¿Estás seguro de que deseas {{ $estadoActual ? 'desactivar' : 'activar' }} esta unidad de medida?
            </flux:subheading>
        </div>

        @if($estadoActual && $unidadACambiar)
            <div class="mb-4 rounded-lg bg-yellow-50 p-3 dark:bg-yellow-900/20">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    <strong>Nota:</strong> Al desactivar esta unidad de medida, no podrá ser seleccionada en nuevos insumos.
                </p>
            </div>
        @endif

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button type="button" wire:click="cerrarModalCambiarEstado" variant="ghost">Cancelar</flux:button>
            <flux:button type="button" wire:click="cambiarEstado" variant="primary">
                Confirmar
            </flux:button>
        </div>
    </flux:modal>

    {{-- Modal Eliminar --}}
    <flux:modal wire:model="modalEliminar" class="md:w-96">
        <div>
            <flux:heading size="lg">Eliminar Unidad de Medida</flux:heading>
            <flux:subheading class="mb-4">
                ¿Estás seguro de que deseas eliminar esta unidad de medida?
            </flux:subheading>
        </div>

        <div class="mb-4 rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
            <p class="text-sm text-red-800 dark:text-red-200">
                <strong>Advertencia:</strong> Esta acción no se puede deshacer. Si hay insumos usando esta unidad, no se podrá eliminar.
            </p>
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button type="button" wire:click="cerrarModalEliminar" variant="ghost">Cancelar</flux:button>
            <flux:button type="button" wire:click="eliminar" variant="danger">
                Eliminar
            </flux:button>
        </div>
    </flux:modal>

    {{-- Modal Eliminar --}}
    <flux:modal wire:model="modalEliminar" class="md:w-96">
        <div>
            <flux:heading size="lg">Eliminar Unidad de Medida</flux:heading>
            <flux:subheading class="mb-4">
                ¿Estás seguro de que deseas eliminar esta unidad de medida?
            </flux:subheading>
        </div>

        <div class="mb-4 rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
            <p class="text-sm text-red-800 dark:text-red-200">
                <strong>Advertencia:</strong> Esta acción no se puede deshacer. Si hay insumos usando esta unidad, no se podrá eliminar.
            </p>
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button type="button" wire:click="cerrarModalEliminar" variant="ghost">Cancelar</flux:button>
            <flux:button type="button" wire:click="eliminar" variant="danger">
                Eliminar
            </flux:button>
        </div>
    </flux:modal>
</div>
