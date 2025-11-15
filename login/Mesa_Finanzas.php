<?php
/********************************************************************************************
 * Qué hace: Página "Mesa Finanzas".
 * - Muestra pagos registrados en Pagos pendientes de conciliación.
 * - Permite conciliar (registrar depósito bancario) vía Funcionalidad_Cobros.php.
 * - Muestra operaciones de Mercado Pago desde VentasMercadoPago.
 * - Desde aquí se pueden disparar recordatorios de pago (correo / SMS) vía Funcionalidad_Cobros.php.
 * Fecha: 14/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

// =================== Sesión y dependencias ===================
session_start();
require_once '../eia/librerias.php';
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
    date_approved
  FROM VentasMercadoPago
  WHERE date_created BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)
  ORDER BY date_created DESC
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

// Total MP
$totalMPPendiente = 0.0;
foreach ($mpOps as $mp) {
  if (strtoupper((string)$mp['estatus_pago']) === 'PENDIENTE' ||
      strtoupper((string)$mp['mp_payment_status']) !== 'APPROVED') {
    $totalMPPendiente += (float)$mp['amount'];
  }
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F2F2F2">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Mesa Finanzas</title>

  <!-- PWA / iOS -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= h($VerCache) ?>">
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
  <main class="page-content container">
    <!-- Filtros de fecha + resumen -->
    <div class="card-form mb-3">
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

      <div class="row mt-3">
        <div class="col-md-4">
          <p class="mb-1"><strong>Efectivo / Depósito pendiente de conciliar</strong></p>
          <h4>$<?= number_format($totalPendiente, 2) ?></h4>
        </div>
        <div class="col-md-4">
          <p class="mb-1"><strong>MP pendiente de aprobar / aplicar</strong></p>
          <h4>$<?= number_format($totalMPPendiente, 2) ?></h4>
        </div>
      </div>
    </div>

    <!-- Pagos pendientes de conciliación -->
    <div class="table-responsive mesa-table-wrapper">
      <h5>Pagos registrados por vendedores (pendientes de conciliación)</h5>
      <br>
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

    <!-- Operaciones Mercado Pago -->
    <div class="table-responsive mesa-table-wrapper">
      <h5>Operaciones Mercado Pago</h5>
      <br>
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
          </tr>
        </thead>
        <tbody>
        <?php if (!$mpOps): ?>
          <tr>
            <td colspan="7" class="text-center text-muted">Sin operaciones en el periodo.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($mpOps as $mp): ?>
            <?php
              $chipVenta  = mesa_status_chip((string)$mp['estatus']);
              $chipPago   = mesa_status_chip((string)$mp['estatus_pago']);
              $chipMP     = mesa_status_chip((string)$mp['mp_payment_status'] ?: 'PENDIENTE');
              $fechaCrea  = $mp['date_created']  ? date('Y-m-d H:i', strtotime((string)$mp['date_created']))  : '';
              $fechaApr   = $mp['date_approved'] ? date('Y-m-d H:i', strtotime((string)$mp['date_approved'])) : '';
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
                <div><small>Aprobado: <?= h($fechaApr) ?></small></div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <br><br><br><br>
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
