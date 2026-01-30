<div class="grid gap-4 md:grid-cols-2">
    {{-- Registrar Nueva Muestra --}}
    <a href="{{ route('muestras.crear') }}" class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 p-8 text-white shadow-lg transition-all hover:shadow-2xl hover:scale-[1.02]">
        <div class="relative z-10">
            <div class="mb-4 inline-flex rounded-lg bg-white/20 p-4 backdrop-blur-sm">
                <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
            </div>
            <h3 class="text-2xl font-bold mb-2">Registrar Nueva Muestra</h3>
            <p class="text-blue-100 text-sm mb-4">Ingrese los datos del paciente y tipos de análisis</p>
            <div class="inline-flex items-center gap-2 text-sm font-semibold">
                Comenzar
                <svg class="size-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </div>
        </div>
        {{-- Patrón decorativo --}}
        <div class="absolute right-0 top-0 opacity-10">
            <svg class="size-48" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 16a9.065 9.065 0 0 1-6.23-.693L5 15.3m14.8 0 .892 3.35c.033.123.033.235 0 .358a.75.75 0 0 1-.73.535H3.038a.75.75 0 0 1-.73-.535 1.342 1.342 0 0 1 0-.358l.892-3.35" />
            </svg>
        </div>
    </a>

    {{-- Escanear Código --}}
    <a href="{{ route('muestras.escanear') }}" class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-purple-500 to-purple-700 p-8 text-white shadow-lg transition-all hover:shadow-2xl hover:scale-[1.02]">
        <div class="relative z-10">
            <div class="mb-4 inline-flex rounded-lg bg-white/20 p-4 backdrop-blur-sm">
                <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                </svg>
            </div>
            <h3 class="text-2xl font-bold mb-2">Escanear Código</h3>
            <p class="text-purple-100 text-sm mb-4">Use el lector de código de barras para procesar muestras</p>
            <div class="inline-flex items-center gap-2 text-sm font-semibold">
                Ir a Escaneo
                <svg class="size-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </div>
        </div>
        {{-- Patrón decorativo --}}
        <div class="absolute right-0 top-0 opacity-10">
            <svg class="size-48" fill="currentColor" viewBox="0 0 24 24">
                <path d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
            </svg>
        </div>
    </a>
</div>
