<?php
declare(strict_types=1);
require __DIR__.'/../config/mp.php';

use MercadoPago\Client\Preference\PreferenceClient;

// 1) Validar ref
$ref = $_GET['ref'] ?? '';
if ($ref === '') { http_response_code(400); exit('Falta ref'); }

// 2) TODO: trae venta por $ref desde tu BD
// $venta = ...; $monto = (float)$venta['monto']; $desc = $venta['producto'];
$monto = 3000.00;                 // demo
$desc  = 'Servicio funerario KASU';

// 3) Crear preferencia
$client = new PreferenceClient();
$pref = $client->create([
  'items' => [[
    'title'       => $desc,
    'quantity'    => 1,
    'currency_id' => 'MXN',
    'unit_price'  => $monto,
  ]],
  'external_reference' => $ref,
  'back_urls' => [
    'success' => 'https://TU_DOMINIO/pago/exito.php',
    'pending' => 'https://TU_DOMINIO/pago/pendiente.php',
    'failure' => 'https://TU_DOMINIO/pago/error.php',
  ],
  'auto_return' => 'approved',
  'notification_url' => 'https://TU_DOMINIO/hooks/mp/index.php'
]);

// 4) TODO: guarda en BD: mp_preference_id y mp_init_point por $ref
// 5) Redirige al checkout
header('Location: '.$pref->init_point);
exit;