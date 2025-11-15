<?php
//varibles de conexiones de base de datos
    $db_host = "localhost"; // o prueba con la IP: 93.188.160.2 || srv908.hstgr.io
    $db_user = "u557645733_prospectos";
    $db_password = "Bo^6WK4ON8";
    $db_name = "u557645733_prospectos";
//coneccion con la base de datos para el registro de datos
$pros = new mysqli($db_host, $db_user, $db_password, $db_name);
//validacion de la conexion
    //if ($pros->connect_errno){
    //    echo "<br> Fallo al conectar a MySQL: (" . $pros->connect_errno . ") " . $pros->connect_error;
    //  }
    //      echo $pros->host_info . "\n"."Conexion Base Prospectos <br>";
?>