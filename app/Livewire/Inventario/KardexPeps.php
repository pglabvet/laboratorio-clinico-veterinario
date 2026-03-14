<?php

namespace App\Livewire\Inventario;

use App\Models\Insumo;
use App\Models\CategoriaInsumo;
use App\Models\Sucursal;
use App\Services\PepsInventarioService;
use Livewire\Component;
use Livewire\Attributes\Computed;

class KardexPeps extends Component
{
    public $sucursal_id = '';
    public $insumo_id = '';
    public $filtro_categoria = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';
    public $kardexData = null;

    // Paginación manual
    public $paginaActual = 1;
    public $porPagina = 20;

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
        $this->generarKardex();
    }

    public function updatedFiltroCategoria()
    {
        $this->insumo_id = '';
        $this->kardexData = null;
        $this->paginaActual = 1;
        $this->generarKardex();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['insumo_id', 'fecha_desde', 'fecha_hasta'])) {
            $this->paginaActual = 1;
            $this->generarKardex();
        }
    }

    public function generarKardex()
    {
        $this->kardexData = null;

        if (!$this->sucursal_id) {
            return;
        }

        // Se permite generar con solo sucursal (muestra todos los insumos)

        try {
            $service = app(PepsInventarioService::class);

            if ($this->insumo_id) {
                // Kardex para un insumo específico
                $kardex = $service->generarKardex(
                    insumoId: (int) $this->insumo_id,
                    sucursalId: (int) $this->sucursal_id,
                    fechaDesde: $this->fecha_desde ?: null,
                    fechaHasta: $this->fecha_hasta ?: null,
                );

                // Agregar nombre del insumo a cada registro
                $insumo = Insumo::find($this->insumo_id);
                foreach ($kardex['registros'] as &$registro) {
                    $registro['insumo_nombre'] = $insumo->nombre ?? '';
                }
                unset($registro);

                $this->kardexData = $kardex;
            } else {
                // Kardex para múltiples insumos (por categoría o todos)
                $queryInsumos = Insumo::where('estado', true);

                if ($this->filtro_categoria) {
                    $queryInsumos->where('categoria_id', $this->filtro_categoria);
                }

                $insumosListado = $queryInsumos->orderBy('nombre')->get();

                if ($insumosListado->isEmpty()) {
                    return;
                }

                $registrosCombinados = [];
                $saldoFinalCantidad = 0;
                $saldoFinalCosto = 0;

                foreach ($insumosListado as $insumo) {
                    $kardex = $service->generarKardex(
                        insumoId: $insumo->id,
                        sucursalId: (int) $this->sucursal_id,
                        fechaDesde: $this->fecha_desde ?: null,
                        fechaHasta: $this->fecha_hasta ?: null,
                    );

                    // Agregar nombre del insumo a cada registro
                    foreach ($kardex['registros'] as $registro) {
                        $registro['insumo_nombre'] = $insumo->nombre;
                        $registrosCombinados[] = $registro;
                    }

                    $saldoFinalCantidad += $kardex['saldo_final_cantidad'];
                    $saldoFinalCosto += $kardex['saldo_final_costo'];
                }

                // Ordenar por fecha
                usort($registrosCombinados, function ($a, $b) {
                    $dateA = \Carbon\Carbon::createFromFormat('d/m/Y', $a['fecha']);
                    $dateB = \Carbon\Carbon::createFromFormat('d/m/Y', $b['fecha']);
                    return $dateA->timestamp - $dateB->timestamp;
                });

                $this->kardexData = [
                    'saldo_inicial_cantidad' => 0,
                    'saldo_inicial_costo' => 0,
                    'registros' => $registrosCombinados,
                    'saldo_final_cantidad' => round($saldoFinalCantidad, 2),
                    'saldo_final_costo' => round($saldoFinalCosto, 4),
                ];
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error al generar el Kardex: ' . $e->getMessage());
        }
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
     * Obtener registros paginados para la vista
     */
    #[Computed]
    public function registrosPaginados()
    {
        if (!$this->kardexData || empty($this->kardexData['registros'])) {
            return [];
        }

        $registros = $this->kardexData['registros'];
        $offset = ($this->paginaActual - 1) * $this->porPagina;

        return array_slice($registros, $offset, $this->porPagina);
    }

    /**
     * Total de páginas
     */
    #[Computed]
    public function totalPaginas()
    {
        if (!$this->kardexData || empty($this->kardexData['registros'])) {
            return 1;
        }

        return (int) ceil(count($this->kardexData['registros']) / $this->porPagina);
    }

    /**
     * Total de registros
     */
    #[Computed]
    public function totalRegistros()
    {
        if (!$this->kardexData || empty($this->kardexData['registros'])) {
            return 0;
        }

        return count($this->kardexData['registros']);
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
        $this->reset(['sucursal_id', 'insumo_id', 'filtro_categoria', 'fecha_desde', 'fecha_hasta', 'kardexData', 'paginaActual']);
    }

    public function render()
    {
        return view('livewire.inventario.kardex-peps');
    }
}