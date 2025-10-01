<?php
session_start();

// ===== includes =====
require_once '../eia/librerias.php';
require_once 'php/Analisis_Metas.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
if (!isset($mysqli) || !($mysqli instanceof mysqli)) { die('DB no inicializada'); }
if (!$mysqli->ping()) { die('DB desconectada: ' . $mysqli->connect_error); }

// ===== zona horaria / fecha =====
$tz  = new DateTimeZone('America/Mexico_City');
$hoy = new DateTimeImmutable('today', $tz);

// ===== sesión =====
if (!isset($_SESSION["Vendedor"])) {
  if (isset($_GET['dataP']) && base64_decode($_GET['dataP']) === "ValidJCCM") {
    $_SESSION["dataP"] = "ValidJCCM";
  } else {
    header('Location: https://kasu.com.mx/login');
    exit;
  }
} else {
  $Niv = $basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
}

// ===== helpers numéricos =====
$fmt = new NumberFormatter('es_MX', NumberFormatter::DECIMAL);
$fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);
$fmtMoney = new NumberFormatter('es_MX', NumberFormatter::CURRENCY);
function div_safe($num,$den,$on_zero=0){ $d=(float)$den; return ($d==0.0)?$on_zero:((float)$num/$d); }

// ===== variables =====
$_SESSION["dataP"]= $_SESSION["dataP"] ?? 0;
$Fec0 = date("Y-m-01");
$vTtOT=$F0003=$VaF0003=$CarteCol=$SaldCre1=$PagEr=$PagEnMor3=$PagEnMor=0;
$ft=$V=$ed=$Ed1Cte=$pagoHoy=$pagPero=$AvCob=$SUeldos=$comisiones=$CacVta=$CicVta=0;
$EdadCte=$ModaClie=$SerPagados=$TotCtesACT=$CtesMesCob=$ServOtCte=$EdPrmcte=0;
$Prod=$Año=$UVen=[]; $NombreGraf=''; $ini=$in2=$Med=$Me2=$Fin=$Fi2=''; $Nu=$Ne=$Uv=0;
$VtasCacelTot=0;

// ===== fechas/ventas base =====
$FeIniVtas = date("d-M-Y", strtotime($basicas->MinDat($mysqli, "FechaRegistro", "Venta")));

// ===== prospectos mes =====
$ProsGen = $basicas->Cuenta0Fec($mysqli,"Contacto","FechaRegistro",$Fec0);
$ProsDig = $basicas->Cuenta1Fec($mysqli,"Contacto","Usuario","PLATAFORMA","FechaRegistro",$Fec0);
$ProsGen = $ProsGen - $ProsDig;

$ProsDgt  = $basicas->Cuenta0Fec($pros,"prospectos","Alta",$Fec0);
$ProsTier = $basicas->Cuenta1Fec($pros,"prospectos","Origen","vta","Alta",$Fec0);
$ProsDgt  = $ProsDgt - $ProsTier;

// ===== ventas del mes =====
$Ventas  = $basicas->Cuenta0Fec($mysqli,"Venta","FechaRegistro",$Fec0);
$VtaDine = $basicas->Sumar0Fecha($mysqli,"CostoVenta","Venta","FechaRegistro",$Fec0);

// ===== tasas por producto =====
$prodRates=[];
$resP=$mysqli->query("SELECT Producto, Fideicomiso, TFideicomiso FROM Productos");
foreach($resP as $p){
  $prodRates[$p['Producto']] = [
    'fide'=> ((float)$p['Fideicomiso'])/100.0,
    'tf'  => ((float)$p['TFideicomiso'])/100.0
  ];
}

