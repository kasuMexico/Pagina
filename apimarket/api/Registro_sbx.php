<?php
declare(strict_types=1);

/**
 * Registro_sbx.php
 * Sandbox legacy para pruebas de API Market V1.
 * NO USAR EN PRODUCCION: este endpoint es solo para entornos sandbox.
 *
 * La Secret_KEY se obtiene desde variable de entorno KASU_SANDBOX_SECRET.
 * Las CURPs de prueba estan anonimizadas.
 */

require_once __DIR__ . '/../librerias_api.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// GUARDIA DE ENTORNO: Abortar si no estamos explícitamente en el sandbox.
// Va después de librerias_api.php para que getenv() lea APIMARKET_ENV del .env.
if (getenv('APIMARKET_ENV') !== 'sandbox') {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode([
        'error'   => 'Forbidden',
        'message' => 'El entorno de pruebas (sandbox) no está habilitado.',
    ], JSON_UNESCAPED_UNICODE));
}

try {
    api_security_headers();
    api_rate_limit('sandbox:' . api_client_ip(), 10, 60);

    $data = api_read_json();

    // Secret KEY desde entorno (sin hardcode)
    $Secret_KEY = getenv('KASU_SANDBOX_SECRET') ?: '';
    if ($Secret_KEY === '') {
        api_error(500, 'Configuracion sandbox no disponible');
    }

    // --- CURPs de prueba ANONIMIZADAS (datos ficticios) ---
    $curpsPrueba = [
        'TECA880526HMCBNR04' => ['Nombre' => 'PRUEBA', 'Paterno' => 'UNO',   'Materno' => 'TEST'],
        'TEPC200504HMCBXRA2' => ['Nombre' => 'PRUEBA', 'Paterno' => 'DOS',   'Materno' => 'TEST'],
        'TEEE060617MMCYLVA4' => ['Nombre' => 'PRUEBA', 'Paterno' => 'TRES',  'Materno' => 'TEST'],
    ];

    $curpInput = api_norm_curp((string)($data['curp_en_uso'] ?? ''));
    if (isset($curpsPrueba[$curpInput])) {
        $ArrayRes = [
            'Response'   => 'correct',
            'StatusCurp' => 'NC',
            'Nombre'     => $curpsPrueba[$curpInput]['Nombre'],
            'Paterno'    => $curpsPrueba[$curpInput]['Paterno'],
            'Materno'    => $curpsPrueba[$curpInput]['Materno'],
        ];
    } else {
        $ArrayRes = [
            'Response'   => 'Error',
            'StatusCurp' => 'BD',
        ];
    }

    $tipo = (string)($data['tipo_peticion'] ?? '');

    // --- token_full ---
    if ($tipo === 'token_full') {
        if ($ArrayRes['Response'] === 'Error' || $ArrayRes['StatusCurp'] === 'BD') {
            api_error(417, 'CURP no valida');
        }

        $firmaUser = trim((string)($data['firma_KEY'] ?? ''));
        $firmaEsperada = hash_hmac('sha256', $curpInput, $Secret_KEY);
        if (!hash_equals($firmaEsperada, $firmaUser)) {
            api_error(401, 'Firma invalida');
        }

        $dbSandbox = api_require_db($mysqli_api ?? null, 'apimarket');
        $userName = trim((string)($data['nombre_de_usuario'] ?? ''));
        $usrOk = api_fetch_one(
            $dbSandbox,
            'SELECT nombre_de_usuario FROM api_usuarios WHERE nombre_de_usuario = ? AND activo = 1 LIMIT 1',
            's',
            [$userName]
        );
        if (!$usrOk) {
            api_error(403, 'Usuario sandbox no autorizado');
        }

        $tokenData = [
            'timestamp' => time(),
            'expires_in' => 600,
        ];
        $tokenJson = json_encode($tokenData, JSON_UNESCAPED_UNICODE);
        $token = hash_hmac('sha256', (string)$tokenJson, $firmaEsperada);

        api_json([
            'ok' => true,
            'token' => $token,
            'nombre' => trim($ArrayRes['Nombre'] . ' ' . $ArrayRes['Paterno'] . ' ' . $ArrayRes['Materno']),
            'token_data' => $tokenData,
        ]);
    }

    // --- product_cost ---
    if ($tipo === 'product_cost') {
        $token = api_bearer_token();
        if ($token === '') {
            api_error(401, 'Falta Authorization: Bearer');
        }
        $valid = api_token_verify($token, $data, $Secret_KEY);
        if ($valid !== true) {
            api_error(401, 'Token invalido o expirado');
        }

        $producto = trim((string)($data['producto'] ?? ''));
        if ($producto === '') {
            api_error(400, 'Falta producto');
        }

        $db = api_require_db($mysqli ?? null, 'ventas');
        $productCode = api_product_code($producto, $curpInput);
        if ($productCode === null) {
            api_error(406, 'Producto no viable');
        }
        $productData = api_product_data($db, $productCode);
        if (!$productData) {
            api_error(406, 'Producto inexistente');
        }

        api_json([
            'ok' => true,
            'costo' => (float)$productData['Costo'],
            'comision' => (float)($productData['comision'] ?? 0),
            'forma_pago' => [
                'meses_max' => (int)($productData['MaxCredito'] ?? 0),
                'tasa_interes' => (float)($productData['TasaAnual'] ?? 0),
            ],
        ]);
    }

    // --- registro_servicio ---
    if ($tipo === 'registro_servicio') {
        $required = ['curp_en_uso', 'mail', 'telefono', 'producto', 'numero_pagos', 'terminos', 'aviso', 'fideicomiso'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $data) || trim((string)$data[$key]) === '') {
                api_error(400, 'Falta dato requerido: ' . $key);
            }
        }

        if (!api_accepts($data['terminos']) || !api_accepts($data['aviso']) || !api_accepts($data['fideicomiso'])) {
            api_error(409, 'Debe aceptar terminos, aviso y fideicomiso');
        }

        $token = api_bearer_token();
        if ($token === '') {
            api_error(401, 'Falta Authorization: Bearer');
        }
        $valid = api_token_verify($token, $data, $Secret_KEY);
        if ($valid !== true) {
            api_error(401, 'Token invalido o expirado');
        }

        $email = strtolower(trim((string)$data['mail']));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            api_error(400, 'Email invalido');
        }
        $telefono = api_norm_phone_mx((string)$data['telefono']);
        if (strlen($telefono) !== 10) {
            api_error(400, 'Telefono invalido');
        }

        $db = api_require_db($mysqli ?? null, 'ventas');
        $producto = trim((string)$data['producto']);
        $productCode = api_product_code($producto, $curpInput);
        if ($productCode === null) {
            api_error(406, 'Producto no viable');
        }
        $productData = api_product_data($db, $productCode);
        if (!$productData) {
            api_error(406, 'Producto inexistente');
        }

        if ($ArrayRes['Response'] !== 'correct' || $ArrayRes['StatusCurp'] === 'BD') {
            api_error(417, 'CURP no valida');
        }

        $plazo = max(1, (int)$data['numero_pagos']);
        $costoVenta = round((float)$productData['Costo'], 2);
        $subtotal = ($plazo > 1) ? api_pago_credito_values($db, $productCode, $plazo, $costoVenta) : $costoVenta;
        $nombreCompleto = trim($ArrayRes['Nombre'] . ' ' . $ArrayRes['Paterno'] . ' ' . $ArrayRes['Materno']);

        $direccion = $data['direccion'] ?? [];
        $pick = static function (array $source, array $keys, string $fallback = ''): string {
            foreach ($keys as $key) {
                if (isset($source[$key]) && trim((string)$source[$key]) !== '') {
                    return trim((string)$source[$key]);
                }
            }
            return $fallback;
        };

        $db->begin_transaction();
        try {
            $idContacto = api_insert($db, 'Contacto', [
                'Usuario' => 'SANDBOX',
                'Idgps' => 0,
                'Host' => 'API_REGISTRO_SBX',
                'Mail' => $email,
                'Telefono' => $telefono,
                'calle' => $pick($direccion, ['calle', 'Calle']),
                'numero' => $pick($direccion, ['numero', 'Numero', 'nro'], '0'),
                'colonia' => $pick($direccion, ['colonia', 'Colonia']),
                'municipio' => $pick($direccion, ['municipio', 'Municipio']),
                'codigo_postal' => $pick($direccion, ['codigo_postal', 'Codigo_Postal', 'CodigoPostal', 'CP', 'cp']),
                'estado' => $pick($direccion, ['estado', 'Estado']),
                'Producto' => $productCode,
            ]);

            api_insert($db, 'Legal', [
                'IdContacto' => $idContacto,
                'Meses' => $plazo,
                'Terminos' => 'ACEPTO',
                'Aviso' => 'ACEPTO',
                'Fideicomiso' => 'ACEPTO',
            ]);

            api_insert($db, 'Usuario', [
                'Usuario' => 'SANDBOX',
                'IdContact' => $idContacto,
                'Tipo' => 'Cliente',
                'Nombre' => $ArrayRes['Nombre'],
                'Paterno' => $ArrayRes['Paterno'],
                'Materno' => $ArrayRes['Materno'],
                'ClaveCurp' => $curpInput,
                'Email' => $email,
            ]);

            $fechaAltaUsuario = api_now();
            $folio = api_poliza_id_compacto($curpInput, $fechaAltaUsuario, api_master_key());

            api_insert($db, 'Venta', [
                'Usuario' => 'SANDBOX',
                'IdContact' => $idContacto,
                'Nombre' => $nombreCompleto,
                'Producto' => $productCode,
                'CostoVenta' => $costoVenta,
                'Idgps' => 0,
                'Subtotal' => $subtotal,
                'NumeroPagos' => $plazo,
                'DiaPago' => 0,
                'IdFIrma' => $folio,
                'Status' => 'PREVENTA',
                'Mes' => date('M'),
                'Cupon' => 0,
                'TipoServicio' => 'Ecologico',
            ]);

            api_log_event($db, $data, 'API_SANDBOX_registro', $idContacto, null);
            $db->commit();
        } catch (Throwable $e) {
            $db->rollback();
            throw $e;
        }

        api_json([
            'ok' => true,
            'mensaje' => 'Registro exitoso del servicio ' . $producto,
            'datos_compra' => [
                'nombre' => $nombreCompleto,
                'CURP' => $curpInput,
                'mail' => $email,
                'poliza' => $folio,
                'Status' => 'PREVENTA',
                'Costo' => $costoVenta,
            ],
        ], 201);
    }

    api_error(404, 'Peticion desconocida');

} catch (mysqli_sql_exception $e) {
    error_log('[API_SANDBOX] ' . $e->getMessage());
    api_error(500, 'Error de base de datos');
} catch (Throwable $e) {
    error_log('[API_SANDBOX] ' . $e->getMessage());
    api_error(500, $e->getMessage());
}
