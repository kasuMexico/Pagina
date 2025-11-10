<?php
/********************************************************************************************
 * Qué hace: Página "Mesa Fianznas" permite conciliar los pagos bancarios con los pagos 
 * registrados en la plataforma, asi como visualizar los pagos por realizar, empleados, comisiones, y referidos
 * Fecha: 09/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

// =================== Sesión y dependencias ===================
// Qué hace: Inicia sesión, carga librerías y activa excepciones de mysqli para PHP 8.2
// Fecha: 05/11/2025 | Revisado por: JCCM
session_start();
require_once '../eia/librerias.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// =================== Guardia de sesión ===================
// Qué hace: Valida autenticación y redirige a login si no hay sesión de Vendedor
// Fecha: 05/11/2025 | Revisado por: JCCM
if (empty($_SESSION["Vendedor"])) {
  header('Location: https://kasu.com.mx/login');
  exit;
}

// =================== Utilidades ===================
// Qué hace: Función de escape HTML segura para impresión en vista
// Fecha: 05/11/2025 | Revisado por: JCCM
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// =================== Fechas periodo ===================
// Qué hace: Define inicio de mes y fecha de hoy para filtros y vistas
// Fecha: 05/11/2025 | Revisado por: JCCM
$FechIni = date("d-m-Y", strtotime('first day of this month'));
$FechFin = date("d-m-Y");

// =================== Alerta opcional por GET ?Msg= ===================
// Qué hace: Muestra alerta informativa si viene un mensaje por querystring
// Fecha: 05/11/2025 | Revisado por: JCCM
if (isset($_GET['Msg'])) {
  echo "<script>alert('".htmlspecialchars((string)$_GET['Msg'], ENT_QUOTES, 'UTF-8')."');</script>";
}

?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F2F2F2">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Mesa Prospectos</title>

  <!-- =================== PWA / iOS ===================
       Qué hace: Manifiesto y meta para instalación como app
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- =================== CSS unificado ===================
       Qué hace: Carga de estilos base de Bootstrap, iconos y hoja local
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= h($VerCache) ?>">
  <link rel="stylesheet" href="assets/css/Grafica.css">
</head>
<body onload="localize()">
<!-- =================== Top bar fija ===================
    Qué hace: Encabezado con título y botón para crear prospecto
    Fecha: 05/11/2025 | Revisado por: JCCM
-->
    <div class="topbar">
        <div class="d-flex align-items-center w-100">
        <h4 class="title">Datos de Marketing</h4>

        <!-- botón crear tarjeta -->
        <form class="BtnSocial m-0 ml-auto" method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>">
            <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">
            <label for="btnCrear" class="btn mb-0" title="Nuevo prospecto" style="background:#F7DC6F;color:#000;">
            <i class="material-icons">person_add</i>
            </label>
            <!-- Enviar IdProspecto = "20" (2 + 0) para que el selector abra Ventana2 -->
            <input id="btnCrear" type="submit" name="IdProspecto" value="40" hidden>
        </form>
        </div>
    </div>

<!-- =================== Menú inferior compacto ===================
    Qué hace: Navegación inferior de la PWA
    Fecha: 05/11/2025 | Revisado por: JCCM -->
<!-- Menú inferior fijo -->
    <section id="Menu">
        <?php require_once __DIR__ . '/html/Menuprinc.php'; ?>
    </section>


  <!-- =================== JS únicos y en orden ===================
       Qué hace: Carga dependencias JS y utilidades de la PWA
       Fecha: 05/11/2025 | Revisado por: JCCM -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="Javascript/finger.js?v=3"></script>
    <script src="Javascript/localize.js?v=3"></script>
    <script src="Javascript/Inyectar_gps_form.js"></script>

  <!-- =================== Auto-apertura de modal ===================
       Qué hace: Si $Lanzar tiene valor, abre el modal #Ventana al cargar
       Fecha: 05/11/2025 | Revisado por: JCCM -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        <?php if (!empty($Lanzar)): ?>
            $('<?= h($Lanzar) ?>').modal('show');
        <?php endif; ?>
        });
    </script>
</body>
</html>