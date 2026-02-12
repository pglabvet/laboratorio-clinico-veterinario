{{-- Componente PDF: Tabla de Resultados --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(isset($componente['propiedades']['descripcion']) && $componente['propiedades']['descripcion'])
    <p style="font-style: italic; color: #718096; margin-bottom: 8px; font-size: 9px;">
        {{ $componente['propiedades']['descripcion'] }}
    </p>
@endif

@if(!empty($resultado) && is_array($resultado))
<table>
    <thead>
        <tr>
            @foreach($componente['propiedades']['columnas'] ?? [] as $columna)
                <th>{{ $columna['nombre'] ?? '' }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($resultado as $fila)
            @if(is_array($fila))
            <tr>
                <td style="font-weight: bold; background-color: #f7fafc;">{{ $fila['nombre'] ?? '' }}</td>
                @php
                    $columnas = $componente['propiedades']['columnas'] ?? [];
                    $numColumnas = count($columnas) - 1;
                @endphp
                @for($i = 0; $i < $numColumnas; $i++)
                    @php
                        $valor = $fila['col_' . $i] ?? '';
                        $estiloExtra = '';
                        
                        // Si es la columna de resultado (col_0) y existe rango de referencia (col_1)
                        if ($i === 0 && isset($fila['col_1'])) {
                            $resultado = floatval($valor);
                            $rangoRef = $fila['col_1'];
                            
                            // Extraer rango numérico (ej: "100-200" o "100-200 ng/ml")
                            if (preg_match('/(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)/', $rangoRef, $matches)) {
                                $min = floatval($matches[1]);
                                $max = floatval($matches[2]);
                                
                                // Aplicar estilo rojo si está fuera de rango
                                if ($valor !== '' && ($resultado < $min || $resultado > $max)) {
                                    $estiloExtra = 'color: #dc2626; font-weight: bold;';
                                }
                            }
                        }
                        
                        // Si es la columna de rango (col_1), agregar la unidad
                        $mostrarUnidad = ($i === 1 && isset($fila['unidad']) && $fila['unidad']);
                    @endphp
                    <td style="text-align: center; {{ $estiloExtra }}">
                        {{ $valor }}
                        @if($mostrarUnidad)
                            <span style="margin-left: 8px; color: #718096;">{{ $fila['unidad'] }}</span>
                        @endif
                    </td>
                @endfor
            </tr>
            @endif
        @endforeach
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin resultados</p>
@endif
