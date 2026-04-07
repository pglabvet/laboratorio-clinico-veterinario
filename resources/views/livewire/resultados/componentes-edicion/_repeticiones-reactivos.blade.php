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
    
    UX: Los inputs de repeticiones se muestran deshabilitados (con badge "sin resultado")
    hasta que el usuario haya ingresado un resultado para ese análisis/campo.
    Cuando el resultado se llena, se habilita automáticamente con valor 1.
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
        foreach ($props['filas'] as $filaIdx => $fila) {
            if (!empty($fila['reactivos'])) {
                $nombre = $fila['nombre'] ?? $fila['analisis'] ?? "Fila " . ($filaIdx + 1);
                $itemsConReactivo[] = [
                    'wire_key' => "{$index}.{$filaIdx}",
                    'nombre' => $nombre,
                    'tipo_check' => 'fila',
                    'fila_idx' => $filaIdx,
                ];
                $tieneReactivos = true;
            }
        }
    } elseif (in_array($tipo, ['campos-etiquetados', 'serologia']) && !empty($props['campos'])) {
        foreach ($props['campos'] as $campoIdx => $campo) {
            if (is_array($campo) && !empty($campo['reactivos'])) {
                $itemsConReactivo[] = [
                    'wire_key' => "{$index}.c{$campoIdx}",
                    'nombre' => $campo['nombre'] ?? "Campo " . ($campoIdx + 1),
                    'tipo_check' => 'campo',
                    'campo_nombre' => $campo['nombre'] ?? '',
                    'campo_idx' => $campoIdx,
                ];
                $tieneReactivos = true;
            }
        }
    } elseif ($tipo === 'tabla-dos-columnas') {
        if (!empty($props['secciones'])) {
            foreach ($props['secciones'] as $secIdx => $seccion) {
                foreach ($seccion['campos'] ?? [] as $campoIdx => $campo) {
                    if (!empty($campo['reactivos'])) {
                        $itemsConReactivo[] = [
                            'wire_key' => "{$index}.s{$secIdx}.c{$campoIdx}",
                            'nombre' => $campo['nombre'] ?? "Campo",
                            'tipo_check' => 'campo_anidado',
                            'campo_nombre' => $campo['nombre'] ?? '',
                        ];
                        $tieneReactivos = true;
                    }
                }
            }
        }
        if (!empty($props['reactivos'])) {
            $itemsConReactivo[] = [
                'wire_key' => "{$index}.global",
                'nombre' => ($props['titulo'] ?? 'Tabla') . " (Global)",
                'tipo_check' => 'bloque',
            ];
            $tieneReactivos = true;
        }
    } elseif (in_array($tipo, $tiposBloque) && !empty($props['reactivos'])) {
        $itemsConReactivo[] = [
            'wire_key' => (string) $index,
            'nombre' => $props['titulo'] ?? str_replace('-', ' ', ucfirst($tipo)),
            'tipo_check' => 'bloque',
        ];
        $tieneReactivos = true;
    }
@endphp

