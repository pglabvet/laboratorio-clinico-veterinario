<!-- Preview de Campo de Texto Simple -->
<div class="space-y-2">
    <label class="block text-sm font-medium text-gray-700">
        {{ $props['label'] ?? 'Campo' }}
    </label>
    
    @if($props['tipo'] === 'numero')
        <input type="number" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
               placeholder="{{ $props['placeholder'] ?? 'Ingrese un número' }}"
               disabled>
    @elseif($props['tipo'] === 'fecha')
        <input type="date" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
               disabled>
    @else
        <input type="text" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
               placeholder="{{ $props['placeholder'] ?? 'Ingrese texto' }}"
               disabled>
    @endif
</div>
