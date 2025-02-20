<?php
    //Consulta para los artiulos
    require_once '../eia/librerias.php';
    //Creamos la variables pricipales
    //Javascript que imprime el mensaje de alerta de recepcion de comentario
    if(isset($_GET['Msg'])){
    	echo "<script type='text/javascript'>
    						alert('".$_GET['Msg']."');
    				</script>";
    }
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-MH6TN5Z');</script>
    <!-- End Google Tag Manager -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="KASU Te permite conectar tu plataforma Web o Movil a nuestras bases de Datos">
    <meta name="author" content="Erendida Itzel Castro Marquez">
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
    <link rel="icon" href="https://kasu.com.mx/assets/images/Index/florkasu.png">
    <title>KASU | Apimarket</title>
    <!-- Additional CSS Files -->
    <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/font-awesome.css">
    <link rel="stylesheet" href="https://kasu.com.mx/assets/css/templatemo-softy-pinko.css">
    <link rel="stylesheet" href="https://kasu.com.mx/assets/css/index.css">
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/codigo.css">
</head>

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MH6TN5Z"
    height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
    <!-- Load Facebook SDK for JavaScript -->
    <div id="fb-root"></div>
    <script>
        window.fbAsyncInit = function() {
          FB.init({
            xfbml            : true,
            version          : 'v5.0'
          });
        };

        (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = 'https://connect.facebook.net/es_LA/sdk/xfbml.customerchat.js';
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
    </script>
      <!-- Your customer chat code -->
      <div class="fb-customerchat"
        attribution=setup_tool
        page_id="404668209882209"
        theme_color="#7646ff"
        logged_in_greeting="En que puedo ayudarte?"
        logged_out_greeting="En que puedo ayudarte?">
      </div>
    <!-- La venta emergente debe de estar fuera del div que lo lanza *JCCM -->
    <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content" style="height:auto; padding:1em;">
                <div id="datos">
                </div>
            </div>
        </div>
    </div>
    <!-- ***** Header Area Start ***** -->
    <header class="header-area header-sticky">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav class="main-nav">
                        <!-- ***** Logo Start ***** -->
                        <a href="#" class="logo">
                            <img src="https://kasu.com.mx/assets/images/kasu_logo.jpeg" alt="Logo Kasu"/>
                        </a>
                        <!-- ***** Logo End ***** -->
                        <!-- ***** Menu Start ***** -->
                        <ul class="nav" >
                            <li><a style="color: black;" href="https://kasu.com.mx/" class="comprar">KASU</a></li>
                            <li><a style="color: black;" href="#apikasu">Documentación</a></li>
                            <li><a style="color: black;" href="#contact-us">Contactános</a></li>
                        </ul>
                        <a class='menu-trigger'>
                            <span>Menú</span>
                        </a>
                        <!-- ***** Menu End ***** -->
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <div class="welcome-area">
        <!-- ***** Header Text Start ***** -->
        <div class="header-text">
            <div class="container">
                <div class="row">
                    <div class="offset-xl-3 col-xl-6 offset-lg-2 col-lg-8 col-md-12 col-sm-12">
                       <!-- offset-xl-3 col-xl-6 offset-lg-2 col-lg-8 col-md-12 col-sm-12 -->
                        <h1 style="color:white">Todas las oportunidades del open banking a tu alcance <strong>Apimarket_KASU</strong></h1>
                    </div>
                </div>
            </div>
        </div>
        <!-- ***** Header Text End ***** -->
    </div>
    <!-- ***** Descripción General de la API ***** -->
    <section class="section padding-top-70" id="">
      <div class="container">
        <div class="row">
          <div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
            <div class="center-heading">
              <div class="count-item decoration-bottom">
                  <h2 class="section-title">
                    <strong>1<?PHP echo number_format($basicas->MaxDat($mysqli,"Id","Venta"),0,".",",");?></strong><span> Clientes Activos</span>
                  </h2>
              </div>
              <p style="text-align: justify;"><strong>KASU</strong> es una plataforma que cuenta con un entorno de gestión robusto que permite a los usuarios realizar la compra de pequeñas partes de fideicomisos que sirven como ahorro para afrontar situaciones difíciles en su vida, tales como gastos funerarios, enviar a sus hijos a la universidad, crear fondos para el retiro y envio y recepcion de remesas.</p>
            </div>
          </div>
          <div class="col-lg-2 col-md-12 col-sm-12 align-self-center" ></div>
          <div class="col-lg-4 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
            <div class="row">
              <div class="features-small-item">
                <div class="center-heading">
                  <h2 class="section-title">Usa el entorno KASU</h2>
                </div>
                <div class="center-text" id="apikasu">
                  <p style="text-align: justify;"><strong>1.- </strong>Comercializa nuestros servicios y recibe interesantes comisiones por ello.</p>
                  <p style="text-align: justify;"><strong>2.- </strong>Recibe los pagos que nuestros clientes tienen que hacer sobre los servicios de KASU y obtén una comisión por cada peso que cobres.</p>
                  <p style="text-align: justify;"><strong>3.- </strong>Realiza validaciones de datos de clientes con los datos de los clientes de KASU.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- ***** Porque contratar Ventajas***** -->
    <section class="section colored padding-top-70" id="Ventajas">
      <div class="col-lg-12">
          <div class="center-heading">
              <br><br>
              <h2 class="section-title" >Selecciona la <strong>API's</strong> que mejor se adapte a tus necesidades</h2>
          </div>
      </div>
      <br>
      <? require_once 'html/select_api.php'; ?>
    </section>
    <!-- Registramos los datos que muestran el funcionamuiento en fotrma grafica-->
    <section class="section padding-top-70">
      <div class="container">
        <div class="Consulta">
            <h2 class="titulos"><strong>USABILIDAD GENERAL</strong></h2>
            <br>
            <p>Las <strong>API</strong> que hemos desarrollado para ti cuentan con una usabilidad formada por bloques que pueden comunicarse entre sí o intercambiar información generada en un bloque para interactuar en cualquier otro.</p>
            <br>
            <p>Solo recuerda que debes tener permisos para cada una de nuestras <strong>API</strong> verticales.</p>
            <br>
        </div>
        <div class="table-container">
      		<table class="table">
      			<tbody>
      				<tr>
      					<td class="blue" style="padding: 25px;"><h2><strong>API_CUSTOMER</strong></h2></td>
      					<td class="red" style="padding: 25px;"><h2><strong>API_PAYMENTS</strong></h2></td>
      					<td class="purple" style="padding: 25px;"><h2><strong>API_ACCOUNTS</strong></h2></td>
      				</tr>
      				<tr>
      					<td colspan="3" class="green">
                  <h2><strong>token_full</strong></h2>
                  <p>Consulta que retorna un token de acceso para todas las API_KASU tu Usuario debe estar liberado para las 3 diferentes bloques, ya que de caso contrario la api retornará errores o enviará a correo al usuario para los datos faltantes </p>
                </td>
      				</tr>
      				<tr>
      					<td class="blue">
                  <h2><strong>request</strong></h2>
                  <br><p>Te muestra los codigos para las busquedas ya sea por bloques o individuales de datos que se pueden consultar ya sea para validar a tus clientes o para poder realizar una implementacion</p>
                </td>
                <td class="red">
                  <h2><strong>account_status</strong></h2>
                  <br><p>Consulta el estado de cuenta de un cliente, pagos, pendiente y moras, pide el comportamiento del cliente para identificar si el cliente es sujeto de credito.</p>
                </td>
                <td class="purple">
                  <h2><strong>new_service</strong></h2>
                  <br><p>Te permite realizar el registro de un servicio <strong>KASU</strong>, ligado a una clave <strong>CURP</strong>, genera ingresos por venta <strong>Ya!!</strong>.</p>
                </td>
      				</tr>
              <tr>
                <td class="blue">
                  <h2><strong>individual_request</strong></h2>
                  <br><p>Te muestra los datos individualkes de clientes, productos o polizas ya sea para validar a tus clientes o para poder realizar una implementacion </p>
                </td>
                <td class="red">
                  <h2><strong>pagos_psd2</strong></h2>
                  <br><p>Realiza el cobro de un servicio <strong>KASU</strong> y genera una comision por cada pago que recibas, el pago que recibes en efectivo se debita de tu banca.</p>
                </td>
                <td class="purple">
                  <h2><strong>modify_record</strong></h2>
                  <br><p>Te permite realizar modificaciones al registro de un cliente, unicamente puedes actualizar sus datos generales recuerda que <strong>KASU</strong> esta ligado a una clave <strong>CURP</strong>.</p>
                </td>
              </tr>
      				<tr>
      					<td class="blue">
                  <h2><strong>request_block</strong></h2>
                  <br><p>Te muestra por bloques los datos de los usuarios, productos o polizas ya sea para validar a tus clientes o para poder realizar una implementacion</p>
                </td>
                <td class="red">
                  <h2><strong>envios_swift</strong></h2>
                  <br><p>Realiza la entrega de envios que reciban tus clientes desde <strong>Estados Unidos</strong>, los envios entregados en efectivo se transfieren a tu cuenta bancaria</p>
                </td>
      					<td class="purple">
                  <h2><strong></strong></h2>
                  <p></p>
                </td>
      				</tr>
      			</tbody>
      		</table>
      	</div>
      </div>
    </section>
    <!-- *****          TOKEN DE ACCESO         ***** -->
  	<? require_once 'html/Autenticacion.php';?>
    <!-- ***** Opiniones de los clientes ***** -->
    <section class="section">
          <div class="col-lg-12">
              <div class="center-heading">
                  <br><br>
                  <h2 class="section-title">Conoce los productos <strong>KASU</strong> y ofrecelo a tus clientes</h2>
              </div>
          </div>
          <br>
					<div class="container">
						<div class="row">
							<?php
              $pagina = "https://kasu.com.mx/productos.php?Art=";
							//Creamos la variables pricipales
							$cont = 1;
							//Contamos el no de  Articulos
							$MaxPro = $basicas->MaxDat($mysqli,"id","ContProd");
							//Se imprimen los comentarios
							while($cont <= $MaxPro){
								//Consulta para los artiulos
								$SqlPro="SELECT * FROM ContProd WHERE id =".$cont;
								//Si la consulta es verdadera imprime el articulo
								if ($ResArti=$mysqli->query($SqlPro)){
									$Pro=$ResArti->fetch_row();
										printf('
											<div class="col-lg-4 col-md-6 col-sm-12" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
												<div class="team-item">
													<div class="team-content">
														<br><br>
														<div class="team-info">
															<h2 class="user-name" style="padding: 8px;"><strong>%s</strong></h2>
															<div class="descri">%s</div>
															<div class="form-group">
																	<a href="'.$pagina.'%s" class="main-button-slider"><strong>Comprar Ahora</strong></a>
															</div>
														</div>
														<div class="">
															<img src="%s" alt="%s" style="border-radius: 15px; height: 120px; width: 100px;">
														</div>
													</div>
												</div>
											</div>
												',$Pro[2],$Pro[14],$Pro[0],$Pro[3],$Pro[2]);
								}
								$cont++;
							}
							?>
						</div>
					</div>
    </section>
    <!-- ***** Features Small End ***** -->
    <section class="section colored" id="contact-us">
        <div class="container">
            <br><br>
            <!-- ***** Section Title End ***** -->
            <div class="row">
                <!-- ***** Contact Text Start ***** -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <h2 class="margin-bottom-30">Contactanos y obten tu Acceso</h2>
                    <br>
                    <div class="contact-text">
                        <p style="text-align: justify;">Puedes enviar un correo electrónico registrandote en este formulario y proporcionar información sobre su caso de uso. y nos comunicaremos contigo para otorgarte o denegar el acceso a las API's.</p>
                    </div>
                </div>
                <!-- ***** Contact Text End ***** -->
                <!-- ***** Contact Form Start ***** -->
                <div class="col-lg-8 col-md-6 col-sm-12">
                    <div class="contact-form">
                      <form id="ContactoApiMarket" action="contacto.php" method="post">
                          <div class="row">
                              <div class="col-lg-6 col-md-12 col-sm-12">
                                  <fieldset>
                                      <input type="text"  name="name" id="name"  class="form-control" placeholder="Nombre" required>
                                  </fieldset>
                              </div>
                              <div class="col-lg-6 col-md-12 col-sm-12">
                                  <fieldset>
                                      <input type="email" name="email" id="email" class="form-control"  placeholder="Correo" required>
                                  </fieldset>
                              </div>
                              <div class="col-lg-12">
                                  <fieldset>
                                      <textarea name="message" id="message" rows="6" class="form-control"  placeholder="Comentanos brevemente en que usaras las API's de KASU" required></textarea>
                                  </fieldset>
                              </div>
                              <div class="col-lg-12">
                                  <fieldset>
                                      <button type="submit" id="Enviar" class="main-button">Enviar</button>
                                  </fieldset>
                              </div>
                          </div>
                      </form>
                    </div>
                </div>
                <!-- ***** Contact Form End ***** -->
            </div>
        </div>
    </section>
    <!-- ***** Contact Us End ***** -->

    <!-- ***** Footer Start ***** -->
    <footer>
      <? require_once ('assets/footer.php');?>
    </footer>
    <!-- jQuery -->
    <script src="https://kasu.com.mx/assets/js/jquery-2.1.0.min.js"></script>
    <!-- Bootstrap -->
    <script src="https://kasu.com.mx/assets/js/bootstrap.min.js"></script>
    <!-- Plugins -->
    <script src="https://kasu.com.mx/assets/js/scrollreveal.min.js"></script>
    <!-- Global Init -->
    <script src="https://kasu.com.mx/assets/js/custom.js"></script>
  </body>
</html>
