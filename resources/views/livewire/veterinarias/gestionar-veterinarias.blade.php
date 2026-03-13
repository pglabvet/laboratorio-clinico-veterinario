<div>
    {{-- Mensajes toast en esquina superior derecha --}}
    <x-toast type="success" :message="session('mensaje')" />
    <x-toast type="error" :message="session('error')" />

    {{-- Header de la página --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Gestión de Veterinarias</flux:heading>
        <flux:subheading>Administra las veterinarias del sistema</flux:subheading>
    </div>

    {{-- Barra de acciones --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            {{-- Búsqueda --}}
            <div class="w-full sm:w-96">
                <flux:input 
                    wire:model.live.debounce.300ms="buscar"
                    icon="magnifying-glass"
                    placeholder="Buscar veterinarias..."
                    class="w-full"
                />
            </div>

            {{-- Botón limpiar filtro --}}
            @if($buscar)
                <div class="flex items-center">
                    <flux:button 
                        wire:click="limpiarBuscar"
                        variant="ghost"
                        icon="x-mark"
                    >
                        Limpiar
                    </flux:button>
                </div>
            @endif
        </div>

        {{-- Botón crear --}}
        @can('crear-veterinarias')
        <flux:button 
            wire:click="crear"
            icon="plus"
            variant="primary"
        >
            Nueva Veterinaria
        </flux:button>
        @endcan
    </div>

    {{-- Tabla de veterinarias --}}
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
                            <button wire:click="ordenarPor('telefono')" class="flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                <span>TELÉFONO</span>
                                @if($sortBy === 'telefono')
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
                                {{ $veterinaria->telefono }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <flux:badge 
                                    :color="$veterinaria->estado ? 'green' : 'red'" 
                                    size="sm"
                                    inset="top bottom"
                                >
                                    {{ $veterinaria->estado ? 'Activa' : 'Inactiva' }}
                                </flux:badge>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    {{-- Botón ver --}}
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

                                    {{-- Botón editar --}}
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

                                    {{-- Botón eliminar --}}
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
                                    <svg class="mb-3 h-12 w-12 text-neutral-400 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <flux:heading size="lg" class="mb-1">No hay veterinarias</flux:heading>
                                    <flux:subheading>
                                        @if ($buscar)
                                            No se encontraron veterinarias con el término "{{ $buscar }}"
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

        {{-- Paginación --}}
        @if ($veterinarias->hasPages())
            <div class="border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-700 dark:bg-neutral-900">
                {{ $veterinarias->links() }}
            </div>
        @endif
    </div>

    {{-- Modal para crear/editar veterinaria --}}
    <flux:modal wire:model="modalAbierto" class="w-full max-w-2xl">
        <form wire:submit.prevent="guardar">
            <flux:heading size="lg" class="mb-2">
                {{ $modoEdicion ? 'Editar Veterinaria' : 'Nueva Veterinaria' }}
            </flux:heading>
            <flux:subheading class="mb-6">
                {{ $modoEdicion ? 'Actualiza la información de la veterinaria' : 'Ingresa los datos de la nueva veterinaria' }}
            </flux:subheading>

            <div class="space-y-6">
                {{-- Nombre --}}
                <flux:input 
                    wire:model="nombre"
                    label="Nombre de la Veterinaria"
                    placeholder="Ej: Veterinaria San Francisco"
                    required
                    :error="$errors->first('nombre')"
                />

                {{-- Responsable --}}
                <flux:input 
                    wire:model="responsable"
                    label="Responsable"
                    placeholder="Ej: Dr. Juan Pérez"
                    required
                    :error="$errors->first('responsable')"
                />

                {{-- Email y Teléfono en dos columnas --}}
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {{-- Email --}}
                    <flux:input 
                        wire:model="email"
                        label="Email"
                        type="email"
                        placeholder="contacto@veterinaria.com"
                        required
                        :error="$errors->first('email')"
                    />

                    {{-- Teléfono --}}
                    <flux:input 
                        wire:model="telefono"
                        label="Teléfono"
                        placeholder="Ej: 555-1234"
                        required
                        :error="$errors->first('telefono')"
                    />
                </div>

                {{-- Dirección --}}
                <flux:textarea 
                    wire:model="direccion"
                    label="Dirección"
                    placeholder="Ingresa la dirección completa"
                    rows="3"
                    required
                    :error="$errors->first('direccion')"
                />

                {{-- Estado --}}
                <flux:select 
                    wire:model="estado"
                    label="Estado de la Veterinaria"
                    placeholder="Selecciona el estado"
                    required
                >
                    <option value="1">Activa</option>
                    <option value="0">Inactiva</option>
                </flux:select>
            </div>

            {{-- Botones del modal --}}
            <div class="mt-8 flex justify-end gap-3">
                <flux:button 
                    type="button"
                    wire:click="cerrarModal"
                    variant="outline"
                    class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950"
                >
                    Cancelar
                </flux:button>
                @can('guardar-veterinaria')
                <flux:button 
                    type="submit"
                    variant="primary"
                >
                    {{ $modoEdicion ? 'Actualizar' : 'Guardar' }}
                </flux:button>
                @endcan
            </div>
        </form>
    </flux:modal>

    {{-- Modal para ver detalles de veterinaria --}}
    <flux:modal wire:model="modalVer" class="w-full max-w-2xl">
        @if($veterinariaAVer)
            @php
                $estadoBadge = [
                    true => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
                    false => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
                ];
            @endphp
            <div class="space-y-5">
                {{-- Encabezado: Nombre veterinaria + Badge estado --}}
                <div class="pb-4 border-b border-neutral-200 dark:border-neutral-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-semibold text-neutral-900 dark:text-neutral-100">{{ $veterinariaAVer->nombre }}</h2>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estadoBadge[$veterinariaAVer->estado] ?? 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/20 dark:text-neutral-400' }}">
                                {{ $veterinariaAVer->estado ? 'Activa' : 'Inactiva' }}
                            </span>
                        </div>
                    </div>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Registro desde: <span class="font-medium">{{ $veterinariaAVer->created_at->format('d/m/Y') }}</span></p>
                </div>

                {{-- Datos de Contacto --}}
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 divide-y divide-neutral-200 dark:divide-neutral-700 overflow-hidden bg-white dark:bg-neutral-800/50">
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-indigo-500 dark:text-indigo-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Responsable</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $veterinariaAVer->responsable }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Email</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $veterinariaAVer->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-emerald-500 dark:text-emerald-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Teléfono</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $veterinariaAVer->telefono }}</p>
                        </div>
                    </div>
                </div>

                {{-- Ubicación --}}
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 divide-y divide-neutral-200 dark:divide-neutral-700 overflow-hidden bg-white dark:bg-neutral-800/50">
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-violet-500 dark:text-violet-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Dirección</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $veterinariaAVer->direccion }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-amber-500 dark:text-amber-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Fecha de Registro</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $veterinariaAVer->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>

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
                <flux:heading size="lg" class="mb-2">Eliminar Veterinaria</flux:heading>
                <flux:subheading>
                    ¿Estás seguro de que deseas eliminar esta veterinaria? Esta acción no se puede deshacer y se perderán todos los datos asociados.
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

    {{-- Modal de confirmación para cambiar estado --}}
    <flux:modal wire:model="modalCambiarEstado" class="w-full max-w-md">
        <div class="space-y-6">
            {{-- Ícono dinámico según la acción --}}
            <div class="flex justify-center">
                @if($veterinariaACambiar && $estadoActual === true)
                    {{-- Ícono para desactivar (rojo) --}}
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
                @if($veterinariaACambiar && $estadoActual === true)
                    <flux:heading size="lg" class="mb-2">Desactivar Veterinaria</flux:heading>
                    <flux:subheading>
                        ¿Estás seguro de que deseas <strong>desactivar</strong> esta veterinaria?
                        <br><br>
                        Al desactivarla, no estará disponible para nuevas operaciones, pero se mantendrán todos los datos históricos y registros asociados.
                    </flux:subheading>
                @else
                    <flux:heading size="lg" class="mb-2">Activar Veterinaria</flux:heading>
                    <flux:subheading>
                        ¿Estás seguro de que deseas <strong>activar</strong> esta veterinaria?
                        <br><br>
                        Al activarla, estará disponible nuevamente para realizar operaciones y gestionar nuevos registros.
                    </flux:subheading>
                @endif
            </div>

            {{-- Botones --}}
            <div class="flex justify-end gap-3">
                <flux:button 
                    type="button"
                    wire:click="cancelarCambiarEstado"
                    variant="outline"
                    class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950"
                >
                    Cancelar
                </flux:button>
                @if($veterinariaACambiar && $estadoActual === true)
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
