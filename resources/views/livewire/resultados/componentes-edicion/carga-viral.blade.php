{{-- Componente de edición: Carga Viral qPCR --}}
@php
    $propiedades = $componente['propiedades'] ?? [];
    $campos = $propiedades['campos'] ?? [];
    $umbralValor = $propiedades['umbral_valor'] ?? 10;
    $umbralExponente = $propiedades['umbral_exponente'] ?? 5;
    $unidad = $propiedades['unidad'] ?? 'copias/ml';
    $patogeno = $propiedades['patogeno'] ?? 'Patógeno';
    $nombreCompleto = $propiedades['nombre_completo'] ?? '';
    $interpretaciones = $propiedades['interpretaciones'] ?? [];
    $mostrarGrafica = $propiedades['mostrar_grafica'] ?? true;
@endphp

<div 
    wire:ignore
    x-data="{
        datosExistentes: @js($componentesData[$index]['data'] ?? []),
        campos: @js($campos),
        valores: {},
        umbralValor: {{ $umbralValor }},
        umbralExponente: {{ $umbralExponente }},
        unidad: '{{ $unidad }}',
        patogeno: '{{ $patogeno }}',
        mostrarGrafica: {{ $mostrarGrafica ? 'true' : 'false' }},
        chartInstance: null,
        index: {{ $index }},
        
        init() {
            // Inicializar valores vacíos para cada campo
            this.campos.forEach((campo, i) => {
                this.valores[i] = {
                    etiqueta: campo.etiqueta || '',
                    tipo: campo.tipo || 'texto',
                    valor: ''
                };
            });
            
            // Cargar datos existentes si existen
            if (Array.isArray(this.datosExistentes) && this.datosExistentes.length > 0) {
                this.datosExistentes.forEach((item, i) => {
                    if (this.valores[i]) {
                        this.valores[i].valor = item.valor || '';
                    }
                });
                
                // Inicializar gráfica con datos existentes (con pequeño delay para asegurar que Chart.js esté listo)
                setTimeout(() => {
                    this.actualizarGrafica();
                }, 100);
            }
            
            // Escuchar evento de guardado para forzar sincronización
            window.addEventListener('antes-de-guardar', () => {
                this.sincronizarConLivewire();
            });
            
            // Escuchar solicitud de exportación de gráficas
            window.addEventListener('exportar-graficas', () => {
                if (typeof Chart !== 'undefined' && this.chartInstance && this.mostrarGrafica) {
                    const image = this.chartInstance.toBase64Image();
                    window.dispatchEvent(new CustomEvent('grafica-lista', { 
                        detail: { 
                            index: this.index, 
                            image: image 
                        } 
                    }));
                }
            });
        },
        
        sincronizarConLivewire() {
            $wire.set('componentesData.{{ $index }}.data', Object.values(this.valores));
        },
        
        // Guardar la gráfica automáticamente al servidor
        async guardarGraficaEnServidor() {
            if (!this.chartInstance || !this.mostrarGrafica) return;
            
            try {
                const image = this.chartInstance.toBase64Image();
                await fetch(`/analisis/{{ $analisis->id ?? 0 }}/guardar-grafica`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        image: image,
                        component_index: this.index
                    })
                });
            } catch (e) {
                console.warn('No se pudo guardar la gráfica:', e);
            }
        },
        
        obtenerResultadoDetectado() {
            // Buscar el campo con tipo 'select' que indica si es detectado o no
            for (let key in this.valores) {
                if (this.valores[key].tipo === 'select') {
                    return this.valores[key].valor;
                }
            }
            return '';
        },
        
        obtenerCargaViral() {
            // Buscar el campo con tipo 'numero_cientifico'
            for (let key in this.valores) {
                if (this.valores[key].tipo === 'numero_cientifico') {
                    return parseFloat(this.valores[key].valor) || 0;
                }
            }
            return 0;
        },
        
        obtenerInterpretacion() {
            const detectado = this.obtenerResultadoDetectado();
            const cargaViral = this.obtenerCargaViral();
            
            if (detectado === 'NO DETECTADO (-)' || detectado === '' || !detectado) {
                return 'no_detectado';
            } else if (cargaViral < this.umbralValor) {
                return 'regresivo';
            } else {
                return 'progresivo';
            }
        },
        
        actualizarGrafica() {
            if (!this.mostrarGrafica) return;
            
            // Verificar que Chart.js esté disponible
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js no está cargado. La gráfica no se puede mostrar.');
                return;
            }
            
            const canvas = this.$refs.grafica;
            if (!canvas) return;
            
            const cargaViral = this.obtenerCargaViral();
            const detectado = this.obtenerResultadoDetectado();
            
            // Destruir gráfica existente
            const existingChart = Chart.getChart(canvas);
            if (existingChart) {
                existingChart.destroy();
            }
            
            // Si no hay resultado, no mostrar gráfica
            if (detectado === 'NO DETECTADO (-)' || detectado === '' || cargaViral <= 0) {
                this.chartInstance = null;
                return;
            }
            
            const ctx = canvas.getContext('2d');
            const umbralValor = this.umbralValor;
            const umbralExponente = this.umbralExponente;
            const patogeno = this.patogeno;
            
            // Centrar el umbral haciendo que sea el punto medio de la escala
            const maxEscala = umbralValor * 2;
            
            // Plugin para dibujar zonas de fondo y línea del umbral
            const zonasPlugin = {
                id: 'zonasPlugin',
                beforeDatasetsDraw: (chart) => {
                    const ctx = chart.ctx;
                    const chartArea = chart.chartArea;
                    const xScale = chart.scales.x;
                    
                    const umbralPos = xScale.getPixelForValue(umbralValor);
                    
                    // Zona regresiva (verde)
                    ctx.fillStyle = 'rgba(34, 197, 94, 0.25)';
                    ctx.fillRect(chartArea.left, chartArea.top, umbralPos - chartArea.left, chartArea.bottom - chartArea.top);
                    
                    // Zona progresiva (roja)
                    ctx.fillStyle = 'rgba(239, 68, 68, 0.25)';
                    ctx.fillRect(umbralPos, chartArea.top, chartArea.right - umbralPos, chartArea.bottom - chartArea.top);
                    
                    // Línea vertical del umbral
                    ctx.beginPath();
                    ctx.strokeStyle = 'rgb(59, 130, 246)';
                    ctx.lineWidth = 3;
                    ctx.setLineDash([8, 4]);
                    ctx.moveTo(umbralPos, chartArea.top);
                    ctx.lineTo(umbralPos, chartArea.bottom);
                    ctx.stroke();
                    ctx.setLineDash([]);
                    
                    // Etiqueta del umbral
                    ctx.fillStyle = 'rgb(59, 130, 246)';
                    ctx.font = 'bold 11px Arial';
                    ctx.textAlign = 'center';
                    ctx.fillText('Umbral: ' + umbralValor, umbralPos, chartArea.top - 8);
                }
            };
            
            this.chartInstance = new Chart(ctx, {
                type: 'scatter',
                plugins: [zonasPlugin],
                data: {
                    datasets: [{
                        label: 'Paciente',
                        data: [{ x: cargaViral, y: 0.7 }],
                        backgroundColor: cargaViral >= umbralValor ? 'rgb(239, 68, 68)' : 'rgb(234, 179, 8)',
                        borderColor: cargaViral >= umbralValor ? 'rgb(185, 28, 28)' : 'rgb(161, 98, 7)',
                        borderWidth: 1,
                        pointRadius: 10,
                        pointHoverRadius: 10,
                        pointStyle: 'triangle',
                        rotation: 180,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    devicePixelRatio: 4,
                    layout: {
                        padding: {
                            top: 25
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: patogeno + ' - Posición del Paciente Respecto al Umbral',
                            font: { size: 14, weight: 'bold' },
                            padding: { bottom: 20 }
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    return 'Carga viral: ' + context.raw.x + ' × 10^' + umbralExponente + ' copias/ml';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            type: 'linear',
                            min: 0,
                            max: maxEscala,
                            position: 'bottom',
                            title: {
                                display: true,
                                text: 'Carga viral (×10^' + umbralExponente + ' copias/ml)',
                                font: { size: 12 }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                stepSize: umbralValor / 2,
                                callback: function(value) {
                                    return value;
                                }
                            }
                        },
                        y: {
                            min: 0,
                            max: 1,
                            display: false
                        }
                    }
                }
            });
            
            // Guardar la gráfica automáticamente después de renderizar
            setTimeout(() => {
                this.guardarGraficaEnServidor();
            }, 500);
        }
    }"
    class="border border-gray-200 dark:border-zinc-700 rounded-lg overflow-hidden bg-white dark:bg-zinc-900"
