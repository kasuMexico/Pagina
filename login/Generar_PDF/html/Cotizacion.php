<?php
/* Plantilla DOMPDF para Cotización
   Requiere:
   - $Propuest (o $Propuesta) con a02a29..a65a69, FechaRegistro, Plazo/plazo
   - $Prospecto (o $Reg) con FullName, NoTel, Email, Servicio_Interes
   - $basicas, $mysqli, $tel disponibles
*/

$P = $Propuest  ?? ($Propuesta ?? []);
$C = $Prospecto ?? ($Reg ?? []);

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$telEmpresa = $tel ?? '55 8851 0571';
$fechaReg   = $P['FechaRegistro'] ?? date('Y-m-d');

// Detecta prefijo por servicio
$serv = strtoupper(trim($C['Servicio_Interes'] ?? ''));
$prefix = ($serv === 'TRANSPORTE') ? 'T' : (($serv === 'SEGURIDAD') ? 'P' : '');

// Plazo solicitado
$plazoSolic = (int)($P['Plazo'] ?? $P['plazo'] ?? 1);

// Rangos base (sin prefijo)
$rangos = [
  'a02a29' => '02a29',
  'a30a49' => '30a49',
  'a50a54' => '50a54',
  'a55a59' => '55a59',
  'a60a64' => '60a64',
  'a65a69' => '65a69',
];

// Cantidades por rango
$qty = [];
foreach ($rangos as $k => $_) {
  $qty[$k] = (int)($P[$k] ?? 0);
}

// Carga precios, tasa y MaxCredito por los códigos realmente usados
$labels = [
  '02a29' => '02 a 29 años',
  '30a49' => '30 a 49 años',
  '50a54' => '50 a 54 años',
  '55a59' => '55 a 59 años',
  '60a64' => '60 a 64 años',
  '65a69' => '65 a 69 años',
];

$precio = [];
$imp    = [];
$sal    = 0.0;
$codigosUsados = [];
$tasas = [];
$maxCreds = [];

foreach ($rangos as $k => $base) {
  if ($qty[$k] <= 0) { $precio[$k] = 0; $imp[$k] = 0; continue; }

  // Código a consultar: si hay prefijo (T o P) se usa SIEMPRE el prefijo.
  $code = $prefix ? ($prefix.$base) : $base;

  // Precio unitario (no hacer fallback al familiar cuando hay prefijo)
  $pu = (float)$basicas->BuscarCampos($mysqli, 'Costo',      'Productos', 'Producto', $code);
  $precio[$k] = $pu;
  $imp[$k]    = $qty[$k] * $pu;
  $sal       += $imp[$k];

  // Para tasa y MaxCredito
  $tAnual   = (float)$basicas->BuscarCampos($mysqli, 'TasaAnual',  'Productos', 'Producto', $code);
  $maxCred  = (int)$basicas->BuscarCampos($mysqli, 'MaxCredito',   'Productos', 'Producto', $code);
  if ($tAnual > 0)   { $tasas[] = $tAnual; }
  if ($maxCred > 0)  { $maxCreds[] = $maxCred; }

  $codigosUsados[] = $code;
}

// Tasa anual: si hay varias, toma la MÁXIMA (conservador)
$tasaAnual = count($tasas) ? max($tasas) : 0.0;

// Plazo efectivo: no puede exceder el MÍNIMO MaxCredito de los productos usados
$plazoMaxPermitido = count($maxCreds) ? min($maxCreds) : $plazoSolic;
$plazoEfectivo = min(max(1, $plazoSolic), $plazoMaxPermitido);

// Descuento especial si 24 meses (opcional, conserva tu lógica)
$DesCt = 0.0;
if ($plazoEfectivo === 24) {
  $DesCt = $sal * 0.15;
  $sal  -= $DesCt;
}

