<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$str = "Línea 1\nLínea 2\n\"Comillas\" y 'Simples'";
echo 'Js::from() output: ' . Illuminate\Support\Js::from($str) . PHP_EOL;
echo 'json_encode() output: ' . json_encode($str) . PHP_EOL;
