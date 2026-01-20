<!-- Propiedades de Texto Libre -->
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

    <!-- Formato -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Formato</label>
        <select 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.formato"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="parrafos">Párrafos</option>
            <option value="lista">Lista con viñetas</option>
        </select>
    </div>

    <!-- Contenido de ejemplo -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Contenido de ejemplo (opcional)</label>
        <textarea 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.contenido"
            rows="4"
            placeholder="Texto de ejemplo para la vista previa..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
        <p class="text-xs text-gray-500 mt-1">
            Este texto solo es para la vista previa
        </p>
    </div>
</div>
