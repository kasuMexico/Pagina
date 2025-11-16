<?php
declare(strict_types=1);

// === SDK MP ===
require __DIR__ . '/../../config/mp.php';
use MercadoPago\Client\Payment\PaymentClient;

// Respondemos 200 lo antes posible (MP solo necesita esto)
http_response_code(200);

// ======================================================================
// 1) Obtener el payment_id desde:
//    - Webhook JSON:  { "type":"payment", "data": { "id": 123 } }
//    - IPN clásico:   ?topic=payment&id=123   (GET o POST)
// ======================================================================
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];

$paymentId = null;

// Webhook JSON
if (($data['type'] ?? '') === 'payment' && !empty($data['data']['id'])) {
    $paymentId = (string)$data['data']['id'];
}

// IPN por GET: ?topic=payment&id=123
if (!$paymentId && isset($_GET['topic'], $_GET['id']) && $_GET['topic'] === 'payment') {
    $paymentId = (string)$_GET['id'];
}

// IPN por POST
if (!$paymentId && isset($_POST['topic'], $_POST['id']) && $_POST['topic'] === 'payment') {
    $paymentId = (string)$_POST['id'];
}

if (!$paymentId) {
    // Opcional para debug:
    // error_log('MP Webhook: sin payment id. GET=' . json_encode($_GET) . ' RAW=' . $raw);
    exit;
}

// ======================================================================
// 2) Consultar el pago en Mercado Pago
// ======================================================================
$client = new PaymentClient();

try {
    $payment = $client->get($paymentId);
} catch (Throwable $e) {
    error_log('MP Webhook: error get payment '.$paymentId.' - '.$e->getMessage());
    exit;
}

// Campos clave
$folio      = (string)($payment->external_reference ?? '');
if ($folio === '') {
    // Sin external_reference no sabemos a qué venta amarrar
    error_log('MP Webhook: payment sin external_reference '.$paymentId);
    exit;
}

$status     = (string)($payment->status ?? '');          // approved | pending | rejected
$statusDet  = (string)($payment->status_detail ?? '');
$amount     = (float)($payment->transaction_amount ?? 0.0);
$currency   = (string)($payment->currency_id ?? 'MXN');
$method     = (string)($payment->payment_method_id ?? '');
$payerEmail = (string)($payment->payer->email ?? '');
$createdAt  = (string)($payment->date_created ?? null);
$approvedAt = (string)($payment->date_approved ?? null);

$estatusPago = $status === 'approved'
    ? 'APROBADO'
    : ($status === 'pending' ? 'PENDIENTE' : 'RECHAZADO');

// ======================================================================
// 3) Conexión BD
// ======================================================================
$bootstrap = __DIR__ . '/../../eia/librerias.php';
if (is_file($bootstrap)) {
    require_once $bootstrap; // debe definir $mysqli
}

if (!isset($mysqli) || !($mysqli instanceof mysqli) || $mysqli->connect_errno) {
    error_log('MP Webhook: BD no disponible');
    exit;
}
$mysqli->set_charset('utf8mb4');

// ======================================================================
// 4) UPSERT idempotente sobre VentasMercadoPago
// ======================================================================
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
        $status // para el CASE (?='approved')
    );
    $stmt->execute();
    $stmt->close();
} else {
    error_log('MP Webhook: error prepare SQL VentasMercadoPago: '.$mysqli->error);
}
