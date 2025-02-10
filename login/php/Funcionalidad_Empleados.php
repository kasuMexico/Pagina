<?php
// Iniciar sesión
session_start();

// Incluir el archivo de librerías que contiene las clases actualizadas
require_once 'librerias.php';

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');

// Variables de tiempo actuales
$hoy = date('Y-m-d');
$HoraActual = date('H:i:s');

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
    Basicas::InsertCampo($mysqli, "Comisiones_pagos", $DatGps);

    // Redireccionar a la pantalla del empleado
    header('Location: https://kasu.com.mx' . $Host . '?Vt=1&name=' . $name);
    exit();
}

/********************************* BLOQUE: CAMBIO DE VENDEDOR *********************************/
if (isset($_POST['CambiVend'])) {
    $IdEmpleado = isset($_POST['IdEmpleado']) ? $mysqli->real_escape_string($_POST['IdEmpleado']) : '';
    $NvoVend   = isset($_POST['NvoVend'])   ? $mysqli->real_escape_string($_POST['NvoVend'])   : '';
    $Host      = isset($_POST['Host'])      ? $mysqli->real_escape_string($_POST['Host'])      : '';
    $name      = isset($_POST['name'])      ? $mysqli->real_escape_string($_POST['name'])      : '';

    // Verificar el equipo actual del empleado
    $IdBAse = Basicas::BuscarCampos($mysqli, "Equipo", "Empleados", "Id", $IdEmpleado);
    if ($IdBAse != $NvoVend) {
        Basicas::ActCampo($mysqli, "Empleados", "Equipo", $NvoVend, $IdEmpleado);
    }

    header('Location: https://kasu.com.mx' . $Host . '?Vt=1&name=' . $name);
    exit();
}

/********************************* BLOQUE: BAJA DE EMPLEADO *********************************/
if (isset($_POST['BajaEmp'])) {
    $IdEmpleado = isset($_POST['IdEmpleado']) ? $mysqli->real_escape_string($_POST['IdEmpleado']) : '';
    $MotivoBaja = isset($_POST['MotivoBaja']) ? $mysqli->real_escape_string($_POST['MotivoBaja']) : '';
    $Host       = isset($_POST['Host'])       ? $mysqli->real_escape_string($_POST['Host'])       : '';
    $name       = isset($_POST['name'])       ? $mysqli->real_escape_string($_POST['name'])       : '';

    // Obtener el Id de contacto del empleado
    $IdBAse = Basicas::BuscarCampos($mysqli, "IdContacto", "Empleados", "Id", $IdEmpleado);
    // Actualizar datos del empleado a "baja"
    Basicas::ActCampo($mysqli, "Empleados", "Nombre", "Vacante", $IdEmpleado);
    Basicas::ActCampo($mysqli, "Empleados", "IdUsuario", "Usuario", $IdEmpleado);
    Basicas::ActCampo($mysqli, "Empleados", "IdContacto", NULL, $IdEmpleado);
    Basicas::ActCampo($mysqli, "Empleados", "Pass", NULL, $IdEmpleado);
    Basicas::ActCampo($mysqli, "Empleados", "Equipo", NULL, $IdEmpleado);
    Basicas::ActCampo($mysqli, "Empleados", "Sucursal", NULL, $IdEmpleado);
    // Registrar el motivo de baja en la tabla Contacto
    Basicas::ActCampo($mysqli, "Contacto", "Motivo", $MotivoBaja, $IdBAse);

    header('Location: https://kasu.com.mx' . $Host . '?Vt=1&name=' . $name);
    exit();
}

