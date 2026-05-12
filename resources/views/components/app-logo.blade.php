@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-12 items-center justify-center">
            <img src="{{ asset('images/LOGO_LIGHT.png') }}" alt="LabVet Logo" class="size-12 object-contain dark:hidden" />
            <img src="{{ asset('images/LOGO_DARK.webp') }}" alt="LabVet Logo" class="size-12 object-contain hidden dark:block" />
        </x-slot>
        <x-slot name="name">
            <div class="flex flex-col">
                <span class="font-semibold">PG LABVET</span>
                <span class="text-xs text-neutral-500 dark:text-neutral-400">Laboratorio Clínico Veterinario</span>
            </div>
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-12 items-center justify-center">
            <img src="{{ asset('images/LOGO_LIGHT.png') }}" alt="LabVet Logo" class="size-12 object-contain dark:hidden" />
            <img src="{{ asset('images/LOGO_DARK.webp') }}" alt="LabVet Logo" class="size-12 object-contain hidden dark:block" />
        </x-slot>
        <x-slot name="name">
            <div class="flex flex-col">
                <span class="font-semibold">PG LABVET</span>
                <span class="text-xs text-neutral-500 dark:text-neutral-400">Laboratorio Clínico Veterinario</span>
            </div>
        </x-slot>
    </flux:brand>
@endif
