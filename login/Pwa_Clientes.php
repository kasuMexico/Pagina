<?php
//indicar que se inicia una sesion
session_start();
//inlcuir el archivo de funciones
require_once '../eia/librerias.php';
//Validar si existe la session y redireccionar
    if(!isset($_SESSION["Vendedor"])){
        header('Location: https://kasu.com.mx/login');
      }elseif (!empty($_POST['SelCte'])) {
        //realizamos la consulta
        $venta = "SELECT * FROM Venta WHERE Id = '".$_POST['IdVenta']."'";
        //Realiza consulta
            $res = mysqli_query($mysqli, $venta);
        //Si existe el registro se asocia en un fetch_assoc
            if($Reg=mysqli_fetch_assoc($res)){
                //Si el status del cliente esta en activacon no muestra el pago
                if($_POST['StatusVta'] != "ACTIVO" AND $_POST['StatusVta'] != "ACTIVACION" ){
                    //Si el saldo es mayor al costo de compra se usa el pago extemporaneo
                    $s56 = $financieras->SaldoCredito($mysqli,$_POST['IdVenta']);
                    //Se obtieneel valor del pago
                    $Pago1 = $financieras->Pago($mysqli,$_POST['IdVenta']);
                    $Pago = money_format('%.2n', $Pago1);
                    //Saldo de la cuenta
                    $Saldo = $financieras->SaldoCredito($mysqli,$_POST['IdVenta']);
                    $Saldo = money_format('%.2n', $Saldo);
                    //Se obtiene el numero de pagos pendientes
                    $PagoPend = $financieras->PagosPend($mysqli,$_POST['IdVenta']);
                }
            }
            //Variables para lanzar las ventanas emergentes
            $Ventana = "Ventana1";
            $Lanzar = "#Ventana";
      }
      if(!empty($_GET['Vt'])){
        //Variables para lanzar las ventanas emergentes
        $Ventana = "Ventana".$_GET['Vt'];
        $Lanzar = "#Ventana";
      }elseif(!empty($_POST['CreaCte'])){
        //Lanzamos Ventana emergente
          $Ventana = "#Ventana4";
      }
  //Buscar el id de el vendedor
      $IdVen = $basicas->BuscarCampos($mysqli,"Id","Empleados","IdUsuario",$_SESSION["Vendedor"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Cartera Clientes</title>
    <!-- CODELAB: Add meta theme-color -->
    <meta name="theme-color" content="#2F3BA2"/>
    <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
    <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="/login/assets/css/styles.min.css">
    <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons'>
    <!-- Inicio Librerias prara las ventanas emergentes automaticas-->
    <script src='https://code.jquery.com/jquery-3.3.1.slim.min.js' integrity='sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo' crossorigin='anonymous'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js' integrity='sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49' crossorigin='anonymous'></script>
    <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js' integrity='sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy' crossorigin='anonymous'></script>
    <!-- Fin Librerias prara las ventanas emergentes automaticas-->
</head>
<body>
  <!--onload="localize();"-->
  <!--Inicio de menu principal fijo-->
    <section id="Menu">
      <?require_once 'html/Menuprinc.php';?>
    </section>
    <!--Final de menu principal fijo-->
    <!--Inicio Creacion de las ventanas emergentes-->
    <script type='text/javascript'>
        $( document ).ready(function() {
            $('<? echo $Lanzar; ?>').modal('toggle')
        });
    </script>
    <!-- Modal que Muestra la informacion de el cliente -->
    <div class="modal fade" id="Ventana" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
              <? if($Ventana == "Ventana1"){
                require 'html/EmEdoCte.php';
              }elseif($Ventana == "Ventana2"){
                require 'html/EmPago.php';
              }elseif($Ventana == "Ventana3"){
                require 'html/EmPagoEx.php';
              }elseif($Ventana == "Ventana4"){
                require 'html/NvoCliente.php';
              }
              ?>
            </div>
        </div>
    </div>
    <!-- Start: Login Form Clean -->
    <section class="container"  style="width: 99%;">
        <div class="form-group">
            <div class="row align-items-center">
                <div class="col-auto">
                    <form class="BtnSocial" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="padding-top: 5px; padding-left: 5px;">
                        <input type="text" name="Host" value="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: none;">
                        <label for="400" title="Crear nuevo prospecto" class="btn" style="background: #58D68D; color: #F8F9F9;">
                            <i class="material-icons">person_add</i>
                        </label>
                        <input id="400" type="submit" name="CreaCte" class="hidden" style="display: none;" />
                    </form>
                </div>
                <div class="col">
                    <h4 class="mb-0">Cartera de Clientes</h4>
                    <hr>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="table-responsive">
                   <?PHP
                    if($Niv >= 5){
                    //Crear consulta
                        $Ventas = "SELECT * FROM Venta WHERE Usuario = '".$_SESSION["Vendedor"]."'";
                        //Realiza consulta
                            if ($resultado = $mysqli -> query($Ventas)){
                        // obtener el array de objetos
                                while ($fila = $resultado -> fetch_row()) {
                                    printf("
                                        <form method='POST' action='".$_SERVER['PHP_SELF']."'>
                                            <input type='number' name='IdVenta' style='display:  none;' value='%s' />
                                            <input type='text' name='StatusVta' style='display:  none;' value='%s' />
                                            <span class='new badge blue %s' style='position: relative;padding: 0px;width: 100px;top: 20px;'>%s</span>
                                            <input type='submit' id='%s' name='SelCte' class='%s' value='%s' />
                                        </form>
                                        ",$fila[0],$fila[10],$fila[10],$fila[10],$fila[10],$fila[10],$fila[3]);
                                }
                            }
                    }elseif($Niv <= 4 AND $Niv >= 2){
                      //Buscamos el id de la sucursal
                      $IdSuc = $basicas->BuscarCampos($mysqli,"Sucursal","Empleados","IdUsuario",$_SESSION["Vendedor"]);
                      //Buscamos el nombre de la sucursal
                      $NomSuc = $basicas->BuscarCampos($mysqli,"NombreSucursal","Sucursal","Id",$IdSuc);
                      //Crear consulta
                      $sqal = "SELECT * FROM Empleados WHERE Nombre != 'Vacante' AND Nivel >= '$Niv' AND Sucursal = $IdSuc";
                      //Realiza consulta
                      $r4e9s = $mysqli->query($sqal);
                      //Si existe el registro se asocia en un fetch_assoc
                      foreach ($r4e9s as $Resd5){
                        //Crear consulta
                        $Ventas = "SELECT * FROM Venta WHERE Usuario = '".$Resd5["IdUsuario"]."'";
                            //Realiza consulta
                            if ($resultado = $mysqli -> query($Ventas)){
                            // obtener el array de objetos
                                while ($fila = $resultado -> fetch_row()) {
                                    printf("
                                            <form method='POST' action='".$_SERVER['PHP_SELF']."'>
                                                <input type='number' name='IdVenta' style='display:  none;' value='%s' />
                                                <input type='text' name='StatusVta' style='display:  none;' value='%s' />
                                                <input type='text' name='IdVendedor' style='display:  none;' value='".$Resd5["IdUsuario"]."' />
                                                <span class='new badge blue %s' style='position: relative;padding: 0px;width: 100px;top: 20px;'>%s</span>
                                                <input type='submit' id='%s' name='SelCte' class='%s' value='%s - ".$Resd5["IdUsuario"]." - $NomSuc' />
                                            </form>
                                            ",$fila[0],$fila[10],$fila[10],$fila[10],$fila[10],$fila[10],$fila[3]);
                                }
                            }
                        }
                    }elseif($Niv == 1){
                      //Buscamos el id de la sucursal
                      $IdSuc = $basicas->BuscarCampos($mysqli,"Sucursal","Empleados","IdUsuario",$_SESSION["Vendedor"]);
                      //Buscamos el nombre de la sucursal
                      $NomSuc = $basicas->BuscarCampos($mysqli,"NombreSucursal","Sucursal","Id",$IdSuc);
                      //Crear consulta
                      $sqal = "SELECT * FROM Empleados WHERE Nombre != 'Vacante' AND Nivel >= '$Niv'";
                      //Realiza consulta
                      $r4e9s = $mysqli->query($sqal);
                      //Si existe el registro se asocia en un fetch_assoc
                      foreach ($r4e9s as $Resd5){
                        //Crear consulta
                        $Ventas = "SELECT * FROM Venta WHERE Usuario = '".$Resd5["IdUsuario"]."'";
                            //Realiza consulta
                            if ($resultado = $mysqli -> query($Ventas)){
                            // obtener el array de objetos
                                while ($fila = $resultado -> fetch_row()) {
                                    printf("
                                            <form method='POST' action='".$_SERVER['PHP_SELF']."'>
                                                <input type='number' name='IdVenta' style='display:  none;' value='%s' />
                                                <input type='text' name='StatusVta' style='display:  none;' value='%s' />
                                                <input type='text' name='IdVendedor' style='display:  none;' value='".$Resd5["IdUsuario"]."' />
                                                <span class='new badge blue %s' style='position: relative;padding: 0px;width: 100px;top: 20px;'>%s</span>
                                                <input type='submit' id='%s' name='SelCte' class='%s' value='%s - ".$Resd5["IdUsuario"]." - $NomSuc' />
                                            </form>
                                            ",$fila[0],$fila[10],$fila[10],$fila[10],$fila[10],$fila[10],$fila[3]);
                                }
                            }
                        }
                    }
                    ?>
            </div>
        </div>
        <br><br><br><br>
    </section>
    <!-- End: Login Form Clean -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="Javascript/finger.js"></script>
    <script type="text/javascript" src="Javascript/localize.js"></script>
</body>

</html>
