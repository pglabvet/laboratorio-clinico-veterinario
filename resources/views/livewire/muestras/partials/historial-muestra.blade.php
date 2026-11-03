{{-- Historial legible de una muestra: quién hizo qué y cuándo --}}
@php
    $colores = [
        'green' => 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
        'amber' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
        'blue' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
        'red' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
        'violet' => 'bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400',
        'zinc' => 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
    ];
@endphp

<div wire:loading.delay wire:target="pestanaDetalle" class="py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">
    Cargando historial...
</div>

<div wire:loading.remove wire:target="pestanaDetalle" class="max-h-[60vh] overflow-y-auto pr-1">
    @forelse($eventos as $evento)
        <div class="relative flex gap-3 pb-5 last:pb-0" wire:key="historial-{{ $evento['id'] }}">
            {{-- Línea vertical --}}
            @unless($loop->last)
                <span class="absolute left-4 top-9 -bottom-0 w-px bg-neutral-200 dark:bg-neutral-700"></span>
            @endunless

            <div class="relative flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $colores[$evento['color']] ?? $colores['zinc'] }}">
                <flux:icon :name="$evento['icono']" class="size-4" />
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-sm text-neutral-900 dark:text-neutral-100">
                    <span class="font-semibold">{{ $evento['usuario'] }}</span>
                    {{ lcfirst($evento['texto']) }}
                </p>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    {{ $evento['fecha']->format('d/m/Y H:i') }}
                </p>

                @if($evento['motivo'])
                    <p class="mt-1 rounded-md bg-red-50 px-2 py-1 text-xs text-red-700 dark:bg-red-900/10 dark:text-red-400">
                        Motivo: {{ $evento['motivo'] }}
                    </p>
                @endif

                @if(count($evento['detalles']))
                    <ul class="mt-1.5 space-y-0.5 rounded-md bg-neutral-50 px-2 py-1.5 text-xs dark:bg-neutral-900">
                        @foreach($evento['detalles'] as $detalle)
                            <li class="text-neutral-700 dark:text-neutral-300">
                                <span class="font-medium">{{ $detalle['campo'] }}:</span>
                                <span class="text-neutral-500 line-through dark:text-neutral-500">{{ Str::limit($detalle['antes'], 60) }}</span>
                                <span class="text-neutral-400">→</span>
                                <span class="font-medium">{{ Str::limit($detalle['despues'], 60) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @empty
        <p class="py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">
            No hay movimientos registrados para esta muestra.
        </p>
    @endforelse
</div>
