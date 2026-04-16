{{-- Componente PDF V2: Tabla Dos Columnas (sin bordes pesados) --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@php
    $col1 = $componente['propiedades']['columna1_nombre'] ?? 'Campo';
    $col2 = $componente['propiedades']['columna2_nombre'] ?? 'Valor';
    $tieneSecciones = false;
    if (!empty($resultado) && is_array($resultado)) {
        $tieneSecciones = collect($resultado)->contains(fn($f) => is_array($f) && !empty($f['seccion']));
    }
@endphp

@if(!empty($resultado) && is_array($resultado))
    @if(!$tieneSecciones)
        {{-- Sin secciones: tabla simple con encabezados --}}
        <table>
            <thead>
                <tr>
                    <th style="width: 40%; font-size: 10px; color: #1e3a5f;">{{ $col1 }}</th>
                    <th style="width: 60%; font-size: 10px; color: #1e3a5f;">{{ $col2 }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultado as $fila)
                    @if(is_array($fila) && isset($fila['valor']) && $fila['valor'] !== '' && $fila['valor'] !== null)
                    @php $valTdc = trim($fila['valor'] ?? $fila['resultado'] ?? ''); @endphp
                    <tr>
                        <td>
                            {{ $fila['campo'] ?? $fila['nombre'] ?? $fila['etiqueta'] ?? '' }}
                        </td>
                        <td @if(in_array($valTdc, ['Intermedio', 'Resistente'])) class="resultado-critico" @endif>{{ $valTdc }}</td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @else
        {{-- Con secciones: subtítulo + encabezados repetidos por sección --}}
        @php
            $secciones = [];
            $currentSec = '__sin_seccion__';
            foreach ($resultado as $fila) {
                if (is_array($fila) && !empty($fila['seccion'])) {
                    $currentSec = $fila['seccion'];
                }
                if (is_array($fila) && isset($fila['valor']) && $fila['valor'] !== '' && $fila['valor'] !== null) {
                    $secciones[$currentSec][] = $fila;
                }
            }
        @endphp

        @foreach($secciones as $secNombre => $filas)
            @if($secNombre !== '__sin_seccion__')
                <div style="font-weight: bold; text-align: left; padding: 14px 0 4px 0; font-size: 10px; color: #1e3a5f; text-transform: uppercase; letter-spacing: 0.5px;">
                    {{ $secNombre }}
                </div>
            @endif
            <table>
                <thead>
                    <tr>
                        <th style="width: 40%; font-size: 10px; color: #1e3a5f;">{{ $col1 }}</th>
                        <th style="width: 60%; font-size: 10px; color: #1e3a5f;">{{ $col2 }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($filas as $fila)
                    @php $valTdc = trim($fila['valor'] ?? $fila['resultado'] ?? ''); @endphp
                    <tr>
                        <td>
                            {{ $fila['campo'] ?? $fila['nombre'] ?? $fila['etiqueta'] ?? '' }}
                        </td>
                        <td @if(in_array($valTdc, ['Intermedio', 'Resistente'])) class="resultado-critico" @endif>{{ $valTdc }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endif
@else
<p style="color: #718096; font-style: italic;">Sin datos</p>
@endif
