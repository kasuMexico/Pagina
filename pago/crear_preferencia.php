<?php
declare(strict_types=1);

use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

require __DIR__ . '/../config/mp.php';
require __DIR__ . '/../eia/librerias.php';

$ref = isset($_GET['ref']) ? trim((string)$_GET['ref']) : '';
if ($ref === '') {
  http_response_code(400);
  exit('Falta ref');
}

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
  http_response_code(500);
  exit('Error de conexión.');
}

$sql = "
SELECT
  v.Id,
  v.Nombre,
  v.Producto,
  v.CostoVenta,
  v.IdFIrma,
  vm.plan,
  vm.plazo_meses,
  vm.precio_base,
  vm.amount,
  c.Mail     AS email,
  c.Telefono AS telefono
FROM Venta v
LEFT JOIN VentasMercadoPago vm ON vm.folio = v.IdFIrma
LEFT JOIN Contacto c ON c.id = v.IdContact
WHERE v.IdFIrma = ?
LIMIT 1";

$st = $mysqli->prepare($sql);
$st->bind_param('s', $ref);
$st->execute();
$info = $st->get_result()->fetch_assoc() ?: null;
$st->close();

if (!$info) {
  http_response_code(404);
  exit('Venta no encontrada.');
}

$amount = (float)($info['amount'] ?? $info['precio_base'] ?? $info['CostoVenta'] ?? 0);
if ($amount <= 0) {
  http_response_code(500);
  exit('Monto inválido.');
}

$descripcion = trim((string)($info['Producto'] ?: 'Servicio KASU'));
$nombreCliente = trim((string)($info['Nombre'] ?? 'Cliente KASU'));
$emailCliente  = filter_var((string)($info['email'] ?? ''), FILTER_VALIDATE_EMAIL) ? (string)$info['email'] : 'no-reply@kasu.com.mx';

$baseUrl = 'https://kasu.com.mx';
$client  = new PreferenceClient();

try {
  $pref = $client->create([
    'items' => [[
      'title'        => $descripcion,
      'description'  => "Plan {$info['plan']} - Folio {$ref}",
      'quantity'     => 1,
      'currency_id'  => 'MXN',
      'unit_price'   => $amount,
      'category_id'  => 'services',
    ]],
    'external_reference' => $ref,
    'payer' => [
      'name'  => $nombreCliente,
      'email' => $emailCliente,
    ],
    'back_urls' => [
      'success' => "{$baseUrl}/pago/exito.php?ref={$ref}",
      'pending' => "{$baseUrl}/pago/pendiente.php?ref={$ref}",
      'failure' => "{$baseUrl}/pago/error.php?ref={$ref}",
    ],
    'auto_return'      => 'approved',
    'notification_url' => "{$baseUrl}/hooks/mp/index.php",
  ]);
} catch (MPApiException $e) {
  error_log("[MP] Error creando preferencia: {$e->getMessage()}");
  http_response_code(502);
  exit('Error al crear la preferencia de pago.');
}

if (isset($pref->id)) {
  $upd = $mysqli->prepare("UPDATE VentasMercadoPago
                            SET mp_preference_id = ?, mp_init_point = ?, updated_at = NOW()
                            WHERE folio = ?");
  if ($upd) {
    $initPoint = (string)$pref->init_point;
    $prefId    = (string)$pref->id;
    $upd->bind_param('sss', $prefId, $initPoint, $ref);
    $upd->execute();
    $upd->close();
  }
}

header('Location: ' . $pref->init_point);
exit;
