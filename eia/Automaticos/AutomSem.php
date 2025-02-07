<?
//indicar que se inicia una sesion
session_start();
//inlcuir el archivo de funciones
require_once '../php/Funciones_kasu.php';
//Registramos la varoable empresa
$empresa = 'https://www.kasu.com.mx/';
//Registramos la variable de el Asunto
$asunto = "ARTÍCULOS SUGERIDOS";
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
  $dirUrl1 = $empresa."blog/".$dir[$claves_aleatorias[0]];
  $dirUrl2 = $empresa."blog/".$dir[$claves_aleatorias[1]];
  $dirUrl3 = $empresa."blog/".$dir[$claves_aleatorias[2]];
  $dirUrl4 = $empresa."blog/".$dir[$claves_aleatorias[3]];
/*************************************************************************************************************
                                      Envio de correos a Prospectos
*************************************************************************************************************/
  //Se identifican cuantos son los valores maximos en la lista de Prospectos
  $MxArt = Basicas::MaxDat($pros,Id,"prospectos");
  $i = 1;
  //Se enviaun correo de articulos a cada uno
  while ($i <= $MxArt) {
  //Se busca si el servicio esta cancelado
      $Fa = Basicas::BuscarCampos($pros,"Cancelacion","prospectos","Id",$i);
  //Si esta dado de alta en la cancelacion del servicio no se envia
      if(empty($Fa)){
  //Se seleccionan los datos de el cliente
          $FullName = Basicas::BuscarCampos($pros,"FullName","prospectos","Id",$i);
          $RegEmail = Basicas::BuscarCampos($pros,"Email","prospectos","Id",$i);
          $Rl = Basicas::BuscarCampos($pros,"Sugeridos","prospectos","Id",$i);
  //Se crea el correo electronico para enviarlo segun los modelos
          $mensa = Correo::Mensaje($asunto,$FullName,$empresa,$dirUrl1,$imag1,$Titulo1,'',$dirUrl2,$imag2,$Titulo2,'',$dirUrl3,$imag3,$Titulo3,'',$dirUrl4,$imag4,$Titulo4,'',$i);
  //Se envia el correo electronico
          Correo::EnviarCorreo($FullName,$RegEmail,$asunto,$mensa);
  //Se suma el valor para saber cuantas veces se ha enviado este Email
          $Rl++;
  //Se inserta el estado en la base de datos
          Basicas::ActCampo($pros,"prospectos","Sugeridos",$Rl,$i);
    }
  //Se aumenta el valor de i
    $i++;
  }
  /*************************************************************************************************************
                                        Envio de correos a Clientes
  *************************************************************************************************************/
  //Realizamos la busqueda de los clientes activos
      $sql9 = "SELECT * FROM Venta WHERE Status != 'CANCELADO'";
      $S629 = $mysqli->query($sql9);
      while($S659= mysqli_fetch_array($S629)){
  //Se busca el contacto de la venta
          $i = Basicas::BuscarCampos($mysqli,"IdContact","Venta","Id",$S659[0]);
  //Se busca si el servicio esta cancelado
          $Fa = Basicas::BuscarCampos($mysqli,"Cancelacion","Contacto","id",$i);
  //Si esta dado de alta en la cancelacion del servicio no se envia
          if(empty($Fa)){
  //Se seleccionan los datos de el cliente
              $FullName = Basicas::BuscarCampos($mysqli,"Nombre","Usuario","IdContact",$i);
              $RegEmail = Basicas::BuscarCampos($mysqli,"Mail","Contacto","id",$i);
              if(!empty($RegEmail)){
                  $Rl = Basicas::BuscarCampos($mysqli,"Sugeridos","Contacto","id",$i);
      //Se crea el correo electronico para enviarlo segun los modelos
                  $mensa = Correo::Mensaje($asunto,$FullName,$empresa,$dirUrl1,$imag1,$Titulo1,'Baja',$dirUrl2,$imag2,$Titulo2,'',$dirUrl3,$imag3,$Titulo3,'',$dirUrl4,$imag4,$Titulo4,'',$i);
      //Se envia el correo electronico
                  Correo::EnviarCorreo($FullName,$RegEmail,$asunto,$mensa);
      //Se suma el valor para saber cuantas veces se ha enviado este Email
                  $Rl++;
      //Se inserta el estado en la base de datos
                  Basicas::ActCampo($mysqli,"Contacto","Sugeridos",$Rl,$i);
              }
          }
      }
