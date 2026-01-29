{{-- Componente de edición: Campos Etiquetados --}}
<div 
    wire:ignore
    x-data="{
        datosExistentes: @js($componentesData[$index]['data'] ?? []),
        campos: {
            @foreach($componente['propiedades']['campos'] ?? [] as $i => $campo)
                @if($campo)
                {{ $i }}: { nombre: '{{ addslashes($campo) }}', valor: '' }{{ $loop->last ? '' : ',' }}
                @endif
            @endforeach
        },
        init() {
            // Cargar datos existentes si existen
            if (Array.isArray(this.datosExistentes) && this.datosExistentes.length > 0) {
                this.datosExistentes.forEach((item, i) => {
                    if (this.campos[i]) {
                        this.campos[i].valor = item.valor || '';
                    }
                });
            }
            
            // Sincronizar antes de cualquier acción de Livewire
            window.addEventListener('livewire:initialized', () => {
                Livewire.hook('morph.updating', () => {
                    this.sincronizarConLivewire();
                });
            });
        },
        sincronizarConLivewire() {
            $wire.set('componentesData.{{ $index }}.data', Object.values(this.campos));
        }
    }"
    class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    @if(isset($componente['propiedades']['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-lg mb-4">
        {{ $componente['propiedades']['titulo'] }}
    </h4>
    @endif

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
