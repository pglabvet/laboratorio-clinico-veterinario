<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla de auditorías del sistema.
 *
 * Esta tabla registra automáticamente todas las acciones que los usuarios
 * realizan sobre las entidades del sistema (crear, actualizar, eliminar).
 * Es fundamental para trazabilidad y seguridad.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();

            // ¿Quién hizo la acción? (puede ser null si fue una acción del sistema)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // ¿Qué acción realizó? (crear, actualizar, eliminar)
            $table->string('accion', 20);

            // ¿Sobre qué entidad? (nombre del modelo, ej: "Muestra", "Insumo")
            $table->string('entidad');

            // ¿Cuál registro específico? (ID del registro afectado)
            $table->unsignedBigInteger('entidad_id')->nullable();

            // Descripción legible de la acción (ej: "Creó la muestra EQ-AA0001")
            $table->string('descripcion');

            // Valores anteriores (JSON) - para actualizaciones y eliminaciones
            $table->jsonb('valores_anteriores')->nullable();

            // Valores nuevos (JSON) - para creaciones y actualizaciones
            $table->jsonb('valores_nuevos')->nullable();

            // Dirección IP desde donde se realizó la acción
            $table->string('ip', 45)->nullable();

            // Agente de usuario (navegador)
            $table->string('user_agent')->nullable();

            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index('user_id');
            $table->index('accion');
            $table->index('entidad');
            $table->index('created_at');
            $table->index(['entidad', 'entidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
