{{-- Partial reutilizable: Sección de Repeticiones para consumo de reactivos --}}
{{-- 
    Variables requeridas:
    - $componente: array con tipo y propiedades del componente
    - $index: índice del componente en la plantilla
    
    Soporta 4 granularidades según tipo de componente:
    - Por fila: tabla-resultados, tabla-temporal
    - Por campo: campos-etiquetados, serologia
    - Por campo anidado: tabla-dos-columnas
    - Por bloque: antibiograma, examen-diferencial, examen-microscopico, 
                  coproparasitologia-seriado, carga-viral, tabla-hematologica
--}}

@php
    $tipo = $componente['tipo'];
    $props = $componente['propiedades'] ?? [];
    
    // Tipos que usan reactivos a nivel de bloque completo
    $tiposBloque = ['antibiograma', 'examen-diferencial', 'examen-microscopico',
                    'coproparasitologia-seriado', 'carga-viral', 'tabla-hematologica'];
    
    // Determinar si hay reactivos según el tipo
    $tieneReactivos = false;
    $itemsConReactivo = [];
    
    if (in_array($tipo, ['tabla-resultados', 'tabla-temporal']) && !empty($props['filas'])) {
        // Por fila
        foreach ($props['filas'] as $filaIdx => $fila) {
            if (!empty($fila['reactivos'])) {
                $nombre = $fila['nombre'] ?? $fila['analisis'] ?? "Fila " . ($filaIdx + 1);
                $itemsConReactivo[] = [
                    'key' => "{$index}.{$filaIdx}",
                    'nombre' => $nombre,
                ];
                $tieneReactivos = true;
            }
        }
    } elseif (in_array($tipo, ['campos-etiquetados', 'serologia']) && !empty($props['campos'])) {
        // Por campo
        foreach ($props['campos'] as $campoIdx => $campo) {
            if (is_array($campo) && !empty($campo['reactivos'])) {
                $itemsConReactivo[] = [
                    'key' => "{$index}.c{$campoIdx}",
                    'nombre' => $campo['nombre'] ?? "Campo " . ($campoIdx + 1),
                ];
                $tieneReactivos = true;
            }
        }
    } elseif ($tipo === 'tabla-dos-columnas') {
        // Por campo anidado
        if (!empty($props['secciones'])) {
            foreach ($props['secciones'] as $secIdx => $seccion) {
                foreach ($seccion['campos'] ?? [] as $campoIdx => $campo) {
                    if (!empty($campo['reactivos'])) {
                        $itemsConReactivo[] = [
                            'key' => "{$index}.s{$secIdx}.c{$campoIdx}",
                            'nombre' => $campo['nombre'] ?? "Campo",
                        ];
                        $tieneReactivos = true;
                    }
                }
            }
        }
        // Nivel global
        if (!empty($props['reactivos'])) {
            $itemsConReactivo[] = [
                'key' => "{$index}.global",
                'nombre' => ($props['titulo'] ?? 'Tabla') . " (Global)",
            ];
            $tieneReactivos = true;
        }
    } elseif (in_array($tipo, $tiposBloque) && !empty($props['reactivos'])) {
        // Nivel bloque
        $itemsConReactivo[] = [
            'key' => "{$index}",
            'nombre' => $props['titulo'] ?? str_replace('-', ' ', ucfirst($tipo)),
        ];
        $tieneReactivos = true;
    }
@endphp

@if($tieneReactivos)
<div class="mt-3 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg border border-emerald-200 dark:border-emerald-800">
    <p class="text-xs font-semibold text-emerald-800 dark:text-emerald-300 mb-2 flex items-center gap-1">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
        </svg>
        Repeticiones (Consumo de Reactivos)
    </p>
    <div class="space-y-2">
        @foreach($itemsConReactivo as $item)
        <div class="flex items-center gap-3">
            <span class="text-xs text-emerald-700 dark:text-emerald-300 flex-1 truncate">
                {{ $item['nombre'] }}
            </span>
            <div class="flex items-center gap-1">
                <input
                    type="number"
                    wire:model.live="repeticionesData.{{ $item['key'] }}"
                    min="1"
                    step="1"
                    class="w-16 px-2 py-1 border border-emerald-300 dark:border-emerald-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 text-center"
                />
                <span class="text-xs text-emerald-600 dark:text-emerald-400">rep.</span>
            </div>
        </div>
        @endforeach
    </div>
    <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2">
        <i class="fas fa-info-circle mr-1"></i>
        Indica cuántas veces se procesó cada análisis.
    </p>
</div>
@endif
