<?
	session_start();

	// Incluir funciones y conexiones
	require_once '../eia/librerias.php';

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
		$Niv = $basicas -> BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
	}

	// Incluir lógica de análisis
	require_once 'php/Analisis_Metas.php';

	// Declarar todas las variables necesarias
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

	// Crear un formateador para valores monetarios y numéricos en México
	$fmtMoney = new NumberFormatter('es_MX', NumberFormatter::CURRENCY);

	// Obtener fecha inicial de ventas
	$FeIniVtas = date("d-M-Y", strtotime($basicas->MinDat($mysqli, "FechaRegistro", "Venta")));
	//$sqal = "alter table Productos add (PlazoPagos varchar (20)  null)";
	//$sqal = "SHOW TABLES FROM u176240951_web";
	$sqal = "SELECT * FROM Productos";
	//Realiza consulta
	$r4e9s = $mysqli->query($sqal);
	//Si existe el registro se asocia en un fetch_assoc
	foreach ($r4e9s as $Resd6){
	  //print_r($Resd6);
	  //$basicas->ActTab($mysqli,'Productos','PlazoPagos',15,'Id',$Resd6['Id']);
	}
	//COntamos los prospectos
	$ProsGen = $basicas->Cuenta0Fec($mysqli,"Contacto","FechaRegistro",$Fec0);
	//Prospecto digitales
	$ProsDig = $basicas->Cuenta1Fec($mysqli,"Contacto","Usuario","PLATAFORMA","FechaRegistro",$Fec0);
	//Obetenemos el total
	$ProsGen = $ProsGen-$ProsDig;
	//Prospectos generaros en la base de datos de ventas
	$ProsDgt = $basicas->Cuenta0Fec($pros,"prospectos","Alta",$Fec0);
	//Prspectos digitales
	$ProsTier = $basicas->Cuenta1Fec($pros,"prospectos","Origen","vta","Alta",$Fec0);
	//Obetenemos el total
	$ProsDgt = $ProsDgt-$ProsTier;
	//Contamos las ventas de el mes
	$Ventas = $basicas->Cuenta0Fec($mysqli,"Venta","FechaRegistro",$Fec0);
	//Sumamos el valor de las ventas del mes
	$VtaDine = $basicas->Sumar0Fecha($mysqli,"CostoVenta","Venta","FechaRegistro",$Fec0);
	//Valor del fideicomiso
	$sqal = "SELECT * FROM Productos";
	//Realiza consulta
	$r4e9s = $mysqli->query($sqal);
	//Si existe el registro se asocia en un fetch_assoc
	foreach ($r4e9s as $Resd6){
	  //COsto de venta de cada producto
	  $vTAStOT = $basicas->Sumar2cond($mysqli,"CostoVenta","Venta","Status","ACTIVO","Producto",$Resd6['Producto']);
	  //Costo de venta
	  $vTtOT = $vTtOT+$vTAStOT;
	  //Valor Actual del fideicomiso
	  $TFid = $Resd6['Fideicomiso']/100;
	  $Fide0 = $vTAStOT*$TFid;
	  //Sumamos el valor fideicomitido
	  $F0003 = $F0003+$Fide0;
	}
	//Valor actual de el fideicomiso
	$sqa = "SELECT * FROM Venta ";
	//Realiza consulta
	$r4e9 = $mysqli->query($sqa);
	//Si existe el registro se asocia en un fetch_assoc
	foreach ($r4e9 as $Resd7){
	  $V++;
	  if($Resd7['Status'] == "ACTIVO"){

		// Obtener y limpiar tasa de fideicomiso
		$TiFideRaw = $basicas->BuscarCampos($mysqli, "TFideicomiso", "Productos", "Producto", $Resd7['Producto']);
		$TiFideClean = str_replace([',', '%', '$', 'MXN', ' '], '', $TiFideRaw);

		// Validar y convertir a número
		$TiFide = is_numeric($TiFideClean) ? floatval($TiFideClean) / 100 : 0.0;

		// Calcular tasa diaria
		$TiD = $TiFide / 365;
		$Bta = $TiD + 1;

		// Fecha inicial
		$fecRegis = strtotime($Resd7['FechaRegistro']);
		$FeUnix = $hoy - $fecRegis;
		$Dias = $FeUnix / 86400;

		// Calcular tasa compuesta
		$TasaF = pow($Bta, $Dias);

		// Obtener y limpiar porcentaje de fideicomiso
		$FideAhoRaw = $basicas->BuscarCampos($mysqli, "Fideicomiso", "Productos", "Producto", $Resd7['Producto']);
		$FideAhoClean = str_replace([',', '%', '$', 'MXN', ' '], '', $FideAhoRaw);
		$FideAho = is_numeric($FideAhoClean) ? floatval($FideAhoClean) / 100 : 0.0;

		// Calcular valor actual
		$ValFPr = floatval($Resd7['CostoVenta']) * $FideAho;
		$VFPVta = $ValFPr * $TasaF;

		// Acumular valor
		$VaF0003 += $VFPVta;


	}elseif($Resd7['Status'] == "PREVENTA"){
	  //Ventas no concretadas
	  $ft++;
	}elseif($Resd7['Status'] == "COBRANZA"){
	    //Ventas no concretadas
	    $pago = $financieras->Pago($mysqli,$Resd7['Id']);
	    //Suma de los pagos
	    $pagPero = $pagPero+$pago;
	    //buscamos el id de las polizas
	    $NP1i = $financieras->PagosPend($mysqli, $Resd7['Id']);
	    //Pago a dar
	    $p1go = $financieras->Pago($mysqli, $Resd7['Id']);
	    //SUmamos los pagos
	    if($NP1i > 0){
	      $PaMo = $p1go*$NP1i;
	      //Pagos pendients
	      $PagEr = $PagEr+$PaMo;
	    }
	    //Sumamos los valores de la venta
	    $CarteCol = $CarteCol+$Resd7['CostoVenta'];
	    //Creamos los pagos para la colocacion
	    $Sald2re1 = $financieras->SaldoCredito($mysqli, $Resd7['Id']);
	    //Calculos de cartera
	    $SaldCre1 = $SaldCre1+$Sald2re1;
	
	  }elseif($Resd7['Status'] == "CANCELADO"){
	    //Sacamos el ultimo pago de el cliente
	    $IniMs = strtotime($Fec0);
	    //Buscamos el ultimo pago de el cliente
	    $fecRegis = strtotime($basicas->Max1Dat($mysqli,"FechaRegistro","Pagos","Id",$Resd7['Id']));
	    //Comparamos la venta
	    if($fecRegis >= $IniMs){
	      //buscamos el id de las polizas
	      $NPOi3 = $financieras->PagosPend($mysqli, $Resd7['Id']);
	      //Pago a dar
	      $pago3 = $financieras->Pago($mysqli, $Resd7['Id']);
	      //SUmamos los pagos
	      if($NPOi3 > 0){
	          $PenaMo3 = $pago3*$NPOi3;
	          //Pagos pendients
	          $PagEnMor3 = $PagEnMor3+$PenaMo3;
	      }
	    }
	      //buscamos el id de las polizas
	      $NPOi = $financieras->PagosPend($mysqli, $Resd7['Id']);
	      //Pago a dar
	      $pago = $financieras->Pago($mysqli, $Resd7['Id']);
	      //SUmamos los pagos
	      if($NPOi > 0){
	        $PenaMo = $pago*$NPOi;
	        //Pagos pendients
	        $PagEnMor = $PagEnMor+$PenaMo;
	      }
	  }
	}
	//Regostro de la cobranza pendiente
	if($hoy < $Quincena){$pagPero = $pagPero*2;}
	if($hoy == $Quincena || $hoy == $Fec0){$pagoHoy = $pagPero*2;}
	//Contamos
	$dcv = $ft/$V;
	//Subimos a Relacion de 10
	$dcv = $dcv*10;
	//Contamos
	$dc1v = $ed/$V;
	//Subimos a Relacion de 10
	$dc1v = $dc1v*10;
	//Obtenemos la cobranza total
	$CObTo = $basicas->Sumar($mysqli,"Cantidad","Pagos");
	//SUmamos comisiones
	$Cta = $basicas->Sumar($mysqli,"Cantidad","Comisiones_pagos");
	//SUmamos el costo po poliza entregada
	$Plizas = $basicas->Cuenta0Fec($mysqli,"Contacto","FechaRegistro",$FeIniVtas);
	$Cpol = $Plizas*37.5;
	//lag de las ventas no Concretadas
	$ValREm = $dcv*37.5;
	$ValREm = $ValREm/$dc1v;
	//Costo de contrato digital
	$CostCont = $dcv*15;
	$CostCont = $CostCont/$dc1v;
	//sumamos los costos de la poliza
	$CV1ta = $Cta+$Cpol;
	//Calculamos el costo de adquisicion
	$CacVta = $CV1ta/$ed;
	//Calculos de los valores extras
	$CacVta = $CacVta+$ValREm+$CostCont;
	//Calculo de venta promedio
	$CicVta = $CObTo/$ed;
	//Sacamos la edad promedio
	$EdadCte = $Ed1Cte/$ed;
	//Sacamos la moda de edad
	$cuenta = array_count_values($tuArray);
	arsort($cuenta);
	$ModaClie = key($cuenta);
	//Valor Actual del fideicomiso
	$Ti = $basicas->BuscarCampos($mysqli,"TasaAnual","Productos","Id",1);
	$Ta = $Ti/100;
	//Generamos el valor de los servicios Pagados
	$SerPagados = $basicas->Sumar($mysqli,"Costo","EntregaServicio");
	//Post de busqueda
	if(!empty($_POST['ConsulVect'])){
	  //Nombre de la grafica
	  $NombreGraf = "Ventas Totales Efectivas de ".$_POST['Periodo'];
	  //Fecha inicial
	  $Fech0 = date("Y",strtotime($_POST['Periodo']."-01-01"));
	  $Fecha = date("d-m-Y",strtotime('first day of january '.$Fech0));
	  //Ultimo dia permanente
	    $i = 0;
	    //Realizamos la busqueda de los status de la venta
	    $sql1 = "SELECT * FROM Productos ";
	    //Realiza consulta
	    $res1 = $mysqli->query($sql1);
	    //Si existe el registro se asocia en un fetch_assoc
	    foreach ($res1 as $Reg1){
	          //Array de los productos
	          $Prod[$i] = $Reg1['Producto'];
	          //Buscamos los años que se ha vendido
	          $c = 0;
	          while ($c <= 11) {
	            //primer dia
	            $Fe2a = date("Y-m-d",strtotime($Fecha.'+ '.$c.' month'));
	            //Ultimo dia de el año
	            $Fecha2 = date("Y-m-d",strtotime('last day of this month'.$Fe2a));
	            //Buscamos los productos por el año
	            $UVen[] = $unidades_vendidas = $basicas->CuentaFechas($mysqli,'Venta','Producto',$Reg1['Producto'],'FechaRegistro',$Fecha2,'FechaRegistro',$Fe2a,'Status','PREVENTA');
	            //Año de venta y creacion de array
	            $Año[$c] = $aNOi = date("M",strtotime($Fe2a));
	          $c++;
	        }
	        $i++;
	    }
	    //Variables para inicial las cadenas de texto
	    $ini = "['Base','";
	    $in2 = "['";
	    $Med = "','";
	    $Me2 = ",";
	    $Fin = "'],";
	    $Fi2 = "],";
	    //COntador de los array
	    $Nu = count($Prod);
	    $Ne = count($Año);
	    $Uv = count($UVen);
	}else{
	//Nombre de la grafica
	$NombreGraf = "Ventas Totales Efectivas";
	//Fecha inicial
	$Fech0 = $basicas->MinDat($mysqli,'FechaRegistro','Venta');
	$Fecha = date("d-m-Y",strtotime('first day of january '.date("Y",strtotime($Fech0))));
	//Ultimo dia permanente
	  $i = 0;
	  //Realizamos la busqueda de los status de la venta
	  $sql1 = "SELECT * FROM Productos ";
	  //Realiza consulta
	  $res1 = $mysqli->query($sql1);
	  //Si existe el registro se asocia en un fetch_assoc
	  foreach ($res1 as $Reg1){
	        //Array de los productos
	        $Prod[$i] = $Reg1['Producto'];
	        //Buscamos los años que se ha vendido
	        $c = 0;
	        while ($c <= 4) {
	          //primer dia
	          $Fe2a = date("Y-m-d",strtotime($Fecha.'+ '.$c.' Year'));
	          //Ultimo dia de el año
	          $Fecha2 = date("Y-m-d",strtotime('last day of December'.date("Y",strtotime($Fe2a))));
	          //Buscamos los productos por el año
	          $UVen[] = $unidades_vendidas = $basicas->CuentaFechas($mysqli,'Venta','Producto',$Reg1['Producto'],'FechaRegistro',$Fecha2,'FechaRegistro',$Fe2a,'Status','PREVENTA');
	          //Año de venta y creacion de array
	          $Año[$c] = $aNOi = date("Y",strtotime($Fe2a));
	        $c++;
	      }
	      $i++;
	  }
	  //Variables para inicial las cadenas de texto
	  $ini = "['Base','";
	  $in2 = "['";
	  $Med = "','";
	  $Me2 = ",";
	  $Fin = "'],";
	  $Fi2 = "],";
	  //COntador de los array
	  $Nu = count($Prod);
	  $Ne = count($Año);
	  $Uv = count($UVen);
	}
	?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no">
   <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no"> -->
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
    <!-- Scripts de generacion de graficas -->
    <?
    require_once 'php/Archivos_Grafica/Generacion_Grafica_Js.php';
    //require_once '/GraficasFechas/a_Ventas_anuales_efectivas.';
    ?>
    <script type="text/javascript">
        function GenGrafica() {
          var data = google.visualization.arrayToDataTable(
              [<?
              //Imprimimos la primera parte de la tabla
              echo $ini;//Imprimimos el inicio de la tabla
              $e = 0;//Valor inicial de el while
              while ($e < $Nu){//while que recorre el array
                  $Nu2 = $Nu-1;//Bajamos el valor para marcar cuando se imprime en el medio
                  echo $Prod[$e];//Imprimimos cada valor de la busqueda
                  if($e != $Nu2){//validamos en donde esta de la tabla
                        echo $Med;//imprimimos el medio de la grafica
                  }
                  $e++;
              }
              echo $Fin;//Final de la fila de la tabla
              //Imprimimos la segunda parte de la tabla
              $f = 0;//Valor inicial de el while
              while ($f < $Ne){
                echo $in2;//Imprimimos el inicio de la tabla
                echo $Año[$f]."'";//imprimimos el año o mes segun la busqueda
                $Bl = $Uv/$Nu;//Dividimos el valor para crear bloques homogeneos
                $g = 0;
                while ($g < $Nu) {
                  $Nu2 = $Nu-1;
                  //lanzamos el numero donde se inicia
                  $NiV1 = $g*$Bl;
                  //EN el primer recorrido ingresamos la multiplicacion de la base
                  if($f == 0){$NiV = $NiV1;}
                  //En el segundo nivel sumamos al multiplicador las vueltas q lleva
                  else{$NiV = $NiV1+$f;}
                  //$NiV = $B2l*$g;//Calculamos el numero que debe imprimir segun las columnas y filas
                  if($g == 0){echo $Me2;}//si inicia el bloque imprime
                  echo $UVen[$NiV];//imprimios el valor de la busqueda
                  if($g != $Nu2){echo $Me2;}//si no inicia el bloque imprime
                  $g++;
                }
                echo $Fi2;//Final de la fila de la tabla
                $f++;
              }
              ?>]
          );

          var options = {
              title: '<?echo $NombreGraf;?>',
              curveType: 'function',
              width: 1250,
              height: 400,
              legend: { position: 'bottom' }
          };

          //var data = new google.visualization.DataTable(data);
          var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
          chart.draw(data, options);
      }
      // load the visualization api
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(GenGrafica);
    </script>
