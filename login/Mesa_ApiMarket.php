<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/eia/session.php';
kasu_session_start();
require_once __DIR__ . '/../eia/Conexiones/cn_vtas.php';
require_once __DIR__ . '/../eia/Funciones/Funciones_Basicas.php';
require_once __DIR__ . '/../apimarket/Funciones/cn_apimarket.php';
require_once __DIR__ . '/../apimarket/Funciones/Funciones_ApiAccess.php';

date_default_timezone_set('America/Mexico_City');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header_remove('X-Powered-By');

$basicas = new Basicas();

// En algunos entornos la BD exclusiva de API Market no está disponible.
// Usamos la conexión principal como respaldo para no bloquear la mesa interna.
if ((!isset($mysqli_api) || !($mysqli_api instanceof mysqli)) && isset($mysqli) && ($mysqli instanceof mysqli)) {
    $mysqli_api = $mysqli;
}

if (!function_exists('h')) {
    function h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
}

if (!isset($_SESSION['Vendedor']) || $_SESSION['Vendedor'] === '') {
    header('Location: https://kasu.com.mx/login');
    exit;
}

if (!isset($mysqli) || !($mysqli instanceof mysqli) || !isset($mysqli_api) || !($mysqli_api instanceof mysqli)) {
    http_response_code(500);
    exit('Error de conexión a base de datos.');
}

$stmt = $mysqli->prepare('SELECT * FROM Empleados WHERE IdUsuario = ? LIMIT 1');
$stmt->bind_param('s', $_SESSION['Vendedor']);
$stmt->execute();
$Reg = $stmt->get_result()->fetch_assoc() ?: null;
$stmt->close();
$Nivel = (int)($Reg['Nivel'] ?? 99);
$canManageApis = ($Nivel === 1 || $Nivel === 2);

if (!$canManageApis) {
    http_response_code(403);
    exit('No tienes permisos para administrar API Market.');
}

api_access_schema($mysqli_api);
$_SESSION['csrf_api_admin'] = $_SESSION['csrf_api_admin'] ?? bin2hex(random_bytes(32));
$flashSecret = $_SESSION['api_admin_flash_secret'] ?? null;
unset($_SESSION['api_admin_flash_secret']);

function api_admin_redirect(string $msg): void
{
    header('Location: Mesa_ApiMarket.php?Msg=' . rawurlencode($msg), true, 303);
    exit;
}

function api_admin_request(mysqli $db, int $id): ?array
{
    $stmt = $db->prepare('SELECT * FROM api_access_requests WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

function api_admin_credentials_for_request(mysqli $db, array $request): array
{
    $apiUser = trim((string)($request['api_user'] ?? ''));
    if ($apiUser === '') {
        throw new RuntimeException('La solicitud no tiene usuario API asignado.');
    }

    $stmt = $db->prepare('SELECT Pass FROM Empleados WHERE IdUsuario = ? LIMIT 1');
    $stmt->bind_param('s', $apiUser);
    $stmt->execute();
    $employee = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    $privateKey = trim((string)($employee['Pass'] ?? ''));
    if ($privateKey === '') {
        throw new RuntimeException('No se encontró PRIVATE_KEY para este usuario API.');
    }

    $secret = null;
    $secretId = (int)($request['secret_key_id'] ?? 0);
    if ($secretId > 0) {
        $stmt = $db->prepare('SELECT Id, Usuario FROM Secret_KEY WHERE Id = ? AND IdUsuario = ? LIMIT 1');
        $stmt->bind_param('is', $secretId, $apiUser);
        $stmt->execute();
        $secret = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
    }

    if (!$secret) {
        $stmt = $db->prepare('SELECT Id, Usuario FROM Secret_KEY WHERE IdUsuario = ? ORDER BY Id DESC LIMIT 1');
        $stmt->bind_param('s', $apiUser);
        $stmt->execute();
        $secret = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
    }

    if (!$secret) {
        throw new RuntimeException('No se encontró User-Agent para este usuario API.');
    }

    return [
        'api_user' => $apiUser,
        'private_key' => $privateKey,
        'user_agent' => (string)$secret['Usuario'] . '_' . (int)$secret['Id'],
    ];
}

function api_admin_env(string $key, string $default = ''): string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }
    return is_string($value) ? $value : $default;
}

