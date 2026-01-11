<?php
/**
 * Qué hace: Página de producto. Carga por ?Art=ID, arma SEO/OG/LD+JSON y CTAs, con opcional de Mercado Pago.
 * Fecha: 03/11/2025
 * Revisado por: JCCM
 */

/************************************************************************
  productos.php
  - Carga por ?Art=ID con canónica a /productos/{slug}
  - Sanitiza entrada y evita consultas duplicadas
  - MercadoPago opcional con ?Pro=REFERENCIA
************************************************************************/

session_start();
require_once __DIR__ . '/eia/analytics_bootstrap.php';
require_once __DIR__ . '/eia/librerias.php';
// Se establece el número de contacto según horario
require_once __DIR__ . '/eia/php/Telcto.php';
$tel = isset($tel) && $tel !== '' ? $tel : '7208177632';

/* ---------- 1) Entrada segura ---------- */
$artId = filter_input(INPUT_GET, 'Art', FILTER_VALIDATE_INT, [
  'options' => ['default' => 1, 'min_range' => 1]
]);

/* ---------- 2) Validación básica ---------- */
$maxProd = (int)$basicas->MaxDat($mysqli, "Id", "ContProd");
if ($artId < 1 || $artId > $maxProd) {
  header('Location: https://kasu.com.mx/error', true, 302);
  exit;
}

/* ---------- 3) Carga del producto (UNA consulta) ---------- */
$stmt = $mysqli->prepare("SELECT * FROM ContProd WHERE Id = ?");
if (!$stmt) {
  header('Location: https://kasu.com.mx/error', true, 302);
  exit;
}
$stmt->bind_param('i', $artId);
$stmt->execute();
$Reg = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$Reg) {
  header('Location: https://kasu.com.mx/error', true, 302);
  exit;
}

/* ---------- 4) MercadoPago por ?Pro= ---------- */
/* FILTER_SANITIZE_STRING está deprecado en 8.1+. Usar FILTER_UNSAFE_RAW + limpieza manual. */
$proRef = filter_input(INPUT_GET, 'Pro', FILTER_UNSAFE_RAW);
$proRef = $proRef !== null ? trim($proRef) : '';
$proRef = $proRef !== '' ? preg_replace('/[^\w\-\.]/u', '', $proRef) : '';

$preference = '';
if ($proRef !== '') {
  $ligamp   = $basicas->BuscarCampos($mysqli, "Liga",  "MercadoPago", "Referencia", $proRef);
  $mediPago = (int)$basicas->BuscarCampos($mysqli, "Plazo", "MercadoPago", "Referencia", $proRef);
  if (!empty($ligamp)) {
    $preference = ($mediPago === 1)
      ? "https://www.mercadopago.com.mx/checkout/v1/redirect?preference-id=" . urlencode($ligamp)
      : "https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_plan_id=" . urlencode($ligamp);
  }
}

/* ---------- 5) Utilidades SEO ---------- */
function slugify($text) {
  $text = (string)$text;
  $from = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
  $text = $from !== false ? $from : $text;
  $text = preg_replace('~[^\\pL\\d]+~u', '-', $text);
  $text = trim($text, '-');
  $text = strtolower($text);
  $text = preg_replace('~[^-a-z0-9]+~', '', $text);
  return $text ?: 'producto';
}

// Slugs canónicos fijos para evitar cambios por nombre
$slugMap = [
  1 => 'gastos-funerarios',
  2 => 'plan-privado-de-retiro',
  3 => 'gastos-funerarios-policias',
  6 => 'gastos-funerarios-taxistas',
  7 => 'kasu-maternidad',
  8 => 'kasu-futuro-18',
];
$slug      = $slugMap[$artId] ?? slugify($Reg['Nombre'] ?? ('producto-'.$artId));
$canonical = 'https://kasu.com.mx/productos/' . $slug;

$rawProdName = $Reg['Nombre'] ?? 'Producto';
$rawProdCat  = $Reg['Producto'] ?? 'Servicios';
$rawDesc     = $Reg['DesIni_Producto'] ?? ($Reg['Descripcion_Producto'] ?? $rawProdName);

