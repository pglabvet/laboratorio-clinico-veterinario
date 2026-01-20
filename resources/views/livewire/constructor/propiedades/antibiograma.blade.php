<!-- Propiedades de Antibiograma -->
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

    <!-- Nombres de columnas -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Columnas</label>
        @foreach($props['columnas'] ?? ['SENSIBLE', 'INTERMEDIO', 'RESISTENTE'] as $index => $columna)
        <div class="mb-2">
            <input 
                type="text"
                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.columnas.{{ $index }}"
                placeholder="Nombre de columna"
                class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
        </div>
        @endforeach
    </div>

    <div class="p-3 bg-yellow-50 rounded border border-yellow-200">
        <p class="text-xs text-yellow-800">
            <i class="fas fa-info-circle mr-1"></i>
            Los antibióticos se agregarán al llenar el formulario
        </p>
    </div>
</div>
