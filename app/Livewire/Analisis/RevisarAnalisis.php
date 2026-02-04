<?php

namespace App\Livewire\Analisis;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Analisis;
use App\Models\TipoAnalisis;
use Illuminate\Support\Facades\Auth;

class RevisarAnalisis extends Component
{
    use WithPagination;

    public $busqueda = '';
    public $filtroEstado = 'En revision';
    public $filtroTipoAnalisis = '';
    public $filtroFechaDesde = '';
    public $filtroFechaHasta = '';
    public $ordenarPor = 'fecha_finalizacion';
    public $ordenDireccion = 'desc';

    protected $queryString = [
        'busqueda' => ['except' => ''],
        'filtroEstado' => ['except' => 'En revision'],
        'filtroTipoAnalisis' => ['except' => ''],
        'ordenarPor' => ['except' => 'fecha_finalizacion'],
    ];

    public function mount()
    {
        // Por defecto mostrar análisis finalizados
    }

    public function updatingBusqueda()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->busqueda = '';
        $this->filtroEstado = 'En revision';
        $this->filtroTipoAnalisis = '';
        $this->filtroFechaDesde = '';
        $this->filtroFechaHasta = '';
    }

    public function filtrarHoy()
    {
        $this->filtroFechaDesde = now()->format('Y-m-d');
        $this->filtroFechaHasta = now()->format('Y-m-d');
    }

    public function filtrarAyer()
    {
        $this->filtroFechaDesde = now()->subDay()->format('Y-m-d');
        $this->filtroFechaHasta = now()->subDay()->format('Y-m-d');
    }

    public function filtrarUltimos7Dias()
    {
        $this->filtroFechaDesde = now()->subDays(6)->format('Y-m-d');
        $this->filtroFechaHasta = now()->format('Y-m-d');
    }

    public function filtrarEstaSemana()
    {
        $this->filtroFechaDesde = now()->startOfWeek()->format('Y-m-d');
        $this->filtroFechaHasta = now()->endOfWeek()->format('Y-m-d');
    }

    public function filtrarEsteMes()
    {
        $this->filtroFechaDesde = now()->startOfMonth()->format('Y-m-d');
        $this->filtroFechaHasta = now()->endOfMonth()->format('Y-m-d');
    }

    public function filtrarAnioActual()
    {
        $this->filtroFechaDesde = now()->startOfYear()->format('Y-m-d');
        $this->filtroFechaHasta = now()->format('Y-m-d');
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

    public function aprobarAnalisis($analisisId)
    {
        $analisis = Analisis::findOrFail($analisisId);
        
        $analisis->update([
            'estado' => Analisis::ESTADO_APROBADO,
            'aprobador_id' => Auth::id(),
            'fecha_aprobacion' => now(),
        ]);

        // Verificar si todos los análisis de la muestra están completados
        $this->verificarMuestraCompletada($analisis);

        session()->flash('success', 'Análisis aprobado correctamente.');
    }

    public function rechazarAnalisis($analisisId, $observaciones = null)
    {
        $analisis = Analisis::findOrFail($analisisId);
        
        $analisis->update([
            'estado' => Analisis::ESTADO_PENDIENTE, // Volver a pendiente para que lo corrijan
            'aprobador_id' => Auth::id(),
            'observaciones_aprobador' => $observaciones,
        ]);

        // Si la muestra estaba en Completado, volver a En proceso
        $this->revertirMuestraEnProceso($analisis);

        session()->flash('warning', 'Análisis rechazado. El bioquímico debe realizar correcciones.');
    }

    /**
     * Verificar si todos los análisis de la muestra están completados
     */
    private function verificarMuestraCompletada($analisis)
    {
        $muestra = $analisis->muestra->fresh();
        $analisisRestantes = $muestra->analisis()
            ->whereNotIn('estado', ['En revision', 'Aprobado', 'Enviado'])
            ->count();
        
        // Si no quedan análisis pendientes, marcar muestra como completada
        if ($analisisRestantes === 0) {
            $muestra->update([
                'estado' => 'Completado'
            ]);
        }
    }

    /**
     * Si se rechaza un análisis y la muestra estaba Completada, volver a En proceso
     */
    private function revertirMuestraEnProceso($analisis)
    {
        $muestra = $analisis->muestra->fresh();
        
        if ($muestra->estado === 'Completado') {
            $muestra->update([
                'estado' => 'En proceso'
            ]);
        }
    }

    public function render()
    {
        $query = Analisis::with([
            'muestra.especie',
            'muestra.veterinaria',
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
        
        $tiposAnalisis = TipoAnalisis::where('estado', true)
            ->orderBy('nombre')
            ->get();

        return view('livewire.analisis.revisar-analisis', [
            'analisis' => $analisis,
            'tiposAnalisis' => $tiposAnalisis,
        ])->layout('components.layouts.app');
    }
}
