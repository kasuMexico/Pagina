<?php
/**
 * Qué hace: Formulario de registro y APIs auxiliares (lookup CURP y cotización). Envía a /eia/Registrar_Venta.php.
 * Fecha: 03/11/2025
 * Revisado por: JCCM
 */

// registro.php
session_start();
require_once __DIR__ . '/eia/analytics_bootstrap.php';
require_once __DIR__ . '/eia/librerias.php';
date_default_timezone_set('America/Mexico_City');
header_remove('X-Powered-By');

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
  http_response_code(500);
  exit('Error de conexión.');
}

/* ===== Procesador POST (ruta) ===== */
$archivoRegistro = '/eia/Registrar_Venta.php';

/* ===== Utilidades ===== */
function clean_str(?string $v): string { return trim((string)$v); }
function is_curp(string $s): bool {
  return (bool)preg_match('/^[A-Z]{4}\d{6}[HM][A-Z]{2}[A-Z]{3}[A-Z0-9]{2}$/', $s);
}

/* ===== API: lookup por CURP ===== */
if (isset($_GET['action']) && $_GET['action'] === 'curp_lookup') {
  header('Content-Type: application/json; charset=utf-8');
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
  if (strlen($curp)!==18 || !is_curp($curp) || !in_array($producto, ['Funerario','Retiro'], true)) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Parámetros inválidos'], JSON_UNESCAPED_UNICODE);
    exit;
  }

  try {
    $edad = (int)$basicas->ObtenerEdad($curp);
    $prodTarifa = ($producto === 'Retiro') ? 'Retiro' : $basicas->ProdFune($edad);
    $costo = (float)$basicas->BuscarCampos($mysqli, 'Costo', 'Productos', 'Producto', $prodTarifa);

    if ($producto === 'Retiro') {
      $pago = ['CONTADO' => 'CONTADO'];
      $plazos = [];
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
        'plazos'     => $plazos
      ]
    ], JSON_UNESCAPED_UNICODE);
  } catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Error interno'], JSON_UNESCAPED_UNICODE);
  }
  exit;
}

/* ===== CSRF ===== */
if (empty($_SESSION['csrf_reg'])) { $_SESSION['csrf_reg'] = bin2hex(random_bytes(32)); }
$csrf = $_SESSION['csrf_reg'];

/* ===== Preselección por ?pro= ===== */
$pro      = filter_input(INPUT_GET, 'pro', FILTER_VALIDATE_INT) ?: 0;
$proMap   = [1 => 'Funerario', 2 => 'Retiro', 3 => 'Seguridad'];
$proName  = $proMap[$pro] ?? '';

/* ===== Cupón (opcional) ===== */
$Producto  = $_SESSION['Producto'] ?? ($proName ?: null);
$Img=''; $Descuento=0.0; $Costo=(float)($_SESSION['Costo'] ?? 0);
if ($Producto && !empty($_SESSION['tarjeta'])) {
  $IdProd=(int)$basicas->BuscarCampos($mysqli,'Id','Productos','Producto',$Producto);
  $Img   =(string)$basicas->BuscarCampos($mysqli,'Img','PostSociales','Id',$_SESSION['tarjeta']);
  $Descuento=(float)$basicas->BuscarCampos($mysqli,'Descuento','PostSociales','Id',$_SESSION['tarjeta']);
  $Prod  =(string)$basicas->BuscarCampos($mysqli,'Producto','PostSociales','Id',$_SESSION['tarjeta']);
  $IdPCup=(int)$basicas->BuscarCampos($mysqli,'Id','Productos','Producto',$Prod);
  if ($IdProd >= $IdPCup) { $Costo = max(0, $Costo - $Descuento); }
}

//Validamos si ya tiene un IdProspecto
if(isset($_GET['idp'])){
    $IdProspecto = $basicas->BuscarCampos($pros,'Curp','prospectos','Id',$_GET['idp']);;
    //Buscamos el Email
    $Mail = $basicas->BuscarCampos($pros,'Email','prospectos','Id',$_GET['idp']);
}

