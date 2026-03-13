<div>
    {{-- Mensajes toast --}}
    <x-toast type="success" :message="session('mensaje')" />
    <x-toast type="error" :message="session('error')" />

    {{-- Header de la página --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Gestión de Usuarios</flux:heading>
        <flux:subheading>Administra los usuarios del sistema</flux:subheading>
    </div>

    {{-- Barra de acciones --}}
    <div class="mb-6 flex flex-col gap-4">
        <div class="flex flex-col sm:flex-row gap-4 sm:items-end sm:justify-between">
            {{-- Búsqueda y Filtros --}}
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <div class="w-full sm:w-80">
                    <flux:input 
                        wire:model.live.debounce.300ms="search"
                        icon="magnifying-glass"
                        placeholder="Buscar usuarios..."
                        class="w-full"
                    />
                </div>

                <div class="w-full sm:w-64">
                    <flux:select 
                        wire:model.live="filtroSucursal"
                        placeholder="Todas las sucursales"
                    >
                        <option value="">Todas las sucursales</option>
                        @foreach ($this->sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                        @endforeach
                    </flux:select>
                </div>

                {{-- Botón limpiar filtro --}}
                @if($search || $filtroSucursal)
                    <div class="flex items-center">
                        <flux:button 
                            wire:click="limpiarFiltro"
                            variant="ghost"
                            icon="x-mark"
                        >
                            Limpiar
                        </flux:button>
                    </div>
                @endif
            </div>

            {{-- Botón crear --}}
            @can('gestionar-usuarios')
                <flux:button 
                    wire:click="abrirModal"
                    icon="plus"
                    variant="primary"
                >
                    Nuevo Usuario
                </flux:button>
            @endcan
        </div>
    </div>

    {{-- Tabla de usuarios --}}
    <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow dark:border-neutral-700 dark:bg-neutral-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            #
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Usuario
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Rol
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Sucursal
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
                    @forelse ($usuarios as $usuario)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50" wire:key="usuario-{{ $usuario->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                                {{ $usuario->id }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold">
                                            {{ strtoupper(substr($usuario->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                            {{ $usuario->name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ $usuario->email }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if ($usuario->roles->isNotEmpty())
                                    @php
                                        // Paleta de 6 colores para los primeros 6 roles
                                        $roleColorPalette = [
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
                                            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
                                            'bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400',
                                            'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/20 dark:text-cyan-400',
                                            'bg-pink-100 text-pink-800 dark:bg-pink-900/20 dark:text-pink-400',
                                        ];
                                        
                                        $rol = $usuario->roles->first();
                                        // Obtener todos los roles ordenados por ID para mantener consistencia
                                        $todosLosRoles = \Spatie\Permission\Models\Role::orderBy('id')->pluck('id')->toArray();
                                        // Buscar la posición del rol actual en la lista
                                        $colorIndex = array_search($rol->id, $todosLosRoles);
                                        
                                        // Si el índice está dentro de la paleta, usar ese color, sino gris
                                        $colorClass = isset($roleColorPalette[$colorIndex]) 
                                            ? $roleColorPalette[$colorIndex] 
                                            : 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300';
                                    @endphp
                                    
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $colorClass }}">
                                        {{ $rol->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Sin rol</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ $usuario->sucursal?->nombre ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                @can('gestionar-usuarios')
                                    <button type="button" wire:click="toggleEstado({{ $usuario->id }})" class="cursor-pointer group outline-none focus:outline-none">
                                        <div class="pointer-events-none">
                                            <flux:switch 
                                                :checked="$usuario->estado"
                                                wire:key="switch-{{ $usuario->id }}-{{ $usuario->estado ? 'active' : 'inactive' }}"
                                            />
                                        </div>
                                    </button>
                                @else
                                    <div class="pointer-events-none">
                                        <flux:switch 
                                            :checked="$usuario->estado"
                                            disabled
                                        />
                                    </div>
                                @endcan
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    @can('gestionar-usuarios')
                                        {{-- Botón editar --}}
                                        <flux:button
                                            wire:click="editar({{ $usuario->id }})"
                                            variant="ghost"
                                            size="sm"
                                            icon="pencil"
                                            title="Editar"
                                        />

                                        {{-- Botón eliminar --}}
                                        @if($usuario->id !== auth()->id())
                                            <flux:button
                                                wire:click="confirmarEliminar({{ $usuario->id }})"
                                                variant="ghost"
                                                size="sm"
                                                icon="trash"
                                                class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                                title="Eliminar"
                                            />
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="mb-3 h-12 w-12 text-neutral-400 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <flux:heading size="lg" class="mb-1">No hay usuarios</flux:heading>
                                    <flux:subheading>
                                        @if ($search)
                                            No se encontraron usuarios con el término "{{ $search }}"
                                        @else
                                            Comienza creando tu primer usuario
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
        @if ($usuarios->hasPages())
            <div class="border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-700 dark:bg-neutral-900">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>

    {{-- Modal para crear/editar usuario --}}
    <flux:modal wire:model="modalAbierto" class="w-full max-w-xl">
        <form wire:submit.prevent="guardar">
            <flux:heading size="lg" class="mb-2">
                {{ $modoEdicion ? 'Editar Usuario' : 'Nuevo Usuario' }}
            </flux:heading>
            <flux:subheading class="mb-6">
                {{ $modoEdicion ? 'Actualiza la información del usuario' : 'Ingresa los datos del nuevo usuario' }}
            </flux:subheading>

            <div class="space-y-4">
                {{-- Nombre --}}
                <flux:input 
                    wire:model="name"
                    label="Nombre completo"
                    placeholder="Juan Pérez"
                    required
                />
                @error('name')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                {{-- Email --}}
                <flux:field>
                    <flux:label>Email</flux:label>
                    <div class="flex items-center gap-2">
                        <flux:input 
                            type="email"
                            wire:model="email"
                            placeholder="juan@ejemplo.com"
                            required
                            class="flex-1"
                        />
                        @if (!$modoEdicion)
                            <flux:button 
                                type="button"
                                wire:click="generarEmail"
                                variant="ghost"
                                title="Generar correo automáticamente"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                            </flux:button>
                        @endif
                    </div>
                </flux:field>
                @error('email')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                @if (!$modoEdicion)
                    {{-- Contraseña (solo para nuevos usuarios) --}}
                    <flux:field>
                        <flux:label>Contraseña</flux:label>
                        <div class="relative">
                            <flux:input 
                                :type="$mostrarPassword ? 'text' : 'password'"
                                wire:model="password"
                                placeholder="Mínimo 8 caracteres"
                                required
                            />
                            <button 
                                type="button"
                                wire:click="toggleMostrarPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200"
                            >
                                @if ($mostrarPassword)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                @endif
                            </button>
                        </div>
                    </flux:field>
                    @error('password')
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    <flux:field>
                        <flux:label>Confirmar contraseña</flux:label>
                        <div class="relative">
                            <flux:input 
                                :type="$mostrarPasswordConfirmation ? 'text' : 'password'"
                                wire:model="password_confirmation"
                                placeholder="Repite la contraseña"
                                required
                            />
                            <button 
                                type="button"
                                wire:click="toggleMostrarPasswordConfirmation"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200"
                            >
                                @if ($mostrarPasswordConfirmation)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                @endif
                            </button>
                        </div>
                    </flux:field>
                @else
                    {{-- Cambio de contraseña opcional --}}
                    <div class="border-t border-neutral-200 dark:border-neutral-700 pt-4 mt-4">
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-3">
                            Deja en blanco para mantener la contraseña actual
                        </p>
                        
                        <flux:field>
                            <flux:label>Nueva contraseña (opcional)</flux:label>
                            <div class="relative">
                                <flux:input 
                                    :type="$mostrarPassword ? 'text' : 'password'"
                                    wire:model="password"
                                    placeholder="Mínimo 8 caracteres"
                                />
                                <button 
                                    type="button"
                                    wire:click="toggleMostrarPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200"
                                >
                                    @if ($mostrarPassword)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    @endif
                                </button>
                            </div>
                        </flux:field>
                        @error('password')
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        <flux:field class="mt-4">
                            <flux:label>Confirmar nueva contraseña</flux:label>
                            <div class="relative">
                                <flux:input 
                                    :type="$mostrarPasswordConfirmation ? 'text' : 'password'"
                                    wire:model="password_confirmation"
                                    placeholder="Repite la contraseña"
                                />
                                <button 
                                    type="button"
                                    wire:click="toggleMostrarPasswordConfirmation"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200"
                                >
                                    @if ($mostrarPasswordConfirmation)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    @endif
                                </button>
                            </div>
                        </flux:field>
                    </div>
                @endif

                {{-- Rol --}}
                <flux:select 
                    wire:model="rol_id"
                    label="Rol"
                    placeholder="Selecciona un rol"
                >
                    <option value="">Selecciona un rol</option>
                    @foreach ($this->roles as $rol)
                        <option value="{{ $rol->id }}">{{ $rol->name }}</option>
                    @endforeach
                </flux:select>
                @error('rol_id')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                {{-- Sucursal --}}
                <flux:select 
                    wire:model="sucursal_id"
                    label="Sucursal (opcional)"
                    placeholder="Selecciona una sucursal"
                >
                    <option value="">Sin sucursal asignada</option>
                    @foreach ($this->sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                    @endforeach
                </flux:select>
                @error('sucursal_id')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                {{-- Estado --}}
                <div class="rounded-lg border border-neutral-200 bg-neutral-50 p-3 dark:border-neutral-700 dark:bg-neutral-900/50">
                    <flux:checkbox
                        wire:model="estado"
                        label="Usuario activo"
                        description="Define si el usuario podra acceder al sistema"
                    />
                </div>
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
                <flux:button 
                    type="submit"
                    variant="primary"
                >
                    {{ $modoEdicion ? 'Actualizar' : 'Crear' }}
                </flux:button>
            </div>
        </form>
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
                <flux:heading size="lg" class="mb-2">Eliminar Usuario</flux:heading>
                <flux:subheading>
                    ¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.
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
