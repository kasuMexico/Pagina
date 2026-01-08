<?php
/********************************************************************************************
 * Página: prospectos.php (raíz)
 * Qué hace: Registro de prospectos para recibir más información de KASU.
 *           - Envía a login/php/Registro_Prospectos.php (mismo destino que el modal interno).
 *           - Registra GPS y Fingerprint usando los mismos slots que el sistema actual.
 * Fecha: 15/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

session_start();
require_once __DIR__ . '/eia/analytics_bootstrap.php';
require_once __DIR__ . '/eia/librerias.php';
date_default_timezone_set('America/Mexico_City');
header_remove('X-Powered-By');

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
  http_response_code(500);
  exit('Error de conexión.');
}

/* ===== Contexto básico ===== */
$nombre      = $_GET['nombre']   ?? '';
$producto    = $_GET['producto'] ?? ''; // opcional
$productoRaw = trim((string)$producto);
$nombreSafe  = htmlspecialchars((string)$nombre,   ENT_QUOTES, 'UTF-8');
$selfSafe    = htmlspecialchars((string)($_SERVER['PHP_SELF'] ?? ''), ENT_QUOTES, 'UTF-8');
$productoSafe= htmlspecialchars($productoRaw, ENT_QUOTES, 'UTF-8');

/* ===== Mapeo de servicio para prospectos ===== */
function map_servicio_interes(string $productoRaw): string {
  $key = strtolower(trim($productoRaw));
  $map = [
    'funerario'  => 'FUNERARIO',
    'retiro'     => 'RETIRO',
    'policias'   => 'SEGURIDAD',
    'seguridad'  => 'SEGURIDAD',
    'transporte' => 'TRANSPORTE',
    'maternidad' => 'MATERNIDAD',
    'universidad'=> 'UNIVERSIDAD',
  ];
  if (isset($map[$key])) return $map[$key];
  $upper = strtoupper($productoRaw);
  $allowed = ['FUNERARIO','RETIRO','SEGURIDAD','TRANSPORTE','MATERNIDAD','UNIVERSIDAD','DISTRIBUIDOR'];
  return in_array($upper, $allowed, true) ? $upper : '';
}
$servicioFromQuery = map_servicio_interes($productoRaw);

/* ===== Catalogo de productos (iconos desde ContProd.Image_Desc) ===== */
$catalogOrder = [1, 2, 3, 6, 7, 8];
$catalogMeta = [
  1 => ['value' => 'FUNERARIO',  'fallback_name' => 'Funerario',   'fallback_icon' => '/assets/images/Index/funer.png'],
  2 => ['value' => 'RETIRO',     'fallback_name' => 'Retiro',      'fallback_icon' => '/assets/images/Index/retiro.png'],
  3 => ['value' => 'SEGURIDAD',  'fallback_name' => 'Seguridad',   'fallback_icon' => '/assets/images/Index/funer.png'],
  6 => ['value' => 'TRANSPORTE', 'fallback_name' => 'Transporte',  'fallback_icon' => '/assets/images/Index/funer.png'],
  7 => ['value' => 'MATERNIDAD', 'fallback_name' => 'Maternidad',  'fallback_icon' => '/assets/images/Index/funer.png'],
  8 => ['value' => 'UNIVERSIDAD','fallback_name' => 'Universidad', 'fallback_icon' => '/assets/images/Index/retiro.png'],
];
$catalogRows = [];
$catalog = [];
$sqlCatalog = "SELECT Id, Producto, Nombre, Image_Desc, Imagen_index FROM ContProd WHERE Id IN (1,2,3,6,7,8)";
if ($resCatalog = $mysqli->query($sqlCatalog)) {
  while ($row = $resCatalog->fetch_assoc()) {
    $catalogRows[(int)$row['Id']] = $row;
  }
  $resCatalog->free();
}
foreach ($catalogOrder as $id) {
  $meta = $catalogMeta[$id];
  $row = $catalogRows[$id] ?? [];
  $label = (string)($row['Nombre'] ?? $meta['fallback_name']);
  $icon = (string)($row['Image_Desc'] ?? '');
  if ($icon === '') {
    $icon = (string)($row['Imagen_index'] ?? $meta['fallback_icon']);
  }
  if ($icon === '') {
    $icon = '/assets/images/kasu_logo.jpeg';
  }
  $catalog[] = [
    'value' => $meta['value'],
    'label' => $label,
    'icon'  => $icon,
  ];
}

