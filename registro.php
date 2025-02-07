<?php
//indicar que se inicia una sesion aqui estoy
	session_start();
	//Requerimos el archivo de librerias *JCCM
	  require_once 'eia/librerias.php';
	//Modificamos el archivo donde se envia los registros para hacer pruebas
	$ArchivoRegistro = "/eia/php/Registrar_Venta.php"; //Archivo para los registros
	//$ArchivoRegistro = "/eia/php/CopVta.php"; //Archivo para los registros
/*******************************************************************************
    INICIO Funcion valida el status de la venta para lanzar las ventanas emergentes
*******************************************************************************/
//Se crea una variable que carga la funcion localizar
    $localizar = "localize()";
//Valida que la session producto existe
    if(isset($_SESSION["Producto"])){
//Validamos el cupon registrado
				if(!empty($_SESSION["tarjeta"])){
					//Buscamos si el producto puede aplicar el descuento
							$IdProd = Basicas::BuscarCampos($mysqli,"Id","Productos","Producto",$_SESSION["Producto"]);
					//Buscamos las variables de los cupones
							$Img = Basicas::BuscarCampos($mysqli,"Img","PostSociales","Id",$_SESSION["tarjeta"]);
							$Descuento = Basicas::BuscarCampos($mysqli,"Descuento","PostSociales","Id",$_SESSION["tarjeta"]);
							$Prod = Basicas::BuscarCampos($mysqli,"Producto","PostSociales","Id",$_SESSION["tarjeta"]);
					//Buscamos el id de el cupon asignado
							$IdPCup = Basicas::BuscarCampos($mysqli,"Id","Productos","Producto",$Prod);
					//Creamos el nuevo precio de el producto
							if($IdProd >= $IdPCup){
								$Valor = 1;
								$Costo = $_SESSION["Costo"]-$Descuento;
							}else{
								$Valor = "El codigo de descuento no se puede aplicar a este producto";
							}
				}else{
					$Costo = $_SESSION["Costo"];
				}
//Valida que la session ventana exista y asigna el valor a la variable
        if(isset($_SESSION["Ventana"])){
        //Se asigna un valor a la ventana emergente
            $Ventana = $_SESSION["Ventana"];
            $localizar = "";
        }else{
            $Ventana = "Ventana1";
            $localizar = "";
        }
    }else if($_GET['stat'] == 1){
        $COntVtan = "
            <h2>FELICIDADES!!!</h2>
            <pre id='resultadoVta'></pre>
            <h3>Estas a un paso de concluir tu registro </h3>
            <p>En este momento te estamos enviando tu tarjeta de cliente a tu direccion</p>
            <p>Ayudanos ingresando tu clave CURP, para ligarla a tu pago</p>
            <form id='Registro-CURP' method='POST' action='.$ArchivoRegistro.'>
                <input name='stat' id='stat' value='".$_GET['stat']."' style='display: none;'>
                <input name='Dtpg' id='Dtpg' value='".$_GET['Dtpg']."' style='display: none;'>
                <input name='CurBen' id='CURP' class='InputCurp' placeholder='Clave CURP' maxlength='18' oninput='validarInputVta(this)' required>
                <input type='submit' class='main-button' name='ActuPago' value='VERIFICAR Y VALIDAR MI PAGO'>
            </form>
            ";
        //Si ya se pago se registra el pago y se muestra ventana de pago
        $Ventana = "Ventana3";

    }else if($_GET['stat'] == 2){
        $COntVtan = "
            <h2>Parece que hubo un error!!</h2>
						<h3>".$_GET['Cte']."</h3>
            <h3>Parece que no se pudo realizar el cargo a tu cuenta, pero no te preocupes</h3>
            <p>En este momento te estamos enviando tus fichas de pago a tu e-mail</p>
            <p>una vez realizado tu pago, este se verá acreditado en un plazo máximo de 24 horas habiles</p>
            ";
        //Si ya se pago se registra el pago y se muestra ventana de pago
        $Ventana = "Ventana3";
    }else if($_GET['stat'] == 3){
        $COntVtan = "
            <h2>FELICIDADES!!!</h2>
						<h3>".$_GET['Cte']."</h3>
            <h3>Estas a un paso de concluir tu registro </h3>
            <p>En este momento te estamos enviando tus fichas de pago a tu e-mail</p>
            <p>una vez realizado tu pago, este se verá acreditado en un plazo máximo de 24 horas habiles</p>
						<a href='".$_GET['liga']."' class='main-button'><strong>ir a Pagar ahora</strong></a>
            ";
        //Si ya se pago se registra el pago y se muestra ventana de pago
        $Ventana = "Ventana3";
    }else if($_GET['stat'] == 4){
        $COntVtan = "
            <h2>".$_GET['Name']."</h2>
            <h3>TU YA ERES CLIENTE KASU </h3>
            <p>El CURP ".$_GET['curp']." ya se encuentra registrado</p>
            <p>Llama a nuestro centro de atencion si requieres mas informacion</p>
            <a href='tel:+527125975763' class='main-button' >LLAMAR A KASU</a>
            ";
        //Si ya se pago se registra el pago y se muestra ventana de pago
        $Ventana = "Ventana3";
    }else if($_GET['stat'] == 5){
        $COntVtan = "
            <h2>".$_GET['Name']."</h2>
            <h3>LA CLAVE CURP QUE REGISTRASTE NO EXISTE</h3>
            <p>El CURP ".$_GET['curp']." no se encuentra en los datos del Renapo o pertenece a una persona fallecida</p>
            <p>Llama a nuestro centro de atencion si requieres mas informacion</p>
            <a href='tel:+527125975763' class='main-button' >LLAMAR A KASU</a>
            ";
        //Si ya se pago se registra el pago y se muestra ventana de pago
        $Ventana = "Ventana3";
    }
		//Selector de creditos
    if($_SESSION["Producto"] == "Universidad"){
        $sel18 = "<option value='36' selected>3 años</option>
									<option value='60'>5 años</option>";
        $sel24 = "<option value='96'>8 años</option>";
    }else{
        $SelTipServ = "
        <p>Selecciona el tipo de servicio</p>
        <select name='TipoServicio'>
          <option value='Tradicional'>Tradicional</option>
          <option value='Ecologico' selected>Ecologico</option>
          <option value='Cremacion'>Cremacion</option>
        </select>
        ";
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
		})(window,document,'script','dataLayer','GTM-MCR6T6W');</script>
		<!-- End Google Tag Manager -->
		<meta charset="utf-8">
		<meta name="description" content="Kasu se preocupa por brindarte los mejores productos a tú alcance,  gracias por tu registro para adquirir nuestros servicios.">
		<meta name="keywords" content="registro">
		<link rel="canonical" href="https://kasu.com.mx/registro.php">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" href="assets/images/kasu_logo.jpeg">
		<title>Registro| KASU</title>
		<link rel="stylesheet" href="assets/css/Compra.css">
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" media="screen">
		<script type="text/javascript" src="eia/javascript/Registro.js"></script>
		<script type="text/javascript" src="eia/javascript/validarcurp.js"></script>
		<!-- Inicio Librerias prara las ventanas emergentes automaticas-->
		<script src='https://code.jquery.com/jquery-3.3.1.slim.min.js' integrity='sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo' crossorigin='anonymous'></script>
		<script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js' integrity='sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49' crossorigin='anonymous'></script>
		<script src='https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js' integrity='sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy' crossorigin='anonymous'></script>
		<!-- Fin Librerias prara las ventanas emergentes automaticas-->
