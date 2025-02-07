<?php
/********************************************************************************************************************************************
                        ESTE ARCHIVO REALIZA LOS REGISTROS DE INTERACCION GENERAL CON LA PLATAFORMA DE CRM
********************************************************************************************************************************************/
  //indicar que se inicia una sesion
  session_start();
  //inlcuir el archivo de funciones
  require_once '../../eia/librerias.php';
  date_default_timezone_set('America/Mexico_City');
  //Variables de tiempo
  $hoy = date('Y-m-d');
  $HoraActual = date('H:i:s');
  /**************************************************************************
          INICIO Validacion de Usuario con contraseña del cliente
  **************************************************************************/
  if (isset($_POST['Login'])){
    foreach($_POST as $key => $value){
      $asignacion = "\$" . $key . "='" . $value . "';";
      eval($asignacion);
    }
    //Convertir el Pasword a sha256 para comprarlo
    $PassSHa = hash('sha256', $PassWord);
    //Buscamos la contraseña del usuario
    $Pass = Basicas::BuscarCampos($mysqli,"Pass","Empleados","IdUsuario", $Usuario);
    //COmparar el hash para validar el usuario y redireccionar
    if ($PassSHa !== $Pass) {
          //Creamos un alert y regresamos a la pagina de login
          echo "<script type='text/javascript'>alert('Usuario o contraseña incorrectos, intente de nuevo');</script>";
          header("Refresh: 0; URL= https://kasu.com.mx/login/");
      }else{
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
        //Se crea el array que contiene los datos para REGISTRO DE EVENTOS
        $DatEventos = array(
            "IdFInger"  => $fingerprint,
            "Idgps"     => $gpsLogin,
            "Contacto"  => Basicas::BuscarCampos($mysqli,"IdContacto","Empleados","IdUsuario",$Usuario),
            "Host"      => $Host,
            "Evento"    => "Ingreso",
            "Usuario"   => $Usuario,
        "FechaRegistro" => $hoy." ".$HoraActual
        );
        //Se realiza el insert en la base de datos
        Basicas::InsertCampo($mysqli, "Eventos", $DatEventos);
/********************* Bloque de registro de eventos **********************/
        //Buscar el Contacto del Usuario
        $_SESSION["Vendedor"] = $Usuario;
        //Redireccionamos a la pagina de inicio
        header('Location: https://kasu.com.mx/login/Pwa_Principal.php');
    }
}
  /*************************************************************************
  realiza el filtrado de los clientes para mostrarlos como cartera de clientes
  **************************************************************************/
  if (isset($_POST['Relog'])) {
    foreach ($_POST as $key => $value) {
      $asignacion = "\$" . $key . "='" . $value . "';";
      eval($asignacion);
    }
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
    if (empty(Basicas::BuscarCampos($mysqli,"id","FingerPrint","fingerprint",$fingerprint))) {
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
            Basicas::InsertCampo($mysqli,"FingerPrint",$DatFinger);
            }
        //Se crea el array que contiene los datos para REGISTRO DE EVENTOS
        $DatEventos = array(
            "IdFInger"  => $fingerprint,
            "Idgps"     => $gpsLogin,
            "Host"      => $Host,
            "Evento"    => $checkdia,
            "Usuario"   => $_SESSION["Vendedor"],
            "FechaRegistro" => $hoy." ".$HoraActual
        );
        //Se realiza el insert en la base de datos
        Basicas::InsertCampo($mysqli, "Eventos", $DatEventos);
/********************* Bloque de registro de eventos **********************/
        //Se valida que esta regsitrando
        if ($checkdia == "Salida"){
          //Limpiar la sesion
          unset($_SESSION["Entrada"]);
        } else {
          //Se crea la sesion
          $_SESSION["Entrada"] = $checkdia;
        }
    header('Location: https://kasu.com.mx/login/Pwa_Principal.php');
  }
  /*************************************************************************
            realiza el registro de una salida de la plataforma
  **************************************************************************/
  if (isset($_POST['Salir'])) {
    foreach ($_POST as $key => $value) {
      $asignacion = "\$" . $key . "='" . $value . "';";
      eval($asignacion);

    }
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
    if (empty(Basicas::BuscarCampos($mysqli,"id","FingerPrint","fingerprint",$fingerprint))) {
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
            Basicas::InsertCampo($mysqli,"FingerPrint",$DatFinger);
            }
        //Se crea el array que contiene los datos para REGISTRO DE EVENTOS
        $DatEventos = array(
            "IdFInger"      => $fingerprint,
            "Idgps"         => $gpsLogin,
            "Host"          => $Host,
            "Evento"        => $Evento,
            "Usuario"       => $_SESSION["Vendedor"],
            "FechaRegistro" => $hoy." ".$HoraActual
        );
        //Se realiza el insert en la base de datos
        Basicas::InsertCampo($mysqli, "Eventos", $DatEventos);
/********************* Bloque de registro de eventos **********************/
    header('Location: https://kasu.com.mx/login/logout.php');
  }
  /*************************************************************************
            realiza el registro de un PAGO de un cliente
  **************************************************************************/
  if (isset($_POST['Pago'])) {
    foreach ($_POST as $key => $value) {
      $asignacion = "\$" . $key . "='" . $value . "';";
      eval($asignacion);
    }
    //Se crea el array que contiene los datos de GPS
        $DatGps = array(
            "Latitud"    => $Latitud,
            "Longitud"   => $Longitud,
            "Presicion"  => $Presicion
        );
    //Se realiza el insert en la base de datos
     $GpsPag = Basicas::InsertCampo($mysqli,"gps",$DatGps);
    //Se valida que no exista pago a esta venta
     $ActVta = Basicas::ConUno($mysqli,"Pagos","IdVenta",$IdVenta);
    //Contar los valores y cambiar el status de venta
     if(empty($ActVta)){
    //Se cambia el status de la venta a COBRANZA
        Basicas::ActCampo($mysqli,"Venta","Status","COBRANZA",$IdVenta);
     }
     //Variable de el while
      $a = 1;
      $Cont = 2;
      $stat = "Pago";
    //Se identifica si se registrata la mora o el pago
      if($Status == "Normal"){
        $Cont = 1;
      }else{
        //Se quita la mora a la cantidad
        $Cantidad = $Cantidad - $Status;
      }
      while ($a <= $Cont) {
        //Cuando este en la segunda interacion cambia registro de mora
        if($a == 2){
          $stat = "Mora";
          $Cantidad = $Status;
        }
        //codigo por si registra un pago un ejecutivo superior
        if(empty($IdVendedor)){
            $Usuario = $_SESSION["Vendedor"];
        }else{
            $Usuario = $IdVendedor;
        }
        //Se registra el array de registro de pago
        $DatPago = array(
            "IdVenta"       => $IdVenta,
            "Referencia"    => $Referencia,
            "Usuario"       => $Usuario,
            "Idgps"         => $GpsPag,
            "Cantidad"      => $Cantidad,
            "Metodo"        => "Cobro",
            "status"        => $stat,
            "FechaRegistro" => $hoy." ".$HoraActual
        );
        //Se realiza el insert en la base de datos
        Basicas::InsertCampo($mysqli,"Pagos",$DatPago);
        //Se eleva el valor de a
        $a++;
      }
    /*Registro de las promesas de pago de el cliente*/
    //Se busca el valor de los pagos de el cliente
        $sd = Basicas::BuscarCampos($mysqli,"CostoVenta","Venta","Id",$IdVenta);
        //Se buscan los Pagos
        $sf = Basicas::BuscarCampos($mysqli,"NumeroPagos","Venta","Id",$IdVenta);
        //Se establece el pago
        $pago = $sd/$sf;
        $m = $pago/100;
        $mora = $m*10;
        //Se registran las promesas de pagos
    		$Pripg = array (
    				"IdVta"     => $IdVenta,
    				"FechaPago" => $Promesa,
    				"pago"    	=> $pago,
    				"Mora"    	=> $mora,
    				"FechaReg"  => date('Y-m-d'),
    				"User"     	=> Basicas::BuscarCampos($mysqli,"Usuario","Venta","Id",$IdVenta)
    		);
    		//Insertar los datos en la base
    		Basicas::InsertCampo($mysqli,"PromesaPago",$Pripg);
        //Calculamos si el credito se ha pagado y se cambia el status de la venta
        $SubTotl = Financieras::SaldoCredito($mysqli,$IdVenta);
        //Se valida que el pago sea menor a cero o igual a cero
        if($SubTotl <= 0 ){
        //Se cambia el status de la venta a ACTIVACION
            Basicas::ActCampo($mysqli,"Venta","Status","ACTIVACION",$IdVenta);
        }
        //Redireccionamos a la pagina del pago
        header('Location: https://kasu.com.mx'.$Host.'?Ml=5&Vt=3&name='.$name);
  }
  /*************************************************************************
            realiza el registro de una promesa de pago
  **************************************************************************/
  if (isset($_POST['PromPago'])) {
    foreach ($_POST as $key => $value) {
      $asignacion = "\$" . $key . "='" . $value . "';";
      eval($asignacion);
    }
    //Se crea el array que contiene los datos de GPS
        $DatGps = array(
            "Latitud"    => $Latitud,
            "Longitud"   => $Longitud,
            "Presicion"  => $Presicion
        );
    //Se realiza el insert en la base de datos
      $GpsPag = Basicas::InsertCampo($mysqli,"gps",$DatGps);
    /*Registro de las promesas de pago de el cliente*/
    //Se registran las promesas de pagos
      $Pripg = array (
            "IdVta"     => $IdVenta,
            "FechaPago" => $Promesa,
            "pago"    	=> $Cantidad,
            "FechaReg"  => date('Y-m-d'),
            "User"     	=> $_SESSION["Vendedor"]
        );
        //Insertar los datos en la base
        Basicas::InsertCampo($mysqli,"PromesaPago",$Pripg);
        //Se cambia a COBRANZA
        if($Status == "PREVENTA" || $Status == "CANCELADO"){
          Basicas::ActCampo($mysqli,"Venta","Status","COBRANZA",$IdVenta);
        }
        //Redireccionamos a la pagina del pago
        header('Location: https://kasu.com.mx'.$Host.'?Ml=5&Vt=1&name='.$name);
  }
  /*************************************************************************
            realiza el registro de un servicio realizado
  **************************************************************************/
  if (isset($_POST['AsiServ'])) {
    foreach ($_POST as $key => $value) {
      $asignacion = "\$" . $key . "='" . $value . "';";
      eval($asignacion);
    }
    //Se registran el servicio prestado
      $Pripg = array (
            "Usuario"       => $_SESSION["Vendedor"],
            "IdVenta"       => $IdVenta,
            "Nombre"        => Basicas::BuscarCampos($mysqli,"Nombre","Venta","Id",$IdVenta),
            "Firma"    	    => Basicas::BuscarCampos($mysqli,"IdFIrma","Venta","Id",$IdVenta),
            "Prestador"     => $Prestador,
            "Telefono"      => $Telefono,
            "Estado"        => $Estado,
            "Municipio"     => $Municipio,
            "Cp"    	      => $Cp,
            "EmpFune"       => $EmpFune,
            "Costo"         => $Costo,
            "FechaEntrega"  => date('Y-m-d')
        );
        //Insertar los datos en la base
        Basicas::InsertCampo($mysqli,"EntregaServicio",$Pripg);
        //Se cambia el status de el cliente
        Basicas::ActCampo($mysqli,"Venta","Status","FALLECIDO",$IdVenta);
        //Redireccionamos a la pagina del pago
        header('Location: https://kasu.com.mx'.$Host.'?Ml=4&Vt=1&name='.$name);
  }
  /*************************************************************************
            realiza el alta de un ticket de atencion al cliente
  **************************************************************************/
  if (isset($_POST['AltaTicket'])) {
    foreach ($_POST as $key => $value) {
      $asignacion = "\$" . $key . "='" . $value . "';";
      eval($asignacion);
    }
    //Se busca si existe un Alta previa
    $Alta = Basicas::Buscar3Campos($mysqli,"Id","Eventos","Evento","Alta_Cte","IdVta",$IdVenta,"FechaRegistro",$hoy);
    //Moficacmos los eventos para crearlos
    if(empty($Alta)){
        $Evento = "Alta_Cte";
        //Creamos la session
        $_SESSION["Alta"] = $IdVenta;
        //Bloqueamos el usuario para llamadas
        Basicas::ActTab($mysqli,"Empleados","Telefono",1,"IdUsuario",$_SESSION["Vendedor"]);
    }else{
        $Evento = "Baja_Cte";
        //Vaciamos la session
        unset($_SESSION["Alta"]);
        //liberamos el usuario para llamadas
        Basicas::ActTab($mysqli,"Empleados","Telefono",0,"IdUsuario",$_SESSION["Vendedor"]);
    }
    //Se crea el array que contiene los datos para REGISTRO DE EVENTOS
    $DatEventos = array(
        "Evento"        => $Evento,
        "Usuario"       => $_SESSION["Vendedor"],
        "IdVta"         => $IdVenta,
        "FechaRegistro" => $hoy
    );
    //Se realiza el insert en la base de datos
    Basicas::InsertCampo($mysqli,"Eventos",$DatEventos);
    //Redireccionamos a la pagina del pago
    header('Location: https://kasu.com.mx'.$Host.'?Ml=5&Vt=1&name='.$name);
  }
  /*************************************************************************
            realiza La Actualiz de los datos de un cliente
  **************************************************************************/
  if (isset($_POST['CamDat'])) {
    foreach ($_POST as $key => $value) {
        $asignacion = "\$" . $key . "='" . $value . "';";
        eval($asignacion);
    }
      //Se obtienen los datos de contacto
      echo $sql = "SELECT * FROM Contacto WHERE Id = '".$IdContact."'";
      //Realiza consulta
      $recs = mysqli_query($mysqli, $sql);
      //Si existe el registro se asocia en un fetch_assoc
      if($Recg=mysqli_fetch_assoc($recs)){
        print_r($Recg);
      //se valida que los datos se hayan mofificado
          if($Recg['Direccion'] != $Direccion){ $val++; }
          if($Recg['Telefono'] != $Telefono){ $val++; }
          if($Recg['Mail'] != $Email){ $val++; }
      }
      //Se valida si se realizo algun cambio
      if($val > 0){
      //Se registran el servicio prestado
        $Pripg = array (
              "Usuario"       => $_SESSION["Vendedor"],
              "Host"          => $Host,
              "Mail"          => $Email,
              "Telefono"      => $Telefono,
              "Direccion"     => $Direccion,
              "Producto"      => Basicas::BuscarCampos($mysqli,"Producto","Venta","Id",$IdVenta)
        );
      //Insertar los datos en la base
      $NvoCnc = Basicas::InsertCampo($mysqli,"Contacto",$Pripg);
      //Se actualiza en la tabla de el uduario el Id del contacto
      Basicas::ActTab($mysqli,"Usuario","IdContact",$NvoCnc,"IdContact",$IdContact);
      //Se actualiza el valor del contacto en la venta
      Basicas::ActCampo($mysqli,"Venta","IdContact",$NvoCnc,$IdVenta);
      }
      //Se cambia el Servicio de el cliente
      Basicas::ActCampo($mysqli,"Venta","TipoServicio",$TipoServ,$IdVenta);
      // se redirecciona y se envia un alert de registro efectivo
      header('Location: https://kasu.com.mx'.$Host.'?Ml=5&Vt=1&name='.$name);
  }
