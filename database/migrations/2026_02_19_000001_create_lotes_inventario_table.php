<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lotes_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->foreignId('movimiento_entrada_id')->constrained('movimientos_inventario')->onDelete('cascade');
            $table->decimal('cantidad_inicial', 10, 2);
            $table->decimal('cantidad_restante', 10, 2);
            $table->decimal('costo_unitario', 12, 4); // Bs por unidad
            $table->timestamp('fecha_entrada');
            $table->timestamps();

            // Índice para consultas PEPS (lotes más antiguos primero)
            $table->index(['insumo_id', 'sucursal_id', 'fecha_entrada'], 'lotes_peps_index');
            // Índice para encontrar lotes con stock disponible
            $table->index(['insumo_id', 'sucursal_id', 'cantidad_restante'], 'lotes_con_stock_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes_inventario');
    }
};
