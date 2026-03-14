{{-- Componente de edición: Serología --}}
@php
    $campos = array_values(array_filter($componente['propiedades']['campos'] ?? [], fn($c) => !empty($c)));
    $datosIniciales = [];
    foreach ($campos as $i => $campo) {
        $datosIniciales[$i] = ['campo' => $campo, 'valor' => ''];
    }
@endphp

<div 
    wire:ignore
    x-data="{
        datosExistentes: @js($componentesData[$index]['data'] ?? []),
        datos: @js($datosIniciales),
        init() {
            let existentes = this.datosExistentes;
            if (existentes && !Array.isArray(existentes)) {
                existentes = Object.values(existentes);
            }

            if (Array.isArray(existentes) && existentes.length > 0) {
                Object.keys(this.datos).forEach(key => {
                    const nombre = this.datos[key].campo;
                    const match = existentes.find(item => item && item.campo === nombre);
                    if (match) {
                        this.datos[key].valor = match.valor || '';
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
        }
    }"
    class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    
    @if(isset($componente['propiedades']['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-lg mb-1">
        {{ $componente['propiedades']['titulo'] }}
    </h4>
    @endif

    @if(isset($componente['propiedades']['descripcion']) && $componente['propiedades']['descripcion'])
    <p class="text-xs text-gray-500 dark:text-zinc-400 text-center italic mb-3">
        {{ $componente['propiedades']['descripcion'] }}
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
</div>
