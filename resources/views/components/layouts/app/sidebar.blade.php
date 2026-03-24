{{-- Sidebar Component - Navigation Only --}}
<flux:sidebar sticky stashable class="border-e border-zinc-300 bg-slate-200 shadow-md dark:border-zinc-700 dark:bg-zinc-900 lg:w-72">
    <flux:sidebar.header>
        <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
    </flux:sidebar.header>

    <flux:sidebar.nav>
        {{-- Principal --}}
        <flux:sidebar.group :heading="__('Principal')" class="grid">
            @can('ver-dashboard')
            <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </flux:sidebar.item>
            @endcan
        </flux:sidebar.group>

        {{-- Laboratorio: flujo de trabajo diario --}}
        @canany(['ver-muestras', 'escanear-muestras', 'ver-analisis'])
        <flux:sidebar.group :heading="__('Laboratorio')" class="grid">
            @can('ver-muestras')
            <flux:sidebar.item icon="beaker" :href="route('muestras.index')" :current="request()->routeIs('muestras.index')" wire:navigate>
                {{ __('Muestras') }}
            </flux:sidebar.item>
            @endcan
            @can('escanear-muestras')
            <flux:sidebar.item icon="viewfinder-circle" :href="route('muestras.escanear')" :current="request()->routeIs('muestras.escanear')" wire:navigate>
                {{ __('Escanear Muestra') }}
            </flux:sidebar.item>
            @endcan
            @can('ver-analisis')
            <flux:sidebar.item icon="clipboard-document-check" :href="route('analisis.revisar')" :current="request()->routeIs('analisis.revisar') || request()->routeIs('analisis.ver')" wire:navigate>
                {{ __('Revisar Análisis') }}
            </flux:sidebar.item>
            @endcan
        </flux:sidebar.group>
        @endcanany

        {{-- Muestras Rechazadas --}}
        @can('ver-muestras-rechazadas')
        <flux:sidebar.group :heading="__('Muestras Rechazadas')" class="grid">
            <flux:sidebar.item icon="x-circle" :href="route('muestras-rechazadas.index')" :current="request()->routeIs('muestras-rechazadas.*')" wire:navigate>
                {{ __('Muestras Rechazadas') }}
            </flux:sidebar.item>
        </flux:sidebar.group>
        @endcan

        {{-- Configuración: se configura una vez --}}
        @canany(['ver-sucursales', 'ver-especies', 'ver-veterinarias', 'ver-tipos-analisis', 'ver-plantillas'])
        <flux:sidebar.group :heading="__('Catálogos')" class="grid">
            @can('ver-sucursales')
            <flux:sidebar.item icon="building-office-2" :href="route('sucursales.index')" :current="request()->routeIs('sucursales.*')" wire:navigate>
                {{ __('Sucursales') }}
            </flux:sidebar.item>
            @endcan
            @can('ver-especies')
            <flux:sidebar.item icon="rectangle-group" :href="route('especies.index')" :current="request()->routeIs('especies.*')" wire:navigate>
                {{ __('Especies') }}
            </flux:sidebar.item>
            @endcan
            @can('ver-veterinarias')
            <flux:sidebar.item icon="building-storefront" :href="route('veterinarias.index')" :current="request()->routeIs('veterinarias.*')" wire:navigate>
                {{ __('Veterinarias') }}
            </flux:sidebar.item>
            @endcan
            @can('ver-tipos-analisis')
            <flux:sidebar.item icon="clipboard-document-list" :href="route('tipos-analisis.index')" :current="request()->routeIs('tipos-analisis.*')" wire:navigate>
                {{ __('Tipos de Análisis') }}
            </flux:sidebar.item>
            @endcan
            @can('ver-plantillas')
            <flux:sidebar.item icon="document-text" :href="route('plantillas.index')" :current="request()->routeIs('plantillas.*')" wire:navigate>
                {{ __('Plantillas') }}
            </flux:sidebar.item>
            @endcan
        </flux:sidebar.group>
        @endcanany

        @canany(['ver-unidades-medida', 'ver-insumos', 'ver-categorias-insumo', 'ver-historial-inventario', 'ver-registrar-entrada', 'ver-salidas-manuales', 'ver-kardex-peps'])
        <flux:sidebar.group :heading="__('Inventario')" class="grid">
            @can('ver-categorias-insumo')
            <flux:sidebar.item icon="tag" :href="route('categorias-insumo.index')" :current="request()->routeIs('categorias-insumo.*')" wire:navigate>
                {{ __('Categorías de Insumos') }}
            </flux:sidebar.item>
            @endcan
            @can('ver-unidades-medida')
            <flux:sidebar.item icon="scale" :href="route('unidades-medida.index')" :current="request()->routeIs('unidades-medida.*')" wire:navigate>
                {{ __('Unidades de Medida') }}
            </flux:sidebar.item>
            @endcan
            @can('ver-insumos')
            <flux:sidebar.item icon="cube" :href="route('insumos.index')" :current="request()->routeIs('insumos.*')" wire:navigate>
                {{ __('Insumos') }}
            </flux:sidebar.item>
            @endcan
            @can('ver-registrar-entrada')
            <flux:sidebar.item icon="arrow-down-tray" :href="route('inventario.entradas')" :current="request()->routeIs('inventario.entradas')" wire:navigate>
                {{ __('Registrar Entrada') }}
            </flux:sidebar.item>
            @endcan
            @can('ver-salidas-manuales')
            <flux:sidebar.item icon="arrow-up-tray" :href="route('inventario.salidas')" :current="request()->routeIs('inventario.salidas')" wire:navigate>
                {{ __('Salidas Manuales') }}
            </flux:sidebar.item>
            @endcan
            @can('ver-historial-inventario')
            <flux:sidebar.item icon="clipboard-document-list" :href="route('inventario.historial')" :current="request()->routeIs('inventario.historial')" wire:navigate>
                {{ __('Historial') }}
            </flux:sidebar.item>
            @endcan
            @can('ver-kardex-peps')
            <flux:sidebar.item icon="document-chart-bar" :href="route('inventario.kardex')" :current="request()->routeIs('inventario.kardex')" wire:navigate>
                {{ __('Kardex PEPS') }}
            </flux:sidebar.item>
            @endcan
        </flux:sidebar.group>
        @endcanany

        @canany(['ver-usuarios', 'ver-roles', 'ver-auditorias'])
        <flux:sidebar.group :heading="__('Administración')" class="grid">
            @can('ver-usuarios')
            <flux:sidebar.item icon="users" :href="route('usuarios.index')" :current="request()->routeIs('usuarios.*')" wire:navigate>
                {{ __('Usuarios') }}
            </flux:sidebar.item>
            @endcan
            
            @can('ver-roles')
            <flux:sidebar.item icon="shield-check" :href="route('roles.index')" :current="request()->routeIs('roles.*')" wire:navigate>
                {{ __('Roles') }}
            </flux:sidebar.item>
            @endcan

            @can('ver-auditorias')
            <flux:sidebar.item icon="eye" :href="route('auditorias.index')" :current="request()->routeIs('auditorias.*')" wire:navigate>
                {{ __('Auditorías') }}
            </flux:sidebar.item>
            @endcan
        </flux:sidebar.group>
        @endcanany

        {{-- Guía del Sistema - solo con permiso ver-ayuda --}}
        @can('ver-ayuda')
        <flux:sidebar.group :heading="__('Ayuda')" class="grid">
            <flux:sidebar.item icon="information-circle" :href="route('guia.index')" :current="request()->routeIs('guia.*')" wire:navigate>
                {{ __('Guía del Sistema') }}
            </flux:sidebar.item>
        </flux:sidebar.group>
        @endcan
    </flux:sidebar.nav>

    <flux:spacer />

    <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
</flux:sidebar>
