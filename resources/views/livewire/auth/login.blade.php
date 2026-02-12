<x-layouts.auth>
    {{-- Logo --}}
    <div class="flex flex-col items-center gap-3 mb-2">
        <div class="flex h-20 w-20 items-center justify-center rounded-2xl border border-cyan-800/30 bg-cyan-950/70 p-2">
            <img src="{{ asset('images/LOGO_DARK.webp') }}" alt="PG LabVet" class="h-14 w-14 object-contain" />
        </div>
        <div class="text-center">
            <h1 class="text-xl font-semibold tracking-tight text-white">PG LABVET</h1>
            <p class="text-sm text-neutral-500">Laboratorio Clínico Veterinario</p>
        </div>
    </div>

    {{-- Encabezado del formulario --}}
    <div class="mb-2">
        <h2 class="text-[22px] font-semibold tracking-tight text-white">Bienvenido de vuelta</h2>
        <p class="mt-1 text-sm text-neutral-500">Ingresa tus credenciales para continuar</p>
    </div>

    {{-- Session Status --}}
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5" id="login-form">
        @csrf

        {{-- Email --}}
        <flux:input
            name="email"
            :label="__('Correo electrónico')"
            :value="old('email')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="correo@ejemplo.com"
        />

        {{-- Password --}}
        <flux:input
            name="password"
            :label="__('Contraseña')"
            type="password"
            required
            autocomplete="current-password"
            :placeholder="__('Contraseña')"
            viewable
        />

        {{-- Remember Me --}}
        <flux:checkbox name="remember" :label="__('Recordarme')" :checked="old('remember')" />

        {{-- Submit --}}
        <div class="pt-1">
            <flux:button variant="primary" type="submit" class="login-btn w-full" data-test="login-button">
                {{ __('Iniciar Sesión') }}
            </flux:button>
        </div>
    </form>

    <script>
        document.getElementById('login-form').addEventListener('submit', function () {
            const btn = this.querySelector('[data-test="login-button"]');
            if (btn) {
                btn.disabled = true;
                btn.style.opacity = '0.6';
                btn.style.pointerEvents = 'none';
                btn.innerHTML = 'Validando...';
            }
        });
    </script>


</x-layouts.auth>
