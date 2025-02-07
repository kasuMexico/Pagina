<?php
// Inicia la sesión y establece la zona horaria
session_start();
date_default_timezone_set('America/Mexico_City');

// Se incluye el archivo que carga las clases, funciones y conexiones necesarias
require_once 'librerias.php';

// ====================
// Filtrado de Variables
// ====================
// Para evitar el uso de eval y problemas de seguridad, se accede directamente a los datos.
// Se pueden utilizar filtros para GET y POST según convenga:
$EnCoti       = filter_input(INPUT_GET, 'EnCoti', FILTER_SANITIZE_STRING);
$hash         = filter_input(INPUT_GET, 'hash', FILTER_SANITIZE_STRING);
$MxVta        = filter_input(INPUT_GET, 'MxVta', FILTER_SANITIZE_STRING);
$EnFi         = filter_input(INPUT_GET, 'EnFi', FILTER_SANITIZE_STRING);
$ProReIn      = filter_input(INPUT_GET, 'ProReIn', FILTER_SANITIZE_STRING);
$Servicio     = filter_input(INPUT_GET, 'Servicio', FILTER_SANITIZE_STRING);

// Para los botones que se envían vía POST
$EnviarPoliza = filter_input(INPUT_POST, 'EnviarPoliza', FILTER_SANITIZE_STRING);
$EnviarFichas = filter_input(INPUT_POST, 'EnviarFichas', FILTER_SANITIZE_STRING);
$EnviarEdoCta = filter_input(INPUT_POST, 'EnviarEdoCta', FILTER_SANITIZE_STRING);

// También se pueden obtener otras variables POST directamente
$Descripcion  = filter_input(INPUT_POST, 'Descripcion', FILTER_SANITIZE_STRING);
$IdVenta      = filter_input(INPUT_POST, 'IdVenta', FILTER_SANITIZE_STRING);
$Usuario      = filter_input(INPUT_POST, 'Usuario', FILTER_SANITIZE_STRING);
$Event        = filter_input(INPUT_POST, 'Event', FILTER_SANITIZE_STRING);
$Cupon        = filter_input(INPUT_POST, 'Cupon', FILTER_SANITIZE_STRING);

// Inicializamos variables opcionales (si no están definidas, las dejamos vacías)
$Titulo1 = $Desc1 = $dirUrl2 = $imag2 = $Titulo2 = $Desc2 = $dirUrl3 = $imag3 = $Titulo3 = $Desc3 = $dirUrl4 = $imag4 = $Titulo4 = $Desc4 = "";
$stat = ""; // Variable para redirección

