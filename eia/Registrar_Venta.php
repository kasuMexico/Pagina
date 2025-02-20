<?php
/********************************************************************************************************************************************
 * ESTE ARCHIVO REALIZA LOS REGISTROS DE VENTA
 ********************************************************************************************************************************************/

session_start();

// Incluir el archivo central que carga las funciones, clases y conexiones necesarias.
// Ajusta la ruta según tu estructura; si ya no existe "Funciones_kasu.php", usa "librerias.php".
require_once 'librerias.php';

// -----------------------------------------------------------------------------
// Registro de datos generales: Fingerprint, Eventos, GPS y Vendedor
// -----------------------------------------------------------------------------

// Determinar el vendedor: si existe IdUsr en sesión, se usa; de lo contrario se busca el primer registro
if (!empty($_SESSION["IdUsr"])) {
    $VendeDor = $_SESSION["IdUsr"];
} else {
    // Se busca el primer usuario que registró el fingerprint con evento "Tarjeta"
    $IdReg = $basicas->Min2Dat($mysqli, "Id", "Eventos", "IdFInger", $fingerprint, "Evento", "Tarjeta");
    $BusFing = $basicas->BuscarCampos($mysqli, "Usuario", "Eventos", "Id", $IdReg);
    $VendeDor = !empty($BusFing) ? $BusFing : "PLATAFORMA";
}

// Se recomienda eliminar el uso de eval() para procesar $_POST.
// En lugar de ello, utiliza directamente las variables filtradas o asignadas previamente.
// Por ejemplo, si esperas que existan variables como $fingerprint, $browser, etc., asegúrate de que se definan.

// -----------------------------------------------------------------------------
// Registro de GPS (si aún no se ha guardado en sesión)
if (empty($_SESSION["gps"])) {
    $DatGps = array(
        "Latitud"   => $mysqli->real_escape_string($_POST['Latitud']),
        "Longitud"  => $mysqli->real_escape_string($_POST['Longitud']),
        "Presicion" => $mysqli->real_escape_string($_POST['Presicion'])
    );
    $_SESSION["gps"] = $basicas->InsertCampo($mysqli, "gps", $DatGps);
}

// -----------------------------------------------------------------------------
// Registro de Fingerprint
$fingerprintExists = $basicas->BuscarCampos($mysqli, "id", "FingerPrint", "fingerprint", $fingerprint);
if (empty($fingerprintExists)) {
    $DatFinger = array(
        "fingerprint"   => $mysqli->real_escape_string($fingerprint),
        "browser"       => $mysqli->real_escape_string($browser),
        "flash"         => $mysqli->real_escape_string($flash),
        "canvas"        => $mysqli->real_escape_string($canvas),
        "connection"    => $mysqli->real_escape_string($connection),
        "cookie"        => $mysqli->real_escape_string($cookie),
        "display"       => $mysqli->real_escape_string($display),
        "fontsmoothing" => $mysqli->real_escape_string($fontsmoothing),
        "fonts"         => $mysqli->real_escape_string($fonts),
        "formfields"    => $mysqli->real_escape_string($formfields),
        "java"          => $mysqli->real_escape_string($java),
        "language"      => $mysqli->real_escape_string($language),
        "silverlight"   => $mysqli->real_escape_string($silverlight),
        "os"            => $mysqli->real_escape_string($os),
        "timezone"      => $mysqli->real_escape_string($timezone),
        "touch"         => $mysqli->real_escape_string($touch),
        "truebrowser"   => $mysqli->real_escape_string($truebrowser),
        "plugins"       => $mysqli->real_escape_string($plugins),
        "useragent"     => $mysqli->real_escape_string($useragent)
    );
    $basicas->InsertCampo($mysqli, "FingerPrint", $DatFinger);
}

// -----------------------------------------------------------------------------
// Registro de DATOS CONTACTO del cliente
if (isset($_POST['Registro'])) {
    $DatContac = array(
        "Usuario"   => $VendeDor,
        "Idgps"     => $_SESSION["gps"],
        "Host"      => $mysqli->real_escape_string($Host),
        "Mail"      => $mysqli->real_escape_string($Mail),
        "Telefono"  => $mysqli->real_escape_string($Telefono),
        "calle"     => $mysqli->real_escape_string($Direccion),
        "Producto"  => $mysqli->real_escape_string($Producto)
    );
    $_SESSION["Cnc"] = $basicas->InsertCampo($mysqli, "Contacto", $DatContac);
    $_SESSION["Mail"] = $Mail;
    
    $DatEventos = array(
        "IdFInger"      => $mysqli->real_escape_string($fingerprint),
        "Contacto"      => $_SESSION["Cnc"],
        "Idgps"         => $_SESSION["gps"],
        "Evento"        => "Registro",
        "Host"          => $mysqli->real_escape_string($Host),
        "MetodGet"      => $mysqli->real_escape_string($formfields),
        "connection"    => $mysqli->real_escape_string($connection),
        "timezone"      => $mysqli->real_escape_string($timezone),
        "touch"         => $mysqli->real_escape_string($touch),
        "Cupon"         => $_SESSION["tarjeta"],
        "FechaRegistro" => date('Y-m-d') . " " . date('H:i:s')
    );
    $basicas->InsertCampo($mysqli, "Eventos", $DatEventos);
    $_SESSION["Producto"] = $Producto;
    header('Location: https://kasu.com.mx/registro.php');
    exit;
}

