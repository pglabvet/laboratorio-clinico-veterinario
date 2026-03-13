<div>
    {{-- ========================================== --}}
    {{-- ENCABEZADO: Título y descripción            --}}
    {{-- ========================================== --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-1">Auditorías del Sistema</flux:heading>
        <flux:subheading>Registro de todas las acciones realizadas por los usuarios</flux:subheading>
    </div>

    {{-- ========================================== --}}
    {{-- PANEL DE FILTROS                            --}}
    {{-- ========================================== --}}
    <div class="mb-4" x-data="{
        mostrarFiltros: window.innerWidth >= 640,
        get filtrosActivos() {
            let count = 0;
            if ($wire.busqueda) count++;
            if ($wire.filtroUsuario) count++;
            if ($wire.filtroAccion) count++;
            if ($wire.filtroEntidad) count++;
            if ($wire.fechaDesde) count++;
            if ($wire.fechaHasta) count++;
            return count;
        }
    }">
        {{-- Botón para mostrar/ocultar filtros en móvil --}}
        <div class="mb-3 sm:hidden">
            <flux:button
                @click="mostrarFiltros = !mostrarFiltros"
                variant="outline"
                icon="funnel"
                class="w-full relative"
            >
                <span x-text="mostrarFiltros ? 'Ocultar filtros' : 'Mostrar filtros'"></span>
                <span x-show="filtrosActivos > 0"
                      class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-blue-600 rounded-full"
                      x-text="filtrosActivos"></span>
            </flux:button>
        </div>

        {{-- Contenedor de filtros --}}
        <div x-show="mostrarFiltros"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="rounded-lg border border-neutral-200 bg-neutral-50 p-3 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

            <div class="space-y-3">
                {{-- Búsqueda (ancho completo) --}}
                <div>
                    <flux:input
                        wire:model.live.debounce.300ms="busqueda"
                        icon="magnifying-glass"
                        placeholder="Buscar..."
                        class="w-full"
                    />
                </div>

                {{-- Dropdowns + Fechas + Limpiar --}}
                <div class="grid grid-cols-2 gap-3 sm:flex sm:flex-row sm:flex-wrap sm:items-end">
                    {{-- Fecha desde --}}
                    <div>
                        <flux:input
                            type="date"
                            wire:model.live="fechaDesde"
                            label="Desde"
                        />
                    </div>

                    {{-- Fecha hasta --}}
                    <div>
                        <flux:input
                            type="date"
                            wire:model.live="fechaHasta"
                            label="Hasta"
                        />
                    </div>

                    {{-- Filtro por usuario --}}
                    <div class="w-full sm:w-auto">
                        <flux:dropdown>
                            <flux:button variant="outline" icon="user" icon-trailing="chevron-down">
                                {{ $filtroUsuario ? $this->usuarios->firstWhere('id', $filtroUsuario)?->name : 'Usuario' }}
                            </flux:button>
                            <flux:menu>
                                <flux:menu.item wire:click="$set('filtroUsuario', '')" icon="bars-3">
                                    Todos los usuarios
                                </flux:menu.item>
                                <flux:menu.separator />
                                @foreach($this->usuarios as $usuario)
                                    <flux:menu.item wire:click="$set('filtroUsuario', '{{ $usuario->id }}')" icon="user-circle">
                                        {{ $usuario->name }}
                                    </flux:menu.item>
                                @endforeach
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    {{-- Filtro por acción --}}
                    <div class="w-full sm:w-auto">
                        <flux:dropdown>
                            <flux:button variant="outline" icon="bolt" icon-trailing="chevron-down">
                                {{ $filtroAccion ? ucfirst($filtroAccion) : 'Acción' }}
                            </flux:button>
                            <flux:menu>
                                <flux:menu.item wire:click="$set('filtroAccion', '')" icon="bars-3">
                                    Todas las acciones
                                </flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item wire:click="$set('filtroAccion', 'crear')" icon="plus-circle">
                                    Crear
                                </flux:menu.item>
                                <flux:menu.item wire:click="$set('filtroAccion', 'actualizar')" icon="pencil-square">
                                    Actualizar
                                </flux:menu.item>
                                <flux:menu.item wire:click="$set('filtroAccion', 'eliminar')" icon="trash">
                                    Eliminar
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    {{-- Filtro por entidad --}}
                    <div class="w-full sm:w-auto">
                        <flux:dropdown>
                            <flux:button variant="outline" icon="cube" icon-trailing="chevron-down">
                                {{ $filtroEntidad ? (\App\Models\Auditoria::nombresEntidades()[$filtroEntidad] ?? $filtroEntidad) : 'Entidad' }}
                            </flux:button>
                            <flux:menu>
                                <flux:menu.item wire:click="$set('filtroEntidad', '')" icon="bars-3">
                                    Todas las entidades
                                </flux:menu.item>
                                <flux:menu.separator />
                                @foreach($this->entidades as $entidad)
                                    <flux:menu.item wire:click="$set('filtroEntidad', '{{ $entidad }}')" icon="document">
                                        {{ \App\Models\Auditoria::nombresEntidades()[$entidad] ?? $entidad }}
                                    </flux:menu.item>
                                @endforeach
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    {{-- Botón limpiar filtros --}}
                    <div class="w-full sm:w-auto">
                        <flux:button
                            wire:click="limpiarFiltros"
                            variant="outline"
                            icon="x-mark"
                        >
                            Limpiar
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- ========================================== --}}
    {{-- TABLA DE AUDITORÍAS                         --}}
    {{-- Muestra el listado paginado de registros    --}}
    {{-- ========================================== --}}
    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <div class="flex items-center gap-1">
                            Fecha
                            <flux:icon name="arrow-down" class="size-3" />
                            <span class="text-[10px] font-normal normal-case text-zinc-400">(más reciente)</span>
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Usuario
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Acción
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Entidad
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Descripción
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        IP
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Detalle
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                @forelse($auditorias as $auditoria)
                    <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        {{-- FECHA: Formato legible con hora --}}
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                            <div>{{ $auditoria->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-zinc-400">{{ $auditoria->created_at->format('H:i:s') }}</div>
                        </td>

                        {{-- USUARIO: Quién realizó la acción --}}
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            <div class="flex items-center gap-2">
                                {{-- Avatar con las iniciales del usuario --}}
                                @if($auditoria->user)
                                    <flux:avatar size="xs" name="{{ $auditoria->user->name }}" />
                                    <span class="text-zinc-800 dark:text-zinc-200">{{ $auditoria->user->name }}</span>
                                @else
                                    <flux:avatar size="xs" name="S" />
                                    <span class="text-zinc-400 italic">Sistema</span>
                                @endif
                            </div>
                        </td>

                        {{-- ACCIÓN: Badge con color según el tipo --}}
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            <flux:badge
                                size="sm"
                                color="{{ $auditoria->getColor() }}"
                                icon="{{ $auditoria->getIcono() }}"
                            >
                                {{ ucfirst($auditoria->accion) }}
                            </flux:badge>
                        </td>

                        {{-- ENTIDAD: Nombre legible del tipo de registro --}}
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $auditoria->getNombreEntidad() }}
                        </td>

                        {{-- DESCRIPCIÓN: Texto legible de lo que se hizo --}}
                        <td class="max-w-xs truncate px-4 py-3 text-sm text-zinc-800 dark:text-zinc-200" title="{{ $auditoria->descripcion }}">
                            {{ $auditoria->descripcion }}
                        </td>

                        {{-- IP: Dirección IP del usuario --}}
                        <td class="whitespace-nowrap px-4 py-3 text-xs font-mono text-zinc-400">
                            {{ $auditoria->ip ?? '-' }}
                        </td>

                        {{-- BOTÓN DE DETALLE: Solo aparece si hay datos para ver --}}
                        <td class="whitespace-nowrap px-4 py-3 text-center">
                            @if($auditoria->valores_anteriores || $auditoria->valores_nuevos)
                                <flux:button
                                    wire:click="verDetalle({{ $auditoria->id }})"
                                    size="xs"
                                    variant="subtle"
                                    icon="eye"
                                />
                            @else
                                <span class="text-xs text-zinc-300">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    {{-- Mensaje cuando no hay resultados --}}
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <flux:icon name="clipboard-document-list" class="size-12 text-zinc-300 dark:text-zinc-600" />
                                <p class="text-sm text-zinc-500">No se encontraron registros de auditoría</p>
                                @if($busqueda || $filtroUsuario || $filtroAccion || $filtroEntidad || $fechaDesde || $fechaHasta)
                                    <flux:button wire:click="limpiarFiltros" size="xs" variant="subtle">
                                        Limpiar filtros
                                    </flux:button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ========================================== --}}
    {{-- PAGINACIÓN                                  --}}
    {{-- Livewire genera los links automáticamente   --}}
    {{-- ========================================== --}}
    <div class="mt-4">
        {{ $auditorias->links() }}
    </div>

    {{-- ========================================== --}}
    {{-- MODAL DE DETALLE                            --}}
    {{-- Muestra los valores anteriores y nuevos     --}}
    {{-- cuando el usuario hace clic en "ver"        --}}
    {{-- ========================================== --}}
    <flux:modal wire:model="mostrarDetalle" class="max-w-2xl">
        @if($detalle)
            <div class="space-y-6">
                {{-- Encabezado del modal --}}
                <div>
                    <flux:heading size="lg">Detalle de Auditoría</flux:heading>
                    <flux:subheading>{{ $detalle->descripcion }}</flux:subheading>
                </div>

                {{-- Información general --}}
                <div class="grid grid-cols-2 gap-4 rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800">
                    <div>
                        <p class="text-xs font-medium uppercase text-zinc-500">Usuario</p>
                        <p class="text-sm text-zinc-800 dark:text-zinc-200">
                            {{ $detalle->user?->name ?? 'Sistema' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase text-zinc-500">Fecha y Hora</p>
                        <p class="text-sm text-zinc-800 dark:text-zinc-200">
                            {{ $detalle->created_at->format('d/m/Y H:i:s') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase text-zinc-500">Acción</p>
                        <flux:badge size="sm" color="{{ $detalle->getColor() }}" icon="{{ $detalle->getIcono() }}">
                            {{ ucfirst($detalle->accion) }}
                        </flux:badge>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase text-zinc-500">Entidad</p>
                        <p class="text-sm text-zinc-800 dark:text-zinc-200">
                            {{ $detalle->getNombreEntidad() }} #{{ $detalle->entidad_id }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase text-zinc-500">Dirección IP</p>
                        <p class="text-sm font-mono text-zinc-800 dark:text-zinc-200">
                            {{ $detalle->ip ?? '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase text-zinc-500">Navegador</p>
                        <p class="max-w-xs truncate text-sm text-zinc-800 dark:text-zinc-200" title="{{ $detalle->user_agent }}">
                            {{ Str::limit($detalle->user_agent, 50) ?? '-' }}
                        </p>
                    </div>
                </div>

                {{-- ================================== --}}
                {{-- SECCIÓN DE CAMBIOS                  --}}
                {{-- Para ACTUALIZAR: tabla comparativa  --}}
                {{-- Para CREAR: valores del nuevo       --}}
                {{-- Para ELIMINAR: valores del borrado  --}}
                {{-- ================================== --}}

                @if($detalle->accion === 'actualizar' && $detalle->getCambios())
                    {{-- ACTUALIZACIÓN: Muestra qué cambió (antes → después) --}}
                    <div>
                        <p class="mb-2 text-sm font-semibold text-zinc-700 dark:text-zinc-300">Campos modificados</p>
                        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-zinc-500">Campo</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-zinc-500">Valor Anterior</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-zinc-500">Valor Nuevo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($detalle->getCambios() as $campo => $valores)
                                        <tr>
                                            <td class="whitespace-nowrap px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                                {{ $campo }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-red-600 dark:text-red-400">
                                                @if(is_array($valores['anterior']))
                                                    <code class="text-xs">{{ json_encode($valores['anterior'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code>
                                                @else
                                                    {{ $valores['anterior'] ?? '—' }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-sm text-green-600 dark:text-green-400">
                                                @if(is_array($valores['nuevo']))
                                                    <code class="text-xs">{{ json_encode($valores['nuevo'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code>
                                                @else
                                                    {{ $valores['nuevo'] ?? '—' }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                @elseif($detalle->accion === 'crear' && $detalle->valores_nuevos)
                    {{-- CREACIÓN: Muestra los valores del registro nuevo --}}
                    <div>
                        <p class="mb-2 text-sm font-semibold text-zinc-700 dark:text-zinc-300">Valores del registro creado</p>
                        <div class="max-h-80 overflow-y-auto rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                            <dl class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                @foreach($detalle->valores_nuevos as $campo => $valor)
                                    <div>
                                        <dt class="text-xs font-medium uppercase text-zinc-500">{{ $campo }}</dt>
                                        <dd class="text-sm text-zinc-800 dark:text-zinc-200">
                                            @if(is_array($valor))
                                                <code class="text-xs">{{ json_encode($valor, JSON_UNESCAPED_UNICODE) }}</code>
                                            @else
                                                {{ $valor ?? '—' }}
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </div>

                @elseif($detalle->accion === 'eliminar' && $detalle->valores_anteriores)
                    {{-- ELIMINACIÓN: Muestra los valores del registro eliminado --}}
                    <div>
                        <p class="mb-2 text-sm font-semibold text-zinc-700 dark:text-zinc-300">Valores del registro eliminado</p>
                        <div class="max-h-80 overflow-y-auto rounded-lg border border-red-200 bg-red-50/50 p-3 dark:border-red-800 dark:bg-red-900/10">
                            <dl class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                @foreach($detalle->valores_anteriores as $campo => $valor)
                                    <div>
                                        <dt class="text-xs font-medium uppercase text-zinc-500">{{ $campo }}</dt>
                                        <dd class="text-sm text-zinc-800 dark:text-zinc-200">
                                            @if(is_array($valor))
                                                <code class="text-xs">{{ json_encode($valor, JSON_UNESCAPED_UNICODE) }}</code>
                                            @else
                                                {{ $valor ?? '—' }}
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </flux:modal>
</div>
