{{-- Componente PDF: Examen Microscópico --}}
@php
    $filas = $componente['propiedades']['filas'] ?? [];
    $tieneRangos = collect($filas)->contains(fn($f) => !empty($f['rango_referencia']));
@endphp

@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(!empty($resultado) && is_array($resultado))
<table>
    <thead>
        <tr>
            <th style="text-align: left; width: {{ $tieneRangos ? '35%' : '40%' }};">{{ $componente['propiedades']['columna_parametro'] ?? 'PARÁMETRO' }}</th>
            <th style="text-align: center;">{{ $componente['propiedades']['columna_resultado'] ?? 'RESULTADO' }}</th>
            @if($tieneRangos)
            <th style="text-align: center; width: 20%;">{{ $componente['propiedades']['columna_rango'] ?? 'RANGO REF.' }}</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($resultado as $fila)
            @if(is_array($fila) && !empty($fila['resultado']))
            <tr>
                <td style="font-weight: bold;">
                    {{ $fila['parametro'] ?? '' }}
                </td>
                <td style="text-align: center;">
                    {{ $fila['resultado'] ?? '' }}
                </td>
                @if($tieneRangos)
                <td style="text-align: center; font-size: 9px; color: #718096;">
                    {{ $fila['rango_referencia'] ?? '' }}
                </td>
                @endif
            </tr>
            @endif
        @endforeach
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin datos</p>
@endif