$seoTitleRaw = 'KASU | ' . $rawProdName;
$seoDescRaw  = mb_substr(trim(strip_tags((string)$rawDesc)), 0, 160);
$seoKeywordsRaw = 'KASU, Servicio funerario, Servicio universitario, Servicio retiro, Proteccion familiar, ' . $rawProdCat;

if ($artId === 1) {
  $seoTitleRaw = 'Planes funerarios y servicios funerarios | KASU';
  $seoDescRaw  = 'Planes funerarios y servicios funerarios con Plan Servicios funerarios y planes de prevision. Cobertura nacional y red de funeraria en Mexico.';
  $seoKeywordsRaw = 'planes funerarios, servicios funerarios, plan de servicios funerarios, planes de prevision, funeraria, KASU';
} elseif ($artId === 3) {
  $seoTitleRaw = 'Planes funerarios para policias | KASU';
  $seoDescRaw  = 'Planes funerarios para policias y personal de seguridad publica. Servicios funerarios con pago unico y cobertura nacional.';
  $seoKeywordsRaw = 'planes funerarios para policias, servicios funerarios, seguridad publica, KASU';
} elseif ($artId === 6) {
  $seoTitleRaw = 'Planes funerarios para taxistas | KASU';
  $seoDescRaw  = 'Planes funerarios para taxistas y transportistas. Servicios funerarios con pago unico y cobertura nacional en Mexico.';
  $seoKeywordsRaw = 'planes funerarios para taxistas, servicios funerarios, transportistas, KASU';
} elseif ($artId === 7) {
  $seoTitleRaw = 'KASU Maternidad | Prevision de parto y cesarea';
  $seoDescRaw  = 'Prevision para maternidad con parto o cesarea en red KASU. Plan a plazos o pago unico, ligado a CURP.';
  $seoKeywordsRaw = 'kasu maternidad, plan maternidad, prevision maternidad, parto, cesarea, red kasu';
} elseif ($artId === 8) {
  $seoTitleRaw = 'KASU Futuro 18 | Universidad y emprendimiento';
  $seoDescRaw  = 'Plan para capital a los 18: universidad, emprendimiento o certificaciones. Aportaciones en fondo conservador.';
  $seoKeywordsRaw = 'kasu futuro 18, plan universidad, capital emprendimiento, ahorro 18 años, fondo kasu';
}

$seoTitle = htmlspecialchars($seoTitleRaw, ENT_QUOTES, 'UTF-8');
$seoDesc  = htmlspecialchars($seoDescRaw, ENT_QUOTES, 'UTF-8');
$seoImageRaw = $Reg['Imagen_Producto'] ?? 'https://kasu.com.mx/assets/images/kasu_logo.jpeg';
$seoImage = htmlspecialchars($seoImageRaw, ENT_QUOTES, 'UTF-8');
$prodCat  = htmlspecialchars($rawProdCat, ENT_QUOTES, 'UTF-8');
$seoKeywords = htmlspecialchars($seoKeywordsRaw, ENT_QUOTES, 'UTF-8');

/* ---------- 5.1) Ofertas para datos estructurados ---------- */
function format_price($value): string {
  return number_format((float)$value, 2, '.', '');
}

$paymentTermsText = 'Hasta 12 meses de credito';

$offerMap = [
  1 => ['type' => 'aggregate', 'low' => 3000,  'high' => 8500,   'count' => 6], // Gastos Funerarios
  2 => ['type' => 'aggregate', 'low' => 40000, 'high' => 100000, 'count' => 3], // Mi Retiro
  3 => ['type' => 'aggregate', 'low' => 6800,  'high' => 20500,  'count' => 6], // Oficiales de seguridad
  6 => ['type' => 'aggregate', 'low' => 6800,  'high' => 20500,  'count' => 6], // Taxistas
  7 => ['type' => 'aggregate', 'low' => 39900, 'high' => 44900,  'count' => 2], // Plan Bebe en Ruta
  8 => [
    'type' => 'monthly',
    'price' => 3000,
    'total' => 360000,
    'term_years' => 10,
  ], // Arranque Joven
];

