<?php
//indicar que se inicia una sesion
  session_start();
//inlcuir el archivo de funciones
  require_once '../eia/librerias.php';
//Validar si existe la session y redireccionar
  if(!isset($_SESSION["Vendedor"])){
      header('Location: https://kasu.com.mx/login');
  }else{
    //realizamos la consulta
    $venta = "SELECT * FROM Empleados WHERE IdUsuario = '".$_SESSION["Vendedor"]."'";
    //Realiza consulta
        $res = mysqli_query($mysqli, $venta);
    //Si existe el registro se asocia en un fetch_assoc
        if($Reg=mysqli_fetch_assoc($res)){
          //Seleccion de Usuarios por nivel del usuario
          $Vende = $Reg['Nivel'];
          //realizamos la consulta
          $ContC = "SELECT * FROM Contacto WHERE Id = '".$Reg["IdContacto"]."'";
          //Realiza consulta
              $ResCt = mysqli_query($mysqli, $ContC);
          //Si existe el registro se asocia en un fetch_assoc
              if($RegCt=mysqli_fetch_assoc($ResCt)){
              }
        }
  }
  if(isset($_GET['Msg'])){
    $Mens = base64_decode($_GET['Msg']);
  	echo "<script type='text/javascript'>
  						alert('".$Mens."');
  				</script>";
  }
  if(!empty($_POST['RepDat'])){
    $Ventana = "Ventana1";
  }elseif(!empty($_POST['ActDatos'])){
    $Ventana = "Ventana2";
  }
  //alertas de correo electronico
  require_once 'php/Selector_Emergentes_Ml.php';
