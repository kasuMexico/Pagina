<?php
//varibles de conexiones de base de datos
    $db_host = "localhost";
    $db_user = "u557645733_kasuw";
    $db_password = ";9Ai!5;G0QU";
    $db_name = "u557645733_web";
//coneccion con la base de datos para el registro de datos
$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
//Validar la conexion con la base de datos
    // if ($mysqli->connect_errno){
    //     echo "Fallo al conectar a MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    // }
    //     echo $mysqli->host_info . "\n"."Conexion Base Datos Ventas <br>";

?>
