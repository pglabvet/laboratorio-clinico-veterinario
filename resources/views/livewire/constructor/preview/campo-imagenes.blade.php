<!-- Preview de Campo de Imágenes -->
<div class="space-y-3">
    @if(isset($props['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center">{{ $props['titulo'] }}</h4>
    @endif

    @if($props['permitir'] ?? true)
        <div class="grid grid-cols-2 gap-4">
            <!-- Imagen 1 -->
            <div class="border-2 border-dashed border-gray-300 dark:border-zinc-600 rounded-lg p-6 bg-gray-50 dark:bg-zinc-900 text-center"
                 style="min-height: 200px;">
                <div class="flex flex-col items-center justify-center h-full">
                    <i class="fas fa-image text-4xl text-gray-300 dark:text-zinc-600 mb-2"></i>
                    <p class="text-sm text-gray-500 dark:text-zinc-400">Imagen 1</p>
                    <p class="text-xs text-gray-400 dark:text-zinc-500 mt-1">Aquí se mostrará la primera imagen</p>
                </div>
            </div>
            
            <!-- Imagen 2 -->
            <div class="border-2 border-dashed border-gray-300 dark:border-zinc-600 rounded-lg p-6 bg-gray-50 dark:bg-zinc-900 text-center"
                 style="min-height: 200px;">
                <div class="flex flex-col items-center justify-center h-full">
                    <i class="fas fa-image text-4xl text-gray-300 dark:text-zinc-600 mb-2"></i>
                    <p class="text-sm text-gray-500 dark:text-zinc-400">Imagen 2</p>
                    <p class="text-xs text-gray-400 dark:text-zinc-500 mt-1">Aquí se mostrará la segunda imagen</p>
                </div>
            </div>
        </div>
    @else
        <p class="text-sm text-gray-400 dark:text-zinc-500 italic">Campo de imágenes deshabilitado</p>
    @endif
</div>
