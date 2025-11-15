<?php
/********************************************************************************************
 * Qué hace: Renderiza la pantalla de inicio PWA de KASU para el vendedor autenticado.
 *           Carga datos del empleado, metas/KPIs, foto de perfil y menús. Incluye PWA helpers.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

/* ==========================================================================================
 * BLOQUE: Sesión y dependencias
 * Qué hace: Inicia sesión, carga librerías y módulo de análisis de metas. Fija TZ MX.
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
session_start();
require_once __DIR__ . '/../eia/librerias.php';
require_once __DIR__ . '/php/Analisis_Metas.php';
date_default_timezone_set('America/Mexico_City');

/* ==========================================================================================
 * BLOQUE: Autenticación requerida
 * Qué hace: Si no hay sesión de vendedor, redirige al login.
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
if (!isset($_SESSION['Vendedor']) || $_SESSION['Vendedor'] === '') {
    header('Location: https://kasu.com.mx/login/');
    exit();
}
$Vend = (string)$_SESSION['Vendedor'];

/* ==========================================================================================
 * BLOQUE: Foto de perfil
 * Qué hace: Resuelve la URL pública de la foto de perfil. Prioriza:
 *   1) Nombre de archivo guardado en BD (Empleados.Foto)
 *   2) Archivo más reciente que haga match con IdEmpleado_*.jpg
 *   3) default.jpg
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
$docRoot   = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), DIRECTORY_SEPARATOR);
$fsDir     = $docRoot . '/login/assets/img/perfil/';
$publicBase = '/login/assets/img/perfil/';
$profileUrl = $publicBase . 'default.jpg';

$VendId = (int)$basicas->BuscarCampos($mysqli, 'Id', 'Empleados', 'IdUsuario', $Vend);

// Nombre de archivo explícito en BD
$last = (string) ($basicas->BuscarCampos($mysqli, 'Foto', 'Empleados', 'Id', $VendId) ?? '');
if ($last !== '' && is_file($fsDir . $last)) {
    $profileUrl = $publicBase . $last . '?v=' . filemtime($fsDir . $last);
} else {
    // Fallback: más reciente por patrón Id_*.jpg
    $pattern = $fsDir . $VendId . '_*.jpg';
    $matches = glob($pattern) ?: [];
    if (!empty($matches)) {
        usort($matches, static function (string $a, string $b): int {
            return filemtime($b) <=> filemtime($a);
        });
        $fname = basename($matches[0]);
        $profileUrl = $publicBase . $fname . '?v=' . filemtime($matches[0]);
    }
}

if (!empty($_SESSION['FotoCacheBust'])) {
    $separator = str_contains($profileUrl, '?') ? '&' : '?';
    $profileUrl .= $separator . 'cb=' . rawurlencode((string)$_SESSION['FotoCacheBust']);
    unset($_SESSION['FotoCacheBust']);
}

/* ==========================================================================================
 * BLOQUE: Datos del usuario para cabecera
 * Qué hace: Obtiene nombre, nivel, sucursal y etiquetas legibles.
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
$SL1          = (string)$basicas->BuscarCampos($mysqli, 'Nombre',         'Empleados', 'IdUsuario', $Vend);
$NivRaw       = $basicas->BuscarCampos($mysqli, 'Nivel',          'Empleados', 'IdUsuario', $Vend);
$suc          = (int)$basicas->BuscarCampos($mysqli, 'Sucursal',       'Empleados', 'IdUsuario', $Vend);
$su2          = (string)$basicas->BuscarCampos($mysqli, 'NombreSucursal', 'Sucursal',  'Id',        $suc);
$nombreNivel  = (string)$basicas->BuscarCampos($mysqli, 'NombreNivel',    'Nivel',     'Id',        $NivRaw);
$Niv          = (int)$NivRaw;

/* ==========================================================================================
 * BLOQUE: Defaults de variables de metas/KPIs (respaldo si no vienen desde Analisis_Metas.php)
 * Qué hace: Evita notices de variables no definidas en vistas.
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
$spv      = isset($spv)      ? (string)$spv      : '#2e7d32';
$bxo      = isset($bxo)      ? (string)$bxo      : '#1565c0';
$ComGenHoy= isset($ComGenHoy)? (float)$ComGenHoy : 0.0;
$AvCob    = isset($AvCob)    ? (float)$AvCob     : 0.0;
$MetaCob  = isset($MetaCob)  ? (float)$MetaCob   : 0.0;
$CobHoy   = isset($CobHoy)   ? (float)$CobHoy    : 0.0;
$MetaVta  = isset($MetaVta)  ? (float)$MetaVta   : 0.0;
$AvVtas   = isset($AvVtas)   ? (float)$AvVtas    : 0.0;

// Cache-busting para CSS si $VerCache no está definido
$VerCacheSafe = isset($VerCache) ? (string)$VerCache : '1';

date_default_timezone_set('America/Mexico_City');
$ini = (new DateTime('first day of this month'))->format('d/m/Y');
$fin = (new DateTime('last day of this month'))->format('d/m/Y');

?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F2F2F2">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>KASU Inicio</title>

  <!-- PWA / iOS -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= htmlspecialchars($VerCacheSafe, ENT_QUOTES) ?>">
  <link rel="stylesheet" href="assets/css/Grafica.css">

  <!-- JS externos -->
  <script src="https://www.gstatic.com/charts/loader.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <script src="Javascript/GenGrafica.js"></script>
</head>
<body onload="localize()">
  <!-- TOP BAR -->
  <div class="topbar">
    <img alt="logo" src="/login/assets/img/logoKasu.png" style="height:38px">
  </div>

  <!-- Menú inferior fijo -->
  <section id="Menu">
    <?php require_once __DIR__ . '/html/Menuprinc.php'; ?>
  </section>

  <!-- Contenido -->
  <main class="page-content">
    <!-- Perfil -->
    <div class="dpersonales">
      <div class="imgPerfil">
        <img class="img-thumbnail" alt="perfil" src="<?= htmlspecialchars($profileUrl, ENT_QUOTES) ?>" id="FotoPerfil">
        <button type="button" class="btn-change-photo" id="btnFoto" aria-label="Actualizar foto">
          <i class="fa fa-camera"></i>
        </button>
        <form id="perfilForm" method="POST" enctype="multipart/form-data" action="php/Funcionalidad_Pwa.php" class="perfil-uploader">
          <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">
          <input type="hidden" name="btnEnviar" value="1">
          <input class="d-none" type="file" id="subirImg" name="subirImg" accept="image/*">
        </form>
      </div>
      <div class="Nombre">
        <p class="mb-1"><?= htmlspecialchars($SL1, ENT_QUOTES) ?></p>
        <p class="mb-0"><?= htmlspecialchars($nombreNivel . ' - ' . $su2, ENT_QUOTES) ?></p>
      </div>
      <div class="flex-grow-1 text-right">
        <button class="btn btn-success btn-install" id="btnInstall">Instalar KASU</button>
      </div>
    </div>

    <!-- KPIs + gráfica -->
    <div class="container">
      <div class="row">
        <!-- Gráfica -->
        <div class="col-md-6">
          <div class="Grafica" id="chart_container"></div>
        </div>

        <!-- Metas -->
        <div class="col-md-6">
          <?php if ($Niv === 7 || $Niv === 6): ?>
            <div class="col-md-12">
              <p>Comisiones Acumuladas</p>
              <h3 style="color:<?= htmlspecialchars($spv, ENT_QUOTES) ?>;">
                $<?= number_format($ComGenHoy, 2) ?>
              </h3>
            </div>
          <?php else: ?>
            <div class="col-md-12">
              <a href="Pwa_Registro_Pagos.php">
                <h3 style="color:<?= htmlspecialchars($spv, ENT_QUOTES) ?>;"><?= round($AvCob) ?> %</h3>
              </a>
              <p>Normalidad de cobranza mensual</p>
            </div>
          <?php endif; ?>

          <div class="row">
            <?php if ($Niv !== 7): ?>
              <div class="col-md-6">
                <hr>
                <p><strong>Meta de Cobranza del <?= $ini ?> al <?= $fin ?></strong></p>
                <h3>$<?= number_format($MetaCob, 2) ?></h3>
                <p>Avance de Cobranza</p>
                <a href="Pwa_Registro_Pagos.php">
                  <h3 style="color:<?= htmlspecialchars($spv, ENT_QUOTES) ?>;">
                    $<?= number_format($CobHoy, 2) ?>
                  </h3>
                </a>
              </div>
            <?php endif; ?>

            <?php if ($Niv !== 5): ?>
              <div class="col-md-6">
                <hr>
                <p><strong>Meta de Venta del <?= $ini ?> al <?= $fin ?></strong></p>
                <h3>$<?= number_format($MetaVta, 2) ?></h3>
                <p>Avance de Venta</p>
                <a href="Pwa_Clientes.php">
                  <h3 style="color:<?= htmlspecialchars($bxo, ENT_QUOTES) ?>;">
                    <?= round($AvVtas) ?> %
                  </h3>
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- PWA / helpers -->
  <script src="Javascript/finger.js?v=3"></script>
  <script src="Javascript/localize.js?v=3"></script>
  <script src="Javascript/Inyectar_gps_form.js"></script>
  <script src="Javascript/perfil.js?v=<?= htmlspecialchars($VerCacheSafe, ENT_QUOTES) ?>"></script>
</body>
</html>
