<!-- Preview de Antibiograma -->
<div class="space-y-3">
    @if(isset($props['titulo']))
    <h4 class="font-bold text-gray-800 text-center">{{ $props['titulo'] }}</h4>
    @endif

    <table class="w-full border border-gray-300 text-sm">
        <thead>
            <tr class="bg-gray-100">
                @foreach($props['columnas'] ?? ['SENSIBLE', 'INTERMEDIO', 'RESISTENTE'] as $columna)
                <th class="border border-gray-300 px-3 py-2 font-semibold">{{ $columna }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if(isset($props['antibioticos']) && count($props['antibioticos']) > 0)
                @foreach($props['antibioticos'] as $antibiotico)
                <tr>
                    @foreach($props['columnas'] ?? ['SENSIBLE', 'INTERMEDIO', 'RESISTENTE'] as $columna)
                    <td class="border border-gray-300 px-3 py-2">
                        @if(isset($antibiotico[strtolower($columna)]))
                            {{ $antibiotico[strtolower($columna)] }}
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="border border-gray-300 px-3 py-4 text-center text-gray-400">
                        Antibióticos se agregarán al llenar el formulario
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
