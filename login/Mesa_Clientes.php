<?php
// Iniciar sesión
session_start();

// Incluir librerías y clases
require_once '../eia/librerias.php';

// Verificar usuario logueado
if (!isset($_SESSION["Vendedor"])) {
    header('Location: https://kasu.com.mx/login');
    exit;
}

// Definición de variables principales
$Reg = [];
$Recg = [];
$Recg1 = [];
$Pago = $Pago1 = $PagoPend = $Saldo = 0;

// Procesar POST si se envía un cliente específico
if (isset($_POST['IdCliente'])) {
    $Vtn = substr($_POST['IdCliente'], 0, 1);
    $Cte = substr($_POST['IdCliente'], 1, 5);

    // Consulta de venta
    $venta = "SELECT * FROM Venta WHERE Id = '".$Cte."'";
    $res = mysqli_query($mysqli, $venta);

    if ($Reg = mysqli_fetch_assoc($res)) {
        $Pago1 = $financieras->Pago($mysqli, $Cte);
        $Pago = number_format($Pago1, 2);
        $PagoPend = $financieras->PagosPend($mysqli, $Cte);
        $Saldo = $financieras->SaldoCredito($mysqli, $Cte);
        $Saldo = number_format($Saldo, 2);
        //Calculamos si el cliente esta en mora o al corriente
        $StatVtas = $financieras->estado_mora_corriente( (int)$Reg['Id'] );
        //Reasignamos valor con base en el si el cliente esta en mora o al corriente
        if($StatVtas['estado'] == "AL CORRIENTE"){
             $Status = "Pago";
        }else{
             $Status = "Mora";
        }
        // Buscar usuario relacionado
        $sql1 = "SELECT * FROM Usuario WHERE IdContact = '".$Reg['IdContact']."'";
        $recs1 = mysqli_query($mysqli, $sql1);
        if ($Recg1 = mysqli_fetch_assoc($recs1)) {}

        // Buscar contacto relacionado
        $sql = "SELECT * FROM Contacto WHERE id = '".$Reg['IdContact']."'";
        $recs = mysqli_query($mysqli, $sql);
        if ($Recg = mysqli_fetch_assoc($recs)) {}
    }

    $Ventana = "Ventana" . $Vtn;
    $Vende = $basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
} else {
    $Ventana = "";
    $Vende = $basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
}

// Procesar cambios de ejecutivo 
if (!empty($_POST['CambiVend'])) {
    $basicas->ActCampo($mysqli, "Venta", "Usuario", $_POST['NvoVend'], $_POST['IdVenta']);
    $basicas->ActTab($mysqli, "PromesaPago", "User", $_POST['NvoVend'], "IdVta", $_POST['IdVenta']);
    $basicas->ActTab($mysqli, "Pagos", "Usuario", $_POST['NvoVend'], "IdVenta", $_POST['IdVenta']);
} elseif (!empty($_POST['CancelaCte'])) {
    //************* Funcion: de Registros de Eventos, GPS y Fingerprint *************************//
    $ids = $seguridad->auditoria_registrar(
        $mysqli,                     // conexión principal
        $basicas,                    // tu helper Basicas
        $_POST,                      // datos del form (fingerprint, gps, etc.)
        'Cancelar_Venta',           // nombre del evento
        $_POST['Host'] ?? $_SERVER['PHP_SELF']  // host/origen
    );
    //************ Funcion: de Registros de Eventos, GPS y Fingerprint *************************//
    //Actualiza el status de la venta a CANCELADO
    $basicas->ActCampo($mysqli, "Venta", "Status", "CANCELADO", $_POST['IdVenta']);
    //Mensaje de Cancelacion de Cliente
    $_GET['Vt'] =1;
    $_GET['Msg'] = "Se ha cancelado la Venta";
}