/********************************* BLOQUE: CREAR EMPLEADO Y ENVIAR CORREO *********************************/
if (isset($_POST['CreaEmpl'])) {
    // Extraer y sanitizar las variables necesarias
    $Nombre   = isset($_POST['Nombre'])   ? $mysqli->real_escape_string($_POST['Nombre'])   : '';
    $Email    = isset($_POST['Email'])    ? $mysqli->real_escape_string($_POST['Email'])    : '';
    $Telefono = isset($_POST['Telefono']) ? $mysqli->real_escape_string($_POST['Telefono']) : '';
    $Direccion= isset($_POST['Direccion'])? $mysqli->real_escape_string($_POST['Direccion']): '';
    $Host     = isset($_POST['Host'])     ? $mysqli->real_escape_string($_POST['Host'])     : '';
    $name     = isset($_POST['name'])     ? $mysqli->real_escape_string($_POST['name'])     : '';
    $Nivel    = isset($_POST['Nivel'])    ? $mysqli->real_escape_string($_POST['Nivel'])    : '';
    $Sucursal = isset($_POST['Sucursal']) ? $mysqli->real_escape_string($_POST['Sucursal']) : '';
    $Lider    = isset($_POST['Lider'])    ? $mysqli->real_escape_string($_POST['Lider'])    : '';
    $Cuenta   = isset($_POST['Cuenta'])   ? $mysqli->real_escape_string($_POST['Cuenta'])   : '';
    $IdProspecto = isset($_POST['IdProspecto']) ? $mysqli->real_escape_string($_POST['IdProspecto']) : '';

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
    $uSR = Basicas::InsertCampo($mysqli, "Contacto", $DatContac);

    // Se busca un registro en Empleados (por ejemplo, el primer disponible de un nivel y sucursal)
    $Reg = Basicas::Buscar2Campos($mysqli, "Id", "Empleados", "Sucursal", 0, "Nivel", $Nivel);
    if (!empty($Reg)) {
        Basicas::ActCampo($mysqli, "Empleados", "Nombre", $Nombre, $Reg);
        Basicas::ActCampo($mysqli, "Empleados", "IdContacto", $uSR, $Reg);
        Basicas::ActCampo($mysqli, "Empleados", "IdUsuario", NULL, $Reg);
        Basicas::ActCampo($mysqli, "Empleados", "Pass", $dRc, $Reg);
        Basicas::ActCampo($mysqli, "Empleados", "Equipo", $Lider, $Reg);
        Basicas::ActCampo($mysqli, "Empleados", "Sucursal", $Sucursal, $Reg);
        Basicas::ActCampo($mysqli, "Empleados", "FechaAlta", $hoy, $Reg);
        Basicas::ActCampo($mysqli, "Empleados", "Cuenta", $Cuenta, $Reg);
    }
    // Si el nivel es 7, se asume que se debe realizar algún ajuste adicional
    if ($Nivel == 7) {
        $contra = '&Add=' . $uSR;
        // Actualiza el estado del prospecto
        Basicas::ActCampo($pros, "prospectos", "Cancelacion", 1, $IdProspecto);
    }
    header('Location: https://kasu.com.mx' . $Host . '?Ml=1&name=' . $name . $contra);
    exit();
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
    Basicas::ActCampo($mysqli, "Empleados", "Pass", $dRc, $Id);
    $Mensaje = Correo::Mensaje('RESTABLECIMIENTO DE CONTRASEÑA', $Nombre, $DirUrl, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
    Correo::EnviarCorreo($Nombre, $Email, 'RESTABLECIMIENTO DE CONTRASEÑA', $Mensaje);
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
        $PassRecordId = Basicas::BuscarCampos($mysqli, "Id", "Empleados", "Pass", $decodedPass);
        if (!empty($PassRecordId)) {
            // Registro de eventos: insertar GPS y FingerPrint si aún no existen
            $gpsLogin = Basicas::InsertCampo($mysqli, "gps", [
                "Latitud" => isset($_POST['Latitud']) ? $mysqli->real_escape_string($_POST['Latitud']) : '',
                "Longitud" => isset($_POST['Longitud']) ? $mysqli->real_escape_string($_POST['Longitud']) : '',
                "Presicion" => isset($_POST['Presicion']) ? $mysqli->real_escape_string($_POST['Presicion']) : ''
            ]);
            if (empty(Basicas::BuscarCampos($mysqli, "id", "FingerPrint", "fingerprint", $fingerprint))) {
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
                Basicas::InsertCampo($mysqli, "FingerPrint", $DatFinger);
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
            Basicas::InsertCampo($mysqli, "Eventos", $DatEventos);

            // Convertir contraseña a SHA-256
            $PassSHa = hash('sha256', $PassWord1);
            // Actualizar datos del empleado (asumiendo que $PassRecordId es la clave a actualizar)
            Basicas::ActCampo($mysqli, "Empleados", "IdUsuario", isset($_POST['User']) ? $_POST['User'] : '', $PassRecordId);
            Basicas::ActCampo($mysqli, "Empleados", "Pass", $PassSHa, $PassRecordId);
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
    $IdVenta = Basicas::BuscarCampos($mysqli, "Id", "Venta", "IdContact", $IdContact);
    if (!empty($IdVenta)) {
        $SDTM = Basicas::BuscarCampos($mysqli, "Producto", "Venta", "Id", $IdVenta);
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
        $NvoCnc = Basicas::InsertCampo($mysqli, "Contacto", $Pripg);
        Basicas::ActTab($mysqli, "Usuario", "IdContact", $NvoCnc, "IdContact", $IdContact);
        if (!empty($IdVenta)) {
            Basicas::ActCampo($mysqli, "Venta", "IdContact", $NvoCnc, $IdVenta);
        }
    }
    // Enviar correo de actualización
    $Mensaje = Correo::Mensaje("ACTUALIZACION DE DATOS", $Nombre, 'https://kasu.com.mx/ActualizacionDatos/index.php', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', $IdVenta);
    Correo::EnviarCorreo($Nombre, $Mail, "ACTUALIZACION DE DATOS", $Mensaje);
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
    Basicas::InsertCampo($mysqli, "Problemas", $DatProblema);
    $msg = base64_encode('Gracias por enviarnos tu reporte');
    header('Location: https://kasu.com.mx' . $Host . '?Msg=' . $msg);
    exit();
}
?>