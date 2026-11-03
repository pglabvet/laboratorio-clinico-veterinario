<?php

declare(strict_types=1);

use App\Livewire\Muestras\FormularioMuestra;
use Illuminate\Support\Facades\DB;

// Antes usaba un mock "alias" de TipoAnalisis, que reemplaza la clase para todo el
// proceso y rompía los tests posteriores que usan análisis reales. Ahora usa la BD de prueba.
it('only requests active analysis types in form component', function () {
    DB::table('tipos_analisis')->insert([
        ['nombre' => 'Urianálisis', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
        ['nombre' => 'Bioquímica', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
        ['nombre' => 'Inactivo', 'estado' => false, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $result = (new FormularioMuestra)->tiposAnalisis();

    expect($result)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($result->pluck('nombre')->all())->toBe(['Bioquímica', 'Urianálisis']);
});
