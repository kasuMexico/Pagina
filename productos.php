<?php
/************************************************************************
            Recuerda ajustar tu api en el archivo de librerias
************************************************************************/
//indicar que se inicia una sesion *JCCM
	session_start();
//Requerimos el archivo de librerias *JCCM
  require_once 'eia/librerias.php';
//Si esta vacio el get se asigna el servicio 1
    if(!isset($_GET['Art'])){
      $_GET['Art'] = 1;
    }
//Buscamos el maximo Id de los productos
		$MaxProd = Basicas::MaxDat($mysqli,"Id","ContProd");
//Si estan buscando un archivo que no existe te redirecciona a la pagina de error 404
		if ($_GET['Art'] > $MaxProd) {
			//Hacemos el comparativo
			header('Location: https://kasu.com.mx/error');
		}
//Extraemos el valor de Mercado pago
		$LigaMP = Basicas::BuscarCampos($mysqli,"Liga","MercadoPago","Referencia",$_GET['Pro']);
//Seleccionamos la liga de Suscripcion o pago unico
		$MediPago = Basicas::BuscarCampos($mysqli,"Plazo","MercadoPago","Referencia",$_GET['Pro']);
		if($MediPago == 1){
			$PreLig = "https://www.mercadopago.com.mx/checkout/v1/redirect?preference-id=";
		}else{
			$PreLig = "https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_plan_id=";
		}
//Se protege el valor a insertar en la base de datos
		$dat = $mysqli -> real_escape_string($_GET['Art']);
//Creamos la consulta en la base de datos apra los datos del blog
    $ConArt = "SELECT * FROM ContProd WHERE Id = ".$dat;
//Ejecutamos la consulta
    $res = mysqli_query($mysqli, $ConArt);
//Si existe el registro se asocia en un fetch_assoc
    if($Reg=mysqli_fetch_assoc($res)){}

?>
<!DOCTYPE html>
<html lang="es">
<head>
		<!-- Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','GTM-MCR6T6W');</script>
		<!-- End Google Tag Manager -->
    <meta charset="utf-8">
    <meta name="description" content="<? echo $Reg['Nombre'];?>">
    <meta name="keywords" content="Kasu Servicio Funerario, Servicio Universitario, Servicio Retiro, Protege a quien amas">
    <link rel="canonical" href="https://kasu.com.mx/productos.php?Art=<?echo $_GET['Art'];?>">
    <meta name="author" content="Jose Carlos Cabrera Monroy">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
    <link rel="icon" href="../assets/images/kasu_logo.jpeg">

    <title>KASU| <?echo $Reg['Nombre'];?></title>
    <!-- Additional CSS Files -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.css">
    <link rel="stylesheet" href="assets/css/templatemo-softy-pinko.css">
    <link rel="stylesheet" href="assets/css/index.css">
		<link rel="stylesheet" href="assets/css/productos.css">
		<script src="assets/js/js_productos.js"></script>
