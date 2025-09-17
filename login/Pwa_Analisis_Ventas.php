<?
	session_start();

	// Incluir funciones y conexiones
	require_once '../eia/librerias.php';
	require_once 'php/Analisis_Metas.php';

	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
			die('DB no inicializada');
		}
		if (!$mysqli->ping()) {
			die('DB desconectada: ' . $mysqli->connect_error);
		}


	// Zona horaria y "hoy" como objeto DateTimeImmutable
	$tz  = new DateTimeZone('America/Mexico_City');
	$hoy = new DateTimeImmutable('today', $tz);

	// Validar sesión o redireccionar
	if (!isset($_SESSION["Vendedor"])) {
		if (isset($_GET['dataP'])) {
			$sua = base64_decode($_GET['dataP']);
			if ($sua == "ValidJCCM") {
				$_SESSION["dataP"] = $sua;
			} else {
				header('Location: https://kasu.com.mx/login');
				exit;
			}
		} else {
			header('Location: https://kasu.com.mx/login');
			exit;
		}
	} else {
		$Niv = $basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
	}

	// Crear formateador decimal
	$fmt = new NumberFormatter('es_MX', NumberFormatter::DECIMAL);
	$fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

	// Declarar todas las variables necesarias
	$_SESSION["dataP"] = 0;
	$Fec0 = date("Y-m-01");
	$vTtOT = 0;
	$F0003 = 0;
	$VaF0003 = 0;
	$CarteCol = 0;
	$SaldCre1 = 0;
	$PagEr = 0;
	$PagEnMor3 = 0;
	$PagEnMor = 0;
	$ft = 0;
	$V = 0;
	$ed = 0;
	$Ed1Cte = 0;
	$tuArray = [];
	$pagoHoy = 0;
	$pagPero = 0;
	$AvCob = 0;
	$SUeldos = 0;
	$comisiones = 0;
	$CacVta = 0;
	$CicVta = 0;
	$EdadCte = 0;
	$ModaClie = 0;
	$SerPagados = 0;
	$Prod = [];
	$Año = [];
	$UVen = [];
	$NombreGraf = '';
	$ini = '';
	$in2 = '';
	$Med = '';
	$Me2 = '';
	$Fin = '';
	$Fi2 = '';
	$Nu = 0;
	$Ne = 0;
	$Uv = 0;
	$TotCtesACT = 0;
	$CtesMesCob = 0;
	$ServOtCte = 0;

	// Formateador monetario
	$fmtMoney = new NumberFormatter('es_MX', NumberFormatter::CURRENCY);

	// Obtener fecha inicial de ventas
	$FeIniVtas = date("d-M-Y", strtotime($basicas->MinDat($mysqli, "FechaRegistro", "Venta")));

	// Productos (no se usa el resultado en pantalla pero mantiene lógica previa)
	$sqal = "SELECT * FROM Productos";
	$r4e9s = $mysqli->query($sqal);
	foreach ($r4e9s as $Resd6){}

	// Prospectos del mes
	$ProsGen = $basicas->Cuenta0Fec($mysqli,"Contacto","FechaRegistro",$Fec0);
	$ProsDig = $basicas->Cuenta1Fec($mysqli,"Contacto","Usuario","PLATAFORMA","FechaRegistro",$Fec0);
	$ProsGen = $ProsGen - $ProsDig;

	$ProsDgt  = $basicas->Cuenta0Fec($pros,"prospectos","Alta",$Fec0);
	$ProsTier = $basicas->Cuenta1Fec($pros,"prospectos","Origen","vta","Alta",$Fec0);
	$ProsDgt  = $ProsDgt - $ProsTier;

	// Ventas del mes
	$Ventas  = $basicas->Cuenta0Fec($mysqli,"Venta","FechaRegistro",$Fec0);
	$VtaDine = $basicas->Sumar0Fecha($mysqli,"CostoVenta","Venta","FechaRegistro",$Fec0);

	// Valores acumulados por producto
	// Inicializa acumulados
	$F0003 = 0.0;
	$VaF0003 = 0.0;

	// Cache de tasas por producto
	$prodRates = [];
	$resP = $mysqli->query("SELECT Producto, Fideicomiso, TFideicomiso FROM Productos");
	foreach ($resP as $p) {
	$prodRates[$p['Producto']] = [
		'fide' => ((float)$p['Fideicomiso'])/100.0,     // porcentaje a fideicomiso
		'tf'   => ((float)$p['TFideicomiso'])/100.0     // tasa anual de inversión
	];
	}

	// Recorrido de ventas para valor F/0003 actual
	$sqa  = "SELECT * FROM Venta";
	$r4e9 = $mysqli->query($sqa);
	foreach ($r4e9 as $Resd7){
		$V++;
		if ($Resd7['Status'] == "ACTIVO") {

			$prod = $Resd7['Producto'];
			if (!isset($prodRates[$prod])) { continue; }  // no return

			$deposito = (float)$Resd7['CostoVenta'] * (float)$prodRates[$prod]['fide'];
			$F0003 += $deposito;

			/* días transcurridos: usa strtotime para evitar fallas de formato */
			$tsVenta = strtotime((string)$Resd7['FechaRegistro']);           // soporta 'Y-m-d H:i:s' y 'Y-m-d'
			$tsHoy   = strtotime('today');                                   // medianoche local
			$dias    = $tsVenta ? max(0, intdiv($tsHoy - $tsVenta, 86400)) : 0;

			/* tasa diaria: asegúrate que TFideicomiso venga > 0 */
			$tfAnual = (float)$prodRates[$prod]['tf'];                       // ej. 0.22
			$rateDay = $tfAnual / 365.0;
			$factor  = ($rateDay > 0 && $dias > 0) ? pow(1.0 + $rateDay, $dias) : 1.0;

			$VaF0003 += $deposito * $factor;

			/* DEPURACIÓN OPCIONAL: quita después */
			if ($dias === 0 || $rateDay === 0.0) {
			error_log(sprintf(
				'Fide debug -> Id:%s Prod:%s Costo:%s TF:%0.4f dias:%d tsVenta:%s',
				$Resd7['Id'] ?? '-', $prod, $Resd7['CostoVenta'], $tfAnual, $dias, $Resd7['FechaRegistro']
			));
			}
			//Contamos los clientes Activos Totales
			$TotCtesACT++;

		} elseif ($Resd7['Status'] == "PREVENTA") {
			$ft++; // ventas no concretadas

		} elseif ($Resd7['Status'] == "COBRANZA") {
			$pago    = $financieras->Pago($mysqli,$Resd7['Id']);
			$pagPero += $pago;

			$NP1i = $financieras->PagosPend($mysqli, $Resd7['Id']);
			$p1go = $financieras->Pago($mysqli, $Resd7['Id']);
			if ($NP1i > 0){
				$PaMo  = $p1go * $NP1i;
				$PagEr += $PaMo;
			}

			$CarteCol += $Resd7['CostoVenta'];

			$Sald2re1 = $financieras->SaldoCredito($mysqli, $Resd7['Id']);
			$SaldCre1 += $Sald2re1;

		} elseif ($Resd7['Status'] == "CANCELADO") {
			$IniMs   = strtotime($Fec0);
			$fecRegis = strtotime($basicas->Max1Dat($mysqli,"FechaRegistro","Pagos","Id",$Resd7['Id']));

			if ($fecRegis >= $IniMs){
				$NPOi3   = $financieras->PagosPend($mysqli, $Resd7['Id']);
				$pago3   = $financieras->Pago($mysqli, $Resd7['Id']);
				if ($NPOi3 > 0){
					$PenaMo3   = $pago3 * $NPOi3;
					$PagEnMor3 += $PenaMo3;
				}
			}

			$NPOi = $financieras->PagosPend($mysqli, $Resd7['Id']);
			$pago = $financieras->Pago($mysqli, $Resd7['Id']);
			if ($NPOi > 0){
				$PenaMo   = $pago * $NPOi;
				$PagEnMor += $PenaMo;
			}
		} 
	}

	// Comparaciones de fecha con timestamps
	$Quincena = new DateTimeImmutable($hoy->format('Y-m-15'), $tz);
	$tsHoy    = $hoy->getTimestamp();
	$tsQuince = $Quincena->getTimestamp();
	$tsFec0   = strtotime($Fec0) ?: 0;

	if ($tsHoy < $tsQuince){ $pagPero = $pagPero * 2; }
	if ($tsHoy == $tsQuince || $tsHoy == $tsFec0){ $pagoHoy = $pagPero * 2; }

	// Helper división segura
	function div_safe($num, $den, $on_zero = 0){
		$d = (float)$den;
		return ($d == 0.0) ? $on_zero : ((float)$num / $d);
	}

	// Razones de ventas no concretadas y concretadas
	$V  = (int)$V;
	$ft = (int)$ft;
	$ed = (int)$ed;

	// Si $ed no fue asignado, asume ventas concretadas = V - ft
	if ($ed === 0){ $ed = max(0, $V - $ft); }

	$dcv  = ($V > 0) ? ($ft / $V) : 0; // no concretadas
	$dcv  = $dcv * 10;

	$dc1v = ($V > 0) ? ($ed / $V) : 0; // concretadas
	$dc1v = $dc1v * 10;

	// Cobranza total y comisiones
	$CObTo = $basicas->Sumar($mysqli,"Cantidad","Pagos");
	$Cta   = $basicas->Sumar($mysqli,"Cantidad","Comisiones_pagos");

	// Costo por póliza entregada
	$Plizas = $basicas->Cuenta0Fec($mysqli,"Contacto","FechaRegistro",$FeIniVtas);
	$Cpol   = $Plizas * 37.5;

	// Lags y costos
	$ValREm   = div_safe($dcv * 37.5, $dc1v, 0); // lag ventas no concretadas
	$CostCont = div_safe($dcv * 15,   $dc1v, 0); // costo contrato digital

	// Costo de adquisición
	$CV1ta = $Cta + $Cpol;
	$CacVta  = div_safe($CV1ta, $ed, 0);
	$CacVta += $ValREm + $CostCont;

	// Venta promedio por cliente
	$CicVta  = div_safe($CObTo,  $ed, 0);

	// Calcular la Edad promedio
	
	$EdadCte = div_safe($Ed1Cte, $ed, 0);

	// Moda de edad
	$cuenta    = array_count_values($tuArray);
	arsort($cuenta);
	$ModaClie  = key($cuenta);

	// Datos de fideicomiso generales
	$Ti = $basicas->BuscarCampos($mysqli,"TasaAnual","Productos","Id",1);
	$Ta = $Ti/100;

	// Servicios pagados
	$SerPagados = $basicas->Sumar($mysqli,"Costo","EntregaServicio"); //Valor de los servicios pagados
	$ServOtCte = $basicas->ContarTabla($mysqli, "EntregaServicio"); //Cantidad de servicios Realizados

	// Valor real del fondo menos los servicios pagados
	$VaF0003 = $VaF0003 - (float)$SerPagados;

    // años límite
    $minDate   = $basicas->MinDat($mysqli,'FechaRegistro','Venta');          // p.ej. 2017-01-06
    $maxDateDb = $basicas->MaxDat($mysqli,'FechaRegistro','Venta') ?? '';    // si tienes este helper
    $startYear = (int)date('Y', strtotime($minDate));
    $endYearDb = $maxDateDb ? (int)date('Y', strtotime($maxDateDb)) : (int)date('Y');
    $endYear   = max($startYear, $endYearDb, (int)date('Y')); // fallback a año actual

	// POST para rango
	if (!empty($_POST['ConsulVect'])){
		$NombreGraf_LG = "Ventas Totales Efectivas de ".$_POST['Periodo'];
		$Fech0 = date("Y",strtotime($_POST['Periodo']."-01-01"));
		$Fecha = date("d-m-Y",strtotime('first day of january '.$Fech0));

		$i = 0;
		$sql1 = "SELECT * FROM Productos ";
		$res1 = $mysqli->query($sql1);
		foreach ($res1 as $Reg1){
			$Prod[$i] = $Reg1['Producto'];
			$c = 0;
			while ($c <= 11) {
				$Fe2a   = date("Y-m-d",strtotime($Fecha.'+ '.$c.' month'));
				$Fecha2 = date("Y-m-d",strtotime('last day of this month'.$Fe2a));
				$UVen[] = $basicas->CuentaFechas($mysqli,'Venta','Producto',$Reg1['Producto'],'FechaRegistro',$Fecha2,'FechaRegistro',$Fe2a,'Status','PREVENTA');
				$Año[$c]= date("M",strtotime($Fe2a));
				$c++;
			}
			$i++;
		}

		$ini = "['Base','";
		$in2 = "['";
		$Med = "','";
		$Me2 = ",";
		$Fin = "'],";
		$Fi2 = "],";

		$Nu = count($Prod);
		$Ne = count($Año);
		$Uv = count($UVen);

} else {
    $NombreGraf_LG = "Ventas Totales Efectivas";

    $Fecha = date("Y-m-d", strtotime("first day of january $startYear"));
    $years = $endYear - $startYear;  // span dinámico

    $i = 0;
    $sql1 = "SELECT * FROM Productos";
    $res1 = $mysqli->query($sql1);
    foreach ($res1 as $Reg1){
        $Prod[$i] = $Reg1['Producto'];
        $c = 0;
        while ($c <= $years) {
            // inicio y fin de cada año
            $Fe2a   = date("Y-m-d", strtotime("$Fecha + $c year"));
            $y      = date("Y", strtotime($Fe2a));
            $Fecha2 = date("Y-m-d", strtotime("last day of december $y"));

            $UVen[] = $basicas->CuentaFechas(
                $mysqli,'Venta','Producto',$Reg1['Producto'],
                'FechaRegistro',$Fecha2,'FechaRegistro',$Fe2a,'Status','PREVENTA'
            );
            $Año[$c] = $y;
            $c++;
        }
        $i++;
    }
    $ini = "['Base','"; $in2 = "['"; $Med = "','"; $Me2 = ","; $Fin = "'],"; $Fi2 = "],";
    $Nu = count($Prod); $Ne = count($Año); $Uv = count($UVen);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, user-scalable=no">
	<title>Analisis</title>
	<meta name="theme-color" content="#2F3BA2" />
	<link rel="apple-touch-icon" href="../images/logo.png">
	<link rel="icon" href="../images/logo.png">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="assets/css/styles.min.css">
	<link rel="stylesheet" href="assets/css/Grafica.css">
	<!--Load the AJAX API-->
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<?
	require_once 'php/Archivos_Grafica/Generacion_Grafica_Js.php';
	?>
	<script type="text/javascript">
	function GenGrafica() {
		var data = google.visualization.arrayToDataTable(
			[<?
				echo $ini;
				$e = 0;
				while ($e < $Nu){
					$Nu2 = $Nu-1;
					echo $Prod[$e];
					if($e != $Nu2){ echo $Med; }
					$e++;
				}
				echo $Fin;

				$f = 0;
				while ($f < $Ne){
					echo $in2;
					echo $Año[$f]."'";
					$Bl = $Uv/$Nu;
					$g = 0;
					while ($g < $Nu) {
						$Nu2 = $Nu-1;
						$NiV1 = $g*$Bl;
						if($f == 0){ $NiV = $NiV1; }
						else{ $NiV = $NiV1+$f; }
						if($g == 0){ echo $Me2; }
						echo $UVen[$NiV];
						if($g != $Nu2){ echo $Me2; }
						$g++;
					}
					echo $Fi2;
					$f++;
				}
			?>]
		);

		var options = {
			title: '<?echo $NombreGraf_LG;?>',
			curveType: 'function',
			legend: { position: 'bottom' }
		};

		var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
		chart.draw(data, options);
	}
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(GenGrafica);
	</script>
</head>
<body>
	<section id="Menu">
		<?
		if($_SESSION["dataP"] != "ValidJCCM" || isset($_SESSION["Vendedor"])){
			require_once 'html/Menuprinc.php';
		}
		?>
	</section>

	<div class="principal">
		<div calss="row" style="display:flex;">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<img alt="perfil" class="img-fluid" style="padding-left: 10px;" src="/login/assets/img/logoKasu.png" alt="Carga tu foto de perfil">
			<div style="transform: translate(0, 25px)">
				<p style="transform: scaleY(2);">
					<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Protege a Quien Amas</strong>
				</p>
			</div>
		</div>
		<hr>
	</div>

	<div class="card-body">
		<div class="row">
			<div class="col-lg-4">
				<div class="card">
					<div class="card-header bg-secondary text-light">Generales KASU</div>
					<div class="card-body">
						Ventas Activas :  
						<strong><? echo number_format($vTtOT,2); ?></strong>
						<br>Clientes Totales ACTIVOS :  
						<strong><? echo number_format($TotCtesACT,0); ?></strong> <!-- Pendiente Ingresar el numero de clientes activos totales-->
						<br>Cobros Totales :  
						<strong><? echo number_format($CObTo,2); ?></strong>
						<br>Ventas no Concretadas :  
						<strong><? echo round($dcv); ?> de 10</strong>
						<br>Ventas Concretadas :  
						<strong><? echo round($dc1v); ?> de 10</strong>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="card">
					<div class="card-header bg-secondary text-light">Gastos Mensuales</div>
					<div class="card-body">
						Sueldos a pagar :  
						<strong><? echo number_format($SUeldos,2); ?></strong>
						<br>Clientes en COBRANZA : 
						<strong><? echo number_format($CtesMesCob,0); ?></strong>
						<br>Comisiones pendientes por pagar : 
						<strong><? echo number_format($comisiones,2); ?></strong>
						<br>Costo de adquisicion de el cliente : 
						<strong><? echo number_format($CacVta,2); ?></strong>
						<br>Promedio de Venta por cliente :  
						<strong><? echo number_format($CicVta,2); ?></strong>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="card">
					<div class="card-header bg-secondary text-light">Generales de Prospeccion </div>
					<div class="card-body">
						Prospectos Tierra en el mes :
						<strong><?= $fmt->format($ProsTier) ?></strong>
						<br>Prospectos Digitales en el mes :
						<strong><?= $fmt->format($ProsDgt) ?></strong>
						<br>Registros Generados en el mes :
						<strong><?= $fmt->format($ProsGen) ?></strong>
						<br>Registros Digitales Generados :
						<strong><?= $fmt->format($ProsDig) ?></strong>
						<br>Clientes Generados en el mes :
						<strong><?= $fmt->format($Ventas) ?></strong>
					</div>
				</div>
			</div>
		</div>
		<br>

		<div class="row">
			<div class="col-lg-4">
				<div class="card">
					<div class="card-header bg-secondary text-light">Comportamiento Mensual</div>
					<div class="card-body">
						Cobranza de el dia :  
						<strong><? echo number_format($pagoHoy,2); ?></strong>
						<br>Cobranza del mes : 
						<strong><? echo number_format($pagPero,2); ?></strong>
						<br>Normalidad : 
						<strong><? echo round($AvCob); ?> %</strong>
						<br>Mora generada en el Mes:  
						<strong><? echo number_format($PagEnMor3,2); ?></strong>
						<br>Ventas del Mes : 
						<strong><? echo number_format($VtaDine,2); ?></strong>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="card">
					<div class="card-header bg-secondary text-light">Datos Crediticios</div>
					<div class="card-body">
						Valor Cartera  : 
						<strong><? echo number_format($SaldCre1,2); ?></strong>
						<br>Capital colocado : 
						<strong><? echo number_format($CarteCol,2); ?></strong>
						<br>Cartera en Cobranza : 
						<strong><? echo number_format($PagEr,2); ?></strong>
						<br>Cartera en Mora : 
						<strong><? echo number_format(0); ?></strong>
						<br>Cartera dictaminada : 
						<strong><? echo number_format($PagEnMor,2); ?></strong>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="card">
					<div class="card-header bg-secondary text-light">Datos Fideicomiso</div>
					<div class="card-body">
						Valor del fideicomiso : 
						<strong><? echo number_format($F0003,2); ?></strong>
						<br>Valor Actual F/0003: 
						<strong><? echo number_format($VaF0003,2); ?></strong>
						<br>Servicios Pagados : 
						<strong><? echo number_format($SerPagados,2); ?></strong>
						<br>Servicios Otorgados : 
						<strong><? echo round($ServOtCte); ?></strong>
						<br>Edad promedio Cliente : 
						<strong><? echo round($EdadCte); ?></strong>
					</div>
				</div>
			</div>
		</div>
		<br>
	</div>

	<div class="col-lg-12">
		<div class="center-heading">
			<p>Analisis de Ventas en el historico de el tiempo desde <strong><? echo $FeIniVtas;?></strong></p>
		</div>
		<div class="row">
			<?
			$a = 1;
			while ($a <= 3) {
				echo '
				<div class="col-lg-4">
					<div id="Grafica_'.$a.'"></div>
				</div>
				';
				$a++;
			}
			?>
		</div>
		<hr>
		<div class="center-heading">
			<p>Analisis de Ventas en el año en curso <strong><? echo date('Y');?></strong></p>
		</div>
		<div class="row">
			<?
			while ($a <= 6) {
				echo '
				<div class="col-lg-4">
					<div id="Grafica_'.$a.'"></div>
				</div>
				';
				$a++;
			}
			?>
		</div>
		<div class="row">
		<div class="col-lg-12">
			<hr>
			<div class="center-heading">
			<form method="POST" action="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>">
				<select name="Periodo" class="form-control">
				<option value="0" selected>selecciona un rango para analizar</option>
				<?php for($y=$startYear; $y<=$endYear; $y++): ?>
				<option value="<?=$y?>">Año <?=$y?></option>
				<?php endfor; ?>
				</select>
				<br>
				<input type="submit" name="ConsulVect" class="btn btn-primary btn-block" value="Consultar periodo">
			</form>
			</div>
			<br>
			<!-- NO metas .col dentro de .col; usa un div normal -->
			<div class="Grafica">
				<div id="curve_chart"></div>
			</div>
		</div>
		</div>

	</div>

	<br><br><br>
	<script src="https://www.gstatic.com/charts/loader.js"></script>
	<!--script defer src="Javascript/grafica.js"></script-->
	<script defer src="Javascript/finger.js"></script>
	<script defer src="Javascript/localize.js"></script>
</body>
</html>