{{-- Componente PDF V2: Panel qPCR — Una hoja completa por patógeno --}}
@php
    $propiedades    = $componente['propiedades'] ?? [];
    $patogenos      = $propiedades['patogenos']  ?? [];
    $datosPatogenos = is_array($resultado) ? $resultado : [];
    $chartImages    = $chartImages ?? [];
    $patogenosRenderizados = 0;
@endphp

@foreach($patogenos as $pi => $patogeno)
@php
    $camposP         = $patogeno['campos']           ?? [];
    $interp          = $patogeno['interpretaciones'] ?? [];
    $umbralValor     = $patogeno['umbral_valor']     ?? 10;
    $umbralExponente = $patogeno['umbral_exponente'] ?? 5;
    $unidad          = $patogeno['unidad']           ?? 'copias/ml';
    $mostrarGrafica  = $patogeno['mostrar_grafica']  ?? true;
    $chartImg        = $chartImages[$pi]             ?? null;
    $siglas          = $patogeno['siglas']           ?? 'PATÓGENO';
    $nombreCompleto  = $patogeno['nombre_completo']  ?? '';

    // Datos del patógeno: nueva estructura valores[pi][ci] = {etiqueta, tipo, valor}
    $datosP = $datosPatogenos[$pi] ?? [];

    // Verificar si este patógeno tiene al menos un campo con valor ingresado
    $tieneValores = false;
    foreach ($camposP as $ci => $campoDef) {
        $datoC = $datosP[$ci] ?? null;
        if (is_array($datoC) && isset($datoC['valor']) && $datoC['valor'] !== '' && $datoC['valor'] !== null) {
            $tieneValores = true;
            break;
        } elseif (is_string($datoC) && $datoC !== '') {
            $tieneValores = true;
            break;
        }
    }
@endphp

{{-- Saltar patógenos sin datos ingresados --}}
@if(!$tieneValores)
    @continue
@endif

@php
    $esElPrimeroRenderizado = ($patogenosRenderizados === 0);
    $patogenosRenderizados++;

    // Construir lista de campos con sus valores y calcular interpretación
    $camposConValor   = [];
    $resultadoDetectado = '';
    $cargaViral = 0;

    foreach ($camposP as $ci => $campoDef) {
        $datoC = $datosP[$ci] ?? null;
        $valor = '';
        if (is_array($datoC) && isset($datoC['valor'])) {
            $valor = $datoC['valor'];
        } elseif (is_string($datoC)) {
            $valor = $datoC;
        }
        $tipo = $campoDef['tipo'] ?? 'texto';
        $camposConValor[] = [
            'etiqueta' => $campoDef['etiqueta'] ?? 'Campo',
            'tipo'     => $tipo,
            'valor'    => $valor,
        ];
        if ($tipo === 'select')              $resultadoDetectado = $valor;
        if ($tipo === 'numero_cientifico')   $cargaViral = floatval($valor);
    }

    // Calcular interpretación (igual que carga-viral)
    $interpretacion = 'no_detectado';
    if ($resultadoDetectado === 'DETECTADO (+)' && $cargaViral > 0) {
        $interpretacion = $cargaViral < $umbralValor ? 'regresivo' : 'progresivo';
    }

@endphp

{{-- Salto de página entre patógenos (no antes del primero renderizado) --}}
@if(!$esElPrimeroRenderizado)
    <div style="page-break-before: always;"></div>
@endif

{{-- ══ HOJA DEL PATÓGENO (mismos estilos que carga-viral.blade.php) ══ --}}

{{-- Título --}}
@if(!empty($patogeno['titulo']))
    <div class="component-title">{{ $patogeno['titulo'] }}</div>
@endif

{{-- Nombre completo --}}
@if($nombreCompleto)
    <div style="text-align: left; font-size: 10px; color: #4a5568; margin-bottom: 10px;">
        {{ $nombreCompleto }}
    </div>
@endif

