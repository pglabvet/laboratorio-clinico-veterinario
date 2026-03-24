<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('consumos_pendientes');
    }

    public function down(): void
    {
        Schema::create('consumos_pendientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained('insumos');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->decimal('cantidad', 12, 4);
            $table->foreignId('usuario_id')->constrained('users');
            $table->string('observacion')->nullable();
            $table->string('estado')->default('pendiente');
            $table->timestamps();
        });
    }
};
