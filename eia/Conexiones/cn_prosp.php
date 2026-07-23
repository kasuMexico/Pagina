<?php
// Variables de conexión desde entorno (.env), con fallback para no romper entornos sin .env
$db_host     = getenv('DB_HOST_PROSP')     ?: 'localhost';
$db_user     = getenv('DB_USER_PROSP')     ?: 'u557645733_prospectos';
$db_password = getenv('DB_PASS_PROSP')     ?: '';
$db_name     = getenv('DB_NAME_PROSP')     ?: 'u557645733_prospectos';

// Si no hay contraseña en entorno, el archivo .env no está configurado
if ($db_password === '') {
    die('Error de configuracion: DB_PASS_PROSP no definida.');
}

$pros = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($pros->connect_errno) {
    error_log('[KASU][cn_prosp] Fallo conexion: ' . $pros->connect_error);
}
?>