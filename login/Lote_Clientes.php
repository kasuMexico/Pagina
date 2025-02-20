<?php
  //indicar que se inicia una sesion
  session_start();

//   define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/');

  //inlcuir el archivo de funciones
    require_once '../eia/librerias.php';

  //Validar si existe la session y redireccionar
  if(!isset($_SESSION["Vendedor"])){
      header('Location: https://kasu.com.mx/login');
  }else{
    //realizamos la consulta
    $venta = "SELECT * FROM Empleados WHERE IdUsuario = '".$_SESSION["Vendedor"]."'";

    //Realiza consulta
    $res = mysqli_query($mysqli, $venta);
    //Si existe el registro se asocia en un fetch_assoc
    if($Reg=mysqli_fetch_assoc($res)){
      //Seleccion de Usuarios por nivel del usuario
      $Vende = $Reg['Nivel'];

      //realizamos la consulta
      $ContC = "SELECT * FROM Contacto WHERE Id = '".$Reg["IdContacto"]."'";

      //Realiza consulta
      $ResCt = mysqli_query($mysqli, $ContC);

      //Si existe el registro se asocia en un fetch_assoc
      if($RegCt=mysqli_fetch_assoc($ResCt)){
      }
    }
  }
  $registrosFallidos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['archivo_csv']) && $_FILES['archivo_csv']['error'] === UPLOAD_ERR_OK) {
        $nombreArchivo = $_FILES['archivo_csv']['tmp_name'];

        $contenidoCSV = file_get_contents($nombreArchivo);
        $filas = explode(PHP_EOL, $contenidoCSV);

        if (count($filas) > 0) {
            $encabezados = str_getcsv(array_shift($filas));

            if (validarEncabezados($encabezados)) {
                $registrosTotales = 0;
                $registrosExitosos = 0;

                foreach ($filas as $fila) {
                    $datos = str_getcsv($fila);

                    if (count($datos) === count($encabezados)) {
                        if (validarDatos($datos)) {
                           $registrosCorrectos[] = guardarRegistroEnBD($datos, $mysqli);
                            $registrosExitosos++;
                        } else {
                            $registrosFallidos[] = [
                                obtenerDatoFallido($datos)
                            ];
                        }

                        $registrosTotales++;
                    }
                }

                echo 'Estadísticas:<br>';
                echo 'Registros totales: ' . $registrosTotales . '<br>';
                echo 'Registros exitosos: ' . $registrosExitosos . '<br>';
                echo 'Registros fallidos: ' . (count($registrosFallidos)) . '<br>';

                if (count($registrosCorrectos) > 0) {
                    $nombreArchivo = 'registros_' . date('Y_m_d_H_i_s') . '.csv';
                    $rutaArchivo = dirname(__FILE__) . '/' . $nombreArchivo;

                    // Crear el contenido del archivo CSV
                    $archivoFallidos = fopen($nombreArchivo, 'w');

                    // Escribir encabezados en el archivo CSV
                    array_push($encabezados, 'mensaje');
                    fputcsv($archivoFallidos, $encabezados);

                    // Escribir registros fallidos en el archivo CSV
                    foreach ($registrosCorrectos as $registro) {
                        array_push($registro[0][0], implode(' - ', $registro[0][1]));
                        fputcsv($archivoFallidos, $registro[0][0]);
                    }

                    fclose($archivoFallidos);

                    // Guardar el archivo CSV en el servidor
                    file_put_contents($rutaArchivo, $archivoFallidos);
                    echo '<script>';
                    echo 'window.open("https://kasu.com.mx/login/' . $nombreArchivo . '", "_blank");';
                    echo '</script>';

                    echo 'Registros generados en: https://kasu.com.mx/login/' . $nombreArchivo . '<br>';
                }

                if (count($registrosFallidos) > 0) {
                    $nombreArchivoFallidos = 'registros_fallidos_'.date('Y_m_d_H_i_s').'.csv';
                    $rutaArchivoFallidos = dirname(__FILE__).'/' . $nombreArchivoFallidos;

                    // Crear el contenido del archivo CSV
                    $archivoFallidos = fopen($nombreArchivoFallidos, 'w');

                    // Escribir encabezados en el archivo CSV
                    array_push($encabezados, 'mensaje');
                    fputcsv($archivoFallidos, $encabezados);

                    // Escribir registros fallidos en el archivo CSV
                    foreach ($registrosFallidos as $registro) {
                        array_push($registro[0][0], implode(' - ', $registro[0][1]));
                        fputcsv($archivoFallidos, $registro[0][0]);
                    }

                    fclose($archivoFallidos);

                    // Guardar el archivo CSV en el servidor
                    file_put_contents($rutaArchivoFallidos, $archivoFallidos);
                    echo '<script>';
                    echo 'window.open("https://kasu.com.mx/login/' . $nombreArchivoFallidos . '", "_blank");';
                    echo '</script>';

                    echo 'Registros fallidos generados en: https://kasu.com.mx/login/' . $nombreArchivoFallidos . '<br>';
                }

                echo '<script>alert("
                    Estadísticas:<br>
                    Registros totales: ' . $registrosTotales . '<br>
                    Registros exitosos: ' . $registrosExitosos . '<br>
                    Registros fallidos: ' . (count($registrosFallidos)) . '<br>
                    Registros generados en: https://kasu.com.mx/login/' . $nombreArchivo . '<br>
                    Registros fallidos generados en: https://kasu.com.mx/login/' . $nombreArchivoFallidos . '<br>
                "); window.history.back();</script>';
                exit;
            } else {
                echo '<script>alert("Los encabezados del CSV no son válidos"); window.history.back();</script>';
                exit;
            }
        } else {
            echo '<script>alert("El archivo CSV está vacío"); window.history.back();</script>';
            exit;
        }
    } else {
        echo '<script>alert("No se ha enviado ningún archivo CSV"); window.history.back();</script>';
        exit;
    }
}

