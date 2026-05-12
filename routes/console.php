<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Purgar auditorías con más de 6 meses cada domingo a las 2:00 AM
Schedule::command('auditorias:purgar')->weekly()->sundays()->at('02:00');

// Limpiar archivos antiguos (PDFs y charts) cada domingo a las 2:30 AM
Schedule::command('cleanup:archivos-antiguos')->weekly()->sundays()->at('02:30');

