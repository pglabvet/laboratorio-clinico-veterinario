{{-- Componente PDF V2: Campo Texto (estilo nota técnica con borde) --}}
@php
    $tipoUso = $componente['propiedades']['tipo_uso'] ?? 'editable';
    $titulo = $componente['propiedades']['titulo'] ?? '';
@endphp

@if($tipoUso === 'nota')
    @if(!empty($componente['propiedades']['contenido']))
    <div style="padding: 8px 0; margin: 8px 0;">
        <span style="font-size: 9px; line-height: 1.6; color: #2d3748;">
            @if(!empty($titulo))
                <strong style="color: #1e3a5f;">{{ $titulo }}:</strong>
            @endif
            {{ $componente['propiedades']['contenido'] }}
        </span>
    </div>
    @endif
@else
    @php
        $valor = is_array($resultado) ? ($resultado['valor'] ?? '') : $resultado;
    @endphp

    @if(!empty($valor))
    <div style="padding: 8px 0; margin: 8px 0;">
        <span style="font-size: 9px; line-height: 1.6; color: #2d3748;">
            @if(!empty($titulo))
                <strong style="color: #1e3a5f;">{{ $titulo }}:</strong>
            @endif
            {{ $valor }}
        </span>
    </div>
    @endif
@endif
