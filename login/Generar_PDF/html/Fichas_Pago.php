<?
//creamos una variable general para las funciones
$basicas = new Basicas();
$financieras = new Financieras();
//SI no tiene promesa de primer pago asigna la venta mas 15 dias
if(empty($fec)){
    $Fecha = date( "Y-m-d",strtotime($row['FechaRegistro']));
}else{
    // promesa de primer pago
    $Fecha = date( "d-m-Y",strtotime($fec));
}
//Si el saldo es mayor al costo de compra se usa el pago extemporaneo
$s56 = $financieras->SaldoCredito($mysqli,$row['Id']);
//Se obtiene el monto de la deuda
$pago = $financieras->Pago($mysqli,$row['Id']);
//Variable principal
$a = 1;
$cont = 1;
$Npg = 1;
//Validacion si el cliente va a dar un pago o dos pagos
if($row['NumeroPagos'] == 0  || $row['NumeroPagos'] == 1){
    // numero de pagos
    $num = 1;
    //Si el pago es de contado se imprime el saldo de el credito
    $pago = $s56;
    $a = $basicas->BuscarCampos($mysqli,"Perido","Productos","Producto",$row['Producto']);;
}else{
    // Numero de pagos multiplicado para que simule las quincenas
    $num	= $row['NumeroPagos'];
}
//se imprimen las fichas dos veces por q es la periodicidad
$rf = $basicas->BuscarCampos($mysqli,"Perido","Productos","Producto",$row['Producto']);
//Se calculan los dias a sumar para los Pagos
$Day = 30/$rf;
$Day1 = round($Day, 0, PHP_ROUND_HALF_DOWN);
//Ciclos a realizos por numeros de pagos
while($cont <= $num){
      //creamos la primer fecha o ultima del mes
      $fist = date("d-m-Y",strtotime('first day of this month'.$Fecha));
      $last = date("d-m-Y",strtotime('last day of this month'.$Fecha));
      //comparamos si el cliente ya paso la fecha o es futura
      if($Fecha <= $last){
          $Fecha = $last;
      }else{
          $Fecha = $fist;
      }
    //While para la impresion de las fichas dentro de un solo mes
    while ($a <= $rf) {
        $Fecha = date("d-m-Y",strtotime($Fecha."+".$Day1." days"));
        // Aumento 3 dias a la primer fecha
        $tre = date("d-m-Y",strtotime($Fecha."+3 days"));
        ?>
        <html lang='es'>
        <head>
          <title>FICHA DE DEPOSITO</title>
          <link rel="stylesheet" href="css/fichas.css">
        </head>
        <body>
          <div class='wrapper'>
            <div class="content">
              <div class="encabezado ">
                <table>
                  <tr>
                    <td class="no-bor">KASU, Servicios A Futuro</td>
                    <td class="no-bor"></td>
                    <td class="no-bor"></td>
                    <td class='no-bor size'> <strong class='size-little'>FICHA DEPOSITO</strong> <br> PROTEGE A QUIEN AMAS</td>
                  </tr>
                  <tr>
                    <td class='grey size' colspan="4" >Nombre completo</td>
                  </tr>
                  <tr>
                    <td class='green size' colspan="4"><?php echo htmlentities($row['Nombre'], ENT_QUOTES, "UTF-8");  ?></td>
                  </tr>
                  <tr>
                    <td class='grey size'>No de cliente</td>
                    <td class='grey size'>Tipo de Servicio</td>
                    <td class='grey size'>No. De pago</td>
                    <td class='no-bor size'>Efectivo (X)</td>
                  </tr>
                  <tr>
                    <td class='green size'><?php  echo $row['Id']; ?> </td>
                    <td class='green size'><?php echo $row['TipoServicio']; ?> </td>
                    <td class='trasp size'><?php echo $Npg; ?> </td>
                    <td class='no-bor size'>M.N.(X)</td>
                  </tr>
                  <tr>
                    <td class='grey  size'> Hasta el: </td>
                    <td class='green size'><?php echo $Fecha; ?></td>
                    <td class='grey  size'>Pago por:</td>
                    <td class='green size'> $&nbsp;<?php  echo $pago;?></td>
                  </tr>
                  <tr>
                    <td class='grey   size'>A partir del:</td>
                    <td class='trasp  size'>	<?php echo $tre; ?></td>
                    <td class='grey   size'>Pago por:</td>
                    <td class='trasp  size'> $&nbsp;<?php echo $financieras->Mora($pago);?></td>

                  </tr>
                  <tr>
                    <td> <center><img 	src='img/BBVA.png' width='60px' class="img"> </center></td>
                    <td class='no-bor size'>CTA. 0116940382</td>
                    <td> <center> <img 	src='img/OXXO.png' width='60px' class="img"> </center> </td>
                    <td class='no-bor size'>4152&nbsp;3140&nbsp;4971&nbsp;7112 </td>
                  </tr>
                  <tr>
                    <td class='grey size' width='60px'>Referencia</td>
                    <td class='no-bor size'><?php echo $row['IdFIrma']; ?></td>
                    <td class='grey size' width='60px'>Receptor</td>
                    <td class='no-bor size'>KASU, SERVICIOS A FUTURO</td>
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
    $a=1;
    $cont++;
}
?>
	</body>
	</html>
