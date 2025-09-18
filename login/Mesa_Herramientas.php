<?php
// Procesa el cierre de sesión ANTES de todo
if (isset($_POST['Salir'])) {
    session_start();
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: https://kasu.com.mx/login');
    exit;
}

session_start();
require_once '../eia/librerias.php';

// Validar si existe la sesión y redireccionar
if (!isset($_SESSION["Vendedor"])) {
    header('Location: https://kasu.com.mx/login');
    exit;
} else {
    $venta = "SELECT * FROM Empleados WHERE IdUsuario = '".$_SESSION["Vendedor"]."'";
    $res = mysqli_query($mysqli, $venta);
    if ($Reg = mysqli_fetch_assoc($res)) {
        $Vende = $Reg['Nivel'];
        $ContC = "SELECT * FROM Contacto WHERE Id = '".$Reg["IdContacto"]."'";
        $ResCt = mysqli_query($mysqli, $ContC);
        if ($RegCt = mysqli_fetch_assoc($ResCt)) {}
    }
}

if(isset($_GET['Msg'])){
    $Mens = base64_decode($_GET['Msg']);
    echo "<script type='text/javascript'>alert('".$Mens."');</script>";
}
if(!empty($_POST['RepDat'])){
    $Ventana = "Ventana1";
} elseif(!empty($_POST['ActDatos'])){
    $Ventana = "Ventana2";
}
require_once 'php/Selector_Emergentes_Ml.php';
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,user-scalable=no">
    <title>herramientas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link href='https://fonts.googleapis.com/icon?family=Material+Icons' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
    <script src='https://code.jquery.com/jquery-3.3.1.slim.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js'></script>
    <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js'></script>
