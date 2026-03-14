{{-- Componente de edición: Tabla de Resultados --}}
@php
    $filas = array_values($componente['propiedades']['filas'] ?? []);
    $columnas = array_values($componente['propiedades']['columnas'] ?? []);

    $col1Displays = [];
    foreach ($filas as $idx => $a) {
        if (!is_array($a)) { $col1Displays[$idx] = ''; continue; }
        $tipo = $a['rango_tipo'] ?? '';
        if ($tipo === 'min-max') {
            $min = $a['rango_min'] ?? ''; $max = $a['rango_max'] ?? '';
            $col1Displays[$idx] = ($min !== '' && $max !== '') ? "$min - $max" : '';
        } elseif ($tipo === 'menor') {
            $v = $a['rango_valor'] ?? ''; $col1Displays[$idx] = $v !== '' ? "< $v" : '';
        } elseif ($tipo === 'menor-igual') {
            $v = $a['rango_valor'] ?? ''; $col1Displays[$idx] = $v !== '' ? "<= $v" : '';
        } elseif ($tipo === 'mayor') {
            $v = $a['rango_valor'] ?? ''; $col1Displays[$idx] = $v !== '' ? "> $v" : '';
        } elseif ($tipo === 'mayor-igual') {
            $v = $a['rango_valor'] ?? ''; $col1Displays[$idx] = $v !== '' ? ">= $v" : '';
        } elseif ($tipo === 'multiple') {
            $rangos = $a['rangos'] ?? [];
            $unidadR = $a['unidad'] ?? '';
            if (!empty($rangos)) {
                $lines = [];
                foreach ($rangos as $re) {
                    $t = $re['tipo'] ?? 'min-max';
                    $str = match($t) {
                        'min-max' => ($re['min'] ?? '') . ' - ' . ($re['max'] ?? ''),
                        'menor' => '< ' . ($re['valor'] ?? ''),
                        'menor-igual' => '<= ' . ($re['valor'] ?? ''),
                        'mayor' => '> ' . ($re['valor'] ?? ''),
                        'mayor-igual' => '>= ' . ($re['valor'] ?? ''),
                        default => '',
                    };
                    $parts = array_filter([$str, $unidadR, $re['etiqueta'] ?? '']);
                    $lines[] = implode(' ', $parts);
                }
                $col1Displays[$idx] = implode("\n", $lines);
            } else {
                $col1Displays[$idx] = $a['rango_ref'] ?? '';
            }
        } else {
            $col1Displays[$idx] = $a['rango_ref'] ?? '';
        }
    }
@endphp

