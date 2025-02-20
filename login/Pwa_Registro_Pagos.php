<?php
//indicar que se inicia una sesion
session_start();
setlocale(LC_ALL, 'es_ES');
//inlcuir el archivo de funciones
require_once '../eia/librerias.php';
//Validar si existe la session y redireccionar
    if(!isset($_SESSION["Vendedor"])){
        header('Location: https://kasu.com.mx/login');
      }else if (!empty($_POST['SelCte'])) {
      //}else if (!empty($_GET['SelCte'])) {
        //realizamos la consulta
        $venta = "SELECT * FROM Venta WHERE Id = '".$_POST['IdVenta']."'";
        //$venta = "SELECT * FROM Venta WHERE Id = '".$_GET['SelCte']."'";
        //Realiza consulta
            $res = mysqli_query($mysqli, $venta);
        //Si existe el registro se asocia en un fetch_assoc
            if($Reg=mysqli_fetch_assoc($res)){
              //Se obtiene el monto de la deuda
              $Pago = Financieras::Pago($mysqli,$_POST['IdVenta']);
              //Se obtiene el numero de pagos pendientes
              $PagoPend = Financieras::PagosPend($mysqli,$_POST['IdVenta']);
							//se obtiene la fecha de promesa de el acuerdo
							$FecProm = $basicas->BuscarCampos($mysqli,"FechaReg","PromesaPago","id",$_POST["Referencia"]);
              //sE obtiene la cantidad de la promesa de pago
              $CantProm = $basicas->BuscarCampos($mysqli,"Pago","PromesaPago","id",$_POST["Referencia"]);
              //realizamos la consulta
              $GpsSql = "SELECT * FROM gps WHERE Id = '".$Reg['Idgps']."'";
              //Realiza consulta
                  $resGps = mysqli_query($mysqli, $GpsSql);
              //Si existe el registro se asocia en un fetch_assoc
                  if($RegGps=mysqli_fetch_assoc($resGps)){
                    $Gps = "geo:".$RegGps['Latitud'].",".$RegGps['Longitud'].";u=".$RegGps['Presicion'];
                  }
            }
            $Ventana = "Ventana2";
      }