@if($tieneReactivos)
<div 
    x-data="{
        tipoComponente: @js($tipo),
        indexComponente: {{ $index }},
        items: @js($itemsConReactivo),
        repeticiones: {},
        tick: 0,
        
        init() {
            // Inicializar repeticiones en 0 (deshabilitadas)
            this.items.forEach((item, i) => {
                this.repeticiones[i] = 0;
            });
            
            // Escuchar cuando el componente padre sincroniza sus datos
            window.addEventListener('datos-sincronizados', (e) => {
                if (e.detail && e.detail.index == this.indexComponente) {
                    this.tick++;
                    this.actualizarEstados();
                }
            });
            
            // Verificar estado inicial (por si ya hay datos cargados)
            this.$nextTick(() => {
                this.actualizarEstados();
            });
        },
        
        actualizarEstados() {
            this.items.forEach((item, i) => {
                const tieneRes = this.tieneResultado(item);
                if (tieneRes && this.repeticiones[i] === 0) {
                    this.repeticiones[i] = 1;
                    this.sincronizarItem(i);
                } else if (!tieneRes && this.repeticiones[i] > 0) {
                    this.repeticiones[i] = 0;
                    this.sincronizarItem(i);
                }
            });
        },
        
        sincronizarItem(i) {
            const item = this.items[i];
            const val = this.repeticiones[i];
            $wire.set('repeticionesData.' + item.wire_key, val);
        },
        
        onRepChange(i) {
            const val = parseInt(this.repeticiones[i]) || 0;
            this.repeticiones[i] = val < 1 ? 1 : val;
            this.sincronizarItem(i);
        },
        
        esActivo(i) {
            return this.repeticiones[i] > 0;
        },
        
        tieneResultado(item) {
            const data = window.__labvetData ? window.__labvetData[this.indexComponente] : null;
            if (!data) return false;
            
            if (item.tipo_check === 'fila') {
                const fila = Array.isArray(data) ? data[item.fila_idx] : (data[item.fila_idx] ?? null);
                if (!fila) return false;
                if (this.tipoComponente === 'tabla-resultados') {
                    return fila.col_0 !== undefined && fila.col_0 !== '' && fila.col_0 !== null;
                }
                return fila.resultado !== undefined && fila.resultado !== '' && fila.resultado !== null;
            }
            
            if (item.tipo_check === 'campo') {
                if (this.tipoComponente === 'campos-etiquetados') {
                    const campos = data.campos ?? data;
                    if (!campos) return false;
                    const arr = Array.isArray(campos) ? campos : Object.values(campos);
                    return arr.some(c => c && (c.nombre === item.campo_nombre || c.campo === item.campo_nombre) && 
                        c.valor !== undefined && c.valor !== '' && c.valor !== null);
                }
                if (this.tipoComponente === 'serologia') {
                    const arr = Array.isArray(data) ? data : Object.values(data);
                    return arr.some(c => c && c.campo === item.campo_nombre && 
                        c.valor !== undefined && c.valor !== '' && c.valor !== null);
                }
                return false;
            }
            
            if (item.tipo_check === 'campo_anidado') {
                const arr = Array.isArray(data) ? data : Object.values(data);
                return arr.some(c => c && c.campo === item.campo_nombre && 
                    c.valor !== undefined && c.valor !== '' && c.valor !== null);
            }
            
            if (item.tipo_check === 'bloque') {
                if (this.tipoComponente === 'tabla-hematologica') {
                    const params = data.parametros ?? [];
                    const paramsArr = Array.isArray(params) ? params : Object.values(params);
                    return paramsArr.some(p => p && p.resultado !== undefined && p.resultado !== '' && p.resultado !== null);
                }
                if (this.tipoComponente === 'coproparasitologia-seriado') {
                    const campos = data.campos ?? [];
                    const arr = Array.isArray(campos) ? campos : Object.values(campos);
                    return arr.some(c => {
                        if (!c || !c.valores) return false;
                        const vals = Array.isArray(c.valores) ? c.valores : Object.values(c.valores);
                        return vals.some(v => v !== '' && v !== null && v !== undefined);
                    });
                }
                // antibiograma
                if (this.tipoComponente === 'antibiograma') {
                    const arr = Array.isArray(data) ? data : Object.values(data);
                    return arr.some(f => f && (f.sensible || f.intermedio || f.resistente));
                }
                // examen-diferencial, examen-microscopico
                if (this.tipoComponente === 'examen-diferencial' || this.tipoComponente === 'examen-microscopico') {
                    const arr = Array.isArray(data) ? data : Object.values(data);
                    return arr.some(f => f && f.resultado !== undefined && f.resultado !== '' && f.resultado !== null);
                }
                // carga-viral
                if (this.tipoComponente === 'carga-viral') {
                    const arr = Array.isArray(data) ? data : Object.values(data);
                    return arr.some(f => f && f.valor !== undefined && f.valor !== '' && f.valor !== null);
                }
                // Genérico
                if (typeof data === 'object') {
                    return Object.keys(data).length > 0;
                }
                return !!data;
            }
            
            return false;
        }
    }"
    class="mt-3 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg border border-emerald-200 dark:border-emerald-800"
>
    <p class="text-xs font-semibold text-emerald-800 dark:text-emerald-300 mb-2 flex items-center gap-1">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
        </svg>
        Repeticiones (Consumo de Reactivos)
    </p>
    <div class="space-y-2">
        @foreach($itemsConReactivo as $itemIdx => $item)
        <div class="flex items-center gap-3">
            <span 
                class="text-xs flex-1 truncate transition-colors duration-200"
                :class="esActivo({{ $itemIdx }}) ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-400 dark:text-zinc-500'"
            >
                {{ $item['nombre'] }}
            </span>
            <div class="flex items-center gap-1.5">
                {{-- Badge de estado --}}
                <template x-if="!esActivo({{ $itemIdx }})">
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-200 dark:bg-zinc-700 text-gray-500 dark:text-zinc-400 whitespace-nowrap">
                        sin resultado
                    </span>
                </template>
                <template x-if="esActivo({{ $itemIdx }})">
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-200 dark:bg-emerald-800 text-emerald-700 dark:text-emerald-300 whitespace-nowrap">
                        ✓ activo
                    </span>
                </template>
                <input
                    type="number"
                    x-model.number="repeticiones[{{ $itemIdx }}]"
                    @change="onRepChange({{ $itemIdx }})"
                    min="1"
                    step="1"
                    :disabled="!esActivo({{ $itemIdx }})"
                    class="w-16 px-2 py-1 border rounded text-xs text-center transition-all duration-200"
                    :class="esActivo({{ $itemIdx }}) 
                        ? 'border-emerald-300 dark:border-emerald-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 cursor-text' 
                        : 'border-gray-200 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800/50 text-gray-400 dark:text-zinc-500 cursor-not-allowed opacity-60'"
                />
                <span 
                    class="text-xs transition-colors duration-200"
                    :class="esActivo({{ $itemIdx }}) ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-zinc-500'"
                >rep.</span>
            </div>
        </div>
        @endforeach
    </div>
    <p class="text-[11px] text-emerald-600/70 dark:text-emerald-400/70 mt-2">
        <i class="fas fa-info-circle mr-1"></i>
        Las repeticiones se activan automáticamente al ingresar un resultado.
    </p>
</div>
@endif
