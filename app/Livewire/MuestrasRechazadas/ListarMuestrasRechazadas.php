<?php

namespace App\Livewire\MuestrasRechazadas;

use App\Models\MuestraRechazada;
use App\Models\Sucursal;
use App\Models\Veterinaria;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ListarMuestrasRechazadas extends Component
{
    use WithPagination;

    public $buscar = '';
    public $filtroMotivo = '';
    public $filtroVeterinaria = '';
    public $filtroSucursal = '';
    public $filtroDesde = '';
    public $filtroHasta = '';

    // Modals y datos de acciones
    public $modalVer = false;
    public $muestraAVer = null;
    public $modalEliminar = false;
    public $muestraEliminarId = null;

    public $motivosPredefinidos = [
        'Muestra hemolizada',
        'Muestra coagulada',
        'Volumen insuficiente',
        'Muestra mal etiquetada',
        'Muestra deteriorada por transporte',
        'Tubo incorrecto',
    ];

    protected $queryString = [
        'buscar'         => ['except' => ''],
        'filtroMotivo'   => ['except' => ''],
    ];

    public function updatingBuscar()       { $this->resetPage(); }
    public function updatingFiltroMotivo() { $this->resetPage(); }
    public function updatingFiltroVeterinaria() { $this->resetPage(); }
    public function updatingFiltroSucursal()    { $this->resetPage(); }

    public function limpiarFiltros()
    {
        $this->reset(['buscar', 'filtroMotivo', 'filtroVeterinaria', 'filtroSucursal', 'filtroDesde', 'filtroHasta']);
        $this->resetPage();
    }

    public function crear()
    {
        return redirect()->route('muestras-rechazadas.crear');
    }

    // ─── Acciones: Ver ───
    public function ver($id)
    {
        $this->muestraAVer = MuestraRechazada::with(['especie', 'veterinaria', 'sucursal', 'registradoPor'])->findOrFail($id);
        $this->modalVer = true;
    }

    public function cerrarModalVer()
    {
        $this->modalVer = false;
        $this->muestraAVer = null;
    }

    // ─── Acciones: Eliminar ───
    public function confirmarEliminar($id)
    {
        $this->muestraEliminarId = $id;
        $this->modalEliminar = true;
    }

    public function cancelarEliminar()
    {
        $this->modalEliminar = false;
        $this->muestraEliminarId = null;
    }

    public function eliminar()
    {
        $muestra = MuestraRechazada::findOrFail($this->muestraEliminarId);
        $muestra->delete();
        $this->modalEliminar = false;
        $this->muestraEliminarId = null;
        session()->flash('success', 'Muestra rechazada eliminada exitosamente.');
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
    public function todosMotivosFiltro()
    {
        // Combina los predefinidos + cualquier motivo personalizado guardado en BD
        $predefinidos = collect($this->motivosPredefinidos);
        $enBd = MuestraRechazada::select('motivo_rechazo')
            ->distinct()
            ->pluck('motivo_rechazo');

        return $predefinidos->merge($enBd)->unique()->sort()->values();
    }

    public function render()
    {
        $query = MuestraRechazada::with(['especie', 'veterinaria', 'sucursal', 'registradoPor'])
            ->orderBy('fecha_rechazo', 'desc');

        if ($this->buscar) {
            $buscar = "%{$this->buscar}%";
            $query->where(function ($q) use ($buscar) {
                $q->whereRaw('unaccent(codigo_muestra) ilike unaccent(?)', [$buscar])
                    ->orWhereRaw('unaccent(paciente_nombre) ilike unaccent(?)', [$buscar])
                    ->orWhereRaw('unaccent(propietario_nombre) ilike unaccent(?)', [$buscar]);
            });
        }

        if ($this->filtroMotivo) {
            $query->where('motivo_rechazo', $this->filtroMotivo);
        }

        if ($this->filtroVeterinaria) {
            $query->where('veterinaria_id', $this->filtroVeterinaria);
        }

        if ($this->filtroSucursal) {
            $query->where('sucursal_id', $this->filtroSucursal);
        }

        if ($this->filtroDesde) {
            $query->whereDate('fecha_rechazo', '>=', $this->filtroDesde);
        }

        if ($this->filtroHasta) {
            $query->whereDate('fecha_rechazo', '<=', $this->filtroHasta);
        }

        return view('livewire.muestras-rechazadas.listar-muestras-rechazadas', [
            'muestras' => $query->paginate(15),
        ])->layout('components.layouts.app');
    }
}
