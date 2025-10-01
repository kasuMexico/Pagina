<?php 

// DEBUG: Activar todos los errores y mostrar datos importantes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Si no existe la variable de sesión "Vendedor", redirigir a la página de login
if (!isset($_SESSION["Vendedor"])) {
    header('Location: https://kasu.com.mx/login/');
    exit();
}

// Incluir el archivo de librerías que contiene las clases actualizadas
require_once '../../eia/librerias.php';

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');

// Variables de tiempo actuales
$hoy = date('Y-m-d');
$HoraActual = date('H:i:s');

/**************************************** BLOQUE: ACTUALIZAR DATOS DE UN CLIENTE **********************************************/
/************************************** REVISADO 25/09/2025 JOSE CARLOS CABRERA MONROY ****************************************/
if (!empty($_POST['ActDatosCTE'])){
    // Extraer y sanitizar las variables necesarias
    $calle    = isset($_POST['calle'])   ? $mysqli->real_escape_string($_POST['calle'])   : '';
    $Email    = isset($_POST['Email'])    ? $mysqli->real_escape_string($_POST['Email'])    : '';
    $Telefono = isset($_POST['Telefono']) ? $mysqli->real_escape_string($_POST['Telefono']) : '';
    $Host     = isset($_POST['Host'])     ? $mysqli->real_escape_string($_POST['Host'])     : '';
    $Producto = isset($_POST['Producto'])     ? $mysqli->real_escape_string($_POST['Producto'])     : '';
    //Se registran los datos de el finger print, gps y Evento
    $ids = $seguridad->auditoria_registrar(
        $mysqli,                     // conexión principal
        $basicas,                    // tu helper Basicas
        $_POST,                      // datos del form (fingerprint, gps, etc.)
        'Cambio_Contacto',           // nombre del evento
        $_POST['Host'] ?? $_SERVER['PHP_SELF']  // host/origen
    );
    // Insertar datos de Contacto para el empleado
    $NvoRegistroarray = [
        "Usuario"   => $_SESSION["Vendedor"],
        "Host"      => $Host,
        "Mail"      => $Email,
        "Telefono"  => $Telefono,
        "calle"     => $calle,
        "Idgps"     => $ids['gps_id'],
        "Producto"  => $Producto
    ];
    $NvoRegistro = $basicas->InsertCampo($mysqli, "Contacto", $NvoRegistroarray);
    //Actualizamos el ID del Venta 
    $basicas->ActCampo($mysqli, "Venta", "IdContact", $NvoRegistro, $_POST['IdVenta']);
    //Actualizamos el ID del Usuario 
    $basicas->ActCampo($mysqli, "Usuario", "IdContact", $NvoRegistro, $_POST['IdUsuario']);
    //mensaje de alert para usuario
    $Msg = "Se han actualizado los datos de el cliente";
    //Redireccionar a pagina de donde venimos
    header('Location: https://kasu.com.mx' . $_POST['Host'] . '?Vt=1&Msg='.$Msg.'&nombre=' . $_POST['nombre']);
    exit();
}
/********************************* BLOQUE: REGISTRAR UN TICKET DE ATENCION AL CLIENTE *********************************/
/************************************** REVISADO 25/09/2025 JOSE CARLOS CABRERA MONROY ****************************************/