function validarEncabezados($encabezados)
{
    $encabezadosEsperados = ['id', 'nombre', 'apellido_paterno', 'apellido_materno','telefono', 'email', 'curp', 'edad', 'plan'];
    return ($encabezados === $encabezadosEsperados);
}

function validarDatos($datos)
{
    $expRegNombre = '/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/';
    $expRegApellidoPaterno = '/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/';
    $expRegApellidoMaterno = '/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/';
    $expRegTelefono = '/^\d{10}$/';
    $expRegEmail = '/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/';
    $expRegCurp = '/^[A-Z]{4}\d{6}[HM](AS|BC|BS|CC|CH|CL|CM|CS|DF|DG|GR|GT|HG|JC|MC|MN|MS|NE|NL|NT|OC|PL|QR|QT|SL|SI|SM|SO|TB|TL|TS|VZ|YN|ZS)[A-Z]{3}[A-Z0-9]\d$/';
    $expRegEdad = '/^[1-9][0-9]{0,2}$/';

    return (
        preg_match($expRegNombre, $datos[1]) &&
        preg_match($expRegApellidoPaterno, $datos[2]) &&
        preg_match($expRegApellidoMaterno, $datos[3]) &&
        preg_match($expRegTelefono, $datos[4]) &&
        preg_match($expRegEmail, $datos[5]) &&
        preg_match($expRegCurp, $datos[6]) &&
        preg_match($expRegEdad, $datos[7])
    );
}

