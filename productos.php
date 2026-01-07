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
// Se establecen el número de contacto
$tel = '7208177632';

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
$slug      = slugify($Reg['Nombre'] ?? ('producto-'.$artId));
$canonical = 'https://kasu.com.mx/productos/' . $slug;

$seoTitle = 'KASU | ' . htmlspecialchars($Reg['Nombre'] ?? 'Producto', ENT_QUOTES, 'UTF-8');
$rawDesc  = $Reg['DesIni_Producto'] ?? ($Reg['Descripcion_Producto'] ?? ($Reg['Nombre'] ?? ''));
$seoDesc  = htmlspecialchars(mb_substr(trim(strip_tags((string)$rawDesc)), 0, 160), ENT_QUOTES, 'UTF-8');
$seoImage = htmlspecialchars($Reg['Imagen_Producto'] ?? 'https://kasu.com.mx/assets/images/kasu_logo.jpeg', ENT_QUOTES, 'UTF-8');
$prodCat  = htmlspecialchars($Reg['Producto'] ?? 'Servicios', ENT_QUOTES, 'UTF-8');

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
  <meta name="keywords" content="KASU, Servicio funerario, Servicio universitario, Servicio retiro, Protección familiar, <?= $prodCat ?>">
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
  {
    "@context":"https://schema.org",
    "@type":"Product",
    "name":"<?= htmlspecialchars($Reg['Nombre'] ?? 'Producto', ENT_QUOTES) ?>",
    "image":"<?= $seoImage ?>",
    "url":"<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>",
    "brand":{"@type":"Brand","name":"KASU"},
    "category":"<?= $prodCat ?>",
    "description":"<?= $seoDesc ?>"
  }
  </script>

  <!-- Fuentes + Favicon -->
  <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
  <link rel="icon" href="/assets/images/kasu_logo.jpeg">

  <!-- CSS/JS con rutas absolutas -->
  <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/css/font-awesome.css">
  <link rel="stylesheet" href="/assets/css/kasu-ui.css">
  <link rel="stylesheet" href="/assets/css/productos.css?v=2">
  <script src="/assets/js/js_productos.js" defer></script>
</head>
<body>

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

<section class="section padding-top-140 padding-bottom-0" id="features">
  <div class="container">
    <div class="row">
      <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
        <img src="<?= htmlspecialchars($Reg['Imagen_Producto'] ?? '', ENT_QUOTES) ?>"
             class="rounded img-fluid d-block mx-auto"
             alt="<?= htmlspecialchars($Reg['Nombre'] ?? 'Producto KASU', ENT_QUOTES) ?>">
      </div>
      <div class="col-lg-1"></div>
      <div class="col-lg-6 col-md-12 col-sm-12 align-self-center mobile-top-fix">
        <div class="left-heading">
          <?php
          if (!empty($Nombre)) {
              echo '<h1 class="section-title"><strong>' . htmlspecialchars($Nombre, ENT_QUOTES, 'UTF-8') . '</strong></h1>';
          } else {
              $n = htmlspecialchars($Reg['Nombre'] ?? 'Producto', ENT_QUOTES, 'UTF-8');
              echo '<h1 class="section-title"><strong>' . $n . '</strong></h1>';
          }
          ?>
        </div>
        <div class="left-text">

          <?php
            //Imprimimos la descripcion de el producto
            echo $Reg['DesIni_Producto'] ?? '';
            //Imprimimos el bototn de Comprar
            if (!empty($Desc)) {
              $imgCupon = $basicas->BuscarCampos($mysqli, "Img", "PostSociales", "Id", $_SESSION["tarjeta"]);
              echo '<br><img class="img-thumbnail" src="/assets/images/cupones/' . htmlspecialchars($imgCupon, ENT_QUOTES) . '" style="width: 15em;"><br>';
              $buyUrl = url_with_idp('/registro.php?pro=' . $dat, $idp);
              echo '<a href="' . htmlspecialchars($buyUrl, ENT_QUOTES) . '" class="main-button-slider-pol"><strong>Descuento hoy $ ' . number_format((float)$Desc, 2) . '</strong></a><br>';
            } elseif (!empty($preference)) {
              // Enlace directo a Mercado Pago: no se anexa idp
              echo '<br><a href="' . htmlspecialchars($preference, ENT_QUOTES) . '" class="main-button-slider-pol"><strong>Comprar</strong></a><br><br>';
            } elseif($Reg['Producto'] !== 'Funerario') {
                $buyUrl = url_with_idp('/registro.php?pro=' . $dat, $idp);
                //Construimos el numero de telefono
                $dest = preg_replace('/\D+/', '', $tel ?? '');
                //Imprimimos el boton
                echo '<br>
                        <a href="https://wa.me/'.$dest.'?text='.$Mensaje.'" class="main-button-slider-pol">
                          <strong>
                            Contactar un Agente
                          </strong>
                        </a>
                      <br><br>';
              } else {
                $buyUrl = url_with_idp('/registro.php?pro=' . $dat, $idp);
                echo '<br><a href="' . htmlspecialchars($buyUrl, ENT_QUOTES) . '" class="main-button-slider-pol"><strong>Comprar Ahora</strong></a><br><br>';
              }
          ?>
        </div>
      </div>
    </div>
  </div>
  <br>
