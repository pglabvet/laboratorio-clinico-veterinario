<!-- Propiedades de Campo de Texto Simple -->
@php
    $tipoUso = $this->componentes[$indiceComponente]['propiedades']['tipo_uso'] ?? 'editable';
@endphp
<div class="space-y-4">
    <!-- Tipo de uso -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Tipo de uso</label>
        <div class="flex gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" value="editable"
                    wire:model.live="componentes.{{ $indiceComponente }}.propiedades.tipo_uso"
                    class="text-blue-600 focus:ring-blue-500">
                <span class="text-sm text-gray-700 dark:text-zinc-300">
                    <i class="fas fa-edit mr-1"></i> Editable
                </span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" value="nota"
                    wire:model.live="componentes.{{ $indiceComponente }}.propiedades.tipo_uso"
                    class="text-blue-600 focus:ring-blue-500">
                <span class="text-sm text-gray-700 dark:text-zinc-300">
                    <i class="fas fa-sticky-note mr-1"></i> Nota fija
                </span>
            </label>
        </div>
    </div>

    <!-- Título -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Título</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            placeholder="Ej: DIAGNÓSTICO"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
    </div>

    @if($tipoUso === 'nota')
        <!-- Contenido de la nota -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Contenido de la nota</label>
            <textarea 
                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.contenido"
                rows="4"
                placeholder="Escriba el texto de la nota..."
                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100"></textarea>
        </div>

        <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded border border-amber-200 dark:border-amber-800">
            <p class="text-xs font-semibold text-amber-800 dark:text-amber-300 mb-1">
                <i class="fas fa-sticky-note mr-1"></i> Nota fija
            </p>
            <p class="text-xs text-amber-700 dark:text-amber-300">
                Este texto se mostrará tal cual en el formulario de captura y en el PDF. El bioquímico <strong>no podrá editarlo</strong>.
            </p>
        </div>
    @else
        <!-- Label -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Etiqueta</label>
            <input 
                type="text" 
                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.label"
                placeholder="Ej: Diagnóstico, Observaciones"
                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
        </div>

        <!-- Contenido de ejemplo -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Contenido de ejemplo (opcional)</label>
            <input 
                type="text" 
                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.placeholder"
                placeholder="Texto de ayuda"
                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
        </div>

        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
            <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">
                <i class="fas fa-info-circle mr-1"></i> ¿Cómo funciona este componente?
            </p>
            <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1 ml-4 list-disc">
                <li>Crea un <strong>campo de texto individual</strong> con una etiqueta visible. El bioquímico escribirá el valor al capturar resultados.</li>
                <li>La etiqueta aparecerá como título del campo. El contenido de ejemplo es solo un texto de ayuda (placeholder) para guiar al bioquímico.</li>
            </ul>
        </div>
    @endif
</div>
