<?php

namespace App\Services;

use App\Models\InventarioSucursal;
use App\Models\LoteInventario;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class PepsInventarioService
{
    /**
     * Registrar una entrada de insumo: crea movimiento + lote + actualiza stock y costo total
     */
    public function registrarEntrada(
        int $insumoId,
        int $sucursalId,
        float $cantidad,
        float $costoUnitario,
        string $motivo,
        ?string $observacion,
        int $usuarioId,
        ?string $codigoLote = null,
        ?string $fechaVencimiento = null
        ): MovimientoInventario
    {
        return DB::transaction(function () use ($insumoId, $sucursalId, $cantidad, $costoUnitario, $motivo, $observacion, $usuarioId, $codigoLote, $fechaVencimiento) {
            $costoTotal = round($cantidad * $costoUnitario, 6);

            // 1. Crear movimiento de entrada
            $movimiento = MovimientoInventario::create([
                'insumo_id' => $insumoId,
                'sucursal_id' => $sucursalId,
                'tipo_movimiento' => 'ENTRADA',
                'cantidad' => $cantidad,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoTotal,
                'motivo' => $motivo,
                'observacion' => $observacion,
                'usuario_id' => $usuarioId,
                'fecha' => now(),
            ]);

            // 2. Crear lote de inventario
            LoteInventario::create([
                'insumo_id' => $insumoId,
                'sucursal_id' => $sucursalId,
                'movimiento_entrada_id' => $movimiento->id,
                'cantidad_inicial' => $cantidad,
                'cantidad_restante' => $cantidad,
                'costo_unitario' => $costoUnitario,
                'fecha_entrada' => now(),
                'codigo_lote' => $codigoLote,
                'fecha_vencimiento' => $fechaVencimiento,
            ]);

            // 3. Actualizar inventario de sucursal (stock + costo)
            $inventario = InventarioSucursal::firstOrCreate(
            [
                'insumo_id' => $insumoId,
                'sucursal_id' => $sucursalId,
            ],
            [
                'stock_actual' => 0,
                'stock_minimo' => 0,
                'costo_total' => 0,
            ]
            );

            $inventario->stock_actual += $cantidad;
            $inventario->costo_total += $costoTotal;
            $inventario->save();

            return $movimiento;
        });
    }

    /**
     * Registrar una salida de insumo usando el método PEPS:
     * Consume los lotes más antiguos primero y calcula el costo total
     */
    public function registrarSalida(
        int $insumoId,
        int $sucursalId,
        float $cantidad,
        string $motivo,
        ?string $observacion,
        int $usuarioId
        ): MovimientoInventario
    {
        return DB::transaction(function () use ($insumoId, $sucursalId, $cantidad, $motivo, $observacion, $usuarioId) {
            // 1. Verificar stock disponible
            $inventario = InventarioSucursal::where('insumo_id', $insumoId)
                ->where('sucursal_id', $sucursalId)
                ->lockForUpdate()
                ->first();

            if (!$inventario || $inventario->stock_actual < $cantidad) {
                throw new \Exception('Stock insuficiente. Stock actual: ' . ($inventario->stock_actual ?? 0));
            }

            // 2. Consumir lotes PEPS
            $resultado = $this->consumirLotesPeps($insumoId, $sucursalId, $cantidad);

            // 3. Calcular costo unitario promedio de la salida
            $costoUnitarioSalida = $cantidad > 0 ? round($resultado['costo_total'] / $cantidad, 6) : 0;

            // 4. Crear movimiento de salida
            $movimiento = MovimientoInventario::create([
                'insumo_id' => $insumoId,
                'sucursal_id' => $sucursalId,
                'tipo_movimiento' => 'SALIDA_MANUAL',
                'cantidad' => -$cantidad, // Negativo para salidas
                'costo_unitario' => $costoUnitarioSalida,
                'costo_total' => $resultado['costo_total'],
                'motivo' => $motivo,
                'observacion' => $observacion,
                'usuario_id' => $usuarioId,
                'fecha' => now(),
            ]);

            // 5. Actualizar inventario de sucursal
            $inventario->stock_actual -= $cantidad;
            $inventario->recalcularCostoTotal();

            return $movimiento;
        });
    }

    /**
     * Registrar consumo de insumo por análisis usando método PEPS.
     * Lanza excepción si no hay stock suficiente.
     */
    public function registrarConsumoAnalisis(
        int $insumoId,
        int $sucursalId,
        float $cantidad,
        int $usuarioId,
        ?string $observacion = null
        ): MovimientoInventario
    {
        return DB::transaction(function () use ($insumoId, $sucursalId, $cantidad, $usuarioId, $observacion) {
            // 1. Verificar stock disponible
            $inventario = InventarioSucursal::where('insumo_id', $insumoId)
                ->where('sucursal_id', $sucursalId)
                ->lockForUpdate()
                ->first();

            $stockActual = $inventario ? $inventario->stock_actual : 0;

            if ($stockActual < $cantidad) {
                $insumo = \App\Models\Insumo::find($insumoId);
                $nombre = $insumo ? $insumo->nombre : "Insumo #{$insumoId}";
                $unidad = $insumo?->unidadMedida?->abreviatura ?? '';
                throw new \Exception(
                    "Stock insuficiente de '{$nombre}': disponible {$stockActual} {$unidad}, requerido {$cantidad} {$unidad}."
                );
            }

            // 2. Consumir lotes PEPS
            $resultado = $this->consumirLotesPeps($insumoId, $sucursalId, $cantidad);

            // 3. Calcular costo unitario promedio de la salida
            $costoUnitarioSalida = $cantidad > 0 ? round($resultado['costo_total'] / $cantidad, 6) : 0;

            // 4. Crear movimiento de consumo por análisis
            $movimiento = MovimientoInventario::create([
                'insumo_id' => $insumoId,
                'sucursal_id' => $sucursalId,
                'tipo_movimiento' => 'CONSUMO_ANALISIS',
                'cantidad' => -$cantidad,
                'costo_unitario' => $costoUnitarioSalida,
                'costo_total' => $resultado['costo_total'],
                'motivo' => 'CONSUMO_ANALISIS',
                'observacion' => $observacion,
                'usuario_id' => $usuarioId,
                'fecha' => now(),
            ]);

            // 5. Actualizar inventario de sucursal
            $inventario->stock_actual -= $cantidad;
            $inventario->recalcularCostoTotal();

            return $movimiento;
        });
    }

    /**
     * Revertir un consumo de análisis: crea una entrada de devolución con el mismo costo.
     * Se usa cuando se elimina una muestra para devolver los insumos al stock.
     */
    public function revertirConsumoAnalisis(
        int $insumoId,
        int $sucursalId,
        float $cantidad,
        float $costoUnitario,
        int $usuarioId,
        ?string $observacion = null
        ): MovimientoInventario
    {
        return $this->registrarEntrada(
            insumoId: $insumoId,
            sucursalId: $sucursalId,
            cantidad: $cantidad,
            costoUnitario: $costoUnitario,
            motivo: 'DEVOLUCION',
            observacion: $observacion ?? 'Devolución por eliminación de muestra',
            usuarioId: $usuarioId
        );
    }

    /**
     * Consumir lotes según método PEPS (más antiguo primero)
     * Retorna el detalle de lotes consumidos y el costo total
     */
    private function consumirLotesPeps(int $insumoId, int $sucursalId, float $cantidad): array
    {
        $lotes = LoteInventario::where('insumo_id', $insumoId)
            ->where('sucursal_id', $sucursalId)
            ->conStock()
            ->peps()
            ->lockForUpdate()
            ->get();

        $cantidadPendiente = $cantidad;
        $costoTotal = 0;
        $detalleLotes = [];

        foreach ($lotes as $lote) {
            if ($cantidadPendiente <= 0) {
                break;
            }

            $cantidadDelLote = min($lote->cantidad_restante, $cantidadPendiente);
            $costoDelLote = round($cantidadDelLote * $lote->costo_unitario, 6);

            // Descontar del lote
            $lote->cantidad_restante -= $cantidadDelLote;
            $lote->save();

            $costoTotal += $costoDelLote;
            $cantidadPendiente -= $cantidadDelLote;

            $detalleLotes[] = [
                'lote_id' => $lote->id,
                'cantidad_consumida' => $cantidadDelLote,
                'costo_unitario' => $lote->costo_unitario,
                'costo_subtotal' => $costoDelLote,
                'fecha_entrada' => $lote->fecha_entrada,
            ];
        }

        if ($cantidadPendiente > 0) {
            throw new \Exception('No hay suficientes lotes para cubrir la salida. Faltan: ' . $cantidadPendiente);
        }

        return [
            'costo_total' => round($costoTotal, 6),
            'detalle_lotes' => $detalleLotes,
        ];
    }

    /**
     * Obtener lotes disponibles para un insumo en una sucursal (orden PEPS)
     */
    public function obtenerLotesDisponibles(int $insumoId, int $sucursalId): Collection
    {
        return LoteInventario::where('insumo_id', $insumoId)
            ->where('sucursal_id', $sucursalId)
            ->conStock()
            ->peps()
            ->get();
    }

    /**
     * Calcular el costo estimado de una salida PEPS sin ejecutarla (preview)
     * Retorna el detalle de lotes que se consumirían
     */
    public function calcularCostoPeps(int $insumoId, int $sucursalId, float $cantidad): array
    {
        $lotes = LoteInventario::where('insumo_id', $insumoId)
            ->where('sucursal_id', $sucursalId)
            ->conStock()
            ->peps()
            ->get();

        $cantidadPendiente = $cantidad;
        $costoTotal = 0;
        $detalleLotes = [];

        foreach ($lotes as $lote) {
            if ($cantidadPendiente <= 0) {
                break;
            }

            $cantidadDelLote = min($lote->cantidad_restante, $cantidadPendiente);
            $costoDelLote = round($cantidadDelLote * $lote->costo_unitario, 6);

            $costoTotal += $costoDelLote;
            $cantidadPendiente -= $cantidadDelLote;

            $detalleLotes[] = [
                'lote_id' => $lote->id,
                'cantidad_consumida' => $cantidadDelLote,
                'costo_unitario' => $lote->costo_unitario,
                'costo_subtotal' => $costoDelLote,
                'fecha_entrada' => $lote->fecha_entrada->format('d/m/Y'),
                'cantidad_restante_lote' => $lote->cantidad_restante,
            ];
        }

        $stockSuficiente = $cantidadPendiente <= 0;
        $costoUnitarioPromedio = ($cantidad > 0 && $stockSuficiente)
            ? round($costoTotal / $cantidad, 6)
            : 0;

        return [
            'costo_total' => round($costoTotal, 6),
            'costo_unitario_promedio' => $costoUnitarioPromedio,
            'detalle_lotes' => $detalleLotes,
            'stock_suficiente' => $stockSuficiente,
        ];
    }

    /**
     * Generar datos de Kardex PEPS para un insumo en una sucursal
     */
    public function generarKardex(int $insumoId, int $sucursalId, ?string $fechaDesde = null, ?string $fechaHasta = null): array
    {
        $query = MovimientoInventario::where('insumo_id', $insumoId)
            ->where('sucursal_id', $sucursalId)
            ->orderBy('fecha', 'asc')
            ->orderBy('id', 'asc');

        if ($fechaDesde) {
            $query->whereDate('fecha', '>=', $fechaDesde);
        }

        if ($fechaHasta) {
            $query->whereDate('fecha', '<=', $fechaHasta);
        }

        $movimientos = $query->get();

        // Calcular saldo inicial si hay filtro de fecha
        $saldoCantidadInicial = 0;
        $saldoCostoInicial = 0;

        if ($fechaDesde) {
            $movimientosPrevios = MovimientoInventario::where('insumo_id', $insumoId)
                ->where('sucursal_id', $sucursalId)
                ->whereDate('fecha', '<', $fechaDesde)
                ->get();

            foreach ($movimientosPrevios as $mov) {
                $saldoCantidadInicial += $mov->cantidad; // Positivo para entradas, negativo para salidas
                if ($mov->tipo_movimiento === 'ENTRADA') {
                    $saldoCostoInicial += $mov->costo_total;
                }
                else {
                    $saldoCostoInicial -= $mov->costo_total;
                }
            }
        }

        $saldoCantidad = $saldoCantidadInicial;
        $saldoCosto = $saldoCostoInicial;
        $registros = [];

        foreach ($movimientos as $movimiento) {
            $inicioCantidad = $saldoCantidad;
            $inicioCosto = $saldoCosto;

            $entradaCantidad = null;
            $entradaCosto = null;
            $salidaCantidad = null;
            $salidaCosto = null;

            if ($movimiento->tipo_movimiento === 'ENTRADA') {
                $entradaCantidad = $movimiento->cantidad;
                $entradaCosto = $movimiento->costo_total;
                $saldoCantidad += $movimiento->cantidad;
                $saldoCosto += $movimiento->costo_total;
            }
            else {
                $salidaCantidad = abs($movimiento->cantidad);
                $salidaCosto = $movimiento->costo_total;
                $saldoCantidad -= abs($movimiento->cantidad);
                $saldoCosto -= $movimiento->costo_total;
            }

            // Asegurar que el saldo de costo no sea negativo
            $saldoCosto = max(0, $saldoCosto);

            $detalle = $this->obtenerDetalleMovimiento($movimiento);

            $registros[] = [
                'fecha' => $movimiento->fecha->format('d/m/Y'),
                'detalle' => $detalle,
                'inicio_cantidad' => round($inicioCantidad, 2),
                'entrada_cantidad' => $entradaCantidad !== null ? round($entradaCantidad, 2) : null,
                'salida_cantidad' => $salidaCantidad !== null ? round($salidaCantidad, 2) : null,
                'saldo_cantidad' => round($saldoCantidad, 2),
                'inicio_costo' => round($inicioCosto, 6),
                'entrada_costo' => $entradaCosto !== null ? round($entradaCosto, 6) : null,
                'salida_costo' => $salidaCosto !== null ? round($salidaCosto, 6) : null,
                'saldo_costo' => round($saldoCosto, 6),
            ];
        }

        return [
            'saldo_inicial_cantidad' => round($saldoCantidadInicial, 2),
            'saldo_inicial_costo' => round($saldoCostoInicial, 6),
            'registros' => $registros,
            'saldo_final_cantidad' => round($saldoCantidad, 2),
            'saldo_final_costo' => round($saldoCosto, 6),
        ];
    }

    /**
     * Obtener detalle legible de un movimiento
     */
    private function obtenerDetalleMovimiento(MovimientoInventario $movimiento): string
    {
        $motivos = [
            'MERMA' => 'Merma',
            'VENCIMIENTO' => 'Vencimiento',
            'USO_EXTRAORDINARIO' => 'Uso Extraordinario',
            'CONSUMO_ANALISIS' => 'Consumo Análisis',
            'AJUSTE_INVENTARIO' => 'Ajuste Inventario',
            'COMPRA' => 'Compra',
            'DEVOLUCION' => 'Devolución',
            'OTRO' => 'Otro',
        ];

        $tipo = match ($movimiento->tipo_movimiento) {
                'ENTRADA' => 'Entrada',
                'SALIDA_MANUAL' => 'Salida',
                'CONSUMO_ANALISIS' => 'Consumo',
                'AJUSTE' => 'Ajuste',
                default => $movimiento->tipo_movimiento,
            };

        $motivo = $motivos[$movimiento->motivo] ?? $movimiento->motivo;

        return "{$tipo} - {$motivo}";
    }
}
