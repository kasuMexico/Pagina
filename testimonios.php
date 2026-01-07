<?php
/**
 * Qué hace: Página de testimonios. Lista opiniones reales desde la BD y muestra estructura SEO.
 * Fecha: 03/11/2025
 * Revisado por: JCCM
 * Archivo: testimonios.php
 */

// Iniciar la sesión
session_start();

// Archvo de rastreo de google tag manager
require_once __DIR__ . '/eia/analytics_bootstrap.php';
// Requerir el archivo de librerías
require_once __DIR__ . '/eia/librerias.php';

// Validación mínima de conexión (compat. PHP 8.2)
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
  http_response_code(500);
  exit('Error de conexión.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>KASU | Opiniones reales de clientes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Opiniones reales de clientes KASU sobre nuestros servicios funerarios de pago único y cobertura nacional en México.">
  <meta name="robots" content="index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1">
  <meta name="author" content="Erendida Itzel Castro Marquez">
  <link rel="canonical" href="https://kasu.com.mx/testimonios.php">
  <link rel="alternate" href="https://kasu.com.mx/testimonios.php" hreflang="es-MX">
  <link rel="alternate" href="https://kasu.com.mx/testimonios.php" hreflang="x-default">

  <!-- Open Graph / Twitter -->
  <meta property="og:type" content="website">
  <meta property="og:locale" content="es_MX">
  <meta property="og:title" content="KASU | Opiniones de clientes">
  <meta property="og:description" content="Lee testimonios auténticos de nuestros clientes. Descubre por qué confían en KASU.">
  <meta property="og:url" content="https://kasu.com.mx/testimonios.php">
  <meta property="og:site_name" content="KASU">
  <meta property="og:image" content="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <meta property="og:image:alt" content="Logotipo KASU">
  <meta name="twitter:card" content="summary_large_image">

  <!-- Perf -->
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!-- Fuentes e ícono -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" href="/assets/images/kasu_logo.jpeg">

  <!-- CSS -->
  <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css?v=<? echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/font-awesome.css?v=<? echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/kasu-menu.css?v=<? echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/index-home.css?v=<? echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/testimonios.css?v=<? echo $VerCache;?>">

  <!-- Schema WebPage -->
  <script type="application/ld+json">
  {
    "@context":"https://schema.org",
    "@type":"WebPage",
    "name":"Opiniones de clientes KASU",
    "url":"https://kasu.com.mx/testimonios.php",
    "inLanguage":"es-MX",
    "description":"Testimonios auténticos de clientes sobre los servicios funerarios de KASU en México."
  }
  </script>
</head>
<body class="kasu-ui">
  <!-- Header -->
  <?php require_once __DIR__ . '/html/MenuPrincipal.php'; ?>
  <main class="testimonials-page">
    <section class="testimonials-hero">
      <div class="container">
        <p class="testimonials-eyebrow">Opiniones reales</p>
        <h1 class="testimonials-title">Opiniones de nuestros clientes</h1>
        <p class="testimonials-sub">En KASU creemos en la transparencia y la confianza. Aqui solo veras opiniones reales de clientes que han vivido nuestro servicio.</p>
        <p class="testimonials-sub">No simulamos testimonios. Cada comentario es autentico y representa la voz de quienes nos eligieron para proteger a su familia.</p>
      </div>
    </section>

    <section class="testimonials-grid-section" id="testimonials" itemscope itemtype="https://schema.org/ItemList" aria-label="Opiniones de clientes">
      <meta itemprop="name" content="Opiniones de nuestros clientes">
      <div class="container">
        <div class="testimonials-grid" role="list">
          <?php
          // Una sola consulta
          $sql = "SELECT id, Nombre, Opinion, Servicio, foto FROM opiniones";
          if ($result = $mysqli->query($sql)) {
            $pos = 1;
            while ($art = $result->fetch_assoc()) {
              $nombre   = htmlspecialchars((string)($art['Nombre'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
              $opinion  = htmlspecialchars((string)($art['Opinion'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
              $servicio = htmlspecialchars((string)($art['Servicio'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
              $fotoRaw  = (string)($art['foto'] ?? '');
              $foto     = $fotoRaw !== '' ? htmlspecialchars($fotoRaw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '/assets/images/flor_redonda.svg';

              echo '<div class="testimonials-item" role="listitem" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
              echo '<meta itemprop="position" content="'.(int)$pos.'">';

              echo "
                <article class='opinion-card testimonials-card' itemscope itemtype='https://schema.org/Review'>
                  <div class='opinion-avatar'>
                    <img src='{$foto}' alt='Foto de {$nombre}' loading='lazy' decoding='async' itemprop='image'>
                  </div>
                  <p class='opinion-text' itemprop='reviewBody'>{$opinion}</p>
                  <div class='opinion-name' itemprop='author'>{$nombre}</div>
                  <div class='opinion-service' itemprop='itemReviewed' itemscope itemtype='https://schema.org/Thing'>
                    <meta itemprop='name' content='{$servicio}'>{$servicio}
                  </div>
                </article>
              ";
              echo '</div>';
              $pos++;
            }
            $result->free();
          } else {
            echo "<div class='col-12'><p>No fue posible cargar testimonios en este momento.</p></div>";
          }
          ?>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="site-footer">
    <?php require_once __DIR__ . '/html/footer.php'; ?>
  </footer>

  <!-- JS -->
  <script src="assets/js/jquery-2.1.0.min.js"></script>
  <script src="assets/js/popper.js" defer></script>
  <script src="assets/js/bootstrap.min.js" defer></script>
  <script src="assets/js/scrollreveal.min.js" defer></script>
  <script src="assets/js/waypoints.min.js" defer></script>
  <script src="assets/js/jquery.counterup.min.js" defer></script>
  <script src="assets/js/imgfix.min.js" defer></script>
  <script src="assets/js/custom.js" defer></script>
</body>
</html>
