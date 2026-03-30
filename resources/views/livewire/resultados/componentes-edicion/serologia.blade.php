{{-- Componente de edición: Serología --}}
@php
    $camposRaw = $componente['propiedades']['campos'] ?? [];
    // Normalizar: convertir strings a objetos y filtrar vacíos
    $campos = array_values(array_filter(array_map(function($c) {
        $nombre = is_string($c) ? $c : ($c['nombre'] ?? '');
        return $nombre ? $nombre : null;
    }, $camposRaw)));
    $datosIniciales = [];
    foreach ($campos as $i => $campo) {
        $datosIniciales[$i] = ['campo' => $campo, 'valor' => ''];
    }
    
    // Descripción: tipo fijo o seleccionable
    $tipoDesc = $componente['propiedades']['tipo_descripcion'] ?? 'input';
    $descFija = $componente['propiedades']['descripcion'] ?? '';
    $opcionesDesc = array_filter(array_map('trim', explode(',', $componente['propiedades']['opciones_descripcion'] ?? '')));
@endphp

<div 
    wire:ignore
    x-data="{
        datosExistentes: @js($componentesData[$index]['data'] ?? []),
        datos: @js($datosIniciales),
        tipoDesc: @js($tipoDesc),
        descripcionSeleccionada: '',
        opcionesDesc: @js($opcionesDesc),
        descFija: @js($descFija),
        init() {
            let existentes = this.datosExistentes;
            if (existentes && !Array.isArray(existentes)) {
                existentes = Object.values(existentes);
            }

            if (Array.isArray(existentes) && existentes.length > 0) {
                // Extraer descripción del metadato si existe
                const metaDesc = existentes.find(item => item && item._meta === 'descripcion');
                if (metaDesc) {
                    this.descripcionSeleccionada = metaDesc.valor || '';
                }
                
                Object.keys(this.datos).forEach(key => {
                    const nombre = this.datos[key].campo;
                    const match = existentes.find(item => item && item.campo === nombre);
                    if (match) {
                        this.datos[key].valor = match.valor || '';
                    }
                });
            }

            // Si es texto fijo, usar la descripción de la plantilla
            if (this.tipoDesc === 'input' && this.descFija) {
                this.descripcionSeleccionada = this.descFija;
            }
            // Si es seleccionable y solo hay una opción, usarla automáticamente
            if (this.tipoDesc === 'select' && this.opcionesDesc.length === 1 && !this.descripcionSeleccionada) {
                this.descripcionSeleccionada = this.opcionesDesc[0];
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
            // Agregar la descripción seleccionada como metadato al inicio
            const dataConDesc = [];
            if (this.descripcionSeleccionada) {
                dataConDesc.push({ _meta: 'descripcion', valor: this.descripcionSeleccionada });
            }
            dataConDesc.push(...data);
            window.__labvetData = window.__labvetData || {};
            window.__labvetData['{{ $index }}'] = dataConDesc;
            $wire.set('componentesData.{{ $index }}.data', dataConDesc);
        }
    }"
    class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    
    @if(isset($componente['propiedades']['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-lg mb-1">
        {{ $componente['propiedades']['titulo'] }}
    </h4>
    @endif

    @if($tipoDesc === 'select' && count($opcionesDesc) > 0)
    {{-- Dropdown para elegir la descripción --}}
    <div class="mb-3">
        <label class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1 text-center">Seleccione la técnica / descripción</label>
        <select
            x-model="descripcionSeleccionada"
            @change="sincronizarConLivewire()"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500">
            <option value="">Seleccionar descripción...</option>
            @foreach($opcionesDesc as $descOpcion)
                <option value="{{ $descOpcion }}">{{ $descOpcion }}</option>
            @endforeach
        </select>
    </div>
    @elseif($tipoDesc === 'input' && !empty($descFija))
    <p class="text-xs text-gray-500 dark:text-zinc-400 text-center italic mb-3">
        {{ $descFija }}
    </p>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-300 dark:border-zinc-700 text-sm">
            @if(!empty($componente['propiedades']['columnas']))
            <thead>
                <tr class="bg-gray-100 dark:bg-zinc-900">
                    @foreach($componente['propiedades']['columnas'] as $columna)
                    <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold text-gray-900 dark:text-zinc-100">
                        {{ $columna['nombre'] ?? '' }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            @endif
            @foreach($campos as $i => $campo)
            <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800">
                <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold bg-gray-50 dark:bg-zinc-900 w-2/3 text-gray-900 dark:text-zinc-100">
                    {{ $campo }}
                </td>
                <td class="border border-gray-300 dark:border-zinc-700 px-2 py-2">
                    <select
                        x-model="datos[{{ $i }}].valor"
                        @change="sincronizarConLivewire()"
                        class="w-full px-2 py-1 border-0 focus:ring-2 focus:ring-blue-500 rounded bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 [&>option]:bg-white [&>option]:dark:bg-zinc-800 [&>option]:text-gray-900 [&>option]:dark:text-zinc-100"
                        :class="datos[{{ $i }}].valor === 'Positivo (+)' ? 'text-red-600 dark:text-red-400 font-bold' : ''">
                        <option value="">Seleccionar...</option>
                        <option value="Negativo (-)">Negativo (-)</option>
                        <option value="Positivo (+)">Positivo (+)</option>
                    </select>
                </td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="mt-3 p-2 bg-blue-50 dark:bg-blue-900/20 rounded text-xs text-blue-800 dark:text-blue-300">
        <i class="fas fa-info-circle mr-1"></i>
        Seleccione el resultado para cada prueba serológica.
    </div>

    {{-- Repeticiones por campo (solo si hay reactivos asignados) --}}
    @include('livewire.resultados.componentes-edicion._repeticiones-reactivos', [
        'componente' => $componente,
        'index' => $index,
    ])
</div>
