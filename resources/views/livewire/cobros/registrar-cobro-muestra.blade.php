<div>
    <x-toast type="success" :message="session('mensaje_cobro')" />

    <flux:modal wire:model.live="modalAbierto" class="w-full max-w-2xl">
        @if($cobro)
            <form wire:submit="guardar" class="space-y-5">
                {{-- Encabezado --}}
                <div class="pb-4 border-b border-neutral-200 dark:border-neutral-700">
                    <flux:heading size="lg">Registrar Cobro</flux:heading>
                    <flux:subheading>Complete el cobro de la muestra recién registrada. Si lo omite, quedará como Pendiente en el módulo Cobros.</flux:subheading>
                </div>

                {{-- Datos autocompletados desde la muestra --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-900 p-4">
                    @foreach([
                        'Fecha' => $cobro->muestra->fecha_recepcion?->format('d/m/Y H:i'),
                        'Código' => $cobro->muestra->codigo_muestra,
                        'Paciente' => $cobro->muestra->paciente_nombre,
                        'Veterinaria' => $cobro->muestra->veterinaria->nombre ?? 'N/A',
                        'Sucursal' => $cobro->muestra->sucursal->nombre ?? 'N/A',
                    ] as $etiqueta => $valor)
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">{{ $etiqueta }}</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $valor }}</p>
                        </div>
                    @endforeach
                </div>

                <flux:input wire:model="examen" label="Examen" />

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <flux:input wire:model="total" type="number" step="0.01" min="0" label="Total (Bs)" />
                    <flux:input wire:model="montoPago" type="number" step="0.01" min="0" label="Pago inicial (Bs)" placeholder="Opcional" />
                    <flux:select wire:model="metodoPago" label="Método de pago">
                        @foreach($metodos as $metodo)
                            <flux:select.option value="{{ $metodo }}">{{ $metodo }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:textarea wire:model="observacion" label="Observación" rows="2" />

                <div class="flex justify-end gap-3 border-t border-neutral-200 dark:border-neutral-700 pt-4">
                    <flux:button type="button" wire:click="cerrar" variant="ghost">Omitir</flux:button>
                    <flux:button type="submit" variant="primary" icon="banknotes">Guardar Cobro</flux:button>
                </div>
            </form>
        @endif
    </flux:modal>
</div>
