<?php
// Iniciar sesión
session_start();

// Incluir librerías y clases
require_once '../eia/librerias.php';

// Verificar usuario logueado
if (empty($_SESSION["Vendedor"])) {
    header('Location: https://kasu.com.mx/login');
    exit;
}

// Definición de variables principales
$Reg = [];
$Recg = [];
$Recg1 = [];
$Pago = $Pago1 = $PagoPend = $Saldo = 0.0;
$Status = '';
$Ventana = null;     // e.g., "Ventana1"
$Lanzar  = null;     // e.g., "#Ventana1"

// Procesar POST si se envía un cliente específico
if (!empty($_POST['IdCliente'])) {
    //If que determina si e creara un cliente o se obtiene el valor de los datos seleccionados
    if($_POST['IdCliente'] == "btnCrearCte"){
      $Vtn = 10;
      $Cte = '';
    } else {
      $Vtn = substr($_POST['IdCliente'], 0, 1);
      $Cte = (int)substr($_POST['IdCliente'], 1, 5);
    }

    // Consulta de venta
    $venta = "SELECT * FROM Venta WHERE Id = '".$Cte."'";
    if ($res = mysqli_query($mysqli, $venta)) {
        if ($Reg = mysqli_fetch_assoc($res)) {
            $Pago1    = (float)$financieras->Pago($mysqli, $Cte);
            $Pago     = number_format($Pago1, 2);
            $PagoPend = $financieras->PagosPend($mysqli, $Cte);
            $Saldo    = number_format((float)$financieras->SaldoCredito($mysqli, $Cte), 2);

            // Mora o corriente
            $StatVtas = $financieras->estado_mora_corriente((int)$Reg['Id']);
            $Status   = (!empty($StatVtas['estado']) && $StatVtas['estado'] === "AL CORRIENTE") ? "Pago" : "Mora";

            // Usuario relacionado
            $sql1  = "SELECT * FROM Usuario WHERE IdContact = '".$Reg['IdContact']."'";
            $recs1 = mysqli_query($mysqli, $sql1);
            if ($recs1) { $Recg1 = mysqli_fetch_assoc($recs1) ?: []; }

            // Contacto relacionado
            $sql   = "SELECT * FROM Contacto WHERE id = '".$Reg['IdContact']."'";
            $recs  = mysqli_query($mysqli, $sql);
            if ($recs) { $Recg = mysqli_fetch_assoc($recs) ?: []; }
        }
    }

    $Ventana = "Ventana{$Vtn}";
    $Lanzar  = "#{$Ventana}";
    $Vende   = $basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
} else {
    $Vende = $basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
}

// Procesar cambios de ejecutivo / cancelación
if (!empty($_POST['CambiVend'])) {
    $basicas->ActCampo($mysqli, "Venta", "Usuario", $_POST['NvoVend'], $_POST['IdVenta']);
    $basicas->ActTab($mysqli, "PromesaPago", "User", $_POST['NvoVend'], "IdVta", $_POST['IdVenta']);
    $basicas->ActTab($mysqli, "Pagos", "Usuario", $_POST['NvoVend'], "IdVenta", $_POST['IdVenta']);
} elseif (!empty($_POST['CancelaCte'])) {
    // Auditoría (GPS + fingerprint)
    $ids = $seguridad->auditoria_registrar(
        $mysqli,
        $basicas,
        $_POST,
        'Cancelar_Venta',
        $_POST['Host'] ?? $_SERVER['PHP_SELF']
    );
    $basicas->ActCampo($mysqli, "Venta", "Status", "CANCELADO", $_POST['IdVenta']);
    $_GET['Vt'] = 1;
    $_GET['Msg'] = "Se ha cancelado la Venta";
}

// Captura nombre desde POST o GET
$nombre = $_POST['nombre'] ?? $_GET['nombre'] ?? "";

// Alerts de mensajes recibidos
if(isset($_GET['Msg'])){
    echo "<script>alert('".htmlspecialchars($_GET['Msg'], ENT_QUOTES)."');</script>";
}

// Token anti-duplicado correo
$_SESSION['mail_token'] = bin2hex(random_bytes(16));

