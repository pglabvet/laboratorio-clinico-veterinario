<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            // Usuarios
            'ver-usuarios',
            'crear-usuarios',
            'editar-usuarios',
            'eliminar-usuarios',
            
            // Sucursales
            'ver-sucursales',
            'crear-sucursales',
            'editar-sucursales',
            'eliminar-sucursales',
            
            // Veterinarias
            'ver-veterinarias',
            'crear-veterinarias',
            'editar-veterinarias',
            'eliminar-veterinarias',
            
            // Muestras
            'ver-muestras',
            'crear-muestras',
            'editar-muestras',
            'eliminar-muestras',
            
            // Análisis
            'ver-analisis',
            'crear-analisis',
            'editar-analisis',
            'eliminar-analisis',
            
            // Resultados
            'ver-resultados',
            'crear-resultados',
            'editar-resultados',
            'eliminar-resultados',
            
            // Inventario
            'ver-inventario',
            'crear-inventario',
            'editar-inventario',
            'eliminar-inventario',
            
            // Roles y Permisos
            'ver-roles',
            'crear-roles',
            'editar-roles',
            'eliminar-roles',
            'ver-permisos',
            'crear-permisos',
            'editar-permisos',
            'eliminar-permisos',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Crear rol de Administrador con todos los permisos
        $adminRole = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        // Crear rol de Veterinario con permisos limitados
        $veterinarioRole = Role::firstOrCreate(['name' => 'Veterinario', 'guard_name' => 'web']);
        $veterinarioRole->syncPermissions([
            'ver-veterinarias',
            'ver-muestras',
            'crear-muestras',
            'editar-muestras',
            'ver-analisis',
            'crear-analisis',
            'editar-analisis',
            'ver-resultados',
            'crear-resultados',
            'editar-resultados',
        ]);

        // Crear rol de Laboratorista
        $laboratoristaRole = Role::firstOrCreate(['name' => 'Laboratorista', 'guard_name' => 'web']);
        $laboratoristaRole->syncPermissions([
            'ver-muestras',
            'editar-muestras',
            'ver-analisis',
            'crear-analisis',
            'editar-analisis',
            'ver-resultados',
            'crear-resultados',
            'editar-resultados',
            'ver-inventario',
            'editar-inventario',
        ]);

        // Crear rol de Bioquímico
        $bioquimicoRole = Role::firstOrCreate(['name' => 'Bioquímico', 'guard_name' => 'web']);
        $bioquimicoRole->syncPermissions([
            'ver-muestras',
            'crear-muestras',
            'editar-muestras',
            'ver-analisis',
            'crear-analisis',
            'editar-analisis',
            'eliminar-analisis',
            'ver-resultados',
            'crear-resultados',
            'editar-resultados',
            'eliminar-resultados',
            'ver-inventario',
            'crear-inventario',
            'editar-inventario',
        ]);

        // Crear rol de Recepcionista
        $recepcionistaRole = Role::firstOrCreate(['name' => 'Recepcionista', 'guard_name' => 'web']);
        $recepcionistaRole->syncPermissions([
            'ver-veterinarias',
            'ver-muestras',
            'crear-muestras',
            'ver-analisis',
            'ver-resultados',
        ]);

        // Crear rol de Usuario básico (solo lectura)
        $usuarioRole = Role::firstOrCreate(['name' => 'Usuario', 'guard_name' => 'web']);
        $usuarioRole->syncPermissions([
            'ver-muestras',
            'ver-analisis',
            'ver-resultados',
        ]);

        $this->command->info('Roles y permisos creados exitosamente.');
    }
}
