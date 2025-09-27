<?php
//indicar que se inicia una sesion
session_start();
//inlcuir el archivo de funciones
require_once '../eia/librerias.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

//Validar si existe la session y redireccionar
    if(!isset($_SESSION["Vendedor"])){
        header('Location: https://kasu.com.mx/login');
      }else{
        //Seleccionamos el Id de el usuario 
        $IdAsignacion = $basicas->BuscarCampos($mysqli,"Id","Empleados","IdUsuario",$_SESSION["Vendedor"]);
        //Asigamos el nivel a el Usuario -> no se busca el nivel ya que se busca en el Menu
      }
      //Este IF lanza las ventanas Emergentes para acciones concretas
      if(!empty($_POST['CreaProsp'])){  //Crear un nuevo prospecto
        //Lanzamos Ventana emergente
          $Lanzar = "#Ventana2";
      }elseif (!empty($_POST['ArmaPres'])){
        //realizamos la consulta
        $venta = "SELECT * FROM prospectos WHERE Id = '".$_POST['IdPros']."'";
        //Realiza consulta
            $res = mysqli_query($pros, $venta);
        //Si existe el registro se asocia en un fetch_assoc
            if($Reg=mysqli_fetch_assoc($res)){}
        //Lanzamos la ventana
        $Lanzar = "#Ventana3";
      }elseif (!empty($_POST['SelPros'])) {
        //realizamos la consulta
        $venta = "SELECT * FROM prospectos WHERE Id = '".$_POST['IdProspecto']."'";
        //Realiza consulta
            $res = mysqli_query($pros, $venta);
        //Si existe el registro se asocia en un fetch_assoc
            if($Reg=mysqli_fetch_assoc($res)){
              //Variables para lanzar las ventanas emergentes
              $Lanzar = "#Ventana1";
              $nomVd = $basicas->BuscarCampos($mysqli,"Nombre","Empleados","IdUsuario",$_SESSION["Vendedor"]);
            }
      }elseif (!empty($_POST['Cancelar'])) {
          //Actualizacion de Datos
          $nomVd = $basicas->ActCampo($pros,"prospectos","Cancelacion",1,$_POST['IdPros']);
      }
      //Javascript que imprime el mensaje de alerta de recepcion de comentario
      if(isset($_GET['Msg'])){
      	echo "<script type='text/javascript'>
      						alert('".$_GET['Msg']."');
      				</script>";
      }
//Registro de metodo para pagos / mesa de control/
$Metodo = "Vtas";
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <!-- Evitar el escalado para mantener el diseño -->
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#F2F2F2" />
    <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
    <title>Cartera Prospectos</title>
    <!-- Manifest de /login -->
    <link rel="manifest" href="/login/manifest.webmanifest">
    <!-- Icono iOS -->
    <link rel="apple-touch-icon" href="/login/assets/img/icon-180x180.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="stylesheet" href="/login/assets/css/styles.min.css">
    <!-- CSS desde CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons'>
    <!-- Librerías para gráficos y jQuery -->
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <!-- CSS locales -->
    <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?echo $VerCache;?>">
