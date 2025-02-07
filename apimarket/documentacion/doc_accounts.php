<?
//indicar que se inicia una sesion *JCCM
	session_start();
//se insertan las funciones
		require_once '../../eia/librerias.php';
//realizamos la busqueda en la base de datos
		$filename = basename($_SERVER['PHP_SELF']); // Obtiene el nombre del archivo "doc_accounts.php"
		$extension = pathinfo($filename, PATHINFO_EXTENSION); // Obtiene la extensión del archivo "php"
		$basename = basename($filename, "." . $extension); // Obtiene el nombre del archivo sin la extensión "doc_accounts"
		$word = substr($basename, strpos($basename, "_") + 1); // Obtiene la palabra "accounts"
//Crear consulta
		$sql = "SELECT * FROM ContApiMarket WHERE Nombre = '$word'";
//Realiza consulta
		$res = mysqli_query($mysqli, $sql);
//Si existe el registro se asocia en un fetch_assoc
		if($Reg=mysqli_fetch_assoc($res)){}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- End Google Tag Manager -->
		<meta charset="utf-8">
		<meta name="keywords" content="Cobros">
		<link rel="canonical" href="kasu.com.mx">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Registra, modifica y borra registros de clientes">
    <meta name="author" content="Jose Carlos cabrera Monroy">
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
		<link rel="icon" href="https://kasu.com.mx/assets/images/Index/florkasu.png">
    <title>KASU| API_ACCOUNTS</title>
    <!-- Additional CSS Files -->
		<link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/font-awesome.css">
    <link rel="stylesheet" href="https://kasu.com.mx/assets/css/templatemo-softy-pinko.css">
    <link rel="stylesheet" href="https://kasu.com.mx/assets/css/index.css">
		<link rel="stylesheet" href="../assets/codigo.css">
