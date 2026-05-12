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
        Schema::create('muestras_rechazadas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_muestra')->unique();
            $table->string('paciente_nombre');
            $table->foreignId('especie_id')->nullable()->constrained('especies')->nullOnDelete();
            $table->string('raza', 100)->nullable();
            $table->string('edad', 50);
            $table->enum('sexo', ['M', 'H'])->default('M');
            $table->string('propietario_nombre');
            $table->foreignId('veterinaria_id')->constrained('veterinarias')->restrictOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->restrictOnDelete();
            $table->string('tipo_muestra', 100);
            $table->string('motivo_rechazo');
            $table->text('observaciones')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_rechazo')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muestras_rechazadas');
    }
};
