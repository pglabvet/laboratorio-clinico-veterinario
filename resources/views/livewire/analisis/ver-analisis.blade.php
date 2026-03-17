<div class="min-h-screen bg-gray-50 dark:bg-zinc-800">
    <div class="container mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="xl">Detalle del Análisis #{{ $analisis->id }}</flux:heading>
                <flux:subheading>{{ $analisis->tipoAnalisis->nombre }}</flux:subheading>
            </div>
            <div class="flex gap-2">
                <flux:button href="{{ route('analisis.revisar') }}" variant="outline" icon="arrow-left" class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950">
                    Volver
                </flux:button>
                
            
                @if($analisis->estado === 'Aprobado')
                    {{-- Botones PDF para análisis aprobados --}}
                    @can('descargar-pdf-analisis')
                    <flux:button href="{{ route('analisis.ver-pdf', $analisis->id) }}" target="_blank" variant="primary" icon="eye">
                        Ver PDF
                    </flux:button>
                    <flux:button href="{{ route('analisis.pdf', $analisis->id) }}" target="_blank" variant="outline" icon="arrow-down-tray" class="border-blue-500 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950">
                        Descargar PDF
                    </flux:button>
                    @endcan
                @elseif($analisis->estado === 'En revision')
                    <flux:button href="{{ route('resultados.editar', $analisis->id) }}" variant="outline" icon="pencil">
                        Editar
                    </flux:button>
                    @can('rechazar-analisis')
                    <flux:button wire:click="rechazar" wire:confirm="¿Rechazar este análisis?" variant="danger" icon="x-mark">
                        Rechazar
                    </flux:button>
                    @endcan
                    @can('aprobar-analisis')
                    <flux:button wire:click="aprobar" wire:confirm="¿Aprobar este análisis?" variant="primary" icon="check">
                        Aprobar
                    </flux:button>
                    @endcan
                @endif
            </div>
        </div>

        {{-- Estado --}}
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-zinc-400 mb-1">Estado actual</p>
                    @php
                        $estadoConfig = [
                            'pendiente' => ['color' => 'zinc', 'texto' => 'Pendiente'],
                            'en_proceso' => ['color' => 'blue', 'texto' => 'En Proceso'],
                            'finalizado' => ['color' => 'yellow', 'texto' => 'Finalizado - Esperando Aprobación'],
                            'aprobado' => ['color' => 'green', 'texto' => 'Aprobado'],
                            'rechazado' => ['color' => 'red', 'texto' => 'Rechazado - Requiere Correcciones'],
                        ];
                        $config = $estadoConfig[$analisis->estado] ?? ['color' => 'zinc', 'texto' => $analisis->estado];
                    @endphp
                    <flux:badge :color="$config['color']" size="lg">
                        {{ $config['texto'] }}
                    </flux:badge>
                </div>

                @if($analisis->aprobador)
                <div class="text-right">
                    <p class="text-sm text-gray-500 dark:text-zinc-400 mb-1">
                        {{ $analisis->estado === 'aprobado' ? 'Aprobado por' : 'Revisado por' }}
                    </p>
                    <p class="font-semibold text-gray-900 dark:text-zinc-100">
                        {{ $analisis->aprobador->name }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-zinc-400">
                        {{ $analisis->fecha_aprobacion?->format('d/m/Y H:i') }}
                    </p>
                </div>
                @endif
            </div>

            @if($analisis->observaciones_aprobador)
            <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded">
                <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-300 mb-1">Observaciones del Aprobador</p>
                <p class="text-sm text-yellow-700 dark:text-yellow-400">{{ $analisis->observaciones_aprobador }}</p>
            </div>
            @endif
        </div>

        {{-- Info del Paciente y Muestra --}}
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-md p-6 mb-6">
            <h3 class="font-bold text-lg text-gray-800 dark:text-zinc-100 mb-4">Información del Paciente</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Paciente</p>
                    <p class="font-semibold text-gray-900 dark:text-zinc-100">{{ $analisis->muestra->paciente_nombre }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Propietario</p>
                    <p class="text-sm text-gray-900 dark:text-zinc-100">{{ $analisis->muestra->propietario_nombre }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Especie</p>
                    <p class="text-sm text-gray-900 dark:text-zinc-100">{{ $analisis->muestra->especie->nombre ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Código de Muestra</p>
                    <p class="font-semibold text-blue-600 dark:text-blue-400">{{ $analisis->muestra->codigo_muestra }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Bioquímico</p>
                    <p class="text-sm text-gray-900 dark:text-zinc-100">{{ $analisis->bioquimico->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Fecha Inicio</p>
                    <p class="text-sm text-gray-900 dark:text-zinc-100">{{ $analisis->fecha_inicio?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Fecha Finalización</p>
                    <p class="text-sm text-gray-900 dark:text-zinc-100">{{ $analisis->fecha_finalizacion?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase mb-1">Solicitado por</p>
                    <p class="text-sm text-gray-900 dark:text-zinc-100">{{ $analisis->muestra->veterinaria->nombre ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        {{-- Resultados Capturados --}}
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-md p-6">
            <h3 class="font-bold text-lg text-gray-800 dark:text-zinc-100 mb-4">Resultados Capturados</h3>

            @php
                $plantilla = null;
                if ($analisis->plantilla_formulario_id) {
                    $plantilla = \App\Models\PlantillaFormulario::find($analisis->plantilla_formulario_id);
                }
                if (!$plantilla) {
                    $plantilla = $analisis->tipoAnalisis->plantillas->firstWhere('activo', true)
                        ?? $analisis->tipoAnalisis->plantillas->first();
                }
                $componentesPlantilla = $plantilla?->componentes ?? [];

                $clasificarResultadoVer = function ($resultadoStr, $template, $infijo = '') {
                    if ($resultadoStr === '' || $resultadoStr === null || !$template || !is_numeric($resultadoStr)) {
                        return 'normal';
                    }
                    $res = floatval(str_replace(',', '', $resultadoStr));
                    $tipo = $template['rango_' . $infijo . 'tipo'] ?? 'min-max';
                    $min = $template['rango_' . $infijo . 'min'] ?? null;
                    $max = $template['rango_' . $infijo . 'max'] ?? null;
                    $valor = $template['rango_' . $infijo . 'valor'] ?? null;

                    if ($tipo === 'min-max') {
                        $minF = ($min !== null && $min !== '' && is_numeric($min)) ? floatval($min) : null;
                        $maxF = ($max !== null && $max !== '' && is_numeric($max)) ? floatval($max) : null;
                        if ($minF === null && $maxF === null) return 'normal';
                        $amplitud = ($minF !== null && $maxF !== null) ? $maxF - $minF : 0;
                        $umbral = $amplitud * config('labvet.umbral_resultado');
                        if ($minF !== null && $res < $minF) return ($amplitud > 0 && $res >= $minF - $umbral) ? 'alerta' : 'critico';
                        if ($maxF !== null && $res > $maxF) return ($amplitud > 0 && $res <= $maxF + $umbral) ? 'alerta' : 'critico';
                        return 'normal';
                    }

                    if ($valor === null || $valor === '' || !is_numeric($valor)) return 'normal';
                    $valorF = floatval($valor);
                    $umbral = abs($valorF) * config('labvet.umbral_resultado');
                    $fuera = match($tipo) {
                        'menor' => $res >= $valorF, 'menor-igual' => $res > $valorF,
                        'mayor' => $res <= $valorF, 'mayor-igual' => $res < $valorF,
                        default => false,
                    };
                    if (!$fuera) return 'normal';
                    $dist = match($tipo) {
                        'menor', 'menor-igual' => $res - $valorF,
                        'mayor', 'mayor-igual' => $valorF - $res,
                        default => 0,
                    };
                    return $dist <= $umbral ? 'alerta' : 'critico';
                };

                $claseClasificacion = function ($clasificacion) {
                    return match($clasificacion) {
                        'alerta' => 'text-blue-600 dark:text-blue-400 font-bold',
                        'critico' => 'text-red-600 dark:text-red-400 font-bold',
                        default => '',
                    };
                };
            @endphp

            @forelse($resultadosAgrupados as $indice => $resultado)
                @php
                    $tipo = $resultado->tipo;
                    $propiedadesComponente = $componentesPlantilla[$indice]['propiedades'] ?? [];
                @endphp
                <div class="mb-6 last:mb-0">
                    <h4 class="font-semibold text-gray-700 dark:text-zinc-300 mb-3 text-uppercase">
                        {{ $propiedadesComponente['titulo'] ?? str_replace('-', ' ', str_replace('_', ' ', $tipo)) }}
                    </h4>

                        <div class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 mb-3">
                            @if($tipo === 'antibiograma')
                                {{-- Antibiograma --}}
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-zinc-800">
                                            <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-left">SENSIBLE</th>
                                            <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-left">INTERMEDIO</th>
                                            <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-left">RESISTENTE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($resultado->valor as $fila)
                                        <tr>
                                            <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2">{{ $fila['sensible'] ?? '' }}</td>
                                            <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2">{{ $fila['intermedio'] ?? '' }}</td>
                                            <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2">{{ $fila['resistente'] ?? '' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            @elseif($tipo === 'lista_items' || $tipo === 'lista-items')
                                {{-- Lista de Items --}}
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($resultado->valor as $item)
                                        <li class="text-gray-900 dark:text-zinc-100">{{ $item }}</li>
                                    @endforeach
                                </ul>

                            @elseif($tipo === 'campo-imagenes')
                                {{-- Imágenes --}}
                                <div class="grid grid-cols-2 gap-4">
                                    @if(isset($resultado->valor['imagen1']))
                                        <div>
                                            <p class="text-sm font-semibold mb-2">Imagen 1</p>
                                            <img src="{{ asset('storage/' . $resultado->valor['imagen1']) }}" alt="Imagen 1" class="rounded border border-gray-300 dark:border-zinc-700 w-full">
                                        </div>
                                    @endif
                                    @if(isset($resultado->valor['imagen2']))
                                        <div>
                                            <p class="text-sm font-semibold mb-2">Imagen 2</p>
                                            <img src="{{ asset('storage/' . $resultado->valor['imagen2']) }}" alt="Imagen 2" class="rounded border border-gray-300 dark:border-zinc-700 w-full">
                                        </div>
                                    @endif
                                </div>

                            @elseif($tipo === 'campos-etiquetados')
                                {{-- Campos Etiquetados --}}
                                @php
                                    $itemsCE = $resultado->valor['campos'] ?? [];
                                @endphp
                                <table class="w-full text-sm">
                                    @if(!empty($propiedadesComponente['columnas']))
                                    <thead>
                                        <tr class="bg-gray-100 dark:bg-zinc-900">
                                            @foreach($propiedadesComponente['columnas'] as $columna)
                                            <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold text-gray-900 dark:text-zinc-100">
                                                {{ $columna['nombre'] ?? '' }}
                                            </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    @endif
                                    <tbody>
                                        @foreach($itemsCE as $item)
                                        <tr class="border-b border-gray-200 dark:border-zinc-700 last:border-0">
                                            <td class="py-2 px-3 font-semibold bg-gray-50 dark:bg-zinc-800 w-1/3">
                                                {{ $item['nombre'] ?? 'Campo' }}
                                            </td>
                                            <td class="py-2 px-3">
                                                {{ $item['valor'] ?? '' }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            @elseif($tipo === 'tabla-resultados' || $tipo === 'tabla-dos-columnas' || $tipo === 'serologia')
                                {{-- Tablas --}}
                                @php
                                    $filasTemplateVer = $propiedadesComponente['filas'] ?? [];
                                    $filasTemplateByNameVer = collect($filasTemplateVer)->filter(fn($f) => is_array($f))->keyBy('nombre');
                                @endphp
                                <table class="w-full text-sm">
                                    <tbody>
                                        @foreach($resultado->valor as $item)
                                        @php
                                            $nombreItem = $item['nombre'] ?? $item['campo'] ?? 'Campo';
                                            $valorResultado = $item['col_0'] ?? '';
                                            $ftVer = $filasTemplateByNameVer->get($nombreItem);
                                            $clasifVer = 'normal';
                                            if ($ftVer && $valorResultado !== '' && is_numeric($valorResultado)) {
                                                $tipoRango = $ftVer['rango_tipo'] ?? '';
                                                if ($tipoRango === 'multiple') {
                                                    $rangosVer = $ftVer['rangos'] ?? [];
                                                    $resNum = floatval($valorResultado);
                                                    $encontrado = null;
                                                    foreach ($rangosVer as $rv) {
                                                        $tR = $rv['tipo'] ?? 'min-max';
                                                        $coincide = match($tR) {
                                                            'min-max' => $resNum >= floatval($rv['min'] ?? 0) && $resNum <= floatval($rv['max'] ?? 0),
                                                            'menor' => $resNum < floatval($rv['valor'] ?? 0),
                                                            'menor-igual' => $resNum <= floatval($rv['valor'] ?? 0),
                                                            'mayor' => $resNum > floatval($rv['valor'] ?? 0),
                                                            'mayor-igual' => $resNum >= floatval($rv['valor'] ?? 0),
                                                            default => false,
                                                        };
                                                        if ($coincide) { $encontrado = $rv; break; }
                                                    }
                                                    if ($encontrado) {
                                                        $clasifVer = ($encontrado['es_normal'] ?? false) ? 'normal' : 'alerta';
                                                    } elseif (!empty($rangosVer)) {
                                                        $clasifVer = 'critico';
                                                    }
                                                } else {
                                                    $clasifVer = $clasificarResultadoVer($valorResultado, $ftVer);
                                                }
                                            }
                                        @endphp
                                        <tr class="border-b border-gray-200 dark:border-zinc-700 last:border-0">
                                            <td class="py-2 px-3 font-semibold bg-gray-50 dark:bg-zinc-800 w-1/3">
                                                {{ $nombreItem }}
                                            </td>
                                            <td class="py-2 px-3 {{ $claseClasificacion($clasifVer) }}">
                                                @foreach($item as $key => $value)
                                                    @if($key !== 'nombre' && $key !== 'campo' && !empty($value))
                                                        {{ $value }}{{ !$loop->last ? ' | ' : '' }}
                                                    @endif
                                                @endforeach
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            @elseif($tipo === 'examen-microscopico')
                                {{-- Examen Microscópico --}}
                                @php
                                    $filasPlantillaEM = $propiedadesComponente['filas'] ?? [];
                                    $filasPlantillaByNameEM = collect($filasPlantillaEM)->keyBy('parametro');

                                    $generarTextoRangoEM = function ($fila) {
                                        $tipo = $fila['rango_tipo'] ?? 'min-max';
                                        $unidad = $fila['unidad'] ?? '';
                                        $sufijo = $unidad ? ' ' . $unidad : '';
                                        return match($tipo) {
                                            'min-max' => (!empty($fila['rango_min']) || !empty($fila['rango_max']))
                                                ? ($fila['rango_min'] ?? '') . ' - ' . ($fila['rango_max'] ?? '') . $sufijo
                                                : '',
                                            'menor' => !empty($fila['rango_valor']) ? '< ' . $fila['rango_valor'] . $sufijo : '',
                                            'menor-igual' => !empty($fila['rango_valor']) ? '<= ' . $fila['rango_valor'] . $sufijo : '',
                                            'mayor' => !empty($fila['rango_valor']) ? '> ' . $fila['rango_valor'] . $sufijo : '',
                                            'mayor-igual' => !empty($fila['rango_valor']) ? '>= ' . $fila['rango_valor'] . $sufijo : '',
                                            default => '',
                                        };
                                    };

                                    $tieneRangosEM = collect($filasPlantillaEM)->contains(fn($f) => $generarTextoRangoEM($f) !== '');
                                @endphp
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-zinc-800">
                                            <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-left">{{ $propiedadesComponente['columna_parametro'] ?? 'PARÁMETRO' }}</th>
                                            <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center">{{ $propiedadesComponente['columna_resultado'] ?? 'RESULTADO' }}</th>
                                            @if($tieneRangosEM)
                                            <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center">{{ $propiedadesComponente['columna_rango'] ?? 'RANGO REF.' }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($resultado->valor as $fila)
                                        @php
                                            $filaTemplateEM = $filasPlantillaByNameEM->get($fila['parametro'] ?? '');
                                            $rangoTextoEM = $filaTemplateEM ? $generarTextoRangoEM($filaTemplateEM) : '';
                                            $clasifEM = $filaTemplateEM ? $clasificarResultadoVer($fila['resultado'] ?? '', $filaTemplateEM) : 'normal';
                                        @endphp
                                        <tr>
                                            <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold">{{ $fila['parametro'] ?? '' }}</td>
                                            <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center {{ $claseClasificacion($clasifEM) }}">{{ $fila['resultado'] ?? '' }}</td>
                                            @if($tieneRangosEM)
                                            <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center text-xs text-gray-500 dark:text-zinc-400">{{ $rangoTextoEM }}</td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            @elseif($tipo === 'tabla-hematologica')
                                {{-- Tabla Hematológica --}}
                                @if(isset($resultado->valor['parametros']))
                                    @php
                                        $paramTemplatesVer = collect($propiedadesComponente['parametros_principales'] ?? [])->keyBy('nombre');
                                        $difTemplatesVer = collect($propiedadesComponente['diferenciales'] ?? [])->keyBy('nombre');
                                        $indTemplatesVer = collect($propiedadesComponente['indices'] ?? [])->keyBy('nombre');
                                    @endphp
                                    <div class="mb-4">
                                        <p class="font-semibold mb-2">Parámetros Principales</p>
                                        <table class="w-full text-sm">
                                            <tbody>
                                                @foreach($resultado->valor['parametros'] as $param)
                                                @php
                                                    $ptVer = $paramTemplatesVer->get($param['nombre'] ?? '');
                                                    $clasifParam = $ptVer ? $clasificarResultadoVer($param['resultado'] ?? '', $ptVer) : 'normal';
                                                @endphp
                                                <tr class="border-b border-gray-200 dark:border-zinc-700">
                                                    <td class="py-2 px-3 font-semibold bg-gray-50 dark:bg-zinc-800">{{ $param['nombre'] }}</td>
                                                    <td class="py-2 px-3 {{ $claseClasificacion($clasifParam) }}">{{ $param['resultado'] }} {{ $param['unidad'] }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if(!empty($resultado->valor['diferenciales']))
                                    <div class="mb-4">
                                        <p class="font-semibold mb-2">Diferencial Leucocitario</p>
                                        <table class="w-full text-sm">
                                            <tbody>
                                                @foreach($resultado->valor['diferenciales'] as $dif)
                                                @php
                                                    $dtVer = $difTemplatesVer->get($dif['nombre'] ?? '');
                                                    $clasifRel = $dtVer ? $clasificarResultadoVer($dif['valor_rel'] ?? '', $dtVer, 'rel_') : 'normal';
                                                    $clasifAbs = $dtVer ? $clasificarResultadoVer($dif['valor_abs'] ?? '', $dtVer, 'abs_') : 'normal';
                                                @endphp
                                                <tr class="border-b border-gray-200 dark:border-zinc-700">
                                                    <td class="py-2 px-3 font-semibold bg-gray-50 dark:bg-zinc-800">{{ $dif['nombre'] }}</td>
                                                    <td class="py-2 px-3 {{ $claseClasificacion($clasifRel) }}">{{ ($dif['valor_rel'] ?? '') !== '' ? $dif['valor_rel'] . ' %' : '' }}</td>
                                                    <td class="py-2 px-3 {{ $claseClasificacion($clasifAbs) }}">{{ ($dif['valor_abs'] ?? '') !== '' ? $dif['valor_abs'] . ' mm³' : '' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                    @if(!empty($resultado->valor['indices']))
                                    <div class="mb-4">
                                        <p class="font-semibold mb-2">Índices Eritrocitarios</p>
                                        <table class="w-full text-sm">
                                            <tbody>
                                                @foreach($resultado->valor['indices'] as $ind)
                                                @php
                                                    $itVer = $indTemplatesVer->get($ind['nombre'] ?? '');
                                                    $clasifInd = $itVer ? $clasificarResultadoVer($ind['resultado'] ?? '', $itVer) : 'normal';
                                                @endphp
                                                <tr class="border-b border-gray-200 dark:border-zinc-700">
                                                    <td class="py-2 px-3 font-semibold bg-gray-50 dark:bg-zinc-800">{{ $ind['nombre'] }}</td>
                                                    <td class="py-2 px-3 {{ $claseClasificacion($clasifInd) }}">{{ $ind['resultado'] ?? '' }} {{ $ind['unidad'] ?? '' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                @endif

                            @elseif($tipo === 'examen-diferencial')
                                {{-- Examen Diferencial --}}
                                @php
                                    $filasValor = is_array($resultado->valor) ? $resultado->valor : [];
                                    $filasPlantillaED = $propiedadesComponente['filas'] ?? [];
                                    $filasPlantillaByNameED = collect($filasPlantillaED)->keyBy('nombre');

                                    $generarTextoRangoED = function ($fila) {
                                        $tipo = $fila['rango_tipo'] ?? 'min-max';
                                        $unidad = $fila['unidad'] ?? '';
                                        $sufijo = $unidad ? ' ' . $unidad : '';
                                        return match($tipo) {
                                            'min-max' => (!empty($fila['rango_min']) || !empty($fila['rango_max']))
                                                ? ($fila['rango_min'] ?? '') . ' - ' . ($fila['rango_max'] ?? '') . $sufijo
                                                : '',
                                            'menor' => !empty($fila['rango_valor']) ? '< ' . $fila['rango_valor'] . $sufijo : '',
                                            'menor-igual' => !empty($fila['rango_valor']) ? '<= ' . $fila['rango_valor'] . $sufijo : '',
                                            'mayor' => !empty($fila['rango_valor']) ? '> ' . $fila['rango_valor'] . $sufijo : '',
                                            'mayor-igual' => !empty($fila['rango_valor']) ? '>= ' . $fila['rango_valor'] . $sufijo : '',
                                            default => '',
                                        };
                                    };

                                    $tieneRangosED = collect($filasPlantillaED)->contains(function ($f) use ($generarTextoRangoED) {
                                        return ($f['tipo_fila'] ?? '3col') === '3col' && $generarTextoRangoED($f) !== '';
                                    });
                                @endphp
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-zinc-800">
                                            <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-left">{{ $propiedadesComponente['columna_analisis'] ?? 'ANÁLISIS' }}</th>
                                            <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center">{{ $propiedadesComponente['columna_resultado'] ?? 'RESULTADO' }}</th>
                                            @if($tieneRangosED)
                                            <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center">{{ $propiedadesComponente['columna_rango'] ?? 'RANGO REF.' }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($filasValor as $fila)
                                        @php
                                            $tipoFilaED = $fila['tipo_fila'] ?? '3col';
                                            $filaTemplateED = $filasPlantillaByNameED->get($fila['nombre'] ?? '');
                                            $rangoTextoED = $filaTemplateED ? $generarTextoRangoED($filaTemplateED) : '';
                                            $clasifED = ($tipoFilaED === '3col' && $filaTemplateED) ? $clasificarResultadoVer($fila['resultado'] ?? '', $filaTemplateED) : 'normal';
                                        @endphp
                                        <tr>
                                            <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold">{{ $fila['nombre'] ?? '' }}</td>
                                            @if($tipoFilaED === '2col')
                                                <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center" {{ $tieneRangosED ? 'colspan=2' : '' }}>{{ $fila['resultado'] ?? '' }}</td>
                                            @else
                                                <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center {{ $claseClasificacion($clasifED) }}">{{ $fila['resultado'] ?? '' }}</td>
                                                @if($tieneRangosED)
                                                <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center text-xs text-gray-500 dark:text-zinc-400">{{ $rangoTextoED }}</td>
                                                @endif
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            @elseif($tipo === 'coproparasitologia-seriado')
                                {{-- Coproparasitología Seriado --}}
                                @php
                                    $numMuestrasVer = (int) ($propiedadesComponente['num_muestras'] ?? 3);
                                    $mostrarFechaVer = $propiedadesComponente['mostrar_fecha'] ?? true;
                                    $ordinalLabelsVer = ['1ra', '2da', '3ra', '4ta', '5ta', '6ta'];
                                    $camposVer = $resultado->valor['campos'] ?? [];
                                    $fechasVer = $resultado->valor['fechas'] ?? [];
                                    if (!is_array($camposVer) || (count($camposVer) > 0 && !isset($camposVer[0]))) {
                                        $camposVer = array_values($camposVer);
                                    }
                                    $camposVerByName = collect($camposVer)->keyBy('campo');
                                @endphp
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-zinc-800">
                                            <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-left"></th>
                                            @for($mv = 0; $mv < $numMuestrasVer; $mv++)
                                            <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center">
                                                {{ $ordinalLabelsVer[$mv] ?? ($mv + 1) . 'ta' }} MUESTRA
                                                @if($mostrarFechaVer && !empty($fechasVer[$mv]))
                                                <br><span class="text-xs font-normal text-gray-500 dark:text-zinc-400">{{ \Carbon\Carbon::parse($fechasVer[$mv])->format('d/m/Y') }}</span>
                                                @endif
                                            </th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($propiedadesComponente['secciones'] ?? [] as $seccionVer)
                                            @if($seccionVer['subtitulo'] ?? null)
                                            <tr>
                                                <td colspan="{{ $numMuestrasVer + 1 }}" class="bg-gray-100 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 px-3 py-2 font-bold text-center">
                                                    {{ $seccionVer['subtitulo'] }}
                                                </td>
                                            </tr>
                                            @endif
                                            @foreach($seccionVer['campos'] ?? [] as $campoVer)
                                                @php
                                                    $nombreVer = $campoVer['nombre'] ?? '';
                                                    $datosVer = $camposVerByName->get($nombreVer);
                                                    $valoresVer = $datosVer['valores'] ?? [];
                                                    if (!is_array($valoresVer)) { $valoresVer = array_values((array) $valoresVer); }
                                                @endphp
                                                @if($nombreVer && collect($valoresVer)->contains(fn($v) => !empty($v)))
                                                <tr>
                                                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold bg-gray-50 dark:bg-zinc-800">{{ $nombreVer }}</td>
                                                    @for($mv = 0; $mv < $numMuestrasVer; $mv++)
                                                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-center">{{ $valoresVer[$mv] ?? '' }}</td>
                                                    @endfor
                                                </tr>
                                                @endif
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>

                            @elseif($tipo === 'campo-texto')
                                {{-- Campo de Texto / Nota Fija --}}
                                @php
                                    $tipoUsoCT = $propiedadesComponente['tipo_uso'] ?? 'editable';
                                    $valorCT = '';
                                    if ($tipoUsoCT === 'nota') {
                                        $valorCT = $propiedadesComponente['contenido'] ?? '';
                                    } else {
                                        $val = $resultado->valor;
                                        $valorCT = is_array($val) ? ($val['valor'] ?? '') : $val;
                                    }
                                @endphp
                                @if(!empty($valorCT))
                                    @if($tipoUsoCT === 'nota')
                                        <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded border border-amber-200 dark:border-amber-800">
                                            <p class="text-sm text-amber-900 dark:text-amber-200 whitespace-pre-line">{{ $valorCT }}</p>
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-900 dark:text-zinc-100 whitespace-pre-line">{{ $valorCT }}</p>
                                    @endif
                                @else
                                    <p class="text-sm text-gray-400 dark:text-zinc-500 italic">Sin valor</p>
                                @endif

                            @else
                                {{-- Otros tipos --}}
                                <pre class="text-sm text-gray-900 dark:text-zinc-100 whitespace-pre-wrap">{{ json_encode($resultado->valor, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @endif
                        </div>
                </div>
            @empty
                <p class="text-gray-500 dark:text-zinc-400 text-center py-8">
                    <i class="fas fa-inbox text-4xl mb-3"></i><br>
                    No se han capturado resultados para este análisis
                </p>
            @endforelse
        </div>
    </div>
</div>
