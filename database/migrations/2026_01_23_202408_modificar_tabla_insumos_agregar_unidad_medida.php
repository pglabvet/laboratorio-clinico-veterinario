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
        Schema::table('insumos', function (Blueprint $table) {
            // Eliminar columna antigua 'unidad' si existe
            if (Schema::hasColumn('insumos', 'unidad')) {
                $table->dropColumn('unidad');
            }
            
            // Eliminar columnas de stock si existen
            if (Schema::hasColumn('insumos', 'stock_actual')) {
                $table->dropColumn('stock_actual');
            }
            if (Schema::hasColumn('insumos', 'stock_minimo')) {
                $table->dropColumn('stock_minimo');
            }
            
            // Hacer categoria_id nullable
            if (Schema::hasColumn('insumos', 'categoria_id')) {
                $table->foreignId('categoria_id')->nullable()->change();
            }
            
            // Agregar nueva columna unidad_medida_id
            if (!Schema::hasColumn('insumos', 'unidad_medida_id')) {
                $table->foreignId('unidad_medida_id')->nullable()->after('categoria_id')->constrained('unidades_medida')->onDelete('restrict');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            // Revertir cambios
            if (Schema::hasColumn('insumos', 'unidad_medida_id')) {
                $table->dropForeign(['unidad_medida_id']);
                $table->dropColumn('unidad_medida_id');
            }
            
            // Restaurar columnas antiguas
            if (!Schema::hasColumn('insumos', 'unidad')) {
                $table->string('unidad')->after('categoria_id');
            }
            if (!Schema::hasColumn('insumos', 'stock_actual')) {
                $table->decimal('stock_actual', 10, 2)->default(0)->after('unidad');
            }
            if (!Schema::hasColumn('insumos', 'stock_minimo')) {
                $table->decimal('stock_minimo', 10, 2)->default(0)->after('stock_actual');
            }
        });
    }
};
