<?php
  //indicar que se inicia una sesion
  session_start();
  //inlcuir el archivo de funciones
  require_once '../../eia/librerias.php';
  date_default_timezone_set('America/Mexico_City');
  //Variables de tiempo
  $hoy = date('Y-m-d');
  $HoraActual = date('H:i:s');
 /************************************* Pagar comisiones de un periodo*************************************/
  if (isset($_POST['PagoCom'])){
    foreach($_POST as $key => $value){
      $asignacion = "\$" . $key . "='" . $value . "';";
      eval($asignacion);
    }
    //Se crea el array que contiene los datos de los Pagos
      $DatGps = array(
          "Cantidad"      => $Cantidad,
          "IdVendedor"    => $IdEmpleado,
          "UsrResgistra"  => $_SESSION["Vendedor"],
          "Banco"         => $Cuenta,
          "Referencia"    => $RefDepo,
          "fechaRegistro" => $hoy." ".$HoraActual
      );
    //Se realiza el insert en la base de datos
      Basicas::InsertCampo($mysqli,"Comisiones_pagos",$DatGps);
    //Redireccionamos a pantalla de empleado
    header('Location: https://kasu.com.mx'.$Host.'?Vt=1&name='.$name);
  }
  /********************************* Reasignar el ejecutivo a otro superior *********************************/
  if (isset($_POST['CambiVend'])){
    foreach($_POST as $key => $value){
      $asignacion = "\$" . $key . "='" . $value . "';";
      eval($asignacion);
    }
    //Validamos que el usuario no este asignado al mismo equipo
    $IdBAse = Basicas::BuscarCampos($mysqli,"Equipo","Empleados","Id",$IdEmpleado);
    //comparar los usuarios
    if($IdBAse != $NvoVend){
      //Se actualiza el valor de el equipo
      Basicas::ActCampo($mysqli,"Empleados","Equipo",$NvoVend,$IdEmpleado);
    }
    //Redireccionamos a pantalla de empleado
    header('Location: https://kasu.com.mx'.$Host.'?Vt=1&name='.$name);
  }
  /***************************************** Dar de baja al ejecutivo *****************************************/
  if (isset($_POST['BajaEmp'])){
    foreach($_POST as $key => $value){
      $asignacion = "\$" . $key . "='" . $value . "';";
      eval($asignacion);
    }
    //Se obtiene el contacto de el cliente
    $IdBAse = Basicas::BuscarCampos($mysqli,"IdContacto","Empleados","Id",$IdEmpleado);
    //Si actualizan los datos de el empleado en la tabla Usuario
    Basicas::ActCampo($mysqli,"Empleados","Nombre","Vacante",$IdEmpleado);
    Basicas::ActCampo($mysqli,"Empleados","IdUsuario","Usuario",$IdEmpleado);
    Basicas::ActCampo($mysqli,"Empleados","IdContacto",NULL,$IdEmpleado);
    Basicas::ActCampo($mysqli,"Empleados","Pass",NULL,$IdEmpleado);
    Basicas::ActCampo($mysqli,"Empleados","Equipo",NULL,$IdEmpleado);
    Basicas::ActCampo($mysqli,"Empleados","Sucursal",NULL,$IdEmpleado);
    //Se actualiza el motivo de baja en la tabla contactos
    Basicas::ActCampo($mysqli,"Contacto","Motivo",$MotivoBaja,$IdBAse);
    //Redireccionamos a pantalla de empleado
    header('Location: https://kasu.com.mx'.$Host.'?Vt=1&name='.$name);
  }
  /************************* Crea el Empleado y envia correo para usuario y contraseña*************************/
  if (isset($_POST['CreaEmpl'])){
    foreach($_POST as $key => $value){
      $asignacion = "\$" . $key . "='" . $value . "';";
      eval($asignacion);
    }
  //Creamos el codigo de un solo uso para el registro de el cliente
      $Sg1t = substr($Nombre, 0, 3);
      $Sg2t = substr($Nombre, -3);
      $Dil = $Sg1t.$Sg2t;
      $DirUrl = strtoupper($Dil);
      $envTs = "Tu usuario es ".$DirUrl;
      $dRc = mt_rand();
      $dirUrl1 = base64_encode($dRc);
      //Se crea el array que contiene los datos de registro
      $DatContac = array (
          "Usuario"   => $_SESSION["Vendedor"],
          "Host"      => $Host,
          "Mail"      => $Email,
          "Telefono"  => $Telefono,
          "Direccion" => $Direccion,
          "Producto"  => "Empleado"
      );
      //Se realiza el insert en la base de datos
      $uSR = Basicas::InsertCampo($mysqli,"Contacto",$DatContac);
      //Registramos datos para nivel 7
      /*if($Nivel == 7){
        //Creamos el correo para enviarlo
            $Mensaje = Correo::Mensaje('ALTA DISTRIBUIDOR',$Nombre,$envTs,$dirUrl1,$DirUrl,$uSR,'','','','','','','','','','','','','','');
        //Se envia el correo de el usuario para q registre sus datos
            Correo::EnviarCorreo($Nombre,$Email,'ALTA DISTRIBUIDOR',$Mensaje);
      }else{
        //Creamos el correo para enviarlo
            $Mensaje = Correo::Mensaje('ALTA DE COLABORADOR',$Nombre,$envTs,$dirUrl1,$DirUrl,'','','','','','','','','','','','','','','');
        //Se envia el correo de el usuario para q registre sus datos
            Correo::EnviarCorreo($Nombre,$Email,'ALTA DE COLABORADOR',$Mensaje);
      }*/
  //Se busca otra configuracion para registrar el usuario
      $Reg = Basicas::Buscar2Campos($mysqli,"Id","Empleados","Sucursal",0,"Nivel",$Nivel);
  //Si existe el registro se asocia en un fetch_assoc
      if(!empty($Reg)){
      //Se realiza la actualizacion en la base
      Basicas::ActCampo($mysqli,"Empleados","Nombre",$Nombre,$Reg);
      Basicas::ActCampo($mysqli,"Empleados","IdContacto",$uSR,$Reg);
      Basicas::ActCampo($mysqli,"Empleados","IdUsuario",NULL,$Reg);
      Basicas::ActCampo($mysqli,"Empleados","Pass",$dRc,$Reg);
      Basicas::ActCampo($mysqli,"Empleados","Equipo",$Lider,$Reg);
      Basicas::ActCampo($mysqli,"Empleados","Sucursal",$Sucursal,$Reg);
      Basicas::ActCampo($mysqli,"Empleados","FechaAlta",$hoy,$Reg);
      Basicas::ActCampo($mysqli,"Empleados","Cuenta",$Cuenta,$Reg);
      }
      //Registramos datos para nivel 7
      if($Nivel == 7){
        //Armamos la descarga de el contrato
        $contra = '&Add='.$uSR;
        //damos de baja el prospecto en la base de datos de Prospectos
        Basicas::ActCampo($pros,"prospectos","Cancelacion",1,$IdProspecto);
      }
  //Redireccionamos a pantalla de empleado
    header('Location: https://kasu.com.mx'.$Host.'?Ml=1&name='.$name.$contra);
  }
  /************************* Crea el Empleado y envia correo para usuario y contraseña*************************/
  if (isset($_POST['ReenCOntra'])){
    foreach($_POST as $key => $value){
      $asignacion = "\$" . $key . "='" . $value . "';";
      eval($asignacion);
    }
  //Creamos el codigo de un solo uso para el registro de el cliente
      $dRc = mt_rand();
      $dirUrl1 = base64_encode($dRc);
      $DirUrl = "https://kasu.com.mx/login/index.php?data=".$dirUrl1."&Usr=".$IdUsuario;
      //Se realiza la actualizacion en la base
      Basicas::ActCampo($mysqli,"Empleados","Pass",$dRc,$Id);
  //Creamos el correo para enviarlo
      $Mensaje = Correo::Mensaje('RESTABLECIMIENTO DE CONTRASEÑA',$Nombre,$DirUrl,'','','','','','','','','','','','','','','','','');
  //Se envia el correo de el usuario para q registre sus datos
      Correo::EnviarCorreo($Nombre,$Email,'RESTABLECIMIENTO DE CONTRASEÑA',$Mensaje);
  //Redireccionamos a pantalla de empleado
      header('Location: https://kasu.com.mx'.$Host.'?Ml=1&name='.$name);
  }
