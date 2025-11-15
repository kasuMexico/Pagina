<?php
//Este código busca todos los archivos con extensión ".php" en la carpeta "Funciones"  con un array los requiere
foreach (glob("../Funciones/*.php") as $archivo) {
    require_once $archivo;
}
//creamos una variable general para las funciones
$basicas = new Basicas();
//Requerir las conexiones
require_once '../Conexiones/cn_vtas.php';
//require_once '../Conexiones/cn_pruebas.php';
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

//****  Esta peticion nos permite saber datos individuales de un; PRODUCTO, CLIENTE o COMPRA dadas las siguientes condiciones;
//****  Esta peticion nos permite reaalizar una busqueda por grupo de alguna de las siguientes situaciones
//****  Situacion 1.- Se realiza una busqueda de un cliente y muestra sus datos generales de el cliente; el usuario deberia conocer un dato de el cliente que funcione como id unico CURP
//****  Situacion 2.- Se realiza la busqueda de un producto y muestra toda la informacion de un producto; el usuario deberia conocer un dato de el cliente que funcione como id unico NOMBRE
//****  Situacion 3.- Se realiza la busqueda de un servicio vendido a un Cliente determinado; el usuario deberia conocer un dato especifico de la compra que funcione como id unico FIRMA
//****  el Token de Acceso, retorna el costo del producto y te permite calcular las comisiones, pagos y maximos tiempos de credito
//****  API_KEY_AQUI	   Reemplaza el API_KEY_AQUI con el TOKEN recibido en la petición de AUTENTICACION
//****  tipo_peticion	   Especifica el tipo de petición, debe ser establecido segun las tablas de acceso
//****  curp_en_uso	     La clave CURP de el cliente con el que interactuaras
//****  producto	       Especifica el tipo de producto, debe ser establecido segun las tablas de acceso
//****  token_data	     Es el token retornado por la peticion de ACCESO a API_REGISTRO
//****  timestamp	       EL tiempo en el cual se genero el token retornado por la peticion de ACCESO a API_REGISTRO
//****  expires_in	     EL tiempo en el cual sera valido el token retornado por la peticion de ACCESO a API_REGISTRO
// Procesar la petición

