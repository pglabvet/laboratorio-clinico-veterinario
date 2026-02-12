<?php

namespace App\Livewire\Analisis;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\Analisis;
use App\Models\TipoAnalisis;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Auth;

class RevisarAnalisis extends Component
{
    use WithPagination;

    public $busqueda = '';
    public $filtroEstado = 'En revision';
    public $filtroTipoAnalisis = '';
    public $filtroFechaDesde = '';
    public $filtroFechaHasta = '';
    public $filtroPeriodo = '';
    public $ordenarPor = 'fecha_finalizacion';
    public $ordenDireccion = 'desc';
    public $filtroSucursal = '';

    // Modales de confirmación
    public $modalAprobar = false;
    public $modalRechazar = false;
    public $analisisAAprobar = null;
    public $analisisARechazar = null;
    public $observacionesRechazo = '';

    protected $queryString = [
        'busqueda' => ['except' => ''],
        'filtroEstado' => ['except' => 'En revision'],
        'filtroTipoAnalisis' => ['except' => ''],
        'ordenarPor' => ['except' => 'fecha_finalizacion'],
        'filtroSucursal' => ['except' => ''],
    ];

    public function mount()
    {
        // Por defecto mostrar análisis finalizados
    }

    public function updatingBusqueda()
    {
        $this->resetPage();
    }

    public function updatedFiltroSucursal()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->busqueda = '';
        $this->filtroEstado = 'En revision';
        $this->filtroTipoAnalisis = '';
        $this->filtroSucursal = '';
        $this->filtroFechaDesde = '';
        $this->filtroFechaHasta = '';
        $this->filtroPeriodo = '';
    }

    public function filtrarHoy()
    {
        $this->filtroFechaDesde = now()->format('Y-m-d');
        $this->filtroFechaHasta = now()->format('Y-m-d');
        $this->filtroPeriodo = 'Hoy';
    }

    public function filtrarAyer()
    {
        $this->filtroFechaDesde = now()->subDay()->format('Y-m-d');
        $this->filtroFechaHasta = now()->subDay()->format('Y-m-d');
        $this->filtroPeriodo = 'Ayer';
    }

    public function filtrarUltimos7Dias()
    {
        $this->filtroFechaDesde = now()->subDays(6)->format('Y-m-d');
        $this->filtroFechaHasta = now()->format('Y-m-d');
        $this->filtroPeriodo = 'Últimos 7 días';
    }

    public function filtrarEstaSemana()
    {
        $this->filtroFechaDesde = now()->startOfWeek()->format('Y-m-d');
        $this->filtroFechaHasta = now()->endOfWeek()->format('Y-m-d');
        $this->filtroPeriodo = 'Esta semana';
    }

    public function filtrarEsteMes()
    {
        $this->filtroFechaDesde = now()->startOfMonth()->format('Y-m-d');
        $this->filtroFechaHasta = now()->endOfMonth()->format('Y-m-d');
        $this->filtroPeriodo = 'Este mes';
    }

    public function filtrarAnioActual()
    {
        $this->filtroFechaDesde = now()->startOfYear()->format('Y-m-d');
        $this->filtroFechaHasta = now()->format('Y-m-d');
        $this->filtroPeriodo = 'Año actual';
    }

    public function updatedFiltroFechaDesde()
    {
        $this->filtroPeriodo = '';
    }

    public function updatedFiltroFechaHasta()
    {
        $this->filtroPeriodo = '';
    }

    public function ordenar($campo)
    {
        if ($this->ordenarPor === $campo) {
            $this->ordenDireccion = $this->ordenDireccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenarPor = $campo;
            $this->ordenDireccion = 'asc';
        }
    }

    public function confirmarAprobar($analisisId)
    {
        $this->analisisAAprobar = $analisisId;
        $this->modalAprobar = true;
    }

    public function cancelarAprobar()
    {
        $this->modalAprobar = false;
        $this->analisisAAprobar = null;
    }

    public function confirmarRechazar($analisisId)
    {
        $this->analisisARechazar = $analisisId;
        $this->observacionesRechazo = '';
        $this->modalRechazar = true;
    }

    public function cancelarRechazar()
    {
        $this->modalRechazar = false;
        $this->analisisARechazar = null;
        $this->observacionesRechazo = '';
    }

    public function aprobarAnalisis()
    {
        $analisis = Analisis::findOrFail($this->analisisAAprobar);
        
        // El modelo Analisis sincroniza automáticamente el estado de la muestra
        $analisis->update([
            'estado' => Analisis::ESTADO_APROBADO,
            'aprobador_id' => Auth::id(),
            'fecha_aprobacion' => now(),
        ]);

        $this->modalAprobar = false;
        $this->analisisAAprobar = null;

        session()->flash('success', 'Análisis aprobado correctamente.');
    }

    public function rechazarAnalisis()
    {
        $analisis = Analisis::findOrFail($this->analisisARechazar);
        
        // El modelo Analisis sincroniza automáticamente el estado de la muestra
        $analisis->update([
            'estado' => Analisis::ESTADO_PENDIENTE, // Volver a pendiente para que lo corrijan
            'aprobador_id' => Auth::id(),
            'observaciones_aprobador' => $this->observacionesRechazo,
        ]);

        $this->modalRechazar = false;
        $this->analisisARechazar = null;
        $this->observacionesRechazo = '';

        session()->flash('warning', 'Análisis rechazado. El bioquímico debe realizar correcciones.');
    }


    public function render()
    {
        $query = Analisis::with([
            'muestra.especie',
            'muestra.veterinaria',
            'muestra.sucursal',
            'tipoAnalisis',
            'bioquimico',
            'aprobador'
        ]);

        // Filtro por estado
        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }

        // Filtro por tipo de análisis
        if ($this->filtroTipoAnalisis) {
            $query->where('tipo_analisis_id', $this->filtroTipoAnalisis);
        }

        // Filtro por fecha
        if ($this->filtroFechaDesde) {
            $query->whereDate('fecha_finalizacion', '>=', $this->filtroFechaDesde);
        }
        if ($this->filtroFechaHasta) {
            $query->whereDate('fecha_finalizacion', '<=', $this->filtroFechaHasta);
        }

        // Filtro por sucursal
        if ($this->filtroSucursal) {
            $query->whereHas('muestra.sucursal', function ($q) {
                $q->where('id', $this->filtroSucursal);
            });
        }

        // Búsqueda
        if ($this->busqueda) {
            $query->where(function ($q) {
                $q->whereHas('muestra', function ($muestraQuery) {
                    $muestraQuery->where('codigo_muestra', 'ilike', '%' . $this->busqueda . '%')
                        ->orWhere('paciente_nombre', 'ilike', '%' . $this->busqueda . '%')
                        ->orWhere('propietario_nombre', 'ilike', '%' . $this->busqueda . '%');
                });
            });
        }

        // Ordenamiento
        $query->orderBy($this->ordenarPor, $this->ordenDireccion);

        $analisis = $query->paginate(15);

        return view('livewire.analisis.revisar-analisis', [
            'analisis' => $analisis,
        ]);
    }

    #[Computed]
    public function tiposAnalisis()
    {
        return TipoAnalisis::where('estado', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function sucursales()
    {
        return Sucursal::all();
    }
}
