<?php
//indicar que se inicia una sesion *JCCM
	session_start();
	//Requerimos el archivo de librerias *JCCM
	  require_once 'eia/librerias.php';
//Javascript que imprime el mensaje de alerta de recepcion de comentario

if(isset($_GET['Msg'])){
	echo "<script type='text/javascript'>
						alert('".$_GET['Msg']."');
				</script>";
}
//Pasamos el get a variable
if(isset($_GET['Lg'])){
	$Lgj = $_GET['Lg'];
}else{
	$Lgj = "Espanol";
}
//Creamos la funcion de conversion
function some_function($VAr) {
	 	$Lenguaje = require_once 'html/EspanolNFT.php';
	return $Lenguaje;
}
function some_function2($VAr) {
	 	$Lenguaje = require_once 'html/InglesNFT.php';
	return $Lenguaje;
}
function some_function3($VAr) {
	 	$Lenguaje = require_once 'html/AlemanNFT.php';
	return $Lenguaje;
}
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
				<meta name="description" content="La primer aseguradora en financiar con NFT's">
				<meta name="keywords" content="Funerario">
				<link rel="canonical" href="kasu.com.mx">
				<meta name="author" content="Jose Carlos Cabrera Monroy">
				<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
				<!-- Metadatos de Facebook -->
				<meta property="og:url" content="https://kasu.com.mx/nft.php" />
				<meta property="og:title" content="THE NFT KASU" />
				<meta property="og:description" content="La primer aseguradora en financiar con NFT's" />
				<meta property="og:image" content="https://kasu.com.mx/assets/images/nft2.gif" />
				<title>THE KASU NFT</title>
				<!-- Additional CSS Files -->
				<link rel="icon" href="https://kasu.com.mx/assets/images/nft2.gif">
				<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons'>
				<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
				<link rel="stylesheet" type="text/css" href="assets/css/font-awesome.css">
				<link rel="stylesheet" href="assets/css/templatemo-softy-pinko.css">
				<link rel="stylesheet" href="assets/css/index.css">
		</head>
		<body>
				<!-- Chat de Facebook -->
				<?
				require_once 'html/CodeFb.php';
				?>
				<!-- Google Tag Manager (noscript)-->
				<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MCR6T6W"
				height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
				<!-- End Google Tag Manager (noscript) -->
				<? require_once 'html/MenuPrincipal.php';?>
				<!-- Portada de pagina -->
				<div class="welcome-area">
				<!-- <div class="welcome-area" style="background-image:url(assets/images/Correo/entregables.jpeg);"> -->
					<br><br><br>
					<div class="Mover">
						<div class="features-small-item">
								<div class="logo">
										<a href="#"><img src="assets/images/kasu_logo.jpeg"></a>
								</div>
								<h1><strong>KASU</strong></h1>
								<br>
								<h2>
									<?
									if(empty($Lgj) || $Lgj == "Espanol"){
										echo "CADA NFT GENERA RECOMPENSAS";
									}elseif($Lgj == "Ingles"){
										echo "EVERY NFT GENERATES REWARDS";
									}elseif ($Lgj == "Aleman") {
										echo "JEDE NFT ERZIELT BELOHNUNGEN";
									}
									?>
								</h2>
								<br>
								<select class="custom-select mr-sm-2"  onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
									<option value="">Lenguaje</option>
									<option value="https://kasu.com.mx/nft.php?Lg=Ingles">English</option>
									<option value="https://kasu.com.mx/nft.php?Lg=Aleman">Deutsch</option>
									<option value="https://kasu.com.mx/nft.php?Lg=Espanol">Español</option>
								</select>
								<br><br>
								<a href="#" class="btn btn-dark btn-lg">vincular tu wallet</a>
						</div>
					</div>
				</div>
				<?
				//echo $Leguaje;
				if(empty($Lgj) || $Lgj == "Espanol"){
					echo some_function($Lgj);
				}elseif($Lgj == "Ingles"){
					echo some_function2($Lgj);
				}elseif ($Lgj == "Aleman") {
					echo some_function3($Lgj);
				}
				?>
				<footer>
					<? require_once 'html/footer.php';?>
				</footer>
				<script src="assets/js/jquery-2.1.0.min.js"></script>
				<script src="assets/js/bootstrap.min.js"></script>
				<script src="assets/js/waypoints.min.js"></script>
				<script src="assets/js/imgfix.min.js"></script>
				<script src="assets/js/jquery.counterup.min.js"></script>
				<script src="assets/js/scrollreveal.min.js"></script>
				<script src="assets/js/custom.js"></script>
				<script type="text/javascript" async src="https://d335luupugsy2.cloudfront.net/js/loader-scripts/28dd2782-ee7d-4b25-82b1-f5993b27764a-loader.js"></script>
		</body>
</html>
