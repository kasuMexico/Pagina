<?php
require __DIR__.'/../vendor/autoload.php';
$ok = class_exists(\Dotenv\Dotenv::class) && class_exists(\MercadoPago\MercadoPagoConfig::class);
echo $ok ? "autoload OK\n" : "vendor incompleto\n";