if (!empty($_POST['AltaTicket'])){
    
    // Extraer y sanitizar las variables necesarias
    $Producto       = isset($_POST['Producto'])         ? $mysqli->real_escape_string($_POST['Producto'])   : '';
    $Status         = isset($_POST['Status'])           ? $mysqli->real_escape_string($_POST['Status'])    : '';
    $Prioridad      = isset($_POST['Prioridad'])        ? $mysqli->real_escape_string($_POST['Prioridad']) : '';
    $Descripcion    = isset($_POST['Descripcion'])    ? $mysqli->real_escape_string($_POST['Descripcion']) : '';
    $Telefono       = isset($_POST['Telefono'])         ? $mysqli->real_escape_string($_POST['Telefono'])     : '';

    //************************* Funcion: de Registros de Eventos, GPS y Fingerprint ********************************//
    $ids = $seguridad->auditoria_registrar(
        $mysqli,                     // conexión principal
        $basicas,                    // tu helper Basicas
        $_POST,                      // datos del form (fingerprint, gps, etc.)
        'Ticket_Atencion',           // nombre del evento
        $_POST['Host'] ?? $_SERVER['PHP_SELF']  // host/origen
    );
    //************************* Funcion: de Registros de Eventos, GPS y Fingerprint ********************************//
    // Insertar datos para ticket
    $NvoRegistroarray = [
        "IdVta"         => $_POST["IdVenta"],
        "IdUsr"         => $_POST["IdUsuario"],
        "IdContacto"    => $_POST["IdContact"],
        "Ticket"        => $_POST["Descripcion"],
        "Vendedor"      => $_SESSION["Vendedor"],
        "Host"          => $_POST["Host"],
        "Prioridad"     => $_POST["Prioridad"],
        "Status"        => $_POST["Status"],
        "Telefono"      => $_POST["Telefono"]
    ];
    //Registramos en la base de datos la leyenda
    $NvoRegistro = $basicas->InsertCampo($mysqli, "Atn_Cliente", $NvoRegistroarray);
    //mensaje de alert para usuario
    $Msg = "Se ha registrado correctamente el Ticket";
    //Redireccionar a pagina de donde venimos
    header('Location: https://kasu.com.mx' . $_POST['Host'] . '?Vt=1&Msg='.$Msg.'&nombre=' . $_POST['nombre']);
    exit();
}

/***************************************** BLOQUE: REGISTRAR SERVICIO FUNERARIO ************************************************/
/************************************** REVISADO 25/09/2025 JOSE CARLOS CABRERA MONROY ****************************************/

if (!empty($_POST['RegisFun'])){

    // Extraer y sanitizar las variables necesarias
    $Prestador      = isset($_POST['Prestador'])    ? $mysqli->real_escape_string($_POST['Prestador']) : '';
    $RFC            = isset($_POST['RFC'])          ? $mysqli->real_escape_string($_POST['RFC']) : '';
    $CodigoPostal   = isset($_POST['CodigoPostal']) ? $mysqli->real_escape_string($_POST['CodigoPostal']) : '';
    $Firma          = isset($_POST['Firma'])        ? $mysqli->real_escape_string($_POST['Firma']) : '';
    $Costo          = isset($_POST['Costo'])        ? $mysqli->real_escape_string($_POST['Costo']) : '';
    $EmpFune        = isset($_POST['EmpFune'])      ? $mysqli->real_escape_string($_POST['EmpFune']) : '';

    //************************* Funcion: de Registros de Eventos, GPS y Fingerprint ********************************//
    $ids = $seguridad->auditoria_registrar(
        $mysqli,                     // conexión principal
        $basicas,                    // tu helper Basicas
        $_POST,                      // datos del form (fingerprint, gps, etc.)
        'Servicio_Funerario',           // nombre del evento
        $_POST['Host'] ?? $_SERVER['PHP_SELF']  // host/origen
    );
    //************************* Funcion: de Registros de Eventos, GPS y Fingerprint ********************************//
    //Buscamos el nombre de el cliente
    $NombreCte = $basicas->BuscarCampos($mysqli,'Nombre','Venta','Id',$_POST['IdVenta']);
    // Insertar datos para ticket
    $NvoRegistroarray = [
        "Usuario"       => $_SESSION["Vendedor"],
        "IdVenta"       => $_POST['IdVenta'],
        "Nombre"        => $NombreCte,
        "Prestador"     => $Prestador,
        "CodigoPostal"  => $CodigoPostal,
        "CFDI"          => $Firma,
        "Costo"         => $Costo,
        "EmpFune"       => $EmpFune
    ];
    //Registramos en la base de datos la leyenda
    $NvoRegistro = $basicas->InsertCampo($mysqli, "EntregaServicio", $NvoRegistroarray);
    //Actualizamos el status de la Venta 
    $basicas->ActCampo($mysqli, "Venta", "Status", "FALLECIDO", $_POST['IdVenta']);
    //mensaje de alert para usuario
    $Msg = "Se ha registrado correctamente el SERVICIO";
    //Redireccionar a pagina de donde venimos
    header('Location: https://kasu.com.mx' . $_POST['Host'] . '?Vt=1&Msg='.$Msg.'&nombre=' . $_POST['nombre']);
    exit();
}

