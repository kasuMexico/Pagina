<?php
echo '
<html lang="es">
<head>
	   <title>Estado de Cuenta</title>
		 <link rel="stylesheet" href="css/EstadoCta.css">
</head>
<body>
	<table class="t-h">
			<tr>
					<td>
						<h1 class="ha-text"><strong>KASU, Servicios a Futuro S.A de C.V.  </strong></h1>
						<p class="hb-text"> Julian Gonzalez 10 2do piso, Fermin J. Villaloz</p>
						<p class="hb-text"> Atlacomulco, Estado de Mexico, Mexico C.P. 50450</p>
						<p class="hb-text"> Telefono: <? echo $tel;?></p>
					</td>
			</tr>
	</table>
	<img src="https://kasu.com.mx/assets/poliza/img2/transp.jpg" class="header">
		<div class="container">
			 <div class="cardheader">Datos del Cliente</div>
					<div class="cardbody">
							Nombre : '.htmlentities($persona['Nombre'], ENT_QUOTES, "UTF-8").'<br>
							CURP : '.$persona['ClaveCurp'].'<br>
							Fecha Registro : '.substr($venta['FechaRegistro'], 0 , 10).'<br>
							Fecha Utima Moficacion : '.substr($persona['FechaRegistro'], 0 , 10).'<br>
					</div>
				<div class="cardheader"></div>
				<div class="cardbody">
								Direccion : '.htmlentities($datos['Direccion'], ENT_QUOTES, "UTF-8").'<br>
								Telefono : '.$datos['Telefono'].'<br>
								Email : '.$datos['Mail'].'<br>
								Producto : '.$venta['Producto'].'<br>
								N. Activador : '.$venta['IdFIrma'].'<br>
								Status : '.$venta['Status'].'<br>
								'.$Credito.'<br>
				</div>
				<div class="card">
						<div class="cardheader">Historial de transacciones</div>
						<div class="cardbody">
								<table class="table">
										<thead>
												<tr>
													<th>Fecha</th>
													<th>Concepto</th>
													<th>Saldo</th>
													<th>Pagos</th>
												</tr>
										</thead>
										<tbody>
											<tr>
												<td>'.substr($venta['FechaRegistro'], 0 , 10).'</td>
												<td>Compra de servicio '.$datos['Producto'].'</td>
												<td>'.money_format('%.2n',$venta['CostoVenta']).'</td>
												<td> - </td>
											</tr>';
											//Realiza consulta
											$Ct4 = "SELECT * FROM Pagos WHERE IdVenta = '".$busqueda."'";
											if ($resultado = $mysqli->query($Ct4)) {
												/* obtener un array asociativo */
												while ($pago = $resultado->fetch_assoc()) {
													printf (
                          "
													<tr>
  													<td>%s</td>
  													<td>%s de Servicio %s</td>
  													<td> - </td>
  													<td> %s </td>
													</tr>
													"
													,substr($pago['FechaRegistro'], 0 , 10),$pago['status'],$venta['Producto'],money_format('%.2n',$pago['Cantidad'])
                        );
												}
											}
										echo '
										</tbody>
								</table>
								<table class="table">
										<tbody>
												<tr>
													<td>Saldo de la cuenta</td>
													<td>'.$saldo.'</td>
												</tr>
										</tbody>
								</table>
						</div>
				</div>
		</div>
		<img src="https://kasu.com.mx/assets/poliza/img2/LINE7.jpg" class="h-line">
		<h2 class="url">CONSULTA NUESTRO AVISO DE PRIVACIDAD EN :WWW.KASU.COM.MX/AVISOPRIVACIDAD.HTML</h2>
		<img src="https://kasu.com.mx/assets/poliza/img2/img.jpg" class="fin2">
  </body>
</html>';
?>