$offerSchema = null;
if (isset($offerMap[$artId])) {
  $range = $offerMap[$artId];
  if ($range['type'] === 'monthly') {
    $priceMonthly = format_price($range['price']);
    $totalAmount = format_price($range['total']);
    $offerSchema = [
      '@type' => 'Offer',
      'price' => $priceMonthly,
      'priceCurrency' => 'MXN',
      'availability' => 'https://schema.org/InStock',
      'url' => $canonical,
      'paymentTerms' => $paymentTermsText,
      'description' => 'Pago mensual por ' . (int)$range['term_years'] . ' anos (total ' . $totalAmount . ' MXN).',
      'priceSpecification' => [
        '@type' => 'UnitPriceSpecification',
        'price' => $priceMonthly,
        'priceCurrency' => 'MXN',
        'billingDuration' => 1,
        'unitText' => 'MONTH',
        'name' => 'Pago mensual',
      ],
    ];
  } else {
    $offerSchema = [
      '@type' => 'AggregateOffer',
      'priceCurrency' => 'MXN',
      'lowPrice' => format_price($range['low']),
      'highPrice' => format_price($range['high']),
      'offerCount' => $range['count'],
      'availability' => 'https://schema.org/InStock',
      'url' => $canonical,
      'paymentTerms' => $paymentTermsText,
    ];
  }
}

$productSchema = [
  '@context' => 'https://schema.org',
  '@type' => 'Product',
  'name' => $rawProdName,
  'image' => $seoImageRaw,
  'url' => $canonical,
  'brand' => ['@type' => 'Brand', 'name' => 'KASU'],
  'category' => $rawProdCat,
  'description' => $seoDescRaw,
  'sku' => (string)$artId,
];
if ($offerSchema) {
  $productSchema['offers'] = $offerSchema;
}

/* ---------- 6) Descuento por tarjeta (una sola vez) ---------- */
$Desc = null;
if (!empty($_SESSION['tarjeta'])) {
  $Desc = $basicas->BuscarCampos($mysqli, "Descuento", "PostSociales", "Id", $_SESSION['tarjeta']);
}

/* ---------- 7) Auxiliares de vista ---------- */
$dat = $artId; // Id de producto para CTAs

