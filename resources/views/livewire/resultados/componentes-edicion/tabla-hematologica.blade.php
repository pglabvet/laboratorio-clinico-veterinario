{{-- Componente de edición: Tabla Hematológica --}}
<div 
    wire:ignore
    x-data="{
        datosExistentes: @js($componentesData[$index]['data'] ?? null),
        parametros: {
            @foreach($componente['propiedades']['parametros_principales'] ?? [] as $i => $param)
            {{ $i }}: { nombre: '{{ addslashes($param["nombre"]) }}', resultado: '', unidad: '{{ addslashes($param["unidad"]) }}' }{{ $loop->last ? '' : ',' }}
            @endforeach
        },
        diferenciales: {
            @foreach($componente['propiedades']['diferenciales'] ?? [] as $i => $dif)
            {{ $i }}: { nombre: '{{ addslashes($dif["nombre"]) }}', valor_rel: '', valor_abs: '' }{{ $loop->last ? '' : ',' }}
            @endforeach
        },
        indices: {
            @foreach($componente['propiedades']['indices'] ?? [] as $i => $indice)
            {{ $i }}: { nombre: '{{ addslashes($indice["nombre"]) }}', resultado: '', unidad: '{{ addslashes($indice["unidad"]) }}' }{{ $loop->last ? '' : ',' }}
            @endforeach
        },
        init() {
            // Cargar datos existentes si existen
            if (this.datosExistentes && typeof this.datosExistentes === 'object') {
                if (Array.isArray(this.datosExistentes.parametros)) {
                    this.datosExistentes.parametros.forEach((param, i) => {
                        if (this.parametros[i]) {
                            this.parametros[i].resultado = param.resultado || '';
                        }
                    });
                }
                if (Array.isArray(this.datosExistentes.diferenciales)) {
                    this.datosExistentes.diferenciales.forEach((dif, i) => {
                        if (this.diferenciales[i]) {
                            this.diferenciales[i].valor_rel = dif.valor_rel || '';
                            this.diferenciales[i].valor_abs = dif.valor_abs || '';
                        }
                    });
                }
                if (Array.isArray(this.datosExistentes.indices)) {
                    this.datosExistentes.indices.forEach((ind, i) => {
                        if (this.indices[i]) {
                            this.indices[i].resultado = ind.resultado || '';
                        }
                    });
                }
            }
            
            // Sincronizar antes de cualquier acción de Livewire
            window.addEventListener('livewire:initialized', () => {
                Livewire.hook('morph.updating', () => {
                    this.sincronizarConLivewire();
                });
            });
        },
        sincronizarConLivewire() {
            $wire.set('componentesData.{{ $index }}.data', {
                parametros: Object.values(this.parametros),
                diferenciales: Object.values(this.diferenciales),
                indices: Object.values(this.indices)
            });
        }
    }"
    class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    <div class="overflow-x-auto">
        <table class="w-full border border-gray-300 dark:border-zinc-700 text-xs">
            <thead>
                <tr>
                    <th colspan="5" class="border border-gray-300 dark:border-zinc-700 px-2 py-2 bg-purple-100 dark:bg-purple-900/30 font-bold text-gray-900 dark:text-zinc-100">
                        {{ $componente['propiedades']['titulo'] ?? 'CUADRO HEMÁTICO' }}
                    </th>
                    <th colspan="7" class="border border-gray-300 dark:border-zinc-700 px-2 py-2 bg-purple-100 dark:bg-purple-900/30"></th>
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
                        <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 bg-purple-50 dark:bg-purple-900/20 font-semibold text-xs text-gray-900 dark:text-zinc-100">
                            {{ $param['nombre'] }}
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1">
                            <input type="text" x-model="parametros[{{ $i }}].resultado" @change="sincronizarConLivewire()" @blur="sincronizarConLivewire()" class="w-full px-1 py-0.5 text-xs border-0 focus:ring-1 focus:ring-blue-500 rounded bg-transparent text-gray-900 dark:text-zinc-100" />
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">
                            {{ $param['unidad'] }}
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-500 dark:text-zinc-400">
                            {{ $param['ref_min'] }}
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-500 dark:text-zinc-400">
                            {{ $param['ref_max'] ?? '' }}
                        </td>
                    @else
                        <td class="border border-gray-300 dark:border-zinc-700" colspan="5"></td>
                    @endif
                    
                    {{-- Diferenciales (Lado Derecho) --}}
                    @if($i < count($componente['propiedades']['diferenciales'] ?? []))
                        @php $dif = $componente['propiedades']['diferenciales'][$i]; @endphp
                        <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 bg-purple-50 dark:bg-purple-900/20 font-semibold text-xs text-gray-900 dark:text-zinc-100">
                            {{ $dif['nombre'] }}
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1">
                            <input type="text" x-model="diferenciales[{{ $i }}].valor_rel" @change="sincronizarConLivewire()" @blur="sincronizarConLivewire()" class="w-full px-1 py-0.5 text-xs border-0 focus:ring-1 focus:ring-blue-500 rounded bg-transparent text-gray-900 dark:text-zinc-100" />
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">%</td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-500 dark:text-zinc-400">
                            {{ $dif['ref_rel_min'] }}-{{ $dif['ref_rel_max'] }}
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1">
                            <input type="text" x-model="diferenciales[{{ $i }}].valor_abs" @change="sincronizarConLivewire()" @blur="sincronizarConLivewire()" class="w-full px-1 py-0.5 text-xs border-0 focus:ring-1 focus:ring-blue-500 rounded bg-transparent text-gray-900 dark:text-zinc-100" />
                        </td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">mm³</td>
                        <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-500 dark:text-zinc-400">
                            {{ $dif['ref_abs_min'] }}-{{ $dif['ref_abs_max'] }}
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
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 bg-purple-50 dark:bg-purple-900/20 font-semibold text-xs text-gray-900 dark:text-zinc-100" rowspan="{{ count($componente['propiedades']['indices'] ?? []) }}">
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
                        {{ $indice['referencia'] }}
                    </td>
                    <td class="border border-gray-300 dark:border-zinc-700" colspan="7"></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Ayuda visual --}}
    <div class="mt-3 p-2 bg-purple-50 dark:bg-purple-900/20 rounded text-xs text-purple-800 dark:text-purple-300">
        <i class="fas fa-info-circle mr-1"></i>
        Complete los valores en cada campo. Los rangos de referencia se muestran como guía.
    </div>
</div>
