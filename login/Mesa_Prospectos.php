<?php
// Iniciar sesión
session_start();

// Incluir librerías y clases
require_once '../eia/librerias.php';

// Fechas del período actual (si las usas en otro lado)
$FechIni = date("d-m-Y", strtotime('first day of this month'));
$FechFin = date("d-m-Y");

// Verificar sesión
if (!isset($_SESSION["Vendedor"])) {
    header('Location: https://kasu.com.mx/login');
    exit;
}

/* ================== Variables base ================== */
$Reg    = null;
$RegDos = null;
$Ventana = null;   // "Ventana1" .. "Ventana9"
$Lanzar  = null;   // '#Ventana' para abrir el modal

/* ================== Parseo del selector ================== */
$IdProspecto = $_POST['IdProspecto'] ?? $_GET['IdProspecto'] ?? null;
if ($IdProspecto !== null && $IdProspecto !== '') {
    $Vtn   = substr($IdProspecto, 0, 1);
    $idStr = substr($IdProspecto, 1);
    if (ctype_digit($idStr)) {
        $CteInt = (int)$idStr;

        // Prospecto
        $stmt = $pros->prepare("SELECT * FROM prospectos WHERE Id = ?");
        if (!$stmt) { error_log($pros->error); die('Error SQL'); }
        $stmt->bind_param('i', $CteInt);
        $stmt->execute();
        $res = $stmt->get_result();
        $Reg = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        // Distribuidor (si aplica)
        if ($Reg) {
            $stmt2 = $pros->prepare("SELECT * FROM Distribuidores WHERE IdProspecto = ?");
            if ($stmt2) {
                $stmt2->bind_param('i', $Reg['Id']);
                $stmt2->execute();
                $ResDos = $stmt2->get_result();
                $RegDos = $ResDos ? $ResDos->fetch_assoc() : null;
                $stmt2->close();
            } else {
                error_log($pros->error);
            }
        }
    } else {
        $Vtn = '';
    }

    // Configurar modal a abrir
    if (!empty($Vtn) && ctype_digit($Vtn)) {
        $Ventana = 'Ventana' . $Vtn;
        $Lanzar  = '#Ventana';
    }
}

// Alerts de mensajes recibidos
if(isset($_GET['Msg'])){
    echo "<script>alert('".htmlspecialchars($_GET['Msg'], ENT_QUOTES)."');</script>";
}

// Método (tracking interno)
$Metodo = "Mesa";

// Captura nombre desde POST o GET
$nombre = $_POST['nombre'] ?? $_GET['nombre'] ?? "";
if ($nombre === '') { $nombre = ' '; }
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#F2F2F2">
    <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
    <title>Mesa Prospectos</title>

    <!-- PWA -->
    <link rel="manifest" href="/login/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- CSS -->
    <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?php echo htmlspecialchars($VerCache ?? '', ENT_QUOTES); ?>">
    <link rel="stylesheet" href="assets/css/Grafica.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>