// Registro de método para pagos
$Metodo = "Mesa";
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
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?php echo htmlspecialchars($VerCache ?? '1', ENT_QUOTES); ?>">
  <link rel="stylesheet" href="assets/css/Grafica.css">
</head>
<body onload="localize()">

  <!-- Menu principal -->
  <section id="Menu">
    <div class="MenuPrincipal">
      <a class="BtnMenu" href="Pwa_Principal.php"><img src="assets/img/FlorKasu.png" alt="Inicio"></a>
      <a class="BtnMenu" href="Mesa_Herramientas.php"><img src="assets/img/herramientas.png" style="background:#A9D0F5;" alt="Herramientas"></a>
    </div>
  </section>

  <section name="VentanasEMergentes">
    <!-- Ventana1: Agregar pago REVISADO: 26/09/2025 JCCM -->
    <div class="modal fade" id="Ventana1" tabindex="-1" role="dialog" aria-labelledby="modalV1" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <?php require 'html/Emergente_Registrar_Pago.php'; ?>
        </div>
      </div>
    </div>

    <!-- Ventana2: Promesa de pago REVISADO: 26/09/2025 JCCM -->
    <div class="modal fade" id="Ventana2" tabindex="-1" role="dialog" aria-labelledby="modalV2" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <?php require 'html/Emergente_Promesa_Pago.php'; ?>
        </div>
      </div>
    </div>

    <!-- Ventana3: Reasignar ejecutivo REVISADO: 26/09/2025 JCCM -->
    <div class="modal fade" id="Ventana3" tabindex="-1" role="dialog" aria-labelledby="modalV3" aria-hidden="true">
      <div class="modal-dialog" role="document"><div class="modal-content">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES); ?>">
          <div class="modal-header">
            <h5 class="modal-title" id="modalV3">Reasignar cliente</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="IdVenta" value="<?php echo (int)($Reg['Id'] ?? 0); ?>">
            <input type="hidden" name="nombre"  value="<?php echo htmlspecialchars($nombre, ENT_QUOTES); ?>">
            <input type="hidden" name="Status"  value="<?php echo htmlspecialchars($_POST['Status'] ?? '', ENT_QUOTES); ?>">
            <p>Nombre de el Cliente</p>
            <h4 class="text-center"><strong><?php echo htmlspecialchars($Reg['Nombre'] ?? '', ENT_QUOTES); ?></strong></h4>
            <p>Este cliente está asignado a</p>
            <h4 class="text-center">
              <strong>
                <?php
                  $Niv = (int)$basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
                  if (($Reg['Usuario'] ?? '') === "SISTEMA") {
                      echo "SISTEMA";
                      $Niv = 4;
                      $sql = "SELECT * FROM Empleados WHERE Nivel >= $Niv AND Nombre != 'Vacante'";
                  } elseif ($Niv == 1) {
                      $UsrPro = $basicas->BuscarCampos($mysqli, "Nombre", "Empleados", "IdUsuario", $Reg['Usuario'] ?? '');
                      echo $UsrPro ?: "Sin Asignar";
                      $sql = "SELECT * FROM Empleados WHERE Nombre != 'Vacante'";
                  } else {
                      echo htmlspecialchars($basicas->BuscarCampos($mysqli, "Nombre", "Empleados", "IdUsuario", $Reg['Usuario'] ?? ''), ENT_QUOTES);
                      $Suc = (int)$basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "IdUsuario", $Reg['Usuario'] ?? '');
                      $Niv = (int)$basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $Reg['Usuario'] ?? '');
                      $sql = "SELECT * FROM Empleados WHERE Nivel >= $Niv AND Nombre != 'Vacante' AND Sucursal = $Suc";
                  }
                ?>
              </strong>
            </h4>

            <label>Selecciona el nuevo Ejecutivo</label>
            <select class="form-control" name="NvoVend">
              <?php
                if (!empty($sql)) {
                    if ($S62 = $mysqli->query($sql)) {
                        while ($S63 = mysqli_fetch_array($S62)) {
                            echo "<option value='".htmlspecialchars($S63['IdUsuario'], ENT_QUOTES)."'>".
                              htmlspecialchars($basicas->BuscarCampos($mysqli, "NombreNivel", "Nivel", "Id", $S63['Nivel']), ENT_QUOTES)." - ".
                              htmlspecialchars($basicas->BuscarCampos($mysqli, "nombreSucursal", "Sucursal", "Id", $S63['Sucursal']), ENT_QUOTES)." - ".
                              htmlspecialchars($S63['Nombre'], ENT_QUOTES).
                            "</option>";
                        }
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

    <!-- Ventana4: Cambiar datos del cliente REVISADO: 29/09/2025 JCCM -->
    <div class="modal fade" id="Ventana4" tabindex="-1" role="dialog" aria-labelledby="modalV4" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <?php require 'html/ActualizarDatos.php'; ?>
        </div>
      </div>
    </div>

    <!-- Ventana5: Servicio funerario REVISADO: 26/09/2025 JCCM -->
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
            <input type="hidden" name="nombre"   value="<?php echo htmlspecialchars($nombre, ENT_QUOTES); ?>">
            <input type="hidden" name="Host"     value="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES); ?>">
            <input type="hidden" name="IdVenta"  value="<?php echo (int)($Reg['Id'] ?? 0); ?>">
            <input type="hidden" name="IdContact"value="<?php echo (int)($Recg['id'] ?? 0); ?>">
            <input type="hidden" name="IdUsuario"value="<?php echo (int)($Recg1['id'] ?? 0); ?>">
            <input type="hidden" name="Producto" value="<?php echo htmlspecialchars($Reg['Producto'] ?? '', ENT_QUOTES); ?>">

            <p>Nombre del Cliente:</p>
            <h4 class="text-center"><strong><?php echo htmlspecialchars($Reg['Nombre'] ?? '', ENT_QUOTES); ?></strong></h4>
            <p>Tipo de servicio Contratado:</p>
            <h4 class="text-center"><strong><?php echo htmlspecialchars($Reg['Producto'] ?? '', ENT_QUOTES); ?> años</strong></h4>
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

    <!-- Ventana6: Cancelar venta REVISADO: 26/09/2025 JCCM -->
    <div class="modal fade" id="Ventana6" tabindex="-1" role="dialog" aria-labelledby="modalV6" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <?php require 'html/CancelUsr.php'; ?>
        </div>
      </div>
    </div>
    
    <!-- Ventana7: Generar fichas REVISADO: 26/09/2025 JCCM -->
    <div class="modal fade" id="Ventana7" tabindex="-1" role="dialog" aria-labelledby="modalV7" aria-hidden="true">
      <div class="modal-dialog" role="document"><div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalV7"><?php echo htmlspecialchars($Reg['Nombre'] ?? '', ENT_QUOTES); ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="IdVenta" value="<?php echo (int)($Reg['Id'] ?? 0); ?>">
          <input type="hidden" name="nombre"  value="<?php echo htmlspecialchars($nombre, ENT_QUOTES); ?>">
          <input type="hidden" name="Status"  value="<?php echo htmlspecialchars($_POST['Status'] ?? '', ENT_QUOTES); ?>">
          <p><strong>Elige una opción para entregar las fichas al cliente</strong></p>
          <?php if (empty($Recg['Mail'])): ?>
            <h5 class="alert alert-danger" id="exampleModalLabel">Este cliente no cuenta con un Email Registrado</h5>
          <?php endif; ?>
          <br>
        </div>
        <div class="modal-footer">
          <form action="../eia/EnviarCorreo.php" method="post" style="padding-right:5px;">
            <div id="Gps"></div>
            <div data-fingerprint-slot></div>
            <input type="hidden" name="nombre"    value="<?php echo htmlspecialchars($nombre, ENT_QUOTES); ?>">
            <input type="hidden" name="Host"      value="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES); ?>">
            <input type="hidden" name="IdVenta"   value="<?php echo (int)($Reg['Id'] ?? 0); ?>">
            <input type="hidden" name="IdContact" value="<?php echo (int)($Recg['id'] ?? 0); ?>">
            <input type="hidden" name="IdUsuario" value="<?php echo (int)($Recg1['id'] ?? 0); ?>">
            <input type="hidden" name="Producto"  value="<?php echo htmlspecialchars($Reg['Producto'] ?? '', ENT_QUOTES); ?>">
            <input type="hidden" name="Email"     value="<?php echo htmlspecialchars($Recg['Mail'] ?? '', ENT_QUOTES); ?>">
            <input type="hidden" name="mail_token" value="<?php echo htmlspecialchars($_SESSION['mail_token'], ENT_QUOTES); ?>">
            <?php if (!empty($Recg['Mail'])): ?>
              <input type="submit" name="EnviarFichas" class="btn btn-secondary" value="Enviar por Email">
            <?php endif; ?>
          </form>
          <a href="https://kasu.com.mx/login/Generar_PDF/Fichas_Pago_pdf.php?Cte=<?php echo base64_encode((string)($Reg['Id'] ?? 0)); ?>" class="btn btn-success" download>Descargar</a>
        </div>
      </div></div>
    </div>

    <!-- Ventana8: Generar Póliza REVISADO: 26/09/2025 JCCM -->
    <div class="modal fade" id="Ventana8" tabindex="-1" role="dialog" aria-labelledby="modalV8" aria-hidden="true">
      <div class="modal-dialog" role="document"><div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalV8"><?php echo htmlspecialchars($Reg['Nombre'] ?? '', ENT_QUOTES); ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="IdVenta" value="<?php echo (int)($Reg['Id'] ?? 0); ?>">
          <input type="hidden" name="nombre"  value="<?php echo htmlspecialchars($nombre, ENT_QUOTES); ?>">
          <input type="hidden" name="Status"  value="<?php echo htmlspecialchars($_POST['Status'] ?? '', ENT_QUOTES); ?>">
          <p><strong>Elige una opción para entregar la póliza al cliente</strong></p>
          <?php if (empty($Recg['Mail'])): ?>
            <h5 class="alert alert-danger" id="exampleModalLabel">Este cliente no cuenta con un Email Registrado</h5>
          <?php endif; ?>
          <br>
        </div>
        <div class="modal-footer">
          <form action="../eia/EnviarCorreo.php" method="post" style="padding-right:5px;">
            <div id="Gps" style="display:none;"></div>
            <div data-fingerprint-slot></div>
            <input type="hidden" name="nombre"    value="<?php echo htmlspecialchars($nombre, ENT_QUOTES); ?>">
            <input type="hidden" name="Host"      value="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES); ?>">
            <input type="hidden" name="IdVenta"   value="<?php echo (int)($Reg['Id'] ?? 0); ?>">
            <input type="hidden" name="IdContact" value="<?php echo (int)($Recg['id'] ?? 0); ?>">
            <input type="hidden" name="IdUsuario" value="<?php echo (int)($Recg1['id'] ?? 0); ?>">
            <input type="hidden" name="Producto"  value="<?php echo htmlspecialchars($Reg['Producto'] ?? '', ENT_QUOTES); ?>">
            <input type="hidden" name="FullName"  value="<?php echo htmlspecialchars($Reg['Nombre'] ?? '', ENT_QUOTES); ?>">
            <input type="hidden" name="Email"     value="<?php echo htmlspecialchars($Recg['Mail'] ?? '', ENT_QUOTES); ?>">
            <input type="hidden" name="mail_token" value="<?php echo htmlspecialchars($_SESSION['mail_token'], ENT_QUOTES); ?>">
            <?php if (!empty($Recg['Mail'])): ?>
              <input type="submit" name="EnviarPoliza" class="btn btn-secondary" value="Enviar por Email">
            <?php endif; ?>
          </form>
          <a href="https://kasu.com.mx/login/Generar_PDF/Poliza_pdf.php?busqueda=<?php echo base64_encode((string)($Recg['id'] ?? 0)); ?>" class="btn btn-success" download>Descargar</a>
        </div>
      </div></div>
    </div>

    <!-- Ventana9: Ticket de atención REVISADO: 26/09/2025 JCCM -->
    <div class="modal fade" id="Ventana9" tabindex="-1" role="dialog" aria-labelledby="modalV9" aria-hidden="true">
      <div class="modal-dialog" role="document"><div class="modal-content">
        <form method="POST" action="php/Funcionalidad_Pwa.php">
          <div id="Gps"></div>
          <div data-fingerprint-slot></div>
          <input type="hidden" name="nombre"   value="<?php echo htmlspecialchars($nombre, ENT_QUOTES); ?>">
          <input type="hidden" name="Host"     value="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES); ?>">
          <input type="hidden" name="IdVenta"  value="<?php echo (int)($Reg['Id'] ?? 0); ?>">
          <input type="hidden" name="IdContact"value="<?php echo (int)($Recg['id'] ?? 0); ?>">
          <input type="hidden" name="IdUsuario"value="<?php echo (int)($Recg1['id'] ?? 0); ?>">
          <input type="hidden" name="Producto" value="<?php echo htmlspecialchars($Reg['Producto'] ?? '', ENT_QUOTES); ?>">

          <div class="modal-header">
            <h5 class="modal-title" id="modalV9">Ticket de <?php echo htmlspecialchars($Reg['Nombre'] ?? '', ENT_QUOTES); ?></h5>
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
            <input type="text" class="form-control" id="Telefono" value="<?php echo htmlspecialchars($Recg['Telefono'] ?? '', ENT_QUOTES); ?>" name="Telefono" required>
            <br>
          </div>
          <div class="modal-footer">
            <input type="submit" name="AltaTicket" class="btn btn-success" value="Levantar ticket">
          </div>
        </form>
      </div></div>
    </div>
    <!-- Ventana10: Nuevo Cliente REVISADO:  -->
    <div class="modal fade" id="Ventana10" tabindex="-1" role="dialog" aria-labelledby="modalV10" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <?php require 'html/NvoCliente.php'; ?>
        </div>
      </div>
    </div>
  </section>
  </br></br>
  <main class="page-content" name="impresion de datos finales">
    <table class="table">
      <tr>
        <th>Nombre Cliente</th>
        <th>Asignado</th>
        <th>Status</th>
        <th>Producto</th>
        <th>Acciones</th>
      </tr>
      <?php
      // Búsqueda de clientes
      if (!empty($_POST['Status'])) {
          $buscar = $basicas->BLikes($mysqli, "Venta", "Status", $_POST['Status']);
      } elseif (!empty($nombre)) {
          $buscar = $basicas->BLikes($mysqli, "Venta", "Nombre", $nombre);
      } else {
          $buscar = [];
      }
      foreach ($buscar as $row) {
          echo "<tr>
            <th>".htmlspecialchars($row['Nombre'])."</th>
            <th>".htmlspecialchars($row['Usuario'])."</th>
            <th>".htmlspecialchars($row['Status'])."</th>
            <th>".htmlspecialchars($row['Producto'])."</th>
            <th>
            <div style='display:flex;'>
              <form method='POST' action='Mesa_Estado_Cuenta.php' style='padding-right:5px;'>
                <input type='hidden' name='nombre' value='".htmlspecialchars($nombre)."'>";

          if (in_array($row['Status'], ["ACTIVO","COBRANZA","CANCELADO"], true)) {
              echo "
                <label for='0".$row['Id']."' title='Ver estado de cuenta' class='btn' style='background:#F7DC6F;color:#F8F9F9;'><i class='material-icons'>contact_page</i></label>
                <input type='hidden' value='".(int)$row['Id']."' name='busqueda'>
                <input id='0".$row['Id']."' type='submit' name='enviar' class='hidden' style='display: none;' />";
          }

          echo "</form>
            <form method='POST' action='".htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES)."'>
              <input type='hidden' name='nombre' value='".htmlspecialchars($nombre)."'>";

          if ($row['Status'] === "COBRANZA") {
              echo "
                <label for='7".$row['Id']."' title='Generar Fichas' class='btn' style='background:#EB984E;color:#F8F9F9;'><i class='material-icons'>send_to_mobile</i></label>
                <input id='7".$row['Id']."' type='submit' value='7".$row['Id']."' name='IdCliente' class='hidden' style='display: none;'>
                <label for='1".$row['Id']."' title='Agregar un pago' class='btn' style='background:#58D68D;color:#F8F9F9;'><i class='material-icons'>attach_money</i></label>
                <input id='1".$row['Id']."' type='submit' value='1".$row['Id']."' name='IdCliente' class='hidden' style='display: none;'>
                <label for='2".$row['Id']."' title='Promesa de pago' class='btn' style='background:#85C1E9;color:#F8F9F9;'><i class='material-icons'>event</i></label>
                <input id='2".$row['Id']."' type='submit' value='2".$row['Id']."' name='IdCliente' class='hidden' style='display: none;'>
                <label for='3".$row['Id']."' title='Reasignar ejecutivo' class='btn' style='background:#AF7AC5;color:#F8F9F9;'><i class='material-icons'>people_alt</i></label>
                <input id='3".$row['Id']."' type='submit' value='3".$row['Id']."' name='IdCliente' class='hidden' style='display: none;'>";
          }

          if ($row['Status'] === "CANCELADO") {
              echo "
                <label for='1".$row['Id']."' title='Agregar un pago' class='btn' style='background:#58D68D;color:#F8F9F9;'><i class='material-icons'>attach_money</i></label>
                <input id='1".$row['Id']."' type='submit' value='1".$row['Id']."' name='IdCliente' class='hidden' style='display: none;'>";
          }

          if (!in_array($row['Status'], ["CANCELADO","ACTIVO","FALLECIDO"], true)) {
              echo "
                <label for='6".$row['Id']."' title='Cancelar venta' class='btn' style='background:#E74C3C;color:#F8F9F9;'><i class='material-icons'>cancel</i></label>
                <input id='6".$row['Id']."' type='submit' value='6".$row['Id']."' name='IdCliente' class='hidden' style='display: none;'>";
          }

          if ($row['Status'] !== "FALLECIDO") {
              echo "
                <label for='4".$row['Id']."' title='Cambiar datos' class='btn' style='background:#AAB7B8;color:#F8F9F9;'><i class='material-icons'>badge</i></label>
                <input id='4".$row['Id']."' type='submit' value='4".$row['Id']."' name='IdCliente' class='hidden' style='display: none;'>
                <label for='9".$row['Id']."' title='Ticket atención' class='btn' style='background:#F39C12;color:#F8F9F9;'><i class='material-icons'>phone_locked</i></label>
                <input id='9".$row['Id']."' type='submit' value='9".$row['Id']."' name='IdCliente' class='hidden' style='display: none;'>";
          }

          if ($row['Status'] === "ACTIVO") {
              echo "
                <label for='8".$row['Id']."' title='Generar Póliza' class='btn' style='background:#5DADE2;color:#F8F9F9;'><i class='material-icons'>feed</i></label>
                <input id='8".$row['Id']."' type='submit' value='8".$row['Id']."' name='IdCliente' style='display: none;'>
                <label for='5".$row['Id']."' title='Asignar Servicio' class='btn' style='background:#273746;color:#F8F9F9;'><i class='material-icons'>account_balance</i></label>
                <input id='5".$row['Id']."' type='submit' value='5".$row['Id']."' name='IdCliente' class='hidden' style='display: none;'>";
          }

          if ($row['Status'] === "ACTIVACION") {
              echo "
                <label for='8".$row['Id']."' title='Generar Póliza' class='btn' style='background:#5DADE2;color:#F8F9F9;'><i class='material-icons'>feed</i></label>
                <input id='8".$row['Id']."' type='submit' value='8".$row['Id']."' name='IdCliente' style='display: none;'>";
          }

          echo "</form></div></th></tr>";
      }
      ?>
    </table>
  </main>

  <!-- JS únicos y en orden -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>

  <script src="https://www.gstatic.com/charts/loader.js"></script>
  <script src="Javascript/fingerprint-core-y-utils.js"></script>
  <script src="Javascript/finger.js" defer></script>
  <script src="Javascript/localize.js"></script>

  <!-- Abrir modal solo si corresponde -->
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    <?php if (!empty($Lanzar)): ?>
      $('<?php echo $Lanzar; ?>').modal('show');
    <?php endif; ?>
  });
  </script>
  </body>
</html>