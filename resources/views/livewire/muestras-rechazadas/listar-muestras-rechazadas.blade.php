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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
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
</div>