// -----------------------------------------------------------------------------
// Registro de CURP cuando la venta es para el CLIENTE
if (isset($_POST['BtnRegCurBen'])) {
    $OPsd = $basicas->BuscarCampos($mysqli, "Nombre", "Usuario", "ClaveCurp", $CurClie);
    $ArrayRes = Seguridad::peticion_get($CurClie);
    $_SESSION["NombreCOm"] = $ArrayRes["Nombre"] . " " . $ArrayRes["Paterno"] . " " . $ArrayRes["Materno"];
    
    if (!empty($OPsd)) {
        session_destroy();
        header('Location: https://kasu.com.mx/registro.php?curp=' . $CurClie . '&stat=4&Name=' . $OPsd);
        exit;
    } elseif ($ArrayRes["Response"] == "correct" && $ArrayRes["StatusCurp"] != "BD") {
        $DatUser = array(
            "IdContact"   => $_SESSION["Cnc"],
            "Usuario"     => $VendeDor,
            "Tipo"        => "Cliente",
            "Nombre"      => $ArrayRes["Nombre"],
            "Paterno"     => $ArrayRes["Paterno"],
            "Materno"     => $ArrayRes["Materno"],
            "ClaveCurp"   => $ArrayRes["Curp"],
            "Email"       => $_SESSION["Mail"]
        );
        $basicas->InsertCampo($mysqli, "Usuario", $DatUser);
        if ($_SESSION["Producto"] == "Funerario") {
            $edad = $basicas->ObtenerEdad($CurClie);
            $SubProd = $basicas->ProdFune($edad);
            $_SESSION["Producto"] = $SubProd;
            $_SESSION["Edad"] = $edad;
        }
        $_SESSION["Costo"] = $basicas->BuscarCampos($mysqli, "Costo", "Productos", "Producto", $_SESSION["Producto"]);
        $_SESSION["Tasa"]  = $basicas->BuscarCampos($mysqli, "TasaAnual", "Productos", "Producto", $_SESSION["Producto"]);
        $_SESSION["Ventana"] = "Ventana2";
        header('Location: https://kasu.com.mx/registro.php');
        exit;
    } else {
        session_destroy();
        header('Location: https://kasu.com.mx/registro.php?curp=' . $CurClie . '&stat=5&Name=' . $OPsd);
        exit;
    }
}

// -----------------------------------------------------------------------------
// Registro de CURP cuando la venta es para el BENEFICIARIO
if (isset($_POST['BtnRegCurCli'])) {
    $OPsd = $basicas->BuscarCampos($mysqli, "Nombre", "Usuario", "ClaveCurp", $CurBen);
    $ArrayRes = Seguridad::peticion_get($CurBen);
    $_SESSION["NombreCOm"] = $ArrayRes["Nombre"] . " " . $ArrayRes["Paterno"] . " " . $ArrayRes["Materno"];
    
    if (!empty($OPsd)) {
        session_destroy();
        header('Location: https://kasu.com.mx/registro.php?curp=' . $CurBen . '&stat=4&Name=' . $OPsd);
        exit;
    } elseif ($ArrayRes["Response"] == "correct" && $ArrayRes["StatusCurp"] != "BD") {
        $DatUser = array(
            "IdContact"   => $_SESSION["Cnc"],
            "Usuario"     => $VendeDor,
            "Tipo"        => "Beneficiario",
            "Nombre"      => $ArrayRes["Nombre"],
            "Paterno"     => $ArrayRes["Paterno"],
            "Materno"     => $ArrayRes["Materno"],
            "ClaveCurp"   => $ArrayRes["Curp"],
            "Email"       => $mysqli->real_escape_string($EmaBen)
        );
        $basicas->InsertCampo($mysqli, "Usuario", $DatUser);
        if ($_SESSION["Producto"] == "Funerario") {
            $edad = $basicas->ObtenerEdad($CurBen);
            $SubProd = $basicas->ProdFune($edad);
            $_SESSION["Producto"] = $SubProd;
        }
        $_SESSION["Costo"] = $basicas->BuscarCampos($mysqli, "Costo", "Productos", "Producto", $_SESSION["Producto"]);
        $_SESSION["Tasa"]  = $basicas->BuscarCampos($mysqli, "TasaAnual", "Productos", "Producto", $_SESSION["Producto"]);
        $_SESSION["Ventana"] = "Ventana2";
        header('Location: https://kasu.com.mx/registro.php');
        exit;
    } else {
        session_destroy();
        header('Location: https://kasu.com.mx/registro.php?curp=' . $CurBen . '&stat=5&Name=' . $OPsd);
        exit;
    }
}

