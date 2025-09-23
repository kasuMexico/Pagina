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
        $venta = "SELECT * FROM Venta WHERE Id = '".base64_decode($_GET['busqueda'])."'";
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
				  &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp <strong>Costo de compra;</strong> <?PHP echo number_format($Reg['CostoVenta'],2);?></p>
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
                <?php
                // Asume $mysqli ya conectado
                $mysqli->set_charset('utf8mb4');

                if (!isset($_GET['busqueda'])) { exit; }

                $dec = base64_decode($_GET['busqueda'], true);
                if ($dec === false) { exit; }

                $IdVenta = trim($dec);
                if (!ctype_digit($IdVenta)) { exit; }
                $IdVenta = (int)$IdVenta;

                $sql  = "SELECT * FROM Pagos WHERE IdVenta = ?";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param('i', $IdVenta);
                $stmt->execute();
                $res  = $stmt->get_result();

                // Formateador de moneda MXN
                $fmt = class_exists('NumberFormatter') ? new NumberFormatter('es_MX', NumberFormatter::CURRENCY) : null;

                while ($Pago = $res->fetch_row()) {
                    // Ajusta índices según tu esquema: [10]=fecha, [5]=monto, [7]=estatus
                    $fecha  = htmlspecialchars(substr((string)$Pago[10], 0, 10), ENT_QUOTES, 'UTF-8');
                    $monto  = (float)$Pago[5];
                    $estatus= htmlspecialchars((string)$Pago[7], ENT_QUOTES, 'UTF-8');

                    $montoFmt = $fmt ? $fmt->formatCurrency($monto, 'MXN') : ('$' . number_format($monto, 2, '.', ','));

                    printf(
                        "<tr>\n  <td>%s</td>\n  <td>%s</td>\n  <td>%s</td>\n</tr>\n",
                        $fecha,
                        $montoFmt,
                        $estatus
                    );
                }

                $stmt->close();
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
								$PagsRe = $financieras->SumarPagos($mysqli,"cantidad","Pagos","IdVenta",$IdVenta);
								echo number_format($PagsRe,2);
                        	?>
						</td>
                    </tr>
						<tr>
                            <td>Moras Pagadas</td>
                            <td>
							<?PHP
								$MorRe = $financieras->SumarMora($mysqli,"cantidad","Pagos","IdVenta",$IdVenta);
								echo number_format($MorRe,2);
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
							$doa = $financieras->SaldoCredito($mysqli,$IdVenta);
							echo number_format($doa,2);
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