<body onload="localize()">

  <!-- Top bar fija -->
  <div class="topbar">
    <div class="d-flex align-items-center w-100">
      <h4 class="title">Cartera de Prospectos</h4>

      <!-- botón crear prospecto -->
      <form class="BtnSocial m-0 ml-auto" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <input type="hidden" name="Host" value="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <label for="btnCrear" class="btn mb-0" title="Crear nuevo prospecto" style="background:#F7DC6F;color:#000;">
          <i class="material-icons">person_add</i>
        </label>
        <input id="btnCrear" type="submit" name="CreaProsp" hidden>
      </form>
    </div>
  </div>

    <!-- Menú principal -->
    <section id="Menu">
        <div class="MenuPrincipal">
            <a class="BtnMenu" href="Pwa_Principal.php"><img src="assets/img/FlorKasu.png" alt=""></a>
            <a class="BtnMenu" href="Mesa_Herramientas.php"><img src="assets/img/herramientas.png" style="background:#A9D0F5;" alt=""></a>
        </div>
    </section>

    <!-- CONTENEDOR ÚNICO DE MODAL (seguimos el modelo que sí funciona) -->
    <div class="modal fade" id="Ventana" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <?php
                // Render dinámico del contenido según $Ventana
                if ($Ventana === "Ventana1") : ?>
                    <form method="POST" action="../eia/php/Registrar_Venta.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabel"><?php echo htmlspecialchars($Reg['FullName'] ?? ''); ?></h5>
                        </div>
                        <div class="modal-body">
                            <input type="text" name="Host" value="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" hidden>
                            <input type="text" name="FullName" value="<?php echo htmlspecialchars($Reg['FullName'] ?? ''); ?>" hidden>
                            <input type="text" name="IdProspecto" value="<?php echo htmlspecialchars($Reg['Id'] ?? '', ENT_QUOTES); ?>" hidden>
                            <input type="text" name="Producto" value="<?php echo htmlspecialchars($Reg['Servicio_Interes'] ?? ''); ?>" hidden>

                            <pre id="resultado"></pre>
                            <label>Clave CURP</label>
                            <input class="form-control" type="text" name="CurClie" id="CurCli" maxlength="18" oninput="validarInput(this)" required>
                            <label>Correo Electrónico</label>
                            <input class="form-control" type="email" name="Mail" value="<?php echo htmlspecialchars($Reg['Email'] ?? ''); ?>" required>
                            <label>Teléfono</label>
                            <input class="form-control" type="number" name="Telefono" value="<?php echo htmlspecialchars($Reg['NoTel'] ?? ''); ?>" required>
                            <label>Calle</label>
                            <input class="form-control" type="text" name="calle" required>
                            <label>Número</label>
                            <input class="form-control" type="text" name="numero" required>
                            <label>Colonia</label>
                            <input class="form-control" type="text" name="colonia" required>
                            <label>Municipio</label>
                            <input class="form-control" type="text" name="municipio" required>
                            <label>Estado</label>
                            <input class="form-control" type="text" name="estado" required>
                            <label>Código Postal</label>
                            <input class="form-control" type="text" name="codigo_postal" required>

                            <label>Tipo de servicio</label>
                            <select class="form-control" name="TipoServicio">
                                <option value="Tradicional">Tradicional</option>
                                <option value="Ecologico" selected>Ecologico</option>
                                <option value="Cremacion">Cremación</option>
                            </select>

                            <label>Tiempo de pago</label>
                            <select class="form-control" name="Meses">
                                <option value="0">Pago Único</option>
                                <option value="3">3 Meses</option>
                                <option value="6">6 Meses</option>
                                <option value="9">9 Meses</option>
                            </select>
                            <br>
                            <div class="Legales">
                                <p><input type="checkbox" name="Terminos" value="acepto" required> Acepto Términos y Condiciones (kasu.com.mx/terminos-y-condiciones.php)</p>
                                <p><input type="checkbox" name="Aviso" value="acepto" required> Acepto Aviso de privacidad (kasu.com.mx/Aviso-de-privacidad)</p>
                                <p><input type="checkbox" name="Fideicomiso" value="acepto" required> Conozco el Fideicomiso KASU (kasu.com.mx/Fideicomiso_F0003.pdf)</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="submit" name="RegistroMesa" class="btn btn-primary" value="Registrar Venta">
                        </div>
                    </form>
                <?php
                elseif ($Ventana === "Ventana2") : ?>
                    <form method="POST" action="php/Funcionalidad_Empleados.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabel">Registrar distribuidor</h5>
                        </div>
                        <div class="modal-body">
                            <input type="text" name="Host" value="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" hidden>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" hidden>
                            <input type="number" name="Nivel" value="7" hidden>
                            <input type="number" name="IdProspecto" value="<?php echo htmlspecialchars($RegDos['IdProspecto'] ?? ''); ?>" hidden>

                            <label>Nombre</label>
                            <input type="text" name="Nombre" value="<?php echo htmlspecialchars($RegDos['name'] ?? ''); ?>" hidden>
                            <input class="form-control" type="text" value="<?php echo htmlspecialchars($RegDos['name'] ?? ''); ?>" disabled>

                            <label>Teléfono</label>
                            <input type="text" name="Telefono" value="<?php echo htmlspecialchars($RegDos['Telefono'] ?? ''); ?>" hidden>
                            <input class="form-control" type="text" value="<?php echo htmlspecialchars($RegDos['Telefono'] ?? ''); ?>" disabled>

                            <label>Email</label>
                            <input type="text" name="Email" value="<?php echo htmlspecialchars($RegDos['Mail'] ?? ''); ?>" hidden>
                            <input class="form-control" type="text" value="<?php echo htmlspecialchars($RegDos['Mail'] ?? ''); ?>" disabled>

                            <label>Dirección</label>
                            <input type="text" name="Direccion" value="<?php echo htmlspecialchars($RegDos['Direccion'] ?? ''); ?>" hidden>
                            <input class="form-control" type="text" value="<?php echo htmlspecialchars($RegDos['Direccion'] ?? ''); ?>" disabled>

                            <label>Cuenta Bancaria (CLABE)</label>
                            <input type="number" name="Cuenta" value="<?php echo htmlspecialchars($RegDos['Clabe'] ?? ''); ?>" hidden>
                            <input class="form-control" type="number" value="<?php echo htmlspecialchars($RegDos['Clabe'] ?? ''); ?>" disabled>

                            <label>Sucursal</label>
                            <select class="form-control" name="Sucursal" required>
                                <?php
                                $sql1 = "SELECT * FROM Sucursal WHERE Status = 1";
                                if ($S621 = $mysqli->query($sql1)) {
                                    while ($S631 = $S621->fetch_assoc()) {
                                        echo "<option value='".htmlspecialchars($S631['id'])."'>".htmlspecialchars($S631['nombreSucursal'])."</option>";
                                    }
                                }
                                ?>
                            </select>

                            <label>Jefe Directo</label>
                            <select class="form-control" name="Lider" required>
                                <?php
                                $Nivel = 0;
                                $sql3 = "SELECT * FROM Empleados WHERE Nivel >= '".$Nivel."' AND Nombre != 'Vacante'";
                                if ($S623 = $mysqli->query($sql3)) {
                                    while ($S633 = $S623->fetch_assoc()) {
                                        $Su2cur3 = $basicas->BuscarCampos($mysqli,"nombreSucursal","Sucursal","Id",$S633['Sucursal']);
                                        $St2ats3 = $basicas->BuscarCampos($mysqli,"NombreNivel","Nivel","Id",$S633['Nivel']);
                                        echo "<option value='".htmlspecialchars($S633['Id'])."'>".htmlspecialchars($S633['Nombre'])." - ".htmlspecialchars($St2ats3)." - ".htmlspecialchars($Su2cur3)."</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <input type="submit" name="CreaEmpl" class="btn btn-primary" value="Registrar distribuidor">
                        </div>
                    </form>
                <?php
                elseif ($Ventana === "Ventana3") : ?>
                    <form method="POST" action="php/Registro_Prospectos.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabel"><?php echo htmlspecialchars($Reg['FullName'] ?? ''); ?></h5>
                        </div>
                        <div class="modal-body">
                            <input type="text" name="Host" value="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" hidden>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" hidden>
                            <input type="text" name="IdProspecto" value="<?php echo htmlspecialchars($Reg['Id'] ?? ''); ?>" hidden>
                            <p>Asignar prospecto a</p>
                            <label>Selecciona a quien se asignará</label>
                            <select class="form-control" name="NvoVend">
                                <?php
                                $Nvl = $basicas->BuscarCampos($mysqli,'Nivel','Empleados','IdUsuario',$_SESSION["Vendedor"]);
                                $sql9 = ($Nvl == 1)
                                    ? "SELECT * FROM Empleados WHERE Nombre != 'Vacante'"
                                    : "SELECT * FROM Empleados WHERE Nivel >= 5 AND Nombre != 'Vacante'";
                                if ($S629 = $mysqli->query($sql9)) {
                                    while ($S635 = $S629->fetch_assoc()) {
                                        $Su2cur = $basicas->BuscarCampos($mysqli,"nombreSucursal","Sucursal","Id",$S635['Sucursal']);
                                        $St2ats = $basicas->BuscarCampos($mysqli,"NombreNivel","Nivel","Id",$S635['Nivel']);
                                        echo "<option value='".htmlspecialchars($S635['Id'])."'>".htmlspecialchars($S635['Nombre'])." - ".htmlspecialchars($St2ats)." - ".htmlspecialchars($Su2cur)."</option>";
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
                <?php
                elseif ($Ventana === "Ventana4") : ?>
                    <?php require_once 'html/NvoProspecto.php'; ?>
                <?php
                elseif ($Ventana === "Ventana5") : ?>
                    <form method="POST" action="php/Registro_Prospectos.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabel">Fecha de Alta <?php echo htmlspecialchars($Reg['Alta'] ?? ''); ?></h5>
                        </div>
                        <div class="modal-body">
                            <input type="text" name="Host" value="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" hidden>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" hidden>
                            <input type="text" name="IdProspecto" value="<?php echo htmlspecialchars($Reg['Id'] ?? ''); ?>" hidden>

                            <label>Nombre</label>
                            <input class="form-control" type="text" name="FullName" value="<?php echo htmlspecialchars($Reg['FullName'] ?? ''); ?>">

                            <label>Teléfono</label>
                            <input class="form-control" type="text" name="NoTel" value="<?php echo htmlspecialchars($Reg['NoTel'] ?? ''); ?>">

                            <label>Dirección</label>
                            <input class="form-control" type="text" name="Direccion" value="<?php echo htmlspecialchars($Reg['Direccion'] ?? ''); ?>">

                            <label>Email</label>
                            <input class="form-control" type="text" name="Email" value="<?php echo htmlspecialchars($Reg['Email'] ?? ''); ?>">

                            <label>Servicio de Interés ⇒ <?php echo htmlspecialchars($Reg['Servicio_Interes'] ?? ''); ?></label>
                            <select class="form-control" name="Servicio_Interes">
                                <option value="0">SELECCIONA UN SERVICIO</option>
                                <option value="FUNERARIO">FUNERARIO</option>
                                <option value="DISTRIBUIDOR">DISTRIBUIDOR</option>
                                <option value="UNIVERSITARIO">INVERSION UNIVERSITARIA</option>
                                <option value="RETIRO">RETIRO SEGURO</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <input type="submit" name="CamDat" class="btn btn-primary" value="Modificar Datos">
                        </div>
                    </form>
                <?php
                elseif ($Ventana === "Ventana6") : ?>
                    <form method="POST" action="php/Registro_Prospectos.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabel"><?php echo htmlspecialchars($Reg['FullName'] ?? ''); ?></h5>
                        </div>
                        <div class="modal-body">
                            <input type="text" name="Host" value="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" hidden>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" hidden>
                            <input type="text" name="IdProspecto" value="<?php echo htmlspecialchars($Reg['Id'] ?? ''); ?>" hidden>

                            <label>Selecciona el motivo de la baja</label>
                            <select class="form-control" name="MotivoBaja" required>
                                <option value="Declinada">Propuesta Declinada</option>
                                <option value="expirado">Tiempo de venta expirado</option>
                                <option value="Inexistente">Contacto Inexistente</option>
                                <option value="prospecto">Solicitud de prospecto</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <input type="submit" name="BajaEmp" class="btn btn-primary" value="Dar de Baja">
                        </div>
                    </form>
                <?php
                elseif ($Ventana === "Ventana7") : ?>
                    <form action="php/Registro_Prospectos.php" method="post">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabel"><?php echo htmlspecialchars($Reg['FullName'] ?? ''); ?></h5>
                        </div>
                        <div class="modal-body">
                            <input type="text" name="Host" value="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" hidden>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($Reg['FullName'] ?? ''); ?>" hidden>
                            <input type="text" name="IdProspecto" value="<?php echo htmlspecialchars($Reg['Id'] ?? ''); ?>" hidden>

                            <select class="form-control" name="Asunto">
                                <option value="0">Correo a enviar:</option>
                                <?php
                                $tipo = (($Reg['Servicio_Interes'] ?? '') === "DISTRIBUIDOR") ? 'DISTRIBUIDOR' : 'VENTA';
                                if ($EmTit = $pros->query("SELECT * FROM correos WHERE Tipo = '".$pros->real_escape_string($tipo)."'")) {
                                    while ($TitMa = $EmTit->fetch_assoc()) {
                                        echo '<option value="'.htmlspecialchars($TitMa['Asunto']).'">'.htmlspecialchars($TitMa['Seguimiento'].' => '.$TitMa['Asunto']).'</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <input type="submit" name="interno" class="btn btn-primary" value="Enviar Correo">
                        </div>
                    </form>
                <?php
                elseif ($Ventana === "Ventana8" || $Ventana === "Ventana9") : ?>
                    <form method="POST" action="php/Funcionalidad_Pwa.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabel"><?php echo htmlspecialchars($Reg['FullName'] ?? ''); ?></h5>
                        </div>
                        <div class="modal-body">
                            <?php
                            if (isset($_SESSION['Alta'])) {
                                echo '
                                    <input type="number" name="IdProspecto" value="'.htmlspecialchars($Reg['Id'] ?? '').'" hidden>
                                    <input type="text" name="Host" value="'.htmlspecialchars($_SERVER['PHP_SELF']).'" hidden>
                                    <input type="text" name="nombre" value="'.htmlspecialchars($nombre).'" hidden>
                                    <p>Cerrar servicio de atención al cliente</p>
                                ';
                                $BtnAtnSer = "Registrar Cita";
                            } else {
                                echo '
                                    <input type="number" name="IdProspecto" value="'.htmlspecialchars($Reg['Id'] ?? '').'" hidden>
                                    <input type="text" name="Host" value="'.htmlspecialchars($_SERVER['PHP_SELF']).'" hidden>
                                    <input type="text" name="nombre" value="'.htmlspecialchars($nombre).'" hidden>
                                    <label>Origen</label>
                                    <input class="form-control" disabled type="text" value="'.htmlspecialchars($Reg['Origen'] ?? '').'">
                                    <label>Alta</label>
                                    <input class="form-control" disabled type="text" value="'.htmlspecialchars($Reg['Alta'] ?? '').'">
                                    <label>Número de Teléfono</label>
                                    <input class="form-control" disabled type="text" value="'.htmlspecialchars($Reg['NoTel'] ?? '').'">
                                    <label>Email</label>
                                    <input class="form-control" disabled type="text" value="'.htmlspecialchars($Reg['Email'] ?? '').'">
                                    <label>Producto de Interés</label>
                                    <input class="form-control" disabled type="text" value="'.htmlspecialchars($Reg['Servicio_Interes'] ?? '').'">
                                ';
                                $BtnAtnSer = "Iniciar Cita telefónica";
                            }
                            ?>
                        </div>
                        <div class="modal-footer">
                            <input type="submit" name="RegistroCita" class="btn btn-primary" value="<?php echo htmlspecialchars($BtnAtnSer); ?>">
                        </div>
                    </form>
                <?php
                endif; ?>
            </div>
        </div>
    </div>
    </br></br>
    <!-- Tabla de resultados -->
    <section name="impresion de datos finales">
        <table class="table">
            <tr>
                <th>Nombre Prospecto</th>
                <th>Sem. Activo</th>
                <th>Servicio</th>
                <th>Art. Env</th>
                <th>Asignado</th>
                <th>Acciones</th>
            </tr>
            <?php
            if ($nombre === ' ') {
                $buscar = $basicas->BLikesD2($pros,'prospectos','FullName',$nombre,'Cancelacion',0,'Automatico',0);
            } else {
                $buscar = $basicas->BLikesCan($pros,"prospectos","FullName",$nombre,"Cancelacion",0);
            }
            foreach ($buscar as $row) {
                // Semanas activas
                $Sem   = strtotime($row['Alta']);
                $HoyA  = strtotime(date("Y-m-d"));
                $CSem  = $HoyA - $Sem;
                $ContSem = $CSem/604800;

                // ¿Es distribuidor?
                $Distri = $basicas->BuscarCampos($pros,'Id','Distribuidores','IdProspecto',$row['Id']);

                echo '<tr>
                        <th>'.htmlspecialchars($row["FullName"]).'</th>
                        <th>'.round($ContSem,0).'</th>
                        <th>'.htmlspecialchars($row["Servicio_Interes"]).'</th>
                        <th>'.htmlspecialchars($row["Sugeridos"]).'</th>
                        <th>'.htmlspecialchars($basicas->BuscarCampos($mysqli,"IdUsuario","Empleados","Id",$row["Asignado"])).'</th>
                        <th>
                        <div style="display:flex;">
                            <form method="POST" action="'.htmlspecialchars($_SERVER['PHP_SELF']).'">
                                <input type="text" name="Host" value="'.htmlspecialchars($_SERVER['PHP_SELF']).'" hidden>
                                <input type="text" name="nombre" value="'.htmlspecialchars($nombre).'" hidden>

                                <!-- Registrar Venta (Ventana1) -->
                                <label for="btn1'.$row['Id'].'" title="Registrar Venta" class="btn" style="background:#58D68D;color:#F8F9F9;">
                                    <i class="material-icons">attach_money</i>
                                </label>
                                <input id="btn1'.$row['Id'].'" type="submit" name="IdProspecto" value="1'.$row['Id'].'" hidden>

                                <!-- Enviar prospecto a lead sales (usa Ventana6 según tu flujo original de envío 6) -->
                                <label for="btn4'.$row['Id'].'" title="Enviar lead Sales" class="btn" style="background:#21618C;color:#F8F9F9;">
                                    <i class="material-icons">card_travel</i>
                                </label>
                                <input id="btn4'.$row['Id'].'" type="submit" name="IdProspecto" value="6'.$row['Id'].'" hidden>

                                <!-- Generar Presupuesto (si en otra pantalla) mantenemos id demostrativo Ventana8 para consistencia -->
                                <label for="btn8'.$row['Id'].'" title="Generar Presupuesto" class="btn" style="background:#5DADE2;color:#F8F9F9;">
                                    <i class="material-icons">feed</i>
                                </label>
                                <input id="btn8'.$row['Id'].'" type="submit" name="IdProspecto" value="8'.$row['Id'].'" hidden>

                                <!-- Dar de Baja (Ventana6) -->
                                <label for="btn6'.$row['Id'].'" title="Dar de Baja al Prospecto" class="btn" style="background:#E74C3C;color:#F8F9F9;">
                                    <i class="material-icons">cancel</i>
                                </label>
                                <input id="btn6'.$row['Id'].'" type="submit" name="IdProspecto" value="6'.$row['Id'].'" hidden>

                                <!-- Asignar a ejecutivo (Ventana3) -->
                                <label for="btn3'.$row['Id'].'" title="Asignar prospecto a un ejecutivo" class="btn" style="background:#AF7AC5;color:#F8F9F9;">
                                    <i class="material-icons">people_alt</i>
                                </label>
                                <input id="btn3'.$row['Id'].'" type="submit" name="IdProspecto" value="3'.$row['Id'].'" hidden>

                                <!-- Enviar correo (Ventana7) -->
                                <label for="btn7'.$row['Id'].'" title="Enviar correo electrónico" class="btn" style="background:#EB984E;color:#F8F9F9;">
                                    <i class="material-icons">send_to_mobile</i>
                                </label>
                                <input id="btn7'.$row['Id'].'" type="submit" name="IdProspecto" value="7'.$row['Id'].'" hidden>

                                <!-- Cambiar datos (Ventana5) -->
                                <label for="btn5'.$row['Id'].'" title="Cambiar datos del Prospecto" class="btn" style="background:#AAB7B8;color:#F8F9F9;">
                                    <i class="material-icons">badge</i>
                                </label>
                                <input id="btn5'.$row['Id'].'" type="submit" name="IdProspecto" value="5'.$row['Id'].'" hidden>';

                if (!empty($Distri)) {
                    echo '
                                <!-- Convertir en distribuidor (Ventana2) -->
                                <label for="btn2'.$row['Id'].'" title="Convertir en distribuidor" class="btn" style="background:#58D68D;color:#F8F9F9;">
                                    <i class="material-icons">verified_user</i>
                                </label>
                                <input id="btn2'.$row['Id'].'" type="submit" name="IdProspecto" value="2'.$row['Id'].'" hidden>';
                }

                if (!empty($basicas->Buscar2Campos($pros,'Id','citas','IdProspecto',$row['Id'],'FechaCita',date("Y-m-d")))) {
                    echo '
                                <!-- Ticket Atención (Ventana9) -->
                                <label for="btn9'.$row['Id'].'" title="Ticket de Atención al cliente" class="btn" style="background:#F39C12;color:#F8F9F9;">
                                    <i class="material-icons">phone_locked</i>
                                </label>
                                <input id="btn9'.$row['Id'].'" type="submit" name="IdProspecto" value="9'.$row['Id'].'" hidden>';
                }

                echo '          </form>
                        </div>
                    </th>
                </tr>';
            }
            ?>
        </table>
    </section>

    <!-- JS (UNA sola versión consistente) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>

    <!-- Libs varias -->
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="Javascript/fingerprint-core-y-utils.js"></script>
    <script src="Javascript/finger.js"></script>
    <script src="Javascript/localize.js"></script>

    <!-- Lanzar modal si corresponde (modelo que sí funciona) -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php if (!empty($Lanzar)) : ?>
            $('<?php echo $Lanzar; ?>').modal('show');
            <?php endif; ?>
        });
    </script>
</body>
</html>
