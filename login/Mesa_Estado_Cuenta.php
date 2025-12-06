<?php
/********************************************************************************************
 * Qué hace: Página "Estado de Cuenta". Muestra datos de la venta, cliente y movimientos,
 *           y permite enviar por correo o descargar el PDF. Adaptada a PHP 8.2:
 *           - mysqli en modo excepciones
 *           - consultas preparadas
 *           - sanitización de salidas
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 * Archivo: Mesa_Estado_Cuenta.php
 ********************************************************************************************/

declare(strict_types=1);

// =================== Sesión y dependencias ===================
require_once dirname(__DIR__) . '/eia/session.php';
kasu_session_start();
require_once __DIR__ . '/../eia/librerias.php';
require_once __DIR__ . '/php/mesa_helpers.php';
date_default_timezone_set('America/Mexico_City');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header_remove('X-Powered-By');

// =================== Utilidades ===================
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// =================== Guardia de sesión ===================
if (empty($_SESSION['Vendedor'])) {
    header('Location: https://kasu.com.mx/login');
    exit();
}

// =================== Entrada y constantes ===================
$busqueda = $_POST['busqueda'] ?? ($_GET['busqueda'] ?? '');
$tel      = '7208177632'; // Teléfono de la empresa

// =================== Validación de conexión ===================
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    http_response_code(500);
    exit('Error de conexión.');
}

// =================== Cargar Venta ===================
$venta = null;
if ($busqueda === '' || !ctype_digit((string)$busqueda)) {
    $busqueda = '0';
}
$stmt = $mysqli->prepare('SELECT * FROM Venta WHERE Id = ? LIMIT 1');
$idVenta = (int)$busqueda;
$stmt->bind_param('i', $idVenta);
$stmt->execute();
if ($res = $stmt->get_result()) {
    $venta = $res->fetch_assoc() ?: null;
}
$stmt->close();

// =================== Si no hay venta ===================
if (!$venta) {
    ?>
<!DOCTYPE html>
<html lang="es-MX" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F3F4F6">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Estado de Cuenta</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="/login/assets/css/Menu_Superior.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="/login/assets/css/pwa-core.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="/login/assets/css/pwa-components.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="/login/assets/css/vistas.css?v=<?= h((string)$VerCache ?? time()) ?>">
</head>
<body onload="localize()">
  <div class="empty-card">
    <h1>Venta no encontrada</h1>
    <p>No pudimos localizar el estado de cuenta solicitado.</p>
    <a class="action-btn" href="Mesa_Clientes.php">
      <i class="material-icons">arrow_back</i>
      Regresar
    </a>
  </div>
</body>
</html>
    <?php
    exit;
}

