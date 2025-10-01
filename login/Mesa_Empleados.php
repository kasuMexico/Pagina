<?php
// Sesión y dependencias
session_start();
require_once '../eia/librerias.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Guardia de sesión
if (empty($_SESSION['Vendedor'])) {
  header('Location: https://kasu.com.mx/login');
  exit;
}

// Utils
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// Fechas de periodo
$FechIni_str = date('d-m-Y', strtotime('first day of this month'));
$FechFin_str = date('d-m-Y');
$FechIni = date('Y-m-d', strtotime($FechIni_str)); // ISO para SQL
$FechFin = date('Y-m-d', strtotime($FechFin_str));

$name = $_POST['nombre'] ?? ($_GET['name'] ?? '');
$Vende = $basicas->BuscarCampos($mysqli,'Id','Empleados','IdUsuario',$_SESSION['Vendedor']);
$Nivel = $basicas->BuscarCampos($mysqli,'Nivel','Empleados','IdUsuario',$_SESSION['Vendedor']);

// Selector de ventana por parámetro IdEmpleado (formato: {Vtn}{Id})
$IdEmpleadoParam = $_POST['IdEmpleado'] ?? ($_GET['IdEmpleado'] ?? null);
$Reg = [];
$Lanzar = '';

if ($IdEmpleadoParam !== null) {
  $Vtn = substr($IdEmpleadoParam, 0, 1);
  $Cte = (int)substr($IdEmpleadoParam, 1); // Id de empleado
  if ($Cte > 0) {
    $stmt = $mysqli->prepare('SELECT * FROM Empleados WHERE Id = ? LIMIT 1');
    $stmt->bind_param('i',$Cte);
    $stmt->execute();
    $Reg = $stmt->get_result()->fetch_assoc() ?: [];
    $stmt->close();
  }
  $Lanzar = '#Ventana'.$Vtn;
}

// Datos auxiliares cuando hay $Reg
$Email = '';
$Cuenta = '';
$RefDepo = '';
if ($Reg) {
  $Email   = $basicas->BuscarCampos($mysqli,'Mail','Contacto','Id',$Reg['IdContacto'] ?? 0);
  $PagosPerio = $basicas->SumarFechas(
    $mysqli,'Cantidad','Comisiones_pagos','IdVendedor',$Reg['IdUsuario'] ?? 0,
    'fechaRegistro',$FechFin,'fechaRegistro',$FechIni
  );
  $Cuenta  = $Reg['Cuenta'] ?? '';
  $RefDepo = hash('adler32', $FechFin_str.'-'.($Reg['IdUsuario'] ?? '').'-'.rand(1,9));
}

// Acciones POST simples
if (!empty($_POST['CambiNivl']) && !empty($_POST['NvoNivel']) && !empty($_POST['IdEmpleado'])) {
  $basicas->ActCampo($mysqli,'Empleados','Nivel',(int)$_POST['NvoNivel'],(int)$_POST['IdEmpleado']);
}

if (!empty($_GET['Add'])) {
  header('Location: https://kasu.com.mx/login/Generar_PDF/Contrato_Ejecutivo_pdf.php?Add='.rawurlencode($_GET['Add']));
  exit;
}

// Alertas de correo
require_once 'php/Selector_Emergentes_Ml.php';

// Cache bust
$VerCache = $VerCache ?? time();
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F2F2F2">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Mesa Prospectos</title>

  <!-- PWA / iOS -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?=h($VerCache)?>">
  <link rel="stylesheet" href="assets/css/Grafica.css">
