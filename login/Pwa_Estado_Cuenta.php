<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/eia/session.php';
kasu_session_start();
require_once __DIR__ . '/../eia/librerias.php';
date_default_timezone_set('America/Mexico_City');
header_remove('X-Powered-By');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Sesión
if (empty($_SESSION['Vendedor'])) {
  header('Location: https://kasu.com.mx/login'); exit;
}

// GET requerido
if (!isset($_GET['busqueda'])) {
  http_response_code(400); exit('Falta parámetro.');
}
$dec = base64_decode($_GET['busqueda'], true);
if ($dec === false || !preg_match('/^\d+$/', $dec)) {
  http_response_code(400); exit('Parámetro inválido.');
}

$IdVenta = (int)$dec;

// Escapar rápido
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Cargar venta
$Reg = null;
$mysqli->set_charset('utf8mb4');
$stmt = $mysqli->prepare("SELECT * FROM Venta WHERE Id = ? LIMIT 1");
$stmt->bind_param('i', $IdVenta);
$stmt->execute();
$ventaRes = $stmt->get_result();
if ($ventaRes && $ventaRes->num_rows) {
  $Reg = $ventaRes->fetch_assoc();
} else {
  http_response_code(404); exit('Venta no encontrada.');
}
$stmt->close();

// Moneda MXN
$fmt = class_exists('NumberFormatter') ? new NumberFormatter('es_MX', NumberFormatter::CURRENCY) : null;
$money = function($n) use ($fmt){
  $n = (float)$n;
  return $fmt ? $fmt->formatCurrency($n, 'MXN') : ('$'.number_format($n,2,'.',','));
};

$VerCache = $VerCache ?? 1;
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F1F7FC">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Estado de Cuenta</title>

  <!-- PWA -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= h($VerCache) ?>">
  <link rel="stylesheet" href="assets/css/Grafica.css">
  <style>
    body{
      margin:0;
      font-family:"Inter","SF Pro Display","Segoe UI",system-ui,-apple-system,sans-serif;
      background:#F1F7FC;
      color:#0f172a;
    }
    .topbar{
      position:sticky;
      top:0;
      z-index:10;
      background:#F1F7FC;
      border-bottom:1px solid rgba(15,23,42,.06);
      padding:calc(8px + var(--safe-t)) 16px 12px;
      backdrop-filter:blur(12px);
      display:flex;
      align-items:center;
      gap:10px;
    }
    .topbar .title{
      margin:0;
      font-weight:800;
      font-size:1.05rem;
      letter-spacing:.02em;
    }
    main.page-content{
      padding-top: calc(var(--topbar-h) + var(--safe-t) + 6px);
      padding-bottom: calc(
        max(var(--bottombar-h), calc(var(--icon) + 2*var(--pad-v)))
        + max(var(--safe-b), 8px) + 16px
      );
    }
    .dashboard-shell{
      max-width:1000px;
      margin:0 auto;
      padding:10px 16px 0;
    }
    .page-heading{
      margin:12px 0 14px;
    }
    .page-heading h1{
      font-size:1.5rem;
      font-weight:800;
      margin:0 0 4px;
    }
    .page-heading p{
      margin:0;
      color:#6b7280;
      font-size:.95rem;
    }
    .card-glass{
      border-radius:20px;
      padding:16px;
      background:rgba(255,255,255,.94);
      backdrop-filter:blur(16px);
      box-shadow:0 20px 45px rgba(15,23,42,.12);
      border:1px solid rgba(226,232,240,.9);
      margin-bottom:14px;
    }
    .card-glass header{
      margin-bottom:10px;
    }
    .card-glass h2{
      font-size:1.15rem;
      font-weight:800;
      margin:0;
    }
    .card-glass table{
      margin-bottom:0;
    }
    .summary-card{
      max-width:480px;
      margin-left:auto;
      margin-right:auto;
      padding:12px 14px;
    }
    .summary-card h2{
      font-size:1rem;
    }
    .summary-card table td{
      padding:6px 8px;
      font-size:.93rem;
    }
    .summary-card .label{
      font-weight:700;
      color:#1c2540;
    }
    .summary-card .value{
      font-weight:800;
      text-align:right;
      color:#0f172a;
    }
    .pill{
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding:6px 10px;
      border-radius:999px;
      background:#f4f7fb;
      color:#1f2a37;
      font-weight:600;
      font-size:.82rem;
      border:1px solid #e5e9f0;
      white-space:nowrap;
      margin-right:8px;
      margin-bottom:6px;
    }
  </style>
