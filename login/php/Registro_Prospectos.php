<?
//indicar que se inicia una sesion
session_start();
//inlcuir el archivo de funciones
require_once '../../eia/librerias.php';
date_default_timezone_set('America/Mexico_City');
$hoy = date('Y-m-d');
$HoraActual = date('H:i:s');
//Varible principal
$empresa = 'https://www.kasu.com.mx/';
/********************************************************** Registros Generales de GPS y Fingerprint ***********************************************************/
//Converson general de variables
foreach ($_POST as $key => $value){
    $asignacion="\$".$key."='".$value."';";
    eval($asignacion);
}
//Se validan los datos de GPS
if(isset($Latitud)){
    //Se crea el array que contiene los datos de GPS
    $DatGps = array (
        "Latitud"   => $Latitud,
        "Longitud"  => $Longitud,
        "Presicion" => $Presicion
    );
    //Se realiza el insert en la base de datos
    $basicas->InsertCampo($pros,"gps",$DatGps);
}
//Sevalida que el fingerprint exista
if(isset($fingerprint)){
      //Sevalida que el fingerprint exista
      $Reg2 = $basicas->BuscarCampos($pros,'Id','FingerPrint','fingerprint',$fingerprint);
      //COndicional si esta vacia la consulta
      if(empty($Reg2)){
      //Se crea el array que contiene los datos de FINGERPRINT
      $DatFinger = array (
              "fingerprint"   => $fingerprint,
              "browser"       => $browser,
              "flash"         => $flash,
              "canvas"        => $canvas,
              "connection"    => $connection,
              "cookie"        => $cookie,
              "display"       => $display,
              "fontsmoothing" => $fontsmoothing,
              "fonts"         => $fonts,
              "formfields"    => $formfields,
              "java"          => $java,
              "language"      => $language,
              "silverlight"   => $silverlight,
              "os"            => $os,
              "timezone"      => $timezone,
              "touch"         => $touch,
              "truebrowser"   => $truebrowser,
              "plugins"       => $plugins,
              "useragent"     => $useragent
          );
    //Se realiza el insert en la base de datos
    $basicas->InsertCampo($pros,"FingerPrint",$DatFinger);
    }
}
/*******************************************************************************************************************************
                                              Registro de Prospectos pagina principal
********************************************************************************************************************************/
  if(isset($_POST['Registro'])){
      //Verificamos que no exista el prospectos
      $IdProspecto = $basicas->BuscarCampos($pros,'Id','prospectos','Email',$Mail);
      //Seleccionamos el origen de el prospecto
      if(empty($Origen)){
        $Origen = "Web";
      }
      //Creamos el array para guardar los datos
      $DatProsp = array(
          "IdFingerprint"     => $fingerprint,
          "FullName"          => $name,
          "NoTel"             => $Telefono,
          "Email"             => $Mail,
          "Servicio_Interes"  => $Servicio,
          "Origen"            => $Origen,
          "Cancelacion"       => 0,
          "Automatico"        => 0,
          "Alta"              => $hoy." ".$HoraActual
      );
//Se realiza el insert en la base de datos
      $Us = $basicas->InsertCampo($pros,"prospectos",$DatProsp);
//Creamos el array para guardar los datos
      $DatEventos = array(
          "Us"            => $Us,
          "IdFInger"      => $fingerprint,
          "Evento"        => $Evento,
          "Host"          => $Host,
          "connection"    => $connection,
          "timezone"      => $timezone,
          "touch"         => $touch,
          "Cupon"         => $Cupon,
          "FechaRegistro" => $hoy." ".$HoraActual
      );
//Se realiza el insert en la base de datos
    $basicas->InsertCampo($mysqli,"Eventos",$DatEventos);
//Enviamos los correos de confirmacion de registro
    header('Location: https://kasu.com.mx/eia/EnviarCorreo.php?ProReIn='.$Us.'&Host='.$Host.'&Mail='.$Mail.'&Servicio='.$Servicio);

}
/*****************************Registro de Prospectos pagina principal******************************/
  if(isset($_POST['PerDIstri'])){
//Creamos el array para guardar los datos
    $DatProsp = array(
          "IdProspecto"   => $Usr,
          "name"          => $name,
          "Mail"          => $Mail,
          "Telefono"      => $Telefono,
          "Clabe"         => $Clabe,
          "Direccion"     => $Direccion,
          "Alta"          => $hoy." ".$HoraActual
    );
//Se realiza el insert en la base de datos
    $basicas->InsertCampo($pros,"Distribuidores",$DatProsp);
/******************  Se registra el evento  *********************/
//Creamos el array para guardar los datos
      $DatEventos = array(
          "Us"            => $Usr,
          "IdFInger"      => $fingerprint,
          "Evento"        => "Comp_Dist",
          "Host"          => $Host,
          "connection"    => $connection,
          "timezone"      => $timezone,
          "touch"         => $touch,
          "Cupon"         => $Cupon,
          "FechaRegistro" => $hoy." ".$HoraActual
      );
//Se realiza el insert en la base de datos
    $basicas->InsertCampo($mysqli,"Eventos",$DatEventos);
//Creamos la respuesta
    $Msg = "se ha registrado correctamente tus datos, te enviaremos en breve el contrato de distribuidor";
//echo "Se ha enviado un email al correo registrado.";
    header('Location: https://kasu.com.mx/index.php?Msg='.$Msg);
}
/*********************************** Registra un nuevo prospecto ***************************************/
    if(isset($_POST['prospectoNvo'])){
//Verificamos que no exista el prospectos
    $IdProspecto = $basicas->BuscarCampos($pros,'Id','prospectos','Email',$Mail);
//Si el prospectoexiste no se registra
    if(empty($IdProspecto)){
//Creamos el array para guardar los datos
          $DatProsp = array(
              "FullName"          => $name,
              "Email"             => $Mail,
              "NoTel"             => $Telefono,
              "FechaNac"          => $FechaNac,
              "Servicio_Interes"  => $Servicio,
              "Origen"            => $Origen,
              "Estado"            => 1,
              "Cancelacion"       => 0,
              "Automatico"        => 0,
              "Asignado"          => $IdAsignacion,
              "Alta"              => $hoy." ".$HoraActual
          );
//Se realiza el insert en la base de datos
      $Us = $basicas->InsertCampo($pros,"prospectos",$DatProsp);
//El prospecto ya se registro previamente
          $asunto = "CONOCE KASU";
          $Evento = "1MAILPROS";
      }else{
//EL prospecto no se ha registrado
          $asunto = "¿AUN TIENES DUDAS?";
          $Evento = "2MAILPROS";
          $Us = $IdProspecto;
//Se actualiza el Estado en la base
      $basicas->ActCampo($pros,"prospectos","Estado",2,$IdProspecto);
      }
//Creamos el array para guardar los datos
      $DatEventos = array(
          "Us"            => $Us,
          "IdFInger"      => $fingerprint,
          "Evento"        => $Evento,
          "Host"          => $Host,
          "connection"    => $connection,
          "timezone"      => $timezone,
          "touch"         => $touch,
          "Cupon"         => $Cupon,
          "FechaRegistro" => $hoy." ".$HoraActual
      );
//Se realiza el insert en la base de datos
      $basicas->InsertCampo($mysqli,"Eventos",$DatEventos);
      //Redireccionamos a la pagina del pago
      header('Location: https://kasu.com.mx'.$Host.'?Ml=5&name='.$name);
}
/*********************************** Descarga Cotizacion a prospecto ***************************************/
    if(isset($_POST['DescargaPres'])){
      //codigo por si registra un pago un ejecutivo superior
      if(empty($IdVendedor)){
          $Usuario = $_SESSION["Vendedor"];
      }else{
          $Usuario = $IdVendedor;
      }
      //seleccionamos la informacion de la BD correspondiente al email del user
      $sql = "SELECT * FROM prospectos WHERE  Id = '$Id'";
      //Realiza consulta
      $res = mysqli_query($pros, $sql);
      //Si existe el registro se asocia en un fetch_assoc
      if($Reg=mysqli_fetch_assoc($res)){
          //Creamos el array para guardar los datos
            $DatEventos = array(
                "IdProspecto"   => $Id,
                "IdUser"        => $Usuario,
                "a0a29"         => $a0a29,
                "a30a49"        => $a30a49,
                "a50a54"        => $a50a54,
                "a55a59"        => $a55a59,
                "a60a64"        => $a60a64,
                "a65a69"        => $a65a69,
                "Univ"          => $Univ,
                "plazo"         => $plazo,
                "Pago"          => $Pago,
                "FechaRegistro" => $hoy." ".$HoraActual
            );
          //Se realiza el insert en la base de datos
          $IdPros = $basicas->InsertCampo($pros,"PrespEnviado",$DatEventos);
          //Direccion de descarga
          $dirUrl1 = "https://kasu.com.mx/login/Generar_PDF/Cotizacion_pdf.php";
      }
//Se redirecciona a la pagina de los presupuestos
    header('Location: '.$dirUrl1.'?Host='.$Host.'&name='.$name.'&busqueda='.$IdPros);
}
/*********************************** Envia Cotizacion a prospecto ***************************************/
    if(isset($_POST['EnviaPres'])){
      //codigo por si registra un pago un ejecutivo superior
      if(empty($IdVendedor)){
          $Usuario = $_SESSION["Vendedor"];
      }else{
          $Usuario = $IdVendedor;
      }
      //seleccionamos la informacion de la BD correspondiente al email del user
      $sql = "SELECT * FROM prospectos WHERE  Id = '$Id'";
      //Realiza consulta
      $res = mysqli_query($pros, $sql);
      //Si existe el registro se asocia en un fetch_assoc
      if($Reg=mysqli_fetch_assoc($res)){
          //Creamos el array para guardar los datos
            $DatEventos = array(
                "IdProspecto"   => $Id,
                "IdUser"        => $Usuario,
                "a0a29"         => $a0a29,
                "a30a49"        => $a30a49,
                "a50a54"        => $a50a54,
                "a55a59"        => $a55a59,
                "a60a64"        => $a60a64,
                "a65a69"        => $a65a69,
                "Univ"          => $Univ,
                "plazo"         => $plazo,
                "Pago"          => $Pago,
                "FechaRegistro" => $hoy." ".$HoraActual
            );
          //Se realiza el insert en la base de datos
          $IdPros = $basicas->InsertCampo($pros,"PrespEnviado",$DatEventos);
          //Direccion de descarga
          $dirUrl1 = "https://kasu.com.mx/eia/EnviarCorreo.php";
      }
//Se redirecciona a la pagina de los presupuestos
    header('Location: '.$dirUrl1.'?Host='.$Host.'&EnCoti='.$IdPros);
}
/**************************************************************************************************************
            realiza el registro de automatico de los correos de seguimiento a los clientes
***************************************************************************************************************/
    if(isset($_POST['Autom'])){
      //Creamos el array para guardar los datos
            $DatEventos = array(
                "Us"            => $IdProspecto,
                "IdFInger"      => $fingerprint,
                "Evento"        => "AltaAuto",
                "Host"          => $Host,
                "connection"    => $connection,
                "timezone"      => $timezone,
                "touch"         => $touch,
                "Cupon"         => $Cupon,
                "FechaRegistro" => $hoy." ".$HoraActual
            );
      //Se realiza el insert en la base de datos
            $basicas->InsertCampo($mysqli,"Eventos",$DatEventos);
     //Se envia el correo en automatico
            $basicas->ActCampo($pros,"prospectos","Automatico",1,$IdProspecto);
    //Se redirecciona a la pagina de inicio
            header('Location: https://kasu.com.mx'.$Host.'?Ml=5&name='.$name);
    }
