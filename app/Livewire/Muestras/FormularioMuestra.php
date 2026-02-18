<?php

namespace App\Livewire\Muestras;

use App\Models\Muestra;
use App\Models\Especie;
use App\Models\Veterinaria;
use App\Models\Sucursal;
use App\Models\TipoAnalisis;
use App\Models\PlantillaFormulario;
use App\Models\Analisis;
use App\Services\MuestraService;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

class FormularioMuestra extends Component
{
    // Propiedades del formulario - Muestra
    public $muestra_id;
    public $codigo_muestra;
    public $tipo_muestra;
    public $fecha_recepcion;
    public $estado = 'Pendiente';
    public $observaciones = 'Sin Observaciones';
    public $sucursal_id;

    // Propiedades del formulario - Paciente
    public $paciente_nombre;
    public $especie_id;
    public $raza;
    public $edadCantidad;
    public $edadUnidad = 'años';
    public $sexo = 'M';
    public $color;
    public $propietario_nombre;

    // Propiedades del formulario - Veterinaria
    public $veterinaria_id;

    // Análisis seleccionados con sus plantillas
    public $analisisSeleccionados = []; // [{tipo_analisis_id: X, plantilla_id: Y}]
    
    // Control del modal de análisis
    public $modalAnalisisAbierto = false;
    public $tipoAnalisisTemp;
    public $plantillasDisponibles = [];
    public $plantillaSeleccionadaTemp;

    // Reglas de validación
    protected function rules()
    {
        return [
            'paciente_nombre' => 'required|string|max:255',
            'especie_id' => 'required|exists:especies,id',
            'raza' => 'nullable|string|max:100',
            'edadCantidad' => 'required|numeric|min:0|max:999',
            'edadUnidad' => 'required|in:años,meses,días',
            'sexo' => 'required|in:M,H',
            'color' => 'nullable|string|max:100',
            'propietario_nombre' => 'required|string|max:255',
            'veterinaria_id' => 'required|exists:veterinarias,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'tipo_muestra' => 'required|string|max:100',

            'observaciones' => 'nullable|string',
            'analisisSeleccionados' => 'required|array|min:1',
        ];
    }

    protected $messages = [
        'paciente_nombre.required' => 'El nombre del paciente es obligatorio.',
        'especie_id.required' => 'Debe seleccionar una especie.',
        'edadCantidad.required' => 'La edad del paciente es obligatoria.',
        'edadCantidad.numeric' => 'La edad debe ser un número.',
        'edadUnidad.required' => 'Debe seleccionar la unidad de edad.',
        'sexo.required' => 'El sexo del paciente es obligatorio.',
        'propietario_nombre.required' => 'El nombre del propietario es obligatorio.',
        'veterinaria_id.required' => 'Debe seleccionar una veterinaria.',
        'sucursal_id.required' => 'Debe seleccionar una sucursal.',
        'tipo_muestra.required' => 'El tipo de muestra es obligatorio.',

        'analisisSeleccionados.required' => 'Debe agregar al menos un análisis.',
        'analisisSeleccionados.min' => 'Debe agregar al menos un análisis.',
    ];

    public function mount($id = null)
    {

        $this->sucursal_id = auth()->user()->sucursal_id ?? Sucursal::first()?->id;

        if ($id) {
            $this->cargarMuestra($id);
        }
    }

    public function cargarMuestra($id)
    {
        $muestra = Muestra::with('analisis.plantillaFormulario')->findOrFail($id);
        
        $this->muestra_id = $muestra->id;
        $this->codigo_muestra = $muestra->codigo_muestra;
        $this->tipo_muestra = $muestra->tipo_muestra;

        $this->observaciones = $muestra->observaciones;
        $this->sucursal_id = $muestra->sucursal_id;
        
        $this->paciente_nombre = $muestra->paciente_nombre;
        $this->especie_id = $muestra->especie_id;
        $this->raza = $muestra->raza;
        // Parsear la edad almacenada (ej: "3 años" -> cantidad=3, unidad=años)
        $this->parsearEdad($muestra->edad);
        $this->sexo = $muestra->sexo;
        $this->color = $muestra->color;
        $this->propietario_nombre = $muestra->propietario_nombre;
        $this->veterinaria_id = $muestra->veterinaria_id;

        // Cargar análisis seleccionados
        foreach ($muestra->analisis as $analisis) {
            $this->analisisSeleccionados[] = [
                'tipo_analisis_id' => $analisis->tipo_analisis_id,
                'plantilla_id' => $analisis->plantilla_formulario_id,
                'tipo_nombre' => $analisis->tipoAnalisis->nombre ?? '',
                'plantilla_nombre' => $analisis->plantillaFormulario->nombre ?? '',
                'plantilla_version' => $analisis->plantillaFormulario->version ?? 1,
            ];
        }
    }

    /**
     * Abrir modal para agregar análisis
     */
    public function abrirModalAnalisis()
    {
        $this->tipoAnalisisTemp = null;
        $this->plantillasDisponibles = [];
        $this->plantillaSeleccionadaTemp = null;
        $this->modalAnalisisAbierto = true;
    }

