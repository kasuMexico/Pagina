<?php
//creamos una variable general para las funciones
$basicas = new Basicas();
//Archivo con el que se generan los Presupuestos
  //Se pasan las variables POST a Variable
  if(!isset($_POST['busqueda'])){
      $busqueda = $_GET['busqueda'];
  }else{
      $busqueda = $_POST['busqueda'];
  }
  //Cosnulta de la venta
  $Cdbt3 = "SELECT * FROM PrespEnviado WHERE Id = '".$busqueda."'";
  $lsCt3a = mysqli_query($pros, $Cdbt3);
  //Si existe el registro se asocia en un fetch_assoc
  		if($Propuest=mysqli_fetch_assoc($lsCt3a)){
        //Consulta a tabla de cotizacion
        $Ct3 = "SELECT * FROM prospectos WHERE Id = '".$Propuest['IdProspecto']."'";
        $Ct3a = mysqli_query($pros, $Ct3);
        //Si existe el registro se asocia en un fetch_assoc
            if($Prospecto=mysqli_fetch_assoc($Ct3a)){
?>
<html lang='es'>
<head>
	   <title>Propuesto de Venta</title>
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
							Nombre : <?php echo htmlentities($Prospecto['FullName'], ENT_QUOTES, "UTF-8"); ?> <br>
              Telefono : <? echo $Prospecto['NoTel']; ?> <br>
              Email : <? echo $Prospecto['Email']; ?> <br>
              Producto : <?php echo $Prospecto['Servicio_Interes']; ?><br>
					</div>
				<div class="card">
						<div class="cardheader">Propuesta de Venta</div>
						<div class="cardbody">
								<table class="table">
										<thead>
												<tr>
													<th>Fecha</th>
													<th>Concepto</th>
													<th>Cantidad</th>
													<th>Precio U.</th>
													<th>Costo</th>
												</tr>
										</thead>
										<tbody>
                      <?
                      if(!empty($Propuest['a0a29'])){
                        $Pra0a29 = $basicas->BuscarCampos($mysqli,"Costo","Productos","Producto","02a29");
                        $Pa0a29 = $Propuest['a0a29']*$Pra0a29;
                        echo '
											<tr>
												<td>'.date("d-m-Y", strtotime($Propuest['FechaRegistro'])).'</td>
												<td>Compra de servicio 02a29</td>
                        <td> '.$Propuest['a0a29'].' </td>
                        <td> '.money_format('%.2n',$Pra0a29).' </td>
                        <td>'.money_format('%.2n',$Pa0a29).'</td>
											</tr>
                      ';
                      }
                      if(!empty($Propuest['a30a49'])){
                        $Pra30a49 = $basicas->BuscarCampos($mysqli,"Costo","Productos","Producto","30a49");
                        $Pa30a49 = $Propuest['a30a49']*$Pra30a49;
                        echo '
                      <tr>
                        <td>'.date("d-m-Y", strtotime($Propuest['FechaRegistro'])).'</td>
                        <td>Compra de servicio 30a49</td>
                        <td> '.$Propuest['a30a49'].' </td>
                        <td> '.money_format('%.2n',$Pra30a49).' </td>
                        <td>'.money_format('%.2n',$Pa30a49).'</td>
                      </tr>
                      ';
                      }
                      if(!empty($Propuest['a50a54'])){
                        $Pra50a54 = $basicas->BuscarCampos($mysqli,"Costo","Productos","Producto","50a54");
                        $Pa50a54 = $Propuest['a50a54']*$Pra50a54;
                        echo '
                      <tr>
                        <td>'.date("d-m-Y", strtotime($Propuest['FechaRegistro'])).'</td>
                        <td>Compra de servicio 50a54</td>
                        <td> '.$Propuest['a50a54'].' </td>
                        <td> '.money_format('%.2n',$Pra50a54).' </td>
                        <td>'.money_format('%.2n',$Pa50a54).'</td>
                      </tr>
                      ';
                      }
                      if(!empty($Propuest['a55a59'])){
                        $Pra55a59 = $basicas->BuscarCampos($mysqli,"Costo","Productos","Producto","55a59");
                        $Pa55a59 = $Propuest['a55a59']*$Pra55a59;
                        echo '
                      <tr>
                        <td>'.date("d-m-Y", strtotime($Propuest['FechaRegistro'])).'</td>
                        <td>Compra de servicio 55a59</td>
                        <td> '.$Propuest['a55a59'].' </td>
                        <td> '.money_format('%.2n',$Pra55a59).' </td>
                        <td>'.money_format('%.2n',$Pa55a59).'</td>
                      </tr>
                      ';
                      }
                      if(!empty($Propuest['a60a64'])){
                        $Pra60a64 = $basicas->BuscarCampos($mysqli,"Costo","Productos","Producto","60a64");
                        $Pa60a64 = $Propuest['a60a64']*$Pra60a64;
                        echo '
                      <tr>
                        <td>'.date("d-m-Y", strtotime($Propuest['FechaRegistro'])).'</td>
                        <td>Compra de servicio 60a64</td>
                        <td> '.$Propuest['a60a64'].' </td>
                        <td> '.money_format('%.2n',$Pra60a64).' </td>
                        <td>'.money_format('%.2n',$Pa60a64).'</td>
                      </tr>
                      ';
                      }
                      if(!empty($Propuest['a65a69'])){
                        $Pra65a69 = $basicas->BuscarCampos($mysqli,"Costo","Productos","Producto","65a69");
                        $Pa65a69 = $Propuest['a65a69']*$Pra65a69;
                        echo '
                      <tr>
                        <td>'.date("d-m-Y", strtotime($Propuest['FechaRegistro'])).'</td>
                        <td>Compra de servicio 65a69</td>
                        <td> '.$Propuest['a65a69'].' </td>
                        <td> '.money_format('%.2n',$Pra65a69).' </td>
                        <td>'.money_format('%.2n',$Pa65a69).'</td>
                      </tr>
                      ';
                      }
                      //SE suman los valores
                      $sal = $Pa0a29+$Pa30a49+$Pa50a54+$Pa55a59+$Pa60a64+$Pa65a69;
                      //Se suman las cantidades
                      $Canti = $Propuest['a0a29']+$Propuest['a30a49']+$Propuest['a50a54']+$Propuest['a55a59']+$Propuest['a60a64']+$Propuest['a65a69'];
                      //se obtiene la tasa de interes
                      $tasa = $basicas->BuscarCampos($mysqli,"TasaAnual","Productos","Producto","02a29");
                      //Tasa anual se divide en meses
                      $aR=$tasa/12;
                      //tasa entre 100
                      $a=$aR/100;
                      //SE le suma 1
                      $a = 1+$a;
                      //Potencia
                      $sr = pow($a,$Propuest['plazo']);
                      //SAldo Real
                      $saldo = $sal*$sr;
                      //Pago de el periodo
                      $pagm = $saldo/$Propuest['plazo'];
                      ?>
										</tbody>
								</table>
								<table class="table">
										<tbody>
                      <?
                      if($Propuest['plazo'] == 1){
                        echo '
												<tr>
                          <td><strong>Saldo Neto a pagar</strong></td>
													<td>'.money_format('%.2n',$sal).'</td>
												</tr>
                        ';
                      }else{
                        echo '
                        <tr>
                          <td><strong>'.$Propuest['plazo'].' Pagos mensuales de</strong></td>
                          <td> '.money_format('%.2n',$pagm).' </td>
                        </tr>
                        <tr>
                          <td><strong>Saldo Total a pagar</strong></td>
                          <td>'.money_format('%.2n',$saldo).'</td>
                        </tr>
                        ';
                      }
                      ?>
										</tbody>
								</table>
						</div>
				</div>
		</div>
		<img src="https://kasu.com.mx/assets/poliza/img2/LINE7.jpg" class="h-line">
		<h2 class="url">CONSULTA NUESTRO AVISO DE PRIVACIDAD EN :WWW.KASU.COM.MX/AVISOPRIVACIDAD.HTML</h2>
		<br>
		<img src="https://kasu.com.mx/assets/poliza/img2/img.jpg" class="fin2">
  </body>
</html>
<?php
}}
?>
