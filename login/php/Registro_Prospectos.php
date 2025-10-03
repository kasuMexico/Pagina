<?
//indicar que se inicia una sesion
session_start();
//inlcuir el archivo de funciones
require_once '../../eia/librerias.php';
date_default_timezone_set('America/Mexico_City');

$hoy = date('Y-m-d');
$HoraActual = date('H:i:s');
//Varible principal

/******************************************** BLOQUE: Registra un nuevo prospecto **********************************************/
/************************************** REVISADO 25/09/2025 JOSE CARLOS CABRERA MONROY ****************************************/

    if(isset($_POST['prospectoNvo'])){

    // Extraer y sanitizar las variables necesarias
    function post_has($k){ return array_key_exists($k, $_POST); }
    function post_get($k){ return post_has($k) ? trim((string)$_POST[$k]) : null; }

    function s_str($v){ return $v===null ? null : filter_var($v, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH); }
    function s_int($v){ if($v===null) return null; $x = filter_var($v, FILTER_VALIDATE_INT); return $x===false ? null : $x; }
    function s_bool01($v){ if($v===null) return null; $t=strtolower((string)$v); return in_array($t,['1','true','on','yes','si','sí'],true)?1:0; }
    function s_email($v){ if($v===null) return null; $x = filter_var($v, FILTER_VALIDATE_EMAIL); return $x?:null; }
    function s_phone10($v){ if($v===null) return null; $d=preg_replace('/\D+/','',$v); return strlen($d)>=10 ? substr($d,-10) : null; }
    function s_curp($v){
    if($v===null) return null;
    $u = strtoupper(preg_replace('/[^A-Za-z0-9]/','',$v));
    $re = '/^[A-Z]{4}\d{6}[HM](AS|BC|BS|CC|CS|CH|CL|CM|CO|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$/';
    return preg_match($re,$u) ? $u : null;
    }
    function s_date($v){ // -> Y-m-d
    if($v===null) return null;
    $v=trim($v);
    foreach (['Y-m-d','d/m/Y','d-m-Y'] as $fmt){
        $dt = DateTime::createFromFormat($fmt,$v);
        if($dt && $dt->format($fmt)===$v) return $dt->format('Y-m-d');
    }
    return null;
    }
    function s_datetime($v){ // -> Y-m-d H:i:s
    if($v===null) return null;
    foreach (['Y-m-d H:i:s','d/m/Y H:i:s','Y-m-d'] as $fmt){
        $dt = DateTime::createFromFormat($fmt,$v);
        if($dt && $dt->format($fmt)===$v) return $dt->format('Y-m-d H:i:s');
    }
    return null;
    }
    function s_choice($v, array $allowed){
    if($v===null) return null;
    $x = strtoupper(trim($v));
    return in_array($x,$allowed,true) ? $x : null;
    }

    // ==== Catálogos ====
    $SERV_ALLOWED = ['FUNERARIO','SEGURIDAD','TRANSPORTE','RETIRO','DISTRIBUIDOR'];

    // ==== Captura + sanitizado ====
    // alias útiles desde tu modal/HTML
    $Curp            = s_curp( post_get('Curp')      ?? post_get('CURP') );
    $NoTel           = s_phone10( post_get('NoTel')  ?? post_get('Telefono') );
    $Email           = s_email( post_get('Email')    ?? post_get('Email') );
    $Servicio_Interes= s_choice( post_get('Servicio_Interes') ?? post_get('Servicio'), $SERV_ALLOWED );
    $FechaNac        = s_date( post_get('FechaNac')  ?? post_get("FechaNac\t") ?? post_get('fecha_nac') );
    $Alta            = s_datetime( post_get('Alta') ) ?? date('Y-m-d H:i:s');

    // otros campos tal cual con su tipo
    $IdFingerprint   = s_str( post_get('IdFingerprint') ?? post_get('fingerprint') );
    $IdFacebook      = s_str( post_get('IdFacebook') );
    $UsrApi          = s_str( post_get('UsrApi') );
    $Direccion       = s_str( post_get('Direccion') );
    $Origen          = s_str( post_get('Origen') );
    $Sugeridos       = s_int( post_get('Sugeridos') );
    $Cancelacion     = post_has('Cancelacion') ? s_bool01(post_get('Cancelacion')) : null;
    $Automatico      = post_has('Automatico')  ? s_bool01(post_get('Automatico'))  : null;

    //Validamos que el prospecto no se encuentre registrado
    $CurpValid = $basicas->BuscarCampos($pros,'Id','prospectos','Curp',$Curp);
    //Validamos que el correo electronico y el telefono no se registre doble
    $ValidTele = $basicas->BuscarCampos($pros,'Id','prospectos','NoTel',$NoTel);
    $ValidMail = $basicas->BuscarCampos($pros,'Id','prospectos','Email',$Email);
    //Validamos que el CURP que se encuentra registrado tenga un pape line valido
    $PapeCurp = $basicas->BuscarCampos($pros,'Papeline','prospectos','Curp',$Curp);
    
    //Buscamos si el cliente existe ya
    $IdContac = $basicas->BuscarCampos($mysqli,'IdContact','Usuario','ClaveCurp',$Curp);
    if (!empty($IdContac)){
        //Validamos el status de el servicio
        $StatVta = $basicas->BuscarCampos($mysqli,'Producto','Venta','IdContact',$IdContac);
        //Creamos un validador para la ejecucion
        if($StatVta != "Universidad" || $StatVta != "Retiro"){
            $ValidacionProducto = "InValido";
        }
    }

    //Validamos que el prospecto no se encuentre registrado
    if($ValidacionProducto == "InValido"){
        //Se registran los datos de el finger print, gps y Evento
        $ids = $seguridad->auditoria_registrar(
            $mysqli,                                // conexión principal
            $basicas,                               // tu helper Basicas
            $_POST,                                 // datos del form (fingerprint, gps, etc.)
            'Pros_ya_Cte_Pwa',                    // nombre del evento
            $_POST['Host'] ?? $_SERVER['PHP_SELF']  // host/origen
        );
        //mensaje de alert para usuario
        $Msg = "Este prospecto ya es cliente de KASU";
    } elseif(!empty($CurpValid) && $PapeCurp == "Prospeccion"){
        //Se registran los datos de el finger print, gps y Evento
        $ids = $seguridad->auditoria_registrar(
            $mysqli,                                // conexión principal
            $basicas,                               // tu helper Basicas
            $_POST,                                 // datos del form (fingerprint, gps, etc.)
            'Fallido_Prospecto_Pwa',                    // nombre del evento
            $_POST['Host'] ?? $_SERVER['PHP_SELF']  // host/origen
        );
        //mensaje de alert para usuario
        $Msg = "Este prospecto ya se encuentra registrado y no ha concluido el proceso";
    } elseif(!empty($ValidTele) || !empty($ValidMail)){
        //Se registran los datos de el finger print, gps y Evento
        $ids = $seguridad->auditoria_registrar(
            $mysqli,                                // conexión principal
            $basicas,                               // tu helper Basicas
            $_POST,                                 // datos del form (fingerprint, gps, etc.)
            'Pros_Contacto_Duplicado',              // nombre del evento
            $_POST['Host'] ?? $_SERVER['PHP_SELF']  // host/origen
        );
        //mensaje de alert para usuario
        $Msg = "los datos de contacto de este prospecto ya se encuentran registrados";
    } else {
        //Validamos los datos de el prospecto
        $DatProsp = $seguridad->peticion_get($Curp);
        // Validamos que la curp sea real
        if($DatProsp['Response'] == "Error"){
            //Se registran los datos de el finger print, gps y Evento
            $ids = $seguridad->auditoria_registrar(
                $mysqli,                                // conexión principal
                $basicas,                               // tu helper Basicas
                $_POST,                                 // datos del form (fingerprint, gps, etc.)
                'Pros_Curp_Falsa_Pwa',                    // nombre del evento
                $_POST['Host'] ?? $_SERVER['PHP_SELF']  // host/origen
            );
            //mensaje de alert para usuario
            $Msg = $DatProsp['Msg'];
        } else {
            //Buscamos el Id de el vendedor que crea el proespeco
            $Asignado = $basicas->BuscarCampos($mysqli,'Id','Empleados','IdUsuario',$_SESSION["Vendedor"]);

            //Se registran los datos de el finger print, gps y Evento
            $ids = $seguridad->auditoria_registrar(
                $mysqli,                                // conexión principal
                $basicas,                               // tu helper Basicas
                $_POST,                                 // datos del form (fingerprint, gps, etc.)
                'Pros_Curp_Falsa_Pwa',                    // nombre del evento
                $_POST['Host'] ?? $_SERVER['PHP_SELF']  // host/origen
            );

            //Realizamos el registro de el Usuario
            // ==== Empaque para DB ====
            $data = [
            "IdFingerprint"     => $ids['fingerprint_id'],
            "IdFacebook"        => $IdFacebook,
            "UsrApi"            => $UsrApi,
            "FullName"          => $DatProsp['Nombre']." ".$DatProsp['Paterno']." ".$DatProsp['Materno'],
            "Curp"              => $Curp,
            "NoTel"             => $NoTel,
            "Email"             => $Email,
            "Direccion"         => $Direccion,
            "Servicio_Interes"  => $Servicio_Interes,
            "Alta"              => $Alta,
            "Origen"            => $Origen,
            "Papeline"          => "Prospeccion",
            "PosPapeline"       => 1,
            "Sugeridos"         => $Sugeridos,
            "Cancelacion"       => $Cancelacion,
            "FechaNac"          => $DatProsp['FechaNacimiento'],
            "Automatico"        => $Automatico,
            "Asignado"          => $Asignado
            ];
            //Registramos en la base de datos la leyenda
            $NvoRegistro = $basicas->InsertCampo($pros, "prospectos", $data);

            //mensaje de alert para usuario
            $Msg = "Se ha registrado correctamente el prospecto";
        }
    }
    //Redireccionar a pagina de donde venimos
    header('Location: https://kasu.com.mx' . $_POST['Host'] . '?Vt=1&Msg='.$Msg.'&nombre=' . $_POST['nombre']);
    exit();
}
/***************************************** BLOQUE: Descarga Cotizacion a prospecto ********************************************/
/************************************** REVISADO 25/09/2025 JOSE CARLOS CABRERA MONROY ****************************************/

