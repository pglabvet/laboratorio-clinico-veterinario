{{-- Componente PDF: Tabla Hematológica --}}
@php
    $parametros = $resultado['parametros'] ?? [];
    $diferenciales = $resultado['diferenciales'] ?? [];
    $indices = $resultado['indices'] ?? [];
    $maxRows = max(count($parametros), count($diferenciales));
@endphp

@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(!empty($parametros) || !empty($diferenciales) || !empty($indices))
<table style="font-size: 8px;">
    <thead>
        <tr style="background-color: #9f7aea; color: white;">
            <th colspan="5" style="text-align: center;">CUADRO HEMÁTICO</th>
            <th colspan="4" style="text-align: center;">DIFERENCIAL LEUCOCITARIO</th>
        </tr>
        <tr style="background-color: #edf2f7;">
            <th>Parámetro</th>
            <th>Resultado</th>
            <th>Unidad</th>
            <th colspan="2">Valores Ref.</th>
            <th>Tipo</th>
            <th>% Rel.</th>
            <th>Abs.</th>
            <th>Ref.</th>
        </tr>
    </thead>
    <tbody>
        @for($i = 0; $i < $maxRows; $i++)
            <tr>
                {{-- Parámetros principales --}}
                @if($i < count($parametros))
                    @php $param = $parametros[$i]; @endphp
                    <td style="font-weight: bold; background-color: #faf5ff;">{{ $param['nombre'] ?? '' }}</td>
                    <td style="text-align: center;">{{ $param['resultado'] ?? '' }}</td>
                    <td style="text-align: center;">{{ $param['unidad'] ?? '' }}</td>
                    <td style="text-align: center; color: #718096;" colspan="2">
                        {{ isset($componente['propiedades']['parametros_principales'][$i]) ? 
                           ($componente['propiedades']['parametros_principales'][$i]['ref_min'] ?? '') . ' - ' . 
                           ($componente['propiedades']['parametros_principales'][$i]['ref_max'] ?? '') : '' }}
                    </td>
                @else
                    <td colspan="5"></td>
                @endif
                
                {{-- Diferenciales --}}
                @if($i < count($diferenciales))
                    @php $dif = $diferenciales[$i]; @endphp
                    <td style="font-weight: bold; background-color: #faf5ff;">{{ $dif['nombre'] ?? '' }}</td>
                    <td style="text-align: center;">{{ $dif['valor_rel'] ?? '' }}%</td>
                    <td style="text-align: center;">{{ $dif['valor_abs'] ?? '' }}</td>
                    <td style="text-align: center; color: #718096;">
                        {{ isset($componente['propiedades']['diferenciales'][$i]) ? 
                           ($componente['propiedades']['diferenciales'][$i]['ref_rel_min'] ?? '') . '-' . 
                           ($componente['propiedades']['diferenciales'][$i]['ref_rel_max'] ?? '') : '' }}
                    </td>
                @else
                    <td colspan="4"></td>
                @endif
            </tr>
        @endfor
        
        {{-- Índices Eritrocitarios --}}
        @if(!empty($indices))
            <tr style="background-color: #faf5ff;">
                <td colspan="9" style="font-weight: bold; text-align: center;">ÍNDICES ERITROCITARIOS</td>
            </tr>
            @foreach($indices as $i => $indice)
            <tr>
                <td colspan="2" style="font-weight: bold;">{{ $indice['nombre'] ?? '' }}</td>
                <td style="text-align: center;">{{ $indice['resultado'] ?? '' }}</td>
                <td>{{ $indice['unidad'] ?? '' }}</td>
                <td colspan="5" style="color: #718096;">
                    {{ isset($componente['propiedades']['indices'][$i]) ? 
                       ($componente['propiedades']['indices'][$i]['referencia'] ?? '') : '' }}
                </td>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin datos hematológicos</p>
@endif
