<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Permiso para la pestaña Historial en el detalle de la muestra.
     *
     * Solo se crea; no se asigna a ningún rol para no modificar la
     * configuración existente. Se asigna desde la gestión de Roles.
     */
    public function up(): void
    {
        Permission::firstOrCreate(['name' => 'ver-historial-muestra', 'guard_name' => 'web']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::where('name', 'ver-historial-muestra')->where('guard_name', 'web')->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
