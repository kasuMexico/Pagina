<?php
/**
 * Qué hace: Formulario de registro y APIs auxiliares (lookup CURP y cotización). Envía a /eia/Registrar_Venta.php.
 * Fecha: 03/11/2025
 * Revisado por: JCCM
 * Archivo registro.php
 */

// registro.php
session_start();
require_once __DIR__ . '/eia/analytics_bootstrap.php';
require_once __DIR__ . '/eia/librerias.php';
date_default_timezone_set('America/Mexico_City');
header_remove('X-Powered-By');

// Content Security Policy
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://www.googletagmanager.com; style-src 'self' 'unsafe-inline' https://stackpath.bootstrapcdn.com; img-src 'self' data:; frame-src https://www.googletagmanager.com; connect-src 'self'");

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
  http_response_code(500);
  exit('Error de conexión.');
}

/* ===== Procesador POST (ruta) ===== */
$archivoRegistro = '/eia/Registrar_Venta.php';

/* ===== Utilidades ===== */
function is_curp(string $s): bool {
  return (bool)preg_match('/^[A-Z]{4}\d{6}[HM][A-Z]{2}[A-Z]{3}[A-Z0-9]{2}$/', $s);
}

function rate_limit(string $key, int $maxRequests = 30, int $windowSeconds = 60): bool {
  $now = time();
  $bucket = $_SESSION['rate_' . $key] ?? ['count' => 0, 'reset' => $now + $windowSeconds];
  if ($now > $bucket['reset']) {
    $bucket = ['count' => 1, 'reset' => $now + $windowSeconds];
  } else {
    $bucket['count']++;
  }
  $_SESSION['rate_' . $key] = $bucket;
  return $bucket['count'] <= $maxRequests;
}

function obtener_tarjeta_descuento($mysqli): float {
  if (empty($_SESSION['tarjeta'])) return 0.0;
  if ($st = $mysqli->prepare("SELECT Descuento FROM PostSociales
                              WHERE Id=? AND Status=1
                                AND (Validez_Fin IS NULL OR Validez_Fin='' OR Validez_Fin >= CURDATE())
                              LIMIT 1")) {
    $st->bind_param('i', $_SESSION['tarjeta']);
    $st->execute();
    $rs = $st->get_result();
    if ($row = $rs->fetch_assoc()) {
      $descuento = (float)$row['Descuento'];
      $st->close();
      return $descuento;
    }
    $st->close();
  }
  return 0.0;
}

function is_cross_origin(): bool {
  $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
  if ($origin === '') {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer === '') return false;
    $origin = $referer;
  }
  $host = parse_url($origin, PHP_URL_HOST);
  $expected = $_SERVER['HTTP_HOST'] ?? '';
  return $host !== $expected && $host !== '';
}

/* ===== API: lookup por CURP ===== */
if (isset($_GET['action']) && $_GET['action'] === 'curp_lookup') {
  header('Content-Type: application/json; charset=utf-8');
  if (!isset($seguridad) || !is_object($seguridad)) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Servicio no disponible'], JSON_UNESCAPED_UNICODE);
    exit;
  }
  if (!rate_limit('curp_lookup', 30, 60)) {
    http_response_code(429);
    echo json_encode(['ok'=>false,'error'=>'Demasiadas peticiones. Intenta de nuevo en un minuto.'], JSON_UNESCAPED_UNICODE);
    exit;
  }
  if (is_cross_origin()) {
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'Acceso no permitido'], JSON_UNESCAPED_UNICODE);
    exit;
  }
  $qcurp = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string)($_GET['curp'] ?? '')));
  if (strlen($qcurp) !== 18 || !is_curp($qcurp)) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'CURP inválida'], JSON_UNESCAPED_UNICODE);
    exit;
  }
  try {
    $data = $seguridad->peticion_get($qcurp);
    $resp = [
      'Nombre'    => (string)($data['Nombre']    ?? $data['nombre']  ?? ''),
      'ApPaterno' => (string)($data['ApPaterno'] ?? $data['paterno'] ?? ''),
      'ApMaterno' => (string)($data['ApMaterno'] ?? $data['materno'] ?? '')
    ];
    echo json_encode(['ok'=>true,'data'=>$resp], JSON_UNESCAPED_UNICODE);
  } catch (Throwable $e) {
    error_log('registro.php curp_lookup: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Error interno'], JSON_UNESCAPED_UNICODE);
  }
  exit;
}

