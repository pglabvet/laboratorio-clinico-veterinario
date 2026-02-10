<div>
    {{-- Mensajes toast --}}
    <x-toast type="success" :message="session('mensaje')" />
    <x-toast type="error" :message="session('error')" />

    {{-- Header de la página --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Gestión de Roles</flux:heading>
        <flux:subheading>Administra los roles y sus permisos del sistema</flux:subheading>
    </div>

    {{-- Barra de acciones --}}
    <div class="mb-6 flex flex-col gap-4">
        <div class="flex flex-col sm:flex-row gap-4 sm:items-end sm:justify-between">
            {{-- Búsqueda --}}
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <div class="w-full sm:w-80">
                    <flux:input 
                        wire:model.live.debounce.300ms="buscar"
                        icon="magnifying-glass"
                        placeholder="Buscar roles..."
                        class="w-full"
                    />
                </div>

                {{-- Botón limpiar filtro --}}
                @if($buscar)
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
            @can('crear-roles')
                <flux:button 
                    wire:click="crear"
                    icon="plus"
                    variant="primary"
                >
                    Nuevo Rol
                </flux:button>
            @endcan
        </div>
    </div>

    {{-- Tabla de roles --}}
    <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-md ring-1 ring-neutral-200/50 dark:border-neutral-700 dark:bg-neutral-800 dark:ring-neutral-700/50">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            <button wire:click="ordenarPor('name')" class="flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                <span>Nombre</span>
                                @if($sortBy === 'name')
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
                            Permisos
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Usuarios
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Fecha de Creación
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                    @forelse ($roles as $role)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50" wire:key="role-{{ $role->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                {{ $role->name }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                    {{ $role->permissions_count }} {{ $role->permissions_count == 1 ? 'permiso' : 'permisos' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900/20 dark:text-purple-400">
                                    {{ $role->users_count }} {{ $role->users_count == 1 ? 'usuario' : 'usuarios' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ $role->created_at->format('d/m/Y') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    {{-- Botón ver --}}
                                    @can('mostrar-detalle-rol')
                                        <flux:button
                                            wire:click="ver({{ $role->id }})"
                                            variant="ghost"
                                            size="sm"
                                            icon="eye"
                                            color="neutral"
                                            title="Ver detalles"
                                        />
                                    @endcan

                                    {{-- Botón editar --}}
                                    @can('editar-roles')
                                        <flux:button
                                            wire:click="editar({{ $role->id }})"
                                            variant="ghost"
                                            size="sm"
                                            icon="pencil"
                                            color="cyan"
                                            title="Editar"
                                        />
                                    @endcan

                                    {{-- Botón eliminar --}}
                                    @can('eliminar-roles')
                                        <flux:button
                                            wire:click="confirmarEliminar({{ $role->id }})"
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
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="mb-3 h-12 w-12 text-neutral-400 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <flux:heading size="lg" class="mb-1">No hay roles</flux:heading>
                                    <flux:subheading>
                                        @if ($buscar)
                                            No se encontraron roles con el término "{{ $buscar }}"
                                        @else
                                            Comienza creando tu primer rol
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
        @if ($roles->hasPages())
            <div class="border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-700 dark:bg-neutral-900">
                {{ $roles->links() }}
            </div>
        @endif
    </div>

    {{-- Modal para crear/editar rol --}}
    <flux:modal wire:model="modalAbierto" class="w-full max-w-4xl">
        <form wire:submit.prevent="guardar">
            <flux:heading size="lg" class="mb-2">
                {{ $modoEdicion ? 'Editar Rol' : 'Nuevo Rol' }}
            </flux:heading>
            <flux:subheading class="mb-6">
                {{ $modoEdicion ? 'Actualiza la información del rol' : 'Ingresa los datos del nuevo rol' }}
            </flux:subheading>

            <div class="space-y-6">
                {{-- Nombre --}}
                <flux:input 
                    wire:model="name"
                    label="Nombre del Rol"
                    placeholder="Ej: Administrador, Editor, Usuario"
                    required
                    :error="$errors->first('name')"
                />

                {{-- Permisos agrupados --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                            Permisos ({{ count($permissions) }} seleccionados)
                        </label>
                        <div class="flex gap-2">
                            <flux:button 
                                type="button"
                                wire:click="seleccionarTodosPermisos"
                                variant="ghost"
                                size="sm"
                            >
                                Seleccionar todos
                            </flux:button>
                            <flux:button 
                                type="button"
                                wire:click="limpiarPermisos"
                                variant="ghost"
                                size="sm"
                            >
                                Limpiar
                            </flux:button>
                        </div>
                    </div>

                    @php
                        // Definir permisos que pertenecen al Dashboard
                        // Todos estos permisos se agruparán bajo "Dashboard" en la UI
                        $permisosDashboard = [
                            // Tarjetas de estadísticas
                            'ver-dashboard',
                            'ver-estadisticas-completas',
                            // Filtros
                            'ver-filtros-dashboard',
                            'filtrar-por-sucursal',
                            // Gráficos
                            'ver-graficos-estadisticas',
                            // Secciones de información
                            'ver-actividad-reciente',
                            'ver-alertas-inventario',
                            'ver-ultimas-muestras',
                            // Acciones rápidas
                            'ver-acciones-rapidas',
                            'registrar-muestras',
                            'escanear-muestras',
                        ];
                        
                        // Mapeo manual de permisos especiales a su grupo correspondiente
                        $permisosEspeciales = [
                            'mostrar-detalle-muestra' => 'muestras',
                            'mostrar-detalle-sucursal' => 'sucursales',
                            'mostrar-detalle-veterinaria' => 'veterinarias',
                            'mostrar-detalle-especie' => 'especies',
                            'mostrar-detalle-tipo-analisis' => 'tipos-analisis',
                            'mostrar-detalle-permiso' => 'permisos',
                            'mostrar-detalle-rol' => 'roles',
                            'duplicar-plantilla' => 'plantillas',
                            'guardar-sucursal' => 'sucursales',
                            'guardar-veterinaria' => 'veterinarias',
                            'guardar-especie' => 'especies',
                            'guardar-tipo-analisis' => 'tipos-analisis',
                            'guardar-unidad-medida' => 'unidades-medida',
                            'guardar-insumo' => 'insumos',
                            'guardar-categoria-insumo' => 'categorias-insumo',
                            'guardar-rol' => 'roles',
                            'guardar-permiso' => 'permisos',
                            'enviar-resultados-muestra' => 'muestras',
                            'ver-codigo-barras-muestra' => 'muestras',
                            'filtro-de-sucursal-muestra' => 'muestras',
                            'aprobar-analisis' => 'analisis',
                            'rechazar-analisis' => 'analisis',
                            'actualizar-datos-analisis' => 'analisis',
                            'descargar-pdf-analisis' => 'analisis',
                        ];

                        // Agrupar permisos por módulo/sección
                        $permisosAgrupados = $allPermissions->groupBy(function($permission) use ($permisosDashboard, $permisosEspeciales) {
                            $nombre = $permission->name;
                            
                            // Si está en la lista de permisos de Dashboard
                            if (in_array($nombre, $permisosDashboard)) {
                                return 'dashboard';
                            }

                            // Si tiene mapeo especial, usar ese grupo
                            if (isset($permisosEspeciales[$nombre])) {
                                return $permisosEspeciales[$nombre];
                            }
                            
                            // Para otros permisos, tomar todo después del primer guión
                            $parts = explode('-', $nombre);
                            if (count($parts) > 1) {
                                array_shift($parts);
                                return implode('-', $parts);
                            }
                            
                            return 'otros';
                        });
                        
                        // Ordenar: Dashboard primero, luego el resto alfabéticamente
                        $permisosAgrupados = $permisosAgrupados->sortKeys()->sortBy(function($permisos, $key) {
                            return $key === 'dashboard' ? '0' : $key;
                        });
                    @endphp

                    <div class="max-h-[500px] overflow-y-auto space-y-4 p-4 bg-neutral-50 dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700">
                        @foreach($permisosAgrupados as $categoria => $permisos)
                            <div class="bg-white dark:bg-neutral-800 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
                                <h4 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-3 capitalize flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-xs font-bold">
                                        {{ $permisos->count() }}
                                    </span>
                                    {{ str_replace('-', ' ', $categoria) }}
                                </h4>
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach($permisos->sortBy('name') as $permission)
                                        <flux:checkbox 
                                            wire:model.live="permissions"
                                            :value="$permission->id"
                                            :label="$permission->name"
                                        />
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(count($allPermissions) == 0)
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-2">
                            No hay permisos disponibles. Crea permisos primero.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Botones del modal --}}
            <div class="mt-8 flex justify-end gap-3 border-t border-neutral-200 dark:border-neutral-700 pt-4">
                <flux:button 
                    type="button"
                    wire:click="cerrarModal"
                    variant="outline"
                    class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950"
                >
                    Cancelar
                </flux:button>
                @can('guardar-rol')
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

    {{-- Modal para ver detalles del rol --}}
    <flux:modal wire:model="modalVer" class="w-full max-w-3xl">
        @if($roleAVer)
            <div class="space-y-6">
                {{-- Encabezado --}}
                <div>
                    <flux:heading size="lg" class="mb-1">Detalles del Rol</flux:heading>
                    <flux:subheading>{{ $roleAVer->name }}</flux:subheading>
                </div>

                {{-- Información general --}}
                <div class="grid grid-cols-2 gap-4 p-4 bg-neutral-50 dark:bg-neutral-900 rounded-lg">
                    <div>
                        <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">
                            Total de Permisos
                        </label>
                        <p class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                            {{ $roleAVer->permissions->count() }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">
                            Fecha de Creación
                        </label>
                        <p class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                            {{ $roleAVer->created_at->format('d/m/Y') }}
                        </p>
                    </div>
                </div>

                {{-- Permisos agrupados --}}
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-4">
                        Permisos Asignados
                    </label>
                    
                    @if($roleAVer->permissions->count() > 0)
                        @php
                            // Definir permisos que pertenecen al Dashboard
                            $permisosDashboard = [
                                'ver-dashboard',
                                'ver-estadisticas-completas',
                                'filtrar-por-sucursal',
                                'ver-graficos-estadisticas',
                                'ver-actividad-reciente',
                                'ver-alertas-inventario',
                                'ver-ultimas-muestras',
                            ];
                            
                            // Agrupar permisos por módulo/sección
                            $permisosAgrupados = $roleAVer->permissions->groupBy(function($permission) use ($permisosDashboard) {
                                $nombre = $permission->name;
                                
                                // Si está en la lista de permisos de Dashboard
                                if (in_array($nombre, $permisosDashboard)) {
                                    return 'dashboard';
                                }
                                
                                // Para otros permisos, tomar todo después del primer guión
                                $parts = explode('-', $nombre);
                                if (count($parts) > 1) {
                                    array_shift($parts); // Quitar la primera parte (ver, gestionar, etc)
                                    return implode('-', $parts);
                                }
                                
                                return 'otros';
                            });
                            
                            // Ordenar: Dashboard primero, luego el resto alfabéticamente
                            $permisosAgrupados = $permisosAgrupados->sortKeys()->sortBy(function($permisos, $key) {
                                return $key === 'dashboard' ? '0' : $key;
                            });
                        @endphp

                        <div class="space-y-4">
                            @foreach($permisosAgrupados as $categoria => $permisos)
                                <div class="border border-neutral-200 dark:border-neutral-700 rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-3 capitalize">
                                        {{ str_replace('-', ' ', $categoria) }}
                                        <span class="ml-2 text-xs font-normal text-neutral-500 dark:text-neutral-400">
                                            ({{ $permisos->count() }})
                                        </span>
                                    </h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach($permisos->sortBy('name') as $permission)
                                            <div class="flex items-center gap-2 text-sm text-neutral-700 dark:text-neutral-300">
                                                <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>{{ $permission->name }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                            <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                                Este rol no tiene permisos asignados
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Botón cerrar --}}
                <div class="flex justify-end border-t border-neutral-200 dark:border-neutral-700 pt-4">
                    <flux:button 
                        type="button"
                        wire:click="cerrarModalVer"
                        variant="ghost"
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
                <flux:heading size="lg" class="mb-2">Eliminar Rol</flux:heading>
                <flux:subheading>
                    ¿Estás seguro de que deseas eliminar este rol? Esta acción no se puede deshacer y se perderán todos los datos asociados.
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
