<div>
    {{-- Mensajes toast --}}
    <x-toast type="success" :message="session('mensaje')" />
    <x-toast type="error" :message="session('error')" />
    <x-toast type="warning" :message="session('warning')" />

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
        <flux:heading size="xl" class="mb-1">{{ $muestra_id ? 'Editar Muestra' : 'Registrar Nueva Muestra' }}</flux:heading>
        <flux:subheading>Complete el formulario con los datos del paciente y la muestra</flux:subheading>
    </div>

    {{-- Formulario --}}
    <form wire:submit="guardar">
        <div class="space-y-6">
            
            {{-- Sección 1: Datos del Paciente y Muestra --}}
            <div class="rounded-lg border border-neutral-200 bg-white p-6 shadow dark:border-neutral-700 dark:bg-neutral-900">
                <h3 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                    Información del Paciente
                </h3>
                
                {{-- Fila 1: Fecha | Veterinaria | Código --}}
                <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-3 mb-6">
                    <flux:input 
                        type="date"
                        label="Fecha"
                        :value="date('Y-m-d')"
                        disabled
                    />

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

                    <flux:input 
                        wire:model="codigo_muestra"
                        label="Código de Muestra"
                        placeholder="Se genera automáticamente"
                        disabled
                    />
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

                {{-- Fila 3: Especie | Raza | Edad | Sexo | Color --}}
                <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-2 lg:grid-cols-5 mb-6">
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
                                <flux:select 
                                    wire:model="edadUnidad"
                                    :error="$errors->first('edadUnidad')"
                                >
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

                    <flux:input 
                        wire:model="color"
                        label="Color"
                        placeholder="Ej: Dorado"
                    />
                </div>
            </div>

            {{-- Sección 2: Información de la Muestra --}}
            <div class="rounded-lg border border-neutral-200 bg-white p-6 shadow dark:border-neutral-700 dark:bg-neutral-900">
                <h3 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                    Información de la Muestra
                </h3>
                
                <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <flux:label>Sucursal <span class="text-red-500">*</span></flux:label>
                        <flux:select 
                            wire:model="sucursal_id"
                            :error="$errors->first('sucursal_id')"
                            :disabled="!$this->puedeSeleccionarSucursal"
                            class="mt-1"
                        >
                            @foreach($this->sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div>
                        <flux:label>Tipo de Muestra <span class="text-red-500">*</span></flux:label>
                        <flux:input 
                            wire:model="tipo_muestra"
                            placeholder="Ej: Sangre, Orina, Heces"
                            :error="$errors->first('tipo_muestra')"
                            class="mt-1"
                        />
                    </div>

                    <div class="md:col-span-2 lg:col-span-3">
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
            <div class="rounded-lg border border-neutral-200 bg-white p-6 shadow dark:border-neutral-700 dark:bg-neutral-900">
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

                <div class="grid grid-cols-1 items-stretch gap-6 lg:grid-cols-2">
                    {{-- Formulario para agregar análisis --}}
                    <div class="rounded-lg border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-800/50">
                        <div class="space-y-4">
                            {{-- Tipo de Análisis --}}
                            <div>
                                <flux:select 
                                    wire:model.live="tipoAnalisisTemp"
                                    label="Tipo de Análisis"
                                    placeholder=""
                                >
                                    <option value="">Seleccione un tipo de análisis</option>
                                    @foreach($this->tiposAnalisis as $tipo)
                                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                    @endforeach
                                </flux:select>
                            </div>

                            {{-- Plantillas Disponibles --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Plantilla
                                </label>
                                
                                {{-- Skeleton mientras carga --}}
                                <div wire:loading wire:target="tipoAnalisisTemp" class="w-full rounded-lg border border-neutral-200 bg-white p-3 dark:border-neutral-600 dark:bg-neutral-700" style="min-height: 120px;">
                                    <div class="space-y-3">
                                        <div class="flex w-full gap-3">
                                            <flux:skeleton class="h-4 w-4 shrink-0 rounded" />
                                            <div class="flex-1 space-y-2">
                                                <flux:skeleton class="h-4 w-full" />
                                                <flux:skeleton class="h-3 w-full" />
                                            </div>
                                        </div>
                                        <div class="flex w-full gap-3">
                                            <flux:skeleton class="h-4 w-4 shrink-0 rounded" />
                                            <div class="flex-1 space-y-2">
                                                <flux:skeleton class="h-4 w-full" />
                                                <flux:skeleton class="h-3 w-full" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Contenido real --}}
                                <div wire:loading.remove wire:target="tipoAnalisisTemp">
                                    @if($tipoAnalisisTemp)
                                        @if(count($plantillasDisponibles) > 0)
                                            <div class="space-y-2 overflow-y-auto rounded-lg border border-neutral-200 bg-white p-3 dark:border-neutral-600 dark:bg-neutral-700" style="max-height: 200px;">
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
                                            <div class="rounded-lg border border-neutral-200 bg-white p-4 text-center dark:border-neutral-600 dark:bg-neutral-700">
                                                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                                    No hay plantillas activas.
                                                </p>
                                            </div>
                                        @endif
                                    @else
                                        <div class="rounded-lg border border-dashed border-neutral-300 p-4 text-center dark:border-neutral-600">
                                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                                Seleccione primero un tipo de análisis
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Botón agregar --}}
                            <div class="flex justify-end">
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
                    </div>

                    {{-- Lista de análisis agregados --}}
                    <div class="flex h-full flex-col">
                        @if(count($analisisSeleccionados) > 0)
                            <div class="space-y-3">
                                <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                    Análisis agregados ({{ count($analisisSeleccionados) }})
                                </p>
                                @foreach($analisisSeleccionados as $index => $analisis)
                                    <div class="group relative flex items-start justify-between gap-4 rounded-xl border border-neutral-200 bg-white p-4 shadow-sm transition-all hover:shadow-md hover:border-cyan-300 dark:border-neutral-600 dark:bg-neutral-800 dark:hover:border-cyan-600">
                                        <!-- Borde lateral decorativo -->
                                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-cyan-500 to-blue-500 rounded-l-xl"></div>
                                        
                                        <div class="flex-1 pl-3">
                                            <div class="flex items-center gap-2 mb-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-cyan-600 dark:text-cyan-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                                                </svg>
                                                <p class="font-semibold text-neutral-900 dark:text-neutral-100">
                                                    {{ $analisis['tipo_nombre'] }}
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="inline-flex items-center gap-1 rounded-full bg-neutral-100 px-2.5 py-0.5 text-xs font-medium text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                                    </svg>
                                                    {{ $analisis['plantilla_nombre'] }}
                                                    @if(isset($analisis['plantilla_version']) && $analisis['plantilla_version'] > 1)
                                                        <span class="ml-1 text-xs font-semibold text-blue-600 dark:text-blue-400">v{{ $analisis['plantilla_version'] }}</span>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <button
                                            wire:click="eliminarAnalisis({{ $index }})"
                                            type="button"
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-neutral-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                                            title="Eliminar análisis"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex h-full min-h-full items-center justify-center rounded-lg border-2 border-dashed border-neutral-300 p-6 text-center dark:border-neutral-600">
                                <div>
                                    <flux:icon.clipboard-document-list class="mx-auto mb-2 size-8 text-neutral-400" />
                                    <p class="text-neutral-600 dark:text-neutral-400">
                                        No hay análisis agregados aún.
                                    </p>
                                </div>
                            </div>
                        @endif
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
