<!-- Preview de Lista de Items -->
<div class="space-y-2">
    @if(isset($props['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100">{{ $props['titulo'] }}</h4>
    @endif

    <ul class="list-disc list-inside space-y-1 p-3 bg-gray-50 dark:bg-zinc-900 rounded border border-gray-200 dark:border-zinc-700">
        @if(isset($props['items']) && count($props['items']) > 0)
            @foreach($props['items'] as $item)
            <li class="text-sm text-gray-700 dark:text-zinc-300">{{ $item }}</li>
            @endforeach
        @else
            <li class="text-sm text-gray-400 dark:text-zinc-500 italic">Los items se configurarán en las propiedades</li>
        @endif
    </ul>
</div>
