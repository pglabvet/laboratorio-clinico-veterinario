<?php

declare(strict_types=1);

use App\Livewire\Veterinarias\GestionarVeterinarias;

it('keeps current principal when adding a new phone by default', function () {
    $component = new GestionarVeterinarias;

    $component->telefonos = [
        [
            'telefono' => '700000001',
            'nombre_contacto' => 'Principal Actual',
            'es_principal' => true,
        ],
        [
            'telefono' => '700000002',
            'nombre_contacto' => 'Secundario',
            'es_principal' => false,
        ],
    ];

    $component->nuevoTelefono = '700000003';
    $component->nuevoNombreContacto = 'Nuevo';

    $component->agregarTelefono();

    expect($component->telefonos[0]['es_principal'])->toBeTrue();
    expect($component->telefonos[1]['es_principal'])->toBeFalse();
    expect($component->telefonos[2]['es_principal'])->toBeFalse();
});

it('assigns principal to the new phone only when there is no current principal', function () {
    $component = new GestionarVeterinarias;

    $component->telefonos = [
        [
            'telefono' => '700000010',
            'nombre_contacto' => 'Sin Principal 1',
            'es_principal' => false,
        ],
        [
            'telefono' => '700000011',
            'nombre_contacto' => 'Sin Principal 2',
            'es_principal' => false,
        ],
    ];

    $component->nuevoTelefono = '700000012';
    $component->nuevoNombreContacto = '';

    $component->agregarTelefono();

    expect($component->telefonos[2]['es_principal'])->toBeTrue();
});
