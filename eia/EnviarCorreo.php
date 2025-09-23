<?php
session_start();
date_default_timezone_set('America/Mexico_City');

require_once 'librerias.php';
$Correo = new Correo();

// ========= Inputs =========
$EnCoti       = filter_input(INPUT_GET,  'EnCoti',      FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$hash         = filter_input(INPUT_GET,  'hash',        FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$MxVta        = filter_input(INPUT_GET,  'MxVta',       FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$EnFi         = filter_input(INPUT_GET,  'EnFi',        FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$ProReIn      = filter_input(INPUT_GET,  'ProReIn',     FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$Servicio     = filter_input(INPUT_GET,  'Servicio',    FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$EnviarPoliza = filter_input(INPUT_POST, 'EnviarPoliza',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$EnviarFichas = filter_input(INPUT_POST, 'EnviarFichas',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$EnviarEdoCta = filter_input(INPUT_POST, 'EnviarEdoCta',FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$Descripcion  = filter_input(INPUT_POST, 'Descripcion', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$IdVenta      = filter_input(INPUT_POST, 'IdVenta',     FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$Usuario      = filter_input(INPUT_POST, 'Usuario',     FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$Event        = filter_input(INPUT_POST, 'Event',       FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$Cupon        = filter_input(INPUT_POST, 'Cupon',       FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$FullNamePost = filter_input(INPUT_POST, 'FullName',    FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$EmailPost    = filter_input(INPUT_POST, 'Email',       FILTER_SANITIZE_EMAIL);
$IdContactPost= filter_input(INPUT_POST, 'IdContact',   FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$HostPost     = filter_input(INPUT_POST, 'Host',        FILTER_UNSAFE_RAW);
$NombrePost   = filter_input(INPUT_POST, 'nombre',      FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// ========= One-shot token para evitar doble envío por doble submit / refresh =========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['mail_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['mail_token'] ?? '', $token)) {
        http_response_code(400);
        exit('Solicitud inválida');
    }
    unset($_SESSION['mail_token']); // invalida token
}

// ========= Variables base correo =========
$stat = "";
$Asunto = "";
$Email = "";
$FullName = "";
$Id = "";
$Msg = "";
$data = [];

// ========= Selección de acción =========
if (!empty($EnCoti)) {
    $IdProspecto = $basicas->BuscarCampos($pros, "IdProspecto", "PrespEnviado", "Id", $EnCoti);
    $FullName    = $basicas->BuscarCampos($pros, "FullName", "prospectos", "Id", $IdProspecto);
    $Email       = $basicas->BuscarCampos($pros, "Email", "prospectos", "Id", $IdProspecto);
    $Asunto      = "ENVÍO DE ARCHIVO";
    $data = [
        'Cte'    => $FullName,
        'DirUrl' => "https://kasu.com.mx/login/Generar_PDF/Cotizacion_pdf.php?coti={$EnCoti}",
    ];
    $Id  = $EnCoti;
    $Msg = "Se ha enviado la cotización al correo registrado de tu cliente";

} elseif (!empty($EnviarPoliza)) {  //Enviar poliza para descargar por el usuario
    // Auditoría/GPS/Fingerprint
    $ids = $seguridad->auditoria_registrar(
        $mysqli,
        $basicas,
        $_POST,
        'Envio_Poliza',
        $HostPost ?? $_SERVER['PHP_SELF']
    );
    //Armado de Correo para envio
    $Asunto   = "¡BIENVENIDO A KASU!";
    $Email    = $EmailPost;
    $FullName = $FullNamePost;
    $data = [
        'Cte'    => $FullName,
        'DirUrl' => base64_encode($IdContactPost),
    ];
    $Id  = $IdVenta;
    $Msg = "Se ha enviado la poliza de tu cliente";

} elseif (!empty($EnviarFichas)) { //Enivar fichas de pago para descargar al usuario
    // Auditoría/GPS/Fingerprint
    $ids = $seguridad->auditoria_registrar(
        $mysqli,
        $basicas,
        $_POST,
        'Envio_Fichas',
        $HostPost ?? $_SERVER['PHP_SELF']
    );
    
    //Armado de Correo para envio
    $Asunto   = "ENVIO DE FICHAS";
    $Email    = $EmailPost;
    $FullName = $basicas->BuscarCampos($mysqli, "Nombre", "Venta", "Id", $IdVenta);
;
    $data = [
        'Cte'    => $FullName,
        'DirUrl' => "https://kasu.com.mx/login/Generar_PDF/Fichas_Pago_pdf.php?Cte=" . base64_encode($IdVenta),
    ];
    $Id = $IdVenta;
    
    $Msg = "Se han enviado las fichas de pago de tu cliente";

} elseif (!empty($EnviarEdoCta)) { //Enviar estado de cuenta de Cliente
    // Auditoría/GPS/Fingerprint
    $ids = $seguridad->auditoria_registrar(
        $mysqli,
        $basicas,
        $_POST,
        'Envio_Edo_Cta',
        $HostPost ?? $_SERVER['PHP_SELF']
    );
    //Armado de Correo para envio
    $Asunto   = "ENVIO DE ESTADO DE CUENTA";
    $FullName = $Usuario;
    $Email    = $EmailPost;
    $data = [
        'Cte'    => $FullName,
        'DirUrl' => "https://kasu.com.mx/login/Generar_PDF/Estado_Cuenta_pdf.php?busqueda=" . base64_encode($IdVenta),
    ];
    $Id = $IdVenta;
    
    $Msg = "Se ha enviado el estado de cuenta al correo registrado de tu cliente";

} elseif (!empty($EnFi)) {
    $Asunto   = "PAGO PENDIENTE";
    $Email    = $basicas->BuscarCampos($mysqli, "Mail",   "Contacto", "id", $_SESSION["Cnc"]);
    $FullName = $basicas->BuscarCampos($mysqli, "Nombre", "Usuario",  "IdContact", $_SESSION["Cnc"]);
    $Id       = $_SESSION["Cnc"];
    if ($EnFi == 1) {
        $DirUrl = "https://www.mercadopago.com.mx/checkout/v1/redirect?preference-id=" . $hash;
    } else {
        $DirUrl = "https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_plan_id=" . $hash;
    }
    $data = ['Cte' => $FullName, 'DirUrl' => $DirUrl];
    $stat = "3";

} elseif (!empty($MxVta)) {
    $FullName      = $basicas->BuscarCampos($mysqli, "Nombre",      "Venta",    "Id", $MxVta);
    $CnTo          = $basicas->BuscarCampos($mysqli, "IdContact",   "Venta",    "Id", $MxVta);
    $Email         = $basicas->BuscarCampos($mysqli, "Mail",        "Contacto", "id", $CnTo);
    $FechaRegistro = $basicas->BuscarCampos($mysqli, "FechaRegistro","Venta",   "Id", $MxVta);
    $DirUrl        = base64_encode($MxVta);
    $Asunto        = "PAGO PENDIENTE";
    $data = ['Cte' => $FullName, 'DirUrl' => $DirUrl];
    $Id   = $MxVta;
    $stat = "2";

} elseif (!empty($ProReIn)) {
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

} else {
    $Asunto   = "SIN ASUNTO";
    $FullName = "Usuario";
    $Email    = $EmailPost ?: "";
    $data     = ['Cte' => $FullName, 'DirUrl' => ""];
    $Id       = "";
}

// ========= Cuerpo HTML =========
$mensa = $Correo->Mensaje($Asunto, $data, $Id);

// ========= PHPMailer =========
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.mx';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'atncliente@kasu.com.mx';
    $mail->Password   = '01J76e90@';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom('atncliente@kasu.com.mx', 'KASU');
    if (!empty($Email)) {
        $mail->addAddress($Email, $FullName ?: $Email);
    }

    $mail->isHTML(true);
    $mail->Subject = $Asunto;
    $mail->Body    = $mensa;

    // Anti-doble envío (15s)
    $key = 'maillock:poliza:' . ($Id ?: '0');
    if (empty($_SESSION[$key]) || (time() - $_SESSION[$key]) >= 15) {
        $_SESSION[$key] = time();
        $mail->send();
    }
} catch (Exception $e) {
    echo "Error al enviar el mensaje: {$mail->ErrorInfo}";
}

// ========= Redirecciones =========
if (!empty($_GET)) {
    if (!empty($EnCoti)) {
        $dest = ($HostPost ?? '/login/Mesa_Herramientas.php') . '?Msg=' . urlencode($Msg);
        header('Location: ' . $dest);
        exit;
    } elseif (!empty($ProReIn)) {
        header('Location: https://kasu.com.mx/index.php?Msg=' . urlencode("Felicidades, ya te hemos enviado un correo y en breve un ejecutivo te contactará"));
        exit;
    }
    session_destroy();
    header('Location: https://kasu.com.mx/registro.php?stat=' . urlencode($stat) . '&Cte=' . urlencode($FullName) . '&liga=' . urlencode($data['DirUrl']));
    exit;

} elseif (!empty($HostPost)) {
    header(
    'Location: https://kasu.com.mx' . $HostPost
    . '?Vt=1&Msg=' . urlencode($Msg ?? '')
    . '&name=' . urlencode($NombrePost ?? '')
    . '&busqueda=' . urlencode((string)($IdVenta ?? ''))
    );
    exit;

} else {
    echo "<script>
            alert('Se ha enviado el correo electrónico');
            window.location.href = '../login/Mesa_Herramientas.php';
          </script>";
}
