<?php
session_start();
require_once '../../eia/librerias.php';
require_once('../../vendor/autoload.php');

$txtNom_ActCur = $_POST['txtNom_ActCur'];
$txtDir_ActCur = $_POST['txtDir_ActCur'];
$txtTel_ActCur = $_POST['txtTel_ActCur'];
$txtCor_ActCur = $_POST['txtCor_ActCur'];
$txtCodMai_ModVerCod = $_POST['txtCodMai_ModVerCod'];
$txtCodTel_ModVerCod = $_POST['txtCodTel_ModVerCod'];

$txt_ActCur = $_POST['txtNom_ActCur'];

$htmlVerCorTel = '<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>KASU| Actualizar</title>
    <link rel="stylesheet" type="text/css" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../../assets/css/font-awesome.css">
    <link rel="stylesheet" href="../../assets/css/templatemo-softy-pinko.css">
    <link rel="icon" href="../../assets/images/logo.png">
    <script type="text/javascript" src="js/validarcurp.js"></script>
  </head>
  <body>
    <div class="welcome-area" id="welcome">
      <div class="container">
        <div class="row">
          <div class="offset-xl-3 col-xl-6 offset-lg-2 col-lg-12 col-md-12 col-sm-12">
            <div class="features-small-item">
              <div class="consulta">
                <h5 class="features-title">Verificacion</h5>
                <form method="POST" id="ModVerCod">
                Se ha enviado un codigo a
                tu telefono:<b>'.$txtTel_ActCur.'</b> y a tu correo: <b>'.$txtCor_ActCur.'</b>,
                por favor, verificalos y
                escribelos en los campos
                correspondientes.
                <br>
                <br>
                Telefono <br>
                <input type="text" placeholder="Código" id="txtCodTel_ModVerCod" name="txtCodTel_ModVerCod" maxlength="4"><br>
                Correo <br>
                <input type="text" placeholder="Código" id="txtCodMai_ModVerCod" name="txtCodMai_ModVerCod"><br><br>
                <input style="outline: none;border: none; cursor: pointer; font-size: 15px; border-radius: 20px;
                padding: 12px 20px; background-color: #012F91; text-transform: uppercase; color: #fff;
                letter-spacing: 0.25px; -webkit-transition: all 0.3s ease 0s;-moz-transition: all 0.3s ease 0s;
                -o-transition: all 0.3s ease 0s;transition: all 0.3s ease 0s;" type="submit" value="Continuar" id="btnContAct" name="btnContAct">
                <br><small>*Sea paciente, los codigos pueden demorar un poco*</small>
                <br><small>*Algunos correos pueden llegar en su bandeja de SPAM*</small>
            </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="../../assets/js/jquery-2.1.0.min.js"></script>
  <script src="../../assets/js/popper.js"></script>
  <script src="../../assets/js/bootstrap.min.js"></script>

  <script src="../../assets/js/scrollreveal.min.js"></script>
  <script src="../../assets/js/waypoints.min.js"></script>
  <script src="../../assets/js/jquery.counterup.min.js"></script>
  <script src="../../assets/js/imgfix.min.js"></script>
  <script src="assets/js/custom.js"></script>
</body>
</html>';

$htmlVerCor = '<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>KASU| Actualizar</title>
    <link rel="stylesheet" type="text/css" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../../assets/css/font-awesome.css">
    <link rel="stylesheet" href="../../assets/css/templatemo-softy-pinko.css">
    <link rel="icon" href="../../assets/images/logo.png">
    <script type="text/javascript" src="js/validarcurp.js"></script>
  </head>
  <body>
    <div class="welcome-area" id="welcome">
      <div class="container">
        <div class="row">
          <div class="offset-xl-3 col-xl-6 offset-lg-2 col-lg-12 col-md-12 col-sm-12">
            <div class="features-small-item">
              <div class="consulta">
                <h5 class="features-title">Verificacion</h5>
                <form method="POST" id="ModVerCod">
                Se ha enviado un codigo a tu correo: <b>'.$txtCor_ActCur.'</b>,
                por favor, verificalo y
                escribelo en el campo
                correspondiente.
                <br>
                <br>
                Correo <br>
                <input type="text" placeholder="Código" id="txtCodMai_ModVerCod" name="txtCodMai_ModVerCod"><br><br>
                <input style="outline: none;border: none; cursor: pointer; font-size: 15px; border-radius: 20px;
                padding: 12px 20px; background-color: #012F91; text-transform: uppercase; color: #fff;
                letter-spacing: 0.25px; -webkit-transition: all 0.3s ease 0s;-moz-transition: all 0.3s ease 0s;
                -o-transition: all 0.3s ease 0s;transition: all 0.3s ease 0s;" type="submit" value="Continuar" id="btnContAct" name="btnContAct">
                <br><small>*Sea paciente, los codigos pueden demorar un poco*</small>
                <br><small>*Algunos correos pueden llegar en su bandeja de SPAM*</small>
            </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="../../assets/js/jquery-2.1.0.min.js"></script>
  <script src="../../assets/js/popper.js"></script>
  <script src="../../assets/js/bootstrap.min.js"></script>

  <script src="../../assets/js/scrollreveal.min.js"></script>
  <script src="../../assets/js/waypoints.min.js"></script>
  <script src="../../assets/js/jquery.counterup.min.js"></script>
  <script src="../../assets/js/imgfix.min.js"></script>
  <script src="assets/js/custom.js"></script>
