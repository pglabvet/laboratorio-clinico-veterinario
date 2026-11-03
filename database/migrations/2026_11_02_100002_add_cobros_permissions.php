<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Permisos del módulo Cobros.
     *
     * Solo se crean; no se asignan a ningún rol para no modificar la
     * configuración existente. Se asignan desde la gestión de Roles.
     */
    private array $permisos = [
        'ver-cobros',                // Acceso al módulo (sidebar + ruta)
        'mostrar-detalle-cobro',     // Botón ver detalle / historial de pagos
        'registrar-pago-cobro',      // Botón registrar pago
        'anular-pago-cobro',         // Anular un pago desde el detalle
        'editar-cobros',             // Editar total, examen y observación
        'eliminar-cobros',           // Eliminar cobro
        'exonerar-cobro',            // Marcar / quitar exoneración
        'filtro-de-sucursal-cobro',  // Filtro de sucursal en la tabla
        'registrar-cobro-muestra',   // Formulario flotante al registrar una muestra
        'exportar-cobros',           // Botón exportar PDF / Excel
    ];

    public function up(): void
    {
        foreach ($this->permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::whereIn('name', $this->permisos)->where('guard_name', 'web')->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
