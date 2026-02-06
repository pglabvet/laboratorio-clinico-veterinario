<div class="space-y-6">
    {{-- Encabezado --}}
    <div>
        <flux:heading size="xl" class="mb-1">Registrar Entrada de Insumos</flux:heading>
        <flux:subheading>Registra el ingreso de insumos al inventario de una sucursal</flux:subheading>
    </div>

    {{-- Mensajes de alerta --}}
    <x-toast type="success" :message="session('mensaje')" />
    <x-toast type="error" :message="session('error')" />

    {{-- Formulario de registro --}}
    <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6">
        <flux:heading size="lg" class="mb-6">Datos de la Entrada</flux:heading>

        <form wire:submit="registrarEntrada">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Sucursal --}}
                <flux:select 
                    wire:model.live="sucursal_id"
                    label="Sucursal"
                    placeholder="Seleccione..."
                    :error="$errors->first('sucursal_id')"
                >
                    <option value="">Seleccione...</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                    @endforeach
                </flux:select>

                {{-- Insumo --}}
                <flux:select 
                    wire:model.live="insumo_id"
                    label="Insumo"
                    placeholder="Seleccione..."
                    :error="$errors->first('insumo_id')"
                >
                    <option value="">Seleccione...</option>
                    @foreach($insumos as $insumo)
                        <option value="{{ $insumo->id }}">
                            {{ $insumo->nombre }}
                            @if($insumo->categoria)
                                ({{ $insumo->categoria->nombre }})
                            @endif
                        </option>
                    @endforeach
                </flux:select>

                {{-- Cantidad --}}
                <flux:input 
                    wire:model="cantidad"
                    label="Cantidad a Ingresar"
                    type="number"
                    step="0.01"
                    min="0.01"
                    placeholder="0.00"
                    :error="$errors->first('cantidad')"
                />

                {{-- Motivo --}}
                <flux:select 
                    wire:model="motivo"
                    label="Motivo de Entrada"
                    placeholder="Seleccione..."
                    :error="$errors->first('motivo')"
                >
                    <option value="">Seleccione...</option>
                    @foreach($motivosDisponibles as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </flux:select>

                {{-- Observación --}}
                <div class="md:col-span-2">
                    <flux:textarea 
                        wire:model="observacion"
                        label="Observación"
                        placeholder="Detalles adicionales sobre esta entrada (opcional)"
                        rows="3"
                        :error="$errors->first('observacion')"
                    />
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Opcional: agregue cualquier información adicional relevante sobre esta entrada.
                    </p>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="mt-6 flex gap-3">
                <flux:button 
                    type="submit"
                    variant="primary"
                    icon="arrow-up-tray"
                >
                    Registrar Entrada
                </flux:button>
                
                <flux:button 
                    type="button"
                    wire:click="cancelar"
                    variant="outline"
                    icon="arrow-path"
                >
                    Limpiar
                </flux:button>
            </div>
        </form>
    </div>

    {{-- Historial reciente --}}
    <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700">
        <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <flux:heading size="lg">Entradas Recientes</flux:heading>
                
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    {{-- Filtro de Sucursal --}}
                    <flux:dropdown>
                        <flux:button variant="outline" icon="building-office-2" icon-trailing="chevron-down">
                            {{ $filtroSucursalEntradas ? $sucursales->firstWhere('id', $filtroSucursalEntradas)?->nombre : 'Sucursales' }}
                        </flux:button>

                        <flux:menu>
                            <flux:menu.item wire:click="$set('filtroSucursalEntradas', '')" icon="bars-3">
                                Todas las sucursales
                            </flux:menu.item>
                            <flux:menu.separator />
                            @foreach($sucursales as $sucursal)
                                <flux:menu.item wire:click="$set('filtroSucursalEntradas', '{{ $sucursal->id }}')" icon="building-storefront">
                                    {{ $sucursal->nombre }}
                                </flux:menu.item>
                            @endforeach
                        </flux:menu>
                    </flux:dropdown>

                    {{-- Filtro de Período --}}
                    <flux:dropdown>
                        <flux:button variant="outline" icon="calendar" icon-trailing="chevron-down">
                            {{ match($filtroFechaEntradas) {
                                'hoy' => 'Hoy',
                                'ayer' => 'Ayer',
                                'ultimos_7_dias' => 'Últimos 7 días',
                                'esta_semana' => 'Esta semana',
                                'este_mes' => 'Este mes',
                                'este_año' => 'Año actual',
                                default => 'Período'
                            } }}
                        </flux:button>

                        <flux:menu>
                            <flux:menu.item wire:click="$set('filtroFechaEntradas', '')" icon="bars-3">
                                Todos
                            </flux:menu.item>
                            <flux:menu.separator />
                            <flux:menu.item wire:click="$set('filtroFechaEntradas', 'hoy')" icon="sun">
                                Hoy
                            </flux:menu.item>
                            <flux:menu.item wire:click="$set('filtroFechaEntradas', 'ayer')" icon="arrow-uturn-left">
                                Ayer
                            </flux:menu.item>
                            <flux:menu.item wire:click="$set('filtroFechaEntradas', 'ultimos_7_dias')" icon="calendar-days">
                                Últimos 7 días
                            </flux:menu.item>
                            <flux:menu.separator />
                            <flux:menu.item wire:click="$set('filtroFechaEntradas', 'esta_semana')" icon="calendar">
                                Esta semana
                            </flux:menu.item>
                            <flux:menu.item wire:click="$set('filtroFechaEntradas', 'este_mes')" icon="calendar">
                                Este mes
                            </flux:menu.item>
                            <flux:menu.item wire:click="$set('filtroFechaEntradas', 'este_año')" icon="calendar">
                                Año actual
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Fecha
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Sucursal
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Insumo
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Cantidad
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Motivo
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Usuario
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                    @forelse($entradasRecientes as $entrada)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $entrada->fecha->format('d/m/Y H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $entrada->sucursal->nombre }}
                            </td>
                            <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $entrada->insumo->nombre }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span class="font-medium text-green-600 dark:text-green-400">
                                    +{{ number_format($entrada->cantidad, 2) }} 
                                    {{ $entrada->insumo->unidadMedida->abreviatura ?? '' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <flux:badge 
                                    :color="match($entrada->motivo) {
                                        'COMPRA' => 'green',
                                        'DEVOLUCIÓN' => 'blue',
                                        'AJUSTE' => 'amber',
                                        default => 'zinc'
                                    }"
                                    size="sm"
                                >
                                    {{ $motivosDisponibles[$entrada->motivo] ?? $entrada->motivo }}
                                </flux:badge>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $entrada->usuario->name }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-neutral-500 dark:text-neutral-400">
                                    <flux:icon.arrow-up-tray class="mb-3 size-12" />
                                    <p class="text-sm">No hay entradas registradas</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($entradasRecientes->hasPages())
            <div class="border-t border-neutral-200 dark:border-neutral-700 px-6 py-4">
                {{ $entradasRecientes->links() }}
            </div>
        @endif
    </div>
</div>
