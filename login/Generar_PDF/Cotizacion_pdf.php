<?php
try {
	ob_start(); // inicia la creacion de los documentos
	//Se incluyen lo archivos prinicipales
	require_once '../../eia/Conexiones/cn_prosp.php';
	require_once 'dompdfMaster/dompdf_config.inc.php';//Bloqeuado para impresion
	require_once 'dompdfMaster/include/autoload.inc.php'; //se carga el autoload para poder tener disponible la case DOMPDF
    // datos de la fecha en php JOSE CARLOS CABRERA MONROY
    $fecha = date("Y-m-d-H-i-s");
	//Archivo con el que se generan los Presupuestos
	//Se pasan las variables POST a Variable
	if(!isset($_POST['busqueda'])){
	    $busqueda = $_GET['busqueda'];
	}else{
	    $busqueda = $_POST['busqueda'];
	}
	//Cosnulta de la venta
	$Cdbt3 = "SELECT * FROM PrespEnviado WHERE Id = '".$busqueda."'";
	$lsCt3a = mysqli_query($pros, $Cdbt3);
	$datospdf = new DOMPDF();// Instanciamos un objeto de la clase DOMPDF.
	//Si existe el registro se asocia en un fetch_assoc
		if($Propuest=mysqli_fetch_assoc($lsCt3a)){
	      //Consulta a tabla de cotizacion
	      $Ct3 = "SELECT * FROM prospectos WHERE Id = '".$Propuest['IdProspecto']."'";
	      $Ct3a = mysqli_query($pros, $Ct3);
	      //Si existe el registro se asocia en un fetch_assoc
	      if($Prospecto=mysqli_fetch_assoc($Ct3a)){
					ob_start();
					include 'html/Cotizacion.php';
					$html = ob_get_clean();
				}
		}
		//quitamos espacios al nombre cte para el documento
		$NomFichas = str_replace(' ', '',$Prospecto['FullName']);//se crea el PDF
		$datospdf->set_option('enable_html5_parser', TRUE);      // Se modifico -> $datospdf = new DOMPDF(); con $datospdf->set_option('enable_html5_parser', TRUE);
		$datospdf->set_paper("A4", "portrait"); 				 // Tamaño de la hoja
		$datospdf->load_html($html);   							 // Obtiene el contenido - Se modifico ob_get_clean() por la variable $html
		$datospdf->render(); 									 // Renderizamos el documento PDF
		$output = $datospdf->output(); 							 // Datos de salida del PDF
		$nombrePdf = "Propuesta_".$NomFichas.".pdf"; 			 // Nombre del archivo personalizado
		file_put_contents(("DATES/" . $nombrePdf), $output); 	 // Escribir datos en un fichero
		$datospdf->stream($nombrePdf);
		//Codigo agregado por Ivan
		} catch (\Throwable $e) {
			echo $e->getMessage();
			echo $e->getLine();
			var_dump($e->getTrace());
		}
?>
