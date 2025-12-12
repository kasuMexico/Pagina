<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : ia_agente_conversacional.php
 * Carpeta : /eia/Vista-360
 * Qué hace: Endpoint principal del agente conversacional inteligente
 *           Versión simplificada que funciona sin OpenAI inicialmente
 * ============================================================================
 */

header('Content-Type: application/json; charset=utf-8');

try {
    // ==================== INICIALIZACIÓN ====================
    
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
    
    // Dependencias globales
    require_once __DIR__ . '/../librerias.php';
    require_once __DIR__ . '/../Funciones/Funciones_Financieras.php';
    
    global $mysqli, $pros, $basicas, $financieras;
    
    if (!$mysqli) throw new RuntimeException('Conexión $mysqli no disponible.');
    if (!$pros) throw new RuntimeException('Conexión $pros no disponible.');
    
    if (!isset($financieras) || !($financieras instanceof Financieras)) {
        $financieras = new Financieras();
    }
    
    // Verificar sesión
    if (empty($_SESSION['Vendedor'])) {
        throw new RuntimeException('Sesión no válida. Se requiere autenticación.');
    }
    
    // Cargar componentes del agente
    require_once __DIR__ . '/ia_tools_registry.php';
    require_once __DIR__ . '/ia_context_builder.php';
    require_once __DIR__ . '/ia_action_sequencer.php';
    require_once __DIR__ . '/ia_conversation_store.php';
    require_once __DIR__ . '/ia_response_formatter.php';
    
    // Intentar cargar OpenAI (pero no es crítico si falla)
    $openaiAvailable = false;
    $openaiConfigFile = __DIR__ . '/openai_config.php';
    if (is_file($openaiConfigFile)) {
        require_once $openaiConfigFile;
        $openaiAvailable = function_exists('openai_simple_text');
    }
    
    // ==================== ENTRADA ====================
    $raw = file_get_contents('php://input');
    $input = $raw ? json_decode($raw, true) : [];
    if (!is_array($input)) $input = [];
    
    $userMessage = trim((string)($input['mensaje'] ?? ''));
    $confirmationId = trim((string)($input['confirmation_id'] ?? ''));
    $confirmationResponse = trim((string)($input['confirmation_response'] ?? ''));
    
    if (empty($userMessage) && empty($confirmationId)) {
        throw new InvalidArgumentException('Se requiere "mensaje" o "confirmation_id".');
    }
    
    // ==================== INICIALIZAR COMPONENTES ====================
    
    // 1. Registro de tools
    $toolsRegistry = new IAToolsRegistry();
    $toolsRegistry->initializeKasutools();
    
    // 2. Almacenamiento de conversación
    $conversationStore = new IAConversationStore('ia_agente_conversacion', 25);
    
    // 3. Constructor de contexto
    $contextBuilder = new IAContextBuilder($_SESSION);
    
    // 4. Secuenciador de acciones
    $actionSequencer = new IAActionSequencer($toolsRegistry);
    
    // 5. Formateador de respuestas
    $responseFormatter = new IAResponseFormatter();
    
    // ==================== MANEJAR CONFIRMACIÓN PENDIENTE ====================
    
    if (!empty($confirmationId) && isset($_SESSION['pending_confirmation'][$confirmationId])) {
        $pendingConfirmation = $_SESSION['pending_confirmation'][$confirmationId];
        unset($_SESSION['pending_confirmation'][$confirmationId]);
        
        if ($confirmationResponse === 'confirm') {
            // Ejecutar acción confirmada
            $result = $actionSequencer->executeConfirmedAction(
                $pendingConfirmation['action'],
                $pendingConfirmation['user_context']
            );
            
            // Registrar en conversación
            $conversationStore->addSystemTurn(
                $pendingConfirmation['action']['tool'],
                $result,
                'Acción confirmada y ejecutada'
            );
            
            // Continuar con el resto del plan si existe
            if (!empty($pendingConfirmation['pending_actions'])) {
                $remainingPlan = [
                    'actions' => $pendingConfirmation['pending_actions'],
                    'response' => 'Continuando con las acciones restantes...',
                    'next_steps' => []
                ];
                
                $executionResult = $actionSequencer->executePlan($remainingPlan, $pendingConfirmation['user_context']);
                
                // Formatear y retornar respuesta
                $response = $responseFormatter->formatSuccessResponse($executionResult, $pendingConfirmation['user_context']);
            } else {
                // Solo fue una acción individual
                $response = [
                    'ok' => true,
                    'type' => 'single_action_completed',
                    'html' => '<p><strong>✅ Acción completada exitosamente.</strong></p>',
                    'data' => ['result' => $result],
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            // Usuario canceló
            $conversationStore->addSystemTurn(
                $pendingConfirmation['action']['tool'],
                ['ok' => false, 'error' => 'Acción cancelada por el usuario'],
                'Acción cancelada por el usuario'
            );
            
            $response = [
                'ok' => true,
                'type' => 'action_cancelled',
                'html' => '<p><strong>⚠️ Acción cancelada.</strong></p>',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    // ==================== MANEJAR MENSAJE NORMAL ====================
    
    // Registrar mensaje del usuario
    $conversationStore->addTurn('user', $userMessage);
    
    // Construir contexto del usuario
    $userContext = $contextBuilder->buildContext($userMessage, [
        'tools_available'   => count($toolsRegistry->getToolsForOpenAI()),
        'openai_available'  => $openaiAvailable
    ]);

    // Si el usuario pide explícitamente una recomendación para mejorar su desempeño,
    // delegamos al orquestador vista360_chat_acciones.php y respondemos directo.
    if (!empty($userMessage) && iaDetectRecomendacionIntent($userMessage)) {
        $recResponse = iaCallVista360Recomendacion($userMessage, $userContext);

        echo json_encode($recResponse, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // ==================== GENERAR PLAN (CON O SIN IA) ====================
    
    if ($openaiAvailable) {
        // Intentar con OpenAI
        try {
            $plan = generatePlanWithAI($userMessage, $userContext, $toolsRegistry, $conversationStore);
        } catch (Throwable $e) {
            error_log('[IA Agente] Falló OpenAI, usando lógica básica: ' . $e->getMessage());
            $plan = generateBasicPlan($userMessage, $userContext);
        }
    } else {
        // Usar lógica básica
        $plan = generateBasicPlan($userMessage, $userContext);
    }

    
    // ==================== EJECUTAR PLAN ====================
    
    $executionResult = $actionSequencer->executePlan($plan, $userContext);
    
    // ==================== MANEJAR RESULTADO ====================
    
    if ($executionResult['status'] === 'needs_confirmation') {
        // Guardar confirmación pendiente en sesión
        $confirmationId = uniqid('confirm_', true);
        
        $_SESSION['pending_confirmation'][$confirmationId] = [
            'action' => $executionResult['confirmation_data']['action'],
            'pending_actions' => $executionResult['confirmation_data']['pending_actions'] ?? [],
            'user_context' => $userContext,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Formatear respuesta de confirmación
        $response = $responseFormatter->formatConfirmationResponse(
            $executionResult['confirmation_data'],
            $userContext
        );
        
        $response['confirmation_id'] = $confirmationId;
        
    } elseif ($executionResult['status'] === 'success' || $executionResult['status'] === 'partial_failure') {
        // Registrar ejecución en conversación
        $summary = $executionResult['execution_summary'] ?? [];
        if (!empty($summary['results'])) {
            foreach ($summary['results'] as $result) {
                if ($result['ok'] ?? false) {
                    $conversationStore->addSystemTurn(
                        $result['tool'] ?? 'unknown',
                        $result,
                        'Acción ejecutada exitosamente'
                    );
                }
            }
        }
        
        // Formatear respuesta normal
        if ($executionResult['status'] === 'success') {
            $response = $responseFormatter->formatSuccessResponse($executionResult, $userContext);
        } else {
            $response = $responseFormatter->formatErrorResponse($executionResult, $userContext);
        }
        
        // Registrar respuesta de la IA
        $conversationStore->addTurn('assistant', $executionResult['response'] ?? '');
        
    } else {
        throw new RuntimeException('Estado de ejecución desconocido: ' . ($executionResult['status'] ?? 'unknown'));
    }
    
    // ==================== ENVIAR RESPUESTA ====================
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
    
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
        'type' => 'validation_error'
    ], JSON_UNESCAPED_UNICODE);
    exit;
    
} catch (Throwable $e) {
    error_log('[IA Agente Conversacional] ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    
    $errorHtml = '<div class="ia-response critical-error">';
    $errorHtml .= '<p><strong>❌ Error crítico en el sistema</strong></p>';
    $errorHtml .= '<p>No se pudo procesar tu solicitud en este momento.</p>';
    $errorHtml .= '<ul>';
    $errorHtml .= '<li>Verifica tu conexión a internet</li>';
    $errorHtml .= '<li>Intenta nuevamente en unos minutos</li>';
    $errorHtml .= '<li>Si el problema persiste, contacta a soporte técnico</li>';
    $errorHtml .= '</ul>';
    $errorHtml .= '</div>';
    
    echo json_encode([
        'ok' => false,
        'type' => 'critical_error',
        'html' => $errorHtml,
        'error' => (isset($_GET['debug']) || isset($input['debug'])) ? $e->getMessage() : 'Error interno del sistema'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ==================== FUNCIONES HELPER ====================

/**
 * Genera un plan usando OpenAI (modo PLAN → ACCIONES).
 * La IA devuelve JSON con: mode, actions, response, next_steps.
 */
function generatePlanWithAI(
    string $userMessage,
    array $userContext,
    IAToolsRegistry $toolsRegistry,
    IAConversationStore $conversationStore
): array {
    // 1) Tools disponibles para que la IA sepa qué puede hacer
    $tools      = $toolsRegistry->getToolsForOpenAI();
    $toolsJson  = json_encode($tools, JSON_UNESCAPED_UNICODE);

    // 2) Historial (últimos turnos) para dar contexto de la conversación
    $history    = $conversationStore->getFormattedHistory(8);

    // 3) Contexto del usuario (rol, sucursal, etc.)
    $userInfo   = json_encode($userContext['usuario'] ?? [], JSON_UNESCAPED_UNICODE);

    // 4) Construir prompt de planificación
    $prompt = <<<PROMPT
Eres el agente de IA de KASU. Tu trabajo NO es responder libremente, sino devolver
un PLAN en formato JSON para que el sistema ejecute acciones sobre clientes y prospectos.

CONTEXTO_USUARIO_JSON:
{$userInfo}

TOOLS_DISPONIBLES_JSON (formato OpenAI "function"):
{$toolsJson}

HISTORIAL_DE_CONVERSACION:
{$history}

MENSAJE_ACTUAL_DEL_EJECUTIVO:
"{$userMessage}"

TAREAS POSIBLES (no exhaustivas, son ejemplos):
- Buscar clientes/prospectos por nombre.
- Calcular saldo, mora, próximo pago, fecha de compra de una venta específica.
- Consultar información completa de un cliente (datos, correo, GPS, etc.).
- Enviar correos (póliza, fichas, estado de cuenta, liga de pago).
- Generar cotización para un prospecto.
- Consultar correos ya enviados por la IA a un cliente.
- Responder preguntas generales sobre el cliente (sin ejecutar nada crítico).

MODOS:
1) "tool_sequence": vas a pedir que se ejecuten una o varias tools (máximo 3).
2) "answer_only": NO se ejecutan tools; solo respondes al usuario con lo que ya sabes.

REGLAS:
- Si la petición implica leer/cálcular datos reales (saldo, mora, próximos pagos, etc.),
  usa "mode": "tool_sequence" y añade las tools a "actions".
- Si la petición es solo explicativa ("explícame la diferencia entre X e Y"),
  usa "mode": "answer_only" y deja "actions": [].
- Si la acción MODIFICA algo (enviar correo, actualizar datos), marca "confirm": true.
- Si solo CONSULTA datos, usa "confirm": false.

EJEMPLOS DE PLANES:

1) Usuario: "el cliente Jose Carlos Cabrera Monroy me pide el saldo de su servicio"
→ Debes:
  - Buscar al cliente por nombre.
  - Si tuvieras el ID de venta directamente, podrías planear también calcular el estado de crédito.
Plan ejemplo:
{
  "mode": "tool_sequence",
  "reasoning": "Quiere saldo de un cliente específico, primero busco al cliente.",
  "actions": [
    {
      "tool": "buscar_cliente_prospecto",
      "args": {"nombre": "JOSE CARLOS CABRERA MONROY", "tipo": "cliente"},
      "confirm": false
    }
  ],
  "response": "Voy a buscar al cliente para identificar su venta y calcular su saldo.",
  "next_steps": [
    "Si hay varias ventas, te pediré elegir cuál contrato usar para el cálculo."
  ]
}

2) Usuario: "¿Cuál es la diferencia entre póliza tradicional y póliza familiar?"
→ Solo es explicación:
{
  "mode": "answer_only",
  "reasoning": "Es una pregunta conceptual, no necesita consultar BD.",
  "actions": [],
  "response": "Explicación clara de las diferencias...",
  "next_steps": []
}

DEVUELVE SIEMPRE SOLO UN JSON VÁLIDO con la siguiente estructura mínima:

{
  "mode": "tool_sequence" | "answer_only",
  "reasoning": "texto",
  "actions": [
    {
      "tool": "nombre_de_la_tool",
      "args": { ... },
      "confirm": true | false
    }
  ],
  "response": "texto para el ejecutivo",
  "next_steps": ["opcional", "lista de sugerencias"]
}

No agregues explicaciones fuera del JSON.
PROMPT;

    // 5) Llamar a OpenAI usando tu helper actual
    // openai_simple_text DEBE devolver solo el texto de la IA.
    $responseText = openai_simple_text($prompt, 1200);

    // 6) Parsear el JSON devuelto
    $jsonStart = strpos($responseText, '{');
    $jsonEnd   = strrpos($responseText, '}');

    if ($jsonStart === false || $jsonEnd === false) {
        throw new RuntimeException('La IA no devolvió un JSON válido para el plan.');
    }

    $json = substr($responseText, $jsonStart, $jsonEnd - $jsonStart + 1);
    $plan = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($plan)) {
        throw new RuntimeException('Error parseando el JSON del plan de la IA.');
    }

    // 7) Validar y limpiar el plan para que no pida tools inexistentes
    return validateAndEnhancePlan($plan, $tools);
}


/**
 * Genera un plan básico sin IA (fallback)
 */
function generateBasicPlan(string $userMessage, array $userContext): array {
    $userMessageLower = mb_strtolower($userMessage, 'UTF-8');

    $actions   = [];
    $response  = 'Procesando tu solicitud...';
    $nextSteps = [];
    $meta      = [];

    // --------------------------------------------------------------
    // 1) Caso especial: "el cliente X me pide saber cuánto debe / saldo"
    // --------------------------------------------------------------
    $pideSaldo = preg_match('/(cu[aá]nto\s+debe|saldo|adeudo|estado\s+de\s+cuenta)/iu', $userMessageLower);
    $mencionaCliente = preg_match('/\bcliente\b/iu', $userMessageLower);

    if ($pideSaldo && $mencionaCliente) {
        // Intentar extraer el nombre "limpiando" palabras comunes
        $nombreLimpio = preg_replace(
            '/\b(el|la|cliente|me|pide|piden|pidi[oó]|saber|sepa|que|le|les|cu[aá]nto|cuanto|debe|deben|saldo|adeudo|de|su|s[uú]|\bp[oó]liza\b|\bpoliza\b|\bpago\b|\bpagar\b)\b/iu',
            '',
            $userMessage
        );
        $nombreLimpio = trim(preg_replace('/\s+/', ' ', $nombreLimpio));

        if (mb_strlen($nombreLimpio, 'UTF-8') > 2) {
            // Acción 1: buscar al cliente por nombre
            $actions[] = [
                'tool'    => 'buscar_cliente_prospecto',
                'args'    => ['nombre' => $nombreLimpio, 'tipo' => 'cliente'],
                'confirm' => false,
            ];

            // Activar pipeline automático: después de buscar, calcular saldo
            $meta['auto_credit_after_search'] = true;

            $response = "Voy a buscar al cliente {$nombreLimpio} y, en cuanto lo localice, "
                      . "calcularé automáticamente el saldo de su póliza para decirte cuánto debe.";

            $nextSteps = [
                'Si el cliente tiene varias ventas, te mostraré los contratos para elegir el correcto.',
                'Si sólo tiene una venta activa, calcularé directamente el estado de crédito y el saldo pendiente.',
                'Después, si quieres, puedo enviarle por correo su estado de cuenta o la póliza.'
            ];

            return [
                'reasoning'  => 'Detectada intención: saber cuánto debe un cliente específico (saldo de póliza).',
                'actions'    => $actions,
                'response'   => $response,
                'next_steps' => $nextSteps,
                'meta'       => $meta,
            ];
        }
    }

    // --------------------------------------------------------------
    // 2) Patrones para estado de crédito cuando SÍ viene un ID de venta
    //    (ej: "cuánto debe la venta 123", "saldo de la venta 456")
    // --------------------------------------------------------------
    if (preg_match('/\b(cu[aá]nto\s+debe|saldo|adeudo|estado\s+de\s+cuenta)\b.*\b(venta|id)\s*(\d+)/iu', $userMessage, $matches)) {
        if (isset($matches[3])) {
            $idVenta = (int)$matches[3];
            $actions[] = [
                'tool'    => 'calcular_estado_credito',
                'args'    => ['id_venta' => $idVenta],
                'confirm' => false,
            ];
            $response = "Calculando el estado de crédito y el saldo pendiente de la venta #{$idVenta}.";

            return [
                'reasoning'  => 'Detectada intención: calcular saldo/estado de crédito de una venta por ID.',
                'actions'    => $actions,
                'response'   => $response,
                'next_steps' => [
                    'Si necesitas enviar el estado de cuenta o la póliza, pídelo después indicando esta misma venta.'
                ],
                'meta'       => $meta,
            ];
        }
    }

    // --------------------------------------------------------------
    // 3) Patrones para enviar correo (poliza, fichas, estado de cuenta, liga)
    // --------------------------------------------------------------
    if (preg_match('/\b(env[ií]a|envia|manda|mandar)\b.*\b(p[oó]liza|poliza|fichas|estado\s+de\s+cuenta|liga\s+de\s+pago|liga\b|link\b)\b.*\b(venta|id)\s*(\d+)/iu', $userMessage, $matches)) {
        if (isset($matches[4])) {
            $idVenta = (int)$matches[4];
            $tipoCorreo = 'poliza';

            if (stripos($userMessage, 'fichas') !== false) {
                $tipoCorreo = 'fichas';
            } elseif (stripos($userMessageLower, 'estado de cuenta') !== false) {
                $tipoCorreo = 'estado_cuenta';
            } elseif (stripos($userMessageLower, 'liga') !== false || stripos($userMessageLower, 'pago') !== false || stripos($userMessageLower, 'link') !== false) {
                $tipoCorreo = 'liga_pago';
            }

            $actions[] = [
                'tool'    => 'enviar_correo_cliente',
                'args'    => ['id_venta' => $idVenta, 'tipo_correo' => $tipoCorreo],
                'confirm' => true, // SIEMPRE pide confirmación para enviar correos
            ];
            $response = "Preparando el envío de {$tipoCorreo} para la venta #{$idVenta}. "
                      . "Te pediré confirmación antes de enviar el correo.";

            return [
                'reasoning'  => 'Detectada intención: enviar correo asociado a una venta (póliza/fichas/estado/liga).',
                'actions'    => $actions,
                'response'   => $response,
                'next_steps' => [
                    'Confirma el envío cuando veas el detalle de la acción en la pantalla.'
                ],
                'meta'       => $meta,
            ];
        }
    }

    // --------------------------------------------------------------
    // 4) Patrones generales de búsqueda de cliente/prospecto
    // --------------------------------------------------------------
    if (preg_match('/\b(busca|buscar|localiza|encontrar|muestra|mu[eé]strame)\b.*\b(cliente|prospecto|persona)\b/iu', $userMessage)) {
        // Intentar extraer nombre removiendo verbos y conectores
        $nombre = preg_replace(
            '/\b(busca|buscar|localiza|encontrar|muestra|mu[eé]strame|cliente|prospecto|por|favor|a|al|la|el)\b/iu',
            '',
            $userMessage
        );
        $nombre = trim(preg_replace('/\s+/', ' ', $nombre));

        if (mb_strlen($nombre, 'UTF-8') > 2) {
            $actions[] = [
                'tool'    => 'buscar_cliente_prospecto',
                'args'    => ['nombre' => $nombre, 'tipo' => 'ambos'],
                'confirm' => false,
            ];
            $response = "Buscando información para: {$nombre}";

            return [
                'reasoning'  => 'Detectada intención: búsqueda de cliente/prospecto por nombre.',
                'actions'    => $actions,
                'response'   => $response,
                'next_steps' => [
                    'Si el resultado tiene varias coincidencias, escoge cuál es la correcta.',
                ],
                'meta'       => $meta,
            ];
        }
    }

    // --------------------------------------------------------------
    // 5) Fallback: asumir que es búsqueda por nombre genérica
    // --------------------------------------------------------------
    $nombre = preg_replace(
        '/\b(busca|buscar|localiza|dame|informaci[oó]n|info|de|del|la|el|los|las|cliente|prospecto|por|favor)\b/iu',
        '',
        $userMessage
    );
    $nombre = trim(preg_replace('/\s+/', ' ', $nombre));

    if (mb_strlen($nombre, 'UTF-8') > 2) {
        $actions[] = [
            'tool'    => 'buscar_cliente_prospecto',
            'args'    => ['nombre' => $nombre, 'tipo' => 'ambos'],
            'confirm' => false,
        ];
        $response = "Buscando: {$nombre}";

        return [
            'reasoning'  => 'Fallback: interpretado como búsqueda de cliente/prospecto por nombre.',
            'actions'    => $actions,
            'response'   => $response,
            'next_steps' => [
                'Si no era una búsqueda de cliente, aclara qué necesitas (por ejemplo: “recomiéndame cómo mejorar mis ventas este mes”).',
            ],
            'meta'       => $meta,
        ];
    }

    // --------------------------------------------------------------
    // 6) Último recurso: sin acciones claras
    // --------------------------------------------------------------
    return [
        'reasoning'  => 'No se detectó una acción clara; se requiere más contexto.',
        'actions'    => [],
        'response'   => 'No detecté si quieres que busque a alguien, calcule un saldo o envíe un correo. Dime, por ejemplo: "el cliente Juan Pérez me pide saber cuánto debe" o "envía la póliza de la venta 123".',
        'next_steps' => [],
        'meta'       => $meta,
    ];
}

/**
 * Construye prompt para planificación con IA
 */
function buildPlanningPrompt(
    string $userMessage,
    array $userContext,
    string $toolsJson,
    string $history
): string {
    $userInfo = json_encode($userContext['usuario'], JSON_UNESCAPED_UNICODE);
    
    return <<<PROMPT
Eres el agente de IA conversacional de KASU, una plataforma de servicios funerarios a futuro.

CONTEXTO DEL USUARIO (JSON):
{$userInfo}

HERRAMIENTAS DISPONIBLES (JSON array):
{$toolsJson}

HISTORIAL DE CONVERSACIÓN:
{$history}

MENSAJE ACTUAL DEL USUARIO:
"{$userMessage}"

---

INSTRUCCIONES:
1. ANALIZA el mensaje del usuario
2. SELECCIONA las herramientas adecuadas (pueden ser varias)
3. GENERA un plan de acciones

EJEMPLOS:
Usuario: "Busca a Juan Pérez"
→ Acciones: [{"tool": "buscar_cliente_prospecto", "args": {"nombre": "Juan Pérez"}, "confirm": false}]

Usuario: "¿Cuánto debe la venta 123?"
→ Acciones: [{"tool": "calcular_estado_credito", "args": {"id_venta": 123}, "confirm": false}]

Usuario: "Envía la póliza de la venta 456"
→ Acciones: [{"tool": "enviar_correo_cliente", "args": {"id_venta": 456, "tipo_correo": "poliza"}, "confirm": true}]

REGLAS:
- Si la acción MODIFICA datos (envía correo), usa "confirm": true
- Si solo CONSULTA, usa "confirm": false
- Máximo 3 acciones

RESPONDER EN FORMATO JSON:

{
  "reasoning": "Explicación breve",
  "actions": [
    {"tool": "nombre_tool", "args": {...}, "confirm": true/false}
  ],
  "response": "Respuesta al usuario",
  "next_steps": ["Sugerencia 1"]
}
PROMPT;
}

/**
 * Parsea la respuesta de IA a un plan
 */
function parseAIPlanResponse(string $responseText): array {
    // Intentar extraer JSON
    $jsonStart = strpos($responseText, '{');
    $jsonEnd = strrpos($responseText, '}');
    
    if ($jsonStart === false || $jsonEnd === false) {
        throw new RuntimeException('La IA no devolvió un JSON válido');
    }
    
    $json = substr($responseText, $jsonStart, $jsonEnd - $jsonStart + 1);
    $plan = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($plan)) {
        throw new RuntimeException('Error parseando JSON de la IA');
    }
    
    return $plan;
}

/**
 * Valida y mejora el plan
 */
function validateAndEnhancePlan(array $plan, array $availableTools): array {
    // Estructura mínima
    if (!isset($plan['actions']) || !is_array($plan['actions'])) {
        $plan['actions'] = [];
    }
    
    if (!isset($plan['reasoning'])) {
        $plan['reasoning'] = 'Analizando la solicitud...';
    }
    
    if (!isset($plan['response'])) {
        $plan['response'] = 'Procesando tu solicitud...';
    }
    
    // Validar cada acción
    $validActions = [];
    $toolNames = [];
    foreach ($availableTools as $tool) {
        if (isset($tool['function']['name'])) {
            $toolNames[] = $tool['function']['name'];
        }
    }
    
    foreach ($plan['actions'] as $action) {
        if (!is_array($action) || !isset($action['tool'])) {
            continue;
        }
        
        $toolName = $action['tool'];
        
        // Verificar que la tool exista
        if (!in_array($toolName, $toolNames)) {
            error_log("[IA Plan] Tool no disponible: {$toolName}");
            continue;
        }
        
        // Asegurar estructura básica
        $validAction = [
            'tool' => $toolName,
            'args' => $action['args'] ?? [],
            'confirm' => (bool)($action['confirm'] ?? false)
        ];
        
        $validActions[] = $validAction;
    }
    
    $plan['actions'] = $validActions;
    
    // Limitar a 3 acciones
    if (count($plan['actions']) > 3) {
        $plan['actions'] = array_slice($plan['actions'], 0, 3);
    }
    
    return $plan;
}

/**
 * Detecta si el mensaje del usuario es una petición explícita
 * de recomendación / mejora de desempeño para Vista 360.
 */
function iaDetectRecomendacionIntent(string $msg): bool
{
    $msgLower = mb_strtolower($msg, 'UTF-8');

    // Palabras clave de intención
    $keywords = [
        'recomendación ia',
        'recomendacion ia',
        'recomendación',
        'recomendacion',
        'mejorar mis ventas',
        'mejorar mi ventas',
        'mejorar mi cobranza',
        'mejorar mi desempeño',
        'mejorar mi desempeno',
        'mejorar mis resultados',
        'mejorar mi resultado',
        'mejorar en mi puesto',
        'cómo puedo mejorar',
        'como puedo mejorar',
        'mejora mi experiencia',
        'mejora mi trabajo',
        'mejorar con vista 360',
        'mejorar con vista360',
        'recomendación para este mes',
        'recomendacion para este mes',
    ];

    foreach ($keywords as $kw) {
        if (strpos($msgLower, $kw) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Llama al endpoint vista360_chat_acciones.php para obtener
 * una recomendación de desempeño y la adapta al formato del chat.
 */
function iaCallVista360Recomendacion(string $userMessage, array $userContext): array
{
    $path    = '/eia/Vista-360/vista360_chat_acciones.php';
    $docRoot = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $local   = $docRoot . $path;

    if (!is_file($local)) {
        return [
            'ok'   => false,
            'type' => 'recommendation_error',
            'html' => '<p>No encontré el módulo de recomendación de IA en el servidor.</p>',
            'error' => 'Archivo no encontrado: ' . $local,
        ];
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $url    = $scheme . $host . $path;

    // Mensaje que verá vista360_chat_acciones.php como "mensaje" del usuario
    $payload = [
        'mensaje' =>
            'Genera una recomendación breve y accionable para mejorar mi desempeño este mes ' .
            'en ventas, cobranza y prospección en Vista 360. Mensaje original del usuario: "' .
            $userMessage . '".',
    ];

    $ch = curl_init($url);
    if ($ch === false) {
        return [
            'ok'   => false,
            'type' => 'recommendation_error',
            'html' => '<p>No se pudo inicializar la conexión con la recomendación de IA.</p>',
            'error' => 'No se pudo inicializar cURL',
        ];
    }

    $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => $jsonPayload,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
    ]);

    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false) {
        return [
            'ok'   => false,
            'type' => 'recommendation_error',
            'html' => '<p>No fue posible obtener la recomendación de IA en este momento.</p>',
            'error' => 'Error cURL: ' . $err,
        ];
    }

    $data = json_decode($resp, true);
    if (!is_array($data)) {
        return [
            'ok'   => false,
            'type' => 'recommendation_error',
            'html' => '<p>No fue posible interpretar la recomendación de IA.</p>',
            'error' => 'Respuesta no JSON desde vista360_chat_acciones.php (HTTP ' . $code . ')',
        ];
    }

    // Adaptar al formato estándar del chat: ok + html
    $html = (string)($data['html'] ?? '');
    if ($html === '') {
        $html = '<p>No fue posible obtener la recomendación de IA en este momento.</p>';
    }

    return [
        'ok'          => !empty($data['ok']),
        'type'        => 'recommendation',
        'html'        => $html,
        'source'      => 'vista360_chat_acciones',
        'tool_result' => $data,
        'timestamp'   => date('Y-m-d H:i:s'),
    ];
}