<div 
    wire:ignore
    x-data="{
        filas: @js($filas),
        columnas: @js($columnas),
        datosExistentes: @js($componentesData[$index]['data'] ?? []),
        datos: @js(collect($filas)->mapWithKeys(fn($analisis, $rowIndex) => [$rowIndex => [
            'nombre' => is_array($analisis) ? ($analisis['nombre'] ?? '') : $analisis,
            'col_0' => '',
            'col_1' => $col1Displays[$rowIndex] ?? '',
            'unidad' => is_array($analisis) ? ($analisis['unidad'] ?? '') : '',
        ]])),
        init() {
            // Cargar datos existentes si existen (sobrescribe los valores por defecto)
            if (Array.isArray(this.datosExistentes) && this.datosExistentes.length > 0) {
                this.datosExistentes.forEach((fila, filaIndex) => {
                    if (this.datos[filaIndex]) {
                        // Copiar todos los valores excepto 'nombre'
                        Object.keys(fila).forEach(key => {
                            if (key !== 'nombre') {
                                this.datos[filaIndex][key] = fila[key] || '';
                            }
                        });
                    }
                });
            }
            
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
        sincronizarConLivewire() {
            const data = Object.values(this.datos);
            window.__labvetData = window.__labvetData || {};
            window.__labvetData['{{ $index }}'] = data;
            $wire.set('componentesData.{{ $index }}.data', data);
        },
        clasificarResultado(rowIndex) {
            const resultado = this.datos[rowIndex]?.col_0;
            if (!resultado && resultado !== 0) return 'normal';
            const res = parseFloat(resultado);
            if (isNaN(res)) return 'normal';
            const fila = this.filas[rowIndex];
            if (!fila) return 'normal';
            const tipo = fila.rango_tipo || '';
            if (!tipo) return 'normal';
            if (tipo === 'multiple') {
                const rangos = fila.rangos || [];
                if (rangos.length === 0) return 'normal';
                for (const r of rangos) {
                    if (this.valorEnRango(res, r)) return r.es_normal ? 'normal' : 'alerta';
                }
                return 'critico';
            }
            return this.clasificarConRango(res, tipo, fila.rango_min, fila.rango_max, fila.rango_valor);
        },
        valorEnRango(res, rango) {
            const t = rango.tipo || 'min-max';
            if (t === 'min-max') {
                const min = parseFloat(rango.min); const max = parseFloat(rango.max);
                return (!isNaN(min) ? res >= min : true) && (!isNaN(max) ? res <= max : true);
            }
            const v = parseFloat(rango.valor);
            if (isNaN(v)) return false;
            if (t === 'menor') return res < v;
            if (t === 'menor-igual') return res <= v;
            if (t === 'mayor') return res > v;
            if (t === 'mayor-igual') return res >= v;
            return false;
        },
        clasificarConRango(res, tipo, rangoMin, rangoMax, rangoValor) {
            if (tipo === 'min-max') {
                const min = parseFloat(rangoMin); const max = parseFloat(rangoMax);
                if (isNaN(min) && isNaN(max)) return 'normal';
                const amplitud = (!isNaN(min) && !isNaN(max)) ? max - min : 0;
                const umbral = amplitud * 0.15;
                if (!isNaN(min) && res < min) return (amplitud > 0 && res >= min - umbral) ? 'alerta' : 'critico';
                if (!isNaN(max) && res > max) return (amplitud > 0 && res <= max + umbral) ? 'alerta' : 'critico';
                return 'normal';
            }
            const val = parseFloat(rangoValor);
            if (isNaN(val)) return 'normal';
            const umbral = Math.abs(val) * 0.15;
            if (tipo === 'menor' && res >= val) return res <= val + umbral ? 'alerta' : 'critico';
            if (tipo === 'menor-igual' && res > val) return res <= val + umbral ? 'alerta' : 'critico';
            if (tipo === 'mayor' && res <= val) return res >= val - umbral ? 'alerta' : 'critico';
            if (tipo === 'mayor-igual' && res < val) return res >= val - umbral ? 'alerta' : 'critico';
            return 'normal';
        },
        claseResultado(rowIndex) {
            const c = this.clasificarResultado(rowIndex);
            if (c === 'alerta') return 'text-blue-600 dark:text-blue-400 font-bold';
            if (c === 'critico') return 'text-red-600 dark:text-red-400 font-bold';
            return 'text-gray-900 dark:text-zinc-100';
        }
    }"
    class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    {{-- Título del componente --}}
    @if(isset($componente['propiedades']['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-lg mb-2">
        {{ $componente['propiedades']['titulo'] }}
    </h4>
    @endif
    
    @if(isset($componente['propiedades']['descripcion']) && $componente['propiedades']['descripcion'])
    <p class="text-sm text-gray-600 dark:text-zinc-400 text-center italic mb-4">
        {{ $componente['propiedades']['descripcion'] }}
    </p>
    @endif

    {{-- Tabla editable --}}
    <div class="overflow-x-auto">
        <table class="w-full border border-gray-300 dark:border-zinc-700 text-sm">
            <thead>
                <tr class="bg-gray-100 dark:bg-zinc-900">
                    @foreach($componente['propiedades']['columnas'] ?? [] as $columna)
                    <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold text-gray-900 dark:text-zinc-100">
                        {{ $columna['nombre'] }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($componente['propiedades']['filas'] ?? [] as $rowIndex => $analisis)
                <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800">
                    {{-- Primera columna: Nombre del análisis (no editable) --}}
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-medium text-gray-900 dark:text-zinc-100 bg-gray-50 dark:bg-zinc-900">
                        {{ is_array($analisis) ? ($analisis['nombre'] ?? '') : $analisis }}
                    </td>
                    
                    {{-- Columnas dinámicas (según columnas definidas, excepto la primera) --}}
                    @foreach(array_slice($componente['propiedades']['columnas'] ?? [], 1) as $colIndex => $columna)
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-2">
                        @if($colIndex === 0)
                            {{-- Primera columna después del nombre: RESULTADO (editable) --}}
                            <input 
                                type="text"
                                x-model="datos[{{ $rowIndex }}]['col_{{ $colIndex }}']"
                                @change="sincronizarConLivewire()"
                                @blur="sincronizarConLivewire()"
                                placeholder="..."
                                :class="claseResultado({{ $rowIndex }})"
                                class="w-full px-2 py-1 border-0 focus:ring-2 focus:ring-blue-500 rounded bg-transparent placeholder-gray-400 dark:placeholder-zinc-500"
                            />
                        @else
                            {{-- Segunda columna y siguientes: RANGOS DE REFERENCIA (solo lectura - texto estático) --}}
                            @php
                                $col1Val = $col1Displays[$rowIndex] ?? '';
                                $unidadRef = is_array($analisis) ? ($analisis['unidad'] ?? '') : '';
                                $rangosRefEdit = is_string($col1Val) ? array_filter(explode("\n", $col1Val), fn($r) => trim($r) !== '') : [];
                                $esSingleRef = count($rangosRefEdit) <= 1;
                                $singleDisplay = $esSingleRef && count($rangosRefEdit) === 1 ? trim($rangosRefEdit[0]) : '';
                            @endphp
                            <div class="px-3 py-2 text-gray-600 dark:text-zinc-400 text-center">
                                @if(!$esSingleRef)
                                    <div class="inline-block text-left">
                                    @foreach($rangosRefEdit as $r)
                                        <div class="text-xs leading-relaxed">{{ trim($r) }}</div>
                                    @endforeach
                                    </div>
                                @elseif($singleDisplay)
                                    {{ $singleDisplay }}
                                    @if($unidadRef)
                                        <span class="text-gray-500 dark:text-zinc-500 ml-2">{{ $unidadRef }}</span>
                                    @endif
                                @else
                                    ...
                                @endif
                            </div>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Ayuda visual --}}
    <div class="mt-3 p-2 bg-blue-50 dark:bg-blue-900/20 rounded text-xs text-blue-800 dark:text-blue-300">
        <i class="fas fa-info-circle mr-1"></i>
        Complete los campos de resultado para cada análisis. Los rangos de referencia son solo informativos.
    </div>
</div>