function guardarRegistroEnBD($datos, $mysqli)
{
    try {

        // Asignar vededor
        $VendeDor = "PLATAFORMA";

        //Se crea el array que contiene los datos de GPS
        $DatGps = array(
            "Latitud"   => $mysqli->real_escape_string($Latitud),
            "Longitud"  => $mysqli->real_escape_string($Longitud),
            "Presicion" => $mysqli->real_escape_string($Presicion)
        );
        //Se realiza el insert en la base de datos del GPS
        $gps = $basicas->InsertCampo($mysqli, "gps", $DatGps);

        // Se segmentan los datos para poder agregarlo, se epro qu eel csv tenag este acomodo antes de subir la informacion
        $nombre = $datos[1];
        $paterno = $datos[2];
        $materno = $datos[3];
        $telefono = $datos[4];
        $mail = $datos[5];
        $curp = $datos[6];
        $edad = $datos[7];
        $tipo = $datos[8];

        $datosFallidos = [];

        // Buscar usuario
        $OPsd = $basicas->BuscarCampos($mysqli, "Nombre", "Usuario", "ClaveCurp", $curp);
        if (!empty($OPsd)) {
            $datosFallidos[] = [$datos, ['Datos ya registrados']];
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
            $_SESSION["Costo"] = $basicas->BuscarCampos($mysqli, "Costo", "Productos", "Producto", $SubProd);
            $_SESSION["Tasa"] = $basicas->BuscarCampos($mysqli, "TasaAnual", "Productos", "Producto", $SubProd);

            // se crean los registros necesarios
            $_SESSION["Cnc"] = agregarDatosContacto($VendeDor, $gps, 'N/A', 'N/A', 'N/A', $SubProd, $mysqli);

            // Generar el legal antes de la venta
            $firma = $seguridad->Firma($mysqli, $_SESSION["Cnc"], $_SESSION["Costo"]);
            $_SESSION["Venta"] = generarPago($VendeDor, $_SESSION["Cnc"], $Prod, $_SESSION["Costo"], $gps, 1, $firma, 'S/D', 'S/D', $mysqli);

            // Agregar el evento y registar el cliente
            $_SESSION["evento"] = agregarEvento($_SESSION["Cnc"], $gps, 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', $mysqli);
            $_SESSION["cliente"] = agregarCliente($_SESSION["Cnc"], $nombre, $paterno, $materno, $curp, $mail, $mysqli);
        }

        $datosFallidos[] = [$datos, ['Registro exitoso']];
    } catch (\Exception $e) {
        $datosFallidos[] = [$datos, ['Fallo registro']];
    }
    return $datosFallidos;
}

function obtenerDatoFallido($datos)
{
    // $expRegNombre = '/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/';
    // $expRegApellidoPaterno = '/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/';
    // $expRegApellidoMaterno = '/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/';
    // $expRegTelefono = '/^\d{10}$/';
    // $expRegEmail = '/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/';
    // $expRegCurp = '/^[A-Z]{4}\d{6}[HM](AS|BC|BS|CC|CH|CL|CM|CS|DF|DG|GR|GT|HG|JC|MC|MN|MS|NE|NL|NT|OC|PL|QR|QT|SL|SI|SM|SO|TB|TL|TS|VZ|YN|ZS)[A-Z]{3}[A-Z0-9]\d$/';
    // $expRegEdad = '/^[1-9][0-9]{0,2}$/';

    // $datosFallidos = [];
    // $mensajeFallido = [];

    // if (!preg_match($expRegNombre, $datos[0])) {
    //     $mensajeFallido[] = "Nombre";
    // } elseif (!preg_match($expRegApellidoPaterno, $datos[1])) {
    //     $mensajeFallido[] = "Paterno";
    // } elseif (!preg_match($expRegApellidoMaterno, $datos[2])) {
    //     $mensajeFallido[] = "Materno";
    // } elseif (!preg_match($expRegTelefono, $datos[3])) {
    //     $mensajeFallido[] = "Telefono";
    // } elseif (!preg_match($expRegEmail, $datos[4])) {
    //     $mensajeFallido[] = "Email";
    // } elseif (!preg_match($expRegCurp, $datos[5])) {
    //     $mensajeFallido[] = "Curp";
    // }

    // $datosFallidos[] = [$datos, $mensajeFallido];
    // var_dump($datosFallidos[0]);
    // die();

    $expresionesRegulares = [
        'nombre' => '/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/',
        'paterno' => '/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/',
        'materno' => '/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/',
        'telefono' => '/^\d{10}$/',
        'email' => '/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/',
        // 'curp' => '/^[A-Za-z0-9]{18}$/',
        'curp' => '/^[A-Z]{4}\d{6}[HM](AS|BC|BS|CC|CH|CL|CM|CS|DF|DG|GR|GT|HG|JC|MC|MN|MS|NE|NL|NT|OC|PL|QR|QT|SL|SI|SM|SO|TB|TL|TS|VZ|YN|ZS)[A-Z]{3}[A-Z0-9]\d$/',
        'edad' => '/^[1-9][0-9]{0,2}$/'
    ];
    $atributos = array_keys($expresionesRegulares);

    $datosFallidos = [];

    foreach ($datos as $indice => $dato) {
        if (!preg_match($expresionesRegulares[$indice], $dato)) {
            $datosFallidos[] = $atributos[$indice];
        }
    }
    return [$datos, ['Fallo datos, revisar registro']];
}

/********************************************************************************************************************************************
                																	Carga de DATOS CONTACTO del cliente
 ********************************************************************************************************************************************/
function agregarDatosContacto($VendeDor, $gps, $Host, $Mail, $Telefono, $Producto, $mysqli)
{
    //Se crea el array que contiene los datos de registro
    $DatContac = array(
        "Usuario"   => $VendeDor,
        "Idgps"     => $gps,
        "Host"      => $mysqli->real_escape_string($Host),
        "Mail"      => $mysqli->real_escape_string($Mail),
        "Telefono"  => $mysqli->real_escape_string($Telefono),
        "Producto"  => $mysqli->real_escape_string($Producto)
    );

    //Se realiza el insert en la base de datos
    return $basicas->InsertCampo($mysqli, "Contacto", $DatContac);
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
        "Host"        => $mysqli->real_escape_string($Host),
        "MetodGet"    => $mysqli->real_escape_string($formfields),
        "connection"  => $mysqli->real_escape_string($connection),
        "timezone"    => $mysqli->real_escape_string($timezone),
        "touch"       => $mysqli->real_escape_string($touch),
        "Cupon"       => $cupon,
        "FechaRegistro" => date('Y-m-d') . " " . date('H:i:s')
    );
    //Se realiza el insert en la base de datos
    return $basicas->InsertCampo($mysqli, "Eventos", $DatEventos);
}