/*******************************************************************************
            Realiza la asignacion de las metas de ventas
*******************************************************************************/
  if(isset($_POST['Asignar'])){
    foreach ($_POST as $key => $value) {
        $asignacion = "\$" . $key . "='" . $value . "';";
        eval($asignacion);
    }
    mysqli_query($mysqli,"TRUNCATE TABLE Asignacion");
    //Se establece la fecha inicial de el mes
    $Fec0 = date("Y-m-d",strtotime('first day of this month'));
    //Restamos 1 mes a la fecha inicial
    $FecCont = date("Y-m-d",strtotime($Fec0."-1 month"));
    //Fecha final de el meses
    $FecFina = date("Y-m-d",strtotime($FecCont.'last day of this month'));
    //hacemos un while sobre los vendedores activos
    $sql = "SELECT * FROM Empleados WHERE Nombre != 'Vacante'";
    //Realiza consulta
    $res = $mysqli->query($sql);
    //Si existe el registro se asocia en un fetch_assoc
    foreach ($res as $Reg){
      //Se valida para no registrar doble metap por asesor en el mes
      $jsd = Basicas::Buscar2Campos($mysqli,"Fecha","Asignacion","Fecha",$Fec0,"Usuario",$Reg['IdUsuario']);
      //Se separa por bloques de niveles
        if($Reg['Nivel'] >= 5){
            $Reg['IdUsuario'];
            //Se Suma el valor de las ventas realizadas
            $sqlSum = Basicas::SumarFechasIndis($mysqli,"CostoVenta","Venta","Usuario",$Reg['IdUsuario'],"FechaRegistro",$FecFina,"FechaRegistro",$FecCont,"Status","PREVENTA");
            //Se Suman la cantidad de pagos realizados
            $SumCob = Basicas::SumarFechas($mysqli,"Cantidad","Pagos","Usuario",$Reg['IdUsuario'],"FechaRegistro",$FecFina,"FechaRegistro",$FecCont);
            //hacemos un while sobre los pagos que se realizaran en el periodo
            $sql1 = "SELECT * FROM Venta WHERE Usuario = '".$Reg['IdUsuario']."' AND Status = 'COBRANZA'";
            //Realiza consulta
            $res1 = $mysqli->query($sql1);
            $an = 0;
            //Se comparan por nivel para asignar las metas
            if($Reg['Nivel'] == 5){
              //Si existe el registro se asigna un valor para calcular los valores de la cobranza
              foreach ($res1 as $RegA){
                  $an = $an+Financieras::Pago($mysqli, $RegA['Id']);
              }
              //Se crea la meta de cobranza
              $MetaCob = $an;
            }elseif($Reg['Nivel'] == 6){
              //Se comparan los valores con la meta de venta
              if($sqlSum >= $MetaMes){
                //Aumentamosun 10% al valor de la colocacion anterior
                $ro5s = $sqlSum/10;
                $MetaVta = $ro5s+$sqlSum;
              }else{
                //Registramos la meta asignada
                $MetaVta = $MetaMes;
              }
              //Si existe el registro se asigna un valor para calcular los valores de la cobranza
              foreach ($res1 as $RegA){
                  $an = $an+Financieras::Pago($mysqli, $RegA['Id']);
              }
              //Se crea la meta de cobranza
              $MetaCob = $an;
            }elseif($Reg['Nivel'] == 7){
              //EL nivel siete no cuenta con metas de venta por lo q no hay forma de calcular
            }
            //Se registran las metas de los asesores
            if($MetaVta > 0 || $MetaCob > 0 AND empty($jsd)){
                //Se registra el array de registro de pago
                $DatCob = array(
                     "Usuario"    => $Reg['IdUsuario'],
                     "Equipo"     => $Reg['Equipo'],
                     "MVtas"      => $MetaVta,
                     "MCob"       => $MetaCob,
                     "Normalidad" => $Normalidad,
                     "Fecha"      => $Fec0
                 );
                //Se realiza el insert en la base de datos
                Basicas::InsertCampo($mysqli,"Asignacion",$DatCob);
            }
        }else{
          //Se suman las metas de Ventas de los Equipos asignados
          $MetaVta = Basicas::Sumar1Fechas($mysqli,"MVtas","Asignacion","Equipo",$Reg['Id'],"Fecha",$FecFina);
          //Se suman las metas de cobranza de los Equipos asignados
          $MetaCob = Basicas::Sumar1Fechas($mysqli,"MCob","Asignacion","Equipo",$Reg['Id'],"Fecha",$FecFina);
        }
        //Se registran las metas de los asesores
        if($MetaVta > 0 || $MetaCob > 0 AND empty($jsd)) {
            //Se registra el array de registro de pago
            $DatCob = array(
                 "Usuario"    => $Reg['IdUsuario'],
                 "Equipo"     => $Reg['Equipo'],
                 "MVtas"      => $MetaVta,
                 "MCob"       => $MetaCob,
                 "Normalidad" => $Normalidad,
                 "Fecha"      => $Fec0
             );
            //Se realiza el insert en la base de datos
            Basicas::InsertCampo($mysqli,"Asignacion",$DatCob);
        }
    }
    header('Location: https://kasu.com.mx/login/Mesa_Herramientas.php?Ml=5');
 }
 /**********************************Recibe la nueva foto de el usuario**********************************/
 if (!empty($_POST['btnEnviar'])){

   if (isset($_FILES['subirImg'])){
       echo "<br> fileTmpP =>".$fileTmpPath = $_FILES['subirImg']['tmp_name'];
       echo "<br> fileName =>".$fileName = $_FILES['subirImg']['name'];
       echo "<br> fileSize =>".$fileSize = $_FILES['subirImg']['size'];
       echo "<br> fileType =>".$fileType = $_FILES['subirImg']['type'];
       echo "<br> fileName =>".$fileNameCmps = explode(".", $fileName);
       echo "<br> fileExte =>".$fileExtension = strtolower(end($fileNameCmps));
     // se agregan las extenciones permitidas
     $allowedfileExtensions = array('jpg','jpeg','png','bmp','svg');
     if (in_array($fileExtension, $allowedfileExtensions)){
       // directory in which the uploaded file will be moved
       $uploadFileDir = '../assets/img/perfil/';
       if (is_dir($uploadFileDir)){
           // Abre un gestor de directorios para la ruta indicada
           $gestor = opendir($uploadFileDir);
           // Recorre todos los archivos del directorio
           while (($archivo = readdir($gestor)) !== false)  {
               // Solo buscamos archivos sin entrar en subdirectorios
               if (is_file($uploadFileDir."/".$archivo)) {
                   $ext = explode( '.', $archivo );
                   if($ext[0] == $_SESSION["Vendedor"]){
                     $file_ext = $ext[1];
                     unlink($uploadFileDir . $_SESSION["Vendedor"].'.'.$file_ext);
                   }
               }
           }
           // Cierra el gestor de directorios
           closedir($gestor);
         }
       $dest_path = $uploadFileDir . $_SESSION["Vendedor"].'.'.$fileExtension;
       if(move_uploaded_file($fileTmpPath, $dest_path)){
         header("Location: https://kasu.com.mx/login");
       }else{
         echo'<script type="text/javascript">alert("No se subio archivo");</script>';
       }
     }else{
       echo'<script type="text/javascript">alert("Archivo no permitido");</script>';
     }
   }else{
     $message = "Error al subir archivo. Por favor revisa el siguiente error.";
     $message .= "Error:" . $_FILES['subirImg']['error'];

   }
   header("Refresh: 0; URL= https://kasu.com.mx/login");
 }else{

 }
