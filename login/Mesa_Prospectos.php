<?php
/********************************************************************************************
 * Qué hace: Página "Mesa Prospectos" de la PWA. Muestra y gestiona prospectos, abre modales
 *           para registrar venta, cancelar, reasignar, actualizar y enviar a LeadSales.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 * Archivo: Mesa_Proscpectos.php
 ********************************************************************************************/

// =================== Sesión y dependencias ===================
// Qué hace: Inicia sesión, carga librerías y activa excepciones de mysqli para PHP 8.2
// Fecha: 05/11/2025 | Revisado por: JCCM
require_once dirname(__DIR__) . '/eia/session.php';
kasu_session_start();
require_once __DIR__ . '/../eia/librerias.php';
require_once __DIR__ . '/php/mesa_helpers.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// =================== Guardia de sesión ===================
// Qué hace: Valida autenticación y redirige a login si no hay sesión de Vendedor
// Fecha: 05/11/2025 | Revisado por: JCCM
if (empty($_SESSION["Vendedor"])) {
  header('Location: https://kasu.com.mx/login');
  exit;
}

// =================== Utilidades ===================
// Qué hace: Función de escape HTML segura para impresión en vista
// Fecha: 05/11/2025 | Revisado por: JCCM
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

if (!function_exists('kasu_table_exists')) {
  function kasu_table_exists(mysqli $db, string $table): bool {
    $sql = "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1";
    $stmt = $db->prepare($sql);
    if (!$stmt) return false;
    $stmt->bind_param('s', $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $ok = $res && $res->fetch_row();
    $stmt->close();
    return (bool)$ok;
  }
}

if (!function_exists('kasu_format_fecha_es')) {
  function kasu_format_fecha_es(string $raw): string {
    if ($raw === '') return '';
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $raw)
      ?: DateTime::createFromFormat('Y-m-d H:i', $raw)
      ?: DateTime::createFromFormat('Y-m-d', $raw);
    if (!$dt) return $raw;
    $dias = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
    $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $dia = $dias[(int)$dt->format('w')] ?? '';
    $mes = $meses[(int)$dt->format('n') - 1] ?? '';
    return $dia . ' ' . $dt->format('j') . ' de ' . $mes . ', ' . $dt->format('H:i');
  }
}

// =================== Fechas periodo ===================
// Qué hace: Define inicio de mes y fecha de hoy para filtros y vistas
// Fecha: 05/11/2025 | Revisado por: JCCM
$FechIni = date("d-m-Y", strtotime('first day of this month'));
$FechFin = date("d-m-Y");

// =================== Vars base ===================
// Qué hace: Variables de control de ventana/modal y parámetros de búsqueda
// Fecha: 05/11/2025 | Revisado por: JCCM
$Reg     = null;
$Ventana = null;   // "Ventana1".. "Ventana9"
$Lanzar  = null;   // "#Ventana" (contenedor único)
$Metodo  = "Mesa";
$nombre  = $_POST['nombre'] ?? ($_GET['nombre'] ?? '');
if ($nombre === '') $nombre = ' ';
$nombreTrim = trim((string)$nombre);
if (empty($_SESSION['csrf_mesa_pipeline'])) {
  $_SESSION['csrf_mesa_pipeline'] = bin2hex(random_bytes(32));
}
$pipelineCsrf = (string)$_SESSION['csrf_mesa_pipeline'];

// =================== Selector (IdProspecto = {V}{Id}) ===================
// Qué hace: Interpreta el parámetro IdProspecto, determina la ventana a abrir y carga el prospecto
//           V = número de ventana; Id = Id numérico del prospecto
// Fecha: 05/11/2025 | Revisado por: JCCM
$IdProspecto = $_POST['IdProspecto'] ?? ($_GET['IdProspecto'] ?? null);
if ($IdProspecto !== null && $IdProspecto !== '') {
  $Vtn   = substr($IdProspecto, 0, 1);
  $idStr = substr($IdProspecto, 1);

  if (ctype_digit((string)$idStr)) {
    $CteInt = (int)$idStr;

    // Prospecto
    $stmt = $pros->prepare("SELECT * FROM prospectos WHERE Id = ?");
    if (!$stmt) { error_log($pros->error); die('Error SQL'); }
    $stmt->bind_param('i', $CteInt);
    $stmt->execute();
    $res = $stmt->get_result();
    $Reg = $res ? $res->fetch_assoc() : null;
    $stmt->close();
  } else {
    $Vtn = '';
  }

  if ($Vtn !== '' && ctype_digit((string)$Vtn)) {
    $Ventana = 'Ventana'. $Vtn; // para lógica interna
    $Lanzar  = '#Ventana';      // id real del modal único
  }
}

// =================== Alerta opcional por GET ?Msg= ===================
// Qué hace: Muestra alerta informativa si viene un mensaje por querystring
// Fecha: 05/11/2025 | Revisado por: JCCM
if (isset($_GET['Msg'])) {
  echo "<script>alert('".htmlspecialchars((string)$_GET['Msg'], ENT_QUOTES, 'UTF-8')."');</script>";
}

// =================== Cache bust estático ===================
// Qué hace: Versión de recursos estáticos para evitar caché del navegador
// Fecha: 05/11/2025 | Revisado por: JCCM
$VerCache = time();

// =================== Mapa de coordinadores por sucursal ===================
// Qué hace: Carga lista de coordinadores (Nivel=4) agrupados por sucursal para selects
// Fecha: 05/11/2025 | Revisado por: JCCM
$coorsMap = [];
if ($q = $mysqli->query("SELECT Id, Nombre, Sucursal FROM Empleados WHERE Nivel = 4 AND Nombre <> 'Vacante' ORDER BY Nombre")) {
  while ($r = $q->fetch_assoc()) {
    $sid    = (int)$r['Sucursal'];
    $nomSuc = $basicas->BuscarCampos($mysqli,'nombreSucursal','Sucursal','Id',$sid);
    $coorsMap[$sid][] = [
      'id'   => (int)$r['Id'],
      'text' => $r['Nombre'].' - Coordinador - '.$nomSuc
    ];
  }
}

$nivelSesion = (int)$basicas->BuscarCampos($mysqli, 'Nivel', 'Empleados', 'IdUsuario', $_SESSION['Vendedor']);
$idEmpleadoSesion = (int)$basicas->BuscarCampos($mysqli, 'Id', 'Empleados', 'IdUsuario', $_SESSION['Vendedor']);
[$kasuEmpleadosMap, $kasuEmpleadosChildren] = kasu_load_empleados_tree($mysqli);
$kasuScopeUsers = kasu_scope_user_ids($nivelSesion, $idEmpleadoSesion, $kasuEmpleadosMap, $kasuEmpleadosChildren);
$marketingRoleSesion = kasu_marketing_role_key($mysqli, $nivelSesion);
$marketingAssignmentIds = kasu_marketing_assignment_ids(
  $marketingRoleSesion,
  $idEmpleadoSesion,
  $kasuEmpleadosMap,
  $kasuEmpleadosChildren
);
$canReassignProspect = $marketingRoleSesion !== 'ejecutivo';
$kasuScopeSet = null;
if (is_array($kasuScopeUsers)) {
  $kasuScopeSet = [];
  foreach ($kasuScopeUsers as $usr) {
    $usrUp = strtoupper(trim((string)$usr));
    if ($usrUp !== '') {
      $kasuScopeSet[$usrUp] = true;
    }
  }
}

// Los usuarios con alcance limitado solo pueden abrir prospectos asignados
// directamente a ellos o a integrantes de su equipo.
if (is_array($Reg) && !empty($Reg) && $kasuScopeSet !== null) {
  $regAssignedId = (int)($Reg['Asignado'] ?? 0);
  $regAssignedUser = strtoupper(trim((string)($kasuEmpleadosMap[$regAssignedId]['IdUsuario'] ?? '')));
  if ($regAssignedUser === '' || !isset($kasuScopeSet[$regAssignedUser])) {
    $Reg = null;
    $Ventana = null;
    $Lanzar = null;
  }
}
if ($marketingRoleSesion === 'ejecutivo' && $Ventana === 'Ventana3') {
  $Reg = null;
  $Ventana = null;
  $Lanzar = null;
}