// -----------------------------------------------------------------------------
// Registro de MEDIOS DE PAGO de la segunda ventana
if (isset($_POST['BtnMetPago'])) {
    $DatLegal = array(
        "IdContacto"  => $_SESSION["Cnc"],
        "Meses"       => $mysqli->real_escape_string($Meses),
        "Terminos"    => $mysqli->real_escape_string($Terminos),
        "Aviso"       => $mysqli->real_escape_string($Aviso),
        "Fideicomiso" => $mysqli->real_escape_string($Fideicomiso)
    );
    $basicas->InsertCampo($mysqli, "Legal", $DatLegal);
    
    if ($Meses == 0) {
        $Meses = 1;
    }
    $firma = Seguridad::Firma($mysqli, $IdContacto, $Costo);
    
    $Venta = array(
        "Usuario"      => $VendeDor,
        "IdContact"    => $_SESSION["Cnc"],
        "Nombre"       => $basicas->BuscarCampos($mysqli, "Nombre", "Usuario", "IdContact", $_SESSION["Cnc"]),
        "Producto"     => $_SESSION["Producto"],
        "CostoVenta"   => $_SESSION["Costo"],
        "Idgps"        => $_SESSION["gps"],
        "NumeroPagos"  => $mysqli->real_escape_string($Meses),
        "IdFIrma"      => $mysqli->real_escape_string($firma),
        "Status"       => "PREVENTA",
        "Mes"          => date("M"),
        "Cupon"        => $_SESSION["tarjeta"],
        "TipoServicio" => $mysqli->real_escape_string($TipoServicio)
    );
    $_SESSION["Venta"] = $basicas->InsertCampo($mysqli, "Venta", $Venta);
    
    if ($Meses != 1) {
        $pago = Financieras::Pago($mysqli, $_SESSION["Venta"]);
        $mora = Financieras::Mora($pago);
        $Pripg = array(
            "vta"      => $_SESSION["Venta"],
            "fec_pri"  => date('Y-m-d'),
            "pago"     => $pago,
            "mora"     => $mora,
            "FechaReg" => date('Y-m-d'),
            "url"      => "PLATAFORMA"
        );
        $basicas->InsertCampo($mysqli, "PromesaPago", $Pripg);
    } else {
        $DatPago = array(
            "IdVenta"       => $_SESSION["Venta"],
            "Usuario"       => $_SESSION["Vendedor"],
            "Cantidad"      => $_SESSION["Costo"],
            "Metodo"        => "Cobro",
            "status"        => "Normal",
            "FechaRegistro" => date('Y-m-d') . " " . date('H:i:s')
        );
        $basicas->InsertCampo($mysqli, "Pagos", $DatPago);
        $SubTotl = Financieras::SaldoCredito($mysqli, $_SESSION["Venta"]);
        if ($SubTotl <= 0) {
            $basicas->ActCampo($mysqli, "Venta", "Status", "ACTIVACION", $_SESSION["Venta"]);
        }
    }
    
    $basicas->ActCampo($pros, "prospectos", "Cancelacion", 2, $IdProspecto);
    header('Location: https://kasu.com.mx/' . $Host . '?curp=' . $CurClie . '&Ml=7&Name=' . $OPsd);
    exit;
}

