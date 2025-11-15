<?php
/**
 * Qué hace: Landing de prospectos. Lee ?data/?Usr/?SerFb/?Ori en base64, arma copy dinámico y envía a Registro_Prospectos.php.
 * Fecha: 03/11/2025
 * Revisado por: JCCM
 */

// ===== 0) Sesión y dependencias =====
session_start();

// GTM (inserta <head> y <noscript> automáticamente)
require_once $_SERVER['DOCUMENT_ROOT'] . '/eia/analytics_bootstrap.php';

// Librerías de la app
require_once __DIR__ . '/eia/librerias.php';

// ===== 1) Parámetros seguros =====
// Nota: FILTER_SANITIZE_STRING está deprecado. Usamos FILTER_UNSAFE_RAW + trim + whitelist.
function b64safe(?string $v): string {
  if ($v === null || $v === '') return '';
  $v = trim($v);
  // Base64 "común" y url-safe. Bloquea caracteres fuera del set esperado.
  if (!preg_match('/^[A-Za-z0-9+\/_=.\-\s]+$/', $v)) return '';
  $dec = base64_decode($v, true);
  return $dec === false ? '' : trim($dec);
}

$data_enc  = filter_input(INPUT_GET, 'data',   FILTER_UNSAFE_RAW) ?? '';
$usr_enc   = filter_input(INPUT_GET, 'Usr',    FILTER_UNSAFE_RAW) ?? '';
$serfb_enc = filter_input(INPUT_GET, 'SerFb',  FILTER_UNSAFE_RAW) ?? '';
$ori_enc   = filter_input(INPUT_GET, 'Ori',    FILTER_UNSAFE_RAW) ?? '';

$data   = b64safe($data_enc);
$usr    = b64safe($usr_enc);
$SerFb  = b64safe($serfb_enc);
$Origen = b64safe($ori_enc);

// ===== 2) Catálogo de servicios =====
$serviceMap = [
  'FUNERARIO'     => 'Gastos Funerarios',
  'POLICIAS'      => 'Seguridad Pública',
  'UNIVERSITARIO' => 'Inversión Universitaria',
  'RETIRO'        => 'Ahorro para el Retiro',
  'DISTRIBUIDOR'  => 'Agente Externo',
  'CITREG'        => 'Registrar Cita',
  'CITA'          => 'Registrar Cita',
];

$config = [
  'FUNERARIO' => [
    'title'   => '¡Estás un paso más cerca!',
    'message' => 'El equipo KASU está preparando tu cotización, déjanos tus datos',
    'btn'     => 'Registrarme y continuar',
    'imgSide' => 'https://kasu.com.mx/assets/images/gasto_funerario.svg',
    'imgSeo'  => 'https://kasu.com.mx/assets/images/registro/funerario.png',
  ],
  'UNIVERSITARIO' => [
    'title'   => 'Inversión Universitaria',
    'message' => 'Estás cerca de asegurar la educación universitaria de tu hijo',
    'btn'     => 'Registrarme',
    'imgSide' => 'https://kasu.com.mx/assets/images/gasto_universitario.svg',
    'imgSeo'  => 'https://kasu.com.mx/assets/images/registro/universidad.png',
  ],
  'RETIRO' => [
    'title'   => 'Servicio de Retiro',
    'message' => 'Regístrate y en un momento te contactará alguien de nuestro equipo',
    'btn'     => 'Registrarme',
    'imgSide' => 'https://kasu.com.mx/assets/images/gasto_retiro.svg',
    'imgSeo'  => 'https://kasu.com.mx/assets/images/registro/retiro.png',
  ],
  'POLICIAS' => [
    'title'   => 'Contacta con un agente',
    'message' => 'El personal de seguridad merece el mejor respaldo en los momentos más difíciles',
    'btn'     => 'Contactar',
    'imgSide' => 'https://kasu.com.mx/assets/images/gasto_policias.svg',
    'imgSeo'  => 'https://kasu.com.mx/assets/images/registro/policias.png',
  ],
  'DISTRIBUIDOR' => [
    'title'   => 'Agente Externo',
    'message' => 'Felicidades, estás a un paso de generar ingresos desde tu celular',
    'btn'     => 'Recibir más información',
    'imgSide' => 'https://kasu.com.mx/assets/images/padres_con_hijos.jpeg',
    'imgSeo'  => 'https://kasu.com.mx/assets/images/registro/default.png',
  ],
  'CITREG' => [
    'title'   => 'Registrar Cita',
    'message' => 'Registra el día que puedas recibir una llamada de uno de nuestros agentes',
    'btn'     => 'Registrar cita',
    'imgSide' => 'https://kasu.com.mx/assets/images/registro/cita.png',
    'imgSeo'  => 'https://kasu.com.mx/assets/images/registro/cita.png',
  ],
  'CITA' => [
    'title'   => 'Cita Telefónica',
    'message' => 'Elige día y hora para tu llamada con un ejecutivo',
    'btn'     => 'Registrar cita',
    'imgSide' => 'https://kasu.com.mx/assets/images/registro/cita.png',
    'imgSeo'  => 'https://kasu.com.mx/assets/images/registro/cita.png',
  ],
];

