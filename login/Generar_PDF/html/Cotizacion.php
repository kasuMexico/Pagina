<?php
echo '
<html lang="es">
<head>
     <title>Propuesto de Venta</title>
     <link rel="stylesheet" href="css/EstadoCta.css">
</head>
<body>
  <table class="t-h">
      <tr>
          <td>
              <h1 class="ha-text"><strong>KASU, Servicios a Futuro S.A de C.V.  </strong></h1>
              <h2 class="hb-text"> RFC: KSF201022441  WEB: www.kasu.com.mx</h2>
              <p class="hb-text"> Bosque de Chapultepec, Pedregal 24, Molino del Rey, Ciudad de México, CDMX, Mexico C.P. 11000</p>
              <p class="hb-text"> Telefono: '.$tel.' Email: antcliente@kasu.com.mxs</p>
          </td>
      </tr>
  </table>
  <img src="https://kasu.com.mx/assets/poliza/img2/transp.jpg" class="header">
    <div class="container">
       <div class="cardheader">Datos del Cliente</div>
          <div class="cardbody">
              Nombre : '.htmlentities($Prospecto['FullName'], ENT_QUOTES, "UTF-8").' <br>
              Telefono : '.$Prospecto['NoTel'].'<br>
              Email : '.$Prospecto['Email'].'<br>
              Producto : '.$Prospecto['Servicio_Interes'].'<br>
          </div>
        <div class="card">
            <div class="cardheader">En atencion a su solicitud envio la siguiente propuesta de venta</div>
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
                    <tbody>';
                      if(!empty($Propuest['a0a29'])){
                        $Pra0a29 = $basicas->BuscarCampos($mysqli,"Costo","Productos","Producto","02a29");
                        $Pa0a29 = $Propuest['a0a29']*$Pra0a29;
                        echo '
                      <tr>
                        <td>'.date("d-m-Y", strtotime($Propuest['FechaRegistro'])).'</td>
                        <td>Servicio 02 a 29 años</td>
                        <td> '.$Propuest['a0a29'].' </td>
                        <td> $'.number_format($Pra0a29, 2).' </td>
                        <td> $'.number_format($Pa0a29, 2).'</td>
                      </tr>
                      ';
                      }
                      if(!empty($Propuest['a30a49'])){
                        $Pra30a49 = $basicas->BuscarCampos($mysqli,"Costo","Productos","Producto","30a49");
                        $Pa30a49 = $Propuest['a30a49']*$Pra30a49;
                        echo '
                      <tr>
                        <td>'.date("d-m-Y", strtotime($Propuest['FechaRegistro'])).'</td>
                        <td>Servicio 30 a 49 años</td>
                        <td> '.$Propuest['a30a49'].' </td>
                        <td> $'.number_format($Pra30a49, 2).' </td>
                        <td> $'.number_format($Pa30a49, 2).'</td>
                      </tr>
                      ';
                      }
                      if(!empty($Propuest['a50a54'])){
                        $Pra50a54 = $basicas->BuscarCampos($mysqli,"Costo","Productos","Producto","50a54");
                        $Pa50a54 = $Propuest['a50a54']*$Pra50a54;
                        echo '
                      <tr>
                        <td>'.date("d-m-Y", strtotime($Propuest['FechaRegistro'])).'</td>
                        <td>Servicio 50 a 54 años</td>
                        <td> '.$Propuest['a50a54'].' </td>
                        <td> $'.number_format($Pra50a54, 2).' </td>
                        <td> $'.number_format($Pa50a54, 2).'</td>
                      </tr>
                      ';
                      }
                      if(!empty($Propuest['a55a59'])){
                        $Pra55a59 = $basicas->BuscarCampos($mysqli,"Costo","Productos","Producto","55a59");
                        $Pa55a59 = $Propuest['a55a59']*$Pra55a59;
                        echo '
                      <tr>
                        <td>'.date("d-m-Y", strtotime($Propuest['FechaRegistro'])).'</td>
                        <td>Servicio 55 a 59 años</td>
                        <td> '.$Propuest['a55a59'].' </td>
                        <td> $'.number_format($Pra55a59, 2).' </td>
                        <td> $'.number_format($Pa55a59, 2).'</td>
                      </tr>
                      ';
                      }
                      if(!empty($Propuest['a60a64'])){
                        $Pra60a64 = $basicas->BuscarCampos($mysqli,"Costo","Productos","Producto","60a64");
                        $Pa60a64 = $Propuest['a60a64']*$Pra60a64;
                        echo '
                      <tr>
                        <td>'.date("d-m-Y", strtotime($Propuest['FechaRegistro'])).'</td>
                        <td>Servicio 60 a 64 años</td>
                        <td> '.$Propuest['a60a64'].' </td>
                        <td> $'.number_format($Pra60a64, 2).' </td>
                        <td> $'.number_format($Pa60a64, 2).'</td>
                      </tr>
                      ';
                      }
                      if(!empty($Propuest['a65a69'])){
                        $Pra65a69 = $basicas->BuscarCampos($mysqli,"Costo","Productos","Producto","65a69");
                        $Pa65a69 = $Propuest['a65a69']*$Pra65a69;
                        echo '
                      <tr>
                        <td>'.date("d-m-Y", strtotime($Propuest['FechaRegistro'])).'</td>
                        <td>Servicio 65 a 69 años</td>
                        <td> '.$Propuest['a65a69'].' </td>
                        <td> $'.number_format($Pra65a69, 2).' </td>
                        <td> $'.number_format($Pa65a69, 2).'</td>
                      </tr>
                      ';
                      }
                      //SE suman los valores
                      if (!isset($Pa0a29)) $Pa0a29 = 0;
                      if (!isset($Pa30a49)) $Pa30a49 = 0;
                      if (!isset($Pa50a54)) $Pa50a54 = 0;
                      if (!isset($Pa55a59)) $Pa55a59 = 0;
                      if (!isset($Pa60a64)) $Pa60a64 = 0;
                      if (!isset($Pa65a69)) $Pa65a69 = 0;
                      $sal = $Pa0a29 + $Pa30a49 + $Pa50a54 + $Pa55a59 + $Pa60a64 + $Pa65a69;
                      //Se suman las cantidades
                      $Canti = $Propuest['a0a29']+$Propuest['a30a49']+$Propuest['a50a54']+$Propuest['a55a59']+$Propuest['a60a64']+$Propuest['a65a69'];
                      //Si es poliza de ayuntamiento se calcula tasa en 0
                      if($Propuest['plazo'] == 24){
                        //Calculamos la tasa
                        $tasa = 0;
                        //Aplicamos el 15% de Descuento
                        $Deso = $sal/100;
                        $DesCt = $Deso*15;
                        //Cantidad a descontar
                        $sal = $sal-$DesCt;
                      }else{
                        //Calcula la tasa normal
                        $tasa = $basicas->BuscarCampos($mysqli,"TasaAnual","Productos","Producto","02a29");
                      }
                      //Tasa anual se divide en meses
                      $aR=$tasa/12;
                      //tasa entre 100
                      $a=$aR/100;
                      //SE le suma 1
                      $a = 1+$a;
                      //Potencia
                      $sr = pow($a,$Propuest['plazo']);
                      //SAldo Real
                      $saldo = $sal*$sr;
                      //Pago de el periodo
                      $pagm = $saldo/$Propuest['plazo'];
                      echo '
                    </tbody>
                    <tbody>';
                      if($Propuest['plazo'] == 1){
                        echo '
                        <tr>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td><strong>TOTAL</strong></td>
                          <td><strong> $'.number_format($sal, 2).'</strong></td>
                        </tr>
                        ';
                      }else{
                          echo '
                          <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><strong>'.$Propuest['plazo'].' Pagos mensuales de</strong></td>
                            <td><strong>'.number_format($pagm, 2).'</strong></td>
                          </tr>';
                          if (isset($Propuest['Origen']) && $Propuest['Origen'] == "mpio") {
                              echo '
                              <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><strong>Descuento ayuntamiento</strong></td>
                                <td>' . number_format($DesCt, 2) . '</td>
                              </tr>';
                          }
                          echo '
                          <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><strong>TOTAL</strong></td>
                            <td>'.number_format($saldo, 2).'</td>
                          </tr>
                          ';
                      }
                      echo '
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <img src="https://kasu.com.mx/assets/poliza/img2/LINE7.jpg" class="h-line">
    <h2 class="hb-text">Condiciones Comerciales</h2>
    <p class="hb-text"> La presente cotizacion tiene una validez de 60 dias contado a apartir de la fecha de '.date("d M Y").'</p>
    <p class="hb-text"> La presente cotizacion no es transferible y unicamente puede ser ejercida por '.htmlentities($Prospecto['FullName'], ENT_QUOTES, "UTF-8").', en el entendido que la presente cotizacion forma parte de una solucitud realizada por '.htmlentities($Prospecto['FullName'], ENT_QUOTES, "UTF-8").' a KASU, Servicios a Futuro S.A. de C.V.</p>
    <p class="hb-text"> Las condiciones de pago, tales como la forma de pago, plazos, intereses o descuentos, seran pactados entre las partes via contrato de venta.</p>
    <img src="https://kasu.com.mx/assets/poliza/img2/img.jpg" class="fin2">
  </body>
</html>';
?>
