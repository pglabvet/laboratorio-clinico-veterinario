{{-- Componente PDF V2: Antibiograma (3 columnas limpias) --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(!empty($resultado) && is_array($resultado))
<table>
    <thead>
        <tr>
            <th style="width: 33%; text-align: center;">SENSIBLE</th>
            <th style="width: 33%; text-align: center;">INTERMEDIO</th>
            <th style="width: 33%; text-align: center;">RESISTENTE</th>
        </tr>
    </thead>
    <tbody>
        @foreach($resultado as $fila)
            @if(!empty($fila['sensible']) || !empty($fila['intermedio']) || !empty($fila['resistente']))
            <tr>
                <td style="text-transform: uppercase; text-align: center;">{{ $fila['sensible'] ?? '' }}</td>
                <td style="text-transform: uppercase; text-align: center;">{{ $fila['intermedio'] ?? '' }}</td>
                <td style="text-transform: uppercase; text-align: center;">{{ $fila['resistente'] ?? '' }}</td>
            </tr>
            @endif
        @endforeach
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin datos de antibiograma</p>
@endif
