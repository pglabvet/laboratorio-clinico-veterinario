<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Un cobro por muestra. Fecha, veterinaria, código, paciente y sucursal
     * se leen de la muestra; aquí solo se guarda la información del cobro.
     */
    public function up(): void
    {
        Schema::create('cobros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muestra_id')->unique()->constrained('muestras')->cascadeOnDelete();
            $table->string('examen', 500)->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('total_pagado', 10, 2)->default(0); // Recalculado desde pagos_cobro
            $table->string('estado', 20)->default('Pendiente'); // Pendiente, Parcial, Pagado, Exonerado
            $table->boolean('exonerado')->default(false);
            $table->text('observacion')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobros');
    }
};
