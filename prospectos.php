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
if (isset($_GET['Msg'])) {
  echo "<script>alert('".htmlspecialchars((string)$_GET['Msg'], ENT_QUOTES, 'UTF-8')."');</script>";
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
  <meta name="description" content="Regístrate como prospecto KASU para recibir información sobre servicios funerarios y planes de retiro.">
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
    body {
      background:#f5f5f5;
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
    }
  </style>
</head>
<body>

<!-- Modal Geolocalización (mismo que en página de compra) -->
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

<div class="ProspectoWrap">
  <div class="row no-gutter">
    <!-- LADO IZQUIERDO: texto + guía -->
    <div class="col-sm-5">
      <div class="ProspectoImg">
        <img src="assets/images/kasu_logo.jpeg" alt="KASU">
        <h1>Regístrate para recibir información</h1>
        <p>
          Déjanos tus datos para que un asesor KASU pueda contactarte y explicarte
          cómo funcionan nuestros servicios de gastos funerarios y planes de retiro.
        </p>
      </div>

      <div class="ProspectoGuia">
        <p>
          Llena tus datos para recibir la
          <strong>Guía completa para contratar un servicio funerario</strong>.
        </p>
        <img
          src="assets/images/guiafuneraria.jpeg"
          alt="Guía completa para contratar un servicio funerario"
          class="GuiaImg">
      </div>
    </div>

    <!-- LADO DERECHO: formulario (déjalo igual que lo tienes) -->
    <div class="col-sm-7">
      <div class="ProspectoForm">
        <h2>Datos de contacto</h2>

        <form action="https://kasu.com.mx/login/php/Registro_Prospectos.php" method="post" autocomplete="off">
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

          <div class="form-group" id="CurpGroup">
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

          <div class="form-group" id="NombreProspectoGroup" style="display:none;">
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

          <div class="form-group">
            <label>Tu E-mail</label>
            <input class="form-control"
                   type="email"
                   name="Email"
                   placeholder="Correo electrónico"
                   inputmode="email"
                   required
                   autocomplete="email">
          </div>

          <div class="form-group">
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

          <div class="form-group">
            <label>Selecciona una fecha en la cual podamos llamarte</label>
            <input class="form-control"
                   type="date"
                   name="FechaLlamada"
                   id="FechaLlamadaInput"
                   required
                   autocomplete="off">
          </div>

          <div class="form-group">
            <label>Hora para llamada</label>
            <select class="form-control" name="HoraLlamada" id="HoraLlamadaInput" disabled required>
              <option value="">Selecciona una hora</option>
            </select>
            <small id="HoraLlamadaHint">Lunes a viernes 9:00 a 18:00, sabados 10:00 a 14:00.</small>
          </div>

          <?php if ($productoRaw !== ''): ?>
            <input type="hidden" name="Servicio" value="<?= $productoSafe ?>">
          <?php else: ?>
            <div class="form-group">
              <label>Estoy interesado en</label>
              <select class="form-control" name="Servicio" required>
                <option value="">Selecciona una opción</option>
                <option value="FUNERARIO">Gastos funerarios</option>
                <option value="RETIRO">Ahorro para el retiro</option>
                <option value="SEGURIDAD">Gastos funerarios oficiales</option>
                <option value="TRANSPORTE">Servicio de traslado</option>
                <option value="DISTRIBUIDOR">Ser distribuidor</option>
              </select>
            </div>
          <?php endif; ?>

          <input type="hidden" name="OrigenVisible" id="OrigenVisibleInput" value="otro">

          <button type="submit" name="prospectoNvo">Registrar mis datos</button>

          <div class="ProspectoLegal">
            KASU Servicios a Futuro utilizará tus datos únicamente para contactarte y
            brindarte información sobre nuestros servicios, conforme a nuestro Aviso de Privacidad.
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- JS: GPS (igual lógica que en la página de compra) -->
<script>
(function(){
  var gpsDiv = document.getElementById('Gps');
  var origenInput = document.getElementById('OrigenVisibleInput');
  var curpGroup = document.getElementById('CurpGroup');
  var curpInput = document.getElementById('CurpInput');
  var nombreGroup = document.getElementById('NombreProspectoGroup');
  var nombreInput = document.getElementById('FullNameInput');
  var toggleCurpBtn = document.getElementById('toggleCurpBtn');
  var toggleCurpBackBtn = document.getElementById('toggleCurpBackBtn');
  var fechaLlamadaInput = document.getElementById('FechaLlamadaInput');
  var horaLlamadaInput = document.getElementById('HoraLlamadaInput');
  var horaLlamadaHint = document.getElementById('HoraLlamadaHint');

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

  function formatHora(mins){
    var h = Math.floor(mins / 60);
    var m = mins % 60;
    return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
  }

  function updateHoraOptions(){
    if (!fechaLlamadaInput || !horaLlamadaInput) return;
    var fechaVal = fechaLlamadaInput.value;
    horaLlamadaInput.innerHTML = '<option value="">Selecciona una hora</option>';
    if (!fechaVal) {
      horaLlamadaInput.disabled = true;
      horaLlamadaInput.required = false;
      if (fechaLlamadaInput.setCustomValidity) {
        fechaLlamadaInput.setCustomValidity('');
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
      horaLlamadaInput.disabled = true;
      horaLlamadaInput.required = false;
      if (fechaLlamadaInput.setCustomValidity) {
        fechaLlamadaInput.setCustomValidity('Domingos no hay citas disponibles.');
      }
      if (horaLlamadaHint) horaLlamadaHint.textContent = 'Domingos no hay citas disponibles.';
      return;
    }

    for (var mins = startMin; mins + 30 <= endMin; mins += 30) {
      var hora = formatHora(mins);
      var opt = document.createElement('option');
      opt.value = hora + ':00';
      opt.textContent = hora;
      horaLlamadaInput.appendChild(opt);
    }
    horaLlamadaInput.disabled = false;
    horaLlamadaInput.required = true;
    if (fechaLlamadaInput.setCustomValidity) {
      fechaLlamadaInput.setCustomValidity('');
    }
    if (horaLlamadaHint) {
      horaLlamadaHint.textContent = 'Lunes a viernes 9:00 a 18:00, sabados 10:00 a 14:00.';
    }
  }

  if (fechaLlamadaInput) {
    fechaLlamadaInput.addEventListener('change', updateHoraOptions);
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
    var utmSource = normalize(params.get('utm_source'));
    var utmMedium = normalize(params.get('utm_medium'));
    var gclid = params.get('gclid') || params.get('gbraid') || params.get('wbraid');
    var fbclid = params.get('fbclid');

    if (utmSource) {
      if (utmSource.includes('whatsapp') || utmSource === 'wa') return 'whatsapp';
      if (utmSource.includes('linkedin') || utmSource === 'li') return 'linkedin';
      if (utmSource === 'x' || utmSource.includes('twitter')) return 'x';
      if (utmSource.includes('facebook') || utmSource === 'fb') return 'fb';
      if (utmSource.includes('instagram') || utmSource === 'ig') return 'ig';
      if (utmSource.includes('tiktok') || utmSource === 'tt') return 'tt';
      if (utmSource.includes('google') || utmSource === 'gg' || utmMedium === 'cpc') return 'Gg';
      return 'otro';
    }

    if (gclid) return 'Gg';
    if (fbclid) return 'fb';

    var ref = normalize(document.referrer);
    if (ref.includes('x.com') || ref.includes('twitter.com')) return 'x';
    if (ref.includes('linkedin.com')) return 'linkedin';
    if (ref.includes('whatsapp.com') || ref.includes('wa.me')) return 'whatsapp';
    if (ref.includes('facebook.com') || ref.includes('fb.com')) return 'fb';
    if (ref.includes('instagram.com')) return 'ig';
    if (ref.includes('tiktok.com')) return 'tt';
    if (ref.includes('google.')) return 'Gg';
    return 'otro';
  }

  function injectGPS(pos){
    if(!gpsDiv) return;
    var latitude = pos.coords.latitude;
    var longitud = pos.coords.longitude;
    var accuracy = pos.coords.accuracy;
    var ts  = Date.now();

    gpsDiv.innerHTML =
      "<input type='hidden' name='latitud' value='"+latitude+"'>" +
      "<input type='hidden' name='longitud' value='"+longitud+"'>" +
      "<input type='hidden' name='accuracy' value='"+accuracy+"'>" +
      "<input type='hidden' name='GeoTS' value='"+ts+"'>";
  }

  function showGeoMessage(hint){
    var useModal = (window.jQuery && typeof jQuery.fn.modal === 'function');
    if (useModal) {
      if (hint) {
        var p = document.getElementById('geoModalHint');
        if (p){ p.textContent = hint; p.style.display='block'; }
      }
      jQuery('#geoModal').modal('show');
    } else {
      alert("Por disposición oficial debes permitir que KASU rastree tu ubicación.\n\n" + (hint || ""));
    }
  }

  function geoError(err){
    var hint = "";
    if (err && typeof err.code !== 'undefined') {
      if (err.code === 1) hint = "Permiso denegado. Habilita la ubicación en la configuración del navegador.";
      if (err.code === 2) hint = "Ubicación no disponible. Activa el GPS/Ubicación del dispositivo e inténtalo de nuevo.";
      if (err.code === 3) hint = "Tiempo de espera agotado. Intenta nuevamente con mejor señal.";
    }
    showGeoMessage(hint);
  }

  function requestGeo(){
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

  document.addEventListener('click', function(e){
    if (e.target && e.target.id === 'btnGeoRetry') {
      requestGeo();
    }
  });

  function initGeo(){
    if (!navigator.geolocation) { showGeoMessage(); return; }

    if (navigator.permissions && navigator.permissions.query) {
      navigator.permissions.query({ name: 'geolocation' }).then(function(status){
        if (status.state === 'granted') {
          requestGeo();
        } else if (status.state === 'denied') {
          showGeoMessage("La geolocalización está bloqueada. Debes habilitarla en la configuración del navegador.");
        } else {
          navigator.geolocation.getCurrentPosition(injectGPS, function(err){
            geoError(err || {code:1});
          }, { enableHighAccuracy:true, maximumAge:0, timeout:10000 });
        }
        status.onchange = function(){
          if (status.state === 'granted') requestGeo();
        };
      }).catch(function(){
        requestGeo();
      });
    } else {
      requestGeo();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGeo);
  } else {
    initGeo();
  }

  setOrigenVisible(detectOrigenVisible());
})();
</script>

<!-- Fingerprint -->
<script src="eia/javascript/finger.js?v=3"></script>

</body>
</html>
