<?php

declare(strict_types=1);

use App\Models\TokenDescarga;

it('uses 14 days as default expiry when creating download token', function () {
    $method = new ReflectionMethod(TokenDescarga::class, 'crearParaPdf');
    $parameters = $method->getParameters();

    expect($parameters[1]->isDefaultValueAvailable())->toBeTrue();
    expect($parameters[1]->getDefaultValue())->toBe(14);
});
