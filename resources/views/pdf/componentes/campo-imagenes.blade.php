{{-- Componente PDF: Campo de Imágenes --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@php
    $imagen1 = $resultado['imagen1'] ?? null;
    $imagen2 = $resultado['imagen2'] ?? null;
@endphp

@if($imagen1 || $imagen2)
<div class="images-container">
    @if($imagen1)
    <div class="image-cell">
        @php
            $rutaCompleta1 = storage_path('app/public/' . $imagen1);
            $imagenBase64_1 = file_exists($rutaCompleta1) 
                ? 'data:image/' . pathinfo($rutaCompleta1, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($rutaCompleta1))
                : null;
        @endphp
        @if($imagenBase64_1)
            <img src="{{ $imagenBase64_1 }}" alt="Imagen 1" style="max-width: 100%; max-height: 180px;">
            <p style="font-size: 8px; color: #718096; margin-top: 4px;">Imagen 1</p>
        @endif
    </div>
    @endif
    
    @if($imagen2)
    <div class="image-cell">
        @php
            $rutaCompleta2 = storage_path('app/public/' . $imagen2);
            $imagenBase64_2 = file_exists($rutaCompleta2) 
                ? 'data:image/' . pathinfo($rutaCompleta2, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($rutaCompleta2))
                : null;
        @endphp
        @if($imagenBase64_2)
            <img src="{{ $imagenBase64_2 }}" alt="Imagen 2" style="max-width: 100%; max-height: 180px;">
            <p style="font-size: 8px; color: #718096; margin-top: 4px;">Imagen 2</p>
        @endif
    </div>
    @endif
</div>
@else
<p style="color: #718096; font-style: italic;">Sin imágenes adjuntas</p>
@endif