/* ===== Copy segmentado (si viene producto preseleccionado) ===== */
$copyByServicio = [
  'FUNERARIO' => [
    'title' => 'Recibe informacion de Gastos Funerarios',
    'copy'  => 'Dejanos tus datos y un asesor KASU te explicara como funciona el plan funerario con pago unico y cobertura nacional.',
    'lead'  => 'Guia completa para contratar un servicio funerario',
  ],
  'RETIRO' => [
    'title' => 'Recibe informacion de Retiro',
    'copy'  => 'Dejanos tus datos y un asesor KASU te explicara el plan de retiro y sus beneficios.',
    'lead'  => 'Informacion completa del plan de retiro KASU',
  ],
  'SEGURIDAD' => [
    'title' => 'Planes Funerarios para oficiales de seguridad',
    'copy'  => 'Comparte tus datos y te explicamos el plan para oficiales de seguridad publica.',
    'lead'  => 'Informacion completa para seguridad publica',
  ],
  'TRANSPORTE' => [
    'title' => 'Planes Funerarios para taxistas',
    'copy'  => 'Dejanos tus datos y te explicamos el plan para taxistas y transportistas.',
    'lead'  => 'Informacion completa para taxistas',
  ],
  'MATERNIDAD' => [
    'title' => 'KASU Maternidad',
    'copy'  => 'Dejanos tus datos para recibir informacion del plan de maternidad y la red KASU.',
    'lead'  => 'Informacion completa de KASU Maternidad',
  ],
  'UNIVERSIDAD' => [
    'title' => 'KASU Futuro 18',
    'copy'  => 'Dejanos tus datos para recibir informacion del plan de universidad y emprendimiento.',
    'lead'  => 'Informacion completa de KASU Futuro 18',
  ],
  'DISTRIBUIDOR' => [
    'title' => 'Conviértete en distribuidor KASU',
    'copy'  => 'Dejanos tus datos para conocer el programa de distribuidores y sus beneficios.',
    'lead'  => 'Informacion completa para distribuidores KASU',
  ],
];
$defaultCopy = [
  'title' => 'Registrate para recibir informacion',
  'copy'  => 'Dejanos tus datos para que un asesor KASU pueda contactarte y explicarte como funcionan nuestros servicios funerarios, maternidad, universidad y planes de retiro.',
  'lead'  => 'Llena tus datos para recibir informacion de nuestros servicios',
];
$heroCopy = $servicioFromQuery !== '' && isset($copyByServicio[$servicioFromQuery])
  ? $copyByServicio[$servicioFromQuery]
  : $defaultCopy;

/* ===== Guia por producto ===== */
$guideMap = [
  'FUNERARIO'   => 'guiafuneraria.png',
  'TRANSPORTE'  => 'guiafuneraria.png',
  'UNIVERSIDAD' => 'guiauniversidad.png',
  'MATERNIDAD'  => 'guiaembarazo.png',
  'SEGURIDAD'   => 'guiaoficiales.png',
  'DISTRIBUIDOR'=> 'guiadistribuidor.png',
];
$guideImage = $guideMap[$servicioFromQuery] ?? 'guiafuneraria.png';

/* ===== Opiniones (prueba social) ===== */
$opiniones = [];
if ($resOpin = $mysqli->query("SELECT Nombre, Opinion, Servicio, foto FROM opiniones ORDER BY id DESC LIMIT 3")) {
  while ($row = $resOpin->fetch_assoc()) {
    $opiniones[] = $row;
  }
  $resOpin->free();
}

/* ===== CSRF (mismo esquema que el modal de prospectos) ===== */
$csrf = $_SESSION['csrf_auth'] ?? ($_SESSION['csrf'] ?? null);
if (!$csrf) {
  $_SESSION['csrf_auth'] = bin2hex(random_bytes(32));
  $csrf = $_SESSION['csrf_auth'];
}
$csrfSafe = htmlspecialchars((string)$csrf, ENT_QUOTES, 'UTF-8');

/* ===== Origen / método (para compatibilidad con Registro_Prospectos.php) ===== */
$metodoStr  = 'WEB'; // identificador de origen desde sitio público
$metodoSafe = htmlspecialchars($metodoStr, ENT_QUOTES, 'UTF-8');

