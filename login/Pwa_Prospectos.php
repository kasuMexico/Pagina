<?php
/********************************************************************************************
 * Qué hace: Panel de “Cartera Prospectos”. Lista y gestiona prospectos asignados según nivel,
 *           lanza modales de información/alta/cancelación y registra auditoría.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

require_once dirname(__DIR__) . '/eia/session.php';
kasu_session_start();
require_once __DIR__ . '/../eia/librerias.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// sesión
if (!isset($_SESSION['Vendedor']) || $_SESSION['Vendedor'] === '') {
  header('Location: https://kasu.com.mx/login');
  exit();
}

// cache-busting para CSS
$VerCache = (string) time();

// ids / nivel (el nivel se usa en el menú)
$IdAsignacion = (int) ($basicas->BuscarCampos($mysqli, 'Id', 'Empleados', 'IdUsuario', $_SESSION['Vendedor']) ?? 0);
$Niv          = (int) ($basicas->BuscarCampos($mysqli, 'Nivel', 'Empleados', 'IdUsuario', $_SESSION['Vendedor']) ?? 0);

// lanzadores de modales
$Ventana = '';   // Ventana1..Ventana6
$Reg     = [];

/* ==========================================================================================
 * BLOQUE: Abrir modal “Nuevo prospecto”
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
if (!empty($_POST['CreaProsp'])) {
  $Ventana = 'Ventana2'; // Nuevo prospecto
}

/* ==========================================================================================
 * BLOQUE: Abrir modal “Generador de Presupuesto”
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
elseif (!empty($_POST['ArmaPres'])) {
  $id = (int) ($_POST['IdPros'] ?? 0);
  if ($id > 0) {
    $st = $pros->prepare('SELECT * FROM prospectos WHERE Id = ? LIMIT 1');
    $st->bind_param('i', $id);
    $st->execute();
    $res = $st->get_result();
    $Reg = $res->fetch_assoc() ?: [];
    $st->close();
    $Ventana = 'Ventana3'; // Generador de Presupuesto
  }
}

/* ==========================================================================================
 * BLOQUE: Abrir modal “Información de prospecto”
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
elseif (!empty($_POST['SelPros'])) {
  $id = (int) ($_POST['IdProspecto'] ?? 0);
  if ($id > 0) {
    $st = $pros->prepare('SELECT * FROM prospectos WHERE Id = ? LIMIT 1');
    $st->bind_param('i', $id);
    $st->execute();
    $res = $st->get_result();
    if ($Reg = ($res->fetch_assoc() ?: null)) {
      $Ventana = 'Ventana1'; // Info prospecto
      $nomVd   = (string) ($basicas->BuscarCampos($mysqli, 'Nombre', 'Empleados', 'IdUsuario', $_SESSION['Vendedor']) ?? '');
    }
    $st->close();
  }
}

/* ==========================================================================================
 * BLOQUE: Abrir modal “Cancelar venta”
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
elseif (!empty($_POST['Cancelar'])) {
  $id = (int) ($_POST['IdPros'] ?? 0);
  if ($id > 0) {
    $st = $pros->prepare('SELECT * FROM prospectos WHERE Id = ? LIMIT 1');
    $st->bind_param('i', $id);
    $st->execute();
    $res = $st->get_result();
    $Reg = $res->fetch_assoc() ?: [];
    $st->close();
    $Ventana = 'Ventana4'; // Cancelar venta
  }
}

/* ==========================================================================================
 * BLOQUE: Abrir modal “Actualizar datos / Distribuidor”
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
elseif (!empty($_POST['ConvDist'])) {
  $id = (int) ($_POST['IdPros'] ?? 0);
  if ($id > 0) {
    $st = $pros->prepare('SELECT * FROM prospectos WHERE Id = ? LIMIT 1');
    $st->bind_param('i', $id);
    $st->execute();
    $res = $st->get_result();
    $Reg = $res->fetch_assoc() ?: [];
    $st->close();
    $Ventana = 'Ventana5'; // Actualizar datos / Dist
  }
}

/* ==========================================================================================
 * BLOQUE: Abrir modal “Nuevo cliente”
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
elseif (!empty($_POST['Generar'])) {
  $id = (int) ($_POST['IdPros'] ?? 0);
  if ($id > 0) {
    $st = $pros->prepare('SELECT * FROM prospectos WHERE Id = ? LIMIT 1');
    $st->bind_param('i', $id);
    $st->execute();
    $res = $st->get_result();
    $Reg = $res->fetch_assoc() ?: [];
    $st->close();
    $Ventana = 'Ventana6'; // Nuevo cliente
  }
}

/* ==========================================================================================
 * BLOQUE: Cancelar prospecto (acción directa)
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
elseif (!empty($_POST['CancelaCte'])) {
  // Auditoría (GPS + fingerprint)
  $ids = $seguridad->auditoria_registrar(
    $mysqli,
    $basicas,
    $_POST,
    'Cancelar_Venta',
    $_POST['Host'] ?? ($_SERVER['PHP_SELF'] ?? '')
  );

  $_GET['Msg'] = 'Se ha cancelado el prospecto';
  $idVenta     = (int) ($_POST['IdVenta'] ?? 0);
  if ($idVenta > 0) {
    $basicas->ActCampo($pros, 'prospectos', 'Cancelacion', 1, $idVenta);
  }
}

// alert opcional
if (isset($_GET['Msg'])) {
  $msg = htmlspecialchars((string) $_GET['Msg'], ENT_QUOTES, 'UTF-8');
  echo "<script>alert('{$msg}');</script>";
}

// métrica (para mesa de control)
$Metodo = 'Vtas';
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F1F7FC">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Cartera Prospectos</title>

  <!-- PWA / iOS -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-180x180.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= htmlspecialchars($VerCache, ENT_QUOTES) ?>">
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
      background:#f59e0b;
      color:#111827;
      font-weight:700;
      box-shadow:0 14px 28px -18px rgba(245,158,11,.6);
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
    .prospect-grid{
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
      gap:12px;
    }
    .prospect-card{
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
    .prospect-card .cta{
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
    .prospect-card .cta span{
      display:block;
      font-size:.78rem;
      color:#e8f0ff;
      font-weight:500;
    }
    .prospect-card .cta strong{
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
      <h4 class="title">Prospectos Asignados</h4>
      <div class="hero-actions">
        <form class="m-0" method="POST" action="<?= htmlspecialchars((string)($_SERVER['PHP_SELF'] ?? ''), ENT_QUOTES) ?>">
          <input type="hidden" name="Host" value="<?= htmlspecialchars((string)($_SERVER['PHP_SELF'] ?? ''), ENT_QUOTES) ?>">
          <button type="submit" name="CreaProsp" value="1" class="btn-crear">
            <i class="material-icons" style="font-size:18px;">person_add</i>
            Nuevo prospecto
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Menú inferior fijo -->
  <section id="Menu">
    <?php require_once __DIR__ . '/html/Menuprinc.php'; ?>
  </section>

  <!-- Modales -->
  <section class="VentanasEMergentes">
    <div class="modal fade" id="Ventana" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <?php
          switch ($Ventana) {
            case 'Ventana1': require __DIR__ . '/html/Informacion_prospecto.php'; break;
            case 'Ventana2': require __DIR__ . '/html/NvoProspecto.php';        break;
            case 'Ventana3': require __DIR__ . '/html/Presupuesto.php';         break;
            case 'Ventana4': require __DIR__ . '/html/CancelUsr.php';           break;
            case 'Ventana5': require __DIR__ . '/html/ActualizarDatos.php';     break;
            case 'Ventana6': require __DIR__ . '/html/NvoCliente.php';          break;
            default: /* nada */ ;
          }
          ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Contenido -->
  <main class="page-content">
    <div class="dashboard-shell">
      <div class="list-card">
        <header>
          <div>
            <p class="chart-subtitle mb-1">Cartera</p>
          </div>
        </header>
        <div class="prospect-grid">
