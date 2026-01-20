<!-- Preview de Tabla de Dos Columnas -->
<div class="border border-gray-300 rounded-lg overflow-hidden">
    @if(isset($props['titulo']))
    <div class="bg-white border-b border-gray-300">
        <h4 class="font-bold text-gray-800 text-center py-2">{{ $props['titulo'] }}</h4>
    </div>
    @endif

    <table class="w-full text-sm">
        @foreach($props['secciones'] ?? [] as $seccion)
            <!-- Subtítulo de sección si existe -->
            @if($seccion['subtitulo'] ?? null)
            <tr>
                <td colspan="2" class="bg-gray-100 border border-gray-300 px-3 py-2 font-bold text-center">
                    {{ $seccion['subtitulo'] }}
                </td>
            </tr>
            @endif

            <!-- Campos de la sección -->
            @foreach($seccion['campos'] ?? [] as $campo)
                @if($campo)
                <tr>
                    <td class="border border-gray-300 px-3 py-2 font-semibold bg-gray-50 w-1/3">
                        {{ $campo }}
                    </td>
                    <td class="border border-gray-300 px-3 py-2 text-gray-400 italic">
                        (a completar)
                    </td>
                </tr>
                @endif
            @endforeach
        @endforeach
    </table>
</div>
