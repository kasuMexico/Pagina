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
    <meta name="description" content="consulta los datos de clientes y productos">
    <meta name="author" content="Jose Carlos cabrera Monroy">
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
		<link rel="icon" href="https://kasu.com.mx/assets/images/Index/florkasu.png">
    <title>KASU| API_CUSTOMER</title>
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
	?>
	<!-- *****          SANDBOX      ***** -->
	<?
	require_once '../html/Sandbox.php'; 	//Informacion de versiones
	?>
	<!-- *****     CODIGOS GENERALES     ***** -->
	<section class="section padding-top-70 colored" id="">
		<div class="container">
			<div class="Consulta">
					<h2 class="titulos"><strong>CODIGOS GENERALES</strong></h2>
					<br>
					<p>Estos son los codigos generales generados por <strong>API_CUSTOMER</strong>, y las claves para envio de datos </p>
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
								<td>202</td>
								<td style="text-align: justify;">Consulta de datos exitosa.</td>
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
								<td>406</td>
								<td style="text-align: justify;">El producto excede los limites de edad para el producto seleccionado o El producto que ingresaste no existe</td>
							</tr>
							<tr>
								<td>409</td>
								<td style="text-align: justify;">El cliente no autorizo la consulta de sus datos o la clave de aceptacion que enviaste no es correcta</td>
							</tr>
							<tr>
								<td>412</td>
								<td style="text-align: justify;">La condicion que estas buscando no es correcta o no es apta para ser consultada</td>
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
								<td><strong>tipo_peticion</strong></td>
								<td><strong>DESCRIPCION DE CLAVES DE FUNCIONES</strong></td>
							</tr>
							<tr>
								<td>request</td>
								<td style="text-align: justify;">Esta Consulta retorna los datos que se pueden consultar, retorna las claves que se deben usar en las consultas.</td>
							</tr>
							<tr>
								<td>individual_request</td>
								<td style="text-align: justify;">Te permite realizar una busqueda de un dato en especifico de los siguientes conceptos; Datos de <strong>Contacto</strong> del cliente, Datos <strong>personales</strong> cliente y Datos de un <strong>venta</strong> especifica </td>
							</tr>
							<tr>
								<td>request_block</td>
								<td style="text-align: justify;">Te permite realizar una busqueda pdada un conjunto de datos especifica.</td>
							</tr>
						</table>
					</div>
		</div>
	</section>
	<!-- *****  CONSULTA BASE ***** -->
	<section class="section padding-top-70 " id="">
		<div class="container">
			<div class="row">
				<div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
					<!-- ***** REGISTRAR EL CODIGO DE ENVIO DE EL TOKEN INICIAL  ***** -->
					<div class="row">
						<table class="table table-responsive justify">
							<tr>
								<td><strong>tipo_peticion</strong></td>
								<td><strong>DESCRIPCION DE CLAVES DE FUNCIONES</strong></td>
							</tr>
							<tr>
								<td>API_KEY_AQUI</td>
								<td style="text-align: justify;">Reemplaza el <strong>API_KEY_AQUI</strong> con el <strong>TOKEN</strong> recibido en la petición de <strong>AUTENTICACION</strong></td>
							</tr>
							<tr>
								<td>tipo_peticion</td>
								<td style="text-align: justify;">Consulta general, individual o por bloque segun las claves de consulta</td>
							</tr>
							<tr>
								<td>YOUR_APPUSER</td>
								<td style="text-align: justify;">Tu nombre de usuario registrado en la aplicación KASU.</td>
							</tr>
							<tr>
								<td>CLAVE_CONSULTA</td>
								<td style="text-align: justify;">ingresa la clave de la busqueda a realizar.</td>
							</tr>
							<tr>
								<td>CURP_CODE</td>
								<td style="text-align: justify;">La clave <strong>CURP</strong> con la que generaste el <strong>API_KEY</strong><br>
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
				<div class="col-lg-1 col-md-12 col-sm-12 align-self-center" ></div>
				<div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
					<!-- ***** REGISTRAR EL CODIGO DE ENVIO DE EL TOKEN INICIAL  ***** -->
					<div class="code-window">
						<pre id="codecopi" class="userContent" style="white-space: pre-wrap;">
							<code>
								POST https://apimarket.kasu.com.mx/api/Customer_V1

								Headers:
								Authorization: Bearer API_KEY_AQUI
								Content-Type: application/json
								User-Agent: YourApplicationName/1.0

								{
								"tipo_peticion"     : "request",
								"nombre_de_usuario" : "YOUR_APPUSER",
								"request"           : "CLAVE_CONSULTA",
								"curp_en_uso"       : "CURP_CODE",
								"token_data"    : {
								    "timestamp"  : TIMESTAMP,
								    "expires_in" : EXPIRE_IN
								    }
								}
							</code>
						</pre>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- ***** Features Small Start ***** -->
	<section class="section padding-top-70 colored" id="otros">
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
