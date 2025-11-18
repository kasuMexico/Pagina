<?php
/**
 * Bootstrap de librerías y configuración regional.
 * Función: Cargar conexiones, funciones y definir configuración base compatible con PHP 8.2.
 * Fecha: 2025-11-03
 * Revisado por: JCCM
 */

/* ==========================================================================================
 * BLOQUE: Toggles de depuración y archivo de error_log
 * Qué hace: Permite activar/desactivar la visualización de errores y centraliza el error_log.
 *           El archivo generado queda disponible en /eia/error.log (accesible desde
 *           https://kasu.com.mx/eia/error.log). Puedes controlarlo con variables de entorno:
 *           - KASU_DEBUG_MODE={1|0}  -> 1 muestra errores en pantalla, 0 los oculta.
 *           - KASU_LOG_TO_FILE={1|0} -> 1 escribe en el archivo anterior, 0 lo desactiva.
 *           - KASU_ERROR_LOG_FILE    -> Ruta absoluta opcional si deseas otro archivo.
 * Fecha actualización: 2025-11-18 — JCCM
 * ========================================================================================== */
if (!function_exists('kasu_env_flag')) {
  /**
   * Lee variables de entorno tipo booleano con fallback.
   */
  function kasu_env_flag(string $varName, bool $default): bool
  {
    $value = getenv($varName);
    if ($value === false && isset($_SERVER[$varName])) {
      $value = $_SERVER[$varName];
    }
    if ($value === false || $value === null || $value === '') {
      return $default;
    }
    $value = strtolower((string)$value);
    if (in_array($value, ['1', 'true', 'on', 'yes'], true)) {
      return true;
    }
    if (in_array($value, ['0', 'false', 'off', 'no'], true)) {
      return false;
    }
    return $default;
  }
}

$kasuErrorDefault = getenv('KASU_ERROR_LOG_FILE') ?: (__DIR__ . '/error.log');
if (!defined('KASU_ERROR_LOG_FILE')) {
  define('KASU_ERROR_LOG_FILE', $kasuErrorDefault);
}

if (!defined('KASU_DEBUG_MODE')) {
  define('KASU_DEBUG_MODE', kasu_env_flag('KASU_DEBUG_MODE', true));
}
if (!defined('KASU_LOG_TO_FILE')) {
  define('KASU_LOG_TO_FILE', kasu_env_flag('KASU_LOG_TO_FILE', true));
}

if (!function_exists('kasu_apply_error_settings')) {
  /**
   * Aplica la configuración de errores/log centralizada.
   * Usa KASU_DEBUG_MODE/KASU_LOG_TO_FILE definidos arriba.
   */
  function kasu_apply_error_settings(): void
  {
    if (defined('KASU_LOG_TO_FILE') && KASU_LOG_TO_FILE) {
      ini_set('log_errors', '1');
      ini_set('error_log', KASU_ERROR_LOG_FILE);
    }

    if (defined('KASU_DEBUG_MODE') && KASU_DEBUG_MODE) {
      ini_set('display_errors', '1');
      ini_set('display_startup_errors', '1');
      error_reporting(E_ALL);
    } else {
      ini_set('display_errors', '0');
      ini_set('display_startup_errors', '0');
      error_reporting(E_ALL & ~E_NOTICE);
    }
  }
}

kasu_apply_error_settings();

//incluir la conexion a la base de datos
require_once __DIR__ . '/Conexiones/cn_prosp.php';
require_once __DIR__ . '/Conexiones/cn_vtas.php';
//require_once __DIR__ . '/Conexiones/cn_prueba.php';  //Conexion para hacer pruebas la base es una copia exacta de la Real

//inlcuir los archivos de funciones
require_once __DIR__ . '/Funciones/Funciones_Basicas.php';
require_once __DIR__ . '/Funciones/Funciones_Correo.php';
require_once __DIR__ . '/Funciones/Funciones_Financieras.php';
require_once __DIR__ . '/Funciones/Funciones_Seguridad.php';
require_once __DIR__ . '/Funciones/Funciones_Auth.php';

//datos locales
date_default_timezone_set('America/Mexico_City');

// PHP 8.x: setlocale requiere nombres de locale válidos del sistema.
// Probamos variantes comunes y usamos la primera disponible.
$tryLocales = [
  'es_MX.UTF-8', 'es_MX.utf8', 'es_MX', // Linux/macOS
  'Spanish_Mexico.1252',                // Windows
];

foreach ($tryLocales as $loc) {
  if (setlocale(LC_ALL, $loc)) {
    // Ajusta LC_MONETARY y LC_TIME explícitamente si se requiere segmentar
    setlocale(LC_MONETARY, $loc);
    setlocale(LC_TIME, $loc);
    break;
  }
}

// Catálogos locales
$dias  = ["Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado"];
$meses = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];

//Fecha y hora para registros
$hoy        = date('Y-m-d');
$HoraActual = date('H:i:s');

//creamos una variable general para las funciones
$basicas     = new Basicas();
$seguridad   = new Seguridad();
$financieras = new Financieras();
$Correo      = new Correo();

//Temporal para actualizar cache de PWA
$VerCache = 67;
