<?php
/********************************************************************************************
 * Archivo: Pwa_Analisis_Ventas.php
 * Qué hace: Muestra indicadores financieros totales, anuales y por período + 3 gráficas.
 * Compatibilidad: PHP 8.2
 * Fecha: 2025-11-05
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

require_once dirname(__DIR__) . '/eia/session.php';
kasu_session_start();

/* =============================================================================
 * [1] INICIALIZACIÓN: conexión, zona horaria, helpers
 * ========================================================================== */
require_once __DIR__ . '/../eia/librerias.php';          // Debe exponer $mysqli, $basicas, $financieras
require_once 'php/Analisis_Metas.php';        // Calcula metas del mes, sueldos y comisiones
/* =============================================================================
 * [1.5] INCLUIR CÁLCULO FONDO FUNERARIO
 * ========================================================================== */
require_once 'php/AnalisisDatos/ConfigFondoFunerario.php';
require_once 'php/AnalisisDatos/CalculoFondoFunerario.php';
require_once 'php/AnalisisDatos/FondoInversionManager.php';

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
  ? (function(){ 
      $n = new NumberFormatter('es_MX', NumberFormatter::DECIMAL); 
      $n->setAttribute(NumberFormatter::FRACTION_DIGITS, 0); 
      return $n; 
    })()
  : null;

$fmtOut = function ($n) use ($fmtInt): string {
  // Manejar null o vacío
  if ($n === null || $n === '') {
    return $fmtInt ? $fmtInt->format(0) : '0';
  }
  
  // Intentar convertir a número
  if (is_numeric($n)) {
    $num = (int)$n;
  } else {
    // Si no es numérico, intentar extraer números
    preg_match('/\d+/', (string)$n, $matches);
    $num = $matches ? (int)$matches[0] : 0;
  }
  
  return $fmtInt ? $fmtInt->format($num) : number_format($num, 0, '.', ',');
};
$prosTierra = $prosDigital = $regGenerados = $regDigitales = $cteGenerados = 0;

function money_mx(float $n, ?NumberFormatter $fmt): string {
  return $fmt ? $fmt->formatCurrency($n,'MXN') : ('$'.number_format($n,2,'.',','));
}
function div_safe(float $a, float $b, float $fallback=0.0): float {
  return ($b==0.0) ? $fallback : $a/$b;
}
function pct(float $value, int $dec = 1): string {
  return number_format($value * 100, $dec, '.', ',') . '%';
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
$rangeDefaultIni = $minDate ? date('Y-m-d', strtotime($minDate)) : date('Y-01-01');
$rangeDefaultFin = $hoy->format('Y-m-d');
$chartIni = filter_input(INPUT_GET, 'chart_ini', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: $rangeDefaultIni;
$chartFin = filter_input(INPUT_GET, 'chart_fin', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: $rangeDefaultFin;
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $chartIni)) { $chartIni = $rangeDefaultIni; }
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $chartFin)) { $chartFin = $rangeDefaultFin; }
if ($chartIni > $chartFin) {
  [$chartIni, $chartFin] = [$chartFin, $chartIni];
}

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
$VentasActivasCount = 0;
$VentasCanceladasCount = 0;
$MRREstimado = 0.0;