// alert opcional 6 de noviembre 2025
if (isset($_GET['Msg'])) {
  echo "<script>alert('".htmlspecialchars((string)$_GET['Msg'], ENT_QUOTES, 'UTF-8')."');</script>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Registro de servicio | KASU</title>
    <!-- Canonical y hreflang -->
  <link rel="canonical" href="https://kasu.com.mx/registro.php">
  <link rel="alternate" hreflang="es-MX" href="https://kasu.com.mx/prospectos.php">
  
  <!-- Viewport -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- SEO básico -->
  <meta name="description" content="Registra tu servicio KASU. Ingresa tu CURP, verifica tus datos y elige el producto.">
  <meta name="author" content="Erendida Itzel Castro Marquez; Jose Carlos Cabrera Monroy">
  <meta name="robots" content="index,follow,max-image-preview:large">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Comprar KASU">
  <meta property="og:title" content="Comprar KASU | plagina de compra de servicios">
  <meta property="og:description" content="Adquire tu servicio KASU con cobro a tu tarjeta de debito - credito, o paga en tiendas de convenciencia.">
  <meta property="og:url" content="https://kasu.com.mx/registro.php">
  <meta property="og:image" content="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <meta property="og:locale" content="es_MX">
  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Comprar KASU | plagina de compra de servicios">
  <meta name="twitter:description" content="Adquire tu servicio KASU con cobro a tu tarjeta de debito - credito, o paga en tiendas de convenciencia.">
  <meta name="twitter:image" content="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  
  <!-- Iconos -->
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <link rel="apple-touch-icon" sizes="180x180" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  
  <!-- CSS externo + local -->
  <link rel="stylesheet" href="assets/css/Compra.css?v=5">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">

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
  </style>
</head>
<body onload="localize()">
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

<section id="Formulario" class="container-fluid">
  <?php
    $imgByProd = ['Funerario'=>'Gastos-funerarios.png','Retiro'=>'Plan-Retiro-Privado.png','Seguridad'=>'Oficiales-Seguridad.png'];
    $Imagen = $imgByProd[$proName] ?? 'Registro-Servicio.png';
  ?>
  <div class="row no-gutter">
    <div class="col-md-6" >
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

        <div class="logo"><img src="assets/images/kasu_logo.jpeg" alt="KASU"></div>
        <h1 class="text-center">Registra tu servicio</h1>

        <!-- Precio -->
        <div id="preview-precio" style="display:none; margin:10px 0 18px 0">
          <h3 class="text-center">Precio de Contado: </h3>
          <h2 class="text-center" style="color:#37A80D"><span id="pp_monto">$ 0.00</span></h2>
        </div>
        <!-- Campos -->
        <div class="Formulario field-wrap">
          <?php if (!empty($IdProspecto)): ?>
            <input class="form-control mb12" type="text" id="CURP" name="ClaveCurp"
                  value="<?= htmlspecialchars((string)$IdProspecto, ENT_QUOTES, 'UTF-8') ?>" disabled>
            <input class="form-control mb12" type="email" name="Mail" placeholder="Correo electrónico" required
                  autocomplete="email" value="<?= htmlspecialchars((string)$Mail, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="IdProspecto" value="<?= htmlspecialchars((string)$IdProspecto, ENT_QUOTES, 'UTF-8') ?>">
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

          <input type="hidden" id="pp_prodTarifa" name="ProdTarifa" value="">
          <input type="hidden" id="pp_edad" name="Edad" value="">
          <input type="hidden" id="pp_costo" name="Costo" value="">
        </div>

        <!-- Selección de producto -->
        <div class="Botones" style="margin:10px 0">
          <label class="ProdCard <?= $proName==='Funerario' ? 'active' : '' ?>">
            <input type="radio" name="Producto" value="Funerario" <?= $proName==='Funerario' ? 'checked' : '' ?> required>
            <img src="assets/images/Index/funer.png" alt="Servicio Funerario KASU" style="width:60%">
            <div class="PTitle">Funerario</div>
          </label>

          <label class="ProdCard <?= $proName==='Retiro' ? 'active' : '' ?>">
            <input type="radio" name="Producto" value="Retiro" <?= $proName==='Retiro' ? 'checked' : '' ?> required>
            <img src="assets/images/Index/retiro.png" alt="Plan Retiro KASU" style="width:60%">
            <div class="PTitle">Retiro</div>
          </label>
        </div>

        <!-- Tipo de servicio (solo Funerario) -->
        <div id="tipo-servicio-wrap" class="field-wrap" style="display:none; margin-top:10px;">
          <label>Selecciona el tipo de servicio</label>
          <select class="form-control" name="TipoServicio" id="TipoServicio" disabled>
            <option value="Tradicional">Tradicional</option>
            <option value="Cremacion">Cremación</option>
            <option value="Ecologico">Ecológico</option>
          </select>
        </div>

        <div class="field-wrap" style="padding-top: 10px; padding-bottom: 10px;">
          <ul>
            <li>
              <input type="checkbox" name="Terminos" required><strong>Acepto</strong> los <a href="/terminos-y-condiciones.php" target="_blank" rel="noopener">Términos y condiciones</a>
            </li>
            <li>
              <input type="checkbox" name="Aviso" required><strong>Acepto</strong> el <a href="/privacidad.php" target="_blank" rel="noopener">Aviso de Privacidad</a>
            </li>
            <li>          
              <input type="checkbox" name="Fideicomiso" required><strong>Acepto</strong> los terminos del <a href="/Fideicomiso_F0003.pdf" target="_blank" rel="noopener">Fideicomiso F/0003</a>
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
/* ===== Front ===== */
const reCURP = /^[A-Z]{4}\d{6}[HM][A-Z]{2}[A-Z]{3}[A-Z0-9]{2}$/;
const $curp  = document.getElementById('CURP');

function selProducto(){
  const r = document.querySelector('input[name="Producto"]:checked');
  return r ? r.value : '';
}
function formato(n){
  try { return new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN'}).format(+n); }
  catch(_){ return '$ ' + Number(n||0).toFixed(2); }
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

// Validación CURP
function validateCurp() {
  const v = ($curp.value||'').toUpperCase().replace(/[^A-Z0-9]/g,'');
  $curp.value = v;
  const ok = v.length === 18 && reCURP.test(v);
  $curp.classList.toggle('is-invalid', !ok);
  $curp.classList.toggle('is-valid', ok);
  $curp.setCustomValidity(ok ? '' : 'CURP incompleta o inválida');
  $curp.setAttribute('aria-invalid', ok ? 'false' : 'true');
  return ok;
}

// Lookup opcional por CURP
async function curpLookup(curp){
  try{
    const r = await fetch('registro.php?action=curp_lookup&curp='+encodeURIComponent(curp));
    const j = await r.json();
    if(j.ok){
      const N = document.getElementById('Nombre');
      const P = document.getElementById('ApPaterno');
      const M = document.getElementById('ApMaterno');
      if (N) N.value = j.data.Nombre || '';
      if (P) P.value = j.data.ApPaterno || '';
      if (M) M.value = j.data.ApMaterno || '';
    }
  }catch(e){}
}

// Cotización + único select de plazos
async function cotizar(){
  const priceWrap = document.getElementById('preview-precio');
  if (!validateCurp()) { if (priceWrap) priceWrap.style.display='none'; return; }

  const curp = $curp.value;
  const prod = selProducto();
  if (!prod){ if(priceWrap) priceWrap.style.display='none'; return; }

  try{
    const url='registro.php?action=price_quote&curp='+encodeURIComponent(curp)+'&producto='+encodeURIComponent(prod);
    const res=await fetch(url,{cache:'no-store'});
    const j=await res.json();
    if(!j.ok){ if(priceWrap) priceWrap.style.display='none'; return; }

    const d=j.data||{};
    document.getElementById('pp_monto').textContent = formato(d.costo||0);
    document.getElementById('pp_costo').value       = d.costo||0;
    document.getElementById('pp_prodTarifa').value  = d.prodTarifa||'';
    document.getElementById('pp_edad').value        = d.edad||'';

    const $plazo     = document.getElementById('pp_plazo');
    const $plazoWrap = document.getElementById('pp_plazo_wrap');
    while($plazo.firstChild) $plazo.removeChild($plazo.firstChild);

    let o = document.createElement('option');
    o.value = '1';
    o.textContent = 'Pago de Contado';
    $plazo.appendChild(o);

    const plz = d.plazos || {};
    Object.keys(plz).forEach(k=>{
      const opt = document.createElement('option');
      opt.value = k;
      opt.textContent = plz[k];
      $plazo.appendChild(opt);
    });

    if ($plazoWrap) $plazoWrap.style.display = '';
    if (priceWrap) priceWrap.style.display='';
  }catch(e){
    if(priceWrap) priceWrap.style.display='none';
  }
}

// Eventos UI
document.addEventListener('click', e => {
  const label = e.target.closest('.ProdCard'); if (!label) return;
  document.querySelectorAll('.ProdCard').forEach(x=>x.classList.remove('active'));
  label.classList.add('active');
  const r = label.querySelector('input[type=radio]'); if (r) r.checked = true;
  toggleTipoServicio();
  cotizar();
});

$curp.addEventListener('input', () => {
  const ok = validateCurp();
  if (ok) cotizar();
});

$curp.addEventListener('blur', () => {
  const ok = validateCurp();
  if (!ok) return;
  curpLookup($curp.value);
  cotizar();
});

document.addEventListener('DOMContentLoaded', function(){
  toggleTipoServicio();
});
</script>

<script>
(function(){
  var gpsDiv = document.getElementById('Gps');
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
})();
</script>
<script>
(function () {
  // Usa tus funciones existentes
  const reCURP = /^[A-Z]{4}\d{6}[HM][A-Z]{2}[A-Z]{3}[A-Z0-9]{2}$/;

  function ensureHidden(name, value) {
    if (!value) return;
    const form = document.querySelector('form[action*="Registrar_Venta.php"]') || document.querySelector('form');
    if (!form) return;
    if (!form.querySelector('input[name="'+name+'"]')) {
      const h = document.createElement('input');
      h.type = 'hidden';
      h.name = name;
      h.value = value;
      form.appendChild(h);
    }
  }

  function bootPrefill() {
    const curpEl = document.getElementById('CURP');
    if (!curpEl) return;

    // 1) Si viene ?idp= añade hidden IdProspecto
    const qs = new URLSearchParams(location.search);
    const idp = qs.get('idp');
    if (idp) ensureHidden('IdProspecto', idp);

    // 2) Normaliza CURP visible y crea hidden ClaveCurp si el input está deshabilitado
    let v = (curpEl.value || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
    curpEl.value = v;
    if (curpEl.disabled) ensureHidden('ClaveCurp', v);

    // 3) Si hay CURP válida, forza producto y cotiza
    if (v.length === 18 && reCURP.test(v)) {
      // Si no hay selección de producto, marca Funerario
      if (!document.querySelector('input[name="Producto"]:checked')) {
        const def = document.querySelector('input[name="Producto"][value="Funerario"]');
        if (def) { def.checked = true; def.closest('.ProdCard')?.classList.add('active'); }
      }

      // Muestra select de tipo servicio si aplica y cotiza
      if (typeof toggleTipoServicio === 'function') toggleTipoServicio();
      if (typeof curpLookup === 'function')        curpLookup(v);
      if (typeof cotizar === 'function')           cotizar();

      // Asegura que el bloque de precio quede visible
      const pv = document.getElementById('preview-precio');
      if (pv) pv.style.display = '';
    }
  }

  // Ejecuta tras cargar DOM
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootPrefill);
  } else {
    bootPrefill();
  }
})();
</script>

<script src="eia/javascript/finger.js?v=3"></script>
<script src="eia/javascript/localize.js?v=3"></script>
</body>
</html>