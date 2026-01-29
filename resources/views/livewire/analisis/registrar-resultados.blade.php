<div class="space-y-6">
    {{-- Encabezado --}}
    <div>
        <flux:heading size="xl" class="mb-1">Registrar Resultados</flux:heading>
        <flux:subheading>Completar los valores obtenidos en el análisis</flux:subheading>
    </div>

    {{-- Información del análisis --}}
    <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6">
        <flux:heading size="lg" class="mb-4 text-center text-blue-600">
            {{ strtoupper($analisis->tipoAnalisis->nombre) }}
        </flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="font-semibold text-neutral-700 dark:text-neutral-300">PACIENTE:</span>
                <span class="text-neutral-900 dark:text-neutral-100">{{ $analisis->muestra->paciente_nombre }}</span>
            </div>
            <div>
                <span class="font-semibold text-neutral-700 dark:text-neutral-300">CÓDIGO:</span>
                <span class="text-neutral-900 dark:text-neutral-100 font-mono">{{ $analisis->muestra->codigo_muestra }}</span>
            </div>
            <div>
                <span class="font-semibold text-neutral-700 dark:text-neutral-300">EDAD:</span>
                <span class="text-neutral-900 dark:text-neutral-100">{{ $analisis->muestra->edad }}</span>
            </div>
            <div>
                <span class="font-semibold text-neutral-700 dark:text-neutral-300">PROPIETARIO:</span>
                <span class="text-neutral-900 dark:text-neutral-100">{{ $analisis->muestra->propietario_nombre }}</span>
            </div>
            <div>
                <span class="font-semibold text-neutral-700 dark:text-neutral-300">SEXO:</span>
                <span class="text-neutral-900 dark:text-neutral-100">{{ $analisis->muestra->sexo === 'M' ? 'Macho' : 'Hembra' }}</span>
            </div>
            <div>
                <span class="font-semibold text-neutral-700 dark:text-neutral-300">ESPECIE:</span>
                <span class="text-neutral-900 dark:text-neutral-100">{{ $analisis->muestra->especie->nombre }}</span>
            </div>
            <div>
                <span class="font-semibold text-neutral-700 dark:text-neutral-300">SOLICITADO POR:</span>
                <span class="text-neutral-900 dark:text-neutral-100">{{ $analisis->muestra->veterinaria->nombre ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="font-semibold text-neutral-700 dark:text-neutral-300">RAZA:</span>
                <span class="text-neutral-900 dark:text-neutral-100">{{ $analisis->muestra->raza ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="font-semibold text-neutral-700 dark:text-neutral-300">FECHA:</span>
                <span class="text-neutral-900 dark:text-neutral-100">{{ $analisis->muestra->fecha_recepcion->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Tabla de resultados --}}
    <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">
                            ANÁLISIS
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">
                            RESULTADO
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">
                            RANGOS DE REFERENCIA ({{ $analisis->muestra->especie->nombre }})
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">
                            ESTADO
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @foreach($analisis->tipoAnalisis->parametros->sortBy('orden') as $parametro)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                            <td class="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $parametro->nombre }}
                            </td>
                            <td class="px-4 py-3">
                                <flux:input 
                                    wire:model.live="resultados.{{ $parametro->id }}.valor"
                                    wire:change="validarRango({{ $parametro->id }})"
                                    type="number"
                                    step="0.01"
                                    placeholder="---"
                                    class="w-32"
                                />
                            </td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                <div>{{ getRangoReferencia($parametro) }}</div>
                                @if($parametro->unidad)
                                    <div class="text-xs text-neutral-500">{{ $parametro->unidad }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if(!empty($resultados[$parametro->id]['valor']))
                                    @if($resultados[$parametro->id]['fuera_rango'] ?? false)
                                        <flux:badge variant="danger">Fuera de rango</flux:badge>
                                    @else
                                        <flux:badge variant="success">Normal</flux:badge>
                                    @endif
                                @else
                                    <span class="text-neutral-400 text-sm">---</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Imágenes Microscópicas / Fotos --}}
    <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center gap-2 mb-4">
            <x-lucide-image class="size-5 text-blue-600" />
            <flux:heading size="lg">Imágenes Microscópicas / Fotos</flux:heading>
        </div>
        <flux:subheading class="mb-4">Adjunte imágenes del análisis microbiológico (opcional)</flux:subheading>

        <div class="space-y-4">
            {{-- Botón para subir imágenes --}}
            <div class="flex items-center gap-3">
                <flux:button 
                    type="button"
                    variant="outline"
                    onclick="document.getElementById('file-upload').click()"
                >
                    <div class="flex items-center gap-2">
                        <x-lucide-upload class="size-4" />
                        <span>Seleccionar Imágenes</span>
                    </div>
                </flux:button>
                <span class="text-xs text-neutral-600 dark:text-neutral-400">PNG, JPG, JPEG hasta 5MB por imagen</span>
            </div>

            <input 
                id="file-upload"
                type="file" 
                wire:model="imagenesTemporales" 
                accept="image/*" 
                multiple 
                class="hidden"
            />

            {{-- Preview de imágenes nuevas --}}
            @if($imagenesTemporales)
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($imagenesTemporales as $key => $imagen)
                        <div class="relative group">
                            <img src="{{ $imagen->temporaryUrl() }}" class="w-full h-32 object-cover rounded-lg border border-neutral-200 dark:border-neutral-700">
                            <button 
                                wire:click="$set('imagenesTemporales', [])"
                                class="absolute top-2 right-2 bg-red-600 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition"
                            >
                                <x-lucide-x class="size-4" />
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Imágenes ya guardadas --}}
            @if($analisis->imagenes->count() > 0)
                <div class="mt-4">
                    <p class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Imágenes guardadas:</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($analisis->imagenes as $imagen)
                            <div class="relative group">
                                <img src="{{ Storage::url($imagen->ruta) }}" class="w-full h-32 object-cover rounded-lg border border-neutral-200 dark:border-neutral-700">
                                <button 
                                    wire:click="eliminarImagen({{ $imagen->id }})"
                                    class="absolute top-2 right-2 bg-red-600 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition"
                                >
                                    <x-lucide-x class="size-4" />
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Observaciones --}}
    <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center gap-2 mb-4">
            <x-lucide-file-text class="size-5 text-blue-600" />
            <flux:heading size="lg">Observaciones</flux:heading>
        </div>
        <flux:subheading class="mb-4">Agregue observaciones adicionales sobre el análisis (opcional)</flux:subheading>

        <flux:textarea 
            wire:model="observaciones"
            rows="4"
            placeholder="Escriba aquí cualquier observación relevante sobre el análisis..."
        />
        <flux:description class="mt-2">
            Caracteres: {{ strlen($observaciones) }}/500
        </flux:description>
    </div>

    {{-- Botones de acción --}}
    <div class="flex justify-between items-center">
        <flux:button 
            href="{{ route('muestras.escanear') }}"
            variant="ghost"
        >
            <x-lucide-arrow-left class="size-4" />
            Volver
        </flux:button>

        <div class="flex gap-3">
            <flux:button 
                wire:click="guardarBorrador"
                variant="outline"
            >
                <div class="flex items-center gap-2">
                    <x-lucide-save class="size-4" />
                    <span>Guardar Borrador</span>
                </div>
            </flux:button>

            <flux:button 
                wire:click="completarYRevisar"
                variant="primary"
            >
                <div class="flex items-center gap-2">
                    <x-lucide-check-circle class="size-4" />
                    <span>Completar y Revisar</span>
                </div>
            </flux:button>
        </div>
    </div>
</div>
