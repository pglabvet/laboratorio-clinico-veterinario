{{-- Componente PDF: Gráfica de Líneas --}}
{{-- Nota: DomPDF no soporta gráficas interactivas, se muestra como tabla de datos --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(!empty($resultado) && is_array($resultado))
    @if(isset($resultado['puntos']) && is_array($resultado['puntos']))
    <table style="font-size: 9px;">
        <thead>
            <tr>
                <th>Punto</th>
                <th>X</th>
                <th>Y</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resultado['puntos'] as $i => $punto)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ is_array($punto) ? ($punto['x'] ?? $punto[0] ?? '') : '' }}</td>
                <td>{{ is_array($punto) ? ($punto['y'] ?? $punto[1] ?? '') : '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="text-content">
        {{ json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
    </div>
    @endif
@else
<p style="color: #718096; font-style: italic;">Sin datos de gráfica</p>
@endif
