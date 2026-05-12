<!-- Preview de Tabla de Dos Columnas -->
<div class="border border-gray-300 dark:border-zinc-700 rounded-lg overflow-hidden">
    @if(isset($props['titulo']))
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-300 dark:border-zinc-700">
        <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center py-2">{{ $props['titulo'] }}</h4>
    </div>
    @endif

    <table class="w-full text-sm">
        @foreach($props['secciones'] ?? [] as $secIndex => $seccion)
            <!-- Subtítulo de sección si existe -->
            @if($seccion['subtitulo'] ?? null)
            <tr wire:key="preview-sec-{{ $index }}-{{ $secIndex }}-sub">
                <td colspan="2" class="bg-gray-100 dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 px-3 py-2 font-bold text-center text-gray-900 dark:text-zinc-100">
                    {{ $seccion['subtitulo'] }}
                </td>
            </tr>
            @endif

            <!-- Campos de la sección -->
            @foreach($seccion['campos'] ?? [] as $fieldIndex => $campo)
                <tr wire:key="preview-sec-{{ $index }}-{{ $secIndex }}-field-{{ $fieldIndex }}">
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold bg-gray-50 dark:bg-zinc-900 w-1/3 text-gray-900 dark:text-zinc-100">
                        {{ $campo['nombre'] ?: '(sin nombre)' }}
                    </td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-gray-400 dark:text-zinc-500 italic">
                        @if(($campo['tipo_input'] ?? 'input') === 'select')
                            (seleccionar)
                        @else
                            (a completar)
                        @endif
                    </td>
                </tr>
            @endforeach
        @endforeach
    </table>
</div>
