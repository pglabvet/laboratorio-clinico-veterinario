<?php

namespace App\Livewire\Dashboard;

use App\Models\Sucursal;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FiltrosDashboard extends Component
{
    public $rangoFecha = 'todo'; // hoy, semana, mes, todo
    public $sucursalId = null;
    public $fechaInicio = null;
    public $fechaFin = null;

    public function mount()
    {
        $this->aplicarRangoFecha();
    }

    public function render()
    {
        /** @var User $user */
        $user = Auth::user();
        $sucursales = [];
        
        if ($user->can('filtrar-por-sucursal')) {
            $sucursales = Sucursal::where('estado', true)->get();
        }

        return view('livewire.dashboard.filtros-dashboard', [
            'sucursales' => $sucursales,
        ]);
    }

    public function updatedRangoFecha()
    {
        $this->aplicarRangoFecha();
        $this->emitirFiltros();
    }

    public function updatedSucursalId()
    {
        $this->emitirFiltros();
    }

    private function aplicarRangoFecha()
    {
        $this->fechaInicio = match($this->rangoFecha) {
            'hoy' => Carbon::today(),
            'semana' => Carbon::now()->startOfWeek(),
            'mes' => Carbon::now()->startOfMonth(),
            'todo' => null,
            default => null,
        };

        $this->fechaFin = match($this->rangoFecha) {
            'hoy' => Carbon::today()->endOfDay(),
            'semana' => Carbon::now()->endOfWeek(),
            'mes' => Carbon::now()->endOfMonth(),
            'todo' => null,
            default => null,
        };
    }

    private function emitirFiltros()
    {
        $this->dispatch('filtrosActualizados', [
            'fechaInicio' => $this->fechaInicio?->format('Y-m-d'),
            'fechaFin' => $this->fechaFin?->format('Y-m-d'),
            'sucursalId' => $this->sucursalId,
        ]);
    }

    public function limpiarFiltros()
    {
        $this->rangoFecha = 'todo';
        $this->sucursalId = null;
        $this->aplicarRangoFecha();
        $this->emitirFiltros();
    }
}
