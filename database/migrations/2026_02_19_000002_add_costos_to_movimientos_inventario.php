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
            $table->decimal('costo_unitario', 12, 4)->default(0)->after('cantidad'); // Bs por unidad
            $table->decimal('costo_total', 12, 4)->default(0)->after('costo_unitario'); // Cantidad × costo unitario
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropColumn(['costo_unitario', 'costo_total']);
        });
    }
};
