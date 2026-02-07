{{-- Componente de edición: Texto Libre --}}
<div x-data="{
    datoExistente: @js($componentesData[$index]['data'] ?? null),
    contenido: '',
    init() {
        // Cargar dato existente
        if (this.datoExistente && typeof this.datoExistente === 'object' && this.datoExistente.contenido) {
            this.contenido = this.datoExistente.contenido;
        } else if (typeof this.datoExistente === 'string') {
            this.contenido = this.datoExistente;
        }
        
        // Escuchar evento de guardado para forzar sincronización
        window.addEventListener('antes-de-guardar', () => {
            this.enviarDatos();
        });
    },
    enviarDatos() {
        $wire.set('componentesData.{{ $index }}.data', {
            titulo: '{{ $componente["propiedades"]["titulo"] ?? "" }}',
            formato: '{{ $componente["propiedades"]["formato"] ?? "parrafos" }}',
            contenido: this.contenido
        });
    }
}"
class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    @if(isset($componente['propiedades']['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-lg mb-3">
        {{ $componente['propiedades']['titulo'] }}
    </h4>
    @endif

    @if(($componente['propiedades']['formato'] ?? 'parrafos') === 'lista')
        {{-- Editor de lista --}}
        <div class="space-y-1">
            <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-2">
                <i class="fas fa-list-ul mr-1"></i>
                Cada línea será un elemento de la lista
            </label>
            <textarea 
                rows="8"
                x-model="contenido"
                @blur="enviarDatos()"
                placeholder="{{ $componente['propiedades']['contenido'] ?? 'Escribe cada item en una línea diferente' }}"
                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 font-mono text-sm"
            ></textarea>
        </div>
    @else
        {{-- Editor de párrafos --}}
        <div>
            <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-2">
                <i class="fas fa-align-left mr-1"></i>
                Texto en formato párrafo
            </label>
            <textarea 
                rows="8"
                x-model="contenido"
                @blur="enviarDatos()"
                placeholder="{{ $componente['propiedades']['contenido'] ?? 'Escriba el texto libre aquí. Puede incluir múltiples párrafos...' }}"
                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500"
            ></textarea>
        </div>
    @endif
</div>
