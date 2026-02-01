<div class="grid gap-4 {{ auth()->user()->can('ver-estadisticas-completas') ? 'lg:grid-cols-4 md:grid-cols-2' : 'lg:grid-cols-2 md:grid-cols-1' }}">
    {{-- Muestras Pendientes --}}
    <a href="{{ route('muestras.index') }}" class="group block rounded-xl border border-zinc-200 bg-white p-6 transition-all hover:border-yellow-300 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-yellow-700">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <flux:heading size="sm" class="text-zinc-600 dark:text-zinc-400 font-medium">
                    Muestras Pendientes
                </flux:heading>
                <div class="mt-2 flex items-baseline gap-2">
                    <flux:heading size="xl" class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $muestrasPendientes }}
                    </flux:heading>
                </div>
                <flux:subheading class="mt-1 text-xs">
                    Por procesar
                </flux:subheading>
            </div>
            <div class="rounded-lg bg-yellow-100 p-3 transition-transform group-hover:scale-110 dark:bg-yellow-900/20">
                <svg class="size-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
        </div>
    </a>

    {{-- Análisis Pendientes --}}
    <div class="block rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <flux:heading size="sm" class="text-zinc-600 dark:text-zinc-400 font-medium">
                    Análisis Pendientes
                </flux:heading>
                <div class="mt-2 flex items-baseline gap-2">
                    <flux:heading size="xl" class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $analisisPendientes }}
                    </flux:heading>
                </div>
                <flux:subheading class="mt-1 text-xs">
                    {{ auth()->user()->can('ver-estadisticas-completas') ? 'Por iniciar' : 'Asignados a ti' }}
                </flux:subheading>
            </div>
            <div class="rounded-lg bg-blue-100 p-3 dark:bg-blue-900/20">
                <svg class="size-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 16a9.065 9.065 0 0 1-6.23-.693L5 15.3m14.8 0 .892 3.35c.033.123.033.235 0 .358a.75.75 0 0 1-.73.535H3.038a.75.75 0 0 1-.73-.535 1.342 1.342 0 0 1 0-.358l.892-3.35" />
                </svg>
            </div>
        </div>
    </div>

    @can('ver-estadisticas-completas')
    {{-- Muestras del Día --}}
    <a href="{{ route('muestras.index') }}" class="group block rounded-xl border border-zinc-200 bg-white p-6 transition-all hover:border-teal-300 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-teal-700">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <flux:heading size="sm" class="text-zinc-600 dark:text-zinc-400 font-medium">
                    Muestras del Día
                </flux:heading>
                <div class="mt-2 flex items-baseline gap-2">
                    <flux:heading size="xl" class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $muestrasHoy }}
                    </flux:heading>
                </div>
                <flux:subheading class="mt-1 text-xs">
                    Recibidas hoy
                </flux:subheading>
            </div>
            <div class="rounded-lg bg-teal-100 p-3 transition-transform group-hover:scale-110 dark:bg-teal-900/20">
                <svg class="size-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 16a9.065 9.065 0 0 1-6.23-.693L5 15.3m14.8 0 .892 3.35c.033.123.033.235 0 .358a.75.75 0 0 1-.73.535H3.038a.75.75 0 0 1-.73-.535 1.342 1.342 0 0 1 0-.358l.892-3.35" />
                </svg>
            </div>
        </div>
    </a>

    {{-- Usuarios --}}
    <div class="group rounded-xl border border-zinc-200 bg-white p-6 transition-all hover:border-cyan-300 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-cyan-700">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <flux:heading size="sm" class="text-zinc-600 dark:text-zinc-400 font-medium">
                    Usuarios
                </flux:heading>
                <div class="mt-2 flex items-baseline gap-2">
                    <flux:heading size="xl" class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $totalUsuarios }}
                    </flux:heading>
                </div>
                <flux:subheading class="mt-1 text-xs">
                    En el sistema
                </flux:subheading>
            </div>
            <div class="rounded-lg bg-cyan-100 p-3 transition-transform group-hover:scale-110 dark:bg-cyan-900/20">
                <svg class="size-6 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Sucursales --}}
    <a href="{{ route('sucursales.index') }}" class="group block rounded-xl border border-zinc-200 bg-white p-6 transition-all hover:border-purple-300 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-purple-700">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <flux:heading size="sm" class="text-zinc-600 dark:text-zinc-400 font-medium">
                    Sucursales
                </flux:heading>
                <div class="mt-2 flex items-baseline gap-2">
                    <flux:heading size="xl" class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $totalSucursales }}
                    </flux:heading>
                </div>
                <flux:subheading class="mt-1 text-xs">
                    Total activas
                </flux:subheading>
            </div>
            <div class="rounded-lg bg-purple-100 p-3 transition-transform group-hover:scale-110 dark:bg-purple-900/20">
                <svg class="size-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                </svg>
            </div>
        </div>
    </a>

    {{-- Veterinarias --}}
    <a href="{{ route('veterinarias.index') }}" class="group block rounded-xl border border-zinc-200 bg-white p-6 transition-all hover:border-indigo-300 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-indigo-700">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <flux:heading size="sm" class="text-zinc-600 dark:text-zinc-400 font-medium">
                    Veterinarias
                </flux:heading>
                <div class="mt-2 flex items-baseline gap-2">
                    <flux:heading size="xl" class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $totalVeterinarias }}
                    </flux:heading>
                </div>
                <flux:subheading class="mt-1 text-xs">
                    Clientes registrados
                </flux:subheading>
            </div>
            <div class="rounded-lg bg-indigo-100 p-3 transition-transform group-hover:scale-110 dark:bg-indigo-900/20">
                <svg class="size-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                </svg>
            </div>
        </div>
    </a>
    @endcan

    @can('ver-alertas-inventario')
    {{-- Alertas de Inventario --}}
    <a href="{{ route('insumos.index') }}" class="group block rounded-xl border border-zinc-200 bg-white p-6 transition-all {{ $insumosStockBajo > 0 ? 'hover:border-amber-300 dark:hover:border-amber-700' : 'hover:border-green-300 dark:hover:border-green-700' }} hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <flux:heading size="sm" class="text-zinc-600 dark:text-zinc-400 font-medium">
                    Alertas de Inventario
                </flux:heading>
                <div class="mt-2 flex items-baseline gap-2">
                    <flux:heading size="xl" class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $insumosStockBajo }}
                    </flux:heading>
                </div>
                <flux:subheading class="mt-1 text-xs">
                    @if($insumosStockBajo > 0)
                        <span class="text-amber-600 dark:text-amber-400">Stock bajo o crítico</span>
                    @else
                        <span class="text-green-600 dark:text-green-400">Inventario en orden</span>
                    @endif
                </flux:subheading>
            </div>
            <div class="rounded-lg {{ $insumosStockBajo > 0 ? 'bg-amber-100 dark:bg-amber-900/20' : 'bg-green-100 dark:bg-green-900/20' }} p-3 transition-transform group-hover:scale-110">
                <svg class="size-6 {{ $insumosStockBajo > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    @if($insumosStockBajo > 0)
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    @endif
                </svg>
            </div>
        </div>
    </a>
    @endcan
</div>