/*********************************** Registra una cita con un prospecto proveniente de correo ***************************************/
    if(isset($_POST['Cita'])){
//Creamos el array para guardar los datos
      $CitaReg = array(
          "IdProspecto"       => $IdProspecto,
          "Telefono"          => $Telefono,
          "Correo"            => $Mail,
          "FechaCita"         => $FechaCita." ".$HoraCita,
          "Rastreo"           => $Rastreo,
          "FechaRegistro"     => $hoy." ".$HoraActual
      );
//Se realiza el insert en la base de datos
      $Cita = $basicas->InsertCampo($pros,"citas",$CitaReg);
//El prospecto ya se registro previamente
      $asunto = "CITA TELEFONICA";
      $Evento = "CitaRegis";
//Creamos el array para guardar los datos
      $DatEventos = array(
          "Us"            => $IdProspecto,
          "IdFInger"      => $fingerprint,
          "Evento"        => $Evento,
          "Host"          => $Host,
          "connection"    => $connection,
          "timezone"      => $timezone,
          "touch"         => $touch,
          "Cupon"         => $Cupon,
          "FechaRegistro" => $hoy." ".$HoraActual
      );
//Se realiza el insert en la base de datos
      $basicas->InsertCampo($mysqli,"Eventos",$DatEventos);
}
/*******************************************Baja de prospecto*******************************************/
if(isset($_POST['BajaEmp'])){
//Creamos el array para guardar los datos
    $DatEventos = array(
        "Us"            => $IdProspecto,
        "Evento"        => $MotivoBaja,
        "Host"          => $Host,
        "FechaRegistro" => $hoy." ".$HoraActual
    );
//Se realiza el insert en la base de datos
    $basicas->InsertCampo($mysqli,"Eventos",$DatEventos);
//Se inserta el estado en la base de datos
    $basicas->ActCampo($pros,"prospectos","Cancelacion",1,$IdProspecto);
//Se envia a la pagina de origen
    header('Location: https://kasu.com.mx'.$Host.'?Ml=4&name='.$name);
}
/*******************************************Asignacion de prospecto*******************************************/
if(isset($_POST['AsigVende'])){
//Creamos el array para guardar los datos
    $DatEventos = array(
        "Us"            => $IdProspecto,
        "Evento"        => "Asigancion",
        "Host"          => $Host,
        "FechaRegistro" => $hoy." ".$HoraActual
    );
//Se realiza el insert en la base de datos
    $basicas->InsertCampo($mysqli,"Eventos",$DatEventos);
//Se inserta el estado en la base de datos
    $basicas->ActCampo($pros,"prospectos","Asignado",$NvoVend,$IdProspecto);
//Se envia a la pagina de origen
    header('Location: https://kasu.com.mx'.$Host.'?Ml=5&name='.$name);
}
/*************************************************************************
          realiza La Actualiz de los datos de un prospecto
**************************************************************************/
if (isset($_POST['CamDat'])) {
    //Se obtienen los datos de contacto
    $sql = "SELECT * FROM prospectos WHERE Id = '".$IdProspecto."'";
    //Realiza consulta
    $recs = mysqli_query($pros, $sql);
    //Si existe el registro se asocia en un fetch_assoc
    if($Recg=mysqli_fetch_assoc($recs)){
    //se valida que los datos se hayan mofificado
        if($Recg['FullName'] != $FullName){
          $basicas->ActTab($pros,"prospectos","FullName",$FullName,"Id",$IdProspecto);
        }
        if($Recg['NoTel'] != $NoTel){
          $basicas->ActTab($pros,"prospectos","NoTel",$NoTel,"Id",$IdProspecto);
        }
        if($Recg['Email'] != $Email){
          $basicas->ActTab($pros,"prospectos","Email",$Email,"Id",$IdProspecto);
        }
        if($Recg['Direccion'] != $Direccion){
          $basicas->ActTab($pros,"prospectos","Direccion",$Direccion,"Id",$IdProspecto);
        }
        if($Recg['Servicio_Interes'] != $Servicio_Interes){
          $basicas->ActTab($pros,"prospectos","Servicio_Interes",$Servicio_Interes,"Id",$IdProspecto);
        }
    }
    //Redireccionamos a la pagina del pago
    header('Location: https://kasu.com.mx'.$Host.'?Ml=5&name='.$name);
}
/*********************************Enviar correos por flujo de ventas*********************************/
/*/Estructura de correos
if(isset($_POST['interno']) || isset($Evento)){
          header('Location: https://kasu.com.mx'.$Host.'?Ml=1&name='.$name.'&Msg='.$Msg);
    //Se cierra la conexion con la base de datos
$pros->close();
}
*/
/*********************************  Registra el prospecto pr la pagina principal  *********************************/
if (isset($_POST['FormCotizar'])) {
    //se busca que el cliente no este duplicado como poliza vendida
    echo $OPsdA = $basicas->BuscarCampos($mysqli,"id","Usuario","ClaveCurp",$_POST['CURP']);
    //se busca que el cliente no este duplicado como prospecto registrado
    echo $OPsdB = $basicas->BuscarCampos($pros,"Id","prospectos","Curp",$_POST['CURP']);
    //si el cliente esta duplicado se activa este if  $OPsdA >= 1 AND $OPsdB >= 1
    if($OPsdA >= 1 AND $OPsdB >= 1){
        //Registro de Mensaje a mostrar
        $mensaje = "La clave Curp que registraste ya se encuentra registrada";
        //Redireccionamos a la pagina del pago
        header('Location: https://kasu.com.mx?Msg='.$mensaje);
    }else{
        //Registro de el prospecto en la plataforma de prospectos
        $ArrayRes = Seguridad::peticion_get($_POST['CURP']);
        //Validamos que el usuario sea correcto
        if($ArrayRes["Response"] == "correct" AND $ArrayRes["StatusCurp"] != "BD"){
            //Obtenemos el nombre de el cliente
            $FullName = $ArrayRes["Nombre"]." ".$ArrayRes["Paterno"]." ".$ArrayRes["Materno"];
            //Creamos el array para guardar los datos
            $DatProsp = array(
                "IdFingerprint"     => $fingerprint,
                "FullName"          => $FullName,
                "Curp"          		=> $_POST['CURP'],
                "Email"             => $_POST['Email'],
                "Servicio_Interes"  => $_POST['servicio'],
                "Origen"            => "Index",
                "Cancelacion"       => 0,
                "Automatico"        => 0,
                "Alta"              => date('Y-m-d')." ".date('H:i:s')
            );
            //Se realiza el insert en la base de datos
            $basicas->InsertCampo($pros,"prospectos",$DatProsp);
            //Obtenemos el costo de la poliza
            $edad = $basicas->ObtenerEdad($_POST['CURP']);
            $SubProd = $basicas->ProdFune($edad);
            $Costo = $basicas->BuscarCampos($mysqli,"Costo","Productos","Producto",$SubProd);
            //Registro de Mensaje a mostrar
            $mensaje = "Esta poliza de gastos funerarios KASU tiene un costo de".money_format('%.2n', $Costo);
            //Redireccionamos a la pagina del pago
            header('Location: https://kasu.com.mx?Msg='.$mensaje);
        }elseif($ArrayRes["Response"] == "Error"){
            //Registro de Mensaje a mostrar
            $mensaje = "La clave Curp que registraste no es valida, porfavor verificala";
            //Redireccionamos a la pagina del pago
            header('Location: https://kasu.com.mx?Msg='.$mensaje);
        }
    }
}
