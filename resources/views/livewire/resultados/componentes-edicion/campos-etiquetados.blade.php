{{-- Componente de edición: Campos Etiquetados --}}
@php
    $titulosDisponibles = $componente['propiedades']['titulos'] ?? [];
    $camposConfig = $componente['propiedades']['campos'] ?? [];
    // Normalizar campos: si son strings (formato antiguo), convertir a objetos
    $camposNormalizados = collect($camposConfig)->filter()->map(function($campo, $i) {
        if (is_string($campo)) {
            return ['nombre' => $campo, 'tipo_input' => 'texto', 'opciones' => '', 'unidad' => ''];
        }
        return $campo;
    })->values()->all();
@endphp
<div 
    wire:ignore
    x-data="{
        datosExistentes: @js($componentesData[$index]['data'] ?? []),
        titulosDisponibles: @js($titulosDisponibles),
        tituloSeleccionado: @js($titulosDisponibles[0] ?? ''),
        camposConfig: @js($camposNormalizados),
        campos: {},
        init() {
            // Inicializar campos desde config
            this.camposConfig.forEach((campo, i) => {
                this.campos[i] = {
                    nombre: campo.nombre,
                    valor: '',
                    unidad: campo.unidad || ''
                };
            });

            // Cargar datos existentes
            let existentes = this.datosExistentes;
            if (existentes && !Array.isArray(existentes) && typeof existentes === 'object') {
                if (existentes.titulo !== undefined) {
                    this.tituloSeleccionado = existentes.titulo;
                }
                if (existentes.campos) {
                    existentes = existentes.campos;
                } else if (!Array.isArray(existentes)) {
                    existentes = Object.values(existentes);
                }
            }

            // Cargar valores existentes buscando por nombre
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
            const camposData = Object.values(this.campos).map(c => ({
                nombre: c.nombre,
                valor: c.valor,
                unidad: c.unidad
            }));
            const data = {
                titulo: this.tituloSeleccionado,
                campos: camposData
            };
            window.__labvetData = window.__labvetData || {};
            window.__labvetData['{{ $index }}'] = data;
            $wire.set('componentesData.{{ $index }}.data', data);
        }
    }"
    class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    
    {{-- Selector de título --}}
    <div class="mb-4">
        @if(count($titulosDisponibles) > 1)
            <select
                x-model="tituloSeleccionado"
                @change="sincronizarConLivewire()"
                class="w-full px-3 py-2 text-lg font-bold text-center border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                @foreach($titulosDisponibles as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                @endforeach
            </select>
        @else
            <input 
                type="text"
                x-model="tituloSeleccionado"
                @change="sincronizarConLivewire()"
                @blur="sincronizarConLivewire()"
                placeholder="Título del componente"
                class="w-full px-3 py-2 text-lg font-bold text-center border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500"
            />
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-300 dark:border-zinc-700 text-sm">
            @if(!empty($componente['propiedades']['columnas']))
            <thead>
                <tr class="bg-gray-100 dark:bg-zinc-900">
                    @foreach($componente['propiedades']['columnas'] as $columna)
                    <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold text-gray-900 dark:text-zinc-100">
                        {{ $columna['nombre'] ?? '' }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            @endif
            <tbody>
            @foreach($camposNormalizados as $i => $campo)
                @if($campo['nombre'] ?? '')
                <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800">
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold bg-gray-50 dark:bg-zinc-900 text-gray-900 dark:text-zinc-100">
                        {{ $campo['nombre'] }}
                    </td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-2">
                        <div class="flex items-center gap-2">
                            @if(($campo['tipo_input'] ?? 'texto') === 'select')
                                {{-- Select con opciones predefinidas --}}
                                <select
                                    x-model="campos[{{ $i }}].valor"
                                    @change="sincronizarConLivewire()"
                                    class="flex-1 px-3 py-1 border-0 focus:ring-2 focus:ring-blue-500 rounded bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                                    <option value="">Seleccionar...</option>
                                    @foreach(explode(',', $campo['opciones'] ?? '') as $opcion)
                                        @if(trim($opcion))
                                        <option value="{{ trim($opcion) }}">{{ trim($opcion) }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            @else
                                {{-- Input de texto libre --}}
                                <input 
                                    type="text"
                                    x-model="campos[{{ $i }}].valor"
                                    @change="sincronizarConLivewire()"
                                    @blur="sincronizarConLivewire()"
                                    placeholder="Completar..."
                                    class="flex-1 px-3 py-1 border-0 focus:ring-2 focus:ring-blue-500 rounded bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500"
                                />
                            @endif
                            @if(!empty($campo['unidad']))
                                <span class="text-sm text-gray-500 dark:text-zinc-400 whitespace-nowrap font-medium">{{ $campo['unidad'] }}</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endif
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- Repeticiones por campo (solo si hay reactivos asignados) --}}
    @include('livewire.resultados.componentes-edicion._repeticiones-reactivos', [
        'componente' => $componente,
        'index' => $index,
    ])
</div>
