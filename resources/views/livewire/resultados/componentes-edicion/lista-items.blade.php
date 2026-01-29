{{-- Componente de edición: Lista de Items --}}
<div 
    wire:ignore
    x-data="{
        datosExistentes: @js($componentesData[$index]['data'] ?? []),
        items: [],
        
        init() {
            // Cargar datos existentes o inicializar vacío
            if (Array.isArray(this.datosExistentes) && this.datosExistentes.length > 0) {
                this.items = this.datosExistentes.map(texto => ({
                    id: crypto.randomUUID(),
                    texto: texto
                }));
            } else {
                // Inicializar 5 items vacíos con IDs únicos
                for (let i = 0; i < 5; i++) {
                    this.items.push({
                        id: crypto.randomUUID(),
                        texto: ''
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
        
        agregarItem() {
            this.items.push({
                id: crypto.randomUUID(),
                texto: ''
            });
            this.sincronizarConLivewire();
        },
        
        eliminarItem(id) {
            if (this.items.length > 1) {
                this.items = this.items.filter(item => item.id !== id);
                this.sincronizarConLivewire();
            }
        },
        
        sincronizarConLivewire() {
            // Filtrar items vacíos y enviar solo el texto (sin IDs)
            const itemsFiltrados = this.items
                .filter(item => item.texto.trim() !== '')
                .map(item => item.texto);
            $wire.set('componentesData.{{ $index }}.data', itemsFiltrados);
        }
    }"
    class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    @if(isset($componente['propiedades']['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-lg mb-4">
        {{ $componente['propiedades']['titulo'] }}
    </h4>
    @endif

    <div class="space-y-2 mb-3">
        {{-- Lista editable dinámica --}}
        <template x-for="item in items" :key="item.id">
            <div class="flex gap-2 items-center group">
                <span class="text-gray-600 dark:text-zinc-400"></span>
                <input 
                    type="text"
                    x-model="item.texto"
                    @change="sincronizarConLivewire()"
                    @blur="sincronizarConLivewire()"
                    :placeholder="'Item ' + (items.indexOf(item) + 1)"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500"
                />
                <button 
                    @click="eliminarItem(item.id)"
                    type="button"
                    class="p-2 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all"
                    :disabled="items.length <= 1"
                    x-show="items.length > 1"
                    title="Eliminar item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    {{-- Botón agregar más items --}}
    <flux:button 
        @click="agregarItem()"
        variant="outline" 
        icon="plus" 
        size="sm">
        Agregar Item
    </flux:button>
</div>
