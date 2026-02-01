<?php

namespace App\Livewire\Plantillas;

use Livewire\Component;
use App\Models\PlantillaFormulario;
use App\Models\TipoAnalisis;
use App\Models\Insumo;
use Illuminate\Support\Facades\Auth;

class GestionarPlantillas extends Component
{
    public $plantillaId = null;
    public $nombreFormulario = '';
    public $descripcionFormulario = '';
    public $tipo_analisis_id = null;
    public $componentes = [];
    public $componenteSeleccionado = null;
    
    // Insumos requeridos
    public $insumos = []; // Array de ['insumo_id' => x, 'cantidad_requerida' => y]
    
    // Tipos de componentes disponibles
    // Nota: tabla-info no está aquí porque se incluye automáticamente en todos los análisis
    public $tiposComponentes = [
        'tabla-resultados' => 'Tabla de Resultados',
        'antibiograma' => 'Antibiograma',
        'tabla-hematologica' => 'Tabla Hematologica',
        'tabla-dos-columnas' => 'Tabla de Dos Columnas',
        'tabla-temporal' => 'Tabla Temporal con Gráfica',
        'campos-etiquetados' => 'Lista de Campos Etiquetados',
        'texto-libre' => 'Texto Libre',
        'lista-items' => 'Lista de Items',
        'subtitulo' => 'Subtitulo',
        'campo-imagenes' => 'Campo de Imagenes',
        'campo-texto' => 'Campo de Texto Simple',
    ];

