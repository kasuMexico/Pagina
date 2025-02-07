<?php
//indicar que se inicia una sesion
  session_start();
//inlcuir el archivo de funciones
  require_once '../eia/librerias.php';
  //Variables de los periodos
  $FechIni = date("d-m-Y", strtotime('first day of this month'.date()));
  $FechFin = date("d-m-Y");
//Validar si existe la session y redireccionar
  if(!isset($_SESSION["Vendedor"])){
      header('Location: https://kasu.com.mx/login');
  }else{
    //SE separa el $_POST para seleccionar la Ventana
    $Vtn = substr($_POST['IdProspecto'], 0, 1);
    $Cte = substr($_POST['IdProspecto'], 1, 5);
    //realizamos la consulta
    $venta = "SELECT * FROM prospectos WHERE Id = '".$Cte."'";
    //Realiza consulta
        $res = mysqli_query($pros, $venta);
    //Si existe el registro se asocia en un fetch_assoc
        if($Reg=mysqli_fetch_assoc($res)){
          //Busqueda de prospectos
            $ProsDos = "SELECT * FROM Distribuidores WHERE IdProspecto = ".$Reg['Id'];
          //Realiza consulta
                $ResDos = mysqli_query($pros, $ProsDos);
          //Si existe el registro se asocia en un fetch_assoc
                if($RegDos=mysqli_fetch_assoc($ResDos)){}
        }
    $Ventana = "Ventana".$Vtn;
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
        <title>Prospectos</title>
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
           <!--Inicio Creacion de las ventanas emergentes-->
           <script type='text/javascript'>
               $( document ).ready(function() {
                   $('#<?PHP echo $Ventana;?>').modal('toggle')
               });
           </script>
        </section>
            <br><br><br>
        <section name="VentanasEMergentes">
            <!-- Registrar Venta -->
            <div class="modal fade" id="Ventana1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <form method="POST" action="../eia/php/Registrar_Venta.php">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['FullName'];?></h5>
                            </div>
                            <div class="modal-body">
                                <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display:none ;">
                                <input type="text" name="FullName" value="<?PHP echo $Reg['FullName'];?>" style="display:none ;">
                                <input type="text" name="IdProspecto" value="<?PHP echo $Reg['Id'];?>" style="display:none ;">
                                <input type="text" name="Producto" value="<?PHP echo $Reg['Servicio_Interes'];?>" style="display:none ;">
                                <pre id="resultado"></pre>
                                <label for="CURP">Clave CURP</label>
                                <input class="form-control" type="text" name="CurClie" placeholder="Clave CURP" id="CurCli" maxlength="18" oninput="validarInput(this)" required>
                                <label for="Electronico">Correo Electronico</label>
                                <input class="form-control" type="email" name="Mail" placeholder="Mail" value="<?PHP echo $Reg['Email'];?>" required>
                                <label for="Telefono">Telefono</label>
                                <input class="form-control" type="number" name="Telefono" placeholder="Telefono" value="<?PHP echo $Reg['NoTel'];?>" required>
                                <label for="calle">calle</label>
                                <input class="form-control" type="text" name="calle" placeholder="calle" required>
                                <label for="numero">numero</label>
                                <input class="form-control" type="text" name="numero" placeholder="numero" required>
                                <label for="colonia">colonia</label>
                                <input class="form-control" type="text" name="colonia" placeholder="colonia" required>
                                <label for="municipio">municipio</label>
                                <input class="form-control" type="text" name="municipio" placeholder="municipio" required>
                                <label for="estado">estado</label>
                                <input class="form-control" type="text" name="estado" placeholder="estado" required>
                                <label for="Postal">codigo Postal</label>
                                <input class="form-control" type="text" name="codigo_postal" placeholder="codigo Postal" required>
                                <label for="TipServicio">Selecciona el tipo de servicio</label>
                                <select class="form-control" name='TipoServicio'>
                                  <option value='Tradicional'>Tradicional</option>
                                  <option value='Ecologico' selected>Ecologico</option>
                                  <option value='Cremacion'>Cremacion</option>
                                </select>
                                <label for="TiempPago">Selecciona el tiempo de pago</label>
                                <select class="form-control" name="Meses">
                                  <option value="0">Pago Único</option>
                                  <option value="3">3 Meses</option>
                                  <option value="6">6 Meses</option>
                                  <option value="9">9 Meses</option>
                                </select>
                                <br>
                                <div class="Legales">
                                  <p><input type="checkbox" name="Terminos" value="acepto" required> Conozca nuestros Terminos y condiciones accediendo a <a href="https://kasu.com.mx/terminos-y-condiciones.php/">kasu.com.mx/terminos-y-condiciones.php</a></p>
                                  <p><input type="checkbox" name="Aviso" value="acepto" required> Conozca nuestro Aviso de privacidad accediendo a <a href="https://kasu.com.mx/terminos-y-condiciones.php/">kasu.com.mx/Aviso-de-privacidad</a></p>
                                  <p><input type="checkbox" name="Fideicomiso" value="acepto" required> Conozca El fideicomiso KASU accediendo a <a href="#">kasu.com.mx/Fideicomiso_F0003.pdf</a></p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <input type="submit" name="RegistroMesa" class="btn btn-primary" value="Registrar Venta">'
                            </div>
                        </form>
                      </div>
                  </div>
              </div>
            <!-- Crear empleado-->
            <div class="modal fade" id="Ventana2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form method="POST" action="php/Funcionalidad_Empleados.php">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Registrar distribuidor</h5>
                                </div>
                                <div class="modal-body">
                                  <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                                  <input type="text" name="name" value="<?PHP echo $name;?>" style="display: none;">
                                  <input type="number" name="Nivel" value="7" style="display: none;">
                                  <input type="number" name="IdProspecto" value="<? echo $RegDos['IdProspecto'];?>" style="display: none;">
                                  <label> Nombre</label>
                                  <input style="display: none;" type="text" name="Nombre" value="<? echo $RegDos['name'];?>">
                                  <input class="form-control" type="text" name="Nore" value="<? echo $RegDos['name'];?>" disabled>
                                  <label> Telefono</label>
                                  <input style="display: none;" type="text" name="Telefono" value="<? echo $RegDos['Telefono'];?>">
                                  <input class="form-control" type="text" name="Tefono" value="<? echo $RegDos['Telefono'];?>" disabled>
                                  <label> Email</label>
                                  <input style="display: none;" type="text" name="Email" value="<? echo $RegDos['Mail'];?>">
                                  <input class="form-control" type="text" name="Eml" value="<? echo $RegDos['Mail'];?>" disabled>
                                  <label> Direccion</label>
                                  <input style="display: none;" type="text" name="Direccion" value="<? echo $RegDos['Direccion'];?>">
                                  <input class="form-control" type="text" name="Diccion" value="<? echo $RegDos['Direccion'];?>" disabled>
                                  <label> Cuenta Bancaria</label>
                                  <input style="display: none;" type="number" name="Cuenta" value="<? echo $RegDos['Clabe'];?>">
                                  <input class="form-control" type="number" name="enta" value="<? echo $RegDos['Clabe'];?>" disabled>
                                  <label> Sucursal </label>
                                  <select class="form-control" name="Sucursal" required>
                                  <?
                                    //Se crea la consulta para los vendedores
                                    $sql1 = "SELECT * FROM Sucursal WHERE Status = 1";
                                    $S621 = $mysqli->query($sql1);
                                    while($S631= mysqli_fetch_array($S621)){
                                      echo "<option value='".$S631['id']."'>".$S631['nombreSucursal']."</option>";
                                    }
                                  ?>
                                  </select>
                                  <label> Jefe Directo</label>
                                  <select class="form-control" name="Lider" required>
                                  <?
                                    //Se crea la consulta para los vendedores
                                    $sql3 = "SELECT * FROM Empleados WHERE Nivel >= '".$Nivel."' AND Nombre != 'Vacante'";
                                    $S623 = $mysqli->query($sql3);
                                    while($S633= mysqli_fetch_array($S623)){
                                      $Su2cur3 = Basicas::BuscarCampos($mysqli,"nombreSucursal","Sucursal","Id",$S633['Sucursal']);
                                      $St2ats3 = Basicas::BuscarCampos($mysqli,"NombreNivel","Nivel","Id",$S633['Nivel']);
                                      echo "<option value='".$S633['Id']."'>".$S633['Nombre']." - ".$St2ats3." - ".$Su2cur3."</option>";
                                    }
                                  ?>
                                  </select>
                                </div>
                                <div class="modal-footer">
                                    <input type="submit" name="CreaEmpl" class="btn btn-primary" value="Registrar distribuidor">
                                </div>
                            </form>
                        </div>
                    </div>
            </div>
            <!-- Asignar prospecto a un ejecutivo-->
            <div class="modal fade" id="Ventana3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                          <form method="POST" action="php/Registro_Prospectos.php">
                              <div class="modal-header">
                                  <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['FullName'];?></h5>
                              </div>
                              <div class="modal-body">
                                <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                                <input type="text" name="name" value="<?PHP echo $name;?>" style="display: none;">
                                <input type="text" name="IdProspecto" value="<?PHP echo $Reg['Id'];?>" style="display: none;">
                                <p>Asignar prospecto a</p>
                                <label for="exampleFormControlSelect1">Selecciona a quien se asignara</label>
                                <select class="form-control" name="NvoVend">
                                <?
                                //Buscamos el nivel de el usuario
                                $Nvl = Basicas::BuscarCampos($mysqli,'Nivel','Empleados','IdUsuario',$_SESSION["Vendedor"]);
                                //Select para la lista de prospectos a asiganar
                                if($Nvl == 1){
                                  //Se crea la consulta para los vendedores
                                  $sql9 = "SELECT * FROM Empleados WHERE Nombre != 'Vacante'";
                                }else{
                                  //Se crea la consulta para los vendedores
                                  $sql9 = "SELECT * FROM Empleados WHERE Nivel >= 5 AND Nombre != 'Vacante'";
                                }
                                  $S629 = $mysqli->query($sql9);
                                  while($S635= mysqli_fetch_array($S629)){
                                    $Su2cur = Basicas::BuscarCampos($mysqli,"nombreSucursal","Sucursal","Id",$S635['Sucursal']);
                                    $St2ats = Basicas::BuscarCampos($mysqli,"NombreNivel","Nivel","Id",$S635['Nivel']);
                                    echo "<option value='".$S635['Id']."'>".$S635['Nombre']." - ".$St2ats." - ".$Su2cur."</option>";
                                  }
                                ?>
                                </select>
                                <br>
                              </div>
                              <div class="modal-footer">
                                  <input type="submit" name="AsigVende" class="btn btn-primary" value="Asignar Prospecto">
                              </div>
                          </form>
                      </div>
                  </div>
              </div>
            <!-- Crear prospecto-->
            <div class="modal fade" id="Ventana4" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <? require_once 'html/NvoProspecto.php'; ?>
                      </div>
                  </div>
              </div>
            </div>
            <!-- Modificar datos de un prospecto-->
            <div class="modal fade" id="Ventana5" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                          <form method="POST" action="php/Registro_Prospectos.php">
                              <div class="modal-header">
                                  <h5 class="modal-title" id="exampleModalLabel"> Fecha de Alta <?PHP echo $Reg['Alta'];?></h5>
                              </div>
                              <div class="modal-body">
                                <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                                <input type="text" name="name" value="<?PHP echo $name;?>" style="display: none;">
                                <input type="text" name="IdProspecto" value="<?PHP echo $Reg['Id'];?>" style="display: none;">
                                <label for="FullName">Nombre</label>
                                <input class="form-control" type="text" name="FullName" value="<?php echo $Reg['FullName']; ?>">
                                <label for="NoTel">Telefono</label>
                                <input class="form-control" type="text" name="NoTel" value="<?php echo $Reg['NoTel']; ?>">
                                <label for="Dircc">Direccion</label>
                                <input class="form-control" type="text" name="Direccion" value="<?php echo $Reg['Direccion']; ?>">
                                <label for="Email">Email</label>
                                <input class="form-control" type="text" name="Email" value="<?php echo $Reg['Email']; ?>">
                                <label for="Servicio_Interes">Servicio de Interes => <?echo $Reg['Servicio_Interes'];?></label>
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
                      </div>
                  </div>
              </div>
            <!-- Dar de baja al prospecto -->
            <div class="modal fade" id="Ventana6" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <form method="POST" action="php/Registro_Prospectos.php">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['FullName'];?></h5>
                            </div>
                            <div class="modal-body">
                              <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                              <input type="text" name="name" value="<?PHP echo $name;?>" style="display: none;">
                              <input type="text" name="IdProspecto" value="<?PHP echo $Reg['Id'];?>" style="display: none;">
                                <label for="exampleFormControlSelect1">Selecciona el motivo de la baja</label>
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
                      </div>
                  </div>
              </div>
            <!-- Enviar correo a prospecto -->
            <div class="modal fade" id="Ventana7" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <!--Formulario para envio de correo de Bienvenida-->
                        <form action="php/Registro_Prospectos.php" method="post">
                          <div class="modal-header">
                              <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['FullName'];?></h5>
                          </div>
                          <div class="modal-body">
                            <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                            <input type="text" name="name" value="<?PHP echo $Reg['FullName'];?>" style="display: none;">
                            <input type="text" name="IdProspecto" value="<?PHP echo $Reg['Id'];?>" style="display: none;">
                              <select class="form-control" name="Asunto">
                                <option value="0">Correo a enviar:</option>
                                <?php
                                if($Reg['Servicio_Interes'] == "DISTRIBUIDOR"){
                                  $EmTit = $pros -> query ("SELECT * FROM correos WHERE Tipo = 'DISTRIBUIDOR'");
                                }else{
                                  $EmTit = $pros -> query ("SELECT * FROM correos WHERE Tipo = 'VENTA'");
                                }
                                  while ($TitMa = mysqli_fetch_array($EmTit)) {
                                    echo '<option value="'.$TitMa[Asunto].'">'.$TitMa[Seguimiento]." => ".$TitMa[Asunto].'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                            <div class="modal-footer">
                                <input type="submit" name="interno" class="btn btn-primary" value="Enviar Correo">
                            </div>
                        </form>
                      </div>
                  </div>
              </div>
            <!-- Alta de ticket atn cte-->
            <div class="modal fade" id="Ventana9" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form method="POST" action="php/Funcionalidad_Pwa.php">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['FullName'];?></h5>
                            </div>
                            <div class="modal-body">
                            <?
                            if(isset($_SESSION['Alta'])){
                              echo '
                                  <input type="number" name="IdProspecto" value="'.$Reg['Id'].'" style="display:none ;">
                                  <input type="text" name="Host" value="'.$_SERVER['PHP_SELF'].'" style="display: none;">
                                  <input type="text" name="name" value="'.$name.'" style="display: none;">
                                  <p>Cerrar servicio de atencion al cliente</p>
                                  ';
                                  $BtnAtnSer = "Registrar Cita";
                            }else{
                              echo '
                                    <input type="number" name="IdProspecto" value="'.$Reg['Id'].'" style="display:none ;">
                                    <input type="text" name="Host" value="'.$_SERVER['PHP_SELF'].'" style="display: none;">
                                    <input type="text" name="name" value="'.$name.'" style="display: none;">
                                    <label for="exampleFormControlSelect1">Origen</label>
                                    <input class="form-control" disabled type="text" name="Unico" value="'.$Reg['Origen'].'">
                                    <label for="exampleFormControlSelect1">Alta</label>
                                    <input class="form-control" disabled type="text" name="Status" value="'.$Reg['Alta'].'">
                                    <label for="exampleFormControlSelect1">Numero de Telefono</label>
                                    <input class="form-control" disabled type="text" name="Producto" value="'.$Reg['NoTel'].'">
                                    <label for="exampleFormControlSelect1">Email</label>
                                    <input class="form-control" disabled type="text" name="Producto" value="'.$Reg['Email'].'">
                                    <label for="exampleFormControlSelect1">Producto de Interes</label>
                                    <input class="form-control" disabled type="text" name="Direccion" value="'.$Reg['Servicio_Interes'].'">
                                  ';
                                  $BtnAtnSer = "Iniciar Cita telefonica";
                            }
                            ?>
                            </div>
                            <div class="modal-footer">
                                <input type="submit" name="RegistroCita" class="btn btn-primary" value="<?echo $BtnAtnSer;?>">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
        <section name="impresion de datos finales">
            <?
            if(empty($name)){
              $name = ' ';
            }
            ?>
            <div class="d-flex flex-row-reverse" >
                  <div class="p-2" style='display: flex;'>
                  <?
                  echo "
                      <form method='POST' action='".$_SERVER['PHP_SELF']."'  style='padding-right: 5px;'>
                          <!-- Crear nuevo prospecto -->
                          <input type='text' name='Host' value='".$_SERVER['PHP_SELF']."' style='display: none;'>
                          <input type='text' name='nombre' value='".$name."' style='display: none;'>
                          <label for='400' title='Crear nuevo prospecto' class='btn' style='background: #F7DC6F; color: #F8F9F9;' ><i class='material-icons'>person_add</i></label>
                          <input id='400' type='submit' value='400' name='IdProspecto' class='hidden' style='display: none ;' />
                          <!-- Descarga masiva -->
                          <input type='text' name='Host' value='".$_SERVER['PHP_SELF']."' style='display: none;'>
                          <input type='text' name='nombre' value='".$name."' style='display: none;'>
                          <label for='400' title='Descarga masiva' class='btn' style='background: #196F3D; color: #F8F9F9;' ><i class='material-icons'>arrow_downward</i></label>
                          <input id='400' type='submit' value='400' name='IdProspecto' class='hidden' style='display: none ;' />
                      </form>
                  ";
                  ?>
                </div>
            </div>
            <table class="table">
                  <tr>
                      <th>Nombre Prospecto</th>
                      <th>Sem. Activo</th>
                      <th>Servicio</th>
                      <th>Art. Env</th>
                      <th>Asignado</th>
                      <th>Acciones</th>
                  </tr>
              <?
              if($name == ' '){
                  $buscar = Basicas::BLikesD2($pros,'prospectos','FullName',$name,'Cancelacion',0,'Automatico',0);
                }else{
                  $buscar = Basicas::BLikesCan($pros,"prospectos","FullName",$name,"Cancelacion",0);
                }
                  foreach ($buscar as $row){
                  //Contamos las semanas activas
                  $Sem = strtotime($row['Alta']);
                  $HoyA = strtotime(date("Y-m-d"));
                  $CSem = $HoyA-$Sem;
                  $ContSem = $CSem/604800;
                  //COnvertir en distribuidor
                  $Distri = Basicas::BuscarCampos($pros,'Id','Distribuidores','IdProspecto',$row['Id']);
                  //Se busca si el cliente ya esta no debe nada
                        echo "
                        <tr>
                            <th>".$row['FullName']."</th>
                            <th>".round($ContSem,0)."</th>
                            <th>".$row['Servicio_Interes']."</th>
                            <th>".$row['Sugeridos']."</th>
                            <th>".Basicas::BuscarCampos($mysqli,"IdUsuario","Empleados","Id",$row['Asignado'])."</th>
                            <th>
                            <div style='display: flex;'>
                                <form method='POST' action='".$_SERVER['PHP_SELF']."'>
                                    <input type='text' name='Host' value='".$_SERVER['PHP_SELF']."' style='display: none;'>
                                    <input type='text' name='nombre' value='".$name."' style='display: none;'>
                                    <!-- Registrar Venta -->
                                    <label for='1".$row['Id']."' title='Registrar Venta' class='btn' style='background: #58D68D; color: #F8F9F9;' ><i class='material-icons'>attach_money</i></label>
                                    <input id='1".$row['Id']."' type='submit' value='1".$row['Id']."' name='IdProspecto' class='hidden' style='display: none;' />
<!--                                    Enviar prospecto a lead sales
                                    <label for='6".$row['Id']."' title='Enviar lead Sales' class='btn' style='background: #21618C; color: #F8F9F9;' ><i class='material-icons'>card_travel</i></label>
                                    <input id='6".$row['Id']."' type='submit' value='6".$row['Id']."' name='IdProspecto' class='hidden' style='display: none;' />
-->
                                    <!-- Dar de Baja al Prospecto -->
                                    <label for='6".$row['Id']."' title='Dar de Baja al Prospecto' class='btn' style='background: #E74C3C; color: #F8F9F9;' ><i class='material-icons'>cancel</i></label>
                                    <input id='6".$row['Id']."' type='submit' value='6".$row['Id']."' name='IdProspecto' class='hidden' style='display: none;' />
                                    <!-- Asignar prospecto a un ejecutivo-->
                                    <label for='3".$row['Id']."' title='Asignar prospecto a un ejecutivo' class='btn' style='background: #AF7AC5; color: #F8F9F9;' ><i class='material-icons'>people_alt</i></label>
                                    <input id='3".$row['Id']."' type='submit' value='3".$row['Id']."' name='IdProspecto' class='hidden' style='display: none;' />
                                    <!-- Enviar correo electronico-->
                                    <label for='7".$row['Id']."' title='Enviar correo electronico' class='btn' style='background: #EB984E; color: #F8F9F9;' ><i class='material-icons'>send_to_mobile</i></label>
                                    <input id='7".$row['Id']."' type='submit' value='7".$row['Id']."' name='IdProspecto' class='hidden' style='display: none;' />
                                    <!-- Cambiar los datos del cliente -->
                                    <label for='5".$row['Id']."' title='Cambiar los datos del Prospecto' class='btn' style='background: #AAB7B8; color: #F8F9F9;' ><i class='material-icons'>badge</i></label>
                                    <input id='5".$row['Id']."' type='submit' value='5".$row['Id']."' name='IdProspecto' class='hidden' style='display: none;' />
                                    ";
                                    //Buscamos si el prospecto ya esta registrado con datos completos
                                    if(!empty($Distri)){
                                      echo "
                                      <!-- Convertir en distribuidor-->
                                      <label for='2".$row['Id']."' title='Convertir en distribuidor' class='btn' style='background: #58D68D; color: #F8F9F9;' ><i class='material-icons'>verified_user</i></label>
                                      <input id='2".$row['Id']."' type='submit' value='2".$row['Id']."' name='IdProspecto' class='hidden' style='display: none;' />
                                      ";
                                    }
                                    //Cuando el prospecto genero una cita
                                    if(!empty(Basicas::Buscar2Campos($pros,'Id','citas','IdProspecto',$row['Id'],'FechaCita',date("Y-m-d")))){
                                    echo "
                                        <!-- Ticket de Atencion al cliente -->
                                        <label for='9".$row['Id']."' title='Ticket de Atencion al cliente' class='btn' style='background: #F39C12; color: #F8F9F9;' ><i class='material-icons'>phone_locked</i></label>
                                        <input id='9".$row['Id']."' type='submit' value='9".$row['Id']."' name='IdProspecto' class='hidden' style='display: none;' />
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
        <script type="text/javascript" src="../eia/javascript/validarcurp.js"></script>
        <script type="text/javascript" src="Javascript/finger.js"></script>
        <!--script type="text/javascript" src="Javascript/localize.js"></script-->
    </body>
</html>
