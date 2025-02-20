<?php
//indicar que se inicia una sesion
	session_start();
//inlcuir el archivo de funciones
require_once '../eia/librerias.php';
//Validar si existe la session y redireccionar
    if(!isset($_SESSION["Vendedor"])){
        header('Location: https://kasu.com.mx/login');
      }
        //realizamos la consulta
        $venta = "SELECT * FROM Venta WHERE Id = '".$_GET['Id']."'";
        //Realiza consulta
            $res = mysqli_query($mysqli, $venta);
        //Si existe el registro se asocia en un fetch_assoc
            if($Reg=mysqli_fetch_assoc($res)){
            }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>cuenta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="/login/assets/css/styles.min.css">
		<link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
</head>
<body>
		<!--Inicio de menu principal fijo-->
		<section id="Menu">
			<?require_once 'html/Menuprinc.php';?>
		</section>
		<!--Final de menu principal fijo-->
    <h2>Estado de cuenta</h2>
    <div class="cabecera">
				<br>
        <p>&nbsp&nbsp&nbsp<strong><?PHP echo $Reg['Nombre'];?></strong></p>
				<p>&nbsp&nbsp&nbsp <strong>Producto;</strong> <?PHP echo $Reg['Producto'];?>
					&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp <strong>Status;</strong> <? echo $Reg['Status'];?>
				  &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp <strong>Costo de compra;</strong> <?PHP echo money_format('%.2n',$Reg['CostoVenta']);?></p>
    </div>
    <div class="historial">
      <table class="table">
                <tbody>
										<tr>
												<td><strong>Fecha</strong></td>
												<td><strong>Cantidad</strong></td>
												<td><strong>Status</strong></td>
										</tr>
                </tbody>
                <tbody>
                <?PHP
                //Crear consulta
                $Pagos = "SELECT * FROM Pagos WHERE IdVenta = '".$_GET['Id']."'";
                    //Realiza consulta
                        if ($resul = $mysqli->query($Pagos)){
                    // obtener el array de objetos
                            while ($Pago = $resul->fetch_row()) {

                                printf("
                                    <tr>
                                        <td>%s</td>
                                        <td>%s</td>
																				<td>%s</td>
                                    </tr>
                                ",substr($Pago[10], 0 , 10),money_format('%.2n', $Pago[5]),$Pago[7]);

                            }
                        }
                    ?>
                </tbody>
        </table>
    </div>
    <div class="pie">
        <div class="tablaCuenta" >
            <table class="table" >
                <tbody>
                    <tr>
                        <td>Pagos Realizados</td>
                        <td>
													<?PHP
														$PagsRe = $financieras->SumarPagos($mysqli,"cantidad","Pagos","IdVenta",$_GET['Id']);
														echo money_format('%.2n', $PagsRe);
                        	?>
												</td>
                    </tr>
										<tr>
                        <td>Moras Pagadas</td>
                        <td>
													<?PHP
														$MorRe = $financieras->SumarMora($mysqli,"cantidad","Pagos","IdVenta",$_GET['Id']);
														echo money_format('%.2n', $MorRe);
                        	?>
												</td>
                    </tr>
                    <tr>
                        <td>Para liquidar Hoy</td>
                        <td>
													<?PHP
													//Si el status del cliente esta en activacon no muestra el pago
													if($Reg['Status'] != "ACTIVO" AND $Reg['Status'] != "ACTIVACION" ){
															//Se imprime el valor para liquidar el credito
															$doa = $financieras->SaldoCredito($mysqli,$_GET['Id']);
															echo money_format('%.2n', $doa);
													}
													?>
												</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
		<br><br><br>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
</body>

</html>
