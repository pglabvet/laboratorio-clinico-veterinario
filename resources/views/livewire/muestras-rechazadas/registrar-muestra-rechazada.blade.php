<div>
    {{-- Mensajes toast --}}
    <x-toast type="success" :message="session('mensaje')" />
    <x-toast type="error" :message="session('error')" />

    {{-- Header --}}
    <div class="mb-6">
        <flux:button
            wire:click="cancelar"
            variant="outline"
            icon="arrow-left"
            size="sm"
            class="mb-3 border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950"
        >
            Volver
        </flux:button>
        <flux:heading size="xl" class="mb-1">Registrar Muestra Rechazada</flux:heading>
        <flux:subheading>Complete el formulario con los datos del paciente y el motivo de rechazo</flux:subheading>
    </div>

    {{-- Formulario --}}
    <form wire:submit="guardar">
        <div class="space-y-6">

            {{-- Sección 1: Información del Paciente --}}
            <div class="rounded-lg border border-neutral-200 bg-white p-6 shadow dark:border-neutral-700 dark:bg-neutral-900">
                <h3 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                    Información del Paciente
                </h3>

                {{-- Fila 1: Veterinaria | Sucursal --}}
                <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-2 mb-6">
                    <div>
                        <flux:label>Veterinaria <span class="text-red-500">*</span></flux:label>
                        <flux:select
                            wire:model="veterinaria_id"
                            :error="$errors->first('veterinaria_id')"
                            class="mt-1"
                        >
                            <option value="">Seleccione una veterinaria</option>
                            @foreach($this->veterinarias as $veterinaria)
                                <option value="{{ $veterinaria->id }}">{{ $veterinaria->nombre }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div>
                        <flux:label>Sucursal <span class="text-red-500">*</span></flux:label>
                        <flux:select
                            wire:model="sucursal_id"
                            :error="$errors->first('sucursal_id')"
                            class="mt-1"
                        >
                            <option value="">Seleccione una sucursal</option>
                            @foreach($this->sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>

                {{-- Fila 2: Nombre Paciente | Nombre Propietario --}}
                <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-2 mb-6">
                    <div>
                        <flux:label>Nombre del Paciente <span class="text-red-500">*</span></flux:label>
                        <flux:input
                            wire:model="paciente_nombre"
                            placeholder="Ej: Max"
                            :error="$errors->first('paciente_nombre')"
                            class="mt-1"
                        />
                    </div>
                    <div>
                        <flux:label>Nombre del Propietario <span class="text-red-500">*</span></flux:label>
                        <flux:input
                            wire:model="propietario_nombre"
                            placeholder="Ej: Juan Pérez"
                            :error="$errors->first('propietario_nombre')"
                            class="mt-1"
                        />
                    </div>
                </div>

                {{-- Fila 3: Especie | Raza | Edad | Sexo --}}
                <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-2 lg:grid-cols-4 mb-6">
                    <div>
                        <flux:label>Especie <span class="text-red-500">*</span></flux:label>
                        <flux:select
                            wire:model="especie_id"
                            :error="$errors->first('especie_id')"
                            class="mt-1"
                        >
                            <option value="">Seleccione una especie</option>
                            @foreach($this->especies as $especie)
                                <option value="{{ $especie->id }}">{{ $especie->nombre }}</option>
                            @endforeach
                        </flux:select>
                    </div>

                    <flux:input
                        wire:model="raza"
                        label="Raza"
                        placeholder="Ej: Golden Retriever"
                    />

                    <div>
                        <flux:label>Edad <span class="text-red-500">*</span></flux:label>
                        <div class="mt-1 flex gap-2">
                            <div class="w-24">
                                <flux:input
                                    wire:model="edadCantidad"
                                    type="number"
                                    min="0"
                                    max="999"
                                    placeholder="Ej: 3"
                                    :error="$errors->first('edadCantidad')"
                                />
                            </div>
                            <div class="flex-1">
                                <flux:select wire:model="edadUnidad">
                                    <option value="años">Año(s)</option>
                                    <option value="meses">Mes(es)</option>
                                    <option value="semanas">Semana(s)</option>
                                    <option value="días">Día(s)</option>
                                </flux:select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <flux:label>Sexo <span class="text-red-500">*</span></flux:label>
                        <flux:select
                            wire:model="sexo"
                            :error="$errors->first('sexo')"
                            class="mt-1"
                        >
                            <option value="M">Macho</option>
                            <option value="H">Hembra</option>
                        </flux:select>
                    </div>
                </div>
            </div>

            {{-- Sección 2: Información de la Muestra --}}
            <div class="rounded-lg border border-neutral-200 bg-white p-6 shadow dark:border-neutral-700 dark:bg-neutral-900">
                <h3 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                    Información de la Muestra
                </h3>

                <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-2">
                    <div>
                        <flux:label>Tipo de Muestra <span class="text-red-500">*</span></flux:label>
                        <flux:input
                            wire:model="tipo_muestra"
                            placeholder="Ej: Sangre, Orina, Heces"
                            :error="$errors->first('tipo_muestra')"
                            class="mt-1"
                        />
                    </div>
                </div>
            </div>

            {{-- Sección 3: Motivo de Rechazo --}}
            <div class="rounded-lg border border-neutral-200 bg-white p-6 shadow dark:border-neutral-700 dark:bg-neutral-900">
                <h3 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                    Motivo de Rechazo
                </h3>

                <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-2">
                    <div>
                        <flux:label>Motivo <span class="text-red-500">*</span></flux:label>
                        <flux:select
                            wire:model.live="motivo_rechazo"
                            :error="$errors->first('motivo_rechazo')"
                            class="mt-1"
                        >
                            <option value="">Seleccione un motivo</option>
                            @foreach($motivosPredefinidos as $motivo)
                                <option value="{{ $motivo }}">{{ $motivo }}</option>
                            @endforeach
                            <option value="Otro">Otro (especificar)</option>
                        </flux:select>
                    </div>

                    @if($motivo_rechazo === 'Otro')
                    <div>
                        <flux:label>Especificar motivo <span class="text-red-500">*</span></flux:label>
                        <flux:input
                            wire:model="motivo_personalizado"
                            placeholder="Describa brevemente el motivo..."
                            :error="$errors->first('motivo_personalizado')"
                            class="mt-1"
                        />
                    </div>
                    @endif

                    <div class="{{ $motivo_rechazo === 'Otro' ? 'md:col-span-2' : 'md:col-span-2' }}">
                        <flux:textarea
                            wire:model="observaciones"
                            label="Observaciones (opcional)"
                            placeholder="Observaciones adicionales sobre la muestra rechazada..."
                            rows="3"
                        />
                    </div>
                </div>

                {{-- Alerta informativa --}}
                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                    <div class="flex gap-3">
                        <svg class="h-5 w-5 flex-shrink-0 mt-0.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-300">Esta muestra no será procesada</p>
                            <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">El registro es solo de auditoría. No se descontará inventario ni podrá ser analizada.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="flex justify-end gap-3">
                <flux:button
                    wire:click="cancelar"
                    type="button"
                    variant="outline"
                    class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950"
                >
                    Cancelar
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Registrar Muestra Rechazada
                </flux:button>
            </div>

        </div>
    </form>
</div>
