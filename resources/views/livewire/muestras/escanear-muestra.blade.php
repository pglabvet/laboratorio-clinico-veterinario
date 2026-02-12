<div>
    {{-- Header de la página --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="mb-1">Escanear Muestra</flux:heading>
            <flux:subheading>Utilice el lector de código de barras o ingrese manualmente el código de la muestra</flux:subheading>
        </div>
    </div>

    {{-- Formulario de escaneo --}}
    <div class="mb-6 overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-md ring-1 ring-neutral-200/50 dark:border-neutral-700 dark:bg-neutral-800 dark:ring-neutral-700/50">
        <div class="border-b border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-700 dark:bg-neutral-900">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                    <x-lucide-scan class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <flux:heading size="lg" class="mb-0">Identificación de Muestra</flux:heading>
                    <p class="text-xs text-neutral-600 dark:text-neutral-400">Escanee el código de barras o ingréselo manualmente</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="flex-1">
                    <flux:input 
                        wire:model.live="codigo_muestra"
                        placeholder="Código de Muestra (Ej: C-AA0003)"
                        autofocus
                        wire:keydown.enter="escanear"
                    />
                    @error('codigo_muestra')
                        <flux:error class="mt-1">{{ $message }}</flux:error>
                    @else
                        <flux:description class="mt-1">
                            Presione Enter o haga clic en Buscar después de escanear
                        </flux:description>
                    @enderror
                </div>
                
                <div class="flex gap-2">
                    <flux:button 
                        wire:click="escanear" 
                        variant="primary"
                        :disabled="!$codigo_muestra"
                        icon="magnifying-glass"
                    >
                        Buscar
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
        </div>
    </div>

    {{-- Mensaje de error --}}
    @if($mensaje_error)
        <div class="mb-6 overflow-hidden rounded-lg border border-red-200 bg-white shadow-md ring-1 ring-red-200/50 dark:border-red-800 dark:bg-neutral-800 dark:ring-red-800/50">
            <div class="border-b border-red-200 bg-red-50 px-6 py-4 dark:border-red-800 dark:bg-red-950/50">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
                        <x-lucide-alert-circle class="size-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="mb-0 text-red-900 dark:text-red-100">Muestra no encontrada</flux:heading>
                        <p class="text-xs text-red-700 dark:text-red-300">El código ingresado no está registrado en el sistema</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <p class="mb-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $mensaje_error }}</p>
                <p class="text-xs text-neutral-600 dark:text-neutral-400">
                    • Verifique que el código sea correcto<br>
                    • Asegúrese de que la muestra esté registrada en el sistema<br>
                    • Contacte al administrador si el problema persiste
                </p>
            </div>
        </div>
    @endif

    {{-- Datos de la muestra --}}
    @if($muestra)
        <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-md ring-1 ring-neutral-200/50 dark:border-neutral-700 dark:bg-neutral-800 dark:ring-neutral-700/50">
            {{-- Header con código y estado --}}
            <div class="border-b border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-700 dark:bg-neutral-900">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                            <x-lucide-file-text class="size-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="mb-0.5">Datos de la Muestra</flux:heading>
                            <flux:badge variant="outline" size="sm" class="font-mono">
                                {{ $muestra->codigo_muestra }}
                            </flux:badge>
                        </div>
                    </div>
                    <flux:badge :color="$muestra->getColorEstado()" inset="top bottom">
                        {{ $muestra->estado }}
                    </flux:badge>
                </div>
            </div>

            <div class="p-6">
                {{-- Información del Paciente --}}
                <div class="mb-6">
                    <div class="mb-3 flex items-center gap-2 border-b border-neutral-200 pb-2 dark:border-neutral-700">
                        <x-lucide-heart class="size-4 text-neutral-600 dark:text-neutral-400" />
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Información del Paciente
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900">
                            <flux:label class="mb-1 text-xs font-medium text-neutral-600 dark:text-neutral-400">Nombre del Paciente</flux:label>
                            <p class="text-sm font-bold text-neutral-900 dark:text-neutral-100">
                                {{ $muestra->paciente_nombre }}
                            </p>
                        </div>

                        <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900">
                            <flux:label class="mb-1 text-xs font-medium text-neutral-600 dark:text-neutral-400">Propietario</flux:label>
                            <p class="text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $muestra->propietario_nombre }}
                            </p>
                        </div>

                        <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900">
                            <flux:label class="mb-1 text-xs font-medium text-neutral-600 dark:text-neutral-400">Especie</flux:label>
                            <p class="text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $muestra->especie->nombre ?? 'N/A' }}
                            </p>
                        </div>

                        <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900">
                            <flux:label class="mb-1 text-xs font-medium text-neutral-600 dark:text-neutral-400">Raza</flux:label>
                            <p class="text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $muestra->raza ?? 'Sin especificar' }}
                            </p>
                        </div>

                        <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900">
                            <flux:label class="mb-1 text-xs font-medium text-neutral-600 dark:text-neutral-400">Edad</flux:label>
                            <p class="text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $muestra->edad }}
                            </p>
                        </div>

                        <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900">
                            <flux:label class="mb-1 text-xs font-medium text-neutral-600 dark:text-neutral-400">Sexo</flux:label>
                            <p class="text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $muestra->sexo === 'M' ? 'Macho' : 'Hembra' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Información de la Muestra --}}
                <div class="mb-6">
                    <div class="mb-3 flex items-center gap-2 border-b border-neutral-200 pb-2 dark:border-neutral-700">
                        <x-lucide-flask-conical class="size-4 text-neutral-600 dark:text-neutral-400" />
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Detalles de la Muestra
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900">
                            <flux:label class="mb-1 text-xs font-medium text-neutral-600 dark:text-neutral-400">Fecha de Recepción</flux:label>
                            <p class="text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $muestra->fecha_recepcion->format('d/m/Y H:i') }}
                            </p>
                        </div>

                        <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900">
                            <flux:label class="mb-1 text-xs font-medium text-neutral-600 dark:text-neutral-400">Veterinaria</flux:label>
                            <p class="text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $muestra->veterinaria->nombre ?? 'N/A' }}
                            </p>
                        </div>

                        <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900">
                            <flux:label class="mb-1 text-xs font-medium text-neutral-600 dark:text-neutral-400">Sucursal</flux:label>
                            <p class="text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $muestra->sucursal->nombre ?? 'N/A' }}
                            </p>
                        </div>

                        <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900">
                            <flux:label class="mb-1 text-xs font-medium text-neutral-600 dark:text-neutral-400">Tipo de Muestra</flux:label>
                            <p class="text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $muestra->tipo_muestra }}
                            </p>
                        </div>

                        @if($muestra->observaciones)
                            <div class="col-span-full rounded-md bg-amber-50 p-3 dark:bg-amber-900/20">
                                <flux:label class="mb-1 text-xs font-medium text-amber-700 dark:text-amber-400">Observaciones</flux:label>
                                <p class="text-sm text-neutral-900 dark:text-neutral-100">
                                    {{ $muestra->observaciones }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Análisis solicitados --}}
                <div>
                    <div class="mb-3 flex items-center gap-2 border-b border-neutral-200 pb-2 dark:border-neutral-700">
                        <x-lucide-microscope class="size-4 text-neutral-600 dark:text-neutral-400" />
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Análisis Solicitados
                        </h3>
                        <flux:badge size="sm" variant="outline" class="ml-auto">
                            {{ $muestra->analisis->count() }} {{ $muestra->analisis->count() === 1 ? 'análisis' : 'análisis' }}
                        </flux:badge>
                    </div>

                    <div class="space-y-3">
                        @foreach($muestra->analisis as $analisis)
                            <div class="flex items-center justify-between rounded-lg border border-neutral-200 bg-neutral-50 p-4 transition-colors hover:bg-neutral-100 dark:border-neutral-700 dark:bg-neutral-900 dark:hover:bg-neutral-800">
                                <div class="flex flex-1 items-center gap-3">
                                    <div class="flex size-9 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                        <x-lucide-flask-conical class="size-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="mb-1 text-base font-bold text-neutral-900 dark:text-neutral-100">
                                            {{ $analisis->tipoAnalisis->nombre ?? 'N/A' }}
                                        </p>
                                        
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <flux:badge :color="$analisis->getColorEstado()" inset="top bottom">
                                        {{ $analisis->estado }}
                                    </flux:badge>
                                    
                                    @can('ingresar-resultados')
                                        @if($analisis->estado === 'Pendiente')
                                            <flux:button 
                                                href="{{ route('analisis.capturar-resultados', $analisis->id) }}"
                                                size="sm"
                                                variant="primary"
                                                icon="pencil"
                                            >
                                                Ingresar Resultados
                                            </flux:button>
                                        @endif
                                    @endcan

                                    @can('ver-resultados')
                                        @if(in_array($analisis->estado, ['Aprobado', 'Enviado']))
                                            <flux:button 
                                                href="{{ route('analisis.capturar-resultados', $analisis->id) }}"
                                                size="sm"
                                                variant="outline"
                                                icon="eye"
                                            >
                                                Ver Resultados
                                            </flux:button>
                                        @endif
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Footer con acciones --}}
            <div class="border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-700 dark:bg-neutral-900">
                <div class="flex flex-wrap gap-3"
                     x-data="{
                        printWindow: null,
                        printEtiqueta() {
                            if (this.printWindow && !this.printWindow.closed) {
                                this.printWindow.close();
                            }
                            const timestamp = new Date().getTime();
                            const printUrl = `{{ route('muestras.etiqueta', $muestra) }}?t=${timestamp}`;
                            const windowName = `etiqueta_{{ $muestra->id }}_${timestamp}`;
                            this.printWindow = window.open(printUrl, windowName, 'width=800,height=600');
                            if (this.printWindow) {
                                this.printWindow.onload = function() {
                                    setTimeout(() => {
                                        this.print();
                                    }, 500);
                                };
                            }
                        }
                     }">
                    <flux:button 
                        type="button"
                        x-on:click="printEtiqueta()"
                        variant="outline"
                        icon="printer"
                    >
                        Reimprimir Etiqueta
                    </flux:button>

                    <flux:button 
                        href="{{ route('muestras.index') }}"
                        variant="ghost"
                        icon="list-bullet"
                    >
                        Ver todas las muestras
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