/* ===== API: cotización por CURP + Producto (sin enviar) ===== */
if (isset($_GET['action']) && $_GET['action'] === 'price_quote') {
  header('Content-Type: application/json; charset=utf-8');

  $curp = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string)($_GET['curp'] ?? '')));
  $producto = trim((string)($_GET['producto'] ?? ''));
  $prodAllow = ['Funerario','Retiro','Seguridad','Transporte','Maternidad','Universidad'];
  if (strlen($curp)!==18 || !is_curp($curp) || !in_array($producto, $prodAllow, true)) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Parámetros inválidos'], JSON_UNESCAPED_UNICODE);
    exit;
  }

  if (!rate_limit('price_quote', 20, 60)) {
    http_response_code(429);
    echo json_encode(['ok'=>false,'error'=>'Demasiadas peticiones. Intenta de nuevo en un minuto.'], JSON_UNESCAPED_UNICODE);
    exit;
  }

  if (is_cross_origin()) {
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'Acceso no permitido'], JSON_UNESCAPED_UNICODE);
    exit;
  }

  try {
    if (!isset($basicas) || !is_object($basicas)) {
      http_response_code(500);
      echo json_encode(['ok'=>false,'error'=>'Servicio no disponible'], JSON_UNESCAPED_UNICODE);
      exit;
    }
    $edad = (int)$basicas->ObtenerEdad($curp);
    if ($producto === 'Retiro') {
      $prodTarifa = 'Retiro';
    } elseif ($producto === 'Seguridad') {
      $prodTarifa = $basicas->ProdPli($edad);
    } elseif ($producto === 'Transporte') {
      $prodTarifa = $basicas->ProdTrans($edad);
    } else {
      $prodTarifa = ($producto === 'Maternidad' || $producto === 'Universidad')
        ? $producto
        : $basicas->ProdFune($edad);
    }
    $costo = (float)$basicas->BuscarCampos($mysqli, 'Costo', 'Productos', 'Producto', $prodTarifa);
    $tasaAnual = (float)$basicas->BuscarCampos($mysqli, 'TasaAnual', 'Productos', 'Producto', $prodTarifa);
    $descuento = obtener_tarjeta_descuento($mysqli);

    if ($producto === 'Retiro') {
      $pago = ['CONTADO' => 'CONTADO'];
      $plazos = [];
    } elseif ($producto === 'Maternidad') {
      $pago = ['CREDITO' => 'CRÉDITO', 'CONTADO' => 'CONTADO'];
      $plazos = ['24'=>'24 Meses','36'=>'36 Meses'];
    } elseif ($producto === 'Universidad') {
      $pago = ['CREDITO' => 'CRÉDITO', 'CONTADO' => 'CONTADO'];
      $plazos = ['120'=>'120 Meses'];
    } else {
      $pago = ['CREDITO' => 'CRÉDITO', 'CONTADO' => 'CONTADO'];
      $plazos = ['3'=>'3 Meses','6'=>'6 Meses','9'=>'9 Meses'];
    }

    echo json_encode([
      'ok' => true,
      'data' => [
        'costo'      => $costo,
        'prodTarifa' => $prodTarifa,
        'edad'       => $edad,
        'pago'       => $pago,
        'plazos'     => $plazos,
        'tasaAnual'  => $tasaAnual,
        'descuento'  => $descuento
      ]
    ], JSON_UNESCAPED_UNICODE);
  } catch (Throwable $e) {
    error_log('registro.php price_quote: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Error interno'], JSON_UNESCAPED_UNICODE);
  }
  exit;
}

/* ===== CSRF ===== */
if (empty($_SESSION['csrf_reg'])) {
  try {
    $_SESSION['csrf_reg'] = bin2hex(random_bytes(32));
  } catch (\Random\RandomException $e) {
    error_log('registro.php CSRF random_bytes: ' . $e->getMessage());
    $_SESSION['csrf_reg'] = bin2hex(openssl_random_pseudo_bytes(32));
  }
}
$csrf = $_SESSION['csrf_reg'];

/* ===== Preselección por ?pro= ===== */
$pro      = filter_input(INPUT_GET, 'pro', FILTER_VALIDATE_INT) ?: 0;
$proMap   = [1 => 'Funerario', 2 => 'Retiro', 3 => 'Seguridad', 6 => 'Transporte', 7 => 'Maternidad', 8 => 'Universidad'];
$proName  = $proMap[$pro] ?? '';
$prodValues = array_values($proMap);
$defaultProducto = in_array($proName, $prodValues, true) ? $proName : 'Funerario';

/* ===== Catalogo de productos (iconos desde ContProd.Image_Desc) ===== */
require_once __DIR__ . '/eia/catalogo_productos.php';
$preselectedProduct = in_array($proName, $prodValues, true) ? $proName : '';
$catalogToShow = $preselectedProduct !== ''
  ? array_values(array_filter($catalog, function($item) use ($preselectedProduct) {
      return $item['value'] === $preselectedProduct;
    }))
  : $catalog;

