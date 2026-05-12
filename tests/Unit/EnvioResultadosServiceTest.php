<?php

use App\Services\AnalisisPdfService;
use App\Services\EnvioResultadosService;
use Illuminate\Support\Collection;

it('usa el telefono seleccionado si pertenece a la veterinaria', function () {
    $service = new EnvioResultadosService(\Mockery::mock(AnalisisPdfService::class));

    $veterinaria = new class
    {
        public Collection $telefonos;
    };

    $veterinaria->telefonos = collect([
        (object) ['telefono' => '70100001', 'es_principal' => true],
        (object) ['telefono' => '70100002', 'es_principal' => false],
    ]);

    $method = new ReflectionMethod(EnvioResultadosService::class, 'resolverTelefonoWhatsapp');
    $method->setAccessible(true);

    $telefono = $method->invoke($service, $veterinaria, '70100002');

    expect($telefono)->toBe('70100002');
});

it('usa el telefono principal cuando no se selecciona ninguno', function () {
    $service = new EnvioResultadosService(\Mockery::mock(AnalisisPdfService::class));

    $veterinaria = new class
    {
        public Collection $telefonos;
    };

    $veterinaria->telefonos = collect([
        (object) ['telefono' => '70100001', 'es_principal' => false],
        (object) ['telefono' => '70100002', 'es_principal' => true],
    ]);

    $method = new ReflectionMethod(EnvioResultadosService::class, 'resolverTelefonoWhatsapp');
    $method->setAccessible(true);

    $telefono = $method->invoke($service, $veterinaria, null);

    expect($telefono)->toBe('70100002');
});
