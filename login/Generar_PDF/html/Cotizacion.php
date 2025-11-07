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

<style>
  @page {
  margin-left: 35mm;
  margin-right: 35mm;
  }
  /* Fuentes locales opcionales (TTF en /login/Generar_PDF/fonts/) */
  @font-face{font-family:'Montserrat';font-weight:700;src:url('../fonts/Montserrat-Bold.ttf') format('truetype')}
  @font-face{font-family:'OpenSans'; font-weight:400;src:url('../fonts/OpenSans-Regular.ttf') format('truetype')}
  @font-face{font-family:'OpenSans'; font-weight:600;src:url('../fonts/OpenSans-SemiBold.ttf') format('truetype')}

  html,body{font-family:'OpenSans',sans-serif;font-size:10pt;margin:0;padding:0;color:#111}
  h1,h2{margin:0}
  .ha-text{font-family:'Montserrat',sans-serif;font-weight:700;font-size:13pt}
  .hb-text{font-size:9pt;color:#333}

  /* Header con logo cuadrado sin deformación */
  .header-table{width:100%;border-collapse:collapse;margin:12pt 0 6pt}
  .header-table td{vertical-align:top}
  .logo-cell{width:96px}
  .logo-box{width:96px;height:96px}
  .logo{display:block;max-width:96px;max-height:96px;width:auto;height:auto}

  .cardheader{background:#606a78;color:#fff;padding:8pt 10pt;margin-top:8pt}
  .cardbody{padding:8pt 10pt}
  .table{width:100%;border-collapse:collapse;margin-top:8pt}
  .table th,.table td{border-top:1px solid #ddd;border-bottom:1px solid #ddd;padding:6pt 4pt;text-align:left}
  .right{text-align:right}

  /* Decorativos */
  .line-full{width:100%;height:auto;margin:6pt 0}
  .izquierdo{
  display:block;
  float:left;
  width:20%;
  height:auto;
  margin:6pt 12pt 6pt 0; /* arriba der abajo izq */
}
  .footer-wrap{margin-top:18pt}
</style>
</head>
<body>

<!-- Header con logo cuadrado -->
<table class="header-table">
  <tr>
    <td class="logo-cell">
      <div class="logo-box">
        <!-- Logo cuadrado -->
        <img class="logo" src="https://kasu.com.mx/assets/poliza/img2/transp.jpg" alt="KASU">
      </div>
    </td>
    <td>
      <h1 class="ha-text">KASU, Servicios a Futuro S.A. de C.V.</h1>
      <h2 class="hb-text">RFC: KSF201022441 &nbsp; WEB: www.kasu.com.mx</h2>
      <p class="hb-text">Bosque de Chapultepec, Pedregal 24, Molino del Rey, Ciudad de México, CDMX, C.P. 11000</p>
      <p class="hb-text">Teléfono: <?= h($telEmpresa) ?> &nbsp; Email: antcliente@kasu.com.mx</p>
    </td>
  </tr>
</table>

<!-- Línea superior -->
<img src="https://kasu.com.mx/assets/poliza/img2/LINE7.jpg" class="line-full" alt="">

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
            <th class="right">Cantidad</th>
            <th class="right">Precio U.</th>
            <th class="right">Costo</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rangos as $k=>$base): if($qty[$k]>0): ?>
          <tr>
            <td><?= h(date('d-m-Y', strtotime($fechaReg))) ?></td>
            <td>Servicio <?= h($labels[$base] ?? $base) ?></td>
            <td class="right"><?= h($qty[$k]) ?></td>
            <td class="right">$ <?= number_format($precio[$k], 2) ?></td>
            <td class="right">$ <?= number_format($imp[$k], 2) ?></td>
          </tr>
          <?php endif; endforeach; ?>
        </tbody>
        <tbody>
          <tr>
            <td></td><td></td><td></td>
            <td class="right"><strong>TOTAL</strong></td>
            <td class="right"><strong>$ <?= number_format($sal, 2) ?></strong></td>
          </tr>

          <?php if ($esContado): ?>
          <tr>
            <td></td><td></td><td></td>
            <td class="right"><strong>Pago Único (contado)</strong></td>
            <td class="right"><strong>$ <?= number_format($pagm, 2) ?></strong></td>
          </tr>
          <?php else: ?>
          <tr>
            <td></td><td></td><td></td>
            <td class="right"><strong>Pago Mensual (<?= h($plazoEfectivo) ?> meses)</strong></td>
            <td class="right"><strong>$ <?= number_format($pagm, 2) ?></strong></td>
          </tr>
          <?php if ($plazoEfectivo !== $plazoSolic && $plazoSolic > 1): ?>
          <tr>
            <td colspan="5" class="hb-text">
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

<!-- Observaciones header (decorativo) -->
<div class="container">
  <div class="cardheader">Condiciones Comerciales</div>
  <div class="cardbody">
    <p class="hb-text">La presente cotización tiene una validez de 60 días contados a partir de la fecha de <?= h(date('d M Y')) ?>.</p>
    <p class="hb-text">La presente cotización no es transferible y únicamente puede ser ejercida por <?= h($C['FullName'] ?? '') ?>, en el entendido de que forma parte de una solicitud realizada por <?= h($C['FullName'] ?? '') ?> a KASU, Servicios a Futuro S.A. de C.V.</p>
    <p class="hb-text">Las condiciones de pago, tales como forma de pago, plazos, intereses o descuentos, serán pactadas entre las partes vía contrato de venta.</p>
  </div>
</div>
<!-- Pie con elementos gráficos como en tu muestra -->
<div class="footer-wrap">
  <img src="https://kasu.com.mx/assets/poliza/img2/pagin.jpg" style="width:200px;height:auto" alt="www.kasu.com.mx">
  <img src="https://kasu.com.mx/assets/poliza/img2/trans2.jpg" class="line-full" alt="">
</div>

</body>
</html>
