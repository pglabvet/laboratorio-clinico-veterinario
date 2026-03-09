{{-- Componente de edición: Tabla de Dos Columnas --}}
@php
    // Pre-calcular todos los campos para evitar errores de sintaxis en JavaScript
    $todosCampos = [];
    foreach(($componente['propiedades']['secciones'] ?? []) as $seccionIndex => $seccion) {
        foreach(($seccion['campos'] ?? []) as $campoIndex => $campo) {
            if ($campo) {
                $key = $seccionIndex . '_' . $campoIndex;
                $todosCampos[$key] = [
                    'seccion' => $seccion['subtitulo'] ?? '',
                    'campo' => $campo,
                    'valor' => ''
                ];
            }
        }
    }
@endphp

<div 
    wire:ignore
    x-data="{
    datosExistentes: @js($componentesData[$index]['data'] ?? []),
    datos: @js($todosCampos),
    init() {
        // Convertir a array si es objeto (ocurre cuando PHP array_filter preserva keys no secuenciales)
        let existentes = this.datosExistentes;
        if (existentes && !Array.isArray(existentes)) {
            existentes = Object.values(existentes);
        }

        // Cargar datos existentes buscando por nombre de campo
        if (Array.isArray(existentes) && existentes.length > 0) {
            Object.keys(this.datos).forEach(key => {
                const campoName = this.datos[key].campo;
                const match = existentes.find(item => item && item.campo === campoName);
                if (match) {
                    this.datos[key].valor = match.valor || '';
                }
            });
        }
        
        // Escuchar evento de guardado para forzar sincronización
        window.addEventListener('antes-de-guardar', () => {
            this.sincronizarConLivewire();
        });
        
        // Sincronizar antes de cualquier acción de Livewire (como hace campos-etiquetados)
        window.addEventListener('livewire:initialized', () => {
            Livewire.hook('morph.updating', () => {
                this.sincronizarConLivewire();
            });
        });
    },
    sincronizarConLivewire() {
        const data = Object.values(this.datos);
        window.__labvetData = window.__labvetData || {};
        window.__labvetData['{{ $index }}'] = data;
        $wire.set('componentesData.{{ $index }}.data', data);
    }
}"
class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    @if(isset($componente['propiedades']['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-lg mb-4">
        {{ $componente['propiedades']['titulo'] }}
    </h4>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-300 dark:border-zinc-700 text-sm">
            @foreach($componente['propiedades']['secciones'] ?? [] as $seccion)
                {{-- Subtítulo de sección si existe --}}
                @if($seccion['subtitulo'] ?? null)
                <tr>
                    <td colspan="2" class="bg-gray-100 dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 px-3 py-2 font-bold text-center text-gray-900 dark:text-zinc-100">
                        {{ $seccion['subtitulo'] }}
                    </td>
                </tr>
                @endif

                {{-- Campos de la sección --}}
                @foreach($seccion['campos'] ?? [] as $campoIndex => $campo)
                    @if($campo)
                    <tr>
                        {{-- Label fijo --}}
                        <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold bg-gray-50 dark:bg-zinc-900 w-1/3 text-gray-900 dark:text-zinc-100">
                            {{ $campo }}
                        </td>
                        
                        {{-- Input editable --}}
                        <td class="border border-gray-300 dark:border-zinc-700 px-2 py-2">
                            <input 
                                type="text"
                                x-model="datos['{{ $loop->parent->index }}_{{ $campoIndex }}'].valor"
                                @change="sincronizarConLivewire()"
                                @blur="sincronizarConLivewire()"
                                placeholder="Completar..."
                                class="w-full px-2 py-1 border-0 focus:ring-2 focus:ring-blue-500 rounded bg-transparent text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500"
                            />
                        </td>
                    </tr>
                    @endif
                @endforeach
            @endforeach
        </table>
    </div>
</div>
