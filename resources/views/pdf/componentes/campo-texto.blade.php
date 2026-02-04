{{-- Componente PDF: Campo Texto --}}
@if(isset($componente['propiedades']['titulo']) || isset($componente['propiedades']['etiqueta']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] ?? $componente['propiedades']['etiqueta'] ?? '' }}</div>
@endif

@php
    $valor = is_array($resultado) ? ($resultado['valor'] ?? '') : $resultado;
@endphp

@if(!empty($valor))
<div style="padding: 8px; background-color: transparent; border: none;">
    {{ $valor }}
</div>
@else
<p style="color: #718096; font-style: italic;">Sin valor</p>
@endif
