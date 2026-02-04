<div>
    {{-- Mensajes toast en esquina superior derecha --}}
    <x-toast type="success" :message="session('mensaje')" />
    <x-toast type="error" :message="session('error')" />

    {{-- Header de la página --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Gestión de Especies</flux:heading>
        <flux:subheading>Administra las especies de animales del laboratorio veterinario</flux:subheading>
    </div>

    {{-- Barra de acciones --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        {{-- Búsqueda --}}
        <div class="w-full sm:w-96">
            <flux:input 
                wire:model.live.debounce.300ms="buscar"
                icon="magnifying-glass"
                placeholder="Buscar especies..."
                class="w-full"
            />
        </div>

        {{-- Botón crear --}}
        <flux:button 
            wire:click="crear"
            icon="plus"
            variant="primary"
        >
            Nueva Especie
        </flux:button>
    </div>

    {{-- Tabla de especies --}}
    <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow dark:border-neutral-700 dark:bg-neutral-800">
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
                            <button wire:click="ordenarPor('descripcion')" class="flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                <span>Descripción</span>
                                @if($sortBy === 'descripcion')
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
                    @forelse ($especies as $especie)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50" wire:key="especie-{{ $especie->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                {{ $especie->nombre }}
                            </td>
                            <td class="px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ $especie->descripcion ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <button type="button" wire:click="confirmarCambiarEstado({{ $especie->id }})" class="cursor-pointer group outline-none focus:outline-none">
                                    <div class="pointer-events-none">
                                        <flux:switch 
                                            :checked="$especie->estado"
                                            wire:key="switch-{{ $especie->id }}-{{ $especie->estado ? 'active' : 'inactive' }}"
                                        />
                                    </div>
                                </button>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    {{-- Botón ver --}}
                                    <flux:button
                                        wire:click="ver({{ $especie->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="eye"
                                        color="neutral"
                                        title="Ver detalles"
                                    />

                                    {{-- Botón editar --}}
                                    <flux:button
                                        wire:click="editar({{ $especie->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil"
                                        color="cyan"
                                        title="Editar"
                                    />

                                    {{-- Botón eliminar --}}
                                    <flux:button
                                        wire:click="confirmarEliminar({{ $especie->id }})"
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
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                                    </svg>
                                    <flux:heading size="lg" class="mb-1">No hay especies</flux:heading>
                                    <flux:subheading>
                                        @if ($buscar)
                                            No se encontraron especies con el término "{{ $buscar }}"
                                        @else
                                            Comienza creando tu primera especie
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
        @if ($especies->hasPages())
            <div class="border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-700 dark:bg-neutral-900">
                {{ $especies->links() }}
            </div>
        @endif
    </div>

    {{-- Modal para crear/editar especie --}}
    <flux:modal wire:model="modalAbierto" class="w-full max-w-2xl">
        <form wire:submit.prevent="guardar">
            <flux:heading size="lg" class="mb-2">
                {{ $modoEdicion ? 'Editar Especie' : 'Nueva Especie' }}
            </flux:heading>
            <flux:subheading class="mb-6">
                {{ $modoEdicion ? 'Actualiza la información de la especie' : 'Ingresa los datos de la nueva especie' }}
            </flux:subheading>

            <div class="space-y-6">
                {{-- Nombre --}}
                <flux:input 
                    wire:model="nombre"
                    label="Nombre"
                    placeholder="Ej: Canino, Felino, Equino"
                    required
                    :error="$errors->first('nombre')"
                />

                {{-- Descripción --}}
                <flux:textarea 
                    wire:model="descripcion"
                    label="Descripción"
                    placeholder="Ingresa una descripción de la especie (opcional)"
                    rows="3"
                    :error="$errors->first('descripcion')"
                />

                {{-- Estado --}}
                <flux:checkbox 
                    wire:model="estado"
                    label="Especie activa"
                    description="Indica si la especie está disponible para análisis"
                />
            </div>

            {{-- Botones del modal --}}
            <div class="mt-8 flex justify-end gap-3">
                <flux:button 
                    type="button"
                    wire:click="cerrarModal"
                    variant="ghost"
                >
                    Cancelar
                </flux:button>
                <flux:button 
                    type="submit"
                    variant="primary"
                    color="cyan"
                    icon="check"
                >
                    {{ $modoEdicion ? 'Actualizar' : 'Guardar' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Modal para ver detalles de especie --}}
    <flux:modal wire:model="modalVer" class="w-full max-w-2xl">
        @if($especieAVer)
            <div class="space-y-6">
                {{-- Encabezado --}}
                <div>
                    <flux:heading size="lg" class="mb-1">Detalles de la Especie</flux:heading>
                    <flux:subheading>Información completa de la especie</flux:subheading>
                </div>

                {{-- Contenido --}}
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {{-- Nombre --}}
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Nombre
                        </label>
                        <p class="text-base text-neutral-900 dark:text-neutral-100">
                            {{ $especieAVer->nombre }}
                        </p>
                    </div>

                    {{-- Estado --}}
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Estado
                        </label>
                        <p class="text-base text-neutral-900 dark:text-neutral-100">
                            {{ $especieAVer->estado ? 'Activa' : 'Inactiva' }}
                        </p>
                    </div>

                    {{-- Descripción --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Descripción
                        </label>
                        <p class="text-base text-neutral-900 dark:text-neutral-100">
                            {{ $especieAVer->descripcion ?? '-' }}
                        </p>
                    </div>

                    {{-- Fecha de creación --}}
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Fecha de creación
                        </label>
                        <p class="text-base text-neutral-900 dark:text-neutral-100">
                            {{ $especieAVer->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>

                    {{-- Última actualización --}}
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Última actualización
                        </label>
                        <p class="text-base text-neutral-900 dark:text-neutral-100">
                            {{ $especieAVer->updated_at->format('d/m/Y H:i') }}
                        </p>
                    </div>

                    {{-- Muestras asociadas --}}
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Muestras asociadas
                        </label>
                        <p class="text-base text-neutral-900 dark:text-neutral-100">
                            {{ $especieAVer->muestras_count }} muestras
                        </p>
                    </div>

                    {{-- Rangos de referencia --}}
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Rangos de referencia
                        </label>
                        <p class="text-base text-neutral-900 dark:text-neutral-100">
                            {{ $especieAVer->rangos_referencia_count }} rangos
                        </p>
                    </div>
                </div>

                {{-- Botón cerrar --}}
                <div class="flex justify-end">
                    <flux:button 
                        type="button"
                        wire:click="cerrarModalVer"
                        variant="primary"
                        color="cyan"
                    >
                        Cerrar
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

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
                <flux:heading size="lg" class="mb-2">Eliminar Especie</flux:heading>
                <flux:subheading>
                    ¿Estás seguro de que deseas eliminar esta especie? Esta acción no se puede deshacer y se perderán todos los datos asociados.
                </flux:subheading>
            </div>

            {{-- Botones --}}
            <div class="flex justify-end gap-3">
                <flux:button 
                    type="button"
                    wire:click="cancelarEliminar"
                    variant="ghost"
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

    {{-- Modal de confirmación para cambiar estado --}}
    <flux:modal wire:model="modalCambiarEstado" class="w-full max-w-md">
        <div class="space-y-6">
            {{-- Ícono dinámico según la acción --}}
            <div class="flex justify-center">
                @if($especieACambiar && $estadoActual === true)
                    {{-- Ícono para desactivar (naranja) --}}
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/20">
                        <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </div>
                @else
                    {{-- Ícono para activar (verde) --}}
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Título y mensaje dinámico --}}
            <div class="text-center">
                @if($especieACambiar && $estadoActual === true)
                    <flux:heading size="lg" class="mb-2">Desactivar Especie</flux:heading>
                    <flux:subheading>
                        ¿Estás seguro de que deseas <strong>desactivar</strong> esta especie?
                        <br><br>
                        Al desactivarla, no estará disponible para nuevos análisis, pero se mantendrán todos los datos históricos y registros asociados.
                    </flux:subheading>
                @else
                    <flux:heading size="lg" class="mb-2">Activar Especie</flux:heading>
                    <flux:subheading>
                        ¿Estás seguro de que deseas <strong>activar</strong> esta especie?
                        <br><br>
                        Al activarla, estará disponible nuevamente para realizar análisis y gestionar nuevos registros.
                    </flux:subheading>
                @endif
            </div>

            {{-- Botones --}}
            <div class="flex justify-end gap-3">
                <flux:button 
                    type="button"
                    wire:click="cancelarCambiarEstado"
                    variant="ghost"
                >
                    Cancelar
                </flux:button>
                @if($especieACambiar && $estadoActual === true)
                    <flux:button 
                        type="button"
                        wire:click="cambiarEstado"
                        variant="danger"
                        icon="eye-slash"
                    >
                        Desactivar
                    </flux:button>
                @else
                    <flux:button 
                        type="button"
                        wire:click="cambiarEstado"
                        variant="primary"
                        color="cyan"
                        icon="eye"
                    >
                        Activar
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:modal>
</div>
