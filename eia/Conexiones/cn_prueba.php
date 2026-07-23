<?php
// Conexión de PRUEBAS – solo se carga si se descomenta en librerias.php
// Variables desde entorno (.env), sin fallback de contraseña por seguridad
$db_host     = getenv('DB_HOST_PRUEBA')     ?: 'srv908.hstgr.io';
$db_user     = getenv('DB_USER_PRUEBA')     ?: 'u557645733_PlatPrueba';
$db_password = getenv('DB_PASS_PRUEBA')     ?: '';
$db_name     = getenv('DB_NAME_PRUEBA')     ?: 'u557645733_Prueba';

if ($db_password === '') {
    die('Error de configuracion: DB_PASS_PRUEBA no definida.');
}

$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($mysqli->connect_errno) {
    error_log('[KASU][cn_prueba] Fallo conexion: ' . $mysqli->connect_error);
}
?>
