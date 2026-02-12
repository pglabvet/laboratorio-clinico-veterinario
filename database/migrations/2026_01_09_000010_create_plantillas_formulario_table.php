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
        Schema::create('plantillas_formulario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_analisis_id')->nullable()->constrained('tipos_analisis')->onDelete('cascade');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->json('componentes');
            $table->boolean('activo')->default(true);
            $table->integer('version')->default(1);
            $table->foreignId('plantilla_base_id')->nullable()->constrained('plantillas_formulario')->onDelete('set null');
            $table->foreignId('creado_por')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantillas_formulario');
    }
};
