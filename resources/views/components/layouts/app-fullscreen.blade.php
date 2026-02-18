<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gray-100 dark:bg-zinc-800">
        {{-- Header/Topbar con botón de volver --}}
        <header class="flex items-center justify-between border-b border-zinc-200 bg-white px-4 py-3 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <flux:button href="{{ route('plantillas.index') }}" variant="outline" icon="arrow-left" size="sm">
                    Volver a Plantillas
                </flux:button>
            </div>
            <div class="flex items-center gap-3">
                <flux:dropdown position="bottom" align="end">
                    <flux:profile
                        :name="auth()->user()->name"
                        :initials="substr(auth()->user()->name, 0, 1)"
                        icon-trailing="chevron-down"
                    />
                    <flux:menu>
                        <flux:menu.item :href="route('profile.edit')" icon="cog-6-tooth" wire:navigate>Configuración</flux:menu.item>
                        <flux:menu.separator />
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                Cerrar Sesión
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </header>

        {{-- Page Content (sin sidebar, ancho completo) --}}
        <main class="w-full p-4">
            {{ $slot }}
        </main>

        @fluxScripts
        @stack('scripts')
    </body>
</html>