function api_admin_load_mailer(): void
{
    $root = realpath(__DIR__ . '/..') ?: dirname(__DIR__);
    $autoload = $root . '/vendor/autoload.php';
    if (!is_file($autoload)) {
        throw new RuntimeException('No se encontró vendor/autoload.php para PHPMailer.');
    }
    require_once $autoload;

    if (class_exists(\Dotenv\Dotenv::class) && is_file($root . '/.env')) {
        \Dotenv\Dotenv::createUnsafeImmutable($root)->safeLoad();
    }
}

function api_admin_send_credentials_email(
    array $request,
    string $apiUser,
    string $privateKey,
    string $userAgent,
    array $apis,
    int $saldoInicial,
    bool $isResend = false
): void {
    $email = trim((string)($request['correo'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('El correo de la solicitud no es válido.');
    }

    api_admin_load_mailer();
    if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
        throw new RuntimeException('PHPMailer no está disponible en vendor.');
    }

    $nombre = trim((string)($request['nombre'] ?? ''));
    $empresa = trim((string)($request['empresa'] ?? ''));
    $displayName = $empresa !== '' ? $empresa : ($nombre !== '' ? $nombre : $email);
    $apisText = implode(', ', $apis);
    if ($apisText === '') {
        $apisText = 'API Market KASU V1';
    }
    $saldoText = $saldoInicial > 0 ? api_access_centavos_to_money($saldoInicial) : 'No aplica';
    $tokenUrl = 'https://apimarket.kasu.com.mx/api/Token_Full';
    $docsUrl = 'https://apimarket.kasu.com.mx/';
    $title = $isResend ? 'Reenvío de credenciales API Market KASU' : 'Credenciales API Market KASU';
    $intro = $isResend
        ? 'Te reenviamos las credenciales activas de API Market KASU. Guarda estos datos en un administrador seguro y no los compartas por canales públicos.'
        : 'Tu solicitud fue aprobada. Guarda estas credenciales en un administrador seguro y no las compartas por canales públicos.';

    $html = '
        <div style="font-family:Arial,Helvetica,sans-serif;line-height:1.5;color:#172033;background:#f6f8fb;padding:24px">
          <div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #dbe4ef;border-radius:12px;overflow:hidden">
            <div style="background:#0f172a;color:#ffffff;padding:18px 22px">
              <h1 style="font-size:20px;line-height:1.25;margin:0">' . h($title) . '</h1>
              <p style="margin:6px 0 0;color:#cbd5e1">Acceso autorizado para ' . h($displayName) . '</p>
            </div>
            <div style="padding:22px">
              <p style="margin-top:0">' . h($intro) . '</p>
              <table role="presentation" style="width:100%;border-collapse:collapse;margin:16px 0;border:1px solid #dbe4ef">
                <tr>
                  <td style="padding:10px;border-bottom:1px solid #dbe4ef;background:#f8fafc;font-weight:700;width:160px">nombre_de_usuario</td>
                  <td style="padding:10px;border-bottom:1px solid #dbe4ef"><code>' . h($apiUser) . '</code></td>
                </tr>
                <tr>
                  <td style="padding:10px;border-bottom:1px solid #dbe4ef;background:#f8fafc;font-weight:700">PRIVATE_KEY</td>
                  <td style="padding:10px;border-bottom:1px solid #dbe4ef"><code>' . h($privateKey) . '</code></td>
                </tr>
                <tr>
                  <td style="padding:10px;border-bottom:1px solid #dbe4ef;background:#f8fafc;font-weight:700">User-Agent</td>
                  <td style="padding:10px;border-bottom:1px solid #dbe4ef"><code>' . h($userAgent) . '</code></td>
                </tr>
                <tr>
                  <td style="padding:10px;border-bottom:1px solid #dbe4ef;background:#f8fafc;font-weight:700">APIs</td>
                  <td style="padding:10px;border-bottom:1px solid #dbe4ef">' . h($apisText) . '</td>
                </tr>
                <tr>
                  <td style="padding:10px;background:#f8fafc;font-weight:700">Saldo Validate_Mexico</td>
                  <td style="padding:10px">' . h($saldoText) . '</td>
                </tr>
              </table>
              <p>Primero genera un Bearer token en <a href="' . h($tokenUrl) . '">' . h($tokenUrl) . '</a>. La documentación pública está en <a href="' . h($docsUrl) . '">' . h($docsUrl) . '</a>.</p>
              <p style="margin-bottom:0;color:#475569">Si no reconoces esta solicitud, responde a este correo para bloquear el acceso.</p>
            </div>
          </div>
        </div>
    ';

    $text = "{$title}\n"
        . "Acceso autorizado para {$displayName}\n\n"
        . "nombre_de_usuario: {$apiUser}\n"
        . "PRIVATE_KEY: {$privateKey}\n"
        . "User-Agent: {$userAgent}\n"
        . "APIs: {$apisText}\n"
        . "Saldo Validate_Mexico: {$saldoText}\n\n"
        . "Token_Full: {$tokenUrl}\n"
        . "Documentación: {$docsUrl}\n";

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = api_admin_env('SMTP_HOST', 'smtp.hostinger.mx');
        $mail->SMTPAuth = true;
        $mail->Username = api_admin_env('SMTP_USER');
        $mail->Password = api_admin_env('SMTP_PASS');
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int)api_admin_env('SMTP_PORT', '587');
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 15;

        $fromEmail = api_admin_env('SMTP_FROM', api_admin_env('FROM_EMAIL', 'atncliente@kasu.com.mx'));
        $fromName = api_admin_env('SMTP_FROM_NAME', api_admin_env('FROM_NAME', 'KASU API Market'));
        $replyTo = api_admin_env('REPLY_TO', $fromEmail);
        $bounce = api_admin_env('BOUNCE_EMAIL');

        $mail->setFrom($fromEmail, $fromName);
        $mail->addReplyTo($replyTo, $fromName);
        if ($bounce !== '') {
            $mail->Sender = $bounce;
        }
        $mail->addAddress($email, $displayName);

        $mail->isHTML(true);
        $mail->Subject = $title;
        $mail->Body = $html;
        $mail->AltBody = $text;
        $mail->send();
    } catch (Throwable $e) {
        error_log('[Mesa_ApiMarket][MAIL] ' . ($mail->ErrorInfo ?: $e->getMessage()));
        throw new RuntimeException('No se pudo enviar el correo de credenciales.');
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $csrf = (string)($_POST['csrf'] ?? '');
        if (!hash_equals((string)$_SESSION['csrf_api_admin'], $csrf)) {
            api_admin_redirect('Sesión inválida.');
        }

        $action = (string)($_POST['action'] ?? '');
        $managedActions = ['approve_request', 'deny_request', 'secret_status', 'add_wallet', 'resend_credentials'];
        if (in_array($action, $managedActions, true) && !$canManageApis) {
            api_admin_redirect('No tienes permisos para modificar accesos API.');
        }

        if ($action === 'approve_request') {
            $requestId = (int)($_POST['request_id'] ?? 0);
            $request = api_admin_request($mysqli_api, $requestId);
            if (!$request) {
                api_admin_redirect('Solicitud no encontrada.');
            }

            $apiUser = api_access_slug_user((string)($_POST['api_user'] ?? ''));
            if ($apiUser === '' || $apiUser === 'API_USER') {
                $apiUser = api_access_default_api_user($request);
            }

            $privateKey = trim((string)($_POST['private_key'] ?? ''));
            if ($privateKey === '') {
                $privateKey = api_access_random_private_key();
            }
            $secretUsuario = api_access_slug_user((string)($_POST['secret_usuario'] ?? 'KASUAPI'));
            $saldoInicial = api_access_money_to_centavos($_POST['saldo_inicial'] ?? $request['saldo_solicitado_centavos'] / 100);
            $adminNotes = trim((string)($_POST['admin_notes'] ?? ''));
            $adminUser = (string)$_SESSION['Vendedor'];

            api_access_insert_or_update_empleado(
                $mysqli,
                $apiUser,
                $privateKey,
                (string)($request['empresa'] ?: $request['nombre'] ?: $apiUser)
            );
            $secretId = api_access_create_secret_key($mysqli, $apiUser, $secretUsuario);

            if ((int)$request['api_validate_mexico'] === 1 || $saldoInicial > 0) {
                api_access_ensure_validate_mexico_user($mysqli_api, $request, $apiUser, $saldoInicial, $adminUser);
            }
            api_access_sync_grants($mysqli_api, $apiUser, $request);

            $estado = 'APROBADA';
            $stmt = $mysqli_api->prepare('
                UPDATE api_access_requests
                SET estado = ?, admin_user = ?, admin_notes = ?, api_user = ?, secret_key_usuario = ?, secret_key_id = ?
                WHERE id = ?
            ');
            $stmt->bind_param('sssssii', $estado, $adminUser, $adminNotes, $apiUser, $secretUsuario, $secretId, $requestId);
            $stmt->execute();
            $stmt->close();

            $userAgent = $secretUsuario . '_' . $secretId;
            $approvedApis = api_access_request_apis($request);
            $mailError = '';
            try {
                api_admin_send_credentials_email($request, $apiUser, $privateKey, $userAgent, $approvedApis, $saldoInicial);
                $_SESSION['api_admin_flash_secret'] = [
                    'delivery' => 'email',
                    'email' => (string)($request['correo'] ?? ''),
                    'api_user' => $apiUser,
                    'user_agent' => $userAgent,
                ];
                api_admin_redirect('Solicitud aprobada. Credenciales enviadas por correo.');
            } catch (Throwable $mailException) {
                $mailError = $mailException->getMessage();
            }

            $_SESSION['api_admin_flash_secret'] = [
                'delivery' => 'fallback',
                'api_user' => $apiUser,
                'private_key' => $privateKey,
                'user_agent' => $userAgent,
                'mail_error' => $mailError,
            ];
            api_admin_redirect('Solicitud aprobada, pero no se pudo enviar el correo. Copia la credencial privada y entrégala por canal seguro.');
        }

        if ($action === 'resend_credentials') {
            $requestId = (int)($_POST['request_id'] ?? 0);
            $request = api_admin_request($mysqli_api, $requestId);
            if (!$request) {
                api_admin_redirect('Solicitud no encontrada.');
            }
            if ((string)($request['estado'] ?? '') !== 'APROBADA') {
                api_admin_redirect('Solo se pueden reenviar claves de solicitudes aprobadas.');
            }

            $credentials = api_admin_credentials_for_request($mysqli, $request);
            api_admin_send_credentials_email(
                $request,
                $credentials['api_user'],
                $credentials['private_key'],
                $credentials['user_agent'],
                api_access_request_apis($request),
                (int)($request['saldo_solicitado_centavos'] ?? 0),
                true
            );

            $_SESSION['api_admin_flash_secret'] = [
                'delivery' => 'email',
                'email' => (string)($request['correo'] ?? ''),
                'api_user' => $credentials['api_user'],
                'user_agent' => $credentials['user_agent'],
            ];
            api_admin_redirect('Claves reenviadas por correo.');
        }

        if ($action === 'deny_request') {
            $requestId = (int)($_POST['request_id'] ?? 0);
            $adminNotes = trim((string)($_POST['admin_notes'] ?? ''));
            $estado = 'RECHAZADA';
            $adminUser = (string)$_SESSION['Vendedor'];
            $stmt = $mysqli_api->prepare('
                UPDATE api_access_requests
                SET estado = ?, admin_user = ?, admin_notes = ?
                WHERE id = ?
            ');
            $stmt->bind_param('sssi', $estado, $adminUser, $adminNotes, $requestId);
            $stmt->execute();
            $stmt->close();
            api_admin_redirect('Solicitud rechazada.');
        }

        if ($action === 'secret_status') {
            $secretId = (int)($_POST['secret_id'] ?? 0);
            $status = (string)($_POST['status'] ?? '');
            api_access_set_secret_status($mysqli, $secretId, $status === 'ACTIVE' ? null : 'BAJA');
            api_admin_redirect($status === 'ACTIVE' ? 'Token activado.' : 'Token bloqueado.');
        }

        if ($action === 'add_wallet') {
            $apiUser = api_access_slug_user((string)($_POST['api_user'] ?? ''));
            $amount = api_access_money_to_centavos($_POST['amount'] ?? '0');
            $reason = trim((string)($_POST['reason'] ?? 'Carga manual'));
            api_access_add_wallet_balance($mysqli_api, $apiUser, $amount, $reason, (string)$_SESSION['Vendedor']);
            api_admin_redirect('Saldo agregado a Validate_Mexico.');
        }
    }
} catch (Throwable $e) {
    error_log('[Mesa_ApiMarket] ' . $e->getMessage());
    api_admin_redirect('No se pudo completar la acción: ' . $e->getMessage());
}

$requests = [];
$res = $mysqli_api->query('SELECT * FROM api_access_requests ORDER BY FIELD(estado, "PENDIENTE", "APROBADA", "RECHAZADA"), id DESC LIMIT 100');
while ($row = $res->fetch_assoc()) {
    $requests[] = $row;
}

$tokens = [];
$res = $mysqli->query('
    SELECT sk.Id, sk.IdUsuario, sk.Usuario, sk.Status, sk.Timestamp, e.Nombre
    FROM Secret_KEY sk
    LEFT JOIN Empleados e ON e.IdUsuario = sk.IdUsuario
    ORDER BY sk.Id DESC
    LIMIT 100
');
while ($row = $res->fetch_assoc()) {
    $tokens[] = $row;
}

$wallets = [];
$res = $mysqli_api->query('
    SELECT au.nombre_de_usuario, au.activo, au.subdistribuidor_id, COALESCE(w.saldo_centavos, 0) AS saldo_centavos,
           s.nombre, s.empresa, s.correo
    FROM api_usuarios au
    LEFT JOIN api_wallets w ON w.subdistribuidor_id = au.subdistribuidor_id
    LEFT JOIN api_subdistribuidores s ON s.id = au.subdistribuidor_id
    ORDER BY au.id DESC
    LIMIT 100
');
while ($row = $res->fetch_assoc()) {
    $wallets[] = $row;
}

$msg = isset($_GET['Msg']) ? (string)$_GET['Msg'] : '';
$VerCache = time();
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
<meta name="theme-color" content="#F1F7FC">
<link rel="icon" href="/assets/images/Index/florkasu.png">
<title>API Market Admin</title>
<link rel="stylesheet" href="/assets/css/fonts.css?v=<?= h((string)$VerCache) ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= h((string)$VerCache) ?>">
<link rel="stylesheet" href="/login/assets/css/Menu_Superior.css?v=<?= h((string)$VerCache) ?>">
<link rel="stylesheet" href="/login/assets/css/pwa-core.css?v=<?= h((string)$VerCache) ?>">
<link rel="stylesheet" href="/login/assets/css/pwa-components.css?v=<?= h((string)$VerCache) ?>">
<style>
  .api-admin-grid{display:grid;grid-template-columns:1fr;gap:14px}
  @media(min-width:1100px){.api-admin-grid{grid-template-columns:2fr 1fr}}
  .api-admin-card{background:#fff;border:1px solid rgba(226,232,240,.9);border-radius:16px;box-shadow:0 14px 32px rgba(15,23,42,.08);padding:16px}
  .api-admin-card h5{font-weight:800;color:#0f172a;margin:0 0 10px}
  .api-admin-card .table{font-size:.86rem}
  .api-admin-card .form-control,.api-admin-card .btn{border-radius:10px}
  .api-status{display:inline-flex;border-radius:999px;padding:4px 9px;font-size:.72rem;font-weight:800}
  .api-status.PENDIENTE{background:#fff7ed;color:#9a3412}
  .api-status.APROBADA{background:#ecfdf5;color:#047857}
  .api-status.RECHAZADA{background:#fef2f2;color:#b91c1c}
  .api-secret-box{background:#0f172a;color:#e5e7eb;border-radius:14px;padding:14px;margin-bottom:14px}
  .api-secret-box code{color:#86efac}
  .api-secret-box.email-sent{background:#ecfdf5;color:#064e3b;border:1px solid #a7f3d0}
  .api-secret-box.email-sent code{color:#047857}
  .api-secret-box .mail-error{color:#fecaca}
  .api-actions{display:flex;gap:6px;flex-wrap:wrap}
</style>
</head>
<body>
  <div class="topbar">
    <div class="topbar-left">
      <img alt="KASU" src="/login/assets/img/kasu_logo.jpeg">
      <div>
        <p class="eyebrow mb-0">Panel móvil</p>
        <h4 class="title">API Market</h4>
      </div>
    </div>
  </div>

  <section id="Menu">
    <?php require_once __DIR__ . '/html/Menuprinc.php'; ?>
  </section>

  <main class="page-content">
    <div class="dashboard-shell">
      <div class="page-heading">
        <p>Administra solicitudes, credenciales, tokens y saldo prepago de Validate_Mexico.</p>
      </div>

      <?php if ($msg !== '') { ?>
        <div class="alert alert-info"><?= h($msg) ?></div>
      <?php } ?>

      <?php if (is_array($flashSecret)) { ?>
        <?php $mailDelivered = (($flashSecret['delivery'] ?? '') === 'email'); ?>
        <div class="api-secret-box<?= $mailDelivered ? ' email-sent' : '' ?>">
          <?php if ($mailDelivered) { ?>
            <strong>Credenciales enviadas por correo.</strong>
            <p class="mb-1">Destino: <code><?= h($flashSecret['email'] ?? '') ?></code></p>
            <p class="mb-1">nombre_de_usuario: <code><?= h($flashSecret['api_user'] ?? '') ?></code></p>
            <p class="mb-0">User-Agent: <code><?= h($flashSecret['user_agent'] ?? '') ?></code></p>
          <?php } else { ?>
            <strong>Credencial recién generada. Entrégala por canal seguro.</strong>
            <?php if ((string)($flashSecret['mail_error'] ?? '') !== '') { ?>
              <p class="mail-error mb-1">Correo no enviado: <?= h($flashSecret['mail_error']) ?></p>
            <?php } ?>
            <p class="mb-1">nombre_de_usuario: <code><?= h($flashSecret['api_user'] ?? '') ?></code></p>
            <p class="mb-1">PRIVATE_KEY: <code><?= h($flashSecret['private_key'] ?? '') ?></code></p>
            <p class="mb-0">User-Agent: <code><?= h($flashSecret['user_agent'] ?? '') ?></code></p>
          <?php } ?>
        </div>
      <?php } ?>

      <div class="api-admin-grid">
        <section class="api-admin-card">
          <h5>Solicitudes de acceso</h5>
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Solicitud</th>
                  <th>APIs</th>
                  <th>Mensaje</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($requests as $request) { ?>
                <tr>
                  <td>
                    <strong>#<?= (int)$request['id'] ?> <?= h($request['empresa'] ?: $request['nombre']) ?></strong><br>
                    <small><?= h($request['correo']) ?></small><br>
                    <span class="api-status <?= h($request['estado']) ?>"><?= h($request['estado']) ?></span>
                    <?php if ((string)($request['api_user'] ?? '') !== '') { ?><br><small>Usuario API: <?= h($request['api_user']) ?></small><?php } ?>
                  </td>
                  <td>
                    <?= h(implode(', ', api_access_request_apis($request))) ?><br>
                    <?php if ((int)$request['saldo_solicitado_centavos'] > 0) { ?>
                      <small>Saldo: <?= h(api_access_centavos_to_money((int)$request['saldo_solicitado_centavos'])) ?></small>
                    <?php } ?>
                  </td>
                  <td><small><?= nl2br(h($request['mensaje'] ?? '')) ?></small></td>
                  <td>
                    <?php if ($request['estado'] === 'PENDIENTE') { ?>
                    <form method="post" class="mb-2">
                      <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf_api_admin']) ?>">
                      <input type="hidden" name="action" value="approve_request">
                      <input type="hidden" name="request_id" value="<?= (int)$request['id'] ?>">
                      <input class="form-control form-control-sm mb-1" name="api_user" maxlength="20" value="<?= h(api_access_default_api_user($request)) ?>" placeholder="nombre_de_usuario">
                      <input class="form-control form-control-sm mb-1" name="secret_usuario" value="KASUAPI" placeholder="Secret_KEY.Usuario">
                      <input class="form-control form-control-sm mb-1" name="private_key" placeholder="PRIVATE_KEY opcional">
                      <input class="form-control form-control-sm mb-1" name="saldo_inicial" value="<?= h(number_format(((int)$request['saldo_solicitado_centavos']) / 100, 2, '.', '')) ?>" placeholder="Saldo inicial">
                      <textarea class="form-control form-control-sm mb-1" name="admin_notes" rows="2" placeholder="Notas internas"></textarea>
                      <button class="btn btn-success btn-sm btn-block" type="submit">Aprobar</button>
                    </form>
                    <form method="post">
                      <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf_api_admin']) ?>">
                      <input type="hidden" name="action" value="deny_request">
                      <input type="hidden" name="request_id" value="<?= (int)$request['id'] ?>">
                      <input class="form-control form-control-sm mb-1" name="admin_notes" placeholder="Motivo">
                      <button class="btn btn-outline-danger btn-sm btn-block" type="submit">Rechazar</button>
                    </form>
                    <?php } else { ?>
                      <small><?= nl2br(h($request['admin_notes'] ?? '')) ?></small>
                      <?php if ($request['estado'] === 'APROBADA' && (string)($request['api_user'] ?? '') !== '') { ?>
                        <form method="post" class="mt-2">
                          <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf_api_admin']) ?>">
                          <input type="hidden" name="action" value="resend_credentials">
                          <input type="hidden" name="request_id" value="<?= (int)$request['id'] ?>">
                          <button class="btn btn-outline-primary btn-sm btn-block" type="submit">Reenviar claves</button>
                        </form>
                      <?php } ?>
                    <?php } ?>
                  </td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </section>

        <aside>
          <section class="api-admin-card mb-3">
            <h5>Cargar saldo Validate_Mexico</h5>
            <form method="post">
              <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf_api_admin']) ?>">
              <input type="hidden" name="action" value="add_wallet">
              <input class="form-control mb-2" name="api_user" placeholder="nombre_de_usuario" required>
              <input class="form-control mb-2" name="amount" placeholder="Monto MXN, ej. 500.00" required>
              <input class="form-control mb-2" name="reason" placeholder="Motivo" value="Carga manual">
              <button class="btn btn-primary btn-block" type="submit">Agregar saldo</button>
            </form>
          </section>

          <section class="api-admin-card mb-3">
            <h5>Wallets</h5>
            <?php foreach ($wallets as $wallet) { ?>
              <p class="mb-2">
                <strong><?= h($wallet['nombre_de_usuario']) ?></strong><br>
                <small><?= h($wallet['empresa'] ?: $wallet['nombre']) ?></small><br>
                <span><?= h(api_access_centavos_to_money((int)$wallet['saldo_centavos'])) ?></span>
              </p>
            <?php } ?>
          </section>
        </aside>
      </div>

      <section class="api-admin-card mt-3">
        <h5>Tokens Secret_KEY</h5>
        <div class="table-responsive">
          <table class="table table-sm">
            <thead>
              <tr><th>ID</th><th>Usuario API</th><th>User-Agent</th><th>Estado</th><th>Acción</th></tr>
            </thead>
            <tbody>
              <?php foreach ($tokens as $token) { ?>
              <tr>
                <td><?= (int)$token['Id'] ?></td>
                <td><?= h($token['IdUsuario']) ?><br><small><?= h($token['Nombre'] ?? '') ?></small></td>
                <td><code><?= h($token['Usuario'] . '_' . $token['Id']) ?></code></td>
                <td><?= $token['Status'] === null ? 'ACTIVO' : h($token['Status']) ?></td>
                <td>
                  <form method="post" class="api-actions">
                    <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf_api_admin']) ?>">
                    <input type="hidden" name="action" value="secret_status">
                    <input type="hidden" name="secret_id" value="<?= (int)$token['Id'] ?>">
                    <?php if ($token['Status'] === null) { ?>
                      <button class="btn btn-outline-danger btn-sm" name="status" value="BLOCKED" type="submit">Bloquear</button>
                    <?php } else { ?>
                      <button class="btn btn-outline-success btn-sm" name="status" value="ACTIVE" type="submit">Activar</button>
                    <?php } ?>
                  </form>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </main>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