>
    {{-- Título --}}
    @if(isset($propiedades['titulo']))
    <div class="bg-gray-50 dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700 py-3 px-4">
        <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-lg uppercase">
            {{ $propiedades['titulo'] }}
        </h4>
        @if($nombreCompleto)
        <p class="text-gray-600 dark:text-zinc-400 text-center text-sm mt-1">{{ $nombreCompleto }}</p>
        @endif
    </div>
    @endif

    <div class="p-4 space-y-6">
        {{-- Campos editables --}}
        <div class="space-y-4">
            @foreach($campos as $i => $campo)
            <div class="grid grid-cols-12 gap-3 items-center">
                {{-- Etiqueta --}}
                <label class="col-span-4 font-semibold text-gray-700 dark:text-zinc-300 text-sm">
                    {{ $campo['etiqueta'] ?? 'Campo' }}:
                </label>
                
                {{-- Input según tipo --}}
                <div class="col-span-8">
                    @if(($campo['tipo'] ?? 'texto') === 'select')
                        {{-- Selector Detectado/No Detectado --}}
                        <select 
                            x-model="valores[{{ $i }}].valor"
                            @change="sincronizarConLivewire(); actualizarGrafica();"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100"
                        >
                            <option value="">Seleccionar...</option>
                            <option value="DETECTADO (+)">DETECTADO (+)</option>
                            <option value="NO DETECTADO (-)">NO DETECTADO (-)</option>
                        </select>
                    @elseif(($campo['tipo'] ?? 'texto') === 'numero_cientifico')
                        {{-- Número con notación científica --}}
                        <div class="flex items-center">
                            <div class="flex items-center flex-1 px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800">
                                <span class="text-gray-500 dark:text-zinc-400 text-sm mr-2">qPCR:</span>
                                <input 
                                    type="number"
                                    step="0.01"
                                    x-model="valores[{{ $i }}].valor"
                                    @change="sincronizarConLivewire(); actualizarGrafica();"
                                    @blur="sincronizarConLivewire(); actualizarGrafica();"
                                    placeholder="0.00"
                                    class="flex-1 min-w-0 border-0 p-0 focus:ring-0 bg-transparent text-gray-900 dark:text-zinc-100 text-center font-medium"
                                />
                                <span class="text-gray-600 dark:text-zinc-400 text-sm ml-2 whitespace-nowrap">
                                    × 10<sup>{{ $umbralExponente }}</sup> {{ $unidad }}
                                </span>
                            </div>
                        </div>
                    @else
                        {{-- Texto libre --}}
                        <input 
                            type="text"
                            x-model="valores[{{ $i }}].valor"
                            @change="sincronizarConLivewire()"
                            @blur="sincronizarConLivewire()"
                            placeholder="Completar..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500"
                        />
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Sección de Interpretación de Resultados en columnas --}}
        <div class="border border-gray-300 dark:border-zinc-700 rounded-lg overflow-hidden">
            <div class="bg-gray-100 dark:bg-zinc-800 px-4 py-2 border-b border-gray-300 dark:border-zinc-700">
                <h5 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-sm">INTERPRETACIÓN DE RESULTADOS</h5>
            </div>
            
            <div class="p-3 grid grid-cols-3 gap-2 text-xs">
                {{-- No Detectado --}}
                <div 
                    class="pl-2 py-2 rounded-r transition-all"
                    style="border-left-width: 3px;"
                    :class="obtenerInterpretacion() === 'no_detectado' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-300 dark:border-zinc-600 opacity-50'"
                    :style="obtenerInterpretacion() === 'no_detectado' ? 'border-left-color: rgb(34, 197, 94)' : 'border-left-color: rgb(209, 213, 219)'"
                >
                    <p class="font-bold text-xs leading-tight" :class="obtenerInterpretacion() === 'no_detectado' ? 'text-green-700 dark:text-green-400' : 'text-gray-500 dark:text-zinc-500'">
                        {{ $patogeno }} NO DETECTADO:
                    </p>
                    <p class="text-[10px] leading-tight mt-1" :class="obtenerInterpretacion() === 'no_detectado' ? 'text-green-600 dark:text-green-300' : 'text-gray-400 dark:text-zinc-600'">
                        {{ $interpretaciones['no_detectado']['descripcion'] ?? 'Sin detección de ADN viral en la muestra.' }}
                    </p>
                </div>

                {{-- Infección Regresiva --}}
                <div 
                    class="pl-2 py-2 rounded-r transition-all"
                    style="border-left-width: 3px;"
                    :class="obtenerInterpretacion() === 'regresivo' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-300 dark:border-zinc-600 opacity-50'"
                    :style="obtenerInterpretacion() === 'regresivo' ? 'border-left-color: rgb(234, 179, 8)' : 'border-left-color: rgb(209, 213, 219)'"
                >
                    <p class="font-bold text-xs leading-tight" :class="obtenerInterpretacion() === 'regresivo' ? 'text-yellow-700 dark:text-yellow-400' : 'text-gray-500 dark:text-zinc-500'">
                        {{ $patogeno }} - REGRESIVA:
                    </p>
                    <p class="text-[10px] leading-tight mt-1" :class="obtenerInterpretacion() === 'regresivo' ? 'text-yellow-600 dark:text-yellow-300' : 'text-gray-400 dark:text-zinc-600'">
                        {{ $interpretaciones['regresivo']['descripcion'] ?? 'Carga viral baja, fase de resolución.' }}
                    </p>
                    <p class="text-[9px] mt-1" :class="obtenerInterpretacion() === 'regresivo' ? 'text-yellow-500 dark:text-yellow-400' : 'text-gray-400 dark:text-zinc-600'">
                        ADN &lt; {{ $umbralValor }} × 10<sup>{{ $umbralExponente }}</sup>
                    </p>
                </div>

                {{-- Infección Progresiva --}}
                <div 
                    class="pl-2 py-2 rounded-r transition-all"
                    style="border-left-width: 3px;"
                    :class="obtenerInterpretacion() === 'progresivo' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-zinc-600 opacity-50'"
                    :style="obtenerInterpretacion() === 'progresivo' ? 'border-left-color: rgb(239, 68, 68)' : 'border-left-color: rgb(209, 213, 219)'"
                >
                    <p class="font-bold text-xs leading-tight" :class="obtenerInterpretacion() === 'progresivo' ? 'text-red-700 dark:text-red-400' : 'text-gray-500 dark:text-zinc-500'">
                        {{ $patogeno }} - PROGRESIVA:
                    </p>
                    <p class="text-[10px] leading-tight mt-1" :class="obtenerInterpretacion() === 'progresivo' ? 'text-red-600 dark:text-red-300' : 'text-gray-400 dark:text-zinc-600'">
                        {{ $interpretaciones['progresivo']['descripcion'] ?? 'Carga viral alta, infección activa.' }}
                    </p>
                    <p class="text-[9px] mt-1" :class="obtenerInterpretacion() === 'progresivo' ? 'text-red-500 dark:text-red-400' : 'text-gray-400 dark:text-zinc-600'">
                        ADN &gt; {{ $umbralValor }} × 10<sup>{{ $umbralExponente }}</sup>
                    </p>
                </div>
            </div>
        </div>

        {{-- Gráfica --}}
        @if($mostrarGrafica)
        <div class="border border-gray-300 dark:border-zinc-700 rounded-lg overflow-hidden">
            <div class="bg-gray-100 dark:bg-zinc-800 px-4 py-2 border-b border-gray-300 dark:border-zinc-700 flex items-center justify-between">
                <h5 class="font-bold text-gray-800 dark:text-zinc-100 text-sm">
                    {{ $patogeno }} - Posición del Paciente Respecto al Umbral
                </h5>
                <button 
                    @click="actualizarGrafica()"
                    class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded text-xs font-medium transition-colors"
                >
                    <i class="fas fa-sync-alt mr-1"></i> Actualizar Gráfica
                </button>
            </div>
            
            <div class="p-4 bg-white dark:bg-zinc-900">
                {{-- Leyenda --}}
                <div class="flex flex-wrap items-center gap-4 mb-4 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 bg-green-100 border-2 border-green-500 rounded"></span>
                        <span class="text-gray-600 dark:text-zinc-400">Zona Regresiva (&lt; {{ $umbralValor }})</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 bg-red-100 border-2 border-red-500 rounded"></span>
                        <span class="text-gray-600 dark:text-zinc-400">Zona Progresiva (&gt; {{ $umbralValor }})</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-0 border-t-2 border-dashed border-blue-500"></span>
                        <span class="text-gray-600 dark:text-zinc-400">Umbral ({{ $umbralValor }} × 10<sup>{{ $umbralExponente }}</sup>)</span>
                    </div>
                </div>

                {{-- Canvas de la gráfica --}}
                <div class="h-32 relative">
                    <canvas x-ref="grafica"></canvas>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Cargar Chart.js si no está cargado --}}
@once
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@endonce
