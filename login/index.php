<?PHP
//indicar que se inicia una sesion
	session_start();
	require_once '../eia/librerias.php';
//Validar si existe la session y redireccionar
   if(isset($_SESSION["Vendedor"])){
        header('Location: https://kasu.com.mx/login/Pwa_Principal.php');
    }
		if($_GET['Data'] == 1){
			echo "<script>alert('Este Correo ya ha registrado la contraseña, si requieres otra contraseña porfavor ponte en contacto con tu supervisor');</script>";
		}elseif($_GET['Data'] == 2){
			echo "<script>alert('Las contraseñas que registraste no coinciden');</script>";
		}elseif($_GET['Data'] == 3){
			echo "<script>alert('Haz registrado exitosamente tu contraseña, si la olvidas solicita un nuevo correo a tu supervisor');</script>";
		}
?>
<!DOCTYPE html>
<html lang="ES">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no">
   <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
    <title>KASU</title>
    <!-- CODELAB: Add meta theme-color -->
    <meta name="theme-color" content="#2F3BA2" >
    <!-- CODELAB: Add description here -->
    <meta name="description" content="Una aplicacion para Vendedores">
    <!-- CODELAB: Add iOS meta tags and icons -->
    <meta name="apple-mobile-web-app-capable" content="yes" >
    <meta name="apple-mobile-web-app-status-bar-style" content="black" >
    <meta name="apple-mobile-web-app-title" content="KASU Vendedores" >
		<link rel="apple-touch-icon" href="/login/assets/img/kasu_logo.jpeg">
		<link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
    <!-- CODELAB: Add link rel manifest -->
    <link rel="manifest" href="/manifest.webmanifest" >
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css" >
    <link rel="stylesheet" href="/login/assets/css/styles.min.css" >
    <script src="https://kit.fontawesome.com/21478023ef.js" crossorigin="anonymous"></script>
</head>
<main class="main">
	<body>
	  <!--onload="localize();"-->
		<header class="header">
    <div style="text-align:right; width:100%;">
			<!-- Agregar botón de instalación -->
        <button id="buttonAdd" aria-label="Install"><i class="fas fa-download" style="color:#01579b;"></i></button>
        <button onclick="window.location.reload()" id="butRefresh" aria-label="Refresh"><i class="fas fa-redo-alt" style="color:#01579b;"></i></button>
    </div>
</header>
        <!-- Start: Login Form Clean -->
        <div class="login-clean">
						<?
						if(!empty($_GET['data'])){
							echo '
							<form method="POST" action="php/Funcionalidad_Empleados.php">
									<h1 class="sr-only">Login Form</h1>
									<div class="illustration">
											<img alt="" src="/login/assets/img/logoKasu.png">
									</div>
									<!--Insercion de registros de Gps y fingerprint-->
									<div id="Gps" style="display: none;"></div>
									<div id="FingerPrint" style="display:none ;"></div>
									<input type="text" name="Host" value="'.$_SERVER['PHP_SELF'].'" style="display: none;">
									<input type="text" name="data" value="'.$_GET['data'].'" style="display: none;">
									<input type="text" name="User" value="'.$_GET['Usr'].'" style="display: none;">
									<!--Insercion de registros de Gps y fingerprint -->
									<div class="form-group">
											<input class="form-control" type="password" id="password" name="PassWord1" placeholder="Contraseña">
									</div>
									<div class="form-group">
											<input class="form-control" type="password" id="password" name="PassWord2" placeholder="Contraseña">
									</div>
									<div class="form-group">
											<input type="submit" name="GenCont" value="Generar Contraseña" class="btn btn-primary btn-block">
									</div>
							</form>
							';
						}else{
							echo '
							<form method="POST" action="php/Funcionalidad_Pwa.php">
									<h1 class="sr-only">Login Form</h1>
									<div class="illustration">
											<img alt="" src="/login/assets/img/logoKasu.png">
									</div>
									<!--Insercion de registros de Gps y fingerprint-->
									<div id="Gps" style="display:none ;"></div>
									<div id="FingerPrint" style="display:none ;"></div>
									<input type="text" name="Host" value="'.$_SERVER['PHP_SELF'].'" style="display: none;">
									<!--Insercion de registros de Gps y fingerprint -->
									<div class="form-group">
											<input class="form-control" type="text" id="id_vendedor" name="Usuario" placeholder="Usuario">
									</div>
									<div class="form-group">
											<input class="form-control" type="password" id="password" name="PassWord" placeholder="Contraseña">
									</div>
									<div class="form-group">
											<input type="submit" name="Login" value="Ingresar" class="btn btn-primary btn-block">
									</div>
							</form>
							';
						}
						?>
        </div>
        <script type="text/javascript">
            CODELAB: Register service worker.
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('service-worker.js')
                        .then((reg) => {
                            console.log('Service worker registered.', reg);
                        });
                });
            }
        </script>
        <!-- CODELAB: Add the install script here -->
        <script defer type="text/javascript" src="Javascript/install.js"></script>
				<script defer type="text/javascript" src="Javascript/refresh.js"></script>
        <script defer type="text/javascript" src="Javascript/finger.js" defer async></script>
        <script defer type="text/javascript" src="Javascript/localize.js"></script>
    </body>
</main>
</html>
