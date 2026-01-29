{{-- Componente PDF: Campo Texto --}}
@if(isset($componente['propiedades']['titulo']) || isset($componente['propiedades']['etiqueta']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] ?? $componente['propiedades']['etiqueta'] ?? '' }}</div>
@endif

@php
    $valor = is_array($resultado) ? ($resultado['valor'] ?? '') : $resultado;
@endphp

@if(!empty($valor))
<div style="padding: 8px; background-color: #f7fafc; border: 1px solid #e2e8f0; border-radius: 4px;">
    {{ $valor }}
</div>
@else
<p style="color: #718096; font-style: italic;">Sin valor</p>
@endif