//Buscar el nivel de el usuario
$Niv = $basicas->BuscarCampos($mysqli,"Nivel","Empleados","IdUsuario",$_SESSION["Vendedor"]);
//Buscar el id de el vendedor
$IdVen = $basicas->BuscarCampos($mysqli,"Id","Empleados","IdUsuario",$_SESSION["Vendedor"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Cartera Clientes</title>
    <!-- CODELAB: Add meta theme-color -->
    <meta name="theme-color" content="#2F3BA2" />
    <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
    <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="/login/assets/css/styles.min.css">
    <!-- Inicio Librerias prara las ventanas emergentes automaticas-->
    <script src='https://code.jquery.com/jquery-3.3.1.slim.min.js' integrity='sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo' crossorigin='anonymous'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js' integrity='sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49' crossorigin='anonymous'></script>
    <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js' integrity='sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy' crossorigin='anonymous'></script>
<!-- Fin Librerias prara las ventanas emergentes automaticas-->
</head>
<body>
  <!--onload="localize();"-->
  <!--Inicio de menu principal fijo-->
    <section id="Menu">
      <?require_once 'html/Menuprinc.php';?>
    </section>
    <!--Final de menu principal fijo-->
    <!--Inicio Creacion de las ventanas emergentes-->
    <script type='text/javascript'>
        $( document ).ready(function() {
            $('#<?PHP echo $Ventana;?>').modal('toggle')
        });
    </script>
    <!-- Modal que Registra el pago de el cliente -->
    <div class="modal fade" id="Ventana2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
              <? require 'html/EmPago.php'; ?>
            </div>
        </div>
    </div>
    <section class="container"  style="width: 99%;">
        <form  method="post">
            <div class="form-group">
                <h2>Registro de pagos y promesas</h2>
                <hr>
            </div>
            <div class="form-group">
                <div class="table-responsive" >
                   <?PHP
                    if($Niv >= 5){
										//Variables principales
										$df = 1;
										$Ca = 3;
										//Se registra las fechas que se sumaran al tiempo
										while ($df <= $Ca) {
											// se imprimen los pagos por cada dos semanas
											if($df == 1){
												$cd = '+ 7';
												$Ya = '- 1';
											}elseif($df == 2){
												$cd = '+ 15';
												$Ya = '+ 8';
											}else{
												echo "<h2>Pagos del periodo</h2>";
												$cd = '+ 2';
												$Ya = '- 1';
											}
											//Fecha Liminte inferior
											$limiSup = date("Y-m-d",strtotime("$Ya days"));
											//Fecha limite superior
											$limiInf = date("Y-m-d",strtotime("$cd days"));
											//Se imprime el periodo
											echo "<p>Semana del ".strftime("%d", strtotime($limiSup))." al ".strftime("%d de %B", strtotime($limiInf))."</p>";
	                    //Crear consulta para las promesas de pago
											if($df == 3){
												//Fecha Liminte inferior
												$limA = date("Y-m-d",strtotime($limiSup."-14 days"));
												//Fecha limite superior
												$limB = date("Y-m-d",strtotime($limiInf."-14 days"));
												//Si el cliente pago hace 15 dias se lanza de nuevo el pago  AND Usuario = '".$_SESSION["Vendedor"]."'
												$Ventas = "SELECT * FROM Pagos WHERE Usuario = '".$_SESSION["Vendedor"]."' AND FechaRegistro >= '".$limA."' AND FechaRegistro <= '".$limB."' ";
												//si el cliente no ha realizado pago se imprime
											}else{
												//Consulta para las promesas de pago que se han registrado  AND Usuario = '".$_SESSION["Vendedor"]."'
												$Ventas = "SELECT * FROM PromesaPago WHERE User = '".$_SESSION["Vendedor"]."' AND FechaPago >= '".$limiSup."' AND FechaPago <= '".$limiInf."' ";
											}
	                    //Realiza consulta
	                    if ($resultado = $mysqli->query($Ventas)){
	                    // obtener el array de objetos
	                        while ($fila = $resultado->fetch_row()) {
														//Se busca si la promesa de pago ya se realizo
														$RefPag = $basicas->BuscarCampos($mysqli,"Id","Pagos","Referencia",$fila[0]);
														//Si el Id no esta registrado imprime la promesa de pago
														if(empty($RefPag)){
	                        	//Se busca el usuario
	                              $Venta = "SELECT * FROM Venta WHERE Id = '".$fila[1]."' AND	Usuario = '".$_SESSION["Vendedor"]."'";
																//Se aterriza la fecha de promesa
																$Fdp = strftime("%d de %B", strtotime($fila[2]));
																//Se modifica lo q el cliente ve si no hay promesa de pago
																if($df == 3){
																	//Cuando df se activa en 3 quiere decir que son los pagos futuros del periodo
																		$Fdp = strftime("%d de %B", strtotime($fila[13]."+14 days"));
																	//Se busca el pago por el cliente si existe y es por la cantidad del pago no se imprime
																	//Se suman los pagos dentro del periodo
																		$SuPagT = $basicas->SumarFechas($mysqli,"Cantidad","Pagos","IdVenta",$fila[1],'FechaRegistro',$limiInf,'FechaRegistro',$limiSup);
																	//Se resta la suma de los pagos a el pago que le corresponde
																		$pago = Financieras::Pago($mysqli, $fila[1]);
																	//Se operan los pagos para saber si ya re realizo el pago o la suma de los pagos da el pago que le corresponde
																		$dj = $pago-$SuPagT;
																	//Si el pago dado es mayor a el que le corresponde no se imprime
																	if($dj <= 0){
																		$Venta = NULL;
																	}
																}
	                        //Se ejecuta la consulta
	                              $S62 = $mysqli->query($Venta);
	                              $S63 = $S62->fetch_row();
	                              printf("
	                                  <form method='POST' action='".$_SERVER['PHP_SELF']."'>
	                                      <input type='number'id='IdVenta' name='IdVenta' style='display: none;' value='%s'>
																				<input type='number'id='Referencia' name='Referencia' style='display: none;' value='%s'>
                                        <input type='number'id='Promesa' name='Promesa' style='display: none;' value='%s'>
																				<span class='new badge blue ".$S63[10]."' data-badge-caption='' style='position: relative; padding: 0px; width: 100px; top: 10px;'>".$S63[10]."</span>
	                                      <input type='submit' id='%s' name='SelCte' class='%s' value='".$Fdp." - %s' >
	                                  </form>
	                                  ",$S63[0],$fila[0],$fila[3],$S63[10],$S63[10],$S63[3]);
																	}
			                        }
			                    }
													$df++;
												}
										}elseif($Niv <= 4){
                      //Buscamos el id de la sucursal
                      $IdSuc = $basicas->BuscarCampos($mysqli,"Sucursal","Empleados","IdUsuario",$_SESSION["Vendedor"]);
                      //Buscamos el nombre de la sucursal
                      $NomSuc = $basicas->BuscarCampos($mysqli,"NombreSucursal","Sucursal","Id",$IdSuc);
                      //Crear consulta
                      $sqal = "SELECT * FROM Empleados WHERE Nombre != 'Vacante' AND Nivel >= '$Niv' AND Sucursal = $IdSuc";
                      //Realiza consulta
                      $r4e9s = $mysqli->query($sqal);
                      //Si existe el registro se asocia en un fetch_assoc
                      foreach ($r4e9s as $Resd5){
                        //Variables principales
    										$df = 1;
    										$Ca = 3;
    										//Se registra las fechas que se sumaran al tiempo
    										while ($df <= $Ca) {
    											// se imprimen los pagos por cada dos semanas
    											if($df == 1){
    												$cd = '+ 7';
    												$Ya = '- 1';
    											}elseif($df == 2){
    												$cd = '+ 15';
    												$Ya = '+ 8';
    											}else{
    												$cd = '+ 2';
    												$Ya = '- 1';
    											}
    											//Fecha Liminte inferior
    											$limiSup = date("Y-m-d",strtotime("$Ya days"));
    											//Fecha limite superior
    											$limiInf = date("Y-m-d",strtotime("$cd days"));
                          // // if($df == 1){
                          // echo "<br>".$df;
                          //   //Se imprime el periodo
                          //   echo "<p>Semana del ".strftime("%d", strtotime($limiSup))." al ".strftime("%d de %B", strtotime($limiInf))."</p>";
                          // // }
    	                    //Crear consulta para las promesas de pago
    											if($df == 3){
    												//Fecha Liminte inferior
    												$limA = date("Y-m-d",strtotime($limiSup."-14 days"));
    												//Fecha limite superior
    												$limB = date("Y-m-d",strtotime($limiInf."-14 days"));
    												//Si el cliente pago hace 15 dias se lanza de nuevo el pago  AND Usuario = '".$_SESSION["Vendedor"]."'
    												$Ventas = "SELECT * FROM Pagos WHERE Usuario = '".$Resd5["IdUsuario"]."' AND FechaRegistro >= '".$limA."' AND FechaRegistro <= '".$limB."' ";
    												//si el cliente no ha realizado pago se imprime
    											}else{
    												//Consulta para las promesas de pago que se han registrado  AND Usuario = '".$_SESSION["Vendedor"]."'
    												$Ventas = "SELECT * FROM PromesaPago WHERE User = '".$Resd5["IdUsuario"]."' AND FechaPago >= '".$limiSup."' AND FechaPago <= '".$limiInf."' ";
    											}
    	                    //Realiza consulta
    	                    if ($resultado = $mysqli->query($Ventas)){
    	                    // obtener el array de objetos
    	                        while ($fila = $resultado->fetch_row()) {
    														//Se busca si la promesa de pago ya se realizo
    														$RefPag = $basicas->BuscarCampos($mysqli,"Id","Pagos","Referencia",$fila[0]);
    														//Si el Id no esta registrado imprime la promesa de pago
    														if(empty($RefPag)){
    	                        	//Se busca el usuario
    	                              $Venta = "SELECT * FROM Venta WHERE Id = '".$fila[1]."' AND	Usuario = '".$Resd5["IdUsuario"]."'";
    																//Se aterriza la fecha de promesa
    																$Fdp = strftime("%d de %B", strtotime($fila[2]));
    																//Se modifica lo q el cliente ve si no hay promesa de pago
    																if($df == 3){
    																	//Cuando df se activa en 3 quiere decir que son los pagos futuros del periodo
    																		$Fdp = strftime("%d de %B", strtotime($fila[13]."+14 days"));
    																	//Se busca el pago por el cliente si existe y es por la cantidad del pago no se imprime
    																	//Se suman los pagos dentro del periodo
    																		$SuPagT = $basicas->SumarFechas($mysqli,"Cantidad","Pagos","IdVenta",$fila[1],'FechaRegistro',$limiInf,'FechaRegistro',$limiSup);
    																	//Se resta la suma de los pagos a el pago que le corresponde
    																		$pago = Financieras::Pago($mysqli, $fila[1]);
    																	//Se operan los pagos para saber si ya re realizo el pago o la suma de los pagos da el pago que le corresponde
    																		$dj = $pago-$SuPagT;
    																	//Si el pago dado es mayor a el que le corresponde no se imprime
    																	if($dj <= 0){
    																		$Venta = NULL;
    																	}
    																}
    	                        //Se ejecuta la consulta
    	                              $S62 = $mysqli->query($Venta);
    	                              $S63 = $S62->fetch_row();
    	                              printf("
    	                                  <form method='POST' action='".$_SERVER['PHP_SELF']."'>
    	                                      <input type='number'id='IdVenta' name='IdVenta' style='display: none;' value='%s'>
    																				<input type='number'id='Referencia' name='Referencia' style='display: none;' value='%s'>
                                            <input type='number'id='Promesa' name='Promesa' style='display: none;' value='%s'>
                                            <input type='text' name='IdVendedor' style='display:  none;' value='".$Resd5["IdUsuario"]."' />
    																				<span class='new badge blue ".$S63[10]."' data-badge-caption='' style='position: relative; padding: 0px; width: 100px; top: 10px;'>".$S63[10]."</span>
    	                                      <input type='submit' id='%s' name='SelCte' class='%s' value='".$Fdp." - %s - ".$Resd5["IdUsuario"]." - $NomSuc' >
    	                                  </form>
    	                                  ",$S63[0],$fila[0],$fila[3],$S63[10],$S63[10],$S63[3]);
    																	}
    			                        }
    			                    }
    													$df++;
    												}
                        }
                    }
                    ?>
                </div>
            </div>
        </form>
    <br><br><br><br>
    </section>
    <!-- End: Login Form Clean -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="Javascript/finger.js"></script>
    <script type="text/javascript" src="Javascript/localize.js"></script>
</body>

</html>
