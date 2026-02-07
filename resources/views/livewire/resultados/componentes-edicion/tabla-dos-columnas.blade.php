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

<div x-data="{
    datosExistentes: @js($componentesData[$index]['data'] ?? []),
    datos: @js($todosCampos),
    init() {
        // Cargar datos existentes si existen
        if (Array.isArray(this.datosExistentes) && this.datosExistentes.length > 0) {
            let dataIndex = 0;
            @foreach($componente['propiedades']['secciones'] ?? [] as $seccionIndex => $seccion)
                @foreach($seccion['campos'] ?? [] as $campoIndex => $campo)
                    @if($campo)
                    if (this.datosExistentes[dataIndex] && this.datos['{{ $seccionIndex }}_{{ $campoIndex }}']) {
                        this.datos['{{ $seccionIndex }}_{{ $campoIndex }}'].valor = this.datosExistentes[dataIndex].valor || '';
                    }
                    dataIndex++;
                    @endif
                @endforeach
            @endforeach
        }
        
        // Escuchar evento de guardado para forzar sincronización
        window.addEventListener('antes-de-guardar', () => {
            this.enviarDatos();
        });
    },
    enviarDatos() {
        $wire.set('componentesData.{{ $index }}.data', Object.values(this.datos));
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
                                @blur="enviarDatos()"
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
