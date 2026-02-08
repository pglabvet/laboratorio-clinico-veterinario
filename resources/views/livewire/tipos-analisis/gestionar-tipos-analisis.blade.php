<div>
    {{-- Mensajes toast en esquina superior derecha --}}
    <x-toast type="success" :message="session('mensaje')" />
    <x-toast type="error" :message="session('error')" />

    {{-- Header de la página --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Gestión de Tipos de Análisis</flux:heading>
        <flux:subheading>Administra los tipos de análisis disponibles en el laboratorio</flux:subheading>
    </div>

    {{-- Barra de acciones --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        {{-- Búsqueda --}}
        <div class="w-full sm:w-96">
            <flux:input 
                wire:model.live.debounce.300ms="buscar"
                icon="magnifying-glass"
                placeholder="Buscar tipos de análisis..."
                class="w-full"
            />
        </div>

        {{-- Botón crear --}}
        <flux:button 
            wire:click="crear"
            icon="plus"
            variant="primary"
        >
            Nuevo Tipo de Análisis
        </flux:button>
    </div>

    {{-- Tabla de tipos de análisis --}}
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
                            Plantillas
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
                    @forelse ($tiposAnalisis as $tipoAnalisis)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50" wire:key="tipo-analisis-{{ $tipoAnalisis->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                {{ $tipoAnalisis->nombre }}
                            </td>
                            <td class="px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ $tipoAnalisis->descripcion ? Str::limit($tipoAnalisis->descripcion, 80) : 'Sin descripción' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                <div class="flex items-center gap-2">
                                    <flux:badge size="sm" color="cyan">
                                        {{ $tipoAnalisis->plantillas_count }}
                                    </flux:badge>
                                    <span class="text-xs text-neutral-500">plantilla{{ $tipoAnalisis->plantillas_count !== 1 ? 's' : '' }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <button type="button" wire:click="confirmarCambiarEstado({{ $tipoAnalisis->id }})" class="cursor-pointer group outline-none focus:outline-none">
                                    <div class="pointer-events-none">
                                        <flux:switch 
                                            :checked="$tipoAnalisis->estado"
                                            wire:key="switch-{{ $tipoAnalisis->id }}-{{ $tipoAnalisis->estado ? 'active' : 'inactive' }}"
                                        />
                                    </div>
                                </button>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    {{-- Botón ver --}}
                                    <flux:button
                                        wire:click="ver({{ $tipoAnalisis->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="eye"
                                        color="neutral"
                                        title="Ver detalles"
                                    />

                                    {{-- Botón editar --}}
                                    <flux:button
                                        wire:click="editar({{ $tipoAnalisis->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil"
                                        color="cyan"
                                        title="Editar"
                                    />

                                    {{-- Botón eliminar --}}
                                    <flux:button
                                        wire:click="confirmarEliminar({{ $tipoAnalisis->id }})"
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
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <flux:icon.beaker class="mb-3 h-12 w-12 text-neutral-400 dark:text-neutral-600" />
                                    <flux:heading size="lg" class="mb-1">No hay tipos de análisis</flux:heading>
                                    <flux:subheading>
                                        @if ($buscar)
                                            No se encontraron tipos de análisis con el término "{{ $buscar }}"
                                        @else
                                            Comienza creando tu primer tipo de análisis
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
        @if ($tiposAnalisis->hasPages())
            <div class="border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-700 dark:bg-neutral-900">
                {{ $tiposAnalisis->links() }}
            </div>
        @endif
    </div>

    {{-- Modal para crear/editar tipo de análisis --}}
    <flux:modal wire:model="modalAbierto" class="w-full max-w-2xl">
        <form wire:submit.prevent="guardar">
            <flux:heading size="lg" class="mb-2">
                {{ $modoEdicion ? 'Editar Tipo de Análisis' : 'Nuevo Tipo de Análisis' }}
            </flux:heading>
            <flux:subheading class="mb-6">
                {{ $modoEdicion ? 'Actualiza la información del tipo de análisis' : 'Ingresa los datos del nuevo tipo de análisis' }}
            </flux:subheading>

            <div class="space-y-6">
                {{-- Nombre --}}
                <flux:input 
                    wire:model="nombre"
                    label="Nombre"
                    placeholder="Ej: Hemograma Completo"
                    required
                    :error="$errors->first('nombre')"
                />

                {{-- Descripción --}}
                <flux:textarea 
                    wire:model="descripcion"
                    label="Descripción"
                    placeholder="Describe el tipo de análisis (opcional)"
                    rows="4"
                    :error="$errors->first('descripcion')"
                />

                {{-- Estado --}}
                <flux:checkbox 
                    wire:model="estado"
                    label="Tipo de análisis activo"
                    description="Indica si el tipo de análisis está disponible"
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

    {{-- Modal para ver detalles de tipo de análisis --}}
    <flux:modal wire:model="modalVer" class="w-full max-w-3xl">
        @if($tipoAnalisisAVer)
            @php
                $estadoBadge = [
                    true => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
                    false => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
                ];
            @endphp
            <div class="space-y-5">
                {{-- Encabezado: Nombre tipo análisis + Badge estado --}}
                <div class="pb-4 border-b border-neutral-200 dark:border-neutral-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-semibold text-neutral-900 dark:text-neutral-100">{{ $tipoAnalisisAVer->nombre }}</h2>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estadoBadge[$tipoAnalisisAVer->estado] ?? 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/20 dark:text-neutral-400' }}">
                                {{ $tipoAnalisisAVer->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>
                    @if($tipoAnalisisAVer->descripcion)
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $tipoAnalisisAVer->descripcion }}</p>
                    @endif
                </div>

                {{-- Estadísticas --}}
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-3">Estadísticas</h3>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        {{-- Plantillas --}}
                        <div class="flex items-center gap-3 px-4 py-3.5 rounded-lg bg-gradient-to-r from-cyan-50 to-blue-50 dark:from-cyan-900/10 dark:to-blue-900/10 border border-cyan-100 dark:border-cyan-900/30">
                            <svg class="w-8 h-8 text-cyan-500 dark:text-cyan-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-neutral-600 dark:text-neutral-400">Plantillas</p>
                                <p class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ $tipoAnalisisAVer->plantillas->count() }}</p>
                            </div>
                        </div>

                        {{-- Análisis realizados --}}
                        <div class="flex items-center gap-3 px-4 py-3.5 rounded-lg bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/10 dark:to-emerald-900/10 border border-green-100 dark:border-green-900/30">
                            <svg class="w-8 h-8 text-green-500 dark:text-green-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19 14.5M14.25 3.104c.251.023.501.05.75.082M19 14.5l-2.47 2.47a2.25 2.25 0 0 1-1.59.659H9.06a2.25 2.25 0 0 1-1.591-.659L5 14.5m14 0-3.375-3.375M5 14.5l3.375-3.375" /></svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-neutral-600 dark:text-neutral-400">Análisis</p>
                                <p class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ $tipoAnalisisAVer->analisis->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Fechas --}}
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 divide-y divide-neutral-200 dark:divide-neutral-700 overflow-hidden bg-white dark:bg-neutral-800/50">
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-amber-500 dark:text-amber-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Fecha de Registro</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $tipoAnalisisAVer->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-violet-500 dark:text-violet-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Última Actualización</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $tipoAnalisisAVer->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Plantillas Asociadas --}}
                @if($tipoAnalisisAVer->plantillas->count() > 0)
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-3">Plantillas Asociadas ({{ $tipoAnalisisAVer->plantillas->count() }})</h3>
                        <div class="grid gap-2.5">
                            @foreach($tipoAnalisisAVer->plantillas as $plantilla)
                                <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 border border-blue-100 dark:border-blue-900/30">
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 truncate">{{ $plantilla->nombre }}</p>
                                            @if($plantilla->descripcion)
                                                <p class="text-xs text-neutral-600 dark:text-neutral-400 truncate">{{ Str::limit($plantilla->descripcion, 60) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <flux:badge 
                                        :color="$plantilla->activo ? 'green' : 'gray'"
                                        size="sm"
                                        class="shrink-0"
                                    >
                                        {{ $plantilla->activo ? 'Activa' : 'Inactiva' }}
                                    </flux:badge>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Botón cerrar --}}
                <div class="flex justify-end pt-2">
                    <flux:button 
                        type="button"
                        wire:click="cerrarModalVer"
                        variant="primary"
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
                <flux:heading size="lg" class="mb-2">Eliminar Tipo de Análisis</flux:heading>
                <flux:subheading>
                    ¿Estás seguro de que deseas eliminar este tipo de análisis? Esta acción no se puede deshacer y se perderán todos los datos asociados.
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
                @if($tipoAnalisisACambiar && $estadoActual === true)
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
                @if($tipoAnalisisACambiar && $estadoActual === true)
                    <flux:heading size="lg" class="mb-2">Desactivar Tipo de Análisis</flux:heading>
                    <flux:subheading>
                        ¿Estás seguro de que deseas <strong>desactivar</strong> este tipo de análisis?
                        <br><br>
                        Al desactivarlo, no estará disponible para nuevos análisis, pero se mantendrán todos los registros históricos.
                    </flux:subheading>
                @else
                    <flux:heading size="lg" class="mb-2">Activar Tipo de Análisis</flux:heading>
                    <flux:subheading>
                        ¿Estás seguro de que deseas <strong>activar</strong> este tipo de análisis?
                        <br><br>
                        Al activarlo, estará disponible nuevamente para realizar nuevos análisis.
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
                @if($tipoAnalisisACambiar && $estadoActual === true)
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
