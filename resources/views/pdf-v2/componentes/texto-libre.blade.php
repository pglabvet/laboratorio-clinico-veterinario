{{-- Componente PDF V2: Texto Libre (diseño limpio) --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@php
    $contenido = is_array($resultado) ? ($resultado['contenido'] ?? ($resultado['valor'] ?? '')) : $resultado;
    $tagsPermitidos = '<p><strong><b><em><i><u><s><ul><ol><li><br>';
@endphp

@if(!empty($contenido))
<div class="text-content" style="word-wrap: break-word; overflow-wrap: break-word;">
    @if(str_contains($contenido, '<'))
        {!! strip_tags($contenido, $tagsPermitidos) !!}
    @else
        {!! nl2br(e($contenido)) !!}
    @endif
</div>
@endif