// -----------------------------------------------------------------------------
// Registro de venta vía MercadoPago
if (isset($_GET['stat'])) {
    if ($_GET['collection_status'] != "approved") {
        $MaxVta = $basicas->Max1Dat($mysqli, "Id", "Venta", "Status", "PREVENTA");
        header('Location: https://kasu.com.mx/eia/EnviarCorreo.php?MxVta=' . $MaxVta);
        exit;
    } else {
        $Valor = $basicas->BuscarCampos($mysqli, "Valor", "MercadoPago", "Referencia", $_GET['external_reference']);
        $DatPago = array(
            "Referencia"         => $_GET['collection_id'],
            "Usuario"            => $VendeDor,
            "Cantidad"           => $Valor,
            "Metodo"             => $_GET['payment_type'],
            "Dia"                => date("j"),
            "Mes"                => date("M"),
            "Ano"                => date("Y"),
            "status"             => $_GET['collection_status'],
            "merchant_order_id"  => $_GET['merchant_order_id'],
            "external_reference" => $_GET['external_reference']
        );
        $fyn = $basicas->InsertCampo($mysqli, "Pagos", $DatPago);
        if ($_GET['external_reference'] == "T7G9TD8D") {
            header('Location: https://kasu.com.mx/ActualizacionDatos/index.php?stat=' . $_GET['stat'] . '&Dtpg=' . $fyn);
        } else {
            header('Location: https://kasu.com.mx/registro.php?stat=' . $_GET['stat'] . '&Dtpg=' . $fyn);
        }
        exit;
    }
}

// -----------------------------------------------------------------------------
// Registro de actualización de pago (cuando el cliente paga vía MercadoPago)
if (isset($_POST['ActuPago'])) {
    $UsrVta = $basicas->BuscarCampos($mysqli, "IdContact", "Usuario", "ClaveCurp", $CurBen);
    $IdVtaCo = $basicas->BuscarCampos($mysqli, "Id", "Venta", "IdContact", $UsrVta);
    $basicas->ActCampo($mysqli, "Pagos", "IdVenta", $IdVtaCo, $Dtpg);
    header('Location: https://kasu.com.mx/registro.php');
    exit;
}

// -----------------------------------------------------------------------------
// Registro de venta por ejecutivo de atención al cliente
if (isset($_POST['RegistroMesa'])) {
    // Carga de DATOS CONTACTO del cliente
    $DatContac = array(
        "Usuario"       => $VendeDor,
        "Host"          => $mysqli->real_escape_string($Host),
        "Mail"          => $mysqli->real_escape_string($Mail),
        "Telefono"      => $mysqli->real_escape_string($Telefono),
        "calle"         => $mysqli->real_escape_string($calle),
        "numero"        => $mysqli->real_escape_string($numero),
        "colonia"       => $mysqli->real_escape_string($colonia),
        "municipio"     => $mysqli->real_escape_string($municipio),
        "estado"        => $mysqli->real_escape_string($estado),
        "codigo_postal" => $mysqli->real_escape_string($codigo_postal),
        "Producto"      => $mysqli->real_escape_string($Producto)
    );
    $IdContacto = $basicas->InsertCampo($mysqli, "Contacto", $DatContac);
    
    // Registro de CURP para cliente (verificar duplicidad)
    $OPsd = $basicas->BuscarCampos($mysqli, "id", "Usuario", "ClaveCurp", $CurClie);
    $ArrayRes = Seguridad::peticion_get($CurClie);
    $nombreCompleto = $ArrayRes["Nombre"] . " " . $ArrayRes["Paterno"] . " " . $ArrayRes["Materno"];
    
    if (!empty($OPsd)) {
        header('Location: https://kasu.com.mx/registro.php?curp=' . $CurClie . '&stat=4&Name=' . $OPsd);
        exit;
    } elseif ($ArrayRes["Response"] == "correct" && $ArrayRes["StatusCurp"] != "BD") {
        $DatUser = array(
            "IdContact"   => $IdContacto,
            "Usuario"     => $VendeDor,
            "Tipo"        => "Cliente",
            "Nombre"      => $ArrayRes["Nombre"],
            "Paterno"     => $ArrayRes["Paterno"],
            "Materno"     => $ArrayRes["Materno"],
            "ClaveCurp"   => $ArrayRes["Curp"],
            "Email"       => $Mail
        );
        $basicas->InsertCampo($mysqli, "Usuario", $DatUser);
        if ($Producto == "FUNERARIO") {
            $edad = $basicas->ObtenerEdad($CurClie);
            $SubProd = $basicas->ProdFune($edad);
            $_SESSION["Producto"] = $SubProd;
        }
        $_SESSION["Costo"] = $basicas->BuscarCampos($mysqli, "Costo", "Productos", "Producto", $_SESSION["Producto"]);
        $_SESSION["Tasa"]  = $basicas->BuscarCampos($mysqli, "TasaAnual", "Productos", "Producto", $_SESSION["Producto"]);
        $_SESSION["Ventana"] = "Ventana2";
        header('Location: https://kasu.com.mx/registro.php');
        exit;
    } else {
        session_destroy();
        header('Location: https://kasu.com.mx/registro.php?curp=' . $CurClie . '&stat=5&Name=' . $OPsd);
        exit;
    }
}
?>