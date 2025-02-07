<?php
//Archivo con el que se generan las polizas de el cliente
		ob_start();
		//Se incluyen lo archivos prinicipales
		require '../login/Generar_PDF/dompdfMaster/dompdf_config.inc.php'; /*Codigo de Agustin Genera el pdf*/ Este archivo esta duplicado
		require '../eia/php/Funciones_kasu.php';
		//SE ingresa la fecha
		date_default_timezone_set('America/Mexico_City');
		// datos de la fecha en php JOSE CARLOS CABRERA MONROY
		$fecha = date("Y-m-d-H-i-s");
		//Se desencripta el dato
		$dat = base64_decode($_GET['code']);
		//Consultas a la base de datos para la busqueda de el cliente
		$id = $mysqli -> real_escape_string($dat);
		$sql = "SELECT * FROM Usuario WHERE IdContact =$id";
			if($result = $mysqli->query($sql)){// Si obtienes datos
					while($row = $result->fetch_array()){//retorname los datos en un arreglo
						//Datos  del usuario
						 $curp = $row['ClaveCurp'] ?? 'Sin Datos';
						 $cont = $row['IdContact'] ?? 'Sin Datos';
					}
			}
			// datos del usuario en la tabla Contacto
			$sql = "SELECT * FROM Contacto WHERE id=$cont";
			if($result = $mysqli->query($sql)){	// Si obtienes datos
					while($row = $result->fetch_array()){	//retorname los datos en un arreglo
						//Datos  del usuario con contacto
						 $email = $row['Mail'] ?? 'Sin Datos';
						 $phone = $row['Telefono'] ?? 'Sin Datos';
						 $direccion = $row['Direccion'] ?? 'Sin Datos';
					}
			}
			//Busqueda de datos en la tabla venta
			$sql = "SELECT * FROM Venta WHERE IdContact = $cont";
			if($result = $mysqli->query($sql)){	// Si obtienes datos
					while($row = $result->fetch_array()){	//retorname los datos en un arreglo
						//Datos  del usuario con contacto
						 $name = $row['Nombre'] ?? 'Sin Datos';
						 $Producto = $row['Producto'] ?? 'Sin Datos';
						 $TipoServicio = $row['TipoServicio'] ?? 'Sin Datos';
						 $Costo = $row['CostoVenta'] ?? 'Sin Datos';
						 $Status = $row['Status'] ?? 'Sin Datos';
						 $IdFIrma = $row['IdFIrma'] ?? 'Sin Datos';
						 $FechaR = $row['FechaRegistro'] ?? 'Sin Datos';
					}
			}
			//Se redirecciona a la pagina principal si no se ha pagado el servicio
			//Bloqueo de seguridad para que no impriman poizas que no han sido pagadas
			if($Status != "ACTIVO" || $Status != "ACTIVACION"){
				//echo "Estatus de la poliza --> ".$Status;
				echo'<script type="text/javascript">
						alert("Esta poliza no se encuentra liquidada en su totalidad");
						window.location="https://kasu.com.mx/";
						</script>';
			}
			// muestra el formato internacional para la configuraci&oacute;n regional Mexico
			setlocale(LC_MONETARY, 'es_MX');
			$Costo = money_format('%i', $Costo) . "\n";
			//Seleccion de costo a imprimir
			if($Producto == "Universidad") {
			 	$productoA = "Servicio Universitario";
			}else{
				$productoA = substr($Producto, 0, -3)." a ".substr($Producto, 3, 2)." a&ntilde;os";
			}