$hasCitas = kasu_table_exists($pros, 'citas');
$hasAgenda = kasu_table_exists($pros, 'agenda_llamadas');
$hasFunerarias = kasu_table_exists($pros, 'prospectos_funerarias');
$pipelineStages = [
  'lead' => 'Lead',
  'citado' => 'Citado',
  'presupuesto_enviado' => 'Presupuesto Enviado',
  'concretado' => 'Concretado',
  'cancelado' => 'Cancelado',
];
$hasProspectosPipeline = false;
try {
  $pros->query("
    CREATE TABLE IF NOT EXISTS Prospectos_Pipeline (
      IdProspecto INT NOT NULL,
      Etapa VARCHAR(40) NOT NULL DEFAULT 'lead',
      ActualizadoPor VARCHAR(100) NOT NULL DEFAULT '',
      FechaActualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (IdProspecto),
      KEY idx_prospectos_pipeline_etapa (Etapa)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  ");
  $hasProspectosPipeline = kasu_table_exists($pros, 'Prospectos_Pipeline');
} catch (Throwable $e) {
  error_log('Mesa_Prospectos pipeline: ' . $e->getMessage());
}
$stmtCita = null;
$stmtAgenda = null;
if ($hasCitas) {
  $stmtCita = $pros->prepare("SELECT FechaCita FROM citas WHERE IdProspecto = ? ORDER BY FechaCita DESC LIMIT 1");
}
if ($hasAgenda) {
  $stmtAgenda = $pros->prepare("SELECT inicio FROM agenda_llamadas WHERE prospecto_id = ? ORDER BY inicio DESC LIMIT 1");
}

// =================== Movimiento entre etapas del pipeline ===================
if (!empty($_POST['MoverPipeline'])) {
  $pipelineId = (int)($_POST['IdProspectoNum'] ?? 0);
  $pipelineStage = (string)($_POST['EtapaPipeline'] ?? '');
  $pipelineAllowed = isset($pipelineStages[$pipelineStage]);
  $pipelineCsrfOk = hash_equals($pipelineCsrf, (string)($_POST['csrf_pipeline'] ?? ''));
  $pipelineVisible = false;

  if ($pipelineId > 0 && $pipelineAllowed && $pipelineCsrfOk && $hasProspectosPipeline) {
    $stmtVisible = $pros->prepare("SELECT Asignado FROM prospectos WHERE Id = ? AND Cancelacion = 0 LIMIT 1");
    if ($stmtVisible) {
      $stmtVisible->bind_param('i', $pipelineId);
      $stmtVisible->execute();
      $visibleRow = $stmtVisible->get_result()->fetch_assoc();
      $stmtVisible->close();
      if ($visibleRow) {
        $assignedId = (int)($visibleRow['Asignado'] ?? 0);
        $assignedUser = strtoupper(trim((string)($kasuEmpleadosMap[$assignedId]['IdUsuario'] ?? '')));
        $pipelineVisible = $kasuScopeSet === null || ($assignedUser !== '' && isset($kasuScopeSet[$assignedUser]));
      }
    }
  }

  if ($pipelineVisible) {
    $pipelineUser = (string)($_SESSION['Vendedor'] ?? '');
    $stmtMove = $pros->prepare("
      INSERT INTO Prospectos_Pipeline (IdProspecto, Etapa, ActualizadoPor)
      VALUES (?, ?, ?)
      ON DUPLICATE KEY UPDATE Etapa = VALUES(Etapa), ActualizadoPor = VALUES(ActualizadoPor)
    ");
    $stmtMove->bind_param('iss', $pipelineId, $pipelineStage, $pipelineUser);
    $stmtMove->execute();
    $stmtMove->close();
    $msg = 'Prospecto movido a ' . ($pipelineStages[$pipelineStage] ?? 'la nueva etapa') . '.';
  } else {
    $msg = 'No se pudo actualizar la etapa del prospecto.';
  }

  $location = $_SERVER['PHP_SELF'] . '?Msg=' . rawurlencode($msg);
  if ($nombreTrim !== '') {
    $location .= '&nombre=' . rawurlencode($nombreTrim);
  }
  header('Location: ' . $location);
  exit;
}

// =================== Cancelación de prospecto (POST CancelaCte) ===================
// Qué hace: Registra auditoría, marca cancelación y notifica por mensaje
// Fecha: 05/11/2025 | Revisado por: JCCM
if (!empty($_POST['CancelaCte'])) {
    $cancelProspectId = (int)($_POST['IdVenta'] ?? 0);
    if (!kasu_marketing_can_manage_prospect($mysqli, $pros, (string)$_SESSION['Vendedor'], $cancelProspectId)) {
      http_response_code(403);
      exit('No tienes permisos para cancelar este prospecto.');
    }
    // Auditoría (GPS + fingerprint)
    $ids = $seguridad->auditoria_registrar(
        $mysqli,
        $basicas,
        $_POST,
        'Cancelar_Venta',
        $_POST['Host'] ?? $_SERVER['PHP_SELF']
    );
    // Mensaje de cancelación
    $_GET['Msg'] = "Se ha cancelado el prospecto";
    // Cambio de Status de Prospecto
    $basicas->ActCampo($pros, "prospectos", "Cancelacion", 1, (int)($_POST['IdVenta'] ?? 0));
}

// =================== Comentarios de prospecto (POST RegistrarComentario) ===================
// Qué hace: Guarda comentario del ejecutivo en tabla Prospectos_Comentarios
// Fecha: 08/01/2026 | Revisado por: IA
if (!empty($_POST['RegistrarComentario'])) {
  $idProspectoNum = (int)($_POST['IdProspectoNum'] ?? 0);
  $comentario = trim((string)($_POST['Comentario'] ?? ''));
  if (!kasu_marketing_can_manage_prospect($mysqli, $pros, (string)$_SESSION['Vendedor'], $idProspectoNum)) {
    http_response_code(403);
    exit('No tienes permisos para comentar este prospecto.');
  }
  if ($idProspectoNum > 0 && $comentario !== '') {
    if (kasu_table_exists($pros, 'Prospectos_Comentarios')) {
      $idUsuario = (string)($_SESSION['Vendedor'] ?? '');
      $stmtCom = $pros->prepare("
        INSERT INTO Prospectos_Comentarios (IdProspecto, IdUsuario, Comentario, FechaRegistro)
        VALUES (?, ?, ?, NOW())
      ");
      if ($stmtCom) {
        $stmtCom->bind_param('iss', $idProspectoNum, $idUsuario, $comentario);
        $stmtCom->execute();
        $stmtCom->close();
        $seguridad->auditoria_registrar(
          $mysqli,
          $basicas,
          $_POST,
          'Prospecto_Comentario',
          $_POST['Host'] ?? $_SERVER['PHP_SELF']
        );
        $_GET['Msg'] = 'Comentario guardado.';
      } else {
        $_GET['Msg'] = 'No se pudo guardar el comentario.';
      }
    } else {
      $_GET['Msg'] = 'Tabla Prospectos_Comentarios no disponible.';
    }
  } else {
    $_GET['Msg'] = 'Comentario vacio o prospecto invalido.';
  }
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F2F2F2">
  <link rel="icon" href="/assets/images/Index/florkasu.png">
  <title>Mesa Prospectos</title>

  <!-- =================== PWA / iOS ===================
       Qué hace: Manifiesto y meta para instalación como app
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- =================== CSS unificado ===================
       Qué hace: Carga de estilos base de Bootstrap, iconos y hoja local
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <link rel="stylesheet" href="/assets/css/fonts.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="/login/assets/css/Menu_Superior.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="/login/assets/css/pwa-core.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="/login/assets/css/pwa-components.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="assets/css/Grafica.css">
  <style>
    :root {
      --mesa-menu-bg: #808b96;
      --mesa-menu-text: #f8f9f9;
      --topbar-height: 46px;
      --topbar-h: 46px;
    }
    .mesa-prospectos .topbar {
      background: var(--mesa-menu-bg);
      border-bottom: 1px solid rgba(255, 255, 255, 0.25);
      color: var(--mesa-menu-text);
      position: fixed;
      min-height: var(--topbar-height);
      padding: calc(4px + var(--safe-t, 0px)) 14px 4px;
    }
    .mesa-prospectos .topbar-left {
      gap: 7px;
    }
    .mesa-prospectos .topbar img[alt="KASU"] {
      width: 27px;
      height: 27px;
    }
    .mesa-prospectos .topbar .title {
      font-size: .88rem;
      line-height: 1;
      white-space: nowrap;
    }
    .mesa-prospectos .topbar .eyebrow {
      font-size: .58rem;
      line-height: 1;
    }
    .mesa-prospectos .topbar .action-btn {
      gap: 4px;
      padding: 5px 11px;
      font-size: .75rem;
      box-shadow: 0 7px 18px rgba(34, 197, 94, .38);
    }
    .mesa-prospectos .topbar .action-btn .material-icons {
      font-size: 17px;
    }
    .mesa-prospectos .topbar .title,
    .mesa-prospectos .topbar .eyebrow,
    .mesa-prospectos .topbar .topbar-label {
      color: var(--mesa-menu-text);
    }
    .mesa-prospectos .topbar .eyebrow {
      opacity: 0.85;
    }
    .mesa-prospectos-content {
      padding-top: calc(var(--topbar-h, 56px) + var(--safe-t, 0px));
      padding-bottom: calc(var(--bottombar-h, 48px) + var(--safe-b, 0px) + 8px);
    }
    .mesa-prospectos-scroll {
      max-height: calc(
        100vh - (var(--topbar-h, 56px) + var(--safe-t, 0px))
        - (var(--bottombar-h, 48px) + var(--safe-b, 0px))
        - 16px
      );
      overflow-y: auto;
    }
    .mesa-prospectos-scroll thead th {
      position: sticky;
      top: 0;
      background: var(--mesa-menu-bg);
      color: var(--mesa-menu-text);
      z-index: 2;
    }
    .prospectos-dashboard {
      padding: calc(var(--topbar-h) + var(--safe-t, 0px) + 12px) 16px
        calc(var(--bottombar-h, 48px) + var(--safe-b, 0px) + 8px);
    }
    .prospectos-toolbar,
    .prospectos-metrics,
    .prospectos-board-wrap {
      max-width: 1800px;
      margin-left: auto;
      margin-right: auto;
    }
    .prospectos-toolbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 12px;
    }
    .prospectos-toolbar h2 {
      margin: 0;
      font-size: 24px;
    }
    .prospectos-search {
      display: flex;
      gap: 8px;
      width: min(100%, 420px);
    }
    .prospectos-search .form-control {
      border-radius: 10px;
    }
    .prospectos-metrics {
      display: grid;
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: 10px;
      margin-bottom: 12px;
    }
    .prospectos-metric {
      background: #fff;
      border: 1px solid #d5dce3;
      border-radius: 12px;
      padding: 10px 12px;
    }
    .prospectos-metric span {
      display: block;
      color: #667788;
      font-size: 13px;
    }
    .prospectos-metric strong {
      color: #263746;
      font-size: 24px;
    }
    .prospectos-board-wrap {
      background: #fff;
      border: 1px solid #d5dce3;
      border-radius: 14px;
      padding: 14px;
    }
    .prospectos-board-note {
      margin: 0 0 12px;
      color: #667788;
      font-size: 14px;
    }
    .prospectos-board {
      display: grid;
      grid-template-columns: repeat(5, minmax(220px, 1fr));
      gap: 12px;
      overflow-x: auto;
      padding-bottom: 8px;
    }
    .prospectos-column {
      min-height: 470px;
      display: flex;
      flex-direction: column;
      padding: 10px;
      border: 1px solid #ccd5de;
      border-radius: 12px;
      background: #f4f8ff;
      transition: border-color .2s ease, box-shadow .2s ease;
    }
    .prospectos-column[data-stage="citado"] { background: #f1fbf8; }
    .prospectos-column[data-stage="presupuesto_enviado"] { background: #fffaef; }
    .prospectos-column[data-stage="concretado"] { background: #f2fae6; }
    .prospectos-column[data-stage="cancelado"] { background: #fff1f1; }
    .prospectos-column.is-drop-target {
      border-color: #58a66b;
      box-shadow: inset 0 0 0 2px rgba(88, 166, 107, .25);
    }
    .prospectos-column-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      margin-bottom: 10px;
    }
    .prospectos-column-head h3 {
      margin: 0;
      color: #263746;
      font-size: 17px;
    }
    .prospectos-column-head span {
      min-width: 28px;
      padding: 3px 8px;
      border: 1px solid #cad3de;
      border-radius: 999px;
      background: #fff;
      text-align: center;
      font-size: 13px;
    }
    .prospectos-cards {
      display: grid;
      align-content: start;
      gap: 8px;
      flex: 1;
    }
    .prospecto-card {
      display: grid;
      gap: 5px;
      padding: 10px;
      border: 1px solid #cfd6df;
      border-radius: 10px;
      background: #fff;
      box-shadow: 0 3px 10px rgba(13, 31, 56, .06);
      cursor: grab;
      transition: transform .16s ease, border-color .16s ease, box-shadow .16s ease;
    }
    .prospecto-card:hover,
    .prospecto-card:focus {
      border-color: #8099b2;
      box-shadow: 0 8px 18px rgba(13, 31, 56, .12);
      outline: none;
      transform: translateY(-1px);
    }
    .prospecto-card.is-selected {
      border-color: #58a66b;
      box-shadow: 0 0 0 2px rgba(88, 166, 107, .22);
    }
    .prospecto-card.is-dragging {
      opacity: .55;
      cursor: grabbing;
    }
    .prospecto-card strong { color: #263746; }
    .prospecto-card small { color: #667788; }
    .prospecto-card-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 4px;
    }
    .prospecto-chip {
      padding: 2px 6px;
      border-radius: 999px;
      background: #eef2f6;
      color: #455769;
      font-size: 11px;
    }
    .prospecto-card-hint {
      margin-top: 4px;
      padding-top: 6px;
      border-top: 1px solid #edf0f3;
      color: #60758a;
      font-size: 11px;
    }
    .prospecto-detail-backdrop {
      position: fixed;
      inset: 0;
      z-index: 2040;
      border: 0;
      background: rgba(20, 35, 50, .28);
      opacity: 0;
      pointer-events: none;
      transition: opacity .2s ease;
    }
    .prospecto-detail-backdrop.is-open {
      opacity: 1;
      pointer-events: auto;
    }
    .prospecto-detail {
      position: fixed;
      top: 0;
      right: 0;
      z-index: 2050;
      width: min(430px, 100vw);
      height: 100vh;
      padding: calc(var(--safe-t, 0px) + 16px) 18px
        calc(var(--bottombar-h, 48px) + var(--safe-b, 0px) + 16px);
      overflow-y: auto;
      background: #fff;
      border-left: 1px solid #cdd6df;
      box-shadow: -16px 0 36px rgba(13, 31, 56, .18);
      transform: translateX(105%);
      transition: transform .22s ease;
    }
    .prospecto-detail.is-open {
      transform: translateX(0);
    }
    .prospecto-detail-head {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
      padding-bottom: 12px;
      border-bottom: 1px solid #e3e8ed;
    }
    .prospecto-detail-head h3 {
      margin: 0 0 3px;
      color: #263746;
      font-size: 22px;
    }
    .prospecto-detail-head p {
      margin: 0;
      color: #60758a;
      font-size: 13px;
    }
    .prospecto-detail-close {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex: 0 0 34px;
      width: 34px;
      height: 34px;
      padding: 0;
      border: 1px solid #d4dce4;
      border-radius: 999px;
      background: #f4f7fa;
      color: #34495e;
      cursor: pointer;
    }
    .prospecto-detail-section {
      margin-top: 16px;
      padding: 14px;
      border: 1px solid #dce3e9;
      border-radius: 12px;
      background: #f8fafc;
    }
    .prospecto-detail-section h4 {
      margin: 0 0 10px;
      color: #263746;
      font-size: 15px;
    }
    .prospecto-detail-data {
      display: grid;
      gap: 9px;
      margin: 0;
    }
    .prospecto-detail-data div {
      display: grid;
      gap: 1px;
    }
    .prospecto-detail-data dt {
      margin: 0;
      color: #718196;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
    }
    .prospecto-detail-data dd {
      margin: 0;
      color: #263746;
      font-size: 14px;
      overflow-wrap: anywhere;
    }
    .prospecto-detail-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }
    .prospecto-detail-actions form {
      display: contents;
    }
    .prospecto-detail-actions .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      min-height: 38px;
      margin: 0;
      padding: 7px 10px;
      border-radius: 8px;
      color: #fff;
      font-size: 12px;
    }
    .prospecto-detail-actions .material-icons {
      font-size: 18px;
    }
    /* Los formularios de acción existentes se presentan como un segundo panel lateral. */
    .mesa-prospectos #Ventana {
      z-index: 3100;
      padding-right: 0 !important;
    }
    .mesa-prospectos #Ventana .modal-dialog {
      position: absolute;
      top: 0;
      right: 0;
      bottom: 0;
      left: auto;
      width: min(520px, 100vw);
      max-width: none;
      min-height: 100vh;
      margin: 0 !important;
      transform: translateX(105%);
      transition: transform .22s ease-out;
    }
    .mesa-prospectos #Ventana.show .modal-dialog {
      margin: 0 !important;
      transform: translateX(0);
    }
    .mesa-prospectos #Ventana .modal-content {
      min-height: 100vh;
      max-height: 100vh;
      overflow: hidden;
      border: 0;
      border-left: 1px solid #cdd6df;
      border-radius: 0;
      box-shadow: -16px 0 36px rgba(13, 31, 56, .22);
    }
    .mesa-prospectos #Ventana .modal-content > form {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      max-height: 100vh;
    }
    .mesa-prospectos #Ventana .modal-header {
      flex: 0 0 auto;
      align-items: center;
      padding: calc(var(--safe-t, 0px) + 16px) 18px 14px;
      border-bottom: 1px solid #e3e8ed;
    }
    .mesa-prospectos #Ventana .modal-title {
      color: #263746;
      font-size: 20px;
      font-weight: 700;
    }
    .mesa-prospectos #Ventana .modal-header .close {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      margin: 0;
      padding: 0;
      border: 2px solid #1760b3;
      border-radius: 999px;
      color: #1760b3;
      font-size: 26px;
      line-height: 1;
      opacity: 1;
    }
    .mesa-prospectos #Ventana .modal-body {
      flex: 1 1 auto;
      overflow-y: auto;
      padding: 18px;
      background: #fff;
    }
    .mesa-prospectos #Ventana .modal-footer {
      flex: 0 0 auto;
      padding: 12px 18px calc(var(--safe-b, 0px) + 12px);
      background: #fff;
      border-top: 1px solid #e3e8ed;
    }
    .mesa-prospectos #Ventana .modal-footer .btn,
    .mesa-prospectos #Ventana .modal-footer input[type="submit"] {
      min-height: 40px;
      border-radius: 8px;
    }
    .mesa-prospectos #Ventana + .modal-backdrop,
    .mesa-prospectos .modal-backdrop.show {
      opacity: .32;
    }
    .prospectos-empty {
      margin: 0;
      padding: 12px 8px;
      color: #758494;
      text-align: center;
      font-size: 13px;
    }
    @media (max-width: 900px) {
      .prospectos-toolbar { align-items: stretch; flex-direction: column; }
      .prospectos-search { width: 100%; }
      .prospectos-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .prospectos-board { grid-template-columns: repeat(5, minmax(190px, 1fr)); }
      .prospectos-dashboard {
        padding: calc(var(--topbar-h) + var(--safe-t, 0px) + 10px) 10px
          calc(var(--bottombar-h, 48px) + var(--safe-b, 0px) + 8px);
      }
    }
  </style>