/* ===== Mensaje opcional vía ?Msg= ===== */
$msgFromQuery = '';
if (isset($_GET['Msg'])) {
  $msgFromQuery = htmlspecialchars((string)$_GET['Msg'], ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Regístrate para recibir información | KASU</title>

  <!-- Canonical y hreflang -->
  <link rel="canonical" href="https://kasu.com.mx/prospectos.php">
  <link rel="alternate" hreflang="es-MX" href="https://kasu.com.mx/prospectos.php">

  <!-- Viewport -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- SEO básico -->
  <meta name="description" content="Regístrate como prospecto KASU para recibir informacion sobre servicios funerarios, maternidad, universidad y planes de retiro.">
  <meta name="author" content="Erendida Itzel Castro Marquez; Jose Carlos Cabrera Monroy">
  <meta name="robots" content="index,follow,max-image-preview:large">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="KASU Servicios a Futuro">
  <meta property="og:title" content="KASU | Regístrate para recibir información">
  <meta property="og:description" content="Déjanos tus datos para que un asesor KASU te brinde toda la información que necesitas.">
  <meta property="og:url" content="https://kasu.com.mx/prospectos.php">
  <meta property="og:image" content="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <meta property="og:locale" content="es_MX">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="KASU | Regístrate para recibir información">
  <meta name="twitter:description" content="Déjanos tus datos para que un asesor KASU te brinde toda la información que necesitas.">
  <meta name="twitter:image" content="https://kasu.com.mx/assets/images/kasu_logo.jpeg">

  <!-- Iconos -->
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <link rel="apple-touch-icon" sizes="180x180" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">

  <!-- CSS externo + local -->
  <link rel="stylesheet" href="assets/css/Compra.css?v=6">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">

  <style>
    html, body {
      height:auto;
    }
    body {
      background:#f5f5f5;
      overflow:auto;
    }
    .ProspectoWrap {
      max-width: 900px;
      margin: 40px auto;
      background:#fff;
      border-radius:12px;
      box-shadow:0 4px 18px rgba(0,0,0,.08);
      overflow:hidden;
    }
    .ProspectoImg {
      background:#012F91;
      color:#fff;
      padding:30px 20px;
      text-align:center;
    }
    .ProspectoImg img {
      max-width:160px;
      margin-bottom:20px;
    }
    .ProspectoImg h1 {
      font-size:24px;
      margin-bottom:10px;
      font-weight:700;
    }
    .ProspectoImg p {
      font-size:15px;
      line-height:1.5;
      margin:0;
    }
    .ProspectoForm {
      padding:30px 30px 24px;
    }
    .ProspectoMsg {
      display:none;
      margin-bottom:12px;
      padding:10px 12px;
      border-radius:8px;
      font-size:13px;
      background:#f8fafc;
      border:1px solid #e5e7eb;
      color:#374151;
    }
    .ProspectoMsg.ok {
      background:#ecfdf3;
      border:1px solid #a7f3d0;
      color:#065f46;
    }
    .ProspectoMsg.err {
      background:#fef2f2;
      border:1px solid #fecaca;
      color:#991b1b;
    }
    .ProspectoForm h2 {
      font-size:20px;
      margin-top:0;
      margin-bottom:15px;
      font-weight:600;
      text-align:left;
    }
    .ProspectoForm .form-group label {
      font-weight:500;
      margin-bottom:4px;
    }
    .ProspectoForm .form-control {
      max-width:100%;
      box-sizing:border-box;
      min-width:0;
    }
    #AgendaWrap {
      margin-top:10px;
    }
    .ProspectoProductos {
      display:flex;
      flex-wrap:wrap;
      gap:12px;
      justify-content:flex-start;
    }
    .ProdCard {
      display:flex;
      flex-direction:column;
      align-items:center;
      gap:8px;
      padding:12px;
      border:1px solid #d6dbdf;
      border-radius:12px;
      cursor:pointer;
      width:140px;
      background:#fff;
    }
    .ProdCard img {
      width:76px;
      height:auto;
      border-radius:8px;
    }
    .ProdCard input[type="radio"] { display:none; }
    .ProdCard.active {
      border-color:#012F91;
      box-shadow:0 0 0 2px rgba(1,47,145,.15);
    }
    .ProspectoForm small {
      color:#777;
    }
    .ProspectoForm button[type=submit] {
      background:#012F91;
      color:#fff;
      border:0;
      width:100%;
      padding:12px 16px;
      border-radius:4px;
      text-transform:uppercase;
      font-weight:600;
      letter-spacing:.4px;
    }
    .ProspectoForm button[type=submit]:hover {
      opacity:.95;
    }
    .ProspectoLegal {
      margin-top:10px;
      font-size:12px;
      color:#777;
    }
    .ProspectoAgendaLead {
      margin-bottom:12px;
      font-size:14px;
      font-weight:600;
      color:#1f2937;
    }
    .ProspectoSocial {
      margin-top:16px;
      border-top:1px solid #e5e7eb;
      padding-top:12px;
    }
    .ProspectoSocial h3 {
      margin:0 0 10px;
      font-size:15px;
      font-weight:700;
    }
    .ProspectoSocialList {
      display:grid;
      grid-template-columns:repeat(1, minmax(0, 1fr));
      gap:10px;
    }
    .ProspectoSocialCard {
      display:flex;
      gap:10px;
      align-items:flex-start;
      padding:10px;
      border:1px solid #e5e7eb;
      border-radius:10px;
      background:#fff;
    }
    .ProspectoSocialCard img {
      width:42px;
      height:42px;
      border-radius:50%;
      object-fit:cover;
      background:#f3f4f6;
    }
    .ProspectoSocialCard p {
      margin:0;
      font-size:12px;
      color:#374151;
    }
    .ProspectoSocialCard span {
      display:block;
      margin-top:6px;
      font-size:11px;
      color:#6b7280;
    }
    .ProspectoHint {
      font-size:12px;
      color:#6b7280;
      margin-top:6px;
    }
    @media (max-width: 767px) {
      .ProspectoWrap {
        margin:10px;
      }
      .ProspectoImg {
        padding:20px 15px;
      }
      .ProspectoForm {
        padding:20px 15px 18px;
      }
      .ProspectoProductos {
        display:grid;
        grid-template-columns:repeat(2, minmax(0, 1fr));
        gap:12px;
      }
      .ProdCard { width:100%; }
      .ProdCard img { width:64px; }
    }
  </style>
</head>
<body>

<div class="ProspectoWrap">
  <div class="row no-gutter">
    <!-- LADO IZQUIERDO: texto + guía -->
    <div class="col-sm-5">
      <div class="ProspectoImg">
        <h1><?= htmlspecialchars($heroCopy['title'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($heroCopy['copy'], ENT_QUOTES, 'UTF-8') ?></p>
      </div>

      <div class="ProspectoGuia">
        <p><?= htmlspecialchars($heroCopy['lead'], ENT_QUOTES, 'UTF-8') ?></p>
        <img
          src="assets/images/<?= htmlspecialchars($guideImage, ENT_QUOTES, 'UTF-8') ?>"
          alt="Guia informativa KASU"
          class="GuiaImg">
      </div>
    </div>

    <!-- LADO DERECHO: formulario (déjalo igual que lo tienes) -->
    <div class="col-sm-7">
      <div class="ProspectoForm">
        <h2>Datos de contacto</h2>

        <?php $msgDisplay = $msgFromQuery !== '' ? ' style="display:block;"' : ''; ?>
        <div id="ProspectoMsg" class="ProspectoMsg" role="status" aria-live="polite"<?= $msgDisplay ?>><?= $msgFromQuery ?></div>

        <form id="ProspectoForm" action="https://kasu.com.mx/login/php/Registro_Prospectos.php" method="post" autocomplete="off">
          <!-- Slots de GPS y Fingerprint -->
          <div id="Gps" style="display:none;"></div>
          <div data-fingerprint-slot></div>

          <!-- Contexto oculto (compatibilidad con backend actual) -->
          <input type="hidden" name="nombre"    value="<?= $nombreSafe ?>">
          <input type="hidden" name="Host"      value="<?= $selfSafe ?>">
          <input type="hidden" name="IdVenta"   value="PLATAFORMA">
          <input type="hidden" name="IdContact" value="0">
          <input type="hidden" name="IdUsuario" value="0">
          <input type="hidden" name="Producto"  value="<?= $productoSafe ?>">
          <input type="hidden" name="csrf"      value="<?= $csrfSafe ?>">
          <!-- Origen fijado a WEB (puedes cambiarlo en el backend si lo requieres) -->
          <input type="hidden" name="Origen" value="<?= $metodoSafe ?>">
          <input type="hidden" name="prospectoNvo" value="1" id="ProspectoNvoInput">
          <input type="hidden" name="Cita" value="1" id="CitaInput" disabled>
          <input type="hidden" name="IdProspecto" value="" id="IdProspectoInput">

          <div class="form-group ProspectoHideOnAgenda" id="CurpGroup">
            <label>Registra tu CURP </label>
            <input class="form-control text-uppercase"
                   type="text"
                   id="CurpInput"
                   name="CURP"
                   placeholder="CLAVE CURP (18 caracteres)"
                   pattern="[A-Za-z0-9]{18}"
                   maxlength="18"
                   minlength="18"
                   oninput="this.value=this.value.toUpperCase()"
                   required
                   autocomplete="off">
            <small>Si no la tienes a la mano, usa el botón para ingresar tu nombre.</small>
            <button type="button" class="btn btn-link" id="toggleCurpBtn" style="padding:0; font-size:12px;">
              No tengo mi CURP
            </button>
          </div>

          <div class="form-group ProspectoHideOnAgenda" id="NombreProspectoGroup" style="display:none;">
            <label>Registra tu nombre completo</label>
            <input class="form-control text-uppercase"
                   type="text"
                   id="FullNameInput"
                   name="FullName"
                   placeholder="Nombre completo"
                   autocomplete="name"
                   disabled>
            <small>Si prefieres, puedes volver a ingresar tu CURP.</small>
            <button type="button" class="btn btn-link" id="toggleCurpBackBtn" style="padding:0; font-size:12px;">
              Tengo mi CURP
            </button>
          </div>

          <div class="form-group ProspectoHideOnAgenda">
            <label>Tu E-mail</label>
            <input class="form-control"
                   type="email"
                   name="Email"
                   placeholder="Correo electrónico"
                   inputmode="email"
                   required
                   autocomplete="email">
          </div>

          <div class="form-group ProspectoHideOnAgenda">
            <label>Registra tu teléfono</label>
            <input class="form-control"
                   type="tel"
                   name="Telefono"
                   placeholder="10 dígitos"
                   required
                   inputmode="numeric"
                   pattern="[0-9]{10}"
                   maxlength="10"
                   minlength="10"
                   autocomplete="tel">
          </div>

          <?php if ($servicioFromQuery !== ''): ?>
            <input type="hidden" name="Servicio" value="<?= htmlspecialchars($servicioFromQuery, ENT_QUOTES, 'UTF-8') ?>">
          <?php else: ?>
            <div class="form-group ProspectoHideOnAgenda">
              <label>Estoy interesado en</label>
              <div class="ProspectoProductos">
                <?php foreach ($catalog as $item):
                  $valueSafe = htmlspecialchars($item['value'], ENT_QUOTES, 'UTF-8');
                  $labelSafe = htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8');
                  $iconSafe  = htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8');
                ?>
                  <label class="ProdCard">
                    <input type="radio" name="Servicio" value="<?= $valueSafe ?>" required>
                    <img src="<?= $iconSafe ?>" alt="<?= $labelSafe ?>">
                    <div class="PTitle"><?= $labelSafe ?></div>
                  </label>
                <?php endforeach; ?>
                <label class="ProdCard">
                  <input type="radio" name="Servicio" value="DISTRIBUIDOR" required>
                  <img src="/assets/images/kasu_logo.jpeg" alt="Distribuidor">
                  <div class="PTitle">Distribuidor</div>
                </label>
              </div>
            </div>
          <?php endif; ?>

          <div id="AgendaWrap" style="display:none;">
            <div class="ProspectoAgendaLead" id="ProspectoAgendaLead"></div>
            <div class="form-group">
              <label>Que dia podemos llamarte para platicarte de KASU</label>
              <input class="form-control"
                     type="date"
                     name="FechaCita"
                     id="FechaCitaInput"
                     disabled
                     required
                     autocomplete="off">
            </div>

            <div class="form-group">
              <label>Hora para llamada</label>
              <select class="form-control" name="HoraCita" id="HoraCitaInput" disabled required>
                <option value="">Selecciona una hora</option>
              </select>
              <small id="HoraLlamadaHint">Lunes a viernes 9:00 a 18:00, sabados 10:00 a 14:00.</small>
            </div>
          </div>

          <input type="hidden" name="OrigenVisible" id="OrigenVisibleInput" value="otro">

          <button type="submit" id="ProspectoSubmit">Registrar mis datos</button>

          <div class="ProspectoHint">Te contactamos en horario laboral. Si prefieres WhatsApp, indicalo al asesor.</div>

          <div class="ProspectoLegal">
            KASU Servicios a Futuro utilizara tus datos unicamente para contactarte y
            brindarte informacion sobre nuestros servicios, conforme a nuestro
            <a href="/privacidad.php" target="_blank" rel="noopener">Aviso de Privacidad</a>.
          </div>

          <?php if (!empty($opiniones)): ?>
            <div class="ProspectoSocial" aria-label="Opiniones de clientes">
              <h3>Opiniones reales</h3>
              <div class="ProspectoSocialList">
                <?php foreach ($opiniones as $op):
                  $opNombre = htmlspecialchars((string)($op['Nombre'] ?? 'Cliente'), ENT_QUOTES, 'UTF-8');
                  $opServ   = htmlspecialchars((string)($op['Servicio'] ?? ''), ENT_QUOTES, 'UTF-8');
                  $opTexto  = htmlspecialchars((string)($op['Opinion'] ?? ''), ENT_QUOTES, 'UTF-8');
                  $opFoto   = htmlspecialchars((string)($op['foto'] ?? '/assets/images/kasu_logo.jpeg'), ENT_QUOTES, 'UTF-8');
                ?>
                  <div class="ProspectoSocialCard">
                    <img src="<?= $opFoto ?>" alt="<?= $opNombre ?>">
                    <div>
                      <p><?= $opTexto ?></p>
                      <span><?= $opNombre ?><?= $opServ !== '' ? ' · ' . $opServ : '' ?></span>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/jquery-2.1.0.min.js"></script>
<!-- JS: GPS (igual lógica que en la página de compra) -->
<script>
(function(){
  var origenInput = document.getElementById('OrigenVisibleInput');
  var curpGroup = document.getElementById('CurpGroup');
  var curpInput = document.getElementById('CurpInput');
  var nombreGroup = document.getElementById('NombreProspectoGroup');
  var nombreInput = document.getElementById('FullNameInput');
  var toggleCurpBtn = document.getElementById('toggleCurpBtn');
  var toggleCurpBackBtn = document.getElementById('toggleCurpBackBtn');
  var fechaInput = document.getElementById('FechaLlamadaInput') || document.getElementById('FechaCitaInput');
  var horaInput = document.getElementById('HoraLlamadaInput') || document.getElementById('HoraCitaInput');
  var horaLlamadaHint = document.getElementById('HoraLlamadaHint');
  var form = document.getElementById('ProspectoForm');
  var agendaWrap = document.getElementById('AgendaWrap');
  var msgBox = document.getElementById('ProspectoMsg');
  var submitBtn = document.getElementById('ProspectoSubmit');
  var prospectoNvoInput = document.getElementById('ProspectoNvoInput');
  var citaInput = document.getElementById('CitaInput');
  var prospectoIdInput = document.getElementById('IdProspectoInput');
  var agendaLead = document.getElementById('ProspectoAgendaLead');
  var agendaActive = false;

  function setCurpMode(useNombre){
    if (useNombre) {
      if (curpGroup) curpGroup.style.display = 'none';
      if (nombreGroup) nombreGroup.style.display = '';
      if (curpInput) { curpInput.value = ''; curpInput.disabled = true; curpInput.required = false; }
      if (nombreInput) { nombreInput.disabled = false; nombreInput.required = true; nombreInput.focus(); }
    } else {
      if (curpGroup) curpGroup.style.display = '';
      if (nombreGroup) nombreGroup.style.display = 'none';
      if (curpInput) { curpInput.disabled = false; curpInput.required = true; }
      if (nombreInput) { nombreInput.value = ''; nombreInput.disabled = true; nombreInput.required = false; }
    }
  }

  if (toggleCurpBtn) {
    toggleCurpBtn.addEventListener('click', function(){ setCurpMode(true); });
  }
  if (toggleCurpBackBtn) {
    toggleCurpBackBtn.addEventListener('click', function(){ setCurpMode(false); });
  }

  document.addEventListener('click', function(e){
    var card = e.target.closest('.ProdCard');
    if (!card) return;
    document.querySelectorAll('.ProdCard').forEach(function(el){ el.classList.remove('active'); });
    card.classList.add('active');
    var r = card.querySelector('input[type="radio"]');
    if (r) r.checked = true;
  });

  function formatHora(mins){
    var h = Math.floor(mins / 60);
    var m = mins % 60;
    return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
  }

  function updateHoraOptions(){
    if (!fechaInput || !horaInput) return;
    var fechaVal = fechaInput.value;
    horaInput.innerHTML = '<option value="">Selecciona una hora</option>';
    if (!fechaVal) {
      horaInput.disabled = true;
      horaInput.required = false;
      if (fechaInput.setCustomValidity) {
        fechaInput.setCustomValidity('');
      }
      if (horaLlamadaHint) {
        horaLlamadaHint.textContent = 'Lunes a viernes 9:00 a 18:00, sabados 10:00 a 14:00.';
      }
      return;
    }
    var dateObj = new Date(fechaVal + 'T00:00:00');
    var dow = dateObj.getDay(); // 0 domingo, 6 sabado
    var startMin;
    var endMin;

    if (dow >= 1 && dow <= 5) {
      startMin = 9 * 60;
      endMin = 18 * 60;
    } else if (dow === 6) {
      startMin = 10 * 60;
      endMin = 14 * 60;
    } else {
      horaInput.disabled = true;
      horaInput.required = false;
      if (fechaInput.setCustomValidity) {
        fechaInput.setCustomValidity('Domingos no hay citas disponibles.');
      }
      if (horaLlamadaHint) horaLlamadaHint.textContent = 'Domingos no hay citas disponibles.';
      return;
    }

    for (var mins = startMin; mins + 30 <= endMin; mins += 30) {
      var hora = formatHora(mins);
      var opt = document.createElement('option');
      opt.value = hora + ':00';
      opt.textContent = hora;
      horaInput.appendChild(opt);
    }
    horaInput.disabled = false;
    horaInput.required = true;
    if (fechaInput.setCustomValidity) {
      fechaInput.setCustomValidity('');
    }
    if (horaLlamadaHint) {
      horaLlamadaHint.textContent = 'Lunes a viernes 9:00 a 18:00, sabados 10:00 a 14:00.';
    }
  }

  if (fechaInput) {
    fechaInput.addEventListener('change', updateHoraOptions);
    updateHoraOptions();
  }

  function setMsg(text, isError) {
    if (!msgBox) return;
    msgBox.textContent = text || '';
    msgBox.classList.remove('ok', 'err');
    if (text) {
      msgBox.classList.add(isError ? 'err' : 'ok');
      msgBox.style.display = 'block';
    } else {
      msgBox.style.display = 'none';
    }
  }

  function showAgenda() {
    agendaActive = true;
    if (agendaWrap) agendaWrap.style.display = '';
    if (agendaLead) {
      var fullNameRaw = '';
      if (nombreInput && nombreInput.value) {
        fullNameRaw = nombreInput.value;
      }
      if (!fullNameRaw && curpInput && curpInput.value) {
        fullNameRaw = '';
      }
      if (!fullNameRaw) {
        var hiddenName = document.querySelector('input[name="nombre"]');
        if (hiddenName && hiddenName.value) {
          fullNameRaw = hiddenName.value;
        }
      }
      var namePart = '';
      if (fullNameRaw) {
        var parts = fullNameRaw.trim().split(/\s+/);
        namePart = parts[0] || '';
      }
      if (namePart) {
        namePart = namePart.charAt(0).toUpperCase() + namePart.slice(1).toLowerCase();
      }
      var leadText = namePart ? ('Excelente ' + namePart + '. Ahora solo dinos que dia te podemos llamar.') : 'Excelente. Ahora solo dinos que dia te podemos llamar.';
      agendaLead.textContent = leadText;
    }
    setMsg('', false);
    document.querySelectorAll('.ProspectoHideOnAgenda').forEach(function(el){
      el.style.display = 'none';
    });
    if (prospectoNvoInput) prospectoNvoInput.disabled = true;
    if (citaInput) citaInput.disabled = false;
    if (fechaInput) {
      fechaInput.disabled = false;
      fechaInput.required = true;
    }
    if (horaInput) {
      horaInput.disabled = true;
      horaInput.required = true;
    }
    if (submitBtn) submitBtn.textContent = 'Agendar llamada';
    updateHoraOptions();
  }

  setCurpMode(false);

  function normalize(val){
    return (val || '').toString().trim().toLowerCase();
  }

  function setOrigenVisible(val){
    if (!origenInput) return;
    origenInput.value = val || 'otro';
  }

  function detectOrigenVisible(){
    var params = new URLSearchParams(window.location.search);
    var utmSource = normalize(params.get('utm_source') || params.get('origen') || params.get('source'));
    var utmMedium = normalize(params.get('utm_medium'));
    var gclid = params.get('gclid') || params.get('gbraid') || params.get('wbraid');
    var fbclid = params.get('fbclid');

    if (utmSource) {
      if (utmSource.includes('blog')) return 'blog';
      if (utmSource.includes('whatsapp') || utmSource === 'wa') return 'whatsapp';
      if (utmSource.includes('linkedin') || utmSource === 'li') return 'linkedin';
      if (utmSource === 'x' || utmSource.includes('twitter')) return 'x';
      if (utmSource.includes('facebook') || utmSource === 'fb') return 'fb';
      if (utmSource.includes('instagram') || utmSource === 'ig') return 'ig';
      if (utmSource.includes('tiktok') || utmSource === 'tt') return 'tt';
      if (utmSource.includes('google') || utmSource === 'gg' || utmMedium === 'cpc') return 'Gg';
      return 'otro';
    }

    if (utmMedium && utmMedium.includes('blog')) return 'blog';
    if (gclid) return 'Gg';
    if (fbclid) return 'fb';

    var ref = normalize(document.referrer);
    if (ref.includes('kasu.com.mx/blog')) return 'blog';
    if (ref.includes('x.com') || ref.includes('twitter.com')) return 'x';
    if (ref.includes('linkedin.com')) return 'linkedin';
    if (ref.includes('whatsapp.com') || ref.includes('wa.me')) return 'whatsapp';
    if (ref.includes('facebook.com') || ref.includes('fb.com')) return 'fb';
    if (ref.includes('instagram.com')) return 'ig';
    if (ref.includes('tiktok.com')) return 'tt';
    if (ref.includes('google.')) return 'Gg';
    return 'otro';
  }

  setOrigenVisible(detectOrigenVisible());

  if (window.jQuery && form) {
    window.jQuery(form).on('submit', function(e){
      e.preventDefault();
      if (form.checkValidity && !form.checkValidity()) {
        if (form.reportValidity) form.reportValidity();
        return;
      }

      var $form = window.jQuery(form);
      var keepDisabled = false;
      var data = $form.serialize();
      data += (data ? '&' : '') + 'ajax=1';

      if (!agendaActive) {
        if (submitBtn) submitBtn.disabled = true;
        setMsg('Registrando tus datos...', false);
        window.jQuery.ajax({
          url: form.action,
          method: 'POST',
          data: data,
          dataType: 'json'
        }).done(function(res){
          if (res && res.ok && res.prospectoId) {
            if (prospectoIdInput) prospectoIdInput.value = res.prospectoId;
            setMsg('Listo. Ahora agenda tu llamada.', false);
            showAgenda();
          } else {
            setMsg((res && res.msg) ? res.msg : 'No se pudo registrar el prospecto.', true);
          }
        }).fail(function(){
          setMsg('No se pudo registrar el prospecto. Intenta de nuevo.', true);
        }).always(function(){
          if (submitBtn) submitBtn.disabled = false;
        });
        return;
      }

      if (!prospectoIdInput || !prospectoIdInput.value) {
        setMsg('Falta el folio del prospecto. Intenta de nuevo.', true);
        return;
      }

      var fechaVal = fechaInput ? fechaInput.value : '';
      var horaVal = horaInput ? horaInput.value : '';
      if (!fechaVal || !horaVal) {
        setMsg('Completa la fecha y hora para agendar la llamada.', true);
        return;
      }
      data += '&Cita=1';
      data += '&IdProspecto=' + encodeURIComponent(prospectoIdInput.value);
      data += '&FechaCita=' + encodeURIComponent(fechaVal);
      data += '&HoraCita=' + encodeURIComponent(horaVal);

      if (submitBtn) submitBtn.disabled = true;
      setMsg('Agendando llamada...', false);
      window.jQuery.ajax({
        url: form.action,
        method: 'POST',
        data: data,
        dataType: 'json'
      }).done(function(res){
        if (res && res.ok) {
          if (res.send_url) {
            window.jQuery.get(res.send_url);
          }
          setMsg(res.msg ? res.msg : 'Tu llamada quedo agendada y te enviamos la guia.', false);
          keepDisabled = true;
        } else {
          setMsg((res && res.msg) ? res.msg : 'No se pudo agendar la llamada.', true);
        }
      }).fail(function(){
        setMsg('No se pudo agendar la llamada. Intenta de nuevo.', true);
      }).always(function(){
        if (!keepDisabled && submitBtn) submitBtn.disabled = false;
      });
    });
  }
})();
</script>

<!-- Fingerprint -->
<script src="eia/javascript/finger.js?v=3"></script>

</body>
</html>
