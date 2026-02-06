{{-- Sidebar Component - Navigation Only --}}
<flux:sidebar sticky stashable class="border-e border-zinc-300 bg-slate-200 shadow-md dark:border-zinc-700 dark:bg-zinc-900 lg:w-72">
    <flux:sidebar.header>
        <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
    </flux:sidebar.header>

    <flux:sidebar.nav>
        <flux:sidebar.group :heading="__('Platform')" class="grid">
            <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="building-office-2" :href="route('sucursales.index')" :current="request()->routeIs('sucursales.*')" wire:navigate>
                {{ __('Sucursales') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="rectangle-group" :href="route('especies.index')" :current="request()->routeIs('especies.*')" wire:navigate>
                {{ __('Especies') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="clipboard-document-list" :href="route('tipos-analisis.index')" :current="request()->routeIs('tipos-analisis.*')" wire:navigate>
                {{ __('Tipos de Análisis') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="building-storefront" :href="route('veterinarias.index')" :current="request()->routeIs('veterinarias.*')" wire:navigate>
                {{ __('Veterinarias') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="beaker" :href="route('muestras.index')" :current="request()->routeIs('muestras.index')" wire:navigate>
                {{ __('Muestras') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="viewfinder-circle" :href="route('muestras.escanear')" :current="request()->routeIs('muestras.escanear')" wire:navigate>
                {{ __('Escanear Muestra') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="document-text" :href="route('plantillas.index')" :current="request()->routeIs('plantillas.*')" wire:navigate>
                {{ __('Plantillas') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="clipboard-document-check" :href="route('analisis.revisar')" :current="request()->routeIs('analisis.revisar') || request()->routeIs('analisis.ver')" wire:navigate>
                {{ __('Revisar Análisis') }}
            </flux:sidebar.item>
        </flux:sidebar.group>

        <flux:sidebar.group :heading="__('Inventario')" class="grid">
            <flux:sidebar.item icon="scale" :href="route('unidades-medida.index')" :current="request()->routeIs('unidades-medida.*')" wire:navigate>
                {{ __('Unidades de Medida') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="cube" :href="route('insumos.index')" :current="request()->routeIs('insumos.*')" wire:navigate>
                {{ __('Insumos') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="tag" :href="route('categorias-insumo.index')" :current="request()->routeIs('categorias-insumo.*')" wire:navigate>
                {{ __('Categorías de Insumos') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="arrow-down-tray" :href="route('inventario.entradas')" :current="request()->routeIs('inventario.entradas')" wire:navigate>
                {{ __('Registrar Entrada') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="arrow-up-tray" :href="route('inventario.salidas')" :current="request()->routeIs('inventario.salidas')" wire:navigate>
                {{ __('Salidas Manuales') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="clipboard-document-list" :href="route('inventario.historial')" :current="request()->routeIs('inventario.historial')" wire:navigate>
                {{ __('Historial') }}
            </flux:sidebar.item>
        </flux:sidebar.group>

        <flux:sidebar.group :heading="__('Administración')" class="grid">
            <flux:sidebar.item icon="shield-check" :href="route('roles.index')" :current="request()->routeIs('roles.*')" wire:navigate>
                {{ __('Roles') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="lock-closed" :href="route('permisos.index')" :current="request()->routeIs('permisos.*')" wire:navigate>
                {{ __('Permisos') }}
            </flux:sidebar.item>
        </flux:sidebar.group>
    </flux:sidebar.nav>

    <flux:spacer />

    <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
</flux:sidebar>
