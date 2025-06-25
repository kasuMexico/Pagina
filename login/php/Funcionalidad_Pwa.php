<?php 
// Iniciar la sesión
session_start();

require_once '../../eia/librerias.php';

// Si no existe la variable de sesión "Vendedor", redirigir a la página de login
if (!isset($_SESSION["Vendedor"])) {
    header('Location: https://kasu.com.mx/login/');
    exit();
}
echo '<pre>';
print_r($_POST);
echo '</pre>';

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