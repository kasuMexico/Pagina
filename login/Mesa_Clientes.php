<?php
//indicar que se inicia una sesion
  session_start();
//inlcuir el archivo de funciones
  require_once '../eia/librerias.php';
//Validar si existe la session y redireccionar
  if(!isset($_SESSION["Vendedor"])){
      header('Location: https://kasu.com.mx/login');
  }else{
    //SE separa el $_POST para seleccionar la Ventana
    $Vtn = substr($_POST['IdCliente'], 0, 1);
    $Cte = substr($_POST['IdCliente'], 1, 5);
    //realizamos la consulta
    $venta = "SELECT * FROM Venta WHERE Id = '".$Cte."'";
    //Realiza consulta
      $res = mysqli_query($mysqli, $venta);
    //Si existe el registro se asocia en un fetch_assoc
      if($Reg=mysqli_fetch_assoc($res)){
        //Se obtiene el monto de la deuda
        $Pago1 = Financieras::Pago($mysqli,$Cte);
        $Pago = money_format('%.2n', $Pago1);
        //Se obtiene el numero de pagos pendientes
        $PagoPend = Financieras::PagosPend($mysqli,$Cte);
        //Saldo de la cuenta
        $Saldo = Financieras::SaldoCredito($mysqli,$Cte);
        $Saldo = money_format('%.2n', $Saldo);
      }
      //Buscamos los datos de contacto de el clientes
      $sql1 = "SELECT * FROM Usuario WHERE IdContact = '".$Reg['IdContact']."'";
      //Realiza consulta de la tabla Usuarios
      $recs1 = mysqli_query($mysqli, $sql1);
        //Si existe el registro se asocia en un fetch_assoc
        if($Recg1=mysqli_fetch_assoc($recs1)){
        }
      //Buscamos los datos de contacto de el clientes
      $sql = "SELECT * FROM Contacto WHERE Id = '".$Reg['IdContact']."'";
        //Realiza consulta
        $recs = mysqli_query($mysqli, $sql);
        //Si existe el registro se asocia en un fetch_assoc
        if($Recg=mysqli_fetch_assoc($recs)){
        }
    $Ventana = "Ventana".$Vtn;
    //Seleccion de Usuarios por nivel del usuario
    $Vende = $basicas->BuscarCampos($mysqli,"Nivel","Empleados","IdUsuario",$_SESSION["Vendedor"]);
  }
  //Actualizacion de datos
  if(!empty($_POST['CambiVend'])){
      //Actualizar la tabla de ventas
      $basicas->ActCampo($mysqli,"Venta","Usuario",$_POST['NvoVend'],$_POST['IdVenta']);
      //actualiza el valos de una tabla  y una condicion
      $basicas->ActTab($mysqli,"PromesaPago","User",$_POST['NvoVend'],"IdVta",$_POST['IdVenta']);
      //se actualizan los Pagos
      $basicas->ActTab($mysqli,"Pagos","Usuario",$_POST['NvoVend'],"IdVenta",$_POST['IdVenta']);
  }elseif(!empty($_POST['CancelaCte'])){
      //Actualizar PARA Cancelar el Producto
      $basicas->ActCampo($mysqli,"Venta","Status","CANCELADO",$_POST['IdVenta']);
  }
  //Se pasan las variables POST a Variable
  if(!isset($_POST['nombre'])){
    $name = $_GET['name'];
  }else{
    $name = $_POST['nombre'];
  }
  //alertas de correo electronico
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
            <div class="MenuPrincipal">
            <a class="BtnMenu" href="Pwa_Principal.php"><img src="assets/img/FlorKasu.png"></a>
            <a class="BtnMenu" href="Mesa_Herramientas.php"><img src="assets/img/ajustes.png" style="background: #A9D0F5;"></a>
           </div>
        </section>
            <br><br><br>
        <section name="VentanasEMergentes">
            <!--Inicio Creacion de las ventanas emergentes-->
            <script type='text/javascript'>
                $( document ).ready(function() {
                    $('#<?PHP echo $Ventana;?>').modal('toggle')
                });
            </script>
            <!-- Registrar pago -->
            <div class="modal fade" id="Ventana1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <? require 'html/EmPago.php'; ?>
                      </div>
                  </div>
              </div>
            <!-- REgistrar promesa de pago -->
            <div class="modal fade" id="Ventana2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                          <form method="POST" action="php/Funcionalidad_Pwa.php">
                              <div class="modal-header">
                                  <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['Nombre'];?></h5>
                              </div>
                              <div class="modal-body">
                                  <?
                                    $mora = money_format('%.2n', Financieras::Mora($Pago1));
                                    if(empty($_POST['Promesa'])){
                                        echo '
                                        <p>Pagos pendientes <strong> '.$PagoPend.' Pagos</strong></p>
                                        <p>Pago Normal periodo <strong>'.$Pago.'</strong></p>
                                        <p>Pago con Mora <strong>'.$mora.'</strong></p>
                                        ';
                                    }else{
                                        echo '
                                        <p>Promesa de pago <strong>'.money_format('%.2n',$_POST['Promesa']).'</strong></p>
                                        ';
                                    }
                                  ?>
                                  <div id="Gps" style="display: none;"></div>
                                  <input type="number" name="IdVenta" value="<?PHP echo $Reg['Id'];?>" style="display: none;">
                                  <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                                  <input type="text" name="name" value="<?PHP echo $name;?>" style="display: none;">
                                  <input type="text" name="Status" value="<?PHP echo $Reg['Status'];?>" style="display: none;">
                                  <label for="exampleFormControlSelect1">Promesa de Pago</label>
                                  <input class="form-control" type="date" name="Promesa" value="<? echo date("Y-m-d",strtotime("+ 14 days"));?>"required>
                                  <label for="exampleFormControlSelect1">Cantidad a pagar</label>
                                  <input class="form-control" type="number" name="Cantidad" placeholder="Cantidad" required>
                              </div>
                              <div class="modal-footer">
                                  <input type="submit" name="PromPago" class="btn btn-primary" value="Registrar promesa de pago">
                              </div>
                          </form>
                      </div>
                  </div>
              </div>
            <!-- Reasignar el cliente a otro asesor-->
            <div class="modal fade" id="Ventana3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                          <form method="POST" action="<?PHP echo $_SERVER['PHP_SELF'];?>">
                              <div class="modal-header">
                                  <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['Nombre'];?></h5>
                              </div>
                              <div class="modal-body">
                                <input type="number" name="IdVenta" value="<?PHP echo $Reg['Id'];?>" style="display: none;">
                                <input type="text" name="nombre" value="<?PHP echo $name;?>" style="display: none;">
                                <input type="text" name="Status" value="<?PHP echo $_POST['Status'];?>" style="display: none;">
                                <p>Este cliente esta asignado a</p>
                                <p><strong><?PHP
                                              $Niv =  $basicas->BuscarCampos($mysqli,"Nivel","Empleados","IdUsuario",$_SESSION["Vendedor"]);
                                              if($Reg['Usuario'] == "SISTEMA"){
                                                echo $Reg['Usuario'];
                                                $Niv = 4;
                                                $sql = "SELECT * FROM Empleados WHERE Nivel >= $Niv AND Nombre != 'Vacante' ";
                                              }elseif($Niv == 1){
                                                $UsrPro = $basicas->BuscarCampos($mysqli,"Nombre","Empleados","IdUsuario",$Reg['Usuario']);
                                                if(empty($UsrPro)){
                                                  echo "Sin Asignar";
                                                }else{
                                                  echo $UsrPro;
                                                }
                                                //Se busca la sucursal de el actual y nivel de el usuario
                                                $Suc =  $basicas->BuscarCampos($mysqli,"Sucursal","Empleados","IdUsuario",$Reg['Usuario']);
                                                $sql = "SELECT * FROM Empleados WHERE Nombre != 'Vacante'";
                                              }else{
                                                echo $basicas->BuscarCampos($mysqli,"Nombre","Empleados","IdUsuario",$Reg['Usuario']);
                                                //Se busca la sucursal de el actual y nivel de el usuario
                                                $Suc =  $basicas->BuscarCampos($mysqli,"Sucursal","Empleados","IdUsuario",$Reg['Usuario']);
                                                $Niv =  $basicas->BuscarCampos($mysqli,"Nivel","Empleados","IdUsuario",$Reg['Usuario']);
                                                $sql = "SELECT * FROM Empleados WHERE Nivel >= $Niv AND Nombre != 'Vacante' AND Sucursal = $Suc";
                                              }
                                              ?>
                                </strong></p>
                                <label for="exampleFormControlSelect1">Selecciona el nuevo Ejecutivo</label>
                                <select class="form-control" name="NvoVend">
                                <?
                                  //Se crea la consulta para los vendedores sucursales y asignados
                                  $S62 = $mysqli->query($sql);
                                  while($S63= mysqli_fetch_array($S62)){
                                    echo "<option value='".$S63['IdUsuario']."'>".
                                    $basicas->BuscarCampos($mysqli,"NombreNivel","Nivel","Id",$S63['Nivel'])." - ".
                                    $basicas->BuscarCampos($mysqli,"nombreSucursal","Sucursal","Id",$S63['Sucursal'])." - ".
                                    $S63['Nombre']."</option>";
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
            <!-- Modificar Datos -->
            <div class="modal fade" id="Ventana4" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                          <form method="POST" action="php/Funcionalidad_Pwa.php">
                              <div class="modal-header">
                                  <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['Nombre'];?></h5>
                              </div>
                              <div class="modal-body">
                                <input type="number" name="IdVenta" value="<?PHP echo $Reg['Id'];?>" style="display: none;">
                                <input type="number" name="IdContact" value="<?PHP echo $Reg['IdContact'];?>" style="display: none;">
                                <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                                <input type="text" name="name" value="<?PHP echo $name;?>" style="display: none;">
                                <label for="exampleFormControlSelect1">Id Unico</label>
                                <input class="form-control" disabled type="text" name="Unico" value="<?php echo $Reg['IdFIrma'];?>">
                                <label for="exampleFormControlSelect1">Status</label>
                                <input class="form-control" disabled type="text" name="Status" value="<?php echo $Reg['Status'];?>">
                                <label for="exampleFormControlSelect1">Producto</label>
                                <input class="form-control" disabled type="text" name="Producto" value="<?php echo $Reg['Producto'];?>">
                                <label for="exampleFormControlSelect1">Tipo de Servicio</label>
                                <input class="form-control" disabled type="text" name="Producto" value="<?php echo $Reg['TipoServicio'];?>">
                                <label for="exampleFormControlSelect1">Selecciona el Tipo de Servicio</label>
                                <select class="form-control" name="TipoServ">
                                  <option value="Tradicional">TRADICIONAL</option>
                                  <option value="Cremacion">CREMACION</option>
                                  <option value="Ecologico">ECOLOGICO</option>
                                </select>
                                <label for="exampleFormControlSelect1">Direccion</label>
                                <input class="form-control" type="text" name="Direccion" value="<?php echo $Recg['Direccion']; ?>">
                                <label for="exampleFormControlSelect1">Telefono</label>
                                <input class="form-control" type="text" name="Telefono" value="<?php echo $Recg['Telefono']; ?>">
                                <label for="exampleFormControlSelect1">Email</label>
                                <input class="form-control" type="text" name="Email" value="<?php echo $Recg['Mail']; ?>">
                              </div>
                              <div class="modal-footer">
                                  <input type="submit" name="CamDat" class="btn btn-primary" value="Modificar Datos">
                              </div>
                          </form>
                      </div>
                  </div>
              </div>
            <!-- Asignar servicio funerario al cliente -->
            <div class="modal fade" id="Ventana5" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                          <form method="POST" action="php/Funcionalidad_Pwa.php">
                              <div class="modal-header">
                                  <h5 class="modal-title" id="exampleModalLabel">Asignar servicio</h5>
                              </div>
                              <div class="modal-body">
                                  <input type="number" name="IdVenta" value="<?PHP echo $Reg['Id'];?>" style="display:none ;">
                                  <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                                  <input type="text" name="name" value="<?PHP echo $name;?>" style="display: none;">
                                  <p>Se ha realizado el servicio al cliente</p>
                                  <p><strong><?PHP echo $Reg['Nombre'];?></strong></p>
                                  <p>el servicio se asigna al prestador de servicio</p>
                                  <label for="exampleFormControlSelect1">Prestador servicios</label>
                                  <input class="form-control" type="text" name="Prestador" value="" placeholder="Prestador servicios"required>
                                  <label for="exampleFormControlSelect1">Telefono de contacto</label>
                                  <input class="form-control" type="tel" name="Telefono" value="" placeholder="Telefono de contacto"required>
                                  <label for="exampleFormControlSelect1">Estado</label>
                                  <input class="form-control" type="text" name="Estado" value="" placeholder="Estado"required>
                                  <label for="exampleFormControlSelect1">Municipio</label>
                                  <input class="form-control" type="text" name="Municipio" value="" placeholder="Municipio"required>
                                  <label for="exampleFormControlSelect1">Codigo Postal</label>
                                  <input class="form-control" type="number" name="Cp" value="" placeholder="Codigo Postal"required>
                                  <label for="exampleFormControlSelect1">Personal que Atendio</label>
                                  <input class="form-control" type="text" name="EmpFune" value="" placeholder="Personal que Atendio"required>
                                  <label for="exampleFormControlSelect1">Costo pactado</label>
                                  <input class="form-control" type="number" name="Costo" value="" placeholder="Costo pactado"required>
                              </div>
                              <div class="modal-footer">
                                  <input type="submit" name="AsiServ" class="btn btn-primary" value="Servicio Asignado">
                              </div>
                          </form>
                      </div>
                  </div>
              </div>
            <!-- Cancelacion de el Cliente -->
            <div class="modal fade" id="Ventana6" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <form method="POST" action="<?PHP echo $_SERVER['PHP_SELF'];?>">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['Nombre'];?></h5>
                            </div>
                            <div class="modal-body">
                              <input type="number" name="IdVenta" value="<?PHP echo $Reg['Id'];?>" style="display: none;">
                              <input type="text" name="nombre" value="<?PHP echo $name;?>" style="display: none;">
                              <p>Estas a punto de cancelar la venta</p>
                              <br>
                            </div>
                            <div class="modal-footer">
                                <input type="submit" name="CancelaCte" class="btn btn-primary" value="Cancelar al Cliente">
                            </div>
                        </form>
                      </div>
                  </div>
              </div>
            <!-- Generar o descargar fichas de el cliente -->
            <div class="modal fade" id="Ventana7" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                          <div class="modal-header">
                              <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['Nombre'];?></h5>
                          </div>
                          <div class="modal-body">
                            <?PHP
                             echo "
                                    <p> Producto Contratado </p><h2>".$Reg['Producto']."</h2>
                                    <p> Costo de compra </p><h2>".money_format('%.2n', $Reg['CostoVenta'])."</h2>
                                    <p> Liquidacion </p><h2>".money_format('%.2n', Financieras::SaldoCredito($mysqli,$Reg['Id'])).".</h2>
                                    <p> Status de la poliza </p><h2>".$Reg['Status']."</h2>
                                  ";
                             ?>
                          </div>
                          <div class="modal-footer">
                              <form method="POST" action="../eia/EnviarCorreo.php">
                                    <input type="number" name="IdVenta" value="<?PHP echo $Recg1['IdContact'];?>" style="display: none;">
                                    <input type="text" name="FullName" value="<?PHP echo $Recg1['Nombre'];?>" style="display: none;">
                                    <input type="text" name="Email" value="<?PHP echo $Recg['Mail'];?>" style="display: none;">
                                    <input type="text" name="Asunto" value="FICHAS DE PAGO KASU" style="display: none;">
                                    <input type="submit" name="EnviarFichas" class="btn btn-primary" value="Enviar a Fichas">
                              </form>
                              <!-- Envia a Archivo para generar polizas -->
                              <a name="GenerarFicha" class="btn btn-primary" href="https://kasu.com.mx/login/Generar_PDF/Fichas_Pago_pdf.php?Cte=<?echo base64_encode($Reg['Id']);?>">Descargar Fichas</a>
                          </div>
                      </div>
                  </div>
              </div>
            <!-- Generar o descargar poliza de el cliente -->
            <div class="modal fade" id="Ventana8" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                          <div class="modal-header">
                              <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['Nombre'];?></h5>
                          </div>
                          <div class="modal-body">
                            <?PHP
                             echo "
                                    <p> Producto Contratado </p><h2>".$Reg['Producto']."</h2>
                                    <p> Fecha de compra </p><h2>".date("d-m-Y",strtotime($Reg['FechaRegistro']))."</h2>
                                    <p> Status de la poliza </p><h2>".$Reg['Status']."</h2>
                                  ";
                             ?>
                          </div>
                          <div class="modal-footer">
                              <form method="POST" action="../eia/EnviarCorreo.php">
                                    <input type="number" name="IdVenta" value="<?PHP echo $Recg1['IdContact'];?>" style="display: none;">
                                    <input type="text" name="FullName" value="<?PHP echo $Recg1['Nombre'];?>" style="display: none;">
                                    <input type="text" name="Email" value="<?PHP echo $Recg['Mail'];?>" style="display: none;">
                                    <input type="text" name="Asunto" value="ENVIO ARCHIVO" style="display: none;">
                                    <input type="text" name="Descripcion" value="Poliza de Servicio" style="display: none;">
                                    <input type="submit" name="EnviarPoliza" class="btn btn-primary" value="Enviar a Poliza">
                              </form>
                              <a name="GenerarPoliza" class="btn btn-primary" href="https://kasu.com.mx/login/Generar_PDF/Poliza_pdf.php?busqueda=<?echo base64_encode($Reg['IdContact']);?>">Descargar a Poliza</a>
                          </div>
                      </div>
                  </div>
            </div>
            <!-- Alta de ticket atn cte-->
            <div class="modal fade" id="Ventana9" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                          <form method="POST" action="php/Funcionalidad_Pwa.php">
                              <div class="modal-header">
                                  <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['Nombre'];?></h5>
                              </div>
                              <div class="modal-body">
                              <?
                              if(isset($_SESSION['Alta'])){
                                echo '
                                    <input type="number" name="IdVenta" value="'.$Reg['Id'].'" style="display:none ;">
                                    <input type="number" name="IdContact" value="'.$Recg['id'].'" style="display:none ;">
                                    <input type="text" name="Host" value="'.$_SERVER['PHP_SELF'].'" style="display: none;">
                                    <input type="text" name="name" value="'.$name.'" style="display: none;">
                                    <p>Cerrar servicio de atencion al cliente</p>
                                    ';
                                    $BtnAtnSer = "Dar de Baja ticket";
                              }else{
                                echo '
                                      <input type="number" name="IdVenta" value="'.$Reg['Id'].'" style="display:none ;">
                                      <input type="number" name="IdContact" value="'.$Recg['id'].'" style="display:none ;">
                                      <input type="text" name="Host" value="'.$_SERVER['PHP_SELF'].'" style="display: none;">
                                      <input type="text" name="name" value="'.$name.'" style="display: none;">
                                      <label for="exampleFormControlSelect1">Id Unico</label>
                                      <input class="form-control" disabled type="text" name="Unico" value="'.$Reg['IdFIrma'].'">
                                      <label for="exampleFormControlSelect1">Status</label>
                                      <input class="form-control" disabled type="text" name="Status" value="'.$Reg['Status'].'">
                                      <label for="exampleFormControlSelect1">Producto</label>
                                      <input class="form-control" disabled type="text" name="Producto" value="'.$Reg['Producto'].'">
                                      <label for="exampleFormControlSelect1">Tipo de Servicio</label>
                                      <input class="form-control" disabled type="text" name="Producto" value="'.$Reg['TipoServicio'].'">
                                      <label for="exampleFormControlSelect1">Direccion</label>
                                      <input class="form-control" disabled type="text" name="Direccion" value="'.$Recg['Direccion'].'">
                                      <label for="exampleFormControlSelect1">Telefono</label>
                                      <input class="form-control" disabled type="text" name="Telefono" value="'.$Recg['Telefono'].'">
                                      <label for="exampleFormControlSelect1">Email</label>
                                      <input class="form-control" disabled type="text" name="Email" value="'.$Recg['Mail'].'">
                                    ';
                                    $BtnAtnSer = "Dar de alta ticket";
                              }
                              ?>
                              </div>
                              <div class="modal-footer">
                                  <input type="submit" name="AltaTicket" class="btn btn-primary" value="<?echo $BtnAtnSer;?>">
                              </div>
                          </form>
                      </div>
                  </div>
              </div>
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
              <?
              //Busqueda de clientes
              if(!empty($_POST['Status'])){
                  //Buscamos clientes por status
                  $buscar = $basicas->BLikes( $mysqli,"Venta","Status",$_POST['Status']);
              }elseif(!empty($name)){
                  //Buscamos clientes por nombre
                  $buscar = $basicas->BLikes( $mysqli,"Venta","Nombre",$name);
              }
                  foreach ($buscar as $row){
                  //Se busca si el cliente ya esta no debe nada
                        echo "
                        <tr>
                            <th>".$row['Nombre']."</th>
                            <th>".$row['Usuario']."</th>
                            <th>".$row['Status']."</th>
                            <th>".$row['Producto']."</th>
                            <th>
                            <div style='display: flex;'>
                                <form method='POST' action='Mesa_Estado_Cuenta.php' style='padding-right: 5px;'>
                                <input type='text' name='nombre' value='".$name."' style='display: none;'>
                          ";
                                if($row['Status'] == "ACTIVO" || $row['Status'] == "COBRANZA" || $row['Status'] == "CANCELADO"){
                                    echo "
                                        <!-- Ver estado de cuenta -->
                                        <label for='0".$row['Id']."' title='Ver estado de cuenta' class='btn' style='background: #F7DC6F; color: #F8F9F9;' ><i class='material-icons'>contact_page</i></label>
                                        <input type='text' value='".$row['Id']."' name='busqueda' style='display: none ;' >
                                        <input id='0".$row['Id']."' type='submit' name='enviar' class='hidden' style='display: none;' />
                                    ";
                                }
                                echo"
                                </form>
                                <form method='POST' action='".$_SERVER['PHP_SELF']."'>
                                    <input type='text' name='nombre' value='".$name."' style='display: none;'>
                                    ";
                                    if($row['Status'] == "COBRANZA"){
                                      echo "
                                          <!-- Generar Fichas -->
                                          <label for='7".$row['Id']."' title='Generar Fichas' class='btn' style='background: #EB984E; color: #F8F9F9;' ><i class='material-icons'>send_to_mobile</i></label>
                                          <input id='7".$row['Id']."' type='submit' value='7".$row['Id']."' name='IdCliente' class='hidden' style='display: none;' />
                                      ";
                                    }
                                    if($row['Status'] != "ACTIVO" AND $row['Status'] != "ACTIVACION" || $row['Status'] == "COBRANZA" AND $row['Status'] != "FALLECIDO"){
                                      echo "
                                          <!-- Agregar un pago a el cliente -->
                                          <label for='1".$row['Id']."' title='Agregar un pago a el cliente' class='btn' style='background: #58D68D; color: #F8F9F9;' ><i class='material-icons'>attach_money</i></label>
                                          <input id='1".$row['Id']."' type='submit' value='1".$row['Id']."' name='IdCliente' class='hidden' style='display: none;' />
                                          <!-- Generar una promesa de pago -->
                                          <label for='2".$row['Id']."' title='Generar una promesa de pago ' class='btn' style='background: #85C1E9; color: #F8F9F9;' ><i class='material-icons'>event</i></label>
                                          <input id='2".$row['Id']."' type='submit' value='2".$row['Id']."' name='IdCliente' class='hidden' style='display: none;' />
                                          <!-- Reasigna al cliente a un nuevo ejecutivo -->
                                          <label for='3".$row['Id']."' title='Reasigna al cliente a un nuevo ejecutivo' class='btn' style='background: #AF7AC5; color: #F8F9F9;' ><i class='material-icons'>people_alt</i></label>
                                          <input id='3".$row['Id']."' type='submit' value='3".$row['Id']."' name='IdCliente' class='hidden' style='display: none;' />
                                      ";
                                    }
                                    if($row['Status'] != "CANCELADO" AND $row['Status'] != "ACTIVO" AND $row['Status'] != "FALLECIDO"){
                                      echo "
                                          <!-- Cancela la venta -->
                                          <label for='6".$row['Id']."' title='Cancela la venta' class='btn' style='background: #E74C3C; color: #F8F9F9;' ><i class='material-icons'>cancel</i></label>
                                          <input id='6".$row['Id']."' type='submit' value='6".$row['Id']."' name='IdCliente' class='hidden' style='display: none;' />
                                      ";
                                    }
                                    if($row['Status'] != "FALLECIDO"){
                                    echo "
                                        <!-- Cambiar los datos del cliente -->
                                        <label for='4".$row['Id']."' title='Cambiar los datos del cliente' class='btn' style='background: #AAB7B8; color: #F8F9F9;' ><i class='material-icons'>badge</i></label>
                                        <input id='4".$row['Id']."' type='submit' value='4".$row['Id']."' name='IdCliente' class='hidden' style='display: none;' />
                                        <!-- Ticket de Atencion al cliente -->
                                        <label for='9".$row['Id']."' title='Ticket de Atencion al cliente' class='btn' style='background: #F39C12; color: #F8F9F9;' ><i class='material-icons'>phone_locked</i></label>
                                        <input id='9".$row['Id']."' type='submit' value='9".$row['Id']."' name='IdCliente' class='hidden' style='display: none;' />
                                    ";
                                    }
                                    if($row['Status'] == "ACTIVO"){
                                    echo "
                                        <!-- Generar Poliza -->
                                        <label for='8".$row['Id']."' title='Generar Poliza' class='btn' style='background: #5DADE2; color: #F8F9F9;' ><i class='material-icons'>feed</i></label>
                                        <input id='8".$row['Id']."' type='submit' value='8".$row['Id']."' name='IdCliente' style='display: none ;' >
                                        <!-- Asignar Servicio -->
                                        <label for='5".$row['Id']."' title='Asignar Servicio' class='btn' style='background: #273746; color: #F8F9F9;' ><i class='material-icons'>account_balance</i></label>
                                        <input id='5".$row['Id']."' type='submit' value='5".$row['Id']."' name='IdCliente' class='hidden' style='display: none;' />
                                    ";
                                    }
                                    if($row['Status'] == "ACTIVACION"){
                                    echo "
                                    <!-- Generar Poliza -->
                                    <label for='8".$row['Id']."' title='Generar Poliza' class='btn' style='background: #5DADE2; color: #F8F9F9;' ><i class='material-icons'>feed</i></label>
                                    <input id='8".$row['Id']."' type='submit' value='8".$row['Id']."' name='IdCliente' style='display: none ;' >
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
        <!-- End: Login Form Clean -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
        <script type="text/javascript" src="Javascript/finger.js"></script>
        <script type="text/javascript" src="Javascript/localize.js"></script>
    </body>
</html>
