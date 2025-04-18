<?php
//Requerimos el archivo de librerias *JCCM
  require_once 'eia/librerias.php';
//Validar si existe la session y redireccionar
  $venta = "SELECT * FROM Venta WHERE Id = '".$_GET['Id']."'";
//Realiza consulta
  $res = mysqli_query($mysqli, $venta);
//Si existe el registro se asocia en un fetch_assoc
    if($Reg=mysqli_fetch_assoc($res)){
  }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>cuenta</title>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.css">
    <link rel="stylesheet" href="assets/css/templatemo-softy-pinko.css">
    <link rel="stylesheet" href="login/assets/css/styles.min.css">
</head>

<body>
    <!--Inicio de menu principal fijo-->
        <header class="header-area header-sticky">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav class="main-nav">
                        <!-- ***** Logo Start ***** -->
                        <a href="index.php" class="logo">
                            <img src="assets/images/kasu_logo.jpeg" alt="Logo Kasu"/>
                        </a>
                        <!-- ***** Logo End ***** -->
                        <!-- ***** Menu Start ***** -->
                        <ul class="nav">
                            <li><a href="index.php">KASU</a></li>
                        </ul>
                        <a class='menu-trigger'>
                            <span>Menú</span>
                        </a>
                        <!-- ***** Menu End ***** -->
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <!--Final de menu principal fijo-->
    <br>
    <br>
    <br>
        <h2>Estado de cuenta</h2>
    <div class="cabecera">
        <h2><?PHP echo $Reg['Nombre'];?></h2>
        <div class="CabeceraCuenta">
            <div class="caja">
              <h2><?PHP echo $Reg['Producto'];?></h2>
            </div>
            <h2><? echo $Reg['Status'];?></h2>
        </div>
    </div>
    <div class="historial">
      <table class="table">
                <tbody>
                    <tr>
                        <td>Fecha</td>
                        <td>Pagos</td>
                    </tr>
                </tbody>
                <tbody>
                <?PHP
                //Crear consulta
                $Pagos = "SELECT * FROM Pagos WHERE IdVenta = '".$_GET['Id']."'";
                    //Realiza consulta
                        if ($resul = $mysqli->query($Pagos)){
                    // obtener el array de objetos
                            while ($Pago = $resul->fetch_row()) {
                                printf("
                                    <tr>
                                        <td>%s/%s/%s</td>
                                        <td>$ %s</td>
                                    </tr>
                                ",$Pago[6],$Pago[7],$Pago[8],$Pago[5]);
                            }
                        }
                    ?>
                </tbody>
        </table>
    </div>
    <div class="pie">
        <div class="tablaCuenta" >
            <table class="table" >
                <tbody>
                    <tr>
                        <td>Pagos</td>
                        <td>$ <?PHP
                            //Crear consulta
                            $sql = "SELECT SUM(Cantidad) FROM Pagos WHERE IdVenta = '".$_GET['Id']."'";
                            //Realiza consulta
                            $resp = mysqli_query($mysqli, $sql);
                            //Si existe el registro se asocia en un fetch_assoc
                            $SubTot = mysqli_fetch_assoc($resp);
                            //Si existe el campo a buscar
                                echo $SubTot['SUM(Cantidad)'];
                        ?></td>
                    </tr>
                    <tr>
                        <td>Deuda</td>
                        <td>$ <?PHP echo $Reg['Subtotal'];?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
        <!-- jQuery -->
    <script src="assets/js/jquery-2.1.0.min.js"></script>
    <!-- Bootstrap -->
    <script src="assets/js/popper.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <!-- Plugins -->
    <script src="assets/js/scrollreveal.min.js"></script>
    <script src="assets/js/waypoints.min.js"></script>
    <script src="assets/js/jquery.counterup.min.js"></script>
    <script src="assets/js/imgfix.min.js"></script>
    <!-- Global Init -->
    <script src="assets/js/custom.js"></script>
</body>

</html>