</body>
</html>';

$htmlVerTel = '<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>KASU| Actualizar</title>
    <link rel="stylesheet" type="text/css" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../../assets/css/font-awesome.css">
    <link rel="stylesheet" href="../../assets/css/templatemo-softy-pinko.css">
    <script type="text/javascript" src="js/validarcurp.js"></script>
    <link rel="icon" href="../../assets/images/logo.png">
  </head>
  <body>
    <div class="welcome-area" id="welcome">
      <div class="container">
        <div class="row">
          <div class="offset-xl-3 col-xl-6 offset-lg-2 col-lg-12 col-md-12 col-sm-12">
            <div class="features-small-item">
              <div class="consulta">
                <h5 class="features-title">Verificacion</h5>
                <form method="POST" id="ModVerCod">
                Se ha enviado un codigo a
                tu telefono:<b>'.$txtTel_ActCur.'</b>,
                por favor, verificalo y
                escribelo en los campo
                correspondiente.
                <br>
                <br>
                Telefono <br>
                <input type="text" placeholder="Código" id="txtCodTel_ModVerCod" name="txtCodTel_ModVerCod" maxlength="4"><br>
                <input style="outline: none;border: none; cursor: pointer; font-size: 15px; border-radius: 20px;
                padding: 12px 20px; background-color: #012F91; text-transform: uppercase; color: #fff;
                letter-spacing: 0.25px; -webkit-transition: all 0.3s ease 0s;-moz-transition: all 0.3s ease 0s;
                -o-transition: all 0.3s ease 0s;transition: all 0.3s ease 0s;" type="submit" value="Continuar" id="btnContAct" name="btnContAct">
                <br><small>*Sea paciente, los codigos pueden demorar un poco*</small>
                <br><small>*Algunos correos pueden llegar en su bandeja de SPAM*</small>
            </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="../../assets/js/jquery-2.1.0.min.js"></script>
  <script src="../../assets/js/popper.js"></script>
  <script src="../../assets/js/bootstrap.min.js"></script>

  <script src="../../assets/js/scrollreveal.min.js"></script>
  <script src="../../assets/js/waypoints.min.js"></script>
  <script src="../../assets/js/jquery.counterup.min.js"></script>
  <script src="../../assets/js/imgfix.min.js"></script>
  <script src="assets/js/custom.js"></script>
</body>
</html>';

