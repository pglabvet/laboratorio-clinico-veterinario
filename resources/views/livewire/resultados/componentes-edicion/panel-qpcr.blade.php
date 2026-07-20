{{-- Componente de edición: Panel qPCR (Múltiples Patógenos)
     Cada patógeno es funcionalmente idéntico a "Carga Viral qPCR". --}}
@php
    $propiedades = $componente['propiedades'] ?? [];
    $patogenos   = $propiedades['patogenos']  ?? [];
@endphp

<div
    wire:ignore
    x-data="{
        patogenos: @js($patogenos),
        datosExistentes: @js($componentesData[$index]['data'] ?? []),
        valores: {},
        chartInstances: {},
        index: {{ $index }},
        analisisId: {{ $analisis->id ?? 0 }},

        init() {
            // Inicializar valores vacíos para cada campo de cada patógeno
            this.patogenos.forEach((patogeno, pi) => {
                const campos = patogeno.campos || [];
                this.valores[pi] = {};
                campos.forEach((campo, ci) => {
                    this.valores[pi][ci] = {
                        etiqueta: campo.etiqueta || '',
                        tipo: campo.tipo || 'texto',
                        valor: ''
                    };
                });
            });

            // Cargar datos existentes
            if (this.datosExistentes && typeof this.datosExistentes === 'object') {
                Object.keys(this.datosExistentes).forEach(piStr => {
                    const pi = parseInt(piStr);
                    if (!isNaN(pi) && this.valores[pi] && typeof this.datosExistentes[pi] === 'object') {
                        Object.keys(this.datosExistentes[pi]).forEach(ciStr => {
                            const ci = parseInt(ciStr);
                            if (!isNaN(ci) && this.valores[pi][ci] !== undefined) {
                                const dato = this.datosExistentes[pi][ci];
                                this.valores[pi][ci].valor = (dato && dato.valor !== undefined) ? dato.valor : (typeof dato === 'string' ? dato : '');
                            }
                        });
                    }
                });
                // Redibujar gráficas con datos existentes
                setTimeout(() => {
                    this.patogenos.forEach((p, pi) => { this.actualizarGrafica(pi); });
                }, 100);
            }

            // Escuchar evento de guardado
            window.addEventListener('antes-de-guardar', () => this.sincronizarConLivewire());

            // Escuchar solicitud de exportación de gráficas
            window.addEventListener('exportar-graficas', () => {
                this.patogenos.forEach((p, pi) => {
                    if (this.chartInstances[pi]) {
                        const image = this.chartInstances[pi].toBase64Image();
                        window.dispatchEvent(new CustomEvent('grafica-lista', {
                            detail: { index: this.index, patogeno_index: pi, image }
                        }));
                    }
                });
            });
        },

        sincronizarConLivewire() {
            window.__labvetData = window.__labvetData || {};
            window.__labvetData['{{ $index }}'] = this.valores;
            $wire.set('componentesData.{{ $index }}.data', this.valores);
            window.dispatchEvent(new CustomEvent('datos-sincronizados', { detail: { index: {{ $index }} } }));
        },

        // ─── Helpers de interpretación por patógeno (igual que carga-viral) ───

        obtenerResultadoDetectado(pi) {
            const campos = this.valores[pi] || {};
            for (let key in campos) {
                if (campos[key].tipo === 'select') return campos[key].valor;
            }
            return '';
        },

        obtenerCargaViral(pi) {
            const campos = this.valores[pi] || {};
            for (let key in campos) {
                if (campos[key].tipo === 'numero_cientifico') return parseFloat(campos[key].valor) || 0;
            }
            return 0;
        },

        obtenerInterpretacion(pi) {
            const detectado  = this.obtenerResultadoDetectado(pi);
            const cargaViral = this.obtenerCargaViral(pi);
            const umbral     = parseFloat((this.patogenos[pi] && this.patogenos[pi].umbral_valor) || 10);
            if (detectado === 'NO DETECTADO (-)' || detectado === '' || !detectado) return 'no_detectado';
            if (cargaViral < umbral) return 'regresivo';
            return 'progresivo';
        },

        // ─── Gráfica por patógeno (mismo código que carga-viral) ─────────────

        actualizarGrafica(pi) {
            const patogeno = this.patogenos[pi];
            if (!patogeno || !patogeno.mostrar_grafica) return;
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js no está cargado. La gráfica no se puede mostrar.');
                return;
            }

            const canvas = this.$refs['grafica_' + pi];
            if (!canvas) return;

            const cargaViral = this.obtenerCargaViral(pi);
            const detectado  = this.obtenerResultadoDetectado(pi);
            const umbralValor    = parseFloat(patogeno.umbral_valor) || 10;
            const umbralExponente = patogeno.umbral_exponente || 5;
            const unidad    = patogeno.unidad || 'copias/ml';
            const siglas    = patogeno.siglas || 'Patógeno';

            // Destruir gráfica anterior
            const existingChart = Chart.getChart(canvas);
            if (existingChart) existingChart.destroy();
            if (this.chartInstances[pi]) {
                delete this.chartInstances[pi];
            }

            // Si no hay resultado, no mostrar gráfica
            if (detectado === 'NO DETECTADO (-)' || detectado === '' || cargaViral <= 0) {
                this.chartInstances[pi] = null;
                return;
            }

            const ctx = canvas.getContext('2d');
            const maxEscala = umbralValor * 2;

            const zonasPlugin = {
                id: 'zonasPlugin_' + pi,
                beforeDatasetsDraw: (chart) => {
                    const c = chart.ctx;
                    const chartArea = chart.chartArea;
                    const xScale = chart.scales.x;
                    const umbralPos = xScale.getPixelForValue(umbralValor);

                    // Zona regresiva (verde)
                    c.fillStyle = 'rgba(34, 197, 94, 0.25)';
                    c.fillRect(chartArea.left, chartArea.top, umbralPos - chartArea.left, chartArea.bottom - chartArea.top);

                    // Zona progresiva (roja)
                    c.fillStyle = 'rgba(239, 68, 68, 0.25)';
                    c.fillRect(umbralPos, chartArea.top, chartArea.right - umbralPos, chartArea.bottom - chartArea.top);

                    // Línea vertical del umbral
                    c.beginPath();
                    c.strokeStyle = 'rgb(59, 130, 246)';
                    c.lineWidth = 3;
                    c.setLineDash([8, 4]);
                    c.moveTo(umbralPos, chartArea.top);
                    c.lineTo(umbralPos, chartArea.bottom);
                    c.stroke();
                    c.setLineDash([]);

                    // Etiqueta del umbral
                    c.fillStyle = 'rgb(59, 130, 246)';
                    c.font = 'bold 11px Arial';
                    c.textAlign = 'center';
                    c.fillText('Umbral: ' + umbralValor, umbralPos, chartArea.top - 8);
                }
            };

            this.chartInstances[pi] = new Chart(ctx, {
                type: 'scatter',
                plugins: [zonasPlugin],
                data: {
                    datasets: [{
                        label: 'Paciente',
                        data: [{ x: cargaViral, y: 0.7 }],
                        backgroundColor: cargaViral >= umbralValor ? 'rgb(239, 68, 68)' : 'rgb(234, 179, 8)',
                        borderColor:     cargaViral >= umbralValor ? 'rgb(185, 28, 28)'  : 'rgb(161, 98, 7)',
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
                    layout: { padding: { top: 25 } },
                    plugins: {
                        legend: { display: false },
                        title: {
                            display: true,
                            text: siglas + ' - Posición del Paciente Respecto al Umbral',
                            font: { size: 14, weight: 'bold' },
                            padding: { bottom: 20 }
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => 'Carga viral: ' + context.raw.x + ' × 10^' + umbralExponente + ' ' + unidad
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
                                text: 'Carga viral (×10^' + umbralExponente + ' ' + unidad + ')',
                                font: { size: 12 }
                            },
                            grid: { color: 'rgba(0, 0, 0, 0.1)' },
                            ticks: {
                                stepSize: umbralValor / 2,
                                callback: function(value) { return value; }
                            }
                        },
                        y: { min: 0, max: 1, display: false }
                    }
                }
            });

            // Guardar gráfica en servidor
            setTimeout(() => { this.guardarGraficaEnServidor(pi); }, 500);
        },

        async guardarGraficaEnServidor(pi) {
            if (!this.chartInstances[pi]) return;
            try {
                const image = this.chartInstances[pi].toBase64Image();
                await fetch(`/analisis/${this.analisisId}/guardar-grafica-panel`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ image, component_index: this.index, patogeno_index: pi })
                });
            } catch (e) { console.warn('No se pudo guardar la gráfica del panel:', e); }
        }
    }"
    class="border border-gray-200 dark:border-zinc-700 rounded-lg overflow-hidden bg-white dark:bg-zinc-900"
