<?php
declare(strict_types=1);

require_once __DIR__ . '/../librerias_api.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $db = api_require_db($mysqli ?? null, 'ventas');
    $data = api_read_json();
    $auth = api_validate_bearer_or_exit($db, $data, 'API_ACCOUNTS');

    if (($data['tipo_peticion'] ?? '') !== 'new_service') {
        api_error(404, 'Peticion desconocida');
    }

    $required = ['curp_en_uso', 'mail', 'telefono', 'producto', 'numero_pagos', 'terminos', 'aviso', 'fideicomiso', 'nombre_de_usuario'];
    foreach ($required as $key) {
        if (!array_key_exists($key, $data) || trim((string)$data[$key]) === '') {
            api_error(400, 'Falta dato requerido: ' . $key);
        }
    }

    if (!api_accepts($data['terminos']) || !api_accepts($data['aviso']) || !api_accepts($data['fideicomiso'])) {
        api_error(409, 'El cliente debe aceptar terminos, aviso y fideicomiso');
    }

    $curp = api_norm_curp((string)$data['curp_en_uso']);
    if (strlen($curp) !== 18) {
        api_error(400, 'CURP invalida');
    }

    $email = strtolower(trim((string)$data['mail']));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        api_error(400, 'Email invalido');
    }

    $telefono = api_norm_phone_mx((string)$data['telefono']);
    if (strlen($telefono) !== 10) {
        api_error(400, 'Telefono invalido. Requiere 10 digitos MX');
    }

    $plazo = max(1, (int)$data['numero_pagos']);
    $diaPagoRaw = (int)($data['dia_pago'] ?? $data['DiaPago'] ?? 0);
    $diaPago = ($plazo > 1) ? (($diaPagoRaw === 1 || $diaPagoRaw === 15) ? $diaPagoRaw : 1) : 0;

    $direccion = $data['direccion'] ?? [];
    if (!is_array($direccion)) {
        api_error(400, 'direccion debe ser un objeto JSON');
    }
    $pick = static function (array $source, array $keys, string $fallback = ''): string {
        foreach ($keys as $key) {
            if (isset($source[$key]) && trim((string)$source[$key]) !== '') {
                return trim((string)$source[$key]);
            }
        }
        return $fallback;
    };

    $productoSolicitado = trim((string)$data['producto']);
    $producto = api_product_code($productoSolicitado, $curp);
    if ($producto === null || $producto === '') {
        api_error(406, 'Producto no viable para la edad del cliente');
    }
    $productoData = api_product_data($db, $producto);
    if (!$productoData) {
        api_error(406, 'Producto inexistente o no habilitado');
    }

    $idContactExistente = (int)(api_value($db, 'SELECT IdContact FROM Usuario WHERE ClaveCurp = ? LIMIT 1', 's', [$curp]) ?? 0);
    if ($idContactExistente > 0) {
        $ventasExistentes = api_fetch_all($db, 'SELECT Producto FROM Venta WHERE IdContact = ?', 'i', [$idContactExistente]);
        $funeralTokens = ['02a29', '30a49', '50a54', '55a59', '60a64', '65a69'];
        foreach ($ventasExistentes as $ventaExistente) {
            $productoExistente = (string)($ventaExistente['Producto'] ?? '');
            $mismoFunerario = strcasecmp($productoSolicitado, 'Funerario') === 0 && in_array($productoExistente, $funeralTokens, true);
            if ($productoExistente === $producto || $mismoFunerario) {
                api_error(412, 'Cliente ya registrado con el producto seleccionado');
            }
        }
    }

    if (api_value($db, 'SELECT id FROM Contacto WHERE Mail = ? LIMIT 1', 's', [$email])) {
        api_error(409, 'Email ya registrado');
    }
    if (api_value($db, 'SELECT id FROM Contacto WHERE Telefono = ? LIMIT 1', 's', [$telefono])) {
        api_error(409, 'Telefono ya registrado');
    }

    $datosCurp = $seguridad->peticion_get($curp);
    if (!is_array($datosCurp)
        || strcasecmp((string)($datosCurp['Response'] ?? ''), 'correct') !== 0
        || strtoupper((string)($datosCurp['StatusCurp'] ?? '')) === 'BD') {
        api_error(417, 'CURP no valida o no elegible');
    }

    $db->begin_transaction();
    try {
        $usuarioApi = (string)$auth['usuario'];
        $idContacto = api_insert($db, 'Contacto', [
            'Usuario' => $usuarioApi,
            'Idgps' => 0,
            'Host' => 'API_ACCOUNTS_V1',
            'Mail' => $email,
            'Telefono' => $telefono,
            'calle' => $pick($direccion, ['calle', 'Calle']),
            'numero' => $pick($direccion, ['numero', 'Numero', 'nro'], '0'),
            'colonia' => $pick($direccion, ['colonia', 'Colonia']),
            'municipio' => $pick($direccion, ['municipio', 'Municipio']),
            'codigo_postal' => $pick($direccion, ['codigo_postal', 'Codigo_Postal', 'CodigoPostal', 'CP', 'cp']),
            'estado' => $pick($direccion, ['estado', 'Estado']),
            'Referencia' => trim((string)($data['referencia'] ?? $data['Referencia'] ?? '')),
            'Producto' => $producto,
        ]);

        api_insert($db, 'Legal', [
            'IdContacto' => $idContacto,
            'Meses' => $plazo,
            'Terminos' => 'ACEPTO',
            'Aviso' => 'ACEPTO',
            'Fideicomiso' => 'ACEPTO',
        ]);

        $nombre = trim((string)($datosCurp['Nombre'] ?? ''));
        $paterno = trim((string)($datosCurp['Paterno'] ?? ''));
        $materno = trim((string)($datosCurp['Materno'] ?? ''));
        $idUsuario = api_insert($db, 'Usuario', [
            'Usuario' => $usuarioApi,
            'IdContact' => $idContacto,
            'Tipo' => 'Cliente',
            'Nombre' => $nombre,
            'Paterno' => $paterno,
            'Materno' => $materno,
            'ClaveCurp' => (string)($datosCurp['Curp'] ?? $curp),
            'Email' => $email,
        ]);

        $fechaAltaUsuario = (string)(api_value($db, 'SELECT FechaRegistro FROM Usuario WHERE Id = ? LIMIT 1', 'i', [$idUsuario]) ?? api_now());
        $folio = api_poliza_id_compacto($curp, $fechaAltaUsuario, api_master_key());

        $costoVenta = round((float)$productoData['Costo'], 2);
        $subtotal = ($plazo > 1) ? api_pago_credito_values($db, $producto, $plazo, $costoVenta) : $costoVenta;
        $amount = ($plazo > 1) ? round($subtotal / $plazo, 2) : $costoVenta;
        $plan = ($plazo === 1) ? 'CONTADO' : 'MENSUAL';
        $nombreCompleto = trim($nombre . ' ' . $paterno . ' ' . $materno);

        $idVenta = api_insert($db, 'Venta', [
            'Usuario' => $usuarioApi,
            'IdContact' => $idContacto,
            'Nombre' => $nombreCompleto,
            'Producto' => $producto,
            'CostoVenta' => $costoVenta,
            'Idgps' => 0,
            'Subtotal' => $subtotal,
            'NumeroPagos' => $plazo,
            'DiaPago' => $diaPago,
            'IdFIrma' => $folio,
            'Status' => 'PREVENTA',
            'Mes' => date('M'),
            'Cupon' => 0,
            'Referencia_KASU' => trim((string)($data['referencia_kasu'] ?? $data['Referencia_KASU'] ?? '')),
            'TipoServicio' => trim((string)($data['tipo_servicio'] ?? $data['TipoServicio'] ?? 'Ecologico')),
        ]);

        $sqlMp = "
            INSERT INTO VentasMercadoPago
                (folio, plan, plazo_meses, dia_pago, precio_base, amount, estatus, estatus_pago, created_at, updated_at)
            VALUES
                (?, ?, ?, ?, ?, ?, 'PREVENTA', 'PENDIENTE', NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                plan = VALUES(plan),
                plazo_meses = VALUES(plazo_meses),
                dia_pago = VALUES(dia_pago),
                precio_base = VALUES(precio_base),
                amount = VALUES(amount),
                updated_at = NOW()
        ";
        $stmtMp = $db->prepare($sqlMp);
        $diaPagoDb = ($plan === 'MENSUAL') ? $diaPago : 0;
        api_bind_execute($stmtMp, 'ssiidd', [$folio, $plan, $plazo, $diaPagoDb, $costoVenta, $amount]);
        $stmtMp->close();

        api_log_event($db, $data, 'API_ACCOUNTS_new_service', $idContacto, $idVenta);
        $db->commit();
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }

    api_json([
        'ok' => true,
        'mensaje' => 'Registro exitoso del servicio ' . $productoSolicitado,
        'datos_compra' => [
            'id_venta' => $idVenta,
            'id_contacto' => $idContacto,
            'nombre' => $nombreCompleto,
            'CURP' => $curp,
            'mail' => $email,
            'producto' => $producto,
            'poliza' => $folio,
            'status' => 'PREVENTA',
            'costo' => $costoVenta,
            'subtotal' => $subtotal,
            'amount' => $amount,
            'plan' => $plan,
            'numero_pagos' => $plazo,
            'dia_pago' => $diaPago,
            'pago_link' => 'https://kasu.com.mx/pago/crear_preferencia.php?ref=' . rawurlencode($folio),
        ],
    ], 201);
} catch (mysqli_sql_exception $e) {
    error_log('[API_ACCOUNTS] ' . $e->getMessage());
    api_error(500, 'Error de base de datos');
} catch (Throwable $e) {
    error_log('[API_ACCOUNTS] ' . $e->getMessage());
    api_error(500, $e->getMessage());
}
