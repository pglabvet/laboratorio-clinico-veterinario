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
            // ========================================
            // DASHBOARD - TODOS LOS PERMISOS
            // ========================================
            
            // Tarjetas de estadísticas principales
            'ver-dashboard',                      // Ver tarjetas de estadísticas (Muestras/Análisis Pendientes, etc.)
            'ver-estadisticas-completas',         // Ver estadísticas de TODAS las sucursales (solo Administrador)
            
            // Filtros del dashboard
            'ver-filtros-dashboard',              // Mostrar filtros de fecha y sucursal en dashboard
            'filtrar-por-sucursal',               // Poder filtrar datos por sucursal específica (solo Administrador)
            
            // Gráficos y reportes
            'ver-graficos-estadisticas',          // Ver gráficos de distribución y análisis (todos los roles pueden analizar)
            
            // Secciones de información
            'ver-actividad-reciente',             // Ver últimas acciones del sistema
            'ver-alertas-inventario',             // Ver alertas de stock bajo
            'ver-ultimas-muestras',               // Ver listado de muestras recientes
            
            // Acciones rápidas (botones del dashboard)
            'ver-acciones-rapidas',               // Mostrar la sección completa de "Acciones Rápidas"
            'registrar-muestras',                 // Botón "Registrar Nueva Muestra"
            'escanear-muestras',                  // Botón "Escanear Código"
            
            // ========================================
            // MÓDULOS - PERMISOS DE GESTIÓN
            // ========================================
            
            // Usuarios
            'ver-usuarios',
            'crear-usuarios',
            'editar-usuarios',
            'eliminar-usuarios',
            'gestionar-usuarios', // Permiso compuesto para CRUD completo
            
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
        // Dashboard: Ve estadísticas, puede registrar y escanear muestras
        $veterinarioRole = Role::firstOrCreate(['name' => 'Veterinario', 'guard_name' => 'web']);
        $veterinarioRole->syncPermissions([
            // Dashboard
            'ver-dashboard',
            'ver-actividad-reciente',
            'ver-acciones-rapidas',
            'registrar-muestras',
            'escanear-muestras',
            // Módulos
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
        // Dashboard: Ve estadísticas, alertas de inventario, solo puede escanear (no registrar)
        $laboratoristaRole = Role::firstOrCreate(['name' => 'Laboratorista', 'guard_name' => 'web']);
        $laboratoristaRole->syncPermissions([
            // Dashboard
            'ver-dashboard',
            'ver-actividad-reciente',
            'ver-alertas-inventario',
            'ver-acciones-rapidas',
            'escanear-muestras',
            // Módulos
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
        // Dashboard: SOLO ve gráficos con filtros (para analizar rendimiento)
        //           NO ve tarjetas de estadísticas (debe procesar todo lo pendiente sin filtrar)
        $bioquimicoRole = Role::firstOrCreate(['name' => 'Bioquímico', 'guard_name' => 'web']);
        $bioquimicoRole->syncPermissions([
            // Dashboard
            'ver-graficos-estadisticas',
            'ver-actividad-reciente',
            'ver-ultimas-muestras',
            'ver-acciones-rapidas',
            'registrar-muestras',
            'escanear-muestras',
            // Módulos
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
        // Dashboard: Ve estadísticas, solo puede registrar muestras (no escanear)
        $recepcionistaRole = Role::firstOrCreate(['name' => 'Recepcionista', 'guard_name' => 'web']);
        $recepcionistaRole->syncPermissions([
            // Dashboard
            'ver-dashboard',
            'ver-actividad-reciente',
            'ver-acciones-rapidas',
            'registrar-muestras',
            // Módulos
            'ver-veterinarias',
            'ver-muestras',
            'crear-muestras',
            'ver-analisis',
            'ver-resultados',
        ]);

        // Crear rol de Usuario básico (solo lectura)
        // Dashboard: Ve estadísticas y actividad reciente, sin acciones
        $usuarioRole = Role::firstOrCreate(['name' => 'Usuario', 'guard_name' => 'web']);
        $usuarioRole->syncPermissions([
            // Dashboard
            'ver-dashboard',
            'ver-actividad-reciente',
            // Módulos
            'ver-muestras',
            'ver-analisis',
            'ver-resultados',
        ]);

        $this->command->info('Roles y permisos creados exitosamente.');
    }
}