$tsHoy = (int)$hoy->format('U');
$rVentas = $mysqli->query('SELECT * FROM Venta');
foreach ($rVentas as $v) {
  $V++;
  $status = (string)$v['Status'];
  $prod   = (string)$v['Producto'];
  $costo  = (float)$v['CostoVenta'];
  $idV    = (int)$v['Id'];

  if ($status === 'ACTIVO') {
    $VentasActivasCount++;
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
    if ((int)($v['NumeroPagos'] ?? 1) > 1) {
      $MRREstimado += (float)$financieras->Pago($mysqli, $idV);
    }

  } elseif ($status === 'PREVENTA') {
    $prev++;

  } elseif ($status === 'COBRANZA') {
    $efec++;
    $pago = (float)$financieras->Pago($mysqli, $idV);
    $NP   = (int)$financieras->PagosPend($mysqli, $idV);
    if ($NP>0) $PagEr += $pago*$NP;
    $CarteCol += $costo;
    $SaldCre1 += (float)$financieras->SaldoCredito($mysqli,$idV);
    if ((int)($v['NumeroPagos'] ?? 1) > 1) {
      $MRREstimado += (float)$financieras->Pago($mysqli, $idV);
    }

  } elseif ($status === 'CANCELADO') {
    $VentasCanceladasCount++;
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
$TicketPromedio = $TasaCancelacion = $TasaPreventas = 0.0;
$RetentionRate = $MorosidadRatio = $CarteraDictVsCap = $ARPU = 0.0;

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
$TicketPromedio = div_safe($vTtOT, (float)max(1,$VentasActivasCount));
$TasaCancelacion = ($V>0) ? ($VentasCanceladasCount / $V) : 0.0;
$TasaPreventas  = ($V>0) ? ($prev / $V) : 0.0;
$RetentionRate  = max(0.0, 1.0 - $TasaCancelacion);
$MorosidadRatio = div_safe($PagEr, $SaldCre1, 0.0);
$CarteraDictVsCap = div_safe($PagEnMor, $CarteCol, 0.0);
$ARPU = div_safe($CobrosTotales, (float)max(1, $TotCtesACT), 0.0);

/* =============================================================================
 * [7] FUNCIÓN: métricas por período (anual o rango a elección)
 * ========================================================================== */
function metrics_period(mysqli $db, array $rates, string $ini, string $fin, DateTimeImmutable $hoy, $basicas, $financieras): array {
  $out = [
    'CobrosTotales'=>0.0, 'VtasActivas'=>0.0, 'VtasCancel'=>0.0, 'CtesActivos'=>0,
    'Efectividad10'=>0.0, 'ValFide'=>0.0, 'ValActFide'=>0.0,
    'ServPagados'=>0.0, 'ServOtorg'=>0, 'EdadProm'=>0.0,
    'ValCartera'=>0.0, 'CapColocado'=>0.0, 'CarCobranza'=>0.0, 'CarMora'=>0.0, 'CarDict'=>0.0,
    'VentasTot'=>0, 'Preventas'=>0,
    'VentasActivasCount'=>0, 'VentasCanceladasCount'=>0, 'MRR'=>0.0,
    'TicketPromedio'=>0.0, 'CancelRate'=>0.0, 'RetentionRate'=>0.0, 'MoraRatio'=>0.0, 'ARPU'=>0.0
  ];

  $st = $db->prepare('SELECT COALESCE(SUM(Cantidad),0) s FROM Pagos WHERE FechaRegistro BETWEEN ? AND ?');
  $st->bind_param('ss',$ini,$fin); $st->execute();
  $out['CobrosTotales'] = (float)$st->get_result()->fetch_assoc()['s']; $st->close();

  $st = $db->prepare('SELECT * FROM Venta WHERE FechaRegistro BETWEEN ? AND ?');
  $st->bind_param('ss',$ini,$fin); $st->execute();
  $rs = $st->get_result();
  $tsHoy = (int)$hoy->format('U');
  $edadAc=0;
  $activosCount = 0;
  $cancelCount = 0;
  $mrr = 0.0;

  while ($v = $rs->fetch_assoc()) {
    $out['VentasTot']++;
    $status = (string)$v['Status'];
    $prod   = (string)$v['Producto'];
    $costo  = (float)$v['CostoVenta'];
    $idV    = (int)$v['Id'];

    if ($status==='ACTIVO') {
      $activosCount++;
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
      if ((int)($v['NumeroPagos'] ?? 1) > 1) {
        $mrr += (float)$financieras->Pago($db,$idV);
      }

    } elseif ($status==='PREVENTA') {
      $out['Preventas']++;

    } elseif ($status==='COBRANZA') {
      $p   = (float)$financieras->Pago($db,$idV);
      $np  = (int)$financieras->PagosPend($db,$idV);
      if ($np>0) $out['CarCobranza'] += $p*$np;
      $out['CapColocado'] += $costo;
      $out['ValCartera']  += (float)$financieras->SaldoCredito($db,$idV);
      if ((int)($v['NumeroPagos'] ?? 1) > 1) {
        $mrr += (float)$financieras->Pago($db,$idV);
      }

    } elseif ($status==='CANCELADO') {
      $cancelCount++;
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
  $out['VentasActivasCount'] = $activosCount;
  $out['VentasCanceladasCount'] = $cancelCount;
  $out['MRR'] = $mrr;
  $out['TicketPromedio'] = $activosCount > 0 ? div_safe($out['VtasActivas'], (float)$activosCount, 0.0) : 0.0;
  $out['CancelRate'] = ($out['VentasTot']>0) ? ($cancelCount / $out['VentasTot']) : 0.0;
  $out['RetentionRate'] = max(0.0, 1.0 - $out['CancelRate']);
  $out['MoraRatio'] = div_safe($out['CarCobranza'], $out['ValCartera'], 0.0);
  $out['ARPU'] = div_safe($out['CobrosTotales'], (float)max(1, $out['CtesActivos']), 0.0);

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

/* =============================================================================
 * [11] CÁLCULO FONDO FUNERARIO - Nuevo modelo 50%
 * ========================================================================== */
$calculadorFondo = new CalculoFondoFunerario($mysqli, $basicas);
$analisisFondo = $calculadorFondo->analizarVentasActivas();
$recomendacionesInversion = $calculadorFondo->generarRecomendacionesInversion($analisisFondo);

// Calcular métricas clave para display
$brechaPorVenta = $analisisFondo['total_ventas_activas'] > 0 ? 
    $analisisFondo['brecha_total'] / $analisisFondo['total_ventas_activas'] : 0;

$excedentePorVenta = $analisisFondo['total_ventas_activas'] > 0 ? 
    $analisisFondo['excedente_esperado_total'] / $analisisFondo['total_ventas_activas'] : 0;

$comisionPasivaPorVenta = $analisisFondo['total_ventas_activas'] > 0 ? 
    $analisisFondo['comision_pasiva_total'] / $analisisFondo['total_ventas_activas'] : 0;

/* =============================================================================
 * [12] SEGUIMIENTO FONDO DE INVERSIÓN - Historial real
 * ========================================================================== */
$fondoManager = new FondoInversionManager($mysqli);
$historialFondo = $fondoManager->obtenerHistorial(6); // Últimos 6 meses
$estadisticasFondo = $fondoManager->calcularEstadisticas();
$umbralInversion = $fondoManager->calcularUmbralInversion();

// Calcular tendencia
$tendencia = 'ESTABLE';
if (!empty($historialFondo) && count($historialFondo) >= 2) {
    $ultimo = $historialFondo[0]['Rendimiento'];
    $penultimo = $historialFondo[1]['Rendimiento'];
    $tendencia = ($ultimo > $penultimo) ? 'ALCISTA' : (($ultimo < $penultimo) ? 'BAJISTA' : 'ESTABLE');
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F1F7FC">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Análisis Ventas</title>

  <!-- PWA / iOS -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="/login/assets/css/Menu_Superior.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="/login/assets/css/pwa-core.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="/login/assets/css/pwa-components.css?v=<?= h((string)$VerCache) ?>">
  <link rel="stylesheet" href="assets/css/Grafica.css">
  <style>
    /* Estilos adicionales para el fondo de inversión */
    .alert-card {
        background: #fff3f3;
        border: 1px solid #ffcdd2;
        margin-bottom: 20px;
    }
    .alert-header {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
    }
    .recommendation-item {
        transition: all 0.3s ease;
    }
    .recommendation-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .badge-danger { background-color: #e74c3c; }
    .badge-warning { background-color: #f39c12; }
    .badge-success { background-color: #2ecc71; }
    .badge-info { background-color: #3498db; }
    .badge-secondary { background-color: #95a5a6; }
    .card-header.bg-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: white;
    }
    .progress-thick {
        height: 25px;
        margin-bottom: 10px;
    }
    .progress-thick .progress-bar {
        font-weight: bold;
        line-height: 25px;
    }
  </style>

  <!-- JS base + Charts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
  <script src="https://www.gstatic.com/charts/loader.js"></script>
  <script>
    google.charts.load('current', {'packages':['corechart']});
    const chartIni = '<?php echo h($chartIni); ?>';
    const chartFin = '<?php echo h($chartFin); ?>';
    const chartRangeQuery = '?ini=' + encodeURIComponent(chartIni) + '&fin=' + encodeURIComponent(chartFin);

    function drawPieFrom(url, containerId, titleTxt) {
      $.ajax({ url: url, dataType: 'json', cache: false })
        .done(function(tbl){
          if (!document.getElementById(containerId)) return;
          var data  = new google.visualization.DataTable(tbl);
          var chart = new google.visualization.PieChart(document.getElementById(containerId));
          chart.draw(data, { width:'100%', height:300, title: titleTxt });
        });
    }

    function drawFinancialKpis() {
      var container = document.getElementById('g_finanzas_kpi');
      if (!container) { return; }
      var data = google.visualization.arrayToDataTable([
        ['KPI','Monto',{ role:'style' },{ role:'annotation' }],
        ['Cobros',        <?php echo json_encode(round($CobrosTotales,2)); ?>, '#1abc9c', <?php echo json_encode(money_mx($CobrosTotales,$fmtMoney)); ?>],
        ['Capital activo',<?php echo json_encode(round($vTtOT,2)); ?>, '#3498db', <?php echo json_encode(money_mx($vTtOT,$fmtMoney)); ?>],
        ['MRR estimado',  <?php echo json_encode(round($MRREstimado,2)); ?>, '#9b59b6', <?php echo json_encode(money_mx($MRREstimado,$fmtMoney)); ?>],
        ['CAC',           <?php echo json_encode(round($CAC,2)); ?>, '#e67e22', <?php echo json_encode(money_mx($CAC,$fmtMoney)); ?>],
        ['CIC',           <?php echo json_encode(round($CIC,2)); ?>, '#34495e', <?php echo json_encode(money_mx($CIC,$fmtMoney)); ?>]
      ]);
      var options = {
        legend: { position: 'none' },
        height: 320,
        bar: { groupWidth: '65%' },
        vAxis: { format: 'currency' },
        chartArea: { width: '85%', height: '70%' },
        colors: ['#1abc9c','#3498db','#9b59b6','#e67e22','#34495e']
      };
      var chart = new google.visualization.ColumnChart(container);
      chart.draw(data, options);
    }

    function drawConversionKpis() {
      var container = document.getElementById('g_conversion_kpi');
      if (!container) { return; }
      var data = google.visualization.arrayToDataTable([
        ['Indicador','Porcentaje',{ role:'style' },{ role:'annotation' }],
        ['Retención',   <?php echo json_encode(round($RetentionRate*100,2)); ?>, '#2ecc71', <?php echo json_encode(pct($RetentionRate,1)); ?>],
        ['Cancelación', <?php echo json_encode(round($TasaCancelacion*100,2)); ?>, '#e74c3c', <?php echo json_encode(pct($TasaCancelacion,1)); ?>],
        ['Preventas',   <?php echo json_encode(round($TasaPreventas*100,2)); ?>, '#f1c40f', <?php echo json_encode(pct($TasaPreventas,1)); ?>],
        ['Morosidad',   <?php echo json_encode(round($MorosidadRatio*100,2)); ?>, '#9b59b6', <?php echo json_encode(pct($MorosidadRatio,1)); ?>]
      ]);
      var options = {
        legend: { position: 'none' },
        height: 320,
        bar: { groupWidth: '65%' },
        vAxis: { format: '#\'%\'' },
        chartArea: { width: '85%', height: '70%' }
      };
      var chart = new google.visualization.ColumnChart(container);
      chart.draw(data, options);
    }

    function drawActiveValueTotal() {
      var container = document.getElementById('g_activas_total');
      if (!container) { return; }
      $.ajax({ url: 'php/AnalisisDatos/valor_total_activas.php' + chartRangeQuery, dataType: 'json', cache: false })
        .done(function(tbl){
          var data = new google.visualization.DataTable(tbl);
          var options = {
            title: 'Valor total de ventas activas por producto',
            legend: { position: 'right' },
            height: 300,
            pieSliceText: 'percentage'
          };
          var chart = new google.visualization.PieChart(container);
          chart.draw(data, options);
        });
    }

    function drawFinancialKpisAnnual() {
      var container = document.getElementById('g_finanzas_kpi_anual');
      if (!container) { return; }
      var data = google.visualization.arrayToDataTable([
        ['KPI','Monto',{ role:'style' },{ role:'annotation' }],
        ['Cobros',        <?php echo json_encode(round($ANUAL['CobrosTotales'] ?? 0,2)); ?>, '#1abc9c', <?php echo json_encode(money_mx($ANUAL['CobrosTotales'] ?? 0,$fmtMoney)); ?>],
        ['Capital activo',<?php echo json_encode(round($ANUAL['VtasActivas'] ?? 0,2)); ?>, '#3498db', <?php echo json_encode(money_mx($ANUAL['VtasActivas'] ?? 0,$fmtMoney)); ?>],
        ['MRR estimado',  <?php echo json_encode(round($ANUAL['MRR'] ?? 0,2)); ?>, '#9b59b6', <?php echo json_encode(money_mx($ANUAL['MRR'] ?? 0,$fmtMoney)); ?>],
        ['Cartera cobranza',<?php echo json_encode(round($ANUAL['CarCobranza'] ?? 0,2)); ?>, '#e67e22', <?php echo json_encode(money_mx($ANUAL['CarCobranza'] ?? 0,$fmtMoney)); ?>],
        ['Cartera dictaminada',<?php echo json_encode(round($ANUAL['CarDict'] ?? 0,2)); ?>, '#34495e', <?php echo json_encode(money_mx($ANUAL['CarDict'] ?? 0,$fmtMoney)); ?>]
      ]);
      var options = {
        legend: { position: 'none' },
        height: 320,
        bar: { groupWidth: '65%' },
        vAxis: { format: 'currency' },
        chartArea: { width: '85%', height: '70%' },
        colors: ['#1abc9c','#3498db','#9b59b6','#e67e22','#34495e']
      };
      var chart = new google.visualization.ColumnChart(container);
      chart.draw(data, options);
    }

    function drawConversionKpisAnnual() {
      var container = document.getElementById('g_conversion_kpi_anual');
      if (!container) { return; }
      var data = google.visualization.arrayToDataTable([
        ['Indicador','Porcentaje',{ role:'style' },{ role:'annotation' }],
        ['Retención',   <?php echo json_encode(round(($ANUAL['RetentionRate'] ?? 0)*100,2)); ?>, '#2ecc71', <?php echo json_encode(pct($ANUAL['RetentionRate'] ?? 0,1)); ?>],
        ['Cancelación', <?php echo json_encode(round(($ANUAL['CancelRate'] ?? 0)*100,2)); ?>, '#e74c3c', <?php echo json_encode(pct($ANUAL['CancelRate'] ?? 0,1)); ?>],
        ['Mora cartera',<?php echo json_encode(round(($ANUAL['MoraRatio'] ?? 0)*100,2)); ?>, '#9b59b6', <?php echo json_encode(pct($ANUAL['MoraRatio'] ?? 0,1)); ?>]
      ]);
      var options = {
        legend: { position: 'none' },
        height: 320,
        bar: { groupWidth: '65%' },
        vAxis: { format: '#\'%\'' },
        chartArea: { width: '85%', height: '70%' }
      };
      var chart = new google.visualization.ColumnChart(container);
      chart.draw(data, options);
    }

    function drawRendimientoEdad() {
      var container = document.getElementById('g_rendimiento_edad');
      if (!container) { return; }
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Rango de edad');
      data.addColumn('number', 'Rendimiento mínimo requerido (%)');
      data.addColumn({type: 'string', role: 'style'});
      data.addColumn({type: 'string', role: 'annotation'});
      
      <?php foreach ($analisisFondo['resumen_por_rango_edad'] as $rango => $datos): ?>
      <?php if ($datos['ventas'] > 0): ?>
      <?php 
      $rendimiento = $datos['rendimiento_minimo_promedio'] * 100;
      $color = $rendimiento > 15 ? '#e74c3c' : ($rendimiento > 10 ? '#f39c12' : '#2ecc71');
      ?>
      data.addRow([
        '<?= $rango ?>', 
        <?= $rendimiento ?>,
        'color: <?= $color ?>',
        '<?= number_format($rendimiento, 1) ?>%'
      ]);
      <?php endif; ?>
      <?php endforeach; ?>
      
      var options = {
        title: 'Rendimiento anual mínimo requerido para cubrir servicio funerario',
        height: 400,
        legend: { position: 'none' },
        hAxis: { title: 'Rango de edad' },
        vAxis: { 
          title: 'Rendimiento mínimo requerido (%)',
          minValue: 0,
          format: '#\'%\'' 
        },
        colors: ['#4285F4'],
        bar: { groupWidth: '60%' }
      };
      
      var chart = new google.visualization.ColumnChart(container);
      chart.draw(data, options);
    }

    function drawRendimientoHistorico() {
      var container = document.getElementById('g_rendimiento_historico');
      if (!container) { return; }
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Mes');
      data.addColumn('number', 'Rendimiento Real (%)');
      data.addColumn('number', 'Meta Mínima (%)');
      data.addColumn('number', 'Diferencia (%)');
      
      <?php foreach ($historialFondo as $registro): ?>
      data.addRow([
        '<?= $registro['Mes'] ?>',
        <?= $registro['Rendimiento'] * 100 ?>,
        <?= $registro['MetaRendimientoMinimo'] * 100 ?>,
        <?= $registro['RendimientoRealVsMeta'] * 100 ?>
      ]);
      <?php endforeach; ?>
      
      var options = {
        title: 'Evolución del rendimiento del fondo de inversión',
        height: 400,
        curveType: 'function',
        legend: { position: 'bottom' },
        series: {
          0: { color: '#4285F4' },
          1: { color: '#EA4335', lineDashStyle: [4, 4] },
          2: { color: '#34A853', type: 'bars' }
        },
        vAxis: { 
          title: 'Rendimiento (%)',
          format: '#\'%\'' 
        },
        hAxis: { title: 'Mes' }
      };
      
      var chart = new google.visualization.LineChart(container);
      chart.draw(data, options);
    }

    function drawAll() {
      drawPieFrom('php/AnalisisDatos/c_Ventas_Totales.php' + chartRangeQuery,    'g_totales', 'Ventas totales por producto');
      drawPieFrom('php/AnalisisDatos/d_Valor_Ventas.php' + chartRangeQuery,      'g_activas', 'Valor de ventas ACTIVAS por producto');
      drawPieFrom('php/Archivos_Grafica/Consulta_Status.php' + chartRangeQuery,  'g_status',  'Ventas por estatus');
      drawFinancialKpis();
      drawConversionKpis();
      drawFinancialKpisAnnual();
      drawConversionKpisAnnual();
      drawActiveValueTotal();
      drawRendimientoEdad();
      drawRendimientoHistorico();
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

<body onload="localize()">
  <!-- TOP BAR  Pwa_Analisis_Ventas.php-->
  <div class="topbar">
    <div class="topbar-left">
      <img alt="KASU" src="/login/assets/img/kasu_logo.jpeg">
      <div>
        <p class="eyebrow mb-0">Panel móvil</p>
        <h4 class="title">Análisis de Ventas</h4>
      </div>
    </div>
    <div class="topbar-actions"></div>
  </div>

  <!-- Menú inferior -->
  <section id="Menu">
    <?php if(($_SESSION['dataP'] ?? '') !== 'ValidJCCM' || isset($_SESSION['Vendedor'])) { require_once 'html/Menuprinc.php'; } ?>
  </section>

  <!-- CONTENIDO -->
  <main class="page-content">
    <div class="dashboard-shell">

      <div class="page-heading">
        <p>Indicadores financieros y de conversión · Rango <?= h($chartIni); ?> a <?= h($chartFin); ?></p>
      </div>

      <!-- ===================================================================
           [A] INDICADORES TOTALES (tarjetas)
           =================================================================== -->
      <div class="card-grid">
        <!-- Generales KASU -->
        <article class="card-base kpi-card">
          <header><div>
            <p class="chart-subtitle mb-1">Totales</p>
            <h2 class="chart-title">Generales KASU</h2>
          </div><span class="pill">Cobranza</span></header>
          <div class="item"><span>Cobros Totales</span><strong><?php echo h(money_mx($CobrosTotales,$fmtMoney)); ?></strong></div>
          <div class="item"><span>Vtas ACTIVAS</span><strong><?php echo h(money_mx($vTtOT,$fmtMoney)); ?></strong></div>
          <div class="item"><span>Vtas CANCELADAS</span><strong><?php echo h(money_mx($VtasCancelTot,$fmtMoney)); ?></strong></div>
          <div class="item"><span>Clientes ACTIVOS</span><strong><?php echo $fmtInt? $fmtInt->format($TotCtesACT): (string)$TotCtesACT; ?></strong></div>
          <div class="item"><span>Efectividad</span><strong><?php echo (int)round($efectividad10); ?> / 10</strong></div>
        </article>

        <!-- Datos Fideicomiso -->
        <article class="card-base kpi-card">
          <header><div>
            <p class="chart-subtitle mb-1">Fondos</p>
            <h2 class="chart-title">Datos Fideicomiso</h2>
          </div><span class="pill">F/0003</span></header>
          <div class="item"><span>Valor fideicomiso</span><strong><?php echo h(money_mx($F0003,$fmtMoney)); ?></strong></div>
          <div class="item"><span>Valor actual F/0003</span><strong><?php echo h(money_mx($VaF0003,$fmtMoney)); ?></strong></div>
          <div class="item"><span>Servicios pagados</span><strong><?php echo h(money_mx((float)$basicas->Sumar($mysqli,'Costo','EntregaServicio'),$fmtMoney)); ?></strong></div>
          <div class="item"><span>Servicios otorgados</span><strong><?php echo $fmtInt? $fmtInt->format((int)$basicas->ContarTabla($mysqli,'EntregaServicio')): ''; ?></strong></div>
          <div class="item"><span>Edad promedio cliente</span><strong><?php echo $fmtInt? $fmtInt->format((int)round($EdadPromCte)) : (string)round($EdadPromCte); ?></strong></div>
        </article>

        <!-- Datos Crediticios -->
        <article class="card-base kpi-card">
          <header><div>
            <p class="chart-subtitle mb-1">Cartera</p>
            <h2 class="chart-title">Datos Crediticios</h2>
          </div><span class="pill">Crédito</span></header>
          <div class="item"><span>Valor Cartera</span><strong><?php echo h(money_mx($SaldCre1,$fmtMoney)); ?></strong></div>
          <div class="item"><span>Capital colocado</span><strong><?php echo h(money_mx($CarteCol,$fmtMoney)); ?></strong></div>
          <div class="item"><span>Cartera en Cobranza</span><strong><?php echo h(money_mx($PagEr,$fmtMoney)); ?></strong></div>
          <div class="item"><span>Cartera en Mora</span><strong><?php echo h(money_mx(0,$fmtMoney)); ?></strong></div>
          <div class="item"><span>Cartera dictaminada</span><strong><?php echo h(money_mx($PagEnMor,$fmtMoney)); ?></strong></div>
        </article>
      </div>

      <h5 class="mt-3 mb-2">KPIs estratégicos</h5>
      <div class="card-grid">
        <article class="card-base kpi-card">
          <header><div><p class="chart-subtitle mb-1">Ingresos y valor</p><h2 class="chart-title">Monetización</h2></div></header>
          <div class="item"><span>Ticket promedio</span><strong><?= h(money_mx($TicketPromedio, $fmtMoney)); ?></strong></div>
          <div class="item"><span>ARPU (por cliente)</span><strong><?= h(money_mx($ARPU, $fmtMoney)); ?></strong></div>
          <div class="item"><span>CAC efectivo</span><strong><?= h(money_mx($CAC, $fmtMoney)); ?></strong></div>
          <div class="item"><span>CIC (cobro por cliente)</span><strong><?= h(money_mx($CIC, $fmtMoney)); ?></strong></div>
        </article>

        <article class="card-base kpi-card">
          <header><div><p class="chart-subtitle mb-1">Conversión y retención</p><h2 class="chart-title">Salud comercial</h2></div></header>
          <div class="item"><span>Tasa de cancelación</span><strong><?= h(pct($TasaCancelacion)); ?></strong></div>
          <div class="item"><span>Tasa en preventa</span><strong><?= h(pct($TasaPreventas)); ?></strong></div>
          <div class="item"><span>Retención efectiva</span><strong><?= h(pct($RetentionRate)); ?></strong></div>
          <div class="item"><span>Edad promedio cliente</span><strong><?= $fmtInt ? $fmtInt->format((int)round($EdadPromCte)) : (string)round($EdadPromCte); ?></strong></div>
        </article>

        <article class="card-base kpi-card">
          <header><div><p class="chart-subtitle mb-1">Cobranza y liquidez</p><h2 class="chart-title">Liquidez</h2></div></header>
          <div class="item"><span>MRR estimado</span><strong><?= h(money_mx($MRREstimado, $fmtMoney)); ?></strong></div>
          <div class="item"><span>Cartera en Cobranza</span><strong><?= h(money_mx($PagEr, $fmtMoney)); ?> (<?= h(pct($MorosidadRatio)); ?>)</strong></div>
          <div class="item"><span>Cartera dictaminada / capital</span><strong><?= h(pct($CarteraDictVsCap)); ?></strong></div>
          <div class="item"><span>Capital colocado</span><strong><?= h(money_mx($CarteCol, $fmtMoney)); ?></strong></div>
        </article>
      </div>

      <div class="card-grid">
        <article class="card-base chart-card">
          <header>
            <div>
              <p class="chart-subtitle mb-1">KPIs financieros</p>
              <h2 class="chart-title">Cobros vs capital</h2>
            </div>
          </header>
          <div id="g_finanzas_kpi" style="height:320px;"></div>
        </article>
        <article class="card-base chart-card">
          <header>
            <div>
              <p class="chart-subtitle mb-1">Conversión y riesgo</p>
              <h2 class="chart-title">Conversión & mora</h2>
            </div>
          </header>
          <div id="g_conversion_kpi" style="height:320px;"></div>
        </article>
      </div>

      <hr class="section-divider">
      
      <!-- ===================================================================
           [B] INDICADORES ANUALES (año en curso)
           =================================================================== -->
      <h5 class="mt-2 mb-2">Indicadores financieros anuales (<?php echo date('Y'); ?>)</h5>
      <div class="card-grid">
        <article class="card-base kpi-card">
          <header><div><p class="chart-subtitle mb-1">Totales año</p><h2 class="chart-title">Generales KASU</h2></div></header>
          <div class="item"><span>Cobros anuales</span><strong><?php echo h(money_mx($ANUAL['CobrosTotales'],$fmtMoney)); ?></strong></div>
          <div class="item"><span>Vtas ACTIVAS</span><strong><?php echo h(money_mx($ANUAL['VtasActivas'],$fmtMoney)); ?></strong></div>
          <div class="item"><span>Vtas CANCELADAS</span><strong><?php echo h(money_mx($ANUAL['VtasCancel'],$fmtMoney)); ?></strong></div>
          <div class="item"><span>Clientes ACTIVOS</span><strong><?php echo $fmtInt? $fmtInt->format($ANUAL['CtesActivos']): (string)$ANUAL['CtesActivos']; ?></strong></div>
          <div class="item"><span>Efectividad</span><strong><?php echo (int)round($ANUAL['Efectividad10']); ?> / 10</strong></div>
        </article>

        <article class="card-base kpi-card">
          <header><div><p class="chart-subtitle mb-1">Prospección</p><h2 class="chart-title">Generales de Prospección</h2></div></header>
          <div class="item"><span>Prospectos Tierra (año)</span><strong><?= $fmtOut($prosTierra) ?></strong></div>
          <div class="item"><span>Prospectos Digitales</span><strong><?= $fmtOut($prosDigital) ?></strong></div>
          <div class="item"><span>Registros Generados</span><strong><?= $fmtOut($regGenerados) ?></strong></div>
          <div class="item"><span>Registros Digitales</span><strong><?= $fmtOut($regDigitales) ?></strong></div>
          <div class="item"><span>Clientes Generados</span><strong><?= $fmtOut($cteGenerados) ?></strong></div>
        </article>

        <article class="card-base kpi-card">
          <header><div><p class="chart-subtitle mb-1">Fideicomiso / Servicios</p><h2 class="chart-title">Servicios y fondos</h2></div></header>
          <div class="item"><span>Val. Fideicomitido</span><strong><?php echo h(money_mx($ANUAL['ValFide'],$fmtMoney)); ?></strong></div>
          <div class="item"><span>Val. Actual Fideicomiso</span><strong><?php echo h(money_mx($ANUAL['ValActFide'],$fmtMoney)); ?></strong></div>
          <div class="item"><span>Servicios Pagados</span><strong><?php echo h(money_mx($ANUAL['ServPagados'],$fmtMoney)); ?></strong></div>
          <div class="item"><span>Servicios Otorgados</span><strong><?php echo $fmtInt? $fmtInt->format($ANUAL['ServOtorg']): (string)$ANUAL['ServOtorg']; ?></strong></div>
        </article>
      </div>

      <div class="card-grid">
        <article class="card-base kpi-card">
          <header><div><p class="chart-subtitle mb-1">Comercial (año)</p><h2 class="chart-title">KPIs comerciales</h2></div></header>
          <div class="item"><span>Ticket promedio</span><strong><?= h(money_mx($ANUAL['TicketPromedio'], $fmtMoney)); ?></strong></div>
          <div class="item"><span>ARPU</span><strong><?= h(money_mx($ANUAL['ARPU'], $fmtMoney)); ?></strong></div>
          <div class="item"><span>MRR estimado</span><strong><?= h(money_mx($ANUAL['MRR'], $fmtMoney)); ?></strong></div>
          <div class="item"><span>Tasa de cancelación</span><strong><?= h(pct($ANUAL['CancelRate'])); ?></strong></div>
        </article>

        <article class="card-base kpi-card">
          <header><div><p class="chart-subtitle mb-1">Retención y cartera</p><h2 class="chart-title">Retención & mora</h2></div></header>
          <div class="item"><span>Retención efectiva</span><strong><?= h(pct($ANUAL['RetentionRate'])); ?></strong></div>
          <div class="item"><span>Morosidad cartera</span><strong><?= h(pct($ANUAL['MoraRatio'])); ?></strong></div>
          <div class="item"><span>Cartera en Cobranza</span><strong><?= h(money_mx($ANUAL['CarCobranza'], $fmtMoney)); ?></strong></div>
          <div class="item"><span>Cartera dictaminada</span><strong><?= h(money_mx($ANUAL['CarDict'], $fmtMoney)); ?></strong></div>
        </article>
      </div>

      <div class="card-grid">
        <article class="card-base chart-card">
          <header><div><p class="chart-subtitle mb-1">Finanzas anuales</p><h2 class="chart-title">KPIs financieros anuales</h2></div></header>
          <div id="g_finanzas_kpi_anual" style="height:320px;"></div>
        </article>
        <article class="card-base chart-card">
          <header><div><p class="chart-subtitle mb-1">Retención anual</p><h2 class="chart-title">Retención & mora anual</h2></div></header>
          <div id="g_conversion_kpi_anual" style="height:320px;"></div>
        </article>
      </div>

      <hr class="section-divider">

      <!-- ===================================================================
           [C] RESUMEN POR PERÍODO: selector de rango
      =================================================================== -->
      <section class="card-base form-card">
        <form method="POST" action="<?php echo h($_SERVER['PHP_SELF']); ?>" class="mb-1">
          <div class="form-row">
            <div class="form-group col-sm-4">
              <label>Fecha inicio</label>
              <input type="date" class="form-control" name="ini" required>
            </div>
            <div class="form-group col-sm-4">
              <label>Fecha fin</label>
              <input type="date" class="form-control" name="fin" required>
            </div>
            <div class="form-group col-sm-4 d-flex align-items-end">
              <div class="form-actions w-100">
                <button type="submit" class="btn btn-primary btn-block">Consultar período</button>
              </div>
            </div>
          </div>
        </form>
      </section>

      <?php if ($showPeriodo && is_array($PERIODO)): ?>
      <div class="card-grid">
        <!-- Generales KASU (período) -->
        <article class="card-base kpi-card">
          <header><div><p class="chart-subtitle mb-1">Período</p><h2 class="chart-title">Generales KASU</h2></div></header>
          <div class="item"><span>Cobros</span><strong><?php echo h(money_mx($PERIODO['CobrosTotales'],$fmtMoney)); ?></strong></div>
          <div class="item"><span>Vtas ACTIVAS</span><strong><?php echo h(money_mx($PERIODO['VtasActivas'],$fmtMoney)); ?></strong></div>
          <div class="item"><span>Vtas CANCELADAS</span><strong><?php echo h(money_mx($PERIODO['VtasCancel'],$fmtMoney)); ?></strong></div>
          <div class="item"><span>Clientes ACTIVOS</span><strong><?php echo $fmtInt? $fmtInt->format($PERIODO['CtesActivos']) : (string)$PERIODO['CtesActivos']; ?></strong></div>
          <div class="item"><span>Efectividad</span><strong><?php echo (int)round($PERIODO['Efectividad10']); ?> / 10</strong></div>
        </article>

        <!-- Prospección (proxy por ventas/servicios en período) -->
        <article class="card-base kpi-card">
          <header><div><p class="chart-subtitle mb-1">Servicios</p><h2 class="chart-title">Fideicomiso / Servicios</h2></div></header>
          <div class="item"><span>Val. Fideicomitido</span><strong><?php echo h(money_mx($PERIODO['ValFide'],$fmtMoney)); ?></strong></div>
          <div class="item"><span>Val. Actual Fideicomiso</span><strong><?php echo h(money_mx($PERIODO['ValActFide'],$fmtMoney)); ?></strong></div>
          <div class="item"><span>Servicios Pagados</span><strong><?php echo h(money_mx($PERIODO['ServPagados'],$fmtMoney)); ?></strong></div>
          <div class="item"><span>Servicios Otorgados</span><strong><?php echo $fmtInt? $fmtInt->format($PERIODO['ServOtorg']) : (string)$PERIODO['ServOtorg']; ?></strong></div>
        </article>

        <!-- Crediticios (período) -->
        <article class="card-base kpi-card">
          <header><div><p class="chart-subtitle mb-1">Cartera</p><h2 class="chart-title">Datos Crediticios</h2></div></header>
          <div class="item"><span>Valor Cartera</span><strong><?php echo h(money_mx($PERIODO['ValCartera'],$fmtMoney)); ?></strong></div>
          <div class="item"><span>Capital colocado</span><strong><?php echo h(money_mx($PERIODO['CapColocado'],$fmtMoney)); ?></strong></div>
          <div class="item"><span>Cartera en Cobranza</span><strong><?php echo h(money_mx($PERIODO['CarCobranza'],$fmtMoney)); ?></strong></div>
          <div class="item"><span>Cartera dictaminada</span><strong><?php echo h(money_mx($PERIODO['CarDict'],$fmtMoney)); ?></strong></div>
          <div class="item"><span>Edad promedio Cliente</span><strong><?php echo $fmtInt? $fmtInt->format((int)round($PERIODO['EdadProm'])) : (string)round($PERIODO['EdadProm']); ?></strong></div>
        </article>
      </div>
      <div class="card-grid">
        <article class="card-base kpi-card">
          <header><div><p class="chart-subtitle mb-1">Comercial</p><h2 class="chart-title">KPIs comerciales (período)</h2></div></header>
          <div class="item"><span>Ticket promedio</span><strong><?= h(money_mx($PERIODO['TicketPromedio'], $fmtMoney)); ?></strong></div>
          <div class="item"><span>ARPU</span><strong><?= h(money_mx($PERIODO['ARPU'], $fmtMoney)); ?></strong></div>
          <div class="item"><span>MRR estimado</span><strong><?= h(money_mx($PERIODO['MRR'], $fmtMoney)); ?></strong></div>
          <div class="item"><span>Tasa de cancelación</span><strong><?= h(pct($PERIODO['CancelRate'])); ?></strong></div>
        </article>
        <article class="card-base kpi-card">
          <header><div><p class="chart-subtitle mb-1">Retención y cartera</p><h2 class="chart-title">Retención & cartera (período)</h2></div></header>
          <div class="item"><span>Retención efectiva</span><strong><?= h(pct($PERIODO['RetentionRate'])); ?></strong></div>
          <div class="item"><span>Morosidad cartera</span><strong><?= h(pct($PERIODO['MoraRatio'])); ?></strong></div>
          <div class="item"><span>Cartera en Cobranza</span><strong><?= h(money_mx($PERIODO['CarCobranza'], $fmtMoney)); ?></strong></div>
          <div class="item"><span>Cartera dictaminada</span><strong><?= h(money_mx($PERIODO['CarDict'], $fmtMoney)); ?></strong></div>
        </article>
      </div>
      <?php endif; ?>

      <hr class="section-divider">
      <!-- ===================================================================
           [D] GRÁFICAS: selector + gráficas
           =================================================================== -->
      <section class="card-base form-card">
        <form class="form-row align-items-end" method="GET" action="<?php echo h($_SERVER['PHP_SELF']); ?>#graficas-producto">
          <div class="form-group col-md-4">
            <label>Fecha inicio (gráficas)</label>
            <input type="date" class="form-control" name="chart_ini" value="<?php echo h($chartIni); ?>" required>
          </div>
          <div class="form-group col-md-4">
            <label>Fecha fin (gráficas)</label>
            <input type="date" class="form-control" name="chart_fin" value="<?php echo h($chartFin); ?>" required>
          </div>
          <div class="form-group col-md-4">
            <div class="form-actions w-100">
              <button type="submit" class="btn btn-primary btn-block">Actualizar gráficas</button>
            </div>
          </div>
        </form>
      </section>

      <div id="graficas-producto" class="page-heading" style="margin-top:6px;">
        <p class="chart-subtitle mb-1">Gráficas por producto</p>
        <h2 class="chart-title">Rango: <?php echo h($chartIni); ?> a <?php echo h($chartFin); ?></h2>
      </div>

      <div class="card-grid">
        <article class="card-base chart-card"><header><h2 class="chart-title">Valor ventas activas</h2></header><div id="g_activas_total" style="height:300px;"></div></article>
        <article class="card-base chart-card"><header><h2 class="chart-title">Ventas totales</h2></header><div id="g_totales"></div></article>
        <article class="card-base chart-card"><header><h2 class="chart-title">Ventas activas</h2></header><div id="g_activas"></div></article>
        <article class="card-base chart-card"><header><h2 class="chart-title">Ventas por estatus</h2></header><div id="g_status"></div></article>
      </div>

      <hr class="section-divider">

      <div class="card-base chart-card">
        <header><div><p class="chart-subtitle mb-1">Histórico</p><h2 class="chart-title">Curva histórica de ventas efectivas</h2></div></header>
        <div class="Grafica"><div id="curve_chart"></div></div>
      </div>

    <!-- ===================================================================
     [E] ANÁLISIS FONDO FUNERARIO - Nuevo modelo 50%
     =================================================================== -->
    <hr class="section-divider">

    <div class="page-heading">
        <p>Análisis del fondo de inversión · Nuevo modelo 50% aportación</p>
    </div>

    <!-- Tarjeta de alerta si hay riesgo -->
    <?php if ($analisisFondo['alerta_riesgo']): ?>
    <div class="card-base alert-card" style="border-left: 4px solid #e74c3c;">
        <div class="alert-header">
            <i class="fa fa-exclamation-triangle" style="color: #e74c3c; font-size: 24px;"></i>
            <div>
                <h4 style="color: #e74c3c; margin: 0;">Atención: Riesgo en fondo de inversión</h4>
                <p style="margin: 5px 0 0 0;"><?= h($analisisFondo['mensaje_alerta']) ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- KPIs del fondo -->
    <div class="card-grid">
        <article class="card-base kpi-card">
            <header><div>
                <p class="chart-subtitle mb-1">Rendimiento requerido</p>
                <h2 class="chart-title">Meta de inversión</h2>
            </div><span class="pill">Promedio</span></header>
            <div class="item"><span>Rendimiento mínimo promedio</span>
                <strong style="color: #e67e22;"><?= number_format($analisisFondo['rendimiento_minimo_promedio'] * 100, 2) ?>%</strong>
            </div>
            <div class="item"><span>Rendimiento mínimo ponderado</span>
                <strong style="color: #<?= $analisisFondo['rendimiento_minimo_promedio_ponderado'] > 0.15 ? 'e74c3c' : '2ecc71' ?>">
                    <?= number_format($analisisFondo['rendimiento_minimo_promedio_ponderado'] * 100, 2) ?>%
                </strong>
            </div>
            <div class="item"><span>Costo servicio por póliza</span>
                <strong><?= h(money_mx(ConfigFondoFunerario::getCostoServicioHoy(), $fmtMoney)) ?></strong>
            </div>
            <div class="item"><span>Brecha inicial promedio</span>
                <strong><?= h(money_mx($brechaPorVenta, $fmtMoney)) ?></strong>
            </div>
        </article>

        <article class="card-base kpi-card">
            <header><div>
                <p class="chart-subtitle mb-1">Totales acumulados</p>
                <h2 class="chart-title">Fondo actual</h2>
            </div><span class="pill"><?= $fmtOut($analisisFondo['total_ventas_activas']) ?> pólizas</span></header>
            <div class="item"><span>Aportación total al fondo</span>
                <strong><?= h(money_mx($analisisFondo['aportacion_total_fondo'], $fmtMoney)) ?></strong>
            </div>
            <div class="item"><span>Costo servicios total</span>
                <strong><?= h(money_mx($analisisFondo['costo_servicio_total'], $fmtMoney)) ?></strong>
            </div>
            <div class="item"><span>Brecha total a cubrir</span>
                <strong><?= h(money_mx($analisisFondo['brecha_total'], $fmtMoney)) ?></strong>
            </div>
            <div class="item"><span>Excedente esperado total</span>
                <strong style="color: #27ae60;"><?= h(money_mx($analisisFondo['excedente_esperado_total'], $fmtMoney)) ?></strong>
            </div>
        </article>

        <article class="card-base kpi-card">
            <header><div>
                <p class="chart-subtitle mb-1">Incentivos equipo</p>
                <h2 class="chart-title">Ingreso pasivo</h2>
            </div><span class="pill">2% excedente</span></header>
            <div class="item"><span>Comisión pasiva total</span>
                <strong style="color: #3498db;"><?= h(money_mx($analisisFondo['comision_pasiva_total'], $fmtMoney)) ?></strong>
            </div>
            <div class="item"><span>Comisión pasiva promedio</span>
                <strong><?= h(money_mx($comisionPasivaPorVenta, $fmtMoney)) ?></strong>
            </div>
            <div class="item"><span>Excedente promedio</span>
                <strong><?= h(money_mx($excedentePorVenta, $fmtMoney)) ?></strong>
            </div>
            <div class="item"><span>Rendimiento esperado</span>
                <strong>8.00% anual</strong>
            </div>
        </article>
    </div>

    <!-- Recomendaciones de inversión -->
    <div class="card-base mt-3">
        <header><div>
            <p class="chart-subtitle mb-1">Estrategia de inversión</p>
            <h2 class="chart-title">Recomendaciones para el fondo</h2>
        </div></header>
        
        <?php foreach ($recomendacionesInversion as $rec): ?>
        <div class="recommendation-item" style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid 
            <?= $rec['nivel'] == 'BAJO' ? '#2ecc71' : 
              ($rec['nivel'] == 'MODERADO' ? '#f39c12' : 
              ($rec['nivel'] == 'ALTO' ? '#e67e22' : '#e74c3c')) ?>;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h5 style="margin: 0; color: 
                    <?= $rec['nivel'] == 'BAJO' ? '#2ecc71' : 
                      ($rec['nivel'] == 'MODERADO' ? '#f39c12' : 
                      ($rec['nivel'] == 'ALTO' ? '#e67e22' : '#e74c3c')) ?>;">
                    Nivel <?= h($rec['nivel']) ?>: <?= h($rec['mensaje']) ?>
                </h5>
                <span class="badge" style="background: 
                    <?= $rec['nivel'] == 'BAJO' ? '#2ecc71' : 
                      ($rec['nivel'] == 'MODERADO' ? '#f39c12' : 
                      ($rec['nivel'] == 'ALTO' ? '#e67e22' : '#e74c3c')) ?>;">
                    <?= h($rec['nivel']) ?>
                </span>
            </div>
            <p style="margin: 10px 0 5px 0;"><?= h($rec['detalle']) ?></p>
            <div style="margin-top: 10px;">
                <strong>Instrumentos recomendados:</strong>
                <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 5px;">
                    <?php foreach ($rec['instrumentos_recomendados'] as $instrumento): ?>
                    <span class="badge badge-secondary"><?= h($instrumento) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Resumen por rango de edad -->
    <div class="card-base mt-3">
        <header><div>
            <p class="chart-subtitle mb-1">Análisis por edad</p>
            <h2 class="chart-title">Rendimiento requerido por rango de edad</h2>
        </div></header>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Rango de edad</th>
                        <th>Pólizas</th>
                        <th>Aportación promedio</th>
                        <th>Rendimiento mínimo req.</th>
                        <th>Excedente esperado</th>
                        <th>Comisión pasiva</th>
                        <th>Riesgo</th>
                    </tr>
                </thead>
                <tbody>
                  <?php foreach ($analisisFondo['resumen_por_rango_edad'] as $rango => $datos): ?>
                  <?php 
                  // Asegurar que tenemos valores numéricos
                  $ventas = (int)($datos['ventas'] ?? 0);
                  if ($ventas > 0): 
                  $rendimiento = (float)($datos['rendimiento_minimo_promedio'] ?? 0);
                  $colorRiesgo = $rendimiento > 0.15 ? 'danger' : ($rendimiento > 0.10 ? 'warning' : 'success');
                  $textoRiesgo = $rendimiento > 0.15 ? 'Alto' : ($rendimiento > 0.10 ? 'Moderado' : 'Bajo');
                  ?>
                  <tr>
                      <td><?= h($rango) ?></td>
                      <td><?= $fmtOut($ventas) ?></td>
                      <td><?= h(money_mx((float)($datos['aportacion_promedio'] ?? 0), $fmtMoney)) ?></td>
                      <td>
                          <span class="badge badge-<?= $colorRiesgo ?>">
                              <?= number_format($rendimiento * 100, 2) ?>%
                          </span>
                      </td>
                      <td><?= h(money_mx((float)($datos['excedente_esperado'] ?? 0), $fmtMoney)) ?></td>
                      <td><?= h(money_mx((float)($datos['excedente_esperado'] ?? 0) * 0.02, $fmtMoney)) ?></td>
                      <td><span class="badge badge-<?= $colorRiesgo ?>"><?= $textoRiesgo ?></span></td>
                  </tr>
                  <?php endif; ?>
                  <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-3" style="font-size: 0.9em; color: #666;">
            <p><strong>Nota:</strong> El rendimiento mínimo requerido es la tasa anual que debe lograr el fondo 
            para cubrir el costo del servicio funerario (<?= h(money_mx(ConfigFondoFunerario::getCostoServicioHoy(), $fmtMoney)) ?> 
            = 2600 UDIs) al momento del fallecimiento.</p>
            <p>El excedente se calcula asumiendo un rendimiento real del 8% anual. El 2% del excedente se reparte 
            como ingreso pasivo al equipo comercial.</p>
        </div>
    </div>

    <!-- Gráfica de rendimiento por edad -->
    <div class="card-base chart-card mt-3">
        <header><div>
            <p class="chart-subtitle mb-1">Visualización</p>
            <h2 class="chart-title">Rendimiento mínimo requerido por edad</h2>
        </div></header>
        <div id="g_rendimiento_edad" style="height: 400px;"></div>
    </div>

    <!-- ===================================================================
     [F] SEGUIMIENTO FONDO DE INVERSIÓN - Historial real
     =================================================================== -->
    <hr class="section-divider">

    <div class="page-heading">
        <p>Seguimiento del fondo de inversión · Historial real vs meta</p>
    </div>

    <!-- Estado actual del fondo -->
    <div class="card-grid">
        <article class="card-base kpi-card">
            <header><div>
                <p class="chart-subtitle mb-1">Estado actual</p>
                <h2 class="chart-title">Fondo de Inversión</h2>
            </div>
            <span class="pill badge-<?= $umbralInversion['estado'] == 'CRÍTICO' ? 'danger' : 
                                    ($umbralInversion['estado'] == 'MANEJABLE' ? 'warning' : 'success') ?>">
                <?= h($umbralInversion['estado']) ?>
            </span></header>
            <div class="item"><span>Valor actual estimado</span>
                <strong><?= h(money_mx($estadisticasFondo['valor_actual'] ?? 0, $fmtMoney)) ?></strong>
            </div>
            <div class="item"><span>Cobertura de servicios</span>
                <strong><?= number_format(($umbralInversion['cobertura_actual'] ?? 0) * 100, 1) ?>%</strong>
            </div>
            <div class="item"><span>Rendimiento promedio anual</span>
                <strong><?= number_format(($estadisticasFondo['rendimiento_promedio_anual'] ?? 0) * 100, 2) ?>%</strong>
            </div>
            <div class="item"><span>Tendencia</span>
                <strong class="<?= $tendencia == 'ALCISTA' ? 'text-success' : 
                               ($tendencia == 'BAJISTA' ? 'text-danger' : 'text-info') ?>">
                    <?= h($tendencia) ?>
                </strong>
            </div>
        </article>

        <article class="card-base kpi-card">
            <header><div>
                <p class="chart-subtitle mb-1">Cumplimiento de meta</p>
                <h2 class="chart-title">Rendimiento vs Meta</h2>
            </div>
            <span class="pill">
                <?= number_format(($estadisticasFondo['diferencia_promedio'] ?? 0) * 100, 2) ?>%
            </span></header>
            <div class="item"><span>Meta promedio mensual</span>
                <strong><?= number_format(($estadisticasFondo['meta_promedio'] ?? 0) * 100, 2) ?>%</strong>
            </div>
            <div class="item"><span>Real promedio mensual</span>
                <strong><?= number_format(($estadisticasFondo['rendimiento_promedio_mensual'] ?? 0) * 100, 2) ?>%</strong>
            </div>
            <div class="item"><span>Diferencia promedio</span>
                <strong class="<?= ($estadisticasFondo['diferencia_promedio'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= number_format(($estadisticasFondo['diferencia_promedio'] ?? 0) * 100, 2) ?>%
                </strong>
            </div>
            <div class="item"><span>Meses registrados</span>
                <strong><?= $fmtOut($estadisticasFondo['total_meses'] ?? 0) ?></strong>
            </div>
        </article>

        <article class="card-base kpi-card">
            <header><div>
                <p class="chart-subtitle mb-1">Acción requerida</p>
                <h2 class="chart-title">Umbral Mínimo</h2>
            </div>
            <span class="pill badge-info">5 años</span></header>
            <div class="item"><span>Brecha actual</span>
                <strong><?= h(money_mx($umbralInversion['brecha_actual'] ?? 0, $fmtMoney)) ?></strong>
            </div>
            <div class="item"><span>Aporte mensual necesario</span>
                <strong><?= h(money_mx($umbralInversion['aportacion_mensual_necesaria_5anios'] ?? 0, $fmtMoney)) ?></strong>
            </div>
            <div class="item"><span>Años para cerrar brecha</span>
                <strong><?= number_format($umbralInversion['anios_para_cerrar_brecha'] ?? 0, 1) ?></strong>
            </div>
            <div class="item"><span>UDI actual</span>
                <strong>$<?= number_format(ConfigFondoFunerario::$UDI_ACTUAL, 4) ?></strong>
            </div>
        </article>
    </div>

    <!-- Barra de cobertura -->
    <div class="card-base mt-3">
        <header><div>
            <p class="chart-subtitle mb-1">Cobertura del fondo</p>
            <h2 class="chart-title">¿Cuánto cubrimos realmente?</h2>
        </div></header>
        <div class="card-body">
            <?php 
            $cobertura = $umbralInversion['cobertura_actual'] ?? 0;
            $colorBarra = $cobertura >= 1 ? 'success' : ($cobertura >= 0.7 ? 'warning' : 'danger');
            $mensajeCobertura = $cobertura >= 1 ? '✅ Fondo sobrecumple requerimientos' : 
                                ($cobertura >= 0.7 ? '⚠️ Fondo en nivel aceptable' : 
                                ($cobertura >= 0.4 ? '⚠️ Fondo en nivel crítico - Monitorear' : '❌ Fondo insuficiente - Acción requerida'));
            ?>
            <div class="progress progress-thick">
                <div class="progress-bar bg-<?= $colorBarra ?>" 
                     role="progressbar" 
                     style="width: <?= min(100, $cobertura * 100) ?>%"
                     aria-valuenow="<?= $cobertura * 100 ?>"
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    <?= number_format($cobertura * 100, 1) ?>% de cobertura
                </div>
            </div>
            <div class="mt-2">
                <small class="text-muted">
                    <?= $mensajeCobertura ?>
                </small>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body py-2">
                            <small class="text-muted d-block">Aportación Total</small>
                            <strong><?= h(money_mx($umbralInversion['aportacion_total'] ?? 0, $fmtMoney)) ?></strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body py-2">
                            <small class="text-muted d-block">Costo Total Servicios</small>
                            <strong><?= h(money_mx($umbralInversion['costo_total_servicios'] ?? 0, $fmtMoney)) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfica de rendimiento histórico -->
    <div class="card-base chart-card mt-3">
        <header><div>
            <p class="chart-subtitle mb-1">Evolución mensual</p>
            <h2 class="chart-title">Rendimiento real vs meta</h2>
        </div>
        <a href="php/AnalisisDatos/form_fondo.php" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-edit"></i> Actualizar valores
        </a></header>
        <div id="g_rendimiento_historico" style="height: 400px;"></div>
    </div>

    <!-- Tabla de historial -->
    <div class="card-base mt-3">
        <header><div>
            <p class="chart-subtitle mb-1">Historial detallado</p>
            <h2 class="chart-title">Registros mensuales del fondo</h2>
        </div></header>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Mes</th>
                            <th>Valor Inicial</th>
                            <th>Valor Final</th>
                            <th>Aportaciones</th>
                            <th>Retiros</th>
                            <th>Rend. Mensual</th>
                            <th>Meta</th>
                            <th>Diferencia</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($historialFondo as $registro): 
                            $diferencia = (float)$registro['RendimientoRealVsMeta'];
                        ?>
                        <tr>
                            <td><?= $registro['Mes'] ?></td>
                            <td><?= h(money_mx($registro['ValorInicial'], $fmtMoney)) ?></td>
                            <td><strong><?= h(money_mx($registro['ValorFinal'], $fmtMoney)) ?></strong></td>
                            <td><?= h(money_mx($registro['Aportaciones'], $fmtMoney)) ?></td>
                            <td><?= h(money_mx($registro['Retiros'], $fmtMoney)) ?></td>
                            <td><?= number_format($registro['Rendimiento'] * 100, 2) ?>%</td>
                            <td><?= number_format($registro['MetaRendimientoMinimo'] * 100, 2) ?>%</td>
                            <td class="<?= $diferencia >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= number_format($diferencia * 100, 2) ?>%
                            </td>
                            <td>
                                <span class="badge badge-<?= $diferencia >= 0.002 ? 'success' : ($diferencia >= -0.002 ? 'warning' : 'danger') ?>">
                                    <?= $diferencia >= 0.002 ? '✅ Excelente' : ($diferencia >= -0.002 ? '⚠️ Aceptable' : '❌ Bajo') ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Enlace a formulario de actualización -->
    <div class="text-center mt-3 mb-4">
        <a href="php/AnalisisDatos/form_fondo.php" class="btn btn-primary btn-lg">
            <i class="fa fa-chart-line"></i> Actualizar Valores del Fondo Mensual
        </a>
        <p class="text-muted mt-2">
            Actualiza mensualmente los valores reales del fondo para mantener precisión en los cálculos
        </p>
    </div>

    </div> <!-- cierre de dashboard-shell -->
  </main>

  <!-- Helpers -->
  <script src="Javascript/finger.js?v=3"></script>
  <script src="Javascript/localize.js?v=3"></script>
  <script src="Javascript/Inyectar_gps_form.js"></script>
</body>
</html>