<?php
/********************************************************************************************
 * BLOQUE: Listado por nivel
 * - Niv >= 5: solo mis prospectos
 * - 2 <= Niv <= 4: prospectos de la sucursal y niveles >= Niv
 * - Niv == 1: todos los prospectos de todos
 * Notas:
 *  - Se usan consultas preparadas para variables.
 *  - Mantenemos fetch_row() para conservar índices numéricos usados en la UI existente.
 * Fecha: 05/11/2025 — Revisado por: JCCM
 ********************************************************************************************/

// Helper para pintar tarjeta/form con fila de prospecto
$renderProsForm = function(array $fila, string $extraLabel = '', ?string $idVend = null, ?string $nomSuc = null) {
  // $fila[0] = Id, $fila[4] = Nombre?, $fila[9] = Status? (conservamos índice por compatibilidad)
  $idPros   = (int)($fila[0] ?? 0);
  $status   = (string)($fila[9] ?? '');
  $nombre   = (string)($fila[4] ?? ('Prospecto #' . $idPros));
  $labelTxt = $extraLabel !== '' ? ' - ' . $extraLabel : '';
  $vendTxt  = $idVend ? ' - ' . $idVend : '';
  $sucTxt   = $nomSuc ? ' - ' . $nomSuc : '';
  $detalle  = trim($vendTxt . ' ' . $sucTxt . ' ' . $labelTxt);

  printf("
    <form method='POST' action='%s' class='prospect-card'>
      <input type='number' name='IdProspecto' value='%s' hidden>
      <input type='text'   name='StatusVta'  value='%s' hidden>
      %s
      <span class='badge-status badge %s'>%s</span>
      <button type='submit' name='SelPros' value='1' class='cta %s'>
        <span>%s</span>
        <strong>%s</strong>
      </button>
    </form>
  ",
  htmlspecialchars((string)($_SERVER['PHP_SELF'] ?? ''), ENT_QUOTES),
  $idPros,
  htmlspecialchars($status, ENT_QUOTES),
  $idVend ? "<input type='text' name='IdVendedor' value='".htmlspecialchars($idVend, ENT_QUOTES)."' hidden>" : '',
  htmlspecialchars($status, ENT_QUOTES),
  htmlspecialchars($status, ENT_QUOTES),
  htmlspecialchars($status !== '' ? $status : 'btn btn-primary', ENT_QUOTES),
  htmlspecialchars($detalle !== '' ? $detalle : 'Asignado a ti', ENT_QUOTES),
  htmlspecialchars($nombre, ENT_QUOTES)
  );
};