    public function mount($plantilla = null)
    {
        // Si existe parÃƒÂ¡metro de plantilla, es modo ediciÃƒÂ³n
        if ($plantilla) {
            $this->cargarPlantilla($plantilla);
        }
        // Si existe el parÃƒÂ¡metro 'duplicar', cargar datos de esa plantilla
        elseif (request()->has('duplicar')) {
            $plantillaOriginal = PlantillaFormulario::findOrFail(request('duplicar'));
            $this->nombreFormulario = $plantillaOriginal->nombre . ' (Copia)';
            $this->descripcionFormulario = $plantillaOriginal->descripcion;
            
            // Asegurar que todos los componentes tengan ID
            $componentes = $plantillaOriginal->componentes ?? [];
            foreach ($componentes as &$componente) {
                if (!isset($componente['id'])) {
                    $componente['id'] = uniqid('comp_', true);
                }
            }
            $this->componentes = array_values($componentes);
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
        $this->insumos = $plantilla->insumos->map(function($insumo) {
            return [
                'insumo_id' => $insumo->id,
                'cantidad_requerida' => $insumo->pivot->cantidad_requerida,
            ];
        })->toArray();
        
        // Asegurar que todos los componentes tengan ID
        $componentes = $plantilla->componentes ?? [];
        foreach ($componentes as &$componente) {
            if (!isset($componente['id'])) {
                $componente['id'] = uniqid('comp_', true);
            }
        }
        $this->componentes = array_values($componentes);
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
            array_filter($this->componentes, fn($c) => $c['id'] !== $id)
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
        $index = collect($this->componentes)->search(fn($c) => $c['id'] === $componenteId);
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
        $index = collect($this->componentes)->search(fn($c) => $c['id'] === $id);
        return $index !== false ? $index : null;
    }

    private function propiedadesPorDefecto($tipo)
    {
        return match($tipo) {
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
                    ['nombre' => 'Eritrocitos', 'unidad' => 'mm³', 'ref_min' => '5.500.000', 'ref_max' => '8.500.000'],
                    ['nombre' => 'Leucocitos', 'unidad' => 'mm³', 'ref_min' => '6.000', 'ref_max' => '16.000'],
                    ['nombre' => 'Hematocrito', 'unidad' => '%', 'ref_min' => '34', 'ref_max' => '37'],
                    ['nombre' => 'Hemoglobina', 'unidad' => 'g/dl', 'ref_min' => '8', 'ref_max' => '11'],
                    ['nombre' => 'Recuento de plaquetas', 'unidad' => 'mm³', 'ref_min' => '150.000', 'ref_max' => '500.000'],
                ],
                'diferenciales' => [
                    ['nombre' => 'Cayados', 'ref_rel_min' => '0', 'ref_rel_max' => '3', 'ref_abs_min' => '0', 'ref_abs_max' => '300'],
                    ['nombre' => 'Segmentados', 'ref_rel_min' => '60', 'ref_rel_max' => '77', 'ref_abs_min' => '6000', 'ref_abs_max' => '7000'],
                    ['nombre' => 'Eosinófilos', 'ref_rel_min' => '2', 'ref_rel_max' => '6', 'ref_abs_min' => '200', 'ref_abs_max' => '300'],
                    ['nombre' => 'Basófilos', 'ref_rel_min' => '0', 'ref_rel_max' => '1', 'ref_abs_min' => '0', 'ref_abs_max' => '100'],
                    ['nombre' => 'Linfocitos', 'ref_rel_min' => '12', 'ref_rel_max' => '30', 'ref_abs_min' => '1200', 'ref_abs_max' => '3000'],
                    ['nombre' => 'Monocitos', 'ref_rel_min' => '3', 'ref_rel_max' => '8', 'ref_abs_min' => '300', 'ref_abs_max' => '800'],
                ],
                'indices' => [
                    ['nombre' => 'VCM', 'unidad' => 'fl', 'referencia' => 'vn 60-77 fl'],
                    ['nombre' => 'HbCM', 'unidad' => 'pg', 'referencia' => 'vn 17-23 pg'],
                    ['nombre' => 'CCMHb', 'unidad' => 'g/dl', 'referencia' => 'Vn 32-36 g/dl'],
                ],
            ],
            'tabla-dos-columnas' => [
                'titulo' => 'EXAMEN MACROSCOPICO',
                'secciones' => [
                    [
                        'subtitulo' => '',
                        'campos' => ['COLOR', 'CONSISTENCIA', 'RESTOS ALIMENTICIOS'],
                    ],
                    [
                        'subtitulo' => 'EXAMEN MICROSCOPICO',
                        'campos' => ['LEVADURAS', 'PARASITOS'],
                    ],
                ],
            ],
            'campos-etiquetados' => [
                'titulo' => 'CULTIVO',
                'campos' => [
                    'MUESTRA',
                    'COLOR',
                    'TINCION GRAM',
                    'RECUENTO DE COLONIAS',
                    'CRECIMIENTO MICOTICO',
                    'DIAGNOSTICO MICROBIOLOGICO',
                ],
            ],
            'texto-libre' => [
                'titulo' => 'OBSERVACIONES',
                'contenido' => '',
                'formato' => 'parrafos', // parrafos, lista
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
                'label' => 'Campo',
                'placeholder' => '',
                'tipo' => 'texto', // texto, numero, fecha
            ],
            'tabla-temporal' => [
                'titulo' => 'ANÁLISIS TEMPORAL',
                'mostrar_grafica' => true,
                'unidad_medida' => 'ug/dL',
                'filas' => [
                    [
                        'analisis' => 'Cortisol basal 1ra',
                        'rango_referencia' => '2.0 - 6.0 ug/dL',
                    ],
                    [
                        'analisis' => 'Cortisol basal 2ra',
                        'rango_referencia' => '2.0 - 6.0 ug/dL',
                    ],
                    [
                        'analisis' => 'Cortisol basal 3ra',
                        'rango_referencia' => '2.0 - 6.0 ug/dL',
                    ],
                ],
            ],
            default => [],
        };
    }

    public function agregarInsumo()
    {
        $this->insumos[] = ['insumo_id' => '', 'cantidad_requerida' => 1];
    }

    public function eliminarInsumo($index)
    {
        unset($this->insumos[$index]);
        $this->insumos = array_values($this->insumos);
    }

    public function guardarFormulario()
    {
        $this->validate([
            'nombreFormulario' => 'required|min:3|max:255',
            'insumos.*.insumo_id' => 'nullable|exists:insumos,id',
            'insumos.*.cantidad_requerida' => 'nullable|numeric|min:0.01',
        ], [
            'nombreFormulario.required' => 'El nombre del formulario es obligatorio',
            'nombreFormulario.min' => 'El nombre debe tener al menos 3 caracteres',
            'insumos.*.insumo_id.exists' => 'El insumo seleccionado no es válido',
            'insumos.*.cantidad_requerida.numeric' => 'La cantidad debe ser un número',
            'insumos.*.cantidad_requerida.min' => 'La cantidad debe ser mayor a 0',
        ]);

        try {
            if ($this->plantillaId) {
                // Actualizar plantilla existente
                $plantilla = PlantillaFormulario::findOrFail($this->plantillaId);
                $plantilla->update([
                    'nombre' => $this->nombreFormulario,
                    'descripcion' => $this->descripcionFormulario,
                    'tipo_analisis_id' => $this->tipo_analisis_id,
                    'componentes' => $this->componentes,
                ]);
                
                // Sincronizar insumos
                $this->sincronizarInsumos($plantilla);
                
                $mensaje = 'Plantilla actualizada correctamente';
            } else {
                // Crear nueva plantilla
                $plantilla = PlantillaFormulario::create([
                    'nombre' => $this->nombreFormulario,
                    'descripcion' => $this->descripcionFormulario,
                    'tipo_analisis_id' => $this->tipo_analisis_id,
                    'componentes' => $this->componentes,
                    'activo' => true,
                    'creado_por' => Auth::id(),
                ]);
                
                // Sincronizar insumos
                $this->sincronizarInsumos($plantilla);
                
                $mensaje = 'Plantilla creada correctamente';
            }

            session()->flash('success', $mensaje);
            
            return redirect()->route('plantillas.index');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    private function sincronizarInsumos($plantilla)
    {
        // Filtrar insumos válidos (que tengan insumo_id y cantidad)
        $insumosValidos = collect($this->insumos)
            ->filter(function($insumo) {
                return !empty($insumo['insumo_id']) && !empty($insumo['cantidad_requerida']);
            });

        // Preparar array para sync
        $syncData = [];
        foreach ($insumosValidos as $insumo) {
            $syncData[$insumo['insumo_id']] = [
                'cantidad_requerida' => $insumo['cantidad_requerida']
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

        $insumosDisponibles = Insumo::with('unidadMedida')
            ->where('estado', true)
            ->orderBy('nombre')
            ->get();

        return view('livewire.plantillas.gestionar-plantillas', [
            'tiposAnalisis' => $tiposAnalisis,
            'insumosDisponibles' => $insumosDisponibles,
        ]);
    }
}
