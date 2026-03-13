<?php

declare(strict_types=1);

use App\Livewire\Muestras\FormularioMuestra;

it('only requests active analysis types in form component', function () {
    $tipoAnalisisMock = \Mockery::mock('alias:App\\Models\\TipoAnalisis');

    $tipoAnalisisMock->shouldReceive('where')
        ->once()
        ->with('estado', true)
        ->andReturnSelf();

    $tipoAnalisisMock->shouldReceive('orderBy')
        ->once()
        ->with('nombre')
        ->andReturnSelf();

    $tipoAnalisisMock->shouldReceive('get')
        ->once()
        ->andReturn(collect());

    $component = new FormularioMuestra;

    $result = $component->tiposAnalisis();

    expect($result)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

afterEach(function () {
    \Mockery::close();
});
