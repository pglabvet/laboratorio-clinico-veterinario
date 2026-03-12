{{-- Componente PDF: Coproparasitología Seriado --}}
@php
    $numMuestras = (int) ($componente['propiedades']['num_muestras'] ?? 3);
    $mostrarFecha = $componente['propiedades']['mostrar_fecha'] ?? true;
    $ordinalLabels = ['1ra', '2da', '3ra', '4ta', '5ta', '6ta'];

    $campos = [];
    $fechas = [];

    if (!empty($resultado) && is_array($resultado)) {
        $campos = $resultado['campos'] ?? [];
        $fechas = $resultado['fechas'] ?? [];
        // Normalize from object to array if needed
        if (!is_array($campos) || (count($campos) > 0 && !isset($campos[0]))) {
            $campos = array_values($campos);
        }
        if (!is_array($fechas)) {
            $fechas = array_values((array) $fechas);
        }
    }

    // Index campos by name for lookup
    $camposPorNombre = collect($campos)->keyBy('campo');
@endphp

@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(!empty($campos))
<table>
    <thead>
        <tr>
            <th style="width: {{ 100 / ($numMuestras + 1) }}%;"></th>
            @for($m = 0; $m < $numMuestras; $m++)
            <th style="width: {{ 100 / ($numMuestras + 1) }}%; text-align: center;">
                {{ $ordinalLabels[$m] ?? ($m + 1) . 'ta' }} MUESTRA
                @if($mostrarFecha && !empty($fechas[$m]))
                <br><span style="font-weight: normal; font-size: 8px;">{{ \Carbon\Carbon::parse($fechas[$m])->format('d/m/Y') }}</span>
                @endif
            </th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @php $currentSection = null; @endphp
        @foreach($componente['propiedades']['secciones'] ?? [] as $seccion)
            {{-- Subtítulo de sección --}}
            @if(($seccion['subtitulo'] ?? '') !== '' && $seccion['subtitulo'] !== $currentSection)
                @php $currentSection = $seccion['subtitulo']; @endphp
                <tr>
                    <td colspan="{{ $numMuestras + 1 }}" style="background-color: #e2e8f0; font-weight: bold; text-align: center; padding: 8px; font-size: 10px;">
                        {{ $currentSection }}
                    </td>
                </tr>
            @endif

            @foreach($seccion['campos'] ?? [] as $campo)
                @php
                    $nombreCampo = $campo['nombre'] ?? '';
                    $datosCampo = $camposPorNombre->get($nombreCampo);
                    $valores = $datosCampo['valores'] ?? [];
                    if (!is_array($valores)) {
                        $valores = array_values((array) $valores);
                    }
                    // Check if at least one muestra has a value
                    $tieneAlgunValor = collect($valores)->contains(fn($v) => !empty($v));
                @endphp
                @if($nombreCampo && $tieneAlgunValor)
                <tr>
                    <td style="font-weight: bold; background-color: #f7fafc;">
                        {{ $nombreCampo }}
                    </td>
                    @for($m = 0; $m < $numMuestras; $m++)
                    <td style="text-align: center;">
                        {{ $valores[$m] ?? '' }}
                    </td>
                    @endfor
                </tr>
                @endif
            @endforeach
        @endforeach
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin datos</p>
@endif
