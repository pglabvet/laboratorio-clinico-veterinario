<div>
    {{-- Mensajes toast --}}
    <x-toast type="success" :message="session('success')" />
    <x-toast type="error" :message="session('error')" />

    {{-- Header --}}
    <div class="mb-4 flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="mb-1">Muestras Rechazadas</flux:heading>
            <flux:subheading>Registro de muestras que no pudieron ser procesadas en el laboratorio</flux:subheading>
        </div>
        @can('crear-muestras-rechazadas')
        <flux:button
            href="{{ route('muestras-rechazadas.crear') }}"
            wire:navigate
            icon="plus"
            variant="primary"
        >
            Registrar Muestra Rechazada
        </flux:button>
        @endcan
    </div>

    {{-- Bloque de filtros --}}
    <div class="mb-4" x-data="{
        mostrarFiltros: window.innerWidth >= 640,
        get filtrosActivos() {
            let count = 0;
            if ($wire.buscar) count++;
            if ($wire.filtroMotivo) count++;
            if ($wire.filtroVeterinaria) count++;
            if ($wire.filtroSucursal) count++;
            if ($wire.filtroDesde) count++;
            if ($wire.filtroHasta) count++;
            return count;
        }
    }">
        <div class="mb-3 sm:hidden">
            <flux:button @click="mostrarFiltros = !mostrarFiltros" variant="outline" icon="funnel" class="w-full relative">
                <span x-text="mostrarFiltros ? 'Ocultar filtros' : 'Mostrar filtros'"></span>
                <span x-show="filtrosActivos > 0" class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-blue-600 rounded-full" x-text="filtrosActivos"></span>
            </flux:button>
        </div>

        <div x-show="mostrarFiltros"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="rounded-lg border border-neutral-200 bg-neutral-50 p-3 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div class="space-y-3">
                <div>
                    <flux:input wire:model.live.debounce.300ms="buscar" icon="magnifying-glass" placeholder="Buscar por código, paciente, propietario..." class="w-full" />
                </div>
                <div class="grid grid-cols-2 gap-3 sm:flex sm:flex-row sm:flex-wrap sm:items-end">
                    <div>
                        <flux:input type="date" wire:model.live="filtroDesde" label="Desde" />
                    </div>
                    <div>
                        <flux:input type="date" wire:model.live="filtroHasta" label="Hasta" />
                    </div>

                    {{-- Filtro Motivo --}}
                    <div class="w-full sm:w-auto">
                        <flux:dropdown>
                            <flux:button variant="outline" icon="exclamation-triangle" icon-trailing="chevron-down">
                                {{ $filtroMotivo ? Str::limit($filtroMotivo, 16) : 'Motivo' }}
                            </flux:button>
                            <flux:menu>
                                <flux:menu.item wire:click="$set('filtroMotivo', '')" icon="bars-3">
                                    Todos los motivos
                                </flux:menu.item>
                                <flux:menu.separator />
                                @foreach($this->todosMotivosFiltro as $motivo)
                                    <flux:menu.item wire:click="$set('filtroMotivo', '{{ addslashes($motivo) }}')" icon="x-circle">
                                        {{ $motivo }}
                                    </flux:menu.item>
                                @endforeach
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    {{-- Filtro Veterinaria --}}
                    <div class="w-full sm:w-auto">
                        <flux:dropdown>
                            <flux:button variant="outline" icon="building-office" icon-trailing="chevron-down">
                                {{ $filtroVeterinaria ? Str::limit($this->veterinarias->firstWhere('id', $filtroVeterinaria)?->nombre, 12) : 'Veterinaria' }}
                            </flux:button>
                            <flux:menu>
                                <flux:menu.item wire:click="$set('filtroVeterinaria', '')" icon="bars-3">Todas las veterinarias</flux:menu.item>
                                <flux:menu.separator />
                                @foreach($this->veterinarias as $veterinaria)
                                    <flux:menu.item wire:click="$set('filtroVeterinaria', '{{ $veterinaria->id }}')" icon="building-office">{{ $veterinaria->nombre }}</flux:menu.item>
                                @endforeach
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    {{-- Filtro Sucursal --}}
                    <div class="w-full sm:w-auto">
                        <flux:dropdown>
                            <flux:button variant="outline" icon="building-storefront" icon-trailing="chevron-down">
                                {{ $filtroSucursal ? Str::limit($this->sucursales->firstWhere('id', $filtroSucursal)?->nombre, 12) : 'Sucursal' }}
                            </flux:button>
                            <flux:menu>
                                <flux:menu.item wire:click="$set('filtroSucursal', '')" icon="bars-3">Todas las sucursales</flux:menu.item>
                                <flux:menu.separator />
                                @foreach($this->sucursales as $sucursal)
                                    <flux:menu.item wire:click="$set('filtroSucursal', '{{ $sucursal->id }}')" icon="building-storefront">{{ $sucursal->nombre }}</flux:menu.item>
                                @endforeach
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    <div class="w-full sm:w-auto">
                        <flux:button wire:click="limpiarFiltros" variant="outline" icon="x-mark">Limpiar</flux:button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-md ring-1 ring-neutral-200/50 dark:border-neutral-700 dark:bg-neutral-800 dark:ring-neutral-700/50">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">Código</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">Paciente</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">Especie</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">Veterinaria</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">Tipo Muestra</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">Motivo de Rechazo</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">Fecha Rechazo</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">Registrado por</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                    @forelse($muestras as $muestra)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors duration-150 border-l-4 border-l-red-400"
                            wire:key="rechazada-{{ $muestra->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-800 dark:bg-red-900/20 dark:text-red-400">
                                    {{ $muestra->codigo_muestra }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $muestra->paciente_nombre }}</span>
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $muestra->propietario_nombre }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">{{ $muestra->especie->nombre ?? 'N/A' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">{{ $muestra->veterinaria->nombre ?? 'N/A' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">{{ $muestra->tipo_muestra }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300 max-w-xs">
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/20 dark:text-amber-400"
                                      title="{{ $muestra->observaciones }}">
                                    {{ Str::limit($muestra->motivo_rechazo, 35) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                <div class="flex flex-col">
                                    <span>{{ $muestra->fecha_rechazo->format('d/m/Y') }}</span>
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $muestra->fecha_rechazo->format('H:i:s') }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">{{ $muestra->registradoPor->name ?? '-' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    @can('mostrar-detalle-muestra-rechazada')
                                    <flux:button
                                        wire:click="ver({{ $muestra->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="eye"
                                        title="Ver detalles"
                                    />
                                    @endcan

                                    @can('editar-muestras-rechazadas')
                                    <flux:button
                                        href="{{ route('muestras-rechazadas.editar', $muestra->id) }}"
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil-square"
                                        title="Editar"
                                    />
                                    @endcan

                                    @can('eliminar-muestras-rechazadas')
                                    <flux:button
                                        wire:click="confirmarEliminar({{ $muestra->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="trash"
                                        title="Eliminar"
                                    />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="mb-3 h-12 w-12 text-neutral-400 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <flux:heading size="lg" class="mb-1">No hay muestras rechazadas</flux:heading>
                                    <flux:subheading>
                                        @if($buscar)
                                            No se encontraron registros con "{{ $buscar }}"
                                        @else
                                            No se han registrado muestras rechazadas aún
                                        @endif
                                    </flux:subheading>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($muestras->hasPages())
            <div class="border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-700 dark:bg-neutral-900">
                {{ $muestras->links() }}
            </div>
        @endif
    </div>
    {{-- Modal para ver detalles --}}
    <flux:modal wire:model="modalVer" class="w-full max-w-2xl">
        @if($muestraAVer)
            <div class="space-y-5">
                {{-- Encabezado --}}
                <div class="pb-4 border-b border-neutral-200 dark:border-neutral-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-semibold text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->paciente_nombre }}</h2>
                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/20 dark:text-red-400">Rechazada</span>
                        </div>
                    </div>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Código: <span class="font-mono font-medium">{{ $muestraAVer->codigo_muestra }}</span></p>
                </div>

                {{-- Datos del Paciente --}}
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 divide-y divide-neutral-200 dark:divide-neutral-700 overflow-hidden bg-white dark:bg-neutral-800/50">
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-indigo-500 dark:text-indigo-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Propietario</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->propietario_nombre }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-amber-500 dark:text-amber-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Especie / Raza</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->especie->nombre ?? 'N/A' }} <span class="text-neutral-400">&mdash;</span> {{ $muestraAVer->raza ?: 'Sin raza' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-pink-500 dark:text-pink-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Edad / Sexo</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->edad }} <span class="text-neutral-400">&middot;</span> {{ $muestraAVer->sexo == 'M' ? 'Macho' : 'Hembra' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Datos de la Muestra --}}
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 divide-y divide-neutral-200 dark:divide-neutral-700 overflow-hidden bg-white dark:bg-neutral-800/50">
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Veterinaria</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->veterinaria->nombre ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-emerald-500 dark:text-emerald-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Sucursal</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->sucursal->nombre ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-cyan-500 dark:text-cyan-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19 14.5M14.25 3.104c.251.023.501.05.75.082M19 14.5l-2.47 2.47a2.25 2.25 0 0 1-1.59.659H9.06a2.25 2.25 0 0 1-1.591-.659L5 14.5m14 0-3.375-3.375M5 14.5l3.375-3.375" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Tipo de Muestra</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->tipo_muestra }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-violet-500 dark:text-violet-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Fecha de Rechazo</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->fecha_rechazo->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 bg-red-50 dark:bg-red-900/10 hover:bg-red-100 dark:hover:bg-red-900/20 transition-colors">
                        <svg class="w-5 h-5 text-red-500 dark:text-red-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-red-600 dark:text-red-400 mb-0.5">Motivo de Rechazo</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->motivo_rechazo }}</p>
                        </div>
                    </div>
                    @if($muestraAVer->observaciones)
                        <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                            <svg class="w-5 h-5 text-orange-500 dark:text-orange-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Observaciones</p>
                                <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->observaciones }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Registrado por</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->registradoPor->name ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Botón cerrar --}}
                <div class="flex justify-end pt-2">
                    <flux:button type="button" wire:click="cerrarModalVer" variant="primary">Cerrar</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Modal de confirmación para eliminar --}}
    <flux:modal wire:model="modalEliminar" class="w-full max-w-md">
        <div class="space-y-6">
            <div class="flex justify-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
            </div>
            <div class="text-center">
                <flux:heading size="lg" class="mb-2">Eliminar Muestra Rechazada</flux:heading>
                <flux:subheading>¿Estás seguro de que deseas eliminar este registro? Esta acción no se puede deshacer.</flux:subheading>
            </div>
            <div class="flex justify-end gap-3">
                <flux:button type="button" wire:click="cancelarEliminar" variant="ghost">Cancelar</flux:button>
                <flux:button type="button" wire:click="eliminar" variant="danger" icon="trash">Eliminar</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