// Captura nombre desde POST o GET
$nombre = $_POST['nombre'] ?? $_GET['nombre'] ?? "";
//Lanzamos las alertas por las actualizaciones
if (isset($_GET['Vt']) && (int)$_GET['Vt'] === 1) {
      echo "<script>window.addEventListener('load',()=>alert('".$_GET['Msg']."'));</script>";
}
// Alertas de correo electrónico
require_once 'php/Selector_Emergentes_Ml.php';

//Token para no duplicar correos
$_SESSION['mail_token'] = bin2hex(random_bytes(16));
//Registro de metodo para pagos / mesa de control/
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

    <!-- Manifest / iOS -->
    <link rel="manifest" href="/login/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- CSS -->
    <link rel="stylesheet" href="/login/assets/css/styles.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href='https://fonts.googleapis.com/icon?family=Material+Icons' rel='stylesheet'>
    <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?echo $VerCache;?>">
    <link rel="stylesheet" href="assets/css/Grafica.css">

    <!-- Inicio Librerias prara las ventanas emergentes automaticas-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js' integrity='sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49' crossorigin='anonymous'></script>

    <!-- JS externos -->
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

</head>
<body onload="localize()">
    <script type='text/javascript'>
        $(document).ready(function() {
            $('#<?php echo $Ventana; ?>').modal('toggle')
        });
    </script>
<section id="Menu">
    <div class="MenuPrincipal">
        <a class="BtnMenu" href="Pwa_Principal.php"><img src="assets/img/FlorKasu.png"></a>
        <a class="BtnMenu" href="Mesa_Herramientas.php"><img src="assets/img/ajustes.png" style="background: #A9D0F5;"></a>
    </div>
