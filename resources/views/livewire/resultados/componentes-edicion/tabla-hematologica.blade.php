{{-- Componente de edición: Tabla Hematológica --}}
@php
    // Determinar el índice del parámetro "Leucocitos" en parametros_principales
    $indiceLeucocitos = null;
    foreach (($componente['propiedades']['parametros_principales'] ?? []) as $idx => $p) {
        if (str_contains(strtolower($p['nombre'] ?? ''), 'leucocito')) {
            $indiceLeucocitos = $idx;
            break;
        }
    }

    // Generar texto de rango desde datos estructurados (con fallback a campos antiguos)
    $generarTextoRango = function ($item, $infijo = '') {
        $tipo = $item['rango_' . $infijo . 'tipo'] ?? 'min-max';
        $min = $item['rango_' . $infijo . 'min'] ?? $item['ref_' . $infijo . 'min'] ?? '';
        $max = $item['rango_' . $infijo . 'max'] ?? $item['ref_' . $infijo . 'max'] ?? '';
        $valor = $item['rango_' . $infijo . 'valor'] ?? '';
        return match($tipo) {
            'min-max' => (!empty($min) || !empty($max)) ? $min . ' - ' . $max : '',
            'menor' => !empty($valor) ? '< ' . $valor : '',
            'menor-igual' => !empty($valor) ? '≤ ' . $valor : '',
            'mayor' => !empty($valor) ? '> ' . $valor : '',
            'mayor-igual' => !empty($valor) ? '≥ ' . $valor : '',
            default => '',
        };
    };
