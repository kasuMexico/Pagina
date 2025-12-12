<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : ia_stats_empleado_mes.php
 * Carpeta : /eia/Vista-360
 * Qué hace: Devuelve estadísticas de ventas/cobranza/prospectos de un empleado
 *           en un mes dado.
 *
 * Entrada JSON POST:
 *  {
 *    "id_usuario": "USUARIO123",  // opcional, default = $_SESSION['Vendedor']
 *    "mes": "2025-12"             // opcional, default = mes actual
 *  }
 *
 * Salida JSON con:
 *  - datos_empleado (nombre, nivel, sucursal, rol)
 *  - rango_fechas (inicio, fin)
 *  - ventas_mes (unidades, importe, por_status)
 *  - pagos_mes (importe_total, importe_mora, importe_pendiente)
 *  - prospectos_mes (total, por_etapa)
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

    require_once __DIR__ . '/../librerias.php'; // $mysqli, $pros, $basicas

    global $mysqli, $pros, $basicas;

    if (!$mysqli) {
        throw new RuntimeException('Conexión $mysqli no disponible.');
    }
    if (!$pros) {
        throw new RuntimeException('Conexión $pros no disponible.');
    }

    // Entrada
    $raw = file_get_contents('php://input');
    $data = $raw ? json_decode($raw, true) : [];
    if (!is_array($data)) $data = [];

    $idUsuario = (string)($data['id_usuario'] ?? ($_SESSION['Vendedor'] ?? ''));
    if ($idUsuario === '') {
        throw new InvalidArgumentException('No hay id_usuario ni sesión de Vendedor.');
    }

    $mes = (string)($data['mes'] ?? date('Y-m'));   // formato YYYY-MM esperado
    if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
        throw new InvalidArgumentException('Formato de "mes" inválido. Usa YYYY-MM.');
    }

    // Rango de fechas del mes
    $dtInicio = new DateTime($mes . '-01');
    $dtFin    = (clone $dtInicio)->modify('last day of this month');
    $inicio   = $dtInicio->format('Y-m-d');
    $fin      = $dtFin->format('Y-m-d');

    // Datos del empleado
    $nombre = (string)$basicas->BuscarCampos($mysqli, 'Nombre',         'Empleados', 'IdUsuario', $idUsuario);
    $nivel  = (int)$basicas->BuscarCampos($mysqli, 'Nivel',            'Empleados', 'IdUsuario', $idUsuario);
    $idSuc  = (int)$basicas->BuscarCampos($mysqli, 'Sucursal',         'Empleados', 'IdUsuario', $idUsuario);
    $suc    = (string)$basicas->BuscarCampos($mysqli, 'nombreSucursal','Sucursal',  'Id',        $idSuc);
    $nomNiv = (string)$basicas->BuscarCampos($mysqli, 'NombreNivel',   'Nivel',     'Id',        $nivel);

    // Rol descriptivo (igual lógica que ya usamos en Vista-360)
    $rolDescripcion = 'Rol no identificado';
    switch ($nivel) {
        case 7: $rolDescripcion = 'Agente Externo (ejecutivo de ventas externo)'; break;
        case 6: $rolDescripcion = 'Ejecutivo de Ventas (interno)'; break;
        case 5: $rolDescripcion = 'Ejecutivo de Cobranza'; break;
        case 4: $rolDescripcion = 'Coordinador (equipo de ventas/cobranza)'; break;
        case 3: $rolDescripcion = 'Gerente de Ruta (sucursal)'; break;
        case 2: $rolDescripcion = 'Mesa de Control (análisis centralizado)'; break;
        case 1: $rolDescripcion = 'Dirección / CEO'; break;
    }

    $vendEsc = $mysqli->real_escape_string($idUsuario);

    /* =================== Ventas del mes =================== */
    $ventasMes = [
        'unidades'    => 0,
        'importe'     => 0.0,
        'por_status'  => [],  // PREVENTA / COBRANZA / ACTIVACION / ACTIVO / CANCELADO / FALLECIDO
    ];

    $sqlVentas = "
        SELECT
          COUNT(*)                          AS n_ventas,
          COALESCE(SUM(CostoVenta), 0)      AS imp_total
        FROM Venta
        WHERE Usuario = ?
          AND FechaRegistro BETWEEN ? AND ?
    ";
    $stmt = $mysqli->prepare($sqlVentas);
    if (!$stmt) throw new RuntimeException('No se pudo preparar consulta de ventas.');
    $stmt->bind_param('sss', $vendEsc, $inicio, $fin);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: [];
    $stmt->close();

    $ventasMes['unidades'] = (int)($row['n_ventas'] ?? 0);
    $ventasMes['importe']  = (float)($row['imp_total'] ?? 0);

    // Ventas por status en el rango
    $sqlVStatus = "
        SELECT Status, COUNT(*) AS total, COALESCE(SUM(CostoVenta), 0) AS importe
        FROM Venta
        WHERE Usuario = ?
          AND FechaRegistro BETWEEN ? AND ?
        GROUP BY Status
    ";
    $stmt = $mysqli->prepare($sqlVStatus);
    if (!$stmt) throw new RuntimeException('No se pudo preparar consulta de ventas por status.');
    $stmt->bind_param('sss', $vendEsc, $inicio, $fin);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $st = (string)$r['Status'];
        $ventasMes['por_status'][$st] = [
            'unidades' => (int)$r['total'],
            'importe'  => (float)$r['importe'],
        ];
    }
    $stmt->close();

    /* =================== Pagos del mes =================== */
    $pagosMes = [
        'importe_total'     => 0.0,
        'importe_mora'      => 0.0,
        'importe_pendiente' => 0.0,
        'num_registros'     => 0,
    ];

    $sqlPagos = "
        SELECT
          COUNT(*) AS n,
          SUM(Cantidad) AS total,
          SUM(CASE WHEN status = 'Mora' THEN Cantidad ELSE 0 END) AS imp_mora,
          SUM(CASE WHEN status <> 'Mora' AND status <> 'APROBADO' THEN Cantidad ELSE 0 END) AS imp_pend
        FROM Pagos
        WHERE Usuario = ?
          AND FechaRegistro BETWEEN ? AND ?
    ";
    $stmt = $mysqli->prepare($sqlPagos);
    if (!$stmt) throw new RuntimeException('No se pudo preparar consulta de pagos.');
    $stmt->bind_param('sss', $vendEsc, $inicio, $fin);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: [];
    $stmt->close();

    $pagosMes['num_registros']     = (int)($row['n'] ?? 0);
    $pagosMes['importe_total']     = (float)($row['total'] ?? 0);
    $pagosMes['importe_mora']      = (float)($row['imp_mora'] ?? 0);
    $pagosMes['importe_pendiente'] = (float)($row['imp_pend'] ?? 0);

    /* =================== Prospectos del mes =================== */
    $prosEsc = $pros->real_escape_string($idUsuario);

    $prospectosMes = [
        'total'     => 0,
        'por_etapa' => [],   // "Prospeccion#N1" => n
    ];

    // Prospectos dados de alta en el rango para este usuario
    $sqlPros = "
        SELECT
          Papeline,
          PosPapeline,
          COUNT(*) AS total
        FROM prospectos
        WHERE Asignado = ?
          AND Alta BETWEEN ? AND ?
        GROUP BY Papeline, PosPapeline
    ";
    $stmt = $pros->prepare($sqlPros);
    if (!$stmt) throw new RuntimeException('No se pudo preparar consulta de prospectos.');
    $stmt->bind_param('sss', $prosEsc, $inicio, $fin);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($r = $res->fetch_assoc()) {
        $prospectosMes['total'] += (int)$r['total'];
        $clave = (string)$r['Papeline'] . '#N' . (int)$r['PosPapeline'];
        $prospectosMes['por_etapa'][$clave] = (int)$r['total'];
    }
    $stmt->close();

    /* =================== Respuesta =================== */
    echo json_encode([
        'ok' => true,
        'datos_empleado' => [
            'id_usuario'   => $idUsuario,
            'nombre'       => $nombre,
            'nivel'        => $nivel,
            'nombre_nivel' => $nomNiv,
            'sucursal'     => $suc,
            'rol'          => $rolDescripcion,
        ],
        'rango_fechas' => [
            'inicio' => $inicio,
            'fin'    => $fin,
        ],
        'ventas_mes'     => $ventasMes,
        'pagos_mes'      => $pagosMes,
        'prospectos_mes' => $prospectosMes,
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    error_log('[IA Stats Empleado Mes] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
