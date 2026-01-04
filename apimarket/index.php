<?php
/********************************************************************************************
 * Qué hace: Página pública del API Market de KASU. Muestra métricas y módulos estáticos.
 * Fecha: 04/11/2025
 * Revisado por: JCCM
 * Archivo: index.php
 *
 * Notas PHP 8.2:
 * - Sanitización estricta en echo de parámetros GET (ENT_QUOTES, UTF-8).
 * - Rutas absolutas con __DIR__ conservadas.
 * - No se modifican firmas ni retornos de funciones usadas ($basicas->MaxDat).
 ********************************************************************************************/

declare(strict_types=1);

// Debug control (set ?debug=1 or APIMARKET_DEBUG=1)
$debugEnabled = false;
if (isset($_GET['debug'])) {
    $debugEnabled = ($_GET['debug'] === '1' || $_GET['debug'] === 'true');
}
if (getenv('APIMARKET_DEBUG') === '1') {
    $debugEnabled = true;
}

if ($debugEnabled) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/_debug.log');
    error_reporting(E_ALL);

    set_exception_handler(function (Throwable $e): void {
        if (!headers_sent()) {
            header('Content-Type: text/plain; charset=UTF-8', true, 500);
        }
        echo "Uncaught exception:\n";
        echo $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
    });

    register_shutdown_function(function (): void {
        $err = error_get_last();
        if (!$err) {
            return;
        }
        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
        if (!in_array($err['type'], $fatalTypes, true)) {
            return;
        }
        if (!headers_sent()) {
            header('Content-Type: text/plain; charset=UTF-8', true, 500);
        }
        echo "Fatal error:\n";
        echo $err['message'] . "\n";
        echo "File: " . $err['file'] . "\n";
        echo "Line: " . $err['line'] . "\n";
    });
}

// Prefijos relativos para assets y documentación (root vs /documentacion)
function apimarket_path_prefixes(): array {
    $script = isset($_SERVER['SCRIPT_NAME']) ? (string)$_SERVER['SCRIPT_NAME'] : '';
    $scriptDir = str_replace('\\', '/', dirname($script));
    $isDocPage = (strpos($scriptDir, '/documentacion') !== false);
    $assetPrefix = $isDocPage ? '../assets/' : 'assets/';
    $docPrefix = $isDocPage ? '../documentacion/' : 'documentacion/';
    return [$isDocPage, $assetPrefix, $docPrefix];
}

[$isDocPage, $assetPrefix, $docPrefix] = apimarket_path_prefixes();
$assetFsPrefix = $isDocPage ? dirname(__DIR__) . '/assets/' : __DIR__ . '/assets/';

// Librerías y conexiones
require_once __DIR__ . '/librerias_api.php';

// Archivo de rastreo de Google Tag Manager (vía raíz -> /eia/)
require_once __DIR__ . '/../eia/analytics_bootstrap.php';

