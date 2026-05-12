<?php

// El registro público está deshabilitado en esta aplicación.
// Los usuarios son creados únicamente por el administrador.

test('registration route is not available', function () {
    $response = $this->get('/register');

    // La ruta de registro no existe, debe devolver 404
    $response->assertStatus(404);
});