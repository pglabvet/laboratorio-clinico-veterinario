<?php

namespace App\Livewire\Muestras;

use App\Models\Analisis;
use App\Models\Especie;
use App\Models\MovimientoInventario;
use App\Models\Muestra;
use App\Models\Sucursal;
use App\Models\TipoAnalisis;
use App\Models\Veterinaria;
use App\Services\EnvioResultadosService;
use App\Services\MuestraService;
use App\Services\PepsInventarioService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
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

    // Propiedades del formulario - Veterinaria y Análisis
    public $veterinaria_id;

    public $tipos_analisis_seleccionados = [];

    // Propiedades de control
    public $modalAbierto = false;

    public $modalEliminar = false;

    public $modalVer = false;

    public $modalCodigoBarras = false;

    public $modalAnalisis = false;

    public $muestraAEliminar = null;

    public $muestraAVer = null;

    public $muestraCodigoBarras = null;

    public $muestraAnalisis = null;

    public $telefonoWhatsappSeleccionado = '';

    public $telefonosWhatsappDisponibles = [];

    public $buscar = '';

    public $modoEdicion = false;

    // Propiedades de filtros
    public $filtroEstado = '';

    public $filtroEspecie = '';

    public $filtroVeterinaria = '';

    public $filtroSucursal = '';

    public $filtroFechaDesde = '';

    public $filtroFechaHasta = '';

    public $filtroPeriodo = '';

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

        'tipos_analisis_seleccionados.required' => 'Debe seleccionar al menos un tipo de análisis.',
        'tipos_analisis_seleccionados.min' => 'Debe seleccionar al menos un tipo de análisis.',
    ];

    /**
     * Inicializar componente
     */
    public function mount()
    {

        $this->sucursal_id = auth()->user()->sucursal_id ?? Sucursal::first()?->id;

        // Si hay una muestra recién creada, abrir su modal automáticamente
        if (session()->has('muestra_recien_creada_id')) {
            $muestraId = session()->pull('muestra_recien_creada_id');
            $this->verCodigoBarras($muestraId);
        }
    }

    /**
     * Abrir modal para crear nueva muestra
     */
    public function crear()
    {
        return redirect()->route('muestras.crear');
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
            'analisis.tipoAnalisis',
            'analisis.plantillaFormulario',
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
            'sucursal',
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
     * Abrir modal de análisis de la muestra
     */
    public function verAnalisis($id)
    {
        $this->muestraAnalisis = Muestra::with([
            'especie',
            'veterinaria.telefonos',
            'analisis.tipoAnalisis',
            'analisis.resultados',
        ])->findOrFail($id);

        $this->cargarTelefonosWhatsappDisponibles();
        $this->modalAnalisis = true;
    }

    /**
     * Cerrar modal de análisis
     */
    public function cerrarModalAnalisis()
    {
        $this->modalAnalisis = false;
        $this->muestraAnalisis = null;
        $this->telefonoWhatsappSeleccionado = '';
        $this->telefonosWhatsappDisponibles = [];
    }

    /**
     * Enviar resultado de análisis por WhatsApp
     */
    public function enviarWhatsApp($analisisId)
    {
        try {
            $service = app(EnvioResultadosService::class);
            $resultado = $service->prepararWhatsApp($analisisId, $this->telefonoWhatsappSeleccionado ?: null);

            $this->dispatch('abrir-whatsapp', url: $resultado['url']);

            if ($this->muestraAnalisis) {
                $this->muestraAnalisis->load('analisis.tipoAnalisis', 'veterinaria.telefonos');
                $this->cargarTelefonosWhatsappDisponibles();
            }

            session()->flash('mensaje', $resultado['mensaje']);
        } catch (\Exception $e) {
            \Log::error('Error al generar enlace de WhatsApp para análisis '.$analisisId.': '.$e->getMessage(), [
                'exception' => $e,
            ]);
            session()->flash('error', 'No se pudo generar el enlace de WhatsApp. El análisis no ha sido marcado como enviado. Detalle técnico: '.$e->getMessage());
        }
    }

    /**
     * Enviar todos los análisis de la muestra por WhatsApp
     */
    public function enviarTodoWhatsApp()
    {
        if (! $this->muestraAnalisis) {
            session()->flash('error', 'No hay muestra seleccionada.');

            return;
        }

        try {
            $service = app(EnvioResultadosService::class);
            $resultado = $service->prepararWhatsAppMasivo($this->muestraAnalisis, $this->telefonoWhatsappSeleccionado ?: null);

            $this->muestraAnalisis->load('analisis.tipoAnalisis', 'veterinaria.telefonos');
            $this->cargarTelefonosWhatsappDisponibles();
            $this->dispatch('abrir-whatsapp', url: $resultado['url']);

            session()->flash('mensaje', $resultado['mensaje']);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al generar enlaces: '.$e->getMessage());
        }
    }

    /**
     * Enviar resultado de un análisis individual por Email
     */
    public function enviarEmail($analisisId)
    {
        try {
            $service = app(EnvioResultadosService::class);
            $mensaje = $service->enviarEmail($analisisId);

            if ($this->muestraAnalisis) {
                $this->muestraAnalisis->load('analisis.tipoAnalisis');
            }

            session()->flash('mensaje', $mensaje);
        } catch (\Exception $e) {
            \Log::error('Error al enviar email para análisis '.$analisisId.': '.$e->getMessage(), [
                'exception' => $e,
            ]);
            session()->flash('error', 'No se pudo enviar el correo electrónico. Detalle: '.$e->getMessage());
        }
    }

    /**
     * Enviar todos los análisis de la muestra por Email
     */
    public function enviarTodoEmail()
    {
        if (! $this->muestraAnalisis) {
            session()->flash('error', 'No hay muestra seleccionada.');

            return;
        }

        try {
            $service = app(EnvioResultadosService::class);
            $mensaje = $service->enviarEmailMasivo($this->muestraAnalisis);

            $this->muestraAnalisis->load('analisis.tipoAnalisis');

            session()->flash('mensaje', $mensaje);
        } catch (\Exception $e) {
            \Log::error('Error al enviar todos los análisis por email: '.$e->getMessage(), [
                'exception' => $e,
            ]);
            session()->flash('error', 'Error al enviar los resultados por correo: '.$e->getMessage());
        }
    }

    /**
     * Carga los teléfonos de WhatsApp disponibles y preselecciona el principal.
     */
    private function cargarTelefonosWhatsappDisponibles(): void
    {
        $telefonos = collect($this->muestraAnalisis?->veterinaria?->telefonos ?? [])
            ->sortByDesc(fn ($telefono) => (int) $telefono->es_principal)
            ->values();

        $this->telefonosWhatsappDisponibles = $telefonos
            ->map(fn ($telefono) => [
                'telefono' => $telefono->telefono,
                'nombre_contacto' => $telefono->nombre_contacto,
                'es_principal' => (bool) $telefono->es_principal,
            ])
            ->all();

        $telefonoPrincipal = $telefonos->firstWhere('es_principal', true) ?? $telefonos->first();

        $this->telefonoWhatsappSeleccionado = $telefonoPrincipal?->telefono ?? '';
    }

    /**
     * Guardar muestra (crear o actualizar)
     */
    public function guardar()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // UC-B05: Validar stock antes de crear análisis
            if (! $this->modoEdicion) {
                $muestraService = app(MuestraService::class);
                $resultado = $muestraService->validarStockPorTiposAnalisis($this->tipos_analisis_seleccionados, $this->sucursal_id);
                if (! empty($resultado['warnings'])) {
                    session()->flash('warning', '⚠️ ADVERTENCIA: Los siguientes insumos tienen stock bajo: '.implode(', ', $resultado['warnings']).'. Se recomienda reabastecer pronto.');
                }
            }

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
                    'observaciones' => $this->observaciones,
                ]);

                session()->flash('mensaje', 'Muestra actualizada exitosamente.');
            } else {
                // Generar código único de muestra
                $muestraService = $muestraService ?? app(MuestraService::class);
                $this->codigo_muestra = $muestraService->generarCodigoMuestra($this->sucursal_id);

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
                    'fecha_recepcion' => now(),
                    'estado' => 'Pendiente',
                    'observaciones' => $this->observaciones,
                ]);

                // Crear registros de análisis para cada tipo seleccionado
                foreach ($this->tipos_analisis_seleccionados as $tipo_analisis_id) {
                    Analisis::create([
                        'muestra_id' => $muestra->id,
                        'tipo_analisis_id' => $tipo_analisis_id,
                        'bioquimico_id' => auth()->id(),
                        'estado' => 'Pendiente',
                        'fecha_inicio' => now(),
                    ]);
                }

                // Mostrar modal de código de barras
                $this->muestraCodigoBarras = $muestra->load(['especie', 'veterinaria', 'sucursal']);
                $this->modalCodigoBarras = true;

                session()->flash('mensaje', 'Muestra registrada exitosamente.');
            }

            DB::commit();
            $this->cerrarModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al guardar la muestra: '.$e->getMessage());
        }
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
            if (! $this->muestraAEliminar) {
                return;
            }

            DB::beginTransaction();

            $muestra = Muestra::findOrFail($this->muestraAEliminar);

            // Verificar si tiene análisis en proceso o completados
            if ($muestra->analisis()->whereIn('estado', [Analisis::ESTADO_EN_REVISION, Analisis::ESTADO_APROBADO, Analisis::ESTADO_ENVIADO])->count() > 0) {
                session()->flash('error', 'No se puede eliminar la muestra porque tiene análisis en revisión, aprobados o enviados.');
                $this->modalEliminar = false;
                $this->muestraAEliminar = null;
                DB::rollBack();

                return;
            }

            // Revertir insumos consumidos por esta muestra
            $pepsService = app(PepsInventarioService::class);
            $movimientosConsumo = MovimientoInventario::where('tipo_movimiento', 'CONSUMO_ANALISIS')
                ->where('sucursal_id', $muestra->sucursal_id)
                ->where('observacion', 'like', "%Muestra: {$muestra->codigo_muestra}%")
                ->get();

            foreach ($movimientosConsumo as $movimiento) {
                $pepsService->revertirConsumoAnalisis(
                    insumoId: $movimiento->insumo_id,
                    sucursalId: $movimiento->sucursal_id,
                    cantidad: abs($movimiento->cantidad),
                    costoUnitario: $movimiento->costo_unitario,
                    usuarioId: auth()->id(),
                    observacion: "Devolución automática - Eliminación de muestra: {$muestra->codigo_muestra}"
                );
            }

            // Eliminar análisis pendientes
            $muestra->analisis()->where('estado', 'Pendiente')->delete();

            $muestra->delete();

            DB::commit();

            $insumosRevertidos = $movimientosConsumo->count();
            $mensaje = 'Muestra eliminada exitosamente.';
            if ($insumosRevertidos > 0) {
                $mensaje .= " Se revirtieron {$insumosRevertidos} consumo(s) de insumos al inventario.";
            }
            session()->flash('mensaje', $mensaje);

            $this->modalEliminar = false;
            $this->muestraAEliminar = null;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al eliminar la muestra: '.$e->getMessage());
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
        $this->sucursal_id = auth()->user()->sucursal_id ?? Sucursal::first()?->id;
        $this->estado = 'Pendiente';
    }

    /**
     * Resetear búsqueda
     */
    public function updatingBuscar()
    {
        $this->resetPage();
    }

    /**
     * Limpiar filtro de período cuando se cambian las fechas manualmente
     */
    public function updatedFiltroFechaDesde()
    {
        $this->filtroPeriodo = '';
    }

    public function updatedFiltroFechaHasta()
    {
        $this->filtroPeriodo = '';
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
     * Limpiar todos los filtros
     */
    public function limpiarFiltros()
    {
        $this->buscar = '';
        $this->filtroEstado = '';
        $this->filtroEspecie = '';
        $this->filtroVeterinaria = '';
        $this->filtroSucursal = '';
        $this->filtroFechaDesde = '';
        $this->filtroFechaHasta = '';
        $this->filtroPeriodo = '';
        $this->resetPage();
    }

    /**
     * Filtrar por hoy
     */
    public function filtrarHoy()
    {
        $this->filtroFechaDesde = now()->format('Y-m-d');
        $this->filtroFechaHasta = now()->format('Y-m-d');
        $this->filtroPeriodo = 'Hoy';
    }

    /**
     * Filtrar por ayer
     */
    public function filtrarAyer()
    {
        $this->filtroFechaDesde = now()->subDay()->format('Y-m-d');
        $this->filtroFechaHasta = now()->subDay()->format('Y-m-d');
        $this->filtroPeriodo = 'Ayer';
    }

    /**
     * Filtrar últimos 7 días
     */
    public function filtrarUltimos7Dias()
    {
        $this->filtroFechaDesde = now()->subDays(6)->format('Y-m-d');
        $this->filtroFechaHasta = now()->format('Y-m-d');
        $this->filtroPeriodo = 'Últimos 7 días';
    }

    /**
     * Filtrar esta semana
     */
    public function filtrarEstaSemana()
    {
        $this->filtroFechaDesde = now()->startOfWeek()->format('Y-m-d');
        $this->filtroFechaHasta = now()->endOfWeek()->format('Y-m-d');
        $this->filtroPeriodo = 'Esta semana';
    }

    /**
     * Filtrar este mes
     */
    public function filtrarEsteMes()
    {
        $this->filtroFechaDesde = now()->startOfMonth()->format('Y-m-d');
        $this->filtroFechaHasta = now()->endOfMonth()->format('Y-m-d');
        $this->filtroPeriodo = 'Este mes';
    }

    /**
     * Filtrar año actual
     */
    public function filtrarAnioActual()
    {
        $this->filtroFechaDesde = now()->startOfYear()->format('Y-m-d');
        $this->filtroFechaHasta = now()->format('Y-m-d');
        $this->filtroPeriodo = 'Año actual';
    }

    /**
     * Renderizar componente
     */
    public function render()
    {
        $muestras = Muestra::query()
            ->with(['especie', 'veterinaria', 'sucursal'])
            ->withCount('analisis')
            // Filtrar por sucursal del usuario si no tiene vista general
            ->when(! auth()->user()->can('vista-general-sistema'), function ($query) {
                $query->where('sucursal_id', auth()->user()->sucursal_id);
            })
            ->when($this->buscar, function ($query) {
                $searchTerm = '%'.$this->buscar.'%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('muestras.codigo_muestra', 'ilike', $searchTerm)
                        ->orWhere('muestras.paciente_nombre', 'ilike', $searchTerm)
                        ->orWhere('muestras.propietario_nombre', 'ilike', $searchTerm)
                        ->orWhereHas('veterinaria', function ($subQuery) use ($searchTerm) {
                            $subQuery->where('nombre', 'ilike', $searchTerm);
                        });
                });
            })
            ->when($this->filtroEstado, function ($query) {
                $query->where('estado', $this->filtroEstado);
            })
            ->when($this->filtroEspecie, function ($query) {
                $query->where('especie_id', $this->filtroEspecie);
            })
            ->when($this->filtroVeterinaria, function ($query) {
                $query->where('veterinaria_id', $this->filtroVeterinaria);
            })
            ->when($this->filtroSucursal, function ($query) {
                $query->where('sucursal_id', $this->filtroSucursal);
            })
            ->when($this->filtroFechaDesde, function ($query) {
                $query->whereDate('fecha_recepcion', '>=', $this->filtroFechaDesde);
            })
            ->when($this->filtroFechaHasta, function ($query) {
                $query->whereDate('fecha_recepcion', '<=', $this->filtroFechaHasta);
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.muestras.gestionar-muestras', [
            'muestras' => $muestras,
        ]);
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
        return TipoAnalisis::where('estado', true)->orderBy('nombre')->get();
    }
}
