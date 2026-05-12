<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        // Migrar teléfonos existentes a la tabla nueva antes de eliminar la columna
        $veterinarias = DB::table('veterinarias')
            ->whereNotNull('telefono')
            ->where('telefono', '!=', '')
            ->get(['id', 'telefono']);

        foreach ($veterinarias as $vet) {
            // Solo insertar si no existe ya un registro para esta veterinaria
            $existe = DB::table('veterinaria_telefonos')
                ->where('veterinaria_id', $vet->id)
                ->where('telefono', $vet->telefono)
                ->exists();

            if (! $existe) {
                DB::table('veterinaria_telefonos')->insert([
                    'veterinaria_id' => $vet->id,
                    'telefono' => $vet->telefono,
                    'nombre_contacto' => null,
                    'es_principal' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

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

        // Restaurar el teléfono principal de vuelta a la columna
        $telefonos = DB::table('veterinaria_telefonos')
            ->where('es_principal', true)
            ->get(['veterinaria_id', 'telefono']);

        foreach ($telefonos as $tel) {
            DB::table('veterinarias')
                ->where('id', $tel->veterinaria_id)
                ->update(['telefono' => $tel->telefono]);
        }

        Schema::table('veterinaria_telefonos', function (Blueprint $table) {
            $table->dropColumn('es_principal');
            $table->dropColumn('nombre_contacto');
        });
    }
};

