<?php
session_start();
require_once '../eia/librerias.php';
date_default_timezone_set('America/Mexico_City');

// Sesión
if (empty($_SESSION['Vendedor'])) {
  header('Location: https://kasu.com.mx/login');
  exit;
}

// Validar y decodificar el Id de venta desde GET
if (!isset($_GET['busqueda'])) {
  http_response_code(400); exit('Falta parámetro.');
}
$dec = base64_decode($_GET['busqueda'], true);
if ($dec === false || !ctype_digit($dec)) {
  http_response_code(400); exit('Parámetro inválido.');
}
$IdVenta = (int)$dec;

// Cargar venta
$Reg = null;
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

// Formateador de moneda
$fmt = class_exists('NumberFormatter') ? new NumberFormatter('es_MX', NumberFormatter::CURRENCY) : null;
function money_mx($n, $fmt){
  return $fmt ? $fmt->formatCurrency((float)$n, 'MXN') : ('$'.number_format((float)$n,2,'.',','));
}

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

  <!-- PWA / iOS -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?php echo htmlspecialchars($VerCache); ?>">
  <link rel="stylesheet" href="assets/css/Grafica.css">

  <!-- JS base -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
</head>
<body>

  <!-- TOP BAR fija -->
  <div class="topbar">
    <h4 class="title">Estado de Cuenta</h4>
  </div>

  <!-- Menú inferior -->
  <section id="Menu">
    <?php require_once 'html/Menuprinc.php'; ?>
  </section>

  <!-- Contenido -->
  <main class="page-content">
    <div class="container">
      <div class="cabecera">
        <p class="mb-1"><strong><?php echo htmlspecialchars($Reg['Nombre'] ?? ''); ?></strong></p>
        <p class="mb-0">
          <strong>Producto:</strong> <?php echo htmlspecialchars($Reg['Producto'] ?? ''); ?>
          &nbsp;&nbsp; <strong>Status:</strong> <?php echo htmlspecialchars($Reg['Status'] ?? ''); ?>
          &nbsp;&nbsp; <strong>Costo de compra:</strong> <?php echo money_mx($Reg['CostoVenta'] ?? 0, $fmt); ?>
        </p>
      </div>

      <div class="historial">
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
            // Pagos de la venta
            $mysqli->set_charset('utf8mb4');
            $sql  = "SELECT * FROM Pagos WHERE IdVenta = ? ORDER BY FechaRegistro ASC";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('i', $IdVenta);
            $stmt->execute();
            $res  = $stmt->get_result();
            while ($Pago = $res->fetch_assoc()) {
              $fecha   = htmlspecialchars(substr((string)($Pago['FechaRegistro'] ?? ''),0,10), ENT_QUOTES, 'UTF-8');
              $monto   = money_mx($Pago['Cantidad'] ?? 0, $fmt);
              $estatus = htmlspecialchars((string)($Pago['status'] ?? ''), ENT_QUOTES, 'UTF-8');
              echo "<tr><td>{$fecha}</td><td>{$monto}</td><td>{$estatus}</td></tr>";
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
                  echo money_mx($PagsRe, $fmt);
                  ?>
                </td>
              </tr>
              <tr>
                <td>Moras Pagadas</td>
                <td>
                  <?php
                  $MorRe = $financieras->SumarMora($mysqli, "Cantidad", "Pagos", "IdVenta", $IdVenta);
                  echo money_mx($MorRe, $fmt);
                  ?>
                </td>
              </tr>
              <tr>
                <td>Para liquidar hoy</td>
                <td>
                  <?php
                  if (($Reg['Status'] ?? '') !== "ACTIVO" && ($Reg['Status'] ?? '') !== "ACTIVACION") {
                    $doa = $financieras->SaldoCredito($mysqli, $IdVenta);
                    echo money_mx($doa, $fmt);
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
</body>
</html>
