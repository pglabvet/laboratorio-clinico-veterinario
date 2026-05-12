{{-- Componente PDF V2: Coproparasitología Seriado --}}
@php
    $numMuestras = (int) ($componente['propiedades']['num_muestras'] ?? 3);
    $mostrarFecha = $componente['propiedades']['mostrar_fecha'] ?? true;
    $ordinalLabels = ['1ra', '2da', '3ra', '4ta', '5ta', '6ta'];

    $campos = [];
    $fechas = [];

    if (!empty($resultado) && is_array($resultado)) {
        $campos = $resultado['campos'] ?? [];
        $fechas = $resultado['fechas'] ?? [];
        if (!is_array($campos) || (count($campos) > 0 && !isset($campos[0]))) {
            $campos = array_values($campos);
        }
        if (!is_array($fechas)) {
            $fechas = array_values((array) $fechas);
        }
    }

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
                <br><span style="font-weight: normal; font-size: 7px; color: #718096;">{{ \Carbon\Carbon::parse($fechas[$m])->format('d/m/Y') }}</span>
                @endif
            </th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @php $currentSection = null; @endphp
        @foreach($componente['propiedades']['secciones'] ?? [] as $seccion)
            @if(($seccion['subtitulo'] ?? '') !== '' && $seccion['subtitulo'] !== $currentSection)
                @php $currentSection = $seccion['subtitulo']; @endphp
                <tr>
                    <td colspan="{{ $numMuestras + 1 }}" style="font-weight: bold; text-align: left; padding: 8px 0; font-size: 10px; color: #1e3a5f;">
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
                    $tieneAlgunValor = collect($valores)->contains(fn($v) => !empty($v));
                @endphp
                @if($nombreCampo && $tieneAlgunValor)
                <tr>
                    <td>
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
