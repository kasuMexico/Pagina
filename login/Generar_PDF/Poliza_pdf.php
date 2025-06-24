<?php
try {
//indicar que se inicia una sesion
		ob_start(); // inicia la creacion de los documentos
		//Se incluyen lo archivos prinicipales
		require_once '../../eia/Conexiones/cn_vtas.php';
		require_once 'dompdfMaster/dompdf_config.inc.php';//Bloqeuado para impresion
		require_once 'dompdfMaster/include/autoload.inc.php'; //se carga el autoload para poder tener disponible la case DOMPDF

		//SE ingresa la fecha
		date_default_timezone_set('America/Mexico_City');
		// datos de la fecha en php JOSE CARLOS CABRERA MONROY
		$fecha = date("Y-m-d-H-i-s");
		//Se desencripta el dato
		$dat = base64_decode($_GET['busqueda']);
		//Consultas a la base de datos para la busqueda de el cliente
		$id = $mysqli -> real_escape_string($dat);
		$sql = "SELECT * FROM Usuario WHERE IdContact = $id";
			if($result = $mysqli->query($sql)){				// Si obtienes datos
					while($row = $result->fetch_array()){	//retorname los datos en un arreglo
						//Datos  del usuario
						 $curp = $row['ClaveCurp'];
						 $cont = $row['IdContact'];
					}
			}
			// datos del usuario en la tabla Contacto
			$sql = "SELECT * FROM Contacto WHERE id = $cont";
			if($result = $mysqli->query($sql)){	// Si obtienes datos
					while($row = $result->fetch_array()){	//retorname los datos en un arreglo
						//Datos  del usuario con contacto
						 $email = $row['Mail'];
						 $phone = $row['Telefono'];
						 $direccion = $row['Direccion'];
					}
			}
			//Busqueda de datos en la tabla venta
			$sql = "SELECT * FROM Venta WHERE IdContact = $cont";
			if($result = $mysqli->query($sql)){	// Si obtienes datos
					while($row = $result->fetch_array()){	//retorname los datos en un arreglo
						//Datos  del usuario con contacto
						 $name = $row['Nombre'];
						 $Producto = $row['Producto'];
						 $TipoServicio = $row['TipoServicio'];
						 $Costo = $row['CostoVenta'];
						 $Status = $row['Status'];
						 $IdFIrma = $row['IdFIrma'];
						 $FechaR = $row['FechaRegistro'];
					}
			}
			// muestra el formato internacional para la configuraci&oacute;n regional Mexico
			setlocale(LC_MONETARY, 'es_MX');
			$Costo = money_format('%i', $Costo) . "\n";
			//Seleccion de costo a imprimir
			if($Producto == "Universidad") {
			 	$productoA = "Servicio Universitario";
			}else{
				$productoA = substr($Producto, 0, -3)." a ".substr($Producto, 3, 2)." a&ntilde;os";
			}
			//Lanzamos la generacion de el PDF
			$datospdf = new DOMPDF(); // Instanciamos un objeto de la clase DOMPDF. //Bloqeuado para impresion
			//Bloqueo de seguridad para que no impriman poizas que no han sido pagadas
			if($Status == "ACTIVO" || $Status == "ACTIVACION"){
				//echo "Estatus de la poliza --> ".$Status;
				//Codigo agregado por IVAN
				ob_start(); //Bloqeuado para impresion
				require_once 'html/Poliza_Servicio.php';
				$html = ob_get_clean();	//Bloqeuado para impresion
			}else{
				echo'<script type="text/javascript">
						alert("Esta poliza no se encuentra liquidada en su totalidad");
						window.location="https://kasu.com.mx/";
				</script>';
			}
			//quitamos espacios al nombre cte para el documento
		  $NomFichas = str_replace(' ', '',$name);
		  //se crea el PDF
		  $datospdf->set_option('enable_html5_parser', TRUE);     // Se modifico -> $datospdf = new DOMPDF(); con $datospdf->set_option('enable_html5_parser', TRUE);
		  $datospdf->set_paper("A4", "portrait"); 				  // Tamaño de la hoja
		  $datospdf->load_html($html); 							  // Obtiene el contenido - Se modifico ob_get_clean() por la variable $html
		  $datospdf->render(); 									  // Renderizamos el documento PDF
		  $output = $datospdf->output(); 						  // Datos de salida del PDF
		  $nombrePdf = "POLIZA_".$NomFichas.".pdf"; 			  // Nombre del archivo personalizado
		  file_put_contents(("DATES/" . $nombrePdf), $output); 	  // Escribir datos en un fichero
		  $datospdf->stream($nombrePdf);
//Codigo agregado por Ivan para captar los errores de el archivo
	} catch (\Throwable $e) {
		echo $e->getMessage();
		echo $e->getLine();
		var_dump($e->getTrace());
	}
?>
