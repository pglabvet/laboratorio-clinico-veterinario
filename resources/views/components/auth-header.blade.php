@props([
    'title' => null,
    'description' => null,
])

<div class="flex w-full flex-col items-center text-center mb-4">
    {{-- Logo de la empresa --}}
    <div class="mb-4">
        <img src="{{ asset('images/LOGO_LIGHT.png') }}" alt="PG LabVet Logo" class="h-20 w-auto object-contain dark:hidden" />
        <img src="{{ asset('images/LOGO_DARK.webp') }}" alt="PG LabVet Logo" class="h-20 w-auto object-contain hidden dark:block" />
    </div>
    
    {{-- Nombre de la empresa --}}
    <div>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">PG LABVET</h1>
        <p class="text-sm text-neutral-600 dark:text-neutral-400">Laboratorio Clínico Veterinario</p>
    </div>
</div>
