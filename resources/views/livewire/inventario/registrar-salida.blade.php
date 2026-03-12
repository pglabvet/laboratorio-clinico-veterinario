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
            <flux:heading size="xl">Registrar Salida Manual</flux:heading>
        </div>
        <flux:subheading>Disminuir stock por merma, vencimiento o uso extraordinario</flux:subheading>
    </div>

    {{-- Mensajes de alerta --}}
    <x-toast type="success" :message="session('mensaje')" />
    <x-toast type="danger" :message="session('error')" />

    {{-- Formulario de registro --}}
    <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6">
        <flux:heading size="lg" class="mb-6">Datos de la Salida</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Sucursal --}}
            <flux:select 
                wire:model.live="sucursal_id"
                label="Sucursal"
                placeholder="Seleccione una sucursal"
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
                placeholder="Todas las categorías"
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
                placeholder="Seleccione un insumo"
                :error="$errors->first('insumo_id')"
                :disabled="!$sucursal_id"
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

            {{-- Stock Actual (Solo lectura) --}}
            @if($stockActual !== null)
                <div>
                    <flux:input 
                        label="Stock Actual"
                        value="{{ number_format($stockActual, 2) }} {{ $unidadMedida }}"
                        readonly
                        disabled
                    />
                    @if($stockActual <= 0)
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">
                            ⚠️ Sin stock disponible
                        </p>
                    @endif
                </div>
            @endif

            {{-- Cantidad a Retirar --}}
            <flux:input 
                wire:model.live="cantidad"
                label="Cantidad a Retirar"
                type="number"
                step="0.01"
                min="0.01"
                placeholder="0.00"
                :error="$errors->first('cantidad')"
                :disabled="!$insumo_id || !$sucursal_id"
            />

            {{-- Preview de costo PEPS --}}
            @if($costoEstimado)
                <div class="md:col-span-2">
                    <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-800 dark:text-blue-300">Costo estimado PEPS</p>
                                <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">Bs {{ number_format($costoEstimado['costo_total'], 2) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-blue-600 dark:text-blue-400">Costo unit. promedio</p>
                                <p class="text-sm font-semibold text-blue-800 dark:text-blue-200">Bs {{ number_format($costoEstimado['costo_unitario_promedio'], 2) }}/ud</p>
                            </div>
                        </div>
                        @if(count($detalleLotes) > 1)
                            <div class="mt-3 border-t border-blue-200 pt-3 dark:border-blue-700">
                                <p class="text-xs font-medium text-blue-700 dark:text-blue-400 mb-2">Desglose por lotes (PEPS):</p>
                                @foreach($detalleLotes as $lote)
                                    <div class="flex justify-between text-xs text-blue-600 dark:text-blue-300">
                                        <span>Lote {{ $lote['fecha_entrada'] }} — {{ number_format($lote['cantidad_consumida'], 2) }} uds × Bs {{ number_format($lote['costo_unitario'], 2) }}</span>
                                        <span class="font-medium">Bs {{ number_format($lote['costo_subtotal'], 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Motivo --}}
            <flux:select 
                wire:model="motivo"
                label="Motivo de Salida"
                placeholder="Seleccione un motivo"
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
                    wire:model.live="observacion"
                    label="Observación"
                    placeholder="Describa detalladamente el motivo de la salida (mínimo 10 caracteres)"
                    rows="4"
                    :error="$errors->first('observacion')"
                />
                <div class="mt-1 flex items-center justify-between">
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                        Esta información es importante para la auditoría del inventario
                    </p>
                    <p class="text-sm {{ strlen($observacion ?? '') >= 10 ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">
                        {{ strlen($observacion ?? '') }}/10 caracteres
                    </p>
                </div>
            </div>
        </div>

        {{-- Botones de acción --}}
        <div class="mt-6 flex gap-3">
            <flux:button 
                wire:click="abrirConfirmacion"
                variant="primary"
                icon="arrow-down-tray"
                :disabled="!$sucursal_id || !$insumo_id || !$cantidad || $cantidad <= 0 || !$motivo || !$observacion || strlen($observacion) < 10"
            >
                Registrar Salida
            </flux:button>
            
            <flux:button 
                wire:click="resetearFormulario"
                variant="outline"
                icon="arrow-path"
            >
                Limpiar
            </flux:button>
        </div>
    </div>

    {{-- Historial reciente --}}
    <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700">
        <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex flex-col gap-4">
                <flux:heading size="lg">Salidas Recientes</flux:heading>
                
                {{-- Filtros --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    {{-- Buscador --}}
                    <flux:input 
                        wire:model.live.debounce.300ms="busquedaSalidas"
                        placeholder="Buscar insumo, motivo..."
                        icon="magnifying-glass"
                        clearable
                    />

                    {{-- Filtro de Sucursal --}}
                    <flux:select wire:model.live="filtroSucursal">
                        <option value="">Todas las sucursales</option>
                        @foreach($this->sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                        @endforeach
                    </flux:select>

                    {{-- Fecha Desde --}}
                    <flux:input 
                        wire:model.live="fechaDesdeSalidas"
                        type="date"
                        placeholder="Desde"
                        icon="calendar"
                    />

                    {{-- Fecha Hasta --}}
                    <flux:input 
                        wire:model.live="fechaHastaSalidas"
                        type="date"
                        placeholder="Hasta"
                        icon="calendar"
                    />
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
                            Costo PEPS
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
                    @forelse($movimientos as $movimiento)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $movimiento->fecha->format('d/m/Y H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $movimiento->sucursal->nombre }}
                            </td>
                            <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $movimiento->insumo->nombre }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span class="font-medium text-red-600 dark:text-red-400">
                                    {{ number_format(abs($movimiento->cantidad), 2) }} 
                                    {{ $movimiento->insumo->unidadMedida->abreviatura ?? '' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                @if($movimiento->costo_total > 0)
                                    Bs {{ number_format($movimiento->costo_total, 2) }}
                                @else
                                    <span class="text-neutral-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <flux:badge 
                                    :color="match($movimiento->motivo) {
                                        'MERMA' => 'red',
                                        'VENCIMIENTO' => 'red',
                                        'USO_EXTRAORDINARIO' => 'orange',
                                        'CONSUMO_ANALISIS' => 'sky',
                                        'OTRO' => 'zinc',
                                        default => 'zinc'
                                    }"
                                    size="sm"
                                >
                                    {{ $motivosDisponibles[$movimiento->motivo] ?? $movimiento->motivo }}
                                </flux:badge>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $movimiento->usuario->name }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-neutral-500 dark:text-neutral-400">
                                    <flux:icon.arrow-down-tray class="mb-3 size-12" />
                                    <p class="text-sm">No hay salidas registradas</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movimientos->hasPages())
            <div class="border-t border-neutral-200 dark:border-neutral-700 px-6 py-4">
                {{ $movimientos->links() }}
            </div>
        @endif
    </div>

    {{-- Modal de confirmación --}}
    <flux:modal wire:model="modalConfirmacion" class="w-full max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Confirmar Salida Manual</flux:heading>
                <flux:subheading>Verifique que los datos sean correctos</flux:subheading>
            </div>

            <div class="space-y-4 rounded-lg bg-neutral-50 p-4 dark:bg-neutral-800">
                <div class="flex justify-between">
                    <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Sucursal:</span>
                    <span class="text-sm text-neutral-900 dark:text-neutral-100">{{ $sucursalNombre }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Insumo:</span>
                    <span class="text-sm text-neutral-900 dark:text-neutral-100">{{ $insumoNombre }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Cantidad a retirar:</span>
                    <span class="text-sm font-bold text-red-600 dark:text-red-400">
                        {{ number_format((float) $cantidad, 2) }} {{ $unidadMedida }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Stock actual:</span>
                    <span class="text-sm text-neutral-900 dark:text-neutral-100">
                        {{ number_format((float) $stockActual, 2) }} {{ $unidadMedida }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Stock resultante:</span>
                    <span class="text-sm font-bold text-neutral-900 dark:text-neutral-100">
                        {{ number_format((float) $stockActual - (float) $cantidad, 2) }} {{ $unidadMedida }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Motivo:</span>
                    <span class="text-sm text-neutral-900 dark:text-neutral-100">
                        {{ $motivosDisponibles[$motivo] ?? $motivo }}
                    </span>
                </div>

                @if($costoEstimado)
                    <div class="border-t border-neutral-200 pt-3 dark:border-neutral-600">
                        <div class="flex justify-between">
                            <span class="text-sm font-bold text-blue-700 dark:text-blue-300">Costo PEPS total:</span>
                            <span class="text-sm font-bold text-blue-700 dark:text-blue-300">Bs {{ number_format($costoEstimado['costo_total'], 2) }}</span>
                        </div>
                        @if(count($detalleLotes) > 0)
                            <div class="mt-2 space-y-1">
                                <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Lotes a consumir:</p>
                                @foreach($detalleLotes as $lote)
                                    <div class="flex justify-between text-xs text-neutral-600 dark:text-neutral-300">
                                        <span>Lote {{ $lote['fecha_entrada'] }}: {{ number_format($lote['cantidad_consumida'], 2) }} × Bs {{ number_format($lote['costo_unitario'], 2) }}</span>
                                        <span>Bs {{ number_format($lote['costo_subtotal'], 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            @if($stockActual !== null && $cantidad && ((float) $stockActual - (float) $cantidad < 0))
                <div class="rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-400">
                    <div class="flex items-start gap-3">
                        <flux:icon.exclamation-triangle class="size-5 flex-shrink-0" />
                        <div>
                            <strong class="font-semibold">Advertencia:</strong>
                            Esta operación generará un stock negativo. Por favor, verifique la cantidad.
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex gap-3">
                <flux:button 
                    wire:click="registrarSalida"
                    variant="danger"
                    class="flex-1"
                >
                    Confirmar Salida
                </flux:button>
                <flux:button 
                    wire:click="cerrarConfirmacion"
                    variant="outline"
                    class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950"
                >
                    Cancelar
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
