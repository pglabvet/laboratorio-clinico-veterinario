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
        // Agregar columnas a veterinaria_telefonos
        Schema::table('veterinaria_telefonos', function (Blueprint $table) {
            $table->string('nombre_contacto')->nullable()->after('telefono');
            $table->boolean('es_principal')->default(false)->after('nombre_contacto');
        });

        // Eliminar telefono de veterinarias
        Schema::table('veterinarias', function (Blueprint $table) {
            $table->dropColumn('telefono');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('veterinarias', function (Blueprint $table) {
            $table->string('telefono')->after('responsable');
        });

        Schema::table('veterinaria_telefonos', function (Blueprint $table) {
            $table->dropColumn('es_principal');
            $table->dropColumn('nombre_contacto');
        });
    }
};
