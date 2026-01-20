<!-- Preview de Texto Libre -->
<div class="space-y-2">
    @if(isset($props['titulo']))
    <h4 class="font-bold text-gray-800">{{ $props['titulo'] }}</h4>
    @endif

    <div class="p-3 bg-gray-50 rounded border border-gray-200 min-h-[80px]">
        @if(!empty($props['contenido']))
            @if($props['formato'] === 'lista')
                <ul class="list-disc list-inside space-y-1">
                    @foreach(explode("\n", $props['contenido']) as $item)
                        @if(trim($item))
                        <li class="text-sm text-gray-700">{{ trim($item) }}</li>
                        @endif
                    @endforeach
                </ul>
            @else
                <div class="text-sm text-gray-700 whitespace-pre-line">{{ $props['contenido'] }}</div>
            @endif
        @else
            <p class="text-sm text-gray-400 italic">El texto se ingresará al llenar el formulario...</p>
        @endif
    </div>
</div>
