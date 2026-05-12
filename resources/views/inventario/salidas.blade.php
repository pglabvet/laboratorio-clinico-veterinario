<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventario - LabVet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxStyles
</head>
<body class="antialiased bg-neutral-50 dark:bg-neutral-900">
    <flux:sidebar sticky stashable class="bg-neutral-50 dark:bg-neutral-900 border-r border-neutral-200 dark:border-neutral-700">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <flux:brand href="{{ route('dashboard') }}" logo="{{ asset('images/logo.png') }}" name="LabVet" class="px-2 dark:hidden" />
        <flux:brand href="{{ route('dashboard') }}" logo="{{ asset('images/logo-dark.png') }}" name="LabVet" class="px-2 hidden dark:flex" />

        <flux:navlist variant="outline">
            <flux:navlist.item icon="squares-2x2" href="{{ route('dashboard') }}">Dashboard</flux:navlist.item>
            
            <flux:navlist.group expandable heading="Inventario" icon="cube">
                <flux:navlist.item href="{{ route('insumos.index') }}">Catálogo de Insumos</flux:navlist.item>
                <flux:navlist.item href="{{ route('inventario.entradas') }}">Entradas</flux:navlist.item>
                <flux:navlist.item href="{{ route('inventario.salidas') }}" current>Salidas Manuales</flux:navlist.item>
                <flux:navlist.item href="{{ route('inventario.historial') }}">Historial</flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        <flux:navlist variant="outline">
            <flux:navlist.item icon="user" href="#">{{ auth()->user()->name }}</flux:navlist.item>
            <flux:navlist.item icon="arrow-right-start-on-rectangle" href="{{ route('logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Cerrar Sesión
            </flux:navlist.item>
        </flux:navlist>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </flux:sidebar>

    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <flux:spacer />
        <flux:brand href="{{ route('dashboard') }}" logo="{{ asset('images/logo.png') }}" name="LabVet" class="dark:hidden" />
        <flux:brand href="{{ route('dashboard') }}" logo="{{ asset('images/logo-dark.png') }}" name="LabVet" class="hidden dark:flex" />
    </flux:header>

    <flux:main class="p-6">
        @can('ver-salidas-manuales')
            <livewire:inventario.registrar-salida />
        @endcan
    </flux:main>

    @fluxScripts
</body>
</html>
