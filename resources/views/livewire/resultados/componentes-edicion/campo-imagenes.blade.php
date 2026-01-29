{{-- Componente de edición: Campo de Imágenes --}}
<div class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    @if(isset($componente['propiedades']['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-lg mb-4">
        {{ $componente['propiedades']['titulo'] }}
    </h4>
    @endif

    <div class="grid grid-cols-2 gap-4">
        {{-- Imagen 1 --}}
        <div>
            <input 
                type="file" 
                id="imagen1_{{ $index }}"
                wire:model="imagenes.{{ $index }}.imagen1"
                accept="image/*"
                class="hidden"
            />
            
            <label 
                for="imagen1_{{ $index }}"
                class="border-2 border-dashed border-gray-300 dark:border-zinc-600 rounded-lg p-6 bg-gray-50 dark:bg-zinc-900 text-center cursor-pointer hover:bg-gray-100 dark:hover:bg-zinc-800 transition block"
                style="min-height: 200px;">
                
                @if(isset($imagenes[$index]['imagen1']))
                    {{-- Previsualización de imagen temporal nueva --}}
                    <div class="relative">
                        <img 
                            src="{{ $imagenes[$index]['imagen1']->temporaryUrl() }}" 
                            alt="Imagen 1"
                            class="max-h-48 mx-auto rounded"
                        />
                        <button 
                            type="button"
                            wire:click="$set('imagenes.{{ $index }}.imagen1', null)"
                            class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @elseif(isset($imagenes[$index]['preview1']))
                    {{-- Previsualización de imagen guardada --}}
                    <div class="relative">
                        <img 
                            src="{{ \Illuminate\Support\Facades\Storage::url($imagenes[$index]['preview1']) }}" 
                            alt="Imagen 1"
                            class="max-h-48 mx-auto rounded"
                        />
                        <div class="absolute top-2 left-2 bg-green-500 text-white rounded-full px-3 py-1 text-xs font-medium">
                            <i class="fas fa-check mr-1"></i> Guardada
                        </div>
                        <button 
                            type="button"
                            wire:click="eliminarImagenGuardada({{ $index }}, 'preview1')"
                            class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600"
                            title="Eliminar imagen"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @else
                    {{-- Estado vacío --}}
                    <div class="flex flex-col items-center justify-center h-full">
                        <i class="fas fa-cloud-upload-alt text-4xl text-blue-500 dark:text-blue-400 mb-3"></i>
                        <p class="text-sm font-medium text-gray-700 dark:text-zinc-300 mb-1">Subir Imagen 1</p>
                        <p class="text-xs text-gray-500 dark:text-zinc-400">Click o arrastra aquí</p>
                        <p class="text-xs text-gray-400 dark:text-zinc-500 mt-2">PNG, JPG hasta 10MB</p>
                    </div>
                @endif
            </label>
            
            {{-- Indicador de carga --}}
            <div wire:loading wire:target="imagenes.{{ $index }}.imagen1" class="mt-2">
                <div class="flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm">Subiendo imagen...</span>
                </div>
            </div>
            
            @error("imagenes.{$index}.imagen1")
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        
        {{-- Imagen 2 --}}
        <div>
            <input 
                type="file" 
                id="imagen2_{{ $index }}"
                wire:model="imagenes.{{ $index }}.imagen2"
                accept="image/*"
                class="hidden"
            />
            
            <label 
                for="imagen2_{{ $index }}"
                class="border-2 border-dashed border-gray-300 dark:border-zinc-600 rounded-lg p-6 bg-gray-50 dark:bg-zinc-900 text-center cursor-pointer hover:bg-gray-100 dark:hover:bg-zinc-800 transition block"
                style="min-height: 200px;">
                
                @if(isset($imagenes[$index]['imagen2']))
                    {{-- Previsualización de imagen temporal nueva --}}
                    <div class="relative">
                        <img 
                            src="{{ $imagenes[$index]['imagen2']->temporaryUrl() }}" 
                            alt="Imagen 2"
                            class="max-h-48 mx-auto rounded"
                        />
                        <button 
                            type="button"
                            wire:click="$set('imagenes.{{ $index }}.imagen2', null)"
                            class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @elseif(isset($imagenes[$index]['preview2']))
                    {{-- Previsualización de imagen guardada --}}
                    <div class="relative">
                        <img 
                            src="{{ \Illuminate\Support\Facades\Storage::url($imagenes[$index]['preview2']) }}" 
                            alt="Imagen 2"
                            class="max-h-48 mx-auto rounded"
                        />
                        <div class="absolute top-2 left-2 bg-green-500 text-white rounded-full px-3 py-1 text-xs font-medium">
                            <i class="fas fa-check mr-1"></i> Guardada
                        </div>
                        <button 
                            type="button"
                            wire:click="eliminarImagenGuardada({{ $index }}, 'preview2')"
                            class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600"
                            title="Eliminar imagen"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @else
                    {{-- Estado vacío --}}
                    <div class="flex flex-col items-center justify-center h-full">
                        <i class="fas fa-cloud-upload-alt text-4xl text-blue-500 dark:text-blue-400 mb-3"></i>
                        <p class="text-sm font-medium text-gray-700 dark:text-zinc-300 mb-1">Subir Imagen 2</p>
                        <p class="text-xs text-gray-500 dark:text-zinc-400">Click o arrastra aquí</p>
                        <p class="text-xs text-gray-400 dark:text-zinc-500 mt-2">PNG, JPG hasta 10MB</p>
                    </div>
                @endif
            </label>
            
            {{-- Indicador de carga --}}
            <div wire:loading wire:target="imagenes.{{ $index }}.imagen2" class="mt-2">
                <div class="flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm">Subiendo imagen...</span>
                </div>
            </div>
            
            @error("imagenes.{$index}.imagen2")
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="mt-3 p-2 bg-blue-50 dark:bg-blue-900/20 rounded text-xs text-blue-800 dark:text-blue-300">
        <i class="fas fa-info-circle mr-1"></i>
        Puede subir hasta 2 imágenes que se mostrarán en el reporte final
    </div>
</div>
