<?php
//Este código busca todos los archivos con extensión ".php" en la carpeta "Funciones"  con un array los requiere
foreach (glob("../Funciones/*.php") as $archivo) {
    require_once $archivo;
}
//creamos una variable general para las funciones
$basicas = new Basicas();
$seguridad = new Seguridad();
//Requerir las conexiones
//require_once '../Conexiones/cn_vtas.php';
require_once '../Conexiones/cn_pruebas.php';
//require_once '../Conexiones/cn_prosp.php';
// Iniciar el almacenamiento en búfer
ob_start();

// Verificar que la petición sea de tipo POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit;
}

// Leer el contenido de la petición y convertirlo a un array de PHP
  $input = file_get_contents('php://input');
  $data = json_decode($input, true);

//Insertamos el Archivo que genera los token de Acceso
  require_once 'Token_Full.php';

//****  Esta peticion nos permite saber el costo de el producto que seleccione el cliente, recuerda que debes usar la CLAVE CURP que fue usada para generar
//****  el Token de Acceso, retorna el costo del producto y te permite calcular las comisiones, pagos y maximos tiempos de credito
//****  API_KEY_AQUI	   Reemplaza el API_KEY_AQUI con el TOKEN recibido en la petición de AUTENTICACION
//****  tipo_peticion	   Especifica el tipo de petición, debe ser establecido segun las tablas de acceso
//****  curp_en_uso	     La clave CURP de el cliente con el que interactuaras
//****  producto	       Especifica el tipo de producto, debe ser establecido segun las tablas de acceso
//****  token_data	     Es el token retornado por la peticion de ACCESO a API_REGISTRO
//****  timestamp	       EL tiempo en el cual se genero el token retornado por la peticion de ACCESO a API_REGISTRO
//****  expires_in	     EL tiempo en el cual sera valido el token retornado por la peticion de ACCESO a API_REGISTRO
// Procesar la petición
if ($data['tipo_peticion'] == 'product_cost') {
      // Verificar que los datos necesarios estén presentes
      if (!isset($data['curp_en_uso'], $data['producto'], $data['nombre_de_usuario'])) {
          header('HTTP/1.1 400 Bad Request');
          exit;
      }
      //Obtenemos el valor de el TOKEN
      $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'];
      $token = substr($authorizationHeader, 7); // El número 7 representa la longitud del prefijo "Bearer "
      //Variable para multimples consultas
      $producto = $data['producto'];
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
      }else{
        //Validamos el producto
        if($basicas->VerificarProducto($data['curp_en_uso'],$producto)){
          //si el producto es Funerario obtenemos el bloque del producto
          if($producto == "Funerario"){
            //Obtene mos la edad de el cliente
            $EdadCte = $basicas->ObtenerEdad($data['curp_en_uso']);
            $producto =  $basicas->ProdFune($EdadCte);
          }
            // Enviar la respuesta en formato JSON
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json');
            echo json_encode(
              array(
                  'costo'         => $basicas->BuscarCampos($mysqli,"Costo","Productos","Producto",$producto),
                  'comision'      => $basicas->BuscarCampos($mysqli,"comision","Productos","Producto",$producto),
                  'foma_pago'     => array(
                      'meses_max'     => $basicas->BuscarCampos($mysqli,"MaxCredito","Productos","Producto",$producto),
                      'tasa_interes'  => $basicas->BuscarCampos($mysqli,"TasaAnual","Productos","Producto",$producto)
                  )
              )
            );
            exit;
        }else{
          //Si el cliente tiene mas de la edad aceptable del producto
          header('HTTP/1.1 406 No aceptable');
          exit;
        }
      }
    }
