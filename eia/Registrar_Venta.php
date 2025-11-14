<?php
/********************************************************************************************************************************************
 ********** ESTE ARCHIVO REALIZA LOS REGISTROS DE VENTA - 2 de noviembre 2025 - Jose Carlos Cabrera Monroy **************
 ********************************************************************************************************************************************/

session_start();

require_once 'librerias.php';
//Pasos para registrar una venta
//Paso 1.- Limpiamos la informacion de el formulario para evitar sql - Informacion del usuario
$CURP           = isset($_POST['ClaveCurp'])       ? $mysqli->real_escape_string($_POST['ClaveCurp']) : '';
$EmailRaw       = isset($_POST['Mail'])            ? $mysqli->real_escape_string($_POST['Mail']) : '';
$TelRaw         = isset($_POST['Telefono'])        ? $mysqli->real_escape_string($_POST['Telefono']) : '';
$Host           = isset($_POST['Host'])             ? (string)$_POST['Host'] : '';
$Producto       = isset($_POST['Producto'])        ? $mysqli->real_escape_string($_POST['Producto']) : '';
$CP             = isset($_POST['Codigo_Postal'])   ? $mysqli->real_escape_string($_POST['Codigo_Postal']) : '';
$Vendedor       = isset($_POST['IdEmpleado'])      ? $mysqli->real_escape_string($_POST['IdEmpleado']) : 'Plataforma';
$plazo          = isset($_POST['plazo'])           ? $mysqli->real_escape_string($_POST['plazo']) : '';
$TipoServicio   = isset($_POST['TipoServicio'])    ? $mysqli->real_escape_string($_POST['TipoServicio']) : '';
$Referencia_KASU    = isset($_POST['Referencia_KASU'])    ? $mysqli->real_escape_string($_POST['Referencia_KASU']) : '';

// Normalizas los datos legales
$TerminosRaw    = isset($_POST['Terminos'])    ? $mysqli->real_escape_string($_POST['Terminos'])    : '';
$AvisoRaw       = isset($_POST['Aviso'])       ? $mysqli->real_escape_string($_POST['Aviso'])       : '';
$FideicomisoRaw = isset($_POST['Fideicomiso']) ? $mysqli->real_escape_string($_POST['Fideicomiso']) : '';
// Normaliza nombres de geodatos desde distintos clientes
$latitud        = $_POST['latitude']   ?? $_POST['latitud'] ?? $_POST['Lat'] ?? $_POST['lat'] ?? null;
$longitud       = $_POST['longitude']  ?? $_POST['longitud']?? $_POST['Lng'] ?? $_POST['lng'] ?? $_POST['long'] ?? null;
$accuracy       = $_POST['accuracy']   ?? $_POST['Accuracy'] ?? 0;
// Normalizas la CURP del beneficiario
$ClaveCurpBen   = isset($_POST['ClaveCurpBen'])    ? $mysqli->real_escape_string($_POST['ClaveCurpBen'])    : '';
//Normalizamos los valores de los cupones de redes sociales

//Obtenemos el Contacto telefonico y correo pre existentes

$errores = [];