    /**
     * Cargar plantillas manualmente
     */
    public function cargarPlantillas()
    {
        if ($this->tipoAnalisisTemp) {
            $this->plantillasDisponibles = PlantillaFormulario::where('tipo_analisis_id', $this->tipoAnalisisTemp)
                ->where('activo', true)
                ->get();
            $this->plantillaSeleccionadaTemp = null;
        } else {
            $this->plantillasDisponibles = [];
            $this->plantillaSeleccionadaTemp = null;
        }
    }

    /**
     * Cargar plantillas cuando se selecciona un tipo de análisis
     */
    public function updatedTipoAnalisisTemp($value)
    {
        $this->cargarPlantillas();
    }

    /**
     * Agregar análisis a la lista
     */
    public function agregarAnalisis()
    {
        if (!$this->tipoAnalisisTemp || !$this->plantillaSeleccionadaTemp) {
            session()->flash('error', 'Debe seleccionar un tipo de análisis y una plantilla.');
            return;
        }

        // Verificar que no esté duplicado
        foreach ($this->analisisSeleccionados as $analisis) {
            if ($analisis['plantilla_id'] == $this->plantillaSeleccionadaTemp) {
                $plantilla = PlantillaFormulario::find($this->plantillaSeleccionadaTemp);
                $mensajeError = 'La plantilla "' . ($plantilla->nombre ?? 'seleccionada') . '"';
                if ($plantilla && $plantilla->version > 1) {
                    $mensajeError .= ' (v' . $plantilla->version . ')';
                }
                $mensajeError .= ' ya fue agregada a este análisis.';
                session()->flash('error', $mensajeError);
                return;
            }
        }

        $tipoAnalisis = TipoAnalisis::find($this->tipoAnalisisTemp);
        $plantilla = PlantillaFormulario::find($this->plantillaSeleccionadaTemp);

        if (!$tipoAnalisis || !$plantilla) {
            session()->flash('error', 'Error al cargar los datos seleccionados.');
            return;
        }

        $this->analisisSeleccionados[] = [
            'tipo_analisis_id' => $tipoAnalisis->id,
            'plantilla_id' => $plantilla->id,
            'tipo_nombre' => $tipoAnalisis->nombre,
            'plantilla_nombre' => $plantilla->nombre,
            'plantilla_version' => $plantilla->version ?? 1,
        ];

        // Limpiar los campos después de agregar
        $this->tipoAnalisisTemp = null;
        $this->plantillasDisponibles = [];
        $this->plantillaSeleccionadaTemp = null;
    }

    /**
     * Eliminar análisis de la lista
     */
    public function eliminarAnalisis($index)
    {
        unset($this->analisisSeleccionados[$index]);
        $this->analisisSeleccionados = array_values($this->analisisSeleccionados);
    }

    /**
     * Cerrar modal de análisis
     */
    public function cerrarModalAnalisis()
    {
        $this->modalAnalisisAbierto = false;
        $this->tipoAnalisisTemp = null;
        $this->plantillasDisponibles = [];
        $this->plantillaSeleccionadaTemp = null;
    }

    /**
     * Validar stock disponible antes de crear análisis
     */
    private function validarStockDisponible()
    {
        foreach ($this->analisisSeleccionados as $analisisData) {
            $plantilla = PlantillaFormulario::with('insumos')->find($analisisData['plantilla_id']);
            
            if (!$plantilla || $plantilla->insumos->isEmpty()) {
                continue; // No hay insumos configurados
            }

            $insumosInsuficientes = [];
            $insumosStockBajo = [];

            foreach ($plantilla->insumos as $insumo) {
                $cantidadRequerida = $insumo->pivot->cantidad_requerida;
                
                $inventario = InventarioSucursal::where('insumo_id', $insumo->id)
                    ->where('sucursal_id', $this->sucursal_id)
                    ->first();

                if (!$inventario || $inventario->stock_actual <= 0) {
                    $insumosInsuficientes[] = "{$insumo->nombre} (sin stock)";
                } elseif ($inventario->stock_actual < $cantidadRequerida) {
                    $insumosInsuficientes[] = "{$insumo->nombre} (disponible: {$inventario->stock_actual}, requerido: {$cantidadRequerida})";
                } elseif ($inventario->stock_actual <= $inventario->stock_minimo) {
                    $insumosStockBajo[] = $insumo->nombre;
                }
            }

            if (!empty($insumosInsuficientes)) {
                throw new \Exception(
                    "Stock insuficiente para el análisis '{$plantilla->nombre}': " . 
                    implode(', ', $insumosInsuficientes) . 
                    ". Por favor, registre una entrada de inventario antes de continuar."
                );
            }

            if (!empty($insumosStockBajo)) {
                session()->flash('warning', 
                    "⚠️ ADVERTENCIA: Los siguientes insumos están por debajo del stock mínimo para '{$plantilla->nombre}': " . 
                    implode(', ', $insumosStockBajo) . 
                    ". Se recomienda reabastecer pronto."
                );
            }
        }
    }

