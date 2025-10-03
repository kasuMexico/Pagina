<?php
// DEBUG: Activar todos los errores y mostrar datos importantes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Iniciar sesión
session_start();

// Incluir el archivo de librerías que contiene las clases actualizadas
require_once '../../eia/librerias.php';

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');

// Variables de tiempo actuales
$hoy = date('Y-m-d');
$HoraActual = date('H:i:s');

/**************************************** BLOQUE: LOGIN Revisado 1/10/2025 JCCM ****************************************/

if (!empty($_POST['Login'])) {
    // CSRF
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf_auth'] ?? '', $_POST['csrf'])) {       
        http_response_code(403); exit;
    }
    $Host = $mysqli->real_escape_string($_POST['Host'] ?? '/login/index.php');
    $Usuario = trim($_POST['Usuario'] ?? '');
    $Pass    = $_POST['PassWord'] ?? '';

    if ($Usuario === '' || $Pass === '') {
        header('Location: https://kasu.com.mx/login/index.php?data=4'); exit;
    }
    
    $hashDB  = $basicas->BuscarCampos($mysqli, "Pass", "Empleados", "IdUsuario", $Usuario);
    if (!empty($hashDB) && hash('sha256', $Pass) === $hashDB) {
        $idEmp = $basicas->BuscarCampos($mysqli, "Id", "Empleados", "IdUsuario", $Usuario);
        $_SESSION['Vendedor']   = $Usuario;
        $_SESSION['IdVendedor'] = $idEmp;

        // ====== Auditoría (GPS / fingerprint) ======
        $ids = $seguridad->auditoria_registrar(
            $mysqli,
            $basicas,
            $_POST,
            'LoginOK',
            $_POST['Host'] ?? $_SERVER['PHP_SELF']
        );

        header('Location: https://kasu.com.mx/login/Pwa_Principal.php'); exit;
    }

    // ====== Auditoría (GPS / fingerprint) ======
    $ids = $seguridad->auditoria_registrar(
        $mysqli,
        $basicas,
        $_POST,
        'LoginFAIL',
        $_POST['Host'] ?? $_SERVER['PHP_SELF']
    );
    header('Location: https://kasu.com.mx/login/index.php?data=4'); exit;
}

/********************* BLOQUE: CAMBIAR CONTRASEÑA (usuario conoce su contraseña actual) *******************************/
if (!empty($_POST['CambiarPass'])) {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf_auth'] ?? '', $_POST['csrf'])) {
        http_response_code(403); exit;
    }
    $Host    = $mysqli->real_escape_string($_POST['Host'] ?? '/login/index.php');
    $Usuario = trim($_POST['Usuario'] ?? '');
    $PassAct = $_POST['PassAct']   ?? '';
    $P1      = $_POST['PassWord1'] ?? '';
    $P2      = $_POST['PassWord2'] ?? '';

    if ($P1 !== $P2) {
        header('Location: https://kasu.com.mx/login/index.php?action=cp&data=2'); exit;
    }

    $hashDB = $basicas->BuscarCampos($mysqli, "Pass", "Empleados", "IdUsuario", $Usuario);
    if (empty($hashDB) || hash('sha256', $PassAct) !== $hashDB) {
        header('Location: https://kasu.com.mx/login/index.php?action=cp&data=5'); exit;
    }

    $idEmp = $basicas->BuscarCampos($mysqli, "Id", "Empleados", "IdUsuario", $Usuario);
    $basicas->ActCampo($mysqli, "Empleados", "Pass", hash('sha256', $P1), $idEmp);

    // ====== Auditoría (GPS / fingerprint) ======
    $ids = $seguridad->auditoria_registrar(
        $mysqli,
        $basicas,
        $_POST,
        'PassChangeOK',
        $_POST['Host'] ?? $_SERVER['PHP_SELF']
    );

    header('Location: https://kasu.com.mx/login/index.php?data=6'); 
    exit;
}

/********************************* BLOQUE: PAGO DE COMISIONES *********************************/

