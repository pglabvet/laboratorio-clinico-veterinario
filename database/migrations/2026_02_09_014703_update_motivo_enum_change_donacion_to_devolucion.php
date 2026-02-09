<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Desactivar transacciones automáticas para esta migración
    public $withinTransaction = false;
    
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Solo ejecutar en PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Verificar si el valor 'DONACION' existe en el enum
            $enumValues = DB::select("SELECT enumlabel FROM pg_enum WHERE enumtypid = (SELECT oid FROM pg_type WHERE typname = 'motivo_enum')");
            $hasdonacion = collect($enumValues)->pluck('enumlabel')->contains('DONACION');
            
            // Solo proceder si el valor DONACION existe (significa que es una BD antigua)
            if ($hasdonacion) {
                // Paso 1: Agregar el nuevo valor DEVOLUCION al enum existente
                DB::statement("ALTER TYPE motivo_enum ADD VALUE IF NOT EXISTS 'DEVOLUCION'");
                
                // Paso 2: Actualizar todos los registros que tienen DONACION a DEVOLUCION
                DB::statement("UPDATE movimientos_inventario SET motivo = 'DEVOLUCION' WHERE motivo = 'DONACION'");
                
                // Paso 3: Recrear el enum sin DONACION
                // Convertir temporalmente a VARCHAR
                DB::statement("ALTER TABLE movimientos_inventario ALTER COLUMN motivo TYPE VARCHAR(255)");
                
                // Eliminar el enum viejo
                DB::statement("DROP TYPE IF EXISTS motivo_enum");
                
                // Crear el enum nuevo sin DONACION
                DB::statement("CREATE TYPE motivo_enum AS ENUM ('MERMA', 'VENCIMIENTO', 'USO_EXTRAORDINARIO', 'CONSUMO_ANALISIS', 'AJUSTE_INVENTARIO', 'COMPRA', 'DEVOLUCION', 'OTRO')");
                
                // Convertir de vuelta a enum
                DB::statement("ALTER TABLE movimientos_inventario ALTER COLUMN motivo TYPE motivo_enum USING motivo::motivo_enum");
            }
            // Si no tiene DONACION, significa que es una BD fresca y no necesita hacer nada
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Solo ejecutar en PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Convertir temporalmente a VARCHAR
            DB::statement("ALTER TABLE movimientos_inventario ALTER COLUMN motivo TYPE VARCHAR(255)");
            
            // Actualizar de vuelta a DONACION
            DB::statement("UPDATE movimientos_inventario SET motivo = 'DONACION' WHERE motivo = 'DEVOLUCION'");
            
            // Eliminar el enum
            DB::statement("DROP TYPE IF EXISTS motivo_enum");
            
            // Recrear el enum con DONACION
            DB::statement("CREATE TYPE motivo_enum AS ENUM ('MERMA', 'VENCIMIENTO', 'USO_EXTRAORDINARIO', 'CONSUMO_ANALISIS', 'AJUSTE_INVENTARIO', 'COMPRA', 'DONACION', 'OTRO')");
            
            // Convertir de vuelta a enum
            DB::statement("ALTER TABLE movimientos_inventario ALTER COLUMN motivo TYPE motivo_enum USING motivo::motivo_enum");
        }
    }
};
