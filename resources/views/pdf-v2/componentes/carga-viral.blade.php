{{-- Componente PDF V2: Carga Viral qPCR (diseño limpio) --}}
@php
    $propiedades = $componente['propiedades'] ?? [];
    $umbralValor = $propiedades['umbral_valor'] ?? 10;
    $umbralExponente = $propiedades['umbral_exponente'] ?? 5;
    $unidad = $propiedades['unidad'] ?? 'copias/ml';
    $patogeno = $propiedades['patogeno'] ?? 'Patógeno';
    $nombreCompleto = $propiedades['nombre_completo'] ?? '';
    $interpretaciones = $propiedades['interpretaciones'] ?? [];
    
    $campos = [];
    $resultadoDetectado = '';
    $cargaViral = 0;
    
    if (!empty($resultado) && is_array($resultado)) {
        foreach ($resultado as $item) {
            if (is_array($item)) {
                $campos[] = $item;
                if (($item['tipo'] ?? '') === 'select') {
                    $resultadoDetectado = $item['valor'] ?? '';
                }
                if (($item['tipo'] ?? '') === 'numero_cientifico') {
                    $cargaViral = floatval($item['valor'] ?? 0);
                }
            }
        }
    }
    
    $interpretacion = 'no_detectado';
    if ($resultadoDetectado === 'DETECTADO (+)' && $cargaViral > 0) {
        $interpretacion = $cargaViral < $umbralValor ? 'regresivo' : 'progresivo';
    }
@endphp

@if(isset($propiedades['titulo']))
    <div class="component-title">{{ $propiedades['titulo'] }}</div>
@endif

@if($nombreCompleto)
    <div style="text-align: left; font-size: 10px; color: #4a5568; margin-bottom: 10px;">
        {{ $nombreCompleto }}
    </div>
@endif

@if(!empty($campos))
<table style="margin-bottom: 12px;">
    <tbody>
        @foreach($campos as $campo)
            <tr>
                <td style="width: 35%;">
                    {{ $campo['etiqueta'] ?? 'Campo' }}
                </td>
                <td style="width: 65%;">
                    @if(($campo['tipo'] ?? 'texto') === 'numero_cientifico')
                        {{ $campo['valor'] ?? '0' }} × 10<sup>{{ $umbralExponente }}</sup> {{ $unidad }}
                    @else
                        {{ $campo['valor'] ?? 'N/A' }}
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Interpretación en columnas --}}
<div style="margin: 10px 0; padding: 8px 0; page-break-inside: avoid;">
    <div style="font-weight: bold; font-size: 9px; margin-bottom: 8px; text-transform: uppercase; color: #1e3a5f; text-align: center; border-bottom: 2px solid #1e3a5f; padding-bottom: 5px;">
        INTERPRETACIÓN DE RESULTADOS
    </div>
    
    <table style="width: 100%; border-collapse: collapse; table-layout: fixed; page-break-inside: avoid;">
        <tr>
            <td style="width: 33.33%; vertical-align: top; padding: 3px; border: none; border-bottom: none;">
                @if($interpretacion === 'no_detectado')
                    <div style="padding: 5px; border-left: 3px solid #22c55e;">
                        <div style="font-weight: bold; color: #15803d; font-size: 8px;">
                            {{ $patogeno }} NO DETECTADO:
                        </div>
                        <div style="font-size: 7px; color: #166534; margin-top: 2px;">
                            {{ $interpretaciones['no_detectado']['descripcion'] ?? 'Sin detección de ADN viral en la muestra analizada.' }}
                        </div>
                    </div>
                @else
                    <div style="padding: 5px; border-left: 2px solid #d1d5db; opacity: 0.4;">
                        <div style="font-weight: bold; font-size: 7px; color: #9ca3af;">
                            {{ $patogeno }} NO DETECTADO:
                        </div>
                        <div style="font-size: 6px; color: #9ca3af; margin-top: 2px;">
                            {{ $interpretaciones['no_detectado']['descripcion'] ?? 'Sin detección de ADN viral.' }}
                        </div>
                    </div>
                @endif
            </td>
            
            <td style="width: 33.33%; vertical-align: top; padding: 3px; border: none; border-bottom: none;">
                @if($interpretacion === 'regresivo')
                    <div style="padding: 5px; border-left: 3px solid #eab308;">
                        <div style="font-weight: bold; color: #a16207; font-size: 8px;">
                            {{ $patogeno }} DETECTADO - REGRESIVA:
                        </div>
                        <div style="font-size: 7px; color: #854d0e; margin-top: 2px;">
                            {{ $interpretaciones['regresivo']['descripcion'] ?? 'Carga viral baja, posible fase de resolución.' }}
                        </div>
                        <div style="font-size: 6px; color: #ca8a04; margin-top: 2px;">
                            ADN &lt; {{ $umbralValor }} × 10<sup>{{ $umbralExponente }}</sup> {{ $unidad }}
                        </div>
                    </div>
                @else
                    <div style="padding: 5px; border-left: 2px solid #d1d5db; opacity: 0.4;">
                        <div style="font-weight: bold; font-size: 7px; color: #9ca3af;">
                            {{ $patogeno }} - REGRESIVA:
                        </div>
                        <div style="font-size: 6px; color: #9ca3af; margin-top: 2px;">
                            {{ $interpretaciones['regresivo']['descripcion'] ?? 'Fase de resolución.' }}
                        </div>
                        <div style="font-size: 6px; color: #9ca3af; margin-top: 2px;">
                            ADN &lt; {{ $umbralValor }} × 10<sup>{{ $umbralExponente }}</sup>
                        </div>
                    </div>
                @endif
            </td>
            
            <td style="width: 33.33%; vertical-align: top; padding: 3px; border: none; border-bottom: none;">
                @if($interpretacion === 'progresivo')
                    <div style="padding: 5px; border-left: 3px solid #ef4444;">
                        <div style="font-weight: bold; color: #991b1b; font-size: 8px;">
                            {{ $patogeno }} DETECTADO - PROGRESIVA:
                        </div>
                        <div style="font-size: 7px; color: #991b1b; margin-top: 2px;">
                            {{ $interpretaciones['progresivo']['descripcion'] ?? 'Carga viral alta, infección activa.' }}
                        </div>
                        <div style="font-size: 6px; color: #dc2626; margin-top: 2px;">
                            ADN &gt; {{ $umbralValor }} × 10<sup>{{ $umbralExponente }}</sup> {{ $unidad }}
                        </div>
                    </div>
                @else
                    <div style="padding: 5px; border-left: 2px solid #d1d5db; opacity: 0.4;">
                        <div style="font-weight: bold; font-size: 7px; color: #9ca3af;">
                            {{ $patogeno }} - PROGRESIVA:
                        </div>
                        <div style="font-size: 6px; color: #9ca3af; margin-top: 2px;">
                            {{ $interpretaciones['progresivo']['descripcion'] ?? 'Infección activa.' }}
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

{{-- Gráfica --}}
@if(($propiedades['mostrar_grafica'] ?? true) && $resultadoDetectado === 'DETECTADO (+)' && $cargaViral > 0)
    @if(isset($chartImage) && $chartImage)
        <div style="margin-top: 10px; text-align: center;">
            <div style="font-weight: bold; font-size: 10px; margin-bottom: 8px; color: #1e3a5f;">
                {{ $patogeno }} - Posición del Paciente Respecto al Umbral
            </div>
            <div style="width: 100%; height: 100px; overflow: hidden;">
                <img src="{{ $chartImage }}" style="width: 100%; height: 100px;" alt="Gráfica">
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
