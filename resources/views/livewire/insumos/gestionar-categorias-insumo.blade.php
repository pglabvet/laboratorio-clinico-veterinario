<div>
    {{-- Mensajes toast en esquina superior derecha --}}
    <x-toast type="success" :message="session('mensaje')" />
    <x-toast type="error" :message="session('error')" />

    {{-- Header de la página --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Gestión de Categorías de Insumos</flux:heading>
        <flux:subheading>Administra las categorías para organizar los insumos del laboratorio</flux:subheading>
    </div>

    {{-- Barra de acciones --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        {{-- Búsqueda --}}
        <div class="w-full sm:w-96">
            <flux:input 
                wire:model.live.debounce.300ms="buscar"
                icon="magnifying-glass"
                placeholder="Buscar categorías..."
                class="w-full"
            />
        </div>

        {{-- Botón crear --}}
        @can('crear-categorias-insumo')
        <flux:button 
            wire:click="crear"
            icon="plus"
            variant="primary"
        >
            Nueva Categoría
        </flux:button>
        @endcan
    </div>

    {{-- Tabla de categorías --}}
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
                            DESCRIPCIÓN
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            ESTADO
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            ACCIONES
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                    @forelse ($categorias as $categoria)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50" wire:key="categoria-{{ $categoria->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                {{ $categoria->nombre }}
                            </td>
                            <td class="px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                @if($categoria->descripcion)
                                    <span class="line-clamp-2">{{ $categoria->descripcion }}</span>
                                @else
                                    <span class="text-neutral-400 italic">Sin descripción</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                @can('editar-categorias-insumo')
                                <button type="button" wire:click="confirmarCambiarEstado({{ $categoria->id }})" class="cursor-pointer group outline-none focus:outline-none">
                                    <div class="pointer-events-none">
                                        <flux:switch 
                                            :checked="$categoria->estado"
                                            wire:key="switch-{{ $categoria->id }}-{{ $categoria->estado ? 'active' : 'inactive' }}"
                                        />
                                    </div>
                                </button>
                                @else
                                <flux:badge :color="$categoria->estado ? 'green' : 'red'" size="sm">{{ $categoria->estado ? 'Activa' : 'Inactiva' }}</flux:badge>
                                @endcan
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    @can('editar-categorias-insumo')
                                    <flux:button
                                        wire:click="editar({{ $categoria->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil"
                                        color="cyan"
                                        title="Editar"
                                    />
                                    @endcan
                                    
                                    @can('eliminar-categorias-insumo')
                                    <flux:button
                                        wire:click="confirmarEliminar({{ $categoria->id }})"
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
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="mb-3 h-12 w-12 text-neutral-400 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                                    </svg>
                                    <flux:heading size="lg" class="mb-1">No hay categorías</flux:heading>
                                    <flux:subheading>
                                        @if ($buscar)
                                            No se encontraron categorías con el término "{{ $buscar }}"
                                        @else
                                            Comienza creando una nueva categoría
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
        @if($categorias->hasPages())
            <div class="border-t border-neutral-200 bg-neutral-50 px-6 py-3 dark:border-neutral-700 dark:bg-neutral-900">
                {{ $categorias->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Crear/Editar --}}
    <flux:modal wire:model="modalAbierto" class="md:w-[600px]">
        <form wire:submit.prevent="guardar">
            <div>
                <flux:heading size="lg">{{ $modoEdicion ? 'Editar' : 'Nueva' }} Categoría</flux:heading>
                <flux:subheading class="mb-4">
                    {{ $modoEdicion ? 'Modifica los datos de la categoría' : 'Completa los datos para crear una nueva categoría' }}
                </flux:subheading>
            </div>

            <div class="space-y-4">
                <flux:input.group label="Nombre de la categoría" :error="$errors->first('nombre')" required>
                    <flux:input wire:model="nombre" placeholder="Ej: Reactivos químicos" />
                </flux:input.group>

                <flux:input.group label="Descripción" :error="$errors->first('descripcion')">
                    <flux:textarea 
                        wire:model="descripcion" 
                        placeholder="Descripción opcional de la categoría..."
                        rows="3"
                    />
                </flux:input.group>

                <flux:checkbox wire:model="estado" label="Activo" />
            </div>

            <div class="flex gap-2 mt-6">
                <flux:spacer />
                <flux:button type="button" wire:click="cerrarModal" variant="outline" class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950">Cancelar</flux:button>
                @can('guardar-categoria-insumo')
                <flux:button type="submit" variant="primary">
                    {{ $modoEdicion ? 'Actualizar' : 'Guardar' }}
                </flux:button>
                @endcan
            </div>
        </form>
    </flux:modal>

    {{-- Modal Cambiar Estado --}}
    <flux:modal wire:model="modalCambiarEstado" class="md:w-96">
        <div>
            <flux:heading size="lg">Cambiar Estado</flux:heading>
            <flux:subheading class="mb-4">
                ¿Estás seguro de que deseas {{ $estadoActual ? 'desactivar' : 'activar' }} esta categoría?
            </flux:subheading>
        </div>

        @if($estadoActual && $categoriaACambiar)
            <div class="mb-4 rounded-lg bg-yellow-50 p-3 dark:bg-yellow-900/20">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    <strong>Nota:</strong> Al desactivar esta categoría, no aparecerá en el formulario de insumos.
                </p>
            </div>
        @endif

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button type="button" wire:click="cerrarModalCambiarEstado" variant="outline" class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950">Cancelar</flux:button>
            <flux:button type="button" wire:click="cambiarEstado" variant="primary">
                Confirmar
            </flux:button>
        </div>
    </flux:modal>

    {{-- Modal Eliminar --}}
    <flux:modal wire:model="modalEliminar" class="md:w-96">
        <div>
            <flux:heading size="lg">Eliminar Categoría</flux:heading>
            <flux:subheading class="mb-4">
                ¿Estás seguro de que deseas eliminar esta categoría?
            </flux:subheading>
        </div>

        <div class="mb-4 rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
            <p class="text-sm text-red-800 dark:text-red-200">
                <strong>Advertencia:</strong> Esta acción no se puede deshacer. Los insumos que usen esta categoría quedarán sin categoría asignada.
            </p>
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button type="button" wire:click="cerrarModalEliminar" variant="outline" class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950">Cancelar</flux:button>
            <flux:button type="button" wire:click="eliminar" variant="danger">
                Eliminar
            </flux:button>
        </div>
    </flux:modal>
</div>
