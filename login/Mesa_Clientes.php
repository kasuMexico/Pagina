<?php
/********************************************************************************************
 * Qué hace: Panel "Cartera de Clientes". Permite consultar clientes/ventas, abrir modales
 *           para pagos, promesas, reasignación, actualización de datos, servicio funerario,
 *           cancelación, generación de fichas y póliza, y tickets de soporte.
 *           Ajustes PHP 8.2: mysqli en modo excepciones, salidas saneadas, tipos explícitos.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 * Mesa: Mesa_Clientes.php
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

// =================== Guardia de sesión ===================
if (empty($_SESSION['Vendedor'])) {
  header('Location: https://kasu.com.mx/login');
  exit;
}

// =================== Utils ===================
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

// =================== Variables base ===================
$Reg              = [];
$Recg             = [];
$Recg1            = [];
$Pago             = 0.0;
$Pago1            = 0.0;
$PagoPend         = 0.0;
$Saldo            = 0.0;
$Status           = '';
$Ventana          = null;
$Lanzar           = null;
$RegEnviarLigaPago = false;

// Filtros persistentes
$nombreFiltro = isset($_POST['nombre'])
  ? (string)$_POST['nombre']
  : (string)($_GET['nombre'] ?? ($_GET['name'] ?? ''));

$statusFiltro = isset($_POST['Status'])
  ? (string)$_POST['Status']
  : (string)($_GET['Status'] ?? '');

// Lo usamos igual que antes
$nombre = $nombreFiltro;

$Vende = $basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
$IdEmpleadoSesion = (int)$basicas->BuscarCampos($mysqli, "Id", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
[$kasuEmpleadosMap, $kasuEmpleadosChildren] = kasu_load_empleados_tree($mysqli);
$kasuScopeUsers = kasu_scope_user_ids((int)$Vende, $IdEmpleadoSesion, $kasuEmpleadosMap, $kasuEmpleadosChildren);
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

// =================== Router por POST IdCliente ===================
if (!empty($_POST['IdCliente'])) {
  if ($_POST['IdCliente'] === "btnCrearCte") {
    $Vtn = 10;
    $Cte = 0;
  } else {
    $Vtn = (int)substr($_POST['IdCliente'], 0, 1);
    $Cte = (int)substr($_POST['IdCliente'], 1);
  }

  // ===== Venta seleccionada =====
  if ($Cte > 0) {
    $st = $mysqli->prepare("SELECT * FROM Venta WHERE Id = ? LIMIT 1");
    $st->bind_param('i',$Cte);
    $st->execute();
    $Reg = $st->get_result()->fetch_assoc() ?: [];
    $st->close();

    if ($Reg) {
      // Importes y estado crédito
      $Pago1    = (float)$financieras->Pago($mysqli, $Cte);
      $Pago     = (float)number_format($Pago1, 2, '.', '');
      $PagoPend = (float)$financieras->PagosPend($mysqli, $Cte);
      $Saldo    = (float)number_format((float)$financieras->SaldoCredito($mysqli, $Cte), 2, '.', '');

      // Estado de mora/corriente (para la venta seleccionada)
      $StatVtas = $financieras->estado_mora_corriente((int)$Reg['Id']);
      $Status   = (!empty($StatVtas['estado']) && $StatVtas['estado'] === "AL CORRIENTE") ? "Pago" : "Mora";

      // Usuario y contacto relacionados a la venta
      if (!empty($Reg['IdContact'])) {
        $idC = (int)$Reg['IdContact'];

        $st = $mysqli->prepare("SELECT * FROM Usuario WHERE IdContact = ? LIMIT 1");
        $st->bind_param('i', $idC);
        $st->execute();
        $Recg1 = $st->get_result()->fetch_assoc() ?: [];
        $st->close();

        $st = $mysqli->prepare("SELECT * FROM Contacto WHERE id = ? LIMIT 1");
        $st->bind_param('i', $idC);
        $st->execute();
        $Recg = $st->get_result()->fetch_assoc() ?: [];
        $st->close();
      }

      $usuarioVentaSel = strtoupper(trim((string)($Reg['Usuario'] ?? '')));
      $RegEnviarLigaPago = ($usuarioVentaSel === 'PLATAFORMA');
    }
  }

  $Ventana = "Ventana{$Vtn}";
  $Lanzar  = "#{$Ventana}";
}

// =================== Acciones POST ===================
if (!empty($_POST['CambiVend'])) {
  // Reasignar ejecutivo en tablas relacionadas
  $idVta   = (int)($_POST['IdVenta'] ?? 0);
  $nvoVend = (string)$_POST['NvoVend'] ?? '';
  if ($idVta > 0 && $nvoVend !== '') {
    $basicas->ActCampo($mysqli, "Venta", "Usuario", $nvoVend, $idVta);
    $basicas->ActTab($mysqli, "PromesaPago", "Usuario", $nvoVend, "IdVenta", $idVta);
    $basicas->ActTab($mysqli, "Pagos", "Usuario", $nvoVend, "IdVenta", $idVta);
  }
} elseif (!empty($_POST['CancelaCte'])) {
  // Auditoría y cancelación de venta
  $seguridad->auditoria_registrar(
    $mysqli, $basicas, $_POST, 'Cancelar_Venta', $_POST['Host'] ?? $_SERVER['PHP_SELF']
  );
  $idVta = (int)($_POST['IdVenta'] ?? 0);
  if ($idVta > 0) {
    $basicas->ActCampo($mysqli, "Venta", "Status", "CANCELADO", $idVta);
  }
  $_GET['Vt']  = 1;
  $_GET['Msg'] = "Se ha cancelado la Venta";
}

// =================== Alertas ===================
if (isset($_GET['Msg'])) {
  $msg = (string)$_GET['Msg'];
  echo "<script>window.addEventListener('load',()=>alert(".json_encode($msg, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."));</script>";
}

// =================== Token anti-duplicado correo ===================
$_SESSION['mail_token'] = bin2hex(random_bytes(16));

// =================== Cache bust ===================
$VerCache = time();
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F2F2F2">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Mesa Clientes</title>

  <!-- =================== PWA / iOS =================== -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- =================== CSS unificado =================== -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="/login/assets/css/Menu_Superior.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="/login/assets/css/pwa-core.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="/login/assets/css/pwa-components.css?v=<?= h((string)$VerCache) ?>">

  <!-- Ajuste específico de botones para Mesa_Clientes
       (mismo look que Mesa_Prospectos, sin deformarse) -->
  <style>
    :root {
      --mesa-menu-bg: #808b96;
      --mesa-menu-text: #f8f9f9;
    }
    body.mesa-clientes {
      --mesa-topbar-h: calc(var(--topbar-height, 58px) + 16px + var(--safe-t, 0px));
    }
    body.mesa-clientes main.page-content {
      padding-top: var(--mesa-topbar-h);
    }
    body.mesa-clientes .topbar {
      background: var(--mesa-menu-bg);
      border-bottom: 1px solid rgba(255, 255, 255, 0.25);
      color: var(--mesa-menu-text);
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: var(--mesa-topbar-h);
      min-height: 0;
      padding: calc(8px + var(--safe-t, 0px)) 16px 8px;
      box-sizing: border-box;
    }
    body.mesa-clientes .topbar .title,
    body.mesa-clientes .topbar .eyebrow,
    body.mesa-clientes .topbar .topbar-label {
      color: var(--mesa-menu-text);
    }
    body.mesa-clientes .topbar .eyebrow {
      opacity: 0.85;
    }
    body.mesa-clientes .mesa-clientes-scroll {
      height: auto !important;
      max-height: calc(
        100vh - var(--mesa-topbar-h)
        - (var(--bottombar-h, 48px) + var(--safe-b, 0px))
        - 16px
      );
      overflow-x: auto;
      overflow-y: auto;
    }
    body.mesa-clientes .mesa-clientes-scroll thead th {
      position: sticky;
      top: 0;
      background: var(--mesa-menu-bg);
      color: var(--mesa-menu-text);
      z-index: 2;
    }
    /* Contenedor de acciones: distribución tipo grid compacto */
    [data-mesa="clientes"] .mesa-actions {
      display: flex;
      align-items: center;
      justify-content: flex-start;
    }
    [data-mesa="clientes"] .mesa-actions-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      padding: 3px 0;
    }
    [data-mesa="clientes"] .mesa-actions-grid form {
      margin: 0;
      display: inline-flex;
      flex-wrap: wrap;
      gap: 8px;
    }
    [data-mesa="clientes"] .mesa-actions-grid .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 12px;
      box-shadow: 0 8px 18px rgba(15,23,42,.18);
    }
    @media (min-width: 992px){
      [data-mesa="clientes"] .mesa-actions-grid .btn {
        width: 44px;
        height: 44px;
        padding: 0;
      }
      [data-mesa="clientes"] .mesa-actions-grid .btn i.material-icons{
        font-size: 22px;
        line-height: 1;
      }
    }
  </style>