// ====================
// Selección de la rama de acción
// ====================
if (!empty($EnCoti)) {
    // Envío de presupuesto de cliente.
    // Se asume que $pros es la conexión correspondiente a la base de datos de prospectos.
    $IdProspecto = Basicas::BuscarCampos($pros, "IdProspecto", "PrespEnviado", "Id", $EnCoti);
    $FullName    = Basicas::BuscarCampos($pros, "FullName", "prospectos", "Id", $IdProspecto);
    $Email       = Basicas::BuscarCampos($pros, "Email", "prospectos", "Id", $IdProspecto);
    $Asunto      = "ENVIO ARCHIVO";
    $DirUrl      = "Cotizacion de servicios KASU";
    $imag1       = $EnCoti;
    $Msg         = "Se ha enviado la cotización al correo registrado de tu cliente";
    $dirUrl1     = "https://kasu.com.mx/login/Generar_PDF/Cotizacion_pdf.php";
} elseif (!empty($EnviarPoliza)) {
    // Correo que envía la póliza del cliente.
    $Asunto  = "ENVIO POLIZA";
    $DirUrl  = $Descripcion; 
    $dirUrl1 = "https://kasu.com.mx/login/Generar_PDF/Poliza_pdf.php";
    $imag1   = base64_encode($IdVenta);
} elseif (!empty($EnviarFichas)) {
    // Correo que envía las fichas de pago.
    $Asunto  = "ENVIO FICHAS";
    $DirUrl  = base64_encode($IdVenta);
    // Se pueden definir $dirUrl1 y otros parámetros si se requiere.
} elseif (!empty($EnviarEdoCta)) {
    // Correo que envía el estado de cuenta.
    $Asunto  = "ENVIO ESTADO DE CUENTA";
    $dirUrl1 = "https://kasu.com.mx/login/Generar_PDF/Estado_Cuenta_pdf.php";
    $DirUrl  = $Descripcion;
    $imag1   = $IdVenta;
} elseif (!empty($EnFi)) {
    // Envío de fichas vía método GET
    $Asunto    = "PAGO PENDIENTE";
    $Email     = Basicas::BuscarCampos($mysqli, "Mail", "Contacto", "id", $_SESSION["Cnc"]);
    $FullName  = Basicas::BuscarCampos($mysqli, "Nombre", "Usuario", "IdContact", $_SESSION["Cnc"]);
    $IdVenta   = $_SESSION["Cnc"];
    if ($EnFi == 1) {
        $DirUrl = "https://www.mercadopago.com.mx/checkout/v1/redirect?preference-id=" . $hash;
    } else {
        $DirUrl = "https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_plan_id=" . $hash;
    }
    $stat = "3";
} elseif (!empty($MxVta)) {
    // Cuando no se realiza el pago en MercadoPago
    $FullName      = Basicas::BuscarCampos($mysqli, "Nombre", "Venta", "Id", $MxVta);
    $CnTo          = Basicas::BuscarCampos($mysqli, "IdContact", "Venta", "Id", $MxVta);
    $Email         = Basicas::BuscarCampos($mysqli, "Mail", "Contacto", "id", $CnTo);
    $FechaRegistro = Basicas::BuscarCampos($mysqli, "FechaRegistro", "Venta", "Id", $MxVta);
    $DirUrl        = base64_encode($MxVta);
    $dirUrl1       = base64_encode(date("d-m-Y", strtotime($FechaRegistro)));
    $Asunto        = "FICHAS DE PAGO KASU";
    $stat          = "2";
} elseif (!empty($ProReIn)) {
    // Confirmación de registro de un prospecto.
    $FullName = Basicas::BuscarCampos($pros, "FullName", "prospectos", "Id", $ProReIn);
    $Asunto   = "CONOCE KASU";
    $imag1    = strtolower($Servicio);
    $IdVenta  = $ProReIn;
    if ($Servicio === "UNIVERSITARIO") {
        $DirUrl = "https://kasu.com.mx/productos.php?Art=2";
    } elseif ($Servicio === "RETIRO") {
        $DirUrl = "https://kasu.com.mx/productos.php?Art=3";
    } elseif ($Servicio === "POLICIAS") {
        $DirUrl = "https://kasu.com.mx/productos.php?Art=4";
    } else {
        $DirUrl = "https://kasu.com.mx/productos.php?Art=1";
    }
    $dirUrl1 = "https://kasu.com.mx/prospectos.php?data=Q0lUQQ==&Usr=" . $IdVenta;
} else {
    // Si ninguna condición se cumple, se asignan valores por defecto.
    $Asunto  = "SIN ASUNTO";
    $FullName = "Usuario";
    $Email   = "";
    $DirUrl  = "";
    $dirUrl1 = "";
    $imag1   = "";
}

// Generar el contenido del correo mediante la función Mensaje de la clase Correo.
// Se pasan todos los parámetros requeridos; en caso de que alguna variable no esté definida se asume como cadena vacía.
$mensa = Correo::Mensaje(
    $Asunto,
    $FullName,
    $DirUrl,
    $dirUrl1,
    $imag1,
    $Titulo1,
    $Desc1,
    $dirUrl2,
    $imag2,
    $Titulo2,
    $Desc2,
    $dirUrl3,
    $imag3,
    $Titulo3,
    $Desc3,
    $dirUrl4,
    $imag4,
    $Titulo4,
    $Desc4,
    $IdVenta
);

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
    // Configuración del servidor SMTP
    $mail->SMTPDebug = 0; // Cambiar a 2 para ver mensajes de depuración durante el desarrollo.
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.mx';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'atncliente@kasu.com.mx';
    $mail->Password   = '01J76e90@';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    
    // Configurar remitente y destinatario
    $mail->setFrom('atncliente@kasu.com.mx', 'KASU');
    $mail->addAddress($Email, $FullName);
    
    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = $Asunto;
    $mail->Body    = $mensa;
    
    $mail->send();
} catch (Exception $e) {
    echo "Error al enviar el mensaje: {$mail->ErrorInfo}";
}

// ================================
// Redireccionamientos y finalización
// ================================
if (!empty($_GET)) {
    if (!empty($EnCoti)) {
        // $Host se asume definido en Telcto.php u otro archivo incluido
        header('Location: ' . $Host . '&Msg=' . urlencode($Msg));
    } elseif (!empty($ProReIn)) {
        header('Location: https://kasu.com.mx/index.php?Msg=' . urlencode("Felicidades, ya te hemos enviado un correo y en breve un ejecutivo te contactará"));
    }
    session_destroy();
    header('Location: https://kasu.com.mx/registro.php?stat=' . urlencode($stat) . '&Cte=' . urlencode($FullName) . '&liga=' . urlencode($DirUrl));
} else {
    echo "<script>
            alert('Se ha enviado el correo electrónico');
            window.location.href = '../login/Mesa_Herramientas.php';
          </script>";
}
?>