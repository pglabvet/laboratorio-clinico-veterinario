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
        Schema::table('plantillas_formulario', function (Blueprint $table) {
            $table->integer('version')->default(1)->after('activo');
            $table->foreignId('plantilla_base_id')->nullable()->after('version')
                ->constrained('plantillas_formulario')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plantillas_formulario', function (Blueprint $table) {
            $table->dropForeign(['plantilla_base_id']);
            $table->dropColumn(['version', 'plantilla_base_id']);
        });
    }
};
