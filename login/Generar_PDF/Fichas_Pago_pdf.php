<?php
try {
//indicar que se inicia una sesion
	ob_start(); // inicia la creacion de los documentos
  //Se incluyen lo archivos prinicipales
	require_once 'dompdfMaster/dompdf_config.inc.php'; // libreria del pdf
	require_once '../../eia/Conexiones/cn_vtas.php';
	// Recibe  el id de cte
	if(isset($_POST['IdVenta'])){
		$cte = $_POST['IdVenta'];
		$fec = $_POST['data'];
	}else{
		$cte = base64_decode($_GET['Cte']);
		//$fec = $_GET['data'];
	}
  //Lanzamos la generacion de el PDF
  $datospdf = new DOMPDF();
	//Id,IdContact,Nombre,TipoServicio,Subtotal,NumeroPagos,IdFIrma
	echo $sql = "SELECT * FROM Venta WHERE Id = '".$cte."'";
	//si devuelve valor realiza
	$res = mysqli_query($mysqli, $sql);
		//retorname los datos en un arreglo
		if($row = mysqli_fetch_assoc($res)){
				//	Se aterrizan en variables
				$name =	$row['Nombre'];
        //Codigo agregado por IVAN
				ob_start();
				include 'html/Fichas_Pago.php';
				$html = ob_get_clean();
		 }
  //quitamos espacios al nombre cte para el documento
  $NomFichas = str_replace(' ', '',$name);
  //se crea el PDF
  $datospdf->set_option('enable_html5_parser', TRUE);     // Se modifico -> $datospdf = new DOMPDF(); con $datospdf->set_option('enable_html5_parser', TRUE);
  $datospdf->set_paper("A4", "portrait"); 								// Tamaño de la hoja
  $datospdf->load_html($html); 								           	// Obtiene el contenido - Se modifico ob_get_clean() por la variable $html
  $datospdf->render(); 																		// Renderizamos el documento PDF
  $output = $datospdf->output(); 													// Datos de salida del PDF
  $nombrePdf = "FICHAS_".$NomFichas.".pdf"; 			        // Nombre del archivo personalizado
  file_put_contents(("DATES/" . $nombrePdf), $output); 		// Escribir datos en un fichero
  $datospdf->stream($nombrePdf);

  //Codigo agregado por Ivan
  } catch (\Throwable $e) {
    echo $e->getMessage();
    echo $e->getLine();
    var_dump($e->getTrace());
  }
?>
