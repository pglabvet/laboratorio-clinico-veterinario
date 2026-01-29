<?php

namespace App\Livewire\Muestras;

use App\Models\Muestra;
use App\Models\Especie;
use App\Models\Veterinaria;
use App\Models\Sucursal;
use App\Models\TipoAnalisis;
use App\Models\Analisis;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarMuestras extends Component
{
    use WithPagination;

    // Propiedades del formulario - Muestra
    public $muestra_id;
    public $codigo_muestra;
    public $tipo_muestra;
    public $fecha_recepcion;
    public $estado = 'pendiente';
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

    // Propiedades del formulario - Veterinaria y Análisis
    public $veterinaria_id;
    public $tipos_analisis_seleccionados = [];

    // Propiedades de control
    public $modalAbierto = false;
    public $modalEliminar = false;
    public $modalVer = false;
    public $modalCodigoBarras = false;
    public $muestraAEliminar = null;
    public $muestraAVer = null;
    public $muestraCodigoBarras = null;
    public $buscar = '';
    public $modoEdicion = false;

    // Propiedades de ordenamiento
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

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
            'tipos_analisis_seleccionados' => 'required|array|min:1',
            'tipos_analisis_seleccionados.*' => 'exists:tipos_analisis,id',
        ];
    }

    // Mensajes de validación personalizados
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
        'tipos_analisis_seleccionados.required' => 'Debe seleccionar al menos un tipo de análisis.',
        'tipos_analisis_seleccionados.min' => 'Debe seleccionar al menos un tipo de análisis.',
    ];

    /**
     * Inicializar componente
     */
    public function mount()
    {
        $this->fecha_recepcion = now()->format('Y-m-d');
        $this->sucursal_id = auth()->user()->sucursal_id ?? Sucursal::first()?->id;
    }

    /**
     * Abrir modal para crear nueva muestra
     */
    public function crear()
    {
        $this->resetearFormulario();
        $this->modoEdicion = false;
        $this->modalAbierto = true;
    }

    /**
     * Abrir modal para ver detalles de muestra
     */
    public function ver($id)
    {
        $this->muestraAVer = Muestra::with([
            'especie',
            'veterinaria',
            'sucursal',
            'analisis.tipoAnalisis'
        ])->findOrFail($id);
        $this->modalVer = true;
    }

    /**
     * Cerrar modal de ver
     */
    public function cerrarModalVer()
    {
        $this->modalVer = false;
        $this->muestraAVer = null;
    }

    /**
     * Abrir modal de código de barras
     */
    public function verCodigoBarras($id)
    {
        $this->muestraCodigoBarras = Muestra::with([
            'especie',
            'veterinaria',
            'sucursal'
        ])->findOrFail($id);
        $this->modalCodigoBarras = true;
    }

    /**
     * Cerrar modal de código de barras
     */
    public function cerrarModalCodigoBarras()
    {
        $this->modalCodigoBarras = false;
        $this->muestraCodigoBarras = null;
    }

    /**
     * Guardar muestra (crear o actualizar)
     */
    public function guardar()
    {
        $this->validate();

        try {
            if ($this->modoEdicion) {
                $muestra = Muestra::findOrFail($this->muestra_id);
                $muestra->update([
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
                    'observaciones' => $this->observaciones,
                ]);

                session()->flash('mensaje', 'Muestra actualizada exitosamente.');
            } else {
                // Generar código único de muestra
                $this->codigo_muestra = $this->generarCodigoMuestra();
                
                $muestra = Muestra::create([
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
                    'estado' => 'pendiente',
                    'observaciones' => $this->observaciones,
                ]);

                // Crear registros de análisis para cada tipo seleccionado
                foreach ($this->tipos_analisis_seleccionados as $tipo_analisis_id) {
                    Analisis::create([
                        'muestra_id' => $muestra->id,
                        'tipo_analisis_id' => $tipo_analisis_id,
                        'bioquimico_id' => auth()->id(),
                        'estado' => 'pendiente',
                        'fecha_inicio' => now(),
                    ]);
                }

                // Mostrar modal de código de barras
                $this->muestraCodigoBarras = $muestra->load(['especie', 'veterinaria', 'sucursal']);
                $this->modalCodigoBarras = true;

                session()->flash('mensaje', 'Muestra registrada exitosamente.');
            }

            $this->cerrarModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar la muestra: ' . $e->getMessage());
        }
    }

    /**
     * Generar código único para la muestra
     * Formato: AA0000 (2 letras + 4 dígitos)
     * Rango: AA0000 - ZZ9999 (676 * 10,000 = 6,760,000 combinaciones)
     */
    private function generarCodigoMuestra()
    {
        // Obtener el último código de muestra
        $ultimaMuestra = Muestra::orderBy('id', 'desc')->first();
        
        if (!$ultimaMuestra) {
            // Primera muestra
            return 'AA0001';
        }
        
        // Extraer las partes del último código
        $ultimoCodigo = $ultimaMuestra->codigo_muestra;
        
        // Si no sigue el formato AA0000, empezar desde AA0001
        if (!preg_match('/^([A-Z]{2})(\d{4})$/', $ultimoCodigo, $matches)) {
            return 'AA0001';
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
        
        return $letras . str_pad($numero, 4, '0', STR_PAD_LEFT);
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

    /**
     * Abrir modal de confirmación para eliminar
     */
    public function confirmarEliminar($id)
    {
        $this->muestraAEliminar = $id;
        $this->modalEliminar = true;
    }

    /**
     * Cancelar eliminación
     */
    public function cancelarEliminar()
    {
        $this->modalEliminar = false;
        $this->muestraAEliminar = null;
    }

    /**
     * Eliminar muestra
     */
    public function eliminar()
    {
        try {
            if (!$this->muestraAEliminar) {
                return;
            }

            $muestra = Muestra::findOrFail($this->muestraAEliminar);
            
            // Verificar si tiene análisis en proceso
            if ($muestra->analisis()->whereIn('estado', ['EN_PROCESO', 'COMPLETADO'])->count() > 0) {
                session()->flash('error', 'No se puede eliminar la muestra porque tiene análisis en proceso o completados.');
                $this->modalEliminar = false;
                $this->muestraAEliminar = null;
                return;
            }

            // Eliminar análisis pendientes
            $muestra->analisis()->where('estado', 'pendiente')->delete();
            
            $muestra->delete();
            session()->flash('mensaje', 'Muestra eliminada exitosamente.');
            
            $this->modalEliminar = false;
            $this->muestraAEliminar = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar la muestra: ' . $e->getMessage());
            $this->modalEliminar = false;
            $this->muestraAEliminar = null;
        }
    }

    /**
     * Cerrar modal
     */
    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->resetearFormulario();
        $this->resetValidation();
    }

    /**
     * Resetear formulario
     */
    private function resetearFormulario()
    {
        $this->muestra_id = null;
        $this->codigo_muestra = '';
        $this->paciente_nombre = '';
        $this->especie_id = null;
        $this->raza = '';
        $this->edad = '';
        $this->sexo = 'M';
        $this->color = '';
        $this->propietario_nombre = '';
        $this->veterinaria_id = null;
        $this->tipo_muestra = '';
        $this->observaciones = '';
        $this->tipos_analisis_seleccionados = [];
        $this->fecha_recepcion = now()->format('Y-m-d');
        $this->sucursal_id = auth()->user()->sucursal_id ?? Sucursal::first()?->id;
        $this->estado = 'pendiente';
    }

    /**
     * Resetear búsqueda
     */
    public function updatingBuscar()
    {
        $this->resetPage();
    }

    /**
     * Cambiar ordenamiento
     */
    public function ordenarPor($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Renderizar componente
     */
    public function render()
    {
        $muestras = Muestra::query()
            ->with(['especie', 'veterinaria', 'sucursal'])
            ->withCount('analisis')
            ->when($this->buscar, function ($query) {
                $searchTerm = '%' . $this->buscar . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('muestras.codigo_muestra', 'like', $searchTerm)
                        ->orWhere('muestras.paciente_nombre', 'like', $searchTerm)
                        ->orWhere('muestras.propietario_nombre', 'like', $searchTerm)
                        ->orWhereHas('veterinaria', function ($subQuery) use ($searchTerm) {
                            $subQuery->where('nombre', 'like', $searchTerm);
                        });
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        $especies = Especie::where('estado', true)->orderBy('nombre')->get();
        $veterinarias = Veterinaria::where('estado', true)->orderBy('nombre')->get();
        $sucursales = Sucursal::where('estado', true)->orderBy('nombre')->get();
        $tiposAnalisis = TipoAnalisis::where('estado', true)->orderBy('nombre')->get();

        return view('livewire.muestras.gestionar-muestras', [
            'muestras' => $muestras,
            'especies' => $especies,
            'veterinarias' => $veterinarias,
            'sucursales' => $sucursales,
            'tiposAnalisis' => $tiposAnalisis,
        ]);
    }
}