//SE preconstruye el archivo para insertarlo en el HTML
?>
<!--SE crea el archivo a imprimir en pdf mediante estructura html-->
<html lang='es'>
		<head>
				 <title>POLIZA KASU</title>
				 <link rel="stylesheet" href="https://kasu.com.mx/assets/css/poliza.css">
		</head>
		<body>
				<div class="container">
						<table class="t-h">
								<tr>
										<td><h1 class="ha-text"><strong> SERVICIO A FUTURO / SOLICITUD DE APORTACI&Oacute;N </strong></h1></td>
						    </tr>
								<tr>
										<td><h1 class="hb-text"> PARTES DE CONTRATO</h1></td>
								</tr>
						</table>
					  <img src="https://kasu.com.mx/assets/poliza/img2/transp.jpg" class="header">
						<br><br><br><br>
						<div class="w-tab t-one">
								<img src="https://kasu.com.mx/assets/poliza/img2/1.jpg" class="h-lo">
							  <table class="date">
							  		<tr>
							      		<td>NOMBRE:</td>
							       		<td></td>
							         	<td>CAPITAL &amp; FONDEO MEXICO S.A.P.I. SOFOM ENR</td>
							      </tr>
							      <tr>
							          <td>DOMICILIO:</td>
							          <td></td>
							          <td>Avenida Presiente Masarik,No. 61,Int 901-9, Colonia Polanco V secci&oacute;n</td>
							      </tr>
							      <tr>
							          <td>ACTA CONSTITUTIVA:</td>
							          <td></td>
							          <td>30,515 volumen ordinario DXXV(QUINIENTOS TREINTA Y CINCO)</td>
							      </tr>
							      <tr>
							          <td>PODER:</td>
							          <td></td>
							          <td>30,515 volumen ordinario DXXV(QUINIENTOS TREINTA Y CINCO)</td>
							      </tr>
							 </table>
						</div>
						<br>
						<img src="https://kasu.com.mx/assets/poliza/img2/LINE7.jpg" class="h-line">
						<div class="w-tab">
								<br>
								<img src="https://kasu.com.mx/assets/poliza/img2/2.jpg" class="h-lt">
						    <table class="date">
						    		<tr>
						        		<td>NOMBRE:</td>
						            <td>KASU, SERVICIOS A FUTURO S.A. DE C.V.</td>
						        </tr>
						        <tr>
						            <td>DOMICILIO:</td>
						            <td>Privada Vire, No.2 Int.10 col.Centro,Atlacomulco esatdo de M&eacute;xico</td>
						        </tr>
						        <tr>
						            <td>ACTA CONSTITUTIVA:</td>
						            <td>30,569 volumen ordinario DXXIX(QUINIENTOS TREINTA Y NUEVE)</td>
						        </tr>
						        <tr>
						            <td>PODER:</td>
						            <td>30,569 volumen ordinario DXXIX(QUINIENTOS TREINTA Y NUEVE)</td>
						        </tr>
						     </table>
						</div>
						<br>
						<img src="https://kasu.com.mx/assets/poliza/img2/LINE7.jpg" class="h-line">
						<br><br>
						<img src="https://kasu.com.mx/assets/poliza/img2/3.jpg" class="h-ltr">
						<br>
						<table class="ba date">
								<tr>
										<td class="mb"  colspan="3"> Solicitante:</td>
										<td class="mb ct"> <?php if(empty($name)){ echo $curp; } else { echo htmlentities($name, ENT_QUOTES, "UTF-8"); } ?></td>
								</tr>
								<tr>
										<td class="bl">Tel&eacute;fono:</td>
										<td class="bl ct"><?php  echo $phone; ?></td>
										<td class="bl">e-mail</td>
										<td class="bl ct"><?php echo $email; ?></td>
								</tr>
								<tr>
										<td class="mb"  colspan="3"> Direccion:</td>
										<td class="mb ct"> <?php echo $direccion; ?></td>
								</tr>
						</table>
						<br>
						<img src="https://kasu.com.mx/assets/poliza/img2/LINE7.jpg" class="h-line">
						<br>
						<img src="https://kasu.com.mx/assets/poliza/img2/4.jpg" class="h-lf">
						<br><br>
						<table class="ser">
									<tr>
									   	<td class="mw">Servico Contratado</td>
									   	<td class="mw">Descripcion</td>
									</tr>
									<tr>
											<td class="mw">		<?php echo $productoA; ?></td>
											<td class="mda">	<?php echo $Costo; ?></td>
									</tr>
									<tr>
											<td class="mw">Identificador de Poliza </td>
											<td class="md"><?php echo $IdFIrma; ?></td>
									</tr>
									<tr>
											<td class="mw">Tipo de servicio</td>
										  <td class="md"><?php echo $TipoServicio; ?></td>
									</tr>
						</table>
						<br><br>
						<img src="https://kasu.com.mx/assets/poliza/img2/LINE7.jpg" class="h-line">
						<img src="https://kasu.com.mx/assets/poliza/img2/5.jpg" class="h-lfi">
						<table class="ba date">
								<tr>
										<td>
													<ul class="ul-adver">
														<?php
														if ($Producto == "Universidad") {
															echo "
																		<li>El presente se sujeta a las disposiciones estipuladas en el contrato de fideicomiso o irrevocable de garant&iacute;a no.f/0009.</li>
																		<li>Capital &amp; Fondeo de M&eacute;xico, puede reservarse el derecho de proveer el servicio cuando el aportante incumpla total o parcialmente en alguna de sus obligaciones con la misma.</li>
																		<li>La empresa se reserva el servicio si se incurre en omisiones sobre la informaci&oacute;n asentada en la presente;</li>
																		<ul>
																			<li>&nbsp;&nbsp;Sobre siniestros acaecidos como consecuci&oacute;n de conflictos armados o por 'cat&aacute;strofe o calamidad nacional', Siniestros de caracter catastr&oacute;fico acaecidos como consecuencia de reacci&oacute;n o radiaci&oacute;n nuclear o contaminaci&oacute;n radiactiva.</li>
																			<li>&nbsp;&nbsp;Cuando el servicio exceda los fondos depositados en el f/0009, depositados en la cuenta de el cliente ";
																			if(empty($name)){
																				 echo $curp;
																			 } else {
																				 echo htmlentities($name, ENT_QUOTES, "UTF-8");
																			 }
															echo ".</li>
																			<li>&nbsp;&nbsp;El SERVICIO UNIVERSITARIO unicamente podra ser utilizado 10 a&ntilde;os despu&eacute;s de su contrataci&oacute;n la empresa se reserva el derecho de prestar el servici&oacute; cuando no cumplan con este criterio.</li>
																			<li>&nbsp;&nbsp;La cantidad abonada por el F/0009, nunca exceder&aacute; los montos fijados en el fideicomiso F/0009.</li>
																		</ul>
															";
														}else{
															echo "
																	<li>El presente se sujeta a las disposiciones estipuladas en el contrato de fideicomiso o irrevocable de garant&iacute;a no.f/0003.</li>
																	<li>Capital &amp; Fondeo de M&eacute;xico, puede reservarse el derecho de proveer el servicio cuando el aportante incumpla total o parcialmente en alguna de sus obligaciones con la misma.</li>
																	<li>La empresa se reserva el servicio si se incurre en omisiones sobre la informaci&oacute;n asentada en la presente;</li>
																	<ul>
																		<li>&nbsp;&nbsp;&nbsp;&nbsp;Sobre siniestros acaecidos como consecuci&oacute;n de conflictos armados o por 'cat&aacute;strofe o calamidad nacional', Siniestros de caracter catastr&oacute;fico acaecidos como consecuencia de reacci&oacute;n o radiaci&oacute;n nuclear o contaminaci&oacute;n radiactiva.</li>
																		<li>&nbsp;&nbsp;&nbsp;&nbsp;LA PRESENTE TIENE UN RANGO DE EDAD PARA LOS BENEFICIOS DE 2 A 69 A&Ntilde;OS en caso del servicio funerario.</li>
																		<li>&nbsp;&nbsp;&nbsp;&nbsp;La cantidad abonada al F/0003, nunca exceder&aacute; los montos fijados en el fideicomiso F/0003, el restante se considera pago de comisiones, cargos y gastos que ser&aacute;n pagados por el cliente.</li>
																	</ul>
															";
														}
														?>
													</ul>
										</td>
							</tr>
						</table>
						<img src="https://kasu.com.mx/assets/poliza/img2/LINE7.jpg" class="h-line">
						<h2 class="url">CONSULTA NUESTRO AVISO DE PRIVACIDAD EN :WWW.KASU.COM.MX/AVISOPRIVACIDAD.HTML</h2>
						<br>
						<img src="https://kasu.com.mx/assets/poliza/img2/6.jpg" class="h-ls">
						<table class="bt tab date">
								<tr>
										<td class="fi-tx" ><strong>CAPITAL &amp; FONDEO MEXICO S.A.P.I. SOFOM  <br> ENTIDAD NO REGULADA </strong></td>
										<td class="bt-l con md fi-tx" rowspan="3">EL SOLICITANTE <br><br><br> <?php if(empty($name)){ echo $curp; } else { echo htmlentities($name, ENT_QUOTES, "UTF-8"); }?></td>
								</tr>
								<tr>
								    <td class="sp-w"> s  </td>
								</tr>
								<tr>
								    <td class="sp-w">  s </td>
								</tr>
						 </table>
						<img src="https://kasu.com.mx/assets/poliza/img2/firma.jpg" class="img-f2" >
						<br>
						<div class="Div-Cobrar" >
						<h3>&#191;Com&oacute; hacer valido el Servico &#63;</h3>
						<br>
						<?php
						if ($Producto == "Universidad") {
							echo "
								<ul>
										<li>Ingresa a nuestra pagina <strong>www.kasu.com.mx</strong></li>
										<li>Ingresa en el menu <strong>CLIENTES</strong></li>
										<li>llena el formato de ingreso escolar</li>
										<li>espera la resolucion de pagos en un plazo maximo de <strong>treinta dias</strong> habiles</li>
										<li>Recuerda que debes hacer tu tramite <strong>noventa dias</strong> antes de ingresar a la universidad que seleccionaste</li>
								</ul>
							";
						}else{
							echo "
								<ul>
										<li>Ingresa a nuestra pagina <strong>www.kasu.com.mx</strong></li>
										<li>Da Clik en el boton <strong>EMERGENCIA FUNERARIA</strong></li>
										<li>Nuestra plataforma llamara en automatico a el centro de atencion mas cercano a ti</li>
										<li>Entrega la <strong>CLAVE CURP</strong> a la funeraria designada para validar al cliente</li>
								</ul>
							";
						}
						?>
						</div>
						<img src="https://kasu.com.mx/assets/poliza/img2/img.jpg" class="fin2">
				</div>
				<br>
				<br>
				<div class="container">
					<?php
					if($Producto == "Universidad"){
						echo'
						<div class="container">
								<h1 class="hd-tit">CONTRATO DE PRESTACI&Oacute;N DE SERVICIOS</h1>
								<h2 class="hd-sub">A N T E C E D E N T E S</h2>
								<p class="hd-text">
										Mediante el contrato de fideicomiso protocolizado en fecha veinte (20) de mayo de dos mil diecis&eacute;is (2016) denominado
										a partir de este y para el presente como <strong>FIDEICOMISO F/0009</strong>, se nombr&oacute; a <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong>
										como fideicomitente  y fideicomisario en tercer lugar y a <strong>CAPITAL &amp; FONDEO M&Eacute;XICO S. A. DE C. V. SOFOM ENR</strong> fiduciaria
										y fideicomisaria en primer lugar y que mediante contrato de cesi&oacute;n de aportaciones firmado por
										<strong>KASU SERVICIOS A FUTURO S.A. DE C.V</strong> y <strong>CAPITAL &amp; FONDEO M&Eacute;XICO S.A. DE C. V. SOFOM ENR</strong> se protocolizan las solicitudes
										de acceso al <strong>FIDEICOMISO F/0009</strong> y con la aceptaci&oacute;n de los mismo se les nombra a toda persona descrita en el contrato
										de aportaci&oacute;n como fideicomisario  en segundo lugar.
								</p>
								<br>
								<p class="hd-text">
								Por lo que al momento de firmarse en contrato <strong>EL CLIENTE</strong> ser&aacute;:
								</p>
								<p class="hd-text">
								<strong>A)</strong> Acreedor a los beneficios que el fideicomiso señala tomando en consideraci&oacute;n los
								servicios espec&iacute;ficos en la solicitud de <strong>SERVICIO A FUTURO</strong> de cada <strong>CLIENTE</strong>.
								</p>
								<p class="hd-text">
								<strong>B)</strong> Las aportaciones de cada <strong>CLIENTE</strong> al mencionado <strong>FIDEICOMISO F/0009</strong>
								 son documentados por <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> mediante un recibo impreso o
								 digital donde se especificar&aacute; lo siguiente:
								</p>
								<ul class="hd-entrada">
									<li class="hd-text">Contrato de cesi&oacute;n al cual pertenece <strong>EL CLIENTE</strong>.</li>
									<li class="hd-text">Nombre de <strong>EL CLIENTE</strong>.</li>
									<li class="hd-text">Clave &uacute;nica de registro de poblaci&oacute;n de <strong>EL CLIENTE</strong>.</li>
									<li class="hd-text">Contrato de servicio a futuro de <strong>EL CLIENTE</strong>.</li>
									<li class="hd-text">Recibo de dep&oacute;sito de valor unitario de servicio a futuro de <strong>EL CLIENTE</strong>.</li>
								</ul>
								<p class="hd-text">
								C) Que el contrato <strong>FIDEICOMISO F/0009</strong> se especifica que <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.
								</strong> Ser&aacute; el &uacute;nico distribuidor y comercializador con autorizaciones para realizar los contratos de
								 aportaci&oacute;n descritos en las cl&aacute;usulas anteriores.
								</p>
								<br>
								<h2 class="hd-sub">
								<strong>D E C L A R A C I O N E S</strong>
								</h2>
								<p class="hd-text">
								Declara la sociedad denominada <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> a trav&eacute;s de su presentante;
								</p>
								<ul class="hd-entrada" >
									<li class="hd-text">Que es una sociedad constituida al amparo de las leyes mexicanas a partir del d&iacute;a veintid&oacute;s (22) de octubre
											de dos mil veinte (2020), otorgada ante la fe de la licenciada en derecho <strong>NORMA V&Eacute;LEZ BAUTISTA</strong>,
											titular de la notar&iacute;a p&uacute;blica <strong>n&uacute;mero 83 del Estado de M&Eacute;xico</strong>, con residencia en el municipio de
											Atlacomulco, mediante escritura p&uacute;blica n&uacute;mero <strong>38,169 (treinta y ocho mil ciento sesenta)</strong>.</li>
									<li class="hd-text">Cuenta con registro federal de contribuyentes el cual es el siguiente <strong>SIN01010111A</strong>.</li>
									<li class="hd-text">Su representante que cuenta con las facultades suficientes para celebrar el presente contrato en su nombre y
										representaci&oacute;n as&iacute; como para obligarla en los t&eacute;rminos y condiciones del presente con sus anexos y
										referencias a otros instrumentos, seg&uacute;n consta desde el d&iacute;a <strong>veinte (20) de octubre de dos mil veinte
											(2020)</strong>, otorgada ante la fe de la licenciada en derecho <strong>NORMA V&Eacute;LEZ BAUTISTA</strong>,
											titular de la notar&iacute;a p&uacute;blica <strong>n&uacute;mero 83 del Estado de M&Eacute;xico</strong>, con residencia en el municipio
											de Atlacomulco, mediante escritura p&uacute;blica n&uacute;mero <strong>38,160 (treinta y ocho mil ciento sesenta)</strong>.</li>
									<li class="hd-text">En este acto se constituye como <strong>PRESTADOR DE SERVICIOS</strong>.</li>
									<li class="hd-text">No ha iniciado ni se tiene conocimiento de que se haya iniciado procedimiento alguno tendiente a declarar en
										concurso mercantil, en estado de insolvencia o liquidaci&oacute;n respectivamente.</li>
									<li class="hd-text">No tiene ning&uacute;n conocimiento de que se haya iniciado acci&oacute;n o procedimiento alguno ante cualquier
										&oacute;rgano jurisdiccional ante que:</li>
									<li class="hd-text">Afecte o pudiera afectar materialmente la legalidad, validez o exigibilidad del presente contrato o de los
										dem&aacute;s documentos de la operaci&oacute;n o cualquiera de sus obligaciones derivadas o relacionadas con el
										presente contrato o con los dem&aacute;s documentos de la operaci&oacute;n de los que parte.</li>
									<li class="hd-text">Pudiera anular o impedir la transici&oacute;n de los derechos de cobro cedidos al patrimonio del fideicomiso
										conforme al presente contrato y al contrato de sesi&oacute;n original o subsecuente.</li>
									<li class="hd-text">Pudiera impugnar o impedir la emisi&oacute;n o cualquier reapertura subsecuente.</li>
									<li class="hd-text">Conduce su negocio y operaciones de acuerdo a las leyes aplicables correspondientes, cuenta con todos los
										permisos necesarios para llevar a cabo las operaciones a que hay lugar, as&iacute; como estar dentro de los reglamentos,
										 leyes, decretos y &oacute;rdenes de cualquier autoridad gubernamental que le sean aplicables al bien y a sus
										 propiedades.
									Reconoce y acepta que:</li>
									<li class="hd-text">La veracidad y exactitud de sus declaraciones contenidas en el presente contrato,</li>
									<li class="hd-text">La validez y exigibilidad del presente contrato y as&iacute; como de los dem&aacute;s documentos de la operaci&oacute;n
										de los que son parte,</li>
									<li class="hd-text">La validez y exigibilidad de la transmisi&oacute;n de la propiedad y titularidad de los derechos de cobro
										cedidos a favor del fiduciario, motivo determinante de la voluntad del fiduciario para llevar a cabo el presente,
									Que es propietario de los derechos de cobro de materia del presente</li>
									<li class="hd-text">A la fecha del presente contrato no existe huelga, paro, suspensi&oacute;n o reducci&oacute;n de labores,
										procedimientos colectivos de trabajo u otro procedimiento laboral similar en curso, que afecte o pudiere
										llegar a afectar materialmente cualquiera de sus activos e instalaciones correspondientes.</li>
								</ul>
								<br>
								<p class="hd-text">
									Declara <strong>EL CLIENTE</strong> por propia cuenta:
								</p>
								<ul class="hd-entrada" >
										<li class="hd-text">Que es de su inter&eacute;s firmar la presente solicitud de ingreso al <strong>FIDEICOMISO F/0009</strong> firmado entre
											 <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> y <strong>CAPITAL &amp; FONDEO M&Eacute;XICO S. A. DE C. V. SOFOM ENR
											 </strong>.</li>
										<li class="hd-text">Declara que conoce los alcances del <strong>FIDEICOMISO F/0009</strong> as&iacute; como las responsabilidades que el mismo
											le confiere, siendo su deseo ser parte del mismo.</li>
										<li class="hd-text">Declara que sus datos personales los otorgo a <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> para ser
											registrados de forma digital en su base de datos internos ubicados en <strong>www.kasu.com.mx</strong></li>
										<li class="hd-text">Habiendo le&iacute;do el aviso de privacidad y la hoja de datos, autoriza a <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.
										</strong>, el cual contiene y detalla las finalidades del tratamiento de mis datos personales, para
										<strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> utilice mis datos como mejor considere, as&iacute; como a sus asociados
										 con la finalidad de ofrecer un mejor servicio a los actuales y futuros <strong>CLIENTES</strong>.</li>
										<li class="hd-text">Solicito y autorizo a <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> para que con el presente contrato
											pueda compartir la informaci&oacute;n contenida en este documento, con sus empresas relacionadas, afiliadas,
											subsidiarias y dem&aacute;s que auxilien tanto en la operaci&oacute;n y administraci&oacute;n de este contrato,
											as&iacute; como la comercializaci&oacute;n de sus productos y servicios, conforme a la regulaci&oacute;n aplicable,
											misma que tendr&aacute; por objeto servir para efectos estad&iacute;sticos, referencias comerciales y calidad en el servicio.</li>
										<li class="hd-text">Hago constar que, bajo protesta de decir la verdad, que me he enterado debidamente y estoy de acuerdo con las
											condiciones que se describen en el presente contrato y me he informado que tanto los datos presentados en &eacute;sta,
											como los dem&aacute;s requisitos que <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> considere necesarios,
											forma parte del presente contrato.</li>
										<li class="hd-text">En el presente contrato se constituye con el nombre de <strong>EL CLIENTE</strong>.</li>
								</ul>
								<br>
								<h2 class="hd-sub">
								<strong>C  L &Aacute; U  S  U  L  A  S</strong>
								</h2>
								<p class="hd-text">
									<strong>PRIMERA. OBJETO.-</strong> El presente contrato especifica los medios de los cuales
									<strong>EL CLIENTE</strong> puede acceder a los beneficios y servicios amparados con el patrimonio del
									<strong>FIDEICOMISO F/0009</strong> descrito en el <strong>INCISO A</strong> de los antecedentes del
									presente contrato los cuales son <strong>REALIZAR</strong> ya sea por cuenta propia o a trav&eacute;s de alguna de
									sus empresas controladas y/o mediante alg&uacute;n tercero subcontratado con uno o varios de los servicios descritos
									en las <strong>CL&Aacute;USULA SEGUNDA</strong> del presente seg&uacute;n lo establezca <strong>EL CLIENTE</strong>
									y/o <strong>BENEFICIARIO</strong> asentados en la base de datos de <strong>www.kasu.com.mx</strong>
								</p>
								<p class="hd-text">
									<strong>SEGUNDA. SERVICIOS.-</strong> La prestaci&oacute;n de los servicios est&aacute; sujeta a las siguientes
									condiciones y medios de ejecuci&oacute;n para poder hacerse v&aacute;lido:
								</p>
								<p class="hd-text">
								<strong>LA INVERSION UNIVERSITARIA</strong> cubrir&aacute; los siguientes servicios:
								</p>
								<ul class="hd-entrada" >
										<li class="hd-text"><strong>INSCRIPCI&Oacute;N Y REINSCRIPCIONES:</strong> <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong>, a titulo personal
											o travez de sus empresas controladas o controladoras realizar&aacute; el pago de inscripci&oacute;n y reinscripci&oacute;n, entendiendose
											como el pago por costos de acceso a la institucion escolar correspondientes a un maximo de diez (10) semestres o su
											equivalente cuatrimestral, bimestral o similar en un periodo que en ningun caso sera superior a 5 años.
										</li>
										<li class="hd-text">
											<strong>PAGO DE CUOTAS ORDINARIAS:</strong> se le denominan como cuotas ordinarias a todos aquellos pagos correspondientes
											 a las colegiaturas entendiendose como el pago que permitan <strong>AL CLIENTE</strong>  continuar recibiendo los servicios educativos
											 de la institucion escolar correspondientes a un maximo de diez (10) semestres o su equivalente cuatrimestral, bimestral o
											 similar en un periodo que en ningun caso sera superior a 5 años.
										</li>
										<li class="hd-text">
											<strong>PAGO DE ACCCESORIOS A LAS CUOTAS ORDINARIAS:</strong> se le denominan como accesorios cuotas ordinarias a todos aquellos pagos adherentes
											 a las colegiaturas entendiendose como el pago por costos que permitan <strong>AL CLIENTE</strong>  continuar recibiendo los servicios educativos
											 de la institucion escolar correspondientes a un maximo de diez (10) semestres o su equivalente cuatrimestral, bimestral o
											 similar en un periodo que en ningun caso sera superior a 5 años, siempre y cuando no se consideren cuotas ordinarias o de pago periodico
											 como el inciso anterior lo indica.
										</li>
										<li class="hd-text">
											<strong>EL PAGO DE DERECHOS DE EXAMEN:</strong> se le denominan como derechos de examen a todos aquellos pagos correspondientes
											 a los que esten destinados a permitir  <strong>AL CLIENTE</strong> se evaluado dentro de la institucion de servicios educativos
											 de la institucion escolar correspondientes a un maximo de diez (10) semestres o su equivalente cuatrimestral, bimestral o
											 similar en un periodo que en ningun caso sera superior a 5 años.
										</li>
								</ul>
								<p class="hd-text">
									<strong>TERCERA. DURACI&Oacute;N.-</strong> El presente contrato es por tiempo indefinido, debido a la
									prescripci&oacute;n en el <strong>FIDEICOMISO F/0009</strong> sin que cualquiera de las partes pueda darlo
									 por terminado. La vigencia del presente contrato se activa una vez que <strong>EL CLIENTE</strong> cubra
										en su totalidad el pago del servicio y transcurriendo Diez (10) años naturales posteriores a la
										liquidaci&oacute;n de el servicio.
								</p>
								<p class="hd-text">
									<strong>CUARTA. MONEDA. -</strong> Este contrato se denominar&aacute; en pesos de los Estados Unidos de M&Eacute;xico,
									conforme a la Ley Monetaria vigente, las obligaciones se cumplir&aacute;n entregando el equivalente en moneda
									nacional al tipo de cambio que el Banco de M&Eacute;xico publique en el Diario Oficial de la Federaci&oacute;n
									en la fecha en que se efect&uacute;e el pago.
								</p>
						</div>
						<br><br>
						<div class="container">
						<br>
								<p class="hd-text">
									<strong>QUINTA. RESCISI&Oacute;N.-</strong> En caso de incumplimiento de cualquiera de las obligaciones establecidas
									 en el presente contrato, las partes podr&aacute;n rescindir de pleno derecho sin necesidad de declaraci&oacute;n
									 judicial, mediante simple aviso por escrito desde la fecha en que ocurra la violaci&oacute;n. En caso de
									 rescisi&oacute;n <strong>EL CLIENTE</strong> deber&aacute; pagar las comisiones pendientes a
									 <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> y este estar&aacute; obligado a devolver la
									 obligaci&oacute;n que obre en su poder. Manifestando en el presente acto que al momento de la cancelaci&oacute;n
									 del servicio por parte del contratante <strong>NO SE REALIZAR&Aacute; REEMBOLSO ALGUNO</strong> de la cantidad
										abonada por parte de <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> con excepci&oacute;n al servicio
										 universitario con respecto a las comisiones del grupo. El presente contrato se dar&aacute; por terminado
										 autom&aacute;ticamente en los siguientes casos:
									<strong>a)</strong> El fallecimiento de <strong>EL CLIENTE</strong>,
									<strong>b)</strong> La omisi&oacute;n del pago por un periodo mayor a sesenta (60) d&iacute;as naturales o acumular
									m&aacute;s de tres atrasos en el pago.
									<strong>c)</strong> El no cumplir con lo estipulado en el presente contrato y,
									<strong>d)</strong> Acuerdo voluntario de las partes del presente contrato.
								</p>
								<p class="hd-text">
									<strong>SEXTA. CESI&Oacute;N DE DERECHOS. -</strong> Las obligaciones y derechos concedidos por el presente
									s&oacute;lo podr&aacute;n ser cedidos por <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> en lo que
									respecta a los derechos de cobro a <strong>LOS CLIENTES</strong> y en la obligaci&oacute;n de prestar el
									servicio a sus empresas integradas o bien terceros autorizadas por las mismas, dependiendo del servicio del
									contrato.
								</p>
								<p class="hd-text">
									<strong>S&Eacute;PTIMA. REGULACI&Oacute;N. -</strong> Para la interpretaci&oacute;n se sujetar&aacute; a lo
									dispuesto en la Ley General de T&iacute;tulos y Operaciones de Cr&eacute;dito y en caso de controversia las partes
									se someter&aacute;n a la jurisdicci&oacute;n de los tribunales correspondientes renunciando expresamente a
									cualquier otra que pudiera corresponderles en raz&oacute;n de su domicilio actual o futuro.
								</p>
								<p class="hd-text">
									<strong>OCTAVA. - LUGAR DE PAGO. -</strong> El Contratante deber&aacute; pagar a su vencimiento las cantidades
									pactadas correspondientes en las oficinas del <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong>, as&iacute; mismo
									 la entrega del recibo correspondiente ser&aacute; entregada en dichas oficinas mencionadas anteriormente.
									 Sin perjuicio de lo anterior, las partes podr&aacute;n convenir el pago mediante cargo autom&aacute;tico en
									 cuenta bancaria y/o tarjeta de cr&eacute;dito y/o cualquier otra forma de pago que autorice el contratante;
									 en este caso, hasta en tanto <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong>, no entregue el
										recibo de pago, el estado de cuenta donde aparezca el cargo correspondiente ser&aacute; prueba suficiente
										de dicho pago, mientras exista saldo en la cuenta bancaria para el pago de dicho cargo.
								</p>
								<p class="hd-text">
									<strong>NOVENA. NOTIFICACIONES. -</strong> Cualquier comunicaci&oacute;n relacionada con el presente contrato
									deber&aacute; hacerse por escrito a la Instituci&oacute;n en el lugar señalado como domicilio de la misma.
									 <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong>, se compromete a dar aviso ya sea por escrito o
									 de manera telef&oacute;nica a <strong>EL CLIENTE</strong> de cualquier cambio de domicilio dentro de los
										diez d&iacute;as naturales siguientes al cambio, o de cualquier acto importante que necesite ser notificado.
										As&iacute; mismo, EL CLIENTE, deber&aacute; informar de manera escrita y notificar por medio de la Gaceta de
										 Gobierno del Estado de M&Eacute;xico con veinte d&iacute;as h&aacute;biles de anticipaci&oacute;n a
											<strong>KASU SERVICIOS A FUTURO, S.A. DE C.V</strong> sobre cualquier hecho o acto jur&iacute;dico que
											 inicie <strong>EL CLIENTE</strong> en relaci&oacute;n al presente contrato, derivado que
												<strong>EL CLIENTE</strong> omita dicha notificaci&oacute;n el presente contrato quedar&aacute;
												nulo para los efectos legales que tenga a lugar.
								</p>
								<p class="hd-text">
									<strong>D&Eacute;CIMA. ACEPTACI&Oacute;N. -</strong> Al recibir la tarjeta <strong>EL CLIENTE</strong> se
									hace del conocimiento que <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong>, cuenta con el conocimiento
									 de la operativa del servicio, los procesos y los procedimientos mediante los cuales puede hacer valido el
										servicio, as&iacute; como todas las accesorias que este les brinda y las responsabilidades que el mismo le exige
										para el cumplimiento de los descritos en la cl&aacute;usula primera del presente.
								</p>
								<p class="hd-text">
									<strong>D&Eacute;CIMA PRIMERA. AUTONOM&Iacute;A DE LAS DISPOSICIONES. -</strong> La invalidez, la ilegalidad o falta
									 de coercibilidad de cualquiera de las disposiciones contenidas del contrato no afectar&aacute; la validez y
									 exigibilidad de las dem&aacute;s disposiciones acordadas por <strong>LAS PARTES</strong>.
								</p>
								<p class="hd-text">
									<strong>D&Eacute;CIMA SEGUNDA. JURISDICCI&Oacute;N Y TRIBUNALES COMPETENTES. -</strong> Para la
									 interpretaci&oacute;n y cumplimiento del presente instrumento las partes se someten a la jurisdicci&oacute;n
									 y competencia de los Tribunales que correspondan al lugar en que se suscribe este contrato o a los Tribunales
										de la Ciudad de M&Eacute;xico, renunciando a cualquier otro fuero que por raz&oacute;n de su domicilio presente
										 o futuro les pudiera corresponder.
								</p>
								<br>
								<p class="hd-text">
								La agencia se asegurar&aacute; de realizar los tr&aacute;mites y servicios necesarios para la prestaci&oacute;n de
								los servicios antes mencionado del <strong>inciso A al G</strong>, la suma de todos los servicios mencionados
								no exceder&aacute; el valor señalado para tal fin en el <strong>FIDEICOMISO F/0009</strong> donde se especifican
								los valores para este del cual se podr&aacute; consultar una copia en <strong>www.kasu.com.mx//Fideicomiso_F0009.pdf
								</strong> Dicho servicio comenzar&aacute; a tener vigencia a partir de los pasados treinta (30) d&iacute;as de
								liquidaci&oacute;n y activaci&oacute;n en el fideicomiso del presente contrato.
								Le&iacute;do que fue el presente contrato por las partes, explicado su contenido por parte de
								<strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong>  a <strong>EL CLIENTE</strong> y enteradas las
								partes de su contenido y alcance legal, manifiestan que el mismo contiene la libre expresi&oacute;n de su
								voluntad y que no tiene vicios de consentimiento que pudiera invalidar, en consecuencia,
								lo firman en <strong>'.$direccion." el ".substr($FechaR, 0, -16).'</strong>.
								</p>
								<br><br><br>
								<table class="bt tab date">
										<tr>
												<td class="fi-tx" ><strong>CAPITAL &amp; FONDEO MEXICO S.A.P.I. SOFOM  <br> ENTIDAD NO REGULADA </strong></td>
												<td class="bt-l con md fi-tx" rowspan="3">EL SOLICITANTE <br><br><br> ';
												if(empty($name)){ echo $curp; } else { echo htmlentities($name, ENT_QUOTES, "UTF-8"); }
							echo '</td>
										</tr>
										<tr>
												<td class="sp-w"> s  </td>
										</tr>
										<tr>
												<td class="sp-w">  s </td>
										</tr>
								 </table>
								 <img src="https://kasu.com.mx/assets/poliza/img2/firma.jpg" class="img-f2" >
						</div>
						<br><br><br><br><br><br><br><br>
						<br><br><br><br><br><br><br><br>
						<br><br><br><br><br><br><br><br>
						<br><br><br><br><br><br><br><br>
						<br><br><br><br>
						';
					}else{
						echo '
							<div class="container">
								<h1 class="hd-tit">CONTRATO DE PRESTACI&Oacute;N DE SERVICIOS FUNERARIOS A FUTURO</h1>
								<h2 class="hd-sub">A N T E C E D E N T E S</h2>
								<p class="hd-text">
										Mediante el contrato de fideicomiso protocolizado en fecha veinte (20) de mayo de dos mil diecis&eacute;is (2016) denominado
										a partir de este y para el presente como <strong>FIDEICOMISO F/0003</strong>, se nombr&oacute; a <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong>
										como fideicomitente  y fideicomisario en tercer lugar y a <strong>CAPITAL &amp; FONDEO M&Eacute;XICO S. A. DE C. V. SOFOM ENR</strong> fiduciaria
										y fideicomisaria en primer lugar y que mediante contrato de cesi&oacute;n de aportaciones firmado por
										<strong>KASU SERVICIOS A FUTURO S.A. DE C.V</strong> y <strong>CAPITAL &amp; FONDEO M&Eacute;XICO S.A. DE C. V. SOFOM ENR</strong> se protocolizan las solicitudes
										de acceso al <strong>FIDEICOMISO F/0003</strong> y con la aceptaci&oacute;n de los mismo se les nombra a toda persona descrita en el contrato
										de aportaci&oacute;n como fideicomisario  en segundo lugar.
								</p>
								<br>
								<p class="hd-text">
								Por lo que al momento de firmarse en contrato <strong>EL CLIENTE</strong> ser&aacute;:
								</p>
								<p class="hd-text">
								<strong>A)</strong> Acreedor a los beneficios que el fideicomiso señala tomando en consideraci&oacute;n los
								servicios espec&iacute;ficos en la solicitud de <strong>SERVICIO A FUTURO</strong> de cada <strong>CLIENTE</strong>.
								</p>
								<p class="hd-text">
								<strong>B)</strong> Las aportaciones de cada <strong>CLIENTE</strong> al mencionado <strong>FIDEICOMISO F/0003</strong>
								 son documentados por <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> mediante un recibo impreso o
								 digital donde se especificar&aacute; lo siguiente:
								</p>
								<ul class="hd-entrada">
									<li  class="hd-text">Contrato de cesi&oacute;n al cual pertenece <strong>EL CLIENTE</strong>.</li>
									<li class="hd-text">Nombre de <strong>EL CLIENTE</strong>.</li>
									<li class="hd-text">Clave &uacute;nica de registro de poblaci&oacute;n de <strong>EL CLIENTE</strong>.</li>
									<li class="hd-text">Contrato de servicio a futuro de <strong>EL CLIENTE</strong>.</li>
									<li class="hd-text">Recibo de dep&oacute;sito de valor unitario de servicio a futuro de <strong>EL CLIENTE</strong>.</li>
								</ul>
								<p class="hd-text">
								C) Que el contrato <strong>FIDEICOMISO F/0003</strong> se especifica que <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.
								</strong> Ser&aacute; el &uacute;nico distribuidor y comercializador con autorizaciones para realizar los contratos de
								 aportaci&oacute;n descritos en las cl&aacute;usulas anteriores.
								</p>
								<br>
								<h2 class="hd-sub">
								<strong>D E C L A R A C I O N E S</strong>
								</h2>
								<p class="hd-text">
								Declara la sociedad denominada <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> a trav&eacute;s de su presentante;
								</p>
								<ul class="hd-entrada" >
									<li class="hd-text">Que es una sociedad constituida al amparo de las leyes mexicanas a partir del d&iacute;a veintid&oacute;s (22) de octubre
											de dos mil veinte (2020), otorgada ante la fe de la licenciada en derecho <strong>NORMA V&Eacute;LEZ BAUTISTA</strong>,
											titular de la notar&iacute;a p&uacute;blica <strong>n&uacute;mero 83 del Estado de M&Eacute;xico</strong>, con residencia en el municipio de
											Atlacomulco, mediante escritura p&uacute;blica n&uacute;mero <strong>38,169 (treinta y ocho mil ciento sesenta)</strong>.</li>
									<li class="hd-text">Cuenta con registro federal de contribuyentes el cual es el siguiente <strong>SIN01010111A</strong>.</li>
									<li class="hd-text">Su representante que cuenta con las facultades suficientes para celebrar el presente contrato en su nombre y
										representaci&oacute;n as&iacute; como para obligarla en los t&eacute;rminos y condiciones del presente con sus anexos y
										referencias a otros instrumentos, seg&uacute;n consta desde el d&iacute;a <strong>veinte (20) de octubre de dos mil veinte
											(2020)</strong>, otorgada ante la fe de la licenciada en derecho <strong>NORMA V&Eacute;LEZ BAUTISTA</strong>,
											titular de la notar&iacute;a p&uacute;blica <strong>n&uacute;mero 83 del Estado de M&Eacute;xico</strong>, con residencia en el municipio
											de Atlacomulco, mediante escritura p&uacute;blica n&uacute;mero <strong>38,160 (treinta y ocho mil ciento sesenta)</strong>.</li>
									<li class="hd-text">En este acto se constituye como <strong>PRESTADOR DE SERVICIOS</strong>.</li>
									<li class="hd-text">No ha iniciado ni se tiene conocimiento de que se haya iniciado procedimiento alguno tendiente a declarar en
										concurso mercantil, en estado de insolvencia o liquidaci&oacute;n respectivamente.</li>
									<li class="hd-text">No tiene ning&uacute;n conocimiento de que se haya iniciado acci&oacute;n o procedimiento alguno ante cualquier
										&oacute;rgano jurisdiccional ante que:</li>
									<li class="hd-text">Afecte o pudiera afectar materialmente la legalidad, validez o exigibilidad del presente contrato o de los
										dem&aacute;s documentos de la operaci&oacute;n o cualquiera de sus obligaciones derivadas o relacionadas con el
										presente contrato o con los dem&aacute;s documentos de la operaci&oacute;n de los que parte.</li>
									<li class="hd-text">Pudiera anular o impedir la transici&oacute;n de los derechos de cobro cedidos al patrimonio del fideicomiso
										conforme al presente contrato y al contrato de sesi&oacute;n original o subsecuente.</li>
									<li class="hd-text">Pudiera impugnar o impedir la emisi&oacute;n o cualquier reapertura subsecuente.</li>
									<li class="hd-text">Conduce su negocio y operaciones de acuerdo a las leyes aplicables correspondientes, cuenta con todos los
										permisos necesarios para llevar a cabo las operaciones a que hay lugar, as&iacute; como estar dentro de los reglamentos,
										 leyes, decretos y &oacute;rdenes de cualquier autoridad gubernamental que le sean aplicables al bien y a sus
										 propiedades.
									Reconoce y acepta que:</li>
									<li class="hd-text">La veracidad y exactitud de sus declaraciones contenidas en el presente contrato,</li>
									<li class="hd-text">La validez y exigibilidad del presente contrato y as&iacute; como de los dem&aacute;s documentos de la operaci&oacute;n
										de los que son parte,</li>
									<li class="hd-text">La validez y exigibilidad de la transmisi&oacute;n de la propiedad y titularidad de los derechos de cobro
										cedidos a favor del fiduciario, motivo determinante de la voluntad del fiduciario para llevar a cabo el presente,
									Que es propietario de los derechos de cobro de materia del presente</li>
									<li class="hd-text">A la fecha del presente contrato no existe huelga, paro, suspensi&oacute;n o reducci&oacute;n de labores,
										procedimientos colectivos de trabajo u otro procedimiento laboral similar en curso, que afecte o pudiere
										llegar a afectar materialmente cualquiera de sus activos e instalaciones correspondientes.</li>
								</ul>
								<br>
								<p class="hd-text">
									Declara <strong>EL CLIENTE</strong> por propia cuenta:
								</p>
								<ul class="hd-entrada" >
										<li class="hd-text">Que es de su inter&eacute;s firmar la presente solicitud de ingreso al <strong>FIDEICOMISO F/0003</strong> firmado entre
											 <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> y <strong>CAPITAL &amp; FONDEO M&Eacute;XICO S. A. DE C. V. SOFOM ENR
											 </strong>.</li>
										<li class="hd-text">Declara que conoce los alcances del <strong>FIDEICOMISO F/0003</strong> as&iacute; como las responsabilidades que el mismo
											le confiere, siendo su deseo ser parte del mismo.</li>
										<li class="hd-text">Declara que sus datos personales los otorgo a <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> para ser
											registrados de forma digital en su base de datos internos ubicados en <strong>www.kasu.com.mx</strong></li>
										<li class="hd-text">Habiendo le&iacute;do el aviso de privacidad y la hoja de datos, autoriza a <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.
										</strong>, el cual contiene y detalla las finalidades del tratamiento de mis datos personales, para
										<strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> utilice mis datos como mejor considere, as&iacute; como a sus asociados
										 con la finalidad de ofrecer un mejor servicio a los actuales y futuros <strong>CLIENTES</strong>.</li>
										<li class="hd-text">Solicito y autorizo a <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> para que con el presente contrato
											pueda compartir la informaci&oacute;n contenida en este documento, con sus empresas relacionadas, afiliadas,
											subsidiarias y dem&aacute;s que auxilien tanto en la operaci&oacute;n y administraci&oacute;n de este contrato,
											as&iacute; como la comercializaci&oacute;n de sus productos y servicios, conforme a la regulaci&oacute;n aplicable,
											misma que tendr&aacute; por objeto servir para efectos estad&iacute;sticos, referencias comerciales y calidad en el servicio.</li>
										<li class="hd-text">Hago constar que, bajo protesta de decir la verdad, que me he enterado debidamente y estoy de acuerdo con las
											condiciones que se describen en el presente contrato y me he informado que tanto los datos presentados en &eacute;sta,
											como los dem&aacute;s requisitos que <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> considere necesarios,
											forma parte del presente contrato.</li>
										<li class="hd-text">En el presente contrato se constituye con el nombre de <strong>EL CLIENTE</strong>.</li>
								</ul>
								<br>
								<h2 class="hd-sub">
								<strong>C  L &Aacute; U  S  U  L  A  S</strong>
							</h2>
								<p class="hd-text">
									<strong>PRIMERA. OBJETO.-</strong> El presente contrato especifica los medios de los cuales
									<strong>EL CLIENTE</strong> puede acceder a los beneficios y servicios amparados con el patrimonio del
									<strong>FIDEICOMISO F/0003</strong> descrito en el <strong>INCISO A</strong> de los antecedentes del
									presente contrato los cuales son <strong>REALIZAR</strong> ya sea por cuenta propia o a trav&eacute;s de alguna de
									sus empresas controladas y/o mediante alg&uacute;n tercero subcontratado con uno o varios de los servicios descritos
									en las <strong>CL&Aacute;USULA SEGUNDA</strong> del presente seg&uacute;n lo establezca <strong>EL CLIENTE</strong>
									y/o <strong>BENEFICIARIO</strong> asentados en la base de datos de <strong>www.kasu.com.mx</strong>
								</p>
								<p class="hd-text">
									<strong>SEGUNDA. SERVICIOS.-</strong> La prestaci&oacute;n de los servicios est&aacute; sujeta a las siguientes
									condiciones y medios de ejecuci&oacute;n para poder hacerse v&aacute;lido:
								</p>
								<ul class="hd-entrada">
									<li class="hd-text"><strong>SERVICIO FUNERARIO</strong>, el servicio incluye los siguientes anexos:</li>
									<li class="hd-text"><strong>SERVICIO DE TRASLADO:</strong> el servicio se prestar&aacute; en un radio que no exceda los sesenta
										(60) kil&oacute;metros de distancia (<strong>DE KASU O DEL DOMICILIO DEL DEL CLIENTE</strong>), el servicio
										consta de los traslados necesarios del cuerpo, entre la agencia funeraria a la zona donde habr&aacute; de
										requerirse el traslado entre “los servicios de salubridad, el ministerio p&uacute;blico u hospital”, o en su caso
										del traslado de su casa al pante&oacute;n, sin embargo en ning&uacute;n caso exceder&aacute; la suma de sesenta (60)
										kil&oacute;metros de donde ocurri&oacute; el deceso de <strong>EL CLIENTE</strong>., rebasados los sesenta
										(60) kil&oacute;metros que cubre el presente contrato, <strong>EL CLIENTE</strong> se obliga a cubrir los
										gastos generados extraordinariamente.</li>
									<li class="hd-text"><strong>SERVICIO DE SALA DE VELACI&Oacute;N:</strong> El servicio se prestar&aacute; en el espacio que las
										agencias funerarias propias o de terceros, mismas que contar&aacute;n con un espacio m&iacute;nimo para cincuenta
										(50) personas, con espacios suficientes para sentarse, adem&aacute;s la sala de velaci&oacute;n deber&aacute;
										contar con espacio para cafeter&iacute;a y/o comida, as&iacute; mismo un estacionamiento para cinco (5) autos como
										requerimiento m&iacute;nimo, la agencia funeraria deber&aacute; contar con los insumos necesarios para realizar el
										servicio funerario los cuales deber&aacute;n integrarse de floreros de aluminio o de bronce, equipo de
										velaci&oacute;n con porta ata&uacute;d.</li>
									<li class="hd-text"><strong>SERVICIO DE CAFETER&Iacute;A.-</strong> La agencia funeraria deber&aacute; contar con cincuenta (50)
										lonches a modo de cafeter&iacute;a, los cuales deber&aacute;n incluir caf&eacute; o t&eacute; herbal, galletas, agua,
										s&aacute;ndwiches.</li>
									<li class="hd-text"><strong>SERVICIO DE EQUIPO DE VELACI&Oacute;N.-</strong> La agencia funeraria deber&aacute; proporcionar a
										manera de comodato a los familiares de <strong>EL CLIENTE</strong> los insumos necesarios para realizar
										el servicio funerario en el lugar que indiquen los mismos, siempre y cuando no excedan la suma de los
										kil&oacute;metros señalados en el <strong>INCISO A</strong> de la <strong>CL&Aacute;USULA SEGUNDA</strong>
										del presente, los cuales deber&aacute;n integrarse de: floreros de aluminio o de bronce, equipo de
										velaci&oacute;n con porta ata&uacute;d, as&iacute; mismo la agencia deber&aacute; acordar con los familiares de
										<strong>EL CLIENTE</strong> para recuperar sus insumos y los gastos de recuperaci&oacute;n estos
										correr&aacute;n por cuenta de la agencia funeraria.</li>
									<li class="hd-text"><strong>ACONDICIONAMIENTO DEL CUERPO. -</strong> La agencia funeraria ya sea por medios propios o por
										subcontrataci&oacute;n de terceros deber&aacute; realizar los siguientes servicios:</li>
								</ul>
					</div>
									<br><br><br><br>
					<div class="container">
									<ul class="hd-entrada">
										<li class="hd-text">Embalsamiento del cuerpo (en caso de ser necesario) de <strong>EL CLIENTE</strong></li>
										<li class="hd-text">Maquillaje funerario del cuerpo de <strong>EL CLIENTE</strong></li>
										<li class="hd-text">Mortaja funeraria de <strong>EL CLIENTE</strong></li>
										<li class="hd-text"><strong>SERVICIO DE CREMACI&Oacute;N:</strong> La agencia funeraria realizar&aacute; el servicio de
											cremaci&oacute;n por cuenta propia o por medio de un tercero subcontratado</li>
										<li class="hd-text"><strong>ATA&Uacute;D. -</strong> La agencia funeraria proporcionar&aacute; un ata&uacute;d de madera lisa barnizada,
											 que deber&aacute; ceñirse a dos supuestos, si el servicio ser&aacute; de inhumaci&oacute;n el ata&uacute;d
											 deber&aacute; entregarse a la familia para que se sepultado con el cuerpo de <strong>EL CLIENTE</strong>,
											 si el servicio fuese de cremaci&oacute;n se deber&aacute; proporcionar un ata&uacute;d de madera barnizada en forma
											 de comodato a la familia para los servicios de velaci&oacute;n del cuerpo, recuper&aacute;ndose al momento de
											 la cremaci&oacute;n.</li>
										<li class="hd-text"><strong>TR&Aacute;MITES</strong>, la agencia se asegurar&aacute; de realizar los tr&aacute;mites y servicios
											 necesarios para la prestaci&oacute;n de los servicios antes mencionados del <strong>inciso A) al F)</strong>.
										</li>
									</ul>
									<p class="hd-text">
										<strong>TERCERA. DURACI&Oacute;N. -</strong> El presente contrato es por tiempo indefinido, debido a la
										prescripci&oacute;n en el <strong>FIDEICOMISO F/0003</strong> sin que cualquiera de las partes pueda darlo
										 por terminado. La vigencia del presente contrato se activa una vez que <strong>EL CLIENTE</strong> cubra
											en su totalidad el pago del servicio y transcurriendo treinta (30) d&iacute;as naturales posterior a la
											liquidaci&oacute;n el servicio.
									</p>
									<p class="hd-text">
										<strong>CUARTA. MONEDA. -</strong> Este contrato se denominar&aacute; en pesos de los Estados Unidos de M&Eacute;xico,
										conforme a la Ley Monetaria vigente, las obligaciones se cumplir&aacute;n entregando el equivalente en moneda
										nacional al tipo de cambio que el Banco de M&Eacute;xico publique en el Diario Oficial de la Federaci&oacute;n
										en la fecha en que se efect&uacute;e el pago.
									</p>
									<p class="hd-text">
										<strong>QUINTA. RESCISI&Oacute;N.-</strong> En caso de incumplimiento de cualquiera de las obligaciones establecidas
										 en el presente contrato, las partes podr&aacute;n rescindir de pleno derecho sin necesidad de declaraci&oacute;n
										 judicial, mediante simple aviso por escrito desde la fecha en que ocurra la violaci&oacute;n. En caso de
										 rescisi&oacute;n <strong>EL CLIENTE</strong> deber&aacute; pagar las comisiones pendientes a
										 <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> y este estar&aacute; obligado a devolver la
										 obligaci&oacute;n que obre en su poder. Manifestando en el presente acto que al momento de la cancelaci&oacute;n
										 del servicio por parte del contratante <strong>NO SE REALIZAR&Aacute; REEMBOLSO ALGUNO</strong> de la cantidad
											abonada por parte de <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> con excepci&oacute;n al servicio
											 universitario con respecto a las comisiones del grupo. El presente contrato se dar&aacute; por terminado
											 autom&aacute;ticamente en los siguientes casos:
										<strong>a)</strong> El fallecimiento de <strong>EL CLIENTE</strong>,
										<strong>b)</strong> La omisi&oacute;n del pago por un periodo mayor a sesenta (60) d&iacute;as naturales o acumular
										m&aacute;s de tres atrasos en el pago.
										<strong>c)</strong> El no cumplir con lo estipulado en el presente contrato y,
										<strong>d)</strong> Acuerdo voluntario de las partes del presente contrato.
									</p>
									<p class="hd-text">
										<strong>SEXTA. CESI&Oacute;N DE DERECHOS. -</strong> Las obligaciones y derechos concedidos por el presente
										s&oacute;lo podr&aacute;n ser cedidos por <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> en lo que
										respecta a los derechos de cobro a <strong>LOS CLIENTES</strong> y en la obligaci&oacute;n de prestar el
										servicio a sus empresas integradas o bien terceros autorizadas por las mismas, dependiendo del servicio del
										contrato.
									</p>
									<p class="hd-text">
										<strong>S&Eacute;PTIMA. REGULACI&Oacute;N. -</strong> Para la interpretaci&oacute;n se sujetar&aacute; a lo
										dispuesto en la Ley General de T&iacute;tulos y Operaciones de Cr&eacute;dito y en caso de controversia las partes
										se someter&aacute;n a la jurisdicci&oacute;n de los tribunales correspondientes renunciando expresamente a
										cualquier otra que pudiera corresponderles en raz&oacute;n de su domicilio actual o futuro.
									</p>
									<p class="hd-text">
										<strong>OCTAVA. - LUGAR DE PAGO. -</strong> El Contratante deber&aacute; pagar a su vencimiento las cantidades
										pactadas correspondientes en las oficinas del <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong>, as&iacute; mismo
										 la entrega del recibo correspondiente ser&aacute; entregada en dichas oficinas mencionadas anteriormente.
										 Sin perjuicio de lo anterior, las partes podr&aacute;n convenir el pago mediante cargo autom&aacute;tico en
										 cuenta bancaria y/o tarjeta de cr&eacute;dito y/o cualquier otra forma de pago que autorice el contratante;
										 en este caso, hasta en tanto <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong>, no entregue el
											recibo de pago, el estado de cuenta donde aparezca el cargo correspondiente ser&aacute; prueba suficiente
											de dicho pago, mientras exista saldo en la cuenta bancaria para el pago de dicho cargo.
									</p>
									<p class="hd-text">
										<strong>NOVENA. NOTIFICACIONES. -</strong> Cualquier comunicaci&oacute;n relacionada con el presente contrato
										deber&aacute; hacerse por escrito a la Instituci&oacute;n en el lugar señalado como domicilio de la misma.
										 <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong>, se compromete a dar aviso ya sea por escrito o
										 de manera telef&oacute;nica a <strong>EL CLIENTE</strong> de cualquier cambio de domicilio dentro de los
											diez d&iacute;as naturales siguientes al cambio, o de cualquier acto importante que necesite ser notificado.
											As&iacute; mismo, EL CLIENTE, deber&aacute; informar de manera escrita y notificar por medio de la Gaceta de
											 Gobierno del Estado de M&eacute;xico con veinte d&iacute;as h&aacute;biles de anticipaci&oacute;n a
												<strong>KASU SERVICIOS A FUTURO, S.A. DE C.V</strong> sobre cualquier hecho o acto jur&iacute;dico que
												 inicie <strong>EL CLIENTE</strong> en relaci&oacute;n al presente contrato, derivado que
													<strong>EL CLIENTE</strong> omita dicha notificaci&oacute;n el presente contrato quedar&aacute;
													nulo para los efectos legales que tenga a lugar.
									</p>
									<p class="hd-text">
										<strong>D&Eacute;CIMA. ACEPTACI&Oacute;N. -</strong> Al recibir la tarjeta <strong>EL CLIENTE</strong> se
										hace del conocimiento que <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong>, cuenta con el conocimiento
										 de la operativa del servicio, los procesos y los procedimientos mediante los cuales puede hacer valido el
											servicio, as&iacute; como todas las accesorias que este les brinda y las responsabilidades que el mismo le exige
											para el cumplimiento de los descritos en la cl&aacute;usula primera del presente.
									</p>
									<p class="hd-text">
										<strong>D&Eacute;CIMA PRIMERA. AUTONOM&Iacute;A DE LAS DISPOSICIONES. -</strong> La invalidez, la ilegalidad o falta
										 de coercibilidad de cualquiera de las disposiciones contenidas del contrato no afectar&aacute; la validez y
										 exigibilidad de las dem&aacute;s disposiciones acordadas por <strong>LAS PARTES</strong>.
									</p>
									<p class="hd-text">
										<strong>D&Eacute;CIMA SEGUNDA. JURISDICCI&Oacute;N Y TRIBUNALES COMPETENTES. -</strong> Para la
										 interpretaci&oacute;n y cumplimiento del presente instrumento las partes se someten a la jurisdicci&oacute;n
										 y competencia de los Tribunales que correspondan al lugar en que se suscribe este contrato o a los Tribunales
											de la Ciudad de M&eacute;xico, renunciando a cualquier otro fuero que por raz&oacute;n de su domicilio presente
											 o futuro les pudiera corresponder.
									</p>
									<br>
									<p class="hd-text">
									La agencia se asegurar&aacute; de realizar los tr&aacute;mites y servicios necesarios para la prestaci&oacute;n de
									los servicios antes mencionado del <strong>inciso A al G</strong>, la suma de todos los servicios mencionados
									no exceder&aacute; el valor señalado para tal fin en el <strong>FIDEICOMISO F/0003</strong> donde se especifican
									los valores para este del cual se podr&aacute; consultar una copia en <strong>www.kasu.com.mx//Fideicomiso_F0003.pdf
									</strong> Dicho servicio comenzar&aacute; a tener vigencia a partir de los pasados treinta (30) d&iacute;as de
									liquidaci&oacute;n y activaci&oacute;n en el fideicomiso del presente contrato.
									Le&iacute;do que fue el presente contrato por las partes, explicado su contenido por parte de
									<strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong>  a <strong>EL CLIENTE</strong> y enteradas las
									partes de su contenido y alcance legal, manifiestan que el mismo contiene la libre expresi&oacute;n de su
									voluntad y que no tiene vicios de consentimiento que pudiera invalidar, en consecuencia,
									lo firman en <strong>'.$direccion." el ".substr($FechaR, 0, -16).'</strong>.
								</p>
								<br><br><br>
								<table class="bt tab date">
										<tr>
												<td class="fi-tx" ><strong>CAPITAL &amp; FONDEO MEXICO S.A.P.I. SOFOM  <br> ENTIDAD NO REGULADA </strong></td>
												<td class="bt-l con md fi-tx" rowspan="3">EL SOLICITANTE <br><br><br> ';
												if(empty($name)){ echo $curp; } else { echo htmlentities($name, ENT_QUOTES, "UTF-8"); }
							echo '</td>
										</tr>
										<tr>
												<td class="sp-w"> s  </td>
										</tr>
										<tr>
												<td class="sp-w">  s </td>
										</tr>
								 </table>
								 <img src="https://kasu.com.mx/assets/poliza/img2/firma.jpg" class="img-f2" >
						</div>
							 <br><br><br><br><br><br><br><br>
							 <br><br><br><br><br><br><br><br>
							 <br><br><br><br><br><br><br><br>
							 <br><br><br>
						';
					}
					?>
				</div>
				<div class="container">
					<br>
						<img src="https://kasu.com.mx/assets/poliza/img2/transp.jpg" class="hc-header">
						<table class="tc-le">
								<tr class="hc-tab2">
										<td > <h1 class="hc-text">KASU, SERVICIOS A FUTURO S.A DE C.V </h1></td>
								</tr>
								<tr>
										<td> <h1 class="hc-text">Atlacomulco, Estado de M&eacute;xico, M&eacute;xico </h1></td>
								</tr>
						</table>
						<br><br><br>
						<h1 class="hc-tit">&#161;Felicidades&#33;</h1>
						<h1 class="hc-tit"><?php if(empty($name)){ echo $curp; } else { echo htmlentities($name, ENT_QUOTES, "UTF-8"); }?></h1>
						<p class="hc-sub">Sabemos que proteger a tu familia es un trabajo de por vida,<br>
									acabas de adquirir el mejor respaldo para gastos disponible en M&eacute;xico. <br>
									&#161;Gracias por permitirnos estar cerca de ti&#33;</p>
						<img src="https://kasu.com.mx/assets/poliza/img2/question.jpg" class="ask">
						<div class="ask-q">
								<h2 class="hc-tsub">DUDAS Y PREGUNTAS FRECUENTES</h2>
								<br>
								<p class="hc-ts"><strong>- &#191;C&oacute;mo funciona&#63;</strong> <br>KASU atrav&eacute;s de sus agencias tiene la obligaci&oacute;n de prestar el servicio establezca el contrato ya sea funerario o universitario simplemente con realizar una llamada a los telefonos en su p&oacute;liza,el servicio es integramente realizado.</p>
								<p class="hc-ts"><strong>- &#191;Qu&eacute; ampara mi dinero&#63;</strong><br>Puedes sentirte seguro/a ya que tu dinero esta amparado por el fideicomiso f/0003; mismo que esta respaldado por la Secretar&iacute;a de Hacienda y Cr&eacute;dito P&uacute;blico.</p>
								<p class="hc-ts"><strong>-&#191;C&oacute;mo verifico que el fideicomiso es legal&#63;</strong><br>Una vez siendo cliente, puedes acceder a nuestra p&aacute;gina www.kasu.com.mx donde atrav&eacute;s de la CURP y ah&iacute; puedes verificar tu estado as&iacute; como el tipo de servicio que seleccionaste, del mismo modo se encuentra el fideicomiso f/0003 facilitando su descarga y verificaci&oacute;n.</p>
								<p class="hc-ts"><strong>-&#191;Por qu&eacute; pago poco y recibo un servicio de mayor valor&#63;</strong><br>Una parte de su dinero se va al fideicomiso y la otra se invierte en negocios de bajo riesgo, donde se trabaja de forma correcta para la adquisici&oacute;n del costo total del servicio a futuro, cuando se requiera.</p>
						</div>
						<br><br>
						<table>
								<tr>
										<td class="hc-serf">
											<?php
											if($Producto == "Universidad"){
												echo '
												<div>
														<h1  class="hc-sert">PREGUNTAS FRECUENTES</h1>
														<br>
														<h4 class="hc-ques">&#191;C&oacute;mo funciona&#63;</h4>
														<p class="hc-ques-tex">Una vez pagado el servicio, y al haber transcurrido 10 años el servicio se activa.</p>
														<h4 class="hc-ques">&#191;Qu&eacute; puede cancelar mi servicio&#63;</h4>
														<p class="hc-ques-tex">  Falta de pago por un periodo mayor a 60 d&iacute;as natu-rales o cumular m&aacute;s de tres atrasos en el pago.</p>
														<h4 class="hc-ques">&#191;Qu&eacute; incluye el servicio&#63;</h4>
														<div class="hd-vs">
																<ul>
																	<li class="hc-vires">Pago de colegiaturas ( 10 semestres ).</li>
																</ul>
																<ul>
																	<li class="hc-vires">Inscripciones y reinscripciones.</li>
																</ul>
																<ul>
																	<li class="hc-vires">Periodos ordinarios.</li>
																</ul>
														</div>
														<p class="hc-tedob"><strong>&#191;El servicio es trasferible&#63;</strong> No.</p>
														<h4 class="hc-ques">&#191;Com&oacute; lo hago valer&#63;</h4>
														<p class="hc-ques-tex">Acudiendo a nuestra sucursal con el contrato y tarjeta; para hacer valer su servicio.</p>
														<h4 class="hc-ques">&#191;Qu&eacute; modificaciones se pueden hacer&#63;</h4>
														<p class="hc-ques-tex">Cambiar debeneficiario; siempre y cuando se haya contratado bajo los acuerdos y estipulaciones del contrato.</p>

														<h3>Para m&aacute;s informaci&oacute;n ingresa a nuestra p&aacute;gina web:</h3>
														<img src="https://kasu.com.mx/assets/poliza/img2/pagin.jpg" class="hc-url">
												</div>
												';
											}else{
												echo '
												<div>
														<h1 class="hc-sert"> PREGUNTAS FRECUENTES</h1>
														<br>
														<h4 class="hc-ques">&#191;C&oacute;mo funciona&#63;</h4>
														<p class="hc-ques-tex">Una vez pagado el servicio, y pasados 30 d&iacute;as naturales de la liqui-daci&oacute;n el servicio se activa.</p>
														<h4 class="hc-ques">&#191;Qu&eacute; puede cancelar mi servicio&#63;</h4>
														<p class="hc-ques-tex">Falta de pago por un periodo mayor a 60 d&iacute;as naturales o acumular m&aacute;s de tres atrasos en el pago.</p>
														<h4 class="hc-ques">&#191;Qu&eacute; incluye el servicio&#63;</h4>
														<div class="hd-vs">
																<ul>
																		<li class="hc-vires">Asesor&iacute;a legal para la tramitaci&oacute;n correspondiente.</li>
																</ul>
																<ul>
																		<li class="hc-vires">Ata&uacute;d met&aacute;lico medio cristal,ata&uacute;d de madera barnizada o urna para las cenizas.</li>
																</ul>
																<ul>
																		<li class="hc-vires">Traslado m&aacute;ximo de 60km.</li>
																</ul>
																<ul>
																		<li class="hc-vires">Sala de velaci&oacute;n de excelencia est&aacute;ndar y servicio integral de cafeter&iacute;a.</li>
																</ul>
																<ul>
																		<li class="hc-vires">Cremaci&oacute;n,( en caso detener seleccionado este tipo de servicio ).</li>
																</ul>
																<ul>
																		<li class="hc-vires">Equipo de velaci&oacute;n.</li>
																</ul>
																<ul>
																		<li class="hc-vires">Arreglo est&eacute;tico.</li>
																</ul>
																<ul>
																		<li class="hc-vires">Embalsamado del cuerpo.</li>
																</ul>
														</div>
														<p class="hc-tedob"><strong>&#191;El servicio es trasferible&#63;</strong> No.</p>
														<h4 class="hc-ques">&#191;Puedo cambiar de servicios o de funeraria&#63;</h4>
														<p class="hc-ques-tex">KASU, cuenta con la m&aacute;s amplia red funeraria de M&eacute;xico, sin em-bargo puedes escoger otra funeraria siempre y cuando no exceda el topede costo del servicio.</p>
														<h4 class="hc-ques">&#191;Puedo cambiar de servicio un vez contratado&#63;</h4>
														<p class="hc-ques-tex">SI, siempre y cuando solicites a un ejecutivo KASU o acudas direct-amente a tu sucursal; en ning&uacute;n caso se podr&aacute; cambiar el tipo de servicio una vez que el titular ha fallecido.</p>
														<h4 class="hc-ques">&#191;Com&oacute; lo hago valer&#63;</h4>
														<p class="hc-ques-tex">Acudiendo directamente a la funeraria o marcando a los n&uacute;meros asignados presentando su tarjeta y poliza.</p>
												</div>
												';
											}
											?>
										</td>
										<td class="hc-seru">
											<div>
													<h1 class="hc-sert"> NOSOTROS </h1>
													<br>
													<h4 class="hc-ques">&#191;Qu&eacute; es KASU&#63;</h4>
													<p class="hc-ques-tex">KASU es una empresa que desarrolla productos financieros (fideicomiso), para solventar momentos importantes en tu vida y la de los tuyos. </p>
													<h4 class="hc-ques">&#191;Qu&eacute; es el Fideicomiso F/0003&#63;</h4>
													<p class="hc-ques-tex">Un Fideicomiso es un contrato mediante el cual,
																							una persona transmite a una institucion fiduciaria La titularidad
																							de una cantidad de dinero, para ser destinado para un fin determinado,
																							encomendando la realizaci&oacute;n de dicho fin a la institucion fiduciaria
																							misma que solo podra realizar las actividades para las que fue encomendada.</p>
													<h4 class="hc-ques">&#191;Qui&eacute;n es el Fiduciario de KASU &#63;</h4>
													<p class="hc-ques-tex">Fundado en 2006 en M&eacute;xico, <strong>Capital&amp;Fondeo M&eacute;xico</strong>
														                  es una firma especializada en la gestion de activos, y el desarrollo
																							de fideicomisos actuando como intitucion fiduciaria apoyando a sus
																							clientes, protegi&eacute;ndolos y ayud&aacute;ndolos a alcanzar sus
																							metas en la vida. </p>
													<?php
															if($Producto == "Universidad"){
																	echo '
																	<h4 class="hc-ques">&#191;Qui&eacute; es una Inversi&oacute;n Universitaria &#63;</h4>
																	<p class="hc-ques-tex">Es un servicio que te permite pagar la universidad de tus hijos,
																											pagando hoy una aportaci&oacute;n m&iacute;nima, para que si algo llega a suceder
																											un imprevisto tus hijos tengan una educacion asegurada.</p>
																	<p class="hc-ques-tex">Mediante una estructura de fideicomiso KASU te permite pagar la
																											educacion de tus hijos, brindando hasta 10 semestres de educacion
																											privada, recuerda que preveenir es mejor.
																											</p>
																	';
															}else{
																	echo '
																	<h4 class="hc-ques">&#191;Qui&eacute; es un Servicio de Gastos Funerarios &#63;</h4>
																	<p class="hc-ques-tex">Es un servicio que te permite pagar los gastos funerarios tuyos
																											o de alg&uacute;n ser querido, pagando hoy una aportaci&oacute;n m&iacute;nima,
																											para que el d&iacute;a que t&uacute; o tu ser querido fallezca no tengan que
																											pagar nada los deudos.</p>
																	<p class="hc-ques-tex">A diferencia de un seguro un servico a futuro te permite recibir
																											directamente el servicio de la funeraria de tu gusto y al haber
																											recibido el servicio la misma te entregue una factura por el mismo,
																											permitiendole a tus familiares cobrar estos servicios a un tercero.
																											</p>
																	';
															}
													?>
											</div>
										</td>
								</tr>
						</table>
						<img src="https://kasu.com.mx/assets/poliza/img2/trans2.jpg" class="hc-pie">
				</div>
		</body>
</html>
<?php
// accede a la datos y luego a la pagina
		$datospdf = new DOMPDF();
		$datospdf->set_paper("A4", "portrait");
		$datospdf->load_html(ob_get_clean());
		$datospdf->render();
		$output = $datospdf->output();
		$nombrePdf = "POLIZA-" . $fecha . ".pdf";
		file_put_contents(("DATES/" . $nombrePdf), $output);
		$datospdf->stream($nombrePdf);
?>
