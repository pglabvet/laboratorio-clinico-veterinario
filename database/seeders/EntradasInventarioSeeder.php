<?php

namespace Database\Seeders;

use App\Models\Insumo;
use App\Models\InventarioSucursal;
use App\Models\MovimientoInventario;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Seeder;

class EntradasInventarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Registra entradas de inventario para cada insumo en cada sucursal
     */
    public function run(): void
    {
        $sucursales = Sucursal::all();
        $insumos = Insumo::all();
        $usuario = User::first(); // Usuario admin para registrar movimientos

        if ($sucursales->isEmpty() || $insumos->isEmpty() || !$usuario) {
            $this->command->warn('Debe ejecutar primero SucursalesSeeder, InsumosSeeder y UsersSeeder');
            return;
        }

        foreach ($sucursales as $sucursal) {
            foreach ($insumos as $insumo) {
                // Generar cantidad aleatoria según tipo de insumo
                $cantidad = $this->generarCantidadSegunCategoria($insumo);
                $stockMinimo = $this->generarStockMinimo($insumo);

                // Crear o actualizar inventario en sucursal
                $inventario = InventarioSucursal::updateOrCreate(
                    [
                        'insumo_id' => $insumo->id,
                        'sucursal_id' => $sucursal->id,
                    ],
                    [
                        'stock_actual' => $cantidad,
                        'stock_minimo' => $stockMinimo,
                    ]
                );

                // Registrar movimiento de entrada
                MovimientoInventario::create([
                    'insumo_id' => $insumo->id,
                    'sucursal_id' => $sucursal->id,
                    'tipo_movimiento' => 'ENTRADA',
                    'cantidad' => $cantidad,
                    'motivo' => 'COMPRA',
                    'observacion' => 'Inventario inicial - Seeder',
                    'usuario_id' => $usuario->id,
                    'fecha' => now()->subDays(rand(1, 30)),
                ]);
            }
        }

        $this->command->info('Entradas de inventario registradas correctamente.');
    }

    /**
     * Genera cantidad inicial según la categoría del insumo
     */
    private function generarCantidadSegunCategoria(Insumo $insumo): float
    {
        $categoria = $insumo->categoria?->nombre ?? '';

        return match (true) {
            // Reactivos y líquidos: 500-2000 ml
            str_contains($categoria, 'Reactivo') => rand(500, 2000),
            str_contains($categoria, 'Colorante') => rand(100, 500),
            str_contains($categoria, 'Buffer') => rand(500, 1500),
            
            // Medios de cultivo: 100-500 g
            str_contains($categoria, 'Cultivo') => rand(100, 500),
            
            // Material desechable: 50-200 unidades
            str_contains($categoria, 'Desechable') => rand(50, 200),
            str_contains($categoria, 'Vidriería') => rand(5, 20),
            
            // Kits: 5-20 unidades
            str_contains($categoria, 'Kit') => rand(5, 20),
            
            // Anticoagulantes: 50-200 ml/g
            str_contains($categoria, 'Anticoagulante') => rand(50, 200),
            
            // Default
            default => rand(50, 150),
        };
    }

    /**
     * Genera stock mínimo según la categoría del insumo
     */
    private function generarStockMinimo(Insumo $insumo): float
    {
        $categoria = $insumo->categoria?->nombre ?? '';

        return match (true) {
            str_contains($categoria, 'Reactivo') => 100,
            str_contains($categoria, 'Colorante') => 50,
            str_contains($categoria, 'Buffer') => 100,
            str_contains($categoria, 'Cultivo') => 50,
            str_contains($categoria, 'Desechable') => 20,
            str_contains($categoria, 'Vidriería') => 2,
            str_contains($categoria, 'Kit') => 3,
            str_contains($categoria, 'Anticoagulante') => 20,
            default => 10,
        };
    }
}
