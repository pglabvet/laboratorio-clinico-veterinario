<!-- Propiedades de Lista de Items -->
<div class="space-y-4">
    <div class="text-xs text-red-500 bg-red-50 p-2 rounded mb-2">Índice: {{ $indiceComponente }} | ID: {{ $componenteId ?? "N/A" }}</div>
    <!-- Título -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Título</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>

    <!-- Items -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Items predefinidos</label>
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @foreach($props['items'] ?? [] as $index => $item)
            <div class="flex gap-2">
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.items.{{ $index }}"
                    placeholder="Item {{ $index + 1 }}"
                    class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm">
                <button 
                    wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.items', {{ json_encode(array_values(array_filter($props['items'], fn($f, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                    class="px-2 text-red-600 hover:text-red-700">
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
