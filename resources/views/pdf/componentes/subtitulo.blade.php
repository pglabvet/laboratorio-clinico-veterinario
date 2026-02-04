{{-- Componente PDF: Subtítulo --}}
@if(isset($componente['propiedades']['texto']))
    <div style="font-size: 11px; font-weight: bold; color: #4a5568; margin: 10px 0 5px 0; padding-bottom: 3px; border-bottom: 1px solid #e2e8f0;">
        {{ $componente['propiedades']['texto'] }}
    </div>
@endif
