<!-- Propiedades de Antibiograma -->
<div class="space-y-4">
    <!-- Título -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Título</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
    </div>

    <!-- Nombres de columnas -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Columnas</label>
        @foreach($props['columnas'] ?? ['SENSIBLE', 'INTERMEDIO', 'RESISTENTE'] as $index => $columna)
        <div class="mb-2">
            <input 
                type="text"
                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.columnas.{{ $index }}"
                placeholder="Nombre de columna"
                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
        </div>
        @endforeach
    </div>

    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
        <p class="text-xs text-blue-800 dark:text-blue-300">
            <i class="fas fa-info-circle mr-1"></i>
            Los antibióticos se agregarán al llenar el formulario
        </p>
    </div>
</div>