$defaults = [
  'title'   => 'Regístrate',
  'message' => 'Te enviaremos por correo la información necesaria para conocer todo sobre KASU.',
  'btn'     => 'Registrarme',
  'imgSide' => 'https://kasu.com.mx/assets/images/registro/familiaformulario.png',
  'imgSeo'  => 'https://kasu.com.mx/assets/images/registro/default.png',
];

// Servicio válido o default
$svcKey   = array_key_exists($data, $serviceMap) ? $data : '';
$svcName  = $serviceMap[$svcKey] ?? 'Registro';
$settings = array_merge($defaults, $config[$svcKey] ?? []);

// ===== 3) Duplicados por correo (si viene Usr) =====
// Usa la instancia $basicas incluida por librerias.php. Selecciona conexión válida ($pros puede no existir).
if ($usr !== '') {
  $conn = (isset($pros) && $pros instanceof mysqli) ? $pros : $mysqli;
  $prosId = $basicas->BuscarCampos($conn, 'Id', 'Distribuidores', 'IdProspecto', $usr);
  if (!empty($prosId) && !in_array($svcKey, ['CITREG','CITA'], true)) {
    $msg = rawurlencode('Lo sentimos, este correo ya se ha usado.');
    header('Location: https://kasu.com.mx/index.php?Msg=' . $msg, true, 303);
    exit;
  }
}

// ===== 4) Emergentes por Ml (si tu selector los necesita) =====
require_once __DIR__ . '/login/php/Selector_Emergentes_Ml.php';

