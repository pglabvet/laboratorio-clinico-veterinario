<div class="min-h-screen bg-gray-50 dark:bg-zinc-800" 
    x-data="gestorDescargaPDF()"
    @keydown.ctrl.s.prevent.window="if (!{{ json_encode($modoRevision) }}) { window.__labvetData = {}; window.dispatchEvent(new Event('antes-de-guardar')); $wire.guardarBorrador(window.__labvetData); }"
    @keydown.ctrl.enter.prevent.window="if (!{{ json_encode($modoRevision) }}) { window.__labvetData = {}; window.dispatchEvent(new Event('antes-de-guardar')); $wire.finalizarYEnviar(window.__labvetData); }">

    <div class="container mx-auto px-4 py-6">
        {{-- Header con info del análisis --}}
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <flux:heading size="xl">{{ $modoRevision ? 'Revisar Análisis' : 'Capturar Resultados' }}</flux:heading>
                    <flux:subheading>{{ $analisis->tipoAnalisis->nombre }}</flux:subheading>
                </div>
                <div class="flex items-center gap-3">
                    <flux:badge 
                        :color="$analisis->estado === 'En revision' ? 'blue' : ($analisis->estado === 'Aprobado' ? 'green' : ($analisis->estado === 'Enviado' ? 'purple' : 'amber'))" 
                        size="lg">
                        {{ $analisis->estado }}
                    </flux:badge>
                    <flux:badge color="zinc" size="lg">
                        Análisis #{{ $analisis->id }}
                    </flux:badge>
                </div>
            </div>
            
            @if($analisis->estado === 'Pendiente' && $analisis->observaciones_aprobador)
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="flex-1">
                            <p class="font-semibold text-red-900 dark:text-red-100 mb-1">Análisis devuelto para corrección</p>
                            <p class="text-sm text-red-700 dark:text-red-300">{{ $analisis->observaciones_aprobador }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Info del paciente y análisis --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6 bg-gray-50 dark:bg-zinc-800 rounded-lg">
                {{-- Columna 1: Paciente --}}
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Paciente</p>
                        <p class="font-semibold text-gray-900 dark:text-zinc-100">
                            {{ $analisis->muestra->paciente_nombre }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Propietario</p>
                        <p class="text-sm text-gray-900 dark:text-zinc-100">
                            {{ $analisis->muestra->propietario_nombre }}
                        </p>
                    </div>
                </div>

                {{-- Columna 2: Características del paciente --}}
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Especie</p>
                        <p class="text-sm text-gray-900 dark:text-zinc-100">
                            {{ $analisis->muestra->especie->nombre ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Raza</p>
                        <p class="text-sm text-gray-900 dark:text-zinc-100">
                            {{ $analisis->muestra->raza ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                {{-- Columna 3: Edad, Sexo, Color --}}
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Edad</p>
                            <p class="text-sm text-gray-900 dark:text-zinc-100">
                                {{ $analisis->muestra->edad ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Sexo</p>
                            <p class="text-sm text-gray-900 dark:text-zinc-100">
                                {{ $analisis->muestra->sexo === 'M' ? 'Macho' : ($analisis->muestra->sexo === 'H' ? 'Hembra' : 'N/A') }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Color</p>
                        <p class="text-sm text-gray-900 dark:text-zinc-100">
                            {{ $analisis->muestra->color ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                {{-- Columna 4: Muestra e información --}}
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Código de Muestra</p>
                        <p class="font-semibold text-blue-600 dark:text-blue-400">
                            {{ $analisis->muestra->codigo_muestra }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Solicitado por</p>
                        <p class="text-sm text-gray-900 dark:text-zinc-100">
                            {{ $analisis->muestra->veterinaria->nombre ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Fecha</p>
                        <p class="text-sm text-gray-900 dark:text-zinc-100">
                            {{ $analisis->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Formulario de resultados (dinámico desde plantilla) --}}
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-md p-6">
            <flux:heading size="lg" class="mb-6">
                {{ $plantilla->nombre }}
            </flux:heading>

            @if($plantilla->descripcion)
            <p class="text-sm text-gray-600 dark:text-zinc-400 mb-6">{{ $plantilla->descripcion }}</p>
            @endif

            {{-- Renderizado dinámico de componentes desde JSON --}}
            <div class="space-y-4">
                @foreach($plantilla->componentes as $index => $componente)
                    @if(view()->exists('livewire.resultados.componentes-edicion.' . $componente['tipo']))
                        @if($componente['tipo'] === 'subtitulo')
                            {{-- Subtítulo no se colapsa --}}
                            @include('livewire.resultados.componentes-edicion.' . $componente['tipo'], [
                                'componente' => $componente,
                                'index' => $index,
                                'componentesData' => $componentesData,
                                'analisis' => $analisis
                            ])
                        @else
                        <div x-data="{ abierto: true }">
                            {{-- Barra colapsable --}}
                            <button type="button" @click="abierto = !abierto" class="w-full flex items-center justify-between px-4 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors" :class="abierto ? 'rounded-b-none border-b-0' : ''">
                                <span class="font-semibold text-gray-700 dark:text-zinc-300 text-sm uppercase tracking-wide">
                                    {{ $componente['propiedades']['titulo'] ?? str_replace('-', ' ', $componente['tipo']) }}
                                </span>
                                <svg :class="{ 'rotate-180': abierto }" class="w-4 h-4 text-gray-500 dark:text-zinc-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            {{-- Contenido colapsable --}}
                            <div x-show="abierto" x-collapse>
                                @include('livewire.resultados.componentes-edicion.' . $componente['tipo'], [
                                    'componente' => $componente,
                                    'index' => $index,
                                    'componentesData' => $componentesData,
                                    'analisis' => $analisis
                                ])
                            </div>
                        </div>
                        @endif
                    @else
                        <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                            <p class="text-sm text-yellow-800 dark:text-yellow-300">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Componente "{{ $componente['tipo'] }}" no encontrado
                            </p>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Banner de error persistente para stock insuficiente --}}
            @if($errorStock)
                <div class="mt-8 p-4 bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-800 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-red-500 dark:text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"></path>
                        </svg>
                        <div class="flex-1">
                            <p class="font-semibold text-red-800 dark:text-red-200">No se pudieron enviar los resultados</p>
                            <p class="text-sm text-red-600 dark:text-red-300 mt-1">{{ $errorStock }}</p>
                            <p class="text-xs text-red-500 dark:text-red-400 mt-2">Verifique el inventario de insumos y vuelva a intentarlo.</p>
                        </div>
                        <button wire:click="$set('errorStock', '')" class="text-red-400 hover:text-red-600 dark:hover:text-red-300 flex-shrink-0 p-1">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Botones de acción --}}
            <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-200 dark:border-zinc-700">
                <flux:button 
                    wire:click="cancelar" 
                    variant="outline" 
                    icon="arrow-left"
                    class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950">
                    {{ $modoRevision ? 'Volver' : 'Cancelar' }}
                </flux:button>
                
                @if($modoRevision)
                    {{-- Botones de revisión (Aprobar/Rechazar) --}}
                    <div class="flex gap-3">
                        @if($analisis->estado === 'Aprobado' || $analisis->estado === 'Enviado')
                            {{-- Botón Ver PDF (abre en navegador) --}}
                            @can('descargar-pdf-analisis')
                            <flux:button 
                                @click="descargarPDF('ver')"
                                variant="primary" 
                                icon="eye">
                                Ver PDF
                            </flux:button>
                            <flux:button 
                                @click="descargarPDF('descargar')"
                                variant="outline" 
                                icon="arrow-down-tray"
                                class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950">
                                Descargar PDF
                            </flux:button>
                            <flux:button 
                                @click="descargarPDF('ver', 'limpio')"
                                variant="outline" 
                                icon="eye"
                                class="border-amber-500 text-amber-600 hover:bg-amber-50 dark:border-amber-400 dark:text-amber-400 dark:hover:bg-amber-950">
                                Ver PDF Limpio
                            </flux:button>
                            <flux:button 
                                @click="descargarPDF('descargar', 'limpio')"
                                variant="outline" 
                                icon="arrow-down-tray"
                                class="border-amber-500 text-amber-600 hover:bg-amber-50 dark:border-amber-400 dark:text-amber-400 dark:hover:bg-amber-950">
                                Descargar PDF Limpio
                            </flux:button>
                            @endcan
                        @else
                            {{-- Botón Actualizar Datos para guardar cambios --}}
                            @can('actualizar-datos-analisis')
                            <div x-data="{ loading: false }">
                                <flux:button 
                                    @click="loading = true; window.__labvetData = {}; window.dispatchEvent(new Event('antes-de-guardar')); $wire.actualizarDatosRevision(window.__labvetData).then(() => loading = false).catch(() => loading = false)"
                                    variant="outline"
                                    class="min-w-[130px] justify-center"
                                    x-bind:disabled="loading">
                                    <svg x-show="loading" x-cloak class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-show="!loading">Actualizar Datos</span>
                                </flux:button>
                            </div>
                            @endcan
                            @can('rechazar-analisis')
                            <flux:button 
                                wire:click="abrirModalRechazo"
                                variant="danger" 
                                icon="x-circle">
                                Rechazar
                            </flux:button>
                            @endcan
                            @can('aprobar-analisis')
                            <flux:button 
                                @click="window.__labvetData = {}; window.dispatchEvent(new Event('antes-de-guardar')); $wire.aprobarAnalisis(window.__labvetData)"
                                variant="primary" 
                                icon="check-circle">
                                Aprobar
                            </flux:button>
                            @endcan
                        @endif
                    </div>
                @else
                    {{-- Botones normales de captura --}}
                    <div class="flex gap-3 items-center">
                        <span class="text-xs text-gray-400 dark:text-zinc-500 hidden md:inline"><kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-zinc-700 rounded text-gray-500 dark:text-zinc-400 font-mono">Ctrl+S</kbd> borrador · <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-zinc-700 rounded text-gray-500 dark:text-zinc-400 font-mono">Ctrl+Enter</kbd> enviar</span>
                        @can('guardar-borrador-resultados')
                        <flux:button 
                            @click="window.__labvetData = {}; window.dispatchEvent(new Event('antes-de-guardar')); $wire.guardarBorrador(window.__labvetData)"
                            variant="outline" 
                            icon="document">
                            Guardar Borrador
                        </flux:button>
                        @endcan
                        @can('registrar-resultados')
                        <flux:button 
                            @click="window.__labvetData = {}; window.dispatchEvent(new Event('antes-de-guardar')); $wire.finalizarYEnviar(window.__labvetData)"
                            variant="primary" 
                            icon="check">
                            Finalizar y Enviar
                        </flux:button>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
        
        {{-- Modal de Rechazo --}}
        <flux:modal wire:model="mostrarModalRechazo" class="w-full max-w-md">
            <div class="space-y-6">
                {{-- Ícono de advertencia --}}
                <div class="flex justify-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                </div>

                {{-- Título y mensaje --}}
                <div class="text-center">
                    <flux:heading size="lg" class="mb-2">Rechazar Análisis</flux:heading>
                    <flux:subheading>Indique el motivo del rechazo</flux:subheading>
                </div>

                {{-- Campo de observaciones --}}
                <div>
                    <flux:textarea 
                        wire:model="observacionesRechazo"
                        label="Motivo del rechazo"
                        rows="5"
                        placeholder="Describa los errores o problemas encontrados en el análisis..."
                        required
                    />
                </div>

                {{-- Botones --}}
                <div class="flex gap-3 justify-end">
                    <flux:button 
                        wire:click="$set('mostrarModalRechazo', false)" 
                        variant="outline"
                        class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950">
                        Cancelar
                    </flux:button>
                    <flux:button 
                        wire:click="rechazarAnalisis"
                        variant="danger" 
                        icon="x-circle">
                        Confirmar Rechazo
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
</div>

@push('scripts')
<script>
    function gestorDescargaPDF() {
        return {
            async descargarPDF(modo = 'ver', formato = 'completo') {
                try {
                    this.graficas = [];
                    
                    // Escuchar respuestas (una sola vez para esta ejecución)
                    const handler = (e) => {
                        this.graficas.push(e.detail);
                    };
                    window.addEventListener('grafica-lista', handler);
                    
                    // Solicitar exportación a todos los componentes
                    window.dispatchEvent(new Event('exportar-graficas'));
                    
                    // Esperar 1 segundo para asegurar que todos respondan
                    await new Promise(r => setTimeout(r, 1000));
                    
                    window.removeEventListener('grafica-lista', handler);
                    
                    // Enviar al backend si hay gráficas
                    if (this.graficas.length > 0) {
                        try {
                            for (const g of this.graficas) {
                                await fetch(`{{ route('analisis.guardar-grafica', $analisis->id) }}`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        image: g.image,
                                        component_index: g.index
                                    })
                                });
                            }
                        } catch (e) {
                            console.error('Error guardando gráficas', e);
                            alert('Hubo un error guardando las gráficas, el PDF podría no incluirlas todas.');
                        }
                    }
                    
                    // Abrir PDF según el modo y formato
                    let url;
                    if (formato === 'limpio') {
                        url = modo === 'ver'
                            ? `{{ route('analisis.ver-pdf-limpio', $analisis->id) }}`
                            : `{{ route('analisis.pdf-limpio', $analisis->id) }}`;
                    } else {
                        url = modo === 'ver'
                            ? `{{ route('analisis.ver-pdf', $analisis->id) }}`
                            : `{{ route('analisis.pdf', $analisis->id) }}`;
                    }
                    window.open(url, '_blank');
                    
                } catch (e) {
                    console.error('Error general:', e);
                    alert('Error al generar el PDF');
                }
            },
            graficas: []
        }
    }
</script>
@endpush
