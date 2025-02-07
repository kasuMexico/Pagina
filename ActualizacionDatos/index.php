<?php
//Con este codigo se actualizan los datos de los Clientes
    //inicio sesion
    session_start();
    //Recupero varible
    $dat = base64_decode($_GET['value']);
    $_SESSION['txtCurp_ActIndCli'] = $dat;
    //retorna el valor de mercadopago
    $status = $_GET['stat'];
    //requiero librerias
    require_once '../eia/librerias.php';
            //cContenido donde se ingresa la curp cuando no viene de busqyeda de registros
            $htmlCur = '<div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
                                  <div class="features-small-item">
                                      <div class="descri">
                                        <div class="icon">
                                            <i><img src="../assets/images/Index/florkasu.png" name="Logo" alt="Kasu Logo"></i>
                                        </div>
                                      </div>
                                      <h5 class="features-title">Verificacion</h5>
                                      <form method="POST" id="ModVerCod">
                                          Ingresa tu CURP
                                          <br>
                                          <br>
                                          <input type="text" id="txtCurp_St1" name="txtCurp_St1"><br><br>
                                          <input style="outline: none;border: none; cursor: pointer; font-size: 15px; border-radius: 20px;
                                          padding: 12px 20px; background-color: #012F91; text-transform: uppercase; color: #fff;
                                          letter-spacing: 0.25px; -webkit-transition: all 0.3s ease 0s;-moz-transition: all 0.3s ease 0s;
                                          -o-transition: all 0.3s ease 0s;transition: all 0.3s ease 0s;" type="submit" value="Continuar" id="btnVerCur" name="btnVerCur">
                                      </form>
                                  </div>
                              </div>';
          //Imprime la busqueda de las actualizacion de datos
          $htmlIndex = '    <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
                                <div class="features-small-item">
                                    <div class="descri">
                                      <div class="icon">
                                          <i><img src="../assets/images/Index/florkasu.png" name="Logo" alt="Kasu Logo"></i>
                                      </div>
                                    </div>
                                    <h5 class="features-title">Actualizar mis datos</h5>
                                    <div class="consulta">
                                        <!-- Formulario Para Consultar Datos Del Cliente -->
                                        <form method="POST" id="ActIndCli1" action="php/datos.php">
                                            <p>CURP</p>
                                            <input type="text" id="txtCurp_ActIndCli" name="txtCurp_ActIndCli" value="'.$dat.'" disabled >
                                            <br>
                                            <br>
                                            arriba
                                            <br>
                                            <input placeholder="No. de Tarjeta" type="text" id="txtNumTarjeta_ActIndCli" name="txtNumTarjeta_ActIndCli" maxlength="20">
                                            <br>
                                            <br>
                                            <input style="outline: none;border: none; cursor: pointer; font-size: 13px; border-radius: 20px;
                                            padding: 12px 20px; background-color: #012F91; text-transform: uppercase; color: #fff;
                                            letter-spacing: 0.25px; -webkit-transition: all 0.3s ease 0s;-moz-transition: all 0.3s ease 0s;
                                            -o-transition: all 0.3s ease 0s;transition: all 0.3s ease 0s;" type="submit"  id="btnConsultar_ActIndCli" name="btnConsultar_ActIndCli" value="Consultar"><br><br>
                                        </form>
                                        <!-- Boton que te envia a no tengo tarjeta -->
                                        <form method="POST" id="ActIndCli2" action="php/datoscurp.php">
                                            <input style="display:none;" type="text" id="txtCurp" name="txtCurp" value="'.$dat.'" disabled maxlength="18">
                                            <a style="color: #911F66;letter-spacing: 0.25px; -webkit-transition: all 0.3s ease 0s;-moz-transition: all 0.3s ease 0s;
                                            -o-transition: all 0.3s ease 0s;transition: all 0.3s ease 0s;" data-toggle="modal" data-target="#ActIndMod" >No tengo tarjeta</a>
                                        </form>
                                    </div>
                                </div>
                            </div>';

    if(isset($_POST['btnVerCur'])){
        if($_POST['txtCurp_St1'] != ""){
            header("Location: https://kasu.com.mx/ActualizacionDatos/php/datoscurp.php?c=".$_POST['txtCurp_St1']);
        }else{
            echo'<script type="text/javascript">alert("No ingresaste tu CURP, intenta de nuevo");</script>';
            echo $htmlCur;
        }
    }
//
    if(isset($_POST['btnVolCur'])){
      cerrarSession();
      header("Location: https://kasu.com.mx/ActualizacionDatos/");
    }
//
    function cerrarSession(){
        // Si se desea destruir la sesión completamente, borre también la cookie de sesión.
        // Nota: ¡Esto destruirá la sesión, y no la información de la sesión!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        // Finalmente, destruir la sesión.
        session_destroy();
    }
?>