//impresion de mensaje de alert
$qsMsg = $_GET['Msg'] ?? null;
if ($qsMsg !== null) {
    // Escapar seguro para JS y HTML
    $safeMsgJs  = json_encode($qsMsg, JSON_UNESCAPED_UNICODE);
    $safeMsgOut = htmlspecialchars($qsMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo "<script type='text/javascript'>alert($safeMsgJs);</script>";
    //echo "Mensaje recibido: {$safeMsgOut}<br>";
} 

/* ---------- 7) Auxiliares de vista ---------- */
$dat = $artId; // Id de producto para CTAs

// NUEVO: idp desde la URL y helper para anexarlo
$idp = filter_input(INPUT_GET, 'idp', FILTER_VALIDATE_INT) ?: 0;
function url_with_idp(string $url, int $idp): string {
  if ($idp <= 0) return $url;
  return $url . (str_contains($url, '?') ? '&' : '?') . 'idp=' . urlencode((string)$idp);
}

if(isset($_GET['idp'])){
  //Obtenemos el nombre del prospecto
  $NombreRegistro = $basicas->BuscarCampos($pros,'FullName','prospectos','Id',$_GET['idp']);
  //bajamos el nombre a letras mayusculas y minusculas
  $Nombre = $basicas->Minusculas_Nombre($NombreRegistro);
}else{
  $Nombre = '';
}

//Armamos los valores de el mensaje
if($Reg['Producto'] === 'Policias') {
  $Mensaje = 'Estoy%20interesado%20en%20informaci%C3%B3n%20de%20KASU%20para%20los%20oficiales%20de%20mi%20municipio';
}elseif($Reg['Producto'] === 'Retiro') {
  $Mensaje = 'Estoy%20interesado%20en%20informaci%C3%B3n%20de%20KASU%20para%20el%20retiro';
}else {
  $Mensaje = 'Estoy%20interesado%20en%20informaci%C3%B3n%20de%20KASU%20para%gastos%funerarios';
}

// URL de prospectos para el CTA principal
$prospectoMap = [
  1 => 'FUNERARIO',
  2 => 'RETIRO',
  3 => 'SEGURIDAD',
  6 => 'TRANSPORTE',
  7 => 'MATERNIDAD',
  8 => 'UNIVERSIDAD',
];
$prospectoProducto = $prospectoMap[$artId] ?? strtoupper((string)($Reg['Producto'] ?? ''));
$prospectoUrl = '/prospectos.php?producto=' . rawurlencode($prospectoProducto);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title><?= $seoTitle ?></title>
  <meta name="description" content="<?= $seoDesc ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="author" content="KASU Servicios a Futuro">
  <meta name="robots" content="index,follow,max-image-preview:large">
  <meta name="keywords" content="<?= $seoKeywords ?>">
  <meta name="theme-color" content="#F1F1FC">

  <!-- Canonical limpia -->
  <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">

  <!-- Open Graph -->
  <meta property="og:type" content="product">
  <meta property="og:site_name" content="KASU">
  <meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:title" content="<?= $seoTitle ?>">
  <meta property="og:description" content="<?= $seoDesc ?>">
  <meta property="og:image" content="<?= $seoImage ?>">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= $seoTitle ?>">
  <meta name="twitter:description" content="<?= $seoDesc ?>">
  <meta name="twitter:image" content="<?= $seoImage ?>">

  <!-- JSON-LD: WebPage + Product -->
  <script type="application/ld+json">
  {
    "@context":"https://schema.org",
    "@type":"WebPage",
    "url":"<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>",
    "name":"<?= $seoTitle ?>",
    "description":"<?= $seoDesc ?>",
    "isPartOf":{"@type":"WebSite","name":"KASU","url":"https://kasu.com.mx"}
  }
  </script>
  <script type="application/ld+json">
  <?= json_encode($productSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>

  <!-- Fuentes + Favicon -->
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" href="/assets/images/kasu_logo.jpeg">

  <!-- CSS/JS con rutas absolutas -->
  <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css?v=<?php echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/font-awesome.css?v=<?php echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/kasu-menu.css?v=<?php echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/index-home.css?v=<?php echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/productos.css?v=<?php echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/kasu-chat.css?v=<?php echo $VerCache;?>">
  <script src="/assets/js/js_productos.js" defer></script>
</head>
<body class="kasu-ui">

<section name="EmergentesServicio">
  <div class="modal fade" id="ModalGaleria" tabindex="-1" role="dialog" aria-labelledby="ModalGaleria" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="galleryModalLabel">Nuestras otras experiencias</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
        </div>
        <div class="modal-body">
          <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
              <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
              <?php
                $dir = __DIR__ . "/assets/images/cupones";
                $files = is_dir($dir)
                  ? array_values(array_filter(scandir($dir), function($f) use ($dir){
                      return is_file($dir . "/" . $f) && preg_match('/\.jpg$/i', $f);
                    }))
                  : [];
                for ($i = 1; $i < count($files); $i++) {
                  echo '<li data-target="#carouselExampleIndicators" data-slide-to="' . $i . '"></li>';
                }
              ?>
            </ol>
            <div class="carousel-inner">
              <?php
                $first = true;
                foreach (glob(__DIR__ . "/assets/images/cupones/*.jpg") as $absPath) {
                  $relPath = str_replace(__DIR__, '', $absPath); // -> /assets/images/cupones/...
                  echo '<div class="carousel-item' . ($first ? ' active' : '') . '">
                          <img class="d-block w-100" src="' . htmlspecialchars($relPath, ENT_QUOTES) . '" alt="cupón">
                        </div>';
                  $first = false;
                }
              ?>
            </div>
            <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="sr-only">Anterior</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span><span class="sr-only">Siguiente</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/html/MenuPrincipal.php';?>

<section class="product-hero" id="features">
  <div class="container">
    <div class="row product-hero-grid">
      <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
        <img src="<?= htmlspecialchars($Reg['Imagen_Producto'] ?? '', ENT_QUOTES) ?>"
             class="product-hero-image img-fluid d-block mx-auto"
             alt="<?= htmlspecialchars($Reg['Nombre'] ?? 'Producto KASU', ENT_QUOTES) ?>">
      </div>
      <div class="col-lg-1"></div>
      <div class="col-lg-6 col-md-12 col-sm-12 align-self-center product-hero-content">
        <div class="left-heading product-hero-heading">
          <?php
          if (!empty($Nombre)) {
              echo '<h1 class="product-hero-title"><strong>' . htmlspecialchars($Nombre, ENT_QUOTES, 'UTF-8') . '</strong></h1>';
          } else {
              $n = htmlspecialchars($Reg['Nombre'] ?? 'Producto', ENT_QUOTES, 'UTF-8');
              echo '<h1 class="product-hero-title"><strong>' . $n . '</strong></h1>';
          }
          ?>
        </div>
        <div class="left-text product-hero-text">

          <?php
            //Imprimimos la descripcion de el producto
            echo $Reg['DesIni_Producto'] ?? '';
          ?>
          <div class="product-hero-actions">
          <?php
            //Imprimimos el boton de Comprar
            if (!empty($Desc)) {
              $imgCupon = $basicas->BuscarCampos($mysqli, "Img", "PostSociales", "Id", $_SESSION["tarjeta"]);
              echo '<img class="img-thumbnail product-coupon" src="/assets/images/cupones/' . htmlspecialchars($imgCupon, ENT_QUOTES) . '" alt="Cupón de descuento">';
              echo '<a href="' . htmlspecialchars($prospectoUrl, ENT_QUOTES) . '" class="kasu-btn product-btn"><strong>Descuento hoy $ ' . number_format((float)$Desc, 2) . '</strong></a>';
            } elseif (!empty($preference)) {
              echo '<a href="' . htmlspecialchars($prospectoUrl, ENT_QUOTES) . '" class="kasu-btn product-btn"><strong>Comprar</strong></a>';
            } else {
              echo '<a href="' . htmlspecialchars($prospectoUrl, ENT_QUOTES) . '" class="kasu-btn product-btn"><strong>Comprar Ahora</strong></a>';
            }
          ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="product-pricing" id="Comprar">
  <div class="container">
    <div class="row">
      <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
        <div class="pricing-item product-price-card">
          <div class="pricing-body product-price-body">
            <i><img class="product-price-icon" src="/assets/images/icon/credit-card.png" alt="Producto Kasu"></i>
            <h2 class="product-price-title">¿Cuánto cuesta el producto?</h2>
            <div class="product-price-table"><?= $Reg['Precios_Producto'] ?? '' ?></div>

            <?php
              if (!empty($Desc)) {
                $imgCupon = $basicas->BuscarCampos($mysqli, "Img", "PostSociales", "Id", $_SESSION["tarjeta"]);
                echo '<img class="img-thumbnail product-coupon" src="/assets/images/cupones/' . htmlspecialchars($imgCupon, ENT_QUOTES) . '" alt="Cupón de descuento">';
                $buyUrl = url_with_idp('/registro.php?pro=' . $dat, $idp);
                echo '<a href="' . htmlspecialchars($buyUrl, ENT_QUOTES) . '" class="kasu-btn product-btn"><strong>Descuento hoy $ ' . number_format((float)$Desc, 2) . '</strong></a>';
              } elseif (!empty($preference)) {
                echo '<a href="' . htmlspecialchars($preference, ENT_QUOTES) . '" class="kasu-btn product-btn"><strong>Comprar</strong></a>';
              } else {
                $buyUrl = url_with_idp('/registro.php?pro=' . $dat, $idp);
                echo '<a href="' . htmlspecialchars($buyUrl, ENT_QUOTES) . '" class="kasu-btn product-btn"><strong>Comprar Ahora</strong></a>';
              }
            ?>

          </div>
        </div>
      </div>
      <div class="col-lg-1"></div>
      <div class="col-lg-6 col-md-12 col-sm-12 align-self-center">
        <div class="product-includes-card">
          <div class="product-includes-body">
            <div class="product-includes-header">
              <i>
                <img class="product-includes-icon" src="<?= htmlspecialchars($Reg['Image_Desc'] ?? '', ENT_QUOTES) ?>" alt="Producto Kasu">
              </i>
              <h2 class="product-includes-title">
                <strong>¿Qué incluye, el servicio <?= htmlspecialchars($Reg['Nombre'] ?? 'este producto', ENT_QUOTES) ?>?</strong>
              </h2>
            </div>
            <div class="product-includes-content">
              <div class="row justify-content-center text-center">
                <?= $Reg['Descripcion_Producto'] ?? '' ?>
              </div>
            </div>
            <div class="row justify-content-center" id="video">
              <?php if ($artId !== 2): ?>
                 <div class="col-lg-6 col-md-10 text-center">
                  <a href="#" class="kasu-btn product-btn product-strip-btn"><strong>Agencias Autorizadas</strong></a>
                </div>
              <?php else: ?>
                <div class="col-lg-6 col-md-10 text-center">
                  <a href="#" class="kasu-btn product-btn product-strip-btn"><strong>Fondos de Retiro</strong></a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="product-faq" id="pricing-plans">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="product-faq-content">
          <?= $Reg['Tab_Producto'] ?? '' ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="product-cta" id="contacto">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 text-center">
        <h2 class="product-cta-title">¿Aún no te convencemos?</h2>
        <p class="product-cta-text">Brindamos un servicio para proteger y ayudar a tu familia. Cada producto es una oportunidad para cuidar lo que más amas.</p>
        <div class="product-cta-actions">
        <?php
          $uppercaseText     = strtoupper($Reg['Producto'] ?? 'PRODUCTO');
          $base64EncodedText = base64_encode($uppercaseText);
          //Imprimimos el boton
          $buyUrl = url_with_idp('/registro.php?pro=' . $dat, $idp);
          //Construimos el numero de telefono
          $dest = preg_replace('/\D+/', '', $tel ?? '');
          //Imprimimos el boton
          echo '<a href="https://wa.me/'.$dest.'?text='.$Mensaje.'" class="kasu-btn product-btn"><strong>Contactar un Agente</strong></a>';
        ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="Productos-Index"> 
  <div class="container" itemscope itemtype="https://schema.org/CollectionPage">
    <div class="product-list-heading">
      <h2 class="product-list-title">Protege a quien amas, <strong>de la mano de KASU</strong></h2>
    </div>
    <!-- Productos -->
    <?php require_once __DIR__ . '/html/Section_Productos.php'; ?>
  </div>
</section>

<footer class="site-footer"><?php require_once __DIR__ . '/html/footer.php'; ?></footer>

<!-- JS con rutas absolutas -->
<script src="/assets/js/jquery-2.1.0.min.js"></script>
<script src="/assets/js/bootstrap.min.js"></script>
<script src="/assets/js/scrollreveal.min.js"></script>
<script src="/assets/js/waypoints.min.js"></script>
<script src="/assets/js/imgfix.min.js"></script>
<script src="/assets/js/custom.js"></script>
<script async src="https://d335luupugsy2.cloudfront.net/js/loader-scripts/28dd2782-ee7d-4b25-82b1-f5993b27764a-loader.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var fabButton = document.getElementById('kasu-fab');
  var fabPanel = document.getElementById('kasu-fab-panel');
  var chatOpen = document.getElementById('kasu-chat-open');
  var chatOverlay = document.getElementById('kasu-chat-overlay');
  var chatClose = document.getElementById('kasu-chat-close');
  var chatForm = document.getElementById('kasu-chat-form');
  var chatInput = document.getElementById('kasu-chat-input');
  var chatBody = document.getElementById('kasu-chat-messages');

  var hideFabPanel = function () {
    if (fabPanel) {
      fabPanel.setAttribute('hidden', 'hidden');
    }
    if (fabButton) {
      fabButton.setAttribute('aria-expanded', 'false');
    }
  };

  var showFabPanel = function () {
    if (fabPanel) {
      fabPanel.removeAttribute('hidden');
    }
    if (fabButton) {
      fabButton.setAttribute('aria-expanded', 'true');
    }
  };

  var openChat = function () {
    if (!chatOverlay) return;
    chatOverlay.removeAttribute('hidden');
    if (chatOpen) chatOpen.setAttribute('aria-expanded', 'true');
    if (chatInput) chatInput.focus();
    hideFabPanel();
  };

  var closeChat = function () {
    if (!chatOverlay) return;
    chatOverlay.setAttribute('hidden', 'hidden');
    if (chatOpen) chatOpen.setAttribute('aria-expanded', 'false');
    showFabPanel();
  };

  if (fabButton && fabPanel) {
    fabButton.addEventListener('click', function () {
      var chatOpenNow = chatOverlay && !chatOverlay.hasAttribute('hidden');
      if (chatOpenNow) {
        closeChat();
        showFabPanel();
        return;
      }

      var expanded = fabButton.getAttribute('aria-expanded') === 'true';
      fabButton.setAttribute('aria-expanded', String(!expanded));
      if (expanded) {
        fabPanel.setAttribute('hidden', 'hidden');
      } else {
        fabPanel.removeAttribute('hidden');
      }
    });
  }

  if (chatOpen) {
    chatOpen.addEventListener('click', function () {
      var isOpen = chatOverlay && !chatOverlay.hasAttribute('hidden');
      if (isOpen) {
        closeChat();
      } else {
        openChat();
      }
    });
  }

  if (chatClose) {
    chatClose.addEventListener('click', closeChat);
  }

  if (chatForm && chatInput && chatBody) {
    var endpoint = '/eia/Vista-360/kasu_chat_publico.php';
    var typingNode = null;

    var appendUserMessage = function (message) {
      var wrapper = document.createElement('div');
      wrapper.className = 'kasu-chat-message kasu-chat-message--user';
      var bubble = document.createElement('div');
      bubble.className = 'kasu-chat-bubble';
      bubble.textContent = message;
      wrapper.appendChild(bubble);
      chatBody.appendChild(wrapper);
    };

    var appendBotMessage = function (html) {
      var wrapper = document.createElement('div');
      wrapper.className = 'kasu-chat-message kasu-chat-message--bot';
      var avatar = document.createElement('img');
      avatar.src = '/assets/images/flor_redonda.svg';
      avatar.alt = 'KASU';
      avatar.className = 'kasu-chat-avatar';
      avatar.width = 26;
      avatar.height = 26;
      avatar.loading = 'lazy';
      avatar.decoding = 'async';
      var bubble = document.createElement('div');
      bubble.className = 'kasu-chat-bubble';
      bubble.innerHTML = html;
      wrapper.appendChild(avatar);
      wrapper.appendChild(bubble);
      chatBody.appendChild(wrapper);
    };

    var showTyping = function () {
      if (typingNode) return;
      typingNode = document.createElement('div');
      typingNode.className = 'kasu-chat-message kasu-chat-message--bot';
      var avatar = document.createElement('img');
      avatar.src = '/assets/images/flor_redonda.svg';
      avatar.alt = 'KASU';
      avatar.className = 'kasu-chat-avatar';
      avatar.width = 26;
      avatar.height = 26;
      avatar.loading = 'lazy';
      avatar.decoding = 'async';
      var bubble = document.createElement('div');
      bubble.className = 'kasu-chat-bubble';
      bubble.textContent = 'Escribiendo...';
      typingNode.appendChild(avatar);
      typingNode.appendChild(bubble);
      chatBody.appendChild(typingNode);
    };

    var hideTyping = function () {
      if (!typingNode) return;
      typingNode.remove();
      typingNode = null;
    };

    chatForm.addEventListener('submit', function (event) {
      event.preventDefault();
      var message = chatInput.value.trim();
      if (!message) return;

      appendUserMessage(message);
      chatBody.scrollTop = chatBody.scrollHeight;
      chatInput.value = '';
      chatInput.disabled = true;
      showTyping();

      var chatToken = localStorage.getItem('kasu_chat_token');
      if (!chatToken) {
        if (window.crypto && window.crypto.randomUUID) {
          chatToken = window.crypto.randomUUID();
        } else {
          chatToken = 'kasu_' + Date.now() + '_' + Math.random().toString(16).slice(2);
        }
        localStorage.setItem('kasu_chat_token', chatToken);
      }

      fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          mensaje: message,
          source: window.location.pathname,
          chat_token: chatToken
        })
      })
      .then(function (resp) { return resp.json(); })
      .then(function (data) {
        hideTyping();
        chatInput.disabled = false;
        if (data && data.chat_token) {
          localStorage.setItem('kasu_chat_token', data.chat_token);
        }
        if (data && data.ok && data.html) {
          appendBotMessage(data.html);
        } else {
          appendBotMessage('No pude procesar tu solicitud. Intenta de nuevo.');
        }
        chatBody.scrollTop = chatBody.scrollHeight;
      })
      .catch(function () {
        hideTyping();
        chatInput.disabled = false;
        appendBotMessage('No pude conectar con el chat. Intenta mas tarde.');
        chatBody.scrollTop = chatBody.scrollHeight;
      });
    });
  }
});
</script>