if(isset($_POST['DescargaPres'])){
    //Alineamos los POST
    $Host = isset($_POST['Host']) ? $mysqli->real_escape_string($_POST['Host']) : '';
    $IdVenta = isset($_POST['IdVenta']) ? $mysqli->real_escape_string($_POST['IdVenta']) : '';
    $IdContact = isset($_POST['IdContact']) ? $mysqli->real_escape_string($_POST['IdContact']) : '';
    $IdUsuario = isset($_POST['IdUsuario']) ? $mysqli->real_escape_string($_POST['IdUsuario']) : '';
    $Producto = isset($_POST['Producto']) ? $mysqli->real_escape_string($_POST['Producto']) : '';
    $Id = isset($_POST['Id']) ? $mysqli->real_escape_string($_POST['Id']) : '';
    $IdVendedor = isset($_POST['IdVendedor']) ? $mysqli->real_escape_string($_POST['IdVendedor']) : '';
    $name = isset($_POST['name']) ? $mysqli->real_escape_string($_POST['name']) : '';
    $tipo_plan = isset($_POST['tipo_plan']) ? $mysqli->real_escape_string($_POST['tipo_plan']) : '';
    $a02a29 = isset($_POST['a02a29']) ? $mysqli->real_escape_string($_POST['a02a29']) : '';
    $a30a49 = isset($_POST['a30a49']) ? $mysqli->real_escape_string($_POST['a30a49']) : '';
    $a50a54 = isset($_POST['a50a54']) ? $mysqli->real_escape_string($_POST['a50a54']) : '';
    $a55a59 = isset($_POST['a55a59']) ? $mysqli->real_escape_string($_POST['a55a59']) : '';
    $a60a64 = isset($_POST['a60a64']) ? $mysqli->real_escape_string($_POST['a60a64']) : '';
    $a65a69 = isset($_POST['a65a69']) ? $mysqli->real_escape_string($_POST['a65a69']) : '';
    $Retiro = isset($_POST['Retiro']) ? $mysqli->real_escape_string($_POST['Retiro']) : '';
    $plazo = isset($_POST['plazo']) ? $mysqli->real_escape_string($_POST['plazo']) : '';
    $DescargaPres = isset($_POST['DescargaPres']) ? $mysqli->real_escape_string($_POST['DescargaPres']) : '';

    $Ventas = "SELECT * FROM prospectos WHERE Id = '".$IdVenta."'";
    if ($resultado = $pros->query($Ventas)) {
        while ($fila = $resultado->fetch_assoc()) {
            //Validamos que el servicio sean varios o solo 1
            if($tipo_plan == "INDIVIDUAL"){
                $Edad = $basicas->ObtenerEdad($fila['Curp'] ?? '');
                //Seleccionamos el tipo de Producto
                if($fila['Servicio_Interes'] == "TRANSPORTE"){
                    //Producto Funerario TRANSPORTE
                    $ProdSel  = $basicas->ProdTrans($Edad);
                }elseif($fila['Servicio_Interes'] == "SEGURIDAD"){
                    //Producto Funerario SEGURIDAD
                    $ProdSel  = $basicas->ProdPli($Edad);
                }else{
                    //Producto Funerario
                    echo $Prodeda  = $basicas->ProdFune($Edad);
                    $ProdSel = "A".$Prodeda;
                }
                //Obenemos el producto a cotizar
                $Vtn = substr($ProdSel, 1, 6);
                //Armamos una red de valores para identificar el producto seleccionado
                $rangos = ['02a29','30a49','50a54','55a59','60a64','65a69'];
                //Creamos el array de datos para registro en BD
                $data = [
                    "IdProspecto" => $IdVenta ,
                    "IdUser" => $IdVendedor,
                    "SubProducto" => $fila['Servicio_Interes'],
                    "a02a29" => '',
                    "a30a49" => '',
                    "a50a54" => '',
                    "a55a59" => '',
                    "a60a64" => '',
                    "a65a69" => '',
                    "Retiro" => $Retiro,
                    "plazo" => $plazo,
                    "FechaRegistro" => $fila['Alta']
                    ];
                //Asi asignamos el valor de el producto seleccionado
                if (in_array($Vtn, $rangos, true)) {
                    $data['a'.$Vtn] = 1; // solo este va en 1
                }
            } else {
                //Creamos el array de datos para registro en BD
                $data = [
                    "IdProspecto" => $IdVenta ,
                    "IdUser" => $IdVendedor,
                    "SubProducto" => $fila['Servicio_Interes'],
                    "a02a29" => $a02a29,
                    "a30a49" => $a30a49,
                    "a50a54" => $a50a54,
                    "a55a59" => $a55a59,
                    "a60a64" => $a60a64,
                    "a65a69" => $a65a69,
                    "Retiro" => $Retiro,
                    "plazo" => $plazo,
                    "FechaRegistro" => $fila['Alta']
                    ];
            }
            //Se registran los datos de el finger print, gps y Evento
            $ids = $seguridad->auditoria_registrar(
                $mysqli,                                // conexión principal
                $basicas,                               // tu helper Basicas
                $_POST,                                 // datos del form (fingerprint, gps, etc.)
                'Envio_Cotizacion',                     // nombre del evento
                $_POST['Host'] ?? $_SERVER['PHP_SELF']  // host/origen
            );
            //Insertamos en la base de datos los datos
            $NvoRegistro = $basicas->InsertCampo($pros, "PrespEnviado", $data);
            //lo encriptamos
            $NvoRegistro = base64_encode($NvoRegistro);
        }
    }
    //redireccionamos a la pagina de descargas de presupuestos
    header('Location: https://kasu.com.mx/login/Generar_PDF/Cotizacion_pdf.php?busqueda='.$NvoRegistro.'&Host='.$Host.'&name='.$name);
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
        $ArrayRes = $seguridad->peticion_get($_POST['CURP']);
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
