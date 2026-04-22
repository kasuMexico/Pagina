<?php
declare(strict_types=1);

require_once __DIR__ . '/../librerias_api.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function api_payments_sale_or_404(mysqli $db, array $data): array
{
    $curp = api_norm_curp((string)($data['curp_en_uso'] ?? ''));
    $poliza = trim((string)($data['poliza_en_uso'] ?? $data['poliza'] ?? $data['id_firma'] ?? ''));
    if ($curp === '' || $poliza === '') {
        api_error(400, 'Faltan curp_en_uso y poliza_en_uso');
    }
    $venta = api_find_venta_by_curp_poliza($db, $curp, $poliza);
    if (!$venta) {
        api_error(404, 'Venta no encontrada para CURP y poliza');
    }
    return $venta;
}

function api_payments_status_payload(mysqli $db, array $venta): array
{
    $idVenta = (int)$venta['Id'];
    $product = api_product_data($db, (string)$venta['Producto']) ?: [];
    $estado = api_estado_mora_corriente($db, $idVenta);
    $pagoPeriodo = api_pago_periodo($db, $idVenta);
    $saldo = api_saldo_credito($db, $idVenta);
    $pagos = api_sum_pagos($db, $idVenta, false);
    $moraPagada = api_sum_pagos($db, $idVenta, true) - $pagos;

    return [
        'id_venta' => $idVenta,
        'poliza' => (string)$venta['IdFIrma'],
        'curp' => (string)$venta['ClaveCurp'],
        'cliente' => (string)$venta['Nombre'],
        'producto' => (string)$venta['Producto'],
        'tipo_servicio' => (string)($venta['TipoServicio'] ?? ''),
        'status' => (string)$venta['Status'],
        'costo_venta' => round((float)$venta['CostoVenta'], 2),
        'subtotal' => round((float)($venta['Subtotal'] ?? 0), 2),
        'numero_pagos' => (int)$venta['NumeroPagos'],
        'dia_pago' => (int)($venta['DiaPago'] ?? 0),
        'pago_periodo' => round($pagoPeriodo, 2),
        'pago_con_mora' => round($pagoPeriodo * 1.10, 2),
        'pagos_realizados' => round($pagos, 2),
        'mora_pagada' => round(max(0, $moraPagada), 2),
        'saldo' => round($saldo, 2),
        'pagos_pendientes' => api_pagos_pendientes($db, $idVenta),
        'estado_cobranza' => $estado,
        'comision_producto' => round((float)($product['comision'] ?? 0), 2),
        'pago_link' => 'https://kasu.com.mx/pago/crear_preferencia.php?ref=' . rawurlencode((string)$venta['IdFIrma']),
    ];
}

