<?php
/********************************************************************************************
 * Archivo: Pwa_Clientes.php
 * Qué hace: Muestra la cartera de clientes según el nivel del usuario y lanza modales para:
 *           1) Ver estado de cliente (Ventana1), 2) Registrar pago (Ventana2),
 *           3) Crear nuevo cliente/prospecto (Ventana4).
 * Compatibilidad: actualizado para PHP 8.2 evitando valores null en escapes y manteniendo
 *                 la lógica y clases CSS originales (las clases = Status para colores).
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

require_once dirname(__DIR__) . '/eia/session.php';
kasu_session_start();
require_once __DIR__ . '/../eia/librerias.php';
date_default_timezone_set('America/Mexico_City');

/* ===== Bloque utilidades (escape HTML) — 05/11/2025, JCCM ===== */
if (!function_exists('h')) {
  function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

/* ===== Sesión obligatoria — 05/11/2025, JCCM ===== */
if (empty($_SESSION['Vendedor'])) {
  header('Location: https://kasu.com.mx/login');
  exit();
}

/* ===== Defaults y variables de contexto — 05/11/2025, JCCM ===== */
$VerCache = isset($VerCache) ? (string)$VerCache : '1';
$Niv      = (int)$basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
$IdVen    = (int)$basicas->BuscarCampos($mysqli, "Id",    "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
$Ventana  = null;         // Ventana a cargar (Ventana1..4)
$Lanzar   = null;         // '#Ventana' si hay que abrir modal
$Metodo   = "Vtas";       // Métrica para mesa de control

// Variables que pueden usar los includes
$Reg = $Pago1 = $Pago = $Saldo = $PagoPend = $Status = null;

/* ===== POST: selección de cliente para abrir modal — 05/11/2025, JCCM ===== */
$selCte = isset($_POST['SelCte']) ? trim((string)$_POST['SelCte']) : null;

// Selección desde listado: abrir estado/pagos
if ($selCte !== null && $selCte !== '' && $selCte !== 'CrearProspecto' ) {
  $idVentaPost = (int)($_POST['IdVenta'] ?? 0);
  if ($idVentaPost > 0) {
    // Nota: se conserva lógica y consulta tal cual, sólo se castea para evitar null
    $ventaSql = "SELECT * FROM Venta WHERE Id = {$idVentaPost} LIMIT 1";
    if ($res = $mysqli->query($ventaSql)) {
      if ($Reg = $res->fetch_assoc()) {
        $statusVta = $_POST['StatusVta'] ?? ($Reg['Status'] ?? '');

        if (!in_array($statusVta, ['ACTIVO', 'ACTIVACION'], true)) {
          // Cálculos financieros (se conserva lógica original)
          $Pago1    = $financieras->Pago($mysqli, (int)$Reg['Id']);
          $Pago     = number_format($Pago1, 2);
          $Saldo    = '$' . number_format($financieras->SaldoCredito($mysqli, (int)$Reg['Id']), 2);
          $PagoPend = $financieras->PagosPend($mysqli, (int)$Reg['Id']);

          // Estado de mora/corriente si existe el método
          if (method_exists($financieras, 'estado_mora_corriente')) {
            $StatVtas = $financieras->estado_mora_corriente((int)$Reg['Id']);
            $Status   = (!empty($StatVtas['estado']) && $StatVtas['estado'] === 'AL CORRIENTE') ? 'Pago' : 'Mora';
          } else {
            $Status = 'Pago';
          }
        }
        // Configurar modal
        $Ventana = 'Ventana1';
        $Lanzar  = '#Ventana';
      }
    }
  }
}

/* ===== Lanzadores directos de ventana — 05/11/2025, JCCM ===== */
if ($selCte === "CrearProspecto") {
  $Ventana = "Ventana4";
  $Lanzar  = "#Ventana";
} elseif ($selCte === "Agregar Pago") {
  $Ventana = "Ventana2";
  $Lanzar  = "#Ventana";
} elseif ($selCte === "Promesa de Pago"){
  $Ventana = "Ventana3";
  $Lanzar  = "#Ventana";
}

/* ===== Mensaje opcional por GET — 05/11/2025, JCCM ===== */
if (isset($_GET['Msg'])) {
  echo "<script>alert('".h($_GET['Msg'])."');</script>";
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="theme-color" content="#F1F7FC">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Cartera Clientes</title>

  <!-- Manifest / iOS -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="/login/assets/css/Menu_Superior.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="/login/assets/css/pwa-core.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="/login/assets/css/pwa-components.css?v=<?= h((string)$VerCache) ?>">
</head>

<body onload="localize()">

  <!-- TOP BAR Pwa_Clientes.php-->
  <div class="topbar">
    <div class="topbar-left">
      <img alt="KASU" src="/login/assets/img/kasu_logo.jpeg">
      <div>
        <p class="eyebrow mb-0">Panel móvil</p>
        <h4 class="title">Cartera de Clientes</h4>
      </div>
    </div>
    <div class="topbar-actions">
      <!-- Botón crear prospecto (lógica intacta) -->
      <form class="m-0" method="POST" action="<?php echo h($_SERVER['PHP_SELF']); ?>">
        <input type="hidden" name="Host" value="<?php echo h($_SERVER['PHP_SELF']); ?>">
        <input type="hidden" name="SelCte" value="CrearProspecto">
        <button type="submit" class="action-btn success">
          <i class="material-icons">person_add</i>
          <span>Nuevo Cliente</span>
        </button>
      </form>
    </div>
  </div>

  <!-- Menú inferior fijo -->
  <section id="Menu">
    <?php require_once 'html/Menuprinc.php'; ?>
  </section>

  <!-- Modal contenedor -->
  <div class="modal fade" id="Ventana" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <?php
          // Carga de vistas de modal. Lógica intacta.
          if ($Ventana === "Ventana1") {
            require 'html/EmEdoCte.php';
          } elseif ($Ventana === "Ventana2") {
            require 'html/Emergente_Registrar_Pago.php';
          } elseif ($Ventana === "Ventana3") {
            require 'html/Emergente_Promesa_Pago.php';
          } elseif ($Ventana === "Ventana4") {
            require 'html/NvoCliente.php';
          }
        ?>
      </div>
    </div>
  </div>

  <!-- Contenido listado -->
  <main class="page-content">
    <div class="dashboard-shell">
      <div class="card-base list-card">
        <header>
          <div>
            <p class="chart-subtitle mb-1">Cartera</p>
          </div>
        </header>
        <div class="client-grid">
          <?php
          /*********************************************************************************
           * Listado por nivel — 05/11/2025, JCCM
           * NOTA: Se mantiene el uso de clases CSS basadas en el campo Status tal como estaba,
           *       porque tu hoja de estilos pinta por esas clases. No se mapea ni se altera.
           *********************************************************************************/

          if ($Niv >= 5) {
            $usr = (string)$_SESSION["Vendedor"];
            $Ventas = "SELECT * FROM Venta WHERE Usuario = '" . $mysqli->real_escape_string($usr) . "'";
            if ($resultado = $mysqli->query($Ventas)) {
              while ($fila = $resultado->fetch_assoc()) {
                ?>
                <form method="POST" action="<?php echo h($_SERVER['PHP_SELF']); ?>" class="client-card">
                  <input type="number" name="IdVenta"   value="<?php echo (int)$fila['Id']; ?>" hidden>
                  <input type="text"   name="StatusVta" value="<?php echo h($fila['Status']); ?>" hidden>
                  <span class="badge-status badge <?php echo h($fila['Status']); ?>"><?php echo h($fila['Status']); ?></span>
                  <button type="submit" name="SelCte" class="cta <?php echo h($fila['Status']); ?>">
                    <span>Cliente</span>
                    <strong><?php echo h($fila['Nombre']); ?></strong>
                  </button>
                </form>
                <?php
              }
            }

          } elseif ($Niv <= 4 && $Niv >= 2) {
            $IdSuc  = (int)$basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
            $NomSuc = (string)$basicas->BuscarCampos($mysqli, "NombreSucursal", "Sucursal", "Id", $IdSuc);
            $sqal   = "SELECT * FROM Empleados WHERE Nombre!='Vacante' AND Nivel>='{$Niv}' AND Sucursal=".(int)$IdSuc;

            if ($r = $mysqli->query($sqal)) {
              foreach ($r as $emp) {
                $usr = (string)$emp["IdUsuario"];
                $Ventas = "SELECT * FROM Venta WHERE Usuario = '" . $mysqli->real_escape_string($usr) . "'";
                if ($resultado = $mysqli->query($Ventas)) {
                  while ($fila = $resultado->fetch_assoc()) {
                    ?>
                    <form method="POST" action="<?php echo h($_SERVER['PHP_SELF']); ?>" class="client-card">
                      <input type="number" name="IdVenta"    value="<?php echo (int)$fila['Id']; ?>" hidden>
                      <input type="text"   name="StatusVta"  value="<?php echo h($fila['Status']); ?>" hidden>
                      <input type="text"   name="IdVendedor" value="<?php echo h($usr); ?>" hidden>
                      <span class="badge-status badge <?php echo h($fila['Status']); ?>"><?php echo h($fila['Status']); ?></span>
                      <button type="submit" name="SelCte" class="cta <?php echo h($fila['Status']); ?>">
                        <span><?php echo h($usr . ' · ' . $NomSuc); ?></span>
                        <strong><?php echo h($fila['Nombre']); ?></strong>
                      </button>
                    </form>
                    <?php
                  }
                }
              }
            }

          } elseif ($Niv == 1) {
            $IdSuc  = (int)$basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
            $NomSuc = (string)$basicas->BuscarCampos($mysqli, "NombreSucursal", "Sucursal", "Id", $IdSuc);
            $sqal   = "SELECT * FROM Empleados WHERE Nombre!='Vacante' AND Nivel>='{$Niv}'";

            if ($r = $mysqli->query($sqal)) {
              foreach ($r as $emp) {
                $usr = (string)$emp["IdUsuario"];
                $Ventas = "SELECT * FROM Venta WHERE Usuario = '" . $mysqli->real_escape_string($usr) . "'";
                if ($resultado = $mysqli->query($Ventas)) {
                  while ($fila = $resultado->fetch_assoc()) {
                    ?>
                    <form method="POST" action="<?php echo h($_SERVER['PHP_SELF']); ?>" class="client-card">
                      <input type="number" name="IdVenta"    value="<?php echo (int)$fila['Id']; ?>" hidden>
                      <input type="text"   name="StatusVta"  value="<?php echo h($fila['Status']); ?>" hidden>
                      <input type="text"   name="IdVendedor" value="<?php echo h($usr); ?>" hidden>
                      <span class="badge-status badge <?php echo h($fila['Status']); ?>"><?php echo h($fila['Status']); ?></span>
                      <button type="submit" name="SelCte" class="cta <?php echo h($fila['Status']); ?>">
                        <span><?php echo h($usr . ' · ' . $NomSuc); ?></span>
                        <strong><?php echo h($fila['Nombre']); ?></strong>
                      </button>
                    </form>
                    <?php
                  }
                }
              }
            }
          }
          ?>
        </div>
      </div>
    </div>
  </main>

  <!-- JS (una sola versión para compatibilidad visual del sitio) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>

  <!-- Scripts propios -->
  <script src="Javascript/finger.js?v=3"></script>
  <script src="Javascript/Seleccionar.js"></script>
  <script src="Javascript/localize.js?v=3"></script>
  <script src="Javascript/Inyectar_gps_form.js"></script>

  <!-- Abrir modal cuando corresponde -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      <?php if (!empty($Lanzar)) : ?>
      if (window.jQuery) { $('<?php echo h($Lanzar); ?>').modal('show'); }
      <?php endif; ?>
    });
  </script>
</body>
</html>
