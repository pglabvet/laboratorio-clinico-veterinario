<?php

namespace App\Livewire\Dashboard;

use App\Models\Sucursal;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FiltrosDashboard extends Component
{
    public $sucursalId = null;
    public $fechaInicio = null;
    public $fechaFin = null;
    public $periodoSeleccionado = 'Período';
    public $sucursalSeleccionada = 'Todas';

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

    public function updatedSucursalId()
    {
        $this->emitirFiltros();
    }

    /**
     * Filtrar por hoy
     */
    public function filtrarHoy()
    {
        $this->fechaInicio = now()->format('Y-m-d');
        $this->fechaFin = now()->format('Y-m-d');
        $this->periodoSeleccionado = 'Hoy';
        $this->emitirFiltros();
    }

    /**
     * Filtrar por ayer
     */
    public function filtrarAyer()
    {
        $this->fechaInicio = now()->subDay()->format('Y-m-d');
        $this->fechaFin = now()->subDay()->format('Y-m-d');
        $this->periodoSeleccionado = 'Ayer';
        $this->emitirFiltros();
    }

    /**
     * Filtrar últimos 7 días
     */
    public function filtrarUltimos7Dias()
    {
        $this->fechaInicio = now()->subDays(6)->format('Y-m-d');
        $this->fechaFin = now()->format('Y-m-d');
        $this->periodoSeleccionado = 'Últimos 7 días';
        $this->emitirFiltros();
    }

    /**
     * Filtrar esta semana
     */
    public function filtrarEstaSemana()
    {
        $this->fechaInicio = now()->startOfWeek()->format('Y-m-d');
        $this->fechaFin = now()->endOfWeek()->format('Y-m-d');
        $this->periodoSeleccionado = 'Esta semana';
        $this->emitirFiltros();
    }

    /**
     * Filtrar este mes
     */
    public function filtrarEsteMes()
    {
        $this->fechaInicio = now()->startOfMonth()->format('Y-m-d');
        $this->fechaFin = now()->endOfMonth()->format('Y-m-d');
        $this->periodoSeleccionado = 'Este mes';
        $this->emitirFiltros();
    }

    /**
     * Filtrar año actual
     */
    public function filtrarAnioActual()
    {
        $this->fechaInicio = now()->startOfYear()->format('Y-m-d');
        $this->fechaFin = now()->format('Y-m-d');
        $this->periodoSeleccionado = 'Año actual';
        $this->emitirFiltros();
    }

    /**
     * Seleccionar sucursal
     */
    public function seleccionarSucursal($sucursalId, $sucursalNombre)
    {
        $this->sucursalId = $sucursalId;
        $this->sucursalSeleccionada = $sucursalNombre ?: 'Todas';
        $this->emitirFiltros();
    }

    private function emitirFiltros()
    {
        $this->dispatch('filtrosActualizados', [
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'sucursalId' => $this->sucursalId,
        ]);
    }

    public function limpiarFiltros()
    {
        $this->fechaInicio = null;
        $this->fechaFin = null;
        $this->sucursalId = null;
        $this->periodoSeleccionado = 'Período';
        $this->sucursalSeleccionada = 'Todas';
        $this->emitirFiltros();
    }
}
