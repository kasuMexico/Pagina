<?php
//creamos una variable general para las funciones
$financieras = new Financieras();
try {
//indicar que se inicia una sesion
	ob_start(); // inicia la creacion de los documentos
	require_once 'dompdfMaster/dompdf_config.inc.php'; // libreria del pdf
	require_once '../../eia/Conexiones/cn_vtas.php';
  // datos de la fecha en php JOSE CARLOS CABRERA MONROY
  $fecha = date("Y-m-d-H-i-s");
	//Decodificamos el ARCHIVO
	$busqueda = base64_decode($_GET['busqueda']);
  //Cosnulta de la venta
  $Ct3 = "SELECT * FROM Venta WHERE Id = '".$busqueda."'";
  $Ct3a = mysqli_query($mysqli, $Ct3);
  //Si existe el registro se asocia en un fetch_assoc
  	if($venta=mysqli_fetch_assoc($Ct3a)){
        //Realiza consulta
        $Ct1 = "SELECT * FROM Contacto WHERE id = '".$venta['IdContact']."'";
        $Ct1a = mysqli_query($mysqli, $Ct1);
        //Si existe el registro se asocia en un fetch_assoc
        if($datos=mysqli_fetch_assoc($Ct1a)){
        	   //Realiza consulta
            $Ct2 = "SELECT * FROM Usuario WHERE IdContact = '".$venta['IdContact']."'";
            $Ct2a = mysqli_query($mysqli, $Ct2);
            $datospdf = new DOMPDF(); 															// Instanciamos un objeto de la clase DOMPDF.
              //Si existe el registro se asocia en un fetch_assoc
              if($persona=mysqli_fetch_assoc($Ct2a)){
                  $nombre = $persona['Nombre'];
                  //Saldo de el credito
                  if($venta['Status'] == "ACTIVO" || $venta['Status'] == "ACTIVACION"){
                      $saldo = money_format('%.2n',0);
                  }else {
                  		$saldo = $financieras->SaldoCredito($mysqli,$venta['Id']);
                  }
                  //SI el usuario compro a un mes o de contado
                  if( $venta['NumeroPagos'] >= 2 ){
                  		$Credito = "Compra a credito; ".$venta['NumeroPagos']." Meses";
                  }else{
                  		$Credito = "Compra de contado";
                  }
                  //Codigo agregado por IVAN
                  ob_start();
                  include 'html/Estado_Cuenta.php';
                  $html = ob_get_clean();
              }
          }
      }
  //quitamos espacios al nombre cte para el documento
  $NomFichas = str_replace(' ', '',$nombre);
  //se crea el PDF
  $datospdf->set_option('enable_html5_parser', TRUE);     // Se modifico -> $datospdf = new DOMPDF(); con $datospdf->set_option('enable_html5_parser', TRUE);
  $datospdf->set_paper("A4", "portrait"); 								// Tamaño de la hoja
  $datospdf->load_html($html); 								           	// Obtiene el contenido - Se modifico ob_get_clean() por la variable $html
  $datospdf->render(); 																		// Renderizamos el documento PDF
  $output = $datospdf->output(); 													// Datos de salida del PDF
  $nombrePdf = "EDOCTA_".$NomFichas.".pdf"; 			        // Nombre del archivo personalizado
  file_put_contents(("DATES/" . $nombrePdf), $output); 		// Escribir datos en un fichero
  $datospdf->stream($nombrePdf);

    //Codigo agregado por Ivan
    } catch (\Throwable $e) {
      echo $e->getMessage();
      echo $e->getLine();
      var_dump($e->getTrace());
    }
?>
