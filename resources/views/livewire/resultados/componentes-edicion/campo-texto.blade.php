{{-- Componente de edición: Campo de Texto Simple --}}
@php
    $tipoUso = $componente['propiedades']['tipo_uso'] ?? 'editable';
    $titulo = $componente['propiedades']['titulo'] ?? '';
@endphp

@if($tipoUso === 'nota')
{{-- Modo Nota Fija: caja con borde, título inline bold --}}
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
    class="border border-gray-300 dark:border-zinc-600 rounded-lg p-4 bg-white dark:bg-zinc-900"
>
    <p class="text-sm text-gray-700 dark:text-zinc-300 leading-relaxed">
        @if(!empty($titulo))
            <strong class="text-gray-900 dark:text-zinc-100">{{ $titulo }}:</strong>
        @endif
        {{ $componente['propiedades']['contenido'] ?? '' }}
    </p>
</div>

@else
{{-- Modo Editable: caja con borde, título inline bold + textarea --}}
<div x-data="{
    datoExistente: @js($componentesData[$index]['data'] ?? null),
    valor: '',
    init() {
        if (this.datoExistente && typeof this.datoExistente === 'object' && this.datoExistente.valor) {
            this.valor = this.datoExistente.valor;
        } else if (typeof this.datoExistente === 'string') {
            this.valor = this.datoExistente;
        }
        
        window.addEventListener('antes-de-guardar', () => {
            this.enviarDatos();
        });
    },
    enviarDatos() {
        const data = {
            valor: this.valor
        };
        window.__labvetData = window.__labvetData || {};
        window.__labvetData['{{ $index }}'] = data;
        $wire.set('componentesData.{{ $index }}.data', data);
    }
}"
class="border border-gray-300 dark:border-zinc-600 rounded-lg p-4 bg-white dark:bg-zinc-900">
    @if(!empty($titulo))
    <label class="block text-sm font-bold text-gray-900 dark:text-zinc-100 mb-2">
        {{ $titulo }}:
    </label>
    @endif

    <textarea 
        rows="3"
        x-model="valor"
        @blur="enviarDatos()"
        placeholder="Ingrese texto"
        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500"
    ></textarea>
</div>
@endif