</head>
<body>
		<!-- Chat de Facebook -->
		<?
		require_once 'html/CodeFb.php';
		?>
		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MCR6T6W"
		height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->
		<section name="EmergentesServicio">
			<!-- Gallery modal -->
			<div class="modal fade" id="ModalGaleria" tabindex="-1" role="dialog" aria-labelledby="ModalGaleria" aria-hidden="true">
			  <div class="modal-dialog modal-lg" role="document">
			    <div class="modal-content">
			      <div class="modal-header">
			        <h5 class="modal-title" id="galleryModalLabel">Nuestras otras experiencias</h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          <span aria-hidden="true">&times;</span>
			        </button>
			      </div>
			      <div class="modal-body">
			        <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
			          <ol class="carousel-indicators">
									<li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
									<!--Imprimimos el numeroador contando los archivos que estan en la carpeta-->
									<?php
									$dir = "assets/images/cupones"; //Ruta de la carpeta
									$files = scandir($dir); //Leer los archivos de la carpeta
									$count = 0; //Inicializar contador a 0
									foreach($files as $file) {
									  if(is_file($dir . "/" . $file)) { //Si el archivo es un archivo (no una carpeta)
											echo '<li data-target="#carouselExampleIndicators" data-slide-to="'.$count.'"></li>';
									    $count++; //Incrementar contador
									  }
									}
									?>
			          </ol>
			          <div class="carousel-inner">
									<!--Imprimimos las imagenes que se encuentran en la carpeta-->
									<?
									foreach (glob("assets/images/cupones/*.jpg") as $archivo) {
									    echo '
											<div class="carousel-item">
					              <img class="d-block w-100" src="https://kasu.com.mx/'.$archivo.'" alt="'.$archivo.'">
					            </div>
											';
									}
									?>
			          </div>
			          <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
			            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
			            <span class="sr-only">Anterior</span>
			          </a>
			          <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
			            <span class="carousel-control-next-icon" aria-hidden="true"></span>
			            <span class="sr-only">Siguiente</span>
			          </a>
			        </div>
			      </div>
			    </div>
			  </div>
			</div>
		</section>
    <!-- ***** Header Area Start ***** -->
		<? require_once 'html/MenuPrincipal.php';?>
    <!-- ***** Welcome Area End ***** -->
    <!-- ***** Features Big Item Start ***** -->
    <section class="section padding-top-140 padding-bottom-0" id="features">
        <div class="container">
            <div class="row">
                <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
                    <img src="<? echo $Reg['Imagen_Producto'];?>" class="rounded img-fluid d-block mx-auto" alt="<? echo $Reg['Nombre'];?>">
                </div>
                <div class="col-lg-1"></div>
                <div class="col-lg-6 col-md-12 col-sm-12 align-self-center mobile-top-fix">
                    <div class="left-heading">
                        <h1 class="section-title"><strong><? echo $Reg['Nombre'];?>, KASU</strong></h1>
                    </div>
                    <div class="left-text">
                        <? echo $Reg['DesIni_Producto'];
												//Boton de compra debajo de texto
												$Desc = Basicas::BuscarCampos($mysqli,"Descuento","PostSociales","Id",$_SESSION["tarjeta"]);
												if(!empty($Desc)){
													//IMPRIMIOS LA IMAGEN
													echo '
													<br>
													<img class="img-thumbnail" src="assets/images/cupones/'.Basicas::BuscarCampos($mysqli,"Img","PostSociales","Id",$_SESSION["tarjeta"]).'" style="width: 15em;">
													<br>
													<a href="registro.php?pro='.$dat.'" class="main-button-slider-pol"><strong>Comprar -'.money_format('%.2n', $Desc).'</strong></a>
													<br>
													';
												}elseif(!empty($_GET['Pro'])){
													//Generamos el botos de mercado pago
													echo '
													<br>
													<a href="'.$PreLig.$LigaMP.'" class="main-button-slider-pol"><strong>Comprar</strong></a>
													<br>
													<br>
													';
												}else{
													echo '
													<br>
													<a href="registro.php?pro='.$dat.'" class="main-button-slider-pol"><strong>Comprar</strong></a>
													<br>
													<br>
													';
												}
												?>
                    </div>
                </div>
            </div>
						<br>
        </div>
    </section>
		<!-- ***** Home Parallax Start ***** -->
		<section class="mini" id="work-process">
			<div class="mini-content">
				<div class="container">
					<div class="row" id="video">
						<?
						if($_GET['Art'] == 1){
							echo '
							<div class="offset-xl-3 col-xl-6 offset-lg-2 col-lg-8 col-md-12 col-sm-12">
							<br>
							<a href="#" class="main-button-slider-pol"><strong>Agencias Autorizadas</strong></a>
							</div>
							';
						}elseif ($_GET['Art'] == 4) {
							echo '
							<div class="offset-xl-3 col-xl-6 offset-lg-2 col-lg-8 col-md-12 col-sm-12">
							<br>
							<!-- Button trigger modal -->
							<button type="button"  data-toggle="modal" data-target="#ModalGaleria" class="btn btn-primary btn-lg active">
							<strong>Galeria de Fotos</strong>
							</button>
							</div>
							';
						}
						?>
					</div>
				</div>
			</div>
		</section>
		<!-- ***** Home Parallax End ***** -->
		<section class="section padding-bottom-100" id="Comprar">
			<div class="container">
				<div class="row">
					<div class="col-lg-6 col-md-12 col-sm-12 align-self-center mobile-bottom-fix">
						<div class="pricing-item">
							<div class="pricing-body">
								<i>
									<img style="height: 80px;" src="<? echo $Reg['Image_Desc']; ?>" alt="Producto Kasu">
								</i>
								<br>
								<br>
								<strong><? echo $Reg['Nombre'];?></strong>
								<br><br>
								<div class = "dudasfun">
									<? echo $Reg['Tab_Producto']; ?>
								</div>
								<br>
							</div>
						</div>
					</div>
					<div class="col-lg-1"></div>
					<div class="col-lg-5 col-md-12 col-sm-12 align-self-center mobile-bottom-fix-big" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
						<div class="pricing-item">
							<div class="pricing-body">
								<i>
									<img style="height: 80px;" src="assets/images/icon/credit-card.png" alt="Producto Kasu">
								</i>
								<br>
								<br>
								<strong> ¿Cuanto Cuesta el producto?</strong>
								<br><br>
								<div style="padding: 15px;">
									<? echo $Reg['Precios_Producto'];?>
								</div>
								<?
								$Desc = Basicas::BuscarCampos($mysqli,"Descuento","PostSociales","Id",$_SESSION["tarjeta"]);
								if(!empty($Desc)){
									//iMPRIMIOS LA IMAGEN
									echo '
									<br>
									<img class="img-thumbnail" src="assets/images/cupones/'.Basicas::BuscarCampos($mysqli,"Img","PostSociales","Id",$_SESSION["tarjeta"]).'" style="width: 15em;">
									<br>
									<a href="registro.php?pro='.$dat.'" class="main-button-slider-pol"><strong>Comprar -'.money_format('%.2n', $Desc).'</strong></a>
									<br>
									';
								}elseif(!empty($_GET['Pro'])){
									//Generamos el botos de mercado pago
									echo '
									<br>
									<a href="'.$PreLig.$LigaMP.'" class="main-button-slider-pol"><strong>Comprar</strong></a>
									<br>
									<br>
									';
								}else{
									echo '
									<br>
									<a href="registro.php?pro='.$dat.'" class="main-button-slider-pol"><strong>Comprar Ahora</strong></a>
									<br>
									<br>
									';
								}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- ***** Features Big Item End ***** -->
		<section class="section colored" id="pricing-plans">
			<div class="container">
				<div class="row">
					<div class="col-lg-12">
						<br>
						<div class="center-heading">
							<h2 class="section-title"><strong>¿Cuales son los beneficios de <? echo $Reg['Nombre'];?>?</strong></h2>
						</div>
					</div>
					<div class="offset-lg-1 col-lg-10">
						<div class="" >
							<? echo $Reg['Descripcion_Producto']; ?>
						</div>
					</div>
				</div>
			</section>
			<section class="section colored" id="pricing-plans">
				<div class="container">
					<!-- ***** Contact Text Start ***** -->
					<div class="col-md-4 col-md-12 col-sm-12 align-self-center">
						<div class="center-heading">
							<h2 class="section-title">Aun no te hemos convencido</h2>
							<p>Te brindamos un increíble servicio a base de productos, con el objetivo de proteger y ayudar a toda tú familia, sabias ¿Qué? Cada producto es una gran oportunidad exclusiva para prevenir y cuidar lo que tú más amas.</p>
						</div>
						<div class="center-body">
							<br>
							<?
							// Convertir a mayúsculas
							$uppercaseText = strtoupper($Reg['Producto']);
							// Codificar en base64
							$base64EncodedText = base64_encode($uppercaseText);

							if(!empty($Desc)){
								//iMPRIMIOS LA IMAGEN
								echo '
								<br>
								<img class="img-thumbnail" src="assets/images/cupones/'.Basicas::BuscarCampos($mysqli,"Img","PostSociales","Id",$_SESSION["tarjeta"]).'" style="width: 15em;">
								<br>
								<a href="registro.php?pro='.$dat.'" class="main-button-slider-pol"><strong>Comprar -'.money_format('%.2n', $Desc).'</strong></a>
								';
							}elseif(!empty($_GET['Pro'])){
								//Generamos el botos de mercado pago
								echo '
								<br>
								<a href="'.$PreLig.$LigaMP.'" class="main-button-slider-pol"><strong>Comprar</strong></a>
								';
							}else{
								echo '
								<br>
								<a href="https://kasu.com.mx/prospectos.php?data='.$base64EncodedText.'" class="main-button-slider-pol"><strong>Contacta un Agente</strong></a>
								';
							}
							?>
						</div>
					</div>
				</div>
			</section>
			<!-- ***** Inicio Seccion de productos ***** -->
			<? require_once 'html/Section_Productos.php'; ?>
			<!-- ***** Footer Start ***** -->
			<section class="section colored" id="pricing-plans">
				<div class="container">
					<div class="col-md-4 col-md-12 col-sm-12 align-self-center">
						<br>
						<br>
						<div class="center-heading">
							<h2 class="section-title"><strong>Restricciones</strong></h2>
						</div>
						<div class="center-heading">
							<? echo $Reg['Restricciones_Producto'];?>
						</div>
					</div>
				</div>
				<!-- ***** Section Title End ***** -->
			</div>
		</section>
		<footer>
			<? require_once 'html/footer.php';?>
		</footer>
		<!-- jQuery -->
		<script src="assets/js/jquery-2.1.0.min.js"></script>
    <!-- Bootstrap -->
    <script src="assets/js/bootstrap.min.js"></script>
    <!-- Plugins -->
    <script src="assets/js/scrollreveal.min.js"></script>
    <script src="assets/js/waypoints.min.js"></script>
    <!-- <script src="assets/js/jquery.counterup.min.js"></script> -->
    <script src="assets/js/imgfix.min.js"></script>
    <!-- Global Init -->
    <script src="assets/js/custom.js"></script>
    <!-- CONSULTA CURP -->
    <script type="text/javascript" async src="https://d335luupugsy2.cloudfront.net/js/loader-scripts/28dd2782-ee7d-4b25-82b1-f5993b27764a-loader.js" ></script>
  </body>
</html>
