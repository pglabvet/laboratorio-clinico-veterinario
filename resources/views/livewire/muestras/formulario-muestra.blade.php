<div>
    {{-- Mensajes toast --}}
    <x-toast type="success" :message="session('mensaje')" />
    <x-toast type="error" :message="session('error')" />
    <x-toast type="warning" :message="session('warning')" />

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <flux:button 
                wire:click="cancelar"
                variant="ghost"
                icon="arrow-left"
                size="sm"
            >
                Volver
            </flux:button>
            <flux:heading size="xl">{{ $muestra_id ? 'Editar Muestra' : 'Registrar Nueva Muestra' }}</flux:heading>
        </div>
        <flux:subheading>Complete el formulario con los datos del paciente y la muestra</flux:subheading>
    </div>

    {{-- Formulario --}}
    <form wire:submit="guardar">
        <div class="space-y-6">
            
            {{-- Sección 1: Información del Paciente --}}
            <div class="rounded-lg border border-neutral-200 bg-white p-6 shadow dark:border-neutral-700 dark:bg-neutral-800">
                <h3 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                    Información del Paciente
                </h3>
                
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <flux:input 
                        wire:model="paciente_nombre"
                        label="Nombre del Paciente *"
                        placeholder="Ej: Max"
                        :error="$errors->first('paciente_nombre')"
                    />

                    <flux:select 
                        wire:model="especie_id"
                        label="Especie *"
                        placeholder="Seleccione una especie"
                        :error="$errors->first('especie_id')"
                    >
                        @foreach($especies as $especie)
                            <option value="{{ $especie->id }}">{{ $especie->nombre }}</option>
                        @endforeach
                    </flux:select>

                    <flux:input 
                        wire:model="raza"
                        label="Raza"
                        placeholder="Ej: Golden Retriever"
                    />

                    <flux:input 
                        wire:model="edad"
                        label="Edad *"
                        placeholder="Ej: 3 años, 6 meses"
                        :error="$errors->first('edad')"
                    />

                    <flux:select 
                        wire:model="sexo"
                        label="Sexo *"
                        :error="$errors->first('sexo')"
                    >
                        <option value="M">Macho</option>
                        <option value="H">Hembra</option>
                    </flux:select>

                    <flux:input 
                        wire:model="color"
                        label="Color"
                        placeholder="Ej: Dorado"
                    />
                </div>
            </div>

            {{-- Sección 2: Información del Propietario --}}
            <div class="rounded-lg border border-neutral-200 bg-white p-6 shadow dark:border-neutral-700 dark:bg-neutral-800">
                <h3 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                    Información del Propietario
                </h3>
                
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <flux:input 
                        wire:model="propietario_nombre"
                        label="Nombre del Propietario *"
                        placeholder="Ej: Juan Pérez"
                        :error="$errors->first('propietario_nombre')"
                    />

                    <flux:select 
                        wire:model="veterinaria_id"
                        label="Veterinaria *"
                        placeholder="Seleccione una veterinaria"
                        :error="$errors->first('veterinaria_id')"
                    >
                        @foreach($veterinarias as $veterinaria)
                            <option value="{{ $veterinaria->id }}">{{ $veterinaria->nombre }}</option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

            {{-- Sección 3: Información de la Muestra --}}
            <div class="rounded-lg border border-neutral-200 bg-white p-6 shadow dark:border-neutral-700 dark:bg-neutral-800">
                <h3 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                    Información de la Muestra
                </h3>
                
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <flux:input 
                        wire:model="codigo_muestra"
                        label="Código de Muestra"
                        placeholder="Se genera automáticamente"
                        disabled
                    />

                    <flux:select 
                        wire:model="sucursal_id"
                        label="Sucursal *"
                        :error="$errors->first('sucursal_id')"
                    >
                        @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                        @endforeach
                    </flux:select>

                    <flux:input 
                        wire:model="tipo_muestra"
                        label="Tipo de Muestra *"
                        placeholder="Ej: Sangre, Orina, Heces"
                        :error="$errors->first('tipo_muestra')"
                    />

                    <flux:input 
                        wire:model="fecha_recepcion"
                        type="date"
                        label="Fecha de Recepción *"
                        :error="$errors->first('fecha_recepcion')"
                    />

                    <div class="md:col-span-2">
                        <flux:textarea 
                            wire:model="observaciones"
                            label="Observaciones"
                            placeholder="Observaciones adicionales sobre la muestra"
                            rows="3"
                        />
                    </div>
                </div>
            </div>

            {{-- Sección 4: Análisis Solicitados --}}
            <div class="rounded-lg border border-neutral-200 bg-white p-6 shadow dark:border-neutral-700 dark:bg-neutral-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                        Análisis Solicitados *
                    </h3>
                    <flux:button 
                        wire:click="abrirModalAnalisis"
                        type="button"
                        icon="plus"
                        variant="primary"
                        size="sm"
                    >
                        Agregar Análisis
                    </flux:button>
                </div>

                @if($errors->has('analisisSeleccionados'))
                    <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                        {{ $errors->first('analisisSeleccionados') }}
                    </div>
                @endif

                @if(count($analisisSeleccionados) > 0)
                    <div class="space-y-3">
                        @foreach($analisisSeleccionados as $index => $analisis)
                            <div class="flex items-center justify-between rounded-lg border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-600 dark:bg-neutral-700">
                                <div>
                                    <p class="font-medium text-neutral-900 dark:text-neutral-100">
                                        {{ $analisis['tipo_nombre'] }}
                                    </p>
                                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                        Plantilla: {{ $analisis['plantilla_nombre'] }}
                                    </p>
                                </div>
                                <flux:button 
                                    wire:click="eliminarAnalisis({{ $index }})"
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    icon="trash"
                                    color="red"
                                />
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-lg border-2 border-dashed border-neutral-300 p-8 text-center dark:border-neutral-600">
                        <p class="text-neutral-600 dark:text-neutral-400">
                            No hay análisis agregados. Haga clic en "Agregar Análisis" para comenzar.
                        </p>
                    </div>
                @endif
            </div>

            {{-- Botones de acción --}}
            <div class="flex justify-end gap-3">
                <flux:button 
                    wire:click="cancelar"
                    type="button"
                    variant="ghost"
                >
                    Cancelar
                </flux:button>
                <flux:button 
                    type="submit"
                    variant="primary"
                >
                    {{ $muestra_id ? 'Actualizar Muestra' : 'Registrar Muestra' }}
                </flux:button>
            </div>
        </div>
    </form>

    {{-- Modal: Agregar Análisis --}}
    <flux:modal wire:model="modalAnalisisAbierto" class="max-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Agregar Análisis</flux:heading>
                <flux:subheading>Seleccione el tipo de análisis y la plantilla correspondiente</flux:subheading>
            </div>

            {{-- Sección 1: Tipo de Análisis --}}
            <div>
                <flux:select 
                    wire:model="tipoAnalisisTemp"
                    wire:change="cargarPlantillas"
                    label="Tipo de Análisis *"
                    placeholder="Seleccione un tipo de análisis"
                >
                    @foreach($tiposAnalisis as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Sección 2: Plantillas Disponibles --}}
            @if($tipoAnalisisTemp)
                <div>
                    <flux:field>
                        <flux:label>Plantillas Disponibles *</flux:label>
                        
                        @if(count($plantillasDisponibles) > 0)
                            <div class="mt-2 space-y-2 rounded-lg border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-800">
                                @foreach($plantillasDisponibles as $plantilla)
                                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-transparent p-3 hover:border-cyan-500 hover:bg-white dark:hover:bg-neutral-700">
                                        <input 
                                            type="radio" 
                                            wire:model="plantillaSeleccionadaTemp"
                                            value="{{ $plantilla->id }}"
                                            class="mt-1 h-4 w-4 text-cyan-600 focus:ring-cyan-500"
                                        >
                                        <div class="flex-1">
                                            <p class="font-medium text-neutral-900 dark:text-neutral-100">
                                                {{ $plantilla->nombre }}
                                            </p>
                                            @if($plantilla->descripcion)
                                                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                                                    {{ $plantilla->descripcion }}
                                                </p>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-2 rounded-lg border border-neutral-200 bg-neutral-50 p-4 text-center dark:border-neutral-700 dark:bg-neutral-800">
                                <p class="text-neutral-600 dark:text-neutral-400">
                                    No hay plantillas activas para este tipo de análisis.
                                </p>
                            </div>
                        @endif
                    </flux:field>
                </div>
            @else
                <div class="rounded-lg border-2 border-dashed border-neutral-300 p-8 text-center dark:border-neutral-600">
                    <p class="text-neutral-600 dark:text-neutral-400">
                        Seleccione primero un tipo de análisis para ver las plantillas disponibles.
                    </p>
                </div>
            @endif

            {{-- Botones del modal --}}
            <div class="flex justify-end gap-3">
                <flux:button 
                    wire:click="cerrarModalAnalisis"
                    variant="ghost"
                    type="button"
                >
                    Cancelar
                </flux:button>
                <flux:button 
                    wire:click="agregarAnalisis"
                    variant="primary"
                    type="button"
                >
                    Agregar
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
