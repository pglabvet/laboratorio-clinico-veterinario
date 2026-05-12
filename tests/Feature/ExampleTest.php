<?php

test('returns a successful response', function () {
    $response = $this->get('/');

    // La ruta home redirige a login cuando no está autenticado
    $response->assertRedirect();
});