if (isset($_POST['PagoCom'])) {
    // Extraer y sanitizar las variables necesarias desde POST
    $Cantidad   = isset($_POST['Cantidad'])   ? $mysqli->real_escape_string($_POST['Cantidad'])   : '';
    $IdEmpleado = isset($_POST['IdEmpleado']) ? $mysqli->real_escape_string($_POST['IdEmpleado']) : '';
    $Cuenta     = isset($_POST['Cuenta'])     ? $mysqli->real_escape_string($_POST['Cuenta'])     : '';
    $RefDepo    = isset($_POST['RefDepo'])    ? $mysqli->real_escape_string($_POST['RefDepo'])    : '';
    $Host       = isset($_POST['Host'])       ? $mysqli->real_escape_string($_POST['Host'])       : '';
    $name       = isset($_POST['name'])       ? $mysqli->real_escape_string($_POST['name'])       : '';

    // Crear array de datos para el registro de comisión
    $DatGps = [
        "Cantidad"      => $Cantidad,
        "IdVendedor"    => $IdEmpleado,
        "UsrResgistra"  => $_SESSION["Vendedor"],
        "Banco"         => $Cuenta,
        "Referencia"    => $RefDepo,
        "fechaRegistro" => $hoy . " " . $HoraActual
    ];

    // Insertar datos en la tabla Comisiones_pagos
    $basicas->InsertCampo($mysqli, "Comisiones_pagos", $DatGps);

    // Redireccionar a la pantalla del empleado
    header('Location: https://kasu.com.mx' . $Host . '?Vt=1&name=' . $name);
    exit();
}

/********************************* BLOQUE: CAMBIO DE VENDEDOR REVISION 1/10/2025 JCCM *********************************/
if (isset($_POST['CambiVend'])) {
    $Host       = isset($_POST['Host']) ? $mysqli->real_escape_string($_POST['Host']) : '';
    $name       = isset($_POST['name']) ? $mysqli->real_escape_string($_POST['name']) : '';
    $IdVenta    = isset($_POST['IdVenta']) ? $mysqli->real_escape_string($_POST['IdVenta']) : '';
    $IdContact  = isset($_POST['IdContact']) ? $mysqli->real_escape_string($_POST['IdContact']) : '';
    $IdUsuario  = isset($_POST['IdUsuario']) ? $mysqli->real_escape_string($_POST['IdUsuario']) : '';
    $IdEmpleado = isset($_POST['IdEmpleado']) ? $mysqli->real_escape_string($_POST['IdEmpleado']) : '';
    $IdSucursal = isset($_POST['IdSucursal']) ? $mysqli->real_escape_string($_POST['IdSucursal']) : '';
    $NvoSuperior= isset($_POST['NvoSuperior']) ? $mysqli->real_escape_string($_POST['NvoSuperior']) : '';

    //comparamos los datos actuales de el usuario
    $BaseSuperior = $basicas->BuscarCampos($mysqli, "Equipo", "Empleados", "Id", $IdEmpleado);
    $BaseSucursal = $basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "Id", $IdEmpleado);
    //Validamos que se haya echo un cambio
    if($IdSucursal != $BaseSucursal || $NvoSuperior != $BaseSuperior){
        // ====== Auditoría (GPS / fingerprint) ======
        $ids = $seguridad->auditoria_registrar(
            $mysqli,
            $basicas,
            $_POST,
            'Cambio_de_Superior',
            $_POST['Host'] ?? $_SERVER['PHP_SELF']
        );
        //Actualizamos los registros
        $basicas->ActCampo($mysqli, "Empleados", "Sucursal", $IdSucursal, $IdEmpleado);
        $basicas->ActCampo($mysqli, "Empleados", "Equipo", $NvoSuperior, $IdEmpleado);
        //Armamos el mensaje de alerta 
        $alert = "El colaborador se actualizo correctamente";
    } else {
        //Armamos el mensaje de alerta 
        $alert = "El colaborador no puede registrarse en la misma sucursal o en el mismo equipo";
    }
    //Redireccionamos al origen
    header('Location: https://kasu.com.mx' . $Host . '?&name=' . $name.'&Msg='.$alert);
    exit();
}

