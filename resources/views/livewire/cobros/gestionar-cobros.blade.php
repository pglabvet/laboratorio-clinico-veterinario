@php
    $colorEstado = fn ($estado) => match ($estado) {
        'Pagado' => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        'Parcial' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400',
        'Exonerado' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
        default => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
    };
    $bordeEstado = fn ($estado) => match ($estado) {
        'Pagado' => 'border-l-green-400',
        'Parcial' => 'border-l-amber-400',
        'Exonerado' => 'border-l-blue-400',
        default => 'border-l-red-400',
    };
@endphp

<div>
    {{-- Mensajes toast --}}
    <x-toast type="success" :message="session('success')" />
    <x-toast type="error" :message="session('error')" />

    {{-- Header --}}
    <div class="mb-4 flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="mb-1">Cobros</flux:heading>
            <flux:subheading>Registro de cobros y pagos de las muestras recepcionadas</flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            @can('exportar-cobros')
            <flux:dropdown>
                <flux:button variant="outline" icon="arrow-down-tray" icon-trailing="chevron-down">
                    Exportar
                </flux:button>

                <flux:menu>
                    <flux:menu.item
                        icon="document-text"
                        x-on:click.prevent="
                            const params = new URLSearchParams({
                                buscar: $wire.buscar || '',
                                fecha_desde: $wire.filtroDesde || '',
                                fecha_hasta: $wire.filtroHasta || '',
                                estado: $wire.filtroEstado || '',
                                metodo: $wire.filtroMetodo || '',
                                veterinaria_id: $wire.filtroVeterinaria || '',
                                sucursal_id: $wire.filtroSucursal || '',
                            });
                            window.open('{{ route('cobros.exportar.pdf') }}?' + params.toString(), '_blank');
                        "
                    >
                        Exportar PDF
                    </flux:menu.item>
                    <flux:menu.item
                        icon="table-cells"
                        x-on:click.prevent="
                            const params = new URLSearchParams({
                                buscar: $wire.buscar || '',
                                fecha_desde: $wire.filtroDesde || '',
                                fecha_hasta: $wire.filtroHasta || '',
                                estado: $wire.filtroEstado || '',
                                metodo: $wire.filtroMetodo || '',
                                veterinaria_id: $wire.filtroVeterinaria || '',
                                sucursal_id: $wire.filtroSucursal || '',
                            });
                            window.location.href = '{{ route('cobros.exportar.excel') }}?' + params.toString();
                        "
                    >
                        Exportar Excel
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
            @endcan
        </div>
    </div>

    {{-- Bloque de filtros --}}
    <div class="mb-4" x-data="{
        mostrarFiltros: window.innerWidth >= 640,
        get filtrosActivos() {
            let count = 0;
            if ($wire.buscar) count++;
            if ($wire.filtroEstado) count++;
            if ($wire.filtroMetodo) count++;
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

                    {{-- Filtro Estado --}}
                    <div class="w-full sm:w-auto">
                        <flux:dropdown>
                            <flux:button variant="outline" icon="banknotes" icon-trailing="chevron-down">
                                {{ $filtroEstado ?: 'Estado' }}
                            </flux:button>
                            <flux:menu>
                                <flux:menu.item wire:click="$set('filtroEstado', '')" icon="bars-3">Todos los estados</flux:menu.item>
                                <flux:menu.separator />
                                @foreach($this->estados as $estado)
                                    <flux:menu.item wire:click="$set('filtroEstado', '{{ $estado }}')" icon="banknotes">{{ $estado }}</flux:menu.item>
                                @endforeach
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    {{-- Filtro Método --}}
                    <div class="w-full sm:w-auto">
                        <flux:dropdown>
                            <flux:button variant="outline" icon="credit-card" icon-trailing="chevron-down">
                                {{ $filtroMetodo ?: 'Método' }}
                            </flux:button>
                            <flux:menu>
                                <flux:menu.item wire:click="$set('filtroMetodo', '')" icon="bars-3">Todos los métodos</flux:menu.item>
                                <flux:menu.separator />
                                @foreach($this->metodosFiltro as $metodo)
                                    <flux:menu.item wire:click="$set('filtroMetodo', '{{ $metodo }}')" icon="credit-card">{{ $metodo }}</flux:menu.item>
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
                    @can('filtro-de-sucursal-cobro')
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
                    @endcan

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
                        @foreach(['Fecha', 'Código', 'Paciente', 'Veterinaria', 'Sucursal', 'Examen', 'Total', 'Método', 'Estado', 'Observación', 'Acciones'] as $columna)
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">{{ $columna }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                    @forelse($cobros as $cobro)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors duration-150 border-l-4 {{ $bordeEstado($cobro->estado) }}"
                            wire:key="cobro-{{ $cobro->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                <div class="flex flex-col">
                                    <span>{{ $cobro->muestra->fecha_recepcion?->format('d/m/Y') }}</span>
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $cobro->muestra->fecha_recepcion?->format('H:i') }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                    {{ $cobro->muestra->codigo_muestra }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $cobro->muestra->paciente_nombre }}</span>
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $cobro->muestra->propietario_nombre }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">{{ $cobro->muestra->veterinaria->nombre ?? 'N/A' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">{{ $cobro->muestra->sucursal->nombre ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300 max-w-xs">
                                <span title="{{ $cobro->examen }}">{{ Str::limit($cobro->examen, 40) ?: '-' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                                Bs {{ number_format((float) $cobro->total, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                @if($cobro->metodo)
                                    <span class="inline-flex items-center rounded-full bg-neutral-100 px-2.5 py-0.5 text-xs font-medium text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200">
                                        {{ $cobro->metodo }}
                                    </span>
                                @else
                                    <span class="text-neutral-400">-</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex w-fit items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $colorEstado($cobro->estado) }}">
                                        {{ $cobro->estado }}
                                    </span>
                                    @if($cobro->estado === 'Parcial')
                                        <span class="text-xs text-neutral-500 dark:text-neutral-400">debe Bs {{ number_format($cobro->saldo, 2) }}</span>
                                    @elseif($cobro->estado === 'Pendiente' && (float) $cobro->total == 0)
                                        <span class="text-xs text-neutral-500 dark:text-neutral-400">sin monto</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300 max-w-xs">
                                <span title="{{ $cobro->observacion }}">{{ Str::limit($cobro->observacion, 35) ?: '-' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    @can('mostrar-detalle-cobro')
                                    <flux:button wire:click="ver({{ $cobro->id }})" variant="ghost" size="sm" icon="eye" title="Ver detalle" />
                                    @endcan

                                    @can('registrar-pago-cobro')
                                        @if(! in_array($cobro->estado, ['Pagado', 'Exonerado']))
                                        <flux:button wire:click="abrirPago({{ $cobro->id }})" variant="ghost" size="sm" icon="banknotes" title="Registrar pago" />
                                        @endif
                                    @endcan

                                    @can('editar-cobros')
                                    <flux:button wire:click="editar({{ $cobro->id }})" variant="ghost" size="sm" icon="pencil-square" title="Editar" />
                                    @endcan

                                    @can('exonerar-cobro')
                                        @if($cobro->exonerado)
                                        <flux:button wire:click="quitarExoneracion({{ $cobro->id }})" wire:confirm="¿Quitar la exoneración? El estado volverá a calcularse según los pagos." variant="ghost" size="sm" icon="arrow-uturn-left" title="Quitar exoneración" />
                                        @else
                                        <flux:button wire:click="confirmarExonerar({{ $cobro->id }})" variant="ghost" size="sm" icon="gift" title="Exonerar" />
                                        @endif
                                    @endcan

                                    @can('eliminar-cobros')
                                    <flux:button wire:click="confirmarEliminar({{ $cobro->id }})" variant="ghost" size="sm" icon="trash" title="Eliminar" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="mb-3 h-12 w-12 text-neutral-400 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                    </svg>
                                    <flux:heading size="lg" class="mb-1">No hay cobros</flux:heading>
                                    <flux:subheading>
                                        @if($buscar)
                                            No se encontraron registros con "{{ $buscar }}"
                                        @else
                                            Los cobros se crean al registrar una muestra
                                        @endif
                                    </flux:subheading>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cobros->hasPages())
            <div class="border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-700 dark:bg-neutral-900">
                {{ $cobros->links() }}
            </div>
        @endif
    </div>

    {{-- Modal ver detalle --}}
    <flux:modal wire:model="modalVer" class="w-full max-w-2xl">
        @if($cobroAVer)
            <div class="space-y-5">
                {{-- Encabezado --}}
                <div class="pb-4 border-b border-neutral-200 dark:border-neutral-700">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-semibold text-neutral-900 dark:text-neutral-100">{{ $cobroAVer->muestra->paciente_nombre }}</h2>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $colorEstado($cobroAVer->estado) }}">{{ $cobroAVer->estado }}</span>
                    </div>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                        Código: <span class="font-mono font-medium">{{ $cobroAVer->muestra->codigo_muestra }}</span>
                        &middot; {{ $cobroAVer->muestra->veterinaria->nombre ?? 'N/A' }}
                        &middot; {{ $cobroAVer->muestra->sucursal->nombre ?? 'N/A' }}
                    </p>
                </div>

                {{-- Resumen --}}
                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-3 bg-white dark:bg-neutral-800/50">
                        <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Total</p>
                        <p class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Bs {{ number_format((float) $cobroAVer->total, 2) }}</p>
                    </div>
                    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-3 bg-white dark:bg-neutral-800/50">
                        <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Pagado</p>
                        <p class="text-lg font-semibold text-green-600 dark:text-green-400">Bs {{ number_format((float) $cobroAVer->total_pagado, 2) }}</p>
                    </div>
                    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-3 bg-white dark:bg-neutral-800/50">
                        <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Saldo</p>
                        <p class="text-lg font-semibold {{ $cobroAVer->exonerado ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $cobroAVer->exonerado ? 'Exonerado' : 'Bs '.number_format($cobroAVer->saldo, 2) }}
                        </p>
                    </div>
                </div>

                {{-- Datos del cobro --}}
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 divide-y divide-neutral-200 dark:divide-neutral-700 overflow-hidden bg-white dark:bg-neutral-800/50">
                    <div class="px-4 py-3.5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Examen</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $cobroAVer->examen ?: '-' }}</p>
                    </div>
                    <div class="px-4 py-3.5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Método</p>
                        <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $cobroAVer->metodo ?? '-' }}</p>
                    </div>
                    @if($cobroAVer->observacion)
                        <div class="px-4 py-3.5">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Observación</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $cobroAVer->observacion }}</p>
                        </div>
                    @endif
                </div>

                {{-- Historial de pagos --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-2">Historial de pagos</p>
                    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700 text-sm">
                            <thead class="bg-neutral-50 dark:bg-neutral-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase text-neutral-700 dark:text-neutral-300">Fecha</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase text-neutral-700 dark:text-neutral-300">Monto</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase text-neutral-700 dark:text-neutral-300">Método</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase text-neutral-700 dark:text-neutral-300">Registrado por</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700 bg-white dark:bg-neutral-800">
                                @forelse($cobroAVer->pagos as $pago)
                                    <tr wire:key="pago-{{ $pago->id }}" class="{{ $pago->estaAnulado() ? 'opacity-60' : '' }}">
                                        <td class="px-4 py-2 text-neutral-700 dark:text-neutral-300">{{ $pago->fecha_pago->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-2 font-medium text-neutral-900 dark:text-neutral-100 {{ $pago->estaAnulado() ? 'line-through' : '' }}">Bs {{ number_format((float) $pago->monto, 2) }}</td>
                                        <td class="px-4 py-2 text-neutral-700 dark:text-neutral-300">{{ $pago->metodo }}</td>
                                        <td class="px-4 py-2 text-neutral-700 dark:text-neutral-300">
                                            {{ $pago->registradoPor->name ?? '-' }}
                                            @if($pago->estaAnulado())
                                                <span class="block text-xs text-red-600 dark:text-red-400" title="{{ $pago->motivo_anulacion }}">
                                                    Anulado por {{ $pago->anuladoPor->name ?? '-' }}: {{ Str::limit($pago->motivo_anulacion, 30) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            @can('anular-pago-cobro')
                                                @unless($pago->estaAnulado())
                                                <flux:button wire:click="confirmarAnularPago({{ $pago->id }})" variant="ghost" size="sm" icon="no-symbol" title="Anular pago" />
                                                @endunless
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-4 text-center text-neutral-500 dark:text-neutral-400">Aún no se registraron pagos</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <flux:button type="button" wire:click="cerrarModalVer" variant="primary">Cerrar</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Modal registrar pago --}}
    <flux:modal wire:model="modalPago" class="w-full max-w-md">
        <form wire:submit="guardarPago" class="space-y-6">
            <div>
                <flux:heading size="lg">Registrar Pago</flux:heading>
                <flux:subheading>El estado se actualiza automáticamente según lo pagado.</flux:subheading>
            </div>
            <flux:input wire:model="montoPago" type="number" step="0.01" min="0.01" label="Monto (Bs)" />
            <flux:select wire:model="metodoPago" label="Método de pago">
                @foreach($this->metodos as $metodo)
                    <flux:select.option value="{{ $metodo }}">{{ $metodo }}</flux:select.option>
                @endforeach
            </flux:select>
            <div class="flex justify-end gap-3">
                <flux:button type="button" wire:click="$set('modalPago', false)" variant="ghost">Cancelar</flux:button>
                <flux:button type="submit" variant="primary" icon="banknotes">Registrar Pago</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Modal editar --}}
    <flux:modal wire:model="modalEditar" class="w-full max-w-lg">
        <form wire:submit="guardarEdicion" class="space-y-6">
            <div>
                <flux:heading size="lg">Editar Cobro</flux:heading>
                <flux:subheading>El total no puede ser menor a lo ya pagado.</flux:subheading>
            </div>
            <flux:input wire:model="total" type="number" step="0.01" min="0" label="Total (Bs)" />
            <flux:input wire:model="examen" label="Examen" />
            <flux:textarea wire:model="observacion" label="Observación" rows="3" />
            <div class="flex justify-end gap-3">
                <flux:button type="button" wire:click="$set('modalEditar', false)" variant="ghost">Cancelar</flux:button>
                <flux:button type="submit" variant="primary">Guardar</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Modal exonerar --}}
    <flux:modal wire:model="modalExonerar" class="w-full max-w-md">
        <form wire:submit="exonerar" class="space-y-6">
            <div>
                <flux:heading size="lg">Exonerar Cobro</flux:heading>
                <flux:subheading>La muestra dejará de contar como deuda. El motivo se guarda en la observación.</flux:subheading>
            </div>
            <flux:textarea wire:model="motivoExoneracion" label="Motivo" rows="3" placeholder="Ej. Cortesía, análisis repetido por error del laboratorio..." />
            <div class="flex justify-end gap-3">
                <flux:button type="button" wire:click="$set('modalExonerar', false)" variant="ghost">Cancelar</flux:button>
                <flux:button type="submit" variant="primary" icon="gift">Exonerar</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Modal anular pago --}}
    <flux:modal wire:model="modalAnularPago" class="w-full max-w-md">
        <form wire:submit="anularPago" class="space-y-6">
            <div>
                <flux:heading size="lg">Anular Pago</flux:heading>
                <flux:subheading>El pago quedará en el historial como anulado y el saldo se recalculará.</flux:subheading>
            </div>
            <flux:input wire:model="motivoAnulacion" label="Motivo" placeholder="Ej. Monto registrado por error" />
            <div class="flex justify-end gap-3">
                <flux:button type="button" wire:click="$set('modalAnularPago', false)" variant="ghost">Cancelar</flux:button>
                <flux:button type="submit" variant="danger" icon="no-symbol">Anular</flux:button>
            </div>
        </form>
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
                <flux:heading size="lg" class="mb-2">Eliminar Cobro</flux:heading>
                <flux:subheading>¿Estás seguro de que deseas eliminar este cobro y su historial de pagos?</flux:subheading>
            </div>
            <div class="flex justify-end gap-3">
                <flux:button type="button" wire:click="cancelarEliminar" variant="ghost">Cancelar</flux:button>
                <flux:button type="button" wire:click="eliminar" variant="danger" icon="trash">Eliminar</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
