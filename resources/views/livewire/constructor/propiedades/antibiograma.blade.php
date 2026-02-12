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
        <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">
            <i class="fas fa-info-circle mr-1"></i> ¿Cómo funciona este componente?
        </p>
        <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1 ml-4 list-disc">
            <li>Genera una tabla de antibiograma con las columnas que definas arriba (por defecto: SENSIBLE, INTERMEDIO, RESISTENTE).</li>
            <li>Los <strong>antibióticos</strong> (filas) no se configuran aquí. El bioquímico los agregará al momento de capturar resultados, ya que varían según cada caso.</li>
        </ul>
    </div>
</div>
