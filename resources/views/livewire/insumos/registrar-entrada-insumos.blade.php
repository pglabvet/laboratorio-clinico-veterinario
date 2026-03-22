<div class="space-y-6">
    {{-- Encabezado --}}
    <div>
        <div class="flex items-center gap-4 mb-2">
            <flux:button 
                wire:click="cancelar"
                variant="outline"
                icon="arrow-left"
                size="sm"
                class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950"
            >
                Volver
            </flux:button>
            <flux:heading size="xl">Registrar Entrada de Insumos</flux:heading>
        </div>
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
                    :error="$errors->first('sucursal_id')"
                >
                    <option value="">Seleccione...</option>
                    @foreach($this->sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                    @endforeach
                </flux:select>

                {{-- Categoría de Insumo (Filtro) --}}
                <flux:select 
                    wire:model.live="filtro_categoria"
                    label="Categoría de Insumo"
                >
                    <option value="">Todas las categorías</option>
                    @foreach($this->categorias as $categoria)
                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                    @endforeach
                </flux:select>

                {{-- Insumo --}}
                <flux:select 
                    wire:model.live="insumo_id"
                    label="Insumo"
                    :error="$errors->first('insumo_id')"
                >
                    <option value="">Seleccione...</option>
                    @if($insumos->isEmpty())
                        <option disabled>
                            {{ $filtro_categoria ? 'No hay insumos en esta categoría' : 'No hay insumos disponibles' }}
                        </option>
                    @else
                        @foreach($insumos as $insumo)
                            <option value="{{ $insumo->id }}">{{ $insumo->nombre }}</option>
                        @endforeach
                    @endif
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

                {{-- Costo Unitario --}}
                <flux:input 
                    wire:model="costo_unitario"
                    label="Costo Unitario (Bs)"
                    type="number"
                    step="0.01"
                    min="0.01"
                    placeholder="0.00"
                    :error="$errors->first('costo_unitario')"
                />

                {{-- Opcionales: Lote y Vencimiento --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:col-span-2 bg-neutral-50 dark:bg-neutral-800/50 p-4 rounded-lg border border-neutral-100 dark:border-neutral-700/50">
                    <div class="md:col-span-2 mb-2">
                        <flux:heading size="sm" class="text-neutral-500">Datos de Lote (Opcional)</flux:heading>
                    </div>

                    {{-- Código de Lote --}}
                    <flux:input 
                        wire:model="codigo_lote"
                        label="Código de Lote / Serie"
                        placeholder="Ej. B4022"
                        :error="$errors->first('codigo_lote')"
                    />

                    {{-- Fecha de Vencimiento --}}
                    <flux:input 
                        wire:model="fecha_vencimiento"
                        label="Fecha de Vencimiento"
                        type="date"
                        min="{{ date('Y-m-d') }}"
                        :error="$errors->first('fecha_vencimiento')"
                    />
                </div>

                {{-- Motivo --}}
                <flux:select 
                    wire:model="motivo"
                    label="Motivo de Entrada"
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
                    wire:click="limpiarFormulario"
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
        <div class="p-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex flex-col gap-3">
                <flux:heading size="lg">Entradas Recientes</flux:heading>

                {{-- Panel filtros estilo muestras --}}
                <div x-data="{
                    mostrarFiltros: window.innerWidth >= 640,
                    get filtrosActivos() {
                        let count = 0;
                        if ($wire.busquedaEntradas) count++;
                        if ($wire.filtroSucursalEntradas) count++;
                        if ($wire.fechaDesdeEntradas) count++;
                        if ($wire.fechaHastaEntradas) count++;
                        return count;
                    }
                }">
                    {{-- Toggle móvil --}}
                    <div class="mb-2 sm:hidden">
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
                        <div class="flex flex-col sm:flex-row sm:items-end gap-3">
                            {{-- Búsqueda --}}
                            <div class="flex-1 min-w-0">
                                <flux:input
                                    wire:model.live.debounce.300ms="busquedaEntradas"
                                    icon="magnifying-glass"
                                    placeholder="Buscar insumo, motivo..."
                                    class="w-full"
                                />
                            </div>
                            {{-- Desde --}}
                            <div class="w-full sm:w-auto">
                                <flux:input type="date" wire:model.live="fechaDesdeEntradas" label="Desde" />
                            </div>
                            {{-- Hasta --}}
                            <div class="w-full sm:w-auto">
                                <flux:input type="date" wire:model.live="fechaHastaEntradas" label="Hasta" />
                            </div>
                            {{-- Sucursal --}}
                            <div class="w-full sm:w-auto">
                                <flux:dropdown>
                                    <flux:button variant="outline" icon="building-office-2" icon-trailing="chevron-down">
                                        {{ $filtroSucursalEntradas ? $this->sucursales->firstWhere('id', $filtroSucursalEntradas)?->nombre : 'Sucursal' }}
                                    </flux:button>
                                    <flux:menu>
                                        <flux:menu.item wire:click="$set('filtroSucursalEntradas', '')" icon="bars-3">Todas las sucursales</flux:menu.item>
                                        <flux:menu.separator />
                                        @foreach($this->sucursales as $sucursal)
                                            <flux:menu.item wire:click="$set('filtroSucursalEntradas', '{{ $sucursal->id }}')" icon="building-storefront">{{ $sucursal->nombre }}</flux:menu.item>
                                        @endforeach
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                            {{-- Limpiar --}}
                            <div class="w-full sm:w-auto">
                                <flux:button wire:click="limpiarFiltrosHistorial" variant="outline" icon="x-mark">
                                    Limpiar
                                </flux:button>
                            </div>
                        </div>
                    </div>
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
                            Costo Unit.
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Costo Total
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
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                Bs {{ number_format($entrada->costo_unitario, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                Bs {{ number_format($entrada->costo_total, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <flux:badge 
                                    :color="match($entrada->motivo) {
                                        'COMPRA' => 'green',
                                        'DEVOLUCION' => 'blue',
                                        'AJUSTE_INVENTARIO' => 'amber',
                                        'OTRO' => 'zinc',
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
                            <td colspan="8" class="px-6 py-12 text-center">
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