/********************************************************************************************************************************************
                													Carga de CURP cuando la venta es para el cliente
 ********************************************************************************************************************************************/
function agregarCliente($idContacto, $nombre, $paterno, $materno, $curp, $mail, $mysqli)
{
    //Se crea el array que contiene los datos de registro
    $DatUser = array(
        "IdContact"    => $_SESSION["Cnc"],
        "Usuario"       => 'PLATAFORMA',
        "Tipo"          => "Cliente",
        "Nombre"        => $nombre.' '.$paterno.' '.$materno,
        "ClaveCurp"     => $curp,
        "Email"         => $mail
    );
    //Se realiza el insert en la base de datos
    return $basicas->InsertCampo($mysqli, "Usuario", $DatUser);
}

/********************************************************************************************************************************************
                																Generar pago
 ********************************************************************************************************************************************/
function generarPago($VendeDor, $idContacto, $producto, $costo, $gps, $Meses, $firma, $tarjeta, $TipoServicio, $mysqli)
{
    datosLegal($idContacto, 1, 'S/D', 'S/D', 'S/D', $mysqli);

    //Buscamos los datos y realizamos un registro en la venta
    $Venta = array(
        "Usuario"       => $VendeDor,
        "IdContact"     => $idContacto,
        "Nombre"        => $basicas->BuscarCampos($mysqli, "Nombre", "Usuario", "IdContact", $idContacto),
        "Producto"      => $producto,
        "CostoVenta"    => $costo,
        "Idgps"         => $gps,
        "NumeroPagos"   => $mysqli->real_escape_string($Meses),
        "IdFIrma"       => $mysqli->real_escape_string($seguridad->Firma($mysqli, $idContacto, $costo)),
        "Status"        => "PREVENTA",
        "Mes"           => date("M"),
        "Cupon"         => $tarjeta,
        "TipoServicio"  => $mysqli->real_escape_string($TipoServicio)
    );
    //Insertar los datos en la base
    return $basicas->InsertCampo($mysqli, "Venta", $Venta);
}

/********************************************************************************************************************************************
                																Datos legal
 ********************************************************************************************************************************************/
function datosLegal($idContacto, $Meses = 1, $Terminos = 'S/D', $Aviso = 'S/D', $Fideicomiso = 'S/D', $mysqli)
{
    //Se crea el array que contiene los datos de registro
    $DatLegal = array(
        "IdContacto"    => $idContacto,
        "Meses"         => $mysqli->real_escape_string($Meses),
        "Terminos"      => $mysqli->real_escape_string($Terminos),
        "Aviso"         => $mysqli->real_escape_string($Aviso),
        "Fideicomiso"   => $mysqli->real_escape_string($Fideicomiso)
    );

    //Se realiza el insert en la base de datos
    return $basicas->InsertCampo($mysqli, "Legal", $DatLegal);
}

?>
