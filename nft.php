<?php
/**
 * Qué hace: Página NFT multilenguaje con SEO dinámico y carga de secciones por idioma.
 * Fecha: 03/11/2025
 * Revisado por: JCCM
 */

/* ===== Sesión y dependencias ===== */
// indicar que se inicia una sesion *JCCM
session_start();
// Archivo de rastreo de Google Tag Manager
require_once __DIR__ . '/eia/analytics_bootstrap.php';
// Requerimos el archivo de librerias *JCCM
require_once __DIR__ . '/eia/librerias.php';

/* ===== Alert opcional por ?Msg= ===== */
// Javascript que imprime el mensaje de alerta de recepcion de comentario
if (isset($_GET['Msg'])) {
  // json_encode evita problemas de comillas y XSS en alert
  echo "<script type='text/javascript'>alert(" . json_encode((string)$_GET['Msg'], JSON_UNESCAPED_UNICODE) . ");</script>";
}

/* ===== Parámetro de idioma ===== */
// Pasamos el get a variable
$Lgj = filter_input(INPUT_GET, 'Lg', FILTER_UNSAFE_RAW);
$Lgj = $Lgj ?: 'Espanol';

// Whitelist de idiomas válidos
$idiomasValidos = ['Espanol','Ingles','Aleman'];
if (!in_array($Lgj, $idiomasValidos, true)) {
  $Lgj = 'Espanol';
}

