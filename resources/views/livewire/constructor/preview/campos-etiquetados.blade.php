<!-- Preview de Campos Etiquetados -->
<div class="space-y-3 p-4 border border-gray-300 dark:border-zinc-700 rounded-lg bg-gray-50 dark:bg-zinc-900">
    @if(isset($props['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-lg">{{ $props['titulo'] }}</h4>
    @endif

    <div class="space-y-2">
        @if(isset($props['campos']) && count($props['campos']) > 0)
            @foreach($props['campos'] as $campo)
                @if($campo)
                <div class="grid grid-cols-12 gap-3 items-center py-1">
                    <span class="col-span-4 font-semibold text-gray-700 dark:text-zinc-300 text-sm break-words">{{ $campo }}:</span>
                    <span class="col-span-8 text-gray-400 dark:text-zinc-500 italic text-sm">(a completar)</span>
                </div>
                @endif
            @endforeach
        @else
            <p class="text-sm text-gray-400 dark:text-zinc-500 italic text-center py-4">
                Agrega campos en las propiedades
            </p>
        @endif
    </div>
</div>
