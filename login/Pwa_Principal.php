<?php
/********************************************************************************************
 * Qué hace: Renderiza la pantalla de inicio PWA de KASU para el vendedor autenticado.
 *           Carga datos del empleado, metas/KPIs, foto de perfil y menús. Incluye PWA helpers.
 * Fecha: 06/12/2025
 * Revisado por: JCCM
 * Archivo: Pwa_Principal.php
 ********************************************************************************************/

declare(strict_types=1);

/* ==========================================================================================
 * BLOQUE: Sesión y dependencias
 * ========================================================================================== */
require_once dirname(__DIR__) . '/eia/session.php';
kasu_session_start();
require_once __DIR__ . '/../eia/librerias.php';
require_once __DIR__ . '/php/Analisis_Metas.php';
date_default_timezone_set('America/Mexico_City');

/* ==========================================================================================
 * BLOQUE: Autenticación requerida
 * ========================================================================================== */
if (!isset($_SESSION['Vendedor']) || $_SESSION['Vendedor'] === '') {
    header('Location: https://kasu.com.mx/login/');
    exit();
}
$Vend = (string)$_SESSION['Vendedor'];

/* ==========================================================================================
 * BLOQUE: Foto de perfil
 * ========================================================================================== */
$docRoot    = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), DIRECTORY_SEPARATOR);
$fsDir      = $docRoot . '/login/assets/img/perfil/';
$publicBase = '/login/assets/img/perfil/';
$profileUrl = $publicBase . 'default.jpg';

$VendId = (int)$basicas->BuscarCampos($mysqli, 'Id', 'Empleados', 'IdUsuario', $Vend);

// Detectar si existe la columna Foto en Empleados
$colFotoExists = false;
if ($result = $mysqli->query("SHOW COLUMNS FROM Empleados LIKE 'Foto'")) {
    if ($result->num_rows > 0) {
        $colFotoExists = true;
    }
    $result->close();
}

$last = '';
if ($colFotoExists) {
    $tmp = $basicas->BuscarCampos($mysqli, 'Foto', 'Empleados', 'Id', $VendId);
    if ($tmp !== null) {
        $last = (string)$tmp;
    }
}

// 1) Archivo definido en BD
if ($last !== '' && is_file($fsDir . $last)) {
    $profileUrl = $publicBase . $last . '?v=' . filemtime($fsDir . $last);
} else {
    // 2) Fallback: archivo más reciente IdEmpleado_*.jpg
    $pattern = $fsDir . $VendId . '_*.jpg';
    $matches = glob($pattern) ?: [];
    if (!empty($matches)) {
        usort($matches, static function (string $a, string $b): int {
            return filemtime($b) <=> filemtime($a);
        });
        $fname      = basename($matches[0]);
        $profileUrl = $publicBase . $fname . '?v=' . filemtime($matches[0]);
    }
}

// 3) Cache bust adicional desde sesión
if (!empty($_SESSION['FotoCacheBust'])) {
    $separator  = str_contains($profileUrl, '?') ? '&' : '?';
    $profileUrl .= $separator . 'cb=' . rawurlencode((string)$_SESSION['FotoCacheBust']);
    unset($_SESSION['FotoCacheBust']);
}

/* ==========================================================================================
 * BLOQUE: Datos del usuario para cabecera
 * ========================================================================================== */
$SL1         = (string)$basicas->BuscarCampos($mysqli, 'Nombre',         'Empleados', 'IdUsuario', $Vend);
$NivRaw      = $basicas->BuscarCampos($mysqli, 'Nivel',          'Empleados', 'IdUsuario', $Vend);
$suc         = (int)$basicas->BuscarCampos($mysqli, 'Sucursal',       'Empleados', 'IdUsuario', $Vend);
$su2         = (string)$basicas->BuscarCampos($mysqli, 'nombreSucursal', 'Sucursal',  'Id',        $suc);
$nombreNivel = (string)$basicas->BuscarCampos($mysqli, 'NombreNivel',    'Nivel',     'Id',        $NivRaw);
$Niv         = (int)$NivRaw;

/* ==========================================================================================
 * BLOQUE: Defaults de variables de metas/KPIs
 * ========================================================================================== */