try {
    $db = api_require_db($mysqli ?? null, 'ventas');
    $data = api_read_json();
    $auth = api_validate_bearer_or_exit($db, $data, 'API_PAYMENTS');

    $tipo = (string)($data['tipo_peticion'] ?? '');
    if ($tipo === '') {
        api_error(400, 'Falta tipo_peticion');
    }

    $venta = api_payments_sale_or_404($db, $data);
    api_log_event($db, $data, 'API_PAYMENTS_' . $tipo, (int)$venta['IdContact'], (int)$venta['Id']);

    if ($tipo === 'account_status') {
        api_json([
            'ok' => true,
            'data' => api_payments_status_payload($db, $venta),
        ]);
    }

    if ($tipo === 'pagos_psd2') {
        $cantidad = (float)($data['cantidad'] ?? $data['monto'] ?? 0);
        if ($cantidad <= 0) {
            api_error(400, 'cantidad debe ser mayor a 0');
        }

        $metodo = trim((string)($data['metodo'] ?? 'API_PAYMENTS'));
        $referencia = (int)($data['referencia'] ?? $data['Referencia'] ?? 0);
        $idVenta = (int)$venta['Id'];
        $usuarioApi = (string)$auth['usuario'];
        $paymentIds = [];

        $db->begin_transaction();
        try {
            $pagoProm = api_pago_periodo($db, $idVenta);
            $estado = api_estado_mora_corriente($db, $idVenta);
            $statusCobranza = (($estado['estado'] ?? '') === 'MORA') ? 'Mora' : 'Pago';
            $moraTeorica = ($statusCobranza === 'Mora') ? max(0.0, round(($pagoProm * 1.10) - $pagoProm, 2)) : 0.0;
            $aplicaMora = min($cantidad, $moraTeorica);

            if ($aplicaMora > 0) {
                $row = [
                    'IdVenta' => $idVenta,
                    'Usuario' => $usuarioApi,
                    'Idgps' => 0,
                    'Cantidad' => round($aplicaMora, 2),
                    'Metodo' => $metodo,
                    'status' => 'Mora',
                    'FechaRegistro' => api_now(),
                ];
                if ($referencia > 0) {
                    $row['Referencia'] = $referencia;
                }
                $paymentIds[] = api_insert($db, 'Pagos', $row);
            }

            $importePago = round($cantidad - $aplicaMora, 2);
            if ($importePago > 0) {
                $row = [
                    'IdVenta' => $idVenta,
                    'Usuario' => $usuarioApi,
                    'Idgps' => 0,
                    'Cantidad' => $importePago,
                    'Metodo' => $metodo,
                    'status' => 'Pago',
                    'FechaRegistro' => api_now(),
                ];
                if ($referencia > 0) {
                    $row['Referencia'] = $referencia;
                }
                $paymentIds[] = api_insert($db, 'Pagos', $row);
            }

            if ($referencia > 0 && $importePago > 0) {
                $pagadoActual = (float)(api_value($db, 'SELECT Pagado FROM PromesaPago WHERE Id = ? LIMIT 1', 'i', [$referencia]) ?? 0);
                api_update_by_id($db, 'PromesaPago', ['Pagado' => $pagadoActual + $importePago], $referencia);
            }

            $statusVenta = (string)(api_value($db, 'SELECT Status FROM Venta WHERE Id = ? LIMIT 1', 'i', [$idVenta]) ?? '');
            $saldoPendiente = api_saldo_credito($db, $idVenta);
            if ((int)round($saldoPendiente * 100) <= 0 && in_array($statusVenta, ['COBRANZA', 'PREVENTA'], true)) {
                api_update_by_id($db, 'Venta', ['Status' => 'ACTIVACION'], $idVenta);
            } elseif (in_array($statusVenta, ['CANCELADO', 'PREVENTA'], true)) {
                api_update_by_id($db, 'Venta', ['Status' => 'COBRANZA'], $idVenta);
            }

            $db->commit();
        } catch (Throwable $e) {
            $db->rollback();
            throw $e;
        }

        $ventaActualizada = api_get_venta($db, $idVenta) ?: $venta;
        $ventaActualizada['ClaveCurp'] = $venta['ClaveCurp'];
        $ventaActualizada['Mail'] = $venta['Mail'];
        $ventaActualizada['Telefono'] = $venta['Telefono'];

        api_json([
            'ok' => true,
            'mensaje' => 'Pago registrado correctamente',
            'payment_ids' => $paymentIds,
            'aplicado' => [
                'mora' => round($aplicaMora, 2),
                'pago' => round($importePago, 2),
                'total' => round($cantidad, 2),
            ],
            'data' => api_payments_status_payload($db, $ventaActualizada),
        ], 201);
    }

    api_error(404, 'Peticion desconocida');
} catch (mysqli_sql_exception $e) {
    error_log('[API_PAYMENTS] ' . $e->getMessage());
    api_error(500, 'Error de base de datos');
} catch (Throwable $e) {
    error_log('[API_PAYMENTS] ' . $e->getMessage());
    api_error(500, $e->getMessage());
}