</head>
<body>
	<!-- La venta emergente debe de estar fuera del div que lo lanza *JCCM -->
	<?
	require_once '../html/menu.php';				//Menu principal
	require_once '../html/Inf_general.php';	//informacion general
	require_once '../html/versiones.php'; 	//Informacion de versiones
	require_once '../html/Sandbox.php'; 	//Informacion de versiones
	?>
	<!-- ***** CODIGOS GENERALES ***** -->
	<section class="section padding-top-70 colored" id="">
		<div class="container">
			<div class="Consulta">
					<h2 class="titulos"><strong>CODIGOS GENERALES</strong></h2>
					<br>
					<p>Estos son los codigos generales generados por <strong>API_REGISTRO</strong>, y las claves para envio de datos </p>
					<br>
			</div>
			<div class="row">
				<div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
					<!-- ***** REGISTRAR EL CODIGO DE ENVIO DE EL TOKEN INICIAL  ***** -->
					<div class="row">
						<table class="table table-responsive justify">
							<tr>
								<td><strong>CODIGOS</strong></td>
								<td><strong>DESCRIPCION</strong></td>
							</tr>
							<tr>
								<td>200</td>
								<td style="text-align: justify;">Peticion exitosa, retorna en formato Json.</td>
							</tr>
							<tr>
								<td>400</td>
								<td style="text-align: justify;">Falta algun dato necesario de los que requiere la solicitud.</td>
							</tr>
							<tr>
								<td>401</td>
								<td style="text-align: justify;">La comunicacion entre el cliente y el servidor fue corrupta, los datos fueron modificadas.</td>
							</tr>
							<tr>
								<td>404</td>
								<td style="text-align: justify;">Petición desconocida, Solo se admiten las claves de funciones de la Documentation.</td>
							</tr>
							<tr>
								<td>405</td>
								<td style="text-align: justify;">El método HTTP utilizado en la solicitud es distinto a <strong>POST</strong></td>
							</tr>
							<tr>
								<td>412</td>
								<td style="text-align: justify;">El cliente ya se encuentra registrado con el producto seleccionado</td>
							</tr>
							<tr>
								<td>417</td>
								<td style="text-align: justify;">La clave CURP que intentas registrar es de una persona fallecida o no existe</td>
							</tr>
							<tr>
								<td>418</td>
								<td style="text-align: justify;">Has excedido el tiempo de operacion para este TOKEN</td>
							</tr>
						</table>
					</div>
				</div>
				<div class="col-lg-1 col-md-12 col-sm-12 align-self-center" ></div>
				<div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
					<!-- ***** REGISTRAR EL CODIGO DE ENVIO DE EL TOKEN INICIAL  ***** -->
					<div class="row">
						<table class="table table-responsive justify">
							<tr>
								<td><strong>CLAVES DE FUNCIONES</strong></td>
								<td><strong>DESCRIPCION</strong></td>
							</tr>
							<tr>
								<td>token_full</td>
								<td style="text-align: justify;">Solicita la generacion de un token de autorizacion de usuo con una vigencia de 10 munitos.</td>
							</tr>
							<tr>
								<td>new_service</td>
								<td style="text-align: justify;">Registra un cliente <strong>KASU</strong>.</td>
							</tr>
							<tr>
								<td>modify_record</td>
								<td style="text-align: justify;">Obtiene el precio de un producto <strong>KASU</strong>.</td>
							</tr>
						</table>
					</div>
					<div><br></div>
					<!-- ***** REGISTRAR EL CODIGO DE ENVIO DE EL TOKEN INICIAL  ***** -->
					<div class="row">
						<table class="table table-responsive justify">
							<tr>
								<td><strong>PRODUCTOS</strong></td>
								<td><strong>DESCRIPCION</strong></td>
							</tr>
							<tr>
								<td>Funerario</td>
								<td style="text-align: justify;">Servicio de <strong>Gastos Funerarios</strong> ligado a la edad.</td>
							</tr>
							<tr>
								<td>Universidad</td>
								<td style="text-align: justify;">Inversion Universitaria para niños menores de <strong>8 años</strong>.</td>
							</tr>
							<tr>
								<td>Retiro</td>
								<td style="text-align: justify;">PLan Privado de Retiro para adultos menores de <strong>65 años</strong>.</td>
							</tr>
						</table>
					</div>
		</div>
	</section>
	<!-- ***** REGISTRAR EL SERVICIO ***** -->
	<section class="section padding-top-70" id="">
		<div class="container">
			<div class="Consulta">
					<h2 class="titulos"><strong>REGISTRAR SERVICIO</strong></h2>
					<br>
					<p>Es importante destacar que los valores de los parámetros deben ser reemplazados con los datos reales del cliente y del producto seleccionado. Además, se debe tener en cuenta que algunos de los parámetros son opcionales según el caso.</p>
					<br>
			</div>
			<div class="row">
				<div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
					<div class="row">
						<table class="table table-responsive justify">
							<tr>
								<td><strong>Parámetro</strong></td>
								<td><strong>Descripción</strong></td>
							</tr>
							<tr>
								<td>API_KEY_AQUI</td>
								<td style="text-align: justify;">Reemplaza el <strong>API_KEY_AQUI</strong> con el <strong>TOKEN</strong> recibido en la petición de <strong>AUTENTICACION</strong></td>
							</tr>
							<tr>
								<td>tipo_peticion</td>
								<td style="text-align: justify;">Especifica el tipo de petición, debe ser establecido segun las tablas de acceso</td>
							</tr>
							<tr>
								<td>YOUR_APPUSER</td>
								<td style="text-align: justify;">Tu nombre de usuario registrado en la aplicación KASU.</td>
							</tr>
							<tr>
								<td>CURP_CODE</td>
								<td style="text-align: justify;">La clave CURP de el cliente con el que interactuaras<br>
							</tr>
							<tr>
								<td>Mail</td>
								<td style="text-align: justify;">Correo Electronico de el cliente</td>
							</tr>
							<tr>
								<td>Telefono</td>
								<td style="text-align: justify;">Telefono Celular de el cliente</td>
							</tr>
							<tr>
								<td>Producto</td>
								<td style="text-align: justify;">Especifica el tipo de producto, debe ser establecido segun las tablas de acceso</td>
							</tr>
							<tr>
								<td>NumeroPagos</td>
								<td style="text-align: justify;">El numero de pagos que el cliente seleccione de la respuesta de la <strong>API_REGISTRO</strong></td>
							</tr>
							<tr>
								<td>Terminos</td>
								<td style="text-align: justify;">Aceptacion de el cliente de los <a href="https://kasu.com.mx/terminos-y-condiciones.php"><strong>Terminos y Condiciones</strong></a></td>
							</tr>
							<tr>
								<td>Aviso</td>
								<td style="text-align: justify;">Aceptacion de el cliente del <a href="https://kasu.com.mx/terminos-y-condiciones.php"><strong>Aviso de Privacidad</strong></a></td>
							</tr>
							<tr>
								<td>Fideicomiso</td>
								<td style="text-align: justify;">Solicitud de el cliente de ingreso al <a href="https://kasu.com.mx/Fideicomiso_F0003.pdf"><strong>Fideicomiso F/0003</strong></a></td>
							</tr>
							<tr>
								<td>Calle</td>
								<td style="text-align: justify;">Es la Calle del cliente<td>
							</tr>
							<tr>
								<td>Numero</td>
								<td style="text-align: justify;">Es Numero de casa del cliente<td>
							</tr>
							<tr>
								<td>Colonia</td>
								<td style="text-align: justify;">Es la Colonia del cliente<td>
							</tr>
							<tr>
								<td>Municipio</td>
								<td style="text-align: justify;">Es el Municipio del cliente<td>
							</tr>
							<tr>
								<td>Codigo_Postal</td>
								<td style="text-align: justify;">Es el Codigo Postal del cliente<td>
							</tr>
							<tr>
								<td>Estado</td>
								<td style="text-align: justify;">Es el Estado del cliente<td>
							</tr>
							<tr>
								<td>TIMESTAMP</td>
								<td style="text-align: justify;">EL tiempo en el cual se genero el token retornado por la peticion de <strong>ACCESO</strong> a <strong>API_REGISTRO</strong></td>
							</tr>
							<tr>
								<td>EXPIRE_IN</td>
								<td style="text-align: justify;">EL tiempo en el cual sera valido el token retornado por la peticion de <strong>ACCESO</strong> a <strong>API_REGISTRO</strong></td>
							</tr>
						</table>
					</div>
				</div>

				<div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
					<!-- ***** REGISTRAR EL CODIGO DE ENVIO DE EL TOKEN INICIAL  ***** -->
					<div class="code-window">
						<pre id="codecopi" class="userContent" style="white-space: pre-wrap;">
							<code>
								POST https://apimarket.kasu.com.mx/api/Registro_V1

								Headers:
								Authorization: Bearer API_KEY_AQUI
								Content-Type: application/json
								User-Agent: Your-Application-Name/1.0

								{
									"tipo_peticion"		: "new_service",
									"nombre_de_usuario"	: "YOUR_APPUSER",
									"curp_en_uso"		: "CURP_CODE",
									"mail"			: "CORREO_ELECTRONICO",
									"telefono"		: TELEFONO,
									"producto"		: "PRODUCTO",
									"numero_pagos"		: NUMERO_PAGOS,
									"terminos"		: "ACCEPT",
									"aviso"			: "ACCEPT",
									"fideicomiso"		: "ACCEPT",
									"direccion"		:
									{
									    "calle"			: "CALLE",
									    "numero"		: NUMERO,
									    "colonia"		: "COLONIA",
									    "municipio"		: "MUNICIPIO",
									    "codigo_postal"		: CODIGO_POSTAL,
									    "estado"		: "ESTADO"
									    },
									"token_data"		:
									{
									    "timestamp"		: TIMESTAMP,
									    "expires_in"		: EXPIRE_IN
									    }
								}
							</code>
						</pre>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- ***** REGISTRO DE DATOS DE REGISTRAR EL SERVICIO ***** -->
	<section class="section padding-top-70 colored" id="">
		<div class="container">
			<div class="Consulta">
					<h2 class="titulos"><strong>REGISTRO DE DATOS DE REGISTRAR EL SERVICIO</strong></h2>
					<br>
					<p>Resuelve los datos de las peticiones con codigos de error cuando no resuelve correctamente la <strong>API_REGISTRO</strong>, y requiere intrucciones que le indiquen que funcion ejecutar, aqui podras encontrar aambas para que puedas determinar el mejor funcionamiento de la <strong>API_REGISTRO</strong> </p>
					<br>
			</div>
			<div class="row">
				<div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
					<!-- ***** REGISTRAR EL CODIGO DE ENVIO DE EL TOKEN INICIAL  ***** -->
					<div class="row">
						<table class="table table-responsive justify">
							<tr>
								<td><strong>CLAVE</strong></td>
								<td><strong>DESCRIPCION DE CLAVES DE FUNCIONES</strong></td>
							</tr>
							<tr>
								<td>registro_servicio</td>
								<td style="text-align: justify;">Registra un cliente <strong>KASU</strong>.</td>
							</tr>
						</table>
					</div>
					<div>
						<br><br>
					</div>
					<!-- ***** REGISTRAR EL CODIGO DE ENVIO DE EL TOKEN INICIAL  ***** -->
					<div class="row">
						<table class="table table-responsive justify">
							<tr>
								<td><strong>CODIGO</strong></td>
								<td><strong>ERRORES DE PETICION</strong></td>
							</tr>
							<tr>
								<td>201</td>
								<td style="text-align: justify;">Registro exitoso de cliente, con status de PREVENTA.</td>
							</tr>
							<tr>
								<td>406</td>
								<td style="text-align: justify;">El producto excede los limites de edad para el producto seleccionado o El producto que ingresaste no existe</td>
							</tr>
							<tr>
								<td>409</td>
								<td style="text-align: justify;">El cliente no acepto el fideicomiso, Aviso de privacidad o los Terminos y condiciones</td>
							</tr>
						</table>
					</div>
				</div>
				<div class="col-lg-1 col-md-12 col-sm-12 align-self-center" ></div>
				<div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
					<div class="row">
						<table class="table table-responsive justify">
							<tr>
								<td><strong>LLAVE</strong></td>
								<td><strong>RESPUESTA POSITIVA</strong></td>
							</tr>
							<tr>
								<td>mensaje</td>
								<td style="text-align: justify;">Retorna el mensaje de exito contiene el <strong>SERVICIO</strong></td>
							</tr>
							<tr>
								<td>nombre</td>
								<td style="text-align: justify;">Retorna los el nombre de el cliente registrado en <strong>RENAPO</strong></td>
							</tr>
							<tr>
								<td>CURP</td>
								<td style="text-align: justify;">La clave curp que se ligo a tu servicio <strong>KASU</strong></td>
							</tr>
							<tr>
								<td>mail</td>
								<td style="text-align: justify;">Retorna el Email para enviar correo segun la <strong>API_COBROS</strong></td>
							</tr>
							<tr>
								<td>poliza</td>
								<td style="text-align: justify;">EL <strong>TOKEN</strong> unico que esta ligado a tu servicio</td>
							</tr>
							<tr>
								<td>Status</td>
								<td style="text-align: justify;">Retorna el Status del servicio para enviar a <strong>API_COBROS</strong></td>
							</tr>
							<tr>
								<td>Costo</td>
								<td style="text-align: justify;">Retorna el costo del servicio para enviar a <strong>API_COBROS</strong></td>
							</tr>
						</table>
						<div class="Consulta">
							<br>
							<p>Si no cuentas con acceso a la <strong>API_COBROS</strong>, no se retornan los datos para realizar cobros y el api envia un correo de forma automatica al cliente para que realice el pago</p>
							<br>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- ***** Features Small Start ***** -->
	<section class="section padding-top-70" id="otros">
			<div class="container">
					<div class="row">
							<div class="col-lg-12">
									<div class="row">
											<!-- ***** Section Title Start ***** -->
													<div class="col-lg-12">
															<div class="center-heading">
																	<h2 class="section-title">Otras APIS que te pueren interesar</h2>
																	<br>
															</div>
													</div>
													<? require_once '../html/select_api.php'; ?>
									</div>
							</div>
					</div>
			</div>
	</section>
	<!-- ***** Features Small End ***** -->
	<footer>
		<? require_once ('../assets/footer.php');?>
	</footer>
	<!--Copiar -->
	<script>
		function copiarAlPortapapeles(codecopi) {
			var aux = document.createElement("input");
			aux.setAttribute("value", document.getElementById(codecopi).innerHTML);
			document.body.appendChild(aux);
			aux.select();
			document.execCommand("copy");
			document.body.removeChild(aux);
		}
	</script>
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
