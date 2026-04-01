<!-- Preview de Texto Libre -->
<div class="space-y-2">
    @if(isset($props['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 {{ ($props['alineacion_titulo'] ?? 'left') === 'center' ? 'text-center' : 'text-left' }}">
        {{ $props['titulo'] }}
    </h4>
    @endif

    <div class="p-3 bg-gray-50 dark:bg-zinc-900 rounded border border-gray-200 dark:border-zinc-700 min-h-[80px]">
        @if(!empty($props['contenido']))
            @if(($props['formato'] ?? 'parrafos') === 'lista')
                <ul class="list-disc list-inside space-y-1">
                    @foreach(explode("\n", $props['contenido']) as $item)
                        @if(trim($item))
                        <li class="text-sm text-gray-700 dark:text-zinc-300">{{ trim($item) }}</li>
                        @endif
                    @endforeach
                </ul>
            @else
                {{-- Renderizar HTML si contiene tags, sino texto plano --}}
                @if(str_contains($props['contenido'], '<'))
                    <div class="text-sm text-gray-700 dark:text-zinc-300 prose prose-sm dark:prose-invert max-w-none">
                        {!! Str::of($props['contenido'])->stripTags(['p', 'strong', 'em', 'u', 's', 'ul', 'ol', 'li', 'br']) !!}
                    </div>
                @else
                    <div class="text-sm text-gray-700 dark:text-zinc-300 whitespace-pre-line">{{ $props['contenido'] }}</div>
                @endif
            @endif
        @else
            <p class="text-sm text-gray-400 dark:text-zinc-500 italic">El texto se ingresará al llenar el formulario...</p>
        @endif
    </div>
</div>
