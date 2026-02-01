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
        Schema::create('plantilla_insumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_formulario_id')->constrained('plantillas_formulario')->onDelete('cascade');
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('cascade');
            $table->decimal('cantidad_requerida', 10, 2);
            $table->timestamps();

            // Evitar duplicados
            $table->unique(['plantilla_formulario_id', 'insumo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantilla_insumos');
    }
};