    /**
     * Guardar muestra
     */
    public function guardar()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $muestraService = app(MuestraService::class);

            // UC-B05: Validar stock antes de crear
            $plantillaIds = array_column($this->analisisSeleccionados, 'plantilla_id');
            $resultado = $muestraService->validarStockDisponible($plantillaIds, $this->sucursal_id);
            if (!empty($resultado['warnings'])) {
                session()->flash('warning',
                    '⚠️ ADVERTENCIA: Los siguientes insumos están por debajo del stock mínimo: ' .
                    implode(', ', $resultado['warnings']) .
                    '. Se recomienda reabastecer pronto.'
                );
            }

            // Generar código único si no existe
            if (!$this->codigo_muestra) {
                $this->codigo_muestra = $muestraService->generarCodigoMuestra($this->sucursal_id);
            }

            // Crear o actualizar muestra
            $muestra = Muestra::updateOrCreate(
                ['id' => $this->muestra_id],
                [
                    'codigo_muestra' => $this->codigo_muestra,
                    'paciente_nombre' => $this->paciente_nombre,
                    'especie_id' => $this->especie_id,
                    'raza' => $this->raza,
                    'edad' => $this->edadCantidad . ' ' . $this->edadUnidad,
                    'sexo' => $this->sexo,
                    'color' => $this->color,
                    'propietario_nombre' => $this->propietario_nombre,
                    'veterinaria_id' => $this->veterinaria_id,
                    'sucursal_id' => $this->sucursal_id,
                    'tipo_muestra' => $this->tipo_muestra,
                    'fecha_recepcion' => $this->muestra_id ? Muestra::find($this->muestra_id)->fecha_recepcion : now(),
                    'estado' => $this->estado,
                    'observaciones' => $this->observaciones,
                ]
            );

            // Si es edición, eliminar análisis anteriores
            if ($this->muestra_id) {
                $muestra->analisis()->delete();
            }

            // Crear análisis
            foreach ($this->analisisSeleccionados as $analisisData) {
                Analisis::create([
                    'muestra_id' => $muestra->id,
                    'tipo_analisis_id' => $analisisData['tipo_analisis_id'],
                    'plantilla_formulario_id' => $analisisData['plantilla_id'],
                    'bioquimico_id' => auth()->id(),
                    'estado' => 'Pendiente',
                ]);
            }

            DB::commit();

            // Si es edición, redirigir directamente
            if ($this->muestra_id) {
                session()->flash('mensaje', 'Muestra actualizada exitosamente.');
                return redirect()->route('muestras.index');
            }

            // Si es nueva muestra, guardar ID en sesión para mostrar modal en la vista de gestión
            session()->flash('mensaje', 'Muestra registrada exitosamente.');
            session()->flash('muestra_recien_creada_id', $muestra->id);
            return redirect()->route('muestras.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
        }
    }

    /**
     * Parsear string de edad a cantidad y unidad
     * Ej: "3 años" -> cantidad=3, unidad="años"
     */
    private function parsearEdad(?string $edad): void
    {
        if (!$edad) {
            $this->edadCantidad = null;
            $this->edadUnidad = 'años';
            return;
        }

        // Intentar parsear formato "N unidad" (ej: "3 años", "6 meses", "15 días")
        if (preg_match('/^(\d+)\s*(años?|meses?|días?|dias?)$/i', trim($edad), $matches)) {
            $this->edadCantidad = (int) $matches[1];
            $unidad = mb_strtolower($matches[2]);
            
            // Normalizar unidades
            if (str_starts_with($unidad, 'año') || str_starts_with($unidad, 'ano')) {
                $this->edadUnidad = 'años';
            } elseif (str_starts_with($unidad, 'mes')) {
                $this->edadUnidad = 'meses';
            } elseif (str_starts_with($unidad, 'día') || str_starts_with($unidad, 'dia')) {
                $this->edadUnidad = 'días';
            }
        } else {
            // Fallback: si es solo un número, asume años
            if (is_numeric(trim($edad))) {
                $this->edadCantidad = (int) trim($edad);
                $this->edadUnidad = 'años';
            } else {
                // Legacy: texto libre, poner todo como cantidad vacía
                $this->edadCantidad = null;
                $this->edadUnidad = 'años';
            }
        }
    }

    /**
     * Cancelar y volver
     */
    public function cancelar()
    {
        return redirect()->route('muestras.index');
    }

    public function render()
    {
        return view('livewire.muestras.formulario-muestra');
    }

    #[Computed]
    public function especies()
    {
        return Especie::where('estado', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function veterinarias()
    {
        return Veterinaria::where('estado', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function sucursales()
    {
        return Sucursal::where('estado', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function tiposAnalisis()
    {
        return TipoAnalisis::orderBy('nombre')->get();
    }

    #[Computed]
    public function puedeSeleccionarSucursal()
    {
        return auth()->user()->can('vista-general-sistema');
    }
}
