<?php
declare(strict_types=1);
/*******************************************************************************************
 * Constructor social + logger lite (registra FingerPrint antes de redirigir)
 *******************************************************************************************/
session_start();
require_once __DIR__.'/eia/librerias.php';

/* ===== RUTA POST: logger ===== */
if (($_POST['action'] ?? '') === 'log') {
  header('Content-Type: application/json; charset=UTF-8');

  // Canal por referrer / UA / query
  $qstr = (string)($_POST['qstr'] ?? '');
  $ref  = strtolower((string)($_POST['ref'] ?? ''));
  $ua   = strtolower((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
  $canal = 'direct';
  if (strpos($qstr,'fbclid=')!==false || str_contains($ref,'facebook') || str_contains($ua,'facebook')) $canal='facebook';
  elseif (str_contains($qstr,'li_fat_id=') || str_contains($ref,'linkedin') || str_contains($ua,'linkedin')) $canal='linkedin';
  elseif (str_contains($ref,'t.co') || str_contains($ref,'twitter') || str_contains($ua,'twitter')) $canal='twitter';

  // 1) FingerPrint idempotente
  $fpVal = (string)($_POST['fingerprint'] ?? '');
  $fpId  = null;
  if ($fpVal !== '') {
    $fpId = $basicas->BuscarCampos($mysqli, "id", "FingerPrint", "fingerprint", $fpVal);
    if (empty($fpId)) {
      $datFinger = [
        "fingerprint"   => $fpVal,
        "browser"       => (string)($_POST['browser']       ?? ''),
        "flash"         => (string)($_POST['flash']         ?? ''),
        "canvas"        => (string)($_POST['canvas']        ?? ''),
        "connection"    => (string)($_POST['connection']    ?? ''),
        "cookie"        => (string)($_POST['cookie']        ?? ''),
        "display"       => (string)($_POST['display']       ?? ''),
        "fontsmoothing" => (string)($_POST['fontsmoothing'] ?? ''),
        "fonts"         => (string)($_POST['fonts']         ?? ''),
        "formfields"    => (string)($_POST['formfields']    ?? ''),
        "java"          => (string)($_POST['java']          ?? ''),
        "language"      => (string)($_POST['language']      ?? ''),
        "silverlight"   => (string)($_POST['silverlight']   ?? ''),
        "os"            => (string)($_POST['os']            ?? ''),
        "timezone"      => (string)($_POST['timezone']      ?? ''),
        "touch"         => (string)($_POST['touch']         ?? ''),
        "truebrowser"   => (string)($_POST['truebrowser']   ?? ''),
        "plugins"       => (string)($_POST['plugins']       ?? ''),
        "useragent"     => (string)($_POST['useragent']     ?? '')
      ];
      $fpId = $basicas->InsertCampo($mysqli, "FingerPrint", $datFinger);
    }
  }

  // 2) Evento
  $evt = [
    "IdFInger"   => $fpId,
    "Evento"     => "Tarjeta/$canal",
    "Host"       => (string)($_POST['Host'] ?? ''),
    "MetodGet"   => $qstr,
    "Usuario"    => isset($_SESSION['Vendedor']) ? (string)$_SESSION['Vendedor'] : 'Cupones',
    "IdUsr"      => (string)($_POST['Usuario'] ?? ''),
    "connection" => (string)($_POST['connection'] ?? ''),
    "timezone"   => (string)($_POST['timezone']   ?? ''),
    "touch"      => (string)($_POST['touch']      ?? ''),
    "Cupon"      => (string)($_POST['Cupon']      ?? ''),
    "FechaRegistro" => date('Y-m-d H:i:s')
  ];
  $evtId = $basicas->InsertCampo($mysqli, "Eventos", $evt);

  echo json_encode(["ok"=>true,"evento_id"=>$evtId,"fp_id"=>$fpId]);
  exit;
}

/* ===== RUTA GET: vista + OG ===== */
$datafb = (string)(filter_input(INPUT_GET,'datafb',FILTER_UNSAFE_RAW) ?? '');
if ($datafb==='') { http_response_code(400); exit('Parámetro datafb faltante.'); }

$decoded = base64_decode($datafb, true);
if ($decoded===false) { http_response_code(400); exit('Contenido datafb inválido.'); }

$parts = explode('|', $decoded, 2);
if (!isset($parts[0],$parts[1])) { http_response_code(400); exit('Estructura inválida.'); }

$IdPost = (int)$parts[0];
$IdUsr  = (string)$parts[1];
$_SESSION['tarjeta'] = $IdPost;
$_SESSION['IdUsr']   = $IdUsr;

$Reg = null;
if ($st=$mysqli->prepare("SELECT Id,Img,TitA,DesA,Dire,Red,Tipo FROM PostSociales WHERE Status=1 AND Id=? LIMIT 1")) {
  $st->bind_param('i',$IdPost); $st->execute();
  $res=$st->get_result(); $Reg=$res?$res->fetch_assoc():null; $st->close();
}
if(!$Reg){ http_response_code(404); exit('Tarjeta no encontrada.'); }

$img = ($Reg['Tipo']==='Art')
  ? (string)($Reg['Img'] ?? '')
  : ((($Reg['Img']??'') && !preg_match('~^https?://~i',$Reg['Img']))
      ? "https://kasu.com.mx/assets/images/cupones/".ltrim($Reg['Img'],'/')
      : (string)($Reg['Img'] ?? ''));

$titulo = htmlspecialchars((string)($Reg['TitA'] ?? 'KASU'), ENT_QUOTES, 'UTF-8');
$descr  = htmlspecialchars((string)($Reg['DesA'] ?? ''),   ENT_QUOTES, 'UTF-8');
$dest   = (string)($Reg['Dire'] ?? 'https://www.kasu.com.mx');
$self   = 'https://kasu.com.mx/constructor.php?datafb='.rawurlencode($datafb);

$ua = strtolower((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
$isBot = (bool)preg_match('/facebookexternalhit|twitterbot|linkedinbot|slackbot|whatsapp|telegram|bot|crawler|spider/i',$ua);
?>
<!doctype html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <title><?= $titulo ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <link rel="icon" href="/assets/images/kasu_logo.jpeg">

  <!-- OG / Twitter -->
  <meta property="og:url" content="<?= htmlspecialchars($self,ENT_QUOTES) ?>">
  <meta property="og:type" content="article">
  <meta property="og:title" content="<?= $titulo ?>">
  <meta property="og:description" content="<?= $descr ?>">
  <meta property="og:image" content="<?= htmlspecialchars($img,ENT_QUOTES) ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= $titulo ?>">
  <meta name="twitter:description" content="<?= $descr ?>">
  <meta name="twitter:image" content="<?= htmlspecialchars($img,ENT_QUOTES) ?>">

  <style>
    :root{color-scheme:light dark}
    body{margin:0;background:#0b0b12;color:#eaeaf2;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial,sans-serif}
    .wrap{min-height:100dvh;display:grid;place-items:center;padding:12px}
    .card{width:min(900px,96%);background:#141428;border-radius:16px;box-shadow:0 10px 28px rgba(0,0,0,.45);overflow:hidden}
    .img{width:100%;aspect-ratio:1200/630;object-fit:cover;background:#111;display:block}
    .txt{padding:14px 16px}
    .t{margin:0 0 6px 0;font-size:20px}
    .d{margin:0;font-size:14px;opacity:.9}
    .cta{display:inline-block;margin:12px 16px 16px;padding:10px 14px;border-radius:10px;background:#6b5bff;color:#fff;text-decoration:none}
    .hint{margin:0 16px 16px;font-size:12px;opacity:.7}
    form#fpform{display:none}
  </style>
</head>
<body>
  <div class="wrap">
    <article class="card">
      <img class="img" src="<?= htmlspecialchars($img,ENT_QUOTES) ?>" alt="<?= $titulo ?>">
      <div class="txt">
        <h1 class="t"><?= $titulo ?></h1>
        <p class="d"><?= $descr ?></p>
      </div>
      <a class="cta" href="<?= htmlspecialchars($dest,ENT_QUOTES) ?>" style="display: none;">Abrir</a>
      <p class="hint" style="text-alitext-align:">Te estamos redirigiendo a la pagina .</p>
    </article>
  </div>

  <!-- Form oculto que finger.js completa -->
  <form id="fpform">
    <?php
      // Campos que suele llenar finger.js
      foreach ([
        'fingerprint','browser','flash','canvas','connection','cookie','display',
        'fontsmoothing','fonts','formfields','java','language','silverlight','os',
        'timezone','touch','truebrowser','plugins','useragent'
      ] as $n) {
        echo '<input type="hidden" name="'.$n.'" value="">';
      }
    ?>
  </form>

  <?php if (!$isBot): ?>
  <script defer src="/eia/javascript/finger.js"></script>
  <script>
  (function(){
    const endpoint = '<?= htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES) ?>';
    const redirectTo = '<?= htmlspecialchars($dest,ENT_QUOTES) ?>';
    const base = {
      action: 'log',
      Cupon: '<?= (int)$IdPost ?>',
      Usuario: '<?= htmlspecialchars($IdUsr,ENT_QUOTES) ?>',
      Host: '<?= htmlspecialchars($dest,ENT_QUOTES) ?>',
      qstr: location.search || '',
      ref: document.referrer || ''
    };

    function getVal(n){ const el=document.querySelector('input[name="'+n+'"]'); return el?el.value:''; }

    function ensureFingerprint(){
      let fp = getVal('fingerprint');
      if (!fp) {
        try {
          const tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
          const raw = [navigator.userAgent, screen.width+'x'+screen.height, tz, Date.now()].join('|');
          fp = btoa(unescape(encodeURIComponent(raw))).slice(0,32);
          const el = document.querySelector('input[name="fingerprint"]');
          if (el) el.value = fp;
        } catch(_){}
      }
    }

    function sendLog(){
      ensureFingerprint();
      const fd = new FormData();
      Object.entries(base).forEach(([k,v]) => fd.append(k,v));
      // copiar todos los inputs del form
      document.querySelectorAll('#fpform input[name]').forEach(i => fd.append(i.name, i.value||''));

      if (navigator.sendBeacon) {
        const usp = new URLSearchParams();
        for (const [k,v] of fd.entries()) usp.append(k,v);
        const blob = new Blob([usp.toString()], {type:'application/x-www-form-urlencoded;charset=UTF-8'});
        navigator.sendBeacon(endpoint, blob);
      } else {
        fetch(endpoint, {method:'POST', body:fd, keepalive:true}).catch(()=>{});
      }
    }

    // Espera a que finger.js escriba, si no, entra el fallback.
    let tries = 0;
    function waitFP(){
      const has = !!getVal('fingerprint');
      if (has || tries++ > 20) {
        sendLog();
        setTimeout(()=>location.replace(redirectTo), 600);
        return;
      }
      setTimeout(waitFP, 60);
    }
    document.addEventListener('DOMContentLoaded', waitFP);
  })();
  </script>
  <?php else: ?>
  <noscript><meta http-equiv="refresh" content="2;url=<?= htmlspecialchars($dest,ENT_QUOTES) ?>"></noscript>
  <?php endif; ?>
</body>
</html>
