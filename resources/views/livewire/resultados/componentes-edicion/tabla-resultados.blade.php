{{-- Componente de edición: Tabla de Resultados --}}
@php
    $filas = array_values($componente['propiedades']['filas'] ?? []);
    $columnas = array_values($componente['propiedades']['columnas'] ?? []);
@endphp

<div 
    wire:ignore
    x-data="{
        filas: @js($filas),
        columnas: @js($columnas),
        datosExistentes: @js($componentesData[$index]['data'] ?? []),
        datos: {
            @foreach($filas as $rowIndex => $analisis)
                {{ $rowIndex }}: {
                    nombre: '{{ addslashes(is_array($analisis) ? ($analisis['nombre'] ?? '') : $analisis) }}',
                    col_0: '',
                    col_1: '{{ addslashes(is_array($analisis) ? ($analisis['rango_ref'] ?? '') : '') }}',
                    unidad: '{{ addslashes(is_array($analisis) ? ($analisis['unidad'] ?? '') : '') }}'
                }{{ $loop->last ? '' : ',' }}
            @endforeach
        },
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
            
            // Sincronizar antes de cualquier acción de Livewire
            window.addEventListener('livewire:initialized', () => {
                Livewire.hook('morph.updating', () => {
                    this.sincronizarConLivewire();
                });
            });
        },
        sincronizarConLivewire() {
            $wire.set('componentesData.{{ $index }}.data', Object.values(this.datos));
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
                                class="w-full px-2 py-1 border-0 focus:ring-2 focus:ring-blue-500 rounded bg-transparent text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500"
                            />
                        @else
                            {{-- Segunda columna y siguientes: RANGOS DE REFERENCIA (solo lectura - texto estático) --}}
                            @php
                                $rangoRef = is_array($analisis) ? ($analisis['rango_ref'] ?? '') : '';
                                $unidadRef = is_array($analisis) ? ($analisis['unidad'] ?? '') : '';
                            @endphp
                            <div class="px-3 py-2 text-gray-600 dark:text-zinc-400 text-center">
                                {{ $rangoRef ?: '...' }}
                                @if($unidadRef)
                                    <span class="text-gray-500 dark:text-zinc-500 ml-2">{{ $unidadRef }}</span>
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
