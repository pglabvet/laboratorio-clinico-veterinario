<!-- Preview de Campo de Texto Simple -->
<div class="space-y-2">
    @if(($props['tipo_uso'] ?? 'editable') === 'nota')
        {{-- Nota fija: estilo caja con borde --}}
        <div class="p-3 rounded-lg border border-gray-300 dark:border-zinc-600 min-h-[40px]">
            @if(!empty($props['titulo']) || !empty($props['contenido']))
                <p class="text-sm text-gray-700 dark:text-zinc-300 leading-relaxed">
                    @if(!empty($props['titulo']))
                        <strong class="text-gray-900 dark:text-zinc-100">{{ $props['titulo'] }}:</strong>
                    @endif
                    @if(!empty($props['contenido']))
                        {{ $props['contenido'] }}
                    @else
                        <span class="text-gray-400 dark:text-zinc-500 italic">Escriba el contenido en las propiedades...</span>
                    @endif
                </p>
            @else
                <p class="text-sm text-gray-400 dark:text-zinc-500 italic">Configure el título y contenido en las propiedades...</p>
            @endif
        </div>
    @else
        {{-- Editable: caja con título inline y textarea --}}
        <div class="p-3 rounded-lg border border-gray-300 dark:border-zinc-600">
            <p class="text-sm text-gray-700 dark:text-zinc-300 leading-relaxed">
                @if(!empty($props['titulo']))
                    <strong class="text-gray-900 dark:text-zinc-100">{{ $props['titulo'] }}:</strong>
                @endif
                <span class="text-gray-400 dark:text-zinc-500 italic">(a completar por bioquímico)</span>
            </p>
        </div>
    @endif
</div>
