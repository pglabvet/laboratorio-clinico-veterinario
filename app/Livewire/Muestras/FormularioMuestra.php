<?php

namespace App\Livewire\Muestras;

use App\Models\Muestra;
use App\Models\Especie;
use App\Models\Veterinaria;
use App\Models\Sucursal;
use App\Models\TipoAnalisis;
use App\Models\PlantillaFormulario;
use App\Models\Analisis;
use App\Models\InventarioSucursal;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class FormularioMuestra extends Component
{
    // Propiedades del formulario - Muestra
    public $muestra_id;
    public $codigo_muestra;
    public $tipo_muestra;
    public $fecha_recepcion;
    public $estado = 'Pendiente';
    public $observaciones;
    public $sucursal_id;

    // Propiedades del formulario - Paciente
    public $paciente_nombre;
    public $especie_id;
    public $raza;
    public $edad;
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
            'edad' => 'required|string|max:50',
            'sexo' => 'required|in:M,H',
            'color' => 'nullable|string|max:100',
            'propietario_nombre' => 'required|string|max:255',
            'veterinaria_id' => 'required|exists:veterinarias,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'tipo_muestra' => 'required|string|max:100',
            'fecha_recepcion' => 'required|date',
            'observaciones' => 'nullable|string',
            'analisisSeleccionados' => 'required|array|min:1',
        ];
    }

    protected $messages = [
        'paciente_nombre.required' => 'El nombre del paciente es obligatorio.',
        'especie_id.required' => 'Debe seleccionar una especie.',
        'edad.required' => 'La edad del paciente es obligatoria.',
        'sexo.required' => 'El sexo del paciente es obligatorio.',
        'propietario_nombre.required' => 'El nombre del propietario es obligatorio.',
        'veterinaria_id.required' => 'Debe seleccionar una veterinaria.',
        'sucursal_id.required' => 'Debe seleccionar una sucursal.',
        'tipo_muestra.required' => 'El tipo de muestra es obligatorio.',
        'fecha_recepcion.required' => 'La fecha de recepción es obligatoria.',
        'analisisSeleccionados.required' => 'Debe agregar al menos un análisis.',
        'analisisSeleccionados.min' => 'Debe agregar al menos un análisis.',
    ];

    public function mount($id = null)
    {
        $this->fecha_recepcion = now()->format('Y-m-d');
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
        $this->fecha_recepcion = $muestra->fecha_recepcion->format('Y-m-d');
        $this->observaciones = $muestra->observaciones;
        $this->sucursal_id = $muestra->sucursal_id;
        
        $this->paciente_nombre = $muestra->paciente_nombre;
        $this->especie_id = $muestra->especie_id;
        $this->raza = $muestra->raza;
        $this->edad = $muestra->edad;
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
            $this->dispatch('error', message: 'Debe seleccionar un tipo de análisis y una plantilla.');
            return;
        }

        // Verificar que no esté duplicado
        foreach ($this->analisisSeleccionados as $analisis) {
            if ($analisis['plantilla_id'] == $this->plantillaSeleccionadaTemp) {
                $this->dispatch('error', message: 'Esta plantilla ya fue agregada.');
                return;
            }
        }

        $tipoAnalisis = TipoAnalisis::find($this->tipoAnalisisTemp);
        $plantilla = PlantillaFormulario::find($this->plantillaSeleccionadaTemp);

        if (!$tipoAnalisis || !$plantilla) {
            $this->dispatch('error', message: 'Error al cargar los datos seleccionados.');
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

            // UC-B05: Validar stock antes de crear
            $this->validarStockDisponible();

            // Generar código único si no existe
            if (!$this->codigo_muestra) {
                $this->codigo_muestra = $this->generarCodigoMuestra();
            }

            // Crear o actualizar muestra
            $muestra = Muestra::updateOrCreate(
                ['id' => $this->muestra_id],
                [
                    'codigo_muestra' => $this->codigo_muestra,
                    'paciente_nombre' => $this->paciente_nombre,
                    'especie_id' => $this->especie_id,
                    'raza' => $this->raza,
                    'edad' => $this->edad,
                    'sexo' => $this->sexo,
                    'color' => $this->color,
                    'propietario_nombre' => $this->propietario_nombre,
                    'veterinaria_id' => $this->veterinaria_id,
                    'sucursal_id' => $this->sucursal_id,
                    'tipo_muestra' => $this->tipo_muestra,
                    'fecha_recepcion' => $this->fecha_recepcion,
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
     * Cancelar y volver
     */
    public function cancelar()
    {
        return redirect()->route('muestras.index');
    }

    public function render()
    {
        return view('livewire.muestras.formulario-muestra', [
            'especies' => Especie::orderBy('nombre')->get(),
            'veterinarias' => Veterinaria::orderBy('nombre')->get(),
            'sucursales' => Sucursal::orderBy('nombre')->get(),
            'tiposAnalisis' => TipoAnalisis::orderBy('nombre')->get(),
        ]);
    }

    /**
     * Generar código único para la muestra por sucursal
     * Formato: {PREFIJO}-AA0000 (Prefijo de sucursal + 2 letras + 4 dígitos)
     * Ejemplo: S-AA0001 (Sucursal Sur), N-AA0002 (Sucursal Norte)
     * Rango por sucursal: AA0000 - ZZ9999 (676 * 10,000 = 6,760,000 combinaciones)
     */
    private function generarCodigoMuestra()
    {
        // Obtener prefijo de la sucursal
        $sucursal = Sucursal::find($this->sucursal_id);
        if (!$sucursal) {
            throw new \Exception('Sucursal no encontrada');
        }
        $prefijo = $sucursal->getPrefijo();
        
        // Obtener el último código de muestra de esta sucursal
        $ultimaMuestra = Muestra::where('sucursal_id', $this->sucursal_id)
            ->orderBy('id', 'desc')
            ->first();
        
        if (!$ultimaMuestra) {
            // Primera muestra de esta sucursal
            return $prefijo . '-AA0001';
        }
        
        // Extraer las partes del último código
        $ultimoCodigo = $ultimaMuestra->codigo_muestra;
        
        // Si no sigue el formato PREFIJO-AA0000, empezar desde AA0001
        if (!preg_match('/^[A-Z]{1,2}-([A-Z]{2})(\d{4})$/', $ultimoCodigo, $matches)) {
            return $prefijo . '-AA0001';
        }
        
        $letras = $matches[1];
        $numero = (int)$matches[2];
        
        // Incrementar el número
        $numero++;
        
        // Si el número excede 9999, incrementar las letras
        if ($numero > 9999) {
            $numero = 1;
            $letras = $this->incrementarLetras($letras);
        }
        
        return $prefijo . '-' . $letras . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Incrementar las letras del código (AA -> AB -> AC ... -> AZ -> BA -> BB ... -> ZZ)
     */
    private function incrementarLetras($letras)
    {
        $letra1 = $letras[0];
        $letra2 = $letras[1];
        
        // Incrementar segunda letra
        if ($letra2 === 'Z') {
            $letra2 = 'A';
            // Incrementar primera letra
            if ($letra1 === 'Z') {
                // Se acabaron las combinaciones, volver a AA
                return 'AA';
            } else {
                $letra1 = chr(ord($letra1) + 1);
            }
        } else {
            $letra2 = chr(ord($letra2) + 1);
        }
        
        return $letra1 . $letra2;
    }
}
