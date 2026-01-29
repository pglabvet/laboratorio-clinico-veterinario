{{-- Componente PDF: Texto Libre --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@php
    $contenido = is_array($resultado) ? ($resultado['contenido'] ?? ($resultado['valor'] ?? '')) : $resultado;
@endphp

@if(!empty($contenido))
<div class="text-content">
    {!! nl2br(e($contenido)) !!}
</div>
@else
<p style="color: #718096; font-style: italic;">Sin contenido</p>
@endif
