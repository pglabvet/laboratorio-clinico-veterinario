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
        Schema::create('muestras', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_muestra')->unique();
            $table->string('paciente_nombre');
            $table->foreignId('especie_id')->constrained('especies')->onDelete('cascade');
            $table->string('raza')->nullable();
            $table->string('edad')->nullable();
            $table->string('sexo')->nullable();
            $table->string('color')->nullable();
            $table->string('propietario_nombre');
            $table->foreignId('veterinaria_id')->constrained('veterinarias')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->string('tipo_muestra');
            $table->timestamp('fecha_recepcion');
            $table->string('estado');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muestras');
    }
};