<div class="kasu-fab-wrap" aria-live="polite">
  <div class="kasu-fab-panel" id="kasu-fab-panel" hidden>
    <a href="tel:<?php echo isset($tel) ? htmlspecialchars($tel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : ''; ?>" class="kasu-fab-action kasu-fab-action--call" aria-label="Llamar a KASU">
      <span class="kasu-fab-icon" aria-hidden="true">📞</span>
      Llamar a KASU
    </a>
    <a href="https://wa.me/<?php echo preg_replace('/\\D/', '', $tel ?? ''); ?>?text=Hola,%20requiero%20atención%20inmediata%20de%20KASU" class="kasu-fab-action kasu-fab-action--whats" target="_blank" rel="noopener" aria-label="Enviar WhatsApp a KASU">
      <span class="kasu-fab-icon" aria-hidden="true">💬</span>
      Enviar WhatsApp
    </a>
    <button type="button" class="kasu-fab-action kasu-fab-action--chat" id="kasu-chat-open" aria-expanded="false" aria-controls="kasu-chat-overlay">
      <span class="kasu-fab-icon" aria-hidden="true">🗨️</span>
      Hablar con asistencia
    </button>
  </div>
  <button type="button" class="kasu-fab" id="kasu-fab" aria-expanded="false" aria-controls="kasu-fab-panel">
    <img src="/assets/images/flor_redonda.svg" alt="" width="34" height="34" loading="lazy" decoding="async">
    Atención al cliente
  </button>
