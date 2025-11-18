<?php
/********************************************************************************************
 * Qué hace: Panel "Colaboradores" para gestión de empleados: pago de comisiones, reasignación,
 *           alta, baja y cambio de nivel. Compatible con PHP 8.2, sanitiza salidas y usa
 *           consultas preparadas cuando aplica.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

// =================== Sesión y dependencias ===================
// Qué hace: Inicia sesión, carga librerías, activa excepciones de mysqli
// Fecha: 05/11/2025 | Revisado por: JCCM
require_once dirname(__DIR__) . '/eia/session.php';
kasu_session_start();
require_once __DIR__ . '/../eia/librerias.php';
require_once __DIR__ . '/php/mesa_helpers.php';
date_default_timezone_set('America/Mexico_City');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header_remove('X-Powered-By');

// =================== Guardia de sesión ===================
// Qué hace: Valida autenticación
// Fecha: 05/11/2025 | Revisado por: JCCM
if (empty($_SESSION['Vendedor'])) {
  header('Location: https://kasu.com.mx/login');
  exit;
}

// =================== Utilidad: escape HTML ===================
// Qué hace: h() convierte a string y escapa contenido para HTML
// Fecha: 05/11/2025 | Revisado por: JCCM
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// =================== Fechas de periodo ===================
// Qué hace: Define rango de fechas del mes en curso y sus formas de despliegue
// Fecha: 05/11/2025 | Revisado por: JCCM
$FechIni_str = date('d-m-Y', strtotime('first day of this month'));
$FechFin_str = date('d-m-Y');
$FechIni = date('Y-m-d', strtotime($FechIni_str)); // ISO para SQL
$FechFin = date('Y-m-d', strtotime($FechFin_str));

// =================== Contexto de usuario activo ===================
// Qué hace: Obtiene nombre para búsquedas y nivel/ID del empleado logueado
// Fecha: 05/11/2025 | Revisado por: JCCM
$name  = $_POST['nombre'] ?? ($_GET['name'] ?? '');
$Vende = $basicas->BuscarCampos($mysqli,'Id','Empleados','IdUsuario',$_SESSION['Vendedor']);
$Nivel = $basicas->BuscarCampos($mysqli,'Nivel','Empleados','IdUsuario',$_SESSION['Vendedor']);

// =================== Selector de ventana (IdEmpleado = {Vtn}{Id}) ===================
// Qué hace: Interpreta parámetro disparador y precarga $Reg del empleado si aplica
// Fecha: 05/11/2025 | Revisado por: JCCM
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

// =================== Datos auxiliares de empleado seleccionado ===================
// Qué hace: Resuelve email, cuenta, pagos del periodo y referencia de depósito
// Fecha: 05/11/2025 | Revisado por: JCCM
$Email = '';
$Cuenta = '';
$RefDepo = '';
if ($Reg) {
  $Email   = $basicas->BuscarCampos($mysqli,'Mail','Contacto','Id',$Reg['IdContacto'] ?? 0);
  $PagosPerio = $basicas->SumarFechas(
    $mysqli,'Cantidad','Comisiones_pagos','IdVendedor',$Reg['IdUsuario'] ?? 0,
    'fechaRegistro',$FechFin,'fechaRegistro',$FechIni
  );
  $Cuenta  = (string)($Reg['Cuenta'] ?? '');
  $RefDepo = hash('adler32', $FechFin_str.'-'.($Reg['IdUsuario'] ?? '').'-'.rand(1,9));
}

// =================== Acciones POST simples ===================
// Qué hace: Cambia nivel del empleado si se envía el formulario correspondiente
// Fecha: 05/11/2025 | Revisado por: JCCM
if (!empty($_POST['CambiNivl']) && !empty($_POST['NvoNivel']) && !empty($_POST['IdEmpleado'])) {
  $basicas->ActCampo($mysqli,'Empleados','Nivel',(int)$_POST['NvoNivel'],(int)$_POST['IdEmpleado']);
}

// =================== Redirección a contrato PDF ===================
// Qué hace: Redirige a generador de contrato cuando viene ?Add=
// Fecha: 05/11/2025 | Revisado por: JCCM
if (!empty($_GET['Add'])) {
  header('Location: https://kasu.com.mx/login/Generar_PDF/Contrato_Ejecutivo_pdf.php?Add='.rawurlencode($_GET['Add']));
  exit;
}

// =================== Alertas de mensajes recibidos ===================
// Qué hace: Muestra alert JS con mensajes por GET ?Msg=
// Fecha: 05/11/2025 | Revisado por: JCCM
if(isset($_GET['Msg'])){
    echo "<script>alert('".h($_GET['Msg'])."');</script>";
}

// =================== Cache bust ===================
// Qué hace: Versión de recursos estáticos
// Fecha: 05/11/2025 | Revisado por: JCCM
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

  <!-- =================== PWA / iOS ===================
       Qué hace: Recursos para instalación como app
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- =================== CSS ===================
       Qué hace: Estilos base de la PWA
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= h((string)$VerCache) ?>">
</head>
<body onload="localize()"> 
  <!-- =================== Top bar fija ===================
       Qué hace: Encabezado y botón para crear empleado
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <div class="topbar">
    <div class="d-flex align-items-center w-100">
      <h4 class="title">Colaboradores de la empresa</h4>

      <!-- botón crear empleado -->
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

  <!-- =================== Menú inferior ===================
       Qué hace: Navegación rápida
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <!-- Menú inferior fijo -->
  <section id="Menu">
    <?php require_once __DIR__ . '/html/Menuprinc.php'; ?>
  </section>

  <!-- =================== Ventanas emergentes ===================
       Qué hace: Modales para pago, reasignación, alta, baja y cambio de nivel
       Fecha: 05/11/2025 | Revisado por: JCCM -->
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

    <!-- Ventana3: Reasignar ejecutivo (vista externa) -->
    <div class="modal fade" id="Ventana3" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <?php require 'html/ReasignarEjecutivo.php'; ?>
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

              <label>Ingresa la dirección</label>
              <div class="mb-2">
                <input class="form-control mb-2" type="number" name="codigo_postal" placeholder="Código Postal" inputmode="numeric" pattern="^\d{5}$">
                <div class="row mb-2">
                  <div class="col-6"><input class="form-control" type="text" name="calle" placeholder="Nombre de la Calle"></div>
                  <div class="col-6"><input class="form-control" type="number" name="numero" placeholder="Número"></div>
                </div>
                <input class="form-control mb-2" type="text" name="colonia" placeholder="Colonia / Localidad">
                <div class="row mb-2">
                  <div class="col-6"><input class="form-control" type="text" name="municipio" placeholder="Municipio"></div>
                  <div class="col-6">
                    <select class="form-control" name="estado" id="estado" required>
                      <option value="">Selecciona un estado</option>
                      <option value="Aguascalientes">Aguascalientes</option>
                      <option value="Baja California">Baja California</option>
                      <option value="Baja California Sur">Baja California Sur</option>
                      <option value="Campeche">Campeche</option>
                      <option value="Coahuila">Coahuila</option>
                      <option value="Colima">Colima</option>
                      <option value="Chiapas">Chiapas</option>
                      <option value="Chihuahua">Chihuahua</option>
                      <option value="Ciudad de México">Ciudad de México</option>
                      <option value="Durango">Durango</option>
                      <option value="Guanajuato">Guanajuato</option>
                      <option value="Guerrero">Guerrero</option>
                      <option value="Hidalgo">Hidalgo</option>
                      <option value="Jalisco">Jalisco</option>
                      <option value="Estado de México">Estado de México</option>
                      <option value="Michoacán">Michoacán</option>
                      <option value="Morelos">Morelos</option>
                      <option value="Nayarit">Nayarit</option>
                      <option value="Nuevo León">Nuevo León</option>
                      <option value="Oaxaca">Oaxaca</option>
                      <option value="Puebla">Puebla</option>
                      <option value="Querétaro">Querétaro</option>
                      <option value="Quintana Roo">Quintana Roo</option>
                      <option value="San Luis Potosí">San Luis Potosí</option>
                      <option value="Sinaloa">Sinaloa</option>
                      <option value="Sonora">Sonora</option>
                      <option value="Tabasco">Tabasco</option>
                      <option value="Tamaulipas">Tamaulipas</option>
                      <option value="Tlaxcala">Tlaxcala</option>
                      <option value="Veracruz">Veracruz</option>
                      <option value="Yucatán">Yucatán</option>
                      <option value="Zacatecas">Zacatecas</option>
                    </select>
                  </div>
                </div>
              </div>
              <label class="mt-2">Cuenta Bancaria</label>
              <input class="form-control" type="number" name="Cuenta" required>

              <label class="mt-2">Nómina Quincenal</label>
              <input class="form-control" type="number" name="Nomina" required>

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

  <!-- =================== Contenido principal ===================
       Qué hace: Lista empleados según búsqueda y muestra acciones por fila
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <main class="page-content">
      <?php if (empty($name)): ?>
        <h2><strong>No se ha seleccionado ningún Colaborador</strong></h2>
      <?php endif; ?>

      <div class="table-responsive mesa-table-wrapper">
        <table class="table mesa-table" data-mesa="empleados">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Usuario</th>
              <th>Líder</th>
              <th>Nivel</th>
              <th>Sucursal</th>
              <th>Com.Vtas</th>
              <th>Com.Distr</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php
          if (!empty($name)) {
            $buscar = $basicas->BLikes($mysqli,'Empleados','Nombre',$name);
            foreach ($buscar as $row) {
              if (trim((string)$row['Nombre']) === 'Vacante') { continue; }

              // ===== Cálculos de comisiones (acumuladas menos pagos) =====
              $sj1 = (float)$basicas->Sumar1cond($mysqli,'ComVtas','Comisiones','IdVendedor',$row['IdUsuario']);
              $sj2 = (float)$basicas->Sumar1cond($mysqli,'ComCob','Comisiones','IdVendedor',$row['IdUsuario']);
              $tj  = (float)$basicas->Sumar1cond($mysqli,'Cantidad','Comisiones_pagos','IdVendedor',$row['IdUsuario']);
              $Saldo = max(0.0, ($sj1 + $sj2) - $tj);

              // % comisión por nivel
              $NivU = (int)$basicas->BuscarCampos($mysqli,'Nivel','Empleados','IdUsuario',$row['IdUsuario']);
              $PorCom = (float)$basicas->BuscarCampos($mysqli,'N'.$NivU,'Comision','Id',2);
              $as = $PorCom / 100.0;

              // ===== Comisiones generadas hoy por "Tarjeta" sin repetir fingerprint de ingreso =====
              $Ayer = date('Y-m-d', strtotime('-1 day'));
              $IdContacto = (int)$basicas->BuscarCampos($mysqli,'IdContacto','Empleados','IdUsuario',$row['IdUsuario']);
              $IdFing = (int)$basicas->Max2Dat($mysqli,'Id','Eventos','Evento','Ingreso','Contacto',$IdContacto);
              $Fingerprint = $basicas->BuscarCampos($mysqli,'IdFInger','Eventos','Id',$IdFing);

              $sqlEvt = "SELECT * FROM Eventos WHERE Evento='Tarjeta' AND IdFInger<>? AND Usuario=? AND FechaRegistro>=?";
              $stE = $mysqli->prepare($sqlEvt);
              $stE->bind_param('sis',$Fingerprint,$row['IdUsuario'],$FechIni);
              $stE->execute();
              $rE = $stE->get_result();

              $ComGenHoy = 0.0;
              while ($ev = $rE->fetch_assoc()) {
                // Nota: $pros debe estar disponible desde librerías; se respeta la lógica original
                $Prducto = $basicas->Buscar2Campos($pros,'Producto','PostSociales','Id',$ev['Cupon'],'Tipo','Art');
                $ComGen  = (float)$basicas->BuscarCampos($pros,'comision','Productos','Producto',$Prducto);
                $Comis   = $ComGen * $as;

                if ($Prducto === 'Universidad')      { $Comis /= 2500; }
                elseif ($Prducto === 'Retiro')       { $Comis /= 1000; }
                else                                 { $Comis /= 100; }

                $CatLeid = (int)$basicas->Cuenta1Fec1Cond($mysqli,'Eventos','IdFInger',$ev['IdFInger'],'Usuario',$row['IdUsuario'],'FechaRegistro',$Ayer);
                if ($CatLeid === 1) { $ComGenHoy += $Comis; }
              }
              $stE->close();

              $NvoSal = $Saldo + $ComGenHoy;

              $lidUsuario = $basicas->BuscarCampos($mysqli,'IdUsuario','Empleados','Id',$row['Equipo']);
              $nivNombre  = $basicas->BuscarCampos($mysqli,'NombreNivel','Nivel','Id',$row['Nivel']);
              $sucNombre  = $basicas->BuscarCampos($mysqli,'nombreSucursal','Sucursal','Id',$row['Sucursal']);

              $btnId = (int)$row['Id'];
          ?>
            <tr>
              <td data-label="Nombre"><?= h($row['Nombre']) ?></td>
              <td data-label="Usuario"><?= h($row['IdUsuario']) ?></td>
              <td data-label="Líder"><?= h($lidUsuario) ?></td>
              <td data-label="Nivel"><?= h($nivNombre) ?></td>
              <td data-label="Sucursal"><?= h($sucNombre) ?></td>
              <td>$ <?= number_format((float)$Saldo,2) ?></td>
              <td>$ <?= number_format((float)$ComGenHoy,2) ?></td>
              <td class="mesa-actions" data-label="Acciones">
                <div class="mesa-actions-grid">
                  <form method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>">
                    <?php
                    if ((int)$Nivel < 3){
                      echo '
                        <!-- Botón de Pagar Comisiones -->
                        <input type="hidden" name="nombre" value="'.h($name).'">
                        <input type="hidden" name="Saldo" value="'.h((string)$NvoSal).'">
                        <label for="P1'.$btnId.'" class="btn" title="Pagar comisiones" style="background:#58D68D;color:#F8F9F9;">
                          <i class="material-icons">attach_money</i>
                        </label>

                        <!-- Botón de Reasignar superior -->
                        <input id="P1'.$btnId.'" type="submit" name="IdEmpleado" value="1'.$btnId.'" hidden>
                        <label for="R3'.$btnId.'" class="btn" title="Reasignar superior" style="background:#AF7AC5;color:#F8F9F9;">
                          <i class="material-icons">people_alt</i>
                        </label>

                        <!-- Botón de Cambiar de puesto -->
                        <input id="B6'.$btnId.'" type="submit" name="IdEmpleado" value="6'.$btnId.'" hidden>
                        <label for="N7'.$btnId.'" class="btn" title="Cambiar puesto" style="background:#C0392B;color:#F8F9F9;">
                          <i class="material-icons">swap_vert</i>
                        </label>
                      ';
                    }
                    ?>
                    <!-- Botón de Reenviar Contraseña -->
                    <input id="R3<?= $btnId ?>" type="submit" name="IdEmpleado" value="3<?= $btnId ?>" hidden>
                    <label for="C5<?= $btnId ?>" class="btn" title="Reenviar contraseña" style="background:#3498DB;color:#F8F9F9;">
                      <i class="material-icons">outbox</i>
                    </label>
                    <!-- Botón de Dar de Baja -->
                    <input id="C5<?= $btnId ?>" type="submit" name="IdEmpleado" value="5<?= $btnId ?>" hidden>
                    <label for="B6<?= $btnId ?>" class="btn" title="Dar de baja" style="background:#E74C3C;color:#F8F9F9;">
                      <i class="material-icons">cancel</i>
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
          </tbody>
        </table>
      </div>
      <br><br><br><br>
  </main>

  <!-- =================== JS ===================
       Qué hace: Dependencias y scripts de interacción
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
  <script src="Javascript/finger.js?v=3"></script>
  <script src="Javascript/localize.js?v=3"></script>
  <script src="Javascript/Inyectar_gps_form.js"></script>

  <!-- =================== Auto-apertura de modal ===================
       Qué hace: Abre el modal dirigido por $Lanzar si existe
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      <?php if (!empty($Lanzar)): ?>
        $('<?= h($Lanzar) ?>').modal('show');
      <?php endif; ?>
    });
  </script>
</body>
</html>