</section>
<section name="VentanasEMergentes">
    <!-- Agregar pago a un cliente Funcionando 22/09/25 -->
    <div class="modal fade" id="Ventana1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <?php require 'html/Emergente_Registrar_Pago.php'; ?>
            </div>
        </div>
    </div>
    <!-- Generar Promesa de pago Funcionando 23/s09/2025-->
    <div class="modal fade" id="Ventana2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <?php require 'html/Emergente_Promesa_Pago.php'; ?>
            </div>
        </div>
    </div>
    <!-- Reasignar al cliente a un nuevo ejecutivo Funcionando 22/09/25-->
    <div class="modal fade" id="Ventana3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo $Reg['Nombre'] ?? ''; ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="number" name="IdVenta" value="<?php echo $Reg['Id'] ?? ''; ?>" style="display: none;">
                        <input type="text" name="nombre" value="<?php echo $nombre; ?>" style="display: none;">
                        <input type="text" name="Status" value="<?php echo $_POST['Status'] ?? ''; ?>" style="display: none;">
                        <p>Este cliente esta asignado a</p>
                        <p><strong>
                        <?php
                            $Niv = $basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
                            if (($Reg['Usuario'] ?? '') == "SISTEMA") {
                                echo $Reg['Usuario'];
                                $Niv = 4;
                                $sql = "SELECT * FROM Empleados WHERE Nivel >= $Niv AND Nombre != 'Vacante' ";
                            } elseif ($Niv == 1) {
                                $UsrPro = $basicas->BuscarCampos($mysqli, "Nombre", "Empleados", "IdUsuario", $Reg['Usuario'] ?? '');
                                echo $UsrPro ?: "Sin Asignar";
                                $Suc = $basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "IdUsuario", $Reg['Usuario'] ?? '');
                                $sql = "SELECT * FROM Empleados WHERE Nombre != 'Vacante'";
                            } else {
                                echo $basicas->BuscarCampos($mysqli, "Nombre", "Empleados", "IdUsuario", $Reg['Usuario'] ?? '');
                                $Suc = $basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "IdUsuario", $Reg['Usuario'] ?? '');
                                $Niv = $basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $Reg['Usuario'] ?? '');
                                $sql = "SELECT * FROM Empleados WHERE Nivel >= $Niv AND Nombre != 'Vacante' AND Sucursal = $Suc";
                            }
                        ?>
                        </strong></p>
                        <label>Selecciona el nuevo Ejecutivo</label>
                        <select class="form-control" name="NvoVend">
                            <?php
                            $S62 = $mysqli->query($sql ?? '');
                            while ($S63 = mysqli_fetch_array($S62)) {
                                echo "<option value='" . $S63['IdUsuario'] . "'>" .
                                    $basicas->BuscarCampos($mysqli, "NombreNivel", "Nivel", "Id", $S63['Nivel']) . " - " .
                                    $basicas->BuscarCampos($mysqli, "nombreSucursal", "Sucursal", "Id", $S63['Sucursal']) . " - " .
                                    $S63['Nombre'] . "</option>";
                            }
                            ?>
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
    <!-- Cambiar los datos del cliente  Funcionando 22/09/25-->
    <div class="modal fade" id="Ventana4" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="php/Funcionalidad_Pwa.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Actualizar datos de cliente</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- *********************************************** Bloque de registro de Eventos ************************************************************************* -->
                        <div id="Gps"></div> <!-- Div que lanza el GPS -->
                        <div data-fingerprint-slot></div> <!-- DIV que lanza el Finger Print -->
                        <input type="text" name="nombre" value="<?php echo $nombre; ?>" style="display: none;"> <!-- nombre que busque para esta pantalla -->
                        <input type="text" name="Host" value="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: none;"> <!-- Host de donde estoy enviando la peticion -->
                        <input type="number" name="IdVenta" value="<?php echo $Reg['Id'] ?? ''; ?>" style="display: none;"> <!-- Id de Venta Seleccionado -->
                        <input type="number" name="IdContact" value="<?php echo $Recg['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Contacto Seleccionado -->
                        <input type="number" name="IdUsuario" value="<?php echo $Recg1['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Usuario Seleccionado -->
                        <input type="text" name="Producto" value="<?php echo $Reg['Producto'] ?? ''; ?>" style="display: none;"> <!-- Producto de el cliente Seleccionado -->
                         <!-- ********************************************** Bloque de registro de Eventos ************************************************************************* -->
                        <p>Nombre del Cliente:</p>
                        <h4 class="text-center"><strong><?php echo $Reg['Nombre'] ?? ''; ?></strong></h4>
                        <p>Tipo de servicio Contratado:</p>
                        <h4 class="text-center"><strong><?php echo $Reg['Producto'] ?? ''; ?> años</strong></h4>
                        <p>Direccion del Cliente:</p>
                        <input type="text" class="form-control" value="<?php echo $Recg['calle'] ?? '';?>" name="calle" required>
                        <h4 class="text-center"><strong></strong></h4>
                        <p>Telefono:</p>
                        <input type="number" class="form-control" value="<?php echo $Recg['Telefono'] ?? '';?>" name="Telefono" required>
                        <p>Correo electrónico:</p>
                        <input type="Email" class="form-control" value="<?php echo $Recg['Mail'] ?? '';?>" name="Email" required>
                        <br>
                    </div>
                    <div class="modal-footer">
                        <input type="submit" name="ActDatosCTE" class="btn btn-success" value="Actualizar Datos">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Asignar Servicio - Marcar persona como fallecida Funcionando 22/09/25-->
    <div class="modal fade" id="Ventana5" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="php/Funcionalidad_Pwa.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Servicio Funerario</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- *********************************************** Bloque de registro de Eventos ************************************************************************* -->
                        <div id="Gps"></div> <!-- Div que lanza el GPS -->
                        <div data-fingerprint-slot></div> <!-- DIV que lanza el Finger Print -->
                        <input type="text" name="nombre" value="<?php echo $nombre; ?>" style="display: none;"> <!-- nombre que busque para esta pantalla -->
                        <input type="text" name="Host" value="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: none;"> <!-- Host de donde estoy enviando la peticion -->
                        <input type="number" name="IdVenta" value="<?php echo $Reg['Id'] ?? ''; ?>" style="display: none;"> <!-- Id de Venta Seleccionado -->
                        <input type="number" name="IdContact" value="<?php echo $Recg['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Contacto Seleccionado -->
                        <input type="number" name="IdUsuario" value="<?php echo $Recg1['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Usuario Seleccionado -->
                        <input type="text" name="Producto" value="<?php echo $Reg['Producto'] ?? ''; ?>" style="display: none;"> <!-- Producto de el cliente Seleccionado -->
                        <!-- ********************************************** Bloque de registro de Eventos ************************************************************************* -->
                        <p>Nombre del Cliente:</p>
                        <h4 class="text-center"><strong><?php echo $Reg['Nombre'] ?? ''; ?></strong></h4>
                        <p>Tipo de servicio Contratado:</p>
                        <h4 class="text-center"><strong><?php echo $Reg['Producto'] ?? ''; ?> años</strong></h4>
                        <p>Registra los datos de el Servicio Funerario:</p>
                        <div class="vstack gap-3">
                            <input type="text" class="form-control" name="EmpFune" placeholder="Empleado funerario que atendio el servicio" required>
                            </br>
                            <input type="text" class="form-control" name="Prestador" placeholder="Funeraria que realizo el Servicio" required>
                        </br>
                            <div class="row g-3">
                                <div class="col-6 col-md-8">
                                <input type="text" class="form-control" name="RFC" placeholder="RFC Funeraria" required>
                                </div>
                                <div class="col-6 col-md-4">
                                <input type="number" class="form-control" name="CodigoPostal" placeholder="Código Postal" required>
                                </div>
                            </div>
                            </br>
                            <input type="text" class="form-control" name="Firma" placeholder="Folio del CFDI" required>
                            </br>
                            <input type="number" class="form-control" name="Costo" placeholder="Costo del Servicio" required>
                        </div>
                        <br>
                    </div>
                    <div class="modal-footer">
                        <input type="submit" name="RegisFun" class="btn btn-dark" value="Servicio Realizado">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Cancela la venta Funcionando 22/09/25-->
    <div class="modal fade" id="Ventana6" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <!-- *********************************************** Bloque de registro de Eventos ************************************************************************* -->
                    <div id="Gps"></div> <!-- Div que lanza el GPS -->
                    <div data-fingerprint-slot></div> <!-- DIV que lanza el Finger Print -->
                    <input type="text" name="nombre" value="<?php echo $nombre; ?>" style="display: none;"> <!-- nombre que busque para esta pantalla -->
                    <input type="text" name="Host" value="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: none;"> <!-- Host de donde estoy enviando la peticion -->
                    <input type="number" name="IdVenta" value="<?php echo $Reg['Id'] ?? ''; ?>" style="display: none;"> <!-- Id de Venta Seleccionado -->
                    <input type="number" name="IdContact" value="<?php echo $Recg['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Contacto Seleccionado -->
                    <input type="number" name="IdUsuario" value="<?php echo $Recg1['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Usuario Seleccionado -->
                    <input type="text" name="Producto" value="<?php echo $Reg['Producto'] ?? ''; ?>" style="display: none;"> <!-- Producto de el cliente Seleccionado -->
                    <!-- ********************************************** Bloque de registro de Eventos ************************************************************************* -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Cancelar Venta</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning" role="alert">
                            <input type="text" name="Status" value="<?php echo $_POST['Status'] ?? ''; ?>" style="display: none;">
                            <p>¿Estás seguro que deseas cancelar la poliza de el cliente</p>
                            <h4 class="text-center"><strong><?php echo $Reg['Nombre'] ?? ''; ?></strong></h4>
                            <br>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="submit" name="CancelaCte" class="btn btn-danger" value="Cancelar">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Generar fichas Funcionando 22/09/25-->
    <div class="modal fade" id="Ventana7" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><?php echo $Reg['Nombre'] ?? ''; ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                        <input type="number" name="IdVenta" value="<?php echo $Reg['Id'] ?? ''; ?>" style="display: none;">
                        <input type="text" name="nombre" value="<?php echo $nombre; ?>" style="display: none;">
                        <input type="text" name="Status" value="<?php echo $_POST['Status'] ?? ''; ?>" style="display: none;">
                        <p><strong>Elige una opción para entregar las fichas al cliente</strong></p>
                        <?
                            if(empty($Recg['Mail'])){
                                echo '<h5 class="alert alert-danger" id="exampleModalLabel">Este cliente no cuenta con un Email Registrado</h5>';
                            }
                        ?>
                        <br>
                </div>
                <div class="modal-footer">
                    <!--Enviar Datos para envio de correo electronico con el estado de cuenta   ../eia/EnviarCorreo.php    -->
                    <form action="../eia/EnviarCorreo.php" method="post" style="padding-right: 5px;">
                        <!-- *********************************************** Bloque de registro de Eventos ************************************************************************* -->
                        <div id="Gps"></div> <!-- Div que lanza el GPS -->
                        <div data-fingerprint-slot></div> <!-- DIV que lanza el Finger Print -->
                        <input type="text" name="nombre" value="<?php echo $nombre; ?>" style="display: none;"> <!-- nombre que busque para esta pantalla -->
                        <input type="text" name="Host" value="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: none;"> <!-- Host de donde estoy enviando la peticion -->
                        <input type="number" name="IdVenta" value="<?php echo $Reg['Id'] ?? ''; ?>" style="display: none;"> <!-- Id de Venta Seleccionado -->
                        <input type="number" name="IdContact" value="<?php echo $Recg['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Contacto Seleccionado -->
                        <input type="number" name="IdUsuario" value="<?php echo $Recg1['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Usuario Seleccionado -->
                        <input type="text" name="Producto" value="<?php echo $Reg['Producto'] ?? ''; ?>" style="display: none;"> <!-- Producto de el cliente Seleccionado -->
                        <!-- ********************************************** Bloque de registro de Eventos ************************************************************************* -->
                        <input type="text" name="Email" value="<?php echo $Recg['Mail'] ?? ''; ?>" style="display: none;">
                        <input type="hidden" name="mail_token" value="<?php echo $_SESSION['mail_token']; ?>">
                        <?
                        if(!empty($Recg['Mail'])){
                            echo '<input type="submit" name="EnviarFichas" class="btn btn-secondary" value="Enviar por Email">';
                        }
                        ?>
                    </form>
                    <!-- Descargar poliza por el ejecutivo que atendio al cliente-->
                    <a href="https://kasu.com.mx/login/Generar_PDF/Fichas_Pago_pdf.php?Cte=<? echo base64_encode($Reg['Id']); ?>" class="btn btn-success" download>Descargar</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Generar Poliza Funcionando 22/09/25-->
    <div class="modal fade" id="Ventana8" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><?php echo $Reg['Nombre'] ?? ''; ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                        <input type="number" name="IdVenta" value="<?php echo $Reg['Id'] ?? ''; ?>" style="display: none;">
                        <input type="text" name="nombre" value="<?php echo $nombre; ?>" style="display: none;">
                        <input type="text" name="Status" value="<?php echo $_POST['Status'] ?? ''; ?>" style="display: none;">
                        <p><strong>Elige una opción para entregar la póliza al cliente</strong></p>
                        <?
                            if(empty($Recg['Mail'])){
                                echo '<h5 class="alert alert-danger" id="exampleModalLabel">Este cliente no cuenta con un Email Registrado</h5>';
                            }
                        ?>
                        <br>
                </div>
                <div class="modal-footer">
                    <!--Enviar Datos para envio de correo electronico con el estado de cuenta   ../eia/EnviarCorreo.php    -->
                    <form action="../eia/EnviarCorreo.php" method="post" style="padding-right: 5px;">
                        <!-- *********************************************** Bloque de registro de Eventos ************************************************************************* -->
                        <div id="Gps" style="display: none;"></div> <!-- Div que lanza el GPS -->
                        <div data-fingerprint-slot></div> <!-- DIV que lanza el Finger Print -->
                        <input type="text" name="nombre" value="<?php echo $nombre; ?>" style="display: none;"> <!-- nombre que busque para esta pantalla -->
                        <input type="text" name="Host" value="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: none;"> <!-- Host de donde estoy enviando la peticion -->
                        <input type="number" name="IdVenta" value="<?php echo $Reg['Id'] ?? ''; ?>" style="display: none;"> <!-- Id de Venta Seleccionado -->
                        <input type="number" name="IdContact" value="<?php echo $Recg['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Contacto Seleccionado -->
                        <input type="number" name="IdUsuario" value="<?php echo $Recg1['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Usuario Seleccionado -->
                        <input type="text" name="Producto" value="<?php echo $Reg['Producto'] ?? ''; ?>" style="display: none;"> <!-- Producto de el cliente Seleccionado -->
                        <!-- ********************************************** Bloque de registro de Eventos ************************************************************************* -->
                        <input type="text" name="FullName" value="<?php echo $Reg['Nombre'] ?? ''; ?>" style="display: none;">
                        <input type="text" name="Email" value="<?php echo $Recg['Mail'] ?? ''; ?>" style="display: none;">
                        <input type="hidden" name="mail_token" value="<?php echo $_SESSION['mail_token']; ?>">
                        <?
                        if(!empty($Recg['Mail'])){
                            echo '<input type="submit" name="EnviarPoliza" class="btn btn-secondary" value="Enviar por Email">';
                        }
                        ?>
                    </form>
                    <!-- Descargar poliza por el ejecutivo que atendio al cliente-->
                    <a href="https://kasu.com.mx/login/Generar_PDF/Poliza_pdf.php?busqueda=<? echo base64_encode($Recg['id']); ?>" class="btn btn-success" download>Descargar</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Ticket de Atencion al cliente Funcionando 22/09/25 -->
    <div class="modal fade" id="Ventana9" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="php/Funcionalidad_Pwa.php">
                    <!-- *********************************************** Bloque de registro de Eventos ************************************************************************* -->
                    <div id="Gps"></div> <!-- Div que lanza el GPS -->
                    <div data-fingerprint-slot></div> <!-- DIV que lanza el Finger Print -->
                    <input type="text" name="nombre" value="<?php echo $nombre; ?>" style="display: none;"> <!-- nombre que busque para esta pantalla -->
                    <input type="text" name="Host" value="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: none;"> <!-- Host de donde estoy enviando la peticion -->
                    <input type="number" name="IdVenta" value="<?php echo $Reg['Id'] ?? ''; ?>" style="display: none;"> <!-- Id de Venta Seleccionado -->
                    <input type="number" name="IdContact" value="<?php echo $Recg['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Contacto Seleccionado -->
                    <input type="number" name="IdUsuario" value="<?php echo $Recg1['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Usuario Seleccionado -->
                    <input type="text" name="Producto" value="<?php echo $Reg['Producto'] ?? ''; ?>" style="display: none;"> <!-- Producto de el cliente Seleccionado -->
                    <!-- ********************************************** Bloque de registro de Eventos ************************************************************************* -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Ticket de <?php echo $Reg['Nombre'] ?? ''; ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
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
                            <option value="Baja">Baja - Tiempo solucion 72 h</option>
                            <option value="Media">Media - Tiempo solucion 48 h</option>
                            <option value="Alta">Alta - Tiempo solucion 24 h</option>
                            <option value="Crítica">Crítica - Tiempo solucion 4 h</option>
                        </select>
                        <label for="Telecono" class="form-label mt-3">Telefono adicional para contacto</label>
                        <input type="text" class="form-control" value="<?php echo $Recg['Telefono'] ?? '';?>" name="Telefono" required>
                        <br>
                    </div>
                    <div class="modal-footer">
                        <input type="submit" name="AltaTicket" class="btn btn-success" value="Levantar ticket">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Los demás modales los puedes copiar aquí siguiendo la misma estructura -->
