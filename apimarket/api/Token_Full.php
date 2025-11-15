<?php
//creamos una variable general para las funciones
$basicas = new Basicas();
$seguridad = new Seguridad();
//La peticion debe ser por metodo POST y el cuerpo de la solicitud
//debe estar en formato (Content-Type: application/json)
//y debe contener los siguientes parámetros:
//Tipo_Peticion	    Especifica el tipo de petición, debe ser establecido segun las tablas de acceso
//nombre_de_usuario	Especifica tu nombre de usuario registrado en la aplicación KASU.
//Firma_KEY	        Firma la clave CURP de tu cliente con tu Secret KEY mediante el algoritmo criptográfico HMAC.
//curp_en_uso	      La clave CURP para generar la firma HMAC que este cifrada en BASE64.
if ($data['tipo_peticion'] == 'token_full') {

    // Verificar que los datos necesarios estén presentes
    if (!isset($data['nombre_de_usuario'], $data['firma_KEY'], $data['curp_en_uso'])) {
        header('HTTP/1.1 400 Bad Request');
        exit;
    }
    // Verificar las credenciales del usuario
    if ($Usr_Agent = $seguridad->ValidarUsrAPI($mysqli,$data['nombre_de_usuario'],$_SERVER['HTTP_USER_AGENT'])) {
      //Descargamos la contraseña de el usuario
      $password_usuario = $basicas->BuscarCampos($mysqli,"Pass","Empleados","IdUsuario",$data['nombre_de_usuario']);
      //Buscamos los datos para gener el Secret_KEY
      $Secret_KEY = hash_hmac('sha256',$Usr_Agent,$password_usuario);
      //Enviamos a la funcion de validacion de curp
        $ArrayRes =  $seguridad->peticion_get($data['curp_en_uso']);

      if ($ArrayRes["Response"] == "Error" || $ArrayRes["StatusCurp"] == "BD") {
          header('HTTP/1.1 417 Bad Request');
          exit;
      }
      // Verificar la FIRMA_KEY
      $firma_key_sha = hash_hmac('sha256',$data['curp_en_uso'],$Secret_KEY);
      //Validamos que los hashsean el mismo
      if ($data['firma_KEY'] != $firma_key_sha) {
          header('HTTP/1.1 401 Unauthorized');
          exit;
      }
        //como el tiempo de expiración y los datos del usuario
        $token_data = array(
            "timestamp"   => time(),
            "expires_in"  => 6000, // 10 minutos de duracion por Token 600
        );
        //Convertir los datos en formato JSON.
        $token_data_json = json_encode($token_data);
        //Generar un hash HMAC de los datos JSON utilizando la clave segura generada en el paso 2.
        $token = hash_hmac('sha256', $token_data_json, $firma_key_sha); //Este es el token que contiene todos los datos de uso
        // Enviar la respuesta en formato JSON
        header('HTTP/1.1 200 OK');
        header('Content-Type: application/json');
        echo json_encode(
          array(
            'token'      => $token,
            'nombre'     => $ArrayRes["Nombre"]." ".$ArrayRes["Paterno"]." ".$ArrayRes["Materno"],
            'token_data' => $token_data
          )
        );
        exit;
    }else{
        // Devolver un mensaje de error si las credenciales son inválidas 401
        header('HTTP/1.1 403 Unauthorized');
        exit;
    }
}
