<div>
    {{-- Mensajes toast en esquina superior derecha --}}
    <x-toast type="success" :message="session('mensaje')" />
    <x-toast type="error" :message="session('error')" />

    {{-- Header de la página --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Registrar Entrada de Insumos</flux:heading>
        <flux:subheading>Registra el ingreso de insumos al inventario de una sucursal específica</flux:subheading>
    </div>

    {{-- Formulario de registro --}}
    <div class="max-w-4xl">
        <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow dark:border-neutral-700 dark:bg-neutral-800">
            <form wire:submit="registrarEntrada" class="space-y-6 p-6">
                {{-- Paso 1: Seleccionar sucursal --}}
                <div>
                    <flux:field>
                        <flux:label>
                            Sucursal <span class="text-red-500">*</span>
                        </flux:label>
                        <flux:select 
                            wire:model="sucursal_id" 
                            placeholder="Seleccione una sucursal"
                            name="sucursal_id"
                        >
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="sucursal_id" />
                        <flux:description>
                            El stock se maneja por sucursal. Seleccione la sucursal donde ingresará el insumo.
                        </flux:description>
                    </flux:field>
                </div>

                {{-- Paso 2: Seleccionar insumo --}}
                <div>
                    <flux:field>
                        <flux:label>
                            Insumo <span class="text-red-500">*</span>
                        </flux:label>
                        <flux:select 
                            wire:model.live="insumo_id" 
                            placeholder="Seleccione un insumo"
                            name="insumo_id"
                        >
                            @foreach($insumos as $insumo)
                                <option value="{{ $insumo->id }}">
                                    {{ $insumo->nombre }}
                                    @if($insumo->categoria)
                                        ({{ $insumo->categoria->nombre }})
                                    @endif
                                </option>
                            @endforeach
                        </flux:select>
                        <flux:error name="insumo_id" />
                    </flux:field>

                    {{-- Información del insumo seleccionado --}}
                    @if($insumoSeleccionado)
                        <div class="mt-3 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="grid gap-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">Insumo:</span>
                                    <span class="text-zinc-900 dark:text-white">{{ $insumoSeleccionado->nombre }}</span>
                                </div>
                                @if($insumoSeleccionado->categoria)
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-zinc-700 dark:text-zinc-300">Categoría:</span>
                                        <span class="text-zinc-900 dark:text-white">{{ $insumoSeleccionado->categoria->nombre }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">Unidad de medida:</span>
                                    <span class="text-zinc-900 dark:text-white">
                                        {{ $insumoSeleccionado->unidadMedida->nombre }} 
                                        ({{ $insumoSeleccionado->unidadMedida->abreviatura }})
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Paso 3: Ingresar cantidad --}}
                <div>
                    <flux:field>
                        <flux:label>
                            Cantidad <span class="text-red-500">*</span>
                        </flux:label>
                        <div class="flex items-center gap-2">
                            <div class="flex-1">
                                <flux:input 
                                    wire:model="cantidad" 
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="0.00"
                                    name="cantidad"
                                />
                            </div>
                            @if($insumoSeleccionado)
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-2 dark:border-zinc-700 dark:bg-zinc-800">
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        {{ $insumoSeleccionado->unidadMedida->abreviatura }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <flux:error name="cantidad" />
                        <flux:description>
                            Ingrese la cantidad del insumo que está agregando al inventario.
                        </flux:description>
                    </flux:field>
                </div>

                {{-- Paso 4: Seleccionar motivo --}}
                <div>
                    <flux:field>
                        <flux:label>
                            Motivo de entrada <span class="text-red-500">*</span>
                        </flux:label>
                        <flux:select 
                            wire:model="motivo" 
                            placeholder="Seleccione el motivo"
                            name="motivo"
                        >
                            @foreach(\App\Livewire\Insumos\RegistrarEntradaInsumos::MOTIVOS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="motivo" />
                        <flux:description>
                            Importante para auditoría: registre el motivo del ingreso del insumo.
                        </flux:description>
                    </flux:field>
                </div>

                {{-- Paso 5: Observación (opcional) --}}
                <div>
                    <flux:field>
                        <flux:label>
                            Observación
                        </flux:label>
                        <flux:textarea 
                            wire:model="observacion"
                            rows="3"
                            placeholder="Detalles adicionales sobre esta entrada (opcional)..."
                            name="observacion"
                        />
                        <flux:error name="observacion" />
                        <flux:description>
                            Opcional: agregue cualquier información adicional relevante sobre esta entrada.
                        </flux:description>
                    </flux:field>
                </div>

                {{-- Botones de acción --}}
                <div class="flex items-center justify-end gap-3 border-t border-zinc-200 pt-6 dark:border-zinc-700">
                    <flux:button 
                        type="button"
                        wire:click="cancelar"
                        variant="ghost"
                    >
                        Cancelar
                    </flux:button>
                    <flux:button 
                        type="submit"
                        variant="primary"
                        icon="check"
                    >
                        Registrar Entrada
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
