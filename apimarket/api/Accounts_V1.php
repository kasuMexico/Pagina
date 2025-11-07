<?php
// Incluye la librería de funciones y conexiones (asegúrate de que la ruta es correcta)
require_once 'librerias_api.php';
//Este código busca todos los archivos con extensión ".php" en la carpeta "Funciones"  con un array los requiere
foreach (glob("../Funciones/*.php") as $archivo) {
    require_once $archivo;
}

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
if ($data['tipo_peticion'] === 'new_service') { // if tipo_peticion
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
      //Insertamos la validacion de token de acceso
      require_once 'Validador_Token.php';
      //Variable para multimples consultas
      $producto = $data['producto'];
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
              //Cerramos las conexiones a la base de datos
              mysqli_close($mysqli);
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
                 "Usuario"        => $User_Agent,
                 "Host"           => "API_REGISTRO",
                 "Mail"           => $mail,
                 "Telefono"       => $telefono,
                 "calle"          => $calle,
                 "numero"         => $numero,
                 "colonia"        => $colonia,
                 "municipio"      => $municipio,
                 "estado"         => $estado,
                 "codigo_postal"  => $codigo_postal,
                 "Producto"       => $producto
              );
              //Se realiza el insert en la base de datos
              $IdContacto = $basicas->InsertCampo($mysqli,"Contacto",$DatContac);
              //Se crea el array que contiene los datos de registro
              $DatUser = array (
                  "IdContact"     => $IdContacto,
                  "Usuario"       => $User_Agent,
                  "Tipo"          => "Cliente",
                  "Nombre"        => $ArrayRes["Nombre"],
                  "Paterno"       => $ArrayRes["Paterno"],
                  "Materno"       => $ArrayRes["Materno"],
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
              //Registramos el nombre de el cliente
              $nombre = $ArrayRes["Nombre"]." ".$ArrayRes["Paterno"]." ".$ArrayRes["Materno"];
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
                //Cerramos las conexiones a la base de datos
                mysqli_close($mysqli);
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
  }// if tipo_peticion


ob_end_flush(); // Enviar la salida almacenada en búfer al cliente
// Si se llega hasta aquí, se recibió una petición desconocida
header('HTTP/1.1 404 Not Found');
exit;
