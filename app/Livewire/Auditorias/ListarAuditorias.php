<?php

namespace App\Livewire\Auditorias;

use App\Models\Auditoria;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

/**
 * Componente Livewire para listar y filtrar auditorías del sistema.
 *
 * Permite ver todas las acciones realizadas por los usuarios:
 * - Filtrar por usuario, tipo de acción, entidad y rango de fechas
 * - Buscar por texto en la descripción
 * - Ver el detalle de cada auditoría con los valores que cambiaron
 */
class ListarAuditorias extends Component
{
    // WithPagination permite dividir los resultados en páginas
    // sin recargar toda la página (gracias a Livewire)
    use WithPagination;

    /** Texto de búsqueda libre (busca en la descripción) */
    public string $busqueda = '';

    /** Filtro por usuario específico */
    public string $filtroUsuario = '';

    /** Filtro por tipo de acción (crear, actualizar, eliminar) */
    public string $filtroAccion = '';

    /** Filtro por tipo de entidad (Muestra, Insumo, etc.) */
    public string $filtroEntidad = '';

    /** Filtro fecha desde */
    public string $fechaDesde = '';

    /** Filtro fecha hasta */
    public string $fechaHasta = '';

    /** ID de la auditoría seleccionada para ver su detalle */
    public ?int $auditoriaSeleccionada = null;

    /** Indica si el modal de detalle está abierto */
    public bool $mostrarDetalle = false;

    /**
     * Cuando cambia cualquier filtro, volver a la página 1 de resultados.
     * Si el usuario estaba en la página 5 y filtra, debe ver desde el inicio.
     */
    public function updatedBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroUsuario(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroAccion(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEntidad(): void
    {
        $this->resetPage();
    }

    public function updatedFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatedFechaHasta(): void
    {
        $this->resetPage();
    }

    /**
     * Limpiar todos los filtros de una vez.
     */
    public function limpiarFiltros(): void
    {
        $this->reset(['busqueda', 'filtroUsuario', 'filtroAccion', 'filtroEntidad', 'fechaDesde', 'fechaHasta']);
        $this->resetPage();
    }

    /**
     * Abrir el modal de detalle para una auditoría específica.
     */
    public function verDetalle(int $id): void
    {
        $this->auditoriaSeleccionada = $id;
        $this->mostrarDetalle = true;
    }

    /**
     * Cerrar el modal de detalle.
     */
    public function cerrarDetalle(): void
    {
        $this->mostrarDetalle = false;
        $this->auditoriaSeleccionada = null;
    }

    /**
     * Render: construye la consulta con todos los filtros aplicados.
     *
     * La consulta se construye dinámicamente:
     * - Si hay búsqueda → filtra por descripción (LIKE)
     * - Si hay filtro de usuario → filtra por user_id
     * - Si hay filtro de acción → filtra por accion
     * - Si hay filtro de entidad → filtra por entidad
     * - Si hay fechas → filtra por rango de created_at
     *
     * Siempre ordena por más reciente primero.
     */
    public function render()
    {
        $query = Auditoria::query()
            ->with('user'); // Eager loading del usuario para evitar N+1 queries

        // Filtro de búsqueda en descripción
        if ($this->busqueda !== '') {
            $query->where('descripcion', 'ilike', "%{$this->busqueda}%");
        }

        // Filtro por usuario
        if ($this->filtroUsuario !== '') {
            $query->where('user_id', $this->filtroUsuario);
        }

        // Filtro por acción
        if ($this->filtroAccion !== '') {
            $query->where('accion', $this->filtroAccion);
        }

        // Filtro por entidad
        if ($this->filtroEntidad !== '') {
            $query->where('entidad', $this->filtroEntidad);
        }

        // Filtro por rango de fechas
        if ($this->fechaDesde !== '') {
            $query->whereDate('created_at', '>=', $this->fechaDesde);
        }

        if ($this->fechaHasta !== '') {
            $query->whereDate('created_at', '<=', $this->fechaHasta);
        }

        // Obtener los datos paginados (20 por página)
        $auditorias = $query->orderBy('created_at', 'desc')->paginate(20);

        // Obtener detalle si hay una auditoría seleccionada
        $detalle = null;
        if ($this->auditoriaSeleccionada) {
            $detalle = Auditoria::with('user')->find($this->auditoriaSeleccionada);
        }

        return view('livewire.auditorias.listar-auditorias', [
            'auditorias' => $auditorias,
            'detalle' => $detalle,
        ]);
    }

    #[Computed]
    public function usuarios()
    {
        return User::orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function entidades()
    {
        return Auditoria::select('entidad')->distinct()->orderBy('entidad')->pluck('entidad');
    }
}
