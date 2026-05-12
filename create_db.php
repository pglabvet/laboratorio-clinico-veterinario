<?php
$pdo = new PDO('pgsql:host=127.0.0.1;port=5433;dbname=postgres', 'postgres', '1666');
$pdo->exec('CREATE DATABASE labvet');
echo 'DB created';