</head>
<body class="mesa-clientes" onload="localize()">
  <!-- =================== Top bar fija Mesa_Clientes.php =================== -->
  <div class="topbar">
    <div class="topbar-left">
      <img src="/login/assets/img/kasu_logo.jpeg" alt="KASU">
      <div>
        <p class="eyebrow mb-0">Mesa</p>
        <h4 class="title">Cartera de Clientes</h4>
      </div>
    </div>
    <div class="topbar-actions"></div>
  </div>

  <!-- =================== Menú inferior =================== -->
  <section id="Menu">
    <?php require_once 'html/Menuprinc.php'; ?>
  </section>

  <!-- =================== Ventanas emergentes =================== -->
  <section id="VentanasEMergentes">
    <!-- Ventana1: Agregar pago -->
    <div class="modal fade" id="Ventana1" tabindex="-1" role="dialog" aria-labelledby="modalV1" aria-hidden="true">
      <div class="modal-dialog" role="document"><div class="modal-content">
        <?php require 'html/Emergente_Registrar_Pago.php'; ?>
      </div></div>
    </div>

    <!-- Ventana2: Promesa de pago -->
    <div class="modal fade" id="Ventana2" tabindex="-1" role="dialog" aria-labelledby="modalV2" aria-hidden="true">
      <div class="modal-dialog" role="document"><div class="modal-content">
        <?php require 'html/Emergente_Promesa_Pago.php'; ?>
      </div></div>
    </div>

    <!-- Ventana3: Reasignar ejecutivo -->
    <div class="modal fade" id="Ventana3" tabindex="-1" role="dialog" aria-labelledby="modalV3" aria-hidden="true">
      <div class="modal-dialog" role="document"><div class="modal-content">
        <form method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>">
          <div class="modal-header">
            <h5 class="modal-title" id="modalV3">Reasignar cliente</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="IdVenta" value="<?= (int)($Reg['Id'] ?? 0) ?>">
            <input type="hidden" name="nombre"  value="<?= h($nombreFiltro) ?>">
            <input type="hidden" name="Status"  value="<?= h($statusFiltro) ?>">

            <p>Nombre del Cliente</p>
            <h4 class="text-center"><strong><?= h($Reg['Nombre'] ?? '') ?></strong></h4>
            <p>Este cliente está asignado a</p>
            <h4 class="text-center">
              <strong>
                <?php
                  $Niv = (int)$basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
                  if (($Reg['Usuario'] ?? '') === "SISTEMA") {
                    echo "SISTEMA";
                    $Niv = 4;
                    $sql = "SELECT * FROM Empleados WHERE Nivel >= {$Niv} AND Nombre != 'Vacante'";
                  } elseif ($Niv === 1) {
                    $UsrPro = $basicas->BuscarCampos($mysqli, "Nombre", "Empleados", "IdUsuario", $Reg['Usuario'] ?? '');
                    echo $UsrPro ?: "Sin Asignar";
                    $sql = "SELECT * FROM Empleados WHERE Nombre != 'Vacante'";
                  } else {
                    echo h($basicas->BuscarCampos($mysqli, "Nombre", "Empleados", "IdUsuario", $Reg['Usuario'] ?? ''));
                    $Suc = (int)$basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "IdUsuario", $Reg['Usuario'] ?? '');
                    $Niv = (int)$basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $Reg['Usuario'] ?? '');
                    $sql = "SELECT * FROM Empleados WHERE Nivel >= {$Niv} AND Nombre != 'Vacante' AND Sucursal = {$Suc}";
                  }
                ?>
              </strong>
            </h4>

            <label>Selecciona el nuevo Ejecutivo</label>
            <select class="form-control" name="NvoVend" required>
              <?php
                if (!empty($sql) && ($S62 = $mysqli->query($sql))) {
                  while ($S63 = $S62->fetch_assoc()) {
                    $nivNom = $basicas->BuscarCampos($mysqli, "NombreNivel", "Nivel", "Id", $S63['Nivel']);
                    $sucNom = $basicas->BuscarCampos($mysqli, "nombreSucursal", "Sucursal", "Id", $S63['Sucursal']);
                    echo '<option value="'.h($S63['IdUsuario']).'">'.
                          h($nivNom).' - '.h($sucNom).' - '.h($S63['Nombre']).
                         '</option>';
                  }
                }
              ?>
            </select>
            <br>
          </div>
          <div class="modal-footer">
            <input type="submit" name="CambiVend" class="btn btn-primary" value="Cambiar el ejecutivo">
          </div>
        </form>
      </div></div>
    </div>

    <!-- Ventana4: Cambiar datos -->
    <div class="modal fade" id="Ventana4" tabindex="-1" role="dialog" aria-labelledby="modalV4" aria-hidden="true">
      <div class="modal-dialog" role="document"><div class="modal-content">
        <?php require 'html/ActualizarDatos.php'; ?>
      </div></div>
    </div>

    <!-- Ventana5: Servicio funerario -->
    <div class="modal fade" id="Ventana5" tabindex="-1" role="dialog" aria-labelledby="modalV5" aria-hidden="true">
      <div class="modal-dialog" role="document"><div class="modal-content">
        <form method="POST" action="php/Funcionalidad_Pwa.php">
          <div class="modal-header">
            <h5 class="modal-title" id="modalV5">Servicio Funerario</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            <div id="Gps"></div>
            <div data-fingerprint-slot></div>
            <input type="hidden" name="nombre"    value="<?= h($nombreFiltro) ?>">
            <input type="hidden" name="Status"    value="<?= h($statusFiltro) ?>">
            <input type="hidden" name="Host"      value="<?= h($_SERVER['PHP_SELF']) ?>">
            <input type="hidden" name="IdVenta"   value="<?= (int)($Reg['Id'] ?? 0) ?>">
            <input type="hidden" name="IdContact" value="<?= (int)($Recg['id'] ?? 0) ?>">
            <input type="hidden" name="IdUsuario" value="<?= (int)($Recg1['id'] ?? 0) ?>">
            <input type="hidden" name="Producto"  value="<?= h($Reg['Producto'] ?? '') ?>">

            <p>Nombre del Cliente:</p>
            <h4 class="text-center"><strong><?= h($Reg['Nombre'] ?? '') ?></strong></h4>
            <p>Tipo de servicio Contratado:</p>
            <h4 class="text-center"><strong><?= h($Reg['Producto'] ?? '') ?> años</strong></h4>

            <p>Registra los datos del Servicio Funerario:</p>
            <div class="vstack gap-3">
              <input type="text" class="form-control" name="EmpFune" placeholder="Empleado funerario que atendió el servicio" required>
              <br>
              <input type="text" class="form-control" name="Prestador" placeholder="Funeraria que realizó el Servicio" required>
              <br>
              <div class="row g-3">
                <div class="col-6 col-md-8"><input type="text" class="form-control" name="RFC" placeholder="RFC Funeraria" required></div>
                <div class="col-6 col-md-4"><input type="number" class="form-control" name="CodigoPostal" placeholder="Código Postal" required></div>
              </div>
              <br>
              <input type="text" class="form-control" name="Firma" placeholder="Folio del CFDI" required>
              <br>
              <input type="number" class="form-control" name="Costo" placeholder="Costo del Servicio" required>
            </div>
            <br>
          </div>
          <div class="modal-footer">
            <input type="submit" name="RegisFun" class="btn btn-dark" value="Servicio Realizado">
          </div>
        </form>
      </div></div>
    </div>

    <!-- Ventana6: Cancelar venta -->
    <div class="modal fade" id="Ventana6" tabindex="-1" role="dialog" aria-labelledby="modalV6" aria-hidden="true">
      <div class="modal-dialog" role="document"><div class="modal-content">
        <?php require 'html/CancelUsr.php'; ?>
      </div></div>
    </div>

    <!-- Ventana7: Enviar fichas / liga de pago -->
    <div class="modal fade" id="Ventana7" tabindex="-1" role="dialog" aria-labelledby="modalV7" aria-hidden="true">
      <div class="modal-dialog" role="document"><div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalV7"><?= h($Reg['Nombre'] ?? '') ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <?php
          $emailDestino    = trim((string)($Recg['Mail'] ?? ''));
          $telefonoDestino = trim((string)($Recg['Celular'] ?? ($Recg['Telefono'] ?? '')));
          $textoEnvioCorreo = $RegEnviarLigaPago
            ? 'la liga de pago de Mercado Pago'
            : 'las fichas de pago en PDF';
          $textoEnvioSms = $RegEnviarLigaPago
            ? 'la misma liga de Mercado Pago'
            : 'el acceso a las fichas de pago';
        ?>
        <div class="modal-body">
          <p class="mb-3 font-weight-bold">
            <?= $RegEnviarLigaPago
              ? 'Enviar la liga de Mercado Pago al correo electrónico del cliente o preparar el SMS.'
              : 'Enviar las fichas de pago al correo electrónico del cliente o preparar el SMS.' ?>
          </p>

          <div class="card mb-3 shadow-sm">
            <div class="card-body">
              <h6 class="text-muted text-uppercase mb-2">Correo electrónico</h6>
              <p class="mb-3">
                <?php if ($emailDestino !== ''): ?>
                  Se enviará <?= h($textoEnvioCorreo) ?> a <strong><?= h($emailDestino) ?></strong>.
                <?php else: ?>
                  Este cliente no cuenta con un email registrado.
                <?php endif; ?>
              </p>
              <?php if ($emailDestino !== ''): ?>
                <form action="../eia/EnviarCorreo.php" method="post" class="d-inline">
                  <div id="Gps"></div>
                  <div data-fingerprint-slot></div>
                  <input type="hidden" name="nombre"     value="<?= h($nombreFiltro) ?>">
                  <input type="hidden" name="Status"     value="<?= h($statusFiltro) ?>">
                  <input type="hidden" name="Host"       value="<?= h($_SERVER['PHP_SELF']) ?>">
                  <input type="hidden" name="IdVenta"    value="<?= (int)($Reg['Id'] ?? 0) ?>">
                  <input type="hidden" name="IdContact"  value="<?= (int)($Recg['id'] ?? 0) ?>">
                  <input type="hidden" name="IdUsuario"  value="<?= (int)($Recg1['id'] ?? 0) ?>">
                  <input type="hidden" name="Producto"   value="<?= h($Reg['Producto'] ?? '') ?>">
                  <input type="hidden" name="Email"      value="<?= h($emailDestino) ?>">
                  <input type="hidden" name="mail_token" value="<?= h($_SESSION['mail_token']) ?>">
                  <?php if ($RegEnviarLigaPago): ?>
                    <input type="hidden" name="EnFi" value="1">
                    <button type="submit" class="btn btn-primary">
                      Enviar liga de Mercado Pago
                    </button>
                  <?php else: ?>
                    <button type="submit" name="EnviarFichas" class="btn btn-primary">
                      Enviar fichas por correo
                    </button>
                  <?php endif; ?>
                </form>
              <?php else: ?>
                <div class="alert alert-danger mb-0">Registra un correo en el contacto para poder enviarlo.</div>
              <?php endif; ?>
            </div>
          </div>

          <div class="card bg-light shadow-sm">
            <div class="card-body">
              <h6 class="text-muted text-uppercase mb-2">SMS</h6>
              <p class="mb-3">
                <?php if ($telefonoDestino !== ''): ?>
                  Mensajes SMS se enviarán al número <strong><?= h($telefonoDestino) ?></strong> con <?= h($textoEnvioSms) ?>.
                <?php else: ?>
                  Este cliente no cuenta con teléfono móvil registrado.
                <?php endif; ?>
              </p>
              <button type="button" class="btn btn-outline-secondary" disabled>
                Envío por SMS disponible próximamente
              </button>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <a
            href="https://kasu.com.mx/login/Generar_PDF/Fichas_Pago_pdf.php?Cte=<?= h(base64_encode((string)($Reg['Id'] ?? 0))) ?>"
            class="btn btn-success"
            download
          >
            Descargar fichas (PDF)
          </a>
        </div>
      </div></div>
    </div>

    <!-- Ventana8: Generar póliza -->
    <div class="modal fade" id="Ventana8" tabindex="-1" role="dialog" aria-labelledby="modalV8" aria-hidden="true">
      <div class="modal-dialog" role="document"><div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalV8"><?= h($Reg['Nombre'] ?? '') ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="IdVenta" value="<?= (int)($Reg['Id'] ?? 0) ?>">
          <input type="hidden" name="nombre"  value="<?= h($nombreFiltro) ?>">
          <input type="hidden" name="Status"  value="<?= h($statusFiltro) ?>">
          <p><strong>Elige una opción para entregar la póliza al cliente</strong></p>
          <?php if (empty($Recg['Mail'])): ?>
            <h5 class="alert alert-danger">Este cliente no cuenta con un Email Registrado</h5>
          <?php endif; ?>
          <br>
        </div>
        <div class="modal-footer">
          <form action="../eia/EnviarCorreo.php" method="post" style="padding-right:5px;">
            <div id="Gps" style="display:none;"></div>
            <div data-fingerprint-slot></div>
            <input type="hidden" name="nombre"     value="<?= h($nombreFiltro) ?>">
            <input type="hidden" name="Status"     value="<?= h($statusFiltro) ?>">
            <input type="hidden" name="Host"       value="<?= h($_SERVER['PHP_SELF']) ?>">
            <input type="hidden" name="IdVenta"    value="<?= (int)($Reg['Id'] ?? 0) ?>">
            <input type="hidden" name="IdContact"  value="<?= (int)($Recg['id'] ?? 0) ?>">
            <input type="hidden" name="IdUsuario"  value="<?= (int)($Recg1['id'] ?? 0) ?>">
            <input type="hidden" name="Producto"   value="<?= h($Reg['Producto'] ?? '') ?>">
            <input type="hidden" name="FullName"   value="<?= h($Reg['Nombre'] ?? '') ?>">
            <input type="hidden" name="Email"      value="<?= h($Recg['Mail'] ?? '') ?>">
            <input type="hidden" name="mail_token" value="<?= h($_SESSION['mail_token']) ?>">
            <?php if (!empty($Recg['Mail'])): ?>
              <input type="submit" name="EnviarPoliza" class="btn btn-secondary" value="Enviar por Email">
            <?php endif; ?>
          </form>
          <a href="https://kasu.com.mx/login/Generar_PDF/Poliza_pdf.php?busqueda=<?= h(base64_encode((string)($Recg['id'] ?? 0))) ?>" class="btn btn-success" download>Descargar</a>
        </div>
      </div></div>
    </div>

    <!-- Ventana9: Ticket de atención -->
    <div class="modal fade" id="Ventana9" tabindex="-1" role="dialog" aria-labelledby="modalV9" aria-hidden="true">
      <div class="modal-dialog" role="document"><div class="modal-content">
        <form method="POST" action="php/Funcionalidad_Pwa.php">
          <div id="Gps"></div>
          <div data-fingerprint-slot></div>
          <input type="hidden" name="nombre"    value="<?= h($nombreFiltro) ?>">
          <input type="hidden" name="Status"    value="<?= h($statusFiltro) ?>">
          <input type="hidden" name="Host"      value="<?= h($_SERVER['PHP_SELF']) ?>">
          <input type="hidden" name="IdVenta"   value="<?= (int)($Reg['Id'] ?? 0) ?>">
          <input type="hidden" name="IdContact" value="<?= (int)($Recg['id'] ?? 0) ?>">
          <input type="hidden" name="IdUsuario" value="<?= (int)($Recg1['id'] ?? 0) ?>">
          <input type="hidden" name="Producto"  value="<?= h($Reg['Producto'] ?? '') ?>">

          <div class="modal-header">
            <h5 class="modal-title" id="modalV9">Ticket de <?= h($Reg['Nombre'] ?? '') ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="Descripcion">Descripción</label>
              <textarea class="form-control" id="Descripcion" name="Descripcion" rows="3" required></textarea>
            </div>
            <label for="Status" class="form-label">Estado</label>
            <select id="Status" name="StatusTicket" class="form-control" required>
              <option value="">Selecciona estado</option>
              <option value="Abierto">Abierto</option>
              <option value="En progreso">En progreso</option>
              <option value="En espera">En espera</option>
              <option value="Resuelto">Resuelto</option>
              <option value="Cerrado">Cerrado</option>
            </select>
            <label for="Prioridad" class="form-label mt-3">Prioridad</label>
            <select id="Prioridad" name="Prioridad" class="form-control" required>
              <option value="">Selecciona prioridad</option>
              <option value="Baja">Baja - 72 h</option>
              <option value="Media">Media - 48 h</option>
              <option value="Alta">Alta - 24 h</option>
              <option value="Crítica">Crítica - 4 h</option>
            </select>
            <label for="Telefono" class="form-label mt-3">Teléfono adicional</label>
            <input type="text" class="form-control" id="Telefono" value="<?= h($Recg['Telefono'] ?? '') ?>" name="Telefono" required>
            <br>
          </div>
          <div class="modal-footer">
            <input type="submit" name="AltaTicket" class="btn btn-success" value="Levantar ticket">
          </div>
        </form>
      </div></div>
    </div>

    <!-- Ventana10: Nuevo Cliente -->
    <div class="modal fade" id="Ventana10" tabindex="-1" role="dialog" aria-labelledby="modalV10" aria-hidden="true">
      <div class="modal-dialog" role="document"><div class="modal-content">
        <?php require 'html/NvoCliente.php'; ?>
      </div></div>
    </div>
  </section>

  <!-- =================== Contenido =================== -->
  <main class="page-content" name="impresion de datos finales">
    <div class="table-responsive mesa-table-wrapper mesa-clientes-scroll">
      <table class="table mesa-table" data-mesa="clientes">
        <thead>
          <tr>
            <th>Nombre Cliente</th>
            <th>Asignado</th>
            <th>Status</th>
            <th>Día pago</th>
            <th>Producto</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php
        // ================== Filtro principal ==================
        $statusReq = trim($statusFiltro);

        if ($statusReq !== '') {
          if ($statusReq === 'ATRASADO') {
            $buscar = [];
            $sqlAtr = "
              SELECT *
              FROM Venta
              WHERE NumeroPagos > 1
                AND Status IN ('ACTIVO','COBRANZA','PREVENTA','ACTIVACION')
            ";
            if ($resAtr = $mysqli->query($sqlAtr)) {
              while ($r = $resAtr->fetch_assoc()) {
                try {
                  $infoEstado = $financieras->estado_mora_corriente((int)$r['Id']);
                } catch (\Throwable $e) {
                  $infoEstado = [];
                }
                $estadoCred = isset($infoEstado['estado']) ? strtoupper((string)$infoEstado['estado']) : '';
                if ($estadoCred !== '' && $estadoCred !== 'AL CORRIENTE') {
                  $buscar[] = $r;
                }
              }
              $resAtr->close();
            }
          } else {
            $buscar = $basicas->BLikes($mysqli, "Venta", "Status", $statusReq);
          }
        } elseif ($nombreFiltro !== '') {
          $buscar = $basicas->BLikes($mysqli, "Venta", "Nombre", $nombreFiltro);
        } else {
          $buscar = [];
        }

        foreach ($buscar as $row):
          $usuarioFila = strtoupper(trim((string)($row['Usuario'] ?? '')));
          if ($kasuScopeSet !== null && !isset($kasuScopeSet[$usuarioFila])) {
            continue;
          }
          $estadoLinea = [];
          try {
            $estadoLinea = $financieras->estado_mora_corriente((int)$row['Id']);
          } catch (\Throwable $e) {
            $estadoLinea = [];
          }

          $estatusBD      = (string)$row['Status'];
          $estatusVisual  = $estatusBD;
          $estadoCredito  = isset($estadoLinea['estado']) ? (string)$estadoLinea['estado'] : '';

          $esCredito = ((int)($row['NumeroPagos'] ?? 1) > 1);
          if ($esCredito
              && in_array($estatusBD, ['ACTIVO','COBRANZA','PREVENTA','ACTIVACION'], true)
              && $estadoCredito !== ''
              && strtoupper($estadoCredito) !== 'AL CORRIENTE') {
            $estatusVisual = 'ATRASADO';
          }

          $diaPago  = (int)($row['DiaPago'] ?? 0);
          $rowClass = ($estatusVisual === 'ATRASADO') ? 'table-warning' : '';
        ?>
          <tr class="<?= h($rowClass) ?>">
            <td><?= h($row['Nombre']) ?></td>
            <td><?= h($row['Usuario']) ?></td>
            <td><?= mesa_status_chip($estatusVisual) ?></td>
            <td><?= $diaPago > 0 ? h((string)$diaPago) : '-' ?></td>
            <td><?= h($row['Producto']) ?></td>
            <td class="mesa-actions" data-label="Acciones">
              <div class="mesa-actions-grid">

                <?php if ($esCredito): ?>
                  <?php
                    $usuarioVentaRow   = strtoupper(trim((string)($row['Usuario'] ?? '')));
                    $enviarLigaPagoRow = ($usuarioVentaRow === 'PLATAFORMA');
                    $btnTitlePago      = $enviarLigaPagoRow
                      ? 'Enviar liga de pago (Mercado Pago)'
                      : 'Enviar fichas de pago';
                    $btnIconPago       = $enviarLigaPagoRow ? 'credit_card' : 'email';
                    $btnColorPago      = $enviarLigaPagoRow ? '#1ABC9C' : '#3498DB';
                  ?>
                  <form method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>">
                    <input type="hidden" name="nombre" value="<?= h($nombreFiltro) ?>">
                    <input type="hidden" name="Status" value="<?= h($statusFiltro) ?>">
                    <label
                      for="PF<?= (int)$row['Id'] ?>"
                      title="<?= h($btnTitlePago) ?>"
                      class="btn"
                      style="background:<?= h($btnColorPago) ?>;color:#F8F9F9;"
                    >
                      <i class="material-icons"><?= h($btnIconPago) ?></i>
                    </label>
                    <input id="PF<?= (int)$row['Id'] ?>" type="submit" name="IdCliente" value="7<?= (int)$row['Id'] ?>" hidden>
                  </form>
                <?php endif; ?>

                <form method="POST" action="Mesa_Estado_Cuenta.php">
                  <input type="hidden" name="nombre" value="<?= h($nombreFiltro) ?>">
                  <input type="hidden" name="Status" value="<?= h($statusFiltro) ?>">
                  <?php if (in_array($row['Status'], ["ACTIVO","COBRANZA","CANCELADO"], true)): ?>
                    <label for="EC<?= (int)$row['Id'] ?>" title="Ver estado de cuenta" class="btn" style="background:#F7DC6F;color:#F8F9F9;">
                      <i class="material-icons">contact_page</i>
                    </label>
                    <input id="EC<?= (int)$row['Id'] ?>" type="submit" name="enviar" value="<?= (int)$row['Id'] ?>" hidden>
                    <input type="hidden" name="busqueda" value="<?= (int)$row['Id'] ?>">
                  <?php endif; ?>
                </form>

                <form method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>">
                  <input type="hidden" name="nombre" value="<?= h($nombreFiltro) ?>">
                  <input type="hidden" name="Status" value="<?= h($statusFiltro) ?>">

                  <?php if ($row['Status'] === "COBRANZA"): ?>
                    <label for="F<?= (int)$row['Id'] ?>" title="Generar Fichas" class="btn" style="background:#EB984E;color:#F8F9F9;">
                      <i class="material-icons">send_to_mobile</i>
                    </label>
                    <input id="F<?= (int)$row['Id'] ?>" type="submit" name="IdCliente" value="7<?= (int)$row['Id'] ?>" hidden>

                    <label for="P<?= (int)$row['Id'] ?>" title="Agregar un pago" class="btn" style="background:#58D68D;color:#F8F9F9;">
                      <i class="material-icons">attach_money</i>
                    </label>
                    <input id="P<?= (int)$row['Id'] ?>" type="submit" name="IdCliente" value="1<?= (int)$row['Id'] ?>" hidden>

                    <label for="PP<?= (int)$row['Id'] ?>" title="Promesa de pago" class="btn" style="background:#85C1E9;color:#F8F9F9;">
                      <i class="material-icons">event</i>
                    </label>
                    <input id="PP<?= (int)$row['Id'] ?>" type="submit" name="IdCliente" value="2<?= (int)$row['Id'] ?>" hidden>

                    <label for="R<?= (int)$row['Id'] ?>" title="Reasignar ejecutivo" class="btn" style="background:#AF7AC5;color:#F8F9F9;">
                      <i class="material-icons">people_alt</i>
                    </label>
                    <input id="R<?= (int)$row['Id'] ?>" type="submit" name="IdCliente" value="3<?= (int)$row['Id'] ?>" hidden>
                  <?php endif; ?>

                  <?php if ($row['Status'] === "CANCELADO"): ?>
                    <label for="P2<?= (int)$row['Id'] ?>" title="Agregar un pago" class="btn" style="background:#58D68D;color:#F8F9F9;">
                      <i class="material-icons">attach_money</i>
                    </label>
                    <input id="P2<?= (int)$row['Id'] ?>" type="submit" name="IdCliente" value="1<?= (int)$row['Id'] ?>" hidden>
                  <?php endif; ?>

                  <?php if (!in_array($row['Status'], ["CANCELADO","ACTIVO","FALLECIDO"], true)): ?>
                    <label for="C<?= (int)$row['Id'] ?>" title="Cancelar venta" class="btn" style="background:#E74C3C;color:#F8F9F9;">
                      <i class="material-icons">cancel</i>
                    </label>
                    <input id="C<?= (int)$row['Id'] ?>" type="submit" name="IdCliente" value="6<?= (int)$row['Id'] ?>" hidden>
                  <?php endif; ?>

                  <?php if ($row['Status'] !== "FALLECIDO"): ?>
                    <label for="D<?= (int)$row['Id'] ?>" title="Cambiar datos" class="btn" style="background:#AAB7B8;color:#F8F9F9;">
                      <i class="material-icons">badge</i>
                    </label>
                    <input id="D<?= (int)$row['Id'] ?>" type="submit" name="IdCliente" value="4<?= (int)$row['Id'] ?>" hidden>

                    <label for="T<?= (int)$row['Id'] ?>" title="Ticket atención" class="btn" style="background:#F39C12;color:#F8F9F9;">
                      <i class="material-icons">phone_locked</i>
                    </label>
                    <input id="T<?= (int)$row['Id'] ?>" type="submit" name="IdCliente" value="9<?= (int)$row['Id'] ?>" hidden>
                  <?php endif; ?>

                  <?php if ($row['Status'] === "ACTIVO"): ?>
                    <label for="G<?= (int)$row['Id'] ?>" title="Generar Póliza" class="btn" style="background:#5DADE2;color:#F8F9F9;">
                      <i class="material-icons">feed</i>
                    </label>
                    <input id="G<?= (int)$row['Id'] ?>" type="submit" name="IdCliente" value="8<?= (int)$row['Id'] ?>" hidden>

                    <label for="S<?= (int)$row['Id'] ?>" title="Asignar Servicio" class="btn" style="background:#273746;color:#F8F9F9;">
                      <i class="material-icons">account_balance</i>
                    </label>
                    <input id="S<?= (int)$row['Id'] ?>" type="submit" name="IdCliente" value="5<?= (int)$row['Id'] ?>" hidden>
                  <?php endif; ?>

                  <?php if ($row['Status'] === "ACTIVACION"): ?>
                    <label for="G2<?= (int)$row['Id'] ?>" title="Generar Póliza" class="btn" style="background:#5DADE2;color:#F8F9F9;">
                      <i class="material-icons">feed</i>
                    </label>
                    <input id="G2<?= (int)$row['Id'] ?>" type="submit" name="IdCliente" value="8<?= (int)$row['Id'] ?>" hidden>
                  <?php endif; ?>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <br><br><br><br>
  </main>

  <!-- =================== JS =================== -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
  <script src="https://www.gstatic.com/charts/loader.js"></script>
  <script src="Javascript/finger.js?v=3"></script>
  <script src="Javascript/localize.js?v=3"></script>
  <script src="Javascript/Inyectar_gps_form.js"></script>

  <!-- =================== Abrir modal si corresponde =================== -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      <?php if (!empty($Lanzar)): ?>
        $('<?= h($Lanzar) ?>').modal('show');
      <?php endif; ?>
    });
  </script>
</body>
</html>
