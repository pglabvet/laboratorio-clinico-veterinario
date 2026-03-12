<!-- Preview de Coproparasitología Seriado -->
@php
    $numMuestras = (int) ($props['num_muestras'] ?? 3);
    $mostrarFecha = $props['mostrar_fecha'] ?? true;
    $ordinalLabels = ['1ra', '2da', '3ra', '4ta', '5ta', '6ta'];
@endphp

<div class="overflow-x-auto">
    @if(!empty($props['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-lg mb-3">{{ $props['titulo'] }}</h4>
    @endif
    
    <table class="w-full border-collapse border border-gray-300 dark:border-zinc-700 text-sm">
        {{-- Header: columna vacía + una columna por muestra --}}
        <thead>
            <tr>
                <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-zinc-100"></th>
                @for($m = 0; $m < $numMuestras; $m++)
                <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 text-center">
                    <div>{{ $ordinalLabels[$m] ?? ($m + 1) . 'ta' }} MUESTRA</div>
                    @if($mostrarFecha)
                    <div class="text-xs text-gray-400 dark:text-zinc-500 italic font-normal">(fecha)</div>
                    @endif
                </th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($props['secciones'] ?? [] as $seccion)
                {{-- Subtítulo de sección --}}
                @if($seccion['subtitulo'] ?? null)
                <tr>
                    <td colspan="{{ $numMuestras + 1 }}" class="bg-gray-100 dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 px-3 py-2 font-bold text-center text-gray-900 dark:text-zinc-100">
                        {{ $seccion['subtitulo'] }}
                    </td>
                </tr>
                @endif

                {{-- Campos --}}
                @foreach($seccion['campos'] ?? [] as $campo)
                    @if(!empty($campo['nombre']))
                    <tr>
                        <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold bg-gray-50 dark:bg-zinc-900 text-gray-900 dark:text-zinc-100">
                            {{ $campo['nombre'] }}
                        </td>
                        @for($m = 0; $m < $numMuestras; $m++)
                        <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-gray-400 dark:text-zinc-500 italic text-center">
                            @if(($campo['tipo_input'] ?? 'input') === 'select')
                                (seleccionar)
                            @else
                                (a completar)
                            @endif
                        </td>
                        @endfor
                    </tr>
                    @endif
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>
