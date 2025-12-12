<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : ia_correo_prospecto.php
 * Carpeta : /eia/Vista-360
 *
 * Qué hace:
 *  - Genera con OpenAI el texto de un correo personalizado para un prospecto.
 *  - Envía el correo usando la plantilla "IA · CORREO PROSPECTO".
 *  - Guarda en BD el correo enviado para análisis y mejora futura
 *    en la tabla Correos_Prospectos_IA.
 *  - Devuelve también info del prospecto e historial reciente de correos IA.
 *
 * Entrada JSON POST:
 *  {
 *    "id_prospecto": 123,
 *    "tipo": "primer_contacto|seguimiento|cierre",   // opcional, default: seguimiento
 *    "nota_vendedor": "texto opcional para guiar a la IA"
 *  }
 *
 * Salida JSON:
 *  {
 *    "ok": true|false,
 *    "id_prospecto": 123,
 *    "tipo": "seguimiento",
 *    "asunto": "...",
 *    "email": "...",
 *    "nombre": "...",
 *    "resultado_envio": true|false,
 *    "error_envio": "..." | null,
 *    "prospecto": { ... },
 *    "historial_correos": [ ... ]   // últimos envíos IA a ese prospecto
 *  }
 * ============================================================================
 */

header('Content-Type: application/json; charset=utf-8');