// ===== loop ventas para fideicomiso / métricas =====
$r4e9 = $mysqli->query("SELECT * FROM Venta");
foreach($r4e9 as $Resd7){
  $V++;
  if ($Resd7['Status']=="ACTIVO"){
    $prod=$Resd7['Producto'];
    if (!isset($prodRates[$prod])) { continue; }
    $pagado = (float)$Resd7['CostoVenta'];      $vTtOT += $pagado;
    $deposito = $pagado * $prodRates[$prod]['fide'];  $F0003 += $deposito;

    $tsVenta = strtotime((string)$Resd7['FechaRegistro']);
    $tsHoy   = strtotime('today');
    $dias    = $tsVenta ? max(0, intdiv($tsHoy-$tsVenta,86400)) : 0;
    $rateDay = $prodRates[$prod]['tf']/365.0;
    $factor  = ($rateDay>0 && $dias>0) ? pow(1+$rateDay,$dias) : 1.0;
    $VaF0003 += $deposito * $factor;

    // edad promedio
    $TotCtesACT++;
    $ClaveCurp = $basicas->BuscarCampos($mysqli,"ClaveCurp","Usuario",'IdContact',$Resd7['IdContact']);
    $edad = $basicas->ObtenerEdad($ClaveCurp);
    if (is_numeric($edad)) { $EdPrmcte += (int)$edad; }
    $EdadCte = div_safe($EdPrmcte,$TotCtesACT,0);

  } elseif ($Resd7['Status']=="PREVENTA"){
    $ft++;

  } elseif ($Resd7['Status']=="COBRANZA"){
    $pago    = $financieras->Pago($mysqli,$Resd7['Id']);    $pagPero += $pago;
    $NP1i    = $financieras->PagosPend($mysqli,$Resd7['Id']);
    $p1go    = $financieras->Pago($mysqli,$Resd7['Id']);
    if ($NP1i>0){ $PagEr += $p1go*$NP1i; }
    $CarteCol += $Resd7['CostoVenta'];
    $SaldCre1 += $financieras->SaldoCredito($mysqli,$Resd7['Id']);

  } elseif ($Resd7['Status']=="CANCELADO"){
    $IniMs    = strtotime($Fec0);
    $fecRegis = strtotime($basicas->Max1Dat($mysqli,"FechaRegistro","Pagos","Id",$Resd7['Id']));
    $VtasCacelTot += (float)$Resd7['CostoVenta'];
    if ($fecRegis >= $IniMs){
      $NPOi3 = $financieras->PagosPend($mysqli,$Resd7['Id']);
      $pago3 = $financieras->Pago($mysqli,$Resd7['Id']);
      if ($NPOi3>0){ $PagEnMor3 += $pago3*$NPOi3; }
    }
    $NPOi = $financieras->PagosPend($mysqli,$Resd7['Id']);
    $pago = $financieras->Pago($mysqli,$Resd7['Id']);
    if ($NPOi>0){ $PagEnMor += $pago*$NPOi; }
  }
}

// ===== ajustes por quincena =====
$Quincena = new DateTimeImmutable($hoy->format('Y-m-15'), $tz);
$tsHoy    = $hoy->getTimestamp();
$tsQuince = $Quincena->getTimestamp();
$tsFec0   = strtotime($Fec0) ?: 0;
if ($tsHoy < $tsQuince){ $pagPero = $pagPero * 2; }
if ($tsHoy == $tsQuince || $tsHoy == $tsFec0){ $pagoHoy = $pagPero * 2; }

// ===== razones de ventas =====
$V=(int)$V; $ft=(int)$ft; $ed=(int)$ed;
if ($ed===0){ $ed = max(0,$V-$ft); }
$dcv  = ($V>0)? ($ft/$V)*10 : 0;
$dc1v = ($V>0)? ($ed/$V)*10 : 0;

