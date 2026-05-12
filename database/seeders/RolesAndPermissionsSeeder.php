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
            'ver-dashboard', // Ver tarjetas de estadísticas (Muestras/Análisis Pendientes, etc.)
            'ver-estadisticas-completas', // Ver estadísticas de TODAS las sucursales (solo Administrador)

            // Filtros del dashboard
            'ver-filtros-dashboard', // Mostrar filtros de fecha y sucursal en dashboard
            'filtrar-por-sucursal', // Poder filtrar datos por sucursal específica (solo Administrador)

            // Gráficos y reportes
            'ver-graficos-estadisticas', // Ver gráficos de distribución y análisis (todos los roles pueden analizar)

            // Secciones de información
            'ver-actividad-reciente', // Ver últimas acciones del sistema
            'ver-alertas-inventario', // Ver alertas de stock bajo
            'ver-ultimas-muestras', // Ver listado de muestras recientes

            // Acciones rápidas (botones del dashboard)
            'ver-acciones-rapidas', // Mostrar la sección completa de "Acciones Rápidas"
            'registrar-muestras', // Botón "Registrar Nueva Muestra"
            'escanear-muestras', // Acceso a página "Escanear Muestra" (sidebar + ruta)
            'ver-escanear-dashboard', // Card "Escanear Código" en el dashboard (acciones rápidas)

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
            'guardar-sucursal',

            // Veterinarias
            'ver-veterinarias',
            'crear-veterinarias',
            'editar-veterinarias',
            'eliminar-veterinarias',
            'guardar-veterinaria',

            // Muestras
            'ver-muestras',
            'crear-muestras',
            'editar-muestras',
            'eliminar-muestras',
            'enviar-resultados-muestra',
            'ver-codigo-barras-muestra',
            'filtro-de-sucursal-muestra',
            'exportar-muestras',

            // Vista general del sistema
            'vista-general-sistema', // Ver datos de TODAS las sucursales (sin este permiso, solo ve su sucursal)

            // Análisis
            'ver-analisis',
            'aprobar-analisis',
            'rechazar-analisis',
            'actualizar-datos-analisis',
            'descargar-pdf-analisis',

            // Resultados
            'ver-resultados',
            'registrar-resultados',
            'ingresar-resultados',
            'guardar-borrador-resultados',

            // Inventario
            'ver-historial-inventario',
            'ver-registrar-entrada',
            'ver-salidas-manuales',
            'ver-kardex-peps',

            // Especies
            'ver-especies',
            'crear-especies',
            'editar-especies',
            'eliminar-especies',
            'guardar-especie',

            // Tipos de Análisis
            'ver-tipos-analisis',
            'crear-tipos-analisis',
            'editar-tipos-analisis',
            'eliminar-tipos-analisis',
            'guardar-tipo-analisis',

            // Unidades de Medida
            'ver-unidades-medida',
            'crear-unidades-medida',
            'editar-unidades-medida',
            'eliminar-unidades-medida',
            'guardar-unidad-medida',

            // Insumos
            'ver-insumos',
            'crear-insumos',
            'editar-insumos',
            'eliminar-insumos',
            'guardar-insumo',

            // Categorías de Insumo
            'ver-categorias-insumo',
            'crear-categorias-insumo',
            'editar-categorias-insumo',
            'eliminar-categorias-insumo',
            'guardar-categoria-insumo',

            // Plantillas de Formularios
            'ver-plantillas',
            'crear-plantillas',
            'editar-plantillas',
            'eliminar-plantillas',
            'duplicar-plantilla',

            // Mostrar detalle (botón ver/ojo)
            'mostrar-detalle-muestra',
            'mostrar-detalle-sucursal',
            'mostrar-detalle-veterinaria',
            'mostrar-detalle-especie',
            'mostrar-detalle-tipo-analisis',
            'mostrar-detalle-permiso',
            'mostrar-detalle-rol',

            // Auditorías
            'ver-auditorias',

            // Muestras Rechazadas
            'ver-muestras-rechazadas',
            'crear-muestras-rechazadas',
            'editar-muestras-rechazadas',
            'eliminar-muestras-rechazadas',
            'mostrar-detalle-muestra-rechazada',
            'exportar-muestras-rechazadas',

            // Ayuda / Guía del Sistema
            'ver-ayuda',

            // Roles y Permisos
            'ver-roles',
            'crear-roles',
            'editar-roles',
            'eliminar-roles',
            'guardar-rol',
            'ver-permisos',
            'crear-permisos',
            'editar-permisos',
            'eliminar-permisos',
            'guardar-permiso',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Crear rol de Administrador con todos los permisos EXCEPTO ver-escanear-dashboard
        $adminRole = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $adminRole->syncPermissions(
            Permission::where('name', '!=', 'ver-escanear-dashboard')->get()
        );

        // Crear rol de Bioquímico
        $bioquimicoRole = Role::firstOrCreate(['name' => 'Bioquímico', 'guard_name' => 'web']);
        $bioquimicoRole->syncPermissions([
            // Dashboard
            'ver-dashboard',
            'ver-graficos-estadisticas',
            'ver-actividad-reciente',
            'ver-ultimas-muestras',
            'ver-acciones-rapidas',
            'registrar-muestras',
            'escanear-muestras',
            'ver-escanear-dashboard',
            // Módulos
            'ver-especies',
            'ver-tipos-analisis',
            'ver-unidades-medida',
            'ver-muestras',
            'crear-muestras',
            'editar-muestras',
            'ver-analisis',
            'ver-resultados',
            'registrar-resultados',
            'ingresar-resultados',
            'guardar-borrador-resultados',
            'ver-plantillas',
            'ver-insumos',
            'ver-categorias-insumo',
            'ver-historial-inventario',
            'ver-registrar-entrada',
            'ver-salidas-manuales',
            'ver-kardex-peps',
            // Análisis extra
            'aprobar-analisis',
            'rechazar-analisis',
            'actualizar-datos-analisis',
            'descargar-pdf-analisis',
            // Mostrar detalle
            'mostrar-detalle-muestra',
            'mostrar-detalle-especie',
            'mostrar-detalle-tipo-analisis',
            // Muestras extra
            'enviar-resultados-muestra',
            'ver-codigo-barras-muestra',
            // Muestras rechazadas
            'ver-muestras-rechazadas',
            'crear-muestras-rechazadas',
            'editar-muestras-rechazadas',
            'eliminar-muestras-rechazadas',
            'mostrar-detalle-muestra-rechazada',
        ]);

        $this->command->info('Roles y permisos creados exitosamente.');
    }
}
