<?php
// Activar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar la sesión
session_start();

// Requerir el archivo de librerías
require_once 'eia/librerias.php';

// Para depuración: confirmar carga de librerías
//echo "Librerías cargadas correctamente.<br>";

// Instanciar la clase Basicas (asegúrate de que la clase esté definida en las librerías)
$basicas = new Basicas();
$seguridad = new Seguridad();

//echo "Instancia de Basicas creada.<br>";

//inlcuir el archivo de funciones
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'].'/');
//require_once ($_SERVER['DOCUMENT_ROOT'].'/eia/Funciones/Funciones_Basicas.php');
//require_once ($_SERVER['DOCUMENT_ROOT'].'/eia/Funciones/Funciones_Seguridad.php');
//require_once ($_SERVER['DOCUMENT_ROOT'].'/eia/Conexiones/cn_prueba.php');

try {

  // Asignar vededor
  $VendeDor = "PLATAFORMA";

  //Se crea el array que contiene los datos de GPS
  $DatGps = array (
  		"Latitud"   => $mysqli -> real_escape_string($Latitud),
  		"Longitud"  => $mysqli -> real_escape_string($Longitud),
  		"Presicion" => $mysqli -> real_escape_string($Presicion)
  );
  //Se realiza el insert en la base de datos del GPS
  $gps = $basicas->InsertCampo($mysqli,"gps",$DatGps);

  // generar el ciclo para leer row por row del csv
  $row = 1;

  // registros agregados
  $agregados = 0;

  // registros rechazados
  $rechazados = 0;

  if (($handle = fopen("personal.csv", "r")) !== FALSE) {
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
          // $num = count($data);
          // echo "<p>registro $row: <br /></p>\n";

          // Se segmentan los datos para poder agregarlo, se epro qu eel csv tenag este acomodo antes de subir la informacion
          $nombre = $data[2];
          $paterno = $data[2];
          $materno = $data[2];
          $curp = $data[4];
          $mail = $data[4];
          $edad = $data[10];
          $genero = $data[7];
          $tipo = $data[10];

          // Buscar usuario
          $OPsd = $basicas->BuscarCampos($mysqli,"Nombre","Usuario","ClaveCurp",$curp);
          if(!empty($OPsd)){
            $rechazados++;
          } else {
            // se asigna el producto y subproducto
            $Prod = "Funerario";

            // Buscar el producto segun sea el caso
            switch ($tipo) {
                case 'Policia':
                    // código para la opción 'Retiro'
                    $SubProd = $basicas->ProdPoli($edad);
                    break;
                case 'Retiro':
                    // código para la opción 'Retiro'
                    break;
                case 'Universidad':
                    // código para la opción 'Universidad'
                    break;
                case 'VIDACOLEC':
                    // código para la opción 'VIDACOLEC'
                    break;
                default:
                    $SubProd = $basicas->ProdFune($edad);
                    break;
            }

            // se asigna el costo y la tasa
            $_SESSION["Costo"] = $basicas->BuscarCampos($mysqli,"Costo","Productos","Producto",$SubProd);
            $_SESSION["Tasa"] = $basicas->BuscarCampos($mysqli,"TasaAnual","Productos","Producto",$SubProd);

            // se crean los registros necesarios
            $_SESSION["Cnc"] = agregarDatosContacto($VendeDor, $gps, 'N/A', 'N/A', 'N/A', $SubProd, $mysqli);

            // Generar el legal antes de la venta
            $firma = $seguridad->Firma($mysqli,$_SESSION["Cnc"],$_SESSION["Costo"]);
            $_SESSION["Venta"] = generarPago($VendeDor, $_SESSION["Cnc"], $Prod, $_SESSION["Costo"], $gps, 1, $firma, 'S/D', 'S/D', $mysqli);

            // Agregar el evento y registar el cliente
            $_SESSION["evento"] = agregarEvento($_SESSION["Cnc"], $gps, 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', $mysqli);
            $_SESSION["cliente"] = agregarCliente($_SESSION["Cnc"], $nombre, $paterno, $materno, $curp, $mail, $mysqli);


            // echo '<pre>' . print_r($_SESSION, TRUE) . '</pre>';
            // die();
            $agregados++;
          }
          // echo $SubProd.' - '.$nombre.' - '.$curp.' - '.$edad.' - '.$genero.' - '.$_SESSION["Costo"].' - '.$_SESSION["Tasa"].'<br>';
          // for ($c=0; $c < $num; $c++) {
          //     echo $data[$c] . "<br />\n";
          // }
          $row++;
      }
      fclose($handle);
  }
  echo 'Total registros: '.$row.'<br>';
  echo 'Total agregados: '.$agregados.'<br>';
  echo 'Total rechazados: '.$rechazados.'<br>';
} catch (\Exception $e) {
  die($e);
}