</section>

<section class="mini" id="work-process">
  <div class="mini-content">
    <div class="container">
      <div class="row" id="video">
        <?php if ($artId !== 2): ?>
          <div class="offset-xl-3 col-xl-6 offset-lg-2 col-lg-8 col-md-12 col-sm-12">
            <br><a href="#" class="main-button-slider-pol"><strong>Agencias Autorizadas</strong></a>
          </div>
        <?php else: ?>
          <div class="offset-xl-3 col-xl-6 offset-lg-2 col-lg-8 col-md-12 col-sm-12">
            <br><a href="#" class="main-button-slider-pol"><strong>Fondos de Retiro </strong></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<section class="section padding-bottom-100" id="Comprar">
  <div class="container">
    <div class="row">
            <div class="col-lg-5 col-md-12 col-sm-12 align-self-center mobile-bottom-fix-big" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
        <div class="pricing-item">
          <div class="pricing-body">
            <i><img style="height: 80px;" src="/assets/images/icon/credit-card.png" alt="Producto Kasu"></i>
            <br><br><strong>¿Cuánto cuesta el producto?</strong><br><br>
            <div style="padding: 15px;"><?= $Reg['Precios_Producto'] ?? '' ?></div>

            <?php
              if (!empty($Desc)) {
                $imgCupon = $basicas->BuscarCampos($mysqli, "Img", "PostSociales", "Id", $_SESSION["tarjeta"]);
                echo '<br><img class="img-thumbnail" src="/assets/images/cupones/' . htmlspecialchars($imgCupon, ENT_QUOTES) . '" style="width: 15em;"><br>';
                $buyUrl = url_with_idp('/registro.php?pro=' . $dat, $idp);
                echo '<a href="' . htmlspecialchars($buyUrl, ENT_QUOTES) . '" class="main-button-slider-pol"><strong>Descuento hoy $ ' . number_format((float)$Desc, 2) . '</strong></a><br>';
              } elseif (!empty($preference)) {
                echo '<br><a href="' . htmlspecialchars($preference, ENT_QUOTES) . '" class="main-button-slider-pol"><strong>Comprar</strong></a><br><br>';
              } elseif($Reg['Producto'] !== 'Funerario') {
                $buyUrl = url_with_idp('/registro.php?pro=' . $dat, $idp);
                //Construimos el numero de telefono
                $dest = preg_replace('/\D+/', '', $tel ?? '');
                //Imprimimos el boton
                echo '<br>
                        <a href="https://wa.me/'.$dest.'?text='.$Mensaje.'" class="main-button-slider-pol">
                          <strong>
                            Contactar un Agente
                          </strong>
                        </a>
                      <br><br>';
              } else {
                $buyUrl = url_with_idp('/registro.php?pro=' . $dat, $idp);
                echo '<br><a href="' . htmlspecialchars($buyUrl, ENT_QUOTES) . '" class="main-button-slider-pol"><strong>Contratar a un agente</strong></a><br><br>';
              }
            ?>

          </div>
        </div>
      </div>
      <div class="col-lg-1"></div>
      <div class="col-lg-6 col-md-12 col-sm-12 align-self-center mobile-bottom-fix">
        <!-- Caja con borde, margen, padding y sombra -->
        <div class="border rounded shadow bg-white mx-3 my-4">
          <div class="p-4 p-md-5">
            <div class="col-12 mb-3 text-center">
              <i>
                <img style="height: 80px;" src="<?= htmlspecialchars($Reg['Image_Desc'] ?? '', ENT_QUOTES) ?>" alt="Producto Kasu">
              </i>
              <br><br>
              <h2 class="section-title">
                <strong>¿Qué incluye, el servicio <?= htmlspecialchars($Reg['Nombre'] ?? 'este producto', ENT_QUOTES) ?>?</strong>
              </h2>
            </div>
            <hr>
            <div class="col-12">
              <!-- Icons -->
              <div class="row justify-content-center text-center">
                <?= $Reg['Descripcion_Producto'] ?? '' ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section colored" id="pricing-plans">
  <div class="container">
    <div class="row">
      <br><br>
      <div class="dudasfun"><?= $Reg['Tab_Producto'] ?? '' ?></div>
      <br>
    </div>
  </div>
