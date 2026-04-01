{{-- Componente PDF V2: Citología (diseño limpio) --}}
@php
    $tituloMostrar = $resultado['titulo'] ?? ($componente['propiedades']['titulos'][0] ?? ($componente['propiedades']['titulo'] ?? null));
@endphp

@if($tituloMostrar)
    <div class="component-title" style="text-align: center;">
        {{ $tituloMostrar }}
    </div>
@endif

@php
    $tumor = $resultado['tumor'] ?? '';
    $secciones = $resultado['secciones'] ?? [];
    $tagsPermitidos = '<p><strong><b><em><i><u><s><ul><ol><li><br>';
@endphp

@foreach($secciones as $seccion)
    @if(!empty($seccion['titulo']))
    <div style="font-size: 10px; font-weight: bold; color: #1e3a5f; margin-top: 10px; margin-bottom: 4px;">
        {{ $seccion['titulo'] }}
    </div>
    @endif

    @if(!empty($seccion['contenido']))
    <div class="text-content" style="word-wrap: break-word; overflow-wrap: break-word;">
        @if(str_contains($seccion['contenido'], '<'))
            {!! strip_tags($seccion['contenido'], $tagsPermitidos) !!}
        @else
            {!! nl2br(e($seccion['contenido'])) !!}
        @endif
    </div>
    @endif
@endforeach
