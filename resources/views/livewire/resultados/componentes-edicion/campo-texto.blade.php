{{-- Componente de edición: Campo de Texto Simple --}}
@php
    $tipoUso = $componente['propiedades']['tipo_uso'] ?? 'editable';
@endphp

@if($tipoUso === 'nota')
{{-- Modo Nota Fija: texto predefinido de solo lectura --}}
<div 
    wire:ignore
    x-data="{
        init() {
            const data = {
                tipo_uso: 'nota',
                contenido: @js($componente['propiedades']['contenido'] ?? '')
            };
            window.__labvetData = window.__labvetData || {};
            window.__labvetData['{{ $index }}'] = data;
            $wire.set('componentesData.{{ $index }}.data', data);
            
            window.addEventListener('antes-de-guardar', () => {
                window.__labvetData = window.__labvetData || {};
                window.__labvetData['{{ $index }}'] = data;
                $wire.set('componentesData.{{ $index }}.data', data);
            });
        }
    }"
    class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900"
>
    @if(!empty($componente['propiedades']['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-lg mb-3">
        {{ $componente['propiedades']['titulo'] }}
    </h4>
    @endif

    <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded border border-amber-200 dark:border-amber-800">
        <p class="text-sm text-amber-900 dark:text-amber-200 whitespace-pre-line leading-relaxed">{{ $componente['propiedades']['contenido'] ?? '' }}</p>
    </div>
</div>

@else
{{-- Modo Editable --}}
<div x-data="{
    datoExistente: @js($componentesData[$index]['data'] ?? null),
    valor: '',
    init() {
        // Cargar dato existente
        if (this.datoExistente && typeof this.datoExistente === 'object' && this.datoExistente.valor) {
            this.valor = this.datoExistente.valor;
        } else if (typeof this.datoExistente === 'string') {
            this.valor = this.datoExistente;
        }
        
        // Escuchar evento de guardado para forzar sincronización
        window.addEventListener('antes-de-guardar', () => {
            this.enviarDatos();
        });
    },
    enviarDatos() {
        const data = {
            label: '{{ $componente["propiedades"]["label"] ?? "Campo" }}',
            tipo: '{{ $componente["propiedades"]["tipo"] ?? "texto" }}',
            valor: this.valor
        };
        window.__labvetData = window.__labvetData || {};
        window.__labvetData['{{ $index }}'] = data;
        $wire.set('componentesData.{{ $index }}.data', data);
    }
}"
class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    @if(!empty($componente['propiedades']['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-lg mb-4">
        {{ $componente['propiedades']['titulo'] }}
    </h4>
    @endif

    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">
        {{ $componente['propiedades']['label'] ?? 'Campo' }}
    </label>
    
    @if(($componente['propiedades']['tipo'] ?? 'texto') === 'numero')
        <input 
            type="number" 
            x-model="valor"
            @blur="enviarDatos()"
            placeholder="{{ $componente['propiedades']['placeholder'] ?? 'Ingrese un número' }}"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500"
        />
    @elseif(($componente['propiedades']['tipo'] ?? 'texto') === 'fecha')
        <input 
            type="date" 
            x-model="valor"
            @blur="enviarDatos()"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100"
        />
    @else
        <textarea 
            rows="3"
            x-model="valor"
            @blur="enviarDatos()"
            placeholder="{{ $componente['propiedades']['placeholder'] ?? 'Ingrese texto' }}"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500"
        ></textarea>
    @endif
</div>
@endif
