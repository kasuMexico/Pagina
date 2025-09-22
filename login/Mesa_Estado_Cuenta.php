<?php
// Variables principales de session
session_start();
require_once '../eia/librerias.php';
date_default_timezone_set('America/Mexico_City');
// Validar si existe la session y redireccionar
if (empty($_SESSION['Vendedor'])) {
    header('Location: https://kasu.com.mx/login');
    exit();
}
// Variables principales
$busqueda = $_POST['busqueda'] ?? $_GET['busqueda'] ?? '';
$tel = '7208177632'; // Si tienes el teléfono de la empresa, puedes colocarlo aquí

// Consulta de la venta
$Ct3 = "SELECT * FROM Venta WHERE Id = '".$mysqli->real_escape_string($busqueda)."'";
$Ct3a = mysqli_query($mysqli, $Ct3);
// Si existe el registro se asocia en un fetch_assoc
if ($venta = mysqli_fetch_assoc($Ct3a)) {
    // Realiza consulta
    $Ct1 = "SELECT * FROM Contacto WHERE id = '".$mysqli->real_escape_string($venta['IdContact'])."'";
    $Ct1a = mysqli_query($mysqli, $Ct1);
    // Si existe el registro se asocia en un fetch_assoc
    if ($datos = mysqli_fetch_assoc($Ct1a)) {
        // Realiza consulta
        $Ct2 = "SELECT * FROM Usuario WHERE IdContact = '".$mysqli->real_escape_string($venta['IdContact'])."'";
        $Ct2a = mysqli_query($mysqli, $Ct2);
        // Si existe el registro se asocia en un fetch_assoc
        if ($persona = mysqli_fetch_assoc($Ct2a)) {
            // Saldo de el crédito
            if ($venta['Status'] == "ACTIVO" || $venta['Status'] == "ACTIVACION") {
                $saldo = number_format(0, 2);
            } else {
                $saldo_val = $financieras->SaldoCredito($mysqli, $venta['Id']);
                $saldo = number_format($saldo_val, 2);
            }
            // Si el usuario compró a un mes o de contado
            if ($venta['NumeroPagos'] >= 2) {
                $Credito = "Compra a crédito; " . $venta['NumeroPagos'] . " Meses";
            } else {
                $Credito = "Compra de contado";
            }

    //Token para no duplicar correos
    $_SESSION['mail_token'] = bin2hex(random_bytes(16));
    
    // Captura nombre desde POST o GET
    $name = $_POST['nombre'] ?? $_GET['name'] ?? "";
    //Lanzamos las alertas por las actualizaciones
    if (isset($_GET['Vt']) && (int)$_GET['Vt'] === 1) {
        echo "<script>window.addEventListener('load',()=>alert('".$_GET['Msg']."'));</script>";
    }
?>
<!DOCTYPE html>
<html lang="es" dir="ltr">
<head>
   <meta charset="utf-8">
   <title>Estado de Cuenta</title>
   <link rel="shortcut icon" href="../assets/images/logokasu.ico">
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons'>
   <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
</head>
<body onload="localize()">
    <nav class="navbar navbar-expand-lg text-white  justify-content-between " style="background-color:#8D70E3;">
        <a class="navbar-brand">Estado Cuenta</a>
        <div class="form-inline">
            <!--Retorna a la ventana anterior-->
            <form action="Mesa_Clientes.php" method="post" style="padding-right: 5px;">
                <input type='text' name='nombre' value='<?php echo $name; ?>' style='display: none;'/>
                <label for='Regresar' title='Regresar a cliente' class='btn' style='background: #F5B041; color: #F8F9F9;' ><i class='material-icons'>undo</i></label>
                <input id='Regresar' type='submit' value='Regresar' name='Accion' class='hidden' style='display: none;'/>
            </form>
            <!--Enviar Datos para envio de correo electronico con el estado de cuenta-->
            <form action="../eia/EnviarCorreo.php" method="post" style="padding-right: 5px;">
                <!-- *********************************************** Bloque de registro de Eventos ************************************************************************* -->
                <div id="Gps" style="display: none;"></div> <!-- Div que lanza el GPS -->
                <div data-fingerprint-slot ></div> <!-- DIV que lanza el Finger Print -->
                <input type="text" name="nombre" value="<?php echo $name; ?>" style="display: none;"> <!-- nombre que busque para esta pantalla -->
                <input type="text" name="Host" value="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: none;"> <!-- Host de donde estoy enviando la peticion -->
                <input type="number" name="IdVenta" value="<?php echo $Reg['Id'] ?? ''; ?>" style="display: none;"> <!-- Id de Venta Seleccionado -->
                <input type="number" name="IdContact" value="<?php echo $Recg['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Contacto Seleccionado -->
                <input type="number" name="IdUsuario" value="<?php echo $Recg1['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Usuario Seleccionado -->
                <input type="text" name="Producto" value="<?php echo $Reg['Producto'] ?? ''; ?>" style="display: none;"> <!-- Producto de el cliente Seleccionado -->
                <!-- ********************************************** Bloque de registro de Eventos ************************************************************************* -->
                <input type='text' name='IdVenta' value='<?php echo htmlspecialchars($busqueda); ?>' style='display: none;'/>
                <input type="text" name="FullName" value="<?php echo htmlspecialchars($persona['Nombre']); ?>" style="display: none;">
                <input type="text" name="Email" value="<?php echo htmlspecialchars($datos['Mail']); ?>" style="display: none;">
                <input type="text" name="Asunto" value="ENVIO ARCHIVO" style="display: none;">
                <input type="text" name="Descripcion" value="Estado de Cuenta" style="display: none;">
                <label for='Enviar' title='Enviar estado de cuenta' class='btn' style='background: #2980B9; color: #F8F9F9;' ><i class='material-icons'>email</i></label>
                <input type="hidden" name="mail_token" value="<?php echo $_SESSION['mail_token']; ?>">
                <input id='Enviar' type='submit' value='Enviar' name='EnviarEdoCta' class='hidden' style='display: none;' />
            </form>
            <!--Descargar el estado de cuenta-->
            <a class='btn' style='background: #58D68D; color: #F8F9F9;' href="https://kasu.com.mx/login/Generar_PDF/Estado_Cuenta_pdf.php?busqueda=<?php echo base64_encode($busqueda); ?>"><i class='material-icons'>download</i></a>
        </div>
    </nav>
    <br>
    <div class="container">
        <div class="card">
            <div class="card-header bg-secondary text-light"><?php echo htmlspecialchars($persona['Nombre']); ?></div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="card">
                            <div class="card-header bg-secondary text-light">Datos de la empresa</div>
                            <div class="card-body">
                                 KASU, Servicios a Futuro S.A de C.V. <br>
                                 Fideicomiso F/0003 Gastos Funerarios<br>
                                 Atlacomulco, Estado de México, México C.P. 50450<br>
                                 Teléfono: <?php echo htmlspecialchars($tel); ?><br>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <div class="card-header bg-secondary text-light">Datos del Cliente</div>
                            <div class="card-body">
                                Nombre : <?php echo htmlspecialchars($persona['Nombre']); ?> <br>
                                CURP : <?php echo htmlspecialchars($persona['ClaveCurp']); ?><br>
                                Fecha Registro : <?php echo substr($venta['FechaRegistro'], 0, 10); ?><br>
                                Fecha Última Modificación : <?php echo substr($persona['FechaRegistro'], 0, 10); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col">
                        <div class="card">
                            <div class="card-header bg-secondary text-light"></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
										Dirección : <?php echo isset($datos['calle']) ? htmlspecialchars($datos['calle']) : '<span class="text-danger">No disponible</span>'; ?> <br>
                                        Teléfono : <?php echo htmlspecialchars($datos['Telefono']); ?> <br>
                                        Email : <?php echo htmlspecialchars($datos['Mail']); ?> <br>
                                        Producto : <?php echo htmlspecialchars($venta['Producto']); ?><br>
                                        N. Activador : <?php echo htmlspecialchars($venta['IdFIrma']); ?> <br>
                                        Status : <?php echo htmlspecialchars($venta['Status']); ?><br>
                                        <?php echo htmlspecialchars($Credito); ?><br>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col">
                        <div class="card">
                            <div class="card-header bg-secondary text-light">Historial de transacciones</div>
                            <div class="card-body">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Concepto</th>
                                            <th>Saldo</th>
                                            <th>Debe</th>
                                            <th>Haber</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo substr($venta['FechaRegistro'], 0, 10); ?></td>
                                            <td>Compra de servicio <?php echo htmlspecialchars($datos['Producto']); ?></td>
                                            <td><?php echo number_format($venta['CostoVenta'], 2); ?></td>
                                            <td> - </td>
                                            <td> - </td>
                                        </tr>
                                        <?php
                                        // Realiza consulta
                                        $Ct4 = "SELECT * FROM Pagos WHERE IdVenta = '".$mysqli->real_escape_string($busqueda)."'";
                                        if ($resultado = $mysqli->query($Ct4)) {
                                            while ($pago = $resultado->fetch_assoc()) {
                                                printf(
                                                    "
                                                    <tr>
                                                        <td>%s</td>
                                                        <td>%s de Servicio %s</td>
                                                        <td> - </td>
                                                        <td>%s</td>
                                                        <td>$ - Mxn</td>
                                                    </tr>
                                                    ",
                                                    substr($pago['FechaRegistro'], 0, 10),
                                                    htmlspecialchars($pago['status']),
                                                    htmlspecialchars($venta['Producto']),
                                                    number_format($pago['Cantidad'], 2)
                                                );
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                                <table class="table table-hover">
                                    <tbody>
                                        <tr>
                                            <td>Saldo de la cuenta</td>
                                            <td><?php echo $saldo; ?></td>
                                        </tr>
                                        <?php }}} // FIN de todos los fetch_assoc anidados ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script type="text/javascript" src="Javascript/localize.js"></script>
<script src="Javascript/fingerprint-core-y-utils.js"></script>
<script src="Javascript/finger.js" defer></script>
</html>