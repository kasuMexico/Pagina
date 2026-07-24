<?php
declare(strict_types=1);

/**
 * cn_apimarket.php
 * Conexión exclusiva para API Market (BD separada).
 * No depende de /eia/Conexiones.
 */

mysqli_report(MYSQLI_REPORT_OFF);

$DB_HOST = getenv('APIMARKET_DB_HOST');
$DB_USER = getenv('APIMARKET_DB_USER');
$DB_PASS = getenv('APIMARKET_DB_PASS');
$DB_NAME = getenv('APIMARKET_DB_NAME');
$DB_PORT = (int)(getenv('APIMARKET_DB_PORT') ?: 3306);

// Sin credenciales validas no se puede conectar
if (!$DB_HOST || !$DB_USER || !$DB_PASS || !$DB_NAME) {
    error_log('[KASU][cn_apimarket] Faltan variables de entorno: APIMARKET_DB_HOST, APIMARKET_DB_USER, APIMARKET_DB_PASS, APIMARKET_DB_NAME');
    $mysqli_api = null;
    return;
}

$mysqli_api = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if ($mysqli_api->connect_errno) {
  $mysqli_api = null;
} else {
  $mysqli_api->set_charset('utf8mb4');
}