$spv       = isset($spv)       ? (string)$spv       : '#2e7d32';
$bxo       = isset($bxo)       ? (string)$bxo       : '#1565c0';
$ComGenHoy = isset($ComGenHoy) ? (float)$ComGenHoy  : 0.0;
$AvCob     = isset($AvCob)     ? (float)$AvCob      : 0.0;
$MetaCob   = isset($MetaCob)   ? (float)$MetaCob    : 0.0;
$CobHoy    = isset($CobHoy)    ? (float)$CobHoy     : 0.0;
$MetaVta   = isset($MetaVta)   ? (float)$MetaVta    : 0.0;
$AvVtas    = isset($AvVtas)    ? (float)$AvVtas     : 0.0;
$VtasHoy   = isset($VtasHoy)   ? (float)$VtasHoy    : 0.0;
$PolizasMes   = isset($PolizasMes)   ? (int)$PolizasMes   : 0;
$MetaPolizas  = isset($MetaPolizas)  ? (float)$MetaPolizas : 0.0;
$CobranzaPlataforma   = isset($CobranzaPlataforma)   ? (float)$CobranzaPlataforma   : 0.0;
$CobranzaMpPendiente  = isset($CobranzaMpPendiente)  ? (float)$CobranzaMpPendiente  : 0.0;
$CobranzaSucursales   = isset($CobranzaSucursales)   ? (float)$CobranzaSucursales   : (float)$CobHoy;

if (!function_exists('kasu_fmt_moneda')) {
    function kasu_fmt_moneda(float $value): string {
        return '$' . number_format($value, 2, '.', ',');
    }
}
if (!function_exists('kasu_progress_class')) {
    function kasu_progress_class(float $pct): string {
        if ($pct >= 100) return 'bg-success';
        if ($pct >= 75)  return 'bg-info';
        if ($pct >= 50)  return 'bg-warning';
        return 'bg-danger';
    }
}

// Cache-busting para CSS si $VerCache no está definido
$VerCacheSafe = isset($VerCache) ? (string)$VerCache : '1';

$ini = (new DateTime('first day of this month'))->format('d/m/Y');
$fin = (new DateTime('last day of this month'))->format('d/m/Y');
$mesActualIni = date('Y-m-01');
$hoyStr       = date('Y-m-d');

$cobranzaPct = ($MetaCob > 0)
  ? min(100.0, round(($CobHoy / $MetaCob) * 100, 1))
  : 0.0;

$ventasPct   = ($MetaVta > 0)
  ? min(100.0, round(($VtasHoy / $MetaVta) * 100, 1))
  : 0.0;

$polizasPct  = ($MetaPolizas > 0)
  ? min(100.0, round(($PolizasMes / max(1.0, $MetaPolizas)) * 100, 1))
  : 0.0;

$metaCobLabel = $MetaCob     > 0 ? kasu_fmt_moneda($MetaCob)       : 'Sin meta';
$metaVtaLabel = $MetaVta     > 0 ? kasu_fmt_moneda($MetaVta)       : 'Sin meta';
$metaPzaLabel = $MetaPolizas > 0 ? number_format((int)$MetaPolizas) . ' pólizas' : 'Configura tu meta';

$cobradoLabel = kasu_fmt_moneda($CobHoy);
$ventasLabel  = kasu_fmt_moneda($VtasHoy ?? 0.0);

$pendCobranza = $MetaCob     > 0 ? max(0.0, $MetaCob - $CobHoy)          : 0.0;
$pendVentas   = $MetaVta     > 0 ? max(0.0, $MetaVta - $VtasHoy)         : 0.0;
$pendPolizas  = $MetaPolizas > 0 ? max(0, (int)$MetaPolizas - $PolizasMes) : 0;

$saludo = 'Hola';
$hora   = (int)date('H');
if ($hora >= 6 && $hora < 12) {
    $saludo = 'Buenos días';
} elseif ($hora >= 12 && $hora < 19) {
    $saludo = 'Buenas tardes';
} else {
    $saludo = 'Buenas noches';
}