/* ===== Cupón (opcional) ===== */
$Producto  = $_SESSION['Producto'] ?? ($proName ?: null);

// Validación de tarjeta activa por Validez_Fin
$tarjetaActivaId = 0;
$tarjetaDescuento = 0.0;
$tarjetaImg = '';

if (!empty($_SESSION['tarjeta'])) {
  if ($st = $mysqli->prepare("SELECT Id,Descuento,Img
                              FROM PostSociales
                              WHERE Id=? AND Status=1
                                AND (Validez_Fin IS NULL OR Validez_Fin='' OR Validez_Fin >= CURDATE())
                              LIMIT 1")) {
    $st->bind_param('i', $_SESSION['tarjeta']);
    $st->execute();
    $rs = $st->get_result();
    if ($row = $rs->fetch_assoc()) {
      $tarjetaActivaId   = (int)$row['Id'];
      $tarjetaDescuento  = (float)$row['Descuento'];
      $tarjetaImg        = (string)$row['Img'];
    }
    $st->close();
  }
}

// Inicializar variables por defecto
$curpFromProspecto = '';
$idProspectoReal = '';
$Mail = '';

//Validamos si ya tiene un IdProspecto
if (isset($_GET['idp']) && isset($pros) && $pros instanceof mysqli && isset($basicas) && is_object($basicas)) {
    $idp = (int)$_GET['idp'];
    $idProspectoReal = (string)$idp;
    $curpFromProspecto = $basicas->BuscarCampos($pros, 'Curp', 'prospectos', 'Id', $idp);
    //Buscamos el Email
    $Mail = $basicas->BuscarCampos($pros, 'Email', 'prospectos', 'Id', $idp);
}

// alert opcional 6 de noviembre 2025
if (isset($_GET['Msg'])) {
  $msg = mb_substr((string)$_GET['Msg'], 0, 500);
  echo "<script>alert(" . json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS) . ");</script>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Registro de servicio | KASU</title>
    <!-- Canonical y hreflang -->
  <link rel="canonical" href="https://kasu.com.mx/registro.php">
  <link rel="alternate" hreflang="es-MX" href="https://kasu.com.mx/registro.php">
  <link rel="alternate" hreflang="x-default" href="https://kasu.com.mx/registro.php">
  
  <!-- Viewport -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- SEO básico -->
  <meta name="description" content="Registra tu servicio KASU. Ingresa tu CURP, verifica tus datos y elige el producto.">
  <meta name="author" content="Erendida Itzel Castro Marquez; Jose Carlos Cabrera Monroy">
  <meta name="robots" content="index,follow,max-image-preview:large">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Comprar KASU">
  <meta property="og:title" content="Comprar KASU | página de compra de servicios">
  <meta property="og:description" content="Adquiere tu servicio KASU con cobro a tu tarjeta de débito/crédito, o paga en tiendas de conveniencia.">
  <meta property="og:url" content="https://kasu.com.mx/registro.php">
  <meta property="og:image" content="https://kasu.com.mx/assets/images/guiafuneraria-512.png">
  <meta property="og:locale" content="es_MX">
  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Comprar KASU | página de compra de servicios">
  <meta name="twitter:description" content="Adquiere tu servicio KASU con cobro a tu tarjeta de débito/crédito, o paga en tiendas de conveniencia.">
  <meta name="twitter:image" content="https://kasu.com.mx/assets/images/guiafuneraria-512.png">
  
  <!-- Iconos -->
  <link rel="icon" type="image/png" sizes="48x48" href="/assets/images/Index/florkasu-48.png">
  <link rel="icon" type="image/png" sizes="96x96" href="/assets/images/Index/florkasu-96.png">
  <link rel="icon" type="image/png" sizes="192x192" href="/assets/images/Index/florkasu-192.png">
  <link rel="icon" type="image/png" sizes="512x512" href="/assets/images/Index/florkasu-512.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/Index/florkasu-180.png">
  
  <!-- CSS externo + local -->
  <link rel="stylesheet" href="/assets/css/fonts.css?v=<?php echo $VerCache; ?>">
  <link rel="stylesheet" href="/assets/css/Compra.css?v=6">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"
        integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu"
        crossorigin="anonymous">

  <style>
    .field-wrap{max-width:480px;margin:0 auto}
    .field-wrap .form-control{width:100%}
    .Formulario input[type="checkbox"], .Formulario input[type="radio"]{width:auto;height:auto;margin:0 6px 0 0;border:none}
    .Botones{height:auto;gap:16px;align-items:stretch;justify-content:center;flex-wrap:wrap;display:flex}
    .ProdCard{display:flex;flex-direction:column;align-items:center;gap:8px;padding:12px;border:1px solid #d6dbdf;border-radius:12px;cursor:pointer;width:170px}
    .ProdCard img{width:120px;height:auto;border-radius:8px}
    .ProdCard input[type="radio"]{display:none}
    .ProdCard.active{border-color:#012F91;box-shadow:0 0 0 2px rgba(1,47,145,.15)}
    .PTitle{font-weight:700}
    button.main-button{background:#012F91;color:#fff;text-transform:uppercase;letter-spacing:.25px;border:0;border-radius:4px;padding:12px 28px}
    .mb8{margin-bottom:8px}
    .mb12{margin-bottom:12px}
    @media (max-width: 768px) {
      .AreaTrabajo { padding-left: 8px !important; padding-right: 8px !important; }
      .AreaTrabajo form { width: 100% !important; }
    }
  </style>
</head>
<body>
<!-- Modal Geolocalización -->
<div class="modal fade" id="geoModal" tabindex="-1" role="dialog" aria-labelledby="geoModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="border-radius:8px">
      <div class="modal-header" style="border-bottom:none">
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="geoModalLabel">Permitir ubicación</h4>
      </div>
      <div class="modal-body">
        <p style="font-size:15px; line-height:1.5;">
          Por disposición oficial debes permitir que <strong>KASU Servicios a Futuro</strong> registre tu ubicación.
          Activa los servicios de localización y otorga permiso al navegador para continuar.
        </p>
        <p id="geoModalHint" style="color:#777; margin-top:10px; display:none;"></p>
      </div>
      <div class="modal-footer" style="border-top:none">
        <button type="button" class="btn btn-default" data-dismiss="modal">Entendido</button>
        <button type="button" class="btn btn-primary" id="btnGeoRetry">Permitir ahora</button>
      </div>
    </div>
  </div>
</div>

<section id="Formulario" class="container-fluid" aria-label="Formulario de registro de servicio">
  <?php
    $imgByProd = ['Funerario'=>'Gastos-funerarios.png','Retiro'=>'Plan-Retiro-Privado.png','Seguridad'=>'Oficiales-Seguridad.png'];
    $Imagen = $imgByProd[$proName] ?? 'Registro-Servicio.png';
  ?>
  <div class="row no-gutter">
    <div class="col-md-6">
      <img src="assets/images/registro/<?= htmlspecialchars($Imagen,ENT_QUOTES,'UTF-8') ?>" class="img-responsive" alt="Registro de servicio KASU - <?= htmlspecialchars($proName ?: 'General',ENT_QUOTES,'UTF-8') ?>">
    </div>

    <div class="col-md-6 AreaTrabajo"> 
      <form method="POST" action="<?= htmlspecialchars($archivoRegistro,ENT_QUOTES,'UTF-8') ?>" novalidate style="width: 75%;">
        <div id="Gps" style="display: none;"></div>
        <div data-fingerprint-slot></div>
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf,ENT_QUOTES,'UTF-8') ?>">
        <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'] ?? '',ENT_QUOTES,'UTF-8') ?>">
        <input type="hidden" name="Vendedor" value="Sistema">
        <input type="hidden" name="Cupon" value="<?= htmlspecialchars($_SESSION['data'] ?? '',ENT_QUOTES,'UTF-8') ?>">
        <input type="hidden" name="tarjeta" value="<?= htmlspecialchars((string)$tarjetaActivaId, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="Referencia_KASU" value="<?= htmlspecialchars($_SESSION['IdUsr'] ?? '',ENT_QUOTES,'UTF-8') ?>">

        <?php if (!empty($tarjetaActivaId)): ?>
          <div class="logo"><img class="img-thumbnail" src="/assets/images/cupones/<?= htmlspecialchars($tarjetaImg, ENT_QUOTES) ?>"></div>
          <h1 class="text-center">Registra tu servicio con un descuento de $ <?= number_format((float)$tarjetaDescuento, 2) ?></h1>
        <?php elseif (!empty($_SESSION['tarjeta'])): ?>
          <div class="logo"><img src="assets/images/kasu_logo.jpeg" alt="KASU"></div>
          <h1 class="text-center">Lo lamentamos, esta tarjeta ya no está activa</h1>
        <?php else: ?>
          <div class="logo"><img src="assets/images/kasu_logo.jpeg" alt="KASU"></div>
          <h1 class="text-center">Registra tu servicio</h1>
        <?php endif; ?>

        <!-- Selección de producto -->
        <h3 class="text-center" style="margin:16px 0 8px;color:#012F91;">Elige tu plan</h3>
        <div class="Botones" style="margin:0 0 12px 0">
          <?php foreach ($catalogToShow as $item):
            $isActive = ($defaultProducto === $item['value']);
            $valueSafe = htmlspecialchars($item['value'], ENT_QUOTES, 'UTF-8');
            $labelSafe = htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8');
            $iconSafe  = htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8');
          ?>
            <label class="ProdCard <?= $isActive ? 'active' : '' ?>">
              <input type="radio" name="Producto" value="<?= $valueSafe ?>" <?= $isActive ? 'checked' : '' ?> required>
              <img src="<?= $iconSafe ?>" alt="<?= $labelSafe ?>" style="width:60%">
              <div class="PTitle"><?= $labelSafe ?></div>
            </label>
          <?php endforeach; ?>
        </div>

        <!-- Tipo de servicio (solo Funerario) -->
        <div id="tipo-servicio-wrap" class="field-wrap" style="display:none; margin-bottom:12px;">
          <label>Selecciona el tipo de servicio</label>
          <select class="form-control" name="TipoServicio" id="TipoServicio" disabled>
            <option value="Tradicional">Tradicional</option>
            <option value="Cremacion">Cremación</option>
            <option value="Ecologico">Ecológico</option>
          </select>
        </div>

        <!-- Precio -->
        <div id="preview-precio" style="display:none; margin:10px 0 18px 0">
          <div id="pp_contado_view">
            <h4 class="text-center">Precio de Contado:</h4>
            <h3 class="text-center" style="color:#37A80D;margin-top:0"><span id="pp_monto">$ 0.00</span></h3>
          </div>
          <div id="pp_credito_view" style="display:none;">
            <div style="display:flex; justify-content:center; gap:50px; text-align:center;">
              <div>
                <h4>Total a pagar</h4>
                <h3 style="color:#37A80D;margin-top:0"><span id="pp_total_display">$ 0.00</span></h3>
              </div>
              <div>
                <h4>Pago mensual</h4>
                <h3 style="color:#012F91;margin-top:0"><span id="pp_mensual_display">$ 0.00</span></h3>
              </div>
            </div>
          </div>
        </div>

        <!-- Tus datos -->
        <h3 class="text-center" style="margin:16px 0 8px;color:#012F91;">Tus datos</h3>
        <div class="Formulario field-wrap">
          <?php if (!empty($curpFromProspecto)): ?>
            <input class="form-control mb12" type="text" id="CURP" name="ClaveCurp"
                  value="<?= htmlspecialchars((string)$curpFromProspecto, ENT_QUOTES, 'UTF-8') ?>" disabled>
            <input class="form-control mb12" type="email" name="Mail" placeholder="Correo electrónico" required
                  autocomplete="email" value="<?= htmlspecialchars((string)$Mail, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="IdPros" value="<?= htmlspecialchars((string)$idProspectoReal, ENT_QUOTES, 'UTF-8') ?>">
          <?php else: ?>
            <input class="form-control mb12" type="text" id="CURP" name="ClaveCurp"
                  placeholder="CURP (18 caracteres)" minlength="18" maxlength="18" required
                  style="text-transform:uppercase" autocomplete="off">
            <input class="form-control mb12" type="email" name="Mail" placeholder="Correo electrónico" required
                  autocomplete="email">
          <?php endif; ?>
          <input class="form-control mb12" type="tel"   name="Telefono" placeholder="Teléfono a 10 dígitos" required pattern="\d{10}">
          <input class="form-control mb12" type="text"  name="Codigo_Postal" placeholder="Código Postal" required pattern="\d{5}">
          
          <!-- Forma de Pago -->
          <div id="pp_plazo_wrap" class="mb12" style="display:none;">
            <label>Forma de Pago</label>
            <select id="pp_plazo" name="plazo" class="form-control"></select>
          </div>

          <!-- Día de pago (solo crédito) -->
          <div id="pp_dia_pago_wrap" class="mb12" style="display:none;">
            <select id="pp_dia_pago" name="DiaPago" class="form-control">
              <option value="">Selecciona el día que quieres pagar</option>
              <option value="1">Día 1 de cada mes</option>
              <option value="15">Día 15 de cada mes</option>
            </select>
          </div>

          <input type="hidden" id="pp_prodTarifa" name="ProdTarifa" value="">
          <input type="hidden" id="pp_edad" name="Edad" value="">
          <input type="hidden" id="pp_costo" name="Costo" value="">
          <input type="hidden" id="pp_tasa" name="TasaAnual" value="">
          <input type="hidden" id="pp_descuento" name="DescuentoAplicado" value="">
          <input type="hidden" id="pp_total" name="MontoTotal" value="">
          <input type="hidden" id="pp_mensual" name="PagoMensual" value="">
        </div>

        <!-- Términos -->
        <div class="field-wrap" style="padding-top: 10px; padding-bottom: 10px;">
          <h4 style="margin-bottom:8px;color:#012F91;">Términos y condiciones</h4>
          <ul style="list-style:none;padding-left:0;">
            <li style="margin-bottom:10px;">
              <input type="checkbox" name="Terminos" required style="margin-right:8px;"><strong>Acepto</strong> los <a href="/terminos-y-condiciones" target="_blank" rel="noopener">Términos y condiciones</a>
            </li>
            <li style="margin-bottom:10px;">
              <input type="checkbox" name="Aviso" required style="margin-right:8px;"><strong>Acepto</strong> el <a href="/privacidad" target="_blank" rel="noopener">Aviso de Privacidad</a>
            </li>
            <li style="margin-bottom:10px;">
              <input type="checkbox" name="Fideicomiso" required style="margin-right:8px;"><strong>Acepto</strong> los terminos del <a href="/Fideicomiso_F0003.pdf" target="_blank" rel="noopener">Fideicomiso F/0003</a>
            </li>
          </ul>
        </div>

        <div class="field-wrap" style="margin:30px 0">
          <button type="submit" name="Registro" class="main-button" style="width:100%">Continuar mi compra</button>
        </div>
      </form>
    </div>
  </div>
</section>

<!-- JS -->
<script>
(function () {
  'use strict';

  /* ===== Constantes ===== */
  var reCURP = /^[A-Z]{4}\d{6}[HM][A-Z]{2}[A-Z]{3}[A-Z0-9]{2}$/;
  var $curp  = document.getElementById('CURP');

  /* ===== Formulario ===== */
  function selProducto() {
    var r = document.querySelector('input[name="Producto"]:checked');
    return r ? r.value : '';
  }

  function formato(n) {
    try { return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(+n); }
    catch (_) { return '$ ' + Number(n || 0).toFixed(2); }
  }

  function toggleTipoServicio() {
    var prod = selProducto();
    var wrap = document.getElementById('tipo-servicio-wrap');
    var sel  = document.getElementById('TipoServicio');
    if (!wrap || !sel) return;
    if (prod === 'Funerario') {
      wrap.style.display = '';
      sel.disabled = false;
    } else {
      wrap.style.display = 'none';
      sel.disabled = true;
      sel.selectedIndex = 0;
    }
  }

  function toggleDiaPago() {
    var plazoEl = document.getElementById('pp_plazo');
    var wrap    = document.getElementById('pp_dia_pago_wrap');
    var sel     = document.getElementById('pp_dia_pago');
    if (!plazoEl || !wrap) return;
    var meses = parseInt(plazoEl.value, 10) || 1;
    if (meses > 1) {
      wrap.style.display = '';
    } else {
      wrap.style.display = 'none';
      if (sel) sel.value = '';
    }
  }

  function validateCurp() {
    var v = ($curp.value || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
    $curp.value = v;
    var ok = v.length === 18 && reCURP.test(v);
    $curp.classList.toggle('is-invalid', !ok);
    $curp.classList.toggle('is-valid', ok);
    $curp.setCustomValidity(ok ? '' : 'CURP incompleta o inválida');
    $curp.setAttribute('aria-invalid', ok ? 'false' : 'true');
    return ok;
  }

  async function curpLookup(curp) {
    try {
      var r = await fetch('registro.php?action=curp_lookup&curp=' + encodeURIComponent(curp));
      var j = await r.json();
      if (j.ok) {
        var N = document.getElementById('Nombre');
        var P = document.getElementById('ApPaterno');
        var M = document.getElementById('ApMaterno');
        if (N) N.value = j.data.Nombre || '';
        if (P) P.value = j.data.ApPaterno || '';
        if (M) M.value = j.data.ApMaterno || '';
      }
    } catch (e) {}
  }

  /* ===== Cálculo con descuento y tasa ===== */
  function pagoSI(tasaAnual, meses, principal) {
    principal = Math.max(+principal || 0, 0);
    meses = parseInt(meses, 10) || 1;
    if (meses <= 1) return { mensual: principal, total: principal };
    var tm = (+tasaAnual / 100) / 12;
    if (!isFinite(tm) || tm <= 0) return { mensual: principal / meses, total: principal };
    var factor = Math.pow(1 + tm, meses);
    var p = (principal * tm * factor) / (factor - 1);
    return { mensual: p, total: p * meses };
  }

  function updateMonto() {
    var costo = +document.getElementById('pp_costo').value || 0;
    var tasa  = +document.getElementById('pp_tasa').value  || 0;
    var desc  = +document.getElementById('pp_descuento').value || 0;
    var meses = parseInt(document.getElementById('pp_plazo').value, 10);
    if (!isFinite(meses)) meses = 1;

    var principal = Math.max(costo - desc, 0);
    var r = pagoSI(tasa, meses, principal);

    document.getElementById('pp_monto').textContent = formato(r.total);
    document.getElementById('pp_total').value = r.total.toFixed(2);
    document.getElementById('pp_mensual').value = r.mensual.toFixed(2);

    var contadoView = document.getElementById('pp_contado_view');
    var creditoView = document.getElementById('pp_credito_view');
    var totalDisplay = document.getElementById('pp_total_display');
    var mensualDisplay = document.getElementById('pp_mensual_display');

    if (meses > 1) {
      if (contadoView) contadoView.style.display = 'none';
      if (creditoView) creditoView.style.display = '';
      if (totalDisplay) totalDisplay.textContent = formato(r.total);
      if (mensualDisplay) mensualDisplay.textContent = formato(r.mensual);
    } else {
      if (contadoView) contadoView.style.display = '';
      if (creditoView) creditoView.style.display = 'none';
    }
  }

  async function cotizar() {
    var priceWrap = document.getElementById('preview-precio');
    if (!validateCurp()) { if (priceWrap) priceWrap.style.display = 'none'; return; }

    var curp = $curp.value;
    var prod = selProducto();
    if (!prod) { if (priceWrap) priceWrap.style.display = 'none'; return; }

    try {
      var url = 'registro.php?action=price_quote&curp=' + encodeURIComponent(curp) + '&producto=' + encodeURIComponent(prod);
      var res = await fetch(url, { cache: 'no-store' });
      var j = await res.json();
      if (!j.ok) { if (priceWrap) priceWrap.style.display = 'none'; return; }

      var d = j.data || {};
      document.getElementById('pp_costo').value       = d.costo || 0;
      document.getElementById('pp_prodTarifa').value  = d.prodTarifa || '';
      document.getElementById('pp_edad').value        = d.edad || '';
      document.getElementById('pp_tasa').value        = d.tasaAnual || 0;
      document.getElementById('pp_descuento').value   = d.descuento || 0;

      var $plazo     = document.getElementById('pp_plazo');
      var $plazoWrap = document.getElementById('pp_plazo_wrap');
      while ($plazo.firstChild) $plazo.removeChild($plazo.firstChild);

      var o = document.createElement('option');
      o.value = '1';
      o.textContent = 'Pago de Contado';
      $plazo.appendChild(o);

      var plz = d.plazos || {};
      Object.keys(plz).forEach(function (k) {
        var opt = document.createElement('option');
        opt.value = k;
        opt.textContent = plz[k];
        $plazo.appendChild(opt);
      });

      if ($plazoWrap) $plazoWrap.style.display = '';

      updateMonto();
      toggleDiaPago();

      if (priceWrap) priceWrap.style.display = '';
    } catch (e) {
      if (priceWrap) priceWrap.style.display = 'none';
    }
  }

  /* ===== Geolocalización ===== */
  var gpsDiv = document.getElementById('Gps');

  function injectGPS(pos) {
    if (!gpsDiv) return;
    var latitude  = pos.coords.latitude;
    var longitud  = pos.coords.longitude;
    var accuracy  = pos.coords.accuracy;
    var ts        = Date.now();

    gpsDiv.innerHTML =
      "<input type='hidden' name='latitud' value='" + latitude + "'>" +
      "<input type='hidden' name='longitud' value='" + longitud + "'>" +
      "<input type='hidden' name='accuracy' value='" + accuracy + "'>" +
      "<input type='hidden' name='GeoTS' value='" + ts + "'>";
  }

  function showGeoMessage(hint) {
    var useModal = (window.jQuery && typeof jQuery.fn.modal === 'function');
    if (useModal) {
      if (hint) {
        var p = document.getElementById('geoModalHint');
        if (p) { p.textContent = hint; p.style.display = 'block'; }
      }
      jQuery('#geoModal').modal('show');
    } else {
      alert("Por disposición oficial debes permitir que KASU rastree tu ubicación.\n\n" + (hint || ""));
    }
  }

  function geoError(err) {
    var hint = "";
    if (err && typeof err.code !== 'undefined') {
      if (err.code === 1) hint = "Permiso denegado. Habilita la ubicación en la configuración del navegador.";
      if (err.code === 2) hint = "Ubicación no disponible. Activa el GPS/Ubicación del dispositivo e inténtalo de nuevo.";
      if (err.code === 3) hint = "Tiempo de espera agotado. Intenta nuevamente con mejor señal.";
    }
    showGeoMessage(hint);
  }

  function requestGeo() {
    if (!navigator.geolocation) {
      showGeoMessage("Tu navegador no soporta geolocalización. Por favor usa un navegador actualizado.");
      return;
    }
    navigator.geolocation.getCurrentPosition(injectGPS, geoError, {
      enableHighAccuracy: true,
      maximumAge: 0,
      timeout: 10000
    });
  }

  function initGeo() {
    if (!navigator.geolocation) { showGeoMessage(); return; }

    if (navigator.permissions && navigator.permissions.query) {
      navigator.permissions.query({ name: 'geolocation' }).then(function (status) {
        if (status.state === 'granted') {
          requestGeo();
        } else if (status.state === 'denied') {
          showGeoMessage("La geolocalización está bloqueada. Debes habilitarla en la configuración del navegador.");
        } else {
          navigator.geolocation.getCurrentPosition(injectGPS, function (err) {
            geoError(err || { code: 1 });
          }, { enableHighAccuracy: true, maximumAge: 0, timeout: 10000 });
        }
        status.onchange = function () {
          if (status.state === 'granted') requestGeo();
        };
      }).catch(function () {
        requestGeo();
      });
    } else {
      requestGeo();
    }
  }

  /* ===== Prefill con ?idp= ===== */
  function ensureHidden(name, value) {
    if (!value) return;
    var form = document.querySelector('form[action*="Registrar_Venta.php"]') || document.querySelector('form');
    if (!form) return;
    if (!form.querySelector('input[name="' + name + '"]')) {
      var h = document.createElement('input');
      h.type = 'hidden';
      h.name = name;
      h.value = value;
      form.appendChild(h);
    }
  }

  function bootPrefill() {
    var curpEl = document.getElementById('CURP');
    if (!curpEl) return;

    var qs = new URLSearchParams(location.search);
    var idp = qs.get('idp');
    if (idp) ensureHidden('IdPros', idp);

    var v = (curpEl.value || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
    curpEl.value = v;
    if (curpEl.disabled) ensureHidden('ClaveCurp', v);

    if (v.length === 18 && reCURP.test(v)) {
      if (!document.querySelector('input[name="Producto"]:checked')) {
        var def = document.querySelector('input[name="Producto"][value="Funerario"]');
        if (def) {
          def.checked = true;
          var card = def.closest('.ProdCard');
          if (card) {
            card.classList.add('active');
            // Ocultar las demás tarjetas
            document.querySelectorAll('.ProdCard').forEach(function (x) {
              if (x !== card) x.style.display = 'none';
            });
          }
        }
      }

      toggleTipoServicio();
      curpLookup(v);
      cotizar();

      var pv = document.getElementById('preview-precio');
      if (pv) pv.style.display = '';
    }
  }

  /* ===== Event Listeners ===== */
  document.addEventListener('click', function (e) {
    // ProdCard selection
    var label = e.target.closest('.ProdCard');
    if (label) {
      document.querySelectorAll('.ProdCard').forEach(function (x) { x.classList.remove('active'); });
      label.classList.add('active');
      var r = label.querySelector('input[type=radio]');
      if (r) r.checked = true;

      // Ocultar las demás tarjetas al seleccionar una
      document.querySelectorAll('.ProdCard').forEach(function (x) {
        if (x !== label) x.style.display = 'none';
      });

      toggleTipoServicio();
      cotizar();
    }
    // Geolocation retry button
    if (e.target && e.target.id === 'btnGeoRetry') {
      requestGeo();
    }
  });

  if ($curp) {
    $curp.addEventListener('input', function () {
      if (validateCurp()) cotizar();
    });

    $curp.addEventListener('blur', function () {
      if (!validateCurp()) return;
      curpLookup($curp.value);
      cotizar();
    });
  }

  document.addEventListener('change', function (e) {
    if (e.target && e.target.id === 'pp_plazo') {
      updateMonto();
      toggleDiaPago();
    }
  });

  document.addEventListener('DOMContentLoaded', function () {
    toggleTipoServicio();
    toggleDiaPago();
  });

  /* ===== Inicialización ===== */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { initGeo(); bootPrefill(); });
  } else {
    initGeo();
    bootPrefill();
  }

})();
</script>

<script src="eia/javascript/finger.js?v=4"></script>
<script src="eia/javascript/localize.js?v=3"></script>
<script>if (typeof localize === 'function') localize();</script>
</body>
</html>
