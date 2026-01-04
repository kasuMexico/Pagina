<?php
declare(strict_types=1);

/**
 * librerias_api.php
 * Loader exclusivo de API Market:
 * - Conexión propia ($mysqli_api)
 * - Funciones propias de /apimarket/Funciones
 */

// Conexiones
$mysqli_api = null;
$apimarketConn = __DIR__ . '/Funciones/cn_apimarket.php';
if (is_file($apimarketConn)) {
  try {
    require_once $apimarketConn;
  } catch (Throwable $e) {
    $mysqli_api = null;
  }
}

$ventasConn = __DIR__ . '/../eia/Conexiones/cn_vtas.php';
if (is_file($ventasConn)) {
  try {
    require_once $ventasConn;
  } catch (Throwable $e) {
    $mysqli = null;
  }
}
if (isset($mysqli) && ($mysqli instanceof mysqli) && $mysqli->connect_errno !== 0) {
  $mysqli = null;
}

// Alias de compatibilidad si falta alguna conexión
if (!isset($mysqli) && isset($mysqli_api) && ($mysqli_api instanceof mysqli)) {
  $mysqli = $mysqli_api;
}
if (!isset($mysqli_api) && isset($mysqli) && ($mysqli instanceof mysqli)) {
  $mysqli_api = $mysqli;
}

// Funciones internas de API Market (no usar /eia/Funciones)
$funcionesDir = __DIR__ . '/Funciones';
require_once $funcionesDir . '/Funciones_Basicas.php';
require_once $funcionesDir . '/Funciones_Seguridad.php';
require_once $funcionesDir . '/Funciones_API.php';
require_once $funcionesDir . '/FunctionUsageTracker.php';

// Timezone / locales (solo si aquí también los necesitas)
date_default_timezone_set('America/Mexico_City');

// Instancias estándar (si tu código las espera)
$basicas   = new Basicas();
$seguridad = new Seguridad();

// Exponer conexiones globalmente para includes heredados
global $mysqli_api, $mysqli;

/**
 * DEBUG
 * En producción apágalo.
 * Si quieres control por env var:
 * APIMARKET_DEBUG=1
 */
$debug = (int)(getenv('APIMARKET_DEBUG') ?: 0);
if ($debug === 1) {
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);
} else {
  ini_set('display_errors', '0');
  ini_set('display_startup_errors', '0');
  error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
}