</div>

<section class="kasu-chat-overlay" id="kasu-chat-overlay" aria-live="polite" hidden>
  <div class="kasu-chat-panel">
    <header class="kasu-chat-panel__header">
      <div class="kasu-chat-panel__brand">
        <img src="/assets/images/flor_redonda.svg" alt="KASU" width="28" height="28" loading="lazy" decoding="async">
        <div>
          <p class="kasu-chat-panel__title">Chat KASU</p>
          <span class="kasu-chat-panel__status">En linea</span>
        </div>
      </div>
      <div class="kasu-chat-panel__actions">
        <span class="kasu-chat-panel__pill">Vista 360</span>
        <button type="button" class="kasu-chat-panel__close" id="kasu-chat-close" aria-label="Cerrar chat">×</button>
      </div>
    </header>

    <div class="kasu-chat-panel__messages" id="kasu-chat-messages">
      <div class="kasu-chat-message kasu-chat-message--bot">
        <img src="/assets/images/flor_redonda.svg" alt="KASU" class="kasu-chat-avatar" width="26" height="26" loading="lazy" decoding="async">
        <div class="kasu-chat-bubble">
          Hola, soy la asistente virtual de KASU. en que puedo ayudarte hoy?
        </div>
      </div>
    </div>

    <form class="kasu-chat-panel__form" id="kasu-chat-form" autocomplete="off">
      <input type="text" id="kasu-chat-input" name="kasu-chat-input" placeholder="Escribe tu mensaje">
      <button type="submit">Enviar</button>
    </form>
  </div>
</section>
</body>
</html>
