{{-- Componente de edición: Tabla de Información --}}
<div class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
    @if(isset($componente['propiedades']['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-lg mb-4">
        {{ $componente['propiedades']['titulo'] }}
    </h4>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-300 dark:border-zinc-700 text-sm">
            @php
                $columnas = $componente['propiedades']['columnas'] ?? 3;
                $filas = $componente['propiedades']['filas'] ?? [];
                $chunks = array_chunk($filas, $columnas);
            @endphp
            
            @foreach($chunks as $grupo)
            <tr>
                @foreach($grupo as $fila)
                    {{-- Label --}}
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold bg-gray-100 dark:bg-zinc-900 text-gray-900 dark:text-zinc-100">
                        {{ $fila['label'] ?? 'CAMPO' }}
                    </td>
                    
                    {{-- Input --}}
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-2">
                        <input 
                            type="text"
                            placeholder="{{ $fila['placeholder'] ?? '...' }}"
                            class="w-full px-2 py-1 border-0 focus:ring-2 focus:ring-blue-500 rounded bg-transparent text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500"
                        />
                    </td>
                @endforeach
                
                {{-- Rellenar celdas vacías si no hay suficientes filas --}}
                @if(count($grupo) < $columnas)
                    @for($i = count($grupo); $i < $columnas; $i++)
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2" colspan="2"></td>
                    @endfor
                @endif
            </tr>
            @endforeach
        </table>
    </div>
</div>