/* ===== Renderizado de bloques por idioma ===== */
// Creamos la funcion de conversion
function some_function($VAr) {
  // Español
  ob_start();
  include __DIR__ . '/html/EspanolNFT.php';
  return ob_get_clean();
}
function some_function2($VAr) {
  // Inglés
  ob_start();
  include __DIR__ . '/html/InglesNFT.php';
  return ob_get_clean();
}
function some_function3($VAr) {
  // Alemán
  ob_start();
  include __DIR__ . '/html/AlemanNFT.php';
  return ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php
    // SEO dinámico por idioma (no altera lógica)
    $lg  = $Lgj;
    $abs = 'https://kasu.com.mx' . strtok($_SERVER['REQUEST_URI'] ?? '/nft.php',' ');
    $titleMap = [
      'Espanol' => 'THE KASU NFT | Recompensas por cada NFT',
      'Ingles'  => 'THE KASU NFT | Rewards for every NFT',
      'Aleman'  => 'THE KASU NFT | Belohnungen pro NFT',
    ];
    $descMap = [
      'Espanol' => "La primera aseguradora en financiar con NFT's. Conecta tu wallet y conoce cómo obtener recompensas.",
      'Ingles'  => "The first insurer financing with NFTs. Connect your wallet and start earning rewards.",
      'Aleman'  => "Der erste Versicherer, der mit NFTs finanziert. Wallet verbinden und Belohnungen erhalten.",
    ];
    $title = htmlspecialchars($titleMap[$lg] ?? $titleMap['Espanol'], ENT_QUOTES, 'UTF-8');
    $desc  = htmlspecialchars($descMap[$lg]  ?? $descMap['Espanol'],  ENT_QUOTES, 'UTF-8');
    $ogimg = 'https://kasu.com.mx/assets/images/nft2.gif';
  ?>
  <meta charset="utf-8">
  <title><?= $title ?></title>
  <meta name="description" content="<?= $desc ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="robots" content="index,follow,max-image-preview:large">
  <meta name="author" content="KASU Servicios a Futuro">
  <meta name="keywords" content="KASU, NFT, aseguradora, recompensas, blockchain">
  <meta name="theme-color" content="#911F66">

  <!-- Canonical -->
  <link rel="canonical" href="https://kasu.com.mx/nft.php<?= isset($_GET['Lg']) ? '?Lg='.urlencode($lg) : '' ?>">

  <!-- Hreflang -->
  <link rel="alternate" href="https://kasu.com.mx/nft.php?Lg=Espanol" hreflang="es-MX">
  <link rel="alternate" href="https://kasu.com.mx/nft.php?Lg=Ingles"  hreflang="en">
  <link rel="alternate" href="https://kasu.com.mx/nft.php?Lg=Aleman"  hreflang="de">
  <link rel="alternate" href="https://kasu.com.mx/nft.php"           hreflang="x-default">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="KASU">
  <meta property="og:url" content="<?= htmlspecialchars($abs, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:title" content="<?= $title ?>">
  <meta property="og:description" content="<?= $desc ?>">
  <meta property="og:image" content="<?= $ogimg ?>">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= $title ?>">
  <meta name="twitter:description" content="<?= $desc ?>">
  <meta name="twitter:image" content="<?= $ogimg ?>">

  <!-- JSON-LD WebPage -->
  <script type="application/ld+json">
  {
    "@context":"https://schema.org",
    "@type":"WebPage",
    "url":"<?= htmlspecialchars($abs, ENT_QUOTES, 'UTF-8') ?>",
    "name":"<?= $title ?>",
    "description":"<?= $desc ?>",
    "isPartOf":{"@type":"WebSite","name":"KASU","url":"https://kasu.com.mx"}
  }
  </script>

  <!-- Fuentes + Favicon -->
  <link rel="icon" href="/assets/images/kasu_logo.jpeg">
  <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons'>

  <!-- CSS existentes -->
  <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="/assets/css/font-awesome.css">
  <link rel="stylesheet" href="/assets/css/templatemo-softy-pinko.css">
  <link rel="stylesheet" href="/assets/css/index.css">
</head>
<body>
  <?php require_once __DIR__ . '/html/MenuPrincipal.php'; ?>
  <!-- Portada de pagina -->
  <div class="welcome-area">
    <!-- <div class="welcome-area" style="background-image:url(assets/images/Correo/entregables.jpeg);"> -->
    <br><br><br>
    <div class="Mover">
      <div class="features-small-item">
        <div class="logo">
          <a href="#"><img src="/assets/images/kasu_logo.jpeg" alt="KASU"></a>
        </div>
        <h1><strong>KASU</strong></h1>
        <br>
        <h2>
          <?php
            if ($Lgj === "Ingles") {
              echo "EVERY NFT GENERATES REWARDS";
            } elseif ($Lgj === "Aleman") {
              echo "JEDE NFT ERZIELT BELOHNUNGEN";
            } else {
              echo "CADA NFT GENERA RECOMPENSAS";
            }
          ?>
        </h2>
        <br>
        <select class="custom-select mr-sm-2" onchange="this.value && (window.location = this.value);">
          <option value="">Lenguaje</option>
          <option value="https://kasu.com.mx/nft.php?Lg=Ingles">English</option>
          <option value="https://kasu.com.mx/nft.php?Lg=Aleman">Deutsch</option>
          <option value="https://kasu.com.mx/nft.php?Lg=Espanol">Español</option>
        </select>
        <br><br>
        <a href="#" class="btn btn-dark btn-lg">Vincular tu wallet</a>
      </div>
    </div>
  </div>

  <?php
    // Carga del bloque según idioma
    if ($Lgj === "Ingles") {
      echo some_function2($Lgj);
    } elseif ($Lgj === "Aleman") {
      echo some_function3($Lgj);
    } else {
      echo some_function($Lgj);
    }
  ?>

  <footer>
    <?php require_once __DIR__ . '/html/footer.php'; ?>
  </footer>

  <script src="/assets/js/jquery-2.1.0.min.js"></script>
  <script src="/assets/js/bootstrap.min.js"></script>
  <script src="/assets/js/waypoints.min.js"></script>
  <script src="/assets/js/imgfix.min.js"></script>
  <script src="/assets/js/jquery.counterup.min.js"></script>
  <script src="/assets/js/scrollreveal.min.js"></script>
  <script src="/assets/js/custom.js"></script>
  <script type="text/javascript" async src="https://d335luupugsy2.cloudfront.net/js/loader-scripts/28dd2782-ee7d-4b25-82b1-f5993b27764a-loader.js"></script>
</body>
</html>