</head>
<body onload="localize()"> <!-- Se lanza la funcion de localizacion -->

  <!--Inicio de menu principal fijo-->
    <section id="Menu">
      <?require_once 'html/Menuprinc.php';?>
    </section>
    <!--Final de menu principal fijo-->
    <section class="VentanasEMergentes">
        <!-- Modal que Muestra la informacion de el Prospecto Funcionando 25/09/2025-->
        <div class="modal fade" id="Ventana1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['FullName'];?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </div>
                        <div class="modal-body">
                            <p>Captado en </p>
                            <h2><strong><? echo $Reg['Origen']?></strong></h2>
                            <p>Fecha Alta </p>
                            <h2><strong><? echo date("d-M-Y",strtotime($Reg['Alta']))?></strong></h2>
                            <p>Producto </p>
                            <h2><strong><? echo $Reg['Servicio_Interes']?></strong></h2>
                            <?
                            $Papeline = $basicas->Buscar2Campos($pros, "Nombre", "Papeline", "Pipeline", $Reg['Papeline'], "Nivel", $Reg['PosPapeline']);
                            $MaxPape = $basicas->BuscarCampos($pros, "Maximo", "Papeline", "Pipeline", $Reg['Papeline']);
                            ?>
                            <p> Estatus en el proceso de venta </p>
                            <h2><strong><? echo $Reg['Papeline']." - ".$Papeline?></strong></h2>
                            <p> Avance de la venta </p>
                            <h2><strong><? echo $Reg['PosPapeline']." de ".$MaxPape?></strong></h2>
                        </div>
                        <div class="modal-footer">
                            <a target="_blank" rel="noopener noreferrer" class="btn btn-primary mr-2"
                            href="https://api.whatsapp.com/send?phone=+52<?php echo $Reg['NoTel']; ?>&text=Hola mi nombre es <?php echo $nomVd; ?> te contacto debido a que te interesaron nuestros productos de KASU">
                                Whatsapp
                            </a>
                            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
                                <input type="hidden" name="IdVendedor" value="<?php echo $_POST['IdVendedor']; ?>" />
                                <input type="hidden" name="IdPros" value="<?php echo $Reg['Id']; ?>" />
                                <input type="submit" name="ArmaPres" class="btn btn-primary mr-2" value="Presupuesto" />
                                <input type="submit" name="Cancelar" class="btn btn-danger" value="Cancelar" />
                            </form>
                        </div>
                </div>
            </div>
        </div>
        <!-- Modal que envia Presupuesto de Venta -->
        <div class="modal fade" id="Ventana3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel"> Presupuesto de Venta</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        </div>
                    <? require_once 'html/Presupuesto.php'; ?>
                </div>
            </div>
        </div>
        <!-- Modal que Registra un Nuevo Prospecto Funcionando 25/09/2025 -->
        <div class="modal fade" id="Ventana2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <? require_once 'html/NvoProspecto.php'; ?>
                </div>
            </div>
        </div>
    </section>
    <!-- Start: Login Form Clean -->
    <div class="principal">
        <div class="d-flex align-items-center py-2 pe-3">
            <!-- Título centrado -->
            <h4 class="flex-grow-1 text-center mb-0">Prospectos Asignados</h4>

            <!-- Botón registrar prospecto -->
            <form class="BtnSocial m-0" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <input type="hidden" name="Host" value="<?php echo $_SERVER['PHP_SELF']; ?>">
                <label for="400" title="Crear nuevo prospecto" class="btn mb-0" 
                        style="background: #F7DC6F; color: #000;">
                    <i class="material-icons">person_add</i>
                </label>
                <input id="400" type="submit" name="CreaProsp" style="display: none;" />
            </form>
            <p>&nbsp&nbsp&nbsp</p>
        </div>
        <hr>
    </div>
    <section class="container"  style="width: 99%;">
            <div class="form-group">
                <div class="table-responsive" >
                   <?PHP
                    if($Niv >= 5){
                        //Buscamos el Id de el empleado
                        $Vende = $basicas->BuscarCampos($mysqli,"Id","Empleados","IdUsuario",$_SESSION["Vendedor"]);
                        //Crear consulta
                        $Ventas = "SELECT * FROM prospectos WHERE Asignado = '".$Vende."' AND Cancelacion = 0";
                        //Realiza consulta
                        if ($resultado = $pros -> query($Ventas)){
                        // obtener el array de objetos
                            while ($fila = $resultado -> fetch_row()) {
                                printf("
                                    <form method='POST' action='".$_SERVER['PHP_SELF']."'>
                                        <input type='number' name='IdProspecto' style='display:  none;' value='%s' />
                                        <input type='text' name='StatusVta' style='display:  none;' value='%s' />
                                        <span class='new badge blue %s' style='position: relative;padding: 0px;width: 100px;top: 20px;'>%s</span>
                                        <input type='submit' id='%s' name='SelPros' class='%s' value='%s' />
                                    </form>
                                ",$fila[0],$fila[9],$fila[9],$fila[9],$fila[9],$fila[9],$fila[4]);
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
                            $Ventas = "SELECT * FROM prospectos WHERE Asignado = '".$Resd5["Id"]."' AND Cancelacion = 0";
                                //Realiza consulta
                                if ($resultado = $pros -> query($Ventas)){
                                // obtener el array de objetos
                                    while ($fila = $resultado -> fetch_row()) {
                                      printf("
                                        <form method='POST' action='".$_SERVER['PHP_SELF']."'>
                                            <input type='number' name='IdProspecto' style='display:  none;' value='%s' />
                                            <input type='text' name='StatusVta' style='display:  none;' value='%s' />
                                            <input type='text' name='IdVendedor' style='display:  none;' value='".$Resd5["IdUsuario"]."' />
                                            <span class='new badge blue %s' style='position: relative;padding: 0px;width: 100px;top: 20px;'>%s</span>
                                            <input type='submit' id='%s' name='SelPros' class='%s' value='%s - ".$Resd5["IdUsuario"]." - $NomSuc' />
                                        </form>
                                      ",$fila[0],$fila[9],$fila[9],$fila[9],$fila[9],$fila[9],$fila[4]);
                                    }
                                }
                            }
                    }elseif($Niv == 1){
                        //Buscamos el id de la sucursal
                        $IdSuc = $basicas->BuscarCampos($mysqli,"Sucursal","Empleados","IdUsuario",$_SESSION["Vendedor"]);
                        //Buscamos el nombre de la sucursal
                        $NomSuc = $basicas->BuscarCampos($mysqli,"NombreSucursal","Sucursal","Id",$IdSuc);
                        //Crear consulta
                        $sqal = "SELECT Id, IdUsuario FROM Empleados WHERE Nombre != 'Vacante' AND Nivel >= '$Niv'";
                        //Realiza consulta
                        $r4e9s = $mysqli->query($sqal);
                        //Si existe el registro se asocia en un fetch_assoc
                        foreach ($r4e9s as $Resd5){
                            //Crear consulta
                            $Ventas = "SELECT * FROM prospectos WHERE Asignado = '".$Resd5["Id"]."' AND Cancelacion = 0";
                            //Realiza consulta
                            if ($resultado = $pros -> query($Ventas)){
                            // obtener el array de objetos
                                while ($fila = $resultado -> fetch_row()) {
                                  printf("
                                      <form method='POST' action='".$_SERVER['PHP_SELF']."'>
                                          <input type='number' name='IdProspecto' style='display:  none;' value='%s' />
                                          <input type='text' name='StatusVta' style='display:  none;' value='%s' />
                                          <input type='text' name='IdVendedor' style='display:  none;' value='".$Resd5["IdUsuario"]."' />
                                          <span class='new badge blue %s' style='position: relative;padding: 0px;width: 100px;top: 20px;'>%s</span>
                                          <input type='submit' id='%s' name='SelPros' class='%s' value='%s - ".$Resd5["IdUsuario"]." - $NomSuc' />
                                      </form>
                                  ",$fila[0],$fila[9],$fila[9],$fila[9],$fila[9],$fila[9],$fila[4]);
                                }
                            }
                        }
                    }
                    ?>
                </div>
            </div>
    <br><br><br><br>
    </section>
    <!-- JS (una sola versión) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
    <script src="Javascript/fingerprint-core-y-utils.js"></script>
    <script src="Javascript/finger.js"></script>
    <script src="Javascript/localize.js"></script>
    <!-- Abrir modal solo si corresponde -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        <?php if (!empty($Lanzar)) : ?>
        $('<?php echo $Lanzar; ?>').modal('show');
        <?php endif; ?>
    });
    </script>
</body>
</html>
