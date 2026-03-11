<!-- Preview de Campo de Texto Simple -->
<div class="space-y-2">
    @if(!empty($props['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-lg">{{ $props['titulo'] }}</h4>
    @endif

    @if(($props['tipo_uso'] ?? 'editable') === 'nota')
        {{-- Nota fija --}}
        <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded border border-amber-200 dark:border-amber-800 min-h-[50px]">
            @if(!empty($props['contenido']))
                <p class="text-sm text-amber-900 dark:text-amber-200 whitespace-pre-line">{{ $props['contenido'] }}</p>
            @else
                <p class="text-sm text-amber-400 dark:text-amber-500 italic">Escriba el contenido de la nota en las propiedades...</p>
            @endif
        </div>
    @else
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">
            {{ $props['label'] ?? 'Campo' }}
        </label>
        
        @if(($props['tipo'] ?? 'texto') === 'numero')
            <input type="number" 
                   class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-gray-50 dark:bg-zinc-900 text-gray-900 dark:text-zinc-100"
                   placeholder="{{ $props['placeholder'] ?? 'Ingrese un número' }}"
                   disabled>
        @elseif(($props['tipo'] ?? 'texto') === 'fecha')
            <input type="date" 
                   class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-gray-50 dark:bg-zinc-900 text-gray-900 dark:text-zinc-100"
                   disabled>
        @else
            <input type="text" 
                   class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-gray-50 dark:bg-zinc-900 text-gray-900 dark:text-zinc-100"
                   placeholder="{{ $props['placeholder'] ?? 'Ingrese texto' }}"
                   disabled>
        @endif
    @endif
</div>
