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
        Schema::create('resultados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->onDelete('cascade');
            $table->foreignId('parametro_id')->nullable()->constrained('parametros_analisis')->onDelete('cascade');
            $table->string('tipo')->default('parametro'); // parametro, antibiograma, lista_items, etc.
            $table->json('valor'); // Almacena valores simples o estructuras complejas como arrays
            $table->boolean('fuera_rango')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultados');
    }
};
