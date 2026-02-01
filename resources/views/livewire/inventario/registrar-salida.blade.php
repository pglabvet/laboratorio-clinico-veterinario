<div class="space-y-6">
    {{-- Encabezado --}}
    <div>
        <flux:heading size="xl" class="mb-1">Registrar Salida Manual</flux:heading>
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
                @foreach($sucursales as $sucursal)
                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
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
                @foreach($insumos as $insumo)
                    <option value="{{ $insumo->id }}">
                        {{ $insumo->nombre }}
                    </option>
                @endforeach
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
                    wire:model="observacion"
                    label="Observación"
                    placeholder="Describa detalladamente el motivo de la salida (mínimo 10 caracteres)"
                    rows="4"
                    :error="$errors->first('observacion')"
                />
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Esta información es importante para la auditoría del inventario
                </p>
            </div>
        </div>

        {{-- Botones de acción --}}
        <div class="mt-6 flex gap-3">
            <flux:button 
                wire:click="abrirConfirmacion"
                variant="primary"
                icon="arrow-down-tray"
                :disabled="!$sucursal_id || !$insumo_id || !$cantidad || !$motivo || !$observacion"
            >
                Registrar Salida
            </flux:button>
            
            <flux:button 
                wire:click="resetearFormulario"
                variant="ghost"
                icon="arrow-path"
            >
                Limpiar
            </flux:button>
        </div>
    </div>

    {{-- Historial reciente --}}
    <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700">
        <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Salidas Recientes</flux:heading>
                
                <div class="w-64">
                    <flux:select 
                        wire:model.live="filtroSucursal"
                        placeholder="Todas las sucursales"
                    >
                        <option value="">Todas las sucursales</option>
                        @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                        @endforeach
                    </flux:select>
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
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <flux:badge 
                                    :color="match($movimiento->motivo) {
                                        'MERMA' => 'orange',
                                        'VENCIMIENTO' => 'red',
                                        'USO_EXTRAORDINARIO' => 'blue',
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
                            <td colspan="6" class="px-6 py-12 text-center">
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
                    variant="ghost"
                >
                    Cancelar
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
