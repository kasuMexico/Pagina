<?php 
/* Plantilla DOMPDF para Cotización (PHP 8.2 + dompdf 3.x)
   Usa imágenes en /assets/poliza/img2/
   Requiere $Propuest/$Prospecto y $basicas,$mysqli
*/
$P = $Propuest  ?? ($Propuesta ?? []);
$C = $Prospecto ?? ($Reg ?? []);

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$telEmpresa = $tel ?? '55 8851 0571';
$fechaReg   = $P['FechaRegistro'] ?? date('Y-m-d');
$serv       = strtoupper(trim($C['Servicio_Interes'] ?? ''));
$prefix     = ($serv === 'TRANSPORTE') ? 'T' : (($serv === 'SEGURIDAD') ? 'P' : '');

$plazoSolic = (int)($P['Plazo'] ?? $P['plazo'] ?? 1);

$rangos = [
  'a02a29' => '02a29','a30a49' => '30a49','a50a54' => '50a54',
  'a55a59' => '55a59','a60a64' => '60a64','a65a69' => '65a69',
];
$labels = [
  '02a29'=>'02 a 29 años','30a49'=>'30 a 49 años','50a54'=>'50 a 54 años',
  '55a59'=>'55 a 59 años','60a64'=>'60 a 64 años','65a69'=>'65 a 69 años',
];

$qty=[]; foreach($rangos as $k=>$_){ $qty[$k]=(int)($P[$k]??0); }

$precio=[]; $imp=[]; $sal=0.0; $tasas=[]; $maxCreds=[];
foreach ($rangos as $k=>$base){
  if ($qty[$k]<=0){ $precio[$k]=0; $imp[$k]=0; continue; }
  $code = $prefix?($prefix.$base):$base;
  $pu   = (float)$basicas->BuscarCampos($mysqli,'Costo','Productos','Producto',$code);
  $precio[$k]=$pu; $imp[$k]=$qty[$k]*$pu; $sal += $imp[$k];
  $tA   = (float)$basicas->BuscarCampos($mysqli,'TasaAnual','Productos','Producto',$code);
  $mC   = (int)$basicas->BuscarCampos($mysqli,'MaxCredito','Productos','Producto',$code);
  if($tA>0){$tasas[]=$tA;} if($mC>0){$maxCreds[]=$mC;}
}
$tasaAnual = $tasas?max($tasas):0.0;
$plazoMax  = $maxCreds?min($maxCreds):$plazoSolic;

/* Contado si el usuario pide 0 o 1 mes */
$esContado = ($plazoSolic<=1);

/* Plazo efectivo: si es contado, fuerza 1 y no muestres ajuste; si no, limita */
$plazoEfectivo = $esContado ? 1 : min(max(2,$plazoSolic), max(2,$plazoMax));

/* Descuento opcional a 24 meses */
$DesCt = 0.0;
if (!$esContado && $plazoEfectivo===24){ $DesCt=$sal*0.15; $sal-=$DesCt; }