?>
<!DOCTYPE html>
<html lang="ES">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0,user-scalable=no">
        <title>herramientas</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
        <!-- <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css'> -->
        <link href='https://fonts.googleapis.com/icon?family=Material+Icons' rel='stylesheet'>
        <link rel="stylesheet" href="assets/css/styles.min.css">
        <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
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
        <section class="VentanasEmergenes">
          <!--Inicio Creacion de las ventanas emergentes-->
          <script type='text/javascript'>
              $( document ).ready(function() {
                  $('#<?PHP echo $Ventana;?>').modal('toggle')
              });
          </script>
          <!-- Ventana de modificacion de datos -->
          <div class="modal fade" id="Ventana2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form method="POST" action="php/Funcionalidad_Empleados.php">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Actualizar mis Datos</h5>
                            </div>
                            <div class="modal-body">
                              <input type="number" name="IdContact" value="<?PHP echo $RegCt['id'];?>" style="display:none ;">
                              <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                              <input type="text" name="name" value="<?PHP echo $name;?>" style="display: none;">
                              <label for="exampleFormControlSelect1">Nombre</label>
                              <input class="form-control" disabled type="text" name="Nombre" value="<?php echo $Reg['Nombre'];?>">
                              <label for="exampleFormControlSelect1">Puesto</label>
                              <input class="form-control" disabled type="text" name="Nivel" value="<?php echo $basicas->BuscarCampos($mysqli,"NombreNivel","Nivel","Id",$Reg['Nivel']);?>">
                              <label for="exampleFormControlSelect1">Clabe Bancaria</label>
                              <input class="form-control" disabled type="text" name="Cuenta" value="<?php echo $Reg['Cuenta'];?>">
                              <label for="exampleFormControlSelect1">Direccion</label>
                              <input class="form-control" type="text" name="Direccion" value="<?php echo $RegCt['Direccion']; ?>">
                              <label for="exampleFormControlSelect1">Telefono</label>
                              <input class="form-control" type="text" name="Telefono" value="<?php echo $RegCt['Telefono']; ?>">
                              <label for="exampleFormControlSelect1">Email</label>
                              <input class="form-control" type="text" name="Mail" value="<?php echo $RegCt['Mail']; ?>">
                            </div>
                            <div class="modal-footer">
                                <input type="submit" name="CamDat" class="btn btn-primary" value="Modificar Datos">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
          <!-- Ventana de envio de reporte de datos -->
          <div class="modal fade" id="Ventana1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                          <form method="POST" action="php/Funcionalidad_Empleados.php">
                              <div class="modal-header">
                                  <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['Nombre'];?></h5>
                              </div>
                              <div class="modal-body">
                                <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                                <div class="form-group">
                                  <label for="exampleFormControlTextarea1">¿Que problema tuviste?</label>
                                  <textarea class="form-control" id="exampleFormControlTextarea1"  name="problema" rows="3" placeholder="Se lo mas especifico que puedas al describir el problema"></textarea>
                                </div>
                              </div>
                              <div class="modal-footer">
                                  <input type="submit" name="Reporte" class="btn btn-primary" value="Enviar Reporte">
                              </div>
                          </form>
                      </div>
                  </div>
              </div>
        </section>
        <section class="container"  style="width: 99%;">
          <br>
          <div class='mw-100'>
                <?
                if($Vende == 1){
                  echo "
                    <!-- Busca empleados para hacer acciones con ellos -->
                    <form method='POST' action='Mesa_Empleados.php'>
                        <div class='input-group mb-3'>
                            <input  type='text'  class='form-control' name='nombre' id ='nombre' placeholder='Nombre de el Colaborador ' />
                            <div class='input-group-append'>
                                <button class='btn btn-outline-secondary' type='submit' name='action' value='buscar'>Buscar</button>
                            </div>
                        </div>
                    </form>
                    <hr>
                    <!-- Busca prospectos por status -->
                    <form method='POST' action='Mesa_Clientes.php'>
                        <div class='input-group mb-3'>
                            <select class='form-control' name='Status'>
                              <option value='0'>Buscar cliente por Status</option>
                              <option value='COBRANZA'>COBRANZA</option>
                              <option value='ACTIVO'>ACTIVO</option>
                              <option value='ACTIVACION'>ACTIVACION</option>
                              <option value='CANCELADO'>CANCELADO</option>
                              <option value='PREVENTA'>PREVENTA</option>
                              <option value='FALLECIDO'>FALLECIDO</option>
                            </select>
                            <div class='input-group-append'>
                                <button class='btn btn-outline-secondary' type='submit' name='action' value='buscar'>Buscar</button>
                            </div>
                        </div>
                    </form>
                  ";
                }
                if($Vende <= 3){
                  echo "
                  <!--Buscador de clientes para acciones con el cliente-->
                  <form method='POST' action='Mesa_Clientes.php'>
                      <div class='input-group mb-3'>
                          <input  type='text'  name='nombre' id ='nombre'  class='form-control' placeholder='Buscar Cliente por nombre' />
                          <div class='input-group-append'>
                              <button class='btn btn-outline-secondary' type='submit' name='action' value='buscar'>Buscar</button>
                          </div>
                      </div>
                  </form>
                  <hr>
                  <!-- Busca prospectos para hacer acciones con ellos -->
                  <form method='POST' action='Mesa_Prospectos.php'>
                      <div class='input-group mb-3'>
                          <input  type='text'  name='nombre' id ='nombre' class='form-control' placeholder='Buscar prospecto por nombre' />
                          <div class='input-group-append'>
                              <button class='btn btn-outline-secondary' type='submit' name='action' value='buscar'>Buscar</button>
                          </div>
                      </div>
                  </form>
                  <hr>
                  ";
                  echo "
                  <!--Agregar clientes por lote-->
                  <form method='POST' action='Lote_Clientes.php' enctype='multipart/form-data'>
                      <div class='input-group mb-3'>
                          <input  type='file'  name='archivo_csv' id ='nombre'  class='form-control' placeholder='Subir archivo' />
                          <div class='input-group-append'>
                              <button class='btn btn-outline-secondary' type='submit' name='action' value='buscar'>Subir</button>
                          </div>
                      </div>
                  </form>
                  <hr>
                  ";
                }
                ?>
                <form method='POST' action='php/Funcionalidad_Pwa.php'><?
                if($Vende <= 1){
                  echo "
                  <div class='form-group'>
                      <input class='form-control form-control-sm'  type='number' name='MetaMes' value='' placeholder='Meta de colocacion del Mes de ". $meses[date(n)]."'>
                      <small id='emailHelp' class='form-text text-muted'>No agregues porcentajes ni puntos decimales</small>
                      <input class='form-control form-control-sm'  type='number' name='Normalidad' value='' placeholder='% de normalidad del Mes de ". $meses[date(n)]."'>
                      <small id='emailHelp' class='form-text text-muted'>No agregues signos de pesos o puntos decimales</small>
                  </div>
                      <input class='btn btn-secondary btn-sm btn-block'  type='submit' name='Asignar' value='Asignar Metas de Venta' >
                  <hr>
                  ";
                }
                ?>
              </form>
              <hr>
              <form method="POST" action="<? echo $_SERVER['PHP_SELF'];?>">
                  <div class="form-group">
                    <!-- Enviar reporte de plataforma-->
                    <input class='btn btn-secondary btn-sm btn-block' id="RepDat" name='RepDat' type='submit' value='Reportar un problema' />
                    <!-- Cambiar los datos del cliente -->
                    <input class='btn btn-secondary btn-sm btn-block' id="ActDatos" name='ActDatos' type='submit' value='Actualizar mis Datos' />
                  </div>
              </form>
              <form method="POST" enctype="multipart/form-data" action="php/Funcionalidad_Pwa.php">
                      <label for="subirImg" id="RegCurBen" class='btn btn-secondary btn-sm btn-block' style="display:;">Nueva Foto de Perfil</label>
                      <input type="file" id="subirImg" name="subirImg" onchange='cambiar()' onclick="OcuForCurp(this)" style="display:none">
                      <div id="info"></div>
                      <input type="submit" id="RegCurCli" class='btn btn-secondary btn-sm btn-block' name="btnEnviar" value="Cargar Foto" style="display:none;">
              </form>
              <hr>
              <!-- Formulario para registro de salida -->
              <form method="POST" action="php/Funcionalidad_Pwa.php">
                  <div class="form-group">
                      <!--Insercion de registros de Gps y fingerprint-->
                      <div id="Gps"  style="display: none;"></div>
                      <div id="FingerPrint"  style="display: none;"></div>
                      <input type="text" name="Host" value="<? echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                      <!--Insercion de registros de Gps y fingerprint-->
                      <input type="text" name="Evento" value="LogOut" style="display: none;">
                      <input type="text" name="checkdia" value="<? echo date("Y-m-d");?>" style="display: none ;">
                      <div class="Botones">
                          <input class="btn btn-success btn-sm btn-block" type="submit" value="Salir" name="Salir">
                      </div>
                  </div>
              </form>
          </div>
          <br><br><br>
        </section>
        <!-- code AHH -->
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> -->
        <script src="https://cdn.jsdelivr.net/gh/dmuy/Material-Toast/mdtoast.min.js"></script>
        <script defer type="text/javascript" src="Javascript/finger.js" defer async></script>
        <script defer type="text/javascript" src="Javascript/localize.js"></script>
        <script type="text/javascript">
        //Funcion q carga el archivo de la foto
        function cambiar(){
            var pdrs = document.getElementById('subirImg').files[0].name;
            document.getElementById('info').innerHTML = pdrs;
        }
        //Funcion que oculta y revela los botones si se ha echo click en el
        function OcuForCurp(e){
            "RegCurBen"==e.value?(
                divC=document.getElementById("RegCurBen"),
                divC.style.display="",
                divT=document.getElementById("RegCurCli"),
                divT.style.display="none"):(
                divC=document.getElementById("RegCurBen"),
                divC.style.display="none",
                divT=document.getElementById("RegCurCli"),
                divT.style.display=""
            )}
        </script>
    </body>
</html>
