<?php
/********************************************************************************************
 * Procesa el formulario de acceso a KASU API y guarda en ContacIndex.
 * Fecha: 04/11/2025  ·  PHP 8.2
 * Revisado JCCM
 ********************************************************************************************/

declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
ini_set('display_errors', '0');

session_start();
require_once 'librerias_api.php'; // Debe definir $mysqli conectado y UTF-8

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
  header('Location: index.php?Msg=' . rawurlencode('Error de conexión.'), true, 303);
  exit;
}

/* === CSRF === */
$csrf_ok = true;
if (isset($_POST['csrf'], $_SESSION['csrf_auth'])) {
  $csrf_ok = hash_equals((string)$_SESSION['csrf_auth'], (string)$_POST['csrf']);
}

/* === Mensaje por defecto === */
$Msg = 'Error al contactarnos, intenta más tarde';

try {
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && $csrf_ok) {

    /* === Honeypot: si trae valor, descarta === */
    if (!empty($_POST['company'])) {
      header('Location: index.php?Msg=' . rawurlencode('Solicitud recibida.'), true, 303);
      exit;
    }

    /* === Normalización === */
    $nombre   = isset($_POST['name'])    ? trim((string)$_POST['name'])    : '';
    $correo   = isset($_POST['email'])   ? trim((string)$_POST['email'])   : '';
    $website  = isset($_POST['website']) ? trim((string)$_POST['website']) : '';
    $mensaje  = isset($_POST['message']) ? trim((string)$_POST['message']) : '';

    // números opcionales; si vienen vacíos, 0
    $usersEst = isset($_POST['users_est']) ? (int)$_POST['users_est'] : 0;
    $rpsMax   = isset($_POST['rps_max'])   ? (int)$_POST['rps_max']   : 0;

    /* === Validaciones mínimas === */
    $email_ok   = filter_var($correo, FILTER_VALIDATE_EMAIL);
    $url_ok     = ($website === '') ? true : (bool)filter_var($website, FILTER_VALIDATE_URL);

    if ($nombre !== '' && $email_ok && $url_ok && $mensaje !== '') {
      /* === Límites de longitud === */
      $nombre   = mb_substr($nombre, 0, 150, 'UTF-8');
      $correo   = mb_substr($correo, 0, 190, 'UTF-8');
      $website  = mb_substr($website, 0, 190, 'UTF-8');
      $mensaje  = mb_substr($mensaje, 0, 3000, 'UTF-8');

      // sanea valores numéricos
      if ($usersEst < 0) $usersEst = 0;
      if ($rpsMax   < 0) $rpsMax   = 0;

      /* === Insert seguro ===
         Requiere columnas:
         ContacIndex(Nombre, Correo, SitioWeb, UsuariosEst, RpsMax, Mensaje, Fecha)
      */
      $sql = "INSERT INTO ContacIndex
                (Nombre, Correo, SitioWeb, UsuariosEst, RpsMax, Mensaje, Fecha)
              VALUES
                (?, ?, ?, ?, ?, ?, NOW())";
      $stmt = $mysqli->prepare($sql);
      $stmt->bind_param('sssiss', $nombre, $correo, $website, $usersEst, $rpsMax, $mensaje);
      $stmt->execute();

      $Msg = 'Gracias. Revisaremos tu solicitud y te contactaremos.';
    } else {
      $Msg = 'Datos inválidos. Verifica nombre, correo, sitio web y mensaje.';
    }
  } else {
    if (!$csrf_ok) $Msg = 'Sesión inválida. Recarga el formulario.';
  }
} catch (Throwable $e) {
  // error_log('Contacto error: '.$e->getMessage());
  $Msg = 'No se pudo procesar tu solicitud en este momento.';
}

/* === Redirección === */
header('Location: index.php?Msg=' . rawurlencode($Msg), true, 303);
exit;