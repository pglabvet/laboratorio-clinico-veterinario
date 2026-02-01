<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Solo ejecutar en PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Crear tipos ENUM personalizados en PostgreSQL
            DB::statement("CREATE TYPE tipo_movimiento_enum AS ENUM ('ENTRADA', 'SALIDA_MANUAL', 'CONSUMO_ANALISIS', 'AJUSTE')");
            DB::statement("CREATE TYPE motivo_enum AS ENUM ('MERMA', 'VENCIMIENTO', 'USO_EXTRAORDINARIO', 'CONSUMO_ANALISIS', 'AJUSTE_INVENTARIO', 'COMPRA', 'DONACION', 'OTRO')");
            
            // Convertir columnas a tipo ENUM
            DB::statement("ALTER TABLE movimientos_inventario 
                ALTER COLUMN tipo_movimiento TYPE tipo_movimiento_enum 
                USING tipo_movimiento::tipo_movimiento_enum");
            
            DB::statement("ALTER TABLE movimientos_inventario 
                ALTER COLUMN motivo TYPE motivo_enum 
                USING motivo::motivo_enum");
        }
        // Para SQLite y MySQL, las columnas VARCHAR ya funcionan como restricciones en el modelo
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Solo ejecutar en PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Revertir a VARCHAR
            DB::statement("ALTER TABLE movimientos_inventario 
                ALTER COLUMN tipo_movimiento TYPE VARCHAR(255)");
            
            DB::statement("ALTER TABLE movimientos_inventario 
                ALTER COLUMN motivo TYPE VARCHAR(255)");
            
            // Eliminar tipos ENUM
            DB::statement("DROP TYPE IF EXISTS tipo_movimiento_enum");
            DB::statement("DROP TYPE IF EXISTS motivo_enum");
        }
    }
};
