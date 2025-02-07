<?
/*********************************Enviar correos por flujo de ventas*********************************/
//Estructura de correos
if(isset($_POST['interno']) || isset($Evento)){
      foreach ($_POST as $key => $value){
          $asignacion="\$".$key."='".$value."';";
          eval($asignacion);
    }
    //se valida que el correo exista
    if(empty($_POST['Asunto'])){
      //Verificamos que no exista el prospectos
        $IdProspecto = Basicas::BuscarCampos($pros,'Id','prospectos','Email',$Mail);
        $correo = $Mail;
    }else{
    //Se obtiene el servicio de interes
    $Servicio = Basicas::BuscarCampos($pros,'Servicio_Interes','prospectos','Id',$IdProspecto);
    $correo = Basicas::BuscarCampos($pros,'Email','prospectos','Id',$IdProspecto);
    //obtengo el email desde el form login.php
    $asunto = $_POST['Asunto'];
    }
    //seleccionamos la informacion de la BD correspondiente al email del user
    $sql = "SELECT * FROM prospectos WHERE  Email = '$correo'";
    //Realiza consulta
    $res = mysqli_query($pros, $sql);
    //Si existe el registro se asocia en un fetch_assoc
    if($Reg=mysqli_fetch_assoc($res)){
          //SE valida que correo se enviara
          if($asunto == "CONOCE KASU"){
            //construimos Mensaje personalizado de a cuerdo a su servicio de interes y enviamos
              if($Servicio == "FUNERARIO" ){
                $servicio = "KASU Gastos Funerarios: Es un servicio que te permite pagar los gastos funerarios tuyos o de algún ser querido, pagando hoy una aportación mínima, para que el día que tú o tu ser querido fallezca no tengan que pagar nada los deudos.";
                $DirServicio = "https://www.kasu.com.mx/productos.php?Art=1";
                $Titulo1 = "https://kasu.com.mx/blog/category/tanatologia/";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }else if($Servicio == "UNIVERSITARIO"){
                $servicio = "KASU Inversión universitaria: Es un servicio que te permite invertir en un fideicomiso para que al transcurso de 10 años el beneficiario que escogiste pueda pagar sus estudios universitarios.";
                $DirServicio = "https://www.kasu.com.mx/productos.php?Art=2";
                $Titulo1 = "https://kasu.com.mx/blog/category/educacion/";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }else if($Servicio == "RETIRO"){
                $servicio ="KASU Retiro: Es un servicio que te permite invertir en un fideicomiso para que al tener 65 años cuentes con un respaldo financiero para que tu retiro sea mas comodo.";
                $DirServicio ="https://www.kasu.com.mx/productos.php?Art=3";
                $Titulo1 ="https://kasu.com.mx/blog/category/retiro/";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }else{
                header('Location: https://kasu.com.mx'.$Host.'?Ml=3&name='.$name);
              }
              //Se crea el correo electronico para enviarlo segun los modelos
              $mensa = Correo::Mensaje($asunto,$name,$empresa,$servicio,$DirServicio,$Titulo1,$Desc1,$empresa,'','','','','','','','','','','',$IdProspecto);
              //echo $mensa;
          }
          elseif($asunto == "¡BIENVENIDO A KASU!"){
            //Variable de redireccionamiento para activar la cuenta
            $empresa = 'https://www.kasu.com.mx/clientes/';
            //Se busca el id del cliente
            $dirUrl1 = Basicas::BuscarCampos($mysqli,'Id','Venta','Nombre',$name);
            //Se crea el correo electronico para enviarlo segun los modelos
            $mensa = Correo::Mensaje($asunto,$name,$empresa,$dirUrl1,'','','','','','','','','','','','','','','',$IdProspecto);
            //echo $mensa;
          }
          elseif($asunto == "ARTÍCULOS SUGERIDOS"){
            //Se contruye la consulta para obtener los articulos del blog
            $consulta = "SELECT * FROM wp_posts WHERE post_status = 'publish' AND ping_status = 'open' ";
            //Se realiza la consulta
            if ($resultado = mysqli_query($cnp, $consulta)) {
               // Se arma un while para ingresar los resultados en un array
                $cont = 0;
                while ($fila = mysqli_fetch_array($resultado)) {
                  //Obtengo el Id de el articulo HOY
                   $id[$cont] = $fila['ID'];
                   $dir[$cont] = $fila['post_name'];
                   $tit[$cont] = $fila['post_title'];
                   $cont++;
                }
            }
            //Se seleccionan 4 articulos alatorios del blog
            $claves_aleatorias = array_rand($dir, 4);
            //Obtenemos la liga original de el articulo
            if ($pst_parA = Basicas::BuscarCampos($cnp,"target_post_id","wp_yoast_seo_links","post_id",$id[$claves_aleatorias[0]]) == 0){
              $pst_parA = $id[$claves_aleatorias[0]];
            }
            if ($pst_parB = Basicas::BuscarCampos($cnp,"target_post_id","wp_yoast_seo_links","post_id",$id[$claves_aleatorias[1]]) == 0){
              $pst_parB = $id[$claves_aleatorias[1]];
            }
            if ($pst_parC = Basicas::BuscarCampos($cnp,"target_post_id","wp_yoast_seo_links","post_id",$id[$claves_aleatorias[2]]) == 0) {
              $pst_parC = $id[$claves_aleatorias[2]];
            }
            if ($pst_parD = Basicas::BuscarCampos($cnp,"target_post_id","wp_yoast_seo_links","post_id",$id[$claves_aleatorias[3]]) == 0) {
              $pst_parD = $id[$claves_aleatorias[3]];
            }
            //Obtenemos el id minimo de la imagen a buscar
            $IdImgA = Basicas::Min1Dat($cnp,"ID","wp_posts","post_parent",$pst_parA);
            $IdImgB = Basicas::Min1Dat($cnp,"ID","wp_posts","post_parent",$pst_parB);
            $IdImgC = Basicas::Min1Dat($cnp,"ID","wp_posts","post_parent",$pst_parC);
            $IdImgD = Basicas::Min1Dat($cnp,"ID","wp_posts","post_parent",$pst_parD);
            //Obtenemos la primera imagen de el articulo
            $imag1 = Basicas::BuscarCampos($cnp,"guid","wp_posts","ID",$IdImgA);
            $imag2 = Basicas::BuscarCampos($cnp,"guid","wp_posts","ID",$IdImgB);
            $imag3 = Basicas::BuscarCampos($cnp,"guid","wp_posts","ID",$IdImgC);
            $imag4 = Basicas::BuscarCampos($cnp,"guid","wp_posts","ID",$IdImgD);
            //-titulos aleatorios
            $Titulo1 = $tit[$claves_aleatorias[0]];
            $Titulo2 = $tit[$claves_aleatorias[1]];
            $Titulo3 = $tit[$claves_aleatorias[2]];
            $Titulo4 = $tit[$claves_aleatorias[3]];
            //Articulos aleatorios
            $dirUrl1 = "https://kasu.com.mx/blog/".$dir[$claves_aleatorias[0]];
            $dirUrl2 = "https://kasu.com.mx/blog/".$dir[$claves_aleatorias[1]];
            $dirUrl3 = "https://kasu.com.mx/blog/".$dir[$claves_aleatorias[2]];
            $dirUrl4 = "https://kasu.com.mx/blog/".$dir[$claves_aleatorias[3]];
            //Se crea el correo electronico para enviarlo segun los modelos
            $mensa = Correo::Mensaje($asunto,$name,$empresa,$dirUrl1,$imag1,$Titulo1,'',$dirUrl2,$imag2,$Titulo2,'',$dirUrl3,$imag3,$Titulo3,'',$dirUrl4,$imag4,$Titulo4,'',$IdProspecto);
            //$mensa = Correo::Mensaje($asunto,$name,$empresa,$dirUrl1,'',$Titulo1,'',$dirUrl2,'',$Titulo2,'',$dirUrl3,'',$Titulo3,'',$dirUrl4,'',$Titulo4,'');
          }
          elseif($asunto == "¿AUN TIENES DUDAS?"){
            //construimos Mensaje personalizado de a cuerdo a su servicio de interes y enviamos
              if($Servicio == "FUNERARIO" ){
                $dirUrl1 = "https://www.kasu.com.mx/productos.php?Art=1";
                $Titulo1 = "https://kasu.com.mx/blog/category/tanatologia/";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }else if($Servicio == "UNIVERSITARIO"){
                $dirUrl1 = "https://www.kasu.com.mx/productos.php?Art=2";
                $Titulo1 = "https://kasu.com.mx/blog/category/educacion/";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }else if($Servicio == "RETIRO"){
                $dirUrl1 ="https://www.kasu.com.mx/productos.php?Art=3";
                $Titulo1 ="https://kasu.com.mx/blog/category/retiro/";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }else{
                header('Location: https://kasu.com.mx'.$Host.'?Ml=3&name='.$name);
              }
            //Se crea el correo electronico para enviarlo segun los modelos
            $mensa = Correo::Mensaje($asunto,$name,$empresa,$dirUrl1,'',$Titulo1,$Desc1,'','','','','','','','','','','','',$IdProspecto);
            //echo $mensa;
          }
          elseif ($asunto == "CONOCENOS UN POCO MÁS") {
            //construimos Mensaje personalizado de a cuerdo a su servicio de interes y enviamos
              if($Servicio == "FUNERARIO" ){
                $dirUrl2 = "https://www.kasu.com.mx/productos.php?Art=1";
                $dirUrl1 = "https://kasu.com.mx/Fideicomiso_F0003.pdf";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }else if($Servicio == "UNIVERSITARIO"){
                $dirUrl2 = "https://www.kasu.com.mx/productos.php?Art=2";
                $dirUrl1 = "https://kasu.com.mx/Fideicomiso_F0009.pdf";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }else if($Servicio == "RETIRO"){
                $dirUrl2 ="https://www.kasu.com.mx/productos.php?Art=3";
                $dirUrl1 ="https://kasu.com.mx/Fideicomiso_F0010.pdf";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }else{
                header('Location: https://kasu.com.mx'.$Host.'?Ml=3&name='.$name);
              }
            //Se aterriza la variable de session
            $Titulo1 = $Servicio;
            //Se crea el correo electronico para enviarlo segun los modelos
            $mensa = Correo::Mensaje($asunto,$name,$empresa,$dirUrl1,'https://kasu.com.mx/letraspeq.php',$Titulo1,$Desc1,$dirUrl2,'','','','','','','','','','','',$IdProspecto);
            //echo $mensa;
          }
          elseif ($asunto == "PROCESO DE COMPRA DE SERVICIOS KASU") {
            if($Servicio == "FUNERARIO" ){
              $dirUrl1 = "https://www.kasu.com.mx/productos.php?Art=1";
            }else if($Servicio == "UNIVERSITARIO"){
              $dirUrl1 = "https://www.kasu.com.mx/productos.php?Art=2";
            }else if($Servicio == "RETIRO"){
              $dirUrl1 ="https://www.kasu.com.mx/productos.php?Art=3";
            }else{
              header('Location: https://kasu.com.mx'.$Host.'?Ml=3&name='.$name);
            }
            //Se crea el correo electronico para enviarlo segun los modelos
            $mensa = Correo::Mensaje($asunto,$name,$empresa,$dirUrl1,'','','','','','','','','','','','','','','',$IdProspecto);
            //echo $mensa;
          }
          elseif ($asunto == "AGENDAR CITA") {
            //COnvertimos el id del usuario en base64
            $UsrEncode = base64_encode('CITA');
            $dirUrl1 = base64_encode($IdProspecto);
            //Se crea el correo electronico para enviarlo segun los modelos
            $mensa = Correo::Mensaje($asunto,$name,$UsrEncode,$dirUrl1,'','','','','','','','','','','','','','','',$IdProspecto);
            //echo $mensa;
          }
          elseif ($asunto == "CITA TELEFONICA"){
            //Generamos la fecha de la cita
            $dirUrl1 = $FechaCita." ".$HoraCita;
            //Se crea el correo electronico para enviarlo segun los modelos
            $mensa = Correo::Mensaje($asunto,$name,$Telefono,$dirUrl1,'','','','','','','','','','','','','','','',$IdProspecto);
            //Se borra de automatizar los correos
            Basicas::ActCampo($pros,"prospectos","Automatico",0,$IdProspecto);
          }
          elseif ($asunto == "¡INTEGRATE A KASU!") {
            //COnvertimos el id del usuario en base64
            $UsrEncode = base64_encode($IdProspecto);
            //Se crea el correo electronico para enviarlo segun los modelos
            $mensa = Correo::Mensaje($asunto,$name,$UsrEncode,'','','','','','','','','','','','','','','','',$IdProspecto);
            //echo $mensa;
          }
          //Requisitos para enviar el email
          require '../../eia/PHPMailer/PHPMailer.php';
          require '../../eia/PHPMailer/SMTP.php';
          require '../../eia/PHPMailer/Exception.php';
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
              $mail->addAddress($correo, $name);                       //Add a recipient
              //Content
              $mail->isHTML(true);                                        //Set email format to HTML
              $mail->Subject = $asunto;
              $mail->Body    = $mensa;
              $mail->send();
          } catch (Exception $e) {
              echo "Error al enviar el mensaje: {$mail->ErrorInfo}";
          }
          //se registra el valor del correo enviado
          $ValMail = Basicas::BuscarCampos($pros,'Seguimiento','correos','Asunto',$asunto);
          //Se inserta el estado en la base de datos
          Basicas::ActCampo($pros,"prospectos","Estado",$ValMail,$IdProspecto);
          //echo "Se ha enviado un email al correo registrado.";
          header('Location: https://kasu.com.mx'.$Host.'?Ml=1&name='.$name.'&Msg='.$Msg);
    }else{
        //echo "no existe el correo";
        header('Location: https://kasu.com.mx'.$Host.'?Ml=2&name='.$name.'&Msg='.$Msg);
    }
    //Se cierra la conexion con la base de datos
$pros->close();
}
