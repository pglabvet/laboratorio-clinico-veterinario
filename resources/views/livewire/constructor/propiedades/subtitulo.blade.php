<!-- Propiedades de SubtÃ­tulo -->
<div class="space-y-4">
    <div class="text-xs text-red-500 bg-red-50 p-2 rounded mb-2">Índice: {{ $indiceComponente }} | ID: {{ $componenteId ?? "N/A" }}</div>
    <!-- Texto -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Texto</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.texto"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>

    <!-- TamaÃ±o -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">TamaÃ±o</label>
        <select 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.tamano"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="grande">Grande</option>
            <option value="mediano">Mediano</option>
            <option value="pequeño">Pequeño</option>
        </select>
    </div>

    <!-- Alineación -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Alineación</label>
        <select 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.alineacion"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="izquierda">Izquierda</option>
            <option value="centro">Centro</option>
            <option value="derecha">Derecha</option>
        </select>
    </div>
</div>
