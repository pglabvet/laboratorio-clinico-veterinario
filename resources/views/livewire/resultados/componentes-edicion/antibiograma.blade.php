{{-- Componente de edición: Antibiograma --}}
@php
    $datosIniciales = $componentesData[$index]['data'] ?? [];
@endphp

<div 
    wire:ignore
    x-data="{
        filas: [],
        
        init() {
            const datosExistentes = @js($datosIniciales);
            
            if (Array.isArray(datosExistentes) && datosExistentes.length > 0) {
                // Cargar datos existentes con IDs únicos
                this.filas = datosExistentes.map((fila) => ({
                    id: this.generarId(),
                    sensible: fila.sensible || '',
                    intermedio: fila.intermedio || '',
                    resistente: fila.resistente || ''
                }));
            } else {
                // Inicializar 5 filas vacías
                for (let i = 0; i < 5; i++) {
                    this.agregarFilaSinSync();
                }
            }
            
            // Escuchar evento de guardado para forzar sincronización
            window.addEventListener('antes-de-guardar', () => {
                this.sincronizarConLivewire();
            });
        },
        
        generarId() {
            return Date.now().toString(36) + Math.random().toString(36).substr(2);
        },
        
        agregarFila() {
            this.agregarFilaSinSync();
            this.sincronizarConLivewire();
        },

        agregarFilaSinSync() {
            this.filas.push({
                id: this.generarId(),
                sensible: '',
                intermedio: '',
                resistente: ''
            });
        },
        
        eliminarFila(id) {
            if (this.filas.length > 1) {
                // Usar splice para mejor reactividad en arrays
                const index = this.filas.findIndex(f => f.id === id);
                if (index > -1) {
                    this.filas.splice(index, 1);
                    this.sincronizarConLivewire();
                }
            }
        },
        
        sincronizarConLivewire() {
            // Mapeamos solo los datos necesarios para evitar enviar los IDs generados
            const datosLimpios = this.filas.map(f => ({
                sensible: f.sensible,
                intermedio: f.intermedio,
                resistente: f.resistente
            }));
            window.__labvetData = window.__labvetData || {};
            window.__labvetData['{{ $index }}'] = datosLimpios;
            $wire.set('componentesData.{{ $index }}.data', datosLimpios);
            window.dispatchEvent(new CustomEvent('datos-sincronizados', { detail: { index: {{ $index }} } }));
        }
    }"
    class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900"
>
    @if(isset($componente['propiedades']['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-lg mb-4">
        {{ $componente['propiedades']['titulo'] }}
    </h4>
    @endif

    {{-- Tabla editable --}}
    <div class="overflow-x-auto mb-4">
        <table class="w-full border border-gray-300 dark:border-zinc-700 text-sm">
            <thead>
                <tr class="bg-gray-100 dark:bg-zinc-900">
                    <th class="border border-gray-300 dark:border-zinc-700 px-4 py-3 font-bold text-gray-900 dark:text-zinc-100 w-1/3">
                        SENSIBLE
                    </th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-4 py-3 font-bold text-gray-900 dark:text-zinc-100 w-1/3">
                        INTERMEDIO
                    </th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-4 py-3 font-bold text-gray-900 dark:text-zinc-100 w-1/3">
                        RESISTENTE
                    </th>
                    <th class="border border-gray-300 dark:border-zinc-700 px-2 py-3 w-12"></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="fila in filas" :key="fila.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800">
                        {{-- Columna SENSIBLE --}}
                        <td class="border border-gray-300 dark:border-zinc-700 px-2 py-2">
                            <input 
                                type="text"
                                x-model="fila.sensible"
                                @input="fila.sensible = $event.target.value.toUpperCase()"
                                @blur="sincronizarConLivewire()"
                                placeholder="Ej: LEVOFLOXACINA"
                                class="w-full px-2 py-2 border-0 focus:ring-2 focus:ring-blue-500 rounded bg-transparent text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 uppercase"
                            />
                        </td>
                        
                        {{-- Columna INTERMEDIO --}}
                        <td class="border border-gray-300 dark:border-zinc-700 px-2 py-2">
                            <input 
                                type="text"
                                x-model="fila.intermedio"
                                @input="fila.intermedio = $event.target.value.toUpperCase()"
                                @blur="sincronizarConLivewire()"
                                placeholder="Ej: AZITROMICINA"
                                class="w-full px-2 py-2 border-0 focus:ring-2 focus:ring-blue-500 rounded bg-transparent text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 uppercase"
                            />
                        </td>
                        
                        {{-- Columna RESISTENTE --}}
                        <td class="border border-gray-300 dark:border-zinc-700 px-2 py-2">
                            <input 
                                type="text"
                                x-model="fila.resistente"
                                @input="fila.resistente = $event.target.value.toUpperCase()"
                                @blur="sincronizarConLivewire()"
                                placeholder="Ej: AMOXICILINA"
                                class="w-full px-2 py-2 border-0 focus:ring-2 focus:ring-blue-500 rounded bg-transparent text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 uppercase"
                            />
                        </td>
                        
                        {{-- Botón eliminar --}}
                        <td class="border border-gray-300 dark:border-zinc-700 px-2 py-2 text-center">
                            <button 
                                @click="eliminarFila(fila.id)"
                                type="button"
                                class="p-1.5 text-red-600 dark:text-red-400 hover:text-white dark:hover:text-white hover:bg-red-600 dark:hover:bg-red-500 rounded transition-all"
                                x-show="filas.length > 1"
                                title="Eliminar fila">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Botones de acción --}}
    <div class="flex gap-2">
        <flux:button 
            @click="agregarFila()"
            variant="outline" 
            icon="plus" 
            size="sm">
            Agregar Fila
        </flux:button>
    </div>

    {{-- Ayuda visual --}}
    <div class="mt-3 p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded text-xs text-yellow-800 dark:text-yellow-300">
        <i class="fas fa-info-circle mr-1"></i>
        Agregue los antibióticos probados y marque la sensibilidad correspondiente
    </div>

    {{-- Repeticiones (solo si hay reactivos asignados a nivel componente) --}}
    @include('livewire.resultados.componentes-edicion._repeticiones-reactivos', [
        'componente' => $componente,
        'index' => $index,
    ])
</div>