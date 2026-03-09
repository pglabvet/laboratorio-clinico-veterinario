{{-- Componente de edición: Tabla Temporal con Gráfica --}}
@php
    // Pre-calcular todas las filas para evitar errores de sintaxis en JavaScript
    $todasFilas = [];
    foreach(($componente['propiedades']['filas'] ?? []) as $filaIndex => $fila) {
        $todasFilas[$filaIndex] = [
            'analisis' => $fila['analisis'] ?? '',
            'hora' => '',  // El bioquímico ingresa la hora
            'rango_referencia' => $fila['rango_referencia'] ?? '',
            'unidad' => $fila['unidad'] ?? '',
            'resultado' => ''
        ];
    }
@endphp

<div 
    wire:ignore
    x-data="{
    datosExistentes: @js($componentesData[$index]['data'] ?? []),
    filas: @js($todasFilas),
    mostrarGrafica: @js($componente['propiedades']['mostrar_grafica'] ?? true),
    unidadMedida: @js($componente['propiedades']['unidad_medida'] ?? 'ug/dL'),
    chartInstance: null,
    canvasId: 'chart-{{ $index }}-' + Date.now(),
    index: {{ $index }},
    graficaGuardada: null,
    intentosGuardado: 0,
    
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
        
        // Si hay datos existentes y gráfica habilitada, inicializar automáticamente
        if (Array.isArray(this.datosExistentes) && this.datosExistentes.length > 0 && this.mostrarGrafica) {
            // Verificar si hay al menos un resultado
            const tieneResultados = this.datosExistentes.some(d => d && d.resultado && d.resultado !== '');
            if (tieneResultados) {
                setTimeout(() => {
                    this.actualizarGrafica();
                }, 200);
            }
        }
    },
    
    enviarDatos() {
        const data = Object.values(this.filas);
        window.__labvetData = window.__labvetData || {};
        window.__labvetData['{{ $index }}'] = data;
        $wire.set('componentesData.{{ $index }}.data', data);
    },
    
    // Guardar la gráfica automáticamente al servidor con reintentos
    async guardarGraficaEnServidor() {
        // Verificar que existe chartInstance y tiene el método toBase64Image
        if (!this.chartInstance || !this.mostrarGrafica || typeof this.chartInstance.toBase64Image !== 'function') {
            return;
        }
        
        const maxIntentos = 3;
        
        try {
            const image = this.chartInstance.toBase64Image();
            if (!image) return;
            
            const response = await fetch(`/analisis/{{ $analisis->id ?? 0 }}/guardar-grafica`, {
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
            
            if (response.ok) {
                this.graficaGuardada = true;
                this.intentosGuardado = 0;
            } else {
                throw new Error('Respuesta no OK: ' + response.status);
            }
        } catch (e) {
            this.intentosGuardado++;
            console.warn(`Gráfica: Intento ${this.intentosGuardado}/${maxIntentos} fallido:`, e.message);
            
            if (this.intentosGuardado < maxIntentos) {
                // Reintentar después de 2 segundos
                setTimeout(() => this.guardarGraficaEnServidor(), 2000);
            } else {
                this.graficaGuardada = false;
            }
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
        
        // Destruir gráfica existente
        const existingChart = Chart.getChart(canvas);
        if (existingChart) {
            existingChart.destroy();
        }
        
        // Verificar que TODOS los campos estén completos (hora y resultado)
        const filas = Object.values(this.filas);
        const todosCompletos = filas.every(f => {
            const tieneHora = f.hora && f.hora !== '';
            const tieneResultado = f.resultado !== '' && f.resultado !== null && !isNaN(parseFloat(f.resultado));
            return tieneHora && tieneResultado;
        });
        
        if (!todosCompletos) {
            this.chartInstance = null;
            return;
        }
        
        // Preparar datos
        const labels = filas.map(f => f.hora);
        const valores = filas.map(f => parseFloat(f.resultado));
        
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
                        borderColor: 'rgba(234, 179, 8, 0.8)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 0,
                        fill: false
                    },
                    {
                        label: 'Rango Máximo',
                        data: rangoMax,
                        borderColor: 'rgba(239, 68, 68, 0.8)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 0,
                        fill: false
                    }
                ]
            },
            options: {
                animation: {
                    duration: 0
                },
                responsive: true,
                maintainAspectRatio: false,
                devicePixelRatio: 4,
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
        
        // Guardar la gráfica automáticamente después de renderizar
        setTimeout(() => {
            this.guardarGraficaEnServidor();
        }, 500);
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
                            @change="onResultadoChange()"
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
                            @change="onResultadoChange()"
                            @blur="onResultadoChange()"
                            placeholder="Ingresar valor..."
                            class="w-full px-2 py-1 border-0 focus:ring-2 focus:ring-green-500 rounded bg-transparent text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 text-center font-semibold"
                        />
                    </td>
                    
                    {{-- Rangos de referencia (solo lectura) --}}
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-gray-900 dark:text-zinc-100 text-center">
                        {{ $fila['rango_referencia'] }}
                        @if(!empty($fila['unidad']))
                            <span class="text-gray-500 dark:text-zinc-500 ml-2">{{ $fila['unidad'] }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Gráfica de líneas --}}
    @if(($componente['propiedades']['mostrar_grafica'] ?? true))
    <div class="border border-gray-300 dark:border-zinc-700 rounded-lg overflow-hidden">
        <div class="bg-gray-100 dark:bg-zinc-800 px-4 py-2 border-b border-gray-300 dark:border-zinc-700 flex items-center justify-between">
            <h5 class="font-bold text-gray-800 dark:text-zinc-100 text-sm">
                Gráfica de Resultados - {{ $componente['propiedades']['unidad_medida'] ?? 'ug/dL' }}
            </h5>
            <button 
                @click="actualizarGrafica()"
                type="button"
                class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded text-xs font-medium transition-colors"
            >
                <i class="fas fa-sync-alt mr-1"></i> Actualizar Gráfica
            </button>
        </div>
        
        <div class="p-4 bg-white dark:bg-zinc-900">
            <div class="bg-white dark:bg-zinc-800 rounded-lg p-2" style="height: 280px;">
                <canvas x-ref="grafica" :id="'chart-canvas-' + canvasId"></canvas>
            </div>
            
            <p class="text-xs text-gray-500 dark:text-zinc-400 text-center mt-3">
                <i class="fas fa-info-circle mr-1"></i>
                La gráfica se actualiza automáticamente al ingresar los resultados
            </p>
            
            <!-- Estado de guardado de gráfica -->
            <template x-if="graficaGuardada === true">
                <p class="text-xs text-green-600 dark:text-green-400 text-center mt-2">
                    <i class="fas fa-check-circle mr-1"></i>
                    Gráfica guardada correctamente
                </p>
            </template>
            <template x-if="graficaGuardada === false">
                <p class="text-xs text-amber-600 dark:text-amber-400 text-center mt-2">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    No se pudo guardar la gráfica. Se generará al crear el PDF.
                </p>
            </template>
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
