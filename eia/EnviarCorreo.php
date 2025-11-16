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

/* ===== Token por POST o GET  recepcion y validacion===== */
$token  = $_POST['mail_token'] ?? $_GET['mail_token'] ?? '';
$tok_ok = ($token && hash_equals($_SESSION['mail_token'] ?? '', $token));

if (!$tok_ok) {
  http_response_code(400);
  exit('Solicitud inválida');
}

// One-shot
unset($_SESSION['mail_token']);

/* ===== Debug helpers (no alteran flujo) ===== */
/* Cambia a 1 para activar debug, 0 para desactivarlo. 
*  Debes comentar los reenvios de la pagina para poder ver la impresion del debug
*/
$DBG = 0;

function dbg(string $label, $val = null): void {
  global $DBG;
  $line = "[MAILDBG] {$label}";
  if ($DBG) {
    echo "<pre>{$line}\n";
    if ($val !== null) { print_r($val); }
    echo "</pre>";
  }
  // Envía un resumen al error_log para tener traza aun sin salida HTML
  if ($val === null) {
    error_log($line);
  } else {
    $snippet = @substr(print_r($val, true), 0, 2000);
    error_log($line . ' :: ' . $snippet);
  }
}

function mask_email(?string $e): string {
  if (!$e) return '';
  if (!strpos($e, '@')) return $e;
  [$u,$d] = explode('@',$e,2);
  $u2 = strlen($u) > 2 ? substr($u,0,2) . str_repeat('*', max(1, strlen($u)-2)) : $u . '*';
  return $u2 . '@' . $d;
}

