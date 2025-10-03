<?php
try {
	ob_start(); // inicia la creacion de los documentos
	//Se incluyen lo archivos prinicipales
	require_once '../../eia/librerias.php'; //Cargamos las funciones Basicas
	require_once 'dompdfMaster/dompdf_config.inc.php';//Bloqeuado para impresion
	require_once 'dompdfMaster/include/autoload.inc.php'; //se carga el autoload para poder tener disponible la case DOMPDF
    // datos de la fecha en php JOSE CARLOS CABRERA MONROY
    $fecha = date("Y-m-d-H-i-s");
	//Archivo con el que se generan los Presupuestos
	//Se pasan las variables POST a Variable
	if(!isset($_POST['busqueda'])){
	    $busqueda = base64_decode($_GET['busqueda']);
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
		// Quitamos espacios al nombre del cliente para el nombre del archivo PDF
		$NomFichas = str_replace(' ', '', $Prospecto['FullName']);
		// Opciones para DOMPDF
		$datospdf->set_option('enable_html5_parser', TRUE);        // Habilita el parser HTML5
		$datospdf->set_paper("A4", "portrait");                    // Tamaño y orientación del papel
		// Carga el HTML y renderiza el PDF
		$datospdf->load_html($html);                               // Carga el contenido HTML
		$datospdf->render();                                       // Renderiza el PDF
		// Obtiene el contenido del PDF y define el nombre de archivo
		$output = $datospdf->output();
		$nombrePdf = "Propuesta_" . $NomFichas . ".pdf";
		// Guarda el archivo en la carpeta DATES (opcional)
		file_put_contents("DATES/" . $nombrePdf, $output);
		// Fuerza la descarga automática del PDF al usuario
		$datospdf->stream($nombrePdf, array("Attachment" => 1));
		//Codigo agregado por Ivan
		} catch (\Throwable $e) {
			echo $e->getMessage();
			echo $e->getLine();
			var_dump($e->getTrace());
		}
?>