/******************************** Genera la contraseña y el usuario *********************************/
  if(!empty($_POST['GenCont'])){
    foreach($_POST as $key => $value){
      $asignacion = "\$" . $key . "='" . $value . "';";
      eval($asignacion);
    }
  //Validamos que el usuario haya registrado bien las dos contraseñas
    if($PassWord1 === $PassWord2){
      //Decodificamos la contraseña
      $d4at2a = base64_decode($data);
      //Buscamos los datos para validar que se puede actualizar el registro
      $Pass = Basicas::BuscarCampos($mysqli,"Id","Empleados","Pass", $d4at2a);
      //Re hace el insert en la base de datos
      if(!empty($Pass)){
        /********************* Bloque de registro de eventos ***********************/
            //Se crea el array que contiene los datos de GPS
                $DatGps = array(
                    "Latitud"    => $Latitud,
                    "Longitud"   => $Longitud,
                    "Presicion"  => $Presicion
                );
            //Se realiza el insert en la base de datos
             $gpsLogin=Basicas::InsertCampo($mysqli,"gps",$DatGps);
            //Valida los datos para determinar si ya se registro el FingerPrint
            if(empty(Basicas::BuscarCampos($mysqli,"id","FingerPrint","fingerprint",$fingerprint))){
                //Se crea el array que contiene los datos de FINGERPRINT
                        $DatFinger = array(
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
                    Basicas::InsertCampo($mysqli, "FingerPrint", $DatFinger);
                    }
                    if(empty($Evento)){
                      $Evento = "AltaPass";
                    }
                //Se crea el array que contiene los datos para REGISTRO DE EVENTOS
                $DatEventos = array(
                    "IdFInger"  => $fingerprint,
                    "Idgps"     => $gpsLogin,
                    "Host"      => $Host,
                    "Evento"    => $Evento,
                    "Usuario"   => $Usuario,
                "FechaRegistro" => $hoy." ".$HoraActual
                );
                //Se realiza el insert en la base de datos
                Basicas::InsertCampo($mysqli, "Eventos", $DatEventos);
        /********************* Bloque de registro de eventos **********************/
        //Convertir el Pasword a sha256 para comprarlo
        $PassSHa = hash('sha256', $PassWord1);
        Basicas::ActCampo($mysqli,"Empleados","IdUsuario",$User,$Pass);
        Basicas::ActCampo($mysqli,"Empleados","Pass",$PassSHa,$Pass);
        //EL usuario re registro exitosamente
        $dta = 3;
      }else{
        //EL usuario ya ha sido utilizado
        $dta = 1;
      }
    }else{
      //imprimios un alert de notificacion de error
      $dta = 2;
    }
    header('Location: https://kasu.com.mx'.$Host.'?Vt=1&Data='.$dta);
  }
  /*************************************************************************
            realiza La Actualiz de los datos de un empleado
  **************************************************************************/
  if (isset($_POST['CamDat'])) {
    foreach ($_POST as $key => $value) {
        $asignacion = "\$" . $key . "='" . $value . "';";
        eval($asignacion);
    }
      //Se obtienen los datos de contacto
      $sql = "SELECT * FROM Contacto WHERE Id = '".$IdContact."'";
      //Realiza consulta
      $recs = mysqli_query($mysqli, $sql);
      //Si existe el registro se asocia en un fetch_assoc
      if($Recg=mysqli_fetch_assoc($recs)){
      //se valida que los datos se hayan mofificado
          if($Recg['Direccion'] != $Direccion){ $val++; }
          if($Recg['Telefono'] != $Telefono){ $val++; }
          if($Recg['Mail'] != $Mail){ $val++; }
      }
      $IdVenta = Basicas::BuscarCampos($mysqli,"Id","Venta","IdContact",$IdContact);
      if(!empty($IdVenta)){
        //Se valida si el empleado ademas es cliente
        $SDTM = Basicas::BuscarCampos($mysqli,"Producto","Venta","Id",$IdVenta);
      }
      //Se valida si se realizo algun cambio
      if($val > 0){
      //Se registran el servicio prestado
        $Pripg = array (
              "Usuario"       => $_SESSION["Vendedor"],
              "Host"          => $Host,
              "Mail"          => $Mail,
              "Telefono"      => $Telefono,
              "Direccion"     => $Direccion,
              "Producto"      => $SDTM
        );
      //Insertar los datos en la base
      $NvoCnc = Basicas::InsertCampo($mysqli,"Contacto",$Pripg);
      //Se actualiza en la tabla de el uduario el Id del contacto
      Basicas::ActTab($mysqli,"Usuario","IdContact",$NvoCnc,"IdContact",$IdContact);
      //Validamos que el usuario sea cliente
        if(!empty($IdVenta)){
            //Se actualiza el valor del contacto en la venta
            Basicas::ActCampo($mysqli,"Venta","IdContact",$NvoCnc,$IdVenta);
        }
      }
      //Se envia un email que permite al cliente saber q se cambiaron sus datos
      $Mensaje = Correo::Mensaje("ACTUALIZACION DE DATOS",$Nombre,'https://kasu.com.mx/ActualizacionDatos/index.php','','','','','','','','','','','','','','','','',$IdVenta);
      Correo::EnviarCorreo($Nombre,$Mail,"ACTUALIZACION DE DATOS",$Mensaje);
      //Redireccionamos a la pagina del pago
      header('Location: https://kasu.com.mx'.$Host);
  }
  /*************************************************************************
            realiza el registro de un problema
  **************************************************************************/
  if (isset($_POST['Reporte'])) {
    foreach ($_POST as $key => $value) {
        $asignacion = "\$" . $key . "='" . $value . "';";
        eval($asignacion);
    }
    //Se registran el servicio prestado
      $Pripg = array (
            "Usuario"  => $_SESSION["Vendedor"],
            "Problema" => $problema
      );
    //Insertar los datos en la base
    $NvoCnc = Basicas::InsertCampo($mysqli,"Problemas",$Pripg);
    $msg = 'Gracias por enviarnos tu reporte';
    //Impresion de comprobaciones
    $msg = base64_encode($msg);
    //Redireccionamos a la pagina del pago
    header('Location: https://kasu.com.mx'.$Host.'?Msg='.$msg);
}
