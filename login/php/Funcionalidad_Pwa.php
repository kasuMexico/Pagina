<?php 

// DEBUG: Activar todos los errores y mostrar datos importantes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Si no existe la variable de sesión "Vendedor", redirigir a la página de login
if (!isset($_SESSION["Vendedor"])) {
    header('Location: https://kasu.com.mx/login/');
    exit();
}

// Incluir el archivo de librerías que contiene las clases actualizadas
require_once '../../eia/librerias.php';

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');

// Variables de tiempo actuales
$hoy = date('Y-m-d');
$HoraActual = date('H:i:s');

/********************************* BLOQUE: ACTUALIZAR DATOS DE UN CLIENTE *********************************/
if (!empty($_POST['ActDatosCTE'])){
    // Extraer y sanitizar las variables necesarias
    $calle    = isset($_POST['calle'])   ? $mysqli->real_escape_string($_POST['calle'])   : '';
    $Email    = isset($_POST['Email'])    ? $mysqli->real_escape_string($_POST['Email'])    : '';
    $Telefono = isset($_POST['Telefono']) ? $mysqli->real_escape_string($_POST['Telefono']) : '';
    $Host     = isset($_POST['Host'])     ? $mysqli->real_escape_string($_POST['Host'])     : '';
    $Producto = isset($_POST['Producto'])     ? $mysqli->real_escape_string($_POST['Producto'])     : '';
    //Se registran los datos de el finger print, gps y Evento
    $ids = $seguridad->auditoria_registrar(
        $mysqli,                     // conexión principal
        $basicas,                    // tu helper Basicas
        $_POST,                      // datos del form (fingerprint, gps, etc.)
        'Cambio_Contacto',           // nombre del evento
        $_POST['Host'] ?? $_SERVER['PHP_SELF']  // host/origen
    );
    // Insertar datos de Contacto para el empleado
    $NvoRegistroarray = [
        "Usuario"   => $_SESSION["Vendedor"],
        "Host"      => $Host,
        "Mail"      => $Email,
        "Telefono"  => $Telefono,
        "calle"     => $calle,
        "Idgps"     => $ids['gps_id'],
        "Producto"  => $Producto
    ];
    $NvoRegistro = $basicas->InsertCampo($mysqli, "Contacto", $NvoRegistroarray);
    //Actualizamos el ID del Venta 
    $basicas->ActCampo($mysqli, "Venta", "IdContact", $NvoRegistro, $_POST['IdVenta']);
    //Actualizamos el ID del Usuario 
    $basicas->ActCampo($mysqli, "Usuario", "IdContact", $NvoRegistro, $_POST['IdUsuario']);
    //mensaje de alert para usuario
    $Msg = "Se han actualizado los datos de el cliente";
    //Redireccionar a pagina de donde venimos
    header('Location: https://kasu.com.mx' . $_POST['Host'] . '?Vt=1&Msg='.$Msg.'&name=' . $_POST['nombre']);
    exit();
}
/********************************* BLOQUE: REGISTRAR UN TICKET DE ATENCION AL CLIENTE *********************************/
if (!empty($_POST['AltaTicket'])){
    
    // Extraer y sanitizar las variables necesarias
    $Producto       = isset($_POST['Producto'])         ? $mysqli->real_escape_string($_POST['Producto'])   : '';
    $Status         = isset($_POST['Status'])           ? $mysqli->real_escape_string($_POST['Status'])    : '';
    $Prioridad      = isset($_POST['Prioridad'])        ? $mysqli->real_escape_string($_POST['Prioridad']) : '';
    $Descripcion    = isset($_POST['Descripcion'])    ? $mysqli->real_escape_string($_POST['Descripcion']) : '';
    $Telefono       = isset($_POST['Telefono'])         ? $mysqli->real_escape_string($_POST['Telefono'])     : '';
    //Se registran los datos de el finger print, gps y Evento
    $ids = $seguridad->auditoria_registrar(
        $mysqli,                     // conexión principal
        $basicas,                    // tu helper Basicas
        $_POST,                      // datos del form (fingerprint, gps, etc.)
        'Ticket_Atencion',           // nombre del evento
        $_POST['Host'] ?? $_SERVER['PHP_SELF']  // host/origen
    );
    // Insertar datos para ticket
    $NvoRegistroarray = [
        "IdVta"         => $_POST["IdVenta"],
        "IdUsr"         => $_POST["IdUsuario"],
        "IdContacto"    => $_POST["IdContact"],
        "Ticket"        => $_POST["Descripcion"],
        "Vendedor"      => $_SESSION["Vendedor"],
        "Host"          => $_POST["Host"],
        "Prioridad"     => $_POST["Prioridad"],
        "Status"        => $_POST["Status"],
        "Telefono"      => $_POST["Telefono"]
    ];
    //Registramos en la base de datos la leyenda
    $NvoRegistro = $basicas->InsertCampo($mysqli, "Atn_Cliente", $NvoRegistroarray);
    //mensaje de alert para usuario
    $Msg = "Se ha registrado correctamente el Ticket";
    //Redireccionar a pagina de donde venimos
    header('Location: https://kasu.com.mx' . $_POST['Host'] . '?Vt=1&Msg='.$Msg.'&name=' . $_POST['nombre']);
    exit();
}



/********************************* BLOQUE: REGISTRAR SERVICIO FUNERARIO *********************************/
if (!empty($_POST['RegisFun'])){
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';



}




/********************************* BLOQUE: PAGO DE CLIENTE *********************************/
if (isset($_POST['Pago'])) {
    // Extraer y sanitizar las variables necesarias desde POST
    $IdVenta        = isset($_POST['IdVenta'])   ? $mysqli->real_escape_string($_POST['IdVenta'])   : '';
    $IdVendedor     = isset($_POST['IdVendedor']) ? $mysqli->real_escape_string($_POST['IdVendedor']) : '';
    $Host           = isset($_POST['Host'])     ? $mysqli->real_escape_string($_POST['Host'])     : '';
    $NombreBuscado  = isset($_POST['NombreBuscado'])    ? $mysqli->real_escape_string($_POST['NombreBuscado'])    : '';
    $Cantidad       = isset($_POST['Cantidad'])       ? $mysqli->real_escape_string($_POST['Cantidad'])       : '';
    $Status         = isset($_POST['Status'])       ? $mysqli->real_escape_string($_POST['Status'])       : '';
    $Promesa        = isset($_POST['Promesa'])       ? $mysqli->real_escape_string($_POST['Promesa'])       : '';


    // Crear array de datos para el registro de comisión
    $DatGps = [
        "UsrResgistra"  => $_SESSION["Vendedor"],
        "Cantidad"      => $IdVenta,
        "IdVendedor"    => $IdVendedor,
        "Banco"         => $Host,
        "Referencia"    => $NombreBuscado,
        "Referencia"    => $Cantidad,
        "Referencia"    => $Status,
        "Referencia"    => $Promesa,
        "fechaRegistro" => $hoy . " " . $HoraActual
    ];

    echo '<pre>';
    print_r($DatGps);
    echo '</pre>';

    // Insertar datos en la tabla Comisiones_pagos
    //$basicas->InsertCampo($mysqli, "Comisiones_pagos", $DatGps);

    // Redireccionar a la pantalla del empleado
    //header('Location: https://kasu.com.mx' . $Host . '?Vt=1&name=' . $name);
    //exit();
}