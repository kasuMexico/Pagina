<?php
// Requiere: $row (Venta), $mysqli, $basicas, $financieras, $fec (opcional)

if (!isset($row) || !is_array($row)) {
  die('Template sin datos ($row).');
}
$basicas      = $basicas      ?? new Basicas();
$financieras  = $financieras  ?? new Financieras();

// Fecha inicial
if (!empty($fec)) {
  $Fecha = date("d-m-Y", strtotime($fec));
} else {
  $base = new DateTime($row['FechaRegistro']);   // fecha de registro de la venta
  $dia  = (int)$base->format('j');

  if ($dia <= 15) {
    // 1ª quincena → inicia el 16 del mismo mes
    $base->setDate((int)$base->format('Y'), (int)$base->format('n'), 16);
  } else {
    // 2ª quincena → inicia el 1 del mes siguiente
    $base->modify('first day of next month');
  }
  $Fecha = $base->format('d-m-Y');
}

// Saldo / pago
$s56  = $financieras->SaldoCredito($mysqli, $row['Id']);
$pago = $financieras->Pago($mysqli, $row['Id']);

// Periodicidad del producto (asegura > 0)
$rf = (int)$basicas->BuscarCampos($mysqli, "Perido", "Productos", "Producto", $row['Producto']);
if ($rf <= 0) { $rf = 1; }

$a = 1;
$cont = 1;
$Npg = 1;

// Si es contado, una sola ficha y pago = saldo
if ($row['NumeroPagos'] == 0 || $row['NumeroPagos'] == 1) {
  $num  = 1;
  $pago = $s56;
} else {
  $num = (int)$row['NumeroPagos'];
}

// Días entre pagos dentro del mes
$Day1 = max(1, (int)round(30 / $rf, 0, PHP_ROUND_HALF_DOWN));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>FICHA DE DEPOSITO</title>
  <link rel="stylesheet" href="https://kasu.com.mx/login/Generar_PDF/css/fichas.css?v=1">
  <style>
    /* === Overrides anti-encimado, sin tocar tu CSS base === */
    .wrapper{
      width:49%;
      display:inline-block;      /* estable, permite 2 por fila */
      vertical-align:top;
      height:auto;               /* elimina alto fijo */
      margin:4px 0;              /* separación vertical */
      page-break-inside: avoid;  /* no partir fichas en PDF */
    }
    .wrapper .content{
      position:relative; /* contexto propio por si algo queda absolute */
      height:auto;
      margin: 6px 8px;   /* margen interno razonable */
    }
    /* Quitar posicionamientos absolutos que sacan del flujo */
    .encabezado,.nombre,.datos,.referencias{position:static;margin:0 0 6px 0;}
    .encabezado{width:100%;height:auto;}
    .nombre{width:100%;height:auto;text-align:center;}
    .datos,.referencias{width:100%;height:auto;text-align:center;}

    /* Tablas consistentes */
    .encabezado table,.nombre table,.datos table,.referencias table{
      width:100%; table-layout:fixed; border-collapse:collapse;
    }

    hr{border:1px dotted #000; width:100%; margin:6px 0;}
    .img{display:block;}
  </style>
</head>
<body>
<?php
while ($cont <= $num) {

  // Base timestamp de la fecha
  $tsBase = strtotime(str_replace('/', '-', $Fecha));

  // Primer/último día del mes de esa base
  $first = date("d-m-Y", strtotime('first day of this month', $tsBase));
  $last  = date("d-m-Y", strtotime('last day of this month',  $tsBase));

  // Si la fecha base cae antes del último día del mes, usar ese último día; si no, usar primero del mes
  $Fecha = (strtotime($Fecha) <= strtotime($last)) ? $last : $first;

  // Imprime $rf fichas dentro del mes
  $a = 1;
  while ($a <= $rf) {
    $Fecha = date("d-m-Y", strtotime($Fecha . " +{$Day1} days"));
    $tre   = date("d-m-Y", strtotime($Fecha . " +3 days"));
?>
  <div class="wrapper">
    <div class="content">
      <div class="encabezado">
        <table>
          <tr>
            <td class="no-bor size" colspan="4"><strong class="size-little">KASU, Servicios A Futuro</strong> <br> RFC: KSF201022441 <br> Ficha de deposito</td>
          </tr>
          <tr><td class="grey size" colspan="4">Nombre completo</td></tr>
          <tr><td class="green size" colspan="4"><?= htmlentities($row['Nombre'] ?? '', ENT_QUOTES, "UTF-8") ?></td></tr>
          <tr>
            <td class="grey size">No de cliente</td>
            <td class="grey size">Tipo de Servicio</td>
            <td class="grey size">No. de pago</td>
            <td class="no-bor size">Efectivo (X)</td>
          </tr>
          <tr>
            <td class="green size"><?= (int)$row['Id'] ?></td>
            <td class="green size"><?= htmlspecialchars($row['TipoServicio'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td class="trasp size"><?= (int)$Npg ?></td>
            <td class="no-bor size">M.N. (X)</td>
          </tr>
          <tr>
            <td class="grey size">Hasta el:</td>
            <td class="green size"><?= $Fecha ?></td>
            <td class="grey size">Pago por:</td>
            <td class="green size">$&nbsp;<?= number_format((float)$pago, 2) ?></td>
          </tr>
          <tr>
            <td class="grey size">A partir del:</td>
            <td class="trasp size"><?= $tre ?></td>
            <td class="grey size">Pago por:</td>
            <td class="trasp size">$&nbsp;<?= number_format((float)$financieras->Mora($pago), 2) ?></td>
          </tr>
          <tr>
            <td><center><img src="https://kasu.com.mx/login/Generar_PDF/img/BBVA.png" width="60" class="img" alt="BBVA"></center></td>
            <td class="no-bor size">CTA. 0116940382</td>
            <td><center><img src="https://kasu.com.mx/login/Generar_PDF/img/OXXO.png" width="60" class="img" alt="OXXO"></center></td>
            <td class="no-bor size">4152&nbsp;3140&nbsp;4971&nbsp;7112</td>
          </tr>
          <tr>
            <td class="grey size" width="60">Referencia</td>
            <td class="no-bor size"><?= htmlspecialchars($row['IdFIrma'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td class="grey size" width="60">Receptor</td>
            <td class="no-bor size">KASU, SERVICIOS A FUTURO</td>
          </tr>
        </table>
        <hr />
      </div>
    </div>
  </div>
<?php
    $a++;
    $Npg++;
  }
  $cont++;
}
?>
</body>
</html>
