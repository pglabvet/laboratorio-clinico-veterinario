<!-- Propiedades de Lista de Items -->
<div class="space-y-4">
    <div class="text-xs text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/20 p-2 rounded mb-2">Índice: {{ $indiceComponente }} | ID: {{ $componenteId ?? "N/A" }}</div>
    <!-- Título -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Título</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
    </div>

    <!-- Items -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Items predefinidos</label>
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @foreach($props['items'] ?? [] as $index => $item)
            <div class="flex gap-2">
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.items.{{ $index }}"
                    placeholder="Item {{ $index + 1 }}"
                    class="flex-1 px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                <button 
                    wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.items', {{ json_encode(array_values(array_filter($props['items'], fn($f, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                    class="px-2 text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @endforeach
        </div>
        
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.items', {{ json_encode(array_merge($props['items'] ?? [], [''])) }})"
            variant="primary" 
            icon="plus" 
            class="w-full mt-2">
            Agregar Item
        </flux:button>
    </div>
</div>
