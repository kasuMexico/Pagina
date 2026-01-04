<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : vista360_prospecto_analisis.php
 * Carpeta : /eia/Vista-360
 * Qué hace: Genera analisis IA para un prospecto usando historial de comentarios
 *           y mapeo con ventas efectivas. Devuelve HTML para el modal.
 * ============================================================================
 */

header('Content-Type: application/json; charset=utf-8');

try {
    // ------------------------------------------------------------
    // Sesion
    // ------------------------------------------------------------
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
        throw new RuntimeException('Sesion no valida: falta Vendedor.');
    }

    // ------------------------------------------------------------
    // Dependencias
    // ------------------------------------------------------------
    require_once dirname(__DIR__) . '/librerias.php';
    require_once __DIR__ . '/openai_config.php';
    require_once __DIR__ . '/ia_context_builder.php';

    global $pros, $mysqli, $basicas;

    if (!$pros) {
        throw new RuntimeException('Conexion $pros no disponible.');
    }
    if (!$mysqli) {
        throw new RuntimeException('Conexion $mysqli no disponible.');
    }

    // ------------------------------------------------------------
    // Entrada
    // ------------------------------------------------------------
    $raw = file_get_contents('php://input');
    $body = $raw ? json_decode($raw, true) : [];
    if (!is_array($body)) {
        $body = [];
    }

    $idProspecto = (int)($body['id_prospecto'] ?? 0);
    $force = !empty($body['force']);

    if ($idProspecto <= 0) {
        throw new InvalidArgumentException('Prospecto invalido.');
    }

    // ------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------
    $tableExists = function (mysqli $db, string $table): bool {
        $sql = "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1";
        $stmt = $db->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('s', $table);
        $stmt->execute();
        $res = $stmt->get_result();
        $ok = $res && $res->fetch_row();
        $stmt->close();
        return (bool)$ok;
    };

    $extractJson = function (string $text): ?array {
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }
        $json = substr($text, $start, $end - $start + 1);
        $data = json_decode($json, true);
        return is_array($data) ? $data : null;
    };

    $renderHtml = function (array $analysis): string {
        $score = (int)($analysis['lead_score'] ?? 0);
        $grade = (string)($analysis['lead_grade'] ?? '');
        $summary = (string)($analysis['resumen'] ?? '');
        $recommend = (string)($analysis['recomendacion'] ?? '');
        $nextSteps = $analysis['next_steps'] ?? [];
        if (!is_array($nextSteps)) {
            $nextSteps = [];
        }
        $ventas = $analysis['ventas_relacionadas'] ?? [];
        if (!is_array($ventas)) {
            $ventas = [];
        }

        $html = '<div class="ia-prospecto-card">';
        $html .= '<p><strong>Calificacion lead:</strong> ' . htmlspecialchars((string)$score, ENT_QUOTES, 'UTF-8');
        if ($grade !== '') {
            $html .= ' (' . htmlspecialchars($grade, ENT_QUOTES, 'UTF-8') . ')';
        }
        $html .= '</p>';
        if (!empty($analysis['missing_data']) && is_array($analysis['missing_data'])) {
            $html .= '<p><strong>Datos faltantes:</strong> ' . htmlspecialchars(implode(', ', $analysis['missing_data']), ENT_QUOTES, 'UTF-8') . '</p>';
        }
        if ($summary !== '') {
            $html .= '<p><strong>Resumen:</strong> ' . htmlspecialchars($summary, ENT_QUOTES, 'UTF-8') . '</p>';
        }
        if (!empty($nextSteps)) {
            $html .= '<p><strong>Siguientes pasos:</strong></p><ul>';
            foreach ($nextSteps as $step) {
                $html .= '<li>' . htmlspecialchars((string)$step, ENT_QUOTES, 'UTF-8') . '</li>';
            }
            $html .= '</ul>';
        }
        if ($recommend !== '') {
            $html .= '<p><strong>Recomendacion:</strong> ' . htmlspecialchars($recommend, ENT_QUOTES, 'UTF-8') . '</p>';
        }
        if (!empty($ventas)) {
            $html .= '<p><strong>Ventas relacionadas:</strong></p><ul>';
            foreach ($ventas as $venta) {
                if (!is_array($venta)) {
                    continue;
                }
                $line = trim((string)($venta['producto'] ?? ''));
                $status = trim((string)($venta['status'] ?? ''));
                $fecha = trim((string)($venta['fecha'] ?? ''));
                $piece = $line;
                if ($status !== '') {
                    $piece .= ' · ' . $status;
                }
                if ($fecha !== '') {
                    $piece .= ' · ' . $fecha;
                }
                if ($piece !== '') {
                    $html .= '<li>' . htmlspecialchars($piece, ENT_QUOTES, 'UTF-8') . '</li>';
                }
            }
            $html .= '</ul>';
        }
        $html .= '</div>';
        return $html;
    };

    // ------------------------------------------------------------
    // Prospecto base
    // ------------------------------------------------------------
    $sqlPros = "
        SELECT Id, FullName, NoTel, Email, Servicio_Interes, Origen, Alta, Asignado, Curp, Direccion
        FROM prospectos
        WHERE Id = ?
        LIMIT 1
    ";
    $stmtPros = $pros->prepare($sqlPros);
    if (!$stmtPros) {
        throw new RuntimeException('No se pudo preparar prospecto.');
    }
    $stmtPros->bind_param('i', $idProspecto);
    $stmtPros->execute();
    $prosRow = $stmtPros->get_result()->fetch_assoc() ?: null;
    $stmtPros->close();

    if (!$prosRow) {
        throw new RuntimeException('Prospecto no encontrado.');
    }

    $telRaw = preg_replace('/\\D+/', '', (string)($prosRow['NoTel'] ?? ''));
    $emailRaw = trim((string)($prosRow['Email'] ?? ''));
    $missingData = [];
    if (trim((string)($prosRow['Curp'] ?? '')) === '') {
        $missingData[] = 'CURP';
    }
    if (trim((string)($prosRow['Direccion'] ?? '')) === '') {
        $missingData[] = 'Direccion';
    }

    // ------------------------------------------------------------
    // Comentarios
    // ------------------------------------------------------------
    $comentarios = [];
    $lastCommentAt = '';
    if ($tableExists($pros, 'Prospectos_Comentarios')) {
        $sqlCom = "
            SELECT IdUsuario, Comentario, FechaRegistro
            FROM Prospectos_Comentarios
            WHERE IdProspecto = ?
            ORDER BY FechaRegistro DESC
            LIMIT 25
        ";
        $stmtCom = $pros->prepare($sqlCom);
        if ($stmtCom) {
            $stmtCom->bind_param('i', $idProspecto);
            $stmtCom->execute();
            $resCom = $stmtCom->get_result();
            while ($row = $resCom->fetch_assoc()) {
                $comentarios[] = [
                    'usuario' => (string)($row['IdUsuario'] ?? ''),
                    'comentario' => (string)($row['Comentario'] ?? ''),
                    'fecha' => (string)($row['FechaRegistro'] ?? ''),
                ];
                if ($lastCommentAt === '' && !empty($row['FechaRegistro'])) {
                    $lastCommentAt = (string)$row['FechaRegistro'];
                }
            }
            $stmtCom->close();
        }
    }

    // ------------------------------------------------------------
    // Analisis cache
    // ------------------------------------------------------------
    $analisisRow = null;
    if ($tableExists($pros, 'Prospectos_Analisis_IA')) {
        $sqlAna = "
            SELECT LeadScore, Resumen, PasosSugeridos, Recomendacion, AnalisisJson, FechaAnalisis
            FROM Prospectos_Analisis_IA
            WHERE IdProspecto = ?
            ORDER BY FechaAnalisis DESC
            LIMIT 1
        ";
        $stmtAna = $pros->prepare($sqlAna);
        if ($stmtAna) {
            $stmtAna->bind_param('i', $idProspecto);
            $stmtAna->execute();
            $analisisRow = $stmtAna->get_result()->fetch_assoc() ?: null;
            $stmtAna->close();
        }
    }

    if ($analisisRow && !$force && $lastCommentAt !== '' && !empty($analisisRow['FechaAnalisis'])) {
        if (strtotime((string)$analisisRow['FechaAnalisis']) >= strtotime($lastCommentAt)) {
            $analysis = [];
            if (!empty($analisisRow['AnalisisJson'])) {
                $analysis = json_decode((string)$analisisRow['AnalisisJson'], true) ?: [];
            }
            if (!$analysis) {
                $analysis = [
                    'lead_score' => (int)($analisisRow['LeadScore'] ?? 0),
                    'resumen' => (string)($analisisRow['Resumen'] ?? ''),
                    'recomendacion' => (string)($analisisRow['Recomendacion'] ?? ''),
                    'next_steps' => json_decode((string)($analisisRow['PasosSugeridos'] ?? ''), true) ?: [],
                ];
            }
            echo json_encode([
                'ok' => true,
                'html' => $renderHtml($analysis),
                'cached' => true,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // ------------------------------------------------------------
    // Ventas relacionadas
    // ------------------------------------------------------------
    $ventas = [];
    $ventaConditions = [];
    $ventaParams = [];
    $ventaTypes = '';

    if ($telRaw !== '') {
        $ventaConditions[] = "REPLACE(REPLACE(REPLACE(COALESCE(c.Telefono,''),' ',''),'-',''),'+','') LIKE ?";
        $ventaParams[] = '%' . $telRaw . '%';
        $ventaTypes .= 's';
    }
    if ($emailRaw !== '') {
        $ventaConditions[] = "COALESCE(c.Mail,'') = ?";
        $ventaParams[] = $emailRaw;
        $ventaTypes .= 's';
    }

    if (!empty($ventaConditions)) {
        $sqlVenta = "
            SELECT v.Id, v.Producto, v.Status, v.CostoVenta, v.FechaRegistro, c.Mail AS email, c.Telefono AS telefono
            FROM Venta v
            LEFT JOIN Contacto c ON c.id = v.IdContact
            WHERE " . implode(' OR ', $ventaConditions) . "
            ORDER BY v.FechaRegistro DESC
            LIMIT 5
        ";
        $stmtVenta = $mysqli->prepare($sqlVenta);
        if ($stmtVenta) {
            $stmtVenta->bind_param($ventaTypes, ...$ventaParams);
            $stmtVenta->execute();
            $resVenta = $stmtVenta->get_result();
            while ($row = $resVenta->fetch_assoc()) {
                $ventas[] = [
                    'producto' => (string)($row['Producto'] ?? ''),
                    'status' => (string)($row['Status'] ?? ''),
                    'fecha' => (string)($row['FechaRegistro'] ?? ''),
                    'monto' => (float)($row['CostoVenta'] ?? 0),
                ];
            }
            $stmtVenta->close();
        }
    }

    // ------------------------------------------------------------
    // Contexto IA
    // ------------------------------------------------------------
    $historialTxt = 'Sin comentarios previos.';
    if (!empty($comentarios)) {
        $lines = [];
        foreach ($comentarios as $com) {
            $lines[] = '[' . $com['fecha'] . '] ' . $com['usuario'] . ': ' . $com['comentario'];
        }
        $historialTxt = implode("\n", $lines);
    }

    $contextBuilder = new IAContextBuilder($_SESSION);
    $context = [
        'usuario' => $contextBuilder->getUserData(),
        'prospecto' => [
            'id' => (int)$prosRow['Id'],
            'nombre' => (string)$prosRow['FullName'],
            'telefono' => (string)$prosRow['NoTel'],
            'email' => (string)$prosRow['Email'],
            'servicio_interes' => (string)$prosRow['Servicio_Interes'],
            'origen' => (string)$prosRow['Origen'],
            'alta' => (string)$prosRow['Alta'],
        ],
        'datos_faltantes' => $missingData,
        'historial_comentarios' => $historialTxt,
        'ventas_relacionadas' => $ventas,
    ];

    $prompt = "Eres analista de ventas KASU.\n"
        . "Analiza el prospecto usando el contexto en JSON.\n"
        . "Si hay datos faltantes (CURP, Direccion), el paso 1 debe ser completarlos.\n"
        . "Si hay ventas relacionadas, compara y recomienda pasos para convertir.\n"
        . "Devuelve SOLO un objeto JSON con estas claves:\n"
        . "lead_score (0-100), lead_grade (A,B,C,D), resumen, next_steps (array), recomendacion, ventas_relacionadas (array), missing_data (array).\n"
        . "Contexto:\n" . json_encode($context, JSON_UNESCAPED_UNICODE);

    $analysis = [];
    $source = 'fallback';

    try {
        $rawAI = openai_simple_text($prompt, 650);
        $parsed = $extractJson($rawAI);
        if (is_array($parsed)) {
            $analysis = $parsed;
            $source = 'openai';
        }
    } catch (Throwable $e) {
        // fallback abajo
    }

    if (empty($analysis)) {
        $score = 40;
        if (!empty($comentarios)) {
            $score += min(30, count($comentarios) * 5);
        }
        if (!empty($ventas)) {
            $score += 20;
        }
        if ($score > 95) {
            $score = 95;
        }
        $grade = $score >= 80 ? 'A' : ($score >= 60 ? 'B' : ($score >= 40 ? 'C' : 'D'));
        $analysis = [
            'lead_score' => $score,
            'lead_grade' => $grade,
            'resumen' => 'Analisis basado en historial y datos del prospecto.',
            'next_steps' => [
                !empty($missingData)
                    ? ('Completar datos faltantes: ' . implode(', ', $missingData) . '.')
                    : 'Contactar al prospecto para confirmar interes y presupuesto.',
                'Enviar informacion del servicio y beneficios clave.',
                'Agendar seguimiento con fecha y hora concreta.'
            ],
            'recomendacion' => !empty($ventas)
                ? 'Hay ventas relacionadas: alinea oferta con productos ya vendidos y resalta beneficios.'
                : 'Enfoca la conversacion en necesidad principal y urgencia del servicio.',
            'ventas_relacionadas' => $ventas,
            'missing_data' => $missingData,
        ];
    }

    // Normalizar grade si no viene
    if (empty($analysis['lead_grade'])) {
        $score = (int)($analysis['lead_score'] ?? 0);
        $analysis['lead_grade'] = $score >= 80 ? 'A' : ($score >= 60 ? 'B' : ($score >= 40 ? 'C' : 'D'));
    }

    if (!isset($analysis['missing_data']) || !is_array($analysis['missing_data'])) {
        $analysis['missing_data'] = $missingData;
    }
    if (!empty($missingData)) {
        $analysis['missing_data'] = $missingData;
        if (!empty($analysis['next_steps']) && is_array($analysis['next_steps'])) {
            $firstStep = (string)$analysis['next_steps'][0];
            if (stripos($firstStep, 'completar') === false) {
                array_unshift($analysis['next_steps'], 'Completar datos faltantes: ' . implode(', ', $missingData) . '.');
            }
        }
    }

    $analysis['source'] = $source;

    // ------------------------------------------------------------
    // Guardar analisis
    // ------------------------------------------------------------
    if ($tableExists($pros, 'Prospectos_Analisis_IA')) {
        $leadScore = (int)($analysis['lead_score'] ?? 0);
        $resumen = (string)($analysis['resumen'] ?? '');
        $pasos = $analysis['next_steps'] ?? [];
        if (!is_array($pasos)) {
            $pasos = [];
        }
        $pasosJson = json_encode($pasos, JSON_UNESCAPED_UNICODE);
        $recom = (string)($analysis['recomendacion'] ?? '');
        $analysisJson = json_encode($analysis, JSON_UNESCAPED_UNICODE);

        $sqlUp = "
            INSERT INTO Prospectos_Analisis_IA
                (IdProspecto, LeadScore, Resumen, PasosSugeridos, Recomendacion, AnalisisJson, FechaAnalisis)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                LeadScore = VALUES(LeadScore),
                Resumen = VALUES(Resumen),
                PasosSugeridos = VALUES(PasosSugeridos),
                Recomendacion = VALUES(Recomendacion),
                AnalisisJson = VALUES(AnalisisJson),
                FechaAnalisis = VALUES(FechaAnalisis)
        ";
        $stmtUp = $pros->prepare($sqlUp);
        if ($stmtUp) {
            $stmtUp->bind_param(
                'iissss',
                $idProspecto,
                $leadScore,
                $resumen,
                $pasosJson,
                $recom,
                $analysisJson
            );
            $stmtUp->execute();
            $stmtUp->close();
        }
    }

    echo json_encode([
        'ok' => true,
        'html' => $renderHtml($analysis),
        'cached' => false,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
