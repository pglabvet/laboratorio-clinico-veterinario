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
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->decimal('cantidad', 14, 6)->change();
            $table->decimal('costo_total', 14, 6)->default(0)->change();
            $table->decimal('costo_unitario', 14, 6)->default(0)->change();
        });

        Schema::table('lotes_inventario', function (Blueprint $table) {
            $table->decimal('cantidad_inicial', 14, 6)->change();
            $table->decimal('cantidad_restante', 14, 6)->change();
        });

        Schema::table('inventario_sucursal', function (Blueprint $table) {
            $table->decimal('stock_actual', 14, 6)->default(0)->change();
            $table->decimal('stock_minimo', 14, 6)->default(0)->change();
            $table->decimal('costo_total', 14, 6)->default(0)->change();
        });

        Schema::table('analisis_insumos', function (Blueprint $table) {
            $table->decimal('cantidad_usada', 14, 6)->change();
        });

        Schema::table('tipo_analisis_insumos', function (Blueprint $table) {
            $table->decimal('cantidad_requerida', 14, 6)->change();
        });

        Schema::table('plantilla_insumos', function (Blueprint $table) {
            $table->decimal('cantidad_requerida', 14, 6)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->decimal('cantidad', 10, 2)->change();
            $table->decimal('costo_total', 12, 4)->change();
        });

        Schema::table('lotes_inventario', function (Blueprint $table) {
            $table->decimal('cantidad_inicial', 10, 2)->change();
            $table->decimal('cantidad_restante', 10, 2)->change();
        });

        Schema::table('inventario_sucursal', function (Blueprint $table) {
            $table->decimal('stock_actual', 10, 2)->change();
            $table->decimal('stock_minimo', 10, 2)->change();
            $table->decimal('costo_total', 12, 4)->change();
        });

        Schema::table('analisis_insumos', function (Blueprint $table) {
            $table->decimal('cantidad_usada', 10, 2)->change();
        });

        Schema::table('tipo_analisis_insumos', function (Blueprint $table) {
            $table->decimal('cantidad_requerida', 10, 2)->change();
        });

        Schema::table('plantilla_insumos', function (Blueprint $table) {
            $table->decimal('cantidad_requerida', 10, 2)->change();
        });
    }
};
