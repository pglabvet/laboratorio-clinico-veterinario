{{-- Componente de edición: Tabla Hematológica --}}
@php
    // Determinar índices de parámetros clave por nombre
    $indiceLeucocitos = null;
    $indiceHematocrito = null;
    $indiceEritrocitos = null;
    $indiceHemoglobina = null;
    foreach (($componente['propiedades']['parametros_principales'] ?? []) as $idx => $p) {
        $nombreLower = strtolower($p['nombre'] ?? '');
        if (str_contains($nombreLower, 'leucocito')) $indiceLeucocitos = $idx;
        if (str_contains($nombreLower, 'hematocrito')) $indiceHematocrito = $idx;
        if (str_contains($nombreLower, 'eritrocito')) $indiceEritrocitos = $idx;
        if (str_contains($nombreLower, 'hemoglobina')) $indiceHemoglobina = $idx;
    }

    // Determinar índices de los índices eritrocitarios calculados
    $indiceVCM = null;
    $indiceHbCM = null;
    $indiceCCMHb = null;
    foreach (($componente['propiedades']['indices'] ?? []) as $idx => $ind) {
        $nombreUpper = strtoupper(trim($ind['nombre'] ?? ''));
        if (str_contains($nombreUpper, 'VCM') && !str_contains($nombreUpper, 'CCMHB')) $indiceVCM = $idx;
        if (str_contains($nombreUpper, 'HBCM')) $indiceHbCM = $idx;
        if (str_contains($nombreUpper, 'CCMHB') || str_contains($nombreUpper, 'CCMH')) $indiceCCMHb = $idx;
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
        indiceHematocrito: {{ $indiceHematocrito !== null ? $indiceHematocrito : 'null' }},
        indiceEritrocitos: {{ $indiceEritrocitos !== null ? $indiceEritrocitos : 'null' }},
        indiceHemoglobina: {{ $indiceHemoglobina !== null ? $indiceHemoglobina : 'null' }},
        indiceVCM: {{ $indiceVCM !== null ? $indiceVCM : 'null' }},
        indiceHbCM: {{ $indiceHbCM !== null ? $indiceHbCM : 'null' }},
        indiceCCMHb: {{ $indiceCCMHb !== null ? $indiceCCMHb : 'null' }},
        umbralPorcentaje: {{ config('labvet.umbral_resultado') }},
        parametros: @js(collect($componente['propiedades']['parametros_principales'] ?? [])->mapWithKeys(fn($p, $i) => [$i => ['nombre' => $p['nombre'], 'resultado' => '', 'unidad' => $p['unidad'], 'rango_tipo' => $p['rango_tipo'] ?? 'min-max', 'rango_min' => $p['rango_min'] ?? '', 'rango_max' => $p['rango_max'] ?? '', 'rango_valor' => $p['rango_valor'] ?? '']])),
        diferenciales: @js(collect($componente['propiedades']['diferenciales'] ?? [])->mapWithKeys(fn($d, $i) => [$i => ['nombre' => $d['nombre'], 'valor_rel' => '', 'valor_abs' => '', 'rango_rel_tipo' => $d['rango_rel_tipo'] ?? 'min-max', 'rango_rel_min' => $d['rango_rel_min'] ?? '', 'rango_rel_max' => $d['rango_rel_max'] ?? '', 'rango_rel_valor' => $d['rango_rel_valor'] ?? '', 'rango_abs_tipo' => $d['rango_abs_tipo'] ?? 'min-max', 'rango_abs_min' => $d['rango_abs_min'] ?? '', 'rango_abs_max' => $d['rango_abs_max'] ?? '', 'rango_abs_valor' => $d['rango_abs_valor'] ?? '']])),
        indices: @js(collect($componente['propiedades']['indices'] ?? [])->mapWithKeys(fn($ind, $i) => [$i => ['nombre' => $ind['nombre'], 'resultado' => '', 'unidad' => $ind['unidad'], 'rango_tipo' => $ind['rango_tipo'] ?? 'min-max', 'rango_min' => $ind['rango_min'] ?? '', 'rango_max' => $ind['rango_max'] ?? '', 'rango_valor' => $ind['rango_valor'] ?? '']])),
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
        getHematocrito() {
            if (this.indiceHematocrito === null) return 0;
            const val = this.parametros[this.indiceHematocrito]?.resultado;
            if (!val || val === '') return 0;
            return parseFloat(String(val).replace(/,/g, '')) || 0;
        },
        calcularDesdeHematocrito() {
            const hto = this.getHematocrito();
            if (hto > 0) {
                // Valor crudo para Eritrocitos (sin redondeo intermedio)
                const eritrocitosRaw = (hto * 10) / 47;
                // Valores intermedios con 1 decimal (solo para VCM, HbCM, CCMHb)
                const eritrocitosBase = parseFloat(eritrocitosRaw.toFixed(1));
                const hemoglobinaBase = parseFloat((hto / 3).toFixed(1));

                // Eritrocitos = ((hto * 10) / 47) * 1,000,000 (entero, formateado con puntos)
                if (this.indiceEritrocitos !== null) {
                    const eritrocitos = Math.round(eritrocitosRaw * 1000000);
                    this.parametros[this.indiceEritrocitos].resultado = eritrocitos.toString();
                }

                // Hemoglobina = hematocrito / 3 (1 decimal)
                if (this.indiceHemoglobina !== null) {
                    this.parametros[this.indiceHemoglobina].resultado = hemoglobinaBase.toFixed(1);
                }

                // VCM = (hematocrito / eritrocitosBase) * 10
                if (this.indiceVCM !== null) {
                    const vcm = (hto / eritrocitosBase) * 10;
                    this.indices[this.indiceVCM].resultado = vcm.toFixed(1);
                }

                // HbCM = (hemoglobinaBase / eritrocitosBase) * 10
                if (this.indiceHbCM !== null) {
                    const hbcm = (hemoglobinaBase / eritrocitosBase) * 10;
                    this.indices[this.indiceHbCM].resultado = hbcm.toFixed(1);
                }

                // CCMHb = (hemoglobinaBase / hematocrito) * 100
                if (this.indiceCCMHb !== null) {
                    const ccmhb = (hemoglobinaBase / hto) * 100;
                    this.indices[this.indiceCCMHb].resultado = ccmhb.toFixed(1);
                }
            } else {
                // Limpiar campos calculados si hematocrito está vacío
                if (this.indiceEritrocitos !== null) this.parametros[this.indiceEritrocitos].resultado = '';
                if (this.indiceHemoglobina !== null) this.parametros[this.indiceHemoglobina].resultado = '';
                if (this.indiceVCM !== null) this.indices[this.indiceVCM].resultado = '';
                if (this.indiceHbCM !== null) this.indices[this.indiceHbCM].resultado = '';
                if (this.indiceCCMHb !== null) this.indices[this.indiceCCMHb].resultado = '';
            }
        },
        esParametroCalculado(idx) {
            return idx === this.indiceEritrocitos || idx === this.indiceHemoglobina;
        },
        esIndiceCalculado(idx) {
            return idx === this.indiceVCM || idx === this.indiceHbCM || idx === this.indiceCCMHb;
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
                            this.parametros[match].resultado = param.resultado ?? '';
                        }
                    });
                }

                let difs = this.datosExistentes.diferenciales;
                if (difs && !Array.isArray(difs)) difs = Object.values(difs);
                if (Array.isArray(difs)) {
                    difs.forEach(dif => {
                        const match = Object.keys(this.diferenciales).find(k => this.diferenciales[k].nombre === dif.nombre);
                        if (match !== undefined) {
                            this.diferenciales[match].valor_rel = dif.valor_rel ?? '';
                        }
                    });
                }

                let inds = this.datosExistentes.indices;
                if (inds && !Array.isArray(inds)) inds = Object.values(inds);
                if (Array.isArray(inds)) {
                    inds.forEach(ind => {
                        const match = Object.keys(this.indices).find(k => this.indices[k].nombre === ind.nombre);
                        if (match !== undefined) {
                            this.indices[match].resultado = ind.resultado ?? '';
                        }
                    });
                }
            }

            // Calcular valores absolutos e índices iniciales
            this.calcularValoresAbsolutos();
            this.calcularDesdeHematocrito();

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
            this.calcularDesdeHematocrito();
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
        },
        clasificarConRango(res, rangoTipo, rangoMin, rangoMax, rangoValor) {
            if (isNaN(res)) return 'normal';
            const tipo = rangoTipo || 'min-max';
            if (tipo === 'min-max') {
                const min = parseFloat(rangoMin);
                const max = parseFloat(rangoMax);
                if (isNaN(min) && isNaN(max)) return 'normal';
                if (!isNaN(min) && res < min) return 'bajo';
                if (!isNaN(max) && res > max) return 'alto';
                return 'normal';
            }
            const val = parseFloat(rangoValor);
            if (isNaN(val)) return 'normal';
            if (tipo === 'menor' && res >= val) return 'alto';
            if (tipo === 'menor-igual' && res > val) return 'alto';
            if (tipo === 'mayor' && res <= val) return 'bajo';
            if (tipo === 'mayor-igual' && res < val) return 'bajo';
            return 'normal';
        },
        claseParametro(idx) {
            const p = this.parametros[idx];
            if (!p || !p.resultado) return 'text-gray-900 dark:text-zinc-100';
            // Limpiar separadores de miles (puntos) para parsear correctamente (ej: 4.900.000 → 4900000)
            const valorLimpio = parseFloat(String(p.resultado).replace(/\./g, '').replace(/,/g, '.')) || 0;
            const c = this.clasificarConRango(valorLimpio, p.rango_tipo, p.rango_min, p.rango_max, p.rango_valor);
            if (c === 'bajo') return 'text-blue-600 dark:text-blue-400 font-bold';
            if (c === 'alto') return 'text-red-600 dark:text-red-400 font-bold';
            return 'text-gray-900 dark:text-zinc-100';
        },
        claseDiferencialRel(idx) {
            const d = this.diferenciales[idx];
            if (!d || !d.valor_rel) return 'text-gray-900 dark:text-zinc-100';
            const c = this.clasificarConRango(parseFloat(d.valor_rel), d.rango_rel_tipo, d.rango_rel_min, d.rango_rel_max, d.rango_rel_valor);
            if (c === 'bajo') return 'text-blue-600 dark:text-blue-400 font-bold';
            if (c === 'alto') return 'text-red-600 dark:text-red-400 font-bold';
            return 'text-gray-900 dark:text-zinc-100';
        },
        claseDiferencialAbs(idx) {
            const d = this.diferenciales[idx];
            if (!d || !d.valor_abs) return 'text-gray-900 dark:text-zinc-100';
            const c = this.clasificarConRango(parseFloat(d.valor_abs), d.rango_abs_tipo, d.rango_abs_min, d.rango_abs_max, d.rango_abs_valor);
            if (c === 'bajo') return 'text-blue-600 dark:text-blue-400 font-bold';
            if (c === 'alto') return 'text-red-600 dark:text-red-400 font-bold';
            return 'text-gray-900 dark:text-zinc-100';
        },
        claseIndice(idx) {
            const ind = this.indices[idx];
            if (!ind || !ind.resultado) return 'text-gray-900 dark:text-zinc-100';
            const c = this.clasificarConRango(parseFloat(ind.resultado), ind.rango_tipo, ind.rango_min, ind.rango_max, ind.rango_valor);
            if (c === 'bajo') return 'text-blue-600 dark:text-blue-400 font-bold';
            if (c === 'alto') return 'text-red-600 dark:text-red-400 font-bold';
            return 'text-gray-900 dark:text-zinc-100';
        }
    }"
    class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    <div class="overflow-x-auto">
        <table class="w-full border border-gray-300 dark:border-zinc-700 text-xs">
            <thead>
                <tr>
                    <th colspan="3" class="border border-gray-300 dark:border-zinc-700 px-2 py-2 font-bold text-gray-900 dark:text-zinc-100">
                        {{ $componente['propiedades']['titulo'] ?? 'CUADRO HEMÁTICO' }}
                    </th>
                    <th colspan="5" class="border border-gray-300 dark:border-zinc-700 px-2 py-2"></th>
                </tr>
                <tr class="bg-gray-100 dark:bg-zinc-900 text-center text-xs">
                    <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100">CUADRO<br>HEMÁTICO</th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100">RESULTADO</th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100">VALORES DE REF</th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1"></th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100">VALOR REL</th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100">Val ref</th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100">VALOR ABS</th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100">Val ref</th>
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
                            <template x-if="esParametroCalculado({{ $i }})">
                                <span x-text="parametros[{{ $i }}].resultado" :class="claseParametro({{ $i }})" class="block w-full px-1 py-0.5 text-xs text-center"></span>
                            </template>
                            <template x-if="!esParametroCalculado({{ $i }})">
                                <input type="text" x-model="parametros[{{ $i }}].resultado" @change="onParametroChange()" @blur="onParametroChange()" :class="claseParametro({{ $i }})" placeholder="Ingresar" class="w-full px-1 py-0.5 text-xs text-center border-0 focus:ring-1 focus:ring-blue-500 rounded bg-blue-50 dark:bg-blue-900/30 border-l-2 border-l-blue-400 dark:border-l-blue-500" />
                            </template>
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-500 dark:text-zinc-400">
                            {{ $generarTextoRango($param) }}@if($param['unidad']) <span class="text-gray-900 dark:text-zinc-100">{{ $param['unidad'] }}</span>@endif
                        </td>
                    @else
                        <td class="border border-gray-300 dark:border-zinc-700" colspan="3"></td>
                    @endif
                    
                    {{-- Diferenciales (Lado Derecho) --}}
                    @if($i < count($componente['propiedades']['diferenciales'] ?? []))
                        @php $dif = $componente['propiedades']['diferenciales'][$i]; @endphp
                        <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 font-semibold text-xs text-gray-900 dark:text-zinc-100">
                            {{ $dif['nombre'] }}
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1">
                            <input type="text" x-model="diferenciales[{{ $i }}].valor_rel" @change="onValorRelChange()" @blur="onValorRelChange()" :class="claseDiferencialRel({{ $i }})" placeholder="Ingresar" class="w-full px-1 py-0.5 text-xs text-center border-0 focus:ring-1 focus:ring-blue-500 rounded bg-blue-50 dark:bg-blue-900/30 border-l-2 border-l-blue-400 dark:border-l-blue-500" />
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-500 dark:text-zinc-400">
                            {{ $generarTextoRango($dif, 'rel_') }} <span class="text-gray-900 dark:text-zinc-100">%</span>
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1">
                            <span x-text="diferenciales[{{ $i }}].valor_abs" :class="claseDiferencialAbs({{ $i }})" class="block w-full px-1 py-0.5 text-xs text-center"></span>
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-500 dark:text-zinc-400">
                            {{ $generarTextoRango($dif, 'abs_') }} <span class="text-gray-900 dark:text-zinc-100">mm³</span>
                        </td>
                    @else
                        <td class="border border-gray-300 dark:border-zinc-700" colspan="5"></td>
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
                        <template x-if="esIndiceCalculado({{ $index }})">
                            <span x-text="indices[{{ $index }}].resultado" :class="claseIndice({{ $index }})" class="block w-full px-1 py-0.5 text-xs text-center"></span>
                        </template>
                        <template x-if="!esIndiceCalculado({{ $index }})">
                            <input type="text" x-model="indices[{{ $index }}].resultado" @change="sincronizarConLivewire()" @blur="sincronizarConLivewire()" :class="claseIndice({{ $index }})" class="w-full px-1 py-0.5 text-xs border-0 focus:ring-1 focus:ring-blue-500 rounded bg-transparent" />
                        </template>
                    </td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-xs text-gray-500 dark:text-zinc-400">
                        {{ $generarTextoRango($indice) ?: ($indice['referencia'] ?? '') }}@if($indice['unidad']) <span class="text-gray-900 dark:text-zinc-100">{{ $indice['unidad'] }}</span>@endif
                    </td>
                    <td class="border border-gray-300 dark:border-zinc-700" colspan="5"></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Ayuda visual --}}
    <div class="mt-3 p-2 bg-gray-50 dark:bg-zinc-800 rounded text-xs text-gray-600 dark:text-zinc-400">
        <i class="fas fa-info-circle mr-1"></i>
        Los campos resaltados en azul son los que debe completar.
    </div>
</div>
