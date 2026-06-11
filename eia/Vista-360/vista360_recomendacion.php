<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : vista360_recomendacion.php
 * Carpeta : /eia/Vista-360
 * Qué hace: Endpoint AJAX que genera una recomendación corta de IA para
 *           el dashboard PWA (Vista 360) con base en datos REALES:
 *           ventas, prospectos, pagos y riesgo de cartera del vendedor.
 * Modelo   : GPT-5.1 vía /v1/responses
 * Fecha    : 2025-12-06
 * Revisado : JCCM + IA
 * ============================================================================
 */

header('Content-Type: application/json; charset=utf-8');

$lockName = '';
$lockAcquired = false;

try {
    // -----------------------------------------------------------------------
    // Sesión KASU
    // -----------------------------------------------------------------------
    $sessionFile = dirname(__DIR__) . '/session.php';
    if (is_file($sessionFile)) {
        require_once $sessionFile;
        if (function_exists('kasu_session_start')) {
            kasu_session_start();
        } else {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        }
    } else {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    if (empty($_SESSION['Vendedor'])) {
        throw new RuntimeException('Sesión no válida: falta Vendedor.');
    }
    $idUsuario = (string)$_SESSION['Vendedor'];

    // -----------------------------------------------------------------------
    // Conexiones y funciones globales
    // -----------------------------------------------------------------------
    // $mysqli  -> BD ventas (u557645733_web)
    // $pros    -> BD prospectos (u557645733_prospectos)
    // $basicas -> helpers genéricos
    require_once dirname(__DIR__) . '/librerias.php';
    require_once __DIR__ . '/ia_role_profiles.php';

    // Funciones financieras (usa $basicas, $mysqli, etc.)
    require_once dirname(__DIR__) . '/Funciones/Funciones_Financieras.php';
    global $financieras, $basicas, $mysqli, $pros;

    if (!isset($financieras) || !($financieras instanceof Financieras)) {
        $financieras = new Financieras();
    }

    if (!$mysqli) {
        throw new RuntimeException('Conexión $mysqli no disponible.');
    }
    if (!$pros) {
        throw new RuntimeException('Conexión $pros no disponible.');
    }

    // Configuración OpenAI
    require_once __DIR__ . '/openai_config.php';

    // -----------------------------------------------------------------------
    // Entrada opcional desde el front (nota extra)
    // -----------------------------------------------------------------------
    $raw  = file_get_contents('php://input');
    $body = [];
    if ($raw !== false && $raw !== '') {
        $tmp = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
            $body = $tmp;
        }
    }
    $notaExtra = isset($body['contexto']) ? (string)$body['contexto'] : '';

    // -----------------------------------------------------------------------
    // Datos básicos del usuario / nivel
    // -----------------------------------------------------------------------
    $nombreVendedor = (string)$basicas->BuscarCampos(
        $mysqli, 'Nombre', 'Empleados', 'IdUsuario', $idUsuario
    );
    $nivel          = (int)$basicas->BuscarCampos(
        $mysqli, 'Nivel', 'Empleados', 'IdUsuario', $idUsuario
    );
    $idSucursal     = (int)$basicas->BuscarCampos(
        $mysqli, 'Sucursal', 'Empleados', 'IdUsuario', $idUsuario
    );
    $nombreSucursal = (string)$basicas->BuscarCampos(
        $mysqli, 'nombreSucursal', 'Sucursal', 'Id', $idSucursal
    );
    $nombreNivel    = (string)$basicas->BuscarCampos(
        $mysqli, 'NombreNivel', 'Nivel', 'Id', $nivel
    );

    $perfilIa = kasu_ia_role_profile($nombreNivel, $nivel);
    $rolDescripcion = (string)$perfilIa['descripcion'];
    $fechaRecomendacion = date('Y-m-d');
    $puestoIa = (string)$perfilIa['titulo'];
    $alcanceIa = (string)$perfilIa['alcance'];

    // Caché persistente estricto: una recomendación por usuario y fecha calendario.
    $stmtDaily = $mysqli->prepare(
        'SELECT Recomendacion, FechaCreacion, Fuente
         FROM IA_Recomendacion_Diaria
         WHERE IdUsuario = ? AND Fecha = ?
         LIMIT 1'
    );
    $stmtDaily->bind_param('ss', $idUsuario, $fechaRecomendacion);
    $stmtDaily->execute();
    $dailyRow = $stmtDaily->get_result()->fetch_assoc();
    $stmtDaily->close();
    if (!empty($dailyRow['Recomendacion'])) {
        echo json_encode([
            'ok' => true,
            'html' => (string)$dailyRow['Recomendacion'],
            'cached' => true,
            'cache_scope' => 'daily_database',
            'generated_at' => (string)($dailyRow['FechaCreacion'] ?? ''),
            'source' => (string)($dailyRow['Fuente'] ?? 'openai'),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $scope = kasu_ia_employee_scope($mysqli, $idUsuario, $perfilIa);
    $usuariosSql = kasu_ia_sql_string_list($mysqli, $scope['user_ids']);
    $empleadosSql = kasu_ia_sql_int_list($scope['employee_ids']);
    $instruccionRolIa = kasu_ia_role_instruction($perfilIa);

    // -----------------------------------------------------------------------
    // RESUMEN DE VENTAS (BD: u557645733_web, tabla Venta)
    // -----------------------------------------------------------------------
    $resumenVentasMes = [
        'unidades' => 0,
        'importe'  => 0.0,
    ];
    $ventasPorStatus = [];
    $ventasMora      = [
        'total_analizadas' => 0,
        'en_mora'          => 0,
        'importe_mora'     => 0.0,
    ];

    // Ventas del mes actual (solo tiene sentido para niveles con ventas propias,
    // pero la consulta no hace daño si regresa 0 para otros niveles)
    $sqlMes = "
        SELECT COUNT(*) AS ventas_mes,
               COALESCE(SUM(CostoVenta), 0) AS importe_mes
        FROM Venta
        WHERE Usuario IN ($usuariosSql)
          AND FechaRegistro >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ";
    if ($res = $mysqli->query($sqlMes)) {
        if ($row = $res->fetch_assoc()) {
            $resumenVentasMes['unidades'] = (int)($row['ventas_mes'] ?? 0);
            $resumenVentasMes['importe']  = (float)($row['importe_mes'] ?? 0);
        }
        $res->close();
    }

    // Ventas por estatus
    $sqlStatus = "
        SELECT Status, COUNT(*) AS total,
               COALESCE(SUM(CostoVenta), 0) AS importe
        FROM Venta
        WHERE Usuario IN ($usuariosSql)
        GROUP BY Status
    ";
    if ($res = $mysqli->query($sqlStatus)) {
        while ($row = $res->fetch_assoc()) {
            $status = (string)($row['Status'] ?? 'SIN_STATUS');
            $ventasPorStatus[$status] = [
                'unidades' => (int)$row['total'],
                'importe'  => (float)$row['importe'],
            ];
        }
        $res->close();
    }

    // Riesgo de cartera usando Financieras::estado_mora_corriente
    $sqlMora = "
        SELECT Id, Status
        FROM Venta
        WHERE Usuario IN ($usuariosSql)
          AND Status IN ('COBRANZA','ACTIVACION','ACTIVO')
        ORDER BY FechaRegistro DESC
        LIMIT 30
    ";
    if ($res = $mysqli->query($sqlMora)) {
        while ($row = $res->fetch_assoc()) {
            $idVenta = (int)$row['Id'];
            $ventasMora['total_analizadas']++;

            $estado = $financieras->estado_mora_corriente($idVenta);
            if (!($estado['ok'] ?? false)) {
                continue;
            }
            if (($estado['estado'] ?? '') === 'MORA') {
                $ventasMora['en_mora']++;
                $ventasMora['importe_mora'] += (float)($estado['pendiente_importe'] ?? 0.0);
            }
        }
        $res->close();
    }
    $ventasMora['importe_mora'] = round($ventasMora['importe_mora'], 2);

    // -----------------------------------------------------------------------
    // RESUMEN DE PAGOS (tabla Pagos)
    // -----------------------------------------------------------------------
    $resumenPagos = [
        'total_registros'   => 0,
        'importe_pendiente' => 0.0,
        'importe_mora'      => 0.0,
    ];

    $sqlPagos = "
        SELECT
          COUNT(*) AS total_reg,
          SUM(CASE WHEN status = 'Mora'
                   THEN Cantidad ELSE 0 END) AS imp_mora,
          SUM(CASE WHEN status <> 'Mora' AND status <> 'APROBADO'
                   THEN Cantidad ELSE 0 END) AS imp_pend
        FROM Pagos
        WHERE Usuario IN ($usuariosSql)
    ";
    if ($res = $mysqli->query($sqlPagos)) {
        if ($row = $res->fetch_assoc()) {
            $resumenPagos['total_registros']   = (int)($row['total_reg'] ?? 0);
            $resumenPagos['importe_pendiente'] = (float)($row['imp_pend'] ?? 0.0);
            $resumenPagos['importe_mora']      = (float)($row['imp_mora'] ?? 0.0);
        }
        $res->close();
    }

    // -----------------------------------------------------------------------
    // RESUMEN DE PROSPECTOS (BD: u557645733_prospectos)
    // -----------------------------------------------------------------------
    $prospectosResumen = [
        'total'        => 0,
        'por_etapa'    => [],  // ej. [ "Prospeccion#N1" => 3, ... ]
        'cerrables'    => [],  // lista corta de prospectos listos para cierre
        'sin_contacto' => 0,
    ];

    // Totales y etapas (via Papeline)
    $sqlProsEtapas = "
        SELECT p.Papeline,
               p.PosPapeline,
               COUNT(*) AS total
        FROM prospectos p
        WHERE p.Asignado IN ($empleadosSql)
        GROUP BY p.Papeline, p.PosPapeline
        ORDER BY p.Papeline, p.PosPapeline
    ";
    if ($res = $pros->query($sqlProsEtapas)) {
        while ($row = $res->fetch_assoc()) {
            $prospectosResumen['total'] += (int)$row['total'];
        }
        $res->data_seek(0);
        while ($row = $res->fetch_assoc()) {
            $pipeline = (string)($row['Papeline'] ?? '');
            $pos      = (int)$row['PosPapeline'];
            $clave    = $pipeline . '#N' . $pos;
            $prospectosResumen['por_etapa'][$clave] = (int)$row['total'];
        }
        $res->close();
    }

    // Prospectos listos para cierre (nivel máximo del pipeline Prospeccion)
    $cerrables = [];
    $sqlCerrables = "
        SELECT p.Id, p.FullName, p.NoTel, p.Email, p.Servicio_Interes,
               p.Papeline, p.PosPapeline,
               pa.Nombre   AS etapa,
               pa.Maximo   AS nivel_maximo,
               pa.Nivel    AS nivel_actual
        FROM prospectos p
        LEFT JOIN Papeline pa
          ON pa.Pipeline = 'Prospeccion'
         AND pa.Nivel = p.PosPapeline
        WHERE p.Asignado IN ($empleadosSql)
        ORDER BY pa.Nivel DESC, p.Alta DESC
        LIMIT 5
    ";
    if ($res = $pros->query($sqlCerrables)) {
        while ($row = $res->fetch_assoc()) {
            $cerrables[] = [
                'id'        => (int)$row['Id'],
                'nombre'    => (string)$row['FullName'],
                'telefono'  => (string)($row['NoTel'] ?? ''),
                'email'     => (string)($row['Email'] ?? ''),
                'servicio'  => (string)($row['Servicio_Interes'] ?? ''),
                'etapa'     => (string)($row['etapa'] ?? ''),
                'nivel'     => (int)($row['nivel_actual'] ?? 0),
                'nivel_max' => (int)($row['nivel_maximo'] ?? 0),
            ];
        }
        $res->close();
    }
    $prospectosResumen['cerrables'] = $cerrables;

    // Prospectos sin contacto (ejemplo: PosPapeline = 0 o Papeline vacío)
    $sqlSinContacto = "
        SELECT COUNT(*) AS total
        FROM prospectos
        WHERE Asignado IN ($empleadosSql)
          AND (Papeline = '' OR PosPapeline = 0)
    ";
    if ($res = $pros->query($sqlSinContacto)) {
        if ($row = $res->fetch_assoc()) {
            $prospectosResumen['sin_contacto'] = (int)$row['total'];
        }
        $res->close();
    }

    // Contexto mínimo de decisión: evita enviar datos personales y estructura redundante.
    $contexto = [
        'puesto' => $puestoIa,
        'alcance' => $alcanceIa,
        'sucursal' => $nombreSucursal,
        'periodo' => date('Y-m'),
        'ventas_mes' => $resumenVentasMes,
        'ventas_por_status' => $ventasPorStatus,
        'pagos' => $resumenPagos,
        'riesgo_cartera' => $ventasMora,
        'prospectos' => [
            'total' => $prospectosResumen['total'],
            'por_etapa' => $prospectosResumen['por_etapa'],
            'sin_contacto' => $prospectosResumen['sin_contacto'],
            'cercanos_cierre' => array_map(
                static fn(array $p): array => [
                    'etapa' => $p['etapa'] ?? '',
                    'servicio' => $p['servicio'] ?? '',
                ],
                array_slice($prospectosResumen['cerrables'], 0, 3)
            ),
        ],
        'nota' => mb_substr($notaExtra, 0, 180, 'UTF-8'),
    ];

    $contextJson = json_encode($contexto, JSON_UNESCAPED_UNICODE);

    if ($contextJson === false) {
        throw new RuntimeException('No se pudo codificar JSON de contexto para IA.');
    }

    $contextHash = hash('sha256', 'role-aware-v3|' . $instruccionRolIa . '|' . $contextJson);

    // Evita dos llamadas simultáneas si el navegador dispara solicitudes paralelas.
    $lockName = 'kasu_ia_daily_' . hash('sha256', $idUsuario . '|' . $fechaRecomendacion);
    $stmtLock = $mysqli->prepare('SELECT GET_LOCK(?, 8) AS acquired');
    $stmtLock->bind_param('s', $lockName);
    $stmtLock->execute();
    $lockAcquired = (int)($stmtLock->get_result()->fetch_assoc()['acquired'] ?? 0) === 1;
    $stmtLock->close();
    if (!$lockAcquired) {
        throw new RuntimeException('La recomendación diaria se está generando. Intenta nuevamente en unos segundos.');
    }

    // Otro proceso pudo terminar mientras esta solicitud esperaba el bloqueo.
    $stmtDaily = $mysqli->prepare(
        'SELECT Recomendacion, FechaCreacion, Fuente
         FROM IA_Recomendacion_Diaria
         WHERE IdUsuario = ? AND Fecha = ?
         LIMIT 1'
    );
    $stmtDaily->bind_param('ss', $idUsuario, $fechaRecomendacion);
    $stmtDaily->execute();
    $dailyRow = $stmtDaily->get_result()->fetch_assoc();
    $stmtDaily->close();
    if (!empty($dailyRow['Recomendacion'])) {
        $stmtRelease = $mysqli->prepare('SELECT RELEASE_LOCK(?)');
        $stmtRelease->bind_param('s', $lockName);
        $stmtRelease->execute();
        $stmtRelease->close();
        echo json_encode([
            'ok' => true,
            'html' => (string)$dailyRow['Recomendacion'],
            'cached' => true,
            'cache_scope' => 'daily_database',
            'generated_at' => (string)($dailyRow['FechaCreacion'] ?? ''),
            'source' => (string)($dailyRow['Fuente'] ?? 'openai'),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // -----------------------------------------------------------------------
    // Prompt para GPT-5.1 (ahora considerando el rol/nivel)
    // -----------------------------------------------------------------------
    $prompt = <<<PROMPT
Eres asesor de gestión de KASU.
PUESTO: {$instruccionRolIa}

Genera la mejor recomendación diaria usando solo las métricas proporcionadas.
Prioriza el mayor impacto: una decisión o riesgo principal y 2-3 acciones propias del puesto.
No asignes tareas de otro nivel ni critiques ventas personales si no corresponden al rol.

Salida: solo HTML <p><ul><li><strong>, español, máximo 75 palabras. Sé específico y accionable.
MÉTRICAS: {$contextJson}
PROMPT;

    // -----------------------------------------------------------------------
    // Llamada a OpenAI
    // -----------------------------------------------------------------------
    $fuenteRecomendacion = 'openai';
    $errorRecomendacion = null;
    try {
        $texto = openai_simple_text($prompt, 260);
        $allowed = '<p><ul><ol><li><strong><b><em><i><br>';
        $html = trim(strip_tags($texto, $allowed));
        if ($html === '') {
            throw new RuntimeException('La respuesta de IA llegó vacía.');
        }
    } catch (Throwable $aiError) {
        $fuenteRecomendacion = 'local_fallback';
        $errorRecomendacion = mb_substr($aiError->getMessage(), 0, 500, 'UTF-8');
        $accionesFallback = array_slice((array)($perfilIa['acciones'] ?? []), 0, 3);
        $html = '<p><strong>Prioridad diaria para ' . htmlspecialchars($puestoIa, ENT_QUOTES, 'UTF-8') . '</strong></p><ul>';
        foreach ($accionesFallback as $accionFallback) {
            $html .= '<li>' . htmlspecialchars(ucfirst((string)$accionFallback), ENT_QUOTES, 'UTF-8') . '.</li>';
        }
        $html .= '</ul>';
    }

    $modeloIa = defined('OPENAI_MODEL') ? (string)OPENAI_MODEL : '';
    $stmtSave = $mysqli->prepare(
        'INSERT INTO IA_Recomendacion_Diaria
            (IdUsuario, Fecha, Nivel, Puesto, Alcance, Recomendacion, ContextoHash, Modelo, Fuente, ErrorMsg)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE Recomendacion = Recomendacion'
    );
    $stmtSave->bind_param(
        'ssisssssss',
        $idUsuario,
        $fechaRecomendacion,
        $nivel,
        $puestoIa,
        $alcanceIa,
        $html,
        $contextHash,
        $modeloIa,
        $fuenteRecomendacion,
        $errorRecomendacion
    );
    $stmtSave->execute();
    $stmtSave->close();

    $stmtRelease = $mysqli->prepare('SELECT RELEASE_LOCK(?)');
    $stmtRelease->bind_param('s', $lockName);
    $stmtRelease->execute();
    $stmtRelease->close();
    $lockAcquired = false;

    echo json_encode([
        'ok'     => true,
        'html'   => $html,
        'cached' => false,
        'cache_scope' => 'daily_database',
        'source' => $fuenteRecomendacion,
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    if ($lockAcquired && $lockName !== '' && isset($mysqli) && $mysqli instanceof mysqli) {
        try {
            $stmtRelease = $mysqli->prepare('SELECT RELEASE_LOCK(?)');
            $stmtRelease->bind_param('s', $lockName);
            $stmtRelease->execute();
            $stmtRelease->close();
        } catch (Throwable $releaseError) {
            error_log('[Vista360 IA] No se pudo liberar bloqueo diario: ' . $releaseError->getMessage());
        }
    }
    error_log('[Vista360 IA] ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Error al generar recomendación IA: ' . $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
