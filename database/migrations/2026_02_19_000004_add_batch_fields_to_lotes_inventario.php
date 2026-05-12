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
        Schema::table('lotes_inventario', function (Blueprint $table) {
            $table->string('codigo_lote')->nullable()->after('movimiento_entrada_id');
            $table->date('fecha_vencimiento')->nullable()->after('fecha_entrada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lotes_inventario', function (Blueprint $table) {
            $table->dropColumn(['codigo_lote', 'fecha_vencimiento']);
        });
    }
};
