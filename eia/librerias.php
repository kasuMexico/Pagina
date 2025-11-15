<?php
/**
 * Bootstrap de librerías y configuración regional.
 * Función: Cargar conexiones, funciones y definir configuración base compatible con PHP 8.2.
 * Fecha: 2025-11-03
 * Revisado por: JCCM
 */

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

// DEBUG: Activar todos los errores y mostrar datos importantes (eliminar en producción)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

//Temporal para actualizar cache de PWA
$VerCache = 58;
