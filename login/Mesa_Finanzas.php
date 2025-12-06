<?php
/********************************************************************************************
 * Qué hace: Página "Mesa_Finanzas.php".
 * - Muestra pagos registrados en Pagos pendientes de conciliación.
 * - Permite conciliar (registrar depósito bancario) vía Funcionalidad_Cobros.php.
 * - Muestra operaciones de Mercado Pago desde VentasMercadoPago.
 * - Desde aquí se pueden disparar recordatorios de pago (correo / SMS) vía Funcionalidad_Cobros.php.
 * Fecha: 14/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

// =================== Sesión y dependencias ===================
require_once dirname(__DIR__) . '/eia/session.php';
kasu_session_start();
require_once __DIR__ . '/../eia/librerias.php';
require_once __DIR__ . '/php/mesa_helpers.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// =================== Guardia de sesión ===================
if (empty($_SESSION["Vendedor"])) {
  header('Location: https://kasu.com.mx/login');
  exit;
}

// =================== Utilidades ===================
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// Cache bust
$VerCache = (string)time();

// =================== CSRF para cobros ===================
if (empty($_SESSION['csrf_cobros'])) {
  $_SESSION['csrf_cobros'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_cobros'];

// =================== Fechas periodo ===================
date_default_timezone_set('America/Mexico_City');
$hoy      = date('Y-m-d');
$mesInicio= date('Y-m-01');

// =================== Mensaje GET opcional ===================
if (isset($_GET['Msg'])) {
  echo "<script>alert('".h((string)$_GET['Msg'])."');</script>";
}

// =================== Filtros simples ===================
$f_desde = $_GET['desde'] ?? $mesInicio;
$f_hasta = $_GET['hasta'] ?? $hoy;

// Normalizar fechas (YYYY-MM-DD). Si vienen vacías, se reponen.
if (!preg_match('~^\d{4}-\d{2}-\d{2}$~', $f_desde)) $f_desde = $mesInicio;
if (!preg_match('~^\d{4}-\d{2}-\d{2}$~', $f_hasta)) $f_hasta = $hoy;

// =================== QUERY: Pagos pendientes de conciliación ===================
$sqlPagos = "
  SELECT
    p.Id,
    p.IdVenta,
    p.Cantidad,
    p.Metodo,
    p.status,
    p.FechaRegistro,
    p.Referencia,
    p.Usuario,
    v.Nombre,
    v.Producto,
    v.Status AS StatusVenta
  FROM Pagos p
  LEFT JOIN Venta v ON v.Id = p.IdVenta
  WHERE
    p.FechaRegistro BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)
    AND (p.status IS NULL OR p.status = '' OR p.status = 'PENDIENTE')
  ORDER BY p.FechaRegistro DESC
  LIMIT 300
";
$pagosPendientes = [];
$st = $mysqli->prepare($sqlPagos);
$st->bind_param('ss', $f_desde, $f_hasta);
$st->execute();
$res = $st->get_result();
while ($r = $res->fetch_assoc()) {
  $pagosPendientes[] = $r;
}
$st->close();

// Totales rápidos
$totalPendiente = 0.0;
foreach ($pagosPendientes as $p) {
  $totalPendiente += (float)$p['Cantidad'];
}

// =================== QUERY: Operaciones Mercado Pago ===================
// Usamos COALESCE(date_created, created_at, updated_at) para asegurarnos de
// incluir filas aunque todavía no exista date_created (por ejemplo, antes del webhook).
$sqlMP = "
  SELECT
    id,
    folio,
    estatus,
    estatus_pago,
    mp_payment_id,
    mp_payment_status,
    mp_status_detail,
    amount,
    payer_email,
    date_created,
    date_approved,
    updated_at,
    COALESCE(date_created, created_at, updated_at) AS fecha_ref
  FROM VentasMercadoPago
  WHERE COALESCE(date_created, created_at, updated_at)
        BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)
  ORDER BY fecha_ref DESC
  LIMIT 300
";
$mpOps = [];
$st = $mysqli->prepare($sqlMP);
$st->bind_param('ss', $f_desde, $f_hasta);
$st->execute();
$res = $st->get_result();
while ($r = $res->fetch_assoc()) {
  $mpOps[] = $r;
}
$st->close();

// Total MP pendiente (solo sumamos los que siguen pendientes)
$totalMPPendiente = 0.0;
foreach ($mpOps as $mp) {
  $pago   = strtoupper((string)($mp['estatus_pago'] ?? ''));
  $status = strtoupper((string)($mp['mp_payment_status'] ?? ''));
  if ($pago === 'PENDIENTE' || $status === '' || $status === 'PENDING') {
    $totalMPPendiente += (float)$mp['amount'];
  }
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F1F7FC">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Mesa Finanzas</title>

  <!-- PWA / iOS -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= h($VerCache) ?>">
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
      max-width:1100px;
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
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      margin-bottom:10px;
    }
    .card-glass h2{
      font-size:1.1rem;
      font-weight:800;
      margin:0;
    }
    .card-glass table{
      margin-bottom:0;
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
    .form-card .form-control{
      border-radius:12px;
      border:1px solid #e3ebf5;
      background:#f5f7fb;
      color:#1c2540;
      padding:11px 12px;
      box-shadow:none;
    }
    .form-card .btn{
      border-radius:12px;
      font-weight:700;
      padding:10px 12px;
    }
    .summary-grid{
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
      gap:12px;
      margin-top:10px;
    }
    .summary-box{
      border-radius:14px;
      padding:12px 14px;
      background:#f9fbff;
      border:1px solid #e5e9f0;
      box-shadow:0 12px 28px rgba(15,23,42,.08);
    }
    .summary-box p{
      margin:0 0 4px;
      color:#4b5563;
      font-weight:600;
    }
    .summary-box h4{
      margin:0;
      font-weight:800;
      color:#0f172a;
    }
    .mesa-table-wrapper{overflow-x:auto;}
    .mesa-table{min-width:720px;}
    .mesa-table td[data-label="Fecha"],
    .mesa-table td[data-label="Fechas"] small{
      white-space:nowrap;
      font-variant-numeric:tabular-nums;
    }
  </style>
</head>
<body onload="localize()">
  <!-- TOP BAR -->
  <div class="topbar">
    <div class="d-flex align-items-center w-100">
      <h4 class="title">Mesa Finanzas</h4>
    </div>
  </div>

  <!-- Menú inferior fijo -->
  <section id="Menu">
    <?php require_once __DIR__ . '/html/Menuprinc.php'; ?>
  </section>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="page-content">
    <div class="dashboard-shell">
      <div class="page-heading">
        <h1>Mesa finanzas</h1>
        <p>Conciliación de pagos y operaciones de Mercado Pago.</p>
      </div>

      <!-- Filtros de fecha + resumen -->
      <div class="card-glass form-card">
        <form class="form-row" method="GET" action="<?= h($_SERVER['PHP_SELF']) ?>">
          <div class="form-group col-md-3">
            <label>Desde</label>
            <input type="date" name="desde" class="form-control" value="<?= h($f_desde) ?>">
          </div>
          <div class="form-group col-md-3">
            <label>Hasta</label>
            <input type="date" name="hasta" class="form-control" value="<?= h($f_hasta) ?>">
          </div>
          <div class="form-group col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-secondary btn-block">Aplicar filtros</button>
          </div>
        </form>

        <div class="summary-grid">
          <div class="summary-box">
            <p>Efectivo / Depósito pendiente de conciliar</p>
            <h4>$<?= number_format($totalPendiente, 2) ?></h4>
          </div>
          <div class="summary-box">
            <p>MP pendiente de aprobar / aplicar</p>
            <h4>$<?= number_format($totalMPPendiente, 2) ?></h4>
          </div>
        </div>
      </div>

      <!-- Pagos pendientes de conciliación -->
      <div class="card-glass">
        <header><h2>Pagos registrados por vendedores (pendientes de conciliación)</h2></header>
        <div class="table-responsive mesa-table-wrapper" style="margin-bottom:0;">
          <table class="table mesa-table" data-mesa="pagos-pendientes">
        <thead>
          <tr>
            <th>Cliente</th>
            <th>Venta</th>
            <th>Monto</th>
            <th>Método</th>
            <th>Fecha registro</th>
            <th>Ejecutivo</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$pagosPendientes): ?>
          <tr>
            <td colspan="7" class="text-center text-muted">Sin pagos pendientes en el periodo.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($pagosPendientes as $p): ?>
            <?php
              $fechaReg = $p['FechaRegistro'] ? date('Y-m-d H:i', strtotime((string)$p['FechaRegistro'])) : '';
              $statusTexto = ($p['status'] === null || $p['status'] === '' || $p['status'] === 'PENDIENTE')
                ? 'Pendiente'
                : (string)$p['status'];
            ?>
            <tr>
              <td data-label="Cliente"><?= h($p['Nombre'] ?? '') ?></td>
              <td data-label="Venta #"><?= (int)$p['IdVenta'] ?></td>
              <td data-label="Monto">$<?= number_format((float)$p['Cantidad'], 2) ?></td>
              <td data-label="Método"><?= h($p['Metodo']) ?></td>
              <td data-label="Fecha"><?= h($fechaReg) ?></td>
              <td data-label="Ejecutivo"><?= h($p['Usuario']) ?></td>
              <td class="mesa-actions" data-label="Acciones">
                <div class="mesa-actions-grid">
                  <!-- Conciliar depósito -->
                  <button
                    type="button"
                    class="btn"
                    style="background:#27AE60;color:#F8F9F9;"
                    title="Conciliar depósito"
                    data-toggle="modal"
                    data-target="#ModalConciliar"
                    data-idpago="<?= (int)$p['Id'] ?>"
                    data-idventa="<?= (int)$p['IdVenta'] ?>"
                    data-monto="<?= number_format((float)$p['Cantidad'],2,'.','') ?>"
                    data-cliente="<?= h($p['Nombre'] ?? '') ?>"
                  >
                    <i class="material-icons">done_all</i>
                  </button>

                  <!-- Recordatorio por correo -->
                  <form method="POST" action="/login/php/Funcionalidad_Cobros.php">
                    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                    <input type="hidden" name="accion" value="recordatorio_correo">
                    <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">
                    <input type="hidden" name="IdVenta" value="<?= (int)$p['IdVenta'] ?>">
                    <button
                      type="submit"
                      class="btn"
                      style="background:#5DADE2;color:#F8F9F9;"
                      title="Enviar recordatorio por correo"
                    >
                      <i class="material-icons">email</i>
                    </button>
                  </form>

                  <!-- Recordatorio por SMS -->
                  <form method="POST" action="/login/php/Funcionalidad_Cobros.php">
                    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                    <input type="hidden" name="accion" value="recordatorio_sms">
                    <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">
                    <input type="hidden" name="IdVenta" value="<?= (int)$p['IdVenta'] ?>">
                    <button
                      type="submit"
                      class="btn"
                      style="background:#F39C12;color:#F8F9F9;"
                      title="Enviar recordatorio por SMS"
                    >
                      <i class="material-icons">sms</i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
        </div>
      </div>

      <!-- Operaciones Mercado Pago -->
      <div class="card-glass">
        <header><h2>Operaciones Mercado Pago</h2></header>
        <div class="table-responsive mesa-table-wrapper" style="margin-bottom:0;">
          <table class="table mesa-table" data-mesa="mp-ops">
        <thead>
          <tr>
            <th>Folio</th>
            <th>Importe</th>
            <th>Estatus venta</th>
            <th>Estatus pago</th>
            <th>Status MP</th>
            <th>Cliente / correo</th>
            <th>Fechas</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$mpOps): ?>
          <tr>
            <td colspan="8" class="text-center text-muted">Sin operaciones en el periodo.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($mpOps as $mp): ?>
            <?php
              $chipVenta  = mesa_status_chip((string)$mp['estatus']);
              $chipPago   = mesa_status_chip((string)$mp['estatus_pago']);
              $chipMP     = mesa_status_chip((string)($mp['mp_payment_status'] ?: 'PENDIENTE'));

              // Usamos date_created si existe, si no, fecha_ref (created_at/updated_at)
              $rawCreado = $mp['date_created'] ?: $mp['fecha_ref'];
              $fechaCrea = $rawCreado ? date('Y-m-d H:i', strtotime((string)$rawCreado)) : '';
              $fechaApr  = $mp['date_approved'] ? date('Y-m-d H:i', strtotime((string)$mp['date_approved'])) : '';
            ?>
            <tr>
              <td data-label="Folio"><?= h($mp['folio']) ?></td>
              <td data-label="Importe">$<?= number_format((float)$mp['amount'], 2) ?></td>
              <td data-label="Estatus venta"><?= $chipVenta ?></td>
              <td data-label="Estatus pago"><?= $chipPago ?></td>
              <td data-label="Status MP"><?= $chipMP ?></td>
              <td data-label="Cliente / correo"><?= h($mp['payer_email'] ?? '') ?></td>
              <td data-label="Fechas">
                <div><small>Creado: <?= h($fechaCrea) ?></small></div>
                <div><small>Aprobado: <?= h($fechaApr ?: 'N/A') ?></small></div>
              </td>
              <td class="mesa-actions" data-label="Acciones">
                <div class="mesa-actions-grid">
                  <!-- Enviar liga de pago por correo -->
                  <form method="POST" action="/login/php/Funcionalidad_Cobros.php">
                    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                    <input type="hidden" name="accion" value="mp_enviar_liga_correo">
                    <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">
                    <input type="hidden" name="folio" value="<?= h($mp['folio']) ?>">
                    <button
                      type="submit"
                      class="btn"
                      style="background:#5DADE2;color:#F8F9F9;"
                      title="Enviar liga de pago por correo"
                    >
                      <i class="material-icons">email</i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
        </div>
      </div>
    </div>
  </main>

  <!-- MODAL: Registrar depósito / conciliar pago -->
  <div class="modal fade" id="ModalConciliar" tabindex="-1" role="dialog" aria-labelledby="lblConciliar" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form class="modal-content" method="POST" action="/login/php/Funcionalidad_Cobros.php">
        <div class="modal-header">
          <h5 class="modal-title" id="lblConciliar">Conciliar pago</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
          <input type="hidden" name="accion" value="registrar_deposito">
          <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">
          <input type="hidden" name="IdPago" id="c_IdPago">
          <input type="hidden" name="IdVenta" id="c_IdVenta">

          <p>Cliente</p>
          <h5 id="c_NombreCliente" class="mb-3"></h5>

          <div class="form-group">
            <label>Monto registrado</label>
            <input type="text" class="form-control" id="c_MontoOriginal" readonly>
          </div>

          <div class="form-group">
            <label>Monto depositado</label>
            <input type="number" step="0.01" min="0" class="form-control" name="MontoDeposito" id="c_MontoDeposito" required>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Fecha depósito</label>
              <input type="date" class="form-control" name="FechaDeposito" id="c_FechaDeposito" required>
            </div>
            <div class="form-group col-md-6">
              <label>Hora depósito</label>
              <input type="time" class="form-control" name="HoraDeposito" id="c_HoraDeposito" required>
            </div>
          </div>

          <div class="form-group">
            <label>Banco / medio</label>
            <input type="text" class="form-control" name="Banco" id="c_Banco" placeholder="Banco, sucursal o medio" required>
          </div>

          <div class="form-group">
            <label>Referencia del depósito</label>
            <input type="text" class="form-control" name="ReferenciaDeposito" id="c_ReferenciaDeposito" placeholder="Folio, referencia bancaria, etc.">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar conciliación</button>
        </div>
      </form>
    </div>
  </div>

  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
  <script src="https://www.gstatic.com/charts/loader.js"></script>
  <script src="Javascript/finger.js?v=3"></script>
  <script src="Javascript/localize.js?v=3"></script>
  <script src="Javascript/Inyectar_gps_form.js"></script>

  <!-- JS: Rellenar modal de conciliación -->
  <script>
  $('#ModalConciliar').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var idPago  = button.data('idpago') || '';
    var idVenta = button.data('idventa') || '';
    var monto   = button.data('monto') || '';
    var cliente = button.data('cliente') || '';

    var modal = $(this);
    modal.find('#c_IdPago').val(idPago);
    modal.find('#c_IdVenta').val(idVenta);
    modal.find('#c_NombreCliente').text(cliente);
    modal.find('#c_MontoOriginal').val('$' + monto);
    modal.find('#c_MontoDeposito').val(monto);

    var now   = new Date();
    var fStr  = now.toISOString().slice(0,10);
    var hStr  = now.toTimeString().slice(0,5);
    modal.find('#c_FechaDeposito').val(fStr);
    modal.find('#c_HoraDeposito').val(hStr);
  });
  </script>
</body>
</html>
