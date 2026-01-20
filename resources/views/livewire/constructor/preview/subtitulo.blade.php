<!-- Preview de Subtítulo -->
<div class="py-3">
    <h3 class="font-bold text-gray-900 
        @if($props['alineacion'] === 'centro') text-center @endif
        @if($props['alineacion'] === 'derecha') text-right @endif
        @if($props['tamano'] === 'grande') text-xl @endif
        @if($props['tamano'] === 'mediano') text-lg @endif
        @if($props['tamano'] === 'pequeño') text-base @endif
    ">
        {{ $props['texto'] ?? 'SUBTÍTULO' }}
    </h3>
</div>
