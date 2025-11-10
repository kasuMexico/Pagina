<?php
/********************************************************************************************
 * Pwa_Analisis_Ventas.php — Tablero de análisis KASU
 * Qué hace: Muestra indicadores financieros totales, anuales y por período + 3 gráficas.
 * Compatibilidad: PHP 8.2
 * Fecha: 2025-11-05
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

session_start();

/* =============================================================================
 * [1] INICIALIZACIÓN: conexión, zona horaria, helpers
 * ========================================================================== */
require_once '../eia/librerias.php';          // Debe exponer $mysqli, $basicas, $financieras
require_once 'php/Analisis_Metas.php';        // Calcula metas del mes, sueldos y comisiones

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
  exit('DB no inicializada');
}
$mysqli->set_charset('utf8mb4');
if (!$mysqli->ping()) {
  exit('DB desconectada: '.$mysqli->connect_error);
}

$tz  = new DateTimeZone('America/Mexico_City');
$hoy = new DateTimeImmutable('today', $tz);
$ver = (string)time();

if (!isset($_SESSION['Vendedor'])) {
  $tok = isset($_GET['dataP']) ? base64_decode((string)$_GET['dataP'], true) : null;
  if ($tok === 'ValidJCCM') {
    $_SESSION['dataP'] = 'ValidJCCM';
  } else {
    header('Location: https://kasu.com.mx/login');
    exit;
  }
} else {
  $Niv = (string)$basicas->BuscarCampos($mysqli, 'Nivel', 'Empleados', 'IdUsuario', $_SESSION['Vendedor']);
}

