<?php

namespace Database\Seeders;

use App\Models\Insumo;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\InventarioSucursal;
use App\Models\MovimientoInventario;
use App\Models\LoteInventario;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

/**
 * Seeder para generar datos masivos en el Kardex PEPS.
 * 
 * Uso: php artisan db:seed --class=KardexStressTestSeeder
 * 
 * Genera movimientos de ENTRADA y SALIDA realistas para todos los
 * insumos activos en todas las sucursales activas, distribuyendo
 * los movimientos a lo largo de varios meses.
 */
class KardexStressTestSeeder extends Seeder
{
    /**
     * Cuántos movimientos generar POR INSUMO POR SUCURSAL.
     * Con 20 insumos y 2 sucursales = 20 * 2 * 50 = 2000 movimientos.
     * Ajustá este valor para más o menos datos.
     */
    private int $movimientosPorInsumoPorSucursal = 50;

    public function run(): void
    {
        $this->command->info('🔄 Generando datos masivos para Kardex PEPS...');

        $sucursales = Sucursal::where('estado', true)->get();
        $insumos = Insumo::where('estado', true)->get();
        $usuario = User::first();

        if ($sucursales->isEmpty() || $insumos->isEmpty() || !$usuario) {
            $this->command->error('❌ Se necesitan al menos: 1 sucursal activa, 1 insumo activo y 1 usuario.');
            return;
        }

        $this->command->info("📦 Sucursales: {$sucursales->count()} | Insumos: {$insumos->count()}");
        $this->command->info("📊 Movimientos por insumo/sucursal: {$this->movimientosPorInsumoPorSucursal}");

        $totalMovimientos = 0;
        $motivos = ['COMPRA', 'DEVOLUCION', 'OTRO'];
        $motivosSalida = ['MERMA', 'VENCIMIENTO', 'USO_EXTRAORDINARIO', 'CONSUMO_ANALISIS', 'AJUSTE_INVENTARIO'];

        foreach ($sucursales as $sucursal) {
            foreach ($insumos as $insumo) {
                $this->command->info("  → {$sucursal->nombre} / {$insumo->nombre}");

                $saldoCantidad = 0;
                $saldoCosto = 0;
                $fechaBase = Carbon::now()->subMonths(6);

                for ($i = 0; $i < $this->movimientosPorInsumoPorSucursal; $i++) {
                    // Avanzar la fecha entre 1 y 5 días
                    $fechaBase->addDays(rand(1, 5));
                    $fecha = $fechaBase->copy();

                    // 60% entradas, 40% salidas (pero solo si hay stock)
                    $esEntrada = $i < 3 || rand(1, 100) <= 60 || $saldoCantidad <= 0;

                    if ($esEntrada) {
                        // ENTRADA
                        $cantidad = round(rand(50, 5000) / 10, 2); // 5.0 a 500.0
                        $costoUnitario = round(rand(10, 15000) / 100, 4); // 0.10 a 150.00
                        $costoTotal = round($cantidad * $costoUnitario, 6);

                        $movimiento = MovimientoInventario::create([
                            'insumo_id' => $insumo->id,
                            'sucursal_id' => $sucursal->id,
                            'tipo_movimiento' => 'ENTRADA',
                            'cantidad' => $cantidad,
                            'costo_unitario' => $costoUnitario,
                            'costo_total' => $costoTotal,
                            'motivo' => $motivos[array_rand($motivos)],
                            'observacion' => "Entrada de prueba #{$i}",
                            'usuario_id' => $usuario->id,
                            'fecha' => $fecha,
                        ]);

                        // Crear lote
                        LoteInventario::create([
                            'insumo_id' => $insumo->id,
                            'sucursal_id' => $sucursal->id,
                            'movimiento_entrada_id' => $movimiento->id,
                            'cantidad_inicial' => $cantidad,
                            'cantidad_restante' => $cantidad,
                            'costo_unitario' => $costoUnitario,
                            'fecha_entrada' => $fecha,
                            'codigo_lote' => 'LOT-' . strtoupper(substr(md5(rand()), 0, 6)),
                            'fecha_vencimiento' => $fecha->copy()->addMonths(rand(3, 18)),
                        ]);

                        $saldoCantidad += $cantidad;
                        $saldoCosto += $costoTotal;
                    } else {
                        // SALIDA
                        $maxSalida = min($saldoCantidad, 200);
                        if ($maxSalida <= 0) continue;

                        $cantidad = round(rand(10, (int)($maxSalida * 10)) / 10, 2);
                        $cantidad = min($cantidad, $saldoCantidad);

                        // Calcular costo promedio
                        $costoUnitario = $saldoCantidad > 0 ? round($saldoCosto / $saldoCantidad, 6) : 0;
                        $costoTotal = round($cantidad * $costoUnitario, 6);

                        MovimientoInventario::create([
                            'insumo_id' => $insumo->id,
                            'sucursal_id' => $sucursal->id,
                            'tipo_movimiento' => rand(1, 100) <= 60 ? 'SALIDA_MANUAL' : 'CONSUMO_ANALISIS',
                            'cantidad' => -$cantidad,
                            'costo_unitario' => $costoUnitario,
                            'costo_total' => $costoTotal,
                            'motivo' => $motivosSalida[array_rand($motivosSalida)],
                            'observacion' => "Salida de prueba #{$i}",
                            'usuario_id' => $usuario->id,
                            'fecha' => $fecha,
                        ]);

                        // Descontar de lotes (PEPS simplificado)
                        $pendiente = $cantidad;
                        $lotes = LoteInventario::where('insumo_id', $insumo->id)
                            ->where('sucursal_id', $sucursal->id)
                            ->where('cantidad_restante', '>', 0)
                            ->orderBy('fecha_entrada', 'asc')
                            ->get();

                        foreach ($lotes as $lote) {
                            if ($pendiente <= 0) break;
                            $consumir = min($lote->cantidad_restante, $pendiente);
                            $lote->cantidad_restante -= $consumir;
                            $lote->save();
                            $pendiente -= $consumir;
                        }

                        $saldoCantidad -= $cantidad;
                        $saldoCosto -= $costoTotal;
                        $saldoCosto = max(0, $saldoCosto);
                    }

                    $totalMovimientos++;
                }

                // Actualizar inventario_sucursal
                $inventario = InventarioSucursal::firstOrCreate(
                    ['insumo_id' => $insumo->id, 'sucursal_id' => $sucursal->id],
                    ['stock_actual' => 0, 'stock_minimo' => 0, 'costo_total' => 0]
                );
                $inventario->stock_actual = max(0, round($saldoCantidad, 2));
                $inventario->costo_total = max(0, round($saldoCosto, 6));
                $inventario->save();
            }
        }

        $this->command->info("✅ Generados {$totalMovimientos} movimientos de inventario.");
        $this->command->info('📌 Ahora podés probar el Kardex PEPS y la generación de PDF.');
    }
}
