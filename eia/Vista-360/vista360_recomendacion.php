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
    $vendEsc = $mysqli->real_escape_string($idUsuario);

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

    // Mapeo de nivel → rol funcional
    $rolDescripcion = 'Rol no identificado';
    $tieneVentasPropias = false;
    $enfasisCobranza    = false;
    $enfasisEquipo      = false;
    $enfasisEmpresa     = false;

    switch ($nivel) {
        case 7:
            $rolDescripcion    = 'Agente Externo: ejecutivo de ventas externo que vende productos KASU.';
            $tieneVentasPropias = true;
            break;
        case 6:
            $rolDescripcion    = 'Ejecutivo de Ventas interno: empleado de tiempo completo con metas de ventas claras.';
            $tieneVentasPropias = true;
            break;
        case 5:
            $rolDescripcion = 'Ejecutivo de Cobranza: se enfoca en recuperar pagos; no genera ventas nuevas.';
            $enfasisCobranza = true;
            break;
        case 4:
            $rolDescripcion = 'Coordinador: tiene vendedores y gestores de cobranza a cargo; supervisa un equipo.';
            $enfasisEquipo  = true;
            break;
        case 3:
            $rolDescripcion = 'Gerente de Ruta: responsable de la operación completa de una sucursal.';
            $enfasisEquipo  = true;
            break;
        case 2:
            $rolDescripcion = 'Mesa de Control: unidad centralizada de análisis, control y toma de decisiones.';
            $enfasisEmpresa = true;
            break;
        case 1:
            $rolDescripcion = 'Director general / Directores: visión global de la empresa.';
            $enfasisEmpresa = true;
            break;
        default:
            $rolDescripcion = 'Colaborador KASU con nivel no mapeado.';
            break;
    }

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
        WHERE Usuario = '$vendEsc'
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
        WHERE Usuario = '$vendEsc'
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
        WHERE Usuario = '$vendEsc'
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
        WHERE Usuario = '$vendEsc'
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
    $vendEscPros = $pros->real_escape_string($idUsuario);

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
        WHERE p.Asignado = '$vendEscPros'
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
        WHERE p.Asignado = '$vendEscPros'
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
        WHERE Asignado = '$vendEscPros'
          AND (Papeline = '' OR PosPapeline = 0)
    ";
    if ($res = $pros->query($sqlSinContacto)) {
        if ($row = $res->fetch_assoc()) {
            $prospectosResumen['sin_contacto'] = (int)$row['total'];
        }
        $res->close();
    }

    // -----------------------------------------------------------------------
    // Construir JSON de contexto para la IA
    // -----------------------------------------------------------------------
    $contexto = [
        'vendedor' => [
            'id_usuario'   => $idUsuario,
            'nombre'       => $nombreVendedor,
            'nivel'        => $nivel,
            'nombre_nivel' => $nombreNivel,
            'sucursal'     => $nombreSucursal,
            'rol'          => [
                'descripcion'          => $rolDescripcion,
                'tiene_ventas_propias' => $tieneVentasPropias,
                'enfasis_cobranza'     => $enfasisCobranza,
                'enfasis_equipo'       => $enfasisEquipo,
                'enfasis_empresa'      => $enfasisEmpresa,
            ],
        ],
        'fecha' => [
            'hoy'        => date('Y-m-d'),
            'mes_actual' => date('Y-m'),
        ],
        'resumen_ventas_mes' => $resumenVentasMes,
        'ventas_por_status'  => $ventasPorStatus,
        'pagos'              => $resumenPagos,
        'riesgo_cartera'     => $ventasMora,
        'prospectos'         => $prospectosResumen,
        'nota_extra'         => $notaExtra,
    ];

    $contextJson = json_encode($contexto, JSON_UNESCAPED_UNICODE);

    if ($contextJson === false) {
        throw new RuntimeException('No se pudo codificar JSON de contexto para IA.');
    }

    // -----------------------------------------------------------------------
    // Cache de respuesta para evitar llamadas repetidas con el mismo contexto
    // -----------------------------------------------------------------------
    $cacheKey = hash('sha256', $contextJson);
    $now      = time();
    $ttl      = 86400; // 24 horas (recomendación diaria)

    if (!isset($_SESSION['vista360_cache'])) {
        $_SESSION['vista360_cache'] = [];
    }

    if (isset($_SESSION['vista360_cache'][$cacheKey])) {
        $cached = $_SESSION['vista360_cache'][$cacheKey];
        if (!empty($cached['html']) && ($now - ($cached['time'] ?? 0)) < $ttl) {
            echo json_encode([
                'ok'        => true,
                'html'      => (string)$cached['html'],
                'cached'    => true,
                'cache_age' => $now - (int)$cached['time'],
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // -----------------------------------------------------------------------
    // Prompt para GPT-5.1 (ahora considerando el rol/nivel)
    // -----------------------------------------------------------------------
    $prompt = <<<PROMPT
Eres la IA comercial de KASU, una plataforma de servicios funerarios a futuro.

Recibirás un JSON con datos reales del usuario y su contexto:
- Datos del usuario: id, nombre, sucursal, nivel y rol funcional.
- Resumen de ventas del mes y por estatus (PREVENTA, COBRANZA, ACTIVO, etc.).
- Resumen de pagos (pendientes, en mora).
- Resumen de prospectos por etapas de pipeline y una lista corta de los más cercanos a cierre.
- Resumen de riesgo de cartera (ventas en mora y monto pendiente).

INTERPRETACIÓN DEL ROL (vendedor.rol):
- Si tiene_ventas_propias = true (niveles 6 y 7: ejecutivos de ventas internos o externos),
  enfócate en su cartera personal: a quién llamar, a quién cerrar, cómo llegar a su meta.
- Si enfasis_cobranza = true (nivel 5: Ejecutivo Cobranza),
  enfócate en pagos vencidos, promesas, priorizar cuentas en mora y estrategias de recuperación.
- Si enfasis_equipo = true (niveles 3 y 4: Gerente de Ruta / Coordinador),
  habla en términos de EQUIPO: ventas y cobranza agregadas, qué tipo de colaboradores atender,
  qué pipeline reforzar o limpiar.
- Si enfasis_empresa = true (niveles 1 y 2: Dirección / Mesa de Control),
  da una visión GLOBAL: tendencias, focos rojos, áreas de oportunidad por cartera y pipeline.
- No critiques al usuario por tener ventas = 0 cuando su rol NO tiene ventas propias.

OBJETIVO:
Genera una RECOMENDACIÓN CORTA y ACCIONABLE adaptada al rol, priorizando:
1) Qué acciones debe realizar HOY (contactos, cobros, decisiones).
2) Qué riesgos debe atender (mora, pipeline saturado, pocos prospectos, etc.).
3) Un mensaje motivador alineado a su nivel de responsabilidad.

REGLAS DE ESTILO:
- Responde en ESPAÑOL neutro.
- Máximo 6 líneas de contenido en total.
- Usa un párrafo inicial muy corto (1–2 frases) y después una lista de 2–4 bullets con acciones específicas.
- Adapta el mensaje a los datos del JSON (cantidades, etapas, nivel); no des siempre el mismo texto.
- Si hay poca información, da recomendaciones generales pero útiles para prospección, seguimiento o análisis.

IMPORTANTE:
- Devuelve ÚNICAMENTE HTML válido para incrustar dentro de un <div>, por ejemplo:
  <p>Texto breve...</p>
  <ul><li>Acción 1...</li><li>Acción 2...</li></ul>
- NO incluyas etiquetas <html>, <head> ni <body>.
- No inventes datos; usa solo lo que venga en el JSON.

AQUÍ ESTÁ EL JSON CON LOS DATOS DEL USUARIO:

{$contextJson}
PROMPT;

    // -----------------------------------------------------------------------
    // Llamada a OpenAI
    // -----------------------------------------------------------------------
    $texto = openai_simple_text($prompt, 450);

    // Sanitización básica: permitimos solo etiquetas simples
    $allowed = '<p><ul><ol><li><strong><b><em><i><br>';
    $html    = trim(strip_tags($texto, $allowed));

    if ($html === '') {
        throw new RuntimeException('La respuesta de IA llegó vacía.');
    }

    // Guardar en cache de sesión
    $_SESSION['vista360_cache'][$cacheKey] = [
        'html' => $html,
        'time' => $now,
    ];

    echo json_encode([
        'ok'     => true,
        'html'   => $html,
        'cached' => false,
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    error_log('[Vista360 IA] ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Error al generar recomendación IA: ' . $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
