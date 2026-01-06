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

if (!function_exists('kasu_load_empleados_tree')) {
  function kasu_load_empleados_tree(mysqli $mysqli): array {
    $byId = [];
    $children = [];
    if ($res = $mysqli->query("SELECT Id, IdUsuario, Equipo, Nivel FROM Empleados")) {
      while ($row = $res->fetch_assoc()) {
        $id = (int)$row['Id'];
        $byId[$id] = [
          'IdUsuario' => (string)($row['IdUsuario'] ?? ''),
          'Equipo'    => (int)($row['Equipo'] ?? 0),
          'Nivel'     => (int)($row['Nivel'] ?? 0),
        ];
        $parent = (int)($row['Equipo'] ?? 0);
        if (!isset($children[$parent])) {
          $children[$parent] = [];
        }
        $children[$parent][] = $id;
      }
      $res->close();
    }
    return [$byId, $children];
  }

  function kasu_collect_descendants(int $rootId, array $byId, array $children): array {
    $scope = [];
    if ($rootId <= 0) {
      return [];
    }
    $stack = [$rootId];
    while ($stack) {
      $current = array_pop($stack);
      if (!isset($byId[$current])) {
        continue;
      }
      $usr = strtoupper(trim((string)$byId[$current]['IdUsuario']));
      if ($usr !== '') {
        $scope[$usr] = true;
      }
      foreach ($children[$current] ?? [] as $childId) {
        $stack[] = $childId;
      }
    }
    return array_keys($scope);
  }

  function kasu_find_ancestor_level(int $startId, array $byId, int $targetLevel): ?int {
    $current = $startId;
    while ($current > 0 && isset($byId[$current])) {
      if ((int)($byId[$current]['Nivel'] ?? 0) === $targetLevel) {
        return $current;
      }
      $parent = (int)($byId[$current]['Equipo'] ?? 0);
      if ($parent === 0 || $parent === $current) {
        break;
      }
      $current = $parent;
    }
    return null;
  }

  function kasu_scope_user_ids(int $nivel, int $empleadoId, array $byId, array $children): ?array {
    if ($nivel <= 0 || $empleadoId <= 0) {
      return null;
    }
    if ($nivel <= 2) {
      return null;
    }

    $rootId = $empleadoId;
    if ($nivel === 5) {
      $gerente = kasu_find_ancestor_level($empleadoId, $byId, 3);
      if ($gerente !== null) {
        $rootId = $gerente;
      }
    }

    $ids = kasu_collect_descendants($rootId, $byId, $children);
    return $ids ?: null;
  }
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

$hasCitas = kasu_table_exists($pros, 'citas');
$hasAgenda = kasu_table_exists($pros, 'agenda_llamadas');
$hasFunerarias = kasu_table_exists($pros, 'prospectos_funerarias');
$stmtCita = null;
$stmtAgenda = null;
if ($hasCitas) {
  $stmtCita = $pros->prepare("SELECT FechaCita FROM citas WHERE IdProspecto = ? ORDER BY FechaCita DESC LIMIT 1");
}
if ($hasAgenda) {
  $stmtAgenda = $pros->prepare("SELECT inicio FROM agenda_llamadas WHERE prospecto_id = ? ORDER BY inicio DESC LIMIT 1");
}

// =================== Cancelación de prospecto (POST CancelaCte) ===================
// Qué hace: Registra auditoría, marca cancelación y notifica por mensaje
// Fecha: 05/11/2025 | Revisado por: JCCM
if (!empty($_POST['CancelaCte'])) {
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
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
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
    }
    .mesa-prospectos .topbar {
      background: var(--mesa-menu-bg);
      border-bottom: 1px solid rgba(255, 255, 255, 0.25);
      color: var(--mesa-menu-text);
      position: fixed;
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
                  } elseif ($Niv === 1) {
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
            $sql = "
              SELECT e.Id, e.Nombre, COALESCE(s.nombreSucursal,'Sin sucursal') AS Sucursal
              FROM Empleados e
              LEFT JOIN Sucursal s ON s.Id = e.Sucursal
              WHERE e.Nivel >= 6 AND e.Nombre <> 'Vacante'
              ORDER BY s.nombreSucursal, e.Nombre
            ";
            if ($res = $mysqli->query($sql)) {
              while ($row = $res->fetch_assoc()) {
                echo '<option value="'.h($row['Id']).'">'.h($row['Nombre'].' - '.$row['Sucursal']).'</option>';
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

  <!-- =================== Tabla de prospectos ===================
       Qué hace: Lista prospectos activos, calcula semanas y muestra acciones
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <section name="impresion de datos finales" class="mesa-prospectos-content">
    <div class="mesa-prospectos-scroll table-responsive mesa-table-wrapper">
      <table class="table mesa-table" data-mesa="prospectos">
        <thead>
          <tr>
            <th>Nombre Prospecto</th>
            <th>Sem. Activo</th>
            <th>Servicio</th>
            <th>Cita</th>
            <th>Origen</th>
            <th>Asignado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $rowsUnified = [];

        // Prospectos de ventas
        if ($nombre === ' ') {
          $buscar = $basicas->BLikesD2($pros,'prospectos','FullName',$nombre,'Cancelacion',0,'Automatico',0);
        } else {
          $buscar = $basicas->BLikesCan($pros,'prospectos','FullName',$nombre,'Cancelacion',0);
        }

        foreach ($buscar as $row) {
          $assignedId = (int)($row['Asignado'] ?? 0);
          $assignedUser = '';
          if (isset($kasuEmpleadosMap[$assignedId])) {
            $assignedUser = strtoupper(trim((string)$kasuEmpleadosMap[$assignedId]['IdUsuario']));
          }
          if ($kasuScopeSet !== null && $assignedUser !== '' && !isset($kasuScopeSet[$assignedUser])) {
            continue;
          }
          $rowsUnified[] = [
            'tipo' => 'venta',
            'sort' => strtotime((string)($row['Alta'] ?? '')) ?: 0,
            'data' => $row,
          ];
        }

        // Prospectos de funerarias
        if ($hasFunerarias) {
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
              foreach ($funRows as $rowFun) {
                $rowsUnified[] = [
                  'tipo' => 'funeraria',
                  'sort' => strtotime((string)($rowFun['FechaRegistro'] ?? '')) ?: 0,
                  'data' => $rowFun,
                ];
              }
            }
          } else {
            if ($resFun = $pros->query("
              SELECT NombreComercial, RazonSocial, Contacto, Cargo, Telefono, Whatsapp, Email,
                     Direccion, Ciudad, Estado, CP, Cobertura, Servicios, Salas, CapacidadSala,
                     Disponibilidad, Permisos, Comentarios, FechaRegistro
              FROM prospectos_funerarias
              ORDER BY FechaRegistro DESC
            ")) {
              $funRows = $resFun->fetch_all(MYSQLI_ASSOC);
              $resFun->close();
              foreach ($funRows as $rowFun) {
                $rowsUnified[] = [
                  'tipo' => 'funeraria',
                  'sort' => strtotime((string)($rowFun['FechaRegistro'] ?? '')) ?: 0,
                  'data' => $rowFun,
                ];
              }
            }
          }
        }

        usort($rowsUnified, function ($a, $b) {
          return ($b['sort'] ?? 0) <=> ($a['sort'] ?? 0);
        });

        foreach ($rowsUnified as $item):
          if ($item['tipo'] === 'venta'):
            $row = $item['data'];
            $Sem     = strtotime((string)($row['Alta'] ?? ''));
            $HoyA    = strtotime(date("Y-m-d"));
            $ContSem = $Sem ? (($HoyA - $Sem) / 604800) : 0;

            $citaTxt = '';
            $prosId = (int)($row['Id'] ?? 0);
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
            $telRaw = preg_replace('/\D+/', '', (string)($row['NoTel'] ?? ''));
            $telIntl = $telRaw;
            if ($telRaw !== '' && strlen($telRaw) === 10) {
              $telIntl = '52' . $telRaw;
            }
        ?>
          <tr>
            <td><?= h($row['FullName']) ?></td>
            <td><?= (int)round($ContSem, 0) ?></td>
            <td><?= h($row['Servicio_Interes']) ?></td>
            <td><?= h($citaTxt) ?></td>
            <td><?= h($row['Origen']) ?></td>
            <td><?= h($basicas->BuscarCampos($mysqli,'IdUsuario','Empleados','Id',(int)$row['Asignado'])) ?></td>
            <td class="mesa-actions" data-label="Acciones">
              <div class="mesa-actions-grid">
                <form method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>">
                  <input type="hidden" name="Host"   value="<?= h($_SERVER['PHP_SELF']) ?>">
                  <input type="hidden" name="nombre" value="<?= h($nombre) ?>">

                  <!-- Registrar Venta (1) -->
                  <?php if (trim((string)($row['Curp'] ?? '')) !== ''): ?>
                    <label for="b1<?= (int)$row['Id'] ?>" title="Registrar Venta" class="btn" style="background:#58D68D;color:#F8F9F9;">
                      <i class="material-icons">verified_user</i>
                    </label>
                    <input id="b1<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="1<?= (int)$row['Id'] ?>" hidden>
                  <?php endif; ?>

                  <!-- Enviar lead Sales (6) -->
                  <label for="b6<?= (int)$row['Id'] ?>" title="Enviar lead Sales" class="btn" style="background:#21618C;color:#F8F9F9;">
                    <i class="material-icons">card_travel</i>
                  </label>
                  <input id="b6<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="6<?= (int)$row['Id'] ?>" hidden>

                  <!-- Dar de Baja (2) -->
                  <label for="b2<?= (int)$row['Id'] ?>" title="Dar de Baja al Prospecto" class="btn" style="background:#E74C3C;color:#F8F9F9;">
                    <i class="material-icons">cancel</i>
                  </label>
                  <input id="b2<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="2<?= (int)$row['Id'] ?>" hidden>

                  <!-- Reasignar a ejecutivo (3) -->
                  <label for="b3<?= (int)$row['Id'] ?>" title="Asignar prospecto a un ejecutivo" class="btn" style="background:#AF7AC5;color:#F8F9F9;">
                    <i class="material-icons">people_alt</i>
                  </label>
                  <input id="b3<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="3<?= (int)$row['Id'] ?>" hidden>

                  <!-- Comentarios y seguimiento (7) -->
                  <label for="b7<?= (int)$row['Id'] ?>" title="Comentarios y seguimiento" class="btn" style="background:#3498DB;color:#F8F9F9;">
                    <i class="material-icons">chat</i>
                  </label>
                  <input id="b7<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="7<?= (int)$row['Id'] ?>" hidden>
                  <!-- Cambiar datos (5) -->
                  <label for="b5<?= (int)$row['Id'] ?>" title="Cambiar datos del Prospecto" class="btn" style="background:#AAB7B8;color:#F8F9F9;">
                    <i class="material-icons">badge</i>
                  </label>
                  <input id="b5<?= (int)$row['Id'] ?>" type="submit" name="IdProspecto" value="5<?= (int)$row['Id'] ?>" hidden>

                  <?php if ($telRaw !== ''): ?>
                    <a class="btn" href="<?= h('tel:' . $telRaw) ?>" title="Llamar" style="background:#1abc9c;color:#F8F9F9;">
                      <i class="material-icons">call</i>
                    </a>
                    <a class="btn" href="<?= h('https://wa.me/' . $telIntl) ?>" title="WhatsApp" target="_blank" rel="noopener" style="background:#25D366;color:#F8F9F9;">
                      <i class="fa fa-whatsapp" aria-hidden="true" style="font-size:18px;line-height:1;"></i>
                    </a>
                  <?php endif; ?>
                </form>
              </div>
            </td>
          </tr>
        <?php else:
          $rowFun = $item['data'];
          $fechaFun = (string)($rowFun['FechaRegistro'] ?? '');
          $SemFun = strtotime($fechaFun);
          $HoyA = strtotime(date("Y-m-d"));
          $ContSemFun = $SemFun ? (($HoyA - $SemFun) / 604800) : 0;
          $citaFun = 'Espacio: ' . (int)($rowFun['Salas'] ?? 0) . ' salas / ' . (int)($rowFun['CapacidadSala'] ?? 0) . ' pax';
          $ubicFun = trim((string)($rowFun['Ciudad'] ?? '') . ', ' . (string)($rowFun['Estado'] ?? ''));
          $telFun = preg_replace('/\D+/', '', (string)($rowFun['Telefono'] ?? ''));
          $waFun = preg_replace('/\D+/', '', (string)($rowFun['Whatsapp'] ?? ''));
        ?>
          <tr>
            <td>
              <?= h($rowFun['NombreComercial'] ?? '') ?><br>
              <small class="text-muted"><?= h($rowFun['RazonSocial'] ?? '') ?></small>
            </td>
            <td><?= (int)round($ContSemFun, 0) ?></td>
            <td>
              FUNERARIA<br>
              <small class="text-muted"><?= h($rowFun['Servicios'] ?? '') ?></small>
            </td>
            <td><?= h($citaFun) ?></td>
            <td>
              <?= h($ubicFun) ?><br>
              <small class="text-muted"><?= h($rowFun['Cobertura'] ?? '') ?></small>
            </td>
            <td>
              <?= h($rowFun['Contacto'] ?? '') ?><br>
              <small class="text-muted"><?= h($rowFun['Cargo'] ?? '') ?></small>
            </td>
            <td class="mesa-actions" data-label="Acciones">
              <div class="mesa-actions-grid">
                <button
                  type="button"
                  class="btn"
                  style="background:#5D6D7E;color:#F8F9F9;"
                  data-toggle="modal"
                  data-target="#ModalFuneraria"
                  data-nombre="<?= h($rowFun['NombreComercial'] ?? '') ?>"
                  data-razon="<?= h($rowFun['RazonSocial'] ?? '') ?>"
                  data-contacto="<?= h($rowFun['Contacto'] ?? '') ?>"
                  data-cargo="<?= h($rowFun['Cargo'] ?? '') ?>"
                  data-telefono="<?= h($telFun) ?>"
                  data-whatsapp="<?= h($waFun) ?>"
                  data-email="<?= h($rowFun['Email'] ?? '') ?>"
                  data-direccion="<?= h($rowFun['Direccion'] ?? '') ?>"
                  data-ciudad="<?= h($rowFun['Ciudad'] ?? '') ?>"
                  data-estado="<?= h($rowFun['Estado'] ?? '') ?>"
                  data-cp="<?= h($rowFun['CP'] ?? '') ?>"
                  data-cobertura="<?= h($rowFun['Cobertura'] ?? '') ?>"
                  data-servicios="<?= h($rowFun['Servicios'] ?? '') ?>"
                  data-disponibilidad="<?= h($rowFun['Disponibilidad'] ?? '') ?>"
                  data-permisos="<?= h($rowFun['Permisos'] ?? '') ?>"
                  data-comentarios="<?= h($rowFun['Comentarios'] ?? '') ?>"
                  data-registro="<?= h(kasu_format_fecha_es($fechaFun)) ?>"
                >
                  <i class="material-icons">visibility</i>
                </button>

                <?php if ($telFun !== ''): ?>
                  <a class="btn" href="<?= h('tel:' . $telFun) ?>" title="Llamar" style="background:#1abc9c;color:#F8F9F9;">
                    <i class="material-icons">call</i>
                  </a>
                <?php endif; ?>
                <?php if ($waFun !== ''): ?>
                  <a class="btn" href="<?= h('https://wa.me/52' . $waFun) ?>" title="WhatsApp" target="_blank" rel="noopener" style="background:#25D366;color:#F8F9F9;">
                    <i class="fa fa-whatsapp" aria-hidden="true" style="font-size:18px;line-height:1;"></i>
                  </a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php
          if ($stmtCita) { $stmtCita->close(); }
          if ($stmtAgenda) { $stmtAgenda->close(); }
        ?>
        </tbody>
      </table>

    </div>
  </section>

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
</body>
</html>
