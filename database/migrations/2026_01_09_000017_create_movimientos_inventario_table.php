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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->string('tipo_movimiento'); // ENTRADA, SALIDA_MANUAL, CONSUMO_ANALISIS, AJUSTE
            $table->decimal('cantidad', 10, 2);
            $table->string('motivo'); // MERMA, VENCIMIENTO, USO_EXTRAORDINARIO, CONSUMO_ANALISIS, AJUSTE_INVENTARIO, COMPRA, DEVOLUCION, OTRO
            $table->text('observacion')->nullable();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('fecha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