if(isset($_GET['c']) && !isset($_POST['btnActDatCurp'])){
  $curp = $_GET['c'];
      //Buscarmos la CURP del cliente

      /*SELECT Venta.IdContact,Usuario.ClaveCurp,Venta.IdFIrma,Contacto.Direccion,Contacto.Telefono,Venta.Producto,Venta.TipoServicio,Venta.Status, Contacto.Mail
      FROM Venta inner join Contacto on Venta.IdContact=Contacto.id inner join Usuario on Venta.IdContact=Usuario.`IdContact`
      WHERE Usuario.ClaveCurp = '$curp'*/
      $queUsrCur = $mysqli -> query("SELECT * FROM Usuario WHERE ClaveCurp = '".$curp."'");
      foreach($queUsrCur as $resUsrCur){
          $queVtaCur = $mysqli -> query("SELECT * FROM Venta WHERE IdContact = '".$resUsrCur['IdContact']."'");
          if($queVtaCur !=""){
              foreach($queVtaCur as $resVtaCur){
                  $queCntCur = $mysqli -> query("SELECT * FROM Contacto WHERE id = '".$resVtaCur['IdContact']."'");
                  foreach($queCntCur as $resCntCur){
                        $_SESSION["ActPhpFun_IdC"] = $resVtaCur['IdContact'];
                        $_SESSION["ActPhpFun_Cur"] = $curp;
                        $_SESSION["ActPhpFun_Nta"] = $resVtaCur['IdFIrma'];
                        $_SESSION["ActPhpFun_Nom"] = $resVtaCur['Nombre'];
                        $_SESSION["ActPhpFun_Dir"] = $resCntCur['Direccion'];
                        $_SESSION["ActPhpFun_Tel"] = $resCntCur['Telefono'];
                        $_SESSION["ActPhpFun_Pro"] = $resVtaCur['Producto'];
                        $_SESSION["ActPhpFun_TiS"] = $resVtaCur['TipoServicio'];
                        $_SESSION["ActPhpFun_Sts"] = $resVtaCur['Status'];
                        if($resCntCur['Mail'] == "NO APLICA"){
                            $_SESSION["ActPhpFun_Mai"] = "";
                        }else{
                            $_SESSION["ActPhpFun_Mai"] = $resCntCur['Mail'];
                        }
                        $resQueActDat = $mysqli -> query ("SELECT * FROM DatosActualizados WHERE ClaveCurp = '".$curp."'");
                        //Se genera la consulta y se guarda en un array
                        while($arrResQueAD =  mysqli_fetch_array($resQueActDat)){
                            $Aux_ActDat_Cur = $arrResQueAD['ClaveCurp'];
                            $Aux_ActDat_Nom = $arrResQueAD['nombre'];
                            $Aux_ActDat_idC = $arrResQueAD['IdContact'];
                            $Aux_ActDat_Nta = $arrResQueAD['idFIrma'];
                            $Aux_ActDat_Mai = $arrResQueAD['Email'];
                            $Aux_ActDat_Tel = $arrResQueAD['telefono'];
                            $Aux_ActDat_Dir = $arrResQueAD['Direccion'];
                            $Aux_ActDat_TiS = $arrResQueAD['TipoServicio'];
                            $Aux_ActDat_Sts = $arrResQueAD['Status'];
                            $Aux_ActDat_Pro = $arrResQueAD['producto'];
                        }
                        if($Aux_ActDat_Cur != ""){
                            //Se inicia session con los datos del cliente
                            //Si el correo es igual a NO APLICA
                            if($Aux_ActDat_Mai == "NO APLICA"){
                                //Se le asigna un valor vacio a la sesion de correo
                                $_SESSION["ActPhpFun_Mai"] = "";
                            }else{
                                //Se le asigna el valor de la busqueda
                                $_SESSION["ActPhpFun_Mai"] = $Aux_ActDat_Mai;
                            }
                            $_SESSION["ActPhpFun_IdC"] = $Aux_ActDat_idC;
                            $_SESSION["ActPhpFun_Nom"] = $Aux_ActDat_Nom;
                            $_SESSION["ActPhpFun_Cur"] = $Aux_ActDat_Cur;
                            $_SESSION["ActPhpFun_Nta"] = $Aux_ActDat_Nta;
                            $_SESSION["ActPhpFun_Sts"] = $Aux_ActDat_Sts;
                            $_SESSION["ActPhpFun_TiS"] = $Aux_ActDat_TiS;
                            $_SESSION["ActPhpFun_Dir"] = $Aux_ActDat_Dir;
                            $_SESSION["ActPhpFun_Tel"] = $Aux_ActDat_Tel;
                            $_SESSION["ActPhpFun_Pro"] = $Aux_ActDat_Pro;
                            //Redirecciona a datos.php

                          }
                  }
                }
          }else{
              echo "No se tiene registro de la Curp3";
          }
      }

      header("Refresh: 0; URL=https://kasu.com.mx/ActualizacionDatos/php/datoscurp.php");
}