// ===== cobranza/comisiones/costos =====
$CObTo = $basicas->Sumar($mysqli,"Cantidad","Pagos");
$Cta   = $basicas->Sumar($mysqli,"Cantidad","Comisiones_pagos");
$Plizas= $basicas->Cuenta0Fec($mysqli,"Contacto","FechaRegistro",$FeIniVtas);
$Cpol  = $Plizas * 37.5;
$ValREm   = div_safe($dcv*37.5,$dc1v,0);
$CostCont = div_safe($dcv*15,$dc1v,0);
$CV1ta = $Cta + $Cpol;
$CacVta  = div_safe($CV1ta,$ed,0) + $ValREm + $CostCont;
$CicVta  = div_safe($CObTo,$ed,0);

// ===== servicios / fideicomiso neto =====
$SerPagados = $basicas->Sumar($mysqli,"Costo","EntregaServicio");
$ServOtCte  = $basicas->ContarTabla($mysqli,"EntregaServicio");
$VaF0003    = $VaF0003 - (float)$SerPagados;

// ===== rango años para gráficas =====
$minDate   = $basicas->MinDat($mysqli,'FechaRegistro','Venta');
$maxDateDb = $basicas->MaxDat($mysqli,'FechaRegistro','Venta') ?? '';
$startYear = (int)date('Y', strtotime($minDate));
$endYearDb = $maxDateDb ? (int)date('Y', strtotime($maxDateDb)) : (int)date('Y');
$endYear   = max($startYear,$endYearDb,(int)date('Y'));

