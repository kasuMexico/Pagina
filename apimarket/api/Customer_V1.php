<?php
declare(strict_types=1);

require_once __DIR__ . '/../librerias_api.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function api_customer_contact_id(mysqli $db, string $curp): int
{
    return (int)(api_value($db, 'SELECT IdContact FROM Usuario WHERE ClaveCurp = ? LIMIT 1', 's', [api_norm_curp($curp)]) ?? 0);
}

function api_customer_require_authorized(mysqli $db, string $curp): void
{
    $auth = api_value($db, 'SELECT Clave FROM Autorizacion WHERE ClaveCurp = ? LIMIT 1', 's', [api_norm_curp($curp)]);
    if (strcasecmp((string)$auth, 'No') === 0) {
        api_error(409, 'El cliente no autorizo la consulta de sus datos');
    }
}

try {
    $db = api_require_db($mysqli ?? null, 'ventas');
    $data = api_read_json();
    api_validate_bearer_or_exit($db, $data, 'API_CUSTOMER');

    $tipo = (string)($data['tipo_peticion'] ?? '');
    $request = (string)($data['request'] ?? '');
    $curp = api_norm_curp((string)($data['curp_en_uso'] ?? ''));

    if ($tipo === '' || $request === '' || $curp === '') {
        api_error(400, 'Faltan tipo_peticion, request o curp_en_uso');
    }

    $idContact = api_customer_contact_id($db, $curp);
    api_log_event($db, $data, 'API_CUSTOMER_' . $tipo, $idContact ?: null, null);

    if ($tipo === 'request' && $request === 'request_block') {
        api_json([
            'ok' => true,
            'request_block' => [
                'cliente' => 'Consulta los datos generales de un cliente',
                'catalogo_productos' => 'Consulta todos los productos existentes',
                'producto_cliente' => 'Consulta los datos viables para un cliente; requiere producto',
            ],
        ], 202);
    }

    if ($tipo === 'request' && $request === 'individual_request') {
        api_json([
            'ok' => true,
            'Datos_Contacto' => [
                'Mail' => 'Correo electronico',
                'Telefono' => 'Telefono registrado',
                'calle' => 'Calle registrada',
                'numero' => 'Numero registrado',
                'colonia' => 'Colonia registrada',
                'municipio' => 'Municipio registrado',
                'codigo_postal' => 'Codigo postal registrado',
                'estado' => 'Estado de residencia',
                'Producto' => 'Producto principal del contacto',
            ],
            'Datos_usuario' => [
                'Usuario' => 'Id de ejecutivo que vendio',
                'Tipo' => 'Cliente o Beneficiario',
                'Nombre' => 'Nombre registrado',
                'Paterno' => 'Apellido paterno',
                'Materno' => 'Apellido materno',
            ],
            'Datos_ventas' => [
                'Producto' => 'Producto vendido',
                'CostoVenta' => 'Precio de venta',
                'NumeroPagos' => 'Numero de pagos',
                'IdFIrma' => 'Numero de poliza',
                'Status' => 'Status del servicio',
                'FechaRegistro' => 'Fecha de registro',
            ],
        ], 202);
    }

    if ($tipo === 'individual_request') {
        api_customer_require_authorized($db, $curp);
        if ($idContact <= 0) {
            api_error(404, 'Cliente no encontrado');
        }

        $contactFields = ['Mail', 'Telefono', 'calle', 'numero', 'colonia', 'municipio', 'codigo_postal', 'estado', 'Producto'];
        $userFields = ['Usuario', 'Tipo', 'Nombre', 'Paterno', 'Materno'];
        $saleFields = ['Producto', 'CostoVenta', 'NumeroPagos', 'IdFIrma', 'Status', 'FechaRegistro'];

        if (in_array($request, $contactFields, true)) {
            $value = api_value($db, 'SELECT `' . $request . '` FROM Contacto WHERE id = ? LIMIT 1', 'i', [$idContact]);
        } elseif (in_array($request, $userFields, true)) {
            $value = api_value($db, 'SELECT `' . $request . '` FROM Usuario WHERE ClaveCurp = ? LIMIT 1', 's', [$curp]);
        } elseif (in_array($request, $saleFields, true)) {
            $rows = api_fetch_all($db, 'SELECT `' . $request . '` AS valor, Id AS id_venta, Producto AS producto, IdFIrma AS poliza FROM Venta WHERE IdContact = ? ORDER BY Id DESC', 'i', [$idContact]);
            $value = count($rows) === 1 ? ($rows[0]['valor'] ?? null) : $rows;
        } else {
            api_error(412, 'Clave individual no consultable');
        }

        api_json([
            'ok' => true,
            'curp_en_uso' => $curp,
            'request' => $request,
            'data' => $value,
        ], 202);
    }

    if ($tipo === 'request_block' && $request === 'cliente') {
        api_customer_require_authorized($db, $curp);
        if ($idContact <= 0) {
            api_error(404, 'Cliente no encontrado');
        }

        $usuario = api_fetch_one($db, 'SELECT * FROM Usuario WHERE IdContact = ? ORDER BY Id DESC LIMIT 1', 'i', [$idContact]);
        $contacto = api_fetch_one($db, 'SELECT * FROM Contacto WHERE id = ? LIMIT 1', 'i', [$idContact]);
        $ventas = api_fetch_all(
            $db,
            'SELECT Id, Producto, CostoVenta, NumeroPagos, DiaPago, IdFIrma, Status, TipoServicio, FechaRegistro FROM Venta WHERE IdContact = ? ORDER BY Id DESC',
            'i',
            [$idContact]
        );

        api_json([
            'ok' => true,
            'curp_en_uso' => $curp,
            'Ejecutivo' => $contacto['Usuario'] ?? null,
            'Nombre' => trim((string)($usuario['Nombre'] ?? '') . ' ' . (string)($usuario['Paterno'] ?? '') . ' ' . (string)($usuario['Materno'] ?? '')),
            'Tipo' => $usuario['Tipo'] ?? null,
            'Mail' => $contacto['Mail'] ?? null,
            'Telefono' => $contacto['Telefono'] ?? null,
            'Producto' => $contacto['Producto'] ?? null,
            'FechaRegistro' => $usuario['FechaRegistro'] ?? null,
            'Direccion' => [
                'calle' => $contacto['calle'] ?? '',
                'numero' => $contacto['numero'] ?? '',
                'colonia' => $contacto['colonia'] ?? '',
                'municipio' => $contacto['municipio'] ?? '',
                'codigo_postal' => $contacto['codigo_postal'] ?? '',
                'estado' => $contacto['estado'] ?? '',
            ],
            'ventas' => $ventas,
        ], 202);
    }

    if ($tipo === 'request_block' && $request === 'producto_cliente') {
        $productoSolicitado = trim((string)($data['producto'] ?? ''));
        if ($productoSolicitado === '') {
            api_error(400, 'Falta producto');
        }
        $producto = api_product_code($productoSolicitado, $curp);
        if ($producto === null) {
            api_error(406, 'Producto no viable para la edad del cliente');
        }
        $productoData = api_product_data($db, $producto);
        if (!$productoData) {
            api_error(406, 'Producto inexistente o no habilitado');
        }

        api_json([
            'ok' => true,
            'producto' => $producto,
            'costo' => (float)$productoData['Costo'],
            'comision' => (float)($productoData['comision'] ?? 0),
            'forma_pago' => [
                'meses_max' => (int)($productoData['MaxCredito'] ?? 0),
                'tasa_anual' => (float)($productoData['TasaAnual'] ?? 0),
            ],
        ], 202);
    }

    if ($tipo === 'request_block' && $request === 'catalogo_productos') {
        $productos = api_fetch_all(
            $db,
            'SELECT Producto, Costo, comision, Fideicomiso, MaxCredito, TasaAnual FROM Productos ORDER BY Id ASC'
        );
        $out = [];
        foreach ($productos as $producto) {
            $out[] = [
                'producto' => $producto['Producto'],
                'costo' => (float)$producto['Costo'],
                'comision' => (float)($producto['comision'] ?? 0),
                'fideicomiso' => $producto['Fideicomiso'] ?? null,
                'forma_pago' => [
                    'meses_max' => (int)($producto['MaxCredito'] ?? 0),
                    'tasa_anual' => (float)($producto['TasaAnual'] ?? 0),
                ],
            ];
        }
        api_json(['ok' => true, 'productos' => $out], 202);
    }

    api_error(404, 'Peticion desconocida');
} catch (mysqli_sql_exception $e) {
    error_log('[API_CUSTOMER] ' . $e->getMessage());
    api_error(500, 'Error de base de datos');
} catch (Throwable $e) {
    error_log('[API_CUSTOMER] ' . $e->getMessage());
    api_error(500, $e->getMessage());
}