@endphp
<div 
    wire:ignore
    x-data="{
        datosExistentes: @js($componentesData[$index]['data'] ?? null),
        indiceLeucocitos: {{ $indiceLeucocitos !== null ? $indiceLeucocitos : 'null' }},
        parametros: @js(collect($componente['propiedades']['parametros_principales'] ?? [])->mapWithKeys(fn($p, $i) => [$i => ['nombre' => $p['nombre'], 'resultado' => '', 'unidad' => $p['unidad']]])),
        diferenciales: @js(collect($componente['propiedades']['diferenciales'] ?? [])->mapWithKeys(fn($d, $i) => [$i => ['nombre' => $d['nombre'], 'valor_rel' => '', 'valor_abs' => '']])),
        indices: @js(collect($componente['propiedades']['indices'] ?? [])->mapWithKeys(fn($ind, $i) => [$i => ['nombre' => $ind['nombre'], 'resultado' => '', 'unidad' => $ind['unidad']]])),
        getLeucocitos() {
            if (this.indiceLeucocitos === null) return 0;
            const val = this.parametros[this.indiceLeucocitos]?.resultado;
            if (!val || val === '') return 0;
            return parseFloat(String(val).replace(/,/g, '')) || 0;
        },
        calcularValoresAbsolutos() {
            const leucocitos = this.getLeucocitos();
            Object.keys(this.diferenciales).forEach(key => {
                const valorRel = parseFloat(this.diferenciales[key].valor_rel) || 0;
                if (leucocitos > 0 && valorRel > 0) {
                    this.diferenciales[key].valor_abs = Math.round(leucocitos * valorRel / 100).toString();
                } else if (valorRel === 0 && this.diferenciales[key].valor_rel !== '') {
                    this.diferenciales[key].valor_abs = '0';
                } else {
                    this.diferenciales[key].valor_abs = '';
                }
            });
        },
        init() {
            // Cargar datos existentes si existen
            if (this.datosExistentes && typeof this.datosExistentes === 'object') {
                // Convertir a array si es objeto (ocurre cuando PHP array_filter preserva keys no secuenciales)
                let params = this.datosExistentes.parametros;
                if (params && !Array.isArray(params)) params = Object.values(params);
                if (Array.isArray(params)) {
                    params.forEach(param => {
                        const match = Object.keys(this.parametros).find(k => this.parametros[k].nombre === param.nombre);
                        if (match !== undefined) {
                            this.parametros[match].resultado = param.resultado || '';
                        }
                    });
                }

                let difs = this.datosExistentes.diferenciales;
                if (difs && !Array.isArray(difs)) difs = Object.values(difs);
                if (Array.isArray(difs)) {
                    difs.forEach(dif => {
                        const match = Object.keys(this.diferenciales).find(k => this.diferenciales[k].nombre === dif.nombre);
                        if (match !== undefined) {
                            this.diferenciales[match].valor_rel = dif.valor_rel || '';
                        }
                    });
                }

                let inds = this.datosExistentes.indices;
                if (inds && !Array.isArray(inds)) inds = Object.values(inds);
                if (Array.isArray(inds)) {
                    inds.forEach(ind => {
                        const match = Object.keys(this.indices).find(k => this.indices[k].nombre === ind.nombre);
                        if (match !== undefined) {
                            this.indices[match].resultado = ind.resultado || '';
                        }
                    });
                }
            }

            // Calcular valores absolutos iniciales
            this.calcularValoresAbsolutos();

            // Observar cambios en leucocitos y valores relativos para recalcular
            this.$watch('parametros', () => {
                this.calcularValoresAbsolutos();
            }, { deep: true });

            this.$watch('diferenciales', (newVal, oldVal) => {
                // Solo recalcular si cambió un valor_rel (no valor_abs para evitar loop)
                let relChanged = false;
                Object.keys(newVal).forEach(key => {
                    if (newVal[key]?.valor_rel !== oldVal[key]?.valor_rel) {
                        relChanged = true;
                    }
                });
                if (relChanged) {
                    this.calcularValoresAbsolutos();
                }
            }, { deep: true });
            
            // Sincronizar antes de guardar
            window.addEventListener('antes-de-guardar', () => {
                this.sincronizarConLivewire();
            });

            window.addEventListener('livewire:initialized', () => {
                Livewire.hook('morph.updating', () => {
                    this.sincronizarConLivewire();
                });
            });
        },
        onParametroChange() {
            this.calcularValoresAbsolutos();
            this.sincronizarConLivewire();
        },
        onValorRelChange() {
            this.calcularValoresAbsolutos();
            this.sincronizarConLivewire();
        },
        sincronizarConLivewire() {
            const data = {
                parametros: Object.values(this.parametros),
                diferenciales: Object.values(this.diferenciales),
                indices: Object.values(this.indices)
            };
            window.__labvetData = window.__labvetData || {};
            window.__labvetData['{{ $index }}'] = data;
            $wire.set('componentesData.{{ $index }}.data', data);
        }
    }"
    class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    <div class="overflow-x-auto">
        <table class="w-full border border-gray-300 dark:border-zinc-700 text-xs">
            <thead>
                <tr>
                    <th colspan="5" class="border border-gray-300 dark:border-zinc-700 px-2 py-2 font-bold text-gray-900 dark:text-zinc-100">
                        {{ $componente['propiedades']['titulo'] ?? 'CUADRO HEMÁTICO' }}
                    </th>
                    <th colspan="7" class="border border-gray-300 dark:border-zinc-700 px-2 py-2"></th>
                </tr>
                <tr class="bg-gray-100 dark:bg-zinc-900 text-center text-xs">
                    <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100" rowspan="2">CUADRO<br>HEMÁTICO</th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1" colspan="2">RESULTADO</th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100" colspan="2">VALORES DE REF</th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1" rowspan="2"></th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100" colspan="2">VALOR REL</th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100" rowspan="2">Val ref</th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100" colspan="2">VALOR ABS</th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100" rowspan="2">Val ref</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $maxRows = max(
                        count($componente['propiedades']['parametros_principales'] ?? []), 
                        count($componente['propiedades']['diferenciales'] ?? [])
                    );
                @endphp
                
                @for($i = 0; $i < $maxRows; $i++)
                <tr>
                    {{-- Parámetros Principales (Lado Izquierdo) --}}
                    @if($i < count($componente['propiedades']['parametros_principales'] ?? []))
                        @php $param = $componente['propiedades']['parametros_principales'][$i]; @endphp
                        <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 font-semibold text-xs text-gray-900 dark:text-zinc-100">
                            {{ $param['nombre'] }}
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1">
                            <input type="text" x-model="parametros[{{ $i }}].resultado" @change="onParametroChange()" @blur="onParametroChange()" class="w-full px-1 py-0.5 text-xs border-0 focus:ring-1 focus:ring-blue-500 rounded bg-transparent text-gray-900 dark:text-zinc-100" />
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">
                            {{ $param['unidad'] }}
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-500 dark:text-zinc-400" colspan="2">
                            {{ $generarTextoRango($param) }}
                        </td>
                    @else
                        <td class="border border-gray-300 dark:border-zinc-700" colspan="5"></td>
                    @endif
                    
                    {{-- Diferenciales (Lado Derecho) --}}
                    @if($i < count($componente['propiedades']['diferenciales'] ?? []))
                        @php $dif = $componente['propiedades']['diferenciales'][$i]; @endphp
                        <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 font-semibold text-xs text-gray-900 dark:text-zinc-100">
                            {{ $dif['nombre'] }}
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1">
                            <input type="text" x-model="diferenciales[{{ $i }}].valor_rel" @change="onValorRelChange()" @blur="onValorRelChange()" class="w-full px-1 py-0.5 text-xs border-0 focus:ring-1 focus:ring-blue-500 rounded bg-transparent text-gray-900 dark:text-zinc-100" />
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">%</td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-500 dark:text-zinc-400">
                            {{ $generarTextoRango($dif, 'rel_') }}
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1">
                            <span x-text="diferenciales[{{ $i }}].valor_abs" class="block w-full px-1 py-0.5 text-xs text-center text-green-600 dark:text-green-400 font-semibold"></span>
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">mm³</td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-500 dark:text-zinc-400">
                            {{ $generarTextoRango($dif, 'abs_') }}
                        </td>
                    @else
                        <td class="border border-gray-300 dark:border-zinc-700" colspan="7"></td>
                    @endif
                </tr>
                @endfor
                
                {{-- Índices Eritrocitarios --}}
                @foreach(($componente['propiedades']['indices'] ?? []) as $index => $indice)
                <tr>
                    @if($index === 0)
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 font-semibold text-xs text-gray-900 dark:text-zinc-100" rowspan="{{ count($componente['propiedades']['indices'] ?? []) }}">
                        INDICES<br>ERITROCIT.
                    </td>
                    @endif
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">
                        {{ $indice['nombre'] }}
                    </td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1">
                        <input type="text" x-model="indices[{{ $index }}].resultado" @change="sincronizarConLivewire()" @blur="sincronizarConLivewire()" class="w-full px-1 py-0.5 text-xs border-0 focus:ring-1 focus:ring-blue-500 rounded bg-transparent text-gray-900 dark:text-zinc-100" />
                    </td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">
                        {{ $indice['unidad'] }}
                    </td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-xs text-gray-500 dark:text-zinc-400" colspan="2">
                        {{ $generarTextoRango($indice) ?: ($indice['referencia'] ?? '') }}
                    </td>
                    <td class="border border-gray-300 dark:border-zinc-700" colspan="7"></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Ayuda visual --}}
    <div class="mt-3 p-2 bg-gray-50 dark:bg-zinc-800 rounded text-xs text-gray-600 dark:text-zinc-400">
        <i class="fas fa-info-circle mr-1"></i>
        Complete los valores en cada campo. El <strong>valor absoluto</strong> se calcula automáticamente (Leucocitos × Valor Relativo / 100).
    </div>
</div>
