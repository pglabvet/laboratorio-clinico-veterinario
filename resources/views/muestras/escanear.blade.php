<x-layouts.app>
    <x-slot:heading>
        <div class="flex items-center gap-3">
            <x-lucide-scan class="size-8 text-blue-600" />
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                    Sistema Laboratorio Clínico Veterinario
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">Rol: Bioquímico</p>
            </div>
        </div>
    </x-slot:heading>

    <livewire:muestras.escanear-muestra />
</x-layouts.app>
