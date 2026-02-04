{{-- Componente de edición: Tabla Temporal con Gráfica --}}
@php
    // Pre-calcular todas las filas para evitar errores de sintaxis en JavaScript
    $todasFilas = [];
    foreach(($componente['propiedades']['filas'] ?? []) as $filaIndex => $fila) {
        $todasFilas[$filaIndex] = [
            'analisis' => $fila['analisis'] ?? '',
            'hora' => '',  // El bioquímico ingresa la hora
            'rango_referencia' => $fila['rango_referencia'] ?? '',
            'resultado' => ''
        ];
    }
@endphp

<div x-data="{
    datosExistentes: @js($componentesData[$index]['data'] ?? []),
    filas: @js($todasFilas),
    mostrarGrafica: @js($componente['propiedades']['mostrar_grafica'] ?? true),
    mostrarGraficaActual: false,
    unidadMedida: @js($componente['propiedades']['unidad_medida'] ?? 'ug/dL'),
    chartInstance: null,
    canvasId: 'chart-{{ $index }}-' + Date.now(),
    index: {{ $index }},
    
    init() {
        // Cargar datos existentes si existen
        if (Array.isArray(this.datosExistentes) && this.datosExistentes.length > 0) {
            @foreach($componente['propiedades']['filas'] ?? [] as $filaIndex => $fila)
                if (this.datosExistentes[{{ $filaIndex }}]) {
                    this.filas[{{ $filaIndex }}].resultado = this.datosExistentes[{{ $filaIndex }}].resultado || '';
                    this.filas[{{ $filaIndex }}].hora = this.datosExistentes[{{ $filaIndex }}].hora || '';
                }
            @endforeach
        }
        
        // Escuchar evento de guardado para forzar sincronización
        window.addEventListener('antes-de-guardar', () => {
            this.enviarDatos();
        });
        
        // Escuchar solicitud de exportación de gráficas
        window.addEventListener('exportar-graficas', () => {
             if (this.chartInstance && this.mostrarGrafica) {
                 const image = this.chartInstance.toBase64Image();
                 window.dispatchEvent(new CustomEvent('grafica-lista', { 
                     detail: { 
                         index: this.index, 
                         image: image 
                     } 
                 }));
             }
        });
        
        // NO crear gráfica automáticamente - esperar a que el usuario haga click
    },
    
    enviarDatos() {
        $wire.set('componentesData.{{ $index }}.data', Object.values(this.filas));
    },
    
    actualizarGrafica() {
        if (!this.mostrarGrafica || !this.mostrarGraficaActual) return;
        
        const canvas = this.$refs.grafica;
        if (!canvas) return;
        
        // Usar el método oficial de Chart.js para obtener y destruir
        const existingChart = Chart.getChart(canvas);
        if (existingChart) {
            existingChart.destroy();
        }
        
        this.chartInstance = null;
        
        // Pequeña pausa antes de crear
        setTimeout(() => {
            try {
                const canvas = this.$refs.grafica;
                if (!canvas) return;
                
                // Preparar datos
                const labels = Object.values(this.filas).map(f => f.hora || '--:--');
                const valores = Object.values(this.filas).map(f => {
                    const val = parseFloat(f.resultado);
                    return isNaN(val) ? null : val;
                });
                
                // Rangos
                const rangos = Object.values(this.filas).map(f => {
                    const rango = f.rango_referencia;
                    const match = rango.match(/(\d+\.?\d*)\s*-\s*(\d+\.?\d*)/);
                    if (match) {
                        return {
                            min: parseFloat(match[1]),
                            max: parseFloat(match[2])
                        };
                    }
                    return null;
                });
                
                const rangoMin = rangos.map(r => r ? r.min : null);
                const rangoMax = rangos.map(r => r ? r.max : null);
                
                const ctx = canvas.getContext('2d');
                
                this.chartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Resultado',
                                data: valores,
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 3,
                                pointRadius: 6,
                                pointHoverRadius: 8,
                                pointBackgroundColor: 'rgb(59, 130, 246)',
                                tension: 0.3,
                                fill: false
                            },
                            {
                                label: 'Rango Mínimo',
                                data: rangoMin,
                                borderColor: 'rgba(234, 179, 8, 0.8)', // Yellow-500
                                borderWidth: 2,
                                borderDash: [5, 5],
                                pointRadius: 0,
                                fill: false
                            },
                            {
                                label: 'Rango Máximo',
                                data: rangoMax,
                                borderColor: 'rgba(239, 68, 68, 0.8)', // Red-500
                                borderWidth: 2,
                                borderDash: [5, 5],
                                pointRadius: 0,
                                fill: false
                            }
                        ]
                    },
                    options: {
                        animation: {
                            duration: 0 // Desactivar animación para garantizar captura completa
                        },
                        responsive: true,
                        maintainAspectRatio: false,
                        devicePixelRatio: 4, // Alta resolución para exportación PDF
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    color: document.documentElement.classList.contains('dark') ? '#e4e4e7' : '#18181b',
                                    usePointStyle: true,
                                    padding: 15
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                callbacks: {
                                    label: (context) => {
                                        let label = context.dataset.label || '';
                                        if (label) label += ': ';
                                        if (context.parsed.y !== null) {
                                            label += context.parsed.y + ' ' + this.unidadMedida;
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                ticks: {
                                    color: document.documentElement.classList.contains('dark') ? '#a1a1aa' : '#52525b',
                                    callback: (value) => value + ' ' + this.unidadMedida
                                },
                                grid: {
                                    color: document.documentElement.classList.contains('dark') ? '#3f3f46' : '#e4e4e7'
                                }
                            },
                            x: {
                                ticks: {
                                    color: document.documentElement.classList.contains('dark') ? '#a1a1aa' : '#52525b'
                                },
                                grid: {
                                    color: document.documentElement.classList.contains('dark') ? '#3f3f46' : '#e4e4e7'
                                }
                            }
                        }
                    }
                });
            } catch (e) {
                console.error('Error creando gráfica:', e);
            }
        }, 50);
    },
    
    onResultadoChange() {
        this.enviarDatos();
        if (this.mostrarGrafica) {
            this.actualizarGrafica();
        }
    }
}"
x-init="init()"
class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    
    @if(isset($componente['propiedades']['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-lg mb-4">
        {{ $componente['propiedades']['titulo'] }}
    </h4>
    @endif

    <div class="overflow-x-auto mb-6">
        <table class="w-full border border-gray-300 dark:border-zinc-700 text-sm">
            <thead>
                <tr class="bg-gray-100 dark:bg-zinc-900">
                    <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-gray-900 dark:text-zinc-100 font-semibold">Análisis</th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-gray-900 dark:text-zinc-100 font-semibold">Hora</th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-gray-900 dark:text-zinc-100 font-semibold">Resultado</th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-gray-900 dark:text-zinc-100 font-semibold">Rangos de referencia</th>
                </tr>
            </thead>
            <tbody>
                @foreach($componente['propiedades']['filas'] ?? [] as $filaIndex => $fila)
                <tr>
                    {{-- Análisis (solo lectura) --}}
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold text-gray-900 dark:text-zinc-100">
                        {{ $fila['analisis'] }}
                    </td>
                    
                    {{-- Hora (editable) --}}
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-2">
                        <input 
                            type="time"
                            x-model="filas[{{ $filaIndex }}].hora"
                            @blur="onResultadoChange()"
                            class="w-full px-2 py-1 border-0 focus:ring-2 focus:ring-green-500 rounded bg-transparent text-gray-900 dark:text-zinc-100 text-center font-semibold"
                        />
                    </td>
                    
                    {{-- Resultado (editable) --}}
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-2">
                        <input 
                            type="number"
                            step="0.01"
                            x-model="filas[{{ $filaIndex }}].resultado"
                            @blur="onResultadoChange()"
                            placeholder="Ingresar valor..."
                            class="w-full px-2 py-1 border-0 focus:ring-2 focus:ring-green-500 rounded bg-transparent text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 text-center font-semibold"
                        />
                    </td>
                    
                    {{-- Rangos de referencia (solo lectura) --}}
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-gray-900 dark:text-zinc-100 text-center">
                        {{ $fila['rango_referencia'] }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Gráfica de líneas --}}
    @if(($componente['propiedades']['mostrar_grafica'] ?? true))
    <div class="bg-gray-50 dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg p-4">
        {{-- Botón para mostrar/ocultar gráfica --}}
        <div class="flex items-center justify-between mb-3">
            <h5 class="font-semibold text-gray-800 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-chart-line text-blue-600 dark:text-blue-400"></i>
                Gráfica de Resultados
            </h5>
            <button 
                @click="mostrarGraficaActual = !mostrarGraficaActual; if(mostrarGraficaActual) { setTimeout(() => actualizarGrafica(), 100); }"
                type="button"
                class="px-4 py-2 rounded-lg font-medium transition-colors"
                :class="mostrarGraficaActual 
                    ? 'bg-blue-600 text-white hover:bg-blue-700' 
                    : 'bg-gray-200 dark:bg-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-300 dark:hover:bg-zinc-600'"
            >
                <i class="fas" :class="mostrarGraficaActual ? 'fa-eye-slash' : 'fa-eye'"></i>
                <span x-text="mostrarGraficaActual ? 'Ocultar Gráfica' : 'Mostrar Gráfica'"></span>
            </button>
        </div>
        
        {{-- Contenedor de gráfica con x-show --}}
        <div x-show="mostrarGraficaActual" x-transition class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500 dark:text-zinc-400">Unidad: {{ $componente['propiedades']['unidad_medida'] ?? 'ug/dL' }}</span>
            </div>
            
            <div class="bg-white dark:bg-zinc-800 rounded-lg p-4" style="height: 300px;">
                <canvas x-ref="grafica" :id="'chart-canvas-' + canvasId"></canvas>
            </div>
            
            <p class="text-xs text-gray-500 dark:text-zinc-400 text-center">
                <i class="fas fa-info-circle mr-1"></i>
                La gráfica se actualiza automáticamente al ingresar los resultados
            </p>
        </div>
        
        {{-- Mensaje cuando está oculta --}}
        <div x-show="!mostrarGraficaActual" class="text-center py-8">
            <i class="fas fa-chart-line text-gray-400 dark:text-zinc-600 text-4xl mb-3"></i>
            <p class="text-sm text-gray-500 dark:text-zinc-400">
                Haz clic en <strong>"Mostrar Gráfica"</strong> para visualizar los resultados
            </p>
        </div>
    </div>
    @endif
</div>

{{-- Cargar Chart.js si no está cargado --}}
@once
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@endonce
