<?php
/********************************************************************************************
 * Qué hace: Panel de “Pagos y Promesas de Pago” para KASU. Lista promesas vencidas y por semana,
 *           abre modal de registro de pago, y calcula totales con consultas preparadas.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

/* ==== Sesión y librerías ==== */
require_once dirname(__DIR__) . '/eia/session.php';
kasu_session_start();
date_default_timezone_set('America/Mexico_City');
setlocale(LC_ALL,'es_ES.UTF-8');
require_once __DIR__ . '/../eia/librerias.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* ==== Sesión obligatoria ==== */
if (empty($_SESSION['Vendedor'])) {
  header('Location: https://kasu.com.mx/login');
  exit();
}

/* ==== Vars de usuario ==== */
$VerCache  = time();
$Vendedor  = (string)$_SESSION['Vendedor'];
$Niv       = (int)($basicas->BuscarCampos($mysqli, 'Nivel', 'Empleados', 'IdUsuario', $Vendedor) ?? 0);
$IdSucUsr  = (int)($basicas->BuscarCampos($mysqli, 'Sucursal', 'Empleados', 'IdUsuario', $Vendedor) ?? 0);
$NomSucUsr = (string)($basicas->BuscarCampos($mysqli, 'NombreSucursal', 'Sucursal', 'Id', $IdSucUsr) ?? '');

/* ==== Rangos dinámicos ==== */
$HOY  = date('Y-m-d');
$W1_I = date('Y-m-d', strtotime('monday this week'));
$W1_F = date('Y-m-d', strtotime('sunday this week'));
$W2_I = date('Y-m-d', strtotime('monday next week'));
$W2_F = date('Y-m-d', strtotime('sunday next week'));

/* ==== Modal al dar clic ==== */
$Ventana = '';
$Reg = $Pago = $PagoPend = $FecProm = $CantProm = $Gps = null;

/* ================== Selección de cliente (abre modal) ================== */
if (isset($_POST['SelCte'])) {
  $idVentaPost = (int)($_POST['IdVenta'] ?? 0);
  if ($idVentaPost > 0) {
    $ventaSql = 'SELECT * FROM Venta WHERE Id = ? LIMIT 1';
    $st = $mysqli->prepare($ventaSql);
    $st->bind_param('i', $idVentaPost);
    $st->execute();
    $res = $st->get_result();
    if ($res && ($Reg = $res->fetch_assoc())) {
      // Status de referencia: POST -> venta
      $statusVta = $_POST['StatusVta'] ?? ($Reg['Status'] ?? '');
      if (!in_array($statusVta, ['ACTIVO', 'ACTIVACION'], true)) {
        // Cálculos financieros
        $Pago1    = $financieras->Pago($mysqli, (int)$Reg['Id']);
        $Pago     = number_format((float)$Pago1, 2);
        $Saldo    = '$' . number_format((float)$financieras->SaldoCredito($mysqli, (int)$Reg['Id']), 2);
        $PagoPend = $financieras->PagosPend($mysqli, (int)$Reg['Id']);

        // Estado de mora/corriente
        if (method_exists($financieras, 'estado_mora_corriente')) {
          $StatVtas = $financieras->estado_mora_corriente((int)$Reg['Id']);
          $Status   = (!empty($StatVtas['estado']) && $StatVtas['estado'] === 'AL CORRIENTE') ? 'Pago' : 'Mora';
        } else {
          $Status = 'Pago';
        }
      }
      // Configurar modal
      $Ventana = '#Ventana2';
    }
    $st->close();
  }
}

/* ==== SQL base para promesas (usa pp.Pagado + pagos referenciados a la promesa) ==== */
function sql_promesas_cond(): string {
  return "
    SELECT 
      pp.Id           AS IdPromesa,
      pp.IdVenta,
      pp.Cantidad     AS MontoPromesa,
      pp.Promesa      AS FechaPromesa,
      COALESCE(pp.Pagado,0)                            AS PagadoPP,
      COALESCE(v.Nombre,'')                            AS NombreCliente,
      COALESCE(v.Status,'')                            AS StatusVenta,
      COALESCE(SUM(pg.Cantidad),0)                     AS PagadoRef,
      (pp.Cantidad - (COALESCE(pp.Pagado,0)+COALESCE(SUM(pg.Cantidad),0))) AS SaldoPendiente
    FROM PromesaPago pp
    JOIN Venta v        ON v.Id = pp.IdVenta
    LEFT JOIN Pagos pg  ON pg.Referencia = pp.Id
    WHERE pp.Vendedor = ?
      AND %s
    GROUP BY pp.Id
    HAVING SaldoPendiente > 0
    ORDER BY pp.Promesa, pp.Id";
}

