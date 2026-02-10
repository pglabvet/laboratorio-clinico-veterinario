<div class="min-h-screen bg-gray-50 dark:bg-zinc-800" x-data="gestorDescargaPDF()">
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
                                {{ $analisis->muestra->sexo ?? 'N/A' }}
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
            <div class="space-y-6">
                @foreach($plantilla->componentes as $index => $componente)
                    @if(view()->exists('livewire.resultados.componentes-edicion.' . $componente['tipo']))
                        @include('livewire.resultados.componentes-edicion.' . $componente['tipo'], [
                            'componente' => $componente,
                            'index' => $index,
                            'componentesData' => $componentesData,
                            'analisis' => $analisis
                        ])
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

            {{-- Botones de acción --}}
            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200 dark:border-zinc-700">
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
                            {{-- Botón PDF solo para análisis aprobados o enviados --}}
                            @can('descargar-pdf-analisis')
                            <flux:button 
                                wire:click="descargarPdf"
                                variant="primary" 
                                icon="document-arrow-down">
                                Descargar PDF
                            </flux:button>
                            @endcan
                        @else
                            {{-- Botón Actualizar Datos para guardar cambios --}}
                            @can('actualizar-datos-analisis')
                            <div x-data="{ loading: false }">
                                <flux:button 
                                    @click="loading = true; window.dispatchEvent(new Event('antes-de-guardar')); setTimeout(() => { $wire.actualizarDatosRevision().then(() => loading = false).catch(() => loading = false) }, 100)"
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
                                wire:click="aprobarAnalisis"
                                variant="primary" 
                                icon="check-circle">
                                Aprobar
                            </flux:button>
                            @endcan
                        @endif
                    </div>
                @else
                    {{-- Botones normales de captura --}}
                    <div class="flex gap-3">
                        <flux:button 
                            @click="window.dispatchEvent(new Event('antes-de-guardar')); setTimeout(() => $wire.guardarBorrador(), 100)"
                            variant="outline" 
                            icon="document">
                            Guardar Borrador
                        </flux:button>
                        <flux:button 
                            @click="window.dispatchEvent(new Event('antes-de-guardar')); setTimeout(() => $wire.finalizarYEnviar(), 100)"
                            variant="primary" 
                            icon="check">
                            Finalizar y Enviar
                        </flux:button>
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
            async descargarPDF() {
                // Notificar inicio
                const btn = document.getElementById('btn-descargar-pdf');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generando PDF...';
                btn.disabled = true;
                
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
                    
                    // Descargar PDF
                    const url = `{{ route('analisis.pdf', $analisis->id) }}`;
                    window.open(url, '_blank');
                    
                } catch (e) {
                    console.error('Error general:', e);
                    alert('Error al generar el PDF');
                } finally {
                    // Restaurar botón
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }, 500);
                }
            },
            graficas: []
        }
    }
</script>
@endpush
