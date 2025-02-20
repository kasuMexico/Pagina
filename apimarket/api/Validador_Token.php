<?php
//creamos una variable general para las funciones
$basicas = new Basicas();
$seguridad = new Seguridad();
// *****  Este Codigo Realiza la validacion de los Token de Acceso requiere el envio de los siguientes datos
// *****  HTTP_AUTHORIZATION contiene el Token enviado en la cabecera con el token Bearer
// *****  nombre_de_usuario  Es el Usuario con el que se autenticara el usuario
// *****  $data Contiene los datos con los que se genero el token
// *****  Sebe Tenerse mucho cuidado con la forma en que se homologan las variables para no contener errores de registro
//Obtenemos el valor de el TOKEN
$authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'];
$token = substr($authorizationHeader, 7); // El número 7 representa la longitud del prefijo "Bearer "
//Descargamos la contraseña de el usuario
$password_usuario = $basicas->BuscarCampos($mysqli,"Pass","Empleados","IdUsuario",$data['nombre_de_usuario']);
//Esta funcion Valida y genere el Usr Agent
$Usr_Agent = $seguridad->ValidarUsrAPI($mysqli,$data['nombre_de_usuario'],$_SERVER['HTTP_USER_AGENT']);
//Buscamos los datos para gener el Secret_KEY
$Secret_KEY = hash_hmac('sha256',$Usr_Agent,$password_usuario);
//Validamos por que no se aprobo el token
$Valid_Token = $seguridad->verificarToken($token,$data,$Secret_KEY);
//Validamos el Token de ACCeso
if ($Valid_Token === false) {
  header('HTTP/1.1 401 Unauthorized');
  exit;
} elseif($Valid_Token === "exced_time") {
  header('HTTP/1.1 418 tiempo excedido');
  exit;
}
