<?php
//varibles de conexiones de base de datos
    $db_host = "localhost";
    $db_user = "u557645733_blog_kasu";
    $db_password = 'fZ^;u$gj:8'; //se cambio la contraseña la vieja era:  kasupruebas
    $db_name = "u557645733_blog_kasu";
//coneccion con la base de datos para el registro de datos
$cnp = new mysqli($db_host, $db_user, $db_password, $db_name);
//validacion de la conexion
     //if ($mysqli->connect_errno){
     //    echo "Fallo al conectar a MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
     //}
     //echo $mysqli->host_info . "\n"."Conexion base Blog <br>";
?>
