<?php
/********************************************************************************************************************************************
 * ESTE ARCHIVO REALIZA LOS REGISTROS DE VENTA - 
 * 15 de noviembre 2025 - 
 * Jose Carlos Cabrera Monroy 
 * Archivo: Registrar_Venta.php
 ********************************************************************************************************************************************/

session_start();

// Carga autoload y .env (para claves como KASU_MASTER_KEY)
$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($vendorAutoload)) {
    require_once $vendorAutoload;
    $root = dirname(__DIR__);
    if (is_file($root . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable($root);
        $dotenv->safeLoad();
    }
}

require_once 'librerias.php';
$Seguridad = $seguridad;

// ======================================================================
// Paso 1.- Limpiamos la información del formulario para evitar SQL
// ======================================================================
$CURP             = isset($_POST['ClaveCurp'])       ? $mysqli->real_escape_string($_POST['ClaveCurp'])       : '';
$EmailRaw         = isset($_POST['Mail'])            ? $mysqli->real_escape_string($_POST['Mail'])            : '';
$TelRaw           = isset($_POST['Telefono'])        ? $mysqli->real_escape_string($_POST['Telefono'])        : '';
$Host             = isset($_POST['Host'])            ? (string)$_POST['Host']                                : '';
$Producto         = isset($_POST['Producto'])        ? $mysqli->real_escape_string($_POST['Producto'])        : '';
$CP               = isset($_POST['Codigo_Postal'])   ? $mysqli->real_escape_string($_POST['Codigo_Postal'])   : '';
$Vendedor         = isset($_POST['IdEmpleado'])      ? $mysqli->real_escape_string($_POST['IdEmpleado'])      : 'Plataforma';
$plazo            = isset($_POST['plazo'])           ? $mysqli->real_escape_string($_POST['plazo'])           : '';
$TipoServicio     = isset($_POST['TipoServicio'])    ? $mysqli->real_escape_string($_POST['TipoServicio'])    : '';
$Referencia_KASU  = isset($_POST['Referencia_KASU']) ? $mysqli->real_escape_string($_POST['Referencia_KASU']) : '';
// NUEVO: día de pago (1 o 15) para créditos
$DiaPagoRaw       = isset($_POST['DiaPago'])         ? (int)$_POST['DiaPago']                                 : 0;

// Normalizas los datos legales
$TerminosRaw    = isset($_POST['Terminos'])    ? $mysqli->real_escape_string($_POST['Terminos'])    : '';
$AvisoRaw       = isset($_POST['Aviso'])       ? $mysqli->real_escape_string($_POST['Aviso'])       : '';
$FideicomisoRaw = isset($_POST['Fideicomiso']) ? $mysqli->real_escape_string($_POST['Fideicomiso']) : '';

// Normaliza nombres de geodatos desde distintos clientes
$latitud   = $_POST['latitude']   ?? $_POST['latitud'] ?? $_POST['Lat'] ?? $_POST['lat'] ?? null;
$longitud  = $_POST['longitude']  ?? $_POST['longitud']?? $_POST['Lng'] ?? $_POST['lng'] ?? $_POST['long'] ?? null;
$accuracy  = $_POST['accuracy']   ?? $_POST['Accuracy'] ?? 0;

// Normalizas la CURP del beneficiario
$ClaveCurpBen = isset($_POST['ClaveCurpBen']) ? $mysqli->real_escape_string($_POST['ClaveCurpBen']) : '';

// ======================================================================
// Normalización de datos básicos
// ======================================================================
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

/* === 3.1) Normaliza plazo y día de pago === */
$plazoInt = (int)$plazo;
if ($plazoInt <= 0) {
    $plazoInt = 1;
}
$plazo = (string)$plazoInt;

// Día de pago solo aplica si es crédito (plazo > 1)
// Aceptamos 1 o 15; si viene otro valor, forzamos 1. Para contado lo dejamos en 0 (sin uso)
if ($plazoInt > 1) {
    if ($DiaPagoRaw === 1 || $DiaPagoRaw === 15) {
        $DiaPago = $DiaPagoRaw;
    } else {
        $DiaPago = 1;
    }
} else {
    $DiaPago = 0;
}

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

// ======================================================================
// Paso 2.- Validamos que la CURP sea válida / unicidad de producto
// ======================================================================
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
} elseif ($errores) {
    // Manejo de error unificado de teléfono o correo electrónico
    echo json_encode(['ok' => false, 'errors' => $errores], JSON_UNESCAPED_UNICODE);
    // Cancelamos el registro y retornamos un mensaje genérico
    $Msg = "El teléfono o Email ya se encuentra registrado en la base de datos";
} else {
    // ==================================================================
    // A partir de aquí la venta es válida
    // ==================================================================
    $Msg = "Venta registrada con éxito";

    // Validamos la Clave CURP con la API Rest  
    $datosCurp = $seguridad->peticion_get($CURP);
    // Si la curp es correcta, llenamos nombre
    if ($datosCurp['Response'] == 'correct') {
        $Nombre  = $datosCurp['Nombre'];
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
    } elseif ($Producto == "Seguridad") {
        $Produc_Registro = $basicas->ProdPli($ObtenerEdad);
    } elseif ($Producto == "Transporte") {
        $Produc_Registro = $basicas->ProdTrans($ObtenerEdad);
    } else {
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
    $telefono   = preg_replace('/\D+/', '', (string)($Telefono ?? ''));
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
    
    // ==================================================================
    // Paso 3.- Datos legales (términos, aviso, fideicomiso)
    // ==================================================================
    $toAcepto = static function(string $v): string {
        $v = mb_strtolower(trim($v), 'UTF-8');
        return in_array($v, ['on','1','true','sí','si','acepto','accept','checked'], true) ? 'ACEPTO' : 'NO ACEPTO';
    };

    $Terminos    = $toAcepto($TerminosRaw);
    $Aviso       = $toAcepto($AvisoRaw);
    $Fideicomiso = $toAcepto($FideicomisoRaw);

    $data_legal = [
        'IdContacto'  => (int)$Registrar_Contacto,
        'Meses'       => (int)$plazoInt,
        'Terminos'    => $Terminos,
        'Aviso'       => $Aviso,
        'Fideicomiso' => $Fideicomiso
    ];
    $Registrar_Legal = $basicas->InsertCampo($mysqli, 'Legal', $data_legal);

    // ==================================================================
    // Paso 4.- Registramos el Usuario / Beneficiario
    // ==================================================================
    if (!empty($ClaveCurpBen)) {
        // Validamos la CURP del beneficiario con la API Rest  
        $datosCurpBenef = $seguridad->peticion_get($ClaveCurpBen);
        if ($datosCurpBenef['Response'] == 'correct') {
            $data_Usuario_beneficiario = [
                'Usuario'   => $Vendedor,
                'IdContact' => (int)$Registrar_Contacto,
                'Tipo'      => 'Beneficiario',
                'Nombre'    => $datosCurpBenef['Nombre'],
                'Paterno'   => $datosCurpBenef['Materno'],
                'Materno'   => $datosCurpBenef['Paterno'],
                'ClaveCurp' => $ClaveCurpBen
            ];
            $Registrar_Usuario = $basicas->InsertCampo($mysqli, 'Usuario', $data_Usuario_beneficiario);
        }
    }

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
    $Registrar_Usuario = $basicas->InsertCampo($mysqli, 'Usuario', $data_Usuario);

    // ==================================================================
    // Paso 5.- Registramos la venta final siempre con Status PREVENTA
    // ==================================================================
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
                $tarjeta   = 0;        // sin descuento
                $Descuento = 0;        // explícito
            }
        }
    } else {
        // Buscamos el descuento de la tarjeta en cuestión
        $Descuento = (float)($basicas->BuscarCampos($mysqli, 'Descuento', 'PostSociales', 'Id', $tarjeta) ?? 0);
    }

    // ==================================================================
    // Id único de la póliza
    // ==================================================================
    $curpParaFirma     = !empty($ClaveCurpBen) ? (string)$ClaveCurpBen : (string)($datosCurp['Curp'] ?? $CURP);
    $fechaAltaUsuario  = (string)$basicas->BuscarCampos($mysqli, 'FechaRegistro', 'Usuario', 'Id', $Registrar_Usuario);

    $claveMaestra = (string)(getenv('KASU_MASTER_KEY') ?: ($_ENV['KASU_MASTER_KEY'] ?? ''));
    if ($claveMaestra === '') {
        http_response_code(500);
        exit('Config faltante: KASU_MASTER_KEY');
    }

    $FirmaUnica = $seguridad->poliza_id_compacto($curpParaFirma, $fechaAltaUsuario, $claveMaestra);

    // ==================================================================
    // Cálculo de CostoVenta y armado de Venta
    // ==================================================================
    if (!empty($ClaveCurpBen)) {
        // Producto del beneficiario
        $EdadBenef = $basicas->ObtenerEdad($ClaveCurpBen);
        $Produc_Beneficiarios = $basicas->ProdFune($EdadBenef);

        $CostoVentaOriginal = (float)$basicas->BuscarCampos($mysqli, 'Costo', 'Productos', 'Producto', $Produc_Beneficiarios);
        // No permitir negativos ni sobre-descuento
        $Descuento = max(0.0, (float)$Descuento);
        if ($Descuento > $CostoVentaOriginal) $Descuento = $CostoVentaOriginal;
        $CostoVenta = round($CostoVentaOriginal - $Descuento, 2);

        $data_venta = [
            'Usuario'           => $Vendedor,
            'IdContact'         => $Registrar_Contacto,
            'Nombre'            => $datosCurpBenef['Nombre']." ".$datosCurpBenef['Materno']." ".$datosCurpBenef['Paterno'],
            'Producto'          => $Produc_Beneficiarios,
            'CostoVenta'        => $CostoVenta,
            'Idgps'             => $ids['gps_id'],
            'Subtotal'          => 0,
            'NumeroPagos'       => $plazoInt,
            'DiaPago'           => $DiaPago, // NUEVO: guardar día de pago en Venta
            'IdFIrma'           => $FirmaUnica,
            'Status'            => "PREVENTA",
            'Mes'               => date("M"),
            'Cupon'             => $tarjeta,
            'Referencia_KASU'   => $Referencia_KASU,
            'TipoServicio'      => $TipoServicio
        ];
    } else {
        // Producto titular
        $CostoVentaOriginal = (float)$basicas->BuscarCampos($mysqli, 'Costo', 'Productos', 'Producto', $Produc_Registro);
        $Descuento = max(0.0, (float)$Descuento);
        if ($Descuento > $CostoVentaOriginal) $Descuento = $CostoVentaOriginal;
        $CostoVenta = round($CostoVentaOriginal - $Descuento, 2);

        $data_venta = [
            'Usuario'           => $Vendedor,
            'IdContact'         => $Registrar_Contacto,
            'Nombre'            => $datosCurp['Nombre']." ".$datosCurp['Paterno']." ". $datosCurp['Materno'],
            'Producto'          => $Produc_Registro,
            'CostoVenta'        => $CostoVenta,
            'Idgps'             => $ids['gps_id'],
            'Subtotal'          => 0,
            'NumeroPagos'       => $plazoInt,
            'DiaPago'           => $DiaPago, // NUEVO
            'IdFIrma'           => $FirmaUnica,
            'Status'            => "PREVENTA",
            'Mes'               => date("M"),
            'Cupon'             => $tarjeta,
            'Referencia_KASU'   => $Referencia_KASU,
            'TipoServicio'      => $TipoServicio
        ];
    }

    $Registrar_Venta = $basicas->InsertCampo($mysqli, 'Venta', $data_venta);

    // ==================================================================
    // Subtotal según si es contado o crédito
    // ==================================================================
    if ($plazoInt != 1) {
        // Crédito: calculamos el valor financiado usando Financieras
        $subtotal_inicial = $financieras->PagoCredito($mysqli, $Registrar_Venta);
        $basicas->ActCampo($mysqli,'Venta','Subtotal',$subtotal_inicial,$Registrar_Venta);
    } else {
        // Contado
        $basicas->ActCampo($mysqli,'Venta','Subtotal',$CostoVenta,$Registrar_Venta);
    }

    // ==================================================================
    // Cancelar prospecto si proviene de prospectos
    // ==================================================================
    $path = parse_url($Host, PHP_URL_PATH) ?: '';
    if ($path === '/login/Pwa_Prospectos.php' || $path === '/login/Mesa_Prospectos.php') {
        $Msg = "Se ha registrado la venta de el prospecto ".$datosCurp['Nombre']." ".$datosCurp['Paterno']." ". $datosCurp['Materno'];
        //Cambio de Status de Prospecto, 1 => Cancelado, 2 => Venta 
        $basicas->ActCampo($pros,"prospectos","Cancelacion",2,intval($_POST['IdPros'] ?? 0));
    }    

    // ==================================================================
    // Mercado Pago: preparar venta según producto y plazo (solo registro.php)
    // ==================================================================
    if ($Host === "/registro.php") {
        // Plan según plazo
        $plan       = ($plazoInt === 1) ? 'CONTADO' : 'MENSUAL';
        // SKU del producto usado en Venta (beneficiario o titular)
        $sku        = !empty($ClaveCurpBen) ? (string)$Produc_Beneficiarios : (string)$Produc_Registro;
        $precioBase = (float)$CostoVenta;

        // Monto a cobrar (primer pago):
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
                $meses  = max(1, (int)$plazoInt);
                $amount = round($totalFinanciado / $meses, 2);
            }
        }

        // Guarda/actualiza la orden para Mercado Pago en la tabla VentasMercadoPago
        // folio = FirmaUnica; estados de negocio y de pago se inician como PREVENTA/PENDIENTE
        $sql = "
            INSERT INTO VentasMercadoPago
                (folio, plan, plazo_meses, dia_pago, precio_base, amount, estatus, estatus_pago, created_at, updated_at)
            VALUES
                (?, ?, ?, ?, ?, ?, 'PREVENTA', 'PENDIENTE', NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                plan        = VALUES(plan),
                plazo_meses = VALUES(plazo_meses),
                dia_pago    = VALUES(dia_pago),
                precio_base = VALUES(precio_base),
                amount      = VALUES(amount),
                updated_at  = NOW()
        ";

        if ($stmt = $mysqli->prepare($sql)) {
            $plazoMeses = (int)$plazoInt;
            // Si tu columna dia_pago NO acepta NULL, usa 0 en lugar de null
            $diaPagoDb  = ($plan === 'MENSUAL') ? (int)$DiaPago : 0;

            // s = folio, s = plan, i = plazo_meses, i = dia_pago, d = precio_base, d = amount
            $stmt->bind_param('ssiidd', $FirmaUnica, $plan, $plazoMeses, $diaPagoDb, $precioBase, $amount);
            $stmt->execute();
            $stmt->close();
        }
        // Nota: el redirect a /pago/crear_preferencia.php ya existe abajo y usa ref={$FirmaUnica}
    }

    // ==================================================================
    // Redirecciones según origen
    // ==================================================================
    if ($Host == "/registro.php") {
        // Cliente de la página pública: se manda a la liga de pago
        header("Location: /pago/crear_preferencia.php?ref={$FirmaUnica}");
        exit;
    } elseif ($Host == "/Pwa_Clientes.php" || $Host == "/Pwa_Clientes.php") {
        // Cliente proviene de PWA: solo correo de bienvenida
        header('Location: /EnviarCorreo.php?Email=' . urlencode($Email) . '&IdVenta=' . (int)$Registrar_Venta . '&IdContact=' . (int)$Registrar_Contacto);
        exit();
    }
}

// Fallback general
header('Location: https://kasu.com.mx' . $Host . '?Msg=' . urlencode($Msg ?? ''));
exit();
