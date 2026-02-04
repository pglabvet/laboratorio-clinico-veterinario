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
        Schema::create('analisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muestra_id')->constrained('muestras')->onDelete('cascade');
            $table->foreignId('tipo_analisis_id')->constrained('tipos_analisis')->onDelete('cascade');
            $table->foreignId('bioquimico_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('aprobador_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('estado', ['Pendiente', 'En revision', 'Aprobado', 'Enviado'])->default('Pendiente');
            $table->text('observaciones_bioquimico')->nullable();
            $table->text('observaciones_aprobador')->nullable();
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_finalizacion')->nullable();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analisis');
    }
};
