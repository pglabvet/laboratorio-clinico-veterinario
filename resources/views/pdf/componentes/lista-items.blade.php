{{-- Componente PDF: Lista de Items --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(!empty($resultado) && is_array($resultado))
<ul class="items-list">
    @foreach($resultado as $item)
        @if(!empty($item))
            <li>{{ is_array($item) ? ($item['texto'] ?? json_encode($item)) : $item }}</li>
        @endif
    @endforeach
</ul>
@else
<p style="color: #718096; font-style: italic;">Sin items</p>
@endif
