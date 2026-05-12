{{-- Componente de edición: Subtítulo --}}
<div x-data="{
    datoExistente: @js($componentesData[$index]['data'] ?? null),
    texto: '',
    init() {
        // Cargar dato existente o usar el texto de las propiedades
        if (this.datoExistente && typeof this.datoExistente === 'object' && this.datoExistente.texto) {
            this.texto = this.datoExistente.texto;
        } else if (typeof this.datoExistente === 'string') {
            this.texto = this.datoExistente;
        } else {
            this.texto = '{{ $componente["propiedades"]["texto"] ?? "" }}';
        }
        
        // Escuchar evento de guardado para forzar sincronización
        window.addEventListener('antes-de-guardar', () => {
            this.enviarDatos();
        });
    },
    enviarDatos() {
        const data = {
            texto: this.texto,
            tamano: '{{ $componente["propiedades"]["tamano"] ?? "mediano" }}',
            alineacion: '{{ $componente["propiedades"]["alineacion"] ?? "izquierda" }}'
        };
        window.__labvetData = window.__labvetData || {};
        window.__labvetData['{{ $index }}'] = data;
        $wire.set('componentesData.{{ $index }}.data', data);
    }
}"
class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">
        Subtítulo de Sección
    </label>
    
    <input 
        type="text"
        x-model="texto"
        @blur="enviarDatos()"
        placeholder="Ingrese el subtítulo"
        class="w-full px-4 py-3 text-lg font-bold border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500
            @if(($componente['propiedades']['alineacion'] ?? 'izquierda') === 'centro') text-center @endif
            @if(($componente['propiedades']['alineacion'] ?? 'izquierda') === 'derecha') text-right @endif"
    />
    
    <p class="text-xs text-gray-500 dark:text-zinc-400 mt-2">
        <i class="fas fa-info-circle mr-1"></i>
        Tamaño: {{ ucfirst($componente['propiedades']['tamano'] ?? 'mediano') }} | 
        Alineación: {{ ucfirst($componente['propiedades']['alineacion'] ?? 'izquierda') }}
    </p>
</div>
