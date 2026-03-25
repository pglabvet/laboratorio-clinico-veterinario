<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lotes_inventario', function (Blueprint $table) {
            $table->decimal('costo_unitario', 14, 6)->change();
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->decimal('costo_unitario', 14, 6)->change();
        });
    }

    public function down(): void
    {
        Schema::table('lotes_inventario', function (Blueprint $table) {
            $table->decimal('costo_unitario', 12, 4)->change();
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->decimal('costo_unitario', 12, 4)->change();
        });
    }
};
