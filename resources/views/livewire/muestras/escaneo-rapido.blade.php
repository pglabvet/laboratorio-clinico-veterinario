<div class="bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
    <div class="flex items-center gap-3">
        <div class="flex-shrink-0">
            <div class="bg-blue-600 p-3 rounded-lg">
                <x-lucide-scan class="size-6 text-white" />
            </div>
        </div>
        
        <div class="flex-1">
            <flux:heading size="lg" class="mb-1">Escaneo Rápido</flux:heading>
            <flux:subheading class="text-blue-700 dark:text-blue-300">
                Escanee el código de barras de la muestra para acceder rápidamente
            </flux:subheading>
        </div>
    </div>

    <div class="mt-4">
        <flux:input 
            wire:model.live="codigo"
            placeholder="Escanee aquí el código de barras..."
            autofocus
            class="font-mono text-lg"
        />
        <flux:description class="mt-2">
            <div class="flex items-center gap-2 text-blue-700 dark:text-blue-300">
                <x-lucide-info class="size-4" />
                <span>Coloque el cursor en este campo y escanee el código de barras</span>
            </div>
        </flux:description>
    </div>

    @if($escaneando)
        <div class="mt-3 flex items-center gap-2 text-blue-600 dark:text-blue-400">
            <div class="animate-spin">
                <x-lucide-loader-2 class="size-4" />
            </div>
            <span class="text-sm">Buscando muestra...</span>
        </div>
    @endif
</div>