// ===== datos para curva (anual o periodo POST) =====
if (!empty($_POST['ConsulVect'])){
  $NombreGraf_LG = "Ventas Totales Efectivas de ".$_POST['Periodo'];
  $Fech0 = date("Y",strtotime($_POST['Periodo']."-01-01"));
  $Fecha = date("Y-m-d",strtotime('first day of january '.$Fech0));

  $i=0; $res1=$mysqli->query("SELECT * FROM Productos");
  foreach($res1 as $Reg1){
    $Prod[$i]=$Reg1['Producto'];
    for($c=0;$c<=11;$c++){
      $Fe2a   = date("Y-m-d",strtotime("$Fecha + $c month"));
      $y      = date("M",strtotime($Fe2a));
      $Fecha2 = date("Y-m-d",strtotime("last day of this month $Fe2a"));
      $UVen[] = $basicas->CuentaFechas($mysqli,'Venta','Producto',$Reg1['Producto'],'FechaRegistro',$Fecha2,'FechaRegistro',$Fe2a,'Status','PREVENTA');
      $Año[$c]= $y;
    }
    $i++;
  }
  $ini="['Base','"; $in2="['"; $Med="','"; $Me2=","; $Fin="'],"; $Fi2="],";
  $Nu=count($Prod); $Ne=count($Año); $Uv=count($UVen);

} else {
  $NombreGraf_LG = "Ventas Totales Efectivas";
  $Fecha = date("Y-m-d", strtotime("first day of january $startYear"));
  $years = $endYear - $startYear;

  $i=0; $res1=$mysqli->query("SELECT * FROM Productos");
  foreach($res1 as $Reg1){
    $Prod[$i]=$Reg1['Producto'];
    for($c=0;$c<=$years;$c++){
      $Fe2a   = date("Y-m-d", strtotime("$Fecha + $c year"));
      $y      = date("Y", strtotime($Fe2a));
      $Fecha2 = date("Y-m-d", strtotime("last day of december $y"));
      $UVen[] = $basicas->CuentaFechas($mysqli,'Venta','Producto',$Reg1['Producto'],'FechaRegistro',$Fecha2,'FechaRegistro',$Fe2a,'Status','PREVENTA');
      $Año[$c]= $y;
    }
    $i++;
  }
  $ini="['Base','"; $in2="['"; $Med="','"; $Me2=","; $Fin="'],"; $Fi2="],";
  $Nu=count($Prod); $Ne=count($Año); $Uv=count($UVen);
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F2F2F2">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Análisis Ventas</title>

  <!-- PWA / iOS -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?php echo $VerCache; ?>">
  <link rel="stylesheet" href="assets/css/Grafica.css">

  <!-- JS base -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>

  <!-- Charts -->
  <script src="https://www.gstatic.com/charts/loader.js"></script>
  <?php require_once 'php/Archivos_Grafica/Generacion_Grafica_Js.php'; ?>
  <script>
    function GenGraficaCurva(){
      var data = google.visualization.arrayToDataTable(
        [<?php
          echo $ini;
          for($e=0;$e<$Nu;$e++){
            echo $Prod[$e];
            if($e!=$Nu-1) echo $Med;
          }
          echo $Fin;

          for($f=0;$f<$Ne;$f++){
            echo $in2.$Año[$f]."'";
            $Bl = $Uv/$Nu;
            for($g=0;$g<$Nu;$g++){
              $NiV = ($g*$Bl)+$f;
              echo $Me2.$UVen[$NiV];
            }
            echo $Fi2;
          }
        ?>]
      );
      var options={ title:'<?php echo $NombreGraf_LG; ?>', curveType:'function', legend:{position:'bottom'} };
      var chart=new google.visualization.LineChart(document.getElementById('curve_chart'));
      chart.draw(data,options);
    }
    google.charts.load('current',{'packages':['corechart']});
    google.charts.setOnLoadCallback(GenGraficaCurva);
  </script>
</head>
<body>

  <!-- TOP BAR fija -->
  <div class="topbar">
    <h4 class="title">Análisis de Ventas</h4>
  </div>

  <!-- Menú inferior -->
  <section id="Menu">
    <?php if($_SESSION["dataP"]!="ValidJCCM" || isset($_SESSION["Vendedor"])) { require_once 'html/Menuprinc.php'; } ?>
  </section>

  <!-- Contenido entre barras -->
  <main class="page-content">
    <div class="container">
      <div class="row">
        <!-- Generales KASU -->
        <div class="col-lg-4">
          <div class="card">
            <div class="card-header bg-secondary text-light">Generales KASU</div>
            <div class="card-body">
              Cobros Totales: <strong><?php echo number_format($CObTo,2); ?></strong><br>
              Ventas Totales ACTIVAS: <strong><?php echo number_format($vTtOT,2); ?></strong><br>
              Ventas Totales CANCELADAS: <strong><?php echo number_format($VtasCacelTot,2); ?></strong><br>
              Clientes Totales ACTIVOS: <strong><?php echo number_format($TotCtesACT,0); ?></strong><br>
              Ventas Concretadas: <strong><?php echo round($dc1v); ?> de 10</strong>
            </div>
          </div>
        </div>
        <!-- Gastos Mensuales -->
        <div class="col-lg-4">
          <div class="card">
            <div class="card-header bg-secondary text-light">Gastos Mensuales</div>
            <div class="card-body">
              Sueldos a pagar: <strong><?php echo number_format($SUeldos,2); ?></strong><br>
              Clientes en COBRANZA: <strong><?php echo number_format($CtesMesCob,0); ?></strong><br>
              Comisiones pendientes: <strong><?php echo number_format($comisiones,2); ?></strong><br>
              CAC Cliente: <strong><?php echo number_format($CacVta,2); ?></strong><br>
              Promedio de Venta por cliente: <strong><?php echo number_format($CicVta,2); ?></strong>
            </div>
          </div>
        </div>
        <!-- Prospección -->
        <div class="col-lg-4">
          <div class="card">
            <div class="card-header bg-secondary text-light">Generales de Prospección</div>
            <div class="card-body">
              Prospectos Tierra (mes): <strong><?= $fmt->format($ProsTier) ?></strong><br>
              Prospectos Digitales (mes): <strong><?= $fmt->format($ProsDgt) ?></strong><br>
              Registros Generados (mes): <strong><?= $fmt->format($ProsGen) ?></strong><br>
              Registros Digitales: <strong><?= $fmt->format($ProsDig) ?></strong><br>
              Clientes Generados (mes): <strong><?= $fmt->format($Ventas) ?></strong>
            </div>
          </div>
        </div>
      </div>

      <br>

      <div class="row">
        <!-- Comportamiento Mensual -->
        <div class="col-lg-4">
          <div class="card">
            <div class="card-header bg-secondary text-light">Comportamiento Mensual</div>
            <div class="card-body">
              Cobranza del día: <strong><?php echo number_format($pagoHoy,2); ?></strong><br>
              Cobranza del mes: <strong><?php echo number_format($pagPero,2); ?></strong><br>
              Normalidad: <strong><?php echo round($AvCob); ?> %</strong><br>
              Mora generada en el Mes: <strong><?php echo number_format($PagEnMor3,2); ?></strong><br>
              Ventas del Mes: <strong><?php echo number_format($VtaDine,2); ?></strong>
            </div>
          </div>
        </div>
        <!-- Datos Crediticios -->
        <div class="col-lg-4">
          <div class="card">
            <div class="card-header bg-secondary text-light">Datos Crediticios</div>
            <div class="card-body">
              Valor Cartera: <strong><?php echo number_format($SaldCre1,2); ?></strong><br>
              Capital colocado: <strong><?php echo number_format($CarteCol,2); ?></strong><br>
              Cartera en Cobranza: <strong><?php echo number_format($PagEr,2); ?></strong><br>
              Cartera en Mora: <strong><?php echo number_format(0); ?></strong><br>
              Cartera dictaminada: <strong><?php echo number_format($PagEnMor,2); ?></strong>
            </div>
          </div>
        </div>
        <!-- Fideicomiso -->
        <div class="col-lg-4">
          <div class="card">
            <div class="card-header bg-secondary text-light">Datos Fideicomiso</div>
            <div class="card-body">
              Valor del fideicomiso: <strong><?php echo number_format($F0003,2); ?></strong><br>
              Valor Actual F/0003: <strong><?php echo number_format($VaF0003,2); ?></strong><br>
              Servicios Pagados: <strong><?php echo number_format($SerPagados,2); ?></strong><br>
              Servicios Otorgados: <strong><?php echo round($ServOtCte); ?></strong><br>
              Edad promedio Cliente: <strong><?php echo round($EdadCte); ?></strong>
            </div>
          </div>
        </div>
      </div>

      <br>

      <!-- bloques de gráficas -->
      <div class="center-heading">
        <p>Analisis de Ventas en el histórico desde <strong><?php echo $FeIniVtas; ?></strong></p>
      </div>
      <div class="row">
        <?php for($a=1;$a<=3;$a++): ?>
          <div class="col-lg-4">
            <div id="Grafica_<?php echo $a; ?>"></div>
          </div>
        <?php endfor; ?>
      </div>
      <hr>
      <div class="center-heading">
        <p>Análisis de Ventas del año en curso <strong><?php echo date('Y'); ?></strong></p>
      </div>
      <div class="row">
        <?php for($a=4;$a<=6;$a++): ?>
          <div class="col-lg-4">
            <div id="Grafica_<?php echo $a; ?>"></div>
          </div>
        <?php endfor; ?>
      </div>

      <div class="row">
        <div class="col-lg-12">
          <hr>
          <div class="center-heading">
            <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
              <select name="Periodo" class="form-control">
                <option value="0" selected>Selecciona un rango para analizar</option>
                <?php for($y=$startYear; $y<=$endYear; $y++): ?>
                  <option value="<?=$y?>">Año <?=$y?></option>
                <?php endfor; ?>
              </select>
              <br>
              <input type="submit" name="ConsulVect" class="btn btn-primary btn-block" value="Consultar periodo">
            </form>
          </div>
          <br>
          <div class="Grafica"><div id="curve_chart"></div></div>
        </div>
      </div>
    </div>
  </main>

  <!-- helpers -->
  <script defer src="Javascript/finger.js"></script>
  <script defer src="Javascript/localize.js"></script>
</body>
</html>
