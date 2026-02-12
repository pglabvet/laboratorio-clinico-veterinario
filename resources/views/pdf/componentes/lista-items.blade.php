{{-- Componente PDF: Lista de Items --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(!empty($resultado) && is_array($resultado))
<ul style="list-style-type: disc; padding-left: 20px; margin: 0; word-wrap: break-word; overflow-wrap: break-word;">
    @foreach($resultado as $item)
        @if(!empty($item))
            <li style="margin-bottom: 4px; word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">
                {{ is_array($item) ? ($item['texto'] ?? json_encode($item)) : $item }}
            </li>
        @endif
    @endforeach
</ul>
@else
<p style="color: #718096; font-style: italic;">Sin items</p>
@endif