/********************************************************************************************************************************************
                																	Carga de DATOS CONTACTO del cliente
********************************************************************************************************************************************/
function agregarDatosContacto($VendeDor, $gps, $Host, $Mail, $Telefono, $Producto, $mysqli)
{
  //Se crea el array que contiene los datos de registro
  $DatContac = array (
      "Usuario"   => $VendeDor,
      "Idgps"     => $gps,
      "Host"      => $mysqli -> real_escape_string($Host),
      "Mail"      => $mysqli -> real_escape_string($Mail),
      "Telefono"  => $mysqli -> real_escape_string($Telefono),
      "Producto"  => $mysqli -> real_escape_string($Producto)
  );

  //Se realiza el insert en la base de datos
  return $basicas->InsertCampo($mysqli,"Contacto",$DatContac);
}

/********************************************************************************************************************************************
                																	Carga de DATOS EVENTO del cliente
********************************************************************************************************************************************/
function agregarEvento($contacto, $gps, $Host, $formfields, $connection, $timezone, $touch, $cupon, $mysqli)
{
  //Se crea el array que contiene los datos para REGISTRO DE EVENTOS
  $DatEventos = array(
      "IdFInger"    => 'S/D',
      "Contacto"    => $contacto,
      "Idgps"       => $gps,
      "Evento"      => "Registro",
      "Host"        => $mysqli -> real_escape_string($Host),
      "MetodGet"    => $mysqli -> real_escape_string($formfields),
      "connection"  => $mysqli -> real_escape_string($connection),
      "timezone"    => $mysqli -> real_escape_string($timezone),
      "touch"       => $mysqli -> real_escape_string($touch),
      "Cupon"       => $cupon,
      "FechaRegistro" => date('Y-m-d')." ".date('H:i:s')
  );
  //Se realiza el insert en la base de datos
  return $basicas->InsertCampo($mysqli,"Eventos",$DatEventos);
}

/********************************************************************************************************************************************
                													Carga de CURP cuando la venta es para el cliente
********************************************************************************************************************************************/
function agregarCliente($idContacto, $nombre, $paterno, $materno, $curp, $mail, $mysqli)
{
  //Se crea el array que contiene los datos de registro
  $DatUser = array (
      "IdContact"    => $_SESSION["Cnc"],
      "Usuario"       => $VendeDor,
      "Tipo"          => "Cliente",
      "Nombre"        => $ArrayRes["Nombre"],
      "ClaveCurp"     => $ArrayRes["Curp"],
      "Email"         => $_SESSION["Mail"]
  );
  //Se realiza el insert en la base de datos
  return $basicas->InsertCampo($mysqli,"Usuario",$DatUser);
}

/********************************************************************************************************************************************
                																Generar pago
********************************************************************************************************************************************/
function generarPago($VendeDor, $idContacto, $producto, $costo, $gps, $Meses, $firma, $tarjeta, $TipoServicio, $mysqli)
{
  datosLegal($idContacto, 1, 'S/D', 'S/D', 'S/D', $mysqli);

  //Buscamos los datos y realizamos un registro en la venta
  $Venta = array (
      "Usuario"       => $VendeDor,
      "IdContact"     => $idContacto,
      "Nombre"        => $basicas->BuscarCampos($mysqli,"Nombre","Usuario","IdContact",$idContacto),
      "Producto"      => $producto,
      "CostoVenta"    => $costo,
      "Idgps"         => $gps,
      "NumeroPagos"   => $mysqli -> real_escape_string($Meses),
      "IdFIrma"       => $mysqli -> real_escape_string($seguridad->Firma($mysqli,$idContacto,$costo)),
      "Status"        => "PREVENTA",
      "Mes"           => date("M"),
      "Cupon"         => $tarjeta,
      "TipoServicio"  => $mysqli -> real_escape_string($TipoServicio)
  );
  //Insertar los datos en la base
  return $basicas->InsertCampo($mysqli,"Venta",$Venta);
}

/********************************************************************************************************************************************
                																Datos legal
********************************************************************************************************************************************/
function datosLegal($idContacto, $Meses = 1, $Terminos = 'S/D', $Aviso = 'S/D', $Fideicomiso = 'S/D', $mysqli)
{
  //Se crea el array que contiene los datos de registro
  $DatLegal = array (
      "IdContacto"    => $idContacto,
      "Meses"         => $mysqli -> real_escape_string($Meses),
      "Terminos"      => $mysqli -> real_escape_string($Terminos),
      "Aviso"         => $mysqli -> real_escape_string($Aviso),
      "Fideicomiso"   => $mysqli -> real_escape_string($Fideicomiso)
  );

  //Se realiza el insert en la base de datos
  return $basicas->InsertCampo($mysqli,"Legal",$DatLegal);
}
