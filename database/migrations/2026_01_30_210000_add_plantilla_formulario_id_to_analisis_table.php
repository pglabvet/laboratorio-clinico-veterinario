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
        Schema::table('analisis', function (Blueprint $table) {
            $table->foreignId('plantilla_formulario_id')
                ->nullable()
                ->after('tipo_analisis_id')
                ->constrained('plantillas_formulario')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analisis', function (Blueprint $table) {
            $table->dropForeign(['plantilla_formulario_id']);
            $table->dropColumn('plantilla_formulario_id');
        });
    }
};
