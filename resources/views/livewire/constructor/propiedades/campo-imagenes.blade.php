<!-- Propiedades de Campo de Imágenes -->
<div class="space-y-4">
    <!-- Título -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Título</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
    </div>

    <!-- Permitir imágenes -->
    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-zinc-900 rounded-lg border border-gray-200 dark:border-zinc-700">
        <label class="text-sm font-medium text-gray-700 dark:text-zinc-300">Permitir subir imágenes</label>
        <flux:switch 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.permitir" />
    </div>

    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
        <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">
            <i class="fas fa-info-circle mr-1"></i> ¿Cómo funciona este componente?
        </p>
        <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1 ml-4 list-disc">
            <li>Permite al bioquímico <strong>subir hasta 2 imágenes</strong> que se mostrarán lado a lado en el reporte PDF.</li>
            <li>Útil para adjuntar fotografías de microscopía, placas de cultivo, tinciones o cualquier evidencia visual del análisis.</li>
            <li>Las imágenes se redimensionan automáticamente para ajustarse al ancho del PDF.</li>
        </ul>
    </div>
</div>
        </label>
    </div>

    <!-- Ancho -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Ancho</label>
        <select 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.ancho"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
            <option value="100%">100% (Ancho completo)</option>
            <option value="75%">75%</option>
            <option value="50%">50%</option>
            <option value="25%">25%</option>
        </select>
    </div>
</div>