/* ==== Cargar bucket con consultas preparadas ==== */
function collect_bucket(mysqli $db, array &$bucket, string $vend, ?string $sucName, string $cond, array $params): void {
  $sql = sprintf(sql_promesas_cond(), $cond);
  $st  = $db->prepare($sql);
  if ($st === false) return;

  // Todos los parámetros como string (YYYY-mm-dd y vendedor)
  $types = str_repeat('s', count($params));
  $st->bind_param($types, ...$params);
  $st->execute();
  $res = $st->get_result();
  while ($r = $res->fetch_assoc()) {
    $r['SucursalUI'] = $sucName ?: '';
    $bucket[] = $r;
  }
  $st->close();
}

/* ==== Render de botones/lista ==== */
function render_bucket(array $bucket): void {
  usort($bucket, function($a,$b){
    $c = strcmp((string)$a['FechaPromesa'], (string)$b['FechaPromesa']);
    return $c !== 0 ? $c : ((int)$a['IdPromesa'] <=> (int)$b['IdPromesa']);
  });

  foreach ($bucket as $r) {
    $cls = trim((string)$r['StatusVenta']);
    $nom = $r['NombreCliente'] !== '' ? (string)$r['NombreCliente'] : ('Venta #' . (int)$r['IdVenta']);
    $suc = $r['SucursalUI'] ? ' - ' . htmlspecialchars((string)$r['SucursalUI'], ENT_QUOTES) : '';
    $detalle = trim(($r['FechaPromesa'] ?? '') . ' ' . $suc);

    printf(
      '<form method="POST" action="%s" class="bucket-card">
        <input type="hidden" name="IdVenta"    value="%d">
        <input type="hidden" name="Referencia" value="%d">
        <input type="hidden" name="Promesa"    value="%s">
        <input type="hidden" name="StatusVta"  value="%s">
        <span class="badge-status badge %s">%s</span>
        <button type="submit" name="SelCte" value="1" class="cta %s">
          <span>%s</span>
          <strong>%s</strong>
        </button>
      </form>',
      htmlspecialchars((string)($_SERVER['PHP_SELF'] ?? ''), ENT_QUOTES),
      (int)$r['IdVenta'],
      (int)$r['IdPromesa'],
      htmlspecialchars((string)$r['FechaPromesa'], ENT_QUOTES),
      htmlspecialchars($cls, ENT_QUOTES),
      htmlspecialchars($cls, ENT_QUOTES), htmlspecialchars($cls, ENT_QUOTES),
      htmlspecialchars($cls !== '' ? $cls : 'btn btn-primary', ENT_QUOTES),
      htmlspecialchars($detalle !== '' ? $detalle : 'Promesa pendiente', ENT_QUOTES),
      htmlspecialchars($nom, ENT_QUOTES)
    );
  }
}

