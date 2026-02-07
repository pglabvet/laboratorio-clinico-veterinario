{{-- Componente de edición: Campo de Texto Simple --}}
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
        $wire.set('componentesData.{{ $index }}.data', {
            label: '{{ $componente["propiedades"]["label"] ?? "Campo" }}',
            tipo: '{{ $componente["propiedades"]["tipo"] ?? "texto" }}',
            valor: this.valor
        });
    }
}"
class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">
        {{ $componente['propiedades']['label'] ?? 'Campo' }}
    </label>
    
    @if($componente['propiedades']['tipo'] === 'numero')
        <input 
            type="number" 
            x-model="valor"
            @blur="enviarDatos()"
            placeholder="{{ $componente['propiedades']['placeholder'] ?? 'Ingrese un número' }}"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500"
        />
    @elseif($componente['propiedades']['tipo'] === 'fecha')
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
