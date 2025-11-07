<?php
// Requiere: $row (Venta), $mysqli, $basicas, $financieras, $fec (opcional)

if (!isset($row) || !is_array($row)) {
  die('Template sin datos ($row).');
}
$basicas = $basicas ?? new Basicas();
$financieras = $financieras ?? new Financieras();

// Fecha inicial
if (!empty($fec)) {
  $Fecha = date("d-m-Y", strtotime($fec));
} else {
  $base = new DateTime($row['FechaRegistro']);   // fecha de registro de la venta
  $dia  = (int)$base->format('j');

  if ($dia <= 15) {
    // estaba en la 1ª quincena → inicia el 16 del mismo mes
    $base->setDate((int)$base->format('Y'), (int)$base->format('n'), 16);
  } else {
    // estaba en la 2ª quincena → inicia el 1 del mes siguiente
    $base->modify('first day of next month');
  }

  $Fecha = $base->format('d-m-Y');
}

// Saldo / pago
$s56  = $financieras->SaldoCredito($mysqli, $row['Id']);
$pago = $financieras->Pago($mysqli, $row['Id']);

// Periodicidad del producto (asegura > 0)
$rf = (int)$basicas->BuscarCampos($mysqli,"Perido","Productos","Producto",$row['Producto']);
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
  <link rel="stylesheet" href="css/fichas.css">
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
            <td class="no-bor">KASU, Servicios A Futuro</td>
            <td class="no-bor"></td>
            <td class="no-bor"></td>
            <td class="no-bor size"><strong class="size-little">FICHA DEPOSITO</strong><br>PROTEGE A QUIEN AMAS</td>
          </tr>
          <tr><td class="grey size" colspan="4">Nombre completo</td></tr>
          <tr><td class="green size" colspan="4"><?= htmlentities($row['Nombre'], ENT_QUOTES, "UTF-8") ?></td></tr>
          <tr>
            <td class="grey size">No de cliente</td>
            <td class="grey size">Tipo de Servicio</td>
            <td class="grey size">No. de pago</td>
            <td class="no-bor size">Efectivo (X)</td>
          </tr>
          <tr>
            <td class="green size"><?= $row['Id'] ?></td>
            <td class="green size"><?= htmlspecialchars($row['TipoServicio']) ?></td>
            <td class="trasp size"><?= $Npg ?></td>
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
            <td><center><img src="img/BBVA.png" width="60" class="img"></center></td>
            <td class="no-bor size">CTA. 0116940382</td>
            <td><center><img src="img/OXXO.png" width="60" class="img"></center></td>
            <td class="no-bor size">4152&nbsp;3140&nbsp;4971&nbsp;7112</td>
          </tr>
          <tr>
            <td class="grey size" width="60">Referencia</td>
            <td class="no-bor size"><?= htmlspecialchars($row['IdFIrma']) ?></td>
            <td class="grey size" width="60">Receptor</td>
            <td class="no-bor size">KASU, SERVICIOS A FUTURO</td>
          </tr>
        </table>
        <hr style="border:1px dotted #000; width:100%" />
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
