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
        <p class="text-xs text-blue-800 dark:text-blue-300">
            <i class="fas fa-info-circle mr-1"></i>
            Este componente permite subir 2 imágenes que se mostrarán lado a lado en la misma fila.
        </p>
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
