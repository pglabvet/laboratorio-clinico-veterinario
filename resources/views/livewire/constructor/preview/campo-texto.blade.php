<!-- Preview de Campo de Texto Simple -->
<div class="space-y-2">
    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">
        {{ $props['label'] ?? 'Campo' }}
    </label>
    
    @if($props['tipo'] === 'numero')
        <input type="number" 
               class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-gray-50 dark:bg-zinc-900 text-gray-900 dark:text-zinc-100"
               placeholder="{{ $props['placeholder'] ?? 'Ingrese un número' }}"
               disabled>
    @elseif($props['tipo'] === 'fecha')
        <input type="date" 
               class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-gray-50 dark:bg-zinc-900 text-gray-900 dark:text-zinc-100"
               disabled>
    @else
        <input type="text" 
               class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-gray-50 dark:bg-zinc-900 text-gray-900 dark:text-zinc-100"
               placeholder="{{ $props['placeholder'] ?? 'Ingrese texto' }}"
               disabled>
    @endif
</div>