/*********************************************** BLOQUE: PAGO DE CLIENTE ******************************************************/
/************************************** REVISADO 25/09/2025 JOSE CARLOS CABRERA MONROY ****************************************/

if (isset($_POST['Pago'])) {
    // ====== Inputs (sanitiza / castea) ======
    $IdVenta   = isset($_POST['IdVenta'])   ? $mysqli->real_escape_string($_POST['IdVenta'])   : '';
    $Metodo    = isset($_POST['Metodo'])    ? $mysqli->real_escape_string($_POST['Metodo'])    : '';
    $status    = isset($_POST['Status'])    ? $mysqli->real_escape_string($_POST['Status'])    : '';
    $PromesPga = isset($_POST['PromesPga'])   ? $mysqli->real_escape_string($_POST['PromesPga'])   : null;
    $Promesa   = isset($_POST['Promesa'])   ? $mysqli->real_escape_string($_POST['Promesa'])   : null;

    $PagoProm  = isset($_POST['PagoProm'])  ? (float)$_POST['PagoProm']  : 0.0;  // cuota sin mora
    $PagoMora  = isset($_POST['PagoMora'])  ? (float)$_POST['PagoMora']  : 0.0;  // cuota con mora
    $Cantidad  = isset($_POST['Cantidad'])  ? (float)$_POST['Cantidad']  : 0.0;  // lo que pagó el cliente

    $hoy        = date('Y-m-d');
    $HoraActual = date('H:i:s');

    // Sanitiza y normaliza el host a SOLO la ruta
    $hostRaw = $_POST['Host'] ?? '';
    $host    = parse_url($hostRaw, PHP_URL_PATH) ?? '';
    $host    = $host ?: '/login/Mesa_Herramientas.php';
    $host    = preg_replace('/[\r\n]/', '', $host); // quita CR/LF
    $nombre = $_POST['nombre'] ?? '';

    // ====== Auditoría (GPS / fingerprint) ======
    $ids = $seguridad->auditoria_registrar(
        $mysqli,
        $basicas,
        $_POST,
        'Pago_Servicio',
        $_POST['Host'] ?? $_SERVER['PHP_SELF']
    );

    // ====== Cálculo de mora a aplicar en este pago ======
    // Mora teórica de la cuota: (cuota con mora) - (cuota normal)
    $moraTeorica = max(0, round($PagoMora - $PagoProm, 2));

    // Si el estatus indica mora, del dinero recibido primero se abona a la mora
    $aplicaMora = 0.00;
    if (strcasecmp($status, 'Mora') === 0 && $moraTeorica > 0) {
        // No puedes aplicar más mora de lo pagado
        $aplicaMora = min($Cantidad, $moraTeorica);
        if ($aplicaMora > 0) {
            $basicas->InsertCampo($mysqli, "Pagos", [
                "IdVenta"  => $IdVenta,
                "Usuario"  => $_SESSION["Vendedor"],
                "Idgps"    => $ids['gps_id'] ?? null,
                "Cantidad" => $aplicaMora,
                "Metodo"   => $Metodo,
                "status"   => "Mora",
                "FechaRegistro" => $hoy." ".$HoraActual
            ]);
        }
    }

    // Resto del pago que va al capital/cuota normal
    $importePago = round($Cantidad - $aplicaMora, 2);
    if ($importePago > 0) {
        $basicas->InsertCampo($mysqli, "Pagos", [
            "IdVenta"       => $IdVenta,
            "Usuario"       => $_SESSION["Vendedor"],
            "Idgps"         => $ids['gps_id'] ?? null,
            "Cantidad"      => $importePago,
            "Metodo"        => $Metodo,
            "status"        => "Pago",
            "FechaRegistro" => $hoy." ".$HoraActual
        ]);
    }

    // ====== (Opcional) Guardar promesa de pago ======
    if (!empty($Promesa)) {
        //Buscamos el vendedor que vendio la poliza
        $Vendedor = $basicas->BuscarCampos($mysqli,'Usuario','Venta','Id',$IdVenta);
        //insertamos los datos en las promesas de pago
        $basicas->InsertCampo($mysqli, "PromesaPago", [
            "IdVenta"       => $IdVenta,
            "Cantidad"      => $PromesPga,
            "Promesa"       => $Promesa,
            "Usuario"       => $_SESSION["Vendedor"],
            "Vendedor"      => $Vendedor,
            "FechaRegistro" => $hoy." ".$HoraActual
        ]);
    }
    //mensaje de alert para usuario
    $Msg = "Pago Registrado correctamente";
    //Redireccionar a pagina de donde venimos
    header('Location: https://kasu.com.mx' . $host. '?Msg='.$Msg.'&nombre=' . rawurlencode($nombre));
    exit();
}