//****  Esta peticion nos permite saber el costo de el producto que seleccione el cliente, recuerda que debes usar la CLAVE CURP que fue usada para generar
//****  el Token de Acceso, retorna el costo del producto y te permite calcular las comisiones, pagos y maximos tiempos de credito
//****  API_KEY_AQUI	   Reemplaza el API_KEY_AQUI con el TOKEN recibido en la petición de AUTENTICACION
//****  tipo_peticion	   Especifica el tipo de petición, debe ser establecido segun las tablas de acceso
//****  curp_en_uso	     La clave CURP de el cliente con el que interactuaras
//****  producto	       Especifica el tipo de producto, debe ser establecido segun las tablas de acceso
//****  token_data	     Es el token retornado por la peticion de ACCESO a API_REGISTRO
//****  timestamp	       EL tiempo en el cual se genero el token retornado por la peticion de ACCESO a API_REGISTRO
//****  expires_in	     EL tiempo en el cual sera valido el token retornado por la peticion de ACCESO a API_REGISTRO
if ($data['tipo_peticion'] == 'registro_servicio') { // if tipo_peticion
      // Verificar que los datos necesarios estén presentes
      if (!isset($data['curp_en_uso'],$data['mail'],$data['telefono'], $data['producto'], $data['numero_pagos'],$data['terminos'], $data['aviso'], $data['fideicomiso'],$data['nombre_de_usuario'])) {
          header('HTTP/1.1 400 Bad Request');
          exit;
      }
      //Validamos que hayase aceptado los temrminos y condicones
      if($data['terminos'] != "acepto" && $data['aviso'] != "acepto" && $data['fideicomiso'] != "acepto"){
        //Si el cliente tiene mas de la edad aceptable del producto
        header('HTTP/1.1 406 No aceptable');
        exit;
      }
      //Obtenemos el valor de el TOKEN
      $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'];
      $token = substr($authorizationHeader, 7);
      //Variable para multimples consultas
      $producto = $data['producto'];
      //Descargamos la contraseña de el usuario
      $password_usuario = $basicas->BuscarCampos($mysqli,"Pass","Empleados","IdUsuario",$data['nombre_de_usuario']);
      //Esta funcion Valida y genere el Usr Agent
      $Usr_Agent = $seguridad->ValidarUsrAPI($mysqli,$data['nombre_de_usuario'],$_SERVER['HTTP_USER_AGENT']);
      //Buscamos los datos para gener el Secret_KEY
      $Secret_KEY = hash_hmac('sha256',$Usr_Agent,$password_usuario);
      //Validamos por que no se aprobo el token
      $Valid_Token = $seguridad->verificarToken($token,$data,$Secret_KEY);
      //Validamos el Token de ACCeso
      if ($Valid_Token === false) {  //Validamos el TOKEN
        /*header('HTTP/1.1 201 OK');
        header('Content-Type: application/json');
        echo json_encode(
          array(
               'error' => "401",
               'password_usuario' => $password_usuario,
               'Usr_Agent' => $Usr_Agent,
               'Secret_KEY' => $Secret_KEY,
               'Valid_Token' => $Valid_Token
          )
        );*/
          header('HTTP/1.1 401 Unauthorized');
          exit;
      } elseif($Valid_Token === "exced_time") {
          header('HTTP/1.1 418 tiempo excedido');
          exit;
      }else{ //Validamos el TOKEN
      //si el producto es Funerario obtenemos el bloque del producto
      if($producto == "Funerario"){
            //Obtene mos la edad de el cliente
            $EdadCte = $basicas->ObtenerEdad($data['curp_en_uso']);
            $producto =  $basicas->ProdFune($EdadCte);
      }
      //Buscamos si el cliente no se encuentra duplicado en la base de datos
      $OPsd = $basicas->BuscarCampos($mysqli,"IdContact","Usuario","ClaveCurp",$data['curp_en_uso']);
      //Si el cliente ya se encuentraregistrado arroja un error
      if(!empty($OPsd)){
            //Buscamos si el cliente no se encuentra duplicado en la base de datos
            $DJsuT = $basicas->BuscarCampos($mysqli,"Producto","Venta","IdContact",$OPsd);
            //Se comparan los productos
            if($DJsuT == $producto){ //Producto Duplicado
              //Si el cliente tiene mas de la edad aceptable del producto
              header('HTTP/1.1 412 No aceptable');
              exit;
            }
      }
      //Se busca que el cliente exista
      $ArrayRes = $seguridad->peticion_get($data['curp_en_uso']);
      //Validamos que la curp sea real
      if($ArrayRes["Response"] == "correct" AND $ArrayRes["StatusCurp"] != "BD"){ //Validamos la Clave CURP
              //Creamos la Direccion de el Cliente
              $calle          = $mysqli -> real_escape_string($data['direccion']['calle']);
              $numero         = $mysqli -> real_escape_string($data['direccion']['numero']);
              $colonia        = $mysqli -> real_escape_string($data['direccion']['colonia']);
              $municipio      = $mysqli -> real_escape_string($data['direccion']['municipio']);
              $codigo_postal  = $mysqli -> real_escape_string($data['direccion']['codigo_postal']);
              $estado         = $mysqli -> real_escape_string($data['direccion']['estado']);
              //Aseguramos los datos recibidos por las API REST FULL
              $User_Agent     = $mysqli -> real_escape_string($_SERVER['HTTP_USER_AGENT']);
              $curp_en_uso    = $mysqli -> real_escape_string($data['curp_en_uso']);
              $mail           = $mysqli -> real_escape_string($data['mail']);
              $telefono       = $mysqli -> real_escape_string($data['telefono']);
              $numero_pagos    = $mysqli -> real_escape_string($data['numero_pagos']);
              $terminos       = $mysqli -> real_escape_string($data['terminos']);
              $aviso          = $mysqli -> real_escape_string($data['aviso']);
              $fideicomiso    = $mysqli -> real_escape_string($data['fideicomiso']);
              //Si el pago es de Contado para que el pago de contado sea 1
              if($numero_pagos == 0){$numero_pagos = 1;}
              //Se registra el array para el registro en la base de datos de Contacto
              $DatContac = array (
                 "Usuario"   => $User_Agent,
                 "Host"      => "API_REGISTRO",
                 "Mail"      => $mail,
                 "Telefono"  => $telefono,
                 "Direccion" => $calle." ".$numero.", ".$colonia." ".$municipio." ".$estado." C.P.".$codigo_postal,
                 "Producto"  => $producto
              );
              //Se realiza el insert en la base de datos
              $IdContacto = $basicas->InsertCampo($mysqli,"Contacto",$DatContac);
              //Registramos el nombre de el cliente
              $nombre = $ArrayRes["Nombre"]." ".$ArrayRes["Paterno"]." ".$ArrayRes["Materno"];
              //Se crea el array que contiene los datos de registro
              $DatUser = array (
                  "IdContact"     => $IdContacto,
                  "Usuario"       => $User_Agent,
                  "Tipo"          => "Cliente",
                  "Nombre"        => $nombre,
                  "ClaveCurp"     => $curp_en_uso,
                  "Email"         => $mail
              );
              //Se realiza el insert en la base de datos
              $basicas->InsertCampo($mysqli,"Usuario",$DatUser);
              //Se crea el array que contiene los datos de registro
              $DatLegal = array (
                  "IdContacto"    => $IdContacto,
                  "Meses"         => $numero_pagos,
                  "Terminos"      => $terminos,
                  "Aviso"         => $aviso,
                  "Fideicomiso"   => $fideicomiso
              );
              //Se realiza el insert en la base de datos
              $basicas->InsertCampo($mysqli,"Legal",$DatLegal);
              //Buscar precios y tasas
              $Costo = $basicas->BuscarCampos($mysqli,"Costo","Productos","Producto",$producto);
              $Tasa = $basicas->BuscarCampos($mysqli,"TasaAnual","Productos","Producto",$producto);
              //Se genera la referencia unica del cte MMN
              $firma = $seguridad->Firma($mysqli,$IdContacto,$Costo);
              //Buscamos los datos y realizamos un registro en la venta
              $Venta = array (
                  "Usuario"       => $User_Agent,
                  "IdContact"     => $IdContacto,
                  "Nombre"        => $nombre,
                  "Producto"      => $producto,
                  "CostoVenta"    => $Costo,
                  "NumeroPagos"   => $numero_pagos,
                  "IdFIrma"       => $firma,
                  "Status"        => "PREVENTA",
                  "Mes"           => date("M"),
                  "TipoServicio"  => "Ecologico"
                );
                //Insertar los datos en la base
                $IdVenta = $basicas->InsertCampo($mysqli,"Venta",$Venta);
                //Se crea el array que contiene los datos para REGISTRO DE EVENTOS
                $DatEventos = array(
                    "Contacto"      => $IdContacto,
                    "Evento"        => "Vta",
                    "Host"          => $User_Agent,
                    "FechaRegistro" => date('Y-m-d')." ".date('H:i:s')
                );
                //Se realiza el insert en la base de datos
                $basicas->InsertCampo($mysqli,"Eventos",$DatEventos);
                // Enviar la respuesta en formato JSON
                header('HTTP/1.1 201 OK');
                header('Content-Type: application/json');
                echo json_encode(
                  array(
                       'mensaje'          => "Registro exitoso del servicio ".$data['producto'],
                       'datos_compra'     => array(
                          'nombre' => $nombre,
                          'CURP'   => $curp_en_uso,
                          'mail'   => $mail,
                          'poliza' => $firma,
                          'Status' => "PREVENTA",
                          'Costo'  => $Costo
                       )
                  )
                );
            exit;
        } else { //Validamos la Clave CURP
          //La clave curp de el cliente no existe
          header('HTTP/1.1 417 No aceptable');
          exit;
        }
      } //Validamos el TOKEN
  }// if tipo_peticion

ob_end_flush(); // Enviar la salida almacenada en búfer al cliente
// Si se llega hasta aquí, se recibió una petición desconocida
header('HTTP/1.1 404 Not Found');
exit;