?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F1F7FC">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>KASU · Inicio</title>

  <!-- PWA / iOS -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">

  <!-- CSS externos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <!-- CSS principal PWA -->
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= htmlspecialchars($VerCacheSafe, ENT_QUOTES) ?>">
  <link rel="stylesheet" href="assets/css/Grafica.css">

  <!-- Google Charts -->
  <script src="https://www.gstatic.com/charts/loader.js"></script>
  <!-- jQuery -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

  <style>
    /* Shell general tipo macOS/iOS: fondo muy limpio, tarjetas glass */
    body{
      margin:0;
      font-family:"Inter","SF Pro Display","Segoe UI",system-ui,-apple-system,sans-serif;
      background:#F1F7FC;
      color:#0f172a;
    }

    .dashboard-shell{
      max-width:1100px;
      margin:0 auto;
      padding: calc(var(--topbar-h) + var(--safe-t) + 3=6px) 16px
               calc(max(var(--bottombar-h), calc(var(--icon) + 2*var(--pad-v))) + max(var(--safe-b), 8px) + 4px);
    }

    /* Topbar clara, forzando sobre cualquier regla previa */
    .topbar{
      backdrop-filter: blur(12px);
      background:#F9FAFE !important;
      border-bottom:1px solid rgba(15,23,42,.05);
      color:#0f172a !important;
    }
    .topbar .logo-wordmark{
      display:flex;
      align-items:center;
      gap:8px;
    }
    .topbar .logo-wordmark img{
      height:30px;
      border-radius:8px;
    }
    .topbar .logo-wordmark span{
      font-weight:700;
      letter-spacing:.03em;
      font-size:.9rem;
      text-transform:uppercase;
      color:#4b5563;
    }

    /* Tarjeta hero: saludo + perfil + resumen rápido */
    .hero-card{
      position:relative;
      display:grid;
      grid-template-columns: minmax(0,1.6fr) minmax(0,1.2fr);
      gap:18px;
      padding:18px 18px 16px;
      border-radius:22px;
      background:linear-gradient(135deg, rgba(255,255,255,.92), rgba(241,247,252,.98));
      box-shadow:0 24px 60px rgba(15,23,42,.12);
      border:1px solid rgba(148,163,184,.28);
      margin-bottom:20px;
    }

    @media (max-width:768px){
      .hero-card{
        grid-template-columns: minmax(0,1fr);
      }
    }

    .hero-main-title{
      font-size:1.5rem;
      font-weight:800;
      margin:0 0 4px;
      letter-spacing:.02em;
    }
    .hero-subtitle{
      margin:0;
      font-size:.93rem;
      color:#6b7280;
    }
    .hero-meta{
      display:flex;
      flex-wrap:wrap;
      gap:12px;
      margin-top:14px;
    }
    .hero-pill{
      padding:6px 12px;
      border-radius:999px;
      font-size:.8rem;
      background:rgba(15,111,240,.06);
      color:#0f172a;
      border:1px solid rgba(148,163,184,.28);
    }
    .hero-pill strong{
      font-weight:600;
    }

    .hero-kpi-small{
      margin-top:14px;
      display:flex;
      gap:16px;
      flex-wrap:wrap;
      font-size:.8rem;
      color:#6b7280;
    }
    .hero-kpi-small span{
      white-space:nowrap;
    }

    .hero-secondary{
      display:flex;
      align-items:center;
      justify-content:flex-end;
      gap:16px;
      flex-wrap:wrap;
    }

    .hero-avatar-wrapper{
      display:flex;
      flex-direction:column;
      align-items:center;
      gap:10px;
    }

    .hero-avatar{
      position:relative;
      width:116px;
      height:116px;
      border-radius:30px;
      padding:4px;
      background:linear-gradient(145deg,#e0edff,#fef9c3);
      box-shadow:0 18px 40px rgba(15,23,42,.24);
    }
    .hero-avatar-inner{
      width:100%;
      height:100%;
      border-radius:26px;
      overflow:hidden;
      background:#f8fafc;
    }
    .hero-avatar-inner img{
      width:100%;
      height:100%;
      object-fit:cover;
    }

    .hero-avatar-badge{
      position:absolute;
      right:-6px;
      bottom:-6px;
      width:32px;
      height:32px;
      border-radius:999px;
      background:#0f6ef0;
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-size:1rem;
      box-shadow:0 12px 26px rgba(15,111,240,.6);
    }

    .hero-actions{
      display:flex;
      justify-content:flex-end;
      margin-bottom:10px;
    }

    /* Botón instalar: oculto por defecto, visible solo cuando JS lo marque */
    .btn-install{
      display:none; /* se mostrará solo con .is-visible desde JS */
      position:relative;
      align-items:center;
      gap:8px;
      border-radius:14px;
      border:none;
      padding:10px 14px;
      font-size:.82rem;
      background:#0f172a;
      color:#f9fafb;
      box-shadow:0 18px 40px rgba(15,23,42,.55);
      white-space:nowrap;
    }
    .btn-install i{
      font-size:1rem;
    }
    .btn-install.is-visible{
      display:inline-flex;
    }

    /* Tarjetas KPIs */
    .kpi-grid{
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
      gap:16px;
      margin-bottom:22px;
    }

    .kpi-card{
      position:relative;
      border-radius:20px;
      padding:16px 16px 14px;
      background:rgba(255,255,255,.92);
      backdrop-filter:blur(18px);
      box-shadow:0 20px 45px rgba(15,23,42,.12);
      border:1px solid rgba(226,232,240,.9);
    }

    .kpi-chip{
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding:4px 9px;
      border-radius:999px;
      font-size:.72rem;
      text-transform:uppercase;
      letter-spacing:.1em;
      background:rgba(15,111,240,.06);
      color:#0f6ef0;
      border:1px solid rgba(191,219,254,.9);
      margin-bottom:6px;
    }

    .kpi-title{
      font-size:.95rem;
      font-weight:700;
      margin:0 0 2px;
      color:#0f172a;
    }
    .kpi-meta{
      font-size:.8rem;
      color:#6b7280;
      margin:0 0 6px;
    }

    .kpi-main-value{
      font-size:1.3rem;
      font-weight:800;
      margin:0;
    }
    .kpi-main-sub{
      font-size:.78rem;
      color:#6b7280;
    }

    .kpi-main-row{
      display:flex;
      justify-content:space-between;
      align-items:flex-end;
      gap:12px;
      margin-top:4px;
      margin-bottom:6px;
    }

    .kpi-progress{
      margin-top:6px;
    }
    .kpi-progress .progress{
      height:6px;
      border-radius:999px;
      background:#e5e7eb;
      overflow:hidden;
    }
    .kpi-progress .progress-bar{
      border-radius:999px;
    }
    .kpi-footer{
      margin-top:8px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      font-size:.78rem;
      color:#6b7280;
    }

    .kpi-link{
      font-weight:600;
      text-decoration:none;
      color:#0f6ef0;
    }
    .kpi-link:hover{
      text-decoration:none;
      color:#0c5ad1;
    }

    .badge-soft{
      padding:3px 9px;
      border-radius:999px;
      font-size:.72rem;
      background:#f3f4ff;
      color:#4b5563;
    }

    /* Contenedor de gráfica */
    .chart-card{
      border-radius:22px;
      background:rgba(255,255,255,.9);
      border:1px solid rgba(226,232,240,.9);
      box-shadow:0 20px 50px rgba(15,23,42,.12);
      padding:16px 14px 10px;
      margin-bottom:22px;
    }
    .chart-card-header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:8px;
    }
    .chart-title{
      margin:0;
      font-size:.92rem;
      font-weight:700;
      color:#111827;
    }
    .chart-subtitle{
      margin:0;
      font-size:.78rem;
      color:#6b7280;
    }

    .chart-range-pill{
      padding:3px 8px;
      border-radius:999px;
      font-size:.72rem;
      background:#f3f4ff;
      color:#4b5563;
    }

    .Grafica{
      width:100%;
      min-height:220px;
    }

    .dashboard-row{
      display:grid;
      grid-template-columns: minmax(0,1.4fr) minmax(0,1.6fr);
      gap:18px;
    }
    @media (max-width:992px){
      .dashboard-row{
        grid-template-columns:minmax(0,1fr);
      }
    }

    /* Perfil integrado */
    .dpersonales{
      padding:0;
      margin-bottom:0;
    }
    .imgPerfil{
      position:relative;
      width:88px;
      height:auto;
      flex-shrink:0;
    }
    .imgPerfil img{
      height:auto;
      width:100%;
      border-radius:22px;
      border:none;
    }

    #FotoPerfil{
      border-radius:22px !important;
      border:none !important;
      padding:0 !important;
    }

    .Nombre{
      padding:0;
      text-align:left;
      transform:none;
    }
    .Nombre p{
      margin:0;
    }
    .Nombre p:first-child{
      font-weight:700;
      font-size:1rem;
    }
    .Nombre p:last-child{
      font-size:.82rem;
      color:#6b7280;
    }

    .dashboard-meta-bar{
      display:flex;
      flex-wrap:wrap;
      gap:10px;
      margin-top:8px;
      font-size:.78rem;
      color:#6b7280;
    }
    .dashboard-meta-bar span{
      display:inline-flex;
      align-items:center;
      gap:5px;
    }
    .dot{
      width:8px;
      height:8px;
      border-radius:50%;
      background:#22c55e;
    }
  </style>
