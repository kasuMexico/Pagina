<?php
    //indicar que se inicia una sesion
    session_start();
    //inlcuir el archivo de funciones
    require_once '../eia/librerias.php';
    //Validar si existe la session y redireccionar
    if(!isset($_SESSION["Vendedor"])){
        header('Location: https://kasu.com.mx/login/');
    }else{
    }
    //Php que realiza el analisis de las metas de venta y colocacion
    require_once 'php/Analisis_Metas.php';
/**************************************************************************************************
                          Insersion de la imagen de perfil del usuario
************************************************************************************************************/
    //Obtiene el nombre de el vendedor
    $Vend = $_SESSION["Vendedor"];
    //Ruta para la insersion de la imagen del usuario
    $ruta = "assets/img/perfil/";
    // Se comprueba que realmente sea la ruta de un directorio
    if (is_dir($ruta)){
        // Abre un gestor de directorios para la ruta indicada
        $gestor = opendir($ruta);
        // Recorre todos los archivos del directorio
        while (($archivo = readdir($gestor)) !== false)  {
            // Solo buscamos archivos sin entrar en subdirectorios
            if (is_file($ruta."/".$archivo)) {
                $ext = explode( '.', $archivo );
                if($ext[0] == $_SESSION["Vendedor"]){
                  $file_ext = $ext[1];
                }
            }
        }
        // Cierra el gestor de directorios
        closedir($gestor);
    }
?>
<!DOCTYPE html>
<html lang="es">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, user-scalable=no">
     <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no"> -->
      <title>Inicio</title>
      <meta name="theme-color" content="#2F3BA2" />
      <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
      <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
      <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
      <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
      <link rel="stylesheet" href="assets/css/styles.min.css">
      <link rel="stylesheet" href="assets/css/Grafica.css">
      <!-- Funcion que Genera la Grafica de cartera -->
      <script type="text/javascript" src="Javascript/GenGrafica.js"></script>
  </head>
  <body>
    <!--onload="localize();"-->
    <!--Inicio de menu principal fijo-->
    <section id="Menu">
      <?require_once 'html/Menuprinc.php';?>
    </section>
    <!--Final de menu principal fijo-->
      <!-- menu con los datos de usuario -->
      <div class="principal">
        <!--Logo de KASU-->
          <div calss="row" style="display:flex;">
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <img alt="perfil" class="img-fluid" style="padding-left: 10px;"src="/login/assets/img/logoKasu.png" alt="Carga tu foto de perfil">
              <div style="transform: translate(0, 25px)">
                  <p style="transform: scaleY(2);">
                    <strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Protege a Quien Amas</strong>
                  </p>
              </div>
          </div>
          <hr>
          <div class="dpersonales">
              <div class="imgPerfil">
                <img alt="perfil" class="img-thumbnail" src="/login/assets/img/perfil/<?PHP echo $Vend.".".$file_ext;?>" alt="Carga tu foto de perfil">
              </div>
              <div class="Nombre">
                    <?PHP
                      $SL1 = Basicas::BuscarCampos($mysqli,"Nombre","Empleados","IdUsuario",$_SESSION["Vendedor"]);
                      $SL2 = Basicas::BuscarCampos($mysqli,"Nivel","Empleados","IdUsuario",$_SESSION["Vendedor"]);
                      $suc = Basicas::BuscarCampos($mysqli,"Sucursal","Empleados","IdUsuario",$_SESSION["Vendedor"]);
                      $su2 = Basicas::BuscarCampos($mysqli,"NombreSucursal","Sucursal","Id",$suc);
                      $SL2 = Basicas::BuscarCampos($mysqli,"NombreNivel","Nivel","Id",$SL2);
                      echo "
                          <p>".$SL1."</p>
                          <p>".$SL2." - ".$su2."</p>
                      ";
                    ?>
              </div>
          </div>
          <!-- contenido debajo de la persona -->
          <div class="container">
              <div class="row">
                  <div  class="col-md-6">
                      <div  class="Grafica" id="chart_container"></div>
                  </div>
                  <div class="col-md-6">
                        <?
                        if($Niv == 7){
                          echo '
                          <div class="col-md-12">
                              <p>Comisones Acumuladas</p>
                              <h3 style="color: '.$spv.';">'.money_format('%.2n',$ComGenHoy).' </h3>
                          </div>
                          ';
                        }else{
                          echo '
                          <div class="col-md-12">
                              <p>Normalidad Mensual</p>
                              <a href="Pwa_Clientes.php"><h3 style="color: '.$spv.';">'.round($AvCob).' %</h3></a>
                          </div>
                          ';
                        }
                          echo '<div class="row">';
                        if($Niv != 7){
                          echo '
                              <!-- Bloque informacion de cobranza -->
                              <div class="col-md-6">
                                  <hr>
                                  <p><strong>Meta de Cobranza</strong></p>
                                  <h3>'.money_format('%.2n',$MetaCob).'</h3>
                                  <p>Avance de Cobranza</p>
                                  <a href="Pwa_Registro_Pagos.php"><h3 style="color: '.$spv.';"> '.money_format('%.2n',$CobHoy).'</h3></a>
                              </div>
                              ';
                            }
                        if($Niv != 5){
                          echo '
                              <!-- BLoque informacion de ventas -->
                              <div class="col-md-6">
                                  <hr>
                                  <p><strong>Meta de Venta</strong> </p>
                                  <h3>'.money_format('%.2n',$MetaVta).'</h3>
                                  <p>Avance de Venta</p>
                                  <a href="registro.php"><h3 style="color: '.$bxo.';">'.round($AvVtas).' %</h3><a>
                              </div>
                              ';
                        }
                        ?>
                    </div>
                  </div>
              </div>
          </div>
      </div>
      <br><br><br>
      <script defer type="text/javascript" src="Javascript/finger.js" defer async></script>
      <script defer type="text/javascript" src="Javascript/localize.js"></script>
  </body>
</html>
