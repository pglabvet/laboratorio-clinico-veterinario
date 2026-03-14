<!-- Preview de Campos Etiquetados -->
<div class="border border-gray-300 dark:border-zinc-700 rounded-lg overflow-hidden">
    @if(isset($props['titulo']))
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-300 dark:border-zinc-700">
        <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center py-2">{{ $props['titulo'] }}</h4>
    </div>
    @endif

    <table class="w-full text-sm">
        @if(!empty($props['columnas']))
        <thead>
            <tr class="bg-gray-100 dark:bg-zinc-900">
                @foreach($props['columnas'] as $columna)
                <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold text-gray-900 dark:text-zinc-100">
                    {{ $columna['nombre'] ?? '' }}
                </th>
                @endforeach
            </tr>
        </thead>
        @endif
        <tbody>
        @if(isset($props['campos']) && count($props['campos']) > 0)
            @foreach($props['campos'] as $campo)
                @if($campo)
                <tr>
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold bg-gray-50 dark:bg-zinc-900 text-gray-900 dark:text-zinc-100">{{ $campo }}</td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center text-gray-400 dark:text-zinc-500 italic">(a completar)</td>
                </tr>
                @endif
            @endforeach
        @else
            <tr>
                <td colspan="2" class="text-sm text-gray-400 dark:text-zinc-500 italic text-center py-4">
                    Agrega campos en las propiedades
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