/* ===== Inputs ===== */
$EnCoti        = filter_input(INPUT_GET,  'EnCoti',         FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$hash          = filter_input(INPUT_GET,  'hash',           FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if ($hash === null || $hash === false || $hash === '') {
  $hash = filter_input(INPUT_POST, 'hash', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}
$MxVta         = filter_input(INPUT_GET,  'MxVta',          FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$EnFi          = filter_input(INPUT_POST, 'EnFi',           FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if ($EnFi === null || $EnFi === false || $EnFi === '') {
  $EnFi = filter_input(INPUT_GET, 'EnFi', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}
$ProReIn       = filter_input(INPUT_GET,  'ProReIn',        FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$Servicio      = filter_input(INPUT_GET,  'Servicio',       FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$HostGet       = filter_input(INPUT_GET,  'Host',           FILTER_UNSAFE_RAW);
$NombreGet     = filter_input(INPUT_POST, 'name',           FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$EnviarPoliza  = filter_input(INPUT_POST, 'EnviarPoliza',   FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$EnviarFichas  = filter_input(INPUT_POST, 'EnviarFichas',   FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$EnviarEdoCta  = filter_input(INPUT_POST, 'EnviarEdoCta',   FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$Descripcion   = filter_input(INPUT_POST, 'Descripcion',    FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$Usuario       = filter_input(INPUT_POST, 'Usuario',        FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$Event         = filter_input(INPUT_POST, 'Event',          FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$Cupon         = filter_input(INPUT_POST, 'Cupon',          FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$FullNamePost  = filter_input(INPUT_POST, 'FullName',       FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$HostPost      = filter_input(INPUT_POST, 'Host',           FILTER_UNSAFE_RAW);
$NombrePost    = filter_input(INPUT_POST, 'nombre',         FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$StatusPost    = filter_input(INPUT_POST, 'Status',         FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$IdVenta       = $_POST['IdVenta']   ?? $_GET['IdVenta']   ?? null;
$IdContactPost = $_POST['IdContact'] ?? $_GET['IdContact'] ?? null;
$EmailPost = filter_input(INPUT_POST, 'Email', FILTER_VALIDATE_EMAIL) ?? filter_input(INPUT_GET, 'Email', FILTER_VALIDATE_EMAIL);

/* ===== Parámetros que enviaste en la URL ===== */
$Vta_Liquidada = (int)($_GET['Vta_Liquidada'] ?? $_POST['Vta_Liquidada'] ?? 0);
$Redireccion   = (string)($_GET['Redireccion']   ?? $_POST['Redireccion']   ?? '');
$Msg           = (string)($_GET['Msg']           ?? $_POST['Msg']           ?? '');

dbg('INPUT.GET', $_GET);
dbg('INPUT.POST', $_POST);

/* ===== Variables base ===== */
$stat = ""; $Asunto = ""; $Email = ""; $FullName = ""; $Id = ""; $Msg = ""; $data = []; $Redireccion = "";
dbg('Inicio selección de acción');

/* ===== Selección de acción ===== */
if (!empty($EnCoti)) { // Enviar cotización (seguro) Revisado y funcionado 7 Nov 2025
  //Auditoria de registro de evento
  $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Registro_Prospecto', $HostPost ?? $_SERVER['PHP_SELF']);
  
  $dec = base64_decode($EnCoti, true);
  dbg('Ruta: EnCoti', ['EnCoti'=>$EnCoti, 'dec'=>$dec]);
  if ($dec === false || !ctype_digit($dec)) {
    http_response_code(400);
    exit('Parámetro EnCoti inválido');
  }
  $idPresp = (int)$dec;

  $IdProspecto = (int)$basicas->BuscarCampos($pros, "IdProspecto", "PrespEnviado", "Id", $idPresp);
  dbg('EnCoti Prospecto', ['IdProspecto'=>$IdProspecto]);
  if ($IdProspecto <= 0) {
    $dest = $HostGet ?: '/login/Mesa_Herramientas.php';
    header('Location: ' . $dest . (str_contains((string)$dest, '?') ? '&' : '?') . 'Msg=' . urlencode('No encontramos la cotización solicitada.'), true, 303);
    exit;
  }

  $NombrePros  = (string)$basicas->BuscarCampos($pros, "FullName", "prospectos", "Id", $IdProspecto);
  $Email       = (string)$basicas->BuscarCampos($pros, "Email",    "prospectos", "Id", $IdProspecto);
  $Asunto      = "ENVÍO DE COTIZACIÓN";
  $Id          = (string)$idPresp;

  $DirUrl = 'https://kasu.com.mx/login/Generar_PDF/Cotizacion_pdf.php'
          . '?busqueda=' . rawurlencode($EnCoti)
          . '&Host='     . rawurlencode((string)$HostGet)
          . '&name='     . rawurlencode((string)$NombreGet)
          . '&idp='      . urlencode((string)$IdProspecto);

  $data = ['Nombre'=>$NombrePros, 'DirUrl'=>$DirUrl];

  $Msg = (isset($_GET['Msg']) && $_GET['Msg'] !== '') ? (string)$_GET['Msg'] : ('Se envió la cotización al correo ' . $Email);

  $Redireccion = (string)($_GET['Redireccion'] ?? ($HostGet ?: '/login/Mesa_Herramientas.php'));
  dbg('Redireccion preliminar', $Redireccion);
  if ($Redireccion !== '') {
    $p    = parse_url($Redireccion);
    $host = strtolower($p['host'] ?? '');
    $ok   = ['kasu.com.mx', 'www.kasu.com.mx'];
    if ($host && !in_array($host, $ok, true)) { $Redireccion = '/login/Mesa_Herramientas.php'; }
    $Redireccion .= (str_contains($Redireccion, '?') ? '&' : '?') . 'idp=' . urlencode((string)$IdProspecto);
  }

} elseif (!empty($EnviarPoliza)) {  // Póliza Revisado y funcionado 7 Nov 2025
  dbg('Ruta: EnviarPoliza');
  $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Envio_Poliza', $HostPost ?? $_SERVER['PHP_SELF']);
  $Asunto   = "¡BIENVENIDO A KASU!";
  $Email    = $EmailPost;
  $FullName = $FullNamePost;
  $data = ['Cte'=>$FullName, 'DirUrl'=>base64_encode((string)$IdContactPost)];
  $Id  = $IdVenta;
  $Msg = "Se envió la póliza al cliente";

} elseif (!empty($EnviarFichas)) {  // Fichas de pago Revisado y funcionado 7 Nov 2025
  dbg('Ruta: EnviarFichas', ['IdVenta'=>$IdVenta, 'EmailPost'=>$EmailPost]);
  $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Envio_Fichas', $HostPost ?? $_SERVER['PHP_SELF']);
  $Asunto   = "ENVIO DE FICHAS DE PAGO";
  $Email    = $EmailPost;
  $FullName = $basicas->BuscarCampos($mysqli, "Nombre", "Venta", "Id", $IdVenta);
  $data = [
    'Cte'    => $FullName,
    'DirUrl' => "https://kasu.com.mx/login/Generar_PDF/Fichas_Pago_pdf.php?Cte=" . base64_encode((string)$IdVenta),
  ];
  $Id  = $IdVenta;
  $Msg = "Se enviaron las fichas de pago";
  //echo '<pre>'; print_r($_GET); echo '</pre>'; // existente

} elseif (!empty($EnviarEdoCta)) {  // Estado de cuenta Revisado y funcionado 7 Nov 2025
  dbg('Ruta: EnviarEdoCta', ['IdVenta'=>$IdVenta, 'EmailPost'=>$EmailPost]);
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

} elseif (!empty($EnFi)) {  // Link de pago Mercado Pago funcionado 15 Nov 2025
  $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Envio_Liga_MP', $HostPost ?? $_SERVER['PHP_SELF']);
  $ventaId = (int)($IdVenta ?? 0);
  dbg('Ruta: EnFi', ['EnFi'=>$EnFi, 'IdVenta'=>$ventaId]);

  if ($ventaId <= 0) {
    http_response_code(400);
    exit('Venta inválida para enviar liga de pago.');
  }

  $sqlLiga = "
    SELECT
      v.Id,
      v.Nombre,
      v.IdFIrma,
      c.Mail          AS email,
      vm.mp_init_point AS mp_init_point
    FROM Venta v
    LEFT JOIN Contacto c        ON c.id = v.IdContact
    LEFT JOIN VentasMercadoPago vm ON vm.folio = v.IdFIrma
    WHERE v.Id = ?
    LIMIT 1
  ";

  $stmtLiga = $mysqli->prepare($sqlLiga);
  $stmtLiga->bind_param('i', $ventaId);
  $stmtLiga->execute();
  $infoLiga = $stmtLiga->get_result()->fetch_assoc() ?: [];
  $stmtLiga->close();

  if (!$infoLiga) {
    http_response_code(404);
    exit('No se encontró la venta para enviar la liga de pago.');
  }

  $Email    = $EmailPost ?: (string)($infoLiga['email'] ?? '');
  $FullName = (string)($infoLiga['Nombre'] ?? 'Cliente KASU');
  $Id       = $ventaId;
  $folio    = trim((string)($infoLiga['IdFIrma'] ?? ''));
  $DirUrl   = trim((string)($infoLiga['mp_init_point'] ?? ''));

  if ($DirUrl === '' && $folio !== '') {
    $DirUrl = 'https://kasu.com.mx/pago/crear_preferencia.php?ref=' . rawurlencode($folio);
  } elseif ($DirUrl === '' && $hash !== null && $hash !== false && $hash !== '') {
    if ((string)$EnFi === '1') {
      $DirUrl = "https://www.mercadopago.com.mx/checkout/v1/redirect?preference-id=" . $hash;
    } else {
      $DirUrl = "https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_plan_id=" . $hash;
    }
  }

  if ($DirUrl === '') {
    http_response_code(500);
    exit('No se encontró liga de pago para esta venta.');
  }

  $Asunto = "PAGO PENDIENTE";
  $data   = ['Cte'=>$FullName, 'DirUrl'=>$DirUrl];
  $Msg    = "Se envió la liga de pago al cliente";
  $stat   = "3";

} elseif (!empty($Vta_Liquidada)) {  // Bienvenida al cliente cuando ya esta pagado su servicio.
  dbg('Ruta: Poliza_Liquidada');
  $Asunto    = "¡BIENVENIDO A KASU!";
  $FullName  = $basicas->BuscarCampos($mysqli, "Nombre", "Venta", "Id", $Vta_Liquidada);
  $IdContact = $basicas->BuscarCampos($mysqli, "IdContact", "Venta",  "Id", $Vta_Liquidada);
  $Email     = $basicas->BuscarCampos($mysqli, "Email", "Usuario", "IdContact", $IdContact);
  $data      = ['Cte'=>$FullName, 'DirUrl'=>base64_encode((string)$IdContact)];
  $Id        = $Vta_Liquidada;
} elseif (!empty($ProReIn)) {  // Bienvenida al cliente cuando se registra como prospecto.
  dbg('Ruta: ProReIn', ['ProReIn'=>$ProReIn, 'Servicio'=>$Servicio]);
  $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Envio_Bienvenida', $HostPost ?? $_SERVER['PHP_SELF']);
  $FullName = $basicas->BuscarCampos($pros, "FullName", "prospectos", "Id", $ProReIn);
  $Email    = $basicas->BuscarCampos($pros, "Email",    "prospectos", "Id", $ProReIn);
  $Asunto   = "¡BIENVENIDO A KASU!";
  $Id       = $ProReIn;

  if ($Servicio === "UNIVERSITARIO") { $DirUrl = "https://kasu.com.mx/productos.php?Art=2";
  } elseif ($Servicio === "RETIRO")  { $DirUrl = "https://kasu.com.mx/productos.php?Art=3";
  } elseif ($Servicio === "POLICIAS"){ $DirUrl = "https://kasu.com.mx/productos.php?Art=4";
  } else {                             $DirUrl = "https://kasu.com.mx/productos.php?Art=1"; }
  $data = ['Cte'=>$FullName, 'DirUrl'=>$DirUrl];
  $stat = "3";

} else { // Genérico
  dbg('Ruta: Generic');
  $Asunto   = "KASU";
  $FullName = "Usuario";
  $Email    = $EmailPost ?: "";
  $data     = ['Cte'=>$FullName, 'DirUrl'=>""];
  $Id       = "";
}

dbg('Acción seleccionada', [
  'Asunto'=>$Asunto,
  'Email'=>mask_email($Email),
  'FullName'=>$FullName,
  'Id'=>$Id,
  'DirUrl'=>$data['DirUrl'] ?? null
]);

/* ===== Cuerpo HTML ===== */
$mensa = $Correo->Mensaje($Asunto, $data, $Id);
dbg('Template generado', ['len'=>is_string($mensa)?strlen($mensa):0, 'ok'=>is_string($mensa)]);
if (!is_string($mensa) || $mensa === '') {
  $mensa = '<p>Estimado(a) ' . htmlspecialchars($FullName ?: 'Cliente', ENT_QUOTES, 'UTF-8') . '.</p>';
}

/* ===== Autoload + .env ===== */
$root = realpath(__DIR__ . '/..') ?: __DIR__;
require $root . '/vendor/autoload.php';
dbg('Composer autoload', $root . '/vendor/autoload.php');

// Exporta a getenv()/$_ENV/$_SERVER
if (class_exists(\Dotenv\Dotenv::class) && is_file($root . '/.env')) {
  \Dotenv\Dotenv::createUnsafeImmutable($root)->safeLoad();
  dbg('.env cargado', true);
} else {
  dbg('.env cargado', false);
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
dbg('Validación destinatario', ['email'=>mask_email($Email), 'valido'=>$destinatarioValido]);

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

    // Debug SMTP solo si DBG
    if ($DBG) {
      $mail->SMTPDebug   = 2; // client & server messages
      $mail->Debugoutput = static function($str, $level){ error_log("[SMTP:$level] $str"); };
    }

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
    dbg('Idempotencia', ['lockKey'=>$lockKey, 'last'=>$_SESSION[$lockKey] ?? null, 'now'=>$now]);
    if (empty($_SESSION[$lockKey]) || ($now - (int)$_SESSION[$lockKey]) >= 60) {
      dbg('Intentando enviar', ['accion'=>$accion, 'asunto'=>$Asunto]);
      $mail->send();
      $_SESSION[$lockKey] = $now;
      $Msg = $Msg ?: 'Correo enviado';
      dbg('Resultado envío', ['ok'=>true, 'msg'=>$Msg]);
    } else {
      $Msg = 'Correo ya enviado recientemente';
      dbg('Resultado envío', ['ok'=>false, 'msg'=>$Msg]);
    }
  } catch (Exception $e) {
    error_log('PHPMailer error: ' . $mail->ErrorInfo);
    dbg('Excepción PHPMailer', ['ErrorInfo'=>$mail->ErrorInfo, 'ex'=>$e->getMessage()]);
    $Msg = 'No se pudo enviar el correo';
  }
} else {
  $Msg = 'No se pudo enviar: correo inválido.';
  dbg('Abortado por email inválido', $Email);
}
  /**
   * ========= Redirecciones =========
   * Se debe comentar las siguientes ligas para el Debug
   */

 if ($Redireccion !== '') {
     redirect_with_msg($Redireccion, (string)$Msg,);

 } elseif (!empty($HostPost)) {
     $qs = http_build_query([
         'Vt'       => 1,
         'Msg'      => $Msg ?? '',
         'name'     => $NombrePost ?? '',
         'nombre'   => $NombrePost ?? '',
         'Status'   => $StatusPost ?? '',
         'busqueda' => (string)($IdVenta ?? ''),
     ]);
     header('Location: https://kasu.com.mx' . $HostPost . '?' . $qs, true, 303);
     exit;

 } else {
     echo "<script>
             alert(" . json_encode($Msg ?: 'Se ha procesado el envío de correo') . ");
             window.location.href = '../login/Mesa_Herramientas.php';
           </script>";
     exit;
 }
?>
