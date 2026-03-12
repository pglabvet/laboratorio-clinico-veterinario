{{-- Componente de edición: Campos Etiquetados --}}
<div 
    wire:ignore
    x-data="{
        datosExistentes: @js($componentesData[$index]['data'] ?? []),
        titulo: @js($componente['propiedades']['titulo'] ?? ''),
        campos: @js(collect($componente['propiedades']['campos'] ?? [])->filter()->mapWithKeys(fn($campo, $i) => [$i => ['nombre' => $campo, 'valor' => '']])),
        init() {
            // Convertir a array si es objeto (ocurre cuando PHP preserva keys no secuenciales)
            let existentes = this.datosExistentes;
            if (existentes && !Array.isArray(existentes) && typeof existentes === 'object') {
                // Verificar si tiene titulo guardado
                if (existentes.titulo !== undefined) {
                    this.titulo = existentes.titulo;
                }
                // Los campos pueden estar en existentes.campos o directamente como array
                if (existentes.campos) {
                    existentes = existentes.campos;
                } else if (!Array.isArray(existentes)) {
                    existentes = Object.values(existentes);
                }
            }

            // Cargar datos existentes buscando por nombre
            if (Array.isArray(existentes) && existentes.length > 0) {
                Object.keys(this.campos).forEach(key => {
                    const nombre = this.campos[key].nombre;
                    const match = existentes.find(item => item && item.nombre === nombre);
                    if (match) {
                        this.campos[key].valor = match.valor || '';
                    }
                });
            }
            
            // Sincronizar antes de guardar
            window.addEventListener('antes-de-guardar', () => {
                this.sincronizarConLivewire();
            });

            window.addEventListener('livewire:initialized', () => {
                Livewire.hook('morph.updating', () => {
                    this.sincronizarConLivewire();
                });
            });
        },
        sincronizarConLivewire() {
            const data = {
                titulo: this.titulo,
                campos: Object.values(this.campos)
            };
            window.__labvetData = window.__labvetData || {};
            window.__labvetData['{{ $index }}'] = data;
            $wire.set('componentesData.{{ $index }}.data', data);
        }
    }"
    class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    
    <div class="mb-4">
        <input 
            type="text"
            x-model="titulo"
            @change="sincronizarConLivewire()"
            @blur="sincronizarConLivewire()"
            placeholder="Título del componente"
            class="w-full px-3 py-2 text-lg font-bold text-center border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500"
        />
    </div>

    <div class="space-y-3">
        @foreach($componente['propiedades']['campos'] ?? [] as $i => $campo)
            @if($campo)
            <div class="grid grid-cols-12 gap-3 items-center">
                {{-- Etiqueta del campo --}}
                <label class="col-span-4 font-semibold text-gray-700 dark:text-zinc-300 text-sm">
                    {{ $campo }}:
                </label>
                
                {{-- Input editable --}}
                <div class="col-span-8">
                    <input 
                        type="text"
                        x-model="campos[{{ $i }}].valor"
                        @change="sincronizarConLivewire()"
                        @blur="sincronizarConLivewire()"
                        placeholder="Completar..."
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500"
                    />
                </div>
            </div>
            @endif
        @endforeach
    </div>
</div>