/* Cálculo pagos */
if ($esContado){
  $pagm = $sal;
} else {
  $iMes   = ($tasaAnual/12.0)/100.0;
  $factor = pow(1+$iMes,$plazoEfectivo);
  $saldo  = $sal*$factor;
  $pagm   = $saldo/$plazoEfectivo;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Propuesta de Venta</title>
  <!-- CSS homologado -->
  <link rel="stylesheet" href="https://kasu.com.mx/login/Generar_PDF/css/Cotizacion.css?v=15">
</head>
<body>

<!-- Header con logo cuadrado -->
<table class="doc-header">
  <tr>
    <td class="doc-header__logo-cell">
      <div class="doc-header__logo-box">
        <img src="https://kasu.com.mx/assets/poliza/transp.jpg" class="doc-header__logo" alt="KASU">
      </div>
    </td>
    <td class="doc-header__text">
      <br>
      <h1 class="doc-title">KASU, Servicios a Futuro S.A. de C.V.</h1>
      <h2 class="doc-subtitle">RFC: KSF201022441 &nbsp; WEB: www.kasu.com.mx</h2>
      <p class="u-muted">Bosque de Chapultepec, Pedregal 24, Molino del Rey, Ciudad de México, CDMX, C.P. 11000</p>
      <p class="u-muted">Teléfono: <?= h($telEmpresa) ?> &nbsp; Email: antcliente@kasu.com.mx</p>
    </td>
  </tr>
</table>

<div class="doc-container">
  <div class="doc-section">
    <div class="doc-section__header">Datos del Cliente</div>
    <div class="doc-section__body">
      Nombre: <?= h($C['FullName'] ?? '') ?><br>
      Teléfono: <?= h($C['NoTel'] ?? '') ?><br>
      Email: <?= h($C['Email'] ?? '') ?><br>
      Producto: <?= h($C['Servicio_Interes'] ?? '') ?><br>
    </div>
  </div>

  <div class="doc-section">
    <div class="doc-section__header">En atención a su solicitud, envío la siguiente propuesta de venta</div>
    <div class="doc-section__body">
      <table class="doc-table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Concepto</th>
            <th class="u-right">Cantidad</th>
            <th class="u-right">Precio U.</th>
            <th class="u-right">Costo</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rangos as $k=>$base): if($qty[$k]>0): ?>
          <tr>
            <td><?= h(date('d-m-Y', strtotime($fechaReg))) ?></td>
            <td>Servicio <?= h($labels[$base] ?? $base) ?></td>
            <td class="u-right"><?= h($qty[$k]) ?></td>
            <td class="u-right">$ <?= number_format($precio[$k], 2) ?></td>
            <td class="u-right">$ <?= number_format($imp[$k], 2) ?></td>
          </tr>
          <?php endif; endforeach; ?>
        </tbody>
        <tbody>
          <tr>
            <td></td><td></td><td></td>
            <td class="u-right"><strong>TOTAL</strong></td>
            <td class="u-right"><strong>$ <?= number_format($sal, 2) ?></strong></td>
          </tr>

          <?php if ($esContado): ?>
          <tr>
            <td></td><td></td><td></td>
            <td class="u-right"><strong>Pago Único (contado)</strong></td>
            <td class="u-right"><strong>$ <?= number_format($pagm, 2) ?></strong></td>
          </tr>
          <?php else: ?>
          <tr>
            <td></td><td></td><td></td>
            <td class="u-right"><strong>Pago Mensual (<?= h($plazoEfectivo) ?> meses)</strong></td>
            <td class="u-right"><strong>$ <?= number_format($pagm, 2) ?></strong></td>
          </tr>
          <?php if ($plazoEfectivo !== $plazoSolic && $plazoSolic > 1): ?>
          <tr>
            <td colspan="5" class="doc-subtitle">
              * El plazo solicitado (<?= h($plazoSolic) ?>) fue ajustado a <?= h($plazoEfectivo) ?> meses por límite de crédito del producto seleccionado.
            </td>
          </tr>
          <?php endif; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="doc-container">
  <div class="doc-section">
    <div class="doc-section__header">Condiciones Comerciales</div>
    <div class="doc-section__body">
      <p class="u-muted">La presente cotización tiene una validez de 60 días contados a partir de la fecha de <?= h(date('d M Y')) ?>.</p>
      <p class="u-muted">La presente cotización no es transferible y únicamente puede ser ejercida por <?= h($C['FullName'] ?? '') ?>, en el entendido de que forma parte de una solicitud realizada por <?= h($C['FullName'] ?? '') ?> a KASU, Servicios a Futuro S.A. de C.V.</p>
      <p class="u-muted">Las condiciones de pago, tales como forma de pago, plazos, intereses o descuentos, serán pactadas entre las partes vía contrato de venta.</p>
    </div>
  </div>
</div>

</body>
</html>