// ===== 5) Variables para SEO =====
$reqUri   = $_SERVER['REQUEST_URI'] ?? '/prospectos.php';
$absUrl   = 'https://kasu.com.mx' . strtok($reqUri, ' ');
$canonical = 'https://kasu.com.mx/prospectos.php';
if ($data_enc !== '') {
  $canonical .= '?data=' . urlencode($data_enc);
}
$metaTitle = 'Prospecto | ' . htmlspecialchars($svcName, ENT_QUOTES, 'UTF-8');
$metaDesc  = htmlspecialchars($settings['message'], ENT_QUOTES, 'UTF-8');
$metaImage = htmlspecialchars($settings['imgSeo'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title><?= $metaTitle ?></title>
  <meta name="description" content="<?= $metaDesc ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="index,follow,max-image-preview:large">
  <meta name="theme-color" content="#911F66">

  <!-- Canonical -->
  <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= htmlspecialchars($absUrl, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:title" content="<?= $metaTitle ?>">
  <meta property="og:description" content="<?= $metaDesc ?>">
  <meta property="og:image" content="<?= $metaImage ?>">
  <meta property="og:site_name" content="KASU">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= $metaTitle ?>">
  <meta name="twitter:description" content="<?= $metaDesc ?>">
  <meta name="twitter:image" content="<?= $metaImage ?>">

  <!-- JSON-LD WebSite + WebPage -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebPage",
    "url": "<?= htmlspecialchars($absUrl, ENT_QUOTES, 'UTF-8') ?>",
    "name": "<?= $metaTitle ?>",
    "description": "<?= $metaDesc ?>",
    "isPartOf": {
      "@type": "WebSite",
      "name": "KASU",
      "url": "https://kasu.com.mx"
    },
    "primaryImageOfPage": {
      "@type": "ImageObject",
      "url": "<?= $metaImage ?>",
      "width": 1200,
      "height": 630
    }
  }
  </script>

  <link rel="icon" href="/assets/images/kasu_logo.jpeg">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/css/Compra.css">
  <style>
    /* CSS mínimo para LCP y accesibilidad */
    img{max-width:100%;height:auto}
    .AreaTrabajo{padding:24px}
    .sr-only{position:absolute;width:1px;height:1px;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0}
  </style>
</head>
<body>
  <section id="Formulario" class="row" aria-labelledby="título-formulario">
    <!-- Formulario (izquierda) -->
    <div class="col-md-6 AreaTrabajo">
      <form method="POST"
            id="<?= $SerFb ? 'cita-'.htmlspecialchars($SerFb,ENT_QUOTES,'UTF-8') : 'Prospecto-'.htmlspecialchars($svcKey?:'GEN',ENT_QUOTES,'UTF-8') ?>"
            action="/login/php/Registro_Prospectos.php"
            onsubmit="try{window.dataLayer=window.dataLayer||[];dataLayer.push({event:'lead_submit',form_id:this.id,service:'<?= htmlspecialchars($svcKey?:'GEN',ENT_QUOTES,'UTF-8') ?>',origin:'<?= htmlspecialchars($Origen,ENT_QUOTES,'UTF-8') ?>'});}catch(e){}">
        <div class="logo text-center">
          <a href="/" aria-label="Ir a inicio KASU">
            <img src="/assets/images/kasu_logo.jpeg" alt="KASU" width="96" height="96" loading="eager" fetchpriority="high">
          </a>
        </div>

        <h1 id="título-formulario" class="text-center"><?= htmlspecialchars($settings['title'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-center"><?= htmlspecialchars($settings['message'], ENT_QUOTES, 'UTF-8') ?></p>

        <div class="Formulario">
          <input type="hidden" name="Host"   value="<?= htmlspecialchars($_SERVER['PHP_SELF'] ?? '/prospectos.php', ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="Cupon"  value="<?= htmlspecialchars($data_enc, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="Origen" value="<?= htmlspecialchars($ori_enc, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="SerFb"  value="<?= htmlspecialchars($serfb_enc, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="Svc"    value="<?= htmlspecialchars($svcKey?:'GEN', ENT_QUOTES, 'UTF-8') ?>">

          <label class="sr-only" for="name">Nombre</label>
          <input type="text"  id="name"  name="name" class="form-control" placeholder="Nombre" required autocomplete="name">

          <label class="sr-only" for="Mail">Correo electrónico</label>
          <input type="email" id="Mail"  name="Mail" class="form-control" placeholder="Correo electrónico" required autocomplete="email">

          <label class="sr-only" for="Telefono">Teléfono</label>
          <input type="tel"   id="Telefono" name="Telefono" class="form-control" placeholder="Teléfono" required inputmode="tel" autocomplete="tel">

          <?php if (in_array($svcKey, ['CITA','CITREG'], true)): ?>
            <label for="FechaCita">Fecha de cita</label>
            <input type="date" id="FechaCita" name="FechaCita" class="form-control" required>
            <label for="HoraCita">Hora de cita</label>
            <input type="time" id="HoraCita" name="HoraCita" class="form-control" required>
          <?php endif; ?>
        </div>

        <div class="Formulario text-center" style="margin-top:1em">
          <button type="submit" name="FormProspecto" class="btn btn-primary">
            <?= htmlspecialchars($settings['btn'], ENT_QUOTES, 'UTF-8') ?>
          </button>
        </div>

        <div class="Ligas text-center" style="margin-top:1em">
          <a href="/" style="color:#911F66">Regresar a KASU</a> |
          <a href="/terminos-y-condiciones.php" style="color:#012F91" rel="nofollow">Términos y Condiciones</a>
        </div>
      </form>
    </div>

    <!-- Imagen lateral (derecha) -->
    <div class="col-md-6 text-center" aria-hidden="true">
      <img src="<?= htmlspecialchars($settings['imgSide'], ENT_QUOTES, 'UTF-8') ?>"
           alt="Servicio <?= htmlspecialchars($svcName, ENT_QUOTES, 'UTF-8') ?>"
           width="900" height="900" loading="lazy">
    </div>
  </section>

  <!-- JS -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js" defer></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js" defer></script>
  <script src="/eia/javascript/Registro.js" defer></script>
  <script src="/eia/javascript/finger.js" defer></script>

  <!-- Envío de vista de página virtual para landings específicas -->
  <script>
    window.dataLayer = window.dataLayer || [];
    dataLayer.push({
      event:'virtual_pageview',
      page_title:'<?= $metaTitle ?>',
      page_location:'<?= htmlspecialchars($absUrl, ENT_QUOTES, 'UTF-8') ?>',
      service:'<?= htmlspecialchars($svcKey || 'GEN', ENT_QUOTES, 'UTF-8') ?>',
      origin:'<?= htmlspecialchars($Origen, ENT_QUOTES, 'UTF-8') ?>'
    });
  </script>
</body>
</html>