</section>
<section name="impresion de datos finales">
    <table class="table">
        <tr>
            <th>Nombre Cliente</th>
            <th>Asignado</th>
            <th>Status</th>
            <th>Producto</th>
            <th>Acciones</th>
        </tr>
        <?php
        //Busqueda de clientes
        if (!empty($_POST['Status'])) {
            $buscar = $basicas->BLikes($mysqli, "Venta", "Status", $_POST['Status']);
        } elseif (!empty($nombre)) {
            $buscar = $basicas->BLikes($mysqli, "Venta", "Nombre", $nombre);
        } else {
            $buscar = [];
        }
        //Reccoremos los clientes segun los resultados de la busqueda
        foreach ($buscar as $row) {
            echo "
            <tr>
                <th>" . htmlspecialchars($row['Nombre']) . "</th>
                <th>" . htmlspecialchars($row['Usuario']) . "</th>
                <th>" . htmlspecialchars($row['Status']) . "</th>
                <th>" . htmlspecialchars($row['Producto']) . "</th>
                <th>
                <div style='display: flex;'>
                    <form method='POST' action='Mesa_Estado_Cuenta.php' style='padding-right: 5px;'>
                        <input type='text' name='nombre' value='" . htmlspecialchars($nombre) . "' style='display: none;'>
            ";
            //estos son los bonotes mostrados segun el STATUS de el cliente ; ACTIVO o COBRANZA o CANCELADO 
            if ($row['Status'] == "ACTIVO" || $row['Status'] == "COBRANZA" || $row['Status'] == "CANCELADO") {
                echo "
                        <label for='0" . $row['Id'] . "' title='Ver estado de cuenta' class='btn' style='background: #F7DC6F; color: #F8F9F9;' ><i class='material-icons'>contact_page</i></label>
                        <input type='text' value='" . $row['Id'] . "' name='busqueda' style='display: none ;' >
                        <input id='0" . $row['Id'] . "' type='submit' name='enviar' class='hidden' style='display: none;' />
                ";
            }
                echo "
                    </form>
                    <form method='POST' action='" . $_SERVER['PHP_SELF'] . "'>
                        <input type='text' name='nombre' value='" . htmlspecialchars($nombre) . "' style='display: none;'>
                ";
            //estos son los bonotes mostrados segun el STATUS de el cliente ; COBRANZA 
            //teniamos esta consfiguracion previa; revisar cuando los clientes esten en cobranza
            //$row['Status'] != "ACTIVO" && $row['Status'] != "ACTIVACION" ||  && $row['Status'] != "FALLECIDO"
            if ($row['Status'] == "COBRANZA") {
                echo "
                        <label for='7" . $row['Id'] . "' title='Generar Fichas' class='btn' style='background: #EB984E; color: #F8F9F9;' ><i class='material-icons'>send_to_mobile</i></label>
                        <input id='7" . $row['Id'] . "' type='submit' value='7" . $row['Id'] . "' name='IdCliente' class='hidden' style='display: none;' />
                        <label for='1" . $row['Id'] . "' title='Agregar un pago a el cliente' class='btn' style='background: #58D68D; color: #F8F9F9;' ><i class='material-icons'>attach_money</i></label>
                        <input id='1" . $row['Id'] . "' type='submit' value='1" . $row['Id'] . "' name='IdCliente' class='hidden' style='display: none;' />
                        <label for='2" . $row['Id'] . "' title='Generar una promesa de pago' class='btn' style='background: #85C1E9; color: #F8F9F9;' ><i class='material-icons'>event</i></label>
                        <input id='2" . $row['Id'] . "' type='submit' value='2" . $row['Id'] . "' name='IdCliente' class='hidden' style='display: none;' />
                        <label for='3" . $row['Id'] . "' title='Reasigna al cliente a un nuevo ejecutivo' class='btn' style='background: #AF7AC5; color: #F8F9F9;' ><i class='material-icons'>people_alt</i></label>
                        <input id='3" . $row['Id'] . "' type='submit' value='3" . $row['Id'] . "' name='IdCliente' class='hidden' style='display: none;' />
                ";
            }
            //estos son los bonotes mostrados segun el STATUS de el cliente ; DIFERENTE A ACTIVO,  DIFERENTE A ACTIVACION ; o es; COBRANZA y FALLECIDO
            if ($row['Status'] == "CANCELADO") {
                echo "
                        <label for='1" . $row['Id'] . "' title='Agregar un pago a el cliente' class='btn' style='background: #58D68D; color: #F8F9F9;' ><i class='material-icons'>attach_money</i></label>
                        <input id='1" . $row['Id'] . "' type='submit' value='1" . $row['Id'] . "' name='IdCliente' class='hidden' style='display: none;' />

                ";
            }
            if ($row['Status'] != "CANCELADO" && $row['Status'] != "ACTIVO" && $row['Status'] != "FALLECIDO") {
                echo "
                        <label for='6" . $row['Id'] . "' title='Cancela la venta' class='btn' style='background: #E74C3C; color: #F8F9F9;' ><i class='material-icons'>cancel</i></label>
                        <input id='6" . $row['Id'] . "' type='submit' value='6" . $row['Id'] . "' name='IdCliente' class='hidden' style='display: none;' />
                ";
            }
            if ($row['Status'] != "FALLECIDO") {
                echo "
                        <label for='4" . $row['Id'] . "' title='Cambiar los datos del cliente' class='btn' style='background: #AAB7B8; color: #F8F9F9;' ><i class='material-icons'>badge</i></label>
                        <input id='4" . $row['Id'] . "' type='submit' value='4" . $row['Id'] . "' name='IdCliente' class='hidden' style='display: none;' />
                        <label for='9" . $row['Id'] . "' title='Ticket de Atencion al cliente' class='btn' style='background: #F39C12; color: #F8F9F9;' ><i class='material-icons'>phone_locked</i></label>
                        <input id='9" . $row['Id'] . "' type='submit' value='9" . $row['Id'] . "' name='IdCliente' class='hidden' style='display: none;' />
                ";
            }
            if ($row['Status'] == "ACTIVO") {
                echo "
                        <label for='8" . $row['Id'] . "' title='Generar Poliza' class='btn' style='background: #5DADE2; color: #F8F9F9;' ><i class='material-icons'>feed</i></label>
                        <input id='8" . $row['Id'] . "' type='submit' value='8" . $row['Id'] . "' name='IdCliente' style='display: none ;' >
                        <label for='5" . $row['Id'] . "' title='Asignar Servicio' class='btn' style='background: #273746; color: #F8F9F9;' ><i class='material-icons'>account_balance</i></label>
                        <input id='5" . $row['Id'] . "' type='submit' value='5" . $row['Id'] . "' name='IdCliente' class='hidden' style='display: none;' />
                ";
            }
            if ($row['Status'] == "ACTIVACION") {
                echo "
                        <label for='8" . $row['Id'] . "' title='Generar Poliza' class='btn' style='background: #5DADE2; color: #F8F9F9;' ><i class='material-icons'>feed</i></label>
                        <input id='8" . $row['Id'] . "' type='submit' value='8" . $row['Id'] . "' name='IdCliente' style='display: none ;' >
                ";
            }
            echo "
                </form>
                <div>
                </th>
            </tr>
            ";
        }
        ?>
    </table>
</section>
    <script type="text/javascript" src="Javascript/localize.js"></script>
    <script src="Javascript/fingerprint-core-y-utils.js"></script>
    <script src="Javascript/finger.js" defer></script>
</body>
</html>