</head>
<body>
  <!--onload="localize();"-->
  <!--Inicio de menu principal fijo-->
  <section id="Menu">
    <?
    //Validamos si el usuario viene de una presentacion de inversionistas
    if($_SESSION["dataP"] != "ValidJCCM" || isset($_SESSION["Vendedor"])){
      require_once 'html/Menuprinc.php';
    }
    ?>
  </section>
  <!-- menu con los datos de usuario -->
  <div class="principal">
      <!--Logo de KASU-->
      <div calss="row" style="display:flex;">
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <img alt="perfil" class="img-fluid" style="padding-left: 10px;"src="/login/assets/img/logoKasu.png" alt="Carga tu foto de perfil">
          <div style="transform: translate(0, 25px)">
              <p style="transform: scaleY(2);">
                <strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Protege a Quien Amas</strong>
              </p>
          </div>
      </div>
      <hr>
  </div>
  <!--Final de menu principal fijo-->
  <div class="card-body">
      <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header bg-secondary text-light">Generales KASU</div>
                <div class="card-body">
                  Ventas Activas :  <strong><? echo number_format($vTtOT,2); ?></strong>
                  <br>Cobros Totales :  <strong><? echo number_format($CObTo,2); ?></strong>
                  <br>Ventas no Concretadas :  <strong><? echo round($dcv); ?> de 10</strong>
                  <br>Ventas Concretadas :  <strong><? echo round($dc1v); ?> de 10</strong>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-header bg-secondary text-light">Gastos Mensuales</div>
                <div class="card-body">
                  Sueldos a pagar :  <strong><? echo number_format($SUeldos,2); ?></strong>
                  <br>Comisiones pendientes por pagar : <strong><? echo number_format($comisiones,2); ?></strong>
                  <br>Costo de adquisicion de el cliente : <strong><? echo number_format($CacVta,2); ?></strong>
                  <br>Promedio de Venta por cliente :  <strong><? echo number_format($CicVta,2); ?></strong>
                </div>
            </div>
        </div>
        <div class="col">
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
          <div class="col">
              <div class="card">
                  <div class="card-header bg-secondary text-light">Comportamiento Mensual</div>
                  <div class="card-body">
                       Cobranza de el dia :  <strong><? echo number_format($pagoHoy,2); ?></strong>
                       <br>Cobranza del mes : <strong><? echo number_format($pagPero,2); ?></strong>
                       <br>Normalidad : <strong><? echo round($AvCob); ?> %</strong>
                       <br>Mora generada en el Mes:  <strong><? echo number_format($PagEnMor3,2); ?></strong>
                       <br>Ventas del Mes : <strong><? echo number_format($VtaDine,2); ?></strong>

                  </div>
              </div>
          </div>
          <div class="col">
              <div class="card">
                  <div class="card-header bg-secondary text-light">Datos Crediticios</div>
                  <div class="card-body">
                       Valor Cartera  : <strong><? echo number_format($SaldCre1,2); ?></strong>
                       <br>Capital colocado : <strong><? echo number_format($CarteCol,2); ?></strong>
                       <br>Cartera en Cobranza : <strong><? echo number_format($PagEr,2); ?></strong>
                       <br>Cartera en Mora : <strong><? echo number_format(0); ?></strong>
                       <br>Cartera dictaminada : <strong><? echo number_format($PagEnMor,2); ?></strong>
                  </div>
              </div>
          </div>
          <div class="col">
              <div class="card">
                  <div class="card-header bg-secondary text-light">Datos Fideicomiso</div>
                  <div class="card-body">
                       Valor del fideicomiso : <strong><? echo number_format($F0003,2); ?></strong>
                       <br>Valor Actual F/0003: <strong><? echo number_format($VaF0003,2); ?></strong>
                       <br>Servicios Pagados : <strong><? echo number_format($SerPagados,2); ?></strong>
                       <br>Edad promedio Cliente : <strong><? echo round($EdadCte); ?></strong>
                       <br>Moda Edad : <strong><? echo round($ModaClie); ?></strong>
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
      </div>
      <hr>
      <div class="col-lg-12">
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
      </div>
      <hr>
      <div class="row">
        <div class="col-lg-12">
          <hr>
          <div class="center-heading">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
              <select name='Periodo' class="form-control">
                <option value='0' selected>selecciona un rango para analizar</option>
                <option value='2017'>Año 2017</option>
                <option value='2018'>Año 2018</option>
                <option value='2019'>Año 2019</option>
                <option value='2020'>Año 2020</option>
                <option value='2021'>Año 2021</option>
                <option value='2022'>Año 2022</option>
              </select>
              <br>
              <input type="submit" name="ConsulVect" class="form-control" value="Consultar periodo">
            </form>
          </div>
          <hr>
          <div class="col-lg-12">
            <div id="curve_chart" style="max-width: 100%;"></div>
          </div>
        </div>
      </div>
    <br><br><br>
    <script defer type="text/javascript" src="Javascript/finger.js" defer async></script>
    <script defer type="text/javascript" src="Javascript/localize.js"></script>
</body>
</html>
