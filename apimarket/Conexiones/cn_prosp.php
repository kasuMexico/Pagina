<?php
//varibles de conexiones de base de datos
    $db_host = "localhost";
    $db_user = "u557645733_prospectos";
    $db_password = ";&3HiMo2[";
    $db_name = "u557645733_prospectos";
//coneccion con la base de datos para el registro de datos
$pros = new mysqli($db_host, $db_user, $db_password, $db_name);
//validacion de la conexion
      //if ($pros->connect_errno){
      //    echo "<br> Fallo al conectar a MySQL: (" . $pros->connect_errno . ") " . $pros->connect_error;
      //}
      //echo $pros->host_info . "\n"."Conexion Base Prospectos <br>";
?>
