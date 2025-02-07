<?php
//indicar que se inicia una sesion
session_start();
//inlcuir el archivo de funciones
require_once 'Funciones_kasu.php';
$id = $_GET['n'];
$PromesaPago= $_GET['m'];
date_default_timezone_set('America/Mexico_City');
$fecha = date("Y-m-d-H-i-s");
$data = date( "d-m-Y",strtotime($PromesaPago));
$campos = array("Id"=>"Id","IdContact"=>"IdContact","Nombre"=>"Nombre","TipoServicio"=>"TipoServicio","Subtotal"=>"Subtotal","NumeroPagos"=>"NumeroPagos","IdFIrma"=>"IdFIrma");
//echo $data;
$dataCte = PDF::Datos($mysqli,"Venta",$campos,"IdContact",$id);
//echo $dataCte["Id"]," ",$dataCte["IdContact"]," ",$dataCte["Nombre"]," ",$dataCte["TipoServicio"]," ",$dataCte["Subtotal"]," ",$dataCte["NumeroPagos"]," ",$dataCte["IdFIrma"];
/*calculamos los datos de acuerdo al numero de pagos y los asignamos a las variables para el llenado del formato*/
//Datos asigndos a pago de contado
if( $dataCte['NumeroPagos'] == 0  || $dataCte['NumeroPagos'] == 1 )
{
  $contado = 1; 														// numero de division de acuerdo al mes
  $NumPag = 1; 														// numero de pagos
  $div = 1; 														// numero en el que se divide
  $DM = date_create("$data"); // creamos una fecha dd-mm-yyyy de la mora

  date_modify($DM,"+3 days"); 				// Aumento 3 dias a la primer fecha
  $FormatoFecha = date_format($DM,"d-m-Y"); 	// Realiza un formato entendible
/*********/
$datos = []; // Se crea un array para almacenar las fechas
$datos[$i] = $data;  // Array con datos[1] fecha del dia de pago
$datos[$i.'c'] = $FormatoFecha; // Array con datos[1c] fecha aplicando mora
$pago = round($dataCte['Subtotal']/($div*$contado));  // redondeo de pago Subtotal / numero de pagos * meses
$mora = round(($dataCte['Subtotal']/( $div*$contado))*1.1); // Del pago obtenido se multiplica por 1.1 tasa de intereses
$Formato = PDF::Formato($dataCte["Nombre"],$dataCte["IdContact"],$dataCte["TipoServicio"],$NumPag,$datos[$i],$pago,$datos[$i.'c'],$mora,$dataCte["IdFIrma"]);
//echo $Formato;
//$PDFResult = PDF::DocPDF($id,$dataCte["Nombre"],$fech,$Formato);
//echo $PDFResult;
//header('Location: https://kasu.com.mx');
    echo "pago unico";
}
//Datos asigndos a pago a credito
else
{
    echo "<br> pago credito <br>";
    $credito = 2; 											// numero de division de acuerdo al mes
    $NumPag = $dataCte['NumeroPagos']*2 ; 														// numero de pagos
    //echo $NumPag;
    $div = $dataCte['NumeroPagos']; 														// numero en el que se divide
    //echo $div;
    //asignamos a una nueva variable la fecha a utilizar para las interaciones a para los pagos a credito
    //echo $data;
    $FechaCredito = date_create("$data");

    for($i = 1; $i<= $NumPag; $i++){
        // Fecha de registro + 15 paro los pagos posteriores
        if($i >= 1 ){
           date_modify($FechaCredito,"+15 days");
        }
        $FormatoFC = date_format($FechaCredito,"d-m-Y");
        //echo $FormatoFC, "<br>";
        $date =date_parse($FormatoFC);
        $DI = $date['day'];
        $ME = $date['month'];
        $AN = $date['year'];
        if ($i == 1 )
				{
					// se asigna el mes en formato de 3 letras para la BD
					$MesLetra=PDF::Mes($ME);;

				}
				// interaciones para los pagos posteriores
				if($DI >= 1 && $DI <= 15 ||  $DI == 16)
				{
					$DI = 15; // Dia fijo para pagos
					$Mes = $ME; // variable de mes
					$Year = $AN; // Variable de año
					//cuando el mes es trece se pasa la fecha al primer mes del siguiente año
					if($Mes == 13 )
					{
						$Mes = '01';
						$Year = $AN +1;
					}

				}
        elseif( $DI >= 17 && $DI <= 29 || $DI == 30 || $DI == 31)
				{
					$DI = "01"; // Dia fijo para pagos
					$Mes= $ME+1; // Suma 1 mes mas
					//cuando el mes es trece se pasa la fecha al primer mes del siguinete año
					if($Mes == 13 )
					{
						$Mes = '01';
						$Year = $AN +1;
					}
				}
        if ($i == 1 )// en la primera interacion pasa la fecha insertada
					{
						$S = $data;
            //echo $S;
					}
					else // Mas interaciones
					{
						$S = $DI.'-'.$Mes.'-'.$Year; // Fechas generadas
					}
        	/*aqui se crean arreglos con los datos calculados para generar el pdf */
				$dm = date_create("$S"); // Crea la fecha
				date_modify($dm,"+3 days"); // Se suman 3 dias para la retardos de pago
				$tre = date_format($dm,"d-m-Y"); // Se le da un formato entendible
        //echo $tre,"<br>";
				$datos = []; // Se crea un array para almacenar las fechas
				$datos[$i] = $S;  // Array con datos[1] fecha del dia de pago
        //echo $datos[$i],"<br>";
				$datos[$i.'c'] = $tre; // Array con datos[1c] fecha aplicando mora
				$pago = round($dataCte['Subtotal']/($div*$credito));  // redondeo de pago Subtotal / numero de pagos * meses
        //echo $pago,"<br>";
				$mora = round(($dataCte['Subtotal']/( $div*$credito))*1.1); // Del pago obtenido se multiplica por 1.1 tasa de interes
        //echo "<br>",$datos[$i], $pago, "<br>",$datos[$i. 'c'],$mora, "<br>";

    }
    $Formato = PDF::Formato($dataCte["Nombre"],$dataCte["IdContact"],$dataCte["TipoServicio"],$NumPag,$datos[$i],$pago,$datos[$i.'c'],$mora,$dataCte["IdFIrma"],$PromesaPago);
    $fichas = strlen($Formato);
        echo $fichas;
	   //echo $Formato[];
	   //echo "<br/>";


}



/********************* fin pdf *********************/
?>
