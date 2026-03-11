{{-- Componente de edición: Coproparasitología Seriado --}}
@php
    $numMuestras = (int) ($componente['propiedades']['num_muestras'] ?? 3);
    $mostrarFecha = $componente['propiedades']['mostrar_fecha'] ?? true;
    $ordinalLabels = ['1ra', '2da', '3ra', '4ta', '5ta', '6ta'];

    // Pre-calcular todos los campos con una entrada por muestra
    $todosCampos = [];
    foreach(($componente['propiedades']['secciones'] ?? []) as $seccionIndex => $seccion) {
        foreach(($seccion['campos'] ?? []) as $campoIndex => $campo) {
            $nombreCampo = $campo['nombre'] ?? '';
            if ($nombreCampo) {
                $key = $seccionIndex . '_' . $campoIndex;
                $valores = [];
                for ($m = 0; $m < $numMuestras; $m++) {
                    $valores[$m] = '';
                }
                $todosCampos[$key] = [
                    'seccion' => $seccion['subtitulo'] ?? '',
                    'campo' => $nombreCampo,
                    'valores' => $valores
                ];
            }
        }
    }

    // Inicializar fechas vacías
    $fechasIniciales = [];
    for ($m = 0; $m < $numMuestras; $m++) {
        $fechasIniciales[$m] = '';
    }
@endphp

<div 
    wire:ignore
    x-data="{
    datosExistentes: @js($componentesData[$index]['data'] ?? []),
    datos: @js($todosCampos),
    fechas: @js($fechasIniciales),
    init() {
        let existentes = this.datosExistentes;

        // Formato guardado: { campos: [...], fechas: [...] }
        if (existentes && typeof existentes === 'object' && !Array.isArray(existentes)) {
            // Cargar fechas
            if (Array.isArray(existentes.fechas)) {
                existentes.fechas.forEach((f, i) => {
                    if (i < {{ $numMuestras }}) {
                        this.fechas[i] = f || '';
                    }
                });
            }

            // Cargar campos
            let campos = existentes.campos;
            if (campos && !Array.isArray(campos)) {
                campos = Object.values(campos);
            }
            if (Array.isArray(campos) && campos.length > 0) {
                Object.keys(this.datos).forEach(key => {
                    const campoName = this.datos[key].campo;
                    const match = campos.find(item => item && item.campo === campoName);
                    if (match && match.valores) {
                        let vals = Array.isArray(match.valores) ? match.valores : Object.values(match.valores);
                        vals.forEach((v, i) => {
                            if (i < {{ $numMuestras }}) {
                                this.datos[key].valores[i] = v || '';
                            }
                        });
                    }
                });
            }
        }
        
        // Escuchar evento de guardado
        window.addEventListener('antes-de-guardar', () => {
            this.sincronizarConLivewire();
        });
        
        // Sincronizar antes de cualquier acción de Livewire
        window.addEventListener('livewire:initialized', () => {
            Livewire.hook('morph.updating', () => {
                this.sincronizarConLivewire();
            });
        });
    },
    sincronizarConLivewire() {
        const data = {
            campos: Object.values(this.datos),
            fechas: [...this.fechas]
        };
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
            {{-- Header: columna campo + columnas por muestra --}}
            <thead>
                <tr>
                    <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 text-left"></th>
                    @for($m = 0; $m < $numMuestras; $m++)
                    <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 text-center">
                        <div class="font-bold">{{ $ordinalLabels[$m] ?? ($m + 1) . 'ta' }} MUESTRA</div>
                        @if($mostrarFecha)
                        <div class="mt-1">
                            <input 
                                type="date"
                                x-model="fechas[{{ $m }}]"
                                @change="sincronizarConLivewire()"
                                class="w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 text-center"
                            />
                        </div>
                        @endif
                    </th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($componente['propiedades']['secciones'] ?? [] as $seccion)
                    {{-- Subtítulo de sección si existe --}}
                    @if($seccion['subtitulo'] ?? null)
                    <tr>
                        <td colspan="{{ $numMuestras + 1 }}" class="bg-gray-100 dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 px-3 py-2 font-bold text-center text-gray-900 dark:text-zinc-100">
                            {{ $seccion['subtitulo'] }}
                        </td>
                    </tr>
                    @endif

                    {{-- Campos de la sección --}}
                    @foreach($seccion['campos'] ?? [] as $campoIndex => $campo)
                        @php $nombreCampo = $campo['nombre'] ?? ''; @endphp
                        @if($nombreCampo)
                        <tr>
                            {{-- Label fijo --}}
                            <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold bg-gray-50 dark:bg-zinc-900 text-gray-900 dark:text-zinc-100 whitespace-nowrap">
                                {{ $nombreCampo }}
                            </td>
                            
                            {{-- Una celda por muestra --}}
                            @for($m = 0; $m < $numMuestras; $m++)
                            <td class="border border-gray-300 dark:border-zinc-700 px-2 py-2">
                                @if(($campo['tipo_input'] ?? 'input') === 'select')
                                <select
                                    x-model="datos['{{ $loop->parent->index }}_{{ $campoIndex }}'].valores[{{ $m }}]"
                                    @change="sincronizarConLivewire()"
                                    class="w-full px-2 py-1 border-0 focus:ring-2 focus:ring-blue-500 rounded bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 text-sm">
                                    <option value="" class="bg-white dark:bg-zinc-800">--</option>
                                    @foreach(explode(',', $campo['opciones'] ?? '') as $opcion)
                                        @if(trim($opcion) !== '')
                                        <option value="{{ trim($opcion) }}" class="bg-white dark:bg-zinc-800">{{ trim($opcion) }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @else
                                <input 
                                    type="text"
                                    x-model="datos['{{ $loop->parent->index }}_{{ $campoIndex }}'].valores[{{ $m }}]"
                                    @change="sincronizarConLivewire()"
                                    @blur="sincronizarConLivewire()"
                                    placeholder="..."
                                    class="w-full px-2 py-1 border-0 focus:ring-2 focus:ring-blue-500 rounded bg-transparent text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 text-sm"
                                />
                                @endif
                            </td>
                            @endfor
                        </tr>
                        @endif
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
