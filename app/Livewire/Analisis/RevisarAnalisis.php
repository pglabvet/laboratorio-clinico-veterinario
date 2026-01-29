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
    public $filtroEstado = 'finalizado';
    public $filtroTipoAnalisis = '';
    public $filtroFechaDesde = '';
    public $filtroFechaHasta = '';
    public $ordenarPor = 'fecha_finalizacion';
    public $ordenDireccion = 'desc';

    protected $queryString = [
        'busqueda' => ['except' => ''],
        'filtroEstado' => ['except' => 'finalizado'],
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
        $this->filtroEstado = 'finalizado';
        $this->filtroTipoAnalisis = '';
        $this->filtroFechaDesde = '';
        $this->filtroFechaHasta = '';
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
            'estado' => 'aprobado',
            'aprobador_id' => Auth::id(),
            'fecha_aprobacion' => now(),
        ]);

        session()->flash('success', 'Análisis aprobado correctamente.');
    }

    public function rechazarAnalisis($analisisId, $observaciones = null)
    {
        $analisis = Analisis::findOrFail($analisisId);
        
        $analisis->update([
            'estado' => 'rechazado',
            'aprobador_id' => Auth::id(),
            'observaciones_aprobador' => $observaciones,
        ]);

        session()->flash('warning', 'Análisis rechazado. El bioquímico debe realizar correcciones.');
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
