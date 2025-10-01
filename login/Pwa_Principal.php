<?php
session_start();
require_once '../eia/librerias.php';
require_once 'php/Analisis_Metas.php';
date_default_timezone_set('America/Mexico_City');

// Sesión obligatoria
if (!isset($_SESSION["Vendedor"])) {
    header('Location: https://kasu.com.mx/login/');
    exit();
}

$Vend = $_SESSION["Vendedor"];

// ===== Foto de perfil (usa el primer archivo que haga match con Id.*) =====
$localDir   = __DIR__ . '/assets/img/perfil/';
$publicBase = '/login/assets/img/perfil/'; // carpeta pública donde sirves las fotos
$profileUrl = $publicBase . 'default.jpg';

$matches = glob($localDir . $Vend . '.*');
if ($matches && isset($matches[0])) {
    $fname      = basename($matches[0]);      // p.ej. 123.png
    $profileUrl = $publicBase . $fname;       // /login/assets/img/perfil/123.png
}

// Datos de usuario
$SL1         = $basicas->BuscarCampos($mysqli, "Nombre",         "Empleados", "IdUsuario", $Vend);
$Niv         = $basicas->BuscarCampos($mysqli, "Nivel",          "Empleados", "IdUsuario", $Vend);
$suc         = $basicas->BuscarCampos($mysqli, "Sucursal",       "Empleados", "IdUsuario", $Vend);
$su2         = $basicas->BuscarCampos($mysqli, "NombreSucursal", "Sucursal",  "Id",        $suc);
$nombreNivel = $basicas->BuscarCampos($mysqli, "NombreNivel",    "Nivel",     "Id",        $Niv);
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
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?php echo $VerCache; ?>">
  <link rel="stylesheet" href="assets/css/Grafica.css">

  <!-- JS externos -->
  <script src="https://www.gstatic.com/charts/loader.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <script src="Javascript/GenGrafica.js"></script>
</head>
<body>

  <!-- TOP BAR -->
  <div class="topbar">
    <img alt="logo" src="/login/assets/img/logoKasu.png" style="height:38px">
  </div>

  <!-- Menú inferior fijo -->
  <section id="Menu">
    <?php require_once 'html/Menuprinc.php'; ?>
  </section>

  <!-- Contenido -->
  <main class="page-content">
    <!-- Perfil -->
    <div class="dpersonales">
      <div class="imgPerfil">
        <img class="img-thumbnail" alt="perfil" src="<?php echo htmlspecialchars($profileUrl); ?>">
      </div>
      <div class="Nombre">
        <p class="mb-1"><?php echo htmlspecialchars($SL1); ?></p>
        <p class="mb-0"><?php echo htmlspecialchars($nombreNivel . " - " . $su2); ?></p>
      </div>
      <div class="flex-grow-1 text-right">
        <button class="btn btn-success" id="btnInstall">Instalar KASU</button>
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
          <?php if ($Niv == 7 || $Niv == 6): ?>
            <div class="col-md-12">
              <p>Comisiones Acumuladas</p>
              <h3 style="color:<?php echo htmlspecialchars($spv); ?>;">
                $<?php echo number_format($ComGenHoy, 2); ?>
              </h3>
            </div>
          <?php else: ?>
            <div class="col-md-12">
              <a href="Pwa_Registro_Pagos.php">
                <h3 style="color:<?php echo htmlspecialchars($spv); ?>;"><?php echo round($AvCob); ?> %</h3>
              </a>
              <p>Normalidad de cobranza mensual</p>
            </div>
          <?php endif; ?>

          <div class="row">
            <?php if ($Niv != 7): ?>
              <div class="col-md-6">
                <hr>
                <p><strong>Meta de Cobranza del ___ al ___</strong></p>
                <h3>$<?php echo number_format($MetaCob, 2); ?></h3>
                <p>Avance de Cobranza</p>
                <a href="Pwa_Registro_Pagos.php">
                  <h3 style="color:<?php echo htmlspecialchars($spv); ?>;">
                    $<?php echo number_format($CobHoy, 2); ?>
                  </h3>
                </a>
              </div>
            <?php endif; ?>

            <?php if ($Niv != 5): ?>
              <div class="col-md-6">
                <hr>
                <p><strong>Meta de Venta del Mes</strong></p>
                <h3>$<?php echo number_format($MetaVta, 2); ?></h3>
                <p>Avance de Venta</p>
                <a href="Pwa_Clientes.php">
                  <h3 style="color:<?php echo htmlspecialchars($bxo); ?>;">
                    <?php echo round($AvVtas); ?> %
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
  <script defer src="Javascript/finger.js"></script>
  <script defer src="Javascript/localize.js"></script>
  <script defer src="Javascript/install.js"></script>
  <script>
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/login/service-worker.js', { scope: '/login/' });
    }
  </script>
</body>
</html>