$htmlIndex = '
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>KASU| Actualizar con CURP</title>
    <link rel="stylesheet" type="text/css" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../../assets/css/font-awesome.css">
    <link rel="stylesheet" href="../../assets/css/templatemo-softy-pinko.css">
    <script type="text/javascript" src="js/validarcurp.js"></script>
    <link rel="icon" href="../../assets/images/logo.png">
  </head>
  <body>
    <div class="welcome-area" id="welcome">
      <div class="container">
        <div class="row">
          <div class="offset-xl-3 col-xl-6 offset-lg-2 col-lg-12 col-md-12 col-sm-12">
            <div class="features-small-item">
              <div class="consulta">
                <h5 class="features-title">Actualizar mis datos con CURP</h5>
                <form  method="post">
                  <p>Nombre</p>
                  <input disabled type="text" name="txtNom_ActCur" value="'.$_SESSION["ActPhpFun_Nom"].'">
                  <p>Direccion</p>
                  <input type="text" name="txtDir_ActCur" value="'.$_SESSION["ActPhpFun_Dir"].'">
                  <p>Telefono</p>
                  <input type="tel" name="txtTel_ActCur" value="'.$_SESSION["ActPhpFun_Tel"].'" maxlength="10" placeholder="0000000000">
                  <p>Correo</p>
                  <input type="email" name="txtCor_ActCur" value="'.$_SESSION["ActPhpFun_Mai"].'" placeholder="correo@correo.com">
                  <br><br>
                  <input style="outline: none;border: none; cursor: pointer; font-size: 13px; border-radius: 20px;
                  padding: 12px 20px; background-color: #012F91; text-transform: uppercase; color: #fff;
                  letter-spacing: 0.25px; -webkit-transition: all 0.3s ease 0s;-moz-transition: all 0.3s ease 0s;
                  -o-transition: all 0.3s ease 0s;transition: all 0.3s ease 0s;" data-toggle="modal" data-target="#ActIndMod" type="button" value="Actualizar mis datos"><br><br>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="ActIndMod" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm">
        <div class="modal-content" style="height:auto; padding:1em;">
          <div id="reposicion">
          ¿Desea actualizar sus datos?
          <input style="outline: none;border: none; cursor: pointer; font-size: 13px; border-radius: 20px;
          padding: 12px 20px; background-color: #012F91; text-transform: uppercase; color: #fff;
          letter-spacing: 0.25px; -webkit-transition: all 0.3s ease 0s;-moz-transition: all 0.3s ease 0s;
          -o-transition: all 0.3s ease 0s;transition: all 0.3s ease 0s;" data-toggle="modal" data-target="#ActIndMod" type="submit"  id="btnActDatCurp" name="btnActDatCurp" value="Continuar">
          </div>
        </div>
      </div>
    </div>
