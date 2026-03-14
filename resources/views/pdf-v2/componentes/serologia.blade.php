{{-- Componente PDF V2: Serología (estilo datos sueltos) --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(isset($componente['propiedades']['descripcion']) && $componente['propiedades']['descripcion'])
    <p style="font-style: italic; color: #718096; margin-bottom: 8px; font-size: 8px; text-align: center;">
        {{ $componente['propiedades']['descripcion'] }}
    </p>
@endif

@if(!empty($resultado) && is_array($resultado))
<table>
    <thead>
        <tr>
            <th style="width: 60%;">PRUEBA</th>
            <th style="text-align: center;">RESULTADO</th>
        </tr>
    </thead>
    <tbody>
        @foreach($resultado as $fila)
            @if(is_array($fila) && !empty($fila['valor']))
                @php
                    $esPositivo = str_contains($fila['valor'], 'Positivo');
                @endphp
                <tr>
                    <td style="width: 60%;">
                        {{ $fila['campo'] ?? '' }}
                    </td>
                    <td style="text-align: center;" class="{{ $esPositivo ? 'resultado-critico' : 'resultado-alerta' }}">
                        {{ $fila['valor'] }}
                    </td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin datos</p>
@endif
