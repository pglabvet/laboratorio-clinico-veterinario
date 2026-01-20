<!-- Preview de Lista de Items -->
<div class="space-y-2">
    @if(isset($props['titulo']))
    <h4 class="font-bold text-gray-800">{{ $props['titulo'] }}</h4>
    @endif

    <ul class="list-disc list-inside space-y-1 p-3 bg-gray-50 rounded border border-gray-200">
        @if(isset($props['items']) && count($props['items']) > 0)
            @foreach($props['items'] as $item)
            <li class="text-sm text-gray-700">{{ $item }}</li>
            @endforeach
        @else
            <li class="text-sm text-gray-400 italic">Los items se configurarán en las propiedades</li>
        @endif
    </ul>
</div>