<html lang="es">
      <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no">
          <meta name="description" content="KASU es una empresa dedicada a prestar servicios a futuro, mediante plataformas tecnologicas llevamos servicios a las comunidades mas alejadas">
          <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900">
          <link rel="icon" href="../assets/images/logo.png">
          <title>KASU| Actualizar</title>
          <!-- Additional CSS Files -->
          <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css">
          <link rel="stylesheet" type="text/css" href="../assets/css/font-awesome.css">
          <link rel="stylesheet" href="../assets/css/templatemo-softy-pinko.css">
          <script type="text/javascript" src="js/validarcurp.js"></script>
      </head>
      <body>
          <!--VEntana Emergente de compra de tarjeta-->
          <div id="ActIndMod" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm">
              <div class="modal-content" style="height:auto; padding:1em;">
                <div id="reposicion">
                  <h5>Reposición de la tarjeta</h5>
                  <br>
                  <h5><?echo $dat;?></h5>
                  <br>
                  <p style="font-size:15px;"><b>1.</b> Da clic en <b>PAGAR </b></p>
                  <p style="font-size:15px;"><b>2.</b> Ingresa tu CURP y actualiza tus datos. </p>
                  <p style="font-size:15px;"><b>3.</b> Espera la tarjeta en tu domicilio. </p>
                  <br>
                  <p style="font-size:12px;"><b>NOTA:</b> Si por algun motivo no haz recibido tu tarjeta, comunicate al número <a href="tel:<?php echo $tel;?>" style = "color:#911F66"><?php echo $tel;?></a></p>.
                  <br>
                  <a class="main-button-slider" href="https://www.mercadopago.com.mx/checkout/v1/redirect?pref_id=292541305-e4f4df73-94a8-43ee-9f50-fc235cb29cf1">Pagar</a>
                </div>
              </div>
            </div>
          </div>
          <!--VEntana Emergente en la primer pantalla de instrucciones de Actualizacion-->
          <div id="Instruccion" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm">
              <div class="modal-content" style="height:auto; padding:1em;">
                <div id="reposicion">
                  <h5>Actualización de Datos</h5>
                  <br>
                  <p>A continuación te damos una serie de instrucciones.</p><br>
                  <p style="font-size:15px;"><b>1.</b> Tener a la mano tú  tarjeta KASU.</p>
                  <p style="font-size:15px;"><b>2.</b> Escribir el  No. de Tarjeta. </p>
                  <p style="font-size:15px;"><b>3.</b> Clic en Consultar.</p>
                  <br>
                  <p style="font-size:12px;"><b>NOTA:</b> Para una atención personalizada, contáctenos <a href="tel:<?php echo $tel;?>" style = "color:#911F66"><?php echo $tel;?></a>.</p>
                  <br>
                  <a class="main-button-slider" href="#" data-dismiss="modal" class="btn btn-danger">¡Entiendo!</a>
                </div>
              </div>
            </div>
          </div>

      <!--bloques de informacion para contacto-->
          <section class="section" id="Clientes">
            <div class="container">
							<div class="row">
                    <?
                //Selecciona que alert imprimir segun lo que responde mercadopago
                    if($status == "1"){
                        echo $htmlCur;
                    }elseif($status == "0"){
                        echo'<script type="text/javascript">alert("¡Operacion exitosa!\nRelize su pago para mantener sus datos actualizados");</script>';
                        echo $htmlIndex;
                    }elseif(!isset($_GET['value'])){
                        echo $htmlCur;
                    }else{
                      echo $htmlIndex;
                    }
                    ?>
                  <div class="col-lg-2 col-md-12 col-sm-12 align-self-center"></div>
                  <!-- *****  PRDUCTOS ***** -->
                  <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
                      <div class="features-small-item">
                          <div class="consulta">
                              <div class="icon">
                                  <i><img src="../assets/images/Index/florkasu.png" name="Logo" alt="Kasu Logo"></i>
                              </div>
                          </div>
                          <div class="pricing-body">
                              <h2>¡Conoce Nuestro Plan de Referidos!</h2>
                              <br>
                              <p>Por cada cliente efectivo que refieras para la contratación de cualquier producto KASU, te regalaremos un beneficio efectivo de </p>
                              <br>
                              <h2>Hasta $300 MXN </h2>
                              <br>
                              <p>¡Platica sobre nuestro servicio a familiares o amigos! Puedes dar tú mismo la información a tu familiar y/o conocido interesado, o bien solicitarnos ayuda.</p>
                              <p>¡Ahora es tiempo de que tu referido adquiera tu servicio y también forme parte de la familia KASU!</p>
                              <br>
                              <?php
                              if(!isset($_GET['value'])){
                                  echo "<a href='#' class='main-button-slider'>Registrarme</a>";
                              }else{
                                echo "<a href='/clientes' class='main-button-slider'>Inicar Sesion</a>";
                              }
                              ?>
                          </div>
                      </div>
                  </div>
              </div>
            </div>
          </section>
          <!-- ***** Header Text End ***** -->
          <script src="../assets/js/jquery-2.1.0.min.js"></script>
          <!-- Bootstrap -->
          <script src="../assets/js/popper.js"></script>
          <script src="../assets/js/bootstrap.min.js"></script>
          <!-- Plugins -->
          <script src="../assets/js/scrollreveal.min.js"></script>
          <script src="../assets/js/waypoints.min.js"></script>
          <script src="../assets/js/jquery.counterup.min.js"></script>
          <script src="../assets/js/imgfix.min.js"></script>
          <!-- Global Init -->
          <script src="../assets/js/custom.js"></script>
      </body>
  </html>
    <script>
          $(document).ready(function()
          {
             $("#Instruccion").modal("show");
          });
    </script>
