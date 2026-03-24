{{-- Componente de edición: Examen Diferencial --}}
@php
    $filas = $componente['propiedades']['filas'] ?? [];

    // Generar texto de rango desde datos estructurados
    $generarTextoRango = function ($fila) {
        $tipo = $fila['rango_tipo'] ?? 'min-max';
        $unidad = $fila['unidad'] ?? '';
        $sufijo = $unidad ? ' ' . $unidad : '';
        return match($tipo) {
            'min-max' => (!empty($fila['rango_min']) || !empty($fila['rango_max']))
                ? ($fila['rango_min'] ?? '') . ' - ' . ($fila['rango_max'] ?? '') . $sufijo
                : '',
            'menor' => !empty($fila['rango_valor']) ? '< ' . $fila['rango_valor'] . $sufijo : '',
            'menor-igual' => !empty($fila['rango_valor']) ? '<= ' . $fila['rango_valor'] . $sufijo : '',
            'mayor' => !empty($fila['rango_valor']) ? '> ' . $fila['rango_valor'] . $sufijo : '',
            'mayor-igual' => !empty($fila['rango_valor']) ? '>= ' . $fila['rango_valor'] . $sufijo : '',
            default => '',
        };
    };

    $tieneRangos = collect($filas)->contains(function ($f) use ($generarTextoRango) {
        return ($f['tipo_fila'] ?? '3col') === '3col' && $generarTextoRango($f) !== '';
    });
    
    // Preparar datos iniciales para Alpine
    $datosIniciales = [];
    foreach ($filas as $i => $fila) {
        if (!empty($fila['nombre'])) {
            $datosIniciales[$i] = [
                'nombre' => $fila['nombre'],
                'resultado' => '',
                'rango_tipo' => $fila['rango_tipo'] ?? 'min-max',
                'rango_min' => $fila['rango_min'] ?? '',
                'rango_max' => $fila['rango_max'] ?? '',
                'rango_valor' => $fila['rango_valor'] ?? '',
                'rango_display' => $generarTextoRango($fila),
                'tipo_fila' => $fila['tipo_fila'] ?? '3col',
                'opciones' => $fila['opciones'] ?? '',
            ];
        }
    }
@endphp

<div 
    wire:ignore
    x-data="{
        datosExistentes: @js($componentesData[$index]['data'] ?? []),
        filas: @js($datosIniciales),
        init() {
            let existentes = this.datosExistentes;
            if (existentes && !Array.isArray(existentes)) {
                existentes = Object.values(existentes);
            }

            if (Array.isArray(existentes) && existentes.length > 0) {
                Object.keys(this.filas).forEach(key => {
                    const nombre = this.filas[key].nombre;
                    const match = existentes.find(item => item && item.nombre === nombre);
                    if (match) {
                        this.filas[key].resultado = match.resultado || '';
                    }
                });
            }
            
            window.addEventListener('antes-de-guardar', () => {
                this.sincronizarConLivewire();
            });

            window.addEventListener('livewire:initialized', () => {
                Livewire.hook('morph.updating', () => {
                    this.sincronizarConLivewire();
                });
            });
        },
        clasificarResultado(fila) {
            if (fila.tipo_fila !== '3col' || !fila.resultado) return 'normal';
            const res = parseFloat(fila.resultado);
            if (isNaN(res)) return 'normal';
            const tipo = fila.rango_tipo || 'min-max';
            if (tipo === 'min-max') {
                const min = parseFloat(fila.rango_min);
                const max = parseFloat(fila.rango_max);
                if (isNaN(min) && isNaN(max)) return 'normal';
                if (!isNaN(min) && res < min) return 'bajo';
                if (!isNaN(max) && res > max) return 'alto';
                return 'normal';
            }
            const val = parseFloat(fila.rango_valor);
            if (isNaN(val)) return 'normal';
            if (tipo === 'menor' && res >= val) return 'alto';
            if (tipo === 'menor-igual' && res > val) return 'alto';
            if (tipo === 'mayor' && res <= val) return 'bajo';
            if (tipo === 'mayor-igual' && res < val) return 'bajo';
            return 'normal';
        },
        claseResultado(fila) {
            const c = this.clasificarResultado(fila);
            if (c === 'bajo') return 'text-blue-600 dark:text-blue-400 font-bold';
            if (c === 'alto') return 'text-red-600 dark:text-red-400 font-bold';
            return 'text-gray-900 dark:text-zinc-100';
        },
        sincronizarConLivewire() {
            const data = Object.values(this.filas);
            window.__labvetData = window.__labvetData || {};
            window.__labvetData['{{ $index }}'] = data;
            $wire.set('componentesData.{{ $index }}.data', data);
        }
    }"
    class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    
    @if(isset($componente['propiedades']['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-lg mb-4">
        {{ $componente['propiedades']['titulo'] }}
    </h4>
    @endif

    <table class="w-full text-sm border-collapse">
        <thead>
            <tr class="bg-gray-100 dark:bg-zinc-800">
                <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-left font-semibold text-gray-700 dark:text-zinc-300 w-2/5">{{ $componente['propiedades']['columna_analisis'] ?? 'ANÁLISIS' }}</th>
                <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center font-semibold text-gray-700 dark:text-zinc-300">{{ $componente['propiedades']['columna_resultado'] ?? 'RESULTADO' }}</th>
                @if($tieneRangos)
                <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center font-semibold text-gray-700 dark:text-zinc-300 w-1/5">{{ $componente['propiedades']['columna_rango'] ?? 'RANGO REF.' }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($filas as $i => $fila)
                @if(!empty($fila['nombre']))
                <tr>
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold text-gray-700 dark:text-zinc-300">
                        {{ $fila['nombre'] }}
                    </td>
                    @if(($fila['tipo_fila'] ?? '3col') === '2col')
                        {{-- Fila 2 columnas: select con opciones --}}
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1" {{ $tieneRangos ? 'colspan=2' : '' }}>
                            <select 
                                x-model="filas[{{ $i }}].resultado"
                                @change="sincronizarConLivewire()"
                                class="w-full px-2 py-1 text-center border-0 focus:ring-2 focus:ring-blue-500 rounded bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                                <option value="" class="bg-white dark:bg-zinc-800">-- Seleccionar --</option>
                                @foreach(explode(',', $fila['opciones'] ?? '') as $opcion)
                                    @if(trim($opcion) !== '')
                                    <option value="{{ trim($opcion) }}" class="bg-white dark:bg-zinc-800">{{ trim($opcion) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </td>
                    @else
                        {{-- Fila 3 columnas: input con detección de rango --}}
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1">
                            <input 
                                type="text"
                                x-model="filas[{{ $i }}].resultado"
                                @change="sincronizarConLivewire()"
                                @blur="sincronizarConLivewire()"
                                placeholder="Completar..."
                                :class="claseResultado(filas[{{ $i }}])"
                                class="w-full px-2 py-1 text-center border-0 focus:ring-2 focus:ring-blue-500 rounded bg-transparent placeholder-gray-400 dark:placeholder-zinc-500"
                            />
                        </td>
                        @if($tieneRangos)
                        <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center text-gray-500 dark:text-zinc-400 text-xs">
                            {{ $generarTextoRango($fila) }}
                        </td>
                        @endif
                    @endif
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
