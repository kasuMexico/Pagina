<?php
	//VAriables principales de session
	session_start();
	require_once '../eia/librerias.php';
  date_default_timezone_set('America/Mexico_City');
  //Validar si existe la session y redireccionar
  if(empty($_SESSION['Vendedor'])){
      header('Location: https://kasu.com.mx/login');
    }
	//Variables principales
	$busqueda = $_POST['busqueda'];
	//Cosnulta de la venta
	$Ct3 = "SELECT * FROM Venta WHERE Id = '".$busqueda."'";
	$Ct3a = mysqli_query($mysqli, $Ct3);
	//Si existe el registro se asocia en un fetch_assoc
			if($venta=mysqli_fetch_assoc($Ct3a)){
	//Realiza consulta
        	$Ct1 = "SELECT * FROM Contacto WHERE id = '".$venta['IdContact']."'";
        	$Ct1a = mysqli_query($mysqli, $Ct1);
        	//Si existe el registro se asocia en un fetch_assoc
        			if($datos=mysqli_fetch_assoc($Ct1a)){
    	            //Realiza consulta
                	$Ct2 = "SELECT * FROM Usuario WHERE IdContact = '".$venta['IdContact']."'";
                	$Ct2a = mysqli_query($mysqli, $Ct2);
                	//Si existe el registro se asocia en un fetch_assoc
                			if($persona=mysqli_fetch_assoc($Ct2a)){
	//Saldo de el credito
	if($venta['Status'] == "ACTIVO" || $venta['Status'] == "ACTIVACION"){
		$saldo = money_format('%.2n', 0);
	}else {
		$saldo = $financieras->SaldoCredito($mysqli,$venta['Id']);
	}
	//SI el usuario compro a un mes o de contado
	if( $venta['NumeroPagos'] >= 2 ){
			$Credito = "Compra a credito; ".$venta['NumeroPagos']." Meses";
	}else{
			$Credito = "Compra de contado";
	}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
   <meta charset="utf-8">
   <title>Estado de Cuenta</title>
   <link rel="shortcut icon" href="../assets/images/logokasu.ico">
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
	 <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons'>
	 <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
</head>
<body>
		 <nav class="navbar navbar-expand-lg text-white  justify-content-between " style="background-color:#8D70E3;">
		    <a class="navbar-brand">Estado Cuenta</a>
		    <div class="form-inline">
						<!--Retorna a la ventana aterior-->
						<form action="Mesa_Clientes.php" method="post" style="padding-right: 5px;">
								<input type='text' name='nombre' value='<? echo $_POST['nombre'];?>' style='display: none;'/>
								<label for='Regresar' title='Regresar a cliente' class='btn' style='background: #F5B041; color: #F8F9F9;' ><i class='material-icons'>undo</i></label>
								<input id='Regresar' type='submit' value='Regresar' name='Accion' class='hidden' style='display: none;'/>
						</form>
						<!--Enviar Datos para envio de correo electronico con el estado de cuenta-->
						<form action="../eia/EnviarCorreo.php" method="post" style="padding-right: 5px;">
								<input type='text' name='IdVenta' value='<? echo $busqueda;?>' style='display: none;'/>
								<input type="text" name="FullName" value="<?PHP echo $persona['Nombre'];?>" style="display: none;">
								<input type="text" name="Email" value="<?PHP echo $datos['Mail'];?>" style="display: none;">
								<input type="text" name="Asunto" value="ENVIO ARCHIVO" style="display: none;">
								<input type="text" name="Descripcion" value="Estado de Cuenta" style="display: none;">
								<label for='Enviar' title='Enviar estado de cuenta' class='btn' style='background: #2980B9; color: #F8F9F9;' ><i class='material-icons'>email</i></label>
								<input id='Enviar' type='submit' value='Enviar' name='EnviarEdoCta' class='hidden' style='display: none;' />
						</form>
						<!--Descargar el estado de cuenta-->
						<a class='btn' style='background: #58D68D; color: #F8F9F9;' href="https://kasu.com.mx/login/Generar_PDF/Estado_Cuenta_pdf.php?busqueda=<?echo base64_encode($busqueda);?>"><i class='material-icons'>download</i></a>
				</div>
		</nav>
		<br>
		<div class="container">
			<div class="card">
			  <div class="card-header bg-secondary text-light"><?php echo $persona['Nombre']; ?></div>
			    <div class="card-body">
				      <div class="row">
									<div class="col">
											<div class="card">
													<div class="card-header bg-secondary text-light">Datos de la empresa</div>
													<div class="card-body">
														 KASU, Servicios a Futuro S.A de C.V. <br>
														 Fideicomiso F/0003 Gastos Funerarios<br>
														 Atlacomulco, Estado de Mexico, Mexico C.P. 50450<br>
														 Telefono: <? echo $tel;?><br>
													</div>
											</div>
									</div>
					        <div class="col">
						          <div class="card">
						            	<div class="card-header bg-secondary text-light">Datos del Cliente</div>
								          <div class="card-body">
								             Nombre : <?php echo $persona['Nombre']; ?> <br>
								             CURP : <?php echo $persona['ClaveCurp']; ?><br>
								             Fecha Registro : <?php echo substr($venta['FechaRegistro'], 0 , 10); ?><br>
                             Fecha Utima Moficacion : <?php echo substr($persona['FechaRegistro'], 0 , 10); ?>
								          </div>
						        	</div>
					        </div>
				      </div>
							<br>
			      	<div class="row">
			        		<div class="col">
			          			<div class="card">
			            				<div class="card-header bg-secondary text-light"></div>
																<div class="card-body">
																		<div class="row">
																				<div class="col">
																					Direccion : <? echo $datos['Direccion']; ?> <br>
																					Telefono : <? echo $datos['Telefono']; ?> <br>
																					Email : <? echo $datos['Mail']; ?> <br>
												                  Producto : <?php echo $venta['Producto']; ?><br>
												                  N. Activador : <?php echo $venta['IdFIrma']; ?> <br>
												                  Status : <?php echo $venta['Status']; ?><br>
												                  <?php echo $Credito; ?><br>
												                  </div>
																				</div>
																		</div>
																</div>
														</div>
												</div>
						  <br>
							<div class="row">
			        		<div class="col">
			          			<div class="card">
			                		<div class="card-header bg-secondary text-light">Historial de transacciones</div>
															<div class="card-body">
																	<table class="table table-hover">
																			<thead>
																					<tr>
																						  <th>Fecha</th>
																						  <th>Concepto</th>
																							<th>Saldo</th>
																							<th>Debe</th>
																							<th>Haber</th>
																					</tr>
																			</thead>
																			<tbody>
																					<tr>
																						<td><? echo substr($venta['FechaRegistro'], 0 , 10); ?></td>
																						<td>Compra de servicio <? echo $datos['Producto']; ?></td>
																						<td><?php echo money_format('%.2n',$venta['CostoVenta']); ?></td>
																						<td> - </td>
																						<td> - </td>
																					</tr>
																							<?
																									//Realiza consulta
																									$Ct4 = "SELECT * FROM Pagos WHERE IdVenta = '".$busqueda."'";
																									if ($resultado = $mysqli->query($Ct4)) {
																										/* obtener un array asociativo */
																										while ($pago = $resultado->fetch_assoc()) {
																											printf ("
																															<tr>
																																<td>%s</td>
																																<td>%s de Servicio %s</td>
																																<td> - </td>
																																<td>%s</td>
																																<td>$ -  Mxn</td>
																															</tr>
																															"
																											,substr($pago['FechaRegistro'], 0 , 10),$pago['status'],$venta['Producto'],money_format('%.2n',$pago['Cantidad']));
																										}
																									}
																								?>
																				</tbody>
																	</table>
															</div>
												</div>
										</div>
								</div>
								<div class="row">
				        		<div class="col">
				          			<div class="card">
													<div class="card-body">
														<table class="table table-hover">
															<tbody>
																<tr>
																	<td>Saldo de la cuenta</td>
																	<td><?php echo $saldo; ?></td>
																</tr>
																<?
																	}}}
																?>
																</tbody>
															</table>
														</div>
													</div>
											</div>
									</div>
						</div>
				</div>
		</div>
  </body>
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</html>
