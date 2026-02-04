<?php

use Illuminate\Support\Facades\DB;
use App\Models\Insumo;
use App\Models\UnidadMedida;
use App\Models\Sucursal;
use App\Models\InventarioSucursal;

// Obtener primera unidad de medida activa
$unidad = UnidadMedida::where('estado', true)->first();

if (!$unidad) {
    echo "❌ No hay unidades de medida activas\n";
    exit(1);
}

echo "✓ Unidad de medida: {$unidad->nombre} ({$unidad->abreviatura})\n";

// Crear insumo de prueba
DB::beginTransaction();
try {
    $insumo = Insumo::create([
        'nombre' => 'Alcohol Etílico 70%',
        'unidad_medida_id' => $unidad->id,
        'estado' => true,
    ]);

    echo "✓ Insumo creado: ID {$insumo->id} - {$insumo->nombre}\n";

    // Crear inventarios para cada sucursal activa
    $sucursales = Sucursal::where('estado', true)->get();
    
    if ($sucursales->count() === 0) {
        echo "❌ No hay sucursales activas\n";
        DB::rollBack();
        exit(1);
    }

    foreach ($sucursales as $sucursal) {
        InventarioSucursal::create([
            'insumo_id' => $insumo->id,
            'sucursal_id' => $sucursal->id,
            'stock_actual' => 0,
            'stock_minimo' => 50,
        ]);
        echo "  ✓ Inventario creado para sucursal: {$sucursal->nombre}\n";
    }

    DB::commit();
    echo "\n✅ Insumo de prueba creado exitosamente\n";
    echo "   Total inventarios: {$sucursales->count()}\n";

} catch (Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: {$e->getMessage()}\n";
    echo "   Traza: {$e->getTraceAsString()}\n";
}
