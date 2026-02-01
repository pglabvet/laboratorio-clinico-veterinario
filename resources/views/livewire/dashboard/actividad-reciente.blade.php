<div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
    {{-- Header --}}
    <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    Actividad Reciente
                </flux:heading>
                <flux:subheading class="mt-1">
                    Últimos análisis registrados en el sistema
                </flux:subheading>
            </div>
            <a href="{{ route('muestras.index') }}" wire:navigate class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                Ver todos →
            </a>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-x-auto">
        @if($analisisRecientes->count() > 0)
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-100 dark:bg-zinc-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                            Código Muestra
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                            Tipo de Análisis
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                            Estado
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                            Veterinaria
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                            Bioquímico
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                            Fecha
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                    @foreach($analisisRecientes as $analisis)
                        <tr class="transition-colors hover:bg-zinc-100/50 dark:hover:bg-zinc-800/70" wire:key="analisis-{{ $analisis->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $analisis->muestra->codigo_muestra ?? 'N/A' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                {{ $analisis->tipoAnalisis->nombre ?? 'N/A' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $this->getEstadoBadge($analisis->estado) }}">
                                    {{ $this->getEstadoTexto($analisis->estado) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                {{ $analisis->muestra->veterinaria->nombre ?? 'N/A' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                {{ $analisis->bioquimico->name ?? 'Sin asignar' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                {{ $analisis->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <flux:heading size="lg" class="mt-4 text-zinc-900 dark:text-white">
                    Sin actividad reciente
                </flux:heading>
                <flux:subheading class="mt-2">
                    No hay análisis registrados en el sistema
                </flux:subheading>
            </div>
        @endif
    </div>
</div>