</form>
    <script src="../../assets/js/jquery-2.1.0.min.js"></script>
    <script src="../../assets/js/popper.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>

    <script src="../../assets/js/scrollreveal.min.js"></script>
    <script src="../../assets/js/waypoints.min.js"></script>
    <script src="../../assets/js/jquery.counterup.min.js"></script>
    <script src="../../assets/js/imgfix.min.js"></script>
    <script src="assets/js/custom.js"></script>
  </body>
  </html>';


  if(isset($_POST['btnVolAct'])){
    echo $htmlIndex;
  }elseif(isset($_POST['btnSalDatCurp'])){
  header("Refresh: 0; URL=https://kasu.com.mx/ActualizacionDatos/");
  cerrarSession();
  }elseif(isset($_POST['btnActDatCurp'])){
    $msmAltAct = "";
    if($txtDir_ActCur != ""){
        if($txtTel_ActCur != ""){
            if($txtCor_ActCur != ""){
              if(strlen($txtTel_ActCur)!=10){
                echo'<script type="text/javascript">alert("El numero que ingreso no es incorrecto");</script>';
                echo $htmlIndex;
              }elseif($_SESSION["ActPhpFun_Dir"] == $txtDir_ActCur && $_SESSION["ActPhpFun_Tel"] == $txtTel_ActCur && $_SESSION["ActPhpFun_Mai"] == $txtCor_ActCur){
                echo'<script type="text/javascript">alert("No se realizaron cambios, sus datos han sido guardado");</script>';
                header("Refresh: 0; URL=https://kasu.com.mx/");
              }elseif($_SESSION["ActPhpFun_Dir"] != $txtDir_ActCur || $_SESSION["ActPhpFun_Tel"] == $txtTel_ActCur && $_SESSION["ActPhpFun_Mai"] == $txtCor_ActCur ){
                //echo "1.- Se cambio cualquier dato se enviara a un mensaje";
                $basic  = new \Nexmo\Client\Credentials\Basic('cab8e9a2','ofjWQqJgi3qJdsFZ');
                  //Se asigna a un cliente
                  $client = new \Nexmo\Client($basic);
                  //Se asignan los valores: Telefono a quien se le enviara mensaje, Nombre de la Empresa y la longitud del codigo
                  $verification = $client->verify()->start([
                      'number' => '52'.$txtTel_ActCur,
                      'brand'  => 'Kasu',
                      'code_length'  => '4']);
                  //Se guarda el id de verificacion en una variable de sesion
                  $_SESSION['idverification_ActPhpFun'] = "".$verification->getRequestId();
                  echo $htmlVerTel;
                  $tipVer = 1;
                  $_SESSION["txtMai_ActDatCli"] = $txtCor_ActCur;
                  $_SESSION["txtTel_ActDatCli"] = $txtTel_ActCur;
                  $_SESSION["txtDir_ActDatCli"] = $txtDir_ActCur;
              }elseif($_SESSION["ActPhpFun_Dir"] != $txtDir_ActCur || $_SESSION["ActPhpFun_Tel"] != $txtTel_ActCur && $_SESSION["ActPhpFun_Mai"] == $txtCor_ActCur){
                //echo "2.- Se cambio cualquier dato se enviara a un mensaje";
                $basic  = new \Nexmo\Client\Credentials\Basic('cab8e9a2','ofjWQqJgi3qJdsFZ');
                  //Se asigna a un cliente
                  $client = new \Nexmo\Client($basic);
                  //Se asignan los valores: Telefono a quien se le enviara mensaje, Nombre de la Empresa y la longitud del codigo
                  $verification = $client->verify()->start([
                      'number' => '52'.$txtTel_ActCur,
                      'brand'  => 'Kasu',
                      'code_length'  => '4']);
                  //Se guarda el id de verificacion en una variable de sesion
                  $_SESSION['idverification_ActPhpFun'] = "".$verification->getRequestId();
                   echo $htmlVerTel;
                  $tipVer = 2;
                  $_SESSION["txtMai_ActDatCli"] = $txtCor_ActCur;
                  $_SESSION["txtTel_ActDatCli"] = $txtTel_ActCur;
                  $_SESSION["txtDir_ActDatCli"] = $txtDir_ActCur;
              }elseif($_SESSION["ActPhpFun_Dir"] != $txtDir_ActCur || $_SESSION["ActPhpFun_Tel"] == $txtTel_ActCur && $_SESSION["ActPhpFun_Mai"] != $txtCor_ActCur){
                //echo "3.- Se cambio cualquier dato se enviara un correo";
                $codigoMai_ActDatCli = rand(1000,9999);
                //Llama las las funciones necesarias para enviar un correo
                require_once('../../eia/php/Funciones_kasu.php');
                // asigna los valores obtenidos en del metodo post, a las variables de sesion
                //Llama la funcion para generar el mensaje HTML para que el cliente reciba su codigo
                $maiil = Correo::Mensaje('Codigo de verificacion',$_SESSION["ActPhpFun_Nom"],$codigoMai_ActDatCli,'','','','','','','','','','','','','','','','');
                //Se envia el correo con sus los datos: Nombre, Correo, Asunto, mensaje(HTML)
                Correo::EnviarCorreo($_SESSION["ActPhpFun_Nom"],$txtCor_ActCur ,'Codigo de verificacion',$maiil);
                $_SESSION['codigoMai_ActDatCli'] = $codigoMai_ActDatCli;
                $_SESSION["txtMai_ActDatCli"] = $txtCor_ActCur;
                $_SESSION["txtTel_ActDatCli"] = $txtTel_ActCur;
                $_SESSION["txtDir_ActDatCli"] = $txtDir_ActCur;
                echo $htmlVerCor;
                $tipVer = 3;
              }elseif($_SESSION["ActPhpFun_Dir"] != $txtDir_ActCur || $_SESSION["ActPhpFun_Tel"] != $txtTel_ActCur && $_SESSION["ActPhpFun_Mai"] != $txtCor_ActCur){
                //echo "4.-Se cambio cualquier dato se enviara a un mensaje y un correo";
                $codigoMai_ActDatCli = rand(1000,9999);
                //Llama las las funciones necesarias para enviar un correo
                require_once('../../eia/php/Funciones_kasu.php');
                // asigna los valores obtenidos en del metodo post, a las variables de sesion
                //Llama la funcion para generar el mensaje HTML para que el cliente reciba su codigo
                $maiil = Correo::Mensaje('Codigo de verificacion',$_SESSION["ActPhpFun_Nom"],$codigoMai_ActDatCli,'','','','','','','','','','','','','','','','');
                //Se envia el correo con sus los datos: Nombre, Correo, Asunto, mensaje(HTML)
                Correo::EnviarCorreo($_SESSION["ActPhpFun_Nom"],$txtCor_ActCur ,'Codigo de verificacion',$maiil);
                $basic  = new \Nexmo\Client\Credentials\Basic('cab8e9a2','ofjWQqJgi3qJdsFZ');
                //Se asigna a un cliente
                $client = new \Nexmo\Client($basic);
                //Se asignan los valores: Telefono a quien se le enviara mensaje, Nombre de la Empresa y la longitud del codigo
                $verification = $client->verify()->start([
                    'number' => '52'.$txtTel_ActCur,
                    'brand'  => 'Kasu',
                    'code_length'  => '4']);
                //Se guarda el id de verificacion en una variable de sesion
                $_SESSION['idverification_ActPhpFun'] = "".$verification->getRequestId();
                $_SESSION['codigoMai_ActDatCli'] = $codigoMai_ActDatCli;

                 echo $htmlVerCorTel;
                $tipVer = 4;
                $_SESSION["txtMai_ActDatCli"] = $txtCor_ActCur;
                $_SESSION["txtTel_ActDatCli"] = $txtTel_ActCur;
                $_SESSION["txtDir_ActDatCli"] = $txtDir_ActCur;
              }else{
                echo'<script type="text/javascript">alert("No se realizaron cambios, sus datos han sido guardados");</script>';
                echo $htmlIndex;
              }
              $_SESSION['tipVer'] = $tipVer;
            }else{
                $msmAltAct = "Ingrese un correo";
            }
        }else{
            $msmAltAct = "Ingrese un telefono";
        }
    }else{
        $msmAltAct = "Ingrese una direccion";
    }

    if($msmAltAct != ""){
        echo'<script type="text/javascript">alert("'.$msmAltAct.'");</script>';
        echo $htmlIndex;
    }
}elseif(isset($_POST['btnContAct'])){
  echo $_SESSION['tipVer'];
    //Si El codigo de verificacion del telefono y el correo no estan vacios
    if($_SESSION['tipVer'] == 1 || $_SESSION['tipVer'] == 2){
        //echo "cod tel ing: ".$txtCodTel_ModVerCod." id: ".$_SESSION['idverification_ActPhpFun'];
        $basic  = new \Nexmo\Client\Credentials\Basic('cab8e9a2','ofjWQqJgi3qJdsFZ');
        $client = new \Nexmo\Client($basic);
        $verification = new \Nexmo\Verify\Verification($_SESSION['idverification_ActPhpFun']);
        $result = $client->verify()->check($verification, $txtCodTel_ModVerCod);
        $contValidar = 0;
        if($result["status"] == 6 ){
            //verificar el error de la respuesta =>proceso terminado  not found
            echo'<script type="text/javascript">alert("Proceso terminado, intenta de nuevo");</script>';
            echo $htmlVerTel;
        }
        elseif($result["status"] == 16 )
        { //verificar el error de la respuesta =>codigo invalido no match
            echo'<script type="text/javascript">alert("El PIN ingresado no coincide con nuestros registros");</script>';
            echo $htmlVerTel;
        }
        elseif($verification["status"] == 10)
        {
          echo '<script type="text/javascript">alert("No se permiten verificaciones concurrentes al mismo numero.");</script>';
          echo $htmlVerTel;
        }
        elseif($result["status"] == 17 )
        { //verificar el error de la respuesta =>pin enviado mas de 3 veces
            echo'<script type="text/javascript">alert("Has ingresado el PIN incorrecto demaciadas veces, intenta de nuevo");</script>';
            echo $htmlVerTel;
        }else{
                //Busca en la tabla Contacto el id de contacto guardado en la sesion
                //Se crea el array que contiene los datos de DatosActualizados
                $DatActual = array(
                "IdContact" => $_SESSION["ActPhpFun_IdC"],
                "ClaveCurp" =>$_SESSION["ActPhpFun_Cur"],
                "idFIrma" =>$_SESSION["ActPhpFun_Nta"],
                "nombre" =>$_SESSION["ActPhpFun_Nom"],
                "Email" =>$_SESSION["txtMai_ActDatCli"],
                "telefono" =>$_SESSION["txtTel_ActDatCli"],
                "Direccion" =>$_SESSION["txtDir_ActDatCli"],
                "TipoServicio" =>$_SESSION["ActPhpFun_TiS"],
                "producto" =>$_SESSION["ActPhpFun_Pro"],
                "Status" =>$_SESSION["ActPhpFun_Sts"],
                "actualizacion" =>"ConCurp"
                );

            //Se realiza el insert en la base de datos
              $insert = Basicas::InsertCampo($mysqli,"DatosActualizados",$DatActual);
              //Si se ejecuta algun error
              if($insert == "null"){
                  //Envia mensaje
                  echo'<script type="text/javascript">alert("'.mysqli_error($mysqli).'");</script>';
              //Si no hay errores
              }else{
                  //Enviar mensaje
                  echo'<script type="text/javascript">alert("¡Tus DATOS han sido ACTUALIZADOS!\nTe estamos enviando tu tarjeta\nGracias por actualizar tus datos");</script>';
              }
              //Redirecciona
              header("Refresh: 0; URL=https://kasu.com.mx/ActualizacionDatos/");
            cerrarSession();
        }
    }elseif($_SESSION['tipVer'] == 3){
         if($_SESSION['codigoMai_ActDatCli'] == $txtCodMai_ModVerCod ){
               //Busca en la tabla Contacto el id de contacto guardado en la sesion
               //Se crea el array que contiene los datos de DatosActualizados
            $DatActual = array(
            "IdContact" => $_SESSION["ActPhpFun_IdC"],
            "ClaveCurp" =>$_SESSION["ActPhpFun_Cur"],
            "idFIrma" =>$_SESSION["ActPhpFun_Nta"],
            "nombre" =>$_SESSION["ActPhpFun_Nom"],
            "Email" =>$_SESSION["txtMai_ActDatCli"],
            "telefono" =>$_SESSION["txtTel_ActDatCli"],
            "Direccion" =>$_SESSION["txtDir_ActDatCli"],
            "TipoServicio" =>$_SESSION["ActPhpFun_TiS"],
            "producto" =>$_SESSION["ActPhpFun_Pro"],
            "Status" =>$_SESSION["ActPhpFun_Sts"],
            "actualizacion" =>"ConCurp"
            );
              //Se realiza el insert en la base de datos
             $insert = Basicas::InsertCampo($mysqli,"DatosActualizados",$DatActual);
             //Si se ejecuta algun error
             if($insert == "null"){
                 //Envia mensaje
                 echo'<script type="text/javascript">alert("'.mysqli_error($mysqli).'");</script>';
             //Si no hay errores
             }else{
                 //Enviar mensaje
                 echo'<script type="text/javascript">alert("¡Tus DATOS han sido ACTUALIZADOS!\nSe esta enviando tu tarjeta a tu domicilio");</script>';
             }
             //Redirecciona
             header("Refresh: 0; URL=https://kasu.com.mx/ActualizacionDatos/");
           cerrarSession();
         }else{
           echo'<script type="text/javascript">alert("Su codigo no coincide");</script>';
           echo $htmlVerCor;
         }

    }elseif($_SESSION['tipVer'] == 4){
        //echo "cod tel ing: ".$txtCodTel_ModVerCod." id: ".$_SESSION['idverification_ActPhpFun'];
        $basic  = new \Nexmo\Client\Credentials\Basic('cab8e9a2','ofjWQqJgi3qJdsFZ');
        $client = new \Nexmo\Client($basic);
        $verification = new \Nexmo\Verify\Verification($_SESSION['idverification_ActPhpFun']);
        $result = $client->verify()->check($verification, $txtCodTel_ModVerCod);
        $contValidar = 0;
        if($result["status"] == 6 ){
            //verificar el error de la respuesta =>proceso terminado  not found
            echo'<script type="text/javascript">alert("Proceso terminado, intenta de nuevo");</script>';
            echo $htmlVerCorTel;
        }
        elseif($result["status"] == 16 )
        { //verificar el error de la respuesta =>codigo invalido no match
            echo'<script type="text/javascript">alert("El PIN ingresado no coincide con nuestros registros");</script>';
            echo $htmlVerCorTel;
        }
        elseif($verification["status"] == 10)
        {
          echo '<script type="text/javascript">alert("No se permiten verificaciones concurrentes al mismo numero.");</script>';
          echo $htmlVerCorTel;
        }
        elseif($result["status"] == 17 )
        { //verificar el error de la respuesta =>pin enviado mas de 3 veces
            echo'<script type="text/javascript">alert("Has ingresado el PIN incorrecto demaciadas veces, intenta de nuevo");</script>';
            echo $htmlVerCorTel;
        }else{
          $contValidar++;
         if($_SESSION['codigoMai_ActDatCli'] == $txtCodMai_ModVerCod ){
             $contValidar++;
         }
         if($contValidar == 2){
           //Si el producto viene vacio
           if($_SESSION["txtPro_ActDatCli"] == ""){
               //Busca en la tabla Contacto el id de contacto guardado en la sesion
               $resProducto = $mysqli -> query ("SELECT * FROM Contacto WHERE id = '".$_SESSION["ActPhpFun_idC"]."'");
               while($arrProducto = mysqli_fetch_array($resProducto)){
                   //Guarda el tipo de producto relacionado a id buscado
                   $auxProducto = $arrProducto[Producto];
               }
               //Se crea el array que contiene los datos de DatosActualizados
                   $DatActual = array(
                     "IdContact" => $_SESSION["ActPhpFun_idC"],
                     "ClaveCurp" =>$_SESSION["ActPhpFun_Cur"],
                     "idFIrma" =>$_SESSION["ActPhpFun_Nta"],
                     "nombre" =>$_SESSION["ActPhpFun_Nom"],
                     "Email" =>$_SESSION["txtMai_ActDatCli"],
                     "telefono" =>$_SESSION["txtTel_ActDatCli"],
                     "Direccion" =>$_SESSION["txtDir_ActDatCli"],
                     "TipoServicio" =>$_SESSION["txtTiS_ActDatCli"],
                     "producto" =>$auxProducto,
                     "Status" =>$_SESSION["ActPhpFun_Sts"],
                     "actualizacion" =>"ConCurp"

                   );
           }else{
             $DatActual = array(
               "IdContact" => $_SESSION["ActPhpFun_idC"],
               "ClaveCurp" =>$_SESSION["ActPhpFun_Cur"],
               "idFIrma" =>$_SESSION["ActPhpFun_Nta"],
               "nombre" =>$_SESSION["ActPhpFun_Nom"],
               "Email" =>$_SESSION["txtMai_ActDatCli"],
               "telefono" =>$_SESSION["txtTel_ActDatCli"],
               "Direccion" =>$_SESSION["txtDir_ActDatCli"],
               "TipoServicio" =>$_SESSION["txtTiS_ActDatCli"],
               "producto" =>$_SESSION["txtPro_ActDatCli"],
               "Status" =>$_SESSION["ActPhpFun_Sts"],
               "actualizacion" =>"ConCurp"
             );
           }
            //Se realiza el insert en la base de datos
             $insert = Basicas::InsertCampo($mysqli,"DatosActualizados",$DatActual);
             //Si se ejecuta algun error
             if($insert == "null"){
                 //Envia mensaje
                 echo'<script type="text/javascript">alert("'.mysqli_error($mysqli).'");</script>';
             //Si no hay errores
             }else{
                 //Enviar mensaje
                 echo'<script type="text/javascript">alert("¡Tus DATOS han sido ACTUALIZADOS!\nSe esta enviando tu tarjeta a tu domicilio");</script>';
             }
             //Redirecciona
             cerrarSession();
             header("Refresh: 0; URL=https://kasu.com.mx/ActualizacionDatos/");

         }else{
           echo'<script type="text/javascript">alert("Algun CODIGO esta vacio");</script>';
           echo $htmlVerCorTel;
         }
        }
    }else{
        echo'<script type="text/javascript">alert("error");</script>';
        if($_SESSION['idverification_ActPhpFun'] != ""){
          $basic  = new \Nexmo\Client\Credentials\Basic('cab8e9a2','ofjWQqJgi3qJdsFZ');
          $client = new \Nexmo\Client($basic);
          $verification = new \Nexmo\Verify\Verification($_SESSION['idverification_ActPhpFun']);
          $result = $client->verify()->check($verification, $txtCodTel_ModVerCod);
        }
    }
}elseif($_SESSION["ActPhpFun_Cur"] == ""){
    header("Refresh: 0; URL=https://kasu.com.mx/ActualizacionDatos?stat=1");
    cerrarSession();
  }else{
  echo $htmlIndex;
}

function cerrarSession(){
    unset($_SESSION["ActPhpFun_IdC"]);
    unset($_SESSION["ActPhpFun_Nom"]);
    unset($_SESSION["ActPhpFun_Cur"]);
    unset($_SESSION["ActPhpFun_Nta"]);
    unset($_SESSION["ActPhpFun_Sts"]);
    unset($_SESSION["ActPhpFun_TiS"]);
    unset($_SESSION["ActPhpFun_Dir"]);
    unset($_SESSION["ActPhpFun_Tel"]);
    unset($_SESSION["ActPhpFun_Mai"]);
    unset($_SESSION["ActPhpFun_Pro"]);
    unset($_SESSION["codigoMai_ActDatCli"]);
    unset($_SESSION["codigoTel_ActDatCli"]);
    unset($_SESSION['tipVer']);
}

?>
