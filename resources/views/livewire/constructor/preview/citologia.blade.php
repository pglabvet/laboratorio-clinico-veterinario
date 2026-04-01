<!-- Preview de Citología -->
<div class="space-y-3">
    @if(!empty($props['titulos']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center">
        {{ $props['titulos'][0] ?? 'CITOLOGÍA' }}
    </h4>
    @elseif(isset($props['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center">
        {{ $props['titulo'] }}
    </h4>
    @endif

    {{-- Selector de tumor (preview) --}}
    <div class="flex items-center gap-2 p-2 bg-purple-50 dark:bg-purple-900/20 rounded border border-purple-200 dark:border-purple-800">
        <span class="text-xs font-medium text-purple-700 dark:text-purple-400">
            <i class="fas fa-disease mr-1"></i> Tumor:
        </span>
        <div class="flex-1 px-2 py-1 bg-white dark:bg-zinc-800 rounded border border-purple-200 dark:border-purple-700 text-xs text-gray-700 dark:text-zinc-300">
            @if(!empty($props['tumores']))
                <span class="text-purple-600 dark:text-purple-400">▼</span>
                {{ $props['tumores'][0] ?? 'Seleccionar tumor...' }}
            @else
                <span class="text-gray-400 dark:text-zinc-500 italic">Sin tumores configurados</span>
            @endif
        </div>
    </div>

    {{-- Secciones --}}
    @foreach($props['secciones'] ?? [] as $si => $seccion)
    <div class="p-3 bg-gray-50 dark:bg-zinc-900 rounded border border-gray-200 dark:border-zinc-700">
        @if(!empty($seccion['titulo']))
        <h5 class="font-bold text-sm text-gray-800 dark:text-zinc-100 mb-2">
            {{ $seccion['titulo'] }}
        </h5>
        @endif

        @php $tipo = $seccion['tipo'] ?? 'editable'; @endphp

        @if($tipo === 'editable')
            {{-- Sección editable --}}
            @if(!empty($seccion['texto_base']))
                <div class="text-sm text-gray-700 dark:text-zinc-300 whitespace-pre-line">{{ $seccion['texto_base'] }}</div>
            @else
                <p class="text-sm text-gray-400 dark:text-zinc-500 italic">
                    <i class="fas fa-edit mr-1"></i> Texto editable por el bioquímico...
                </p>
            @endif
        @elseif($tipo === 'dependiente')
            {{-- Sección dependiente del tumor --}}
            @php
                $primerTumor = collect($props['tumores'] ?? [])->filter()->first();
                $textoPrimerTumor = $seccion['textos_por_tumor'][$primerTumor] ?? '';
            @endphp
            @if(!empty($textoPrimerTumor))
                <div class="text-sm text-gray-700 dark:text-zinc-300 whitespace-pre-line">{{ $textoPrimerTumor }}</div>
            @else
                <p class="text-sm text-amber-500 dark:text-amber-400 italic">
                    <i class="fas fa-link mr-1"></i> Texto se carga según tumor seleccionado...
                </p>
            @endif
        @elseif($tipo === 'con_tumor')
            {{-- Sección con tumor --}}
            <div class="text-sm text-gray-700 dark:text-zinc-300">
                {{ $seccion['texto_base'] ?? '' }}
                @if(!empty($props['tumores']))
                    <span class="font-bold text-purple-700 dark:text-purple-400">{{ $props['tumores'][0] ?? '' }}</span>
                @else
                    <span class="font-bold text-purple-400 italic">[tumor]</span>
                @endif
            </div>
        @endif
    </div>
    @endforeach

    @if(empty($props['secciones']))
    <div class="text-center py-4">
        <p class="text-sm text-gray-400 dark:text-zinc-500 italic">Sin secciones configuradas</p>
    </div>
    @endif
</div>
