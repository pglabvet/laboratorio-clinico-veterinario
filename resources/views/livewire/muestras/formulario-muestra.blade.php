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
                        label="Nombre del Paciente "
                        placeholder="Ej: Max"
                        :error="$errors->first('paciente_nombre')"
                    />

                    <flux:select 
                        wire:model="especie_id"
                        label="Especie "
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
                        label="Edad "
                        placeholder="Ej: 3 años, 6 meses"
                        :error="$errors->first('edad')"
                    />

                    <flux:select 
                        wire:model="sexo"
                        label="Sexo "
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
                        label="Nombre del Propietario "
                        placeholder="Ej: Juan Pérez"
                        :error="$errors->first('propietario_nombre')"
                    />

                    <flux:select 
                        wire:model="veterinaria_id"
                        label="Veterinaria "
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
                        label="Sucursal "
                        :error="$errors->first('sucursal_id')"
                    >
                        @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                        @endforeach
                    </flux:select>

                    <flux:input 
                        wire:model="tipo_muestra"
                        label="Tipo de Muestra "
                        placeholder="Ej: Sangre, Orina, Heces"
                        :error="$errors->first('tipo_muestra')"
                    />

                    <flux:input 
                        wire:model="fecha_recepcion"
                        type="date"
                        label="Fecha de Recepción "
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
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                        Análisis Solicitados 
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">Seleccione el tipo de análisis y la plantilla correspondiente</p>
                </div>

                @if($errors->has('analisisSeleccionados'))
                    <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                        {{ $errors->first('analisisSeleccionados') }}
                    </div>
                @endif

                {{-- Formulario para agregar análisis --}}
                <div class="mb-6 rounded-lg border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-800/50">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        {{-- Tipo de Análisis --}}
                        <div class="flex flex-col">
                            <flux:select 
                                wire:model.live="tipoAnalisisTemp"
                                label="Tipo de Análisis"
                                placeholder=""
                            >
                                <option value="">Seleccione un tipo de análisis</option>
                                @foreach($tiposAnalisis as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        {{-- Plantillas Disponibles --}}
                        <div class="flex flex-col">
                            <div class="flex h-full flex-col">
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Plantilla
                                </label>
                                
                                {{-- Skeleton mientras carga --}}
                                <div wire:loading wire:target="tipoAnalisisTemp" class="flex-1 space-y-2 rounded-lg border border-neutral-200 bg-white p-3 dark:border-neutral-600 dark:bg-neutral-700">
                                    <div class="space-y-3">
                                        <div class="flex gap-3">
                                            <flux:skeleton class="h-4 w-4 rounded" />
                                            <div class="flex-1 space-y-2">
                                                <flux:skeleton class="h-4 w-3/4" />
                                                <flux:skeleton class="h-3 w-1/2" />
                                            </div>
                                        </div>
                                        <div class="flex gap-3">
                                            <flux:skeleton class="h-4 w-4 rounded" />
                                            <div class="flex-1 space-y-2">
                                                <flux:skeleton class="h-4 w-2/3" />
                                                <flux:skeleton class="h-3 w-1/3" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Contenido real --}}
                                <div wire:loading.remove wire:target="tipoAnalisisTemp">
                                    @if($tipoAnalisisTemp)
                                        @if(count($plantillasDisponibles) > 0)
                                            <div class="flex-1 space-y-2 overflow-y-auto rounded-lg border border-neutral-200 bg-white p-3 dark:border-neutral-600 dark:bg-neutral-700" style="max-height: 200px;">
                                                @foreach($plantillasDisponibles as $plantilla)
                                                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-neutral-300 bg-white p-3 transition-all hover:border-cyan-500 hover:bg-neutral-50 has-[:checked]:border-cyan-500 has-[:checked]:bg-cyan-50 dark:border-neutral-600 dark:bg-neutral-800 dark:hover:bg-neutral-600 dark:has-[:checked]:border-cyan-500 dark:has-[:checked]:bg-cyan-900/20">
                                                        <input 
                                                            type="radio" 
                                                            wire:model="plantillaSeleccionadaTemp"
                                                            value="{{ $plantilla->id }}"
                                                            class="mt-1 h-4 w-4 text-cyan-600 focus:ring-cyan-500"
                                                        >
                                                        <div class="flex-1">
                                                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                                                {{ $plantilla->nombre }}
                                                                @if($plantilla->version > 1)
                                                                    <span class="ml-1 inline-flex items-center rounded bg-blue-100 px-1.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">v{{ $plantilla->version }}</span>
                                                                @endif
                                                            </p>
                                                            @if($plantilla->descripcion)
                                                                <p class="text-xs text-neutral-600 dark:text-neutral-400">
                                                                    {{ $plantilla->descripcion }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="flex-1 rounded-lg border border-neutral-200 bg-white p-4 text-center dark:border-neutral-600 dark:bg-neutral-700">
                                                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                                    No hay plantillas activas.
                                                </p>
                                            </div>
                                        @endif
                                    @else
                                        <div class="flex-1 rounded-lg border border-dashed border-neutral-300 p-4 text-center dark:border-neutral-600">
                                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                                Seleccione primero un tipo de análisis
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Botón agregar --}}
                    <div class="mt-4 flex justify-end">
                        <flux:button 
                            wire:click="agregarAnalisis"
                            type="button"
                            icon="plus"
                            variant="primary"
                            size="sm"
                        >
                            Agregar Análisis
                        </flux:button>
                    </div>
                </div>

                {{-- Lista de análisis agregados --}}
                @if(count($analisisSeleccionados) > 0)
                    <div class="space-y-3">
                        <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                            Análisis agregados ({{ count($analisisSeleccionados) }})
                        </p>
                        @foreach($analisisSeleccionados as $index => $analisis)
                            <div class="flex items-center justify-between rounded-lg border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-600 dark:bg-neutral-700">
                                <div>
                                    <p class="font-medium text-neutral-900 dark:text-neutral-100">
                                        {{ $analisis['tipo_nombre'] }}
                                    </p>
                                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                        Plantilla: {{ $analisis['plantilla_nombre'] }}
                                        @if(($analisis['plantilla_version'] ?? 1) > 1)
                                            <span class="ml-1 inline-flex items-center rounded bg-blue-100 px-1.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">v{{ $analisis['plantilla_version'] }}</span>
                                        @endif
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
                    <div class="rounded-lg border-2 border-dashed border-neutral-300 p-6 text-center dark:border-neutral-600">
                        <flux:icon.clipboard-document-list class="mx-auto mb-2 size-8 text-neutral-400" />
                        <p class="text-neutral-600 dark:text-neutral-400">
                            No hay análisis agregados aún.
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
</div>
