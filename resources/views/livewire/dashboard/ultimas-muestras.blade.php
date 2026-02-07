<div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
    {{-- Header --}}
    <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="size-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 16a9.065 9.065 0 0 1-6.23-.693L5 15.3m14.8 0 .892 3.35c.033.123.033.235 0 .358a.75.75 0 0 1-.73.535H3.038a.75.75 0 0 1-.73-.535 1.342 1.342 0 0 1 0-.358l.892-3.35" />
                </svg>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    Últimas Muestras Registradas
                </flux:heading>
            </div>
            <a href="{{ route('muestras.index') }}" wire:navigate class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                Ver todas
            </a>
        </div>
    </div>

    {{-- Lista de Muestras --}}
    <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
        @forelse($muestras as $index => $muestra)
            <div class="p-4 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50" wire:key="muestra-{{ $muestra->id }}">
                <div class="flex items-start gap-4">
                    {{-- Número --}}
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white dark:bg-blue-500">
                        {{ $index + 1 }}
                    </div>

                    {{-- Contenido --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                {{-- Código y Estado --}}
                                <div class="flex items-center gap-2 flex-wrap">
                                    <flux:heading size="sm" class="font-semibold text-zinc-900 dark:text-white">
                                        {{ $muestra->codigo_muestra }}
                                    </flux:heading>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $this->getEstadoBadge($muestra) }}">
                                        {{ $muestra->estado }}
                                    </span>
                                </div>

                                {{-- Paciente --}}
                                <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300">
                                    <span class="font-medium">Paciente:</span>
                                    {{ $muestra->paciente_nombre }}
                                    <span class="text-zinc-500 dark:text-zinc-400">({{ $muestra->especie->nombre ?? 'N/A' }})</span>
                                </p>

                                {{-- Veterinaria --}}
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $muestra->veterinaria->nombre ?? 'N/A' }}
                                    <span class="text-zinc-400 dark:text-zinc-500">•</span>
                                    hace {{ $muestra->created_at->locale('es')->diffForHumans() }}
                                </p>
                            </div>

                            {{-- Botón Ver Detalle --}}
                            <a href="{{ route('muestras.index') }}" 
                               wire:navigate
                               class="shrink-0 rounded-lg bg-blue-100 px-4 py-2 text-sm font-medium text-blue-700 transition-colors hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30">
                                Ver Detalle
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 16a9.065 9.065 0 0 1-6.23-.693L5 15.3m14.8 0 .892 3.35c.033.123.033.235 0 .358a.75.75 0 0 1-.73.535H3.038a.75.75 0 0 1-.73-.535 1.342 1.342 0 0 1 0-.358l.892-3.35" />
                </svg>
                <flux:heading size="lg" class="mt-4 text-zinc-900 dark:text-white">
                    Sin muestras recientes
                </flux:heading>
                <flux:subheading class="mt-2">
                    No hay muestras registradas en el sistema
                </flux:subheading>
            </div>
        @endforelse
    </div>
</div>