try {
    // ================= Sesión =================
    $sessionFile = __DIR__ . '/../session.php';
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

    // ================= Dependencias =================
    require_once __DIR__ . '/../librerias.php';       // $mysqli, $pros, $basicas, $seguridad, $Correo
    require_once __DIR__ . '/openai_config.php';      // openai_simple_text()
    require_once __DIR__ . '/ia_tools_correo.php';    // kasu_enviar_correo_simple(), kasu_get_correo_instance()

    global $mysqli, $pros, $basicas, $seguridad;

    if (!$pros || !$mysqli) {
        throw new RuntimeException('Conexiones a BD no disponibles.');
    }

    $idUsuario = (string)($_SESSION['Vendedor'] ?? '');
    if ($idUsuario === '') {
        throw new RuntimeException('Sesión no válida: falta Vendedor.');
    }

    // ================= Entrada =================
    $raw  = file_get_contents('php://input');
    $data = $raw ? json_decode($raw, true) : [];
    if (!is_array($data)) {
        $data = [];
    }

    $idProspecto  = (int)($data['id_prospecto'] ?? 0);
    $tipo         = strtolower(trim((string)($data['tipo'] ?? 'seguimiento')));
    $notaVendedor = trim((string)($data['nota_vendedor'] ?? ''));

    if ($idProspecto <= 0) {
        throw new InvalidArgumentException('id_prospecto inválido.');
    }

    if (!in_array($tipo, ['primer_contacto', 'seguimiento', 'cierre'], true)) {
        $tipo = 'seguimiento';
    }

    // ================= Datos del prospecto =================
    $sql = "SELECT Id, FullName, Email FROM prospectos WHERE Id = ? LIMIT 1";
    $stmt = $pros->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('No se pudo preparar consulta de prospecto.');
    }
    $stmt->bind_param('i', $idProspecto);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: [];
    $stmt->close();

    if (!$row) {
        throw new RuntimeException('Prospecto no encontrado.');
    }

    $nombrePros = (string)$row['FullName'];
    $emailPros  = (string)$row['Email'];

    if (!filter_var($emailPros, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('El prospecto no tiene correo válido.');
    }

    // ================= Prompt para OpenAI =================
    $contextoUsuario = [
        'id_usuario'  => $idUsuario,
        'fecha_hoy'   => date('Y-m-d'),
        'tipo_correo' => $tipo,
    ];

    $ctxJson = json_encode($contextoUsuario, JSON_UNESCAPED_UNICODE);

    $prompt = <<<PROMPT
Eres la IA comercial de KASU. Tu trabajo es redactar correos por EMAIL para prospectos.

Contexto (JSON):
{$ctxJson}

Datos del prospecto:
- Nombre: {$nombrePros}
- Email: {$emailPros}

Tipo de correo a redactar: {$tipo}

Notas del vendedor (si hay): "{$notaVendedor}"

Reglas:
- Tono profesional, cercano, CLARO.
- Máximo 3 párrafos + 1 lista de bullets opcional.
- No incluyas firma larga, solo un cierre corto ("Quedo pendiente", etc.).
- NO pongas saludos tipo "Estimado equipo", siempre habla al prospecto por su nombre.
- NO incluyas <html>, <body> ni estilos; solo contenido dentro del cuerpo.

Responde EXCLUSIVAMENTE un JSON con esta estructura:

{
  "asunto": "línea de asunto clara y breve",
  "cuerpo_html": "<p>...texto en HTML sencillo...</p>",
  "cta_texto": "texto corto para una llamada a la acción (o vacío)",
  "cta_url_sugerida": "puede ir vacío, el sistema puede ignorarlo"
}
PROMPT;

    $respuestaIa = openai_simple_text($prompt, 850);
    $decodedIa   = json_decode($respuestaIa, true);

    $asunto    = '';
    $cuerpo    = '';
    $ctaTexto  = '';
    $ctaUrlSug = '';

    if (is_array($decodedIa)) {
        $asunto    = trim((string)($decodedIa['asunto'] ?? 'Seguimiento de tu plan KASU'));
        $cuerpo    = trim((string)($decodedIa['cuerpo_html'] ?? ''));
        $ctaTexto  = trim((string)($decodedIa['cta_texto'] ?? ''));
        $ctaUrlSug = trim((string)($decodedIa['cta_url_sugerida'] ?? ''));
    }

    if ($cuerpo === '') {
        $cuerpo = '<p>Te contacto de KASU para darte seguimiento a tu interés en proteger a tu familia con un plan a futuro. '
                . 'Puedo apoyarte a resolver dudas y mostrarte cómo quedaría tu plan.</p>';
    }
    if ($asunto === '') {
        $asunto = 'Seguimiento a tu interés en KASU';
    }

    // URL real para CTA (puede venir vacía; la plantilla omitirá el botón si está vacía)
    $ctaUrlReal = $ctaUrlSug;

    // ================= Envío de correo usando plantilla IA =================
    $dataCorreo = [
        'Cte'        => $nombrePros,
        'Nombre'     => $nombrePros,
        'CuerpoHtml' => $cuerpo,
        'CtaTexto'   => $ctaTexto,
        'CtaUrl'     => $ctaUrlReal,
    ];

    // Asunto visible = asunto generado por la IA
    $asuntoVisible = $asunto;

    // Helper centralizado (usa Correo::Mensaje + PHPMailer)
    $resultadoEnvioArr = kasu_enviar_correo_simple(
        $asuntoVisible,
        $emailPros,
        $nombrePros,
        $dataCorreo,
        (string)$idProspecto,
        'IA_Correo_Prospecto'
    );

    $okEnvio = (bool)($resultadoEnvioArr['ok'] ?? false);

    // ================= Log en tabla Correos_Prospectos_IA =================
    $resultadoEnvio = $okEnvio ? 'OK' : 'ERROR';
    $errorMsg       = $okEnvio ? null : 'Fallo en EnviarCorreo';

    try {
        // Canal fijo para identificar que viene desde Vista 360
        $canal = 'IA_Vista360';

        $sqlLog = "INSERT INTO Correos_Prospectos_IA
            (IdProspecto, IdVendedor, Tipo, Canal, EmailDestino, Asunto, CuerpoHtml,
             ResultadoEnvio, ErrorMsg, ModeloIA, PromptBase, RespuestaJson)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmtLog = $mysqli->prepare($sqlLog);
        if ($stmtLog) {
            $modeloIa   = 'gpt-5.1-thinking'; // ajusta si usas otro identificador
            $promptSave = mb_substr($prompt,      0, 60000);
            $respSave   = mb_substr($respuestaIa, 0, 60000);

            $stmtLog->bind_param(
                'isssssssssss',
                $idProspecto,
                $idUsuario,
                $tipo,
                $canal,
                $emailPros,
                $asuntoVisible,
                $cuerpo,
                $resultadoEnvio,
                $errorMsg,
                $modeloIa,
                $promptSave,
                $respSave
            );
            $stmtLog->execute();
            $stmtLog->close();
        } else {
            error_log('[IA Correo Prospecto] No se pudo preparar INSERT de log: ' . $mysqli->error);
        }
    } catch (Throwable $eLog) {
        error_log('[IA Correo Prospecto] Error al registrar log: ' . $eLog->getMessage());
    }

    // ================= Historial de correos IA para el prospecto =================
    $historial = [];
    try {
        $sqlHist = "SELECT Id, Tipo, Canal, Asunto, ResultadoEnvio, FechaEnvio
                    FROM Correos_Prospectos_IA
                    WHERE IdProspecto = ?
                    ORDER BY FechaEnvio DESC
                    LIMIT 10";
        $stmtHist = $mysqli->prepare($sqlHist);
        if ($stmtHist) {
            $stmtHist->bind_param('i', $idProspecto);
            $stmtHist->execute();
            $resHist = $stmtHist->get_result();
            while ($r = $resHist->fetch_assoc()) {
                $historial[] = [
                    'id'              => (int)$r['Id'],
                    'tipo'            => (string)$r['Tipo'],
                    'canal'           => (string)$r['Canal'],
                    'asunto'          => (string)$r['Asunto'],
                    'resultado_envio' => (string)$r['ResultadoEnvio'],
                    'fecha_envio'     => (string)$r['FechaEnvio'],
                ];
            }
            $stmtHist->close();
        }
    } catch (Throwable $eHist) {
        error_log('[IA Correo Prospecto] Error al leer historial: ' . $eHist->getMessage());
    }

    // ================= Respuesta =================
    echo json_encode([
        'ok'              => $okEnvio,
        'id_prospecto'    => $idProspecto,
        'tipo'            => $tipo,
        'asunto'          => $asuntoVisible,
        'email'           => $emailPros,
        'nombre'          => $nombrePros,
        'resultado_envio' => $okEnvio,
        'error_envio'     => $okEnvio ? null : $errorMsg,
        'prospecto'       => [
            'id'     => $idProspecto,
            'nombre' => $nombrePros,
            'email'  => $emailPros,
        ],
        'historial_correos' => $historial,
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'ok'    => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    error_log('[IA Correo Prospecto] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Error interno del sistema',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
