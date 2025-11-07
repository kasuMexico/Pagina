<?php
session_start();

// Incluir el archivo de funciones (se asume que Funciones_kasu.php carga o require_once
// las clases Basicas, Correo, Financieras, Seguridad y FunctionUsageTracker)
require_once '../librerias.php';

// Verifica que las conexiones existan
if (!isset($mysqli) || !isset($pros)) {
    die("Error: No se encontraron las conexiones a la base de datos.");
}

// Registrar el uso de las funciones (esto lo hará cada función que invoque trackUsage)

// Actualizamos las comisiones generadas por los prospectos
$financieras->ActualComis($mysqli);

// Actualizamos las ventas
$financieras->actualizaVts($mysqli);

// Realizamos el envío de correos de solicitud de citas
$sql1 = "SELECT * FROM prospectos";
$res1 = $pros->query($sql1);

if ($res1 === false) {
    die("Error al ejecutar la consulta en prospectos: " . $pros->error);
}

// Iteramos sobre cada prospecto
foreach ($res1 as $Reg1) {
    // Si el prospecto tiene marcado Automatico = 1 y su Servicio_Interes es "DISTRIBUIDOR"
    if ($Reg1['Automatico'] == 1 && $Reg1['Servicio_Interes'] == "DISTRIBUIDOR") {
        $asunto = "AGENDAR CITA";
        // Convertir el identificador a base64 (en este ejemplo se codifica la cadena "CITA"
        // y el id del prospecto; verifica que esto sea lo que deseas)
        $UsrEncode = base64_encode('CITA');
        $dirUrl1 = base64_encode($Reg1['Id']);
        
        // Se crea el contenido del correo según la plantilla "AGENDAR CITA"
        $mensa = Correo::Mensaje(
            $asunto,
            $Reg1['FullName'],
            $UsrEncode,
            $dirUrl1,
            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 
            $Reg1['Id']
        );
        
        // Enviar el correo electrónico (asegúrate de que la función EnviarCorreo esté definida en la clase Correo)
        Correo::EnviarCorreo($Reg1['FullName'], $Reg1['Email'], $asunto, $mensa);
        
        // Se registra el valor del correo enviado consultándolo en la tabla 'correos'
        $ValMail = $basicas->BuscarCampos($pros, 'Seguimiento', 'correos', 'Asunto', $asunto);
        
        // Se actualiza el estado en la tabla prospectos con el valor obtenido
        $basicas->ActCampo($pros, "prospectos", "Estado", $ValMail, $Reg1['Id']);
    }
}

// Cerramos las conexiones a la base de datos
$pros->close();
$mysqli->close();
?>
