<?php

namespace App\Livewire\Inventario;

use App\Models\Insumo;
use App\Models\CategoriaInsumo;
use App\Models\Sucursal;
use App\Services\PepsInventarioService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\Attributes\Computed;

class KardexPeps extends Component
{
    public $sucursal_id = '';
    public $insumo_id = '';
    public $filtro_categoria = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';

    // Solo totales livianos
    public $saldoFinalCantidad = 0;
    public $saldoFinalCosto = 0;
    public $totalMovimientos = 0;
    public $hayDatos = false;

    // Paginación manual
    public $paginaActual = 1;
    public $porPagina = 20;

    // Clave de cache para identificar el dataset actual
    public $cacheKey = '';

    #[Computed]
    public function sucursales()
    {
        return Sucursal::where('estado', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function categorias()
    {
        return CategoriaInsumo::where('estado', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function insumos()
    {
        $query = Insumo::where('estado', true);

        if ($this->filtro_categoria) {
            $query->where('categoria_id', $this->filtro_categoria);
        }

        return $query->orderBy('nombre')->get();
    }

    public function updatedSucursalId()
    {
        $this->paginaActual = 1;
        $this->generarYCachearKardex();
    }

    public function updatedFiltroCategoria()
    {
        $this->insumo_id = '';
        $this->paginaActual = 1;
        $this->generarYCachearKardex();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['insumo_id', 'fecha_desde', 'fecha_hasta'])) {
            $this->paginaActual = 1;
            $this->generarYCachearKardex();
        }
    }

    /**
     * Generar TODOS los registros del kardex UNA SOLA VEZ y guardarlos en cache.
     * Las paginaciones posteriores leen directamente del cache sin regenerar.
     */
    private function generarYCachearKardex()
    {
        // Limpiar cache anterior
        if ($this->cacheKey) {
            Cache::forget($this->cacheKey);
        }

        $this->saldoFinalCantidad = 0;
        $this->saldoFinalCosto = 0;
        $this->totalMovimientos = 0;
        $this->hayDatos = false;
        $this->cacheKey = '';

        if (!$this->sucursal_id) {
            return;
        }

        try {
            $insumoIds = $this->getInsumoIds();

            if (empty($insumoIds)) {
                return;
            }

            $service = app(PepsInventarioService::class);
            $registrosCombinados = [];
            $saldoCantidad = 0;
            $saldoCosto = 0;

            foreach ($insumoIds as $insumoId) {
                $kardex = $service->generarKardex(
                    insumoId: $insumoId,
                    sucursalId: (int) $this->sucursal_id,
                    fechaDesde: $this->fecha_desde ?: null,
                    fechaHasta: $this->fecha_hasta ?: null,
                );

                $insumo = Insumo::find($insumoId);
                foreach ($kardex['registros'] as $registro) {
                    $registro['insumo_nombre'] = $insumo->nombre ?? '';
                    $registrosCombinados[] = $registro;
                }

                $saldoCantidad += $kardex['saldo_final_cantidad'];
                $saldoCosto += $kardex['saldo_final_costo'];
            }

            // Ordenar por fecha si hay múltiples insumos
            if (count($insumoIds) > 1) {
                usort($registrosCombinados, function ($a, $b) {
                    $dateA = \Carbon\Carbon::createFromFormat('d/m/Y', $a['fecha']);
                    $dateB = \Carbon\Carbon::createFromFormat('d/m/Y', $b['fecha']);
                    return $dateA->timestamp - $dateB->timestamp;
                });
            }

            $this->totalMovimientos = count($registrosCombinados);
            $this->hayDatos = $this->totalMovimientos > 0;
            $this->saldoFinalCantidad = round($saldoCantidad, 2);
            $this->saldoFinalCosto = round(max(0, $saldoCosto), 6);

            // Guardar en cache por 10 minutos (NO en propiedad pública de Livewire)
            if ($this->hayDatos) {
                $this->cacheKey = 'kardex_' . auth()->id() . '_' . md5(json_encode([
                    $this->sucursal_id,
                    $this->insumo_id,
                    $this->filtro_categoria,
                    $this->fecha_desde,
                    $this->fecha_hasta,
                ]));
                Cache::put($this->cacheKey, $registrosCombinados, now()->addMinutes(10));
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error al generar el Kardex: ' . $e->getMessage());
        }
    }

    /**
     * Obtener los IDs de insumos según los filtros actuales
     */
    private function getInsumoIds(): array
    {
        if ($this->insumo_id) {
            return [(int) $this->insumo_id];
        }

        $query = Insumo::where('estado', true);
        if ($this->filtro_categoria) {
            $query->where('categoria_id', $this->filtro_categoria);
        }

        return $query->pluck('id')->toArray();
    }

    /**
     * Obtener registros paginados desde el cache.
     * La generación costosa ya se hizo en generarYCachearKardex().
     */
    #[Computed]
    public function registrosPaginados()
    {
        if (!$this->cacheKey || !$this->hayDatos) {
            return [];
        }

        $registros = Cache::get($this->cacheKey, []);

        if (empty($registros)) {
            return [];
        }

        $offset = ($this->paginaActual - 1) * $this->porPagina;
        return array_slice($registros, $offset, $this->porPagina);
    }

    /**
     * Título del Kardex actual
     */
    #[Computed]
    public function tituloKardex()
    {
        if ($this->insumo_id) {
            return 'Inventario: ' . ($this->insumos->firstWhere('id', $this->insumo_id)?->nombre ?? '');
        }

        if ($this->filtro_categoria) {
            return 'Inventario - Categoría: ' . ($this->categorias->firstWhere('id', $this->filtro_categoria)?->nombre ?? '');
        }

        return 'Inventario General';
    }

    /**
     * URL para exportar a Excel
     */
    #[Computed]
    public function urlExcel()
    {
        return route('inventario.kardex.excel', $this->exportParams());
    }

    /**
     * URL para exportar a PDF
     */
    #[Computed]
    public function urlPdf()
    {
        return route('inventario.kardex.pdf', $this->exportParams());
    }

    private function exportParams(): array
    {
        return array_filter([
            'sucursal_id' => $this->sucursal_id,
            'insumo_id' => $this->insumo_id,
            'filtro_categoria' => $this->filtro_categoria,
            'fecha_desde' => $this->fecha_desde,
            'fecha_hasta' => $this->fecha_hasta,
        ]);
    }

    /**
     * Total de páginas
     */
    #[Computed]
    public function totalPaginas()
    {
        if (!$this->hayDatos || $this->totalMovimientos <= 0) {
            return 1;
        }

        return (int) ceil($this->totalMovimientos / $this->porPagina);
    }

    /**
     * Determinar si estamos en modo categoría (multi-insumo)
     */
    #[Computed]
    public function modoCategoria()
    {
        return !$this->insumo_id;
    }

    public function paginaAnterior()
    {
        if ($this->paginaActual > 1) {
            $this->paginaActual--;
        }
    }

    public function paginaSiguiente()
    {
        if ($this->paginaActual < $this->totalPaginas) {
            $this->paginaActual++;
        }
    }

    public function irAPagina($pagina)
    {
        $pagina = max(1, min($pagina, $this->totalPaginas));
        $this->paginaActual = $pagina;
    }

    public function limpiarFiltros()
    {
        if ($this->cacheKey) {
            Cache::forget($this->cacheKey);
        }
        $this->reset(['sucursal_id', 'insumo_id', 'filtro_categoria', 'fecha_desde', 'fecha_hasta', 'paginaActual', 'cacheKey']);
        $this->saldoFinalCantidad = 0;
        $this->saldoFinalCosto = 0;
        $this->totalMovimientos = 0;
        $this->hayDatos = false;
    }

    public function render()
    {
        return view('livewire.inventario.kardex-peps');
    }
}