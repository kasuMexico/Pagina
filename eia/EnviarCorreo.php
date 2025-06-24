<?php
session_start();
date_default_timezone_set('America/Mexico_City');

require_once 'librerias.php';
$Correo = new Correo();

// === Filtrado de Variables ===
$EnCoti       = filter_input(INPUT_GET, 'EnCoti', FILTER_SANITIZE_STRING);
$hash         = filter_input(INPUT_GET, 'hash', FILTER_SANITIZE_STRING);
$MxVta        = filter_input(INPUT_GET, 'MxVta', FILTER_SANITIZE_STRING);
$EnFi         = filter_input(INPUT_GET, 'EnFi', FILTER_SANITIZE_STRING);
$ProReIn      = filter_input(INPUT_GET, 'ProReIn', FILTER_SANITIZE_STRING);
$Servicio     = filter_input(INPUT_GET, 'Servicio', FILTER_SANITIZE_STRING);

$EnviarPoliza = filter_input(INPUT_POST, 'EnviarPoliza', FILTER_SANITIZE_STRING);
$EnviarFichas = filter_input(INPUT_POST, 'EnviarFichas', FILTER_SANITIZE_STRING);
$EnviarEdoCta = filter_input(INPUT_POST, 'EnviarEdoCta', FILTER_SANITIZE_STRING);

$Descripcion  = filter_input(INPUT_POST, 'Descripcion', FILTER_SANITIZE_STRING);
$IdVenta      = filter_input(INPUT_POST, 'IdVenta', FILTER_SANITIZE_STRING);
$Usuario      = filter_input(INPUT_POST, 'Usuario', FILTER_SANITIZE_STRING);
$Event        = filter_input(INPUT_POST, 'Event', FILTER_SANITIZE_STRING);
$Cupon        = filter_input(INPUT_POST, 'Cupon', FILTER_SANITIZE_STRING);

$stat = ""; // para redirección
$Asunto = "";
$Email = "";
$FullName = "";
$Id = "";
// Recibe datos POST correctamente del formulario
echo $FullName = filter_input(INPUT_POST, 'FullName', FILTER_SANITIZE_STRING);
echo $Email    = filter_input(INPUT_POST, 'Email', FILTER_SANITIZE_EMAIL);
echo $Descripcion = filter_input(INPUT_POST, 'Descripcion', FILTER_SANITIZE_STRING);
echo $IdVenta  = filter_input(INPUT_POST, 'IdVenta', FILTER_SANITIZE_STRING);

// ==============================
// Selección de la acción y datos
// ==============================
$data = []; // Inicializa array de datos para el cuerpo del correo

