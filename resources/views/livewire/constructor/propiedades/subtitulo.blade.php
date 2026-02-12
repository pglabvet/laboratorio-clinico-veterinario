<!-- Propiedades de SubtÃ­tulo -->
<div class="space-y-4">
    <!-- Texto -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Texto</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.texto"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
    </div>

    <!-- TamaÃ±o -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Tamaño</label>
        <select 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.tamano"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
            <option value="grande">Grande</option>
            <option value="mediano">Mediano</option>
            <option value="pequeño">Pequeño</option>
        </select>
    </div>

    <!-- Alineación -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Alineación</label>
        <select 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.alineacion"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
            <option value="izquierda">Izquierda</option>
            <option value="centro">Centro</option>
            <option value="derecha">Derecha</option>
        </select>
    </div>

    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
        <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">
            <i class="fas fa-info-circle mr-1"></i> ¿Cómo funciona este componente?
        </p>
        <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1 ml-4 list-disc">
            <li>Inserta un <strong>encabezado o separador visual</strong> entre secciones del reporte. No es editable por el bioquímico.</li>
            <li>Puedes ajustar el <strong>tamaño</strong> (grande, mediano, pequeño) y la <strong>alineación</strong> (izquierda, centro, derecha) según la jerarquía visual que necesites.</li>
            <li>Ideal para separar bloques como: "RESULTADOS", "OBSERVACIONES", "ÍNDICES ERITROCITARIOS", etc.</li>
        </ul>
    </div>
</div>