if ($Niv >= 5) {
  // Mis prospectos
  $VendeId = (int) ($basicas->BuscarCampos($mysqli, 'Id', 'Empleados', 'IdUsuario', $_SESSION['Vendedor']) ?? 0);

  $st = $pros->prepare('SELECT * FROM prospectos WHERE Asignado = ? AND Cancelacion = 0');
  $st->bind_param('i', $VendeId);
  $st->execute();
  $rs = $st->get_result();
  while ($fila = $rs->fetch_row()) {
    $renderProsForm($fila);
  }
  $st->close();

} elseif ($Niv <= 4 && $Niv >= 2) {
  // Mi sucursal, niveles >= Niv
  $IdSuc  = (int) ($basicas->BuscarCampos($mysqli, 'Sucursal', 'Empleados', 'IdUsuario', $_SESSION['Vendedor']) ?? 0);
  $NomSuc = (string) ($basicas->BuscarCampos($mysqli, 'NombreSucursal', 'Sucursal', 'Id', $IdSuc) ?? '');

  $stEmp = $mysqli->prepare("SELECT Id, IdUsuario FROM Empleados WHERE Nombre <> 'Vacante' AND Nivel >= ? AND Sucursal = ?");
  $stEmp->bind_param('ii', $Niv, $IdSuc);
  $stEmp->execute();
  $resEmp = $stEmp->get_result();

  $stPros = $pros->prepare('SELECT * FROM prospectos WHERE Asignado = ? AND Cancelacion = 0');

  while ($emp = $resEmp->fetch_assoc()) {
    $empId = (int) $emp['Id'];
    $stPros->bind_param('i', $empId);
    $stPros->execute();
    $rs = $stPros->get_result();
    while ($fila = $rs->fetch_row()) {
      $renderProsForm($fila, '', (string)$emp['IdUsuario'], $NomSuc);
    }
  }
  $stPros->close();
  $stEmp->close();

} else { // Niv == 1
  // Todos
  $stEmp = $mysqli->prepare("SELECT Id, IdUsuario, Sucursal FROM Empleados WHERE Nombre <> 'Vacante' AND Nivel >= ?");
  $stEmp->bind_param('i', $Niv);
  $stEmp->execute();
  $resEmp = $stEmp->get_result();

  $stPros = $pros->prepare('SELECT * FROM prospectos WHERE Asignado = ? AND Cancelacion = 0');

  while ($emp = $resEmp->fetch_assoc()) {
    $empId = (int) $emp['Id'];
    $nomS  = (string) ($basicas->BuscarCampos($mysqli, 'NombreSucursal', 'Sucursal', 'Id', (int)$emp['Sucursal']) ?? '');

    $stPros->bind_param('i', $empId);
    $stPros->execute();
    $rs = $stPros->get_result();
    while ($fila = $rs->fetch_row()) {
      $renderProsForm($fila, '', (string)$emp['IdUsuario'], $nomS);
    }
  }
  $stPros->close();
  $stEmp->close();
}
?>
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
  <!-- Abrir modal solo si corresponde -->
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    <?php if (!empty($Ventana)) : ?>
      $('#Ventana').modal('show');
    <?php endif; ?>
  });
  </script>
</body>
</html>
