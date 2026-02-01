<?php

use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/sucursales', 'sucursales.index')
        ->name('sucursales.index');
    
    Route::view('/veterinarias', 'veterinarias.index')
        ->name('veterinarias.index');
    
    Route::view('/tipos-analisis', 'tipos-analisis.index')
        ->name('tipos-analisis.index');

    Route::view('/muestras', 'muestras.index')
        ->name('muestras.index');
    
    Route::view('/muestras/crear', 'muestras.crear')
        ->name('muestras.crear');
    
    Route::view('/muestras/editar/{id}', 'muestras.editar')
        ->name('muestras.editar');
    
    Route::view('/muestras/escanear', 'muestras.escanear')
        ->name('muestras.escanear');
    
    // Ruta para imprimir etiqueta de muestra
    Route::get('/muestras/{muestra}/etiqueta', function (\App\Models\Muestra $muestra) {
        return response()
            ->view('components.etiqueta-muestra', ['muestra' => $muestra])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    })->name('muestras.etiqueta');

    // Ruta 
   
    Route::view('/especies', 'especies.index')
        ->name('especies.index');

    
    Route::view('/tipos-analisis', 'tipos-analisis.index')
        ->name('tipos-analisis.index');
    
    Route::view('/unidades-medida', 'unidades-medida.index')
        ->name('unidades-medida.index');
    
    Route::view('/insumos', 'insumos.index')
        ->name('insumos.index');
    
    Route::view('/categorias-insumo', 'categorias-insumo.index')
        ->name('categorias-insumo.index');
    
    // Rutas de inventario
    Route::view('/inventario/entradas', 'inventario.entradas')
        ->name('inventario.entradas');
    
    Route::get('/inventario/salidas', \App\Livewire\Inventario\RegistrarSalida::class)
        ->name('inventario.salidas');
    
    Route::get('/inventario/historial', \App\Livewire\Inventario\HistorialMovimientos::class)
        ->name('inventario.historial');
    
    Route::view('/roles', 'roles.index')
        ->name('roles.index');
    
    Route::view('/permisos', 'permisos.index')
        ->name('permisos.index');
    
    // Constructor de formularios dinámicos (Admin)
    Route::get('/plantillas', \App\Livewire\Plantillas\ListarPlantillas::class)
        ->name('plantillas.index');
    
    Route::get('/plantillas/crear', \App\Livewire\Plantillas\GestionarPlantillas::class)
        ->name('plantillas.crear');
    
    Route::get('/plantillas/{plantilla}/editar', \App\Livewire\Plantillas\GestionarPlantillas::class)
        ->name('plantillas.editar');
    
    // Constructor de formularios dinámicos (Admin) - Ruta legacy
    Route::get('/formularios/constructor', \App\Livewire\Plantillas\GestionarPlantillas::class)
        ->name('formularios.constructor');
    
    // Lista de plantillas disponibles (Bioquímico)
    Route::get('/formularios/plantillas', \App\Livewire\Plantillas\SeleccionarPlantilla::class)
        ->name('formularios.plantillas');
    
    // Rellenar análisis con una plantilla (Bioquímico)
    Route::get('/analisis/nuevo/{plantillaId}', \App\Livewire\Plantillas\RellenarFormulario::class)
        ->name('analisis.nuevo');
    
    
    // Capturar resultados de análisis (Bioquímico)
    Route::get('/analisis/{analisisId}/resultados', \App\Livewire\Resultados\CapturarResultados::class)
        ->name('analisis.capturar-resultados');
    
    // Editar resultados de análisis (Admin/Bioquímico)
    Route::get('/analisis/{analisisId}/editar', \App\Livewire\Resultados\CapturarResultados::class)
        ->name('analisis.editar');
    
    //  PREVIEW: Vista de captura de resultados (frontend temporal)
    Route::view('/resultados/preview', 'livewire.resultados.capturar-resultados')
        ->name('resultados.preview');
    
    
    // Ver listado de análisis completados
    Route::get('/analisis', \App\Livewire\AnalisisListado::class)
        ->name('analisis.index');
    
    // Revisar análisis finalizados (Admin)
    Route::get('/analisis/revisar', \App\Livewire\Analisis\RevisarAnalisis::class)
        ->name('analisis.revisar');
    
    // Ver detalles de análisis (Admin)
    Route::get('/analisis/{analisisId}/ver', \App\Livewire\Analisis\VerAnalisis::class)
        ->name('analisis.ver');
    
    // Generar y descargar PDF de análisis aprobado
    Route::get('/analisis/{analisisId}/pdf', [PdfController::class, 'descargar'])
        ->name('analisis.pdf');

    // Guardar gráfica de análisis
    Route::post('/analisis/{analisisId}/guardar-grafica', [PdfController::class, 'guardarGrafica'])
        ->name('analisis.guardar-grafica');
});

require __DIR__.'/settings.php';
