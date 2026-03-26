<?php

use App\Http\Controllers\KardexExportController;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

// Ruta pública corta para descarga de PDF por código corto
Route::get('/r/{codigo}', [PdfController::class, 'descargarPorCodigoCorto'])
    ->middleware('throttle:10,1')
    ->name('pdf.descargar.corto');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'can:ver-dashboard'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/sucursales', 'sucursales.index')
        ->middleware('can:ver-sucursales')
        ->name('sucursales.index');

    Route::view('/veterinarias', 'veterinarias.index')
        ->middleware('can:ver-veterinarias')
        ->name('veterinarias.index');

    Route::view('/tipos-analisis', 'tipos-analisis.index')
        ->middleware('can:ver-tipos-analisis')
        ->name('tipos-analisis.index');

    Route::view('/muestras', 'muestras.index')
        ->middleware('can:ver-muestras')
        ->name('muestras.index');

    Route::view('/muestras/crear', 'muestras.crear')
        ->middleware('can:crear-muestras')
        ->name('muestras.crear');

    Route::view('/muestras/editar/{id}', 'muestras.editar')
        ->middleware('can:editar-muestras')
        ->name('muestras.editar');

    Route::view('/muestras/escanear', 'muestras.escanear')
        ->middleware('can:escanear-muestras')
        ->name('muestras.escanear');

    // Ruta para imprimir etiqueta de muestra
    Route::get('/muestras/{muestra}/etiqueta', function (\App\Models\Muestra $muestra) {
        return response()
            ->view('components.etiqueta-muestra', ['muestra' => $muestra])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
    )->name('muestras.etiqueta');

    // Ruta

    Route::view('/especies', 'especies.index')
        ->middleware('can:ver-especies')
        ->name('especies.index');

    Route::view('/unidades-medida', 'unidades-medida.index')
        ->middleware('can:ver-unidades-medida')
        ->name('unidades-medida.index');

    Route::view('/insumos', 'insumos.index')
        ->middleware('can:ver-insumos')
        ->name('insumos.index');

    Route::view('/categorias-insumo', 'categorias-insumo.index')
        ->middleware('can:ver-categorias-insumo')
        ->name('categorias-insumo.index');

    // Rutas de inventario
    Route::view('/inventario/entradas', 'inventario.entradas')
        ->middleware('can:ver-registrar-entrada')
        ->name('inventario.entradas');

    Route::get('/inventario/salidas', \App\Livewire\Inventario\RegistrarSalida::class)
        ->middleware('can:ver-salidas-manuales')
        ->name('inventario.salidas');

    Route::get('/inventario/historial', \App\Livewire\Inventario\HistorialMovimientos::class)
        ->middleware('can:ver-historial-inventario')
        ->name('inventario.historial');

    Route::view('/inventario/kardex', 'inventario.kardex')
        ->middleware('can:ver-kardex-peps')
        ->name('inventario.kardex');

    Route::get('/inventario/kardex/exportar/excel', [KardexExportController::class, 'exportarExcel'])
        ->middleware('can:ver-kardex-peps')
        ->name('inventario.kardex.excel');

    Route::get('/inventario/kardex/exportar/pdf', [KardexExportController::class, 'exportarPdf'])
        ->middleware('can:ver-kardex-peps')
        ->name('inventario.kardex.pdf');

    Route::get('/usuarios', \App\Livewire\Usuarios\GestionarUsuarios::class)
        ->middleware('can:ver-usuarios')
        ->name('usuarios.index');

    Route::view('/roles', 'roles.index')
        ->middleware('can:ver-roles')
        ->name('roles.index');

    // Permisos deshabilitado - se gestionan desde el seeder
    // Route::view('/permisos', 'permisos.index')
    //     ->name('permisos.index');

    // Constructor de formularios dinámicos (Admin)
    Route::get('/plantillas', \App\Livewire\Plantillas\ListarPlantillas::class)
        ->middleware('can:ver-plantillas')
        ->name('plantillas.index');

    Route::get('/plantillas/crear', \App\Livewire\Plantillas\GestionarPlantillas::class)
        ->middleware('can:crear-plantillas')
        ->name('plantillas.crear');

    Route::get('/plantillas/{plantilla}/editar', \App\Livewire\Plantillas\GestionarPlantillas::class)
        ->middleware('can:editar-plantillas')
        ->name('plantillas.editar');

    // Capturar resultados de análisis (Bioquímico)
    Route::get('/analisis/{analisisId}/resultados', \App\Livewire\Resultados\CapturarResultados::class)
        ->middleware('can:ingresar-resultados')
        ->name('analisis.capturar-resultados');

    // Editar resultados de análisis (Admin/Bioquímico)
    Route::get('/analisis/{analisisId}/editar', \App\Livewire\Resultados\CapturarResultados::class)
        ->middleware('can:ingresar-resultados')
        ->name('analisis.editar');

    //  PREVIEW: Vista de captura de resultados (frontend temporal)
    Route::view('/resultados/preview', 'livewire.resultados.capturar-resultados')
        ->name('resultados.preview');

    // Revisar análisis finalizados (Admin)
    Route::get('/analisis/revisar', \App\Livewire\Analisis\RevisarAnalisis::class)
        ->middleware('can:ver-analisis')
        ->name('analisis.revisar');

    // Ver detalles de análisis (Admin)
    Route::get('/analisis/{analisisId}/ver', \App\Livewire\Analisis\VerAnalisis::class)
        ->middleware('can:ver-analisis')
        ->name('analisis.ver');

    // Ver PDF de análisis en el navegador (inline)
    Route::get('/analisis/{analisisId}/ver-pdf', [PdfController::class, 'ver'])
        ->name('analisis.ver-pdf');

    // Descargar PDF de análisis aprobado
    Route::get('/analisis/{analisisId}/pdf', [PdfController::class, 'descargar'])
        ->name('analisis.pdf');

    // Ver PDF limpio en el navegador (sin branding)
    Route::get('/analisis/{analisisId}/ver-pdf-limpio', [PdfController::class, 'verLimpio'])
        ->name('analisis.ver-pdf-limpio');

    // Descargar PDF limpio (sin branding)
    Route::get('/analisis/{analisisId}/pdf-limpio', [PdfController::class, 'descargarLimpio'])
        ->name('analisis.pdf-limpio');

    // Guardar gráfica de análisis
    Route::post('/analisis/{analisisId}/guardar-grafica', [PdfController::class, 'guardarGrafica'])
        ->name('analisis.guardar-grafica');

    // Muestras Rechazadas
    Route::get('/muestras-rechazadas', \App\Livewire\MuestrasRechazadas\ListarMuestrasRechazadas::class)
        ->middleware('can:ver-muestras-rechazadas')
        ->name('muestras-rechazadas.index');

    Route::get('/muestras-rechazadas/crear', \App\Livewire\MuestrasRechazadas\RegistrarMuestraRechazada::class)
        ->middleware('can:crear-muestras-rechazadas')
        ->name('muestras-rechazadas.crear');

    // Auditorías del sistema
    Route::get('/auditorias', \App\Livewire\Auditorias\ListarAuditorias::class)
        ->middleware('can:ver-auditorias')
        ->name('auditorias.index');

    // Guía del Sistema (accesible para todos los usuarios autenticados)
    Route::get('/guia-del-sistema', \App\Livewire\GuiaDelSistema::class)
        ->name('guia.index');
});

require __DIR__.'/settings.php';
