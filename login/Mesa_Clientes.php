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

        // Buscar usuario relacionado
        $sql1 = "SELECT * FROM Usuario WHERE IdContact = '".$Reg['IdContact']."'";
        $recs1 = mysqli_query($mysqli, $sql1);
        if ($Recg1 = mysqli_fetch_assoc($recs1)) {}

        // Buscar contacto relacionado
        $sql = "SELECT * FROM Contacto WHERE Id = '".$Reg['IdContact']."'";
        $recs = mysqli_query($mysqli, $sql);
        if ($Recg = mysqli_fetch_assoc($recs)) {}
    }

    $Ventana = "Ventana" . $Vtn;
    $Vende = $basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
} else {
    $Ventana = "";
    $Vende = $basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
}

// Procesar cambios de ejecutivo o cancelaciones
if (!empty($_POST['CambiVend'])) {
    $basicas->ActCampo($mysqli, "Venta", "Usuario", $_POST['NvoVend'], $_POST['IdVenta']);
    $basicas->ActTab($mysqli, "PromesaPago", "User", $_POST['NvoVend'], "IdVta", $_POST['IdVenta']);
    $basicas->ActTab($mysqli, "Pagos", "Usuario", $_POST['NvoVend'], "IdVenta", $_POST['IdVenta']);
} elseif (!empty($_POST['CancelaCte'])) {
    $basicas->ActCampo($mysqli, "Venta", "Status", "CANCELADO", $_POST['IdVenta']);
}

// Captura nombre desde POST o GET
$name = $_POST['nombre'] ?? $_GET['name'] ?? "";

// Alertas de correo electrónico
require_once 'php/Selector_Emergentes_Ml.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,user-scalable=no">
    <title>clientes</title>
    <meta name="theme-color" content="#2F3BA2" />
    <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
    <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles2.min.css">
    <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons'>
    <script src='https://code.jquery.com/jquery-3.3.1.slim.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js'></script>
    <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js'></script>
</head>
<body>
<section id="Menu">
    <div class="MenuPrincipal">
        <a class="BtnMenu" href="Pwa_Principal.php"><img src="assets/img/FlorKasu.png"></a>
        <a class="BtnMenu" href="Mesa_Herramientas.php"><img src="assets/img/ajustes.png" style="background: #A9D0F5;"></a>
    </div>
