<!-- Propiedades de Campo de Texto Simple -->
<div class="space-y-4">
    <div class="text-xs text-red-500 bg-red-50 p-2 rounded mb-2">Índice: {{ $indiceComponente }} | ID: {{ $componenteId ?? "N/A" }}</div>
    <!-- Label -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Etiqueta</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.label"
            placeholder="Ej: Diagnóstico, Observaciones"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>

    <!-- Placeholder -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Placeholder</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.placeholder"
            placeholder="Texto de ayuda"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>

    <!-- Tipo -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de campo</label>
        <select 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.tipo"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="texto">Texto</option>
            <option value="numero">Número</option>
            <option value="fecha">Fecha</option>
        </select>
    </div>
</div>