</head>
<body>
    <section id="Menu">
        <?php require_once 'html/Menuprinc.php'; ?>
    </section>
    <section class="VentanasEmergenes">
        <script type='text/javascript'>
            $(document).ready(function() {
                $('#<?php echo isset($Ventana) ? $Ventana : ''; ?>').modal('toggle');
            });
        </script>
        <!-- Modal Actualizar Datos -->
        <div class="modal fade" id="Ventana2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="php/Funcionalidad_Empleados.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Actualizar mis Datos</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </div>
                        <div class="modal-body">
                            <input type="number" name="IdContact" value="<?php echo isset($RegCt['id']) ? $RegCt['id'] : ''; ?>" style="display:none;">
                            <input type="text" name="Host" value="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: none;">
                            <input type="text" name="name" value="<?php echo isset($name) ? $name : ''; ?>" style="display: none;">
                            <label>Nombre</label>
                            <input class="form-control" disabled type="text" name="Nombre" value="<?php echo isset($Reg['Nombre']) ? $Reg['Nombre'] : ''; ?>">
                            <label>Puesto</label>
                            <input class="form-control" disabled type="text" name="Nivel" value="<?php echo isset($basicas) && isset($Reg['Nivel']) ? $basicas->BuscarCampos($mysqli, "NombreNivel", "Nivel", "Id", $Reg['Nivel']) : ''; ?>">
                            <label>Clabe Bancaria</label>
                            <input class="form-control" disabled type="text" name="Cuenta" value="<?php echo isset($Reg['Cuenta']) ? $Reg['Cuenta'] : ''; ?>">
                            <label>Direccion</label>
                            <input class="form-control" type="text" name="Direccion" value="<?php echo isset($RegCt['Direccion']) ? $RegCt['Direccion'] : ''; ?>">
                            <label>Telefono</label>
                            <input class="form-control" type="text" name="Telefono" value="<?php echo isset($RegCt['Telefono']) ? $RegCt['Telefono'] : ''; ?>">
                            <label>Email</label>
                            <input class="form-control" type="text" name="Mail" value="<?php echo isset($RegCt['Mail']) ? $RegCt['Mail'] : ''; ?>">
                        </div>
                        <div class="modal-footer">
                            <input type="submit" name="CamDat" class="btn btn-primary" value="Modificar Datos">
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Modal Reporte de Problema -->
        <div class="modal fade" id="Ventana1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="php/Funcionalidad_Empleados.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel"><?php echo isset($Reg['Nombre']) ? $Reg['Nombre'] : ''; ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </div>
                        <div class="modal-body">
                            <input type="text" name="Host" value="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: none;">
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
    <!-- Menu de Descripcion-->
    <div class="principal">
        <div class="d-flex align-items-center py-2 pe-3">
            <!-- Título centrado -->
            <h4 class="flex-grow-1 text-center mb-0">Herramientas</h4>
        </div>
        <hr>
    </div>
    <section class="container"  style="width: 99%;">
        <br>
        <div class='mw-100'>
            <?php
            if($Vende == 1){
                echo "
                    <form method='POST' action='Mesa_Empleados.php'>
                        <div class='input-group mb-3'>
                            <input  type='text'  class='form-control' name='nombre' id ='nombre' placeholder='Nombre de el Colaborador ' />
                            <div class='input-group-append'>
                                <button class='btn btn-outline-secondary' type='submit' name='action' value='buscar'>Buscar</button>
                            </div>
                        </div>
                    </form>
                    <hr>
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
                    <hr>
                ";
            }
            if($Vende <= 3){
                echo "
                  <form method='POST' action='Mesa_Clientes.php'>
                      <div class='input-group mb-3'>
                          <input  type='text'  name='nombre' id ='nombre'  class='form-control' placeholder='Buscar Cliente por nombre' />
                          <div class='input-group-append'>
                              <button class='btn btn-outline-secondary' type='submit' name='action' value='buscar'>Buscar</button>
                          </div>
                      </div>
                  </form>
                  <hr>
                  <form method='POST' action='Mesa_Prospectos.php'>
                      <div class='input-group mb-3'>
                          <input  type='text'  name='nombre' id ='nombre' class='form-control' placeholder='Buscar prospecto por nombre' />
                          <div class='input-group-append'>
                              <button class='btn btn-outline-secondary' type='submit' name='action' value='buscar'>Buscar</button>
                          </div>
                      </div>
                  </form>
                  <hr>
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
            <form method='POST' action='php/Funcionalidad_Pwa.php'>
            <?php
            if($Vende <= 1){
                echo "
                <div class='form-group'>
                    <input class='form-control form-control-sm'  type='number' name='MetaMes' value='' placeholder='Meta de colocacion del Mes de ". $meses[date('n')]."'>
                    <small id='emailHelp' class='form-text text-muted'>No agregues porcentajes ni puntos decimales</small>
                    <input class='form-control form-control-sm'  type='number' name='Normalidad' value='' placeholder='% de normalidad del Mes de ". $meses[date('n')]."'>
                    <small id='emailHelp' class='form-text text-muted'>No agregues signos de pesos o puntos decimales</small>
                </div>
                    <input class='btn btn-secondary btn-sm btn-block'  type='submit' name='Asignar' value='Asignar Metas de Venta' >
                <hr>
                ";
            }
            ?>
            </form>
            <hr>
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <div class="form-group">
                    <input class='btn btn-secondary btn-sm btn-block' id="RepDat" name='RepDat' type='submit' value='Reportar un problema' />
                    <input class='btn btn-secondary btn-sm btn-block' id="ActDatos" name='ActDatos' type='submit' value='Actualizar mis Datos' />
                </div>
            </form>
            <form method="POST" enctype="multipart/form-data" action="php/Funcionalidad_Pwa.php">
                <label for="subirImg" id="RegCurBen" class='btn btn-secondary btn-sm btn-block' >Nueva Foto de Perfil</label>
                <input type="file" id="subirImg" name="subirImg" onchange='cambiar()' onclick="OcuForCurp(this)" style="display:none">
                <div id="info"></div>
                <input type="submit" id="RegCurCli" class='btn btn-secondary btn-sm btn-block' name="btnEnviar" value="Cargar Foto" style="display:none;">
            </form>
            <hr>
            <!-- Formulario para registro de salida (SALIR) -->
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <div class="form-group">
                    <div id="Gps"  style="display: none;"></div>
                    <div id="FingerPrint"  style="display: none;"></div>
                    <input type="text" name="Host" value="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: none;">
                    <input type="text" name="Evento" value="LogOut" style="display: none;">
                    <input type="text" name="checkdia" value="<?php echo date('Y-m-d'); ?>" style="display: none;">
                    <div class="Botones">
                        <input class="btn btn-success btn-sm btn-block" type="submit" value="Salir" name="Salir">
                    </div>
                </div>
            </form>
        </div>
        <br><br><br>
    </section>
    <script src="https://cdn.jsdelivr.net/gh/dmuy/Material-Toast/mdtoast.min.js"></script>
    <script defer src="Javascript/finger.js"></script>
    <script defer src="Javascript/localize.js"></script>
    <script type="text/javascript">
    function cambiar(){
        var pdrs = document.getElementById('subirImg').files[0].name;
        document.getElementById('info').innerHTML = pdrs;
    }
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