</head>
<body onload="localize()">
  <div class="topbar">
    <h4 class="title">Estado de Cuenta</h4>
  </div>

  <section id="Menu">
    <?php require_once 'html/Menuprinc.php'; ?>
  </section>

  <main class="page-content">
    <div class="dashboard-shell">
      <div class="page-heading">
        <h1>Estado de cuenta</h1>
        <p>Detalle de pagos y saldo de la venta seleccionada.</p>
      </div>

      <div class="card-glass">
        <header>
          <h2><?= h($Reg['Nombre'] ?? '') ?></h2>
        </header>
        <div class="d-flex flex-wrap">
          <span class="pill">Producto: <?= h($Reg['Producto'] ?? '') ?></span>
          <span class="pill">Status: <?= h($Reg['Status'] ?? '') ?></span>
          <span class="pill">Costo: <?= $money($Reg['CostoVenta'] ?? 0) ?></span>
        </div>
      </div>

      <div class="card-glass">
        <header><h2>Pagos registrados</h2></header>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="thead-light">
              <tr>
                <th>Fecha</th>
                <th>Cantidad</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $sql  = "SELECT FechaRegistro, Cantidad, status FROM Pagos WHERE IdVenta = ? ORDER BY FechaRegistro ASC";
              $stmt = $mysqli->prepare($sql);
              $stmt->bind_param('i', $IdVenta);
              $stmt->execute();
              $res  = $stmt->get_result();

              if ($res->num_rows === 0) {
                echo '<tr><td colspan="3" class="text-muted text-center">Sin pagos registrados</td></tr>';
              } else {
                while ($Pago = $res->fetch_assoc()) {
                  $fecha   = h(substr((string)($Pago['FechaRegistro'] ?? ''),0,10));
                  $monto   = $money($Pago['Cantidad'] ?? 0);
                  $estatus = h($Pago['status'] ?? '');
                  echo "<tr><td>{$fecha}</td><td>{$monto}</td><td>{$estatus}</td></tr>";
                }
              }
              $stmt->close();
              ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card-glass summary-card">
        <header><h2>Resumen</h2></header>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <tbody>
              <tr>
                <td class="label">Pagos Realizados</td>
                <td class="value">
                  <?php
                  $PagsRe = $financieras->SumarPagos($mysqli, "Cantidad", "Pagos", "IdVenta", $IdVenta);
                  echo $money($PagsRe);
                  ?>
                </td>
              </tr>
              <tr>
                <td class="label">Moras Pagadas</td>
                <td class="value">
                  <?php
                  $MorRe = $financieras->SumarMora($mysqli, "Cantidad", "Pagos", "IdVenta", $IdVenta);
                  echo $money($MorRe);
                  ?>
                </td>
              </tr>
              <tr>
                <td class="label">Para liquidar hoy</td>
                <td class="value">
                  <?php
                  $st = (string)($Reg['Status'] ?? '');
                  if ($st !== "ACTIVO" && $st !== "ACTIVACION") {
                    $doa = $financieras->SaldoCredito($mysqli, $IdVenta);
                    echo $money($doa);
                  } else {
                    echo '<span class="text-muted">—</span>';
                  }
                  ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>

  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
  <script src="Javascript/finger.js?v=3"></script>
  <script src="Javascript/localize.js?v=3"></script>
  <script src="Javascript/Inyectar_gps_form.js"></script>
</body>
</html>
