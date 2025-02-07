<?php
//Archivo que registra en la base de datos los comentarios de la pagina prinicipal
//Insertar la conexion con la base de datos
require_once 'Conexiones/cn_vtas.php';
//Se crean las variables prinicipales
$nombre = $mysqli -> real_escape_string($_POST['name']);
$correo = $mysqli -> real_escape_string($_POST['email']);
$mensaje = $mysqli -> real_escape_string($_POST['message']);
//Se insertan en la base de datos
if (!empty($_POST)){
   $Ins = "INSERT INTO ContacIndex (Nombre, Correo, Mensaje, Fecha) VALUES ('$nombre','$correo','$mensaje',now())";
   $ConNew =$mysqli->query($Ins);
   $Msg = "Gracias por contactarnos, en breve te responderemos";
}else{
   $Msg = "Error al contactarnos, intenta más tarde";
}
header("Location: index.php?Msg=".$Msg);
