<!-- Preview de Antibiograma -->
<div class="space-y-3">
    @if(isset($props['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center">{{ $props['titulo'] }}</h4>
    @endif

    <table class="w-full border border-gray-300 dark:border-zinc-700 text-sm">
        <thead>
            <tr class="bg-gray-100 dark:bg-zinc-900">
                @foreach($props['columnas'] ?? ['SENSIBLE', 'INTERMEDIO', 'RESISTENTE'] as $columna)
                <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold text-gray-900 dark:text-zinc-100">{{ $columna }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if(isset($props['antibioticos']) && count($props['antibioticos']) > 0)
                @foreach($props['antibioticos'] as $antibiotico)
                <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700/50">
                    @foreach($props['columnas'] ?? ['SENSIBLE', 'INTERMEDIO', 'RESISTENTE'] as $columna)
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-gray-900 dark:text-zinc-100">
                        @if(isset($antibiotico[strtolower($columna)]))
                            {{ $antibiotico[strtolower($columna)] }}
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="border border-gray-300 dark:border-zinc-700 px-3 py-4 text-center text-gray-400 dark:text-zinc-500">
                        Antibióticos se agregarán al llenar el formulario
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
