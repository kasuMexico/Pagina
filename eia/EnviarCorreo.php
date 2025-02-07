<?php
  //indicar que se inicia una sesion
	session_start();
  //inlcuir el archivo de funciones
  require_once 'librerias.php';
  //Se protejen los Valores y se asignan los for como
  foreach ($_POST as $key => $value){
      $asignacion="\$".$key."='".$value."';";
      eval($asignacion);
  }
	if(!empty($_GET['EnCoti'])){										//envia el presupuesto de el cliente
		//Buscamos el nombre de el prospecto
		$IdProspecto = Basicas::BuscarCampos($pros,"IdProspecto","PrespEnviado","Id",$_GET['EnCoti']);
		$FullName = Basicas::BuscarCampos($pros,"FullName","prospectos","Id",$IdProspecto);
		//Realizamos a busqueda del correo del cte MMN
		$Email = Basicas::BuscarCampos($pros,"Email","prospectos","Id",$IdProspecto);
		//creamos el asunto
		$Asunto = 'ENVIO ARCHIVO';
		$DirUrl = "Cotizacion de servicios KASU";
		$imag1 = $_GET['EnCoti'];
		$Msg = "Se ha la cotizacion a el correo registrado de tu cliente";
		//Direccion de descarga
		$dirUrl1 = "https://kasu.com.mx/login/Generar_PDF/Cotizacion_pdf.php";
  //Seleccionamos el asunto que trata para la generacion de el correo
}elseif (isset($EnviarPoliza)){                    //Correo que envia la poliza del cliente
      //Emparejamos las variables a la funcion general
      $DirUrl = $Descripcion;
      $dirUrl1 = "https://kasu.com.mx/login/Generar_PDF/Poliza_pdf.php";
      $imag1 = base64_encode($IdVenta);
  }elseif (isset($EnviarFichas)){               	//Correo que Envia las Fichas de Pago
      //codificacion de el id
      $DirUrl = base64_encode($IdVenta);
  }elseif (isset($EnviarEdoCta)){               	//Correo que Envia el estado de cuenta
      //Emparejamos las variables a la funcion general
      $dirUrl1 = "https://kasu.com.mx/login/Generar_PDF/Estado_Cuenta_pdf.php";
      $DirUrl = $Descripcion;
      $imag1 = $IdVenta;
  }elseif (!empty($_GET['EnFi'])){              	//Enviar Fichas por metodo GET
      //Creamos las variables de el correo
      $Asunto = "PAGO PENDIENTE";
      //Realizamos a busqueda del correo del cte MMN
      $Email = Basicas::BuscarCampos($mysqli,"Mail","Contacto","id",$_SESSION["Cnc"]);
      $FullName = Basicas::BuscarCampos($mysqli,"Nombre","Usuario","IdContact",$_SESSION["Cnc"]);
      $IdVenta = $_SESSION["Cnc"];
      //Creamos la liga de mercado pago segun el caso
      if($_GET['EnFi'] == 1){
        //Emparejamos las variables a la funcion general
        $DirUrl = "https://www.mercadopago.com.mx/checkout/v1/redirect?preference-id=".$_GET['hash'];
      }else{
        //Liga de registro
        $DirUrl = "https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_plan_id=".$_GET['hash'];
      }
      //creamos las variables para la ventana emergente en la plataforma de Ventas
      $stat = "3";
  }elseif (!empty($_GET['MxVta'])){             	//Cuando no se realiza el pago en MercadoPago
      //Buscamos los datos de el nombre de el cliente
      $FullName = Basicas::BuscarCampos($mysqli,"Nombre","Venta","Id",$_GET['MxVta']);
      //buscar el usuario
      $CnTo = Basicas::BuscarCampos($mysqli,"IdContact","Venta","Id",$_GET['MxVta']);
      //Realizamos a busqueda del correo del cte MMN
      $Email = Basicas::BuscarCampos($mysqli,"Mail","Contacto","id",$CnTo);
      //Realizamos la busqueda de la fecha de la compra
      $FechaRegistro = Basicas::BuscarCampos($mysqli,"FechaRegistro","Venta","Id",$_GET['MxVta']);
      //Se ajustan las variables para el envio del archivo
      $DirUrl = base64_encode($_GET['MxVta']);
      $dirUrl1 = base64_encode(date("d-m-Y",strtotime($FechaRegistro)));
      //Se establece el asunto MMN
      $Asunto = "FICHAS DE PAGO KASU";
      //creamos las variables para la ventana emergente en la plataforma de Ventas
      $stat = "2";
  }elseif (!empty($_GET['ProReIn'])) { 												//Enviamos La confirmacion de registro de un prospecto
		//Aterrizar el nombre de el cliente
		$FullName = Basicas::BuscarCampos($pros,"FullName","prospectos","Id",$_GET['ProReIn']);
		//Se establece el asunto MMN
		$Asunto = "CONOCE KASU";
		//Recogemos el nombre del servicio
		$imag1 = strtolower($_GET['Servicio']);
		//Imagen que se muestra en el correo
		$IdVenta = $_GET['ProReIn'];
		//Direccion de la pagina del prodcuto
		if($_GET['Servicio'] == "UNIVERSITARIO"){
			$DirUrl = "https://kasu.com.mx/productos.php?Art=2";
		}elseif($_GET['Servicio'] == "RETIRO"){
			$DirUrl = "https://kasu.com.mx/productos.php?Art=3";
		}elseif($_GET['Servicio'] == "POLICIAS"){
			$DirUrl = "https://kasu.com.mx/productos.php?Art=4";
		}else{
			$DirUrl = "https://kasu.com.mx/productos.php?Art=1";
		}
		//Armamos el archivo para agendar una cita
		$dirUrl1 = "https://kasu.com.mx/prospectos.php?data=Q0lUQQ==&Usr=".$IdVenta;
  }
  //Se crea el correo electronico para enviarlo segun los modelos
  $mensa = Correo::Mensaje($Asunto,$FullName,$DirUrl,$dirUrl1,$imag1,$Titulo1,$Desc1,$dirUrl2,$imag2,$Titulo2,$Desc2,$dirUrl3,$imag3,$Titulo3,$Desc3,$dirUrl4,$imag4,$Titulo4,$Desc4,$IdVenta);
  //Requisitos para enviar el email
    require 'PHPMailer/PHPMailer.php';
    require 'PHPMailer/SMTP.php';
    require 'PHPMailer/Exception.php';
  //These must be at the top of your script, not inside a function
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
  //Armamos el correo electronico y lo enviamos
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->SMTPDebug = 0;                                       // el 2 habilita el informe de error, dejar en 0 cuando este lista
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.hostinger.mx';                    //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'atncliente@kasu.com.mx';               //SMTP username
        $mail->Password   = '01J76e90@';                            //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        //Recipients
        $mail->setFrom('atncliente@kasu.com.mx', 'KASU');
        $mail->addAddress($Email, $FullName);                       //Add a recipient
        //Content
        $mail->isHTML(true);                                        //Set email format to HTML
        $mail->Subject = $Asunto;
        $mail->Body    = $mensa;
        $mail->send();
    } catch (Exception $e) {
        echo "Error al enviar el mensaje: {$mail->ErrorInfo}";
    }
    //Redireccionamientos a pagina de registro rpincipal cuando el correo se envia desde el registro de clientes de pagina registro.php
    if (!empty($_GET)) {
			if(!empty($_GET['EnCoti'])){
				  header('Location: '.$Host.'&Msg='.$Msg);
			}elseif (!empty($_GET['ProReIn'])) {
					header('Location: https://kasu.com.mx/index.php?Msg=Felicidades%20ya%20te%20hemos%20enviado%20un%20correo%20y%20en%20breve%20un%20ejecutivo%20te%20contatactara');
			}
      // Finalmente, destruir la sesión.
      session_destroy();
      //Redirigimos a la pagina de registros de clientes
      header('Location: https://kasu.com.mx/registro.php?stat='.$stat.'&Cte='.$FullName.'&liga='.$DirUrl);
    }else{
      //Enviamos un alert que indica que el correo se ha enviado correctamente y se redirecciona cuando da click en aceptar
      echo "<script>
              alert('Se ha enviado el correo electronico');
              window.location.href = '../login/Mesa_Herramientas.php';
            </script>";
    }
