<?php
	try {
		//Archivo con el que se generan las polizas de el cliente
		ob_start(); // inicia la creacion de los documentos
		//Se incluyen lo archivos prinicipales
		require_once '../../eia/Conexiones/cn_vtas.php';
		require_once 'dompdfMaster/dompdf_config.inc.php';
		//SE ingresa la fecha
		date_default_timezone_set('America/Mexico_City');
		// datos de la fecha en php JOSE CARLOS CABRERA MONROY
		$fecha = date("d-m-Y");
		// Instanciamos un objeto de la clase DOMPDF.
		$datospdf = new DOMPDF();
		// datos del usuario en la tabla Contacto
		$sql = "SELECT * FROM Contacto WHERE id = $_GET['Add']";
		if($result = $mysqli->query($sql)){	// Si obtienes datos
				while($row2 = $result->fetch_array()){	//retorname los datos en un arreglo
					//Datos  del usuario con contacto
           $Direccion = $row2['Direccion'];
					 $EMail = $row2['Mail'];
				}
		}
		// datos del usuario en la tabla Direccion
		$sql = "SELECT * FROM Empleados WHERE IdContacto = $_GET['Add']";
		if($result = $mysqli->query($sql)){	// Si obtienes datos
				while($row3 = $result->fetch_array()){	//retorname los datos en un arreglo
          $Nombre = $row3['Nombre'];
					$RFC = $row3['RFC'];
					$CLABE = $row3['Cuenta'];
				}
				ob_start();
				include 'html/Contrato_Ejecutivo.php';
				$html = ob_get_clean();
		}
		//quitamos espacios al nombre cte para el documento
		$NomFichas = str_replace(' ', '',$Nombre);
		//se crea el PDF
		$datospdf->set_option('enable_html5_parser', TRUE);     // Se modifico -> $datospdf = new DOMPDF(); con $datospdf->set_option('enable_html5_parser', TRUE);
		$datospdf->set_paper("A4", "portrait"); 								// Tamaño de la hoja
		$datospdf->load_html($html);   													// Obtiene el contenido - Se modifico ob_get_clean() por la variable $html
		$datospdf->render(); 																		// Renderizamos el documento PDF
		$output = $datospdf->output(); 													// Datos de salida del PDF
		$nombrePdf = "Contrato_".$NomFichas.".pdf"; 			      // Nombre del archivo personalizado
		file_put_contents(("DATES/" . $nombrePdf), $output); 		// Escribir datos en un fichero
		$datospdf->stream($nombrePdf);
		//Codigo agregado por Ivan
		} catch (\Throwable $e) {
			echo $e->getMessage();
			echo $e->getLine();
			var_dump($e->getTrace());
		}
	?>
