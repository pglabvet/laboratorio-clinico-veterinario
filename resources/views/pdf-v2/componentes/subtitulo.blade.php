{{-- Componente PDF V2: Subtítulo --}}
@php
    $textoSubtitulo = '';
    if (is_array($resultado) && isset($resultado['texto'])) {
        $textoSubtitulo = $resultado['texto'];
    } elseif (is_string($resultado) && !empty($resultado)) {
        $textoSubtitulo = $resultado;
    } elseif (isset($componente['propiedades']['texto'])) {
        $textoSubtitulo = $componente['propiedades']['texto'];
    }
    
    $alineacion = $resultado['alineacion'] ?? $componente['propiedades']['alineacion'] ?? 'izquierda';
    $tamano = $resultado['tamano'] ?? $componente['propiedades']['tamano'] ?? 'mediano';
    
    $fontSize = match($tamano) {
        'grande' => '16px',
        'mediano' => '14px',
        'pequeño' => '12px',
        default => '14px'
    };
    
    $textAlign = match($alineacion) {
        'centro' => 'center',
        'derecha' => 'right',
        default => 'left'
    };
@endphp

@if(!empty($textoSubtitulo))
    <div style="font-size: {{ $fontSize }}; font-weight: bold; color: #1e3a5f; margin: 12px 0 8px 0; padding-bottom: 4px; text-align: {{ $textAlign }}; text-transform: uppercase; letter-spacing: 0.8px;">
        {{ $textoSubtitulo }}
    </div>
@endif
