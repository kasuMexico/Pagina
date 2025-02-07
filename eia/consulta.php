<?php
    //indicar que se inicia una sesion *JCCM
	  session_start();
    //inlcuir el archivo de funciones
    require_once 'php/Funciones_kasu.php';
    //Requerimos el archivo de librerias *JCCM
		date_default_timezone_set('America/Mexico_City');
    //Crear consulta
    $sql="SELECT id FROM FingerPrint WHERE fingerprint = ".$_POST['fingerprint'];
    //Realiza consulta
    $res = mysqli_query($mysqli, $sql);
    //Si existe el registro se asocia en un fetch_assoc
    if($Reg=mysqli_fetch_assoc($res)){
/********************** Bloque de registro de eventos ************************/
//Valida los datos para determinar si ya se registro el FingerPrint
		    if (!empty($Reg['id'])){
		    //Se crea el array que contiene los datos de FINGERPRINT
		        $DatFinger = array (
		            "fingerprint"   => $_POST['fingerprint'],
		            "browser"       => $_POST['browser'],
		            "flash"         => $_POST['flash'],
		            "canvas"        => $_POST['canvas'],
		            "connection"    => $_POST['connection'],
		            "cookie"        => $_POST['cookie'],
		            "display"       => $_POST['display'],
		            "fontsmoothing" => $_POST['fontsmoothing'],
		            "fonts"         => $_POST['fonts'],
		            "formfields"    => $_POST['formfields'],
		            "java"          => $_POST['java'],
		            "language"      => $_POST['language'],
		            "silverlight"   => $_POST['silverlight'],
		            "os"            => $_POST['os'],
		            "timezone"      => $_POST['timezone'],
		            "touch"         => $_POST['touch'],
		            "truebrowser"   => $_POST['truebrowser'],
		            "plugins"       => $_POST['plugins'],
		            "useragent"     => $_POST['useragent']
		        );
		        $IdFInger = Basicas::InsertCampo($mysqli,"FingerPrint",$DatFinger);
			  }
		}
		//Registramos los eventos
		$hoy = date('Y-m-d');
		$HoraActual = date('H:i:s');
		//Creamos el array que contiene los datos de la consulta
		$DatEventos = array(
				"IdFInger"    	=> $_POST['fingerprint'],
				"Usuario"    	=> $_POST['Usuario'],
				"Evento"      	=> $_POST['Event'],
				"MetodGet"    	=> $_POST['formfields'],
				"connection"  	=> $_POST['connection'],
				"timezone"    	=> $_POST['timezone'],
				"touch"       	=> $_POST['touch'],
				"Cupon"       	=> $_POST['Cupon'],
				"FechaRegistro" => $hoy." ".$HoraActual
		);
		//Se realiza el insert en la base de datos
		echo Basicas::InsertCampo($mysqli,"Eventos",$DatEventos);
?>
