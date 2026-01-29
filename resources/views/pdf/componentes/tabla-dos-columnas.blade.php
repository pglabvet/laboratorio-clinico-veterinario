{{-- Componente PDF: Tabla Dos Columnas --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(!empty($resultado) && is_array($resultado))
<table>
    <thead>
        <tr>
            <th style="width: 50%;">{{ $componente['propiedades']['columna1_nombre'] ?? 'Campo' }}</th>
            <th style="width: 50%;">{{ $componente['propiedades']['columna2_nombre'] ?? 'Valor' }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $currentSection = null;
        @endphp
        @foreach($resultado as $fila)
            @if(is_array($fila) && !empty($fila['valor']))
                {{-- Mostrar subtítulo de sección si cambió --}}
                @if(isset($fila['seccion']) && $fila['seccion'] !== $currentSection && !empty($fila['seccion']))
                    @php
                        $currentSection = $fila['seccion'];
                    @endphp
                    <tr>
                        <td colspan="2" style="background-color: #e2e8f0; font-weight: bold; text-align: center; padding: 8px; font-size: 10px;">
                            {{ $currentSection }}
                        </td>
                    </tr>
                @endif
                
                {{-- Fila de datos --}}
                <tr>
                    <td style="font-weight: bold; background-color: #f7fafc;">
                        {{ $fila['campo'] ?? $fila['nombre'] ?? $fila['etiqueta'] ?? '' }}
                    </td>
                    <td>{{ $fila['valor'] ?? $fila['resultado'] ?? '' }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin datos</p>
@endif
