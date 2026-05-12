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
        Schema::table('inventario_sucursal', function (Blueprint $table) {
            $table->decimal('costo_total', 12, 4)->default(0)->after('stock_minimo'); // Valor total del stock en Bs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventario_sucursal', function (Blueprint $table) {
            $table->dropColumn('costo_total');
        });
    }
};