</head>
<body onload="localize()">
  <!-- TOP BAR -->
  <div class="topbar">
    <div class="logo-wordmark">
      <img alt="KASU" src="/login/assets/img/logoKasu.png">
      <span>KASU VENTAS</span>
    </div>
  </div>

  <!-- Menú inferior fijo -->
  <section id="Menu">
    <?php require_once __DIR__ . '/html/Menuprinc.php'; ?>
  </section>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="page-content">
    <div class="dashboard-shell">

      <!-- Botón instalar (controlado por JS) -->
      <div class="hero-actions">
        <button class="btn-install" id="btnInstall">
          <i class="fa fa-download"></i>
          Instalar app
        </button>
      </div>

      <!-- HERO CARD -->
      <section class="hero-card">
        <div>
          <p class="hero-subtitle mb-1">
            <?= htmlspecialchars($saludo, ENT_QUOTES) ?>,
            <strong><?= htmlspecialchars($SL1, ENT_QUOTES) ?></strong>
          </p>
          <h1 class="hero-main-title">Tu resumen de hoy en KASU</h1>
          <p class="hero-subtitle">
            Del <?= htmlspecialchars($ini, ENT_QUOTES) ?> al <?= htmlspecialchars($fin, ENT_QUOTES) ?> ·
            <?= htmlspecialchars($su2, ENT_QUOTES) ?>
          </p>

          <div class="hero-meta">
            <div class="hero-pill">
              <strong><?= htmlspecialchars($nombreNivel, ENT_QUOTES) ?></strong>
              <span>· ID: <?= htmlspecialchars($Vend, ENT_QUOTES) ?></span>
            </div>
            <div class="hero-pill">
              <strong><?= number_format($PolizasMes) ?></strong>
              &nbsp;pólizas este mes
            </div>
            <div class="hero-pill">
              Cobranza: <?= number_format($cobranzaPct, 1) ?>%
            </div>
          </div>

          <div class="hero-kpi-small">
            <span>Ventas: <?= $ventasLabel ?> (<?= number_format($ventasPct, 1) ?>%)</span>
            <span>Pendiente de cobrar: <?= kasu_fmt_moneda($pendCobranza) ?></span>
          </div>
        </div>

        <div class="hero-secondary">
          <div class="hero-avatar-wrapper">
            <div class="hero-avatar">
              <div class="hero-avatar-inner">
                <img
                  src="<?= htmlspecialchars($profileUrl, ENT_QUOTES) ?>"
                  alt="Foto de perfil"
                  id="FotoPerfil">
              </div>
              <button
                type="button"
                class="hero-avatar-badge"
                id="btnFoto"
                aria-label="Actualizar foto">
                <i class="fa fa-camera"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Formulario de subida de foto -->
        <form
          id="perfilForm"
          method="POST"
          enctype="multipart/form-data"
          action="php/Funcionalidad_Pwa.php"
          class="perfil-uploader">
          <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">
          <input type="hidden" name="btnEnviar" value="1">
          <input class="d-none" type="file" id="subirImg" name="subirImg" accept="image/*">
        </form>
      </section>

      <!-- FILA PRINCIPAL: Gráfica + KPIs -->
      <section class="dashboard-row">
        <!-- Gráfica -->
        <div>
          <article class="chart-card">
            <header class="chart-card-header">
              <div>
                <h2 class="chart-title">Desempeño mensual</h2>
                <p class="chart-subtitle">Cobranza y ventas acumuladas</p>
              </div>
              <div class="chart-range-pill">
                Mes actual · <?= htmlspecialchars(date('M Y'), ENT_QUOTES) ?>
              </div>
            </header>
            <div class="Grafica" id="chart_container"></div>
          </article>
        </div>

        <!-- Tarjetas de KPIs -->
        <div>
          <div class="kpi-grid">
            <?php if ($Niv === 7 || $Niv === 6): ?>
              <article class="kpi-card">
                <div class="kpi-chip">
                  <i class="fa fa-line-chart"></i> Comisiones
                </div>
                <h3 class="kpi-title">Comisiones acumuladas</h3>
                <p class="kpi-meta">Actualizadas al <?= htmlspecialchars(date('d/m/Y'), ENT_QUOTES) ?></p>

                <div class="kpi-main-row">
                  <div>
                    <p class="kpi-main-value" style="color:<?= htmlspecialchars($spv, ENT_QUOTES) ?>;">
                      <?= kasu_fmt_moneda($ComGenHoy) ?>
                    </p>
                    <p class="kpi-main-sub">Sobre tus ventas y cobranza registradas.</p>
                  </div>
                  <span class="badge-soft">
                    Nivel <?= (int)$Niv ?>
                  </span>
                </div>

                <div class="kpi-footer">
                  <span>Consulta el detalle por póliza.</span>
                  <a href="Pwa_Registro_Pagos.php" class="kpi-link">Ver comisiones</a>
                </div>
              </article>
            <?php endif; ?>

            <!-- Cobranza -->
            <article class="kpi-card">
              <div class="kpi-chip">
                <i class="fa fa-credit-card"></i> Cobranza
              </div>
              <h3 class="kpi-title">Cobranza del mes</h3>
              <p class="kpi-meta">Meta: <?= htmlspecialchars($metaCobLabel, ENT_QUOTES) ?></p>

              <div class="kpi-main-row">
                <div>
                  <p class="kpi-main-value"><?= $cobradoLabel ?></p>
                  <p class="kpi-main-sub">Restan <?= kasu_fmt_moneda($pendCobranza) ?> para tu meta.</p>
                </div>
                <span class="badge-soft"><?= number_format($cobranzaPct, 1) ?>%</span>
              </div>

              <div class="kpi-progress">
                <div class="progress">
                  <div
                    class="progress-bar <?= kasu_progress_class($cobranzaPct) ?>"
                    role="progressbar"
                    style="width: <?= $cobranzaPct ?>%"
                    aria-valuenow="<?= $cobranzaPct ?>"
                    aria-valuemin="0"
                    aria-valuemax="100"></div>
                </div>
              </div>

              <div class="kpi-footer">
                <span>Registra tus depósitos y cobranza diaria.</span>
                <a href="Pwa_Registro_Pagos.php" class="kpi-link">Registrar cobro</a>
              </div>
            </article>

            <!-- Ventas -->
            <article class="kpi-card">
              <div class="kpi-chip">
                <i class="fa fa-file-text-o"></i> Ventas
              </div>
              <h3 class="kpi-title">Ventas del mes</h3>
              <p class="kpi-meta">Meta: <?= htmlspecialchars($metaVtaLabel, ENT_QUOTES) ?></p>

              <div class="kpi-main-row">
                <div>
                  <p class="kpi-main-value"><?= $ventasLabel ?></p>
                  <p class="kpi-main-sub">Restan <?= kasu_fmt_moneda($pendVentas) ?> en ventas.</p>
                </div>
                <span class="badge-soft"><?= number_format($ventasPct, 1) ?>%</span>
              </div>

              <div class="kpi-progress">
                <div class="progress">
                  <div
                    class="progress-bar <?= kasu_progress_class($ventasPct) ?>"
                    role="progressbar"
                    style="width: <?= $ventasPct ?>%"
                    aria-valuenow="<?= $ventasPct ?>"
                    aria-valuemin="0"
                    aria-valuemax="100"></div>
                </div>
              </div>

              <div class="kpi-footer">
                <span>Revisa a quién puedes volver a visitar.</span>
                <a href="Pwa_Clientes.php" class="kpi-link">Ver cartera</a>
              </div>
            </article>

            <!-- Pólizas -->
            <article class="kpi-card">
              <div class="kpi-chip">
                <i class="fa fa-shield"></i> Pólizas
              </div>
              <h3 class="kpi-title">Colocación de pólizas</h3>
              <p class="kpi-meta">Meta: <?= htmlspecialchars($metaPzaLabel, ENT_QUOTES) ?></p>

              <div class="kpi-main-row">
                <div>
                  <p class="kpi-main-value"><?= number_format($PolizasMes) ?> pólizas</p>
                  <?php if ($MetaPolizas > 0): ?>
                    <p class="kpi-main-sub">Restan <?= number_format(max(0, $pendPolizas)) ?> pólizas.</p>
                  <?php else: ?>
                    <p class="kpi-main-sub">Configura tu meta de pólizas con tu supervisor.</p>
                  <?php endif; ?>
                </div>
                <span class="badge-soft"><?= number_format($polizasPct, 1) ?>%</span>
              </div>

              <div class="kpi-progress">
                <div class="progress">
                  <div
                    class="progress-bar <?= kasu_progress_class($polizasPct) ?>"
                    role="progressbar"
                    style="width: <?= $polizasPct ?>%"
                    aria-valuenow="<?= $polizasPct ?>"
                    aria-valuemin="0"
                    aria-valuemax="100"></div>
                </div>
              </div>

              <div class="kpi-footer">
                <span>Da seguimiento a tus clientes.</span>
                <a href="Pwa_Clientes.php" class="kpi-link">Ver clientes</a>
              </div>
            </article>

            <!-- Panel extra para Dirección / CEO -->
            <?php if ($Niv === 1): ?>
              <article class="kpi-card">
                <div class="kpi-chip">
                  <i class="fa fa-university"></i> Cobranza global
                </div>
                <h3 class="kpi-title">Detalle de cobranza</h3>
                <p class="kpi-meta">Sucursales vs plataforma</p>

                <div class="kpi-main-row">
                  <div>
                    <p class="kpi-main-value"><?= kasu_fmt_moneda($CobranzaSucursales) ?></p>
                    <p class="kpi-main-sub">Sucursales</p>
                  </div>
                  <div class="text-right">
                    <p class="kpi-main-value"><?= kasu_fmt_moneda($CobranzaPlataforma) ?></p>
                    <p class="kpi-main-sub">Plataforma</p>
                  </div>
                </div>

                <div class="kpi-footer">
                  <span>Pendiente Mercado Pago: <?= kasu_fmt_moneda($CobranzaMpPendiente) ?></span>
                  <span class="badge-soft">Total: <?= kasu_fmt_moneda($CobHoy) ?></span>
                </div>
              </article>
            <?php endif; ?>
          </div>
        </div>
      </section>

    </div>
  </main>

  <!-- JS PWA / helpers -->
  <script src="Javascript/finger.js?v=3"></script>
  <script src="Javascript/localize.js?v=3"></script>
  <script src="Javascript/Inyectar_gps_form.js"></script>
  <script src="Javascript/perfil.js?v=<?= htmlspecialchars($VerCacheSafe, ENT_QUOTES) ?>"></script>

  <script>
    // Ocultar botón de instalación si ya estás en modo standalone (iOS/Android)
    document.addEventListener('DOMContentLoaded', function () {
      var btn = document.getElementById('btnInstall');
      if (!btn) return;

      var isStandalone =
        (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) ||
        (typeof window.navigator.standalone !== 'undefined' && window.navigator.standalone);

      if (isStandalone) {
        btn.style.display = 'none';
      }
      // Si tu /login/Javascript/install.js lo controla con clases (is-visible),
      // seguirá funcionado: este archivo solo asegura que nunca se muestre en standalone.
    });
  </script>
</body>
</html>