if (!function_exists('h')) {
  function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
$fmtMoney = class_exists('NumberFormatter')
  ? new NumberFormatter('es_MX', NumberFormatter::CURRENCY) : null;
$fmtInt = class_exists('NumberFormatter')
  ? (function(){ $n = new NumberFormatter('es_MX', NumberFormatter::DECIMAL); $n->setAttribute(NumberFormatter::FRACTION_DIGITS,0); return $n; })()
  : null;

function money_mx(float $n, ?NumberFormatter $fmt): string {
  return $fmt ? $fmt->formatCurrency($n,'MXN') : ('$'.number_format($n,2,'.',','));
}
function div_safe(float $a, float $b, float $fallback=0.0): float {
  return ($b==0.0) ? $fallback : $a/$b;
}

/* =============================================================================
 * [2] PARÁMETROS DE FECHA BASE: inicio de mes actual y rango histórico
 * ========================================================================== */
$Fec0 = (new DateTimeImmutable('first day of this month', $tz))->format('Y-m-d');

$minDate   = (string)$basicas->MinDat($mysqli,'FechaRegistro','Venta');
$maxDateDb = (string)($basicas->MaxDat($mysqli,'FechaRegistro','Venta') ?? '');
$startYear = (int)date('Y', strtotime($minDate ?: 'today'));
$endYearDb = $maxDateDb ? (int)date('Y', strtotime($maxDateDb)) : (int)date('Y');
$endYear   = max($startYear, $endYearDb, (int)date('Y'));
$FeIniVtas = date('d-M-Y', strtotime($minDate ?: 'today'));

/* =============================================================================
 * [3] TABLAS DE TASAS POR PRODUCTO (fideicomiso y tasa anualizada para valor actual)
 * ========================================================================== */
$prodRates = [];
$resP = $mysqli->query('SELECT Producto, Fideicomiso, TFideicomiso FROM Productos');
foreach ($resP as $p) {
  $prodRates[$p['Producto']] = [
    'fide' => ((float)$p['Fideicomiso'])  / 100.0,  // proporción
    'tf'   => ((float)$p['TFideicomiso']) / 100.0,  // tasa anual
  ];
}

/* =============================================================================
 * [4] MÉTRICAS GLOBALES (toda la base) — Generales, Fideicomiso y Crediticios
 * ========================================================================== */
$vTtOT = $F0003 = $VaF0003 = $CarteCol = $SaldCre1 = 0.0;
$PagEr = $PagEnMor = $PagEnMorMes = 0.0;
$V=0; $prev=0; $efec=0; $TotCtesACT=0; $EdadAcum=0; $VtasCancelTot=0.0;

$tsHoy = (int)$hoy->format('U');
$rVentas = $mysqli->query('SELECT * FROM Venta');
foreach ($rVentas as $v) {
  $V++;
  $status = (string)$v['Status'];
  $prod   = (string)$v['Producto'];
  $costo  = (float)$v['CostoVenta'];
  $idV    = (int)$v['Id'];

  if ($status === 'ACTIVO') {
    if (!isset($prodRates[$prod])) { continue; }
    $vTtOT += $costo;

    $dep = $costo * $prodRates[$prod]['fide'];
    $F0003 += $dep;

    $tsV   = strtotime((string)$v['FechaRegistro']) ?: $tsHoy;
    $dias  = max(0, intdiv($tsHoy - $tsV, 86400));
    $rDay  = $prodRates[$prod]['tf']/365.0;
    $VaF0003 += $dep * ($rDay>0 && $dias>0 ? pow(1+$rDay,$dias) : 1.0);

    $TotCtesACT++;
    $curp = (string)$basicas->BuscarCampos($mysqli,'ClaveCurp','Usuario','IdContact',(int)$v['IdContact']);
    $edad = (int)$basicas->ObtenerEdad($curp);
    if ($edad>0) $EdadAcum += $edad;

  } elseif ($status === 'PREVENTA') {
    $prev++;

  } elseif ($status === 'COBRANZA') {
    $efec++;
    $pago = (float)$financieras->Pago($mysqli, $idV);
    $NP   = (int)$financieras->PagosPend($mysqli, $idV);
    if ($NP>0) $PagEr += $pago*$NP;
    $CarteCol += $costo;
    $SaldCre1 += (float)$financieras->SaldoCredito($mysqli,$idV);

  } elseif ($status === 'CANCELADO') {
    $VtasCancelTot += $costo;
    $NP   = (int)$financieras->PagosPend($mysqli,$idV);
    $pago = (float)$financieras->Pago($mysqli,$idV);
    if ($NP>0) $PagEnMor += $pago*$NP;

    $fecUltPag = (string)$basicas->Max1Dat($mysqli,'FechaRegistro','Pagos','Id',$idV);
    if ($fecUltPag && strtotime($fecUltPag) >= strtotime($Fec0)) {
      if ($NP>0) $PagEnMorMes += $pago*$NP;
    }
  }
}
$EdadPromCte = $TotCtesACT>0 ? $EdadAcum/$TotCtesACT : 0.0;
$edEfectivas = max(1, $V - $prev);
$efectividad10 = ($V>0) ? ($edEfectivas/$V)*10.0 : 0.0;

/* =============================================================================
 * [5] PROSPECCIÓN DEL MES ACTUAL
 * ========================================================================== */
$ProsGen = (int)$basicas->Cuenta0Fec($mysqli,'Contacto','FechaRegistro',$Fec0);
$ProsDig = (int)$basicas->Cuenta1Fec($mysqli,'Contacto','Usuario','PLATAFORMA','FechaRegistro',$Fec0);
$ProsGen = $ProsGen - $ProsDig;

$ProsDgt  = (int)$basicas->Cuenta0Fec($pros,'prospectos','Alta',$Fec0);
$ProsTier = (int)$basicas->Cuenta1Fec($pros,'prospectos','Origen','vta','Alta',$Fec0);
$ProsDgt  = $ProsDgt - $ProsTier;

$VentasMes  = (int)$basicas->Cuenta0Fec($mysqli,'Venta','FechaRegistro',$Fec0);
$VentasMesMX= (float)$basicas->Sumar0Fecha($mysqli,'CostoVenta','Venta','FechaRegistro',$Fec0);

/* =============================================================================
 * [6] GASTOS / CAC / CIC de referencia (global)
 * ========================================================================== */
$CobrosTotales = (float)$basicas->Sumar($mysqli,'Cantidad','Pagos');
$ComPagadas    = (float)$basicas->Sumar($mysqli,'Cantidad','Comisiones_pagos');
$Plizas        = (int)$basicas->Cuenta0Fec($mysqli,'Contacto','FechaRegistro',$FeIniVtas);
$Cpol          = $Plizas * 37.5;
$ValREm        = div_safe(($prev/$V)*10*37.5, ($edEfectivas/$V)*10, 0.0);
$CostCont      = div_safe(($prev/$V)*10*15.0,  ($edEfectivas/$V)*10, 0.0);
$CAC           = div_safe(($ComPagadas + $Cpol), (float)$edEfectivas, 0.0) + $ValREm + $CostCont;
$CIC           = div_safe($CobrosTotales, (float)$edEfectivas, 0.0);

/* =============================================================================
 * [7] FUNCIÓN: métricas por período (anual o rango a elección)
 * ========================================================================== */
function metrics_period(mysqli $db, array $rates, string $ini, string $fin, DateTimeImmutable $hoy, $basicas, $financieras): array {
  $out = [
    'CobrosTotales'=>0.0, 'VtasActivas'=>0.0, 'VtasCancel'=>0.0, 'CtesActivos'=>0,
    'Efectividad10'=>0.0, 'ValFide'=>0.0, 'ValActFide'=>0.0,
    'ServPagados'=>0.0, 'ServOtorg'=>0, 'EdadProm'=>0.0,
    'ValCartera'=>0.0, 'CapColocado'=>0.0, 'CarCobranza'=>0.0, 'CarMora'=>0.0, 'CarDict'=>0.0,
    'VentasTot'=>0, 'Preventas'=>0
  ];

  $st = $db->prepare('SELECT COALESCE(SUM(Cantidad),0) s FROM Pagos WHERE FechaRegistro BETWEEN ? AND ?');
  $st->bind_param('ss',$ini,$fin); $st->execute();
  $out['CobrosTotales'] = (float)$st->get_result()->fetch_assoc()['s']; $st->close();

  $st = $db->prepare('SELECT * FROM Venta WHERE FechaRegistro BETWEEN ? AND ?');
  $st->bind_param('ss',$ini,$fin); $st->execute();
  $rs = $st->get_result();
  $tsHoy = (int)$hoy->format('U');
  $edadAc=0;

  while ($v = $rs->fetch_assoc()) {
    $out['VentasTot']++;
    $status = (string)$v['Status'];
    $prod   = (string)$v['Producto'];
    $costo  = (float)$v['CostoVenta'];
    $idV    = (int)$v['Id'];

    if ($status==='ACTIVO') {
      $out['VtasActivas'] += $costo; $out['CtesActivos']++;
      if (isset($rates[$prod])) {
        $dep = $costo * $rates[$prod]['fide']; $out['ValFide'] += $dep;
        $ts  = strtotime((string)$v['FechaRegistro']) ?: $tsHoy;
        $dias= max(0,intdiv($tsHoy-$ts,86400));
        $rD  = $rates[$prod]['tf']/365.0;
        $out['ValActFide'] += $dep * ($rD>0&&$dias>0 ? pow(1+$rD,$dias):1.0);
      }
      $curp = (string)$basicas->BuscarCampos($db,'ClaveCurp','Usuario','IdContact',(int)$v['IdContact']);
      $e    = (int)$basicas->ObtenerEdad($curp);
      if ($e>0) $edadAc += $e;

    } elseif ($status==='PREVENTA') {
      $out['Preventas']++;

    } elseif ($status==='COBRANZA') {
      $p   = (float)$financieras->Pago($db,$idV);
      $np  = (int)$financieras->PagosPend($db,$idV);
      if ($np>0) $out['CarCobranza'] += $p*$np;
      $out['CapColocado'] += $costo;
      $out['ValCartera']  += (float)$financieras->SaldoCredito($db,$idV);

    } elseif ($status==='CANCELADO') {
      $out['VtasCancel'] += $costo;
      $np  = (int)$financieras->PagosPend($db,$idV);
      $p   = (float)$financieras->Pago($db,$idV);
      if ($np>0) $out['CarDict'] += $p*$np;
    }
  }
  $st->close();

  $st = $db->prepare('SELECT COALESCE(SUM(Costo),0) s, COUNT(*) n FROM EntregaServicio WHERE FechaEntrega BETWEEN ? AND ?');
  $st->bind_param('ss',$ini,$fin); $st->execute();
  $tmp = $st->get_result()->fetch_assoc(); $st->close();
  $out['ServPagados'] = (float)$tmp['s']; $out['ServOtorg'] = (int)$tmp['n'];

  $ctes = max(0,$out['CtesActivos']);
  $out['EdadProm'] = $ctes>0 ? ($edadAc/$ctes) : 0.0;
  $efec = max(1,$out['VentasTot'] - $out['Preventas']);
  $out['Efectividad10'] = ($out['VentasTot']>0) ? ($efec/$out['VentasTot'])*10.0 : 0.0;

  return $out;
}

/* =============================================================================
 * [8] MÉTRICAS ANUALES: año en curso
 * ========================================================================== */
$iniYear = date('Y-01-01'); $finYear = date('Y-12-31');
$ANUAL = metrics_period($mysqli,$prodRates,$iniYear,$finYear,$hoy,$basicas,$financieras);

/* =============================================================================
 * [9] MÉTRICAS POR PERÍODO: selector de rango [fecha inicio | fecha fin]
 * ========================================================================== */
$PERIODO = null;
$showPeriodo = false;

if (!empty($_POST['ini']) && !empty($_POST['fin'])) {
  $iniSel = $_POST['ini'];
  $finSel = $_POST['fin'];
  if (@strtotime($iniSel) && @strtotime($finSel) && $iniSel <= $finSel) {
    $PERIODO = metrics_period($mysqli,$prodRates,$iniSel,$finSel,$hoy,$basicas,$financieras);
    $showPeriodo = true;
  }
}

/* =============================================================================
 * [10] JS: 3 gráficas — ventas totales, valor de ventas activas, ventas por status
 *          Endpoints usados:
 *          - php/AnalisisDatos/c_Ventas_Totales.php        (conteo por producto)
 *          - php/AnalisisDatos/d_Valor_Ventas.php          (valor MXN por producto ACTIVO)
 *          - php/Archivos_Grafica/Consulta_Status.php      (conteo por status)
 * ========================================================================== */
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
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?php echo h($ver); ?>">
  <link rel="stylesheet" href="assets/css/Grafica.css">

  <!-- JS base + Charts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
  <script src="https://www.gstatic.com/charts/loader.js"></script>
  <script>
    google.charts.load('current', {'packages':['corechart']});

    function drawPieFrom(url, containerId, titleTxt) {
      $.ajax({ url: url, dataType: 'json', cache: false })
        .done(function(tbl){
          if (!document.getElementById(containerId)) return;
          var data  = new google.visualization.DataTable(tbl);
          var chart = new google.visualization.PieChart(document.getElementById(containerId));
          chart.draw(data, { width:'100%', height:300, title: titleTxt });
        });
    }

    function drawAll() {
      drawPieFrom('php/AnalisisDatos/c_Ventas_Totales.php',    'g_totales', 'Ventas totales por producto');
      drawPieFrom('php/AnalisisDatos/d_Valor_Ventas.php',      'g_activas', 'Valor de ventas ACTIVAS por producto');
      drawPieFrom('php/Archivos_Grafica/Consulta_Status.php',  'g_status',  'Ventas por estatus');
      <?php /* Curva histórica la generamos abajo */ ?>
    }
    google.charts.setOnLoadCallback(drawAll);
  </script>

  <!-- Curva histórica: se arma en PHP con arrayToDataTable -->
  <script>
    function drawCurve() {
      var data = google.visualization.arrayToDataTable(
        [<?php
          // Datos por año desde inicio histórico
          $NombreGraf_LG = 'Ventas Totales Efectivas';
          $Prod = $Año = $UVen = [];
          $Fecha  = date('Y-m-d', strtotime("first day of january $startYear"));
          $yearsN = $endYear - $startYear;

          $res1 = $mysqli->query('SELECT Producto FROM Productos');
          foreach ($res1 as $i => $Reg1) {
            $Prod[$i] = (string)$Reg1['Producto'];
            for ($c = 0; $c <= $yearsN; $c++) {
              $feIni   = date('Y-m-d', strtotime("$Fecha + $c year"));
              $yearS   = date('Y', strtotime($feIni));
              $feFin   = date('Y-m-d', strtotime("last day of december $yearS"));
              $UVen[]  = (int)$basicas->CuentaFechas($mysqli,'Venta','Producto',$Prod[$i],'FechaRegistro',$feFin,'FechaRegistro',$feIni,'Status','PREVENTA');
              $Año[$c] = $yearS;
            }
          }

          $Nu=count($Prod); $Ne=count($Año); $Uv=count($UVen);
          $ini="['Base','"; $in2="['"; $Med="','"; $Me2=","; $Fin="'],"; $Fi2="],";

          echo $ini;
          for($e=0;$e<$Nu;$e++){ echo $Prod[$e].($e!=$Nu-1?$Med:''); }
          echo $Fin;

          for($f=0;$f<$Ne;$f++){
            echo $in2.$Año[$f]."'";
            $Bl = $Uv/$Nu;
            for($g=0;$g<$Nu;$g++){
              $NiV = ($g*$Bl)+$f;
              echo $Me2.(int)$UVen[$NiV];
            }
            echo $Fi2;
          }
        ?>]
      );
      var options = { title:'<?php echo h($NombreGraf_LG); ?>', curveType:'function', legend:{position:'bottom'} };
      var chart   = new google.visualization.LineChart(document.getElementById('curve_chart'));
      chart.draw(data, options);
    }
    google.charts.setOnLoadCallback(drawCurve);
  </script>
</head>

<body>
  <!-- TOP BAR -->
  <div class="topbar"><h4 class="title">Análisis de Ventas</h4></div>

  <!-- Menú inferior -->
  <section id="Menu">
    <?php if(($_SESSION['dataP'] ?? '') !== 'ValidJCCM' || isset($_SESSION['Vendedor'])) { require_once 'html/Menuprinc.php'; } ?>
  </section>

  <!-- CONTENIDO -->
  <main class="page-content">
    <div class="container">

      <!-- ===================================================================
           [A] INDICADORES TOTALES (3 tarjetas)
           =================================================================== -->
      <h5 class="mb-3">Indicadores financieros totales</h5>
      <div class="row">
        <!-- Generales KASU -->
        <div class="col-lg-4">
          <div class="card"><div class="card-header bg-secondary text-light">Generales KASU</div>
            <div class="card-body">
              Cobros Totales: <strong><?php echo h(money_mx($CobrosTotales,$fmtMoney)); ?></strong><br>
              Vtas Totales ACTIVAS: <strong><?php echo h(money_mx($vTtOT,$fmtMoney)); ?></strong><br>
              Vtas Totales CANCELADAS: <strong><?php echo h(money_mx($VtasCancelTot,$fmtMoney)); ?></strong><br>
              Clientes Totales ACTIVOS: <strong><?php echo $fmtInt? $fmtInt->format($TotCtesACT): (string)$TotCtesACT; ?></strong><br>
              Ventas Concretadas: <strong><?php echo (int)round($efectividad10); ?> de 10</strong>
            </div>
          </div>
        </div>

        <!-- Datos Fideicomiso -->
        <div class="col-lg-4">
          <div class="card"><div class="card-header bg-secondary text-light">Datos Fideicomiso</div>
            <div class="card-body">
              Valor del fideicomiso: <strong><?php echo h(money_mx($F0003,$fmtMoney)); ?></strong><br>
              Valor Actual F/0003: <strong><?php echo h(money_mx($VaF0003,$fmtMoney)); ?></strong><br>
              Servicios Pagados: <strong><?php echo h(money_mx((float)$basicas->Sumar($mysqli,'Costo','EntregaServicio'),$fmtMoney)); ?></strong><br>
              Servicios Funerarios Otorgados: <strong><?php echo $fmtInt? $fmtInt->format((int)$basicas->ContarTabla($mysqli,'EntregaServicio')): ''; ?></strong><br>
              Edad promedio Cliente: <strong><?php echo $fmtInt? $fmtInt->format((int)round($EdadPromCte)) : (string)round($EdadPromCte); ?></strong>
            </div>
          </div>
        </div>

        <!-- Datos Crediticios -->
        <div class="col-lg-4">
          <div class="card"><div class="card-header bg-secondary text-light">Datos Crediticios</div>
            <div class="card-body">
              Valor Cartera: <strong><?php echo h(money_mx($SaldCre1,$fmtMoney)); ?></strong><br>
              Capital colocado: <strong><?php echo h(money_mx($CarteCol,$fmtMoney)); ?></strong><br>
              Cartera en Cobranza: <strong><?php echo h(money_mx($PagEr,$fmtMoney)); ?></strong><br>
              Cartera en Mora: <strong><?php echo h(money_mx(0,$fmtMoney)); ?></strong><br>
              Cartera dictaminada: <strong><?php echo h(money_mx($PagEnMor,$fmtMoney)); ?></strong>
            </div>
          </div>
        </div>
      </div>

      <!-- ===================================================================
           [B] INDICADORES ANUALES (año en curso) — 3 tarjetas
           =================================================================== -->
      <h5 class="mt-4 mb-3">Indicadores financieros anuales (<?php echo date('Y'); ?>)</h5>
      <div class="row">
        <!-- Generales KASU (anual) -->
        <div class="col-lg-4">
          <div class="card"><div class="card-header bg-secondary text-light">Generales KASU</div>
            <div class="card-body">
              Cobros Anuales: <strong><?php echo h(money_mx($ANUAL['CobrosTotales'],$fmtMoney)); ?></strong><br>
              Vtas Anuales ACTIVAS: <strong><?php echo h(money_mx($ANUAL['VtasActivas'],$fmtMoney)); ?></strong><br>
              Vtas Anuales CANCELADAS: <strong><?php echo h(money_mx($ANUAL['VtasCancel'],$fmtMoney)); ?></strong><br>
              Clientes Anuales ACTIVOS: <strong><?php echo $fmtInt? $fmtInt->format($ANUAL['CtesActivos']): (string)$ANUAL['CtesActivos']; ?></strong><br>
              Efectividad de Ventas: <strong><?php echo (int)round($ANUAL['Efectividad10']); ?> de 10</strong>
            </div>
          </div>
        </div>

        <!-- Generales de Prospección (anual) -->
        <div class="col-lg-4">
          <div class="card">
            <div class="card-header bg-secondary text-light">Generales de Prospección</div>
            <div class="card-body">
              <?php
              // Rango anual seguro. Si no existen $iniYear / $finYear, calcúlalos.
              if (!isset($iniYear, $finYear)) {
                $y        = (int)date('Y');
                $iniYear  = date('Y-m-d', strtotime("$y-01-01"));
                $finYear  = date('Y-m-d', strtotime("$y-12-31"));
              }

              // Formateador entero
              $fmtI = $fmtInt ?? null;
              $fmtOut = function (int $n) use ($fmtI) { return $fmtI ? $fmtI->format($n) : (string)$n; };

              // CuentaFechas exige 10 argumentos. Usamos un NE siempre verdadero: Id != '__NONE__'
              $NE_COL = 'Id';
              $NE_VAL = '__NONE__';

              $prosTierra     = (int)$basicas->CuentaFechas(
                $mysqli, 'Contacto', 'Usuario', 'VTA', 'FechaRegistro', $finYear, 'FechaRegistro', $iniYear, $NE_COL, $NE_VAL
              );
              $prosDigital    = (int)$basicas->CuentaFechas(
                $mysqli, 'Contacto', 'Usuario', 'PLATAFORMA', 'FechaRegistro', $finYear, 'FechaRegistro', $iniYear, $NE_COL, $NE_VAL
              );
              $regGenerados   = (int)$basicas->CuentaFechas0(
                $mysqli, 'Contacto', 'FechaRegistro', $finYear, 'FechaRegistro', $iniYear
              );
              $regDigitales   = $prosDigital; // mismo filtro que $prosDigital
              $cteGenerados   = (int)$basicas->CuentaFechas0(
                $mysqli, 'Venta', 'FechaRegistro', $finYear, 'FechaRegistro', $iniYear
              );
              ?>
              Prospectos Tierra (año): <strong><?= $fmtOut($prosTierra) ?></strong><br>
              Prospectos Digitales (año): <strong><?= $fmtOut($prosDigital) ?></strong><br>
              Registros Generados (año): <strong><?= $fmtOut($regGenerados) ?></strong><br>
              Registros Digitales: <strong><?= $fmtOut($regDigitales) ?></strong><br>
              Clientes Generados (año): <strong><?= $fmtOut($cteGenerados) ?></strong>
            </div>
          </div>
        </div>

        <!-- Gasto corriente anual (servicios y fideicomiso del año) -->
        <div class="col-lg-4">
          <div class="card"><div class="card-header bg-secondary text-light">Fideicomiso / Servicios (año)</div>
            <div class="card-body">
              Val. Fideicomitido: <strong><?php echo h(money_mx($ANUAL['ValFide'],$fmtMoney)); ?></strong><br>
              Val. Actual Fideicomiso: <strong><?php echo h(money_mx($ANUAL['ValActFide'],$fmtMoney)); ?></strong><br>
              Servicios Pagados: <strong><?php echo h(money_mx($ANUAL['ServPagados'],$fmtMoney)); ?></strong><br>
              Servicios Otorgados: <strong><?php echo $fmtInt? $fmtInt->format($ANUAL['ServOtorg']): (string)$ANUAL['ServOtorg']; ?></strong>
            </div>
          </div>
        </div>
      </div>

      <!-- ===================================================================
           [C] RESUMEN POR PERÍODO: selector de rango y 3 tarjetas
           =================================================================== -->
      <div class="row mt-4">
        <div class="col-lg-12">
          <form method="POST" action="<?php echo h($_SERVER['PHP_SELF']); ?>" class="mb-3">
            <div class="form-row">
              <div class="col-sm-4">
                <label>Fecha inicio</label>
                <input type="date" class="form-control" name="ini" required>
              </div>
              <div class="col-sm-4">
                <label>Fecha fin</label>
                <input type="date" class="form-control" name="fin" required>
              </div>
              <div class="col-sm-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-block">Consultar período</button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <?php if ($showPeriodo && is_array($PERIODO)): ?>
      <div class="row">
        <!-- Generales KASU (período) -->
        <div class="col-lg-4">
          <div class="card"><div class="card-header bg-secondary text-light">Generales KASU (período)</div>
            <div class="card-body">
              Cobros: <strong><?php echo h(money_mx($PERIODO['CobrosTotales'],$fmtMoney)); ?></strong><br>
              Vtas ACTIVAS: <strong><?php echo h(money_mx($PERIODO['VtasActivas'],$fmtMoney)); ?></strong><br>
              Vtas CANCELADAS: <strong><?php echo h(money_mx($PERIODO['VtasCancel'],$fmtMoney)); ?></strong><br>
              Clientes ACTIVOS: <strong><?php echo $fmtInt? $fmtInt->format($PERIODO['CtesActivos']) : (string)$PERIODO['CtesActivos']; ?></strong><br>
              Efectividad: <strong><?php echo (int)round($PERIODO['Efectividad10']); ?> de 10</strong>
            </div>
          </div>
        </div>

        <!-- Prospección (proxy por ventas/servicios en período) -->
        <div class="col-lg-4">
          <div class="card"><div class="card-header bg-secondary text-light">Fideicomiso / Servicios (período)</div>
            <div class="card-body">
              Val. Fideicomitido: <strong><?php echo h(money_mx($PERIODO['ValFide'],$fmtMoney)); ?></strong><br>
              Val. Actual Fideicomiso: <strong><?php echo h(money_mx($PERIODO['ValActFide'],$fmtMoney)); ?></strong><br>
              Servicios Pagados: <strong><?php echo h(money_mx($PERIODO['ServPagados'],$fmtMoney)); ?></strong><br>
              Servicios Otorgados: <strong><?php echo $fmtInt? $fmtInt->format($PERIODO['ServOtorg']) : (string)$PERIODO['ServOtorg']; ?></strong>
            </div>
          </div>
        </div>

        <!-- Crediticios (período) -->
        <div class="col-lg-4">
          <div class="card"><div class="card-header bg-secondary text-light">Datos Crediticios (período)</div>
            <div class="card-body">
              Valor Cartera: <strong><?php echo h(money_mx($PERIODO['ValCartera'],$fmtMoney)); ?></strong><br>
              Capital colocado: <strong><?php echo h(money_mx($PERIODO['CapColocado'],$fmtMoney)); ?></strong><br>
              Cartera en Cobranza: <strong><?php echo h(money_mx($PERIODO['CarCobranza'],$fmtMoney)); ?></strong><br>
              Cartera dictaminada: <strong><?php echo h(money_mx($PERIODO['CarDict'],$fmtMoney)); ?></strong><br>
              Edad promedio Cliente: <strong><?php echo $fmtInt? $fmtInt->format((int)round($PERIODO['EdadProm'])) : (string)round($PERIODO['EdadProm']); ?></strong>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- ===================================================================
           [D] GRÁFICAS: 3 superiores + curva histórica
           =================================================================== -->
      <div class="center-heading mt-4"><p>Histórico desde <strong><?php echo h($FeIniVtas); ?></strong></p></div>
      <div class="row">
        <div class="col-lg-4"><div id="g_totales"></div></div>
        <div class="col-lg-4"><div id="g_activas"></div></div>
        <div class="col-lg-4"><div id="g_status"></div></div>
      </div>

      <hr>

      <div class="center-heading">
        <p>Curva histórica de ventas efectivas</p>
      </div>
      <div class="Grafica"><div id="curve_chart"></div></div>

    </div>
  </main>

  <!-- Helpers -->
  <script src="Javascript/finger.js?v=3"></script>
  <script src="Javascript/localize.js?v=3"></script>
  <script src="Javascript/Inyectar_gps_form.js"></script>
</body>
</html>