/*************************************************************************************************************
                                      Envio de correos de venta
*************************************************************************************************************/
//Realizamos el envio de correos de solicitud de citas
    $sql1 = "SELECT * FROM prospectos ";
    //Realiza consulta
    $res1 = $pros->query($sql1);
    //Si existe el registro se asocia en un fetch_assoc
    foreach ($res1 as $Reg1){
        //Validamos si el usuario esta solicitando cita
        if($Reg1['Automatico'] == 1 AND $Reg1['Servicio_Interes'] != "DISTRIBUIDOR"){
          //Si el usuario no se le ha enviado correo se le asigna el valor 1
          if($Reg1['Estado'] == 0){
            //buscamos el estado de el prospecto
            $asunto = 'CONOCE KASU';
          }else{
            //buscamos el estado de el prospecto
            $asunto = Basicas::Buscar2Campos($pros,'Asunto','correos','Seguimiento',$Reg1['Estado'],'Tipo','VENTA');
          }
          //SE valida que correo se enviara
          if($asunto == "CONOCE KASU"){
            //construimos Mensaje personalizado de a cuerdo a su servicio de interes y enviamos
              if($Reg1['Servicio_Interes'] == "FUNERARIO" ){
                $servicio = "KASU Gastos Funerarios: Es un servicio que te permite pagar los gastos funerarios tuyos o de algún ser querido, pagando hoy una aportación mínima, para que el día que tú o tu ser querido fallezca no tengan que pagar nada los deudos.";
                $DirServicio = "https://www.kasu.com.mx/productos.php?Art=1";
                $Titulo1 = "https://kasu.com.mx/blog/category/tanatologia/";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }else if($Reg1['Servicio_Interes'] == "UNIVERSITARIO"){
                $servicio = "KASU Inversión universitaria: Es un servicio que te permite invertir en un fideicomiso para que al transcurso de 10 años el beneficiario que escogiste pueda pagar sus estudios universitarios.";
                $DirServicio = "https://www.kasu.com.mx/productos.php?Art=2";
                $Titulo1 = "https://kasu.com.mx/blog/category/educacion/";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }else if($Reg1['Servicio_Interes'] == "RETIRO"){
                $servicio ="KASU Retiro: Es un servicio que te permite invertir en un fideicomiso para que al tener 65 años cuentes con un respaldo financiero para que tu retiro sea mas comodo.";
                $DirServicio ="https://www.kasu.com.mx/productos.php?Art=3";
                $Titulo1 ="https://kasu.com.mx/blog/category/retiro/";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }
              //Se crea el correo electronico para enviarlo segun los modelos
              $mensa = Correo::Mensaje($asunto,$Reg1['FullName'],$empresa,$servicio,$DirServicio,$Titulo1,$Desc1,$empresa,'','','','','','','','','','','',$Reg1['Id']);
              //echo $mensa;
          }
          elseif($asunto == "¿AUN TIENES DUDAS?"){
            //construimos Mensaje personalizado de a cuerdo a su servicio de interes y enviamos
              if($Reg1['Servicio_Interes'] == "FUNERARIO" ){
                $dirUrl1 = "https://www.kasu.com.mx/productos.php?Art=1";
                $Titulo1 = "https://kasu.com.mx/blog/category/tanatologia/";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }else if($Reg1['Servicio_Interes'] == "UNIVERSITARIO"){
                $dirUrl1 = "https://www.kasu.com.mx/productos.php?Art=2";
                $Titulo1 = "https://kasu.com.mx/blog/category/educacion/";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }else if($Reg1['Servicio_Interes'] == "RETIRO"){
                $dirUrl1 ="https://www.kasu.com.mx/productos.php?Art=3";
                $Titulo1 ="https://kasu.com.mx/blog/category/retiro/";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }
            //Se crea el correo electronico para enviarlo segun los modelos
            $mensa = Correo::Mensaje($asunto,$Reg1['FullName'],$empresa,$dirUrl1,'',$Titulo1,$Desc1,'','','','','','','','','','','','',$Reg1['Id']);
            //echo $mensa;
          }
          elseif ($asunto == "CONOCENOS UN POCO MÁS") {
            //construimos Mensaje personalizado de a cuerdo a su servicio de interes y enviamos
              if($Reg1['Servicio_Interes'] == "FUNERARIO" ){
                $dirUrl2 = "https://www.kasu.com.mx/productos.php?Art=1";
                $dirUrl1 = "https://kasu.com.mx/Fideicomiso_F0003.pdf";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }else if($Reg1['Servicio_Interes'] == "UNIVERSITARIO"){
                $dirUrl2 = "https://www.kasu.com.mx/productos.phpId";
                $dirUrl1 = "https://kasu.com.mx/Fideicomiso_F0009.pdf";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }else if($Reg1['Servicio_Interes'] == "RETIRO"){
                $dirUrl2 ="https://www.kasu.com.mx/productos.php?Art=3";
                $dirUrl1 ="https://kasu.com.mx/Fideicomiso_F0010.pdf";
                $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu%20y%20decidi%20contactarlos%20puede%20darme%20mas%20informacion";
              }
            //Se aterriza la variable de session
            $Titulo1 = $Reg1['Servicio_Interes'];
            //Se crea el correo electronico para enviarlo segun los modelos
            $mensa = Correo::Mensaje($asunto,$Reg1['FullName'],$empresa,$dirUrl1,'https://kasu.com.mx/letraspeq.php',$Titulo1,$Desc1,$dirUrl2,'','','','','','','','','','','',$Reg1['Id']);
          }
          //Se envia el correo electronico
          Correo::EnviarCorreo($Reg1['FullName'],$Reg1['Email'],$asunto,$mensa);
          //se registra el valor del correo enviado
          $ValMail = Basicas::BuscarCampos($pros,'Seguimiento','correos','Asunto',$asunto);
          //Se inserta el estado en la base de datos
          Basicas::ActCampo($pros,"prospectos","Estado",$ValMail,$Reg1['Id']);
        }
    }
    //Se cierra la conexion con la base de datos
    $pros->close();
    $mysqli->close();