</head>
<body class="mesa-prospectos" onload="localize()">
  <!-- =================== Top bar fija Mesa_Prospectos.php ===================
       Qué hace: Encabezado con título y botón para crear prospecto
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <div class="topbar">
    <div class="topbar-left">
      <img src="/login/assets/img/kasu_logo.jpeg" alt="KASU">
      <div>
        <p class="eyebrow mb-0">Mesa</p>
        <h4 class="title">Cartera de Prospectos</h4>
      </div>
    </div>
    <div class="topbar-actions">
      <form class="m-0" method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>">
        <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">
        <input type="hidden" name="IdProspecto" value="40">
        <button type="submit" class="action-btn success" title="Nuevo prospecto">
          <i class="material-icons">person_add</i>
          <span>Nuevo prospecto</span>
        </button>
      </form>
    </div>
  </div>

  <!-- =================== Menú inferior compacto ===================
       Qué hace: Navegación inferior de la PWA
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <!-- Menú inferior fijo -->
  <section id="Menu">
    <?php require_once __DIR__ . '/html/Menuprinc.php'; ?>
  </section>

  <!-- =================== Modal único ===================
       Qué hace: Contenedor de modales. Carga vistas por require según $Ventana
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <div class="modal fade" id="Ventana" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document"><div class="modal-content">
      <?php if ($Ventana === "Ventana1"): ?>
        <!-- Registrar Venta HTML Listo para revision Formulario  -->
        <?php require 'html/NvoCliente.php'; ?>

      <?php elseif ($Ventana === "Ventana2"): ?>
        <!-- (2) Cancelar Prospecto HTML Listo para revision Formulario -->
        <?php require 'html/CancelUsr.php'; ?>

      <?php elseif ($Ventana === "Ventana3"): ?>
        <!-- (3) Asignar prospecto a ejecutivo HTML Listo para revision Formulario -->
        <form method="POST" action="php/Registro_Prospectos.php">
          <div class="modal-header">
            <h5 class="modal-title" id="modalV3">Reasignar prospecto</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="IdProspecto" value="<?= h($Reg['Id'] ?? '') ?>">
            <input type="hidden" name="nombre"  value="<?= h($nombre) ?>">
            <input type="hidden" name="Status"  value="<?= h($_POST['Status'] ?? '') ?>">
            <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">

            <p>Nombre del prospecto</p>
            <h4 class="text-center"><strong><?= h($Reg['FullName'] ?? '') ?></strong></h4>
            <p>Este prospecto está asignado a</p>
            <h4 class="text-center">
              <strong>
                <?php
                  //Seleccionamos el nivel del Usuario actual
                  $Niv = (int)$basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
                  //Validamos el nombre del Usuario que tiene asignado el prospecto
                  $RegUsuario = $basicas->BuscarCampos($mysqli, "Nombre", "Empleados", "Id", $Reg['Asignado'] ?? 0);

                  if (($RegUsuario ?? '') === "SISTEMA") {
                    echo "SISTEMA";
                    $Niv = 4;
                    $sql = "SELECT * FROM Empleados WHERE Nivel >= $Niv AND Nombre != 'Vacante'";
                  } elseif ($Niv === 1 || kasu_director_role_key($mysqli, $Niv) === 'general') {
                    $UsrPro = $RegUsuario;
                    echo $UsrPro ?: "Sin Asignar";
                    $sql = "SELECT * FROM Empleados WHERE Nombre != 'Vacante'";
                  } else {
                    echo h($basicas->BuscarCampos($mysqli, "Nombre", "Empleados", "IdUsuario", $Reg['Usuario'] ?? 0));
                    $Suc = (int)$basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "IdUsuario", $Reg['Usuario'] ?? 0);
                    $Niv = (int)$basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $Reg['Usuario'] ?? 0);
                    $sql = "SELECT * FROM Empleados WHERE Nivel >= $Niv AND Nombre != 'Vacante' AND Sucursal = $Suc";
                  }
                ?>
              </strong>
            </h4>

            <label>Selecciona el nuevo Ejecutivo</label>
            <select class="form-control" name="NvoVend">
            <?php
            // Solo nivel 6 o superior, sin "Vacante"
            if (is_array($marketingAssignmentIds)) {
              foreach ($marketingAssignmentIds as $assignmentId) {
                $stmtMarketing = $mysqli->prepare("
                  SELECT e.Id, e.Nombre, COALESCE(s.nombreSucursal, 'Sin sucursal') AS Sucursal
                  FROM Empleados e
                  LEFT JOIN Sucursal s ON s.Id = e.Sucursal
                  WHERE e.Id = ? AND e.Nombre <> 'Vacante'
                  LIMIT 1
                ");
                $stmtMarketing->bind_param('i', $assignmentId);
                $stmtMarketing->execute();
                $row = $stmtMarketing->get_result()->fetch_assoc();
                $stmtMarketing->close();
                if ($row) {
                  echo '<option value="'.h($row['Id']).'">'.h($row['Nombre'].' - Ejecutivo de Marketing - '.$row['Sucursal']).'</option>';
                }
              }
            } else {
              $sql = "
                SELECT e.Id, e.Nombre, COALESCE(s.nombreSucursal,'Sin sucursal') AS Sucursal,
                       COALESCE(n.NombreNivel, 'Ejecutivo') AS Puesto
                FROM Empleados e
                LEFT JOIN Sucursal s ON s.Id = e.Sucursal
                LEFT JOIN Nivel n ON n.Id = e.Nivel
                WHERE e.Nivel >= 6 AND e.Nombre <> 'Vacante'
                ORDER BY s.nombreSucursal, e.Nombre
              ";
              if ($res = $mysqli->query($sql)) {
                while ($row = $res->fetch_assoc()) {
                  echo '<option value="'.h($row['Id']).'">'.h($row['Nombre'].' - '.$row['Puesto'].' - '.$row['Sucursal']).'</option>';
                }
              }
            }
            ?>
            </select>
            <br>
          </div>
          <div class="modal-footer">
            <input type="submit" name="AsigVende" class="btn btn-primary" value="Asignar Prospecto">
          </div>
        </form>

      <?php elseif ($Ventana === "Ventana4"): ?>
        <!-- (4) Crear datos prospecto HTML Listo para revision Formulario -->
        <?php require_once 'html/NvoProspecto.php'; ?>

      <?php elseif ($Ventana === "Ventana5"): ?>
        <!-- (5) Editar datos prospecto HTML Listo para revision Formulario-->
        <?php require 'html/ActualizarDatos.php'; ?>

      <?php elseif ($Ventana === "Ventana6"): ?>
        <!-- (6) Enviar a LeadSales HTML Listo para revision Formulario -->
        <?php require 'html/CrearLeadSales.php'; ?>

      <?php elseif ($Ventana === "Ventana7"): ?>
        <!-- (7) Comentarios y analisis IA del prospecto -->
        <?php
          $prospectoId = (int)($Reg['Id'] ?? 0);
          $comentarios = [];
          if ($prospectoId > 0 && kasu_table_exists($pros, 'Prospectos_Comentarios')) {
            $stmtCom = $pros->prepare("
              SELECT IdUsuario, Comentario, FechaRegistro
              FROM Prospectos_Comentarios
              WHERE IdProspecto = ?
              ORDER BY FechaRegistro DESC
              LIMIT 20
            ");
            if ($stmtCom) {
              $stmtCom->bind_param('i', $prospectoId);
              $stmtCom->execute();
              $resCom = $stmtCom->get_result();
              while ($rowCom = $resCom->fetch_assoc()) {
                $comentarios[] = $rowCom;
              }
              $stmtCom->close();
            }
          }

          $analisis = null;
          if ($prospectoId > 0 && kasu_table_exists($pros, 'Prospectos_Analisis_IA')) {
            $stmtAna = $pros->prepare("
              SELECT LeadScore, Resumen, PasosSugeridos, Recomendacion, AnalisisJson, FechaAnalisis
              FROM Prospectos_Analisis_IA
              WHERE IdProspecto = ?
              ORDER BY FechaAnalisis DESC
              LIMIT 1
            ");
            if ($stmtAna) {
              $stmtAna->bind_param('i', $prospectoId);
              $stmtAna->execute();
              $analisis = $stmtAna->get_result()->fetch_assoc() ?: null;
              $stmtAna->close();
            }
          }

          $analisisHtml = '<p class="text-muted mb-2">Sin analisis reciente. Usa "Analizar con IA".</p>';
          if (is_array($analisis)) {
            $score = (int)($analisis['LeadScore'] ?? 0);
            $resumen = (string)($analisis['Resumen'] ?? '');
            $reco = (string)($analisis['Recomendacion'] ?? '');
            $pasosRaw = (string)($analisis['PasosSugeridos'] ?? '');
            $pasos = json_decode($pasosRaw, true);
            if (!is_array($pasos)) {
              $pasos = array_filter(array_map('trim', preg_split('/\r?\n/', $pasosRaw)));
            }
            $analisisHtml = '<p><strong>Calificacion lead:</strong> ' . h((string)$score) . '</p>';
            if ($resumen !== '') {
              $analisisHtml .= '<p><strong>Resumen:</strong> ' . h($resumen) . '</p>';
            }
            if (!empty($pasos)) {
              $analisisHtml .= '<p><strong>Siguientes pasos:</strong></p><ul>';
              foreach ($pasos as $paso) {
                $analisisHtml .= '<li>' . h((string)$paso) . '</li>';
              }
              $analisisHtml .= '</ul>';
            }
            if ($reco !== '') {
              $analisisHtml .= '<p><strong>Recomendacion:</strong> ' . h($reco) . '</p>';
            }
          }
        ?>
        <form method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>">
          <div class="modal-header">
            <h5 class="modal-title">Comentarios y seguimiento</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="IdProspecto" value="7<?= (int)$prospectoId ?>">
            <input type="hidden" name="IdProspectoNum" value="<?= (int)$prospectoId ?>">
            <input type="hidden" name="nombre" value="<?= h($nombre) ?>">
            <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">

            <div class="border rounded p-3 mb-3">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <strong>Analisis IA</strong>
              </div>
              <div id="ia-prospecto-result" data-ia-prospecto="<?= (int)$prospectoId ?>">
                <?= $analisisHtml ?>
              </div>
            </div>

            <div class="form-group">
              <label for="Comentario">Comentario del ejecutivo</label>
              <textarea id="Comentario" name="Comentario" class="form-control" rows="3" placeholder="Registra el seguimiento..."></textarea>
            </div>

            <div class="mb-3">
              <strong>Historial de comentarios</strong>
              <?php if (!empty($comentarios)): ?>
                <ul class="mt-2">
                  <?php foreach ($comentarios as $com): ?>
                    <li>
                      <small><?= h((string)($com['FechaRegistro'] ?? '')) ?> · <?= h((string)($com['IdUsuario'] ?? '')) ?></small><br>
                      <?= h((string)($com['Comentario'] ?? '')) ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <p class="text-muted mt-2">Sin comentarios registrados.</p>
              <?php endif; ?>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="RegistrarComentario" class="btn btn-primary">Guardar comentario</button>
          </div>
        </form>
      <?php endif; ?>
    </div></div>
  </div>

  <!-- =================== Pipeline de prospectos =================== -->
  <section class="mesa-prospectos-content prospectos-dashboard">
    <?php
      $pipelineMap = [];
      if ($hasProspectosPipeline && ($resPipeline = $pros->query("SELECT IdProspecto, Etapa FROM Prospectos_Pipeline"))) {
        while ($pipelineRow = $resPipeline->fetch_assoc()) {
          $stage = (string)($pipelineRow['Etapa'] ?? 'lead');
          $pipelineMap[(int)$pipelineRow['IdProspecto']] = isset($pipelineStages[$stage]) ? $stage : 'lead';
        }
        $resPipeline->close();
      }

      $pipelineBuckets = array_fill_keys(array_keys($pipelineStages), []);
      $buscar = $nombre === ' '
        ? $basicas->BLikesD2($pros, 'prospectos', 'FullName', $nombre, 'Cancelacion', 0, 'Automatico', 0)
        : $basicas->BLikesCan($pros, 'prospectos', 'FullName', $nombre, 'Cancelacion', 0);

      foreach ($buscar as $row) {
        $assignedId = (int)($row['Asignado'] ?? 0);
        $assignedUser = strtoupper(trim((string)($kasuEmpleadosMap[$assignedId]['IdUsuario'] ?? '')));
        if ($kasuScopeSet !== null && ($assignedUser === '' || !isset($kasuScopeSet[$assignedUser]))) {
          continue;
        }

        $prosId = (int)($row['Id'] ?? 0);
        $citaTxt = '';
        if ($prosId > 0 && $stmtCita) {
          $stmtCita->bind_param('i', $prosId);
          $stmtCita->execute();
          $rowCita = $stmtCita->get_result()->fetch_assoc();
          $citaTxt = $rowCita ? kasu_format_fecha_es((string)($rowCita['FechaCita'] ?? '')) : '';
        }
        if ($citaTxt === '' && $prosId > 0 && $stmtAgenda) {
          $stmtAgenda->bind_param('i', $prosId);
          $stmtAgenda->execute();
          $rowCita = $stmtAgenda->get_result()->fetch_assoc();
          $citaTxt = $rowCita ? kasu_format_fecha_es((string)($rowCita['inicio'] ?? '')) : '';
        }

        $row['_cita'] = $citaTxt;
        $row['_asignado'] = $basicas->BuscarCampos($mysqli, 'IdUsuario', 'Empleados', 'Id', $assignedId);
        $row['_semanas'] = max(0, (int)round(((strtotime(date('Y-m-d')) - (strtotime((string)($row['Alta'] ?? '')) ?: strtotime(date('Y-m-d')))) / 604800), 0));
        $stage = $pipelineMap[$prosId] ?? ($citaTxt !== '' ? 'citado' : 'lead');
        $pipelineBuckets[$stage][] = ['tipo' => 'venta', 'data' => $row];
      }

      if ($hasFunerarias && $kasuScopeSet === null) {
        $funRows = [];
        if ($nombreTrim !== '') {
          $like = '%' . $nombreTrim . '%';
          $stmtFun = $pros->prepare("
            SELECT NombreComercial, RazonSocial, Contacto, Cargo, Telefono, Whatsapp, Email,
                   Direccion, Ciudad, Estado, CP, Cobertura, Servicios, Salas, CapacidadSala,
                   Disponibilidad, Permisos, Comentarios, FechaRegistro
            FROM prospectos_funerarias
            WHERE NombreComercial LIKE ? OR RazonSocial LIKE ? OR Contacto LIKE ?
            ORDER BY FechaRegistro DESC
          ");
          if ($stmtFun) {
            $stmtFun->bind_param('sss', $like, $like, $like);
            $stmtFun->execute();
            $funRows = $stmtFun->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtFun->close();
          }
        } elseif ($resFun = $pros->query("
          SELECT NombreComercial, RazonSocial, Contacto, Cargo, Telefono, Whatsapp, Email,
                 Direccion, Ciudad, Estado, CP, Cobertura, Servicios, Salas, CapacidadSala,
                 Disponibilidad, Permisos, Comentarios, FechaRegistro
          FROM prospectos_funerarias ORDER BY FechaRegistro DESC
        ")) {
          $funRows = $resFun->fetch_all(MYSQLI_ASSOC);
          $resFun->close();
        }
        foreach ($funRows as $rowFun) {
          $pipelineBuckets['lead'][] = ['tipo' => 'funeraria', 'data' => $rowFun];
        }
      }

      if ($stmtCita) { $stmtCita->close(); }
      if ($stmtAgenda) { $stmtAgenda->close(); }
      $pipelineTotal = array_sum(array_map('count', $pipelineBuckets));
    ?>

    <div class="prospectos-toolbar">
      <div>
        <h2>Tablero de Prospectos</h2>
        <small class="text-muted">Haz clic en un prospecto para gestionarlo o arrastra su tarjeta para cambiar la etapa.</small>
      </div>
      <form method="GET" action="<?= h($_SERVER['PHP_SELF']) ?>" class="prospectos-search">
        <input class="form-control" type="search" name="nombre" value="<?= h($nombreTrim) ?>" placeholder="Buscar prospecto, funeraria o contacto">
        <button class="btn btn-dark" type="submit"><i class="material-icons">search</i></button>
      </form>
    </div>

    <div class="prospectos-metrics">
      <?php foreach ($pipelineStages as $stageKey => $stageLabel): ?>
        <article class="prospectos-metric">
          <span><?= h($stageLabel) ?></span>
          <strong><?= count($pipelineBuckets[$stageKey]) ?></strong>
        </article>
      <?php endforeach; ?>
    </div>

    <div class="prospectos-board-wrap">
      <?php if (!$hasProspectosPipeline): ?>
        <div class="alert alert-warning">No fue posible preparar la persistencia del pipeline. El tablero funciona en modo de lectura.</div>
      <?php endif; ?>
      <p class="prospectos-board-note"><?= (int)$pipelineTotal ?> registros visibles. Las funerarias permanecen en Lead hasta contar con identificador de seguimiento.</p>
      <div class="prospectos-board">
        <?php foreach ($pipelineStages as $stageKey => $stageLabel): ?>
          <section class="prospectos-column" data-stage="<?= h($stageKey) ?>">
            <div class="prospectos-column-head">
              <h3><?= h($stageLabel) ?></h3>
              <span><?= count($pipelineBuckets[$stageKey]) ?></span>
            </div>
            <div class="prospectos-cards">
              <?php foreach ($pipelineBuckets[$stageKey] as $item): ?>
                <?php if ($item['tipo'] === 'venta'):
                  $row = $item['data'];
                  $prosId = (int)($row['Id'] ?? 0);
                  $telRaw = preg_replace('/\D+/', '', (string)($row['NoTel'] ?? ''));
                  $telIntl = strlen($telRaw) === 10 ? '52' . $telRaw : $telRaw;
                ?>
                  <article
                    class="prospecto-card js-prospecto-card"
                    draggable="<?= $hasProspectosPipeline ? 'true' : 'false' ?>"
                    tabindex="0"
                    role="button"
                    aria-label="Abrir detalle de <?= h($row['FullName'] ?? 'prospecto') ?>"
                    data-prospecto-id="<?= $prosId ?>"
                    data-stage="<?= h($stageKey) ?>"
                    data-stage-label="<?= h($pipelineStages[$stageKey] ?? $stageKey) ?>"
                    data-name="<?= h($row['FullName'] ?? '') ?>"
                    data-service="<?= h($row['Servicio_Interes'] ?? '') ?>"
                    data-origin="<?= h($row['Origen'] ?? '') ?>"
                    data-assigned="<?= h($row['_asignado'] ?: 'Sin asignar') ?>"
                    data-weeks="<?= (int)($row['_semanas'] ?? 0) ?>"
                    data-appointment="<?= h($row['_cita'] ?? '') ?>"
                    data-phone="<?= h($telRaw) ?>"
                    data-phone-intl="<?= h($telIntl) ?>"
                    data-email="<?= h($row['Email'] ?? '') ?>"
                    data-address="<?= h($row['Direccion'] ?? '') ?>"
                    data-curp="<?= h($row['Curp'] ?? '') ?>"
                    data-created="<?= h(kasu_format_fecha_es((string)($row['Alta'] ?? ''))) ?>"
                  >
                    <strong><?= h($row['FullName'] ?? '') ?></strong>
                    <small><?= h($row['Servicio_Interes'] ?? 'Sin servicio') ?></small>
                    <div class="prospecto-card-meta">
                      <span class="prospecto-chip"><?= (int)($row['_semanas'] ?? 0) ?> sem.</span>
                      <span class="prospecto-chip"><?= h($row['Origen'] ?? 'Sin origen') ?></span>
                      <span class="prospecto-chip"><?= h($row['_asignado'] ?: 'Sin asignar') ?></span>
                    </div>
                    <?php if ((string)($row['_cita'] ?? '') !== ''): ?><small><strong>Cita:</strong> <?= h($row['_cita']) ?></small><?php endif; ?>
                  </article>
                <?php else:
                  $rowFun = $item['data'];
                  $fechaFun = (string)($rowFun['FechaRegistro'] ?? '');
                  $telFun = preg_replace('/\D+/', '', (string)($rowFun['Telefono'] ?? ''));
                  $waFun = preg_replace('/\D+/', '', (string)($rowFun['Whatsapp'] ?? ''));
                ?>
                  <article class="prospecto-card" draggable="false" tabindex="0" role="button"
                    data-toggle="modal" data-target="#ModalFuneraria"
                    data-nombre="<?= h($rowFun['NombreComercial'] ?? '') ?>" data-razon="<?= h($rowFun['RazonSocial'] ?? '') ?>"
                    data-contacto="<?= h($rowFun['Contacto'] ?? '') ?>" data-cargo="<?= h($rowFun['Cargo'] ?? '') ?>"
                    data-telefono="<?= h($telFun) ?>" data-whatsapp="<?= h($waFun) ?>" data-email="<?= h($rowFun['Email'] ?? '') ?>"
                    data-direccion="<?= h($rowFun['Direccion'] ?? '') ?>" data-ciudad="<?= h($rowFun['Ciudad'] ?? '') ?>"
                    data-estado="<?= h($rowFun['Estado'] ?? '') ?>" data-cp="<?= h($rowFun['CP'] ?? '') ?>"
                    data-cobertura="<?= h($rowFun['Cobertura'] ?? '') ?>" data-servicios="<?= h($rowFun['Servicios'] ?? '') ?>"
                    data-disponibilidad="<?= h($rowFun['Disponibilidad'] ?? '') ?>" data-permisos="<?= h($rowFun['Permisos'] ?? '') ?>"
                    data-comentarios="<?= h($rowFun['Comentarios'] ?? '') ?>" data-registro="<?= h(kasu_format_fecha_es($fechaFun)) ?>">
                    <strong><?= h($rowFun['NombreComercial'] ?? '') ?></strong>
                    <small><?= h($rowFun['Contacto'] ?? 'Sin contacto') ?> · Funeraria</small>
                    <div class="prospecto-card-meta">
                      <span class="prospecto-chip"><?= h($rowFun['Ciudad'] ?? '') ?></span>
                      <span class="prospecto-chip"><?= h($rowFun['Cobertura'] ?? 'Sin cobertura') ?></span>
                    </div>
                    <span class="prospecto-card-hint">Clic para ver detalle</span>
                  </article>
                <?php endif; ?>
              <?php endforeach; ?>
              <?php if (count($pipelineBuckets[$stageKey]) === 0): ?><p class="prospectos-empty">Sin prospectos</p><?php endif; ?>
            </div>
          </section>
        <?php endforeach; ?>
      </div>
    </div>

    <form id="pipelineMoveForm" method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>" hidden>
      <input type="hidden" name="MoverPipeline" value="1">
      <input type="hidden" name="IdProspectoNum" id="pipelineMoveId" value="">
      <input type="hidden" name="EtapaPipeline" id="pipelineMoveStage" value="">
      <input type="hidden" name="nombre" value="<?= h($nombre) ?>">
      <input type="hidden" name="csrf_pipeline" value="<?= h($pipelineCsrf) ?>">
    </form>
  </section>

  <button type="button" class="prospecto-detail-backdrop" id="prospectoDetailBackdrop" aria-label="Cerrar detalle"></button>
  <aside class="prospecto-detail" id="prospectoDetail" aria-hidden="true">
    <div class="prospecto-detail-head">
      <div>
        <h3 id="detailName">Prospecto</h3>
        <p><span id="detailService"></span> · <span id="detailStage"></span></p>
      </div>
      <button type="button" class="prospecto-detail-close" id="prospectoDetailClose" aria-label="Cerrar detalle">
        <i class="material-icons">close</i>
      </button>
    </div>

    <section class="prospecto-detail-section">
      <h4>Datos del prospecto</h4>
      <dl class="prospecto-detail-data">
        <div><dt>Teléfono</dt><dd id="detailPhone">Sin teléfono</dd></div>
        <div><dt>Correo</dt><dd id="detailEmail">Sin correo</dd></div>
        <div><dt>Dirección</dt><dd id="detailAddress">Sin dirección</dd></div>
        <div><dt>CURP</dt><dd id="detailCurp">Sin CURP</dd></div>
        <div><dt>Origen</dt><dd id="detailOrigin">Sin origen</dd></div>
        <div><dt>Asignado</dt><dd id="detailAssigned">Sin asignar</dd></div>
        <div><dt>Cita</dt><dd id="detailAppointment">Sin cita</dd></div>
        <div><dt>Antigüedad</dt><dd id="detailAge"></dd></div>
        <div><dt>Alta</dt><dd id="detailCreated">Sin fecha</dd></div>
      </dl>
    </section>

    <section class="prospecto-detail-section">
      <h4>Contacto rápido</h4>
      <div class="prospecto-detail-actions">
        <a class="btn" id="detailCall" href="#" style="background:#1abc9c"><i class="material-icons">call</i> Llamar</a>
        <a class="btn" id="detailWhatsapp" href="#" target="_blank" rel="noopener" style="background:#25D366"><i class="fa fa-whatsapp"></i> WhatsApp</a>
        <a class="btn" id="detailMail" href="#" style="background:#5D6D7E"><i class="material-icons">email</i> Correo</a>
      </div>
    </section>

    <section class="prospecto-detail-section">
      <h4>Acciones del prospecto</h4>
      <div class="prospecto-detail-actions">
        <form method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>" id="detailActionsForm">
          <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">
          <input type="hidden" name="nombre" value="<?= h($nombre) ?>">
          <button class="btn" id="detailSale" type="submit" name="IdProspecto" value="" style="background:#58D68D"><i class="material-icons">verified_user</i> Registrar venta</button>
          <button class="btn" id="detailLeadSales" type="submit" name="IdProspecto" value="" style="background:#21618C"><i class="material-icons">card_travel</i> LeadSales</button>
          <button class="btn" id="detailFollowup" type="submit" name="IdProspecto" value="" style="background:#3498DB"><i class="material-icons">chat</i> Seguimiento</button>
          <button class="btn" id="detailEdit" type="submit" name="IdProspecto" value="" style="background:#7f8c8d"><i class="material-icons">badge</i> Editar</button>
          <?php if ($canReassignProspect): ?>
            <button class="btn" id="detailAssign" type="submit" name="IdProspecto" value="" style="background:#AF7AC5"><i class="material-icons">people_alt</i> Reasignar</button>
          <?php endif; ?>
          <button class="btn" id="detailCancel" type="submit" name="IdProspecto" value="" style="background:#E74C3C"><i class="material-icons">cancel</i> Dar de baja</button>
        </form>
      </div>
    </section>
  </aside>

  <div class="modal fade" id="ModalFuneraria" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detalle de funeraria</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <p class="mb-1"><strong>Funeraria</strong></p>
              <p id="fun-nombre" class="mb-2"></p>
              <p class="mb-1"><strong>Razon social</strong></p>
              <p id="fun-razon" class="mb-2"></p>
              <p class="mb-1"><strong>Contacto</strong></p>
              <p id="fun-contacto" class="mb-2"></p>
              <p class="mb-1"><strong>Telefono</strong></p>
              <p id="fun-telefono" class="mb-2"></p>
              <p class="mb-1"><strong>Email</strong></p>
              <p id="fun-email" class="mb-2"></p>
            </div>
            <div class="col-md-6">
              <p class="mb-1"><strong>Direccion</strong></p>
              <p id="fun-direccion" class="mb-2"></p>
              <p class="mb-1"><strong>Cobertura</strong></p>
              <p id="fun-cobertura" class="mb-2"></p>
              <p class="mb-1"><strong>Servicios</strong></p>
              <p id="fun-servicios" class="mb-2"></p>
              <p class="mb-1"><strong>Disponibilidad / Permisos</strong></p>
              <p id="fun-status" class="mb-2"></p>
              <p class="mb-1"><strong>Registro</strong></p>
              <p id="fun-registro" class="mb-0"></p>
            </div>
          </div>
          <div class="mt-3">
            <p class="mb-1"><strong>Comentarios</strong></p>
            <p id="fun-comentarios" class="mb-0"></p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- =================== JS únicos y en orden ===================
       Qué hace: Carga dependencias JS y utilidades de la PWA
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
  <script src="https://www.gstatic.com/charts/loader.js"></script>
  <script src="Javascript/finger.js?v=3"></script>
  <script src="Javascript/localize.js?v=3"></script>
  <script src="Javascript/Inyectar_gps_form.js"></script>

  <!-- =================== Auto-apertura de modal ===================
       Qué hace: Si $Lanzar tiene valor, abre el modal #Ventana al cargar
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      <?php if (!empty($Lanzar)): ?>
        $('<?= h($Lanzar) ?>').modal('show');
      <?php endif; ?>
    });
  </script>
  <script>
    $('#ModalFuneraria').on('show.bs.modal', function (event) {
      var btn = $(event.relatedTarget);
      var nombre = btn.data('nombre') || '';
      var razon = btn.data('razon') || '';
      var contacto = btn.data('contacto') || '';
      var cargo = btn.data('cargo') || '';
      var telefono = btn.data('telefono') || '';
      var whatsapp = btn.data('whatsapp') || '';
      var email = btn.data('email') || '';
      var direccion = btn.data('direccion') || '';
      var ciudad = btn.data('ciudad') || '';
      var estado = btn.data('estado') || '';
      var cp = btn.data('cp') || '';
      var cobertura = btn.data('cobertura') || '';
      var servicios = btn.data('servicios') || '';
      var disponibilidad = btn.data('disponibilidad') || '';
      var permisos = btn.data('permisos') || '';
      var comentarios = btn.data('comentarios') || '';
      var registro = btn.data('registro') || '';

      var contactoTxt = contacto;
      if (cargo) contactoTxt += (contactoTxt ? ' · ' : '') + cargo;

      var telefonoTxt = telefono;
      if (whatsapp) telefonoTxt += (telefonoTxt ? ' · ' : '') + 'WA: ' + whatsapp;

      var direccionTxt = direccion;
      if (ciudad || estado) {
        direccionTxt += (direccionTxt ? ', ' : '') + ciudad + (estado ? ', ' + estado : '');
      }
      if (cp) direccionTxt += (direccionTxt ? ' ' : '') + 'CP ' + cp;

      $('#fun-nombre').text(nombre);
      $('#fun-razon').text(razon);
      $('#fun-contacto').text(contactoTxt);
      $('#fun-telefono').text(telefonoTxt);
      $('#fun-email').text(email);
      $('#fun-direccion').text(direccionTxt);
      $('#fun-cobertura').text(cobertura);
      $('#fun-servicios').text(servicios);
      $('#fun-status').text((disponibilidad ? 'Disp: ' + disponibilidad : '') + (permisos ? ' · Permisos: ' + permisos : ''));
      $('#fun-comentarios').text(comentarios || 'Sin comentarios');
      $('#fun-registro').text(registro);
    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      $('#Ventana').on('shown.bs.modal', async function () {
        const resultBox = document.getElementById('ia-prospecto-result');
        if (!resultBox) return;
        const prospectoId = resultBox.getAttribute('data-ia-prospecto');
        if (!prospectoId) return;
        resultBox.innerHTML = '<p class="text-muted mb-0">Analizando...</p>';
        try {
          const resp = await fetch('/eia/Vista-360/vista360_prospecto_analisis.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_prospecto: Number(prospectoId), force: true })
          });
          const data = await resp.json();
          if (data && data.ok && data.html) {
            resultBox.innerHTML = data.html;
          } else {
            resultBox.innerHTML = '<p class="text-danger mb-0">No se pudo generar el analisis.</p>';
          }
        } catch (err) {
          resultBox.innerHTML = '<p class="text-danger mb-0">Error al llamar a IA.</p>';
        }
      });
    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var board = document.querySelector('.prospectos-board');
      var moveForm = document.getElementById('pipelineMoveForm');
      var idInput = document.getElementById('pipelineMoveId');
      var stageInput = document.getElementById('pipelineMoveStage');
      if (!board || !moveForm || !idInput || !stageInput) return;

      var detail = document.getElementById('prospectoDetail');
      var detailBackdrop = document.getElementById('prospectoDetailBackdrop');
      var detailClose = document.getElementById('prospectoDetailClose');
      var draggedCard = null;
      var detailFields = {
        name: document.getElementById('detailName'),
        service: document.getElementById('detailService'),
        stage: document.getElementById('detailStage'),
        phone: document.getElementById('detailPhone'),
        email: document.getElementById('detailEmail'),
        address: document.getElementById('detailAddress'),
        curp: document.getElementById('detailCurp'),
        origin: document.getElementById('detailOrigin'),
        assigned: document.getElementById('detailAssigned'),
        appointment: document.getElementById('detailAppointment'),
        age: document.getElementById('detailAge'),
        created: document.getElementById('detailCreated')
      };
      var detailButtons = {
        sale: document.getElementById('detailSale'),
        leadSales: document.getElementById('detailLeadSales'),
        followup: document.getElementById('detailFollowup'),
        edit: document.getElementById('detailEdit'),
        assign: document.getElementById('detailAssign'),
        cancel: document.getElementById('detailCancel'),
        call: document.getElementById('detailCall'),
        whatsapp: document.getElementById('detailWhatsapp'),
        mail: document.getElementById('detailMail')
      };

      function setText(element, value, fallback) {
        if (element) element.textContent = value || fallback || '';
      }

      function setLink(element, href, visible) {
        if (!element) return;
        element.hidden = !visible;
        element.setAttribute('href', visible ? href : '#');
      }

      function closeDetail() {
        if (!detail || !detailBackdrop) return;
        detail.classList.remove('is-open');
        detailBackdrop.classList.remove('is-open');
        detail.setAttribute('aria-hidden', 'true');
        board.querySelectorAll('.prospecto-card.is-selected').forEach(function (card) {
          card.classList.remove('is-selected');
        });
      }

      function openDetail(card) {
        if (!detail || !detailBackdrop) return;
        var data = card.dataset;
        var prospectoId = data.prospectoId || '';

        setText(detailFields.name, data.name, 'Prospecto');
        setText(detailFields.service, data.service, 'Sin servicio');
        setText(detailFields.stage, data.stageLabel, 'Lead');
        setText(detailFields.phone, data.phone, 'Sin teléfono');
        setText(detailFields.email, data.email, 'Sin correo');
        setText(detailFields.address, data.address, 'Sin dirección');
        setText(detailFields.curp, data.curp, 'Sin CURP');
        setText(detailFields.origin, data.origin, 'Sin origen');
        setText(detailFields.assigned, data.assigned, 'Sin asignar');
        setText(detailFields.appointment, data.appointment, 'Sin cita');
        setText(detailFields.age, (data.weeks || '0') + ' semanas activo');
        setText(detailFields.created, data.created, 'Sin fecha');

        if (detailButtons.sale) {
          detailButtons.sale.value = '1' + prospectoId;
          detailButtons.sale.hidden = !data.curp;
        }
        if (detailButtons.leadSales) detailButtons.leadSales.value = '6' + prospectoId;
        if (detailButtons.followup) detailButtons.followup.value = '7' + prospectoId;
        if (detailButtons.edit) detailButtons.edit.value = '5' + prospectoId;
        if (detailButtons.assign) detailButtons.assign.value = '3' + prospectoId;
        if (detailButtons.cancel) detailButtons.cancel.value = '2' + prospectoId;

        setLink(detailButtons.call, 'tel:' + (data.phone || ''), !!data.phone);
        setLink(detailButtons.whatsapp, 'https://wa.me/' + (data.phoneIntl || ''), !!data.phoneIntl);
        setLink(detailButtons.mail, 'mailto:' + (data.email || ''), !!data.email);

        board.querySelectorAll('.prospecto-card.is-selected').forEach(function (selectedCard) {
          selectedCard.classList.remove('is-selected');
        });
        card.classList.add('is-selected');
        detail.classList.add('is-open');
        detailBackdrop.classList.add('is-open');
        detail.setAttribute('aria-hidden', 'false');
        if (detailClose) detailClose.focus();
      }

      function clearTargets() {
        board.querySelectorAll('.prospectos-column.is-drop-target').forEach(function (column) {
          column.classList.remove('is-drop-target');
        });
      }

      board.querySelectorAll('.js-prospecto-card[data-prospecto-id]').forEach(function (card) {
        card.addEventListener('click', function () {
          if (card.dataset.dragged === '1') return;
          openDetail(card);
        });
        card.addEventListener('keydown', function (event) {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openDetail(card);
          }
        });
      });

      board.querySelectorAll('.prospecto-card[draggable="true"][data-prospecto-id]').forEach(function (card) {
        card.addEventListener('dragstart', function (event) {
          draggedCard = card;
          card.dataset.dragged = '1';
          card.classList.add('is-dragging');
          closeDetail();
          if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', card.getAttribute('data-prospecto-id') || '');
          }
        });
        card.addEventListener('dragend', function () {
          card.classList.remove('is-dragging');
          clearTargets();
          draggedCard = null;
          window.setTimeout(function () {
            card.dataset.dragged = '0';
          }, 180);
        });
      });

      if (detailClose) detailClose.addEventListener('click', closeDetail);
      if (detailBackdrop) detailBackdrop.addEventListener('click', closeDetail);
      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeDetail();
      });

      board.querySelectorAll('.prospectos-column[data-stage]').forEach(function (column) {
        column.addEventListener('dragover', function (event) {
          event.preventDefault();
          column.classList.add('is-drop-target');
        });
        column.addEventListener('dragleave', function () {
          column.classList.remove('is-drop-target');
        });
        column.addEventListener('drop', function (event) {
          event.preventDefault();
          clearTargets();
          if (!draggedCard) return;

          var prospectoId = draggedCard.getAttribute('data-prospecto-id') || '';
          var currentStage = draggedCard.getAttribute('data-stage') || '';
          var targetStage = column.getAttribute('data-stage') || '';
          if (!prospectoId || !targetStage || currentStage === targetStage) return;

          idInput.value = prospectoId;
          stageInput.value = targetStage;
          moveForm.submit();
        });
      });
    });
  </script>
</body>
</html>