// Verificar que los datos necesarios estén presentes
  if (!isset($data['request'], $data['tipo_peticion'], $data['nombre_de_usuario'], $data['curp_en_uso'])) {
      header('HTTP/1.1 400 Bad Request');
      exit;
  }
  //Insertamos la validacion de token de acceso
  require_once 'Validador_Token.php';

  //Realizamos un registro de eventos cada que se realice una peticion resulte positiva o negativa
  //buscamos el dato de contacto de el cliente
  $IdContacto = $basicas->BuscarCampos($mysqli,"IdContact","Usuario","ClaveCurp",$data['curp_en_uso']);
  //Realizamos un insert en la base de datos como evento realizado
  $DatEventos = array(
      "Contacto"      => $data['nombre_de_usuario'],
      "Evento"        => $data['tipo_peticion'],
      "MetodGet"      => $data['request'],
      "Host"          => $_SERVER['PHP_SELF'],
      "Usuario"       => $_SERVER['HTTP_USER_AGENT'],
      "IdUsr"         => $IdContacto,
      "FechaRegistro" => date('Y-m-d')." ".date('H:i:s')
  );
  //Se realiza el insert en la base de datos
  $basicas->InsertCampo($mysqli,"Eventos",$DatEventos);


  if($data['tipo_peticion'] === "request" AND $data['request'] === "request_block"){ //Consulta las claves que deben ponerse en individual request
    //imprimimos los datos que se pueden solicitarf y la sintaxis correcta de la peticion
    // Enviar la respuesta en formato JSON
    header('HTTP/1.1 202 OK');
    header('Content-Type: application/json');
    //Envia un producto solamente con todas sus caracteristicas
    echo json_encode(
      array(
        'request_block'=> array(
          'cliente'             => 'Consulta los datos generales de un cliente',
          'catalogo_productos'  => 'Consulta todos los productos existentes',
          'producto_cliente'    => 'Consulta los datos que son viables, se agrega => producto'
        )
      )
    );
    exit;
  //Bloque de consultas indivi
  }elseif($data['tipo_peticion'] === "request" AND $data['request'] === "individual_request"){ //Consulta las claves que deben ponerse en individual request
    //imprimimos los datos que se pueden solicitarf y la sintaxis correcta de la peticion
    // Enviar la respuesta en formato JSON
    header('HTTP/1.1 202 OK');
    header('Content-Type: application/json');
    //Envia un producto solamente con todas sus caracteristicas
    echo json_encode(
      array(
        'Datos_Contacto'=> array(
          'Mail'          => 'Correo electronico',
          'Telefono'      => 'Telefono registrado',
          'calle'         => 'Calle registrada',
          'numero'        => 'Numero registrado',
          'colonia'       => 'Colonia registrada',
          'municipio'     => 'Municipio registrado',
          'codigo_postal' => 'Codigo postal registrado',
          'estado'        => 'Estado de residencia'
        ),
        'Datos_usuario' => array(
          'Usuario'       => 'Id de ejecutivo que vendio',
          'Tipo'          => 'Si beneficiario => fue registrado por un tercero | Si cliente => compro el producto',
          'Nombre'        => 'Nombre registrado en RENAPO',
          'Paterno'       => 'Apellido Paterno registrado en RENAPO',
          'Materno'       => 'Apellido Materno registrado en RENAPO'
        ),
        'Datos_ventas' => array(
          'Producto'      => 'Producto de primer registro',
          'CostoVenta'    => 'El precio pagado por el servicio',
          'NumeroPagos'   => 'El numero de pagos pendientes',
          'IdFIrma'       => 'Numero identificador de la poliza',
          'Status'        => 'El status que tiene el servicio',
          'FechaRegistro' => 'fecha en la que se registro la venta'
        )
      )
    );
    exit;
  //Bloque de consultas indivi
  }elseif($data['tipo_peticion'] === "individual_request" AND $data['request'] != "producto"){ // consulta un dato especifico de el cliente
    //Buscamos la clave proporcionada por el usuario
    $Autorizacion = $basicas->BuscarCampos($mysqli,"Clave","Autorizacion","ClaveCurp",$data['curp_en_uso']);
    //Validamos que las consultas sean autorizadas
    if($Autorizacion == "No"){
      //Cerramos las conexiones a la base de datos
      mysqli_close($mysqli);
      //retornamos un error si es incorrecta la autorizacion dada por el cliente
      header('HTTP/1.1 409 Bad Request');
      exit;
    }
    //Realizamos la busqueda en la base de datos del id para los registros que buscan los datos de contacto
    $IdContact = $basicas->BuscarCampos($mysqli,"IdContact","Usuario","ClaveCurp",$data['curp_en_uso']);
    //Realizamos las busquedas individualizadas
    if (in_array($data['request'], ["Mail", "Telefono", "calle", "numero", "colonia", "municipio", "codigo_postal", "estado", "Producto"])) { //Inician las precondiciones de busqueda
      //Busqueda en la tabla Contacto
      $Retorno = $basicas->BuscarCampos($mysqli,$data['request'],"Contacto","id",$IdContact);
    }elseif (in_array($data['request'], ["Usuario", "Tipo", "Nombre"])) {
      //Busqueda en la tabla Usuario
      $Retorno = $basicas->BuscarCampos($mysqli,$data['request'],"Usuario","ClaveCurp",$data['curp_en_uso']);
    }elseif (in_array($data['request'], ["CostoVenta", "NumeroPagos", "IdFIrma", "Status", "FechaRegistro"])) {
      //Identificamos si hay mas de una venta
      $Cont_vtas = $basicas->ConUno($mysqli,"Venta","IdContact",$IdContact);
      if($Cont_vtas > 1){

        $producto = array(); // Se declara el array vacío

        $sql_Contac = "SELECT * FROM Venta WHERE IdContact = '".$IdContacto."'";
        $res_Contac = mysqli_query($mysqli, $sql_Contac);

        if($Reg_Contac=mysqli_fetch_assoc($res_Contac)) {
            // Obtener datos del producto y guardarlos en el array
            $producto = array(
                'valor' => $Reg_Contac['request']
            );
            array_push($Retorno, $producto);
        }
      }else{
        //Busqueda en la tabla Venta
        $Retorno = $basicas->BuscarCampos($mysqli,$data['request'],"Venta","IdContact",$IdContact);
      }
    }else{
      //Cerramos las conexiones a la base de datos
      mysqli_close($mysqli);
      //No esta especificada en la precondicion
      header('HTTP/1.1 412 Bad Request');
      exit;
    }
    //Cerramos las conexiones a la base de datos
    mysqli_close($mysqli);
    // Enviar la respuesta en formato JSON
    header('HTTP/1.1 202 OK');
    header('Content-Type: application/json');
    //Envia un producto solamente con todas sus caracteristicas
    echo json_encode(
      array(
        'curp_en_uso'   => $data['curp_en_uso'],
        'data'          => $Retorno
      )
    );
    exit;
  //retornamos los datos de un cliente
  }elseif($data['tipo_peticion'] === "request_block" AND $data['request'] === "cliente"){ //consulta los datos generales de un cliente
    //Buscamos la clave proporcionada por el usuario
    $Autorizacion = $basicas->BuscarCampos($mysqli,"Clave","Autorizacion","ClaveCurp",$data['curp_en_uso']);
    //Validamos que las consultas sean autorizadas
    if($Autorizacion == "No"){
      //Cerramos las conexiones a la base de datos
      mysqli_close($mysqli);
      //retornamos un error si es incorrecta la autorizacion dada por el cliente
      header('HTTP/1.1 409 Bad Request');
      exit;
    }
    //Buscamos los datos en las tablas
    $sql_usuario = "SELECT * FROM Usuario WHERE IdContact = '".$IdContacto."'";
    //Realiza consulta
    $res_usuario = mysqli_query($mysqli, $sql_usuario);
    //Si existe el registro se asocia en un fetch_assoc
    if($Reg_usuario=mysqli_fetch_assoc($res_usuario)){}
    //Crear consulta
    $sql_Contac = "SELECT * FROM Contacto WHERE id = '".$IdContacto."'";
    //Realiza consulta
    $res_Contac = mysqli_query($mysqli, $sql_Contac);
    //Si existe el registro se asocia en un fetch_assoc
    if($Reg_Contac=mysqli_fetch_assoc($res_Contac)){}
    //Cerramos las conexiones a la base de datos
    mysqli_close($mysqli);
    // Enviar la respuesta en formato JSON
    header('HTTP/1.1 202 OK');
    header('Content-Type: application/json');
    //Envia un producto solamente con todas sus caracteristicas
    echo json_encode(
      array(
        'curp_en_uso'   => $data['curp_en_uso'],
        'Ejecutivo'     => $Reg_Contac['Usuario'],
        'Nombre'        => $Reg_usuario['Nombre'],
        'Tipo'          => $Reg_usuario['Tipo'],
        'Mail'          => $Reg_Contac['Mail'],
        'Telefono'      => $Reg_Contac['Telefono'],
        'Producto'      => $Reg_Contac['Producto'],
        'FechaRegistro' => $Reg_usuario['FechaRegistro'],
        'Direccion'     => array(
            'calle'         => $Reg_Contac['calle'],
            'numero'        => $Reg_Contac['numero'],
            'colonia'       => $Reg_Contac['colonia'],
            'municipio'     => $Reg_Contac['municipio'],
            'codigo_postal' => $Reg_Contac['codigo_postal'],
            'estado'        => $Reg_Contac['estado']
        )
      )
    );
    exit;
  //Si esta consulta es la consulta sobre producto muestra este dato
  }elseif($data['tipo_peticion'] === "request_block" AND $data['request'] === "producto_cliente"){ //consulta los datos que son viables para un cliente
    //Variables para multimples consultas
    $producto = $data['producto'];
    //Validamos el producto con base en la edad del cliente para saber si es apto
    if($basicas->VerificarProducto($data['curp_en_uso'],$producto) == false){
      //Si el cliente tiene mas de la edad aceptable del producto
      header('HTTP/1.1 406 No aceptable');
      exit;
    }
    //si el producto es Funerario obtenemos el bloque del producto
    if($producto == "Funerario"){
      //Obtene mos la edad de el cliente
      $EdadCte = $basicas->ObtenerEdad($data['curp_en_uso']);
      $producto =  $basicas->ProdFune($EdadCte);
    }
    $costo      = $basicas->BuscarCampos($mysqli,"Costo","Productos","Producto",$producto);
    $comision   = $basicas->BuscarCampos($mysqli,"comision","Productos","Producto",$producto);
    $meses_max  = $basicas->BuscarCampos($mysqli,"MaxCredito","Productos","Producto",$producto);
    $tasa_anual = $basicas->BuscarCampos($mysqli,"TasaAnual","Productos","Producto",$producto);
    //Cerramos las conexiones a la base de datos
    mysqli_close($mysqli);
    // Enviar la respuesta en formato JSON
    header('HTTP/1.1 202 OK');
    header('Content-Type: application/json');
    //Envia un producto solamente con todas sus caracteristicas
    echo json_encode(
      array(
        'producto'   => $producto,
        'costo'      => $costo,
        'comision'   => $comision,
        'foma_pago'  => array(
          'meses_max'     => $meses_max,
          'tasa_anual'  => $tasa_anual
        )
      )
    );
  }elseif($data['tipo_peticion'] === "request_block" AND $data['request'] === "catalogo_productos"){ //consulta todos los productos existentes
    // Obtenemos el dato maximo de productos
    $Max = $basicas->MaxDat($mysqli,"Id","Productos");
    $a = 1;
    $productos = array();
    while ($a <= $Max) {
        //Creamos el array que imprima los datos
        $producto = array(
            'producto'        => $basicas->BuscarCampos($mysqli,"Producto","Productos","Id",$a),
            'costo'           => $basicas->BuscarCampos($mysqli,"Costo","Productos","Id",$a),
            'comision'        => $basicas->BuscarCampos($mysqli,"comision","Productos","Id",$a),
            'fideicomiso'     => $basicas->BuscarCampos($mysqli,"Fideicomiso","Productos","Id",$a),
            'foma_pago'       => array(
                  'meses_max'     => $basicas->BuscarCampos($mysqli,"MaxCredito","Productos","Id",$a),
                  'tasa_anual'  => $basicas->BuscarCampos($mysqli,"TasaAnual","Productos","Id",$a)
            )
        );
        array_push($productos, $producto);
        $a++;
    }
    //Cerramos las conexiones a la base de datos
    mysqli_close($mysqli);
    // Enviar la respuesta en formato JSON
    header('HTTP/1.1 202 OK');
    header('Content-Type: application/json');
    echo json_encode($productos);
    exit;
  } else {
    // Si se llega hasta aquí, se recibió una petición desconocida
    header('HTTP/1.1 404 Not Found');
    exit;
  }
  ob_end_flush(); // Enviar la salida almacenada en búfer al cliente