// =================== Cargar Contacto ===================
$datos = null;
$idContactoVenta = (int)($venta['IdContact'] ?? 0);
$stmt = $mysqli->prepare('SELECT * FROM Contacto WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $idContactoVenta);
$stmt->execute();
if ($res = $stmt->get_result()) {
    $datos = $res->fetch_assoc() ?: null;
}
$stmt->close();

// =================== Cargar Usuario ===================
$persona = null;
$stmt = $mysqli->prepare('SELECT * FROM Usuario WHERE IdContact = ? LIMIT 1');
$stmt->bind_param('i', $idContactoVenta);
$stmt->execute();
if ($res = $stmt->get_result()) {
    $persona = $res->fetch_assoc() ?: null;
}
$stmt->close();

// =================== Cálculos de saldo y tipo de compra ===================
$saldo = '0.00';
if ($venta['Status'] === "ACTIVO" || $venta['Status'] === "ACTIVACION") {
    $saldo = number_format(0, 2);
} else {
    $saldo_val = $financieras->SaldoCredito($mysqli, (int)$venta['Id']);
    $saldo = number_format((float)$saldo_val, 2);
}
if ((int)$venta['NumeroPagos'] >= 2) {
    $Credito = "Compra a crédito; " . (int)$venta['NumeroPagos'] . " Meses";
} else {
    $Credito = "Compra de contado";
}

// =================== Token correo ===================
$_SESSION['mail_token'] = bin2hex(random_bytes(16));

// =================== Parámetro nombre para regresar ===================
$name = $_POST['nombre'] ?? ($_GET['name'] ?? "");

// =================== Alertas por GET ===================
if (isset($_GET['Vt']) && (int)$_GET['Vt'] === 1) {
    $msg = isset($_GET['Msg']) ? (string)$_GET['Msg'] : '';
    echo "<script>window.addEventListener('load',()=>alert(".json_encode($msg, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."));</script>";
}

// =================== IDs auxiliares ===================
$personaId  = $persona['Id']  ?? ($persona['id']  ?? '');
$contactoId = $datos['id']    ?? ($datos['Id']    ?? '');
$ventaId    = $venta['Id']    ?? '';
$producto   = $venta['Producto'] ?? '';
$VerCache   = $VerCache ?? '1';
?>
<!DOCTYPE html>
<html lang="es-MX" dir="ltr">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
   <meta name="theme-color" content="#F3F4F6">
   <title>Estado de Cuenta</title>
   <link rel="shortcut icon" href="../assets/images/logokasu.ico">
   <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
   <link rel="manifest" href="/login/manifest.webmanifest">
   <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
   <meta name="apple-mobile-web-app-capable" content="yes">
   <meta name="apple-mobile-web-app-status-bar-style" content="default">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
   <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
   <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= h($VerCache) ?>">
   <link rel="stylesheet" href="/login/assets/css/Menu_Superior.css?v=<?= h($VerCache) ?>">
   <link rel="stylesheet" href="/login/assets/css/pwa-core.css?v=<?= h($VerCache) ?>">
   <link rel="stylesheet" href="/login/assets/css/pwa-components.css?v=<?= h($VerCache) ?>">
   <link rel="stylesheet" href="/login/assets/css/vistas.css?v=<?= h((string)$VerCache ?? time()) ?>">
</head>
<body onload="localize()">
    <!-- TOP BAR Mesa_Estado_Cuenta.php-->
  <div class="topbar">
    <div class="topbar-left">
      <img src="/login/assets/img/kasu_logo.jpeg" alt="KASU">
      <div>
        <p class="eyebrow mb-0">Mesa</p>
        <h4 class="title">Estado de Cuenta</h4>
      </div>
    </div>
    <div class="topbar-actions">
      <form action="Mesa_Clientes.php" method="post" class="m-0">
        <input type="text" name="nombre" value="<?= h($name); ?>" hidden>
        <button type="submit" name="Accion" value="Regresar" class="action-btn" title="Regresar a cliente">
          <i class="material-icons">arrow_back</i>
          <span>Regresar</span>
        </button>
      </form>

      <form action="../eia/EnviarCorreo.php" method="post" class="m-0">
          <div id="Gps" style="display:none;"></div>
          <div data-fingerprint-slot></div>
          <input type="text" name="nombre" value="<?= h($name); ?>" hidden>
          <input type="text" name="Host" value="<?= h($_SERVER['PHP_SELF']); ?>" hidden>
          <input type="number" name="IdVenta" value="<?= h((string)$ventaId); ?>" hidden>
          <input type="number" name="IdContact" value="<?= h((string)$contactoId); ?>" hidden>
          <input type="number" name="IdUsuario" value="<?= h((string)$personaId); ?>" hidden>
          <input type="text"   name="Producto" value="<?= h($producto); ?>" hidden>
          <input type="text" name="IdVenta"   value="<?= h((string)$busqueda); ?>" hidden>
          <input type="text" name="FullName"  value="<?= h($persona['Nombre'] ?? ''); ?>" hidden>
          <input type="text" name="Email"     value="<?= h($datos['Mail'] ?? ''); ?>" hidden>
          <input type="text" name="Asunto"    value="ENVIO ARCHIVO" hidden>
          <input type="text" name="Descripcion" value="Estado de Cuenta" hidden>
          <input type="hidden" name="mail_token" value="<?= h($_SESSION['mail_token']); ?>">
          <button type="submit" name="EnviarEdoCta" value="Enviar" class="action-btn primary" title="Enviar estado de cuenta">
            <i class="material-icons">email</i>
            <span>Enviar</span>
          </button>
      </form>

      <a class="action-btn success" title="Descargar PDF"
         href="https://kasu.com.mx/login/Generar_PDF/Estado_Cuenta_pdf.php?busqueda=<?= base64_encode((string)$busqueda); ?>">
        <i class="material-icons">download</i>
        <span>PDF</span>
      </a>
    </div>
  </div>

  <section id="Menu">
    <?php require_once 'html/Menuprinc.php'; ?>
  </section>

  <main class="page-content">
    <div class="statement-shell">
      <!-- Encabezado de estado -->
      <div class="statement-header">
        <h1><?= h($persona['Nombre'] ?? ''); ?></h1>
        <div class="meta-row">
          <span>Producto: <?= h($producto); ?></span>
          <span>Saldo: $<?= h($saldo); ?> MXN</span>
          <span><?= h($Credito); ?></span>
          <span class="badge-status <?= strtolower((string)($venta['Status'] ?? '')) === 'activo' ? 'active' : ''; ?>">
            <span>Status:</span>
            <strong><?= h($venta['Status'] ?? ''); ?></strong>
          </span>
        </div>
      </div>

      <!-- Resumen financiero corto -->
      <div class="summary-row">
        <div class="summary-card">
          <div class="label">Cliente</div>
          <p class="value"><?= h($persona['Nombre'] ?? ''); ?></p>
        </div>
        <div class="summary-card">
          <div class="label">Fecha de contratación</div>
          <p class="value"><?= h(substr((string)($venta['FechaRegistro'] ?? ''), 0, 10)); ?></p>
        </div>
        <div class="summary-card">
          <div class="label">Saldo de la cuenta</div>
          <p class="value">$<?= h($saldo); ?> MXN</p>
        </div>
      </div>

      <!-- Datos Empresa / Cliente -->
      <div class="panel">
        <header>
          <p class="panel-title">Datos generales</p>
        </header>
        <div class="info-grid">
          <div>
            <ul class="info-list">
              <li>
                <span class="label">Razón social</span>
                <span class="value">KASU, Servicios a Futuro S.A. de C.V.</span>
              </li>
              <li>
                <span class="label">Fideicomiso</span>
                <span class="value">F/0003 Gastos Funerarios</span>
              </li>
              <li>
                <span class="label">Ubicación</span>
                <span class="value">Atlacomulco, Estado de México, C.P. 50450</span>
              </li>
              <li>
                <span class="label">Teléfono</span>
                <span class="value"><?= h($tel); ?></span>
              </li>
            </ul>
          </div>
          <div>
            <ul class="info-list">
              <li>
                <span class="label">Nombre del cliente</span>
                <span class="value"><?= h($persona['Nombre'] ?? ''); ?></span>
              </li>
              <li>
                <span class="label">CURP</span>
                <span class="value"><?= h($persona['ClaveCurp'] ?? ''); ?></span>
              </li>
              <li>
                <span class="label">Fecha de registro</span>
                <span class="value"><?= h(substr((string)($venta['FechaRegistro'] ?? ''), 0, 10)); ?></span>
              </li>
              <li>
                <span class="label">Última modificación</span>
                <span class="value"><?= h(substr((string)($persona['FechaRegistro'] ?? ''), 0, 10)); ?></span>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Servicio y contacto -->
      <div class="panel">
        <header>
          <p class="panel-title">Servicio y datos de contacto</p>
        </header>
        <div class="info-grid">
          <div class="info-item">
            <span class="label">Dirección</span>
            <p class="value">
              <?php
                if (isset($datos['calle']) && $datos['calle'] !== '') {
                  echo h($datos['calle']);
                } else {
                  echo '<span class="text-danger font-weight-bold">No disponible</span>';
                }
              ?>
            </p>
          </div>
          <div class="info-item">
            <span class="label">Teléfono</span>
            <p class="value mb-0"><?= h($datos['Telefono'] ?? ''); ?></p>
          </div>
          <div class="info-item">
            <span class="label">Correo electrónico</span>
            <p class="value mb-0"><?= h($datos['Mail'] ?? ''); ?></p>
          </div>
          <div class="info-item">
            <span class="label">Producto contratado</span>
            <p class="value mb-0"><?= h($venta['Producto'] ?? ''); ?></p>
          </div>
          <div class="info-item">
            <span class="label">Número de activación</span>
            <p class="value mb-0"><?= h($venta['IdFIrma'] ?? ''); ?></p>
          </div>
          <div class="info-item">
            <span class="label">Tipo de compra</span>
            <p class="value mb-0"><?= h($Credito); ?></p>
          </div>
        </div>
      </div>

      <!-- Movimientos -->
      <div class="panel table-panel">
        <header>
          <p class="panel-title">Histórico de movimientos</p>
        </header>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Concepto</th>
                <th class="text-right">Saldo</th>
                <th class="text-right">Debe</th>
                <th class="text-right">Haber</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?= h(substr((string)$venta['FechaRegistro'], 0, 10)); ?></td>
                <td>Contratación del servicio <?= h($venta['Producto'] ?? ''); ?></td>
                <td class="text-right"><?= number_format((float)($venta['CostoVenta'] ?? 0), 2); ?></td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
              </tr>
              <?php
              $stmt = $mysqli->prepare('SELECT * FROM Pagos WHERE IdVenta = ? ORDER BY FechaRegistro ASC, Id ASC');
              $stmt->bind_param('i', $idVenta);
              $stmt->execute();
              if ($resultado = $stmt->get_result()) {
                  while ($pago = $resultado->fetch_assoc()) {
                      $fec = h(substr((string)$pago['FechaRegistro'], 0, 10));
                      $sts = h($pago['status'] ?? '');
                      $prd = h($venta['Producto'] ?? '');
                      $cant = number_format((float)($pago['Cantidad'] ?? 0), 2);
                      echo <<<HTML
                          <tr>
                              <td>{$fec}</td>
                              <td>{$sts} de servicio {$prd}</td>
                              <td class="text-right">-</td>
                              <td class="text-right">{$cant}</td>
                              <td class="text-right">$ 0.00</td>
                          </tr>
HTML;
                  }
              }
              $stmt->close();
              ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Totales -->
      <div class="panel totals-panel">
        <header>
          <p class="panel-title">Resumen del saldo</p>
        </header>
          <table class="table table-borderless mb-0">
            <tbody>
              <tr>
                <td class="label">Saldo de la cuenta</td>
                <td class="value text-right">$<?= h($saldo); ?> MXN</td>
              </tr>
            </tbody>
          </table>
      </div>

    </div>
  </main>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script type="text/javascript" src="Javascript/localize.js"></script>
<script src="Javascript/fingerprint-core-y-utils.js"></script>
<script src="Javascript/finger.js" defer></script>
</body>
</html>
