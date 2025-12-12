<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : ia_correo_auto.php
 * Carpeta : /eia/Vista-360
 * Qué hace: Endpoint JSON para que la IA (o la PWA) envíe correos automáticos
 *           a clientes o prospectos, aplicando reglas por estatus.
 *
 * Entradas típicas (JSON POST):
 *  - Para cliente por venta:
 *      { "modo": "cliente", "id_venta": 123, "tipo": "auto" }
 *      { "modo": "cliente", "id_venta": 123, "tipo": "poliza" }
 *      tipos: auto | poliza | fichas | estado_cuenta | liga_pago
 *
 *  - Para prospecto:
 *      { "modo": "prospecto", "id_prospecto": 45 }
 *
 * Salida: JSON { ok: bool, ... }
 * ============================================================================
 */

header('Content-Type: application/json; charset=utf-8');

try {
    // Sesión
    $sessionFile = __DIR__ . '/../session.php';
    if (is_file($sessionFile)) {
        require_once $sessionFile;
        if (function_exists('kasu_session_start')) {
            kasu_session_start();
        } else {
            if (session_status() === PHP_SESSION_NONE) session_start();
        }
    } else {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    require_once __DIR__ . '/../librerias.php'; // $mysqli, $pros, $basicas...
    require_once __DIR__ . '/ia_tools_correo.php';

    global $mysqli, $pros, $basicas;

    if (!$mysqli) {
        throw new RuntimeException('Conexión $mysqli no disponible.');
    }
    if (!$pros) {
        throw new RuntimeException('Conexión $pros no disponible.');
    }

    // Leer JSON
    $raw = file_get_contents('php://input');
    $data = $raw ? json_decode($raw, true) : [];
    if (!is_array($data)) $data = [];

    $modo         = strtolower(trim((string)($data['modo'] ?? 'cliente')));
    $tipo         = strtolower(trim((string)($data['tipo'] ?? 'auto')));
    $idVenta      = (int)($data['id_venta'] ?? 0);
    $idProspecto  = (int)($data['id_prospecto'] ?? 0);

    if ($modo === 'cliente') {
        if ($idVenta <= 0) {
            throw new InvalidArgumentException('Debes indicar "id_venta" para modo cliente.');
        }

        // Consultar status de la venta
        $sql = "SELECT Status FROM Venta WHERE Id = ? LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar la consulta de Venta.');
        }
        $stmt->bind_param('i', $idVenta);
        $stmt->execute();
        $info = $stmt->get_result()->fetch_assoc() ?: [];
        $stmt->close();

        if (!$info) {
            throw new RuntimeException('Venta no encontrada.');
        }

        $status = strtoupper(trim((string)$info['Status']));

        // Si tipo = auto, decidir según estatus
        if ($tipo === '' || $tipo === 'auto') {
            switch ($status) {
                case 'ACTIVO':
                case 'ACTIVACION':
                    $tipo = 'poliza';
                    break;
                case 'PREVENTA':
                    $tipo = 'liga_pago'; // recordatorio de pago
                    break;
                case 'COBRANZA':
                    $tipo = 'fichas';    // fichas de pago
                    break;
                case 'FALLECIDO':
                case 'CANCELADO':
                    echo json_encode([
                        'ok'    => false,
                        'error' => 'El cliente está en estatus ' . $status . ', no se envían correos automáticos.',
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                default:
                    $tipo = 'liga_pago'; // fallback
                    break;
            }
        }

        // Ejecutar acción concreta
        switch ($tipo) {
            case 'poliza':
                $res = ia_tool_enviar_poliza($idVenta);
                break;
            case 'fichas':
                $res = ia_tool_enviar_fichas_pago($idVenta);
                break;
            case 'estado_cuenta':
            case 'edo_cta':
                $res = ia_tool_enviar_estado_cuenta($idVenta);
                break;
            case 'liga_pago':
                $res = ia_tool_enviar_liga_pago($idVenta);
                break;
            default:
                throw new InvalidArgumentException('Tipo de correo no reconocido para cliente: ' . $tipo);
        }

        $res['status_venta'] = $status;
        $res['tipo_enviado'] = $tipo;

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;

    } elseif ($modo === 'prospecto') {
        if ($idProspecto <= 0) {
            throw new InvalidArgumentException('Debes indicar "id_prospecto" para modo prospecto.');
        }

        // Última cotización (PrespEnviado) del prospecto
        $idPresp = (int)$basicas->Max1Dat($pros, 'Id', 'PrespEnviado', 'IdProspecto', $idProspecto);
        if ($idPresp <= 0) {
            throw new RuntimeException('El prospecto no tiene cotizaciones enviadas.');
        }

        $res = ia_tool_enviar_cotizacion($idPresp);
        $res['id_prospecto']   = $idProspecto;
        $res['id_cotizacion']  = $idPresp;
        $res['tipo_enviado']   = 'cotizacion';

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;

    } else {
        throw new InvalidArgumentException('Modo no válido. Usa "cliente" o "prospecto".');
    }

} catch (Throwable $e) {
    error_log('[IA Correo Auto] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