// Cálculo pagos
if ($plazoEfectivo <= 1) {
  $saldo = $sal;
  $pagm  = $sal;
} else {
  $iMes   = ($tasaAnual / 12.0) / 100.0;
  $factor = pow(1 + $iMes, $plazoEfectivo);
  $saldo  = $sal * $factor;
  $pagm   = $saldo / $plazoEfectivo;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Propuesta de Venta</title>
  <link rel="stylesheet" href="https://kasu.com.mx/login/Generar_PDF/css/EstadoCta.css">
</head>
<body>
  <table class="t-h">
    <tr>
      <td>
        <h1 class="ha-text"><strong>KASU, Servicios a Futuro S.A. de C.V.</strong></h1>
        <h2 class="hb-text">RFC: KSF201022441 &nbsp; WEB: www.kasu.com.mx</h2>
        <p class="hb-text">Bosque de Chapultepec, Pedregal 24, Molino del Rey, Ciudad de México, CDMX, C.P. 11000</p>
        <p class="hb-text">Teléfono: <?= h($telEmpresa) ?> &nbsp; Email: antcliente@kasu.com.mx</p>
      </td>
    </tr>
  </table>

  <img src="https://kasu.com.mx/assets/poliza/img2/transp.jpg" class="header">

  <div class="container">
    <div class="cardheader">Datos del Cliente</div>
    <div class="cardbody">
      Nombre: <?= h($C['FullName'] ?? '') ?><br>
      Teléfono: <?= h($C['NoTel'] ?? '') ?><br>
      Email: <?= h($C['Email'] ?? '') ?><br>
      Producto: <?= h($C['Servicio_Interes'] ?? '') ?><br>
    </div>

    <div class="card">
      <div class="cardheader">En atención a su solicitud, envío la siguiente propuesta de venta</div>
      <div class="cardbody">
        <table class="table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Concepto</th>
              <th>Cantidad</th>
              <th>Precio U.</th>
              <th>Costo</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rangos as $k => $base): ?>
              <?php if ($qty[$k] > 0): ?>
                <tr>
                  <td><?= h(date('d-m-Y', strtotime($fechaReg))) ?></td>
                  <td>Servicio <?= h($labels[$base] ?? $base) ?></td>
                  <td><?= h($qty[$k]) ?></td>
                  <td>$ <?= number_format($precio[$k], 2) ?></td>
                  <td>$ <?= number_format($imp[$k], 2) ?></td>
                </tr>
              <?php endif; ?>
            <?php endforeach; ?>
          </tbody>

          <tbody>
            <tr>
              <td></td><td></td><td></td>
              <td><strong>TOTAL</strong></td>
              <td><strong>$ <?= number_format($sal, 2) ?></strong></td>
            </tr>
            <tr>
              <td></td><td></td><td></td>
              <td><strong>Pago Mensual (<?= h($plazoEfectivo) ?> meses)</strong></td>
              <td><strong>$ <?= number_format($pagm, 2) ?></strong></td>
            </tr>
            <?php if ($plazoEfectivo !== $plazoSolic): ?>
              <tr>
                <td colspan="5" class="hb-text">
                  * El plazo solicitado (<?= h($plazoSolic) ?>) fue ajustado a <?= h($plazoEfectivo) ?> meses por límite de crédito del producto seleccionado.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <img src="https://kasu.com.mx/assets/poliza/img2/LINE7.jpg" class="h-line">
  <h2 class="hb-text">Condiciones Comerciales</h2>
  <p class="hb-text">La presente cotización tiene una validez de 60 días contados a partir de la fecha de <?= h(date('d M Y')) ?>.</p>
  <p class="hb-text">La presente cotización no es transferible y únicamente puede ser ejercida por <?= h($C['FullName'] ?? '') ?>, en el entendido de que forma parte de una solicitud realizada por <?= h($C['FullName'] ?? '') ?> a KASU, Servicios a Futuro S.A. de C.V.</p>
  <p class="hb-text">Las condiciones de pago, tales como forma de pago, plazos, intereses o descuentos, serán pactadas entre las partes vía contrato de venta.</p>
  <img src="https://kasu.com.mx/assets/poliza/img2/img.jpg" class="fin2">
</body>
</html>