{{-- Tabla de campos con valores --}}
@if(!empty($camposConValor))
<table style="margin-bottom: 12px;">
    <tbody>
        @foreach($camposConValor as $campo)
        <tr>
            <td style="width: 35%;">{{ $campo['etiqueta'] }}</td>
            <td style="width: 65%;">
                @if($campo['tipo'] === 'numero_cientifico' && $campo['valor'] !== '')
                    {{ $campo['valor'] }} × 10<sup>{{ $umbralExponente }}</sup> {{ $unidad }}
                @else
                    {{ $campo['valor'] ?: 'N/A' }}
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Interpretación en columnas (copia exacta de carga-viral.blade.php) --}}
<div style="margin: 10px 0; padding: 8px 0; page-break-inside: avoid;">
    <div style="font-weight: bold; font-size: 9px; margin-bottom: 8px; text-transform: uppercase; color: #1e3a5f; text-align: center; border-bottom: 2px solid #1e3a5f; padding-bottom: 5px;">
        INTERPRETACIÓN DE RESULTADOS
    </div>

    <table style="width: 100%; border-collapse: collapse; table-layout: fixed; page-break-inside: avoid;">
        <tr>
            {{-- No Detectado --}}
            <td style="width: 33.33%; vertical-align: top; padding: 3px; border: none; border-bottom: none;">
                @if($interpretacion === 'no_detectado')
                    <div style="padding: 5px; border-left: 3px solid #22c55e;">
                        <div style="font-weight: bold; color: #15803d; font-size: 8px;">
                            {{ $siglas }} NO DETECTADO:
                        </div>
                        <div style="font-size: 7px; color: #166534; margin-top: 2px;">
                            {{ $interp['no_detectado']['descripcion'] ?? 'Sin detección de ADN viral en la muestra analizada.' }}
                        </div>
                    </div>
                @else
                    <div style="padding: 5px; border-left: 2px solid #d1d5db; opacity: 0.4;">
                        <div style="font-weight: bold; font-size: 7px; color: #9ca3af;">
                            {{ $siglas }} NO DETECTADO:
                        </div>
                        <div style="font-size: 6px; color: #9ca3af; margin-top: 2px;">
                            {{ $interp['no_detectado']['descripcion'] ?? 'Sin detección de ADN viral.' }}
                        </div>
                    </div>
                @endif
            </td>

            {{-- Regresivo --}}
            <td style="width: 33.33%; vertical-align: top; padding: 3px; border: none; border-bottom: none;">
                @if($interpretacion === 'regresivo')
                    <div style="padding: 5px; border-left: 3px solid #eab308;">
                        <div style="font-weight: bold; color: #a16207; font-size: 8px;">
                            {{ $siglas }} DETECTADO - REGRESIVA:
                        </div>
                        <div style="font-size: 7px; color: #854d0e; margin-top: 2px;">
                            {{ $interp['regresivo']['descripcion'] ?? 'Carga viral baja, posible fase de resolución.' }}
                        </div>
                        <div style="font-size: 6px; color: #ca8a04; margin-top: 2px;">
                            ADN &lt; {{ $umbralValor }} × 10<sup>{{ $umbralExponente }}</sup> {{ $unidad }}
                        </div>
                    </div>
                @else
                    <div style="padding: 5px; border-left: 2px solid #d1d5db; opacity: 0.4;">
                        <div style="font-weight: bold; font-size: 7px; color: #9ca3af;">
                            {{ $siglas }} - REGRESIVA:
                        </div>
                        <div style="font-size: 6px; color: #9ca3af; margin-top: 2px;">
                            {{ $interp['regresivo']['descripcion'] ?? 'Fase de resolución.' }}
                        </div>
                        <div style="font-size: 6px; color: #9ca3af; margin-top: 2px;">
                            ADN &lt; {{ $umbralValor }} × 10<sup>{{ $umbralExponente }}</sup>
                        </div>
                    </div>
                @endif
            </td>

            {{-- Progresivo --}}
            <td style="width: 33.33%; vertical-align: top; padding: 3px; border: none; border-bottom: none;">
                @if($interpretacion === 'progresivo')
                    <div style="padding: 5px; border-left: 3px solid #ef4444;">
                        <div style="font-weight: bold; color: #991b1b; font-size: 8px;">
                            {{ $siglas }} DETECTADO - PROGRESIVA:
                        </div>
                        <div style="font-size: 7px; color: #991b1b; margin-top: 2px;">
                            {{ $interp['progresivo']['descripcion'] ?? 'Carga viral alta, infección activa.' }}
                        </div>
                        <div style="font-size: 6px; color: #dc2626; margin-top: 2px;">
                            ADN &gt; {{ $umbralValor }} × 10<sup>{{ $umbralExponente }}</sup> {{ $unidad }}
                        </div>
                    </div>
                @else
                    <div style="padding: 5px; border-left: 2px solid #d1d5db; opacity: 0.4;">
                        <div style="font-weight: bold; font-size: 7px; color: #9ca3af;">
                            {{ $siglas }} - PROGRESIVA:
                        </div>
                        <div style="font-size: 6px; color: #9ca3af; margin-top: 2px;">
                            {{ $interp['progresivo']['descripcion'] ?? 'Infección activa.' }}
                        </div>
                        <div style="font-size: 6px; color: #9ca3af; margin-top: 2px;">
                            ADN &gt; {{ $umbralValor }} × 10<sup>{{ $umbralExponente }}</sup>
                        </div>
                    </div>
                @endif
            </td>
        </tr>
    </table>
</div>

{{-- Gráfica (mismo bloque que carga-viral.blade.php) --}}
@if($mostrarGrafica && $resultadoDetectado === 'DETECTADO (+)' && $cargaViral > 0)
    @if($chartImg)
        <div style="margin-top: 10px; text-align: center;">
            <div style="font-weight: bold; font-size: 10px; margin-bottom: 8px; color: #1e3a5f;">
                {{ $siglas }} - Posición del Paciente Respecto al Umbral
            </div>
            <div style="width: 100%; height: 100px; overflow: hidden;">
                <img src="{{ $chartImg }}" style="width: 100%; height: 100px;" alt="Gráfica {{ $siglas }}">
            </div>

            <div style="margin-top: 10px; font-size: 8px; text-align: center;">
                <span style="display: inline-block; margin: 0 8px;">
                    <span style="display: inline-block; width: 12px; height: 12px; background-color: #bbf7d0; border: 1px solid #22c55e; vertical-align: middle;"></span>
                    Zona Regresiva (&lt; {{ $umbralValor }})
                </span>
                <span style="display: inline-block; margin: 0 8px;">
                    <span style="display: inline-block; width: 12px; height: 12px; background-color: #fecaca; border: 1px solid #ef4444; vertical-align: middle;"></span>
                    Zona Progresiva (&gt; {{ $umbralValor }})
                </span>
                <span style="display: inline-block; margin: 0 8px;">
                    <span style="display: inline-block; width: 16px; height: 0; border-top: 2px dashed #3b82f6; vertical-align: middle;"></span>
                    Umbral ({{ $umbralValor }} × 10<sup>{{ $umbralExponente }}</sup>)
                </span>
            </div>
        </div>
    @endif
@endif

@endforeach
