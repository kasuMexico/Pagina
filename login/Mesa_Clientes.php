<?php
/********************************************************************************************
 * Qué hace: Panel "Cartera de Clientes". Permite consultar clientes/ventas, abrir modales
 *           para pagos, promesas, reasignación, actualización de datos, servicio funerario,
 *           cancelación, generación de fichas y póliza, y tickets de soporte.
 *           Ajustes PHP 8.2: mysqli en modo excepciones, salidas saneadas, tipos explícitos.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

// =================== Sesión y dependencias ===================
// Qué hace: Inicia sesión, carga librerías, fija zona horaria, endurece cabeceras
// Fecha: 05/11/2025 | Revisado por: JCCM
session_start();
require_once '../eia/librerias.php';
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

// =================== Utils ===================
// Qué hace: h() convierte a string y escapa para HTML
// Fecha: 05/11/2025 | Revisado por: JCCM
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// =================== Variables base ===================
// Qué hace: Inicializa estructuras y contexto del usuario actual
// Fecha: 05/11/2025 | Revisado por: JCCM
$Reg = [];
$Recg = [];
$Recg1 = [];
$Pago = $Pago1 = $PagoPend = $Saldo = 0.0;
$Status = '';
$Ventana = null;
$Lanzar  = null;
$nombre  = $_POST['nombre'] ?? ($_GET['nombre'] ?? '');
$Vende   = $basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);

// =================== Router por POST IdCliente ===================
// Qué hace: Interpreta "IdCliente" con patrón {Vtn}{Id} para abrir modal y precargar venta
// Fecha: 05/11/2025 | Revisado por: JCCM
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

      // Estado de mora/corriente
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
    }
  }

  $Ventana = "Ventana{$Vtn}";
  $Lanzar  = "#{$Ventana}";
}

// =================== Acciones POST ===================
// Qué hace: Procesa acciones de reasignación o cancelación
// Fecha: 05/11/2025 | Revisado por: JCCM
if (!empty($_POST['CambiVend'])) {
  // Reasignar ejecutivo en tablas relacionadas
  $idVta   = (int)($_POST['IdVenta'] ?? 0);
  $nvoVend = (string)($_POST['NvoVend'] ?? '');
  if ($idVta > 0 && $nvoVend !== '') {
    $basicas->ActCampo($mysqli, "Venta", "Usuario", $nvoVend, $idVta);
    $basicas->ActTab($mysqli, "PromesaPago", "User", $nvoVend, "IdVta", $idVta);
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
// Qué hace: Lanza alert seguro si viene ?Msg=
// Fecha: 05/11/2025 | Revisado por: JCCM
if (isset($_GET['Msg'])) {
  $msg = (string)$_GET['Msg'];
  echo "<script>window.addEventListener('load',()=>alert(".json_encode($msg, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."));</script>";
}

// =================== Token anti-duplicado correo ===================
// Qué hace: Evita reenvíos múltiples
// Fecha: 05/11/2025 | Revisado por: JCCM
$_SESSION['mail_token'] = bin2hex(random_bytes(16));

// =================== Cache bust ===================
// Qué hace: Versiona recursos estáticos
// Fecha: 05/11/2025 | Revisado por: JCCM
$VerCache = time();
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
       Qué hace: Archivos para instalación en móviles
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- =================== CSS unificado ===================
       Qué hace: Estilos base del panel
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= h((string)$VerCache) ?>">
</head>
<body>
  <!-- =================== Top bar fija ===================
       Qué hace: Título del módulo
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <div class="topbar">
    <div class="d-flex align-items-center w-100">
      <h4 class="title">Cartera de Clientes</h4>
    </div>
  </div>

  <!-- =================== Menú inferior compacto ===================
       Qué hace: Navegación rápida entre módulos
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <section id="Menu" class="mb-2">
    <div class="MenuPrincipal">
      <a class="BtnMenu" href="Pwa_Principal.php"><img src="assets/img/FlorKasu.png" alt="Inicio"></a>
      <a class="BtnMenu" href="Mesa_Herramientas.php"><img src="assets/img/Iconos_menu/ajustes.png" style="background:#A9D0F5;" alt="Herramientas"></a>
    </div>
  </section>

  <!-- =================== Ventanas emergentes ===================
       Qué hace: Modales para acciones sobre el cliente
       Fecha: 05/11/2025 | Revisado por: JCCM -->
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
            <input type="hidden" name="nombre"  value="<?= h($nombre) ?>">
            <input type="hidden" name="Status"  value="<?= h($_POST['Status'] ?? '') ?>">

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
            <input type="hidden" name="nombre"    value="<?= h($nombre) ?>">
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

    <!-- Ventana7: Generar fichas -->
    <div class="modal fade" id="Ventana7" tabindex="-1" role="dialog" aria-labelledby="modalV7" aria-hidden="true">
      <div class="modal-dialog" role="document"><div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalV7"><?= h($Reg['Nombre'] ?? '') ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="IdVenta" value="<?= (int)($Reg['Id'] ?? 0) ?>">
          <input type="hidden" name="nombre"  value="<?= h($nombre) ?>">
          <input type="hidden" name="Status"  value="<?= h($_POST['Status'] ?? '') ?>">
          <p><strong>Elige una opción para entregar las fichas al cliente</strong></p>
          <?php if (empty($Recg['Mail'])): ?>
            <h5 class="alert alert-danger">Este cliente no cuenta con un Email Registrado</h5>
          <?php endif; ?>
          <br>
        </div>
        <div class="modal-footer">
          <form action="../eia/EnviarCorreo.php" method="post" style="padding-right:5px;">
            <div id="Gps"></div>
            <div data-fingerprint-slot></div>
            <input type="hidden" name="nombre"     value="<?= h($nombre) ?>">
            <input type="hidden" name="Host"       value="<?= h($_SERVER['PHP_SELF']) ?>">
            <input type="hidden" name="IdVenta"    value="<?= (int)($Reg['Id'] ?? 0) ?>">
            <input type="hidden" name="IdContact"  value="<?= (int)($Recg['id'] ?? 0) ?>">
            <input type="hidden" name="IdUsuario"  value="<?= (int)($Recg1['id'] ?? 0) ?>">
            <input type="hidden" name="Producto"   value="<?= h($Reg['Producto'] ?? '') ?>">
            <input type="hidden" name="Email"      value="<?= h($Recg['Mail'] ?? '') ?>">
            <input type="hidden" name="mail_token" value="<?= h($_SESSION['mail_token']) ?>">
            <?php if (!empty($Recg['Mail'])): ?>
              <input type="submit" name="EnviarFichas" class="btn btn-secondary" value="Enviar por Email">
            <?php endif; ?>
          </form>
          <a href="https://kasu.com.mx/login/Generar_PDF/Fichas_Pago_pdf.php?Cte=<?= h(base64_encode((string)($Reg['Id'] ?? 0))) ?>" class="btn btn-success" download>Descargar</a>
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
          <input type="hidden" name="nombre"  value="<?= h($nombre) ?>">
          <input type="hidden" name="Status"  value="<?= h($_POST['Status'] ?? '') ?>">
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
            <input type="hidden" name="nombre"     value="<?= h($nombre) ?>">
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
          <input type="hidden" name="nombre"    value="<?= h($nombre) ?>">
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
            <select id="Status" name="Status" class="form-control" required>
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

  <!-- =================== Contenido ===================
       Qué hace: Lista clientes por nombre o status y expone acciones por fila
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <main class="page-content" name="impresion de datos finales">
    <div class="table-responsive">
      <table class="table">
        <tr>
          <th>Nombre Cliente</th>
          <th>Asignado</th>
          <th>Status</th>
          <th>Producto</th>
          <th>Acciones</th>
        </tr>
        <?php
        if (!empty($_POST['Status'])) {
          $buscar = $basicas->BLikes($mysqli, "Venta", "Status", $_POST['Status']);
        } elseif (!empty($nombre)) {
          $buscar = $basicas->BLikes($mysqli, "Venta", "Nombre", $nombre);
        } else {
          $buscar = [];
        }

        foreach ($buscar as $row):
        ?>
          <tr>
            <td><?= h($row['Nombre']) ?></td>
            <td><?= h($row['Usuario']) ?></td>
            <td><?= h($row['Status']) ?></td>
            <td><?= h($row['Producto']) ?></td>
            <td>
              <div class="d-flex">
                <!-- Estado de cuenta -->
                <form method="POST" action="Mesa_Estado_Cuenta.php" class="mr-2">
                  <input type="hidden" name="nombre" value="<?= h($nombre) ?>">
                  <?php if (in_array($row['Status'], ["ACTIVO","COBRANZA","CANCELADO"], true)): ?>
                    <label for="EC<?= (int)$row['Id'] ?>" title="Ver estado de cuenta" class="btn" style="background:#F7DC6F;color:#F8F9F9;">
                      <i class="material-icons">contact_page</i>
                    </label>
                    <input id="EC<?= (int)$row['Id'] ?>" type="submit" name="enviar" value="<?= (int)$row['Id'] ?>" hidden>
                    <input type="hidden" name="busqueda" value="<?= (int)$row['Id'] ?>">
                  <?php endif; ?>
                </form>

                <!-- Acciones por status -->
                <form method="POST" action="<?= h($_SERVER['PHP_SELF']) ?>">
                  <input type="hidden" name="nombre" value="<?= h($nombre) ?>">

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
      </table>
    </div>
    <br><br><br><br>
  </main>

  <!-- =================== JS ===================
       Qué hace: Dependencias y scripts de interacción
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
  <script src="https://www.gstatic.com/charts/loader.js"></script>
  <script src="Javascript/fingerprint-core-y-utils.js"></script>
  <script src="Javascript/finger.js" defer></script>
  <script src="Javascript/localize.js"></script>

  <!-- =================== Abrir modal si corresponde ===================
       Qué hace: Abre el modal indicado por $Lanzar si viene del router
       Fecha: 05/11/2025 | Revisado por: JCCM -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      <?php if (!empty($Lanzar)): ?>
        $('<?= h($Lanzar) ?>').modal('show');
      <?php endif; ?>
    });
  </script>
</body>
</html>
