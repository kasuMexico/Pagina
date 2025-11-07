<?php
declare(strict_types=1);

// === SDK MP ===
require __DIR__ . '/../../config/mp.php';
use MercadoPago\Client\Payment\PaymentClient;

// === HTTP: responde rápido ===
http_response_code(200);

// === Carga cuerpo JSON ===
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (($data['type'] ?? '') !== 'payment') { exit; }
$paymentId = $data['data']['id'] ?? null;
if (!$paymentId) { exit; }

// === Consulta el pago en MP ===
$client  = new PaymentClient();
$payment = $client->get((string)$paymentId);

// Campos clave
$folio       = (string)($payment->external_reference ?? '');
$status      = (string)($payment->status ?? '');            // approved | pending | rejected
$statusDet   = (string)($payment->status_detail ?? '');
$amount      = (float)($payment->transaction_amount ?? 0.0);
$currency    = (string)($payment->currency_id ?? 'MXN');
$method      = (string)($payment->payment_method_id ?? '');
$payerEmail  = (string)($payment->payer->email ?? '');
$createdAt   = (string)($payment->date_created ?? null);
$approvedAt  = (string)($payment->date_approved ?? null);

$estatusPago = $status === 'approved' ? 'APROBADO' : ($status === 'pending' ? 'PENDIENTE' : 'RECHAZADO');

// === Conexión BD (usa tu bootstrap si existe) ===
$mysqli = $mysqli ?? null;
$bootstrap = __DIR__ . '/../../eia/librerias.php';
if (is_file($bootstrap)) {
  require_once $bootstrap; // debe definir $mysqli
}
if (!($mysqli instanceof mysqli)) {
  // Fallback opcional por variables de entorno (si las tienes configuradas)
  $dbHost = getenv('DB_HOST') ?: '';
  $dbUser = getenv('DB_USER') ?: '';
  $dbPass = getenv('DB_PASS') ?: '';
  $dbName = getenv('DB_NAME') ?: '';
  if ($dbHost && $dbUser && $dbName) {
    $mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
  }
}
if (!($mysqli instanceof mysqli) || $mysqli->connect_errno) {
  error_log('MP Webhook: BD no disponible');
  exit; // seguimos respondiendo 200 para que MP no reintente sin fin
}
$mysqli->set_charset('utf8mb4');

// === UPSERT idempotente sobre VentasMercadoPago ===
// Requiere índices UNIQUE en (folio) y (mp_payment_id)
$sql = "
INSERT INTO VentasMercadoPago
  (folio, mp_payment_id, mp_payment_status, mp_status_detail, mp_payment_method,
   currency_id, amount, payer_email, date_created, date_approved,
   estatus_pago, estatus, updated_at)
VALUES
  (?,?,?,?,?,?,?,?,?,?,?,
   /* estatus negocio */ CASE WHEN ?='approved' THEN 'ACTIVA' ELSE 'PREVENTA' END,
   NOW())
ON DUPLICATE KEY UPDATE
  mp_payment_status = VALUES(mp_payment_status),
  mp_status_detail  = VALUES(mp_status_detail),
  mp_payment_method = VALUES(mp_payment_method),
  currency_id       = VALUES(currency_id),
  amount            = VALUES(amount),
  payer_email       = VALUES(payer_email),
  date_created      = VALUES(date_created),
  date_approved     = VALUES(date_approved),
  estatus_pago      = VALUES(estatus_pago),
  -- Solo promover a ACTIVA si se aprobó; nunca degradar
  estatus           = CASE
                        WHEN VALUES(mp_payment_status)='approved' THEN 'ACTIVA'
                        ELSE estatus
                      END,
  updated_at        = NOW()
";
$stmt = $mysqli->prepare($sql);
if ($stmt) {
  $stmt->bind_param(
    'ssssssdsssss',
    $folio,
    $paymentId,
    $status,
    $statusDet,
    $method,
    $currency,
    $amount,
    $payerEmail,
    $createdAt,
    $approvedAt,
    $estatusPago,
    $status // para el CASE del VALUES(?='approved')
  );
  $stmt->execute();
  $stmt->close();
} else {
  error_log('MP Webhook: error prepare SQL VentasMercadoPago: '.$mysqli->error);
}