</section>
<br><br><br>
<section name="VentanasEMergentes">
    <script type='text/javascript'>
        $(document).ready(function() {
            $('#<?php echo $Ventana; ?>').modal('toggle')
        });
    </script>
    <!-- Registrar pago -->
    <div class="modal fade" id="Ventana1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <?php require 'html/Emergente_Registrar_Pago.php'; ?>
            </div>
        </div>
    </div>
    <!-- Registrar promesa de pago -->
    <div class="modal fade" id="Ventana2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <?php require 'html/Emergente_Promesa_Pago.php'; ?>
            </div>
        </div>
    </div>
    <!-- Reasignar cliente un nuevo ejecutivo -->
    <div class="modal fade" id="Ventana3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo $Reg['Nombre'] ?? ''; ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </div>
                    <div class="modal-body">
                        <input type="number" name="IdVenta" value="<?php echo $Reg['Id'] ?? ''; ?>" style="display: none;">
                        <input type="text" name="nombre" value="<?php echo $name; ?>" style="display: none;">
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
    <!-- Cambiar los datos del cliente  -->
    <div class="modal fade" id="Ventana4" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo $Reg['Nombre'] ?? ''; ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </div>
                    <div class="modal-body">
                        <input type="number" name="IdVenta" value="<?php echo $Reg['Id'] ?? ''; ?>" style="display: none;">
                        <input type="text" name="nombre" value="<?php echo $name; ?>" style="display: none;">
                        <input type="text" name="Status" value="<?php echo $_POST['Status'] ?? ''; ?>" style="display: none;">
                        <p>Aqui van los formularios de cambio de datos de el cliente</p>
                        <br>
                    </div>
                    <div class="modal-footer">
                        <input type="submit" name="CambiVend" class="btn btn-primary" value="Reasignar">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Cambiar los datos del cliente  -->
    <div class="modal fade" id="Ventana5" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo $Reg['Nombre'] ?? ''; ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </div>
                    <div class="modal-body">
                        <input type="number" name="IdVenta" value="<?php echo $Reg['Id'] ?? ''; ?>" style="display: none;">
                        <input type="text" name="nombre" value="<?php echo $name; ?>" style="display: none;">
                        <input type="text" name="Status" value="<?php echo $_POST['Status'] ?? ''; ?>" style="display: none;">
                        <p>Aqui van los formularios de cambio de datos de el cliente</p>
                        <br>
                    </div>
                    <div class="modal-footer">
                        <input type="submit" name="CambiVend" class="btn btn-primary" value="Reasignar">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Cancela la venta  -->
    <div class="modal fade" id="Ventana6" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo $Reg['Nombre'] ?? ''; ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning" role="alert">
                            <input type="number" name="IdVenta" value="<?php echo $Reg['Id'] ?? ''; ?>" style="display: none;">
                            <input type="text" name="nombre" value="<?php echo $name; ?>" style="display: none;">
                            <input type="text" name="Status" value="<?php echo $_POST['Status'] ?? ''; ?>" style="display: none;">
                            <p>¿Estás seguro que deseas cancelar esta venta?</p>
                            <p>Esta acción no se puede deshacer.</p>
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
    <!-- Los demás modales los puedes copiar aquí siguiendo la misma estructura -->
    <!-- ... -->
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
        } elseif (!empty($name)) {
            $buscar = $basicas->BLikes($mysqli, "Venta", "Nombre", $name);
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
                        <input type='text' name='nombre' value='" . htmlspecialchars($name) . "' style='display: none;'>
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
                        <input type='text' name='nombre' value='" . htmlspecialchars($name) . "' style='display: none;'>
            ";
            //estos son los bonotes mostrados segun el STATUS de el cliente ; COBRANZA 
            if ($row['Status'] == "COBRANZA") {
                echo "
                        <label for='7" . $row['Id'] . "' title='Generar Fichas' class='btn' style='background: #EB984E; color: #F8F9F9;' ><i class='material-icons'>send_to_mobile</i></label>
                        <input id='7" . $row['Id'] . "' type='submit' value='7" . $row['Id'] . "' name='IdCliente' class='hidden' style='display: none;' />
                ";
            }
            //estos son los bonotes mostrados segun el STATUS de el cliente ; DIFERENTE A ACTIVO,  DIFERENTE A ACTIVACION ; o es; COBRANZA y FALLECIDO
            if ($row['Status'] != "ACTIVO" && $row['Status'] != "ACTIVACION" || $row['Status'] == "COBRANZA" && $row['Status'] != "FALLECIDO") {
                echo "
                        <label for='1" . $row['Id'] . "' title='Agregar un pago a el cliente' class='btn' style='background: #58D68D; color: #F8F9F9;' ><i class='material-icons'>attach_money</i></label>
                        <input id='1" . $row['Id'] . "' type='submit' value='1" . $row['Id'] . "' name='IdCliente' class='hidden' style='display: none;' />
                        <label for='2" . $row['Id'] . "' title='Generar una promesa de pago ' class='btn' style='background: #85C1E9; color: #F8F9F9;' ><i class='material-icons'>event</i></label>
                        <input id='2" . $row['Id'] . "' type='submit' value='2" . $row['Id'] . "' name='IdCliente' class='hidden' style='display: none;' />
                        <label for='3" . $row['Id'] . "' title='Reasigna al cliente a un nuevo ejecutivo' class='btn' style='background: #AF7AC5; color: #F8F9F9;' ><i class='material-icons'>people_alt</i></label>
                        <input id='3" . $row['Id'] . "' type='submit' value='3" . $row['Id'] . "' name='IdCliente' class='hidden' style='display: none;' />
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="Javascript/finger.js"></script>
<script type="text/javascript" src="Javascript/localize.js"></script>
</body>
</html>