// Alerta opcional
if (isset($_GET['Msg'])) {
    $msg = htmlspecialchars((string)$_GET['Msg'], ENT_QUOTES, 'UTF-8');
    echo "<script type='text/javascript'>alert('{$msg}');</script>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>API Market de KASU | Open banking y servicios fintech integrables</title>

  <!-- Canonical y hreflang -->
  <link rel="canonical" href="https://kasu.com.mx/apimarket/">
  <link rel="alternate" hreflang="es-MX" href="https://kasu.com.mx/apimarket/">

  <!-- Viewport -->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- SEO básico -->
  <meta name="description" content="Integra el API Market de KASU a tu app web o móvil. Cobros, cuentas, clientes y remesas mediante APIs seguras, documentación y soporte.">
  <meta name="author" content="Erendida Itzel Castro Marquez; Jose Carlos Cabrera Monroy">
  <meta name="robots" content="index,follow,max-image-preview:large">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="KASU">
  <meta property="og:title" content="API Market de KASU | Open banking y servicios fintech integrables">
  <meta property="og:description" content="Conecta tu plataforma al API Market de KASU. Pagos, cuentas y clientes con seguridad y soporte.">
  <meta property="og:url" content="https://kasu.com.mx/apimarket/">
  <meta property="og:image" content="https://kasu.com.mx/assets/images/og/kasu-apimarket.png">
  <meta property="og:locale" content="es_MX">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="API Market de KASU | Open banking y servicios fintech integrables">
  <meta name="twitter:description" content="Integra pagos, cuentas y clientes a tu app con las APIs de KASU.">
  <meta name="twitter:image" content="https://kasu.com.mx/assets/images/og/kasu-apimarket.png">

  <!-- Icono -->
  <link rel="icon" href="https://kasu.com.mx/assets/images/Index/florkasu.png">

  <!-- Performance: preconnect y fuentes -->
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900&display=swap" rel="stylesheet">

  <!-- CSS externo + local -->
  <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/font-awesome.css">
  <link rel="stylesheet" href="https://kasu.com.mx/assets/css/templatemo-softy-pinko.css">
  <link rel="stylesheet" href="https://kasu.com.mx/assets/css/index.css">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($assetPrefix . 'index.css', ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($assetPrefix . 'codigo.css', ENT_QUOTES, 'UTF-8'); ?>">

  <!-- PWA tint opcional -->
  <meta name="theme-color" content="#e83e8c">

  <!-- JSON-LD: Organization + WebSite + WebPage -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "Organization",
        "name": "KASU",
        "url": "https://kasu.com.mx/",
        "logo": "https://kasu.com.mx/assets/images/kasu_logo.jpeg",
        "sameAs": [
          "https://www.facebook.com/kasu.mx",
          "https://www.linkedin.com/company/kasu-mx"
        ]
      },
      {
        "@type": "WebSite",
        "name": "KASU",
        "url": "https://kasu.com.mx/",
        "potentialAction": {
          "@type": "SearchAction",
          "target": "https://kasu.com.mx/buscar?q={search_term_string}",
          "query-input": "required name=search_term_string"
        }
      },
      {
        "@type": "WebPage",
        "name": "API Market de KASU",
        "url": "https://kasu.com.mx/apimarket/",
        "description": "Marketplace de APIs de KASU para integrar pagos, cuentas y clientes en apps web o móviles.",
        "breadcrumb": {
          "@type": "BreadcrumbList",
          "itemListElement": [
            { "@type": "ListItem", "position": 1, "name": "Inicio", "item": "https://kasu.com.mx/" },
            { "@type": "ListItem", "position": 2, "name": "API Market", "item": "https://kasu.com.mx/apimarket/" }
          ]
        }
      }
    ]
  }
  </script>
</head>

