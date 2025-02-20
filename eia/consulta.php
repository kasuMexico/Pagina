<?php
// Iniciar la sesión y establecer la zona horaria
session_start();
date_default_timezone_set('America/Mexico_City');

// Incluir el archivo de funciones que carga las clases necesarias
require_once 'librerias.php';

// Verificar que la conexión a la base de datos esté definida
if (!isset($mysqli)) {
    die("Conexión a la base de datos no establecida.");
}

// Escapar el valor de la fingerprint recibida por POST
$fingerprint = isset($_POST['fingerprint']) 
    ? mysqli_real_escape_string($mysqli, $_POST['fingerprint']) 
    : '';

// Crear la consulta para verificar si el fingerprint ya existe en la tabla FingerPrint
$sql = "SELECT id FROM FingerPrint WHERE fingerprint = '$fingerprint'";
$res = mysqli_query($mysqli, $sql);
if (!$res) {
    error_log("Error en la consulta de FingerPrint: " . mysqli_error($mysqli));
}
$Reg = mysqli_fetch_assoc($res);

// Si no se encontró registro, se inserta el nuevo fingerprint
if (!$Reg || empty($Reg['id'])) {
    $DatFinger = array(
        "fingerprint"   => $_POST['fingerprint'],
        "browser"       => $_POST['browser'],
        "flash"         => $_POST['flash'],
        "canvas"        => $_POST['canvas'],
        "connection"    => $_POST['connection'],
        "cookie"        => $_POST['cookie'],
        "display"       => $_POST['display'],
        "fontsmoothing" => $_POST['fontsmoothing'],
        "fonts"         => $_POST['fonts'],
        "formfields"    => $_POST['formfields'],
        "java"          => $_POST['java'],
        "language"      => $_POST['language'],
        "silverlight"   => $_POST['silverlight'],
        "os"            => $_POST['os'],
        "timezone"      => $_POST['timezone'],
        "touch"         => $_POST['touch'],
        "truebrowser"   => $_POST['truebrowser'],
        "plugins"       => $_POST['plugins'],
        "useragent"     => $_POST['useragent']
    );
    $IdFinger = $basicas->InsertCampo($mysqli, "FingerPrint", $DatFinger);
    if (!$IdFinger) {
        error_log("Error al insertar FingerPrint.");
    }
}

// Registrar el evento
$hoy = date('Y-m-d');
$HoraActual = date('H:i:s');

$DatEventos = array(
    "IdFInger"      => $_POST['fingerprint'],
    "Usuario"       => $_POST['Usuario'],
    "Evento"        => $_POST['Event'],
    "MetodGet"      => $_POST['formfields'],
    "connection"    => $_POST['connection'],
    "timezone"      => $_POST['timezone'],
    "touch"         => $_POST['touch'],
    "Cupon"         => $_POST['Cupon'],
    "FechaRegistro" => $hoy . " " . $HoraActual
);

$resultEvento = $basicas->InsertCampo($mysqli, "Eventos", $DatEventos);
if ($resultEvento === false) {
    error_log("Error al insertar Evento: " . mysqli_error($mysqli));
} else {
    echo $resultEvento;
}
?>