<?php

declare(strict_types=1);

use App\Models\TokenDescarga;

it('uses 365 days as default expiry when creating download token', function () {
    $method = new ReflectionMethod(TokenDescarga::class, 'crearParaPdf');
    $parameters = $method->getParameters();

    expect($parameters[1]->isDefaultValueAvailable())->toBeTrue();
    expect($parameters[1]->getDefaultValue())->toBe(365);
});

it('generates a short code of exactly 10 alphanumeric characters', function () {
    $codigo = \Illuminate\Support\Str::random(10);

    expect($codigo)->toHaveLength(10);
    expect($codigo)->toMatch('/^[a-zA-Z0-9]{10}$/');
});

