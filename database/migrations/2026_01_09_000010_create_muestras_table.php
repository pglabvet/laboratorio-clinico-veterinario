<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->enum('estado', ['Pendiente', 'En proceso', 'Completado', 'Enviado'])->default('Pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Índices de rendimiento para filtros y ordenamiento
            $table->index('estado');
            $table->index('fecha_recepcion');
            $table->index(['sucursal_id', 'estado']); // Filtro compuesto más frecuente
        });

        // Índices GIN con pg_trgm para búsqueda rápida con ILIKE '%texto%' (solo PostgreSQL)
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
            DB::statement('CREATE INDEX muestras_codigo_muestra_trgm ON muestras USING gin (codigo_muestra gin_trgm_ops)');
            DB::statement('CREATE INDEX muestras_paciente_nombre_trgm ON muestras USING gin (paciente_nombre gin_trgm_ops)');
            DB::statement('CREATE INDEX muestras_propietario_nombre_trgm ON muestras USING gin (propietario_nombre gin_trgm_ops)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muestras');
    }
};
