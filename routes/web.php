<?php

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
    

    Route::view('/muestras', 'muestras.index')
        ->name('muestras.index');
    
    Route::view('/muestras/escanear', 'muestras.escanear')
        ->name('muestras.escanear');
    
    // Ruta para imprimir etiqueta de muestra
    Route::get('/muestras/{muestra}/etiqueta', function (\App\Models\Muestra $muestra) {
        return view('components.etiqueta-muestra', ['muestra' => $muestra]);
    })->name('muestras.etiqueta');

    Route::view('/especies', 'especies.index')
        ->name('especies.index');

    
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
    
    // Ver listado de análisis completados
    Route::get('/analisis', \App\Livewire\AnalisisListado::class)
        ->name('analisis.index');
});

require __DIR__.'/settings.php';
