<div>
    {{-- ========================================== --}}
    {{-- ENCABEZADO: Título y descripción            --}}
    {{-- ========================================== --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-1">Auditorías del Sistema</flux:heading>
        <flux:subheading>Quién hizo qué, dónde y cuándo en el sistema</flux:subheading>
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
                        placeholder="Buscar por código de muestra o descripción..."
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
                                {{ ['crear' => 'Creaciones', 'actualizar' => 'Modificaciones', 'eliminar' => 'Eliminaciones'][$filtroAccion] ?? 'Acción' }}
                            </flux:button>
                            <flux:menu>
                                <flux:menu.item wire:click="$set('filtroAccion', '')" icon="bars-3">
                                    Todas las acciones
                                </flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item wire:click="$set('filtroAccion', 'crear')" icon="plus-circle">
                                    Creaciones
                                </flux:menu.item>
                                <flux:menu.item wire:click="$set('filtroAccion', 'actualizar')" icon="pencil-square">
                                    Modificaciones
                                </flux:menu.item>
                                <flux:menu.item wire:click="$set('filtroAccion', 'eliminar')" icon="trash">
                                    Eliminaciones
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    {{-- Filtro por entidad --}}
                    <div class="w-full sm:w-auto">
                        <flux:dropdown>
                            <flux:button variant="outline" icon="cube" icon-trailing="chevron-down">
                                {{ $filtroEntidad ? (\App\Models\Auditoria::nombresEntidades()[$filtroEntidad] ?? $filtroEntidad) : 'Módulo' }}
                            </flux:button>
                            <flux:menu>
                                <flux:menu.item wire:click="$set('filtroEntidad', '')" icon="bars-3">
                                    Todos los módulos
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


    @php
        $colores = [
            'green' => 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
            'amber' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
            'blue' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
            'red' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
            'violet' => 'bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400',
            'zinc' => 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
        ];
    @endphp

    {{-- ========================================== --}}
    {{-- TABLA: Cuándo | Quién | Qué hizo | Dónde    --}}
    {{-- ========================================== --}}
    <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-md ring-1 ring-neutral-200/50 dark:border-neutral-700 dark:bg-neutral-800 dark:ring-neutral-700/50">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        @foreach(['Cuándo', 'Quién', 'Qué hizo', 'Dónde', ''] as $columna)
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">{{ $columna }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                    @forelse($auditorias as $auditoria)
                        @php $evento = $eventos[$auditoria->id]; @endphp
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors duration-150" wire:key="auditoria-{{ $auditoria->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                <div class="flex flex-col">
                                    <span>{{ $auditoria->created_at->format('d/m/Y') }}</span>
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $auditoria->created_at->format('H:i') }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <flux:avatar size="xs" name="{{ $evento['usuario'] }}" />
                                    <span class="text-neutral-800 dark:text-neutral-200">{{ $evento['usuario'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-neutral-800 dark:text-neutral-200">
                                <div class="flex items-start gap-2">
                                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $colores[$evento['color']] ?? $colores['zinc'] }}">
                                        <flux:icon :name="$evento['icono']" class="size-3.5" />
                                    </span>
                                    <div class="min-w-0">
                                        <p>{{ $evento['texto'] }}</p>
                                        @if($evento['motivo'])
                                            <p class="text-xs text-red-600 dark:text-red-400">Motivo: {{ Str::limit($evento['motivo'], 80) }}</p>
                                        @endif
                                        @foreach(array_slice($evento['detalles'], 0, 2) as $cambio)
                                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                                {{ $cambio['campo'] }}: <span class="line-through">{{ Str::limit($cambio['antes'], 30) }}</span> → <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ Str::limit($cambio['despues'], 30) }}</span>
                                            </p>
                                        @endforeach
                                        @if(count($evento['detalles']) > 2)
                                            <p class="text-xs text-neutral-400">y {{ count($evento['detalles']) - 2 }} cambio(s) más</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">{{ $evento['donde'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <flux:button wire:click="verDetalle({{ $auditoria->id }})" variant="ghost" size="sm" icon="eye" title="Ver más" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <flux:icon name="clipboard-document-list" class="size-12 text-neutral-300 dark:text-neutral-600" />
                                    <p class="text-sm text-neutral-500">No se encontraron movimientos</p>
                                    @if($busqueda || $filtroUsuario || $filtroAccion || $filtroEntidad || $fechaDesde || $fechaHasta)
                                        <flux:button wire:click="limpiarFiltros" size="xs" variant="subtle">Limpiar filtros</flux:button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($auditorias->hasPages())
            <div class="border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-700 dark:bg-neutral-900">
                {{ $auditorias->links() }}
            </div>
        @endif
    </div>

    {{-- ========================================== --}}
    {{-- MODAL "VER MÁS": detalle legible           --}}
    {{-- ========================================== --}}
    <flux:modal wire:model="mostrarDetalle" class="w-full max-w-2xl">
        @if($detalle && $eventoDetalle)
            <div class="space-y-5">
                <div class="pb-4 border-b border-neutral-200 dark:border-neutral-700">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $colores[$eventoDetalle['color']] ?? $colores['zinc'] }}">
                            <flux:icon :name="$eventoDetalle['icono']" class="size-5" />
                        </span>
                        <div>
                            <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                                {{ $eventoDetalle['usuario'] }} {{ lcfirst($eventoDetalle['texto']) }}
                            </h2>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                {{ $detalle->created_at->format('d/m/Y H:i') }} · {{ $eventoDetalle['donde'] }}
                            </p>
                        </div>
                    </div>
                </div>

                @if($eventoDetalle['motivo'])
                    <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/10 dark:text-red-400">
                        <span class="font-semibold">Motivo:</span> {{ $eventoDetalle['motivo'] }}
                    </div>
                @endif

                {{-- Cambios: antes → después --}}
                @if(count($eventoDetalle['detalles']))
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Qué cambió</p>
                        <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                            <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-neutral-700">
                                <thead class="bg-neutral-50 dark:bg-neutral-900">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-neutral-700 dark:text-neutral-300">Dato</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-neutral-700 dark:text-neutral-300">Antes</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-neutral-700 dark:text-neutral-300">Después</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                                    @foreach($eventoDetalle['detalles'] as $cambio)
                                        <tr>
                                            <td class="px-4 py-2 font-medium text-neutral-700 dark:text-neutral-300">{{ $cambio['campo'] }}</td>
                                            <td class="px-4 py-2 text-red-600 dark:text-red-400">{{ $cambio['antes'] }}</td>
                                            <td class="px-4 py-2 text-green-600 dark:text-green-400">{{ $cambio['despues'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Datos del registro creado o eliminado --}}
                @if(count($datosDetalle))
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                            {{ $detalle->accion === 'eliminar' ? 'Datos que tenía' : 'Datos registrados' }}
                        </p>
                        <dl class="grid max-h-72 grid-cols-1 gap-3 overflow-y-auto rounded-xl border border-neutral-200 p-4 sm:grid-cols-2 dark:border-neutral-700">
                            @foreach($datosDetalle as $dato)
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">{{ $dato['campo'] }}</dt>
                                    <dd class="text-sm text-neutral-900 dark:text-neutral-100">{{ $dato['valor'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif

                {{-- Información técnica (oculta por defecto) --}}
                <details class="rounded-lg bg-neutral-50 px-4 py-3 text-sm dark:bg-neutral-900">
                    <summary class="cursor-pointer text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Información técnica</summary>
                    <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <p><span class="text-neutral-500">Dirección IP:</span> <span class="font-mono">{{ $detalle->ip ?? '-' }}</span></p>
                        <p><span class="text-neutral-500">Registro:</span> {{ $detalle->getNombreEntidad() }} #{{ $detalle->entidad_id }}</p>
                        <p class="sm:col-span-2 break-all"><span class="text-neutral-500">Navegador:</span> {{ $detalle->user_agent ?? '-' }}</p>
                    </div>
                </details>

                <div class="flex justify-end">
                    <flux:button type="button" wire:click="cerrarDetalle" variant="primary">Cerrar</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
