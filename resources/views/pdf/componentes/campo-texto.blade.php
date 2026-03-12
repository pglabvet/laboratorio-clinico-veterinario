{{-- Componente PDF: Campo Texto --}}
@php
    $tipoUso = $componente['propiedades']['tipo_uso'] ?? 'editable';
@endphp

@if(!empty($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if($tipoUso === 'nota')
    {{-- Nota fija: usar contenido de la plantilla --}}
    @if(!empty($componente['propiedades']['contenido']))
    <div style="padding: 8px; word-wrap: break-word; white-space: pre-wrap;">
        {!! nl2br(e($componente['propiedades']['contenido'])) !!}
    </div>
    @endif
@else
    @php
        $valor = is_array($resultado) ? ($resultado['valor'] ?? '') : $resultado;
    @endphp

    @if(!empty($valor))
    <div style="padding: 8px; background-color: transparent; border: none; word-wrap: break-word; white-space: pre-wrap;">
        {!! nl2br(e($valor)) !!}
    </div>
    @else
    <p style="color: #718096; font-style: italic;">Sin valor</p>
    @endif
@endif