/* ==== Formateo de rangos de semana sin strftime (deprecado desde 8.1) ==== */
function semana_es(string $ini, string $fin): string {
  $t1 = strtotime($ini);
  $t2 = strtotime($fin);
  if (class_exists('IntlDateFormatter')) {
    $day = new IntlDateFormatter('es_MX', IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'America/Mexico_City', null, 'd');
    $end = new IntlDateFormatter('es_MX', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'America/Mexico_City', null, "d 'de' MMMM y");
    return $day->format($t1) . '–' . $end->format($t2);
  }
  // Fallback manual si no está ext-intl
  $meses = ['','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
  $d1 = (int)date('d', $t1);
  $d2 = (int)date('d', $t2);
  $m2 = $meses[(int)date('n', $t2)];
  $y2 = date('Y', $t2);
  return $d1 . '–' . $d2 . ' de ' . $m2 . ' ' . $y2;
}

/* ==== Buckets ==== */
$bucket_vencidas = [];
$bucket_sem1     = [];
$bucket_sem2     = [];

if ($Niv >= 5) {
  collect_bucket($mysqli, $bucket_vencidas, $Vendedor, $NomSucUsr, 'pp.Promesa <= ?', [$Vendedor, $HOY]);
  collect_bucket($mysqli, $bucket_sem1,     $Vendedor, $NomSucUsr, 'pp.Promesa BETWEEN ? AND ?', [$Vendedor, $W1_I, $W1_F]);
  collect_bucket($mysqli, $bucket_sem2,     $Vendedor, $NomSucUsr, 'pp.Promesa BETWEEN ? AND ?', [$Vendedor, $W2_I, $W2_F]);

} elseif ($Niv <= 4 && $Niv >= 2) {
  $rs = $mysqli->query("SELECT IdUsuario FROM Empleados WHERE Nombre<>'Vacante' AND Nivel>={$Niv} AND Sucursal={$IdSucUsr}");
  while ($rs && ($e = $rs->fetch_assoc())) {
    $vend = (string)$e['IdUsuario'];
    collect_bucket($mysqli, $bucket_vencidas, $vend, $NomSucUsr, 'pp.Promesa <= ?', [$vend, $HOY]);
    collect_bucket($mysqli, $bucket_sem1,     $vend, $NomSucUsr, 'pp.Promesa BETWEEN ? AND ?', [$vend, $W1_I, $W1_F]);
    collect_bucket($mysqli, $bucket_sem2,     $vend, $NomSucUsr, 'pp.Promesa BETWEEN ? AND ?', [$vend, $W2_I, $W2_F]);
  }

} else { // Niv == 1
  $rs = $mysqli->query("SELECT IdUsuario, Sucursal FROM Empleados WHERE Nombre<>'Vacante' AND Nivel>={$Niv}");
  while ($rs && ($e = $rs->fetch_assoc())) {
    $vend = (string)$e['IdUsuario'];
    $nomS = (string)($basicas->BuscarCampos($mysqli, 'NombreSucursal', 'Sucursal', 'Id', (int)$e['Sucursal']) ?? '');
    collect_bucket($mysqli, $bucket_vencidas, $vend, $nomS, 'pp.Promesa <= ?', [$vend, $HOY]);
    collect_bucket($mysqli, $bucket_sem1,     $vend, $nomS, 'pp.Promesa BETWEEN ? AND ?', [$vend, $W1_I, $W1_F]);
    collect_bucket($mysqli, $bucket_sem2,     $vend, $nomS, 'pp.Promesa BETWEEN ? AND ?', [$vend, $W2_I, $W2_F]);
  }
}

/* ===== Mensaje opcional por GET — 05/11/2025, JCCM ===== */
if (isset($_GET['Msg'])) {
  echo "<script>alert('".($_GET['Msg'])."');</script>";
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F1F7FC">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Pagos y Promesas de Pago</title>

  <!-- PWA / iOS -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= htmlspecialchars((string)$VerCache, ENT_QUOTES) ?>">
  <style>
    body{
      margin:0;
      font-family:"Inter","SF Pro Display","Segoe UI",system-ui,-apple-system,sans-serif;
      background:#F1F7FC;
      color:#0f172a;
    }
    .topbar{
      backdrop-filter: blur(12px);
      background:#F1F7FC !important;
      border-bottom:1px solid rgba(15,23,42,.06);
      color:#0f172a !important;
      display:flex;
      align-items:center;
      gap:10px;
      padding: calc(8px + var(--safe-t)) 16px 10px;
      height: calc(var(--topbar-h) + var(--safe-t));
    }
    .topbar .title{
      margin:0;
      font-weight:700;
      font-size:1rem;
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
      max-width:1100px;
      margin:0 auto;
      padding: 8px 16px 0;
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
    .list-card{
      border-radius:20px;
      padding:16px;
      background:rgba(255,255,255,.94);
      backdrop-filter:blur(16px);
      box-shadow:0 20px 45px rgba(15,23,42,.12);
      border:1px solid rgba(226,232,240,.9);
      margin-bottom:16px;
    }
    .list-card header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      margin-bottom:12px;
    }
    .bucket-grid{
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
      gap:12px;
    }
    .bucket-card{
      position:relative;
      padding:14px 14px 12px;
      border-radius:16px;
      background:#f9fbff;
      border:1px solid #e5e9f0;
      box-shadow:0 10px 26px rgba(15,23,42,.08);
    }
    .badge-status{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      padding:4px 10px;
      border-radius:999px;
      font-weight:700;
      font-size:.8rem;
      background:#e8edf7;
      color:#1f2a37;
      margin-bottom:8px;
    }
    .bucket-card .cta{
      width:100%;
      border:none;
      border-radius:12px;
      background:#0f6ef0;
      color:#fff;
      font-weight:700;
      padding:10px 12px;
      box-shadow:0 14px 28px -20px rgba(15,110,240,.65);
      text-align:left;
    }
    .bucket-card .cta span{
      display:block;
      font-size:.78rem;
      color:#e8f0ff;
      font-weight:500;
    }
    .bucket-card .cta strong{
      display:block;
      font-size:.98rem;
      color:#fff;
    }
    .badge.ACTIVO{background:#e0f7ec;color:#0f5132;}
    .badge.PREVENTA{background:#fff4e5;color:#8c6d1f;}
    .badge.COBRANZA{background:#e8f2ff;color:#0f3c91;}
    .badge.CANCELADO{background:#fdecea;color:#7f1d1d;}
    .badge.ACTIVACION{background:#e0f2fe;color:#0b4f71;}
  </style>
</head>
<body onload="localize()">

  <!-- Topbar -->
  <div class="topbar">
    <div class="d-flex align-items-center w-100">
      <h4 class="title">Pagos y Promesas de Pago</h4>
    </div>
  </div>

  <!-- Menú inferior -->
  <section id="Menu">
    <?php require_once __DIR__ . '/html/Menuprinc.php'; ?>
  </section>

  <!-- Modal -->
  <div class="modal fade" id="Ventana2" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <?php require __DIR__ . '/html/Emergente_Registrar_Pago.php'; ?>
      </div>
    </div>
  </div>

  <!-- Contenido -->
  <main class="page-content">
    <div class="dashboard-shell">
      <div class="page-heading">
        <h1>Pagos y promesas</h1>
        <p>Revisa promesas vencidas y próximas, registra pagos desde cada tarjeta.</p>
      </div>

      <div class="list-card">
        <header>
          <div>
            <p class="chart-subtitle mb-1">Estado de cobros</p>
            <h4 class="chart-title">Vencidas y hoy</h4>
          </div>
        </header>
        <?php
          if(empty($bucket_vencidas)){
            echo '<p class="chart-subtitle mb-0">No existen pagos pendientes hoy.</p>';
          } else {
            echo '<div class="bucket-grid">';
            render_bucket($bucket_vencidas);
            echo '</div>';
          }
        ?>
      </div>

      <div class="list-card">
        <header>
          <div>
            <p class="chart-subtitle mb-1">Próxima semana</p>
            <h4 class="chart-title">Semana <?= htmlspecialchars(semana_es($W1_I, $W1_F), ENT_QUOTES) ?></h4>
          </div>
        </header>
        <?php
          if(empty($bucket_sem1)){
            echo '<p class="chart-subtitle mb-0">Sin promesas en este rango.</p>';
          } else {
            echo '<div class="bucket-grid">';
            render_bucket($bucket_sem1);
            echo '</div>';
          }
        ?>
      </div>

      <div class="list-card">
        <header>
          <div>
            <p class="chart-subtitle mb-1">Semana siguiente</p>
            <h4 class="chart-title">Semana <?= htmlspecialchars(semana_es($W2_I, $W2_F), ENT_QUOTES) ?></h4>
          </div>
        </header>
        <?php
          if(empty($bucket_sem2)){
            echo '<p class="chart-subtitle mb-0">Sin promesas en este rango.</p>';
          } else {
            echo '<div class="bucket-grid">';
            render_bucket($bucket_sem2);
            echo '</div>';
          }
        ?>
      </div>
    </div>
  </main>

  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
  <script src="Javascript/finger.js?v=3"></script>
  <script src="Javascript/localize.js?v=3"></script>
  <script src="Javascript/Inyectar_gps_form.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      <?php if (!empty($Ventana)) : ?> $('#Ventana2').modal('show'); <?php endif; ?>
    });
  </script>
</body>
</html>
