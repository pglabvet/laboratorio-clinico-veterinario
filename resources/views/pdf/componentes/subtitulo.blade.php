{{-- Componente PDF: Subtítulo --}}
@php
    // Obtener el texto del resultado (editado por el bioquímico) o de las propiedades originales
    $textoSubtitulo = '';
    if (is_array($resultado) && isset($resultado['texto'])) {
        $textoSubtitulo = $resultado['texto'];
    } elseif (is_string($resultado) && !empty($resultado)) {
        $textoSubtitulo = $resultado;
    } elseif (isset($componente['propiedades']['texto'])) {
        $textoSubtitulo = $componente['propiedades']['texto'];
    }
    
    // Obtener propiedades de estilo
    $alineacion = $resultado['alineacion'] ?? $componente['propiedades']['alineacion'] ?? 'izquierda';
    $tamano = $resultado['tamano'] ?? $componente['propiedades']['tamano'] ?? 'mediano';
    
    // Definir tamaño de fuente según configuración
    $fontSize = match($tamano) {
        'grande' => '16px',
        'mediano' => '14px',
        'pequeño' => '12px',
        default => '14px'
    };
    
    // Definir alineación
    $textAlign = match($alineacion) {
        'centro' => 'center',
        'derecha' => 'right',
        default => 'left'
    };
@endphp

@if(!empty($textoSubtitulo))
    <div style="font-size: {{ $fontSize }}; font-weight: bold; color: #1e3a5f; margin: 15px 0 10px 0; padding-bottom: 5px; border-bottom: 2px solid #1e3a5f; text-align: {{ $textAlign }}; text-transform: uppercase;">
        {{ $textoSubtitulo }}
    </div>
@endif
