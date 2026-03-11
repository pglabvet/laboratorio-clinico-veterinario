{{-- Componente de edición: Examen Microscópico --}}
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

    $tieneRangos = collect($filas)->contains(fn($f) => $generarTextoRango($f) !== '');

    // Preparar datos iniciales para Alpine
    $datosIniciales = [];
    foreach ($filas as $i => $fila) {
        if (!empty($fila['parametro'])) {
            $datosIniciales[$i] = [
                'parametro' => $fila['parametro'],
                'resultado' => '',
                'rango_tipo' => $fila['rango_tipo'] ?? 'min-max',
                'rango_min' => $fila['rango_min'] ?? '',
                'rango_max' => $fila['rango_max'] ?? '',
                'rango_valor' => $fila['rango_valor'] ?? '',
                'rango_display' => $generarTextoRango($fila),
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
            // Convertir a array si es objeto
            let existentes = this.datosExistentes;
            if (existentes && !Array.isArray(existentes)) {
                existentes = Object.values(existentes);
            }

            // Cargar datos existentes buscando por nombre de parámetro
            if (Array.isArray(existentes) && existentes.length > 0) {
                Object.keys(this.filas).forEach(key => {
                    const parametro = this.filas[key].parametro;
                    const match = existentes.find(item => item && item.parametro === parametro);
                    if (match) {
                        this.filas[key].resultado = match.resultado || '';
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
        esFueraDeRango(fila) {
            if (!fila.resultado) return false;
            const resultado = parseFloat(fila.resultado);
            if (isNaN(resultado)) return false;
            
            const tipo = fila.rango_tipo || 'min-max';
            if (tipo === 'min-max') {
                const min = parseFloat(fila.rango_min);
                const max = parseFloat(fila.rango_max);
                if (isNaN(min) && isNaN(max)) return false;
                if (!isNaN(min) && resultado < min) return true;
                if (!isNaN(max) && resultado > max) return true;
                return false;
            }
            const valor = parseFloat(fila.rango_valor);
            if (isNaN(valor)) return false;
            if (tipo === 'menor') return resultado >= valor;
            if (tipo === 'menor-igual') return resultado > valor;
            if (tipo === 'mayor') return resultado <= valor;
            if (tipo === 'mayor-igual') return resultado < valor;
            return false;
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
                <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-left font-semibold text-gray-700 dark:text-zinc-300 w-1/4">{{ $componente['propiedades']['columna_parametro'] ?? 'PARÁMETRO' }}</th>
                <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center font-semibold text-gray-700 dark:text-zinc-300">{{ $componente['propiedades']['columna_resultado'] ?? 'RESULTADO' }}</th>
                @if($tieneRangos)
                <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center font-semibold text-gray-700 dark:text-zinc-300 w-1/5">{{ $componente['propiedades']['columna_rango'] ?? 'RANGO REF.' }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($filas as $i => $fila)
                @if(!empty($fila['parametro']))
                <tr>
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold text-gray-700 dark:text-zinc-300">
                        {{ $fila['parametro'] }}
                    </td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1">
                        <input 
                            type="text"
                            x-model="filas[{{ $i }}].resultado"
                            @change="sincronizarConLivewire()"
                            @blur="sincronizarConLivewire()"
                            :class="esFueraDeRango(filas[{{ $i }}]) ? 'text-red-600 font-bold' : 'text-gray-900 dark:text-zinc-100'"
                            placeholder="Completar..."
                            class="w-full px-2 py-1 text-center border-0 focus:ring-2 focus:ring-blue-500 rounded bg-transparent placeholder-gray-400 dark:placeholder-zinc-500"
                        />
                    </td>
                    @if($tieneRangos)
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center text-gray-500 dark:text-zinc-400 text-xs">
                        {{ $generarTextoRango($fila) }}
                    </td>
                    @endif
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