</head>
<body onload="<?PHP echo $localizar; ?>">
		<!-- Chat de Facebook -->
		<?
		require_once 'hmtl/CodeFb.php';
		?>
		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MCR6T6W"
		height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->
		<section id="Ventanas">
				<!--Inicio Creacion de las ventanas emergentes-->
				<script type='text/javascript'>
					var IntPhp = '<?php echo $_SESSION["Tasa"];?>';
					var CostPhp = '<?php echo $Costo;?>';
					//se lanzan las ventanas emergentes
					$(document).ready(function() {
						$('#<?PHP echo $Ventana;?>').modal('toggle')
					});
				</script>
				<!--Ventana de Registro de Clave CURP -->
				<div class="modal fade" id="Ventana1" tabindex="-1" role="dialog" aria-labelledby="Ventana1" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered" role="document">
						<div class="modal-content">
							<div class="modal-body">
								<div class="Formulario">
									<h3>¿El servicio es para ti?</h3>
									<br>
									<select id="RegSelCur" onChange="OcuForCurp(this)">
										<option value="RegCurBen">Si</option>
										<option value="RegCurCli">No</option>
									</select>
									<pre id="resultado"></pre>
								</div>
								<div class="Formulario" style="display:;">
									<form method="POST" id="RegCURPCliente" action="<?PHP echo $ArchivoRegistro;?>">
										<h2>Ingresa tu clave CURP</h2>
										<p>Usamos este dato para comprobar tus datos de registro y saber quién esta comprando KASU</p>
										<input name="CurClie"  id="ClaveCURP" class="InputCurp" placeholder="Clave CURP" maxlength="18" oninput="validarInput(this)" required>
										<input type="submit" name="BtnRegCurBen" id="Enviar" class="main-button" style="background-color:#012F91;color:white;" value="Calcular" >
									</form>
								</div>
								<div class="Formulario" style="display:none;">
									<form method="POST" id="RegCURPBenef" action="<?PHP echo $ArchivoRegistro;?>">
										<h2>Ayudanos a conocer al beneficiario</h2>
										<p>Usamos este dato para comprobar tus datos y saber quién esta comprando KASU</p>
										<input name="NomBen" id="Nombre" placeholder="Nombre del beneficiario" required>
										<input class="InputCurp" name="CurBen" id="ClaveCURP" placeholder="Clave CURP del beneficiario" id="CurCli" maxlength="18" oninput="validarInput(this)" required>
										<input type="email" name="EmaBen" id="Email" placeholder="Correo Electronico del beneficiario" required>
										<input type="submit" name="BtnRegCurCli" id="Enviar" class="main-button" style="background-color:#012F91;color:white;" style="background-color:#012F91;color:white;" value="Calcular" id="BtnnCURPVta">
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!--Ventana de Forma de Pago -->
				<div class="modal fade" id="Ventana2" tabindex="-1" role="dialog" aria-labelledby="Ventana2" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered" role="document">
						<div class="modal-content">
							<div class="modal-body">
								<form method="POST" id="RegistrarCupon" action="<?PHP echo $ArchivoRegistro;?>">
									<input type="text" name="Cupon"  id="Cupon" value="<?PHP echo $_SESSION["tarjeta"];?>" style="display: none;">
									<div class="Formulario">
										<h2>
											<?PHP echo $_SESSION["NombreCOm"];?>
										</h2>
										<hr style="width:85%;">
										<?
										//El usuario cuenta con un cupon
										if($Valor === 1){
											//Imprimimos la miniatura de el cupon
											echo '
													<hr>
													<img class="img-thumbnail" src="assets/images/cupones/'.$Img.'" style="width: 15em;">
													<div class="Precio">
															<p class="derTit">Precio Inicial</p>
															<h4 class="IzqTit"> '.money_format('%.2n', $_SESSION["Costo"]).' Mxn</h4>
															<p>Tienes un descuento de '.money_format('%.2n', $Descuento).' Mxn</p>
													</div>
													<hr>
													';
										}else{
											echo $Valor;
										}
										?>
										<!--Inicio calculos de pago-->
										<p><strong>Informacion</strong></p>
										<p>Edad: <?php echo $_SESSION["Edad"]?></p>
										<p>Producto: <?php echo $_SESSION["Producto"]?></p>
										<p><strong>Costos</strong><br>
										<div class="Precio" id="PagosCosto"></div>
										<!--Fin calculos de pago-->
										<br>
										<p>Selecciona el tiempo de pago</p>
										<select onChange="CalPre(this)" name="Meses" id="SelectTiempo">
											<option value="0">Selecciona como deseas pagar</option>
											<option value="0">Pago Único</option>
											<?PHP
											if($Valor != 1 AND $_SESSION["Producto"] != "Universidad"){
													//Mostramos los periodos de pago si NO tiene un descuento
													echo '
													<option value="3">3 Meses</option>
													<option value="6">6 Meses</option>
													<option value="9">9 Meses</option>
													';
												}
		                    echo $sel18;
		                    echo $sel24;
		                ?>
										</select>
										<?php
		                    echo $SelTipServ;
		                ?>
									</div>
									<div class="Legales">
										<p><input type="checkbox" name="Terminos" value="acepto" required> Acepto los <a href="https://kasu.com.mx/terminos-y-condiciones.php/">Terminos y condiciones</a></p>
										<p><input type="checkbox" name="Aviso" value="acepto" required> Conozco el <a href="https://kasu.com.mx/terminos-y-condiciones.php/">Aviso de privacidad</a></p>
										<p><input type="checkbox" name="Fideicomiso" value="acepto" required> Solicito el <a href="https://kasu.com.mx/Fideicomiso_F0003.pdf">Acceso a Fideicomiso</a></p>
									</div>
									<div class="Formulario">
										<input type="submit" name="BtnMetPago" id="BtnnPagoVta" class="main-button" style="background-color:#012F91;color:white;" value="Pagar" >
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
				<!--Ventana de Resultado de Registro -->
				<div class="modal fade" id="Ventana3" tabindex="-1" role="dialog" aria-labelledby="Ventana3" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered" role="document">
						<div class="modal-content">
							<div class="modal-body">
								<div class="Formulario">
									<!--iMPRESION DE DATOS DEL ENVIO-->
									<?PHP echo $COntVtan;?>
								</div>
							</div>
						</div>
					</div>
				</div>
		</section>
		<!--Registro de Venta Principal -->
		<section id="Formulario">
			<div class="container-fluid">
			<div class="row mh-100vh">
					<div class="img_familia" class="Contenedor">
							<img src="https://kasu.com.mx/assets/images/registro/familiaformulario.png" align="left">
					</div>
					<div class="AreaTrabajo">
							<div class="Contenedor">
									<!-- Formulario de Registro de Ventas -->
									<form method="POST" id="RegistroContacto" action="<?PHP echo $ArchivoRegistro;?>" <? if(!isset($_GET['pro'])){echo 'onsubmit = "validate(event, this);"';}?>>
											<div class="logo">
													<img src="assets/images/kasu_logo.jpeg">
											</div>
											<h1 style="text-align: center;">REGISTRO DE TÚ SERVICIO</h1>
											<br>
											<div class="Formulario">
													<!--Insercion de registros de Gps y fingerprint-->
													<div id="Gps" style="display: none;"></div>
													<div id="FingerPrint" style="display: none;"></div>
													<input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
													<input type="text" name="Cupon" id="Cupon" value="<?PHP echo $_SESSION[" data"];?>" style="display: none;">
													<!--Insercion de registros de Gps y fingerprint-->
													<input type="email" name="Mail" id="Mail" placeholder="Correo electronico" required>
													<input type="tel" name="Telefono" id="Telefono" placeholder="Telefono" required>
													<input placeholder="Direccion" id="Direccion" name="Direccion" required>
											</div>
											<div class="Botones">
															<?php
															if($_GET['pro'] == 1){
																echo '
																		<!-- input type="checkbox" id="Btn-Fun" class="only-one""><label for="Btn-Fun"><img id="img-fun" src="assets/images/Index/funer.png"></label -->
																		<img id="img-fun" src="assets/images/Index/funer.png">
																		</div>
																<div id="servicio" class="Formulario">
																		<input type="text" disabled name="pro" style="border:none;text-align: center;pointer-events: checked;" value="Funerario">
																		<input type="text" name="Producto" style="display: none;" value="Funerario">
																</div>';
															}else if($_GET['pro'] == 2){
																echo '
																		<!-- input type="checkbox" id="Btn-Esc" class="only-one""><label for="Btn-Esc"><img id="img-fun" src="assets/images/Index/universitario.png"></label></div -->
																		<img id="img-fun" src="assets/images/Index/universitario.png">
																		</div>
																<div id="servicio" class="Formulario">
																		<input type="text" disabled name="pro" style="border:none;text-align: center;pointer-events: checked;" value="Universidad">
																		<input type="text" name="Producto" style="display: none;" value="Universidad">
																</div>';
															}else{
																echo '
																		<input type="checkbox" id="Btn-Fun" name="Producto" value="Funerario" class="only-one" onclick="selectServ(\'Funerario\')">
				                        		<label for="Btn-Fun"><img id="img-fun" src="assets/images/Index/funer.png" alt="Servicio Funerario" onclick="selectServ(\'Funerario\')"></label>
				                        		<input type="checkbox" id="Btn-Esc" name="Producto" value="Universidad" class="only-one" onclick="selectServ(\'Universidad\')">
				                        		<label for="Btn-Esc"><img id="img-esc" src="assets/images/Index/universitario.png" alt="Servicio Universitario" onclick="selectServ(\'Universidad\')"></label>
				                    		</div>
																<div id="servicio" class="Formulario"></div>';
															}
															?>
											<div class="Formulario">
													<input type="submit" name="Registro" id="BtnnContactoVta" class="main-button" value="Continuar mi compra" >
											</div>
											<div class="Ligas">
														<a style="color: #911F66" href="/">Regresar a KASU</a>
														<a style="color: #012F91" href="https://kasu.com.mx/terminos-y-condiciones.php">Términos y condiciones</a>
											</div>
										</div>
								  </form>
							</div>
					</div>
			</div>
		</div>
		</section>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="eia/javascript/AlPie.js"></script>
		<script type="text/javascript" src="eia/javascript/finger.js"></script>
		<script type="text/javascript" src="eia/javascript/localize.js"></script>
		<script type="text/javascript">
			function selectServ(e) {
				if (e == "Funerario") {
					document.getElementById("servicio").innerHTML = '<input type="text" name="Producto" style="border:none;text-align: center;center;pointer-events: none;" value="Funerario">';
				} else if (e == "Universidad") {
					document.getElementById("servicio").innerHTML = '<input type="text" name="Producto" style="border:none;text-align: center;center;pointer-events: none;" value="Universidad"	>';
				} else {
					document.getElementById("servicio").innerHTML = '';
				}
			}
		</script>
	  <script type="text/javascript" async src="https://d335luupugsy2.cloudfront.net/js/loader-scripts/28dd2782-ee7d-4b25-82b1-f5993b27764a-loader.js" ></script>
</body>
</html>
