<!-- Preview de Serología -->
<div class="border border-gray-300 dark:border-zinc-700 rounded-lg overflow-hidden">
    @if(isset($props['titulo']))
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-300 dark:border-zinc-700">
        <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center py-2">{{ $props['titulo'] }}</h4>
    </div>
    @endif

    @if(isset($props['descripcion']) && $props['descripcion'])
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-300 dark:border-zinc-700">
        <p class="text-xs text-gray-500 dark:text-zinc-400 text-center py-1 italic">{{ $props['descripcion'] }}</p>
    </div>
    @endif

    <table class="w-full text-sm">
        @foreach($props['campos'] ?? [] as $campo)
            @if($campo)
            <tr>
                <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold bg-gray-50 dark:bg-zinc-900 w-2/3 text-gray-900 dark:text-zinc-100">
                    {{ $campo }}
                </td>
                <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center text-gray-400 dark:text-zinc-500 italic">
                    Negativo (-) / Positivo (+)
                </td>
            </tr>
            @endif
        @endforeach
    </table>

    @if(empty(array_filter($props['campos'] ?? [])))
    <div class="p-4 text-center text-gray-400 dark:text-zinc-500 text-sm italic">
        Agrega pruebas serológicas para ver la vista previa
    </div>
    @endif
</div>