/******************************************** BLOQUE: Registrar Promesa de Pago ***********************************************/
/************************************** REVISADO 25/09/2025 JOSE CARLOS CABRERA MONROY ****************************************/

if (isset($_POST['PromPago'])) {

    // ====== Inputs (sanitiza / castea) ======
    $IdVenta    = isset($_POST['IdVenta'])   ? $mysqli->real_escape_string($_POST['IdVenta'])   : '';
    $Promesa    = isset($_POST['Promesa'])   ? $mysqli->real_escape_string($_POST['Promesa'])   : null; //fecha en que promete pagar el cliente
    $Cantidad   = isset($_POST['Cantidad'])  ? (float)$_POST['Cantidad']  : 0.0;  // lo que promete el cliente pagar
    $PagoMinimo = isset($_POST['PagoMinimo'])  ? (float)$_POST['PagoMinimo']  : 0.0;  // lo que promete el cliente pagar
    $hoy        = date('Y-m-d');
    $HoraActual = date('H:i:s');
    // Se registra la promesa de pago
    if ($PagoMinimo < $Cantidad) {
    // ====== Auditoría (GPS / fingerprint) ======
    $ids = $seguridad->auditoria_registrar(
        $mysqli,
        $basicas,
        $_POST,
        'Promesa_Pago',
        $_POST['Host'] ?? $_SERVER['PHP_SELF']
    );
    //Se busca el Vendedor que realizo la venta
    $Vendedor = $basicas->BuscarCampos($mysqli,'Usuario','Venta','Id',$IdVenta);
    //Insertamos el registro de la promesa
        $basicas->InsertCampo($mysqli, "PromesaPago", [
            "IdVenta"       => $IdVenta,
            "Cantidad"      => $Cantidad,
            "Promesa"       => $Promesa,
            "Vendedor"      => $Vendedor,
            "Usuario"       => $_SESSION["Vendedor"],
            "FechaRegistro" => $hoy." ".$HoraActual
        ]);
        //mensaje de alert para usuario
        $Msg = "Promesa de pago registrada correctamente";
    }else{
        // ====== Auditoría (GPS / fingerprint) ======
        $ids = $seguridad->auditoria_registrar(
            $mysqli,
            $basicas,
            $_POST,
            'Promesa_No_Registrada',
            $_POST['Host'] ?? $_SERVER['PHP_SELF']
        );
        //Mensaje de error de registro de promesa de pago
        $Msg = "No se puede registrar una promesa menor al pago minimo";
    }
    //Redireccionar a pagina de donde venimos
    header('Location: https://kasu.com.mx' . $_POST['Host'] . '?Vt=1&Msg='.$Msg.'&nombre=' . $_POST['nombre']);
    exit();
}