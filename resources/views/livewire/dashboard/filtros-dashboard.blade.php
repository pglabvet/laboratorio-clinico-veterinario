<div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
    {{-- Título --}}
    <div class="mb-3 border-b border-zinc-200 pb-2 dark:border-zinc-700">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Filtros</h3>
    </div>

    <div class="space-y-4">
        {{-- Filtros de Fecha con Dropdown --}}
        <div class="flex flex-wrap items-center gap-3">
            <flux:dropdown>
                <flux:button variant="outline" icon="calendar-days" icon-trailing="chevron-down" size="sm">
                    {{ $periodoSeleccionado }}
                </flux:button>

                <flux:menu>
                    <flux:menu.item wire:click="filtrarHoy" icon="sun">
                        Hoy
                    </flux:menu.item>
                    <flux:menu.item wire:click="filtrarAyer" icon="arrow-uturn-left">
                        Ayer
                    </flux:menu.item>
                    <flux:menu.item wire:click="filtrarUltimos7Dias" icon="calendar">
                        Últimos 7 días
                    </flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item wire:click="filtrarEstaSemana" icon="calendar-days">
                        Esta semana
                    </flux:menu.item>
                    <flux:menu.item wire:click="filtrarEsteMes" icon="calendar-days">
                        Este mes
                    </flux:menu.item>
                    <flux:menu.item wire:click="filtrarAnioActual" icon="calendar-days">
                        Año actual
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>

            @can('filtrar-por-sucursal')
            @if(count($sucursales) > 0)
            <flux:dropdown>
                <flux:button variant="outline" icon="building-office" icon-trailing="chevron-down" size="sm">
                    {{ $sucursalSeleccionada }}
                </flux:button>

                <flux:menu>
                    <flux:menu.item wire:click="seleccionarSucursal('', 'Todas')" icon="bars-3">
                        Todas
                    </flux:menu.item>
                    <flux:menu.separator />
                    @foreach($sucursales as $sucursal)
                        <flux:menu.item wire:click="seleccionarSucursal('{{ $sucursal->id }}', '{{ $sucursal->nombre }}')" icon="building-office">
                            {{ $sucursal->nombre }}
                        </flux:menu.item>
                    @endforeach
                </flux:menu>
            </flux:dropdown>
            @endif
            @endcan

            @if($fechaInicio || $sucursalId)
            <flux:button 
                wire:click="limpiarFiltros"
                variant="ghost"
                size="sm"
                icon="x-mark"
            >
                Limpiar
            </flux:button>
            @endif
        </div>
    </div>
</div>