</head>
<body>
  <!-- Top bar fija -->
  <div class="topbar">
    <div class="d-flex align-items-center w-100">
      <h4 class="title">Colaboradores de la empresa</h4>

      <!-- botón crear prospecto -->
        <div class="p-2">
          <?php $ids = uniqid(); ?>
          <form method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>">
            <input type="hidden" name="nombre" value="<?= h($name) ?>">
            <label for="V4<?= h($ids) ?>" class="btn" title="Crear nuevo Empleado" style="background:#F7DC6F;color:#F8F9F9;">
              <i class="material-icons">person_add</i>
            </label>
            <input id="V4<?= h($ids) ?>" type="submit" name="IdEmpleado" value="4<?= h($ids) ?>" hidden>
          </form>
        </div>
    </div>
  </div>
  <!-- Menú Inferior compacto -->
  <section id="Menu" class="mb-2">
    <div class="MenuPrincipal">
      <a class="BtnMenu" href="Pwa_Principal.php"><img src="assets/img/FlorKasu.png" alt="Inicio"></a>
      <a class="BtnMenu" href="Mesa_Herramientas.php"><img src="assets/img/herramientas.png" style="background:#A9D0F5;" alt="Herramientas"></a>
    </div>
  </section>

  <!-- Ventanas emergentes -->
  <section id="VentanasEMergentes">
    <!-- Ventana1: Pagar comisiones periodo -->
    <div class="modal fade" id="Ventana1" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form method="POST" action="php/Funcionalidad_Empleados.php">
            <div class="modal-header">
              <h5 class="modal-title"><?= h($Reg['Nombre'] ?? 'Colaborador') ?></h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">
              <input type="hidden" name="name" value="<?= h($name) ?>">
              <input type="hidden" name="IdEmpleado" value="<?= h($Reg['IdUsuario'] ?? '') ?>">
              <input type="hidden" name="Banco" value="<?= h($Cuenta) ?>">
              <input type="hidden" name="Referencia" value="<?= h($RefDepo) ?>">

              <p>Comisiones generadas del</p>
              <h2><strong><?= h($FechIni_str) ?></strong> al <strong><?= h($FechFin_str) ?></strong></h2>

              <p>Saldo a pagar</p>
              <h2><strong>$ <?= number_format((float)($_POST['Saldo'] ?? 0), 2) ?></strong></h2>

              <p>Clabe Registrada</p>
              <h2><strong><?= h($Cuenta) ?></strong></h2>

              <p>Referencia del pago</p>
              <h2><strong><?= h($RefDepo) ?></strong></h2>

              <label>Cantidad a pagar</label>
              <input class="form-control" type="number" name="Cantidad" placeholder="Cantidad" min="0" step="0.01" required>
            </div>
            <div class="modal-footer">
              <?php if (isset($_POST['Saldo']) && (float)$_POST['Saldo'] >= 1): ?>
                <input type="submit" name="PagoCom" class="btn btn-primary" value="Pagar Comisiones">
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Ventana3: Reasignar ejecutivo -->
    <div class="modal fade" id="Ventana3" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form method="POST" action="php/Funcionalidad_Empleados.php">
            <div class="modal-header">
              <h5 class="modal-title">Reasignar Colaborador</h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">
              <input type="hidden" name="name" value="<?= h($name) ?>">
              <input type="hidden" name="IdEmpleado" value="<?= h($Reg['Id'] ?? '') ?>">
              <p>Nombre de el Colaborador</p>
              <h4 class="text-center"><strong><?= h($Reg['Nombre'] ?? 'Colaborador') ?></strong></h4>
              <p>Este ejecutivo está asignado a</p>
              <h4 class="text-center">
                <strong>
                  <?php
                    if (empty($Reg['Equipo'])) {
                      echo 'Sistema';
                    } else {
                      $IdLider = $basicas->BuscarCampos($mysqli,'Id','Empleados','Id',$Reg['Equipo']);
                      if ($IdLider) {
                        $rs = $mysqli->query('SELECT * FROM Empleados WHERE Id = '.$IdLider.' LIMIT 1');
                        if ($dis = $rs->fetch_assoc()) {
                          $Sucur = $basicas->BuscarCampos($mysqli,'nombreSucursal','Sucursal','Id',$dis['Sucursal']);
                          $Stats = $basicas->BuscarCampos($mysqli,'NombreNivel','Nivel','Id',$dis['Nivel']);
                          echo h($dis['Nombre'].' - '.$Stats.' - '.$Sucur);
                        }
                      }
                    }
                    $nue = ($Reg['Nivel'] ?? 0) >= 5 ? 4 : max(1, (int)($Reg['Nivel'] ?? 1) - 1);
                  ?>
                </strong>
              </h4>

              <?php if ((int)($Reg['Equipo'] ?? 0) === 0): ?>
                <label>Selecciona la Sucursal a la que se asignará</label>
                <select class="form-control" name="IdSucursal" required>
                  <?php
                    if (!function_exists('h')) {
                      function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
                    }
                    $actual = (int)($Reg['IdSucursal'] ?? 0); // opcional: preselección
                    $stmt = $mysqli->prepare("SELECT IdSucursal, nombreSucursal FROM Sucursal WHERE Estatus=1 ORDER BY nombreSucursal");
                    $stmt->execute();
                    $rs = $stmt->get_result();
                    while ($row = $rs->fetch_assoc()):
                      $id  = (int)$row['IdSucursal'];
                      $nom = $row['nombreSucursal'];
                  ?>
                    <option value="<?= $id ?>" <?= $actual === $id ? 'selected' : '' ?>>
                      <?= h($nom) ?>
                    </option>
                  <?php endwhile; $stmt->close(); ?>
                </select>
                <br>
              <?php endif; ?>

              <label>Selecciona a quién se asignará</label>
              <select class="form-control" name="NvoVend" required>
                <?php
                  $sql9 = "SELECT * FROM Empleados WHERE Nivel = ? AND Nombre != 'Vacante'";
                  $st9 = $mysqli->prepare($sql9);
                  $st9->bind_param('i',$nue);
                  $st9->execute();
                  $S629 = $st9->get_result();
                  while ($S635 = $S629->fetch_assoc()):
                    $Su2cur = $basicas->BuscarCampos($mysqli,'nombreSucursal','Sucursal','Id',$S635['Sucursal']);
                    $St2ats = $basicas->BuscarCampos($mysqli,'NombreNivel','Nivel','Id',$S635['Nivel']);
                ?>
                  <option value="<?= h($S635['Id']) ?>"><?= h($S635['Nombre'].' - '.$St2ats.' - '.$Su2cur) ?></option>
                <?php endwhile; $st9->close(); ?>
              </select>
              <br>
            </div>
            <div class="modal-footer">
              <input type="submit" name="CambiVend" class="btn btn-primary" value="Cambiar el ejecutivo">
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Ventana4: Crear empleado -->
    <div class="modal fade" id="Ventana4" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form method="POST" action="php/Funcionalidad_Empleados.php">
            <div class="modal-header">
              <h5 class="modal-title">Registrar Nuevo Empleado</h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">
              <input type="hidden" name="name" value="<?= h($name) ?>">

              <label>Nombre</label>
              <input class="form-control" type="text" name="Nombre" required>

              <label class="mt-2">Teléfono</label>
              <input class="form-control" type="text" name="Telefono" required>

              <label class="mt-2">Email</label>
              <input class="form-control" type="email" name="Email" required>

              <label class="mt-2">Dirección</label>
              <input class="form-control" type="text" name="Direccion" required>

              <label class="mt-2">Cuenta Bancaria</label>
              <input class="form-control" type="number" name="Cuenta" required>

              <label class="mt-2">Sucursal</label>
              <select class="form-control" name="Sucursal" required>
                <?php
                  $rs1 = $mysqli->query("SELECT * FROM Sucursal WHERE Estatus = 1");
                  while ($s = $rs1->fetch_assoc()):
                ?>
                  <option value="<?= h($s['id']) ?>"><?= h($s['nombreSucursal']) ?></option>
                <?php endwhile; ?>
              </select>

              <label class="mt-2">Puesto</label>
              <select class="form-control" name="Nivel" required>
                <?php
                  $st2 = $mysqli->prepare("SELECT * FROM Nivel WHERE Id >= ?");
                  $st2->bind_param('i',$Nivel);
                  $st2->execute();
                  $rs2 = $st2->get_result();
                  while ($n = $rs2->fetch_assoc()):
                ?>
                  <option value="<?= h($n['Id']) ?>"><?= h($n['NombreNivel']) ?></option>
                <?php endwhile; $st2->close(); ?>
              </select>

              <label class="mt-2">Jefe Directo</label>
              <select class="form-control" name="Lider" required>
                <?php
                  $st3 = $mysqli->prepare("SELECT * FROM Empleados WHERE Nivel >= ? AND Nombre != 'Vacante'");
                  $st3->bind_param('i',$Nivel);
                  $st3->execute();
                  $rs3 = $st3->get_result();
                  while ($e = $rs3->fetch_assoc()):
                    $suc = $basicas->BuscarCampos($mysqli,'nombreSucursal','Sucursal','Id',$e['Sucursal']);
                    $niv = $basicas->BuscarCampos($mysqli,'NombreNivel','Nivel','Id',$e['Nivel']);
                ?>
                  <option value="<?= h($e['Id']) ?>"><?= h($e['Nombre'].' - '.$niv.' - '.$suc) ?></option>
                <?php endwhile; $st3->close(); ?>
              </select>
            </div>
            <div class="modal-footer">
              <input type="submit" name="CreaEmpl" class="btn btn-primary" value="Crear Empleado">
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Ventana5: Reenviar contraseña -->
    <div class="modal fade" id="Ventana5" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form method="POST" action="php/Funcionalidad_Empleados.php">
            <div class="modal-header">
              <h5 class="modal-title">Reenviar contraseña</h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">
              <input type="hidden" name="name" value="<?= h($name) ?>">
              <input type="hidden" name="IdUsuario" value="<?= h($Reg['IdUsuario'] ?? '') ?>">
              <input type="hidden" name="Id" value="<?= h($Reg['Id'] ?? '') ?>">
              <input type="hidden" name="Nombre" value="<?= h($Reg['Nombre'] ?? '') ?>">
              <input type="hidden" name="Email" value="<?= h($Email) ?>">

              <label>Nombre</label>
              <input class="form-control" type="text" value="<?= h($Reg['Nombre'] ?? '') ?>" disabled>

              <label class="mt-2">Email</label>
              <input class="form-control" type="email" value="<?= h($Email) ?>" disabled>
            </div>
            <div class="modal-footer">
              <input type="submit" name="ReenCOntra" class="btn btn-primary" value="Reenviar contraseña">
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Ventana6: Baja -->
    <div class="modal fade" id="Ventana6" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form method="POST" action="php/Funcionalidad_Empleados.php">
            <div class="modal-header">
              <h5 class="modal-title"><?= h($Reg['Nombre'] ?? 'Colaborador') ?></h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">
              <input type="hidden" name="name" value="<?= h($name) ?>">
              <input type="hidden" name="IdEmpleado" value="<?= h($Reg['Id'] ?? '') ?>">

              <label>Selecciona el motivo de la baja</label>
              <select class="form-control" name="MotivoBaja" required>
                <option value="Renuncia">Renuncia</option>
                <option value="Robo">Robo a la empresa</option>
                <option value="Despido">Despido</option>
                <option value="Abandono">Abandono de Trabajo</option>
                <option value="actas">Acumulación de actas administrativas</option>
                <option value="Rendimiento">Bajo Rendimiento</option>
              </select>
            </div>
            <div class="modal-footer">
              <input type="submit" name="BajaEmp" class="btn btn-primary" value="Dar de Baja">
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Ventana7: Cambiar nivel -->
    <div class="modal fade" id="Ventana7" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>">
            <div class="modal-header">
              <h5 class="modal-title"><?= h($Reg['Nombre'] ?? 'Colaborador') ?></h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="nombre" value="<?= h($name) ?>">
              <input type="hidden" name="IdEmpleado" value="<?= h($Reg['Id'] ?? '') ?>">

              <p>Status actual</p>
              <p><strong><?= h($basicas->BuscarCampos($mysqli,'NombreNivel','Nivel','Id',$Reg['Nivel'] ?? 0)) ?></strong></p>

              <label>Selecciona puesto</label>
              <select class="form-control" name="NvoNivel" required>
                <?php
                  $Rivel = $basicas->BuscarCampos($mysqli,'Nivel','Empleados','IdUsuario',$_SESSION['Vendedor']);
                  $stN = $mysqli->prepare('SELECT * FROM Nivel WHERE Id >= ?');
                  $stN->bind_param('i',$Rivel);
                  $stN->execute();
                  $rsN = $stN->get_result();
                  while ($rowN = $rsN->fetch_assoc()):
                ?>
                  <option value="<?= h($rowN['Id']) ?>"><?= h($rowN['NombreNivel']) ?></option>
                <?php endwhile; $stN->close(); ?>
              </select>
              <br>
            </div>
            <div class="modal-footer">
              <input type="submit" name="CambiNivl" class="btn btn-primary" value="Cambiar el ejecutivo">
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Contenido -->
  <main class="page-content">
      <?php if (empty($name)): ?>
        <h2><strong>No se ha seleccionado ningún Colaborador</strong></h2>
      <?php endif; ?>

      <div class="table-responsive">
        <table class="table">
          <tr>
            <th>Nombre Empleado</th>
            <th>Líder</th>
            <th>Nivel</th>
            <th>Sucursal</th>
            <th>Com.Vtas</th>
            <th>Com.Distr</th>
            <th>Acciones</th>
          </tr>
          <?php
          if (!empty($name)) {
            $buscar = $basicas->BLikes($mysqli,'Empleados','Nombre',$name);
            foreach ($buscar as $row) {
              if (trim($row['Nombre']) === 'Vacante') continue;

              // Cálculos de comisiones
              $sj1 = $basicas->Sumar1cond($mysqli,'ComVtas','Comisiones','IdVendedor',$row['IdUsuario']);
              $sj2 = $basicas->Sumar1cond($mysqli,'ComCob','Comisiones','IdVendedor',$row['IdUsuario']);
              $tj  = $basicas->Sumar1cond($mysqli,'Cantidad','Comisiones_pagos','IdVendedor',$row['IdUsuario']);
              $Saldo = max(0, ($sj1 + $sj2) - $tj);

              $NivU = $basicas->BuscarCampos($mysqli,'Nivel','Empleados','IdUsuario',$row['IdUsuario']);
              $PorCom = $basicas->BuscarCampos($mysqli,'N'.$NivU,'Comision','Id',2);
              $as = ((float)$PorCom)/100.0;

              $Ayer = date('Y-m-d', strtotime('-1 day'));
              $IdContacto = $basicas->BuscarCampos($mysqli,'IdContacto','Empleados','IdUsuario',$row['IdUsuario']);
              $IdFing = $basicas->Max2Dat($mysqli,'Id','Eventos','Evento','Ingreso','Contacto',$IdContacto);
              $Fingerprint = $basicas->BuscarCampos($mysqli,'IdFInger','Eventos','Id',$IdFing);

              $sqlEvt = "SELECT * FROM Eventos WHERE Evento='Tarjeta' AND IdFInger<>? AND Usuario=? AND FechaRegistro>=?";
              $stE = $mysqli->prepare($sqlEvt);
              $stE->bind_param('sis',$Fingerprint,$row['IdUsuario'],$FechIni);
              $stE->execute();
              $rE = $stE->get_result();

              $ComGenHoy = 0.0;
              while ($ev = $rE->fetch_assoc()) {
                $Prducto = $basicas->Buscar2Campos($pros,'Producto','PostSociales','Id',$ev['Cupon'],'Tipo','Art');
                $ComGen  = (float)$basicas->BuscarCampos($pros,'comision','Productos','Producto',$Prducto);
                $Comis   = $ComGen * $as;

                if ($Prducto === 'Universidad')      $Comis /= 2500;
                elseif ($Prducto === 'Retiro')       $Comis /= 1000;
                else                                  $Comis /= 100;

                $CatLeid = $basicas->Cuenta1Fec1Cond($mysqli,'Eventos','IdFInger',$ev['IdFInger'],'Usuario',$row['IdUsuario'],'FechaRegistro',$Ayer);
                if ($CatLeid == 1) $ComGenHoy += $Comis;
              }
              $stE->close();

              $NvoSal = $Saldo + $ComGenHoy;

              $lidUsuario = $basicas->BuscarCampos($mysqli,'IdUsuario','Empleados','Id',$row['Equipo']);
              $nivNombre  = $basicas->BuscarCampos($mysqli,'NombreNivel','Nivel','Id',$row['Nivel']);
              $sucNombre  = $basicas->BuscarCampos($mysqli,'nombreSucursal','Sucursal','Id',$row['Sucursal']);

              $btnId = (int)$row['Id'];
          ?>
            <tr>
              <td><?= h($row['Nombre']) ?></td>
              <td><?= h($lidUsuario) ?></td>
              <td><?= h($nivNombre) ?></td>
              <td><?= h($sucNombre) ?></td>
              <td>$ <?= number_format((float)$Saldo,2) ?></td>
              <td>$ <?= number_format((float)$ComGenHoy,2) ?></td>
              <td>
                <div class="d-flex">
                  <form method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>" class="mr-2">
                    <input type="hidden" name="nombre" value="<?= h($name) ?>">
                    <input type="hidden" name="Saldo" value="<?= h($NvoSal) ?>">
                    <label for="P1<?= $btnId ?>" class="btn" title="Pagar comisiones" style="background:#58D68D;color:#F8F9F9;">
                      <i class="material-icons">attach_money</i>
                    </label>
                    <input id="P1<?= $btnId ?>" type="submit" name="IdEmpleado" value="1<?= $btnId ?>" hidden>
                    <label for="R3<?= $btnId ?>" class="btn" title="Reasignar superior" style="background:#AF7AC5;color:#F8F9F9;">
                      <i class="material-icons">people_alt</i>
                    </label>
                    <input id="R3<?= $btnId ?>" type="submit" name="IdEmpleado" value="3<?= $btnId ?>" hidden>
                    <label for="C5<?= $btnId ?>" class="btn" title="Reenviar contraseña" style="background:#3498DB;color:#F8F9F9;">
                      <i class="material-icons">outbox</i>
                    </label>
                    <input id="C5<?= $btnId ?>" type="submit" name="IdEmpleado" value="5<?= $btnId ?>" hidden>
                    <label for="B6<?= $btnId ?>" class="btn" title="Dar de baja" style="background:#E74C3C;color:#F8F9F9;">
                      <i class="material-icons">cancel</i>
                    </label>
                    <input id="B6<?= $btnId ?>" type="submit" name="IdEmpleado" value="6<?= $btnId ?>" hidden>
                    <label for="N7<?= $btnId ?>" class="btn" title="Cambiar puesto" style="background:#C0392B;color:#F8F9F9;">
                      <i class="material-icons">swap_vert</i>
                    </label>
                    <input id="N7<?= $btnId ?>" type="submit" name="IdEmpleado" value="7<?= $btnId ?>" hidden>
                  </form>
                </div>
              </td>
            </tr>
          <?php
            }
          }
          ?>
        </table>
      </div>
      <br><br><br><br>
  </main>

  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
  <script src="Javascript/fingerprint-core-y-utils.js"></script>
  <script src="Javascript/finger.js"></script>
  <script src="Javascript/localize.js"></script>

  <!-- Abrir modal si corresponde -->
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      <?php if (!empty($Lanzar)): ?>
        $('<?= h($Lanzar) ?>').modal('show');
      <?php endif; ?>
    });
  </script>
</body>
</html>
