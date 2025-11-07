<?php
declare(strict_types=1);
/**
 * Envío de correos transaccionales KASU (cotización, póliza, fichas, estado de cuenta, pagos).
 * PHP 8.2. Idempotente. Variables desde .env / entorno.
 * 2025-11-06 — Revisado por Jose Carlos Cabrera Monroy
 */

session_start();
date_default_timezone_set('America/Mexico_City');

require_once 'librerias.php'; // Debe definir: $Correo, $basicas, $seguridad, $pros, $mysqli
/** @var Correo $Correo */
$Correo = new Correo();

// ========= Inputs =========
$EnCoti        = filter_input(INPUT_GET,  'EnCoti',       FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$hash          = filter_input(INPUT_GET,  'hash',         FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$MxVta         = filter_input(INPUT_GET,  'MxVta',        FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$EnFi          = filter_input(INPUT_GET,  'EnFi',         FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$ProReIn       = filter_input(INPUT_GET,  'ProReIn',      FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$Servicio      = filter_input(INPUT_GET,  'Servicio',     FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$HostGet       = filter_input(INPUT_GET,  'Host',         FILTER_UNSAFE_RAW);
$NombreGet     = filter_input(INPUT_POST, 'name',         FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$EnviarPoliza  = filter_input(INPUT_POST, 'EnviarPoliza', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$EnviarFichas  = filter_input(INPUT_POST, 'EnviarFichas', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$EnviarEdoCta  = filter_input(INPUT_POST, 'EnviarEdoCta', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$Descripcion   = filter_input(INPUT_POST, 'Descripcion',  FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$IdVenta       = filter_input(INPUT_POST, 'IdVenta',      FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$Usuario       = filter_input(INPUT_POST, 'Usuario',      FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$Event         = filter_input(INPUT_POST, 'Event',        FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$Cupon         = filter_input(INPUT_POST, 'Cupon',        FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$FullNamePost  = filter_input(INPUT_POST, 'FullName',     FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$EmailPost     = filter_input(INPUT_POST, 'Email',        FILTER_SANITIZE_EMAIL);
$IdContactPost = filter_input(INPUT_POST, 'IdContact',    FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$HostPost      = filter_input(INPUT_POST, 'Host',         FILTER_UNSAFE_RAW);
$NombrePost    = filter_input(INPUT_POST, 'nombre',       FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// ========= One-shot token POST =========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['mail_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['mail_token'] ?? '', $token)) {
        http_response_code(400);
        exit('Solicitud inválida');
    }
    unset($_SESSION['mail_token']);
}

// ========= Variables base =========
$stat = "";
$Asunto = "";
$Email = "";
$FullName = "";
$Id = "";
$Msg = "";
$data = [];
$Redireccion = "";

// ========= Selección de acción =========
if (!empty($EnCoti)) { // Enviar cotización (seguro)
    $dec = base64_decode($EnCoti, true);
    if ($dec === false || !ctype_digit($dec)) {
        http_response_code(400);
        exit('Parámetro EnCoti inválido');
    }
    $idPresp = (int)$dec;

    $IdProspecto = (int)$basicas->BuscarCampos($pros, "IdProspecto", "PrespEnviado", "Id", $idPresp);
    if ($IdProspecto <= 0) {
        $dest = $HostGet ?: '/login/Mesa_Herramientas.php';
        header('Location: ' . $dest . (str_contains((string)$dest, '?') ? '&' : '?') . 'Msg=' . urlencode('No encontramos la cotización solicitada.'), true, 303);
        exit;
    }

    $NombrePros  = (string)$basicas->BuscarCampos($pros, "FullName", "prospectos", "Id", $IdProspecto);
    $Email       = (string)$basicas->BuscarCampos($pros, "Email",    "prospectos", "Id", $IdProspecto);
    $Asunto      = "ENVÍO DE COTIZACIÓN";
    $Id          = (string)$idPresp;

    // URL del PDF con idp
    $DirUrl = 'https://kasu.com.mx/login/Generar_PDF/Cotizacion_pdf.php'
            . '?busqueda=' . rawurlencode($EnCoti)
            . '&Host='     . rawurlencode((string)$HostGet)
            . '&name='     . rawurlencode((string)$NombreGet)
            . '&idp='      . urlencode((string)$IdProspecto);

    $data = [
        'Nombre' => $NombrePros,
        'DirUrl' => $DirUrl,
    ];

    // Mensaje opcional o default
    $Msg = (isset($_GET['Msg']) && $_GET['Msg'] !== '')
        ? (string)$_GET['Msg']
        : ('Se envió la cotización al correo ' . $Email);

    // Redirección final con whitelist
    $Redireccion = (string)($_GET['Redireccion'] ?? ($HostGet ?: '/login/Mesa_Herramientas.php'));
    if ($Redireccion !== '') {
        $p    = parse_url($Redireccion);
        $host = strtolower($p['host'] ?? '');
        $ok   = ['kasu.com.mx', 'www.kasu.com.mx'];
        if ($host && !in_array($host, $ok, true)) {
            $Redireccion = '/login/Mesa_Herramientas.php';
        }
        // Adjunta solo idp
        $Redireccion .= (str_contains($Redireccion, '?') ? '&' : '?')
                      . 'idp=' . urlencode((string)$IdProspecto);
    }
} elseif (!empty($EnviarPoliza)) {  // Póliza
    $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Envio_Poliza', $HostPost ?? $_SERVER['PHP_SELF']);
    $Asunto   = "¡BIENVENIDO A KASU!";
    $Email    = $EmailPost;
    $FullName = $FullNamePost;
    $data = [
        'Cte'    => $FullName,
        'DirUrl' => base64_encode((string)$IdContactPost),
    ];
    $Id  = $IdVenta;
    $Msg = "Se envió la póliza al cliente";

} elseif (!empty($EnviarFichas)) {  // Fichas de pago
    $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Envio_Fichas', $HostPost ?? $_SERVER['PHP_SELF']);
    $Asunto   = "ENVÍO DE FICHAS DE PAGO";
    $Email    = $EmailPost;
    $FullName = $basicas->BuscarCampos($mysqli, "Nombre", "Venta", "Id", $IdVenta);
    $data = [
        'Cte'    => $FullName,
        'DirUrl' => "https://kasu.com.mx/login/Generar_PDF/Fichas_Pago_pdf.php?Cte=" . base64_encode((string)$IdVenta),
    ];
    $Id  = $IdVenta;
    $Msg = "Se enviaron las fichas de pago";

} elseif (!empty($EnviarEdoCta)) {  // Estado de cuenta
    $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Envio_Edo_Cta', $HostPost ?? $_SERVER['PHP_SELF']);
    $Asunto   = "ENVÍO DE ESTADO DE CUENTA";
    $FullName = $Usuario;
    $Email    = $EmailPost;
    $data = [
        'Cte'    => $FullName,
        'DirUrl' => "https://kasu.com.mx/login/Generar_PDF/Estado_Cuenta_pdf.php?busqueda=" . base64_encode((string)$IdVenta),
    ];
    $Id  = $IdVenta;
    $Msg = "Se envió el estado de cuenta";

} elseif (!empty($EnFi)) {  // Link de pago Mercado Pago
    $Asunto   = "PAGO PENDIENTE";
    $Email    = $basicas->BuscarCampos($mysqli, "Mail",   "Contacto", "id", $_SESSION["Cnc"] ?? 0);
    $FullName = $basicas->BuscarCampos($mysqli, "Nombre", "Usuario",  "IdContact", $_SESSION["Cnc"] ?? 0);
    $Id       = $_SESSION["Cnc"] ?? '';
    if ((string)$EnFi === '1') {
        $DirUrl = "https://www.mercadopago.com.mx/checkout/v1/redirect?preference-id=" . $hash;
    } else {
        $DirUrl = "https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_plan_id=" . $hash;
    }
    $data = ['Cte' => $FullName, 'DirUrl' => $DirUrl];
    $stat = "3";

} elseif (!empty($MxVta)) {  // Recordatorio de pago por venta
    $FullName = $basicas->BuscarCampos($mysqli, "Nombre",    "Venta",    "Id", $MxVta);
    $CnTo     = $basicas->BuscarCampos($mysqli, "IdContact", "Venta",    "Id", $MxVta);
    $Email    = $basicas->BuscarCampos($mysqli, "Mail",      "Contacto", "id", $CnTo);
    $DirUrl   = base64_encode((string)$MxVta);
    $Asunto   = "PAGO PENDIENTE";
    $data = ['Cte' => $FullName, 'DirUrl' => $DirUrl];
    $Id   = $MxVta;
    $stat = "2";

} elseif (!empty($ProReIn)) {  // Bienvenida prospecto
    $FullName = $basicas->BuscarCampos($pros, "FullName", "prospectos", "Id", $ProReIn);
    $Email    = $basicas->BuscarCampos($pros, "Email",    "prospectos", "Id", $ProReIn);
    $Asunto   = "¡BIENVENIDO A KASU!";
    $Id       = $ProReIn;

    if ($Servicio === "UNIVERSITARIO") {
        $DirUrl = "https://kasu.com.mx/productos.php?Art=2";
    } elseif ($Servicio === "RETIRO") {
        $DirUrl = "https://kasu.com.mx/productos.php?Art=3";
    } elseif ($Servicio === "POLICIAS") {
        $DirUrl = "https://kasu.com.mx/productos.php?Art=4";
    } else {
        $DirUrl = "https://kasu.com.mx/productos.php?Art=1";
    }
    $data = ['Cte' => $FullName, 'DirUrl' => $DirUrl];
    $stat = "3";

} else { // Genérico
    $Asunto   = "KASU";
    $FullName = "Usuario";
    $Email    = $EmailPost ?: "";
    $data     = ['Cte' => $FullName, 'DirUrl' => ""];
    $Id       = "";
}

// ========= Cuerpo HTML =========
$mensa = $Correo->Mensaje($Asunto, $data, $Id);
if (!is_string($mensa) || $mensa === '') {
    $mensa = '<p>Estimado(a) ' . htmlspecialchars($FullName ?: 'Cliente', ENT_QUOTES, 'UTF-8') . '.</p>';
}

// ========= Autoload + .env =========
$root = realpath(__DIR__ . '/..') ?: __DIR__;
require $root . '/vendor/autoload.php';

// Exporta a getenv()/$_ENV/$_SERVER
if (class_exists(\Dotenv\Dotenv::class) && is_file($root . '/.env')) {
    \Dotenv\Dotenv::createUnsafeImmutable($root)->safeLoad();
}

// Helper entorno
function env(string $k, ?string $default = null): ?string {
    $v = $_ENV[$k] ?? $_SERVER[$k] ?? getenv($k);
    if ($v === false || $v === null || $v === '') return $default;
    return is_string($v) ? $v : $default;
}

// Helper redirección con Msg
function redirect_with_msg(string $baseUrl, string $msg): never {
    $sep = str_contains($baseUrl, '?') ? '&' : '?';
    header('Location: ' . $baseUrl . $sep . 'Msg=' . urlencode($msg), true, 303);
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$accion = !empty($EnCoti)        ? 'EnCoti'
        : (!empty($EnviarPoliza) ? 'EnviarPoliza'
        : (!empty($EnviarFichas) ? 'EnviarFichas'
        : (!empty($EnviarEdoCta) ? 'EnviarEdoCta'
        : (!empty($EnFi)         ? 'EnFi'
        : (!empty($MxVta)        ? 'MxVta'
        : (!empty($ProReIn)      ? 'ProReIn' : 'Generic'))))));

$destinatarioValido = (bool)filter_var($Email, FILTER_VALIDATE_EMAIL);
if ($destinatarioValido) {
    $mail = new PHPMailer(true);
    try {
        // SMTP
        $mail->isSMTP();
        $mail->Host          = env('SMTP_HOST', 'smtp.hostinger.mx');
        $mail->SMTPAuth      = true;
        $mail->Username      = env('SMTP_USER', '');
        $mail->Password      = env('SMTP_PASS', '');
        $mail->SMTPSecure    = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port          = (int)env('SMTP_PORT', '587');
        $mail->CharSet       = 'UTF-8';
        $mail->Timeout       = 15;
        $mail->SMTPKeepAlive = true;

        // From / Reply-To / Bounce
        $fromEmail = env('FROM_EMAIL', 'atncliente@kasu.com.mx');
        $fromName  = env('FROM_NAME',  'KASU');
        $replyTo   = env('REPLY_TO',   $fromEmail);
        $bounce    = env('BOUNCE_EMAIL', '');

        $mail->setFrom($fromEmail, $fromName);
        $mail->addReplyTo($replyTo, $fromName);
        if ($bounce !== '') { $mail->Sender = $bounce; }

        // Destino
        $mail->addAddress($Email, $FullName ?: $Email);

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $Asunto ?: 'KASU';
        $mail->Body    = $mensa;
        $mail->AltBody = strip_tags($mensa);

        // Idempotencia (60s)
        $lockKey = 'mail_lock:' . $accion . ':' . sha1(($Id ?: '0') . '|' . strtolower($Email));
        $now     = time();
        if (empty($_SESSION[$lockKey]) || ($now - (int)$_SESSION[$lockKey]) >= 60) {
            $mail->send();
            $_SESSION[$lockKey] = $now;
            $Msg = $Msg ?: 'Correo enviado';
        } else {
            $Msg = 'Correo ya enviado recientemente';
        }
    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $mail->ErrorInfo);
        $Msg = 'No se pudo enviar el correo';
    }
} else {
    $Msg = 'No se pudo enviar: correo inválido.';
}

// ========= Redirecciones =========
if ($Redireccion !== '') {
    redirect_with_msg($Redireccion, (string)$Msg,);

} elseif (!empty($HostPost)) {
    header(
        'Location: https://kasu.com.mx' . $HostPost
        . '?Vt=1&Msg=' . urlencode($Msg ?? '')
        . '&name=' . urlencode($NombrePost ?? '')
        . '&busqueda=' . urlencode((string)($IdVenta ?? '')),
        true,
        303
    );
    exit;

} else {
    echo "<script>
            alert(" . json_encode($Msg ?: 'Se ha procesado el envío de correo') . ");
            window.location.href = '../login/Mesa_Herramientas.php';
          </script>";
    exit;
}