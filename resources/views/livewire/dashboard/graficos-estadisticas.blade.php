<div class="grid gap-4 md:grid-cols-2">
    {{-- Gráfico: Análisis por Estado --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading size="lg" class="mb-4 text-zinc-900 dark:text-white">
            Distribución por Estado
        </flux:heading>
        <flux:subheading class="mb-6">
            Total de análisis según su estado actual
        </flux:subheading>

        @if(count($analisisPorEstado) > 0)
            <div class="space-y-4">
                @foreach($analisisPorEstado as $estado => $total)
                    <div>
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                {{ $this->getEstadoLabel($estado) }}
                            </span>
                            <span class="font-bold text-zinc-900 dark:text-white">
                                {{ $total }}
                            </span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <div 
                                class="h-full rounded-full transition-all duration-500"
                                style="width: {{ array_sum($analisisPorEstado) > 0 ? ($total / array_sum($analisisPorEstado) * 100) : 0 }}%; background-color: {{ $this->getEstadoColor($estado) }}"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total</span>
                    <span class="text-lg font-bold text-zinc-900 dark:text-white">{{ array_sum($analisisPorEstado) }}</span>
                </div>
            </div>
        @else
            <div class="py-8 text-center">
                <flux:subheading>No hay datos disponibles</flux:subheading>
            </div>
        @endif
    </div>

    {{-- Gráfico: Análisis por Especie --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading size="lg" class="mb-4 text-zinc-900 dark:text-white">
            Análisis por Especie
        </flux:heading>
        <flux:subheading class="mb-6">
            Top 5 especies más analizadas
        </flux:subheading>

        @if(count($analisisPorEspecie) > 0)
            @php
                $maxValue = max($analisisPorEspecie);
                $colors = ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'];
            @endphp

            <div class="space-y-4">
                @foreach($analisisPorEspecie as $especie => $total)
                    <div>
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                {{ $especie }}
                            </span>
                            <span class="font-bold text-zinc-900 dark:text-white">
                                {{ $total }}
                            </span>
                        </div>
                        <div class="h-3 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <div 
                                class="h-full rounded-full transition-all duration-500"
                                style="width: {{ $maxValue > 0 ? ($total / $maxValue * 100) : 0 }}%; background-color: {{ $colors[$loop->index % count($colors)] }}"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total</span>
                    <span class="text-lg font-bold text-zinc-900 dark:text-white">{{ array_sum($analisisPorEspecie) }}</span>
                </div>
            </div>
        @else
            <div class="py-8 text-center">
                <flux:subheading>No hay datos disponibles</flux:subheading>
            </div>
        @endif
    </div>
</div>
