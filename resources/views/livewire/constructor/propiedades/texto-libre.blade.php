<!-- Propiedades de Texto Libre -->
<div class="space-y-4">
    <!-- Título -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Título</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
    </div>

    <!-- Contenido de ejemplo -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Contenido de ejemplo (opcional)</label>
        <textarea 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.contenido"
            rows="4"
            placeholder="Texto de ejemplo para la vista previa..."
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100"></textarea>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Este texto solo es para la vista previa
        </p>
    </div>

    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
        <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">
            <i class="fas fa-info-circle mr-1"></i> ¿Cómo funciona este componente?
        </p>
        <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1 ml-4 list-disc">
            <li>Proporciona un <strong>área de texto libre</strong> donde el bioquímico puede escribir observaciones, conclusiones o comentarios sin formato predefinido.</li>
            <li>El contenido de ejemplo que escribas aquí es solo para la vista previa de la plantilla, <strong>no aparecerá</strong> al capturar resultados.</li>
            <li>Ideal para: Observaciones generales, Conclusiones, Notas adicionales, Interpretación clínica, etc.</li>
        </ul>
    </div>
</div>