/* === 2) Validación === */
// Email
if ($EmailRaw === '' || !filter_var($EmailRaw, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'Email inválido';
}

// Teléfono MX: 10 dígitos; acepta +52 y separadores
$digits = preg_replace('/\D+/', '', $TelRaw);
if (strlen($digits) === 12 && substr($digits, 0, 2) === '52') {
    $digits = substr($digits, 2);
}
if (strlen($digits) !== 10) {
    $errores[] = 'Teléfono inválido. Requiere 10 dígitos MX';
}

// 3) Normaliza para usar en DB
$Email    = $mysqli->real_escape_string(strtolower($EmailRaw));
$Telefono = $mysqli->real_escape_string($digits); // guarda 10 dígitos

/* === 4) Duplicados con tus funciones === */
// Si BuscarCampos no encuentra, suele devolver null o ''.
// Considera duplicado si regresa un id numérico > 0.
$IdMail     = $basicas->BuscarCampos($mysqli, 'id', 'Contacto', 'Mail', $Email);
$IdTelefono = $basicas->BuscarCampos($mysqli, 'id', 'Contacto', 'Telefono', $Telefono);

//Creamos el nombre de el cliente
if (empty($ClaveCurpBen)) {
    if (!empty($IdMail))     { $errores[] = 'Email ya registrado'; }
    if (!empty($IdTelefono)) { $errores[] = 'Teléfono ya registrado'; }
}

//Paso 2.- Validamos que la CURP sea valida
//Obtenemos el Contacto pre existente
$IdContact = $basicas->BuscarCampos($mysqli,'IdContact','Usuario','ClaveCurp',$CURP);

// 2.1) Obtener el valor original del campo (como string, sin espacios)
$valor = trim((string)$basicas->BuscarCampos($mysqli, 'Producto', 'Venta', 'IdContact', $IdContact));

// 2.2) Partimos de que la categoría es el propio valor
$categoria = $valor;

// 2.3) Si el valor coincide con alguno de los tokens, reclasificamos a "Funerario"
$tokensFunerario = ['02a29','30a49','50a54','55a59','60a64','65a69'];
if (in_array($valor, $tokensFunerario, true)) {
    $categoria = 'Funerario';
}
// 2.4) Validamos que el producto sea el mismo
if (strcasecmp($categoria, (string)$Producto) === 0 && empty($ClaveCurpBen)) { 
    $NombreVenta = (string)$basicas->BuscarCampos($mysqli, 'Nombre', 'Venta', 'IdContact', $IdContact);
    // Cancelamos el registro y retornamos un mensaje
    $Msg = $NombreVenta . " ya se encuentra registrado como cliente";
} elseif($errores) {
    // Manejo de error unificado de telefono o correo electronico // NUMERO 5
    echo json_encode(['ok' => false, 'errors' => $errores], JSON_UNESCAPED_UNICODE);
    // Cancelamos el registro y retornamos un mensaje
    $Msg = $NombreVenta . " El telefono o Email ya se encentra registrado en la base de datos";
} else {
    //Registramos el valor del mensaje exitoso si en el camino de el script se renombra con un error lo imprime
    $Msg = "Venta registrada con exito";
    // Validamos la Clave CURP con la API Rest  
    $datosCurp = $seguridad->peticion_get($CURP);
    // Si la curp es incorrecta retona al inicio y manda un mensaje de error
    if($datosCurp['Response'] == 'correct'){
        $Nombre = $datosCurp['Nombre'];
        $Paterno = $datosCurp['Paterno'];  
        $Materno = $datosCurp['Materno'];
    }
    // Auditoría (GPS + fingerprint)
    $ids = $seguridad->auditoria_registrar(
        $mysqli,
        $basicas,
        $_POST,
        'Registrar_Venta_'.$Vendedor,
        $_POST['Host'] ?? $_SERVER['PHP_SELF']
    );
    //Obtenemos el producto que corresponde al servicio
    $ObtenerEdad = $basicas->ObtenerEdad($CURP);
    //Validamos los productos
    if (strcasecmp($Producto, 'Funerario') === 0) {
        $Produc_Registro = $basicas->ProdFune($ObtenerEdad);
    }elseif($Producto == "Seguridad"){
        $Produc_Registro = $basicas->ProdPli($ObtenerEdad);
    }elseif($Producto == "Transporte"){
        $Produc_Registro = $basicas->ProdTrans($ObtenerEdad);
    }else{
        $Produc_Registro = $Producto;
    }

    // $geo puede venir de reverseGeocodeAddress() o ser null
    $geo = isset($ids['gps_id'])
    ? $seguridad->reverseGeocodeAddress((float)$latitud, (float)$longitud, (string)($CP ?? ''), (float)($accuracy ?? 0))
    : [];

    // Helper: toma el primer $_POST no vacío entre varias claves; si no hay, usa fallback.
    $pick = function(array $keys, $fallback = '') {
    foreach ($keys as $k) {
        if (isset($_POST[$k]) && $_POST[$k] !== '') return trim((string)$_POST[$k]);
    }
    return $fallback;
    };

    // Normaliza datos base
    $telefono = preg_replace('/\D+/', '', (string)($Telefono ?? ''));
    $cpFallback = $CP ?? ($geo['cp_usuario'] ?? '');

    // Construye el array homologado
    $data_contacto = [
    'Usuario'        => $Vendedor,
    'Idgps'          => $ids['gps_id'] ?? null,
    'Host'           => $Host,
    'Mail'           => $Email,
    'Telefono'       => $telefono,

    // Dirección: POST > geo > vacío
    'calle'          => $pick(['Calle','calle'], $geo['calle'] ?? ''),
    'numero'         => $pick(['Numero','numero','nro'], '0'),
    'colonia'        => $pick(['Colonia','colonia'], $geo['colonia'] ?? ''),
    'municipio'      => $pick(['Municipio','municipio'], $geo['municipio'] ?? ''),
    'codigo_postal'  => $pick(['codigo_postal','Codigo_Postal','CodigoPostal','CP','cp'], $cpFallback),
    'estado'         => $pick(['Estado','estado'], $geo['estado'] ?? ''),
    'Referencia'     => $pick(['Referencia','referencia'], ''),

    'Producto'       => $Produc_Registro,
    ];

    // Inserta una sola vez el registro de el contacto
    $Registrar_Contacto = $basicas->InsertCampo($mysqli, 'Contacto', $data_contacto);
    
    //Validamos que el usuario haya aceptado los terminos legales de la venta
    // Función de mapeo robusta
    $toAcepto = static function(string $v): string {
        $v = mb_strtolower(trim($v), 'UTF-8');
        return in_array($v, ['on','1','true','sí','si','acepto','accept','checked'], true) ? 'ACEPTO' : 'NO ACEPTO';
    };

    // Aplica mapeo
    $Terminos    = $toAcepto($TerminosRaw);
    $Aviso       = $toAcepto($AvisoRaw);
    $Fideicomiso = $toAcepto($FideicomisoRaw);

    // Arma el payload
    $data_legal = [
    'IdContacto'  => (int)$Registrar_Contacto,
    'Meses'       => (int)$plazo,
    'Terminos'    => $Terminos,
    'Aviso'       => $Aviso,
    'Fideicomiso' => $Fideicomiso
    ];
    //Insertamos en la base de datos los datos legales
    $Registrar_Legal = $basicas->InsertCampo($mysqli, 'Legal', $data_legal);

    //Paso 4.- Registramos el Usuario
    //Validamos si se compro para el usuario o para un beneficiario
    if (!empty($ClaveCurpBen)) {
        // Validamos la Clave CURP con la API Rest  
        $datosCurpBenef = $seguridad->peticion_get($ClaveCurpBen);
        // Si la curp es incorrecta retona al inicio y manda un mensaje de error
        if($datosCurpBenef['Response'] == 'correct'){
            // Arma el payload
            $data_Usuario_beneficiario = [
                'Usuario'   => $Vendedor,
                'IdContact' => (int)$Registrar_Contacto,
                'Tipo'      => 'Beneficiario',
                'Nombre'    => $datosCurpBenef['Nombre'],
                'Paterno'   => $datosCurpBenef['Materno'],
                'Materno'   => $datosCurpBenef['Paterno'],
                'ClaveCurp' => $ClaveCurpBen
            ];
            //Insertamos en la base de datos los datos legales
            $Registrar_Usuario = $basicas->InsertCampo($mysqli, 'Usuario', $data_Usuario_beneficiario);
        }
    }
    // Arma el payload
    $data_Usuario = [
    'Usuario'   => $Vendedor,
    'IdContact' => (int)$Registrar_Contacto,
    'Tipo'      => 'Cliente',
    'Nombre'    => $Nombre,
    'Paterno'   => $Paterno,
    'Materno'   => $Materno,
    'ClaveCurp' => $datosCurp['Curp'],
    'Email'     => $Email
    ];
    //Insertamos en la base de datos los datos legales
    $Registrar_Usuario = $basicas->InsertCampo($mysqli, 'Usuario', $data_Usuario);

    //Paso 5.- Registramos la venta final siempre con Status PREVENTA
    // Paso 5.1 - llenamos tarjeta con la sesión o null si no existe
    $tarjeta         = $_SESSION['tarjeta'] ?? null;
    $Referencia_KASU = null;
    $Descuento       = 0; // default cuando no hay tarjeta

    // Asegura que $ids exista como arreglo
    $ids = isset($ids) && is_array($ids) ? $ids : [];

    // Validamos que tarjeta esté vacía y lanzamos el if
    if (empty($tarjeta)) {
        $fpId = (int)($ids['fingerprint_id'] ?? 0);
        if ($fpId > 0) {
            // último Id en Eventos por IdFInger y de tipo 'Cupones'
            $IdUltimoEvento = (int)$basicas->Max2Dat(
                $mysqli, 'Id', 'Eventos', 'IdFInger', $fpId, 'Usuario', 'Cupones'
            );
            if ($IdUltimoEvento > 0) {
                // Llenamos las variables si es que existen
                $Referencia_KASU = (string)($basicas->BuscarCampos($mysqli, 'IdUsr', 'Eventos', 'Id', $IdUltimoEvento) ?? '');
                $tarjeta = 0;        // sin descuento
                $Descuento = 0;      // explícito para evitar warnings posteriores
            }
        }
    } else {
        // Buscamos el descuento de la tarjeta en cuestión
        $Descuento = (float)($basicas->BuscarCampos($mysqli, 'Descuento', 'PostSociales', 'Id', $tarjeta) ?? 0);
    }

    // Genera Id único K2 con CURP + FechaRegistro(Usuario) + clave maestra (.env POLIZA_SECRET)
    $curpParaFirma     = !empty($ClaveCurpBen) ? (string)$ClaveCurpBen : (string)($datosCurp['Curp'] ?? $CURP);
    $fechaAltaUsuario  = (string)$basicas->BuscarCampos($mysqli, 'FechaRegistro', 'Usuario', 'Id', $Registrar_Usuario);
    //Obtenemos la clave maestra de el archivo de claves seguras
    $claveMaestra = (string)(getenv('KASU_MASTER_KEY') ?: ($_ENV['KASU_MASTER_KEY'] ?? ''));
    if ($claveMaestra === '') {
    http_response_code(500);
    exit('Config faltante: KASU_MASTER_KEY');
    }
    //Generamos la Firma
    $FirmaUnica = $Seguridad->poliza_id_compacto($curpParaFirma, $fechaAltaUsuario, $claveMaestra);

    //Creamos el nombre de el cliente
    if (!empty($ClaveCurpBen)) {
        //Obtenemos el producto que corresponde al servicio
        $EdadBenef = $basicas->ObtenerEdad($ClaveCurpBen);
        //Obtenemos el producto de el beneficiario
        $Produc_Beneficiarios = $basicas->ProdFune($EdadBenef);
        //Calculamos el valor de el producuto INICIO - REVISION 9 DE Noviembre 2025        
        $CostoVentaOriginal = (float)$basicas->BuscarCampos($mysqli, 'Costo', 'Productos', 'Producto', $Produc_Beneficiarios);
        // No permitir negativos ni sobre-descuento
        $Descuento = max(0.0, (float)$Descuento);
        if ($Descuento > $CostoVentaOriginal) $Descuento = $CostoVentaOriginal;
        // Precio final
        $CostoVenta = round($CostoVentaOriginal - $Descuento, 2);
        //Calculamos el valor de el producuto FINAL - REVISION 9 DE Noviembre 2025  
        // Arma el payload
        $data_venta = [
            'Usuario'           => $Vendedor,
            'IdContact'         => $Registrar_Contacto,
            'Nombre'            => $datosCurpBenef['Nombre']." ".$datosCurpBenef['Materno']." ".$datosCurpBenef['Paterno'],
            'Producto'          => $Produc_Beneficiarios,
            'CostoVenta'        => $CostoVenta,
            'Idgps'             => $ids['gps_id'],
            'Subtotal'          => 0,
            'NumeroPagos'       => $plazo,
            'IdFIrma'           => $FirmaUnica,
            'Status'            => "PREVENTA",
            'Mes'               => date("M"),
            'Cupon'             => $tarjeta,
            'Referencia_KASU'   => $Referencia_KASU,
            'TipoServicio'      => $TipoServicio
        ];
    } else {
        //Calculamos el valor de el producuto
        $CostoVentaOriginal = (float)$basicas->BuscarCampos($mysqli, 'Costo', 'Productos', 'Producto', $Produc_Registro);
        // Precio final
        $CostoVenta = round($CostoVentaOriginal - $Descuento, 2);
        // Arma el payload
        $data_venta = [
            'Usuario'           => $Vendedor,
            'IdContact'         => $Registrar_Contacto,
            'Nombre'            => $datosCurp['Nombre']." ".$datosCurp['Paterno']." ". $datosCurp['Materno'],
            'Producto'          => $Produc_Registro,
            'CostoVenta'        => $CostoVenta,
            'Idgps'             => $ids['gps_id'],
            'Subtotal'          => 0,
            'NumeroPagos'       => $plazo,
            'IdFIrma'           => $FirmaUnica,
            'Status'            => "PREVENTA",
            'Mes'               => date("M"),
            'Cupon'             => $tarjeta,
            'Referencia_KASU'   => $Referencia_KASU,
            'TipoServicio'      => $TipoServicio
        ];
    }
    //Insertamos en la base de datos los datos legales
    $Registrar_Venta = $basicas->InsertCampo($mysqli, 'Venta', $data_venta);
    //Calculamos el valor subtotal si es de contado
    if($plazo != 1){
        //Sacamos el valor de la poliza vendida
        $subtotal_inicial = $financieras->PagoCredito($mysqli, $Registrar_Venta);
        //Actualizamos el sub total de la venta
        $basicas->ActCampo($mysqli,'Venta','Subtotal',$subtotal_inicial,$Registrar_Venta);
    } else {
        //Actualizamos el sub total de la venta
        $basicas->ActCampo($mysqli,'Venta','Subtotal',$CostoVenta,$Registrar_Venta);
    }
    //si el cliente proviene de "/Pwa_Prospectos.php" o "/Mesa_Prospectos.php" de cancela el prospecto
    $path = parse_url($Host, PHP_URL_PATH) ?: '';
    if ($path === '/login/Pwa_Prospectos.php' || $path === '/login/Mesa_Prospectos.php') {
        //Mensaje de cancelacion
        $Msg = "Se ha registrado la venta de el prospecto ".$datosCurp['Nombre']." ".$datosCurp['Paterno']." ". $datosCurp['Materno'];
        //Cambio de Status de Propspecto, 1 => Cancelado, 2 => Venta 
        $basicas->ActCampo($pros,"prospectos","Cancelacion",2,intval($_POST['IdPros']));
    }    
    //Enviar correo de bienvenida al cliente con la liga de pago si es cliente de Registro.php
    
    /* === Mercado Pago: preparar venta según producto y plazo (solo registro.php) === */
    if ($Host === "/registro.php") {
        // Plan según plazo
        $plan       = ((int)$plazo === 1) ? 'CONTADO' : 'MENSUAL';
        // SKU del producto usado en Venta (beneficiario o titular)
        $sku        = !empty($ClaveCurpBen) ? (string)$Produc_Beneficiarios : (string)$Produc_Registro;
        $precioBase = (float)$CostoVenta;

        // Monto a cobrar:
        // - CONTADO: precio completo
        // - MENSUAL: primer pago. Si existe método PagoMensual() se usa; si no, prorrateo del total financiado
        if ($plan === 'CONTADO') {
        $amount = $precioBase;
        } else {
        // total financiado; si no está en variable, toma lo que quedó en Venta.Subtotal
        $totalFinanciado = isset($subtotal_inicial)
            ? (float)$subtotal_inicial
            : (float)$basicas->BuscarCampos($mysqli, 'Subtotal', 'Venta', 'Id', $Registrar_Venta);

        if (method_exists($financieras, 'PagoMensual')) {
            $amount = (float)$financieras->PagoCredito($mysqli, $Registrar_Venta);
        } else {
            $meses  = max(1, (int)$plazo);
            $amount = round($totalFinanciado / $meses, 2);
        }
        }

        // Guarda/actualiza la orden para Mercado Pago en la tabla VentasMercadoPago
        // folio = FirmaUnica; estados de negocio y de pago se inician como PREVENTA/PENDIENTE
        $sql = "
        INSERT INTO VentasMercadoPago
            (folio, producto_sku, plan, plazo_meses, precio_base, amount,
            estatus, estatus_pago, created_at, updated_at)
        VALUES
            (?,?,?,?,?,?,'PREVENTA','PENDIENTE', NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            plan=VALUES(plan),
            plazo_meses=VALUES(plazo_meses),
            precio_base=VALUES(precio_base),
            amount=VALUES(amount),
            updated_at=NOW()
        ";
        if ($stmt = $mysqli->prepare($sql)) {
        $plazoMeses = (int)$plazo;
        $stmt->bind_param('sssidd', $FirmaUnica, $sku, $plan, $plazoMeses, $precioBase, $amount);
        $stmt->execute();
        $stmt->close();
        }
        // Nota: el redirect a /pago/crear_preferencia.php ya existe abajo y usa ref={$FirmaUnica}
    }
    //Validaciones para redirecciones segun pagos o correos
    if($Host == "/registro.php"){
    //Si el cliente se registro por plataforma enviar a liga de pago para cubrir el primer pago
        header("Location: /pago/crear_preferencia.php?ref={$FirmaUnica}");
        exit;
    }elseif($Host == "/Pwa_Clientes.php" || $Host == "/Pwa_Clientes.php"){
    //Si el cliente proviene de PWA donde solo se registra la venta se envia un mensaje de bienvenida
        header('Location: /EnviarCorreo.php?Email=' . $Email . '&IdVenta=' . $Registrar_Venta) . '&IdContact=' . $Registrar_Contacto . '&Vta_Liquidada=' . $Vta_Liquidada;
        exit();
    }
}

header('Location: https://kasu.com.mx' . $Host . '?Msg=' . $Msg);
exit();

