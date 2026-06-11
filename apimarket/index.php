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
  <title>API Market KASU V1 | Cuentas, pagos, clientes y validación</title>

  <!-- Canonical y hreflang -->
  <link rel="canonical" href="https://kasu.com.mx/apimarket/">
  <link rel="alternate" hreflang="es-MX" href="https://kasu.com.mx/apimarket/">

  <!-- Viewport -->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- SEO básico -->
  <meta name="description" content="Integra API Market KASU V1 a tu app web o móvil. Accounts, Payments, Customer y Validate_Mexico con autenticación Bearer y respuestas JSON.">
  <meta name="author" content="Erendida Itzel Castro Marquez; Jose Carlos Cabrera Monroy">
  <meta name="robots" content="index,follow,max-image-preview:large">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="KASU">
  <meta property="og:title" content="API Market KASU V1 | APIs de cuentas, pagos, clientes y validación">
  <meta property="og:description" content="Conecta tu plataforma a API_ACCOUNTS, API_PAYMENTS, API_CUSTOMER y Validate_Mexico con seguridad y soporte.">
  <meta property="og:url" content="https://kasu.com.mx/apimarket/">
  <meta property="og:image" content="https://kasu.com.mx/assets/images/Index/ksulogo.png">
  <meta property="og:locale" content="es_MX">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="API Market de KASU | Open banking y servicios fintech integrables">
  <meta name="twitter:description" content="Integra cuentas, pagos, clientes y validaciones CURP/RFC a tu app con APIs KASU V1.">
  <meta name="twitter:image" content="https://kasu.com.mx/assets/images/Index/ksulogo.png">

  <!-- Icono -->
  <link rel="icon" href="https://kasu.com.mx/assets/images/Index/florkasu.png">

  <!-- Fuentes -->
  <link rel="stylesheet" type="text/css" href="/assets/css/fonts.css">

  <!-- CSS externo + local -->
  <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/font-awesome.css">
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
        "logo": "https://kasu.com.mx/assets/images/Index/florkasu.png",
        "sameAs": [
          "https://www.facebook.com/kasu.mx",
          "https://www.linkedin.com/company/kasu-mx"
        ]
      },
      {
        "@type": "WebSite",
        "name": "KASU",
        "url": "https://kasu.com.mx/"
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
              <img src="https://kasu.com.mx/assets/images/Index/florkasu.png" alt="KASU" loading="eager" decoding="async"/>
            </a>
            <!-- Menu -->
            <ul class="nav">
              <li><a style="color: black;" href="https://kasu.com.mx/" class="comprar">KASU</a></li>
              <li><a style="color: black;" href="#Ventajas">APIs V1</a></li>
              <li><a style="color: black;" href="#Autentica">Autenticación</a></li>
              <li><a style="color: black;" href="acceso.php">Solicitar acceso</a></li>
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
        <div class="row align-items-center">
          <div class="col-lg-6 col-md-12 col-sm-12">
            <span class="api-kicker">API Market KASU V1</span>
            <h1 class="apimarket-hero__title">APIs para registrar servicios, cobrar, consultar clientes y validar identidad.</h1>
            <p class="apimarket-hero__lead">Integra <strong>API_ACCOUNTS</strong>, <strong>API_PAYMENTS</strong>, <strong>API_CUSTOMER</strong> y <strong>Validate_Mexico</strong> con autenticación <strong>Token_Full</strong>, Bearer token y respuestas JSON consistentes.</p>
            <div class="api-action-row">
              <a class="api-button" href="<?php echo htmlspecialchars($docPrefix . 'doc_accounts.php', ENT_QUOTES, 'UTF-8'); ?>">Ver APIs V1</a>
              <a class="api-button api-button--secondary" href="#Autentica">Autenticación</a>
              <a class="api-button api-button--secondary" href="acceso.php">Solicitar acceso</a>
            </div>
            <div class="apimarket-stats">
              <div class="apimarket-stat">
                <strong>4</strong>
                <span>APIs V1 públicas</span>
              </div>
              <div class="apimarket-stat">
                <strong>JSON</strong>
                <span>Errores y respuestas</span>
              </div>
              <div class="apimarket-stat">
                <strong>Bearer</strong>
                <span>Autorización</span>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-md-12 col-sm-12">
            <div class="apimarket-hero__panel" aria-label="Ejemplo de consumo API Market KASU">
              <div class="apimarket-hero__panel-head">
                <span>Contrato público vigente</span>
                <span>V1</span>
              </div>
              <pre><code>POST https://apimarket.kasu.com.mx/api/Accounts_V1
Authorization: Bearer API_KEY_AQUI
Content-Type: application/json

{
  "tipo_peticion": "new_service",
  "nombre_de_usuario": "YOUR_APPUSER",
  "curp_en_uso": "CURP_CODE",
  "producto": "Funerario",
  "terminos": "acepto",
  "aviso": "acepto",
  "fideicomiso": "acepto",
  "token_data": {
    "timestamp": 1760000000,
    "expires_in": 600
  }
}</code></pre>
            </div>
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
            <p style="text-align: justify;"><strong>API Market KASU</strong> expone los flujos transaccionales de la plataforma para aliados: alta de servicios, consulta de clientes, cobranza y validación de identidad. El contrato público vigente usa endpoints <strong>V1</strong> y autenticación <strong>Token_Full</strong>.</p>
          </div>
        </div>
        <div class="col-lg-2 col-md-12 col-sm-12 align-self-center"></div>
        <div class="col-lg-4 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
          <div class="row">
            <div class="doc-panel" id="apikasu">
              <span class="api-kicker">Casos de uso</span>
              <h2 class="section-title">Opera servicios KASU desde tu plataforma</h2>
              <p style="text-align: justify;">Registra ventas, consulta datos autorizados, cobra parcialidades y valida CURP/RFC usando respuestas JSON estables para integración web, móvil o backoffice.</p>
              <div class="api-action-row">
                <a class="api-button api-button--secondary" href="<?php echo htmlspecialchars($docPrefix . 'doc_accounts.php', ENT_QUOTES, 'UTF-8'); ?>">Empezar con Accounts</a>
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
        <span class="api-kicker">Documentación</span>
        <h2 class="section-title">Selecciona la API que necesitas integrar</h2>
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
        <p>Las APIs V1 comparten autenticación, errores JSON y datos de token. Cada token se liga al usuario, a la CURP de operación y a los permisos habilitados para tu integración.</p>
        <br>
      </div>
      <div class="api-flow">
        <div class="api-flow__item">
          <strong>1. Token_Full</strong>
          <span>Genera el Bearer token con firma HMAC y vigencia de 10 minutos.</span>
        </div>
        <div class="api-flow__item">
          <strong>2. Accounts</strong>
          <span>Crea contacto, usuario, legal, venta y liga de pago.</span>
        </div>
        <div class="api-flow__item">
          <strong>3. Customer</strong>
          <span>Consulta datos autorizados, catálogo y ventas por CURP.</span>
        </div>
        <div class="api-flow__item">
          <strong>4. Payments</strong>
          <span>Consulta estado de cuenta y registra pagos con mora si aplica.</span>
        </div>
        <div class="api-flow__item">
          <strong>5. Validate_Mexico</strong>
          <span>Valida CURP/RFC con caché y wallet prepago.</span>
        </div>
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
