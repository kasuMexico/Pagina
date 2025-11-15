<?php
/**
 * Qué hace: Página de materiales/guías gratuitas. Presenta enlaces a descargas y enlaces de contacto.
 * Fecha: 03/11/2025
 * Revisado por: JCCM
 */

/* ===== Diagnóstico en desarrollo (puede desactivar en producción) ===== */
error_reporting(E_ALL);
ini_set('display_errors', '1');

/* ===== Sesión y dependencias ===== */
session_start();
// Librerías generales
require_once __DIR__ . '/eia/librerias.php';
// Teléfonos de contacto para header/footer
require_once __DIR__ . '/eia/php/Telcto.php';

/* ===== Seguridad menor ===== */
header_remove('X-Powered-By');

/* ===== Utilidades locales ===== */
/**
 * Normaliza teléfono a solo dígitos para construir ligas tel:/WhatsApp.
 * Fecha: 03/11/2025 — Revisado por: JCCM
 */
function digits_only(?string $s): string {
  return preg_replace('/\D+/', '', (string)$s) ?? '';
}
$tel_digits = digits_only($tel ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Descarga nuestras guías gratuitas | KASU</title>
  <meta name="description" content="KASU resuelve tus dudas con guías gratuitas sobre servicios funerarios y ahorro educativo. Descárgalas sin costo.">
  <meta name="keywords" content="guías gratuitas, servicios funerarios, ahorro educativo, KASU">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="author" content="Erendida Itzel Castro Marquez">
  <link rel="canonical" href="https://kasu.com.mx/materiales.php">
  <link rel="icon" href="/assets/images/kasu_logo.jpeg">

  <!-- Open Graph básico -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="KASU">
  <meta property="og:title" content="Guías gratuitas | KASU">
  <meta property="og:description" content="Descarga guías gratuitas sobre servicios funerarios y ahorro educativo.">
  <meta property="og:url" content="https://kasu.com.mx/materiales.php">
  <meta property="og:image" content="https://kasu.com.mx/assets/images/kasu_logo.jpeg">

  <!-- Fuentes y CSS -->
  <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/css/font-awesome.css">
  <link rel="stylesheet" href="/assets/css/templatemo-softy-pinko.css">
</head>
<body>
  <!-- SDK de Facebook (chat) — opcional -->
  <div id="fb-root"></div>
  <script>
    window.fbAsyncInit = function() {
      FB.init({xfbml:true, version:'v5.0'});
    };
    (function(d,s,id){
      var js, fjs=d.getElementsByTagName(s)[0];
      if(d.getElementById(id)) return;
      js=d.createElement(s); js.id=id;
      js.src='https://connect.facebook.net/es_LA/sdk/xfbml.customerchat.js';
      fjs.parentNode.insertBefore(js,fjs);
    })(document,'script','facebook-jssdk');
  </script>
  <div class="fb-customerchat"
       attribution="setup_tool"
       page_id="404668209882209"
       theme_color="#7646ff"
       logged_in_greeting="¿En qué puedo ayudarte?"
       logged_out_greeting="¿En qué puedo ayudarte?">
  </div>

  <!-- Modal genérico -->
  <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content" style="height:auto; padding:1em;">
        <div id="datos"></div>
      </div>
    </div>
  </div>

  <!-- Header -->
  <?php require_once __DIR__ . '/html/MenuPrincipal.php'; ?>

  <br><br><br><br><br>

  <section class="mini" id="work-process">
    <div class="mini-content">
      <div class="container">
        <div class="row">
          <div class="offset-xl-3 col-xl-6 offset-lg-2 col-lg-8 col-md-12 col-sm-12">
            <h1 class="section-title" style="font-size:60px;color:white">Guías gratuitas</h1>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section colored" id="pricing-plans">
    <div class="container">
      <div class="row">
        <!-- Guía funeraria -->
        <div class="col-lg-4 col-md-4 col-sm-6" data-scroll-reveal="enter bottom move 50px over 0.1s after 0.1s">
          <div class="pricing-item" id="productos">
            <div class="pricing-header"></div>
            <div class="pricing-body">
              <i><img style="height:280px" src="/assets/images/guiafuneraria.jpeg" alt="Guía para contratar un servicio funerario"></i>
              <br><br>
              <div class="dudasfun">
                <p>Descarga este material y aprende cómo escoger un proveedor funerario que te brinde soporte cuando enfrentas la pérdida de un familiar.</p>
              </div>
            </div>
            <a href="https://materiales.kasu.com.mx/guia-para-contratar-un-servicio-funerario" class="main-button-slider">Descargar Guía</a>
          </div>
        </div>

        <!-- Guía ahorro educativo -->
        <div class="col-lg-4 col-md-4 col-sm-6" data-scroll-reveal="enter bottom move 50px over 0.1s after 0.1s">
          <div class="pricing-item">
            <div class="pricing-body">
              <i><img style="height:280px" src="/assets/images/guiauniversitaria.jpg" alt="Guía de ahorro educativo"></i>
              <br><br>
              <div class="dudasuni">
                <p>Conoce cómo planear tu ahorro educativo, tipos de instrumentos y cómo elegir el más conveniente para tus metas.</p>
              </div>
            </div>
            <a href="https://materiales.kasu.com.mx/ahorro-educativo" class="main-button-slider">Descargar Guía</a>
          </div>
        </div>

        <!-- Placeholder tercera columna si se requiere más adelante -->
        <div class="col-lg-4 col-md-4 col-sm-6" data-scroll-reveal="enter bottom move 50px over 0.1s after 0.1s">
          <div class="pricing-item">
            <div class="pricing-body" style="text-align:center; padding:40px 20px;">
              <i class="fa fa-file-pdf-o" style="font-size:60px;" aria-hidden="true"></i>
              <br><br>
              <strong>Próximamente</strong>
              <p style="margin-top:10px;">Más materiales de retiro y planeación financiera.</p>
            </div>
          </div>
        </div>

      </div> <!-- row -->
    </div> <!-- container -->
  </section>

    <!-- Contacto -->
    <footer>
        <?php require_once __DIR__ . '/html/footer.php'; ?>
    </footer>


  <!-- JS -->
  <script src="/assets/js/jquery-2.1.0.min.js"></script>
  <script src="/assets/js/popper.js" defer></script>
  <script src="/assets/js/bootstrap.min.js" defer></script>
  <script src="/assets/js/scrollreveal.min.js" defer></script>
  <script src="/assets/js/waypoints.min.js" defer></script>
  <script src="/assets/js/jquery.counterup.min.js" defer></script>
  <script src="/assets/js/imgfix.min.js" defer></script>
  <script src="/assets/js/custom.js" defer></script>
  <!-- Carga única del loader externo -->
  <script async src="https://d335luupugsy2.cloudfront.net/js/loader-scripts/28dd2782-ee7d-4b25-82b1-f5993b27764a-loader.js"></script>
</body>
</html>