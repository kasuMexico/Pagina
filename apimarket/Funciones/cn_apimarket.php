<?php
declare(strict_types=1);

/**
 * cn_apimarket.php
 * Conexión exclusiva para API Market (BD separada).
 * No depende de /eia/Conexiones.
 */

mysqli_report(MYSQLI_REPORT_OFF);

$DB_HOST = getenv('APIMARKET_DB_HOST') ?: '127.0.0.1';
$DB_USER = getenv('APIMARKET_DB_USER') ?: 'u557645733_Apis';
$DB_PASS = getenv('APIMARKET_DB_PASS') ?: 'oTdDEU>5=8PI';
$DB_NAME = getenv('APIMARKET_DB_NAME') ?: 'u557645733_KASU_apis';
$DB_PORT = (int)(getenv('APIMARKET_DB_PORT') ?: 3306);

$mysqli_api = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if ($mysqli_api->connect_errno) {
  $mysqli_api = null;
} else {
  $mysqli_api->set_charset('utf8mb4');
}
