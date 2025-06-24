<?php
//indicar que se inicia una sesion
  session_start();
//inlcuir el archivo de funciones
  require_once '../eia/librerias.php';
  //Variables de los periodos
  $FechIni = date("d-m-Y", strtotime('first day of this month'));
  $FechFin = date("d-m-Y");

// Validar si existe la sesión y redireccionar
if (!isset($_SESSION["Vendedor"])) {
    header('Location: https://kasu.com.mx/login');
    exit; // Siempre es recomendable
}

// Obtener IdEmpleado desde POST o GET (o null si no existe)
$IdEmpleado = $_POST['IdEmpleado'] ?? $_GET['IdEmpleado'] ?? null;

if ($IdEmpleado !== null) {
    // SE separa el $_POST/$_GET para seleccionar la Ventana
    $Vtn = substr($IdEmpleado, 0, 1);
    $Cte = substr($IdEmpleado, 1, 5);

    // Realizamos la consulta
    $venta = "SELECT * FROM Empleados WHERE Id = '".$Cte."'";
    $res = mysqli_query($mysqli, $venta);

    // Si existe el registro se asocia en un fetch_assoc
    if ($Reg = mysqli_fetch_assoc($res)) {
        // Aquí puedes trabajar con los datos de $Reg
    }

    $Ventana = "Ventana".$Vtn;
} else {
    // Si no recibes IdEmpleado, puedes definir Ventana como vacío
    $Ventana = "";
    // O manejar un mensaje de error si lo necesitas
    // echo "No se recibió el parámetro IdEmpleado.";
}
  //Buscamos el Id de la venta
  $Vende = $basicas->BuscarCampos($mysqli,"Id","Empleados","IdUsuario",$_SESSION["Vendedor"]);
  //Seleccionamos el nivel de el usuario
  $Nivel = $basicas->BuscarCampos($mysqli,"Nivel","Empleados","IdUsuario",$_SESSION["Vendedor"]);
  //Buscamos el correo del usuario
  if (isset($Reg) && is_array($Reg)) {
    $Email = $basicas->BuscarCampos($mysqli,"Mail","Contacto","Id",$Reg["IdContacto"]);
    $Fec1hFin = date("Y-m-d",strtotime($FechFin));
    $Fec1hIni = date("Y-m-d",strtotime($FechIni));
    $PagosPerio = $basicas->SumarFechas($mysqli,"Cantidad","Comisiones_pagos","IdVendedor",$Reg['IdUsuario'],"fechaRegistro",$Fec1hFin,"fechaRegistro",$Fec1hIni);
    $Cont = rand(1, 9);
    $RefDepo = hash('adler32', $FechFin."-".$Reg['IdUsuario']."-".$Cont);
    $Cuenta = $Reg["Cuenta"];
} else {
    // Opcional: Puedes definir valores vacíos o mostrar un mensaje
    $Email = "";
    $PagosPerio = 0;
    $RefDepo = "";
    $Cuenta = "";
    // También puedes mostrar un mensaje de error personalizado si lo necesitas
    // echo "No se encontró información del empleado.";
}
  if(!empty($_POST['CambiNivl'])){
    //Actualizar la tabla de ventas
    $basicas->ActCampo($mysqli,"Empleados","Nivel",$_POST['NvoNivel'],$_POST['IdEmpleado']);
    //actualiza el valos de una tabla  y una condicion
  }
  //Se pasan las variables POST a Variable
  if(!isset($_POST['nombre'])){
    $name = $_GET['name'];
  }else{
    $name = $_POST['nombre'];
  }
  //Redireccionamos a la descarga de el contrato de el cliente
  if(!empty($_GET['Add'])){
      header('Location: https://kasu.com.mx/login/Generar_PDF/Contrato_Ejecutivo_pdf.php?Add='.$_GET['Add']);
  }
  //alertas de correo electronico
  require_once 'php/Selector_Emergentes_Ml.php';
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0,user-scalable=no">
        <title>Empleados</title>
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
            <!-- Pagar comisiones de un periodo -->
            <div class="modal fade" id="Ventana1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">

                      <div class="modal-content">
                          <form method="POST" action="php/Funcionalidad_Empleados.php">
                              <div class="modal-header">
                                  <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['Nombre'];?></h5>
                                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                              </div>
                              <div class="modal-body">
                                  <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                                  <input type="text" name="name" value="<?PHP echo $name;?>" style="display: none;">
                                  <input type="text" name="IdEmpleado" value="<?PHP echo $Reg['IdUsuario'];?>" style="display: none;">
                                  <input type="text" name="Banco" placeholder="Banco" value="<?PHP echo $Cuenta;?>" style="display: none;">
                                  <input type="text" name="Referencia" placeholder="Referencia" value="<?PHP echo $RefDepo;?>" style="display: none;">
                                  <p>Comisiones generadas del</p>
                                  <h2><strong><? echo $FechIni; ?></strong> al <strong><? echo $FechFin; ?></strong></h2>
                                  <p>Saldo a pagar</p>
                                  <h2><strong> <p>$<? echo number_format($_POST['Saldo'], 2); ?></p></strong></h2>
                                  <p>Clabe Registrada</p>
                                  <h2><strong><? echo $Cuenta; ?></strong></h2>
                                  <p>Referencia del pago </p>
                                  <h2><strong><? echo $RefDepo; ?></strong></h2>
                                  <label for="exampleFormControlSelect1">Cantidad a pagar</label>
                                  <input class="form-control" type="number" name="Cantidad" placeholder="Cantidad" required>
                              </div>
                              <div class="modal-footer">
                                  <?
                                  if($_POST['Saldo'] >= 1){
                                    echo '<input type="submit" name="PagoCom" class="btn btn-primary" value="Pagar Comisiones">';
                                  }
                                  ?>
                              </div>
                          </form>
                      </div>
                  </div>
              </div>
            <!-- Reeasignar el ejecutivo a otro superior-->
            <div class="modal fade" id="Ventana3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                          <form method="POST" action="php/Funcionalidad_Empleados.php">
                              <div class="modal-header">
                                  <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['Nombre'];?></h5>
                                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                              </div>
                              <div class="modal-body">
                                <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                                <input type="text" name="name" value="<?PHP echo $name;?>" style="display: none;">
                                <input type="number" name="IdEmpleado" value="<?PHP echo $Reg['Id'];?>" style="display: none;">
                                <p>Este ejecutivo esta asignado a</p>
                                <p>
                                  <strong>
                                    <?PHP
                                    if($Reg['Equipo'] == ""){
                                      echo "Sistema";
                                    }else{
                                      $Id = $basicas->BuscarCampos($mysqli,"Id","Empleados","Id",$Reg['Equipo']);
                                      //Consulta para encontrar al lider
                                      $lid = "SELECT * FROM Empleados WHERE Id = '".$Id."'";
                                      //Realiza consulta
                                      $rds = mysqli_query($mysqli, $lid);
                                      //Si existe el registro se asocia en un fetch_assoc
                                      if($dis=mysqli_fetch_assoc($rds)){
                                        $Sucur = $basicas->BuscarCampos($mysqli,"nombreSucursal","Sucursal","Id",$dis['Sucursal']);
                                        $Stats = $basicas->BuscarCampos($mysqli,"NombreNivel","Nivel","Id",$dis['Nivel']);
                                        echo $dis['Nombre']." - ".$Stats." - ".$Sucur;
                                      }
                                    }
                                    //Comparativo de registro de nivel para reasignar
                                    if($Reg['Nivel'] >= 5){
                                      $nue = 4;
                                    }else{
                                      $nue = $Reg['Nivel']--;
                                    }
                                    ?>
                                  </strong>
                                </p>
                                <label for="exampleFormControlSelect1">Selecciona a quien se asignara</label>
                                <select class="form-control" name="NvoVend">
                                <?
                                  //$Rivel = $basicas->BuscarCampos($mysqli,"Nivel","Empleados","IdUsuario",$_SESSION["Vendedor"]);
                                  //Se crea la consulta para los vendedores
                                  $sql9 = "SELECT * FROM Empleados WHERE Nivel = '$nue' AND Nombre != 'Vacante'";
                                  $S629 = $mysqli->query($sql9);
                                  while($S635= mysqli_fetch_array($S629)){
                                    $Su2cur = $basicas->BuscarCampos($mysqli,"nombreSucursal","Sucursal","Id",$S635['Sucursal']);
                                    $St2ats = $basicas->BuscarCampos($mysqli,"NombreNivel","Nivel","Id",$S635['Nivel']);
                                    echo "<option value='".$S635['Id']."'>".$S635['Nombre']." - ".$St2ats." - ".$Su2cur."</option>";
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
            <!-- Crear empleado-->
            <div class="modal fade" id="Ventana4" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                          <form method="POST" action="php/Funcionalidad_Empleados.php">
                              <div class="modal-header">
                                  <h5 class="modal-title" id="exampleModalLabel">Registrar Nuevo Empleado</h5>
                                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                              </div>
                              <div class="modal-body">
                                <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                                <input type="text" name="name" value="<?PHP echo $name;?>" style="display: none;">
                                <label> Nombre</label>
                                <input class="form-control" type="text" name="Nombre" value="" required>
                                <label> Telefono</label>
                                <input class="form-control" type="text" name="Telefono" value="" required>
                                <label> Email</label>
                                <input class="form-control" type="text" name="Email" value="" required>
                                <label> Direccion</label>
                                <input class="form-control" type="text" name="Direccion" value="" required>
                                <label> Cuenta Bancaria</label>
                                <input class="form-control" type="number" name="Cuenta" value="" required>
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
                                <label> Puesto </label>
                                <select class="form-control" name="Nivel" required>
                                <?
                                  //Se crea la consulta para los vendedores
                                  $sql2 = "SELECT * FROM Nivel WHERE Id >= '".$Nivel."'";
                                  $S622 = $mysqli->query($sql2);
                                  while($S632= mysqli_fetch_array($S622)){
                                    echo "<option value='".$S632['Id']."'>".$S632['NombreNivel']."</option>";
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
                                    $Su2cur3 = $basicas->BuscarCampos($mysqli,"nombreSucursal","Sucursal","Id",$S633['Sucursal']);
                                    $St2ats3 = $basicas->BuscarCampos($mysqli,"NombreNivel","Nivel","Id",$S633['Nivel']);
                                    echo "<option value='".$S633['Id']."'>".$S633['Nombre']." - ".$St2ats3." - ".$Su2cur3."</option>";
                                  }
                                ?>
                                </select>
                              </div>
                              <div class="modal-footer">
                                  <input type="submit" name="CreaEmpl" class="btn btn-primary" value="Crear Empleado">
                              </div>
                          </form>
                      </div>
                  </div>
            </div>
            <!--Reenviar contraseña-->
            <div class="modal fade" id="Ventana5" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form method="POST" action="php/Funcionalidad_Empleados.php">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Reenviar contraseña</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </div>
                            <div class="modal-body">
                              <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                              <input type="text" name="name" value="<?PHP echo $name;?>" style="display: none;">
                              <input type="text" name="IdUsuario" value="<?PHP echo $Reg['IdUsuario'];?>" style="display: none;">
                              <input type="number" name="Id" value="<?PHP echo $Reg['Id'];?>" style="display: none;">
                              <input type="text" name="Nombre" value="<?PHP echo $Reg['Nombre'];?>" style="display: none;">
                              <input type="mail" name="Email" value="<?PHP echo $Email;?>" style="display: none;">
                                <label> Nombre</label>
                                <input class="form-control" disabled type="text" name="Nombre" value="<?php echo $Reg['Nombre'];?>">
                                <label> Email</label>
                                <input class="form-control" disabled type="text" name="Email" value="<?php echo $Email;?>">
                              </select>
                            </div>
                            <div class="modal-footer">
                                <input type="submit" name="ReenCOntra" class="btn btn-primary" value="Reenviar contraseña">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Dar de baja al ejecutivo -->
            <div class="modal fade" id="Ventana6" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <form method="POST" action="php/Funcionalidad_Empleados.php">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['Nombre'];?></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </div>
                            <div class="modal-body">
                                <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                                <input type="text" name="name" value="<?PHP echo $name;?>" style="display: none;">
                                <input type="number" name="IdEmpleado" value="<?PHP echo $Reg['Id'];?>" style="display: none;">
                                <label for="exampleFormControlSelect1">Selecciona el motivo de la baja</label>
                                <select class="form-control" name="MotivoBaja" required>
                                    <option value="Renuncia">Renuncia</option>
                                    <option value="Robo">Robo a la empresa</option>
                                    <option value="Despido">Despido</option>
                                    <option value="Abandono">Abandono de Trabajo</option>
                                    <option value="actas">Acumulacion de actas adimistrativas</option>
                                    <option value="Rendimiento">Bajo Rendimiento</option>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <input type="submit" name="BajaEmp" class="btn btn-primary" value="Dar de Baja">
                            </div>
                        </form>
                      </div>
                  </div>
              </div>
            <!-- Generar o descargar Estado de cuenta de el Ejecutivo -->
            <div class="modal fade" id="Ventana7" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <form method="POST" action="<?PHP echo $_SERVER['PHP_SELF'];?>">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['Nombre'];?></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </div>
                            <div class="modal-body">
                              <input type="text" name="nombre" value="<?PHP echo $name;?>" style="display: none;">
                              <input type="number" name="IdEmpleado" value="<?PHP echo $Reg['Id'];?>" style="display: none;">
                              <p>Este colaborador tiene el status</p>
                              <p><strong><?PHP
                                              echo $basicas->BuscarCampos($mysqli,"NombreNivel","Nivel","Id",$Reg['Nivel']);
                                        ?>
                              </strong></p>
                              <label for="exampleFormControlSelect1">Selecciona que puesto se le asignara</label>
                              <select class="form-control" name="NvoNivel">
                              <?
                                //Se crea la consulta para los vendedores
                                $Rivel = $basicas->BuscarCampos($mysqli,"Nivel","Empleados","IdUsuario",$_SESSION["Vendedor"]);
                                $sql9 = "SELECT * FROM Nivel WHERE Id >= ".$Rivel;
                                $S629 = $mysqli->query($sql9);
                                while($S635= mysqli_fetch_array($S629)){
                                  echo "<option value='".$S635['Id']."'>".$S635['NombreNivel']."</option>";
                                }
                              ?>
                              </select>
                              <br>
                            </div>
                            <div class="modal-footer">
                                <input type="submit" name="CambiNivl" class="btn btn-primary" value="Cambiar el ejecutivo">
                            </div>
                      </form>
                      </div>
                  </div>
              </div>
        </section>
        <section name="impresion de datos finales">
            <?
            if(empty($name)){
              echo  "<h2><strong>No se ha seleccionado ningun Colaborador</strong></h2>";
            }
            ?>
            <div class="d-flex flex-row-reverse" >
                  <div class="p-2">
                  <?
                  $ids = uniqid(); // O cualquier valor único que desees
                  echo "
                      <form method='POST' action='".$_SERVER['PHP_SELF']."'>
                          <!-- Crear nuevo Empleado -->
                          <input type='text' name='nombre' value='".$name."' style='display: none;'>
                          <label for='4".$ids."' title='Crear nuevo Empleado' class='btn' style='background: #F7DC6F; color: #F8F9F9;' ><i class='material-icons'>person_add</i></label>
                          <input id='4".$ids."' type='submit' value='4".$ids."' name='IdEmpleado' class='hidden' style='display: none ;' />
                      </form>
                  ";
                  ?>
                </div>
            </div>
            <table class="table">
                  <tr>
                      <th>Nombre Empleado</th>
                      <th>Lider</th>
                      <th>Nivel</th>
                      <th>Sucursal</th>
                      <th>Com.Vtas</th>
                      <th>Com.Distr</th>
                      <th>Acciones</th>
                  </tr>
              <?
              if(!empty($name)){
                $buscar = $basicas->BLikes($mysqli, "Empleados", "Nombre", $name);
                foreach ($buscar as $row){
                    // ----------- FILTRO PARA NO MOSTRAR VACANTES -----------
                    if (trim($row['Nombre']) == 'Vacante') {
                        continue; // Salta esta fila y sigue con la siguiente
                    }
                    // -------------------------------------------------------
                    //Se restan los pagos de las comisiones a las comisiones generadas
                    $sj1 = $basicas->Sumar1cond($mysqli,"ComVtas","Comisiones","IdVendedor",$row['IdUsuario']);
                    $sj2 = $basicas->Sumar1cond($mysqli,"ComCob","Comisiones","IdVendedor",$row['IdUsuario']);
                    $tj = $basicas->Sumar1cond($mysqli,"Cantidad","Comisiones_pagos","IdVendedor",$row['IdUsuario']);
                    //Se restan los valores
                    $sj = $sj1+$sj2;
                    $Saldo = $sj-$tj;
                    if($Saldo <= 0){
                        $Saldo = 0;
                    }
                    /************************************* seccion de comiisones por contacto ******************************************/
                    $Niv =  $basicas->BuscarCampos($mysqli,"Nivel","Empleados","IdUsuario",$row['IdUsuario']);
                    //Buscamos la comision de el usuario
                    $PorCom = $basicas->BuscarCampos($mysqli,"N".$Niv,"Comision","Id",2);
                    //Reducimos el porcentaje a centecimas
                    $as = $PorCom/100;
                    //aterrizamos la fecha de ayer
                    $Ayer = date("Y-m-d",strtotime(date("Y-m-d").'-1 day'));
                    //Buscamos mi finger print
                    $IdContacto = $basicas->BuscarCampos($mysqli,"IdContacto","Empleados","IdUsuario",$row['IdUsuario']);
                    //Buscamos el fingerprint de el usuario
                    $IdFing = $basicas->Max2Dat($mysqli,"Id","Eventos","Evento","Ingreso","Contacto",$IdContacto);
                    //Obtenemos el fingerprint
                    $Fingerprint = $basicas->BuscarCampos($mysqli,"IdFInger","Eventos","Id",$IdFing);
                    //Crear consulta
                    $sqal2 = "SELECT * FROM Eventos WHERE Evento = 'Tarjeta' AND IdFInger != '".$Fingerprint."' AND Usuario = '".$row["IdUsuario"]."' AND FechaRegistro >= $FechIni";
                    //Realiza consulta
                    $r4e9s2 = $mysqli->query($sqal2);
                    $ComGenHoy = 0;
                    //Si existe el registro se asocia en un fetch_assoc
                    foreach ($r4e9s2 as $Resd52){
                        //Obnemos el producto de cada cupon
                        $Prducto = $basicas->Buscar2Campos($mysqli,"Producto","PostSociales","Id",$Resd52["Cupon"],"Tipo","Art");
                        //Buscamos el valor de la comision sobre la venta segun el nivel
                        $ComGen = $basicas->BuscarCampos($mysqli,"comision","Productos","Producto",$Prducto);
                        //Calculamos la comision degun el nivel
                        $Comis = $ComGen*$as;
                        //Selector de pago de comisiones
                        if($Prducto == "Universidad"){
                            //Comisiones por universitario
                            $Comis = $Comis/2500;
                        }elseif($Prducto == "Retiro"){
                            //Comisiones por Retiro
                            $Comis = $Comis/1000;
                        }else{
                            //Comisiones por funerario
                            $Comis = $Comis/100;
                        }
                        //solo cuenta una vez por dia
                        $CatLeid = $basicas->Cuenta1Fec1Cond($mysqli,"Eventos","IdFInger",$Resd52["IdFInger"],"Usuario",$row['IdUsuario'],"FechaRegistro",$Ayer);
                        if($CatLeid == 1){
                            //Sumamos las comisiones para obtener el general
                            $ComGenHoy = $ComGenHoy+$Comis;
                        }
                    }
                    $NvoSal = $Saldo+$ComGenHoy;
                    //Se busca si el cliente ya esta no debe nada
                    echo "
                    <tr>
                        <th>".$row['Nombre']."</th>
                        <th>".$basicas->BuscarCampos($mysqli,"IdUsuario","Empleados","Id",$row['Equipo'])."</th>
                        <th>".$basicas->BuscarCampos($mysqli,"NombreNivel","Nivel","Id",$row['Nivel'])."</th>
                        <th>".$basicas->BuscarCampos($mysqli,"nombreSucursal","Sucursal","Id",$row['Sucursal'])."</th>
                        <th> $".number_format($Saldo, 2)."</th>
                        <th> $".number_format($ComGenHoy, 2)."</th>
                        <th>
                        <div style='display: flex;'>
                        <form method='POST' action='' style='padding-right: 5px;'>
                            <!-- Ver estado de Comisiones
                            <label for='0".$row['Id']."' title='Ver Estado de Cuenta de Comisiones' class='btn' style='background: #AAB7B8; color: #F8F9F9;' ><i class='material-icons'>contact_page</i></label>
                            <input type='text' value='".$row['Id']."' name='busqueda' style='display: none ;' >
                            <input id='0".$row['Id']."' type='submit' name='enviar' class='hidden' style='display: none;' />
                            -->
                        </form>
                        <form method='POST' action='".$_SERVER['PHP_SELF']."'>
                                <input type='text' name='nombre' value='".$name."' style='display: none;'>
                                <!-- Pagar comisiones del periodo -->
                                <label for='1".$row['Id']."' title='Pagar comisiones del periodo' class='btn' style='background: #58D68D; color: #F8F9F9;' ><i class='material-icons'>attach_money</i></label>
                                <input type='text' value='".$NvoSal."' name='Saldo' class='hidden' style='display: none;' />
                                <input id='1".$row['Id']."' type='submit' value='1".$row['Id']."' name='IdEmpleado' class='hidden' style='display: none;' />
                                <!-- Cambiar De supervisor al ejecutivo-->
                                <label for='3".$row['Id']."' title='Reasigna a un nuevo superior' class='btn' style='background: #AF7AC5; color: #F8F9F9;' ><i class='material-icons'>people_alt</i></label>
                                <input id='3".$row['Id']."' type='submit' value='3".$row['Id']."' name='IdEmpleado' class='hidden' style='display: none;' />
                                <!--Reenviar contraseña-->
                                <label for='5".$row['Id']."' title='Reenviar contraseña' class='btn' style='background: #3498DB; color: #F8F9F9;' ><i class='material-icons'>outbox</i></label>
                                <input id='5".$row['Id']."' type='submit' value='5".$row['Id']."' name='IdEmpleado' class='hidden' style='display: none;' />
                                <!-- Dar de baja al Vendedor -->
                                <label for='6".$row['Id']."' title='Dar de baja al Colaborador' class='btn' style='background: #E74C3C; color: #F8F9F9;' ><i class='material-icons'>cancel</i></label>
                                <input id='6".$row['Id']."' type='submit' value='6".$row['Id']."' name='IdEmpleado' class='hidden' style='display: none;' />
                                <!-- Ascender-->
                                <label for='7".$row['Id']."' title='Cambia de Puesto' class='btn' style='background: #C0392B; color: #F8F9F9;' ><i class='material-icons'>swap_vert</i></label>
                                <input id='7".$row['Id']."' type='submit' value='7".$row['Id']."' name='IdEmpleado' class='hidden' style='display: none;' />
                            </form>
                        <div>
                    </th>
                </tr>
                    ";
                }
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
