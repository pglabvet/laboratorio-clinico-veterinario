<div class="space-y-6">
    {{-- Encabezado --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="mb-1">Escanear Muestra</flux:heading>
            <flux:subheading>Utilice el lector de código de barras o ingrese manualmente el código de la muestra</flux:subheading>
        </div>
    </div>

    {{-- Formulario de escaneo --}}
    <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center gap-2 mb-2">
            <x-lucide-scan class="size-5 text-blue-600" />
            <flux:heading size="lg">Identificación de Muestra</flux:heading>
        </div>
        <flux:subheading class="mb-4">Escanee el código de barras de la muestra o ingréselo manualmente</flux:subheading>

        <div class="flex gap-3">
            <div class="flex-1">
                <flux:input 
                    wire:model.live="codigo_muestra"
                    placeholder="Código de Muestra"
                    autofocus
                    wire:keydown.enter="escanear"
                />
                <flux:description class="mt-1">
                    Códigos de ejemplo: SM-2024-001, SM-2024-002, SM-2024-003
                </flux:description>
                @error('codigo_muestra')
                    <flux:error class="mt-1">{{ $message }}</flux:error>
                @enderror
            </div>
            
            <flux:button 
                wire:click="escanear" 
                variant="primary"
                :disabled="!$codigo_muestra"
            >
                <div class="flex items-center gap-2">
                    <x-lucide-search class="size-4" />
                    <span>Escanear</span>
                </div>
            </flux:button>

            @if($muestra || $mensaje_error)
                <flux:button 
                    wire:click="limpiar" 
                    variant="outline"
                    icon="x-mark"
                >
                    Limpiar
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Mensaje de error --}}
    @if($mensaje_error)
        <div class="bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 rounded-lg p-6">
            <div class="flex items-start gap-3">
                <x-lucide-alert-circle class="size-6 text-red-600 dark:text-red-400 flex-shrink-0" />
                <div>
                    <flux:heading size="lg" class="text-red-900 dark:text-red-100 mb-1">Muestra no encontrada</flux:heading>
                    <flux:subheading class="text-red-700 dark:text-red-300">{{ $mensaje_error }}</flux:subheading>
                </div>
            </div>
        </div>
    @endif

    {{-- Datos de la muestra --}}
    @if($muestra)
        <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6">
            <div class="flex items-center gap-2 mb-4">
                <x-lucide-file-text class="size-5 text-blue-600" />
                <flux:heading size="lg">Datos de la Muestra</flux:heading>
            </div>
            <flux:subheading class="mb-4">Información del paciente y análisis solicitados</flux:subheading>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Información básica --}}
                <div class="space-y-4">
                    <div>
                        <flux:label class="text-xs">Código de Muestra</flux:label>
                        <div class="flex items-center gap-2 mt-1">
                            <flux:badge variant="outline" size="lg" class="font-mono">
                                {{ $muestra->codigo_muestra }}
                            </flux:badge>
                            <flux:badge size="sm" :color="$muestra->getColorEstado()" inset="top bottom">
                                {{ $muestra->estado }}
                            </flux:badge>
                        </div>
                    </div>

                    <div>
                        <flux:label class="text-xs">Fecha de Solicitud</flux:label>
                        <p class="text-sm text-neutral-900 dark:text-neutral-100 mt-1">
                            {{ $muestra->fecha_recepcion->format('d/m/Y') }}
                        </p>
                    </div>

                    <div>
                        <flux:label class="text-xs">Nombre del Paciente</flux:label>
                        <p class="text-sm text-neutral-900 dark:text-neutral-100 mt-1 font-semibold">
                            {{ $muestra->paciente_nombre }}
                        </p>
                    </div>

                    <div>
                        <flux:label class="text-xs">Especie</flux:label>
                        <p class="text-sm text-neutral-900 dark:text-neutral-100 mt-1">
                            {{ $muestra->especie->nombre ?? 'N/A' }}
                        </p>
                    </div>

                    <div>
                        <flux:label class="text-xs">Raza</flux:label>
                        <p class="text-sm text-neutral-900 dark:text-neutral-100 mt-1">
                            {{ $muestra->raza ?? 'N/A' }}
                        </p>
                    </div>

                    <div>
                        <flux:label class="text-xs">Propietario</flux:label>
                        <p class="text-sm text-neutral-900 dark:text-neutral-100 mt-1">
                            {{ $muestra->propietario_nombre }}
                        </p>
                    </div>
                </div>

                {{-- Información adicional --}}
                <div class="space-y-4">
                    <div>
                        <flux:label class="text-xs">Veterinaria</flux:label>
                        <p class="text-sm text-neutral-900 dark:text-neutral-100 mt-1">
                            {{ $muestra->veterinaria->nombre ?? 'N/A' }}
                        </p>
                    </div>

                    <div>
                        <flux:label class="text-xs">Sucursal</flux:label>
                        <p class="text-sm text-neutral-900 dark:text-neutral-100 mt-1">
                            {{ $muestra->sucursal->nombre ?? 'N/A' }}
                        </p>
                    </div>

                    <div>
                        <flux:label class="text-xs">Tipo de Muestra</flux:label>
                        <p class="text-sm text-neutral-900 dark:text-neutral-100 mt-1">
                            {{ $muestra->tipo_muestra }}
                        </p>
                    </div>

                    <div>
                        <flux:label class="text-xs">Edad</flux:label>
                        <p class="text-sm text-neutral-900 dark:text-neutral-100 mt-1">
                            {{ $muestra->edad }}
                        </p>
                    </div>

                    <div>
                        <flux:label class="text-xs">Sexo</flux:label>
                        <p class="text-sm text-neutral-900 dark:text-neutral-100 mt-1">
                            {{ $muestra->sexo === 'M' ? 'Macho' : 'Hembra' }}
                        </p>
                    </div>

                    @if($muestra->observaciones)
                        <div>
                            <flux:label class="text-xs">Observaciones</flux:label>
                            <p class="text-sm text-neutral-900 dark:text-neutral-100 mt-1">
                                {{ $muestra->observaciones }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Análisis solicitados --}}
            <div class="mt-6 pt-6 border-t border-neutral-200 dark:border-neutral-700">
                <div class="flex items-center gap-2 mb-4">
                    <x-lucide-flask-conical class="size-5 text-blue-600" />
                    <flux:heading size="lg">Análisis Solicitados ({{ $muestra->analisis->count() }})</flux:heading>
                </div>

                <div class="space-y-2">
                    @foreach($muestra->analisis as $analisis)
                        <div class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-800 rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                                    {{ $analisis->tipoAnalisis->nombre ?? 'N/A' }}
                                </p>
                                @if($analisis->bioquimico)
                                    <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">
                                        Asignado a: {{ $analisis->bioquimico->name }}
                                    </p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:badge size="sm" :color="$analisis->getColorEstado()" inset="top bottom">
                                    {{ $analisis->estado }}
                                </flux:badge>
                                
                                @if($analisis->estado === 'Pendiente')
                                    <flux:button 
                                        href="{{ route('analisis.capturar-resultados', $analisis->id) }}"
                                        size="sm"
                                        variant="primary"
                                    >
                                        <div class="flex items-center gap-2">
                                            <x-lucide-edit class="size-4" />
                                            <span>Ingresar Resultados</span>
                                        </div>
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Acciones --}}
            <div class="mt-6 pt-6 border-t border-neutral-200 dark:border-neutral-700 flex gap-3">
                <flux:button 
                    href="{{ route('muestras.etiqueta', $muestra) }}"
                    target="_blank"
                    variant="outline"
                >
                    <x-lucide-printer class="size-4" />
                    Reimprimir Etiqueta
                </flux:button>

                <flux:button 
                    href="{{ route('muestras.index') }}"
                    variant="ghost"
                >
                    <x-lucide-list class="size-4" />
                    Ver todas las muestras
                </flux:button>
            </div>
        </div>
    @endif
</div>
