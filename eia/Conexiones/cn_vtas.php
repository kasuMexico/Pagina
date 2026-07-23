<?php
// Variables de conexión desde entorno (.env), con fallback para no romper entornos sin .env
$db_host     = getenv('DB_HOST_VTAS')     ?: 'srv908.hstgr.io';
$db_user     = getenv('DB_USER_VTAS')     ?: 'u557645733_kasuw';
$db_password = getenv('DB_PASS_VTAS')     ?: '';
$db_name     = getenv('DB_NAME_VTAS')     ?: 'u557645733_web';

// Si no hay contraseña en entorno, el archivo .env no está configurado
if ($db_password === '') {
    die('Error de configuracion: DB_PASS_VTAS no definida.');
}

$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($mysqli->connect_errno) {
    error_log('[KASU][cn_vtas] Fallo conexion: ' . $mysqli->connect_error);
}
?>
