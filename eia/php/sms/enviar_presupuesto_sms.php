<?php
declare(strict_types=1);

/**
 * Endpoint interno: enviar presupuesto por SMS (SMS Masivos).
 * Entrada: POST JSON/x-www-form-urlencoded con presupuesto_id (obligatorio), telefono (opcional).
 * Seguridad: requiere sesión válida (Vendedor o dataP=ValidJCCM).
 */

$root = dirname(__DIR__, 3); // /public_html
require_once $root . '/eia/session.php';
kasu_session_start();
require_once $root . '/eia/librerias.php';

header('Content-Type: application/json; charset=utf-8');

set_exception_handler(function(Throwable $e){
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
    exit;
});

// Acceso básico
if (!isset($_SESSION['Vendedor']) && ($_SESSION['dataP'] ?? '') !== 'ValidJCCM') {
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'No autorizado']);
    exit;
}

// Leer config
$smsConfig = require $root . '/eia/config/smsmasivos.php';
require_once $root . '/eia/Funciones/SmsMasivosClient.php';
$smsClient = new SmsMasivosClient($smsConfig);

// Input
$input = $_POST;
if (empty($input) && ($raw = file_get_contents('php://input'))) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) { $input = $decoded; }
}

$presupuestoId = (int)($input['presupuesto_id'] ?? 0);
$telefono      = $input['telefono'] ?? '';
$plazoReq      = (int)($input['plazo'] ?? 0);

// Cargar propuesta en PrespEnviado (DB prospectos: $pros)
if (!isset($pros) || !$pros instanceof mysqli) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Conexión prospectos no disponible']);
    exit;
}

// Si no mandan presupuesto_id, clonar el más reciente del prospecto y crear uno nuevo con el plazo solicitado
if ($presupuestoId <= 0 && !empty($input['IdProspecto'])) {
    $tmpIdPros = (int)$input['IdProspecto'];
    $stmt = $pros->prepare('SELECT * FROM PrespEnviado WHERE IdProspecto = ? ORDER BY Id DESC LIMIT 1');
    $stmt->bind_param('i', $tmpIdPros);
    $stmt->execute();
    $last = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($last) {
        $nuevoPlazo = $plazoReq > 0 ? $plazoReq : (int)($last['Plazo'] ?? 0);
        $stmt = $pros->prepare("
            INSERT INTO PrespEnviado 
            (IdProspecto, IdUser, SubProducto, a02a29, a30a49, a50a54, a55a59, a60a64, a65a69, Retiro, Plazo, FechaRegistro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param(
            'iisssssssii',
            $last['IdProspecto'],
            $last['IdUser'],
            $last['SubProducto'],
            $last['a02a29'],
            $last['a30a49'],
            $last['a50a54'],
            $last['a55a59'],
            $last['a60a64'],
            $last['a65a69'],
            $last['Retiro'],
            $nuevoPlazo
        );
        $stmt->execute();
        $presupuestoId = (int)$stmt->insert_id;
        $stmt->close();
    }
}

if ($presupuestoId <= 0) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'presupuesto_id requerido']);
    exit;
}

$stmt = $pros->prepare('SELECT * FROM PrespEnviado WHERE Id = ? LIMIT 1');
$stmt->bind_param('i', $presupuestoId);
$stmt->execute();
$presp = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$presp) {
    http_response_code(404);
    echo json_encode(['ok'=>false,'error'=>'Presupuesto no encontrado']);
    exit;
}

$clienteId = (int)($presp['IdProspecto'] ?? 0);

// Buscar teléfono del prospecto si no vino
if (!$telefono) {
    $stmt = $pros->prepare('SELECT NoTel FROM prospectos WHERE Id = ? LIMIT 1');
    $stmt->bind_param('i', $clienteId);
    $stmt->execute();
    $telefono = (string)($stmt->get_result()->fetch_assoc()['NoTel'] ?? '');
    $stmt->close();
}

if (!$telefono) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'No hay teléfono para el prospecto']);
    exit;
}

// Generar token para link
$token = bin2hex(random_bytes(32));
$expira = (new DateTime('+7 days'))->format('Y-m-d H:i:s');

$stmt = $pros->prepare("
    INSERT INTO document_tokens (token, tipo, ref_id, cliente_id, expira_at, created_at)
    VALUES (?, 'presupuesto', ?, ?, ?, NOW())
");
$stmt->bind_param('siis', $token, $presupuestoId, $clienteId, $expira);
$stmt->execute();
$stmt->close();

$publicBase = rtrim($smsConfig['public_url'] ?? 'https://kasu.com.mx', '/');
$url = $publicBase . '/login/Generar_PDF/presupuesto.php?token=' . urlencode($token);

$message = "KASU: Tu presupuesto esta listo. Ver PDF: {$url}";

$resp = $smsClient->sendSms([$telefono], $message, [
    'shorten_url' => 1,
    'extra_params'=> ['presupuesto_id'=>$presupuestoId,'cliente_id'=>$clienteId],
]);

// Log SMS
$stmt = $pros->prepare("
    INSERT INTO sms_outbox_log (tipo, ref_id, telefono, message, provider, provider_response, success, provider_code, created_at)
    VALUES ('presupuesto', ?, ?, ?, 'smsmasivos', ?, ?, ?, NOW())
");
$jsonResp = json_encode($resp);
$success  = $resp['success'] ? 1 : 0;
$code     = $resp['code'] ?? null;
$stmt->bind_param('isssis', $presupuestoId, $telefono, $message, $jsonResp, $success, $code);
$stmt->execute();
$stmt->close();

echo json_encode([
    'ok' => (bool)$resp['success'],
    'sent' => (bool)$resp['success'],
    'code' => $resp['code'] ?? null,
    'message' => $resp['message'] ?? null,
]);
exit;
