<!-- Propiedades de Campos Etiquetados -->
<div class="space-y-4">
    <div class="text-xs text-red-500 bg-red-50 p-2 rounded mb-2">Índice: {{ $indiceComponente }} | ID: {{ $componenteId ?? "N/A" }}</div>
    <!-- Título -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Título</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            placeholder="Ej: CULTIVO DE SECRECION VIA AEREAS"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>

    <!-- Campos -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Campos (etiquetas)</label>
        <p class="text-xs text-gray-500 mb-2">Define los campos que el bioquímico deberá completar</p>
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @foreach($props['campos'] ?? [] as $index => $campo)
            <div class="flex gap-2">
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.campos.{{ $index }}"
                    placeholder="Ej: MUESTRA, COLOR, TINCION GRAM..."
                    class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm">
                <button 
                    wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.campos', {{ json_encode(array_values(array_filter($props['campos'], fn($f, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                    class="px-3 py-1 bg-red-100 text-red-600 hover:bg-red-200 rounded text-xs font-medium">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            @endforeach
        </div>
        
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.campos', {{ json_encode(array_merge($props['campos'] ?? [], [''])) }})"
            variant="primary" 
            icon="plus" 
            class="w-full mt-2">
            Agregar Campo
        </flux:button>
    </div>

    <div class="p-3 bg-blue-50 rounded border border-blue-200">
        <p class="text-xs text-blue-800">
            <i class="fas fa-info-circle mr-1"></i>
            Este componente mostrará una lista de campos con etiquetas que el bioquímico completará con texto libre.
        </p>
    </div>
</div>
