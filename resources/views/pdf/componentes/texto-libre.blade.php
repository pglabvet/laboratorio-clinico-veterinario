{{-- Componente PDF: Texto Libre --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@php
    $contenido = is_array($resultado) ? ($resultado['contenido'] ?? ($resultado['valor'] ?? '')) : $resultado;
    
    // Lista de tags HTML permitidos para el contenido formateado
    $tagsPermitidos = '<p><strong><b><em><i><u><s><ul><ol><li><br>';
@endphp

@if(!empty($contenido))
<div class="text-content" style="word-wrap: break-word; overflow-wrap: break-word;">
    @if(str_contains($contenido, '<'))
        {{-- Renderizar HTML con tags permitidos --}}
        {!! strip_tags($contenido, $tagsPermitidos) !!}
    @else
        {{-- Texto plano con saltos de línea --}}
        {!! nl2br(e($contenido)) !!}
    @endif
</div>
@else
<p style="color: #718096; font-style: italic;">Sin contenido</p>
@endif
