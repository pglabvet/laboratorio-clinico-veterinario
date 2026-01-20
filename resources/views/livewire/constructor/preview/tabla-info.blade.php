<!-- Preview de Tabla de Información -->
<div class="space-y-3">
    @if(isset($props['titulo']))
    <h4 class="font-bold text-gray-800 text-center">{{ $props['titulo'] }}</h4>
    @endif

    <table class="w-full border border-gray-300 text-sm">
        @if(isset($props['filas']))
            @foreach(array_chunk($props['filas'], $props['columnas'] ?? 3) as $grupo)
            <tr>
                @foreach($grupo as $fila)
                <td class="border border-gray-300 px-3 py-2 font-semibold bg-gray-100">
                    {{ $fila['label'] ?? 'CAMPO' }}
                </td>
                <td class="border border-gray-300 px-3 py-2">
                    {{ $fila['placeholder'] ?? '' }}
                </td>
                @endforeach
                
                @if(count($grupo) < ($props['columnas'] ?? 3))
                    @for($i = count($grupo); $i < ($props['columnas'] ?? 3); $i++)
                    <td class="border border-gray-300 px-3 py-2" colspan="2"></td>
                    @endfor
                @endif
            </tr>
            @endforeach
        @endif
    </table>
</div>
