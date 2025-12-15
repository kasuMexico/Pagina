<?php
/********************************************************************************************
 * Qué hace: Panel de “Cartera Prospectos”. Lista y gestiona prospectos asignados según nivel,
 *           lanza modales de información/alta/cancelación y registra auditoría.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 * Archivo: Pwa_Prospectos.php
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
  <link rel="stylesheet" href="/login/assets/css/Menu_Superior.css?v=<?= htmlspecialchars($VerCache, ENT_QUOTES) ?>">
  <link rel="stylesheet" href="/login/assets/css/pwa-core.css?v=<?= htmlspecialchars($VerCache, ENT_QUOTES) ?>">
  <link rel="stylesheet" href="/login/assets/css/pwa-components.css?v=<?= htmlspecialchars($VerCache, ENT_QUOTES) ?>">
  <style>
    .prospect-card .service-type{
      display:block;
      font-size:12px;
      font-weight:700;
      color:#fff;
      text-transform:uppercase;
      margin-bottom:2px;
    }
    /* Botones coloreados por tipo de servicio (usa colores de styles.min.css) */
    .prospect-card .cta.FUNERARIO   { background:#800588 !important; border:1px solid #da46e5 !important; color:#fff; }
    .prospect-card .cta.SEGURIDAD   { background:#4e71f0 !important; border:1px solid #7892ef !important; color:#fff; }
    .prospect-card .cta.TRANSPORTE  { background:#f7be11 !important; border:1px solid #f2ca54 !important; color:#fff; }
    .prospect-card .cta.DISTRIBUIDOR{ background:#2107ec !important; border:1px solid #6c5af0 !important; color:#fff; }
    .prospect-card .cta.RETIRO      { background:#02d45d !important; border:1px solid #68f8a6 !important; color:#fff; }
  </style>
</head>
<body onload="localize()">

  <!-- TOP BAR Pwa_Prospectos.php-->
  <div class="topbar">
    <div class="topbar-left">
      <img alt="KASU" src="/login/assets/img/kasu_logo.jpeg">
      <div>
        <p class="eyebrow mb-0">Panel móvil</p>
        <h4 class="title">Prospectos asignados</h4>
      </div>
    </div>
    <div class="topbar-actions">
      <form class="m-0" method="POST" action="<?= htmlspecialchars((string)($_SERVER['PHP_SELF'] ?? ''), ENT_QUOTES) ?>">
        <input type="hidden" name="Host" value="<?= htmlspecialchars((string)($_SERVER['PHP_SELF'] ?? ''), ENT_QUOTES) ?>">
        <button type="submit" name="CreaProsp" value="1" class="action-btn success">
          <i class="material-icons">person_add</i>
          <span>Nuevo prospecto</span>
        </button>
      </form>
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
      <div class="card-base list-card">
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
  // Compatibilidad: acepta arreglos asociativos o numéricos
  $idPros   = (int)($fila['Id'] ?? $fila[0] ?? 0);
  $servicio = (string)($fila['Servicio_Interes'] ?? $fila[9] ?? $fila[11] ?? '');
  $status   = (string)($fila['Papeline'] ?? $fila['Papeline'] ?? $fila[11] ?? $fila[9] ?? '');
  $nombre   = (string)($fila['FullName'] ?? $fila[4] ?? ('Prospecto #' . $idPros));
  $labelTxt = $extraLabel !== '' ? ' - ' . $extraLabel : '';
  $vendTxt  = $idVend ? '' . $idVend : '';
  $sucTxt   = $nomSuc ? ' - ' . $nomSuc : '';
  $detalle  = trim($vendTxt . ' ' . $sucTxt . ' ' . $labelTxt);
  $action   = htmlspecialchars((string)($_SERVER['PHP_SELF'] ?? ''), ENT_QUOTES);
  $hiddenVend = $idVend ? "<input type='text' name='IdVendedor' value='".htmlspecialchars($idVend, ENT_QUOTES)."' hidden>" : '';
  $clsStatus  = htmlspecialchars($status !== '' ? $status : 'btn', ENT_QUOTES);
  $clsServ    = htmlspecialchars($servicio !== '' ? $servicio : '', ENT_QUOTES);
  $serviceTxt = htmlspecialchars($servicio !== '' ? $servicio : 'SERVICIO', ENT_QUOTES);
  $detalleTxt = htmlspecialchars($detalle !== '' ? $detalle : 'Asignado a ti', ENT_QUOTES);
  $nombreTxt  = htmlspecialchars($nombre, ENT_QUOTES);

  echo "
    <form method='POST' action='{$action}' class='prospect-card'>
      <input type='number' name='IdProspecto' value='{$idPros}' hidden>
      <input type='text'   name='StatusVta'  value='{$clsStatus}' hidden>
      {$hiddenVend}
      <button type='submit' name='SelPros' value='1' class='cta {$clsStatus} {$clsServ}'>
        <span class=\"service-type\">{$serviceTxt}</span>
        <span class=\"service-label\">{$detalleTxt}</span>
        <strong>{$nombreTxt}</strong>
      </button>
    </form>
    ";
};

if ($Niv >= 5) {
  // Mis prospectos
  $VendeId = (int) ($basicas->BuscarCampos($mysqli, 'Id', 'Empleados', 'IdUsuario', $_SESSION['Vendedor']) ?? 0);

  $st = $pros->prepare('SELECT * FROM prospectos WHERE Asignado = ? AND Cancelacion = 0');
  $st->bind_param('i', $VendeId);
  $st->execute();
  $rs = $st->get_result();
  while ($fila = $rs->fetch_assoc()) {
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
    while ($fila = $rs->fetch_assoc()) {
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
    while ($fila = $rs->fetch_assoc()) {
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
  <script>
    // Marca contexto PWA (SameSite=None) para Chrome en modo standalone
    (function markPwaContext(){
      var isStandalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) ||
        (typeof window.navigator.standalone !== 'undefined' && window.navigator.standalone);
      if (isStandalone) {
        try { document.cookie = 'KASU_PWA=1; Path=/; SameSite=None; Secure'; } catch (e) {}
      }
    })();

    // Evita múltiples toques para abrir el modal; el primer clic dispara el POST y evita repetir
    $(function(){
      $('.prospect-card button[type="submit"]').on('click', function(){
        var $btn = $(this);
        if ($btn.data('clicking')) {
          return false;
        }
        $btn.data('clicking', true);
        setTimeout(function(){ $btn.data('clicking', false); }, 1200);
      });
    });
  </script>
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
