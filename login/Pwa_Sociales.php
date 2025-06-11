<?php
//indicar que se inicia una sesion
session_start();
//inlcuir el archivo de funciones
require_once '../eia/librerias.php';
//Validar si existe la session y redireccionar
    if(!isset($_SESSION["Vendedor"])){
        header('Location: https://kasu.com.mx/login');
      }else{
        $Niv =  $basicas->BuscarCampos($mysqli,"Nivel","Empleados","IdUsuario",$_SESSION["Vendedor"]);
        //Buscamos la comision de el usuario
        $PorCom = $basicas->BuscarCampos($mysqli,"N".$Niv,"Comision","Id",2);
      }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>PostSociales</title>
    <!-- CODELAB: Add meta theme-color -->
    <meta name="theme-color" content="#2F3BA2"/>
    <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
    <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons'>
    <link rel="stylesheet" href="/login/assets/css/styles.min.css">
    <link rel="stylesheet" href="/login/assets/css/cupones.css">
</head>
<body>
  <!--onload="localize();"-->
  <!--Inicio de menu principal fijo-->
    <section id="Menu">
      <?require_once 'html/Menuprinc.php';?>
    </section>
    <!-- Start: Login Form Clean -->
    <section class="container"  style="width: 99%;">
        <div class="form-group">
            <h2>Sociales</h2>
            <hr>
        </div>
        <?
        $a = 1;
        $b = $basicas->Max1Dat($mysqli,"Id","PostSociales","Tipo","Vta");
        //Contamos los registros que existen
        while ($a <= 6) {
            //Buscamos 10 codigos en forma random
            $c = rand(1,$b);
            //Si es par imprimimos los div de bloques
            if($a%2!=0){
              echo '
                <div class="card-body">
                <div class="row">
              ';
            }
            // Generamos los cupones
            $venta = "SELECT * FROM PostSociales WHERE Id = '".$c."' AND Status = 1 AND Tipo = 'Vta'";
            //Realiza consulta
            $res = mysqli_query($mysqli, $venta);
            //Si existe el registro se asocia en un fetch_assoc
            if($Reg=mysqli_fetch_assoc($res)){
              //Construmos la clave de el archivo
              $ClArch = $Reg['Id'].'|'.$_SESSION["Vendedor"];
              //selctor de red social
              if($Reg['Red'] == "facebook"){
                $DirPrin = "http://www.facebook.com/sharer.php?u=https://kasu.com.mx/constructor.php?datafb=";
              }elseif($Reg['Red'] == "twetter"){
                $DirPrin = "https://twitter.com/intent/tweet?text=".urlencode($Reg['DesA'])."&url=".urlencode('https://kasu.com.mx/constructor.php?datafb=');
              }
              //Buscamos el valor de la comision sobre la venta segun el nivel
              $ComGen = $basicas->BuscarCampos($mysqli,"comision","Productos","Producto",$Reg['Producto']);
              //Calculamos la comision degun el nivel
              $as = $PorCom/100;
              $Comis = $ComGen*$as;
              //Construimos la tarjeta
              echo '
              <div class="col">
                  <div class="card">
                      <div class="card-body">
                      <a class="ContCupon" href="javascript: void(0);"
                      onclick="window.open('."'".$DirPrin.base64_encode($ClArch)."','ventanacompartir', 'toolbar=0, status=0, width=650, height=500');".'">
                          <img src="https://kasu.com.mx/assets/images/cupones/'.$Reg['Img'].'">
                      </a>
                      <a class="BtnSocial" href="javascript: void(0);"
                      onclick="window.open('."'".$DirPrin.base64_encode($ClArch)."','ventanacompartir', 'toolbar=0, status=0, width=650, height=500');".'">
                        <img src="/login/assets/img/sociales/'.$Reg['Red'].'.png">
                      </a>
                      <div class="ContCupon">
                          <h2>
                          Com/Vta $'.number_format($Comis, 2).'
                          </h2>
                          <h3>'.$Reg['TitA'].'</h3>
                          <p>'.$Reg['DesA'].'</p>
                      </div>
                      </div>
                  </div>
              </div>
              ';
            }
          $a++;
          //Si es par imprimimos los div de bloques
          if($a%2!=0){
            echo '
                </div>
            </div>
            ';
          }
        }
        //Buscamos el final de los articulos
        $g = 1;
        $f = $basicas->Max1Dat($mysqli,"Id","PostSociales","Tipo","Art");
        $b++;
        //Contamos los registros que existen
        while ($g <= 4) {
            //Buscamos 10 codigos en forma random
            $d = rand($b,$f);
            //Si es par imprimimos los div de bloques
            if($g%2!=0){
              echo '
                <div class="card-body">
                <div class="row">
              ';
            }
            // Generamos los cupones
            $venta = "SELECT * FROM PostSociales WHERE Id = '".$d."' AND Status = 1 AND Tipo = 'Art'";
            //Realiza consulta
            $res = mysqli_query($mysqli, $venta);
            //Si existe el registro se asocia en un fetch_assoc
            if($Reg=mysqli_fetch_assoc($res)){
              //Construmos la clave de el archivo
              $ClArch = $Reg['Id'].'|'.$_SESSION["Vendedor"];
              //selctor de red social
              if($Reg['Red'] == "facebook"){
                $DirPrin = "http://www.facebook.com/sharer.php?u=https://kasu.com.mx/constructor.php?datafb=";
              }elseif($Reg['Red'] == "twetter"){
                $DirPrin = "https://twitter.com/intent/tweet?text=".urlencode($Reg['DesA'])."&url=".urlencode('https://kasu.com.mx/constructor.php?datafb=');
              }
              //Buscamos el valor de la comision sobre la venta segun el nivel
              $ComGen = $basicas->BuscarCampos($mysqli,"comision","Productos","Producto",$Reg['Producto']);
              //Calculamos la comision degun el nivel
              $as = $PorCom/100;
              $Comis = $ComGen*$as;
              //Selector de pago de comisiones
              if($Reg['Producto'] == "Universidad"){
                //Comisiones por universitario
                $Comis = $Comis/2500;
              }elseif($Reg['Producto'] == "Retiro"){
                //Comisiones por Retiro
                $Comis = $Comis/1000;
              }else{
                //Comisiones por funerario
                $Comis = $Comis/100;
              }
              //Construimos la tarjeta
              echo '
              <div class="col">
                  <div class="card">
                      <div class="card-body">
                      <a class="ContCupon" href="javascript: void(0);"
                      onclick="window.open('."'".$DirPrin.base64_encode($ClArch)."','ventanacompartir', 'toolbar=0, status=0, width=650, height=500');".'">
                          <img src="'.$Reg['Img'].'">
                      </a>
                      <a class="BtnSocial" href="javascript: void(0);"
                      onclick="window.open('."'".$DirPrin.base64_encode($ClArch)."','ventanacompartir', 'toolbar=0, status=0, width=650, height=500');".'">
                        <img src="/login/assets/img/sociales/'.$Reg['Red'].'.png">
                      </a>
                      <div class="ContCupon">
                          <h2>
                          Lectura $'.number_format($Comis, 2).'
                          </h2>
                          <h3>'.$Reg['TitA'].'</h3>
                          <p>'.$Reg['DesA'].'</p>
                          <h3>*Comision generada por usuario unico, por dia de lectura</h3>
                      </div>
                      </div>
                  </div>
              </div>
              ';
            }
          $g++;
          //Si es par imprimimos los div de bloques
          if($g%2!=0){
            echo '
                </div>
            </div>
            ';
          }
        }
        ?>
    <br><br><br><br>
    </section>
    <!-- End: Login Form Clean -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="Javascript/finger.js"></script>
    <script type="text/javascript" src="Javascript/localize.js"></script>
</body>
</html>