<body>
  <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content" style="height:auto; padding:1em;">
        <div id="datos"></div>
      </div>
    </div>
  </div>

  <!-- ***** Header Area Start ***** -->
  <header class="header-area header-sticky" role="banner">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <nav class="main-nav" aria-label="Principal">
            <!-- Logo -->
            <a href="https://kasu.com.mx/" class="logo" aria-label="KASU inicio">
              <img src="https://kasu.com.mx/assets/images/kasu_logo.jpeg" alt="KASU" loading="eager" decoding="async"/>
            </a>
            <!-- Menu -->
            <ul class="nav">
              <li><a style="color: black;" href="https://kasu.com.mx/" class="comprar">KASU</a></li>
              <li><a style="color: black;" href="#apikasu">Documentación</a></li>
              <li><a style="color: black;" href="#contact-us">Contáctanos</a></li>
            </ul>
            <a class="menu-trigger" aria-label="Abrir menú">
              <span>Menú</span>
            </a>
          </nav>
        </div>
      </div>
    </div>
  </header>

  <div class="welcome-area apimarket-hero">
    <div class="header-text apimarket-hero__text">
      <div class="container">
        <?php
        $heroFile = $assetFsPrefix . 'img/hero_apimarket.svg';
        $heroUrl = $assetPrefix . 'img/hero_apimarket.svg';
        ?>
        <div class="row align-items-center">
          <div class="col-lg-6 col-md-12 col-sm-12">
            <h1 class="apimarket-hero__title">Todas las oportunidades del open insurance a tu alcance <strong>Apimarket_KASU</strong></h1>
            <p class="apimarket-hero__lead">Integra cobros, cuentas y validaciones con APIs seguras, documentación clara y soporte directo.</p>
            <a class="main-button-slider apimarket-hero__cta" href="<?php echo htmlspecialchars($docPrefix . 'doc_customer.php', ENT_QUOTES, 'UTF-8'); ?>">Documentación</a>
          </div>
          <div class="col-lg-6 col-md-12 col-sm-12">
            <?php if (is_file($heroFile)) { ?>
              <img src="<?php echo htmlspecialchars($heroUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="API Market KASU" class="img-fluid apimarket-hero__img" loading="lazy" decoding="async">
            <?php } else { ?>
              <div class="features-small-item" style="margin-top: 20px;">
                <div class="section-title">
                  <h2><strong>Documenta y lanza en minutos</strong></h2>
                </div>
                <p>Activa endpoints, valida identidad y escala tu producto con un flujo simple de autenticación.</p>
                <a class="btn btn-info" href="<?php echo htmlspecialchars($docPrefix . 'doc_customer.php', ENT_QUOTES, 'UTF-8'); ?>">Documentación</a>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Descripción General de la API -->
  <section class="section padding-top-70" id="">
    <div class="container">
      <div class="row">
        <div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
          <div class="center-heading">
            <div class="count-item decoration-bottom">
              <h2 class="section-title">
                <strong>
                  <?php
                  // Contador de clientes activos
                  echo number_format((float)$basicas->MaxDat($mysqli, "Id", "Venta"), 0, ".", ",");
                  ?>
                </strong>
                <span> Clientes Activos</span>
              </h2>
            </div>
            <p style="text-align: justify;"><strong>KASU</strong> es una plataforma que cuenta con un entorno de gestión robusto que permite a los usuarios realizar la compra de pequeñas partes de fideicomisos que sirven como ahorro para afrontar situaciones difíciles en su vida, tales como gastos funerarios, enviar a sus hijos a la universidad, crear fondos para el retiro y envío y recepción de remesas.</p>
          </div>
        </div>
        <div class="col-lg-2 col-md-12 col-sm-12 align-self-center"></div>
        <div class="col-lg-4 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
          <div class="row">
            <div class="features-small-item">
              <div class="center-heading">
                <h2 class="section-title">Usa el entorno KASU</h2>
              </div>
              <div class="center-text" id="apikasu">
                <p style="text-align: justify;"><strong>1.- </strong>Comercializa nuestros servicios y recibe interesantes comisiones por ello.</p>
                <p style="text-align: justify;"><strong>2.- </strong>Recibe los pagos que nuestros clientes tienen que hacer sobre los servicios de KASU y obtén una comisión por cada peso que cobres.</p>
                <p style="text-align: justify;"><strong>3.- </strong>Realiza validaciones de datos de clientes con los datos de los clientes de KASU.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Selecciona la API -->
  <section class="section colored padding-top-70" id="Ventajas">
    <div class="col-lg-12">
      <div class="center-heading">
        <br><br>
        <h2 class="section-title">Selecciona la <strong>API's</strong> que mejor se adapte a tus necesidades</h2>
      </div>
    </div>
    <br>
    <?php require_once __DIR__ . '/html/select_api.php'; ?>
  </section>

  <!-- Sección de usabilidad general -->
  <section class="section padding-top-70">
    <div class="container">
      <div class="Consulta">
        <h2 class="titulos"><strong>USABILIDAD GENERAL</strong></h2>
        <br>
        <p>Las <strong>API</strong> que hemos desarrollado para ti cuentan con una usabilidad formada por bloques que pueden comunicarse entre sí o intercambiar información generada en un bloque para interactuar en cualquier otro.</p>
        <br>
        <p>Solo recuerda que debes tener permisos para cada una de nuestras <strong>API</strong> verticales.</p>
        <br>
      </div>
      <div class="table-container">
        <table class="table api-table">
          <tbody>
            <tr>
              <td class="blue">
                <h2><strong>API_CUSTOMER</strong></h2>
                <br><p>Modelo <strong>PREPAGO</strong> para consultas de datos de clientes.</p>
              </td>
              <td class="red">
                <h2><strong>API_PAYMENTS</strong></h2>
                <br><p>Modelo <strong>POSPAGO</strong> para cobros y conciliación mensual.</p>
              </td>
              <td class="purple">
                <h2><strong>API_ACCOUNTS</strong></h2>
                <br><p>Alta de servicios KASU y comisiones por venta desde tu plataforma.</p>
              </td>
              <td class="teal">
                <h2><strong>Validate_Mexico</strong></h2>
                <br><p>Validación de identidad <strong>CURP/RFC</strong> con caché y prepago.</p>
              </td>
            </tr>
            <tr>
              <td colspan="4" class="green">
                <h2><strong>token_full</strong></h2>
                <p>Token de acceso para las APIs de KASU. Los detalles y ejemplos se encuentran en cada documentación.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Autenticación -->
  <?php require_once __DIR__ . '/html/Autenticacion.php'; ?>

  <!-- formulario de Contacto -->
  <?php require_once __DIR__ . '/html/Contacto.php';?>

  <!-- Footer -->
  <footer>
    <?php require_once __DIR__ . '/html/footer.php'; ?>
  </footer>

  <!-- Scripts -->
  <script src="https://kasu.com.mx/assets/js/jquery-2.1.0.min.js" defer></script>
  <script src="https://kasu.com.mx/assets/js/bootstrap.min.js" defer></script>
  <script src="https://kasu.com.mx/assets/js/scrollreveal.min.js" defer></script>
  <script src="https://kasu.com.mx/assets/js/custom.js" defer></script>
</body>
</html>