if (!empty($EnCoti)) {
    // Presupuesto de cliente
    $IdProspecto = $basicas->BuscarCampos($pros, "IdProspecto", "PrespEnviado", "Id", $EnCoti);
    $FullName    = $basicas->BuscarCampos($pros, "FullName", "prospectos", "Id", $IdProspecto);
    $Email       = $basicas->BuscarCampos($pros, "Email", "prospectos", "Id", $IdProspecto);
    $Asunto      = "ENVÍO DE ARCHIVO";
    $data = [
        'Cte'    => $FullName,
        'DirUrl' => "https://kasu.com.mx/login/Generar_PDF/Cotizacion_pdf.php?coti={$EnCoti}",
    ];
    $Id = $EnCoti;
    $Msg = "Se ha enviado la cotización al correo registrado de tu cliente";
} elseif (!empty($EnviarPoliza)) {
    $Asunto  = "¡BIENVENIDO A KASU!";
    $FullName = $Usuario; // O de donde corresponda
    $Email = $Email; // Define desde tu lógica
    $data = [
        'Cte'    => $FullName,
        'DirUrl' => $Descripcion,
    ];
    $Id = $IdVenta;
} elseif (!empty($EnviarFichas)) {
    $Asunto  = "PAGO PENDIENTE";
    $FullName = $Usuario;
    $Email = $Email; // Define desde tu lógica
    $data = [
        'Cte'    => $FullName,
        'DirUrl' => $Descripcion, // O lo que corresponda
    ];
    $Id = $IdVenta;
} elseif (!empty($EnviarEdoCta)) {
    $Asunto  = "ENVIO DE ESTADO DE CUENTA";
    $FullName = $Usuario;
    $Email = $Email;
    $data = [
        'Cte'    => $FullName,
        'DirUrl' => "https://kasu.com.mx/login/Generar_PDF/Estado_Cuenta_pdf.php?busqueda=" . base64_encode($IdVenta),
    ];
    $Id = $IdVenta;
} elseif (!empty($EnFi)) {
    $Asunto    = "PAGO PENDIENTE";
    $Email     = $basicas->BuscarCampos($mysqli, "Mail", "Contacto", "id", $_SESSION["Cnc"]);
    $FullName  = $basicas->BuscarCampos($mysqli, "Nombre", "Usuario", "IdContact", $_SESSION["Cnc"]);
    $Id = $_SESSION["Cnc"];
    if ($EnFi == 1) {
        $DirUrl = "https://www.mercadopago.com.mx/checkout/v1/redirect?preference-id=" . $hash;
    } else {
        $DirUrl = "https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_plan_id=" . $hash;
    }
    $data = [
        'Cte'    => $FullName,
        'DirUrl' => $DirUrl,
    ];
    $stat = "3";
} elseif (!empty($MxVta)) {
    $FullName      = $basicas->BuscarCampos($mysqli, "Nombre", "Venta", "Id", $MxVta);
    $CnTo          = $basicas->BuscarCampos($mysqli, "IdContact", "Venta", "Id", $MxVta);
    $Email         = $basicas->BuscarCampos($mysqli, "Mail", "Contacto", "id", $CnTo);
    $FechaRegistro = $basicas->BuscarCampos($mysqli, "FechaRegistro", "Venta", "Id", $MxVta);
    $DirUrl        = base64_encode($MxVta);
    $Asunto        = "PAGO PENDIENTE";
    $data = [
        'Cte'    => $FullName,
        'DirUrl' => $DirUrl,
    ];
    $Id = $MxVta;
    $stat = "2";
} elseif (!empty($ProReIn)) {
    $FullName = $basicas->BuscarCampos($pros, "FullName", "prospectos", "Id", $ProReIn);
    $Email    = $basicas->BuscarCampos($pros, "Email", "prospectos", "Id", $ProReIn);
    $Asunto   = "¡BIENVENIDO A KASU!";
    $Id = $ProReIn;
    if ($Servicio === "UNIVERSITARIO") {
        $DirUrl = "https://kasu.com.mx/productos.php?Art=2";
    } elseif ($Servicio === "RETIRO") {
        $DirUrl = "https://kasu.com.mx/productos.php?Art=3";
    } elseif ($Servicio === "POLICIAS") {
        $DirUrl = "https://kasu.com.mx/productos.php?Art=4";
    } else {
        $DirUrl = "https://kasu.com.mx/productos.php?Art=1";
    }
    $data = [
        'Cte'    => $FullName,
        'DirUrl' => $DirUrl,
    ];
    $stat = "3";
} else {
    $Asunto  = "SIN ASUNTO";
    $FullName = "Usuario";
    $Email   = "";
    $data = [
        'Cte'    => $FullName,
        'DirUrl' => "",
    ];
    $Id = "";
}

// === Generar el mensaje HTML del correo ===
echo $mensa = $Correo->Mensaje($Asunto, $data, $Id);

// ================================
// Envío de correo mediante PHPMailer
// ================================
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
    $mail->setFrom('atncliente@kasu.com.mx', 'KASU');
    $mail->addAddress($Email, $FullName);
    $mail->isHTML(true);
    $mail->Subject = $Asunto;
    $mail->Body    = $mensa;
    $mail->send();
} catch (Exception $e) {
    echo "Error al enviar el mensaje: {$mail->ErrorInfo}";
}

// =========== Redirección y cierre ===========
if (!empty($_GET)) {
    if (!empty($EnCoti)) {
        header('Location: ' . $Host . '&Msg=' . urlencode($Msg));
        exit;
    } elseif (!empty($ProReIn)) {
        header('Location: https://kasu.com.mx/index.php?Msg=' . urlencode("Felicidades, ya te hemos enviado un correo y en breve un ejecutivo te contactará"));
        exit;
    }
    session_destroy();
    header('Location: https://kasu.com.mx/registro.php?stat=' . urlencode($stat) . '&Cte=' . urlencode($FullName) . '&liga=' . urlencode($data['DirUrl']));
    exit;
} else {
    echo "<script>
            alert('Se ha enviado el correo electrónico');
            window.location.href = '../login/Mesa_Herramientas.php';
          </script>";
}
?>
