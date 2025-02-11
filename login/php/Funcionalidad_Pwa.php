<?php
// Iniciar sesión
session_start();
// Incluir las librerías que contienen las funciones (Basicas, Financieras, Seguridad, etc.)
require_once '../../eia/librerias.php';
// Configurar la zona horaria
date_default_timezone_set('America/Mexico_City');
// Variables de fecha y hora
$hoy = date('Y-m-d');
$HoraActual = date('H:i:s');
// BLOQUE: VALIDACIÓN DE USUARIO (LOGIN)
if (isset($_POST['Login'])) {
    // Crear una instancia de la clase Basicas
    $basicas = new Basicas();
    // Extraer y sanear las variables de entrada
    $Usuario     = isset($_POST['Usuario']) ? $mysqli->real_escape_string($_POST['Usuario']) : '';
    $PassWord    = isset($_POST['PassWord']) ? $_POST['PassWord'] : ''; // La contraseña se procesará para generar su hash
    $Latitud     = isset($_POST['Latitud']) ? $mysqli->real_escape_string($_POST['Latitud']) : '';
    $Longitud    = isset($_POST['Longitud']) ? $mysqli->real_escape_string($_POST['Longitud']) : '';
    $Presicion   = isset($_POST['Presicion']) ? $mysqli->real_escape_string($_POST['Presicion']) : '';
    $fingerprint = isset($_POST['fingerprint']) ? $_POST['fingerprint'] : '';
    $browser     = isset($_POST['browser']) ? $_POST['browser'] : '';
    $flash       = isset($_POST['flash']) ? $_POST['flash'] : '';
    $canvas      = isset($_POST['canvas']) ? $_POST['canvas'] : '';
    $connection  = isset($_POST['connection']) ? $_POST['connection'] : '';
    $cookie      = isset($_POST['cookie']) ? $_POST['cookie'] : '';
    $display     = isset($_POST['display']) ? $_POST['display'] : '';
    $fontsmoothing = isset($_POST['fontsmoothing']) ? $_POST['fontsmoothing'] : '';
    $fonts       = isset($_POST['fonts']) ? $_POST['fonts'] : '';
    $formfields  = isset($_POST['formfields']) ? $_POST['formfields'] : '';
    $java        = isset($_POST['java']) ? $_POST['java'] : '';
    $language    = isset($_POST['language']) ? $_POST['language'] : '';
    $silverlight = isset($_POST['silverlight']) ? $_POST['silverlight'] : '';
    $os          = isset($_POST['os']) ? $_POST['os'] : '';
    $timezone    = isset($_POST['timezone']) ? $_POST['timezone'] : '';
    $touch       = isset($_POST['touch']) ? $_POST['touch'] : '';
    $truebrowser = isset($_POST['truebrowser']) ? $_POST['truebrowser'] : '';
    $plugins     = isset($_POST['plugins']) ? $_POST['plugins'] : '';
    $useragent   = isset($_POST['useragent']) ? $_POST['useragent'] : '';
    $Host        = isset($_POST['Host']) ? $mysqli->real_escape_string($_POST['Host']) : '';
    // Convertir la contraseña ingresada a hash SHA-256
    $PassSHa = hash('sha256', $PassWord);
    // Llamar al método BuscarCampos() mediante el objeto creado
    $PassStored = $basicas->BuscarCampos($mysqli, "Pass", "Empleados", "IdUsuario", $Usuario);
    // Comparar los hashes de la contraseña
    if ($PassSHa !== $PassStored) {
        // Para depuración, en lugar de redirigir inmediatamente se detiene la ejecución.
        exit();
    } else {
        /**************** Registro de eventos de login ****************/
        // Registrar los datos de GPS
        $DatGps = [
            "Latitud"   => $Latitud,
            "Longitud"  => $Longitud,
            "Presicion" => $Presicion
        ];
        $gpsLogin = $basicas->InsertCampo($mysqli, "gps", $DatGps);
        // Verificar si el fingerprint ya existe en la base de datos
        $existingFingerprint = $basicas->BuscarCampos($mysqli, "id", "FingerPrint", "fingerprint", $fingerprint);
        if (empty($existingFingerprint)) {
            $DatFinger = [
                "fingerprint"   => $fingerprint,
                "browser"       => $browser,
                "flash"         => $flash,
                "canvas"        => $canvas,
                "connection"    => $connection,
                "cookie"        => $cookie,
                "display"       => $display,
                "fontsmoothing" => $fontsmoothing,
                "fonts"         => $fonts,
                "formfields"    => $formfields,
                "java"          => $java,
                "language"      => $language,
                "silverlight"   => $silverlight,
                "os"            => $os,
                "timezone"      => $timezone,
                "touch"         => $touch,
                "truebrowser"   => $truebrowser,
                "plugins"       => $plugins,
                "useragent"     => $useragent
            ];
            $basicas->InsertCampo($mysqli, "FingerPrint", $DatFinger);
        }
        // Registrar el evento "Ingreso" en la tabla Eventos
        $Contacto = $basicas->BuscarCampos($mysqli, "IdContacto", "Empleados", "IdUsuario", $Usuario);
        $DatEventos = [
            "IdFInger"      => $fingerprint,
            "Idgps"         => $gpsLogin,
            "Contacto"      => $Contacto,
            "Host"          => $Host,
            "Evento"        => "Ingreso",
            "Usuario"       => $Usuario,
            "FechaRegistro" => $hoy . " " . $HoraActual
        ];
        $basicas->InsertCampo($mysqli, "Eventos", $DatEventos);
        // Establecer la sesión y mostrar mensaje final de éxito (en vez de redirigir inmediatamente)
        $_SESSION["Vendedor"] = $Usuario;
        // Aquí se podría redirigir automáticamente; para depuración, se detiene la ejecución.
        header('Location: https://kasu.com.mx/login/Pwa_Principal.php');
        exit();
    }
} else {
    //Si No se recibió solicitud de login se envia al login
    header('Location: https://kasu.com.mx/login');

}
?>
