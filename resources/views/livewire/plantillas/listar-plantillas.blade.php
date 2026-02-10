<div>
    {{-- Mensajes toast --}}
    <x-toast type="success" :message="session('success')" />
    <x-toast type="error" :message="session('error')" />

    {{-- Header de la página --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Gestión de Plantillas</flux:heading>
        <flux:subheading>Administra las plantillas de formularios del laboratorio</flux:subheading>
    </div>

    {{-- Barra de acciones --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        {{-- Búsqueda --}}
        <div class="w-full sm:w-96">
            <flux:input 
                wire:model.live.debounce.300ms="busqueda"
                icon="magnifying-glass"
                placeholder="Buscar plantillas..."
                class="w-full"
            />
        </div>

        {{-- Botón crear --}}
        @can('crear-plantillas')
        <flux:button 
            href="{{ route('plantillas.crear') }}"
            icon="plus"
            variant="primary"
        >
            Nueva Plantilla
        </flux:button>
        @endcan
    </div>

    {{-- Tabla de plantillas --}}
    <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-md ring-1 ring-neutral-200/50 dark:border-neutral-700 dark:bg-neutral-800 dark:ring-neutral-700/50">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Nombre
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Descripción
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Tipo de Análisis
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Componentes
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Creador
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Fecha
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Estado
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                    @forelse ($plantillas as $plantilla)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50" wire:key="plantilla-{{ $plantilla->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                {{ $plantilla->nombre }}
                                @if($plantilla->version > 1)
                                    <flux:badge size="sm" color="blue" class="ml-2">v{{ $plantilla->version }}</flux:badge>
                                @endif
                                @if($plantilla->contarAnalisis() > 0)
                                    <span class="ml-2 text-xs text-gray-500 dark:text-zinc-500">({{ $plantilla->contarAnalisis() }} análisis)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ Str::limit($plantilla->descripcion ?? 'Sin descripción', 60) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                @if($plantilla->tipoAnalisis)
                                    <flux:badge size="sm" color="cyan">
                                        {{ $plantilla->tipoAnalisis->nombre }}
                                    </flux:badge>
                                @else
                                    <span class="text-xs text-neutral-400">Sin asignar</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ count($plantilla->componentes ?? []) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ $plantilla->creador->name ?? 'N/A' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                <div>{{ $plantilla->created_at->format('d/m/Y') }}</div>
                                @if($plantilla->created_at != $plantilla->updated_at)
                                <div class="text-xs text-blue-600 dark:text-blue-400">
                                    Act. {{ $plantilla->updated_at->diffForHumans() }}
                                </div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                @can('editar-plantillas')
                                <button type="button" wire:click="toggleActivo({{ $plantilla->id }})" class="cursor-pointer group outline-none focus:outline-none">
                                    <div class="pointer-events-none">
                                        <flux:switch 
                                            :checked="$plantilla->activo"
                                            wire:key="switch-{{ $plantilla->id }}-{{ $plantilla->activo ? 'active' : 'inactive' }}"
                                        />
                                    </div>
                                </button>
                                @else
                                <flux:badge :color="$plantilla->activo ? 'green' : 'red'" size="sm">{{ $plantilla->activo ? 'Activa' : 'Inactiva' }}</flux:badge>
                                @endcan
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    {{-- Botón editar --}}
                                    @can('editar-plantillas')
                                    <flux:button
                                        href="{{ route('plantillas.editar', $plantilla->id) }}"
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil"
                                        color="neutral"
                                        title="Editar plantilla"
                                    />
                                    @endcan

                                    {{-- Botón duplicar --}}
                                    @can('duplicar-plantilla')
                                    <flux:button
                                        wire:click="duplicar({{ $plantilla->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="document-duplicate"
                                        color="neutral"
                                        title="Duplicar para crear nueva versión"
                                    />
                                    @endcan

                                    {{-- Botón eliminar --}}
                                    @can('eliminar-plantillas')
                                    <flux:button
                                        wire:click="confirmarEliminar({{ $plantilla->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="trash"
                                        color="neutral"
                                        title="Eliminar"
                                    />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-12 w-12 text-neutral-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-neutral-500 text-lg mb-2">No hay plantillas disponibles</p>
                                    <p class="text-neutral-400 text-sm">Crea tu primera plantilla de formulario</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginación --}}
    <div class="mt-6">
        {{ $plantillas->links() }}
    </div>

    {{-- Modal de confirmación para eliminar --}}
    <flux:modal wire:model="modalEliminar" class="w-full max-w-md">
        <div class="space-y-6">
            {{-- Ícono de advertencia --}}
            <div class="flex justify-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
            </div>

            {{-- Título y mensaje --}}
            <div class="text-center">
                <flux:heading size="lg" class="mb-2">Eliminar Plantilla</flux:heading>
                <flux:subheading>
                    ¿Estás seguro de que deseas eliminar esta plantilla? Esta acción no se puede deshacer.
                </flux:subheading>
            </div>

            {{-- Botones --}}
            <div class="flex justify-end gap-3">
                <flux:button 
                    type="button"
                    wire:click="cancelarEliminar"
                    variant="outline"
                    class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950"
                >
                    Cancelar
                </flux:button>
                <flux:button 
                    type="button"
                    wire:click="eliminar"
                    variant="danger"
                    icon="trash"
                >
                    Eliminar
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
