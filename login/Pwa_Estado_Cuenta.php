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
  <meta name="theme-color" content="#F2F2F2">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Estado de Cuenta</title>

  <!-- PWA -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= h($VerCache) ?>">
  <link rel="stylesheet" href="assets/css/Grafica.css">
</head>
<body onload="localize()">
  <div class="topbar">
    <h4 class="title">Estado de Cuenta</h4>
  </div>

  <section id="Menu">
    <?php require_once 'html/Menuprinc.php'; ?>
  </section>

  <main class="page-content">
    <div class="container">

      <div class="cabecera">
        <p class="mb-1"><strong><?= h($Reg['Nombre'] ?? '') ?></strong></p>
        <p class="mb-0">
          <strong>Producto:</strong> <?= h($Reg['Producto'] ?? '') ?>
          &nbsp;&nbsp; <strong>Status:</strong> <?= h($Reg['Status'] ?? '') ?>
          &nbsp;&nbsp; <strong>Costo de compra:</strong> <?= $money($Reg['CostoVenta'] ?? 0) ?>
        </p>
      </div>

      <div class="historial mt-3">
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

      <div class="pie mt-3">
        <div class="tablaCuenta">
          <table class="table table-sm">
            <tbody>
              <tr>
                <td>Pagos Realizados</td>
                <td>
                  <?php
                  $PagsRe = $financieras->SumarPagos($mysqli, "Cantidad", "Pagos", "IdVenta", $IdVenta);
                  echo $money($PagsRe);
                  ?>
                </td>
              </tr>
              <tr>
                <td>Moras Pagadas</td>
                <td>
                  <?php
                  $MorRe = $financieras->SumarMora($mysqli, "Cantidad", "Pagos", "IdVenta", $IdVenta);
                  echo $money($MorRe);
                  ?>
                </td>
              </tr>
              <tr>
                <td>Para liquidar hoy</td>
                <td>
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
