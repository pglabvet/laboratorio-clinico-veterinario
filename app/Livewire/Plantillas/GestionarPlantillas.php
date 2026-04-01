<?php

namespace App\Livewire\Plantillas;

use App\Models\CategoriaInsumo;
use App\Models\Insumo;
use App\Models\PlantillaFormulario;
use App\Models\TipoAnalisis;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GestionarPlantillas extends Component
{
    public $plantillaId = null;

    public $nombreFormulario = '';

    public $descripcionFormulario = '';

    public $tipo_analisis_id = null;

    public $componentes = [];

    public $componenteSeleccionado = null;

    // Insumos requeridos
    public $insumos = []; // Array de insumos agregados

    // Campos temporales para agregar nuevo insumo
    public $nuevaCategoria = '';

    public $nuevoInsumo = '';

    public $nuevaCantidad = 1;

    // Tipos de componentes disponibles
    // Nota: tabla-info no está aquí porque se incluye automáticamente en todos los análisis
    public $tiposComponentes = [
        'tabla-resultados' => 'Tabla de Resultados',
        'antibiograma' => 'Antibiograma',
        'tabla-hematologica' => 'Tabla Hematologica',
        'tabla-dos-columnas' => 'Tabla de Dos Columnas',
        'serologia' => 'Serología',
        'tabla-temporal' => 'Tabla Temporal con Gráfica',
        'carga-viral' => 'Carga Viral qPCR',
        'campos-etiquetados' => 'Lista de Campos Etiquetados',
        'texto-libre' => 'Texto Libre',
        'lista-items' => 'Lista de Items',
        'subtitulo' => 'Subtitulo',
        // 'campo-imagenes' => 'Campo de Imagenes', // TODO: Deshabilitado temporalmente - Fase 2
        'campo-texto' => 'Campo de Texto Simple',
        'examen-microscopico' => 'Examen Microscópico',
        'examen-diferencial' => 'Examen Diferencial',
        'coproparasitologia-seriado' => 'Coproparasitología Seriado',
        'citologia' => 'Citología',
    ];

    public function mount($plantilla = null)
    {
        // Si existe parÃƒÂ¡metro de plantilla, es modo ediciÃƒÂ³n
        if ($plantilla) {
            $this->cargarPlantilla($plantilla);
        }
        // Si existe el parÃƒÂ¡metro 'duplicar', cargar datos de esa plantilla
        elseif (request()->has('duplicar')) {
            $plantillaOriginal = PlantillaFormulario::with('insumos')->findOrFail(request('duplicar'));
            $this->nombreFormulario = $plantillaOriginal->nombre.' (Copia)';
            $this->descripcionFormulario = $plantillaOriginal->descripcion;
            $this->tipo_analisis_id = $plantillaOriginal->tipo_analisis_id;

            // Cargar insumos asociados
            $this->insumos = $plantillaOriginal->insumos->map(function ($insumo) {
                return [
                    'insumo_id' => $insumo->id,
                    'cantidad_requerida' => $insumo->pivot->cantidad_requerida,
                ];
            })->toArray();

            // Normalizar y asegurar IDs
            $this->componentes = $this->normalizarComponentes($plantillaOriginal->componentes);
        } else {
            $this->nombreFormulario = 'Nuevo Analisis';
            $this->descripcionFormulario = '';
        }
    }

    public function cargarPlantilla($id)
    {
        $plantilla = PlantillaFormulario::with('insumos')->findOrFail($id);
        $this->plantillaId = $plantilla->id;
        $this->nombreFormulario = $plantilla->nombre;
        $this->descripcionFormulario = $plantilla->descripcion ?? '';
        $this->tipo_analisis_id = $plantilla->tipo_analisis_id;

        // Cargar insumos asociados
        $this->insumos = $plantilla->insumos->map(function ($insumo) {
            return [
                'insumo_id' => $insumo->id,
                'cantidad_requerida' => $insumo->pivot->cantidad_requerida,
            ];
        })->toArray();

        // Normalizar y asegurar IDs
        $this->componentes = $this->normalizarComponentes($plantilla->componentes);
    }

    private function normalizarComponentes($componentesOriginales)
    {
        $componentes = $componentesOriginales ?? [];
        foreach ($componentes as &$componente) {
            if (! isset($componente['id'])) {
                $componente['id'] = uniqid('comp_', true);
            }
            
            // Migrar componentes antiguos de serologia al nuevo formato
            if (isset($componente['tipo']) && $componente['tipo'] === 'serologia') {
                // Migrar campos string → array
                if (isset($componente['propiedades']['campos'])) {
                    foreach ($componente['propiedades']['campos'] as &$campo) {
                        if (is_string($campo)) {
                            $campo = [
                                'nombre' => $campo,
                                'reactivos' => []
                            ];
                        }
                    }
                }
                
                // Asegurar que existan los campos de descripción tipo seleccionable
                if (!isset($componente['propiedades']['tipo_descripcion'])) {
                    $componente['propiedades']['tipo_descripcion'] = 'input';
                }
                if (!isset($componente['propiedades']['opciones_descripcion'])) {
                    $componente['propiedades']['opciones_descripcion'] = '';
                }
                // Limpiar campo descripciones si existía (migración transitoria)
                if (isset($componente['propiedades']['descripciones'])) {
                    $descs = array_filter($componente['propiedades']['descripciones']);
                    if (!empty($descs) && empty($componente['propiedades']['descripcion'])) {
                        // Convertir a formato seleccionable
                        $componente['propiedades']['tipo_descripcion'] = 'select';
                        $componente['propiedades']['opciones_descripcion'] = implode(',', $descs);
                    }
                    unset($componente['propiedades']['descripciones']);
                }
            }
            
            // Migrar componentes antiguos de citologia al nuevo formato de titulos
            if (isset($componente['tipo']) && $componente['tipo'] === 'citologia') {
                if (!isset($componente['propiedades']['titulos'])) {
                    $tituloBase = $componente['propiedades']['titulo'] ?? 'CITOLOGÍA';
                    $componente['propiedades']['titulos'] = [$tituloBase];
                }
            }
        }
        return array_values($componentes);
    }

    public function agregarComponente($tipo)
    {
        $id = uniqid();
        $componente = [
            'id' => $id,
            'tipo' => $tipo,
            'propiedades' => $this->propiedadesPorDefecto($tipo),
        ];

        $this->componentes[] = $componente;
        // Re-indexar para asegurar que los ÃƒÂ­ndices sean estables
        $this->componentes = array_values($this->componentes);
    }

    public function eliminarComponente($id)
    {
        $this->componentes = array_values(
            array_filter($this->componentes, fn ($c) => $c['id'] !== $id)
        );

        if ($this->componenteSeleccionado === $id) {
            $this->componenteSeleccionado = null;
        }
    }

    public function seleccionarComponente($id)
    {
        $this->componenteSeleccionado = $id;
    }

    public function actualizarPropiedadComponente($componenteId, $path, $valor)
    {
        $index = collect($this->componentes)->search(fn ($c) => $c['id'] === $componenteId);
        if ($index !== false) {
            data_set($this->componentes[$index]['propiedades'], $path, $valor);
        }
    }

    public function actualizarPropiedad($componenteId, $propiedad, $valor)
    {
        foreach ($this->componentes as $index => &$componente) {
            if ($componente['id'] === $componenteId) {
                data_set($componente['propiedades'], $propiedad, $valor);
                $this->componentes[$index] = $componente;
                break;
            }
        }
    }

    public function getComponenteIndexPorId($id)
    {
        $index = collect($this->componentes)->search(fn ($c) => $c['id'] === $id);

        return $index !== false ? $index : null;
    }

    private function propiedadesPorDefecto($tipo)
    {
        return match ($tipo) {
            'tabla-info' => [
                'titulo' => 'INFORMACION DEL PACIENTE',
                'filas' => [
                    ['label' => 'PACIENTE', 'campo' => 'nombre', 'tipo' => 'texto'],
                    ['label' => 'PROPIETARIO', 'campo' => 'propietario', 'tipo' => 'texto'],
                    ['label' => 'SOLICITADO POR', 'campo' => 'solicitante', 'tipo' => 'texto'],
                ],
                'columnas' => 3,
            ],
            'tabla-resultados' => [
                'titulo' => 'RESULTADOS',
                'descripcion' => '',
                'columnas' => [
                    ['nombre' => 'ANÁLISIS'],
                    ['nombre' => 'RESULTADO'],
                    ['nombre' => 'RANGOS DE REFERENCIA'],
                ],
                'filas' => [],
            ],
            'antibiograma' => [
                'titulo' => 'ANTIBIOGRAMA',
                'columnas' => ['SENSIBLE', 'INTERMEDIO', 'RESISTENTE'],
            ],
            'tabla-hematologica' => [
                'titulo' => 'CUADRO HEMÁTICO',
                'parametros_principales' => [
                    ['nombre' => 'Eritrocitos', 'unidad' => 'mm³', 'rango_tipo' => 'min-max', 'rango_min' => '5500000', 'rango_max' => '8500000', 'rango_valor' => ''],
                    ['nombre' => 'Leucocitos', 'unidad' => 'mm³', 'rango_tipo' => 'min-max', 'rango_min' => '6000', 'rango_max' => '16000', 'rango_valor' => ''],
                    ['nombre' => 'Hematocrito', 'unidad' => '%', 'rango_tipo' => 'min-max', 'rango_min' => '34', 'rango_max' => '37', 'rango_valor' => ''],
                    ['nombre' => 'Hemoglobina', 'unidad' => 'g/dl', 'rango_tipo' => 'min-max', 'rango_min' => '8', 'rango_max' => '11', 'rango_valor' => ''],
                    ['nombre' => 'Recuento de plaquetas', 'unidad' => 'mm³', 'rango_tipo' => 'min-max', 'rango_min' => '150000', 'rango_max' => '500000', 'rango_valor' => ''],
                ],
                'diferenciales' => [
                    ['nombre' => 'Cayados', 'rango_rel_tipo' => 'min-max', 'rango_rel_min' => '0', 'rango_rel_max' => '3', 'rango_rel_valor' => '', 'rango_abs_tipo' => 'min-max', 'rango_abs_min' => '0', 'rango_abs_max' => '300', 'rango_abs_valor' => ''],
                    ['nombre' => 'Segmentados', 'rango_rel_tipo' => 'min-max', 'rango_rel_min' => '60', 'rango_rel_max' => '77', 'rango_rel_valor' => '', 'rango_abs_tipo' => 'min-max', 'rango_abs_min' => '6000', 'rango_abs_max' => '7000', 'rango_abs_valor' => ''],
                    ['nombre' => 'Eosinófilos', 'rango_rel_tipo' => 'min-max', 'rango_rel_min' => '2', 'rango_rel_max' => '6', 'rango_rel_valor' => '', 'rango_abs_tipo' => 'min-max', 'rango_abs_min' => '200', 'rango_abs_max' => '300', 'rango_abs_valor' => ''],
                    ['nombre' => 'Basófilos', 'rango_rel_tipo' => 'min-max', 'rango_rel_min' => '0', 'rango_rel_max' => '1', 'rango_rel_valor' => '', 'rango_abs_tipo' => 'min-max', 'rango_abs_min' => '0', 'rango_abs_max' => '100', 'rango_abs_valor' => ''],
                    ['nombre' => 'Linfocitos', 'rango_rel_tipo' => 'min-max', 'rango_rel_min' => '12', 'rango_rel_max' => '30', 'rango_rel_valor' => '', 'rango_abs_tipo' => 'min-max', 'rango_abs_min' => '1200', 'rango_abs_max' => '3000', 'rango_abs_valor' => ''],
                    ['nombre' => 'Monocitos', 'rango_rel_tipo' => 'min-max', 'rango_rel_min' => '3', 'rango_rel_max' => '8', 'rango_rel_valor' => '', 'rango_abs_tipo' => 'min-max', 'rango_abs_min' => '300', 'rango_abs_max' => '800', 'rango_abs_valor' => ''],
                ],
                'indices' => [
                    ['nombre' => 'VCM', 'unidad' => 'fl', 'rango_tipo' => 'min-max', 'rango_min' => '60', 'rango_max' => '77', 'rango_valor' => ''],
                    ['nombre' => 'HbCM', 'unidad' => 'pg', 'rango_tipo' => 'min-max', 'rango_min' => '17', 'rango_max' => '23', 'rango_valor' => ''],
                    ['nombre' => 'CCMHb', 'unidad' => 'g/dl', 'rango_tipo' => 'min-max', 'rango_min' => '32', 'rango_max' => '36', 'rango_valor' => ''],
                ],
                'reactivos' => [],
            ],
            'tabla-dos-columnas' => [
                'titulo' => 'EXAMEN MACROSCOPICO',
                'secciones' => [
                    [
                        'subtitulo' => '',
                        'campos' => [
                            ['nombre' => 'COLOR', 'tipo_input' => 'input', 'opciones' => ''],
                            ['nombre' => 'CONSISTENCIA', 'tipo_input' => 'input', 'opciones' => ''],
                            ['nombre' => 'RESTOS ALIMENTICIOS', 'tipo_input' => 'input', 'opciones' => ''],
                        ],
                    ],
                    [
                        'subtitulo' => 'EXAMEN MICROSCOPICO',
                        'campos' => [
                            ['nombre' => 'LEVADURAS', 'tipo_input' => 'input', 'opciones' => ''],
                            ['nombre' => 'PARASITOS', 'tipo_input' => 'input', 'opciones' => ''],
                        ],
                    ],
                ],
                'reactivos' => [],
            ],
            'serologia' => [
                'titulo' => 'SEROLOGIA',
                'descripcion' => '',
                'tipo_descripcion' => 'input',
                'opciones_descripcion' => '',
                'columnas' => [
                    ['nombre' => 'PRUEBA'],
                    ['nombre' => 'RESULTADO'],
                ],
                'campos' => [
                    ['nombre' => 'Dirofilaria Immitis', 'reactivos' => []],
                    ['nombre' => 'Erlichia Canis', 'reactivos' => []],
                    ['nombre' => 'Leishmania infantum', 'reactivos' => []],
                    ['nombre' => 'Anaplacia Phagocytophilum/Anaplacia Platys', 'reactivos' => []],
                ],
            ],
            'campos-etiquetados' => [
                'titulos' => ['CULTIVO'],
                'columnas' => [
                    ['nombre' => 'CAMPO'],
                    ['nombre' => 'RESULTADO'],
                ],
                'campos' => [
                    ['nombre' => 'MUESTRA', 'tipo_input' => 'texto', 'opciones' => '', 'unidad' => ''],
                    ['nombre' => 'COLOR', 'tipo_input' => 'texto', 'opciones' => '', 'unidad' => ''],
                    ['nombre' => 'TINCION GRAM', 'tipo_input' => 'texto', 'opciones' => '', 'unidad' => ''],
                    ['nombre' => 'RECUENTO DE COLONIAS', 'tipo_input' => 'select', 'opciones' => 'Menor a 100000,Mayor a 100000', 'unidad' => 'UFC/ml'],
                    ['nombre' => 'CRECIMIENTO MICOTICO', 'tipo_input' => 'texto', 'opciones' => '', 'unidad' => ''],
                    ['nombre' => 'DIAGNOSTICO MICROBIOLOGICO', 'tipo_input' => 'texto', 'opciones' => '', 'unidad' => ''],
                ],
            ],
            'texto-libre' => [
                'titulo' => 'OBSERVACIONES',
                'contenido' => '',
                'formato' => 'parrafos', // parrafos, lista
                'reactivos' => [],
            ],
            'lista-items' => [
                'titulo' => 'ITEMS',
                'items' => [],
            ],
            'subtitulo' => [
                'texto' => 'SUBTITULO',
                'alineacion' => 'centro',
                'tamano' => 'grande',
            ],
            'campo-imagenes' => [
                'titulo' => 'IMAGENES',
                'permitir' => true,
                'cantidad' => 2,
            ],
            'campo-texto' => [
                'titulo' => '',
                'tipo_uso' => 'editable', // editable, nota
                'contenido' => '',
            ],
            'tabla-temporal' => [
                'titulo' => 'ANÁLISIS TEMPORAL',
                'mostrar_grafica' => true,
                'unidad_medida' => 'ug/dL',
                'filas' => [
                    [
                        'analisis' => 'Cortisol basal 1ra',
                        'rango_tipo' => 'min-max',
                        'rango_min' => '2.0',
                        'rango_max' => '6.0',
                        'rango_valor' => '',
                        'unidad' => 'ug/dL',
                    ],
                    [
                        'analisis' => 'Cortisol basal 2ra',
                        'rango_tipo' => 'min-max',
                        'rango_min' => '2.0',
                        'rango_max' => '6.0',
                        'rango_valor' => '',
                        'unidad' => 'ug/dL',
                    ],
                    [
                        'analisis' => 'Cortisol basal 3ra',
                        'rango_tipo' => 'min-max',
                        'rango_min' => '2.0',
                        'rango_max' => '6.0',
                        'rango_valor' => '',
                        'unidad' => 'ug/dL',
                    ],
                ],
            ],
            'carga-viral' => [
                'titulo' => 'DETECCIÓN POR qPCR EN TIEMPO REAL',
                'patogeno' => 'FeLV',
                'nombre_completo' => 'Virus de la Leucemia Felina',
                'umbral_valor' => 10,
                'umbral_exponente' => 5,
                'unidad' => 'copias/ml',
                'campos' => [
                    ['etiqueta' => 'MUESTRA ANALIZADA', 'tipo' => 'texto'],
                    ['etiqueta' => 'RESULTADO', 'tipo' => 'select'],
                    ['etiqueta' => 'CARGA VIRAL', 'tipo' => 'numero_cientifico'],
                ],
                'interpretaciones' => [
                    'no_detectado' => [
                        'etiqueta' => 'NO DETECTADO',
                        'descripcion' => 'Sin detección de ADN viral en la muestra analizada.',
                    ],
                    'regresivo' => [
                        'etiqueta' => 'INFECCIÓN REGRESIVA',
                        'descripcion' => 'Carga viral baja, posible infección en fase de resolución.',
                    ],
                    'progresivo' => [
                        'etiqueta' => 'INFECCIÓN PROGRESIVA',
                        'descripcion' => 'Carga viral alta, infección activa y progresiva.',
                    ],
                ],
                'mostrar_grafica' => true,
            ],
            'examen-microscopico' => [
                'titulo' => 'EXAMEN MICROSCOPICO',
                'columna_parametro' => 'PARÁMETRO',
                'columna_resultado' => 'RESULTADO',
                'columna_rango' => 'RANGO REF.',
                'filas' => [
                    ['parametro' => 'LEUCOCITOS', 'rango_tipo' => 'min-max', 'rango_min' => '', 'rango_max' => '', 'rango_valor' => '', 'unidad' => ''],
                    ['parametro' => 'CEL EPITELIALES', 'rango_tipo' => 'min-max', 'rango_min' => '', 'rango_max' => '', 'rango_valor' => '', 'unidad' => ''],
                    ['parametro' => 'HEMATIES', 'rango_tipo' => 'min-max', 'rango_min' => '', 'rango_max' => '', 'rango_valor' => '', 'unidad' => ''],
                    ['parametro' => 'FLORA BACTERIANA', 'rango_tipo' => 'min-max', 'rango_min' => '', 'rango_max' => '', 'rango_valor' => '', 'unidad' => ''],
                    ['parametro' => 'OTROS', 'rango_tipo' => 'min-max', 'rango_min' => '', 'rango_max' => '', 'rango_valor' => '', 'unidad' => ''],
                ],
            ],
            'examen-diferencial' => [
                'titulo' => 'EXAMEN DIFERENCIAL CELULAR',
                'columna_analisis' => 'ANÁLISIS',
                'columna_resultado' => 'RESULTADO',
                'columna_rango' => 'RANGO REF.',
                'filas' => [
                    ['tipo_fila' => '3col', 'nombre' => 'LEUCOCITOS', 'rango_tipo' => 'menor', 'rango_min' => '', 'rango_max' => '', 'rango_valor' => '2000', 'unidad' => 'cel/mm3', 'opciones' => ''],
                    ['tipo_fila' => '3col', 'nombre' => 'LEUCOCITOS MONOMORFONUCLEARES', 'rango_tipo' => 'min-max', 'rango_min' => '20', 'rango_max' => '30', 'rango_valor' => '', 'unidad' => '%', 'opciones' => ''],
                    ['tipo_fila' => '3col', 'nombre' => 'LEUCOCITOS POLIMORFONUCLEARES', 'rango_tipo' => 'min-max', 'rango_min' => '60', 'rango_max' => '70', 'rango_valor' => '', 'unidad' => '%', 'opciones' => ''],
                    ['tipo_fila' => '2col', 'nombre' => 'ERITROCITOS', 'rango_tipo' => 'min-max', 'rango_min' => '', 'rango_max' => '', 'rango_valor' => '', 'unidad' => '', 'opciones' => 'NINGUNO,ESCASO,MODERADO,ABUNDANTE'],
                ],
            ],
            'coproparasitologia-seriado' => [
                'titulo' => 'COPROPARASITOLOGÍA SERIADO',
                'num_muestras' => 3,
                'mostrar_fecha' => true,
                'secciones' => [
                    [
                        'subtitulo' => '',
                        'campos' => [
                            ['nombre' => 'COLOR', 'tipo_input' => 'select', 'opciones' => 'Café,Amarillo,Verde,Rojo,Negro,Blanco'],
                            ['nombre' => 'CONSISTENCIA', 'tipo_input' => 'select', 'opciones' => 'Duro,Blando,Semilíquido,Líquido,Pastoso'],
                            ['nombre' => 'RESTOS ALIMENTICIOS', 'tipo_input' => 'input', 'opciones' => ''],
                        ],
                    ],
                    [
                        'subtitulo' => 'EXAMEN MICROSCOPICO',
                        'campos' => [
                            ['nombre' => 'LEVADURAS', 'tipo_input' => 'input', 'opciones' => ''],
                            ['nombre' => 'PARASITOS', 'tipo_input' => 'input', 'opciones' => ''],
                        ],
                    ],
                ],
            ],
            'citologia' => [
                'titulos' => ['CITOLOGÍA'],
                'tumores' => [
                    'Histiositoma fibroso benigno',
                    'Mastocitoma',
                    'Lipoma',
                    'Fibrosarcoma',
                    'Melanoma',
                ],
                'secciones' => [
                    [
                        'titulo' => 'Observación macroscópica',
                        'texto_base' => '',
                        'tipo' => 'editable',
                        'usa_tumor' => false,
                        'textos_por_tumor' => [],
                    ],
                    [
                        'titulo' => 'Observación microscópica',
                        'texto_base' => '',
                        'tipo' => 'dependiente',
                        'usa_tumor' => true,
                        'textos_por_tumor' => [],
                    ],
                    [
                        'titulo' => 'Diagnostico citológico',
                        'texto_base' => 'Según la presencia celular se confirma el diagnostico de',
                        'tipo' => 'con_tumor',
                        'usa_tumor' => true,
                        'textos_por_tumor' => [],
                    ],
                ],
                'reactivos' => [],
            ],
            default => [],
        };
    }

    public function agregarInsumo()
    {
        $this->validate([
            'nuevoInsumo' => 'required|exists:insumos,id',
            'nuevaCantidad' => 'required|numeric|min:0.01',
        ], [
            'nuevoInsumo.required' => 'Debe seleccionar un insumo',
            'nuevoInsumo.exists' => 'El insumo seleccionado no es válido',
            'nuevaCantidad.required' => 'La cantidad es obligatoria',
            'nuevaCantidad.numeric' => 'La cantidad debe ser un número',
            'nuevaCantidad.min' => 'La cantidad debe ser mayor a 0',
        ]);

        // Verificar que no esté duplicado
        $existe = collect($this->insumos)->contains('insumo_id', $this->nuevoInsumo);
        if ($existe) {
            session()->flash('error', 'Este insumo ya ha sido agregado');

            return;
        }

        $this->insumos[] = [
            'insumo_id' => $this->nuevoInsumo,
            'cantidad_requerida' => $this->nuevaCantidad,
        ];

        // Limpiar campos
        $this->nuevaCategoria = '';
        $this->nuevoInsumo = '';
        $this->nuevaCantidad = 1;
    }

    public function eliminarInsumo($index)
    {
        unset($this->insumos[$index]);
        $this->insumos = array_values($this->insumos);
    }

    public function updatedNuevaCategoria($value)
    {
        // Limpiar el insumo seleccionado cuando cambia la categoría
        $this->nuevoInsumo = '';
    }

    public function guardarFormulario()
    {
        $this->validate([
            'nombreFormulario' => 'required|min:3|max:255',
            'insumos' => 'nullable|array',
            'insumos.*.insumo_id' => 'required|exists:insumos,id',
            'insumos.*.cantidad_requerida' => 'required|numeric|min:0.01',
        ], [
            'nombreFormulario.required' => 'El nombre del formulario es obligatorio',
            'nombreFormulario.min' => 'El nombre debe tener al menos 3 caracteres',
            'insumos.*.insumo_id.required' => 'Debe seleccionar un insumo',
            'insumos.*.insumo_id.exists' => 'El insumo seleccionado no es válido',
            'insumos.*.cantidad_requerida.required' => 'La cantidad es obligatoria',
            'insumos.*.cantidad_requerida.numeric' => 'La cantidad debe ser un número',
            'insumos.*.cantidad_requerida.min' => 'La cantidad debe ser mayor a 0',
        ]);

        try {
            if ($this->plantillaId) {
                // Actualizar plantilla existente
                $plantillaOriginal = PlantillaFormulario::findOrFail($this->plantillaId);

                // Verificar si la plantilla está en uso
                if ($plantillaOriginal->estaEnUso()) {
                    // CREAR NUEVA VERSIÓN en lugar de modificar
                    $nuevaVersion = $plantillaOriginal->version + 1;

                    // Desactivar la plantilla anterior
                    $plantillaOriginal->update(['activo' => false]);

                    // Determinar el ID base (la primera versión de la cadena)
                    $plantillaBaseId = $plantillaOriginal->plantilla_base_id ?? $plantillaOriginal->id;

                    // Crear nueva versión
                    $plantilla = PlantillaFormulario::create([
                        'nombre' => $this->nombreFormulario,
                        'descripcion' => $this->descripcionFormulario,
                        'tipo_analisis_id' => $this->tipo_analisis_id ?: null,
                        'componentes' => $this->componentes,
                        'activo' => true,
                        'creado_por' => Auth::id(),
                        'version' => $nuevaVersion,
                        'plantilla_base_id' => $plantillaBaseId,
                    ]);

                    // Sincronizar insumos en la nueva versión
                    $this->sincronizarInsumos($plantilla);

                    $mensaje = "Se ha creado la versión {$nuevaVersion} de la plantilla. Los análisis anteriores mantienen la versión anterior.";
                } else {
                    // Plantilla sin uso - actualizar normalmente
                    $plantillaOriginal->update([
                        'nombre' => $this->nombreFormulario,
                        'descripcion' => $this->descripcionFormulario,
                        'tipo_analisis_id' => $this->tipo_analisis_id ?: null,
                        'componentes' => $this->componentes,
                    ]);

                    // Sincronizar insumos
                    $this->sincronizarInsumos($plantillaOriginal);

                    $mensaje = 'Plantilla actualizada correctamente';
                }
            } else {
                // Crear nueva plantilla
                $plantilla = PlantillaFormulario::create([
                    'nombre' => $this->nombreFormulario,
                    'descripcion' => $this->descripcionFormulario,
                    'tipo_analisis_id' => $this->tipo_analisis_id ?: null,
                    'componentes' => $this->componentes,
                    'activo' => true,
                    'creado_por' => Auth::id(),
                    'version' => 1,
                    'plantilla_base_id' => null,
                ]);

                // Sincronizar insumos
                $this->sincronizarInsumos($plantilla);

                $mensaje = 'Plantilla creada correctamente';
            }

            session()->flash('success', $mensaje);

            return redirect()->route('plantillas.index');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: '.$e->getMessage());
        }
    }

    private function sincronizarInsumos($plantilla)
    {
        // Filtrar insumos válidos (que tengan insumo_id y cantidad)
        $insumosValidos = collect($this->insumos)
            ->filter(function ($insumo) {
                return ! empty($insumo['insumo_id']) && ! empty($insumo['cantidad_requerida']);
            });

        // Preparar array para sync
        $syncData = [];
        foreach ($insumosValidos as $insumo) {
            $syncData[$insumo['insumo_id']] = [
                'cantidad_requerida' => $insumo['cantidad_requerida'],
            ];
        }

        // Sincronizar relación
        $plantilla->insumos()->sync($syncData);
    }

    public function render()
    {
        $tiposAnalisis = TipoAnalisis::where('estado', true)
            ->orderBy('nombre')
            ->get();

        $categoriasInsumos = CategoriaInsumo::where('estado', true)
            ->orderBy('nombre')
            ->get();

        $insumosDisponibles = Insumo::with(['unidadMedida', 'categoria'])
            ->where('estado', true)
            ->orderBy('nombre')
            ->get();

        return view('livewire.plantillas.gestionar-plantillas', [
            'tiposAnalisis' => $tiposAnalisis,
            'insumosDisponibles' => $insumosDisponibles,
            'categoriasInsumos' => $categoriasInsumos,
        ])->layout('components.layouts.app-fullscreen');
    }
}
