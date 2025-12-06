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
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?php echo h($VerCache); ?>">
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
    .hero-actions{
      margin-left:auto;
      display:flex;
      align-items:center;
      gap:10px;
    }
    .btn-crear{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:10px 14px;
      border-radius:12px;
      border:none;
      background:#22c55e;
      color:#fff;
      font-weight:700;
      box-shadow:0 14px 28px -18px rgba(34,197,94,.65);
    }
    .list-card{
      border-radius:20px;
      padding:16px;
      background:rgba(255,255,255,.94);
      backdrop-filter:blur(16px);
      box-shadow:0 20px 45px rgba(15,23,42,.12);
      border:1px solid rgba(226,232,240,.9);
    }
    .list-card header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      margin-bottom:12px;
    }
    .client-grid{
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
      gap:12px;
    }
    .client-card{
      position:relative;
      padding:14px 14px 12px;
      border-radius:16px;
      background:#f9fbff;
      border:1px solid #e5e9f0;
      box-shadow:0 10px 26px rgba(15,23,42,.08);
    }
    .client-card .badge-status{
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
    .client-card .cta{
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
    .client-card .cta span{
      display:block;
      font-size:.78rem;
      color:#e8f0ff;
      font-weight:500;
    }
    .client-card .cta strong{
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

  <!-- Top bar fija -->
  <div class="topbar">
    <div class="d-flex align-items-center w-100">
      <h4 class="title">Cartera de Clientes</h4>
      <div class="hero-actions">
        <!-- Botón crear prospecto (lógica intacta) -->
        <form class="m-0" method="POST" action="<?php echo h($_SERVER['PHP_SELF']); ?>">
          <input type="hidden" name="Host" value="<?php echo h($_SERVER['PHP_SELF']); ?>">
          <input type="hidden" name="SelCte" value="CrearProspecto">
          <button type="submit" class="btn-crear">
            <i class="material-icons" style="font-size:18px;">person_add</i>
            Nuevo Cliente
          </button>
        </form>
      </div>
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
      <div class="list-card">
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