>

    @if(!empty($patogenos))
        @foreach($patogenos as $pi => $patogeno)
        @php
            $camposP        = $patogeno['campos']           ?? [];
            $interp         = $patogeno['interpretaciones'] ?? [];
            $umbralValor    = $patogeno['umbral_valor']     ?? 10;
            $umbralExp      = $patogeno['umbral_exponente'] ?? 5;
            $unidad         = $patogeno['unidad']           ?? 'copias/ml';
            $mostrarGrafica = $patogeno['mostrar_grafica']  ?? true;
        @endphp

        {{-- Separador entre patógenos --}}
        @if($pi > 0)
        <div class="border-t-2 border-dashed border-gray-300 dark:border-zinc-600"></div>
        @endif

        {{-- Título del patógeno --}}
        @if(isset($patogeno['titulo']))
        <div class="bg-gray-50 dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700 py-3 px-4">
            <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-lg uppercase">
                {{ $patogeno['titulo'] }}
            </h4>
            @if(!empty($patogeno['nombre_completo']))
            <p class="text-gray-600 dark:text-zinc-400 text-center text-sm mt-1">{{ $patogeno['nombre_completo'] }}</p>
            @endif
        </div>
        @endif

        <div class="p-4 space-y-6">

            {{-- Campos editables --}}
            <div class="space-y-4">
                @foreach($camposP as $ci => $campo)
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
                                x-model="valores[{{ $pi }}][{{ $ci }}].valor"
                                @change="sincronizarConLivewire(); actualizarGrafica({{ $pi }});"
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
                                        x-model="valores[{{ $pi }}][{{ $ci }}].valor"
                                        @change="sincronizarConLivewire(); actualizarGrafica({{ $pi }});"
                                        @blur="sincronizarConLivewire(); actualizarGrafica({{ $pi }});"
                                        placeholder="0.00"
                                        class="flex-1 min-w-0 border-0 p-0 focus:ring-0 bg-transparent text-gray-900 dark:text-zinc-100 text-center font-medium"
                                    />
                                    <span class="text-gray-600 dark:text-zinc-400 text-sm ml-2 whitespace-nowrap">
                                        × 10<sup>{{ $umbralExp }}</sup> {{ $unidad }}
                                    </span>
                                </div>
                            </div>
                        @else
                            {{-- Texto libre --}}
                            <input
                                type="text"
                                x-model="valores[{{ $pi }}][{{ $ci }}].valor"
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
                        :class="obtenerInterpretacion({{ $pi }}) === 'no_detectado' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-300 dark:border-zinc-600 opacity-50'"
                        :style="obtenerInterpretacion({{ $pi }}) === 'no_detectado' ? 'border-left-color: rgb(34, 197, 94)' : 'border-left-color: rgb(209, 213, 219)'"
                    >
                        <p class="font-bold text-xs leading-tight" :class="obtenerInterpretacion({{ $pi }}) === 'no_detectado' ? 'text-green-700 dark:text-green-400' : 'text-gray-500 dark:text-zinc-500'">
                            {{ $patogeno['siglas'] ?? 'PATÓGENO' }} NO DETECTADO:
                        </p>
                        <p class="text-[10px] leading-tight mt-1" :class="obtenerInterpretacion({{ $pi }}) === 'no_detectado' ? 'text-green-600 dark:text-green-300' : 'text-gray-400 dark:text-zinc-600'">
                            {{ $interp['no_detectado']['descripcion'] ?? 'Sin detección de ADN viral en la muestra.' }}
                        </p>
                    </div>

                    {{-- Infección Regresiva --}}
                    <div
                        class="pl-2 py-2 rounded-r transition-all"
                        style="border-left-width: 3px;"
                        :class="obtenerInterpretacion({{ $pi }}) === 'regresivo' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-300 dark:border-zinc-600 opacity-50'"
                        :style="obtenerInterpretacion({{ $pi }}) === 'regresivo' ? 'border-left-color: rgb(234, 179, 8)' : 'border-left-color: rgb(209, 213, 219)'"
                    >
                        <p class="font-bold text-xs leading-tight" :class="obtenerInterpretacion({{ $pi }}) === 'regresivo' ? 'text-yellow-700 dark:text-yellow-400' : 'text-gray-500 dark:text-zinc-500'">
                            {{ $patogeno['siglas'] ?? 'PATÓGENO' }} - REGRESIVA:
                        </p>
                        <p class="text-[10px] leading-tight mt-1" :class="obtenerInterpretacion({{ $pi }}) === 'regresivo' ? 'text-yellow-600 dark:text-yellow-300' : 'text-gray-400 dark:text-zinc-600'">
                            {{ $interp['regresivo']['descripcion'] ?? 'Carga viral baja, fase de resolución.' }}
                        </p>
                        <p class="text-[9px] mt-1" :class="obtenerInterpretacion({{ $pi }}) === 'regresivo' ? 'text-yellow-500 dark:text-yellow-400' : 'text-gray-400 dark:text-zinc-600'">
                            ADN &lt; {{ $umbralValor }} × 10<sup>{{ $umbralExp }}</sup>
                        </p>
                    </div>

                    {{-- Infección Progresiva --}}
                    <div
                        class="pl-2 py-2 rounded-r transition-all"
                        style="border-left-width: 3px;"
                        :class="obtenerInterpretacion({{ $pi }}) === 'progresivo' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-zinc-600 opacity-50'"
                        :style="obtenerInterpretacion({{ $pi }}) === 'progresivo' ? 'border-left-color: rgb(239, 68, 68)' : 'border-left-color: rgb(209, 213, 219)'"
                    >
                        <p class="font-bold text-xs leading-tight" :class="obtenerInterpretacion({{ $pi }}) === 'progresivo' ? 'text-red-700 dark:text-red-400' : 'text-gray-500 dark:text-zinc-500'">
                            {{ $patogeno['siglas'] ?? 'PATÓGENO' }} - PROGRESIVA:
                        </p>
                        <p class="text-[10px] leading-tight mt-1" :class="obtenerInterpretacion({{ $pi }}) === 'progresivo' ? 'text-red-600 dark:text-red-300' : 'text-gray-400 dark:text-zinc-600'">
                            {{ $interp['progresivo']['descripcion'] ?? 'Carga viral alta, infección activa.' }}
                        </p>
                        <p class="text-[9px] mt-1" :class="obtenerInterpretacion({{ $pi }}) === 'progresivo' ? 'text-red-500 dark:text-red-400' : 'text-gray-400 dark:text-zinc-600'">
                            ADN &gt; {{ $umbralValor }} × 10<sup>{{ $umbralExp }}</sup>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Gráfica del patógeno --}}
            @if($mostrarGrafica)
            <div class="border border-gray-300 dark:border-zinc-700 rounded-lg overflow-hidden">
                <div class="bg-gray-100 dark:bg-zinc-800 px-4 py-2 border-b border-gray-300 dark:border-zinc-700 flex items-center justify-between">
                    <h5 class="font-bold text-gray-800 dark:text-zinc-100 text-sm">
                        {{ $patogeno['siglas'] ?? 'Patógeno' }} - Posición del Paciente Respecto al Umbral
                    </h5>
                    <button
                        @click="actualizarGrafica({{ $pi }})"
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
                            <span class="text-gray-600 dark:text-zinc-400">Umbral ({{ $umbralValor }} × 10<sup>{{ $umbralExp }}</sup>)</span>
                        </div>
                    </div>

                    {{-- Canvas de la gráfica --}}
                    <div class="h-32 relative">
                        <canvas x-ref="grafica_{{ $pi }}"></canvas>
                    </div>
                </div>
            </div>
            @endif

        </div>{{-- fin p-4 --}}

        @endforeach
    @else
    <div class="p-6 text-center text-gray-400 dark:text-zinc-500 text-sm italic">
        Este panel no tiene patógenos configurados. Edita la plantilla para agregar patógenos.
    </div>
    @endif

    {{-- Repeticiones/Reactivos --}}
    @include('livewire.resultados.componentes-edicion._repeticiones-reactivos', [
        'componente' => $componente,
        'index'      => $index,
    ])
</div>

{{-- Cargar Chart.js si no está cargado --}}
@once
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@endonce