/********************************* BLOQUE: BAJA DE EMPLEADO *********************************/
if (isset($_POST['BajaEmp'])) {
    $IdEmpleado = isset($_POST['IdEmpleado']) ? $mysqli->real_escape_string($_POST['IdEmpleado']) : '';
    $MotivoBaja = isset($_POST['MotivoBaja']) ? $mysqli->real_escape_string($_POST['MotivoBaja']) : '';
    $Host       = isset($_POST['Host'])       ? $mysqli->real_escape_string($_POST['Host'])       : '';
    $name       = isset($_POST['name'])       ? $mysqli->real_escape_string($_POST['name'])       : '';

    // Obtener el Id de contacto del empleado
    $IdBAse = $basicas->BuscarCampos($mysqli, "IdContacto", "Empleados", "Id", $IdEmpleado);
    // Actualizar datos del empleado a "baja"
    $basicas->ActCampo($mysqli, "Empleados", "Nombre", "Vacante", $IdEmpleado);
    $basicas->ActCampo($mysqli, "Empleados", "IdUsuario", "Usuario", $IdEmpleado);
    $basicas->ActCampo($mysqli, "Empleados", "IdContacto", NULL, $IdEmpleado);
    $basicas->ActCampo($mysqli, "Empleados", "Pass", NULL, $IdEmpleado);
    $basicas->ActCampo($mysqli, "Empleados", "Equipo", NULL, $IdEmpleado);
    $basicas->ActCampo($mysqli, "Empleados", "Sucursal", NULL, $IdEmpleado);
    // Registrar el motivo de baja en la tabla Contacto
    $basicas->ActCampo($mysqli, "Contacto", "Motivo", $MotivoBaja, $IdBAse);

    header('Location: https://kasu.com.mx' . $Host . '?Vt=1&name=' . $name);
    exit();
}

