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
        Schema::table('resultados', function (Blueprint $table) {
            if (!Schema::hasColumn('resultados', 'repeticiones')) {
                $table->unsignedInteger('repeticiones')->default(1)->after('fuera_rango');
            }
            if (!Schema::hasColumn('resultados', 'repeticiones_procesadas')) {
                $table->unsignedInteger('repeticiones_procesadas')->default(0)->after('repeticiones');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resultados', function (Blueprint $table) {
            $table->dropColumn(['repeticiones', 'repeticiones_procesadas']);
        });
    }
};