</section>

<section class="section colored" id="contacto">
  <div class="container">
    <div class="col-md-4 col-md-12 col-sm-12 align-self-center">
      <div class="center-heading">
        <h2 class="section-title">¿Aún no te convencemos?</h2>
        <p>Brindamos un servicio para proteger y ayudar a tu familia. Cada producto es una oportunidad para cuidar lo que más amas.</p>
      </div>
      <div class="center-body"><br>
        <?php
          $uppercaseText     = strtoupper($Reg['Producto'] ?? 'PRODUCTO');
          $base64EncodedText = base64_encode($uppercaseText);
          //Imprimimos el boton
          $buyUrl = url_with_idp('/registro.php?pro=' . $dat, $idp);
          //Construimos el numero de telefono
          $dest = preg_replace('/\D+/', '', $tel ?? '');
          //Imprimimos el boton
          echo '<br>
                <a href="https://wa.me/'.$dest.'?text='.$Mensaje.'" class="main-button-slider-pol">
                    <strong>
                      Contactar un Agente
                    </strong>
                  </a>
                <br><br>';
        ?>
      </div>
    </div>
  </div>
</section>

<section class="section colored padding-top-70"> 
  <div class="container" itemscope itemtype="https://schema.org/CollectionPage">
    <div class="col-lg-12">
      <div class="center-heading">
        <h2 class="section-title" style="color: #333333;">Protege a quien amas, <strong> de la mano de KASU</strong></h2>
      </div>
    </div>
    <!-- Productos -->
    <?php require_once __DIR__ . '/html/Section_Productos.php'; ?>
  </div>
</section>

<footer><?php require_once __DIR__ . '/html/footer.php'; ?></footer>

<!-- JS con rutas absolutas -->
<script src="/assets/js/jquery-2.1.0.min.js"></script>
<script src="/assets/js/bootstrap.min.js"></script>
<script src="/assets/js/scrollreveal.min.js"></script>
<script src="/assets/js/waypoints.min.js"></script>
<script src="/assets/js/imgfix.min.js"></script>
<script src="/assets/js/custom.js"></script>
<script async src="https://d335luupugsy2.cloudfront.net/js/loader-scripts/28dd2782-ee7d-4b25-82b1-f5993b27764a-loader.js"></script>
</body>
</html>