/**************************** BLOQUE: CREAR EMPLEADO  Revisado 1/10/2025 JCCM *******************************************/
if (isset($_POST['CreaEmpl'])) {

    // Extraer y sanitizar las variables necesarias
    $Nombre         = isset($_POST['Nombre'])   ? $mysqli->real_escape_string($_POST['Nombre'])   : '';
    $Email          = isset($_POST['Email'])    ? $mysqli->real_escape_string($_POST['Email'])    : '';
    $Telefono       = isset($_POST['Telefono']) ? $mysqli->real_escape_string($_POST['Telefono']) : '';
    $Direccion      = isset($_POST['Direccion'])? $mysqli->real_escape_string($_POST['Direccion']): '';
    $calle          = isset($_POST['calle'])    ? $mysqli->real_escape_string($_POST['calle'])    : ''; //Prospectos
    $Host           = isset($_POST['Host'])     ? $mysqli->real_escape_string($_POST['Host'])     : '';
    $name           = isset($_POST['name'])     ? $mysqli->real_escape_string($_POST['name'])     : '';
    $Nivel          = isset($_POST['Nivel'])    ? $mysqli->real_escape_string($_POST['Nivel'])    : '';
    $Sucursal       = isset($_POST['Sucursal']) ? $mysqli->real_escape_string($_POST['Sucursal']) : '';
    $Lider          = isset($_POST['Lider'])    ? $mysqli->real_escape_string($_POST['Lider'])    : '';
    $Cuenta         = isset($_POST['Cuenta'])   ? $mysqli->real_escape_string($_POST['Cuenta'])   : '';
    $IdProspecto    = isset($_POST['IdProspecto']) ? $mysqli->real_escape_string($_POST['IdProspecto']) : ''; //Pwa_Prosp y Mesa_Empl

    //Validamos que sea prospecto o empleado directo
    if ($Host == "/login/Pwa_Prospectos.php") {
        //Obtenemos los datos de el prospecto
        $venta = "SELECT * FROM prospectos WHERE Id='".$IdProspecto."' LIMIT 1";
        $res   = $pros->query($venta);
        if ($Reg = $res->fetch_assoc()) {}

        // Generar código único para el usuario (por ejemplo, usando partes del nombre)
        $Sg1t = substr($Reg['FullName'], 0, 3);
        $Sg2t = substr($Reg['FullName'], -3);
        $Dil = $Sg1t . $Sg2t;
        $DirUrl = strtoupper($Dil);
        $envTs = "Tu usuario es " . $DirUrl;
        $dRc = mt_rand();
        $dirUrl1 = base64_encode($dRc);

        //Actualizamos los datos de el prospecto para su registro correcto
        $basicas->ActCampo($pros,"prospectos","NoTel",$Telefono,$IdProspecto); //Telefono
        $basicas->ActCampo($pros,"prospectos","Email",$Email,$IdProspecto); //Email
        $basicas->ActCampo($pros,"prospectos","Direccion",$calle,$IdProspecto); //Direccion
        $basicas->ActCampo($pros,"prospectos","Cancelacion",1,$IdProspecto); //Status
        
        //Registramos el contacto
        $ArrayContacto = [
            "Usuario"   => $_SESSION["Vendedor"],
            "Host"      => $Host,
            "Mail"      => $Email,
            "Telefono"  => $Telefono,
            "calle" => $calle,
            "Producto"  => "Distribuidor"
        ];

        //Registrar array en la base de datos
        $IdContacto = $basicas->InsertCampo($mysqli, "Contacto", $ArrayContacto);
        //Buscamos los datos del usuario
        $IdUsuario = $basicas->BuscarCampos($mysqli, "id", "Usuario", "ClaveCurp", $Reg['Curp']);
        if (empty($IdUsuario)){
            //Registramos a los datos del usuario
            $ArrayUsuario = [
                "Usuario"       => $_SESSION["Vendedor"],
                "IdContact"     => $IdContacto,
                "Tipo"	        => 'Distribuidor',
                "Nombre"        => $Reg['FullName'],
                "ClaveCurp"     => $Reg['Curp'],
                "Email"         => $Email,
                "FechaRegistro" => $hoy." ".$HoraActual
            ];
            //Regstrar array en base de datos
            $IdUsuario = $basicas->InsertCampo($mysqli, "Usuario", $ArrayUsuario);
        } else {
            //Actualizamos el contacto de el Usuario existente, para actualizar medios de contacto
            $basicas->ActCampo($mysqli, "Usuario", "IdContact", $IdContacto, $IdUsuario);
        }
        // Se busca un registro en Empleados (por ejemplo, el primer disponible de un nivel y sucursal)
        $EspEMp = $basicas->Buscar2Campos($mysqli, "Id", "Empleados", "Sucursal", 0, "Nivel", 7);
        //Registranmos el empleado en la base de datos empleados
        if (!empty($EspEMp)) {
            
            // ====== Auditoría (GPS / fingerprint) ======
            $ids = $seguridad->auditoria_registrar(
                $mysqli,
                $basicas,
                $_POST,
                'Autorizacion_Distibuidor',
                $_POST['Host'] ?? $_SERVER['PHP_SELF']
            );
            //Buscamos la sucursal de el que autoriza el Distribuidor
            $Sucursal = $IdUsuario = $basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
            //Registranmos el empleado en la base de datos empleados
            $basicas->ActCampo($mysqli, "Empleados", "Nombre", $Reg['FullName'], $EspEMp);
            $basicas->ActCampo($mysqli, "Empleados", "IdUsuario", $DirUrl, $EspEMp);
            $basicas->ActCampo($mysqli, "Empleados", "IdContacto", $IdContacto, $EspEMp);
            $basicas->ActCampo($mysqli, "Empleados", "FechaAlta", $hoy, $EspEMp);
            $basicas->ActCampo($mysqli, "Empleados", "Sucursal", $Sucursal, $EspEMp); //Este dato lo cambia mesa de control 
            $basicas->ActCampo($mysqli, "Empleados", "Telefono", $Telefono, $EspEMp);
        }
        //Creamos un alert de registro de DISTRIBUIDOR 
        $alert = "EL distribudor se registro correctamente, asignalo a tu sucursal y al coordinador que correspoanda llamando a mesa de control, despues el usuario podra ingresar al sistema";
        //Redireccionamos al origen
        header('Location: https://kasu.com.mx' . $Host . '?&name=' . $name.'&Msg='.$alert);
        exit();
    } else {

        // Generar código único para el usuario (por ejemplo, usando partes del nombre)
        $Sg1t = substr($Nombre, 0, 3);
        $Sg2t = substr($Nombre, -3);
        $Dil = $Sg1t . $Sg2t;
        $DirUrl = strtoupper($Dil);
        $envTs = "Tu usuario es " . $DirUrl;
        $dRc = mt_rand();
        $dirUrl1 = base64_encode($dRc);

        // Insertar datos de Contacto para el empleado
        $DatContac = [
            "Usuario"   => $_SESSION["Vendedor"],
            "Host"      => $Host,
            "Mail"      => $Email,
            "Telefono"  => $Telefono,
            "Direccion" => $Direccion,
            "Producto"  => "Empleado"
        ];
        //$uSR = $basicas->InsertCampo($mysqli, "Contacto", $DatContac);

        // Se busca un registro en Empleados (por ejemplo, el primer disponible de un nivel y sucursal)
        $Reg = $basicas->Buscar2Campos($mysqli, "Id", "Empleados", "Sucursal", 0, "Nivel", $Nivel);
        if (!empty($Reg)) {
            $basicas->ActCampo($mysqli, "Empleados", "Nombre", $Nombre, $Reg);
            $basicas->ActCampo($mysqli, "Empleados", "IdContacto", $uSR, $Reg);
            $basicas->ActCampo($mysqli, "Empleados", "IdUsuario", NULL, $Reg);
            $basicas->ActCampo($mysqli, "Empleados", "Pass", $dRc, $Reg);
            $basicas->ActCampo($mysqli, "Empleados", "Equipo", $Lider, $Reg);
            $basicas->ActCampo($mysqli, "Empleados", "Sucursal", $Sucursal, $Reg);
            $basicas->ActCampo($mysqli, "Empleados", "FechaAlta", $hoy, $Reg);
            $basicas->ActCampo($mysqli, "Empleados", "Cuenta", $Cuenta, $Reg);
        }
        // Si el nivel es 7, se asume que se debe realizar algún ajuste adicional
        if ($Nivel == 7) {
            $contra = '&Add=' . $uSR;
            // Actualiza el estado del prospecto
            $basicas->ActCampo($pros, "prospectos", "Cancelacion", 1, $IdProspecto);
        }
        //header('Location: https://kasu.com.mx' . $Host . '?Ml=1&name=' . $name . $contra);
        //exit();
    }
}

/********************************* BLOQUE: REENVÍO DE CONTRASEÑA *********************************/
if (isset($_POST['ReenCOntra'])) {
    $Id = isset($_POST['Id']) ? $mysqli->real_escape_string($_POST['Id']) : '';
    $Nombre = isset($_POST['Nombre']) ? $mysqli->real_escape_string($_POST['Nombre']) : '';
    $Email = isset($_POST['Email']) ? $mysqli->real_escape_string($_POST['Email']) : '';
    $User = isset($_POST['User']) ? $mysqli->real_escape_string($_POST['User']) : '';
    $Host = isset($_POST['Host']) ? $mysqli->real_escape_string($_POST['Host']) : '';
    $name = isset($_POST['name']) ? $mysqli->real_escape_string($_POST['name']) : '';

    $dRc = mt_rand();
    $dirUrl1 = base64_encode($dRc);
    $DirUrl = "https://kasu.com.mx/login/index.php?data=" . $dirUrl1 . "&Usr=" . $Id;
    $basicas->ActCampo($mysqli, "Empleados", "Pass", $dRc, $Id);
    $Mensaje = $Correo->Mensaje('RESTABLECIMIENTO DE CONTRASEÑA', $Nombre, $DirUrl, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
    $Correo->EnviarCorreo($Nombre, $Email, 'RESTABLECIMIENTO DE CONTRASEÑA', $Mensaje);
    header('Location: https://kasu.com.mx' . $Host . '?Ml=1&name=' . $name);
    exit();
}

/********************************* BLOQUE: GENERAR CONTRASEÑA (Registro de Contraseña) *********************************/
if (!empty($_POST['GenCont'])) {
    $PassWord1 = isset($_POST['PassWord1']) ? $_POST['PassWord1'] : '';
    $PassWord2 = isset($_POST['PassWord2']) ? $_POST['PassWord2'] : '';
    $data = isset($_POST['data']) ? $_POST['data'] : '';
    $fingerprint = isset($_POST['fingerprint']) ? $_POST['fingerprint'] : '';
    $Host = isset($_POST['Host']) ? $mysqli->real_escape_string($_POST['Host']) : '';

    if ($PassWord1 === $PassWord2) {
        $decodedPass = base64_decode($data);
        $PassRecordId = $basicas->BuscarCampos($mysqli, "Id", "Empleados", "Pass", $decodedPass);
        if (!empty($PassRecordId)) {
            // Registro de eventos: insertar GPS y FingerPrint si aún no existen
            $gpsLogin = $basicas->InsertCampo($mysqli, "gps", [
                "Latitud" => isset($_POST['Latitud']) ? $mysqli->real_escape_string($_POST['Latitud']) : '',
                "Longitud" => isset($_POST['Longitud']) ? $mysqli->real_escape_string($_POST['Longitud']) : '',
                "Presicion" => isset($_POST['Presicion']) ? $mysqli->real_escape_string($_POST['Presicion']) : ''
            ]);
            if (empty($basicas->BuscarCampos($mysqli, "id", "FingerPrint", "fingerprint", $fingerprint))) {
                $DatFinger = [
                    "fingerprint"   => $fingerprint,
                    "browser"       => isset($_POST['browser']) ? $_POST['browser'] : '',
                    "flash"         => isset($_POST['flash']) ? $_POST['flash'] : '',
                    "canvas"        => isset($_POST['canvas']) ? $_POST['canvas'] : '',
                    "connection"    => isset($_POST['connection']) ? $_POST['connection'] : '',
                    "cookie"        => isset($_POST['cookie']) ? $_POST['cookie'] : '',
                    "display"       => isset($_POST['display']) ? $_POST['display'] : '',
                    "fontsmoothing" => isset($_POST['fontsmoothing']) ? $_POST['fontsmoothing'] : '',
                    "fonts"         => isset($_POST['fonts']) ? $_POST['fonts'] : '',
                    "formfields"    => isset($_POST['formfields']) ? $_POST['formfields'] : '',
                    "java"          => isset($_POST['java']) ? $_POST['java'] : '',
                    "language"      => isset($_POST['language']) ? $_POST['language'] : '',
                    "silverlight"   => isset($_POST['silverlight']) ? $_POST['silverlight'] : '',
                    "os"            => isset($_POST['os']) ? $_POST['os'] : '',
                    "timezone"      => isset($_POST['timezone']) ? $_POST['timezone'] : '',
                    "touch"         => isset($_POST['touch']) ? $_POST['touch'] : '',
                    "truebrowser"   => isset($_POST['truebrowser']) ? $_POST['truebrowser'] : '',
                    "plugins"       => isset($_POST['plugins']) ? $_POST['plugins'] : '',
                    "useragent"     => isset($_POST['useragent']) ? $_POST['useragent'] : ''
                ];
                $basicas->InsertCampo($mysqli, "FingerPrint", $DatFinger);
            }
            // Registrar evento
            $DatEventos = [
                "IdFInger"      => $fingerprint,
                "Idgps"         => $gpsLogin,
                "Host"          => $Host,
                "Evento"        => isset($_POST['Evento']) ? $_POST['Evento'] : "AltaPass",
                "Usuario"       => isset($_POST['Usuario']) ? $_POST['Usuario'] : '',
                "FechaRegistro" => $hoy . " " . $HoraActual
            ];
            $basicas->InsertCampo($mysqli, "Eventos", $DatEventos);

            // Convertir contraseña a SHA-256
            $PassSHa = hash('sha256', $PassWord1);
            // Actualizar datos del empleado (asumiendo que $PassRecordId es la clave a actualizar)
            $basicas->ActCampo($mysqli, "Empleados", "IdUsuario", isset($_POST['User']) ? $_POST['User'] : '', $PassRecordId);
            $basicas->ActCampo($mysqli, "Empleados", "Pass", $PassSHa, $PassRecordId);
            $dta = 3; // Registro exitoso
        } else {
            $dta = 1; // El registro ya fue utilizado
        }
    } else {
        $dta = 2; // Contraseñas no coinciden
    }
    header('Location: https://kasu.com.mx' . $Host . '?Vt=1&Data=' . $dta);
    exit();
}

/********************************* BLOQUE: ACTUALIZAR DATOS DE UN EMPLEADO *********************************/
if (isset($_POST['CamDat'])) {
    // Extraer datos del formulario y sanitizarlos
    $IdContact = isset($_POST['IdContact']) ? $mysqli->real_escape_string($_POST['IdContact']) : '';
    $Direccion = isset($_POST['Direccion']) ? $mysqli->real_escape_string($_POST['Direccion']) : '';
    $Telefono  = isset($_POST['Telefono'])  ? $mysqli->real_escape_string($_POST['Telefono'])  : '';
    $Mail      = isset($_POST['Mail'])      ? $mysqli->real_escape_string($_POST['Mail'])      : '';

    // Consultar el contacto actual
    $sql = "SELECT * FROM Contacto WHERE Id = '$IdContact'";
    $recs = mysqli_query($mysqli, $sql);
    $val = 0;
    if ($Recg = mysqli_fetch_assoc($recs)) {
        if ($Recg['Direccion'] != $Direccion) { $val++; }
        if ($Recg['Telefono'] != $Telefono) { $val++; }
        if ($Recg['Mail'] != $Mail) { $val++; }
    }
    $IdVenta = $basicas->BuscarCampos($mysqli, "Id", "Venta", "IdContact", $IdContact);
    if (!empty($IdVenta)) {
        $SDTM = $basicas->BuscarCampos($mysqli, "Producto", "Venta", "Id", $IdVenta);
    }
    if ($val > 0) {
        $Pripg = [
            "Usuario"   => $_SESSION["Vendedor"],
            "Host"      => $mysqli->real_escape_string($_POST['Host']),
            "Mail"      => $Mail,
            "Telefono"  => $Telefono,
            "Direccion" => $Direccion,
            "Producto"  => $SDTM
        ];
        $NvoCnc = $basicas->InsertCampo($mysqli, "Contacto", $Pripg);
        $basicas->ActTab($mysqli, "Usuario", "IdContact", $NvoCnc, "IdContact", $IdContact);
        if (!empty($IdVenta)) {
            $basicas->ActCampo($mysqli, "Venta", "IdContact", $NvoCnc, $IdVenta);
        }
    }
    // Enviar correo de actualización
    $Mensaje = $Correo->Mensaje("ACTUALIZACION DE DATOS", $Nombre, 'https://kasu.com.mx/ActualizacionDatos/index.php', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', $IdVenta);
    $Correo->EnviarCorreo($Nombre, $Mail, "ACTUALIZACION DE DATOS", $Mensaje);
    header('Location: https://kasu.com.mx' . $Host);
    exit();
}

/********************************* BLOQUE: REGISTRO DE PROBLEMAS *********************************/
if (isset($_POST['Reporte'])) {
    $problema = isset($_POST['problema']) ? $mysqli->real_escape_string($_POST['problema']) : '';
    $DatProblema = [
        "Usuario"  => $_SESSION["Vendedor"],
        "Problema" => $problema
    ];
    $basicas->InsertCampo($mysqli, "Problemas", $DatProblema);
    $msg = base64_encode('Gracias por enviarnos tu reporte');
    header('Location: https://kasu.com.mx' . $Host . '?Msg=' . $msg);
    exit();
}

?>