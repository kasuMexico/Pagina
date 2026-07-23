<?php
declare(strict_types=1);

/**
 * Endpoint publico para chat de ventas/atencion KASU.
 * Usa herramientas seguras para prospectos, cotizacion, agenda y FAQs.
 */

header('Content-Type: application/json; charset=utf-8');

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Rate-limiting: 15 mensajes por IP cada 60 segundos
    $rateLimitKey = 'kasu_chat_rl_' . ($_SERVER['REMOTE_ADDR'] ?? 'cli');
    $rateLimitWindow = 60;
    $rateLimitMax = 15;
    $now = time();
    $rlData = $_SESSION[$rateLimitKey] ?? ['count' => 0, 'reset' => $now + $rateLimitWindow];
    if ($now > $rlData['reset']) {
        $rlData = ['count' => 0, 'reset' => $now + $rateLimitWindow];
    }
    $rlData['count']++;
    $_SESSION[$rateLimitKey] = $rlData;
    if ($rlData['count'] > $rateLimitMax) {
        http_response_code(429);
        echo json_encode(['ok' => false, 'error' => 'Demasiadas solicitudes. Espera unos segundos.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    require_once __DIR__ . '/../librerias.php';
    require_once __DIR__ . '/../php/Telcto.php';
    require_once __DIR__ . '/../Funciones/Funciones_Financieras.php';
    require_once __DIR__ . '/ia_conversation_store.php';
    require_once __DIR__ . '/ia_tools_registry.php';

    $openaiAvailable = false;
    $openaiConfigFile = __DIR__ . '/openai_config.php';
    if (is_file($openaiConfigFile)) {
        require_once $openaiConfigFile;
        $openaiAvailable = function_exists('openai_simple_text');
    }

    $correoHelperFile = __DIR__ . '/ia_tools_correo.php';
    if (is_file($correoHelperFile)) {
        require_once $correoHelperFile;
    }

    global $mysqli, $pros, $basicas, $seguridad, $financieras;

    // Si el chat ya fue transferido a un agente humano, rechazar mensajes
    if (!empty($_SESSION['kasu_transferido_humano'])) {
        $ticketCode = $_SESSION['kasu_ticket_code'] ?? 'desconocido';
        http_response_code(200);
        echo json_encode([
            'ok' => true,
            'type' => 'transferred',
            'html' => '<p>Este chat esta siendo atendido por un agente humano. Tu ticket es <strong>' . htmlspecialchars($ticketCode, ENT_QUOTES, 'UTF-8') . '</strong>. Un agente te contactara pronto.</p>',
            'data' => ['results' => []],
            'chat_token' => '',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $contactPhone = '';
    if (isset($tel)) {
        $contactPhone = preg_replace('/\D+/', '', (string)$tel);
    }
    if ($contactPhone === '') {
        $contactPhone = '7208177632';
    }

    if (!$mysqli) {
        throw new RuntimeException('Conexion principal no disponible.');
    }
    if (!$pros) {
        throw new RuntimeException('Conexion prospectos no disponible.');
    }

    if (!isset($financieras) || !($financieras instanceof Financieras)) {
        $financieras = new Financieras();
    }

    $raw = file_get_contents('php://input');
    $input = $raw ? json_decode($raw, true) : [];
    if (!is_array($input)) {
        $input = [];
    }

    $userMessage = trim((string)($input['mensaje'] ?? ''));
    $source = trim((string)($input['source'] ?? ''));
    $chatToken = trim((string)($input['chat_token'] ?? ''));

    if ($userMessage === '') {
        throw new InvalidArgumentException('Mensaje requerido.');
    }

    $conversationStore = new IAConversationStore('kasu_public_chat', 25);
    $conversationStore->addTurn('user', $userMessage, ['source' => $source]);

    $chatDb = $pros;
    $token = kasu_normalize_token($chatToken);
    if ($token === '') {
        $token = kasu_generate_token();
    }
    kasu_chat_init_db($chatDb);
    $sessionData = kasu_chat_load_session($chatDb, $token);

    if (!isset($_SESSION['kasu_public_context']) || !is_array($_SESSION['kasu_public_context'])) {
        $_SESSION['kasu_public_context'] = [];
    }
    $contextSeed = $_SESSION['kasu_public_context'];
    $contextSeed['source'] = $source !== '' ? $source : ($contextSeed['source'] ?? 'public_chat');
    if (!empty($sessionData['context'])) {
        $contextSeed = array_merge($sessionData['context'], $contextSeed);
    }
    $publicContext = kasu_update_public_context($userMessage, $contextSeed);
    $publicContext['contact_phone'] = $contactPhone;
    $publicContext['chat_token'] = $token;
    if (!empty($sessionData['last_openai_id'])) {
        $publicContext['last_openai_id'] = $sessionData['last_openai_id'];
    }
    $_SESSION['kasu_public_context'] = $publicContext;

    $toolsRegistry = new IAToolsRegistry();
    $allowedTools = [];

    $registerTool = function (string $name, array $schema, callable $executor) use ($toolsRegistry, &$allowedTools) {
        $toolsRegistry->registerTool($name, $schema, $executor);
        $allowedTools[] = $name;
    };

    $registerTool('consultar_cliente', [
        'description' => 'Consulta datos basicos de un cliente por CURP y poliza (IdFIrma).',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'curp' => ['type' => 'string'],
                'poliza' => ['type' => 'string'],
            ],
            'required' => ['curp', 'poliza'],
        ],
    ], function (array $args) use ($mysqli, $basicas, $financieras): array {
        $curp = strtoupper(trim((string)($args['curp'] ?? '')));
        $poliza = strtoupper(trim((string)($args['poliza'] ?? '')));

        if ($curp === '' || $poliza === '') {
            return ['ok' => false, 'error' => 'CURP y poliza requeridas.'];
        }

        $idContact = (int)$basicas->BuscarCampos($mysqli, 'IdContact', 'Usuario', 'ClaveCurp', $curp);
        if ($idContact <= 0) {
            return ['ok' => false, 'error' => 'CURP no registrada.'];
        }

        $idVenta = (int)$basicas->BuscarCampos($mysqli, 'Id', 'Venta', 'IdContact', $idContact);
        if ($idVenta <= 0) {
            return ['ok' => false, 'error' => 'No se encontro venta asociada.'];
        }

        $firma = (string)$basicas->BuscarCampos($mysqli, 'IdFIrma', 'Venta', 'Id', $idVenta);
        if (strcasecmp($firma, $poliza) !== 0) {
            return ['ok' => false, 'error' => 'Numero de poliza no coincide.'];
        }

        $status = (string)$basicas->BuscarCampos($mysqli, 'Status', 'Venta', 'Id', $idVenta);
        $producto = (string)$basicas->BuscarCampos($mysqli, 'Producto', 'Venta', 'Id', $idVenta);
        $tipoServicio = (string)$basicas->BuscarCampos($mysqli, 'TipoServicio', 'Venta', 'Id', $idVenta);

        $out = [
            'ok' => true,
            'id_contacto' => $idContact,
            'id_venta' => $idVenta,
            'status' => $status,
            'producto' => $producto,
            'tipo_servicio' => $tipoServicio,
        ];

        if ($status === 'COBRANZA' || $status === 'PREVENTA') {
            $pagos = (float)$financieras->SumarPagos($mysqli, 'Cantidad', 'Pagos', 'IdVenta', $idVenta);
            $pendiente = (float)$financieras->SaldoCredito($mysqli, $idVenta);
            $out['pagos_realizados'] = $pagos;
            $out['pendiente'] = $pendiente;
        }

        $out['poliza_pdf'] = 'https://kasu.com.mx/login/Generar_PDF/Poliza_pdf.php?busqueda=' .
            rawurlencode(base64_encode((string)$idContact));
        $out['mi_cuenta'] = 'https://kasu.com.mx/ActualizacionDatos/index.php?value=' .
            rawurlencode(base64_encode((string)$idContact));

        return $out;
    });

    $registerTool('consultar_cliente_curp', [
        'description' => 'Consulta nombre basico de cliente por CURP para confirmar identidad.',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'curp' => ['type' => 'string'],
            ],
            'required' => ['curp'],
        ],
    ], function (array $args) use ($mysqli): array {
        $curp = strtoupper(trim((string)($args['curp'] ?? '')));
        if ($curp === '') {
            return ['ok' => false, 'error' => 'CURP requerida.'];
        }

        $stmt = $mysqli->prepare('SELECT IdContact, Nombre, Paterno, Materno FROM Usuario WHERE ClaveCurp = ? ORDER BY Id DESC LIMIT 1');
        if (!$stmt) {
            return ['ok' => false, 'error' => 'No se pudo preparar la consulta.'];
        }
        $stmt->bind_param('s', $curp);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc() ?: [];
        $stmt->close();

        if (!$row) {
            return ['ok' => false, 'error' => 'CURP no registrada.'];
        }

        $nombre = trim((string)($row['Nombre'] ?? '') . ' ' . (string)($row['Paterno'] ?? '') . ' ' . (string)($row['Materno'] ?? ''));
        $idContact = (int)($row['IdContact'] ?? 0);
        if ($nombre === '') {
            $nombre = 'Cliente KASU';
        }

        $polizaPdf = '';
        if ($idContact > 0) {
            $polizaPdf = 'https://kasu.com.mx/login/Generar_PDF/Poliza_pdf.php?busqueda=' .
                rawurlencode(base64_encode((string)$idContact));
        }

        return [
            'ok' => true,
            'id_contacto' => $idContact,
            'nombre' => $nombre,
            'poliza_pdf' => $polizaPdf,
        ];
    });

    $registerTool('enviar_poliza_correo', [
        'description' => 'Envia poliza al correo registrado del cliente usando CURP y poliza.',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'curp' => ['type' => 'string'],
                'poliza' => ['type' => 'string'],
            ],
            'required' => ['curp', 'poliza'],
        ],
    ], function (array $args) use ($mysqli, $basicas): array {
        if (!function_exists('ia_tool_enviar_poliza')) {
            return ['ok' => false, 'error' => 'Helper de correo no disponible.'];
        }

        $curp = strtoupper(trim((string)($args['curp'] ?? '')));
        $poliza = strtoupper(trim((string)($args['poliza'] ?? '')));
        if ($curp === '' || $poliza === '') {
            return ['ok' => false, 'error' => 'CURP y poliza requeridas.'];
        }

        $idContact = (int)$basicas->BuscarCampos($mysqli, 'IdContact', 'Usuario', 'ClaveCurp', $curp);
        if ($idContact <= 0) {
            return ['ok' => false, 'error' => 'CURP no registrada.'];
        }

        $idVenta = (int)$basicas->BuscarCampos($mysqli, 'Id', 'Venta', 'IdContact', $idContact);
        if ($idVenta <= 0) {
            return ['ok' => false, 'error' => 'No se encontro venta asociada.'];
        }

        $firma = (string)$basicas->BuscarCampos($mysqli, 'IdFIrma', 'Venta', 'Id', $idVenta);
        if (strcasecmp($firma, $poliza) !== 0) {
            return ['ok' => false, 'error' => 'Numero de poliza no coincide.'];
        }

        $envio = ia_tool_enviar_poliza($idVenta);
        $envio['id_venta'] = $idVenta;
        return $envio;
    });

    $registerTool('cotizar_producto', [
        'description' => 'Cotiza un producto segun edad/curp y servicio (funerario, seguridad, transporte).',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'servicio' => ['type' => 'string'],
                'curp' => ['type' => 'string'],
                'fecha_nacimiento' => ['type' => 'string'],
                'edad' => ['type' => 'integer'],
                'plazo_meses' => ['type' => 'integer'],
            ],
            'required' => ['servicio'],
        ],
    ], function (array $args) use ($mysqli, $basicas, $financieras): array {
        $servicio = strtoupper(trim((string)($args['servicio'] ?? '')));
        $curp = strtoupper(trim((string)($args['curp'] ?? '')));
        $fechaNac = trim((string)($args['fecha_nacimiento'] ?? ''));
        $edadArg = (int)($args['edad'] ?? 0);
        $plazo = (int)($args['plazo_meses'] ?? 1);
        if ($plazo <= 0) {
            $plazo = 1;
        }

        $edad = 0;
        if ($edadArg > 0) {
            $edad = $edadArg;
        } elseif ($curp !== '' && method_exists($basicas, 'ObtenerEdad')) {
            $edad = (int)$basicas->ObtenerEdad($curp);
        } elseif ($fechaNac !== '') {
            $edad = kasu_calcular_edad($fechaNac);
        }

        if ($edad <= 0) {
            return ['ok' => false, 'error' => 'No se pudo determinar la edad.'];
        }

        $producto = '';
        if ($servicio === 'FUNERARIO' || $servicio === 'GASTOS FUNERARIOS') {
            $producto = (string)$basicas->ProdFune($edad);
        } elseif ($servicio === 'SEGURIDAD' || $servicio === 'POLICIAS' || $servicio === 'POLICIA') {
            $producto = (string)$basicas->ProdPli($edad);
        } elseif ($servicio === 'TRANSPORTE' || $servicio === 'TAXI' || $servicio === 'TRANSPORTISTAS') {
            $producto = (string)$basicas->ProdTrans($edad);
        }

        if ($producto === '' || $producto === '<70') {
            return ['ok' => false, 'error' => 'Edad fuera de rango para cotizar.'];
        }

        $stmt = $mysqli->prepare('SELECT Costo, TasaAnual, MaxCredito, Validez FROM Productos WHERE Producto = ? LIMIT 1');
        if (!$stmt) {
            return ['ok' => false, 'error' => 'No se pudo preparar la consulta del producto.'];
        }
        $stmt->bind_param('s', $producto);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc() ?: [];
        $stmt->close();

        if (!$row) {
            return ['ok' => false, 'error' => 'No se encontro el producto.'];
        }

        $costo = (float)($row['Costo'] ?? 0);
        $tasaAnual = (float)($row['TasaAnual'] ?? 0);
        $maxCredito = (int)($row['MaxCredito'] ?? 0);
        $validez = (string)($row['Validez'] ?? '');

        if ($costo <= 0) {
            return ['ok' => false, 'error' => 'Costo no disponible.'];
        }

        $respuesta = [
            'ok' => true,
            'producto' => $producto,
            'edad' => $edad,
            'costo_contado' => round($costo, 2),
            'tasa_anual' => $tasaAnual,
            'max_credito' => $maxCredito,
            'validez' => $validez,
        ];

        if ($plazo > 1) {
            $ajustado = false;
            $plazoOriginal = $plazo;
            if ($maxCredito > 0 && $plazo > $maxCredito) {
                $plazo = $maxCredito;
                $ajustado = true;
            }
            $mensualidad = (float)$financieras->PagoSI($tasaAnual, $plazo, $costo);
            $totalFinanciado = round($mensualidad * $plazo, 2);
            $respuesta['plazo_meses'] = $plazo;
            $respuesta['mensualidad'] = $mensualidad;
            $respuesta['total_financiado'] = $totalFinanciado;
            if ($ajustado) {
                $respuesta['plazo_ajustado'] = true;
                $respuesta['plazo_solicitado'] = $plazoOriginal;
                $respuesta['mensaje'] = 'El plazo solicitado excede el maximo. Se uso el maximo permitido.';
            }
        }

        return $respuesta;
    });

    $registerTool('crear_prospecto', [
        'description' => 'Crea o reutiliza un prospecto con datos minimos.',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'nombre' => ['type' => 'string'],
                'fecha_nacimiento' => ['type' => 'string'],
                'curp' => ['type' => 'string'],
                'telefono' => ['type' => 'string'],
                'email' => ['type' => 'string'],
                'servicio_interes' => ['type' => 'string'],
                'origen' => ['type' => 'string'],
            ],
            'required' => ['nombre'],
        ],
    ], function (array $args) use ($pros, $basicas, $seguridad): array {
        $nombre = trim((string)($args['nombre'] ?? ''));
        $fechaNac = trim((string)($args['fecha_nacimiento'] ?? ''));
        $curp = strtoupper(trim((string)($args['curp'] ?? '')));
        $telefono = preg_replace('/\D+/', '', (string)($args['telefono'] ?? ''));
        $email = strtolower(trim((string)($args['email'] ?? '')));
        $servicio = strtoupper(trim((string)($args['servicio_interes'] ?? '')));
        if ($servicio === '') {
            $servicio = strtoupper(trim((string)($args['servicio'] ?? '')));
        }
        $origen = trim((string)($args['origen'] ?? 'CHAT-IA'));

        if ($nombre === '') {
            return ['ok' => false, 'error' => 'Nombre requerido.'];
        }
        if ($telefono === '' && $email === '') {
            return ['ok' => false, 'error' => 'Telefono o correo requerido.'];
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'Correo invalido.'];
        }
        if ($telefono !== '' && strlen($telefono) !== 10) {
            return ['ok' => false, 'error' => 'Telefono invalido (10 digitos).'];
        }

        if ($curp !== '' && $fechaNac === '' && isset($seguridad)) {
            $datosCurp = $seguridad->peticion_get($curp);
            if (is_array($datosCurp) && ($datosCurp['Response'] ?? '') === 'correct') {
                $fechaNac = (string)($datosCurp['FechaNac'] ?? '');
                $nombre = trim((string)($datosCurp['Nombre'] ?? '') . ' ' . (string)($datosCurp['Paterno'] ?? '') . ' ' . (string)($datosCurp['Materno'] ?? ''));
            }
        }

        $idExistente = 0;
        if ($email !== '') {
            $idExistente = (int)$basicas->BuscarCampos($pros, 'Id', 'prospectos', 'Email', $email);
        }
        if ($idExistente <= 0 && $telefono !== '') {
            $idExistente = (int)$basicas->BuscarCampos($pros, 'Id', 'prospectos', 'NoTel', $telefono);
        }

        if ($idExistente > 0) {
            return ['ok' => true, 'id_prospecto' => $idExistente, 'existing' => true];
        }

        $data = [
            'FullName' => $nombre,
            'Curp' => $curp,
            'NoTel' => $telefono,
            'Email' => $email,
            'FechaNac' => $fechaNac,
            'Servicio_Interes' => $servicio,
            'Origen' => $origen,
            'Papeline' => 'Prospeccion',
            'PosPapeline' => 1,
            'Sugeridos' => 0,
            'Cancelacion' => 0,
            'Automatico' => 1,
            'Alta' => date('Y-m-d H:i:s'),
        ];

        $idProspecto = $basicas->InsertCampo($pros, 'prospectos', $data);
        if (!is_numeric($idProspecto)) {
            return ['ok' => false, 'error' => 'No se pudo crear el prospecto.'];
        }

        return ['ok' => true, 'id_prospecto' => (int)$idProspecto, 'existing' => false];
    });

    $registerTool('enviar_cotizacion_correo', [
        'description' => 'Crea cotizacion y la envia por correo.',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'nombre' => ['type' => 'string'],
                'fecha_nacimiento' => ['type' => 'string'],
                'curp' => ['type' => 'string'],
                'telefono' => ['type' => 'string'],
                'email' => ['type' => 'string'],
                'servicio' => ['type' => 'string'],
                'plazo_meses' => ['type' => 'integer'],
                'origen' => ['type' => 'string'],
            ],
            'required' => ['nombre', 'email', 'servicio'],
        ],
    ], function (array $args) use ($pros, $mysqli, $basicas, $financieras, $seguridad): array {
        if (!function_exists('ia_tool_enviar_cotizacion')) {
            return ['ok' => false, 'error' => 'Helper de correo no disponible.'];
        }

        $nombre = trim((string)($args['nombre'] ?? ''));
        $email = strtolower(trim((string)($args['email'] ?? '')));
        $telefono = preg_replace('/\D+/', '', (string)($args['telefono'] ?? ''));
        $curp = strtoupper(trim((string)($args['curp'] ?? '')));
        $fechaNac = trim((string)($args['fecha_nacimiento'] ?? ''));
        $servicio = strtoupper(trim((string)($args['servicio'] ?? '')));
        if ($servicio === '') {
            $servicio = strtoupper(trim((string)($args['servicio_interes'] ?? '')));
        }
        $plazo = (int)($args['plazo_meses'] ?? 1);
        $origen = trim((string)($args['origen'] ?? 'CHAT-IA'));

        if ($nombre === '' || $email === '') {
            return ['ok' => false, 'error' => 'Nombre y correo son requeridos.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'Correo invalido.'];
        }
        if ($telefono !== '' && strlen($telefono) !== 10) {
            return ['ok' => false, 'error' => 'Telefono invalido (10 digitos).'];
        }
        if ($plazo <= 0) {
            $plazo = 1;
        }

        $edad = 0;
        if ($curp !== '' && method_exists($basicas, 'ObtenerEdad')) {
            $edad = (int)$basicas->ObtenerEdad($curp);
        } elseif ($fechaNac !== '') {
            $edad = kasu_calcular_edad($fechaNac);
        }

        if ($edad <= 0) {
            return ['ok' => false, 'error' => 'No se pudo determinar la edad.'];
        }

        $producto = '';
        if ($servicio === 'FUNERARIO' || $servicio === 'GASTOS FUNERARIOS') {
            $producto = (string)$basicas->ProdFune($edad);
        } elseif ($servicio === 'SEGURIDAD' || $servicio === 'POLICIAS' || $servicio === 'POLICIA') {
            $producto = (string)$basicas->ProdPli($edad);
        } elseif ($servicio === 'TRANSPORTE' || $servicio === 'TAXI' || $servicio === 'TRANSPORTISTAS') {
            $producto = (string)$basicas->ProdTrans($edad);
        }

        if ($producto === '' || $producto === '<70') {
            return ['ok' => false, 'error' => 'Edad fuera de rango para cotizar.'];
        }

        $idExistente = (int)$basicas->BuscarCampos($pros, 'Id', 'prospectos', 'Email', $email);
        if ($idExistente <= 0 && $telefono !== '') {
            $idExistente = (int)$basicas->BuscarCampos($pros, 'Id', 'prospectos', 'NoTel', $telefono);
        }

        if ($idExistente <= 0) {
            $dataPros = [
                'FullName' => $nombre,
                'Curp' => $curp,
                'NoTel' => $telefono,
                'Email' => $email,
                'FechaNac' => $fechaNac,
                'Servicio_Interes' => $servicio,
                'Origen' => $origen,
                'Papeline' => 'Prospeccion',
                'PosPapeline' => 1,
                'Sugeridos' => 0,
                'Cancelacion' => 0,
                'Automatico' => 1,
                'Alta' => date('Y-m-d H:i:s'),
            ];
            $idExistente = (int)$basicas->InsertCampo($pros, 'prospectos', $dataPros);
        }

        if ($idExistente <= 0) {
            return ['ok' => false, 'error' => 'No se pudo crear el prospecto.'];
        }

        $range = substr($producto, -5);
        $rangos = ['02a29','30a49','50a54','55a59','60a64','65a69'];
        if (!in_array($range, $rangos, true)) {
            return ['ok' => false, 'error' => 'Rango de edad no valido para cotizacion.'];
        }

        $dataCoti = [
            'IdProspecto' => $idExistente,
            'IdUser' => 'PLATAFORMA',
            'SubProducto' => $servicio,
            'a02a29' => 0,
            'a30a49' => 0,
            'a50a54' => 0,
            'a55a59' => 0,
            'a60a64' => 0,
            'a65a69' => 0,
            'Retiro' => 0,
            'Plazo' => $plazo,
            'FechaRegistro' => date('Y-m-d H:i:s'),
        ];
        $dataCoti['a' . $range] = 1;

        $idPresp = (int)$basicas->InsertCampo($pros, 'PrespEnviado', $dataCoti);
        if ($idPresp <= 0) {
            return ['ok' => false, 'error' => 'No se pudo generar la cotizacion.'];
        }

        $envio = ia_tool_enviar_cotizacion($idPresp);
        $envio['id_prospecto'] = $idExistente;
        $envio['id_cotizacion'] = $idPresp;
        return $envio;
    });

    $registerTool('registrar_poliza', [
        'description' => 'Registra una poliza nueva siguiendo el flujo de Registro.',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'curp' => ['type' => 'string'],
                'email' => ['type' => 'string'],
                'telefono' => ['type' => 'string'],
                'codigo_postal' => ['type' => 'string'],
                'servicio' => ['type' => 'string'],
                'tipo_servicio' => ['type' => 'string'],
                'plazo_meses' => ['type' => 'integer'],
                'dia_pago' => ['type' => 'integer'],
                'terminos' => ['type' => 'string'],
                'aviso' => ['type' => 'string'],
                'fideicomiso' => ['type' => 'string'],
                'host' => ['type' => 'string'],
                'id_empleado' => ['type' => 'string'],
                'referencia_kasu' => ['type' => 'string'],
            ],
            'required' => ['curp', 'email', 'telefono', 'codigo_postal', 'servicio', 'plazo_meses', 'terminos', 'aviso', 'fideicomiso'],
        ],
    ], function (array $args) use ($mysqli, $basicas, $seguridad, $financieras): array {
        $curp = strtoupper(trim((string)($args['curp'] ?? '')));
        $emailRaw = strtolower(trim((string)($args['email'] ?? '')));
        $telefonoRaw = preg_replace('/\\D+/', '', (string)($args['telefono'] ?? ''));
        if (strlen($telefonoRaw) === 12 && substr($telefonoRaw, 0, 2) === '52') {
            $telefonoRaw = substr($telefonoRaw, 2);
        }
        $codigoPostal = preg_replace('/\\D+/', '', (string)($args['codigo_postal'] ?? ''));
        $servicio = strtoupper(trim((string)($args['servicio'] ?? '')));
        $tipoServicio = trim((string)($args['tipo_servicio'] ?? ''));
        $plazo = (int)($args['plazo_meses'] ?? 1);
        $diaPago = (int)($args['dia_pago'] ?? 0);
        $terminosRaw = (string)($args['terminos'] ?? '');
        $avisoRaw = (string)($args['aviso'] ?? '');
        $fideRaw = (string)($args['fideicomiso'] ?? '');
        $host = trim((string)($args['host'] ?? ''));
        $idEmpleado = trim((string)($args['id_empleado'] ?? 'Plataforma'));
        $referenciaKasu = trim((string)($args['referencia_kasu'] ?? ''));

        if ($curp === '' || $emailRaw === '' || $telefonoRaw === '') {
            return ['ok' => false, 'error' => 'CURP, correo y telefono son requeridos.'];
        }
        if (!filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'Correo invalido.'];
        }
        if (strlen($telefonoRaw) !== 10) {
            return ['ok' => false, 'error' => 'Telefono invalido (10 digitos).'];
        }
        if ($codigoPostal !== '' && strlen($codigoPostal) !== 5) {
            return ['ok' => false, 'error' => 'Codigo postal invalido.'];
        }
        if ($plazo <= 0) {
            $plazo = 1;
        }
        if ($plazo > 1) {
            $diaPago = ($diaPago === 1 || $diaPago === 15) ? $diaPago : 1;
        } else {
            $diaPago = 0;
        }

        $toAcepto = static function(string $v): string {
            $v = mb_strtolower(trim($v), 'UTF-8');
            return in_array($v, ['on','1','true','si','sí','acepto','accept','checked'], true) ? 'ACEPTO' : 'NO ACEPTO';
        };
        $terminos = $toAcepto($terminosRaw);
        $aviso = $toAcepto($avisoRaw);
        $fideicomiso = $toAcepto($fideRaw);
        if ($terminos !== 'ACEPTO' || $aviso !== 'ACEPTO' || $fideicomiso !== 'ACEPTO') {
            return ['ok' => false, 'error' => 'Debes aceptar terminos, aviso y fideicomiso.'];
        }

        $productoBase = '';
        if ($servicio === 'FUNERARIO' || $servicio === 'GASTOS FUNERARIOS') {
            $productoBase = 'Funerario';
        } elseif ($servicio === 'SEGURIDAD' || $servicio === 'POLICIAS' || $servicio === 'POLICIA') {
            $productoBase = 'Seguridad';
        } elseif ($servicio === 'TRANSPORTE' || $servicio === 'TAXI' || $servicio === 'TRANSPORTISTAS') {
            $productoBase = 'Transporte';
        } else {
            return ['ok' => false, 'error' => 'Servicio no valido para registro.'];
        }

        if ($productoBase === 'Funerario' && $tipoServicio === '') {
            $tipoServicio = 'Tradicional';
        }

        $idContact = (int)$basicas->BuscarCampos($mysqli, 'IdContact', 'Usuario', 'ClaveCurp', $curp);
        if ($idContact > 0) {
            $valor = trim((string)$basicas->BuscarCampos($mysqli, 'Producto', 'Venta', 'IdContact', $idContact));
            $categoria = $valor;
            $tokensFunerario = ['02a29','30a49','50a54','55a59','60a64','65a69'];
            if (in_array($valor, $tokensFunerario, true)) {
                $categoria = 'Funerario';
            }
            if (strcasecmp($categoria, $productoBase) === 0) {
                return ['ok' => false, 'error' => 'CURP ya registrada con este producto.'];
            }
        }

        $idMail = $basicas->BuscarCampos($mysqli, 'id', 'Contacto', 'Mail', $emailRaw);
        $idTel = $basicas->BuscarCampos($mysqli, 'id', 'Contacto', 'Telefono', $telefonoRaw);
        if (!empty($idMail)) {
            return ['ok' => false, 'error' => 'Email ya registrado.'];
        }
        if (!empty($idTel)) {
            return ['ok' => false, 'error' => 'Telefono ya registrado.'];
        }

        $datosCurp = $seguridad->peticion_get($curp);
        if (!is_array($datosCurp) || ($datosCurp['Response'] ?? '') !== 'correct') {
            return ['ok' => false, 'error' => 'CURP no valida.'];
        }

        $edad = (int)$basicas->ObtenerEdad($curp);
        if ($edad <= 0) {
            return ['ok' => false, 'error' => 'No se pudo calcular la edad.'];
        }

        if ($productoBase === 'Funerario') {
            $productoFinal = (string)$basicas->ProdFune($edad);
        } elseif ($productoBase === 'Seguridad') {
            $productoFinal = (string)$basicas->ProdPli($edad);
        } else {
            $productoFinal = (string)$basicas->ProdTrans($edad);
        }
        if ($productoFinal === '' || $productoFinal === '<70') {
            return ['ok' => false, 'error' => 'Edad fuera de rango para registro.'];
        }

        $dataContacto = [
            'Usuario'       => $idEmpleado,
            'Idgps'         => null,
            'Host'          => $host,
            'Mail'          => $emailRaw,
            'Telefono'      => $telefonoRaw,
            'codigo_postal' => $codigoPostal,
            'Producto'      => $productoFinal,
        ];
        $idContacto = $basicas->InsertCampo($mysqli, 'Contacto', $dataContacto);
        if (!is_numeric($idContacto)) {
            return ['ok' => false, 'error' => 'No se pudo registrar el contacto.'];
        }

        $dataLegal = [
            'IdContacto'  => (int)$idContacto,
            'Meses'       => (int)$plazo,
            'Terminos'    => $terminos,
            'Aviso'       => $aviso,
            'Fideicomiso' => $fideicomiso,
        ];
        $basicas->InsertCampo($mysqli, 'Legal', $dataLegal);

        $dataUsuario = [
            'Usuario'   => $idEmpleado,
            'IdContact' => (int)$idContacto,
            'Tipo'      => 'Cliente',
            'Nombre'    => $datosCurp['Nombre'] ?? '',
            'Paterno'   => $datosCurp['Paterno'] ?? '',
            'Materno'   => $datosCurp['Materno'] ?? '',
            'ClaveCurp' => $datosCurp['Curp'] ?? $curp,
            'Email'     => $emailRaw,
        ];
        $idUsuario = $basicas->InsertCampo($mysqli, 'Usuario', $dataUsuario);
        if (!is_numeric($idUsuario)) {
            return ['ok' => false, 'error' => 'No se pudo registrar el usuario.'];
        }

        $fechaAltaUsuario = (string)$basicas->BuscarCampos($mysqli, 'FechaRegistro', 'Usuario', 'Id', $idUsuario);
        $claveMaestra = (string)(getenv('KASU_MASTER_KEY') ?: ($_ENV['KASU_MASTER_KEY'] ?? ''));
        if ($claveMaestra === '') {
            return ['ok' => false, 'error' => 'Config faltante: KASU_MASTER_KEY.'];
        }
        $firmaUnica = $seguridad->poliza_id_compacto($curp, $fechaAltaUsuario, $claveMaestra);

        $costoOriginal = (float)$basicas->BuscarCampos($mysqli, 'Costo', 'Productos', 'Producto', $productoFinal);
        if ($costoOriginal <= 0) {
            return ['ok' => false, 'error' => 'Costo no disponible.'];
        }
        $costoVenta = round($costoOriginal, 2);

        $dataVenta = [
            'Usuario'         => $idEmpleado,
            'IdContact'       => (int)$idContacto,
            'Nombre'          => trim(($datosCurp['Nombre'] ?? '') . ' ' . ($datosCurp['Paterno'] ?? '') . ' ' . ($datosCurp['Materno'] ?? '')),
            'Producto'        => $productoFinal,
            'CostoVenta'      => $costoVenta,
            'Idgps'           => null,
            'Subtotal'        => 0,
            'NumeroPagos'     => (int)$plazo,
            'DiaPago'         => $diaPago,
            'IdFIrma'         => $firmaUnica,
            'Status'          => 'PREVENTA',
            'Mes'             => date('M'),
            'Cupon'           => 0,
            'Referencia_KASU' => $referenciaKasu,
            'TipoServicio'    => $tipoServicio,
        ];
        $idVenta = $basicas->InsertCampo($mysqli, 'Venta', $dataVenta);
        if (!is_numeric($idVenta)) {
            return ['ok' => false, 'error' => 'No se pudo registrar la venta.'];
        }

        $subtotal = $costoVenta;
        if ($plazo > 1) {
            $subtotal = (float)$financieras->PagoCredito($mysqli, $idVenta);
        }
        $basicas->ActCampo($mysqli, 'Venta', 'Subtotal', $subtotal, $idVenta);

        return [
            'ok' => true,
            'id_contacto' => (int)$idContacto,
            'id_venta' => (int)$idVenta,
            'poliza' => $firmaUnica,
            'producto' => $productoFinal,
            'status' => 'PREVENTA',
            'subtotal' => round($subtotal, 2),
            'pago_link' => 'https://kasu.com.mx/pago/crear_preferencia.php?ref=' . rawurlencode($firmaUnica),
        ];
    });

    $registerTool('agendar_llamada', [
        'description' => 'Agenda una llamada con un prospecto.',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'id_prospecto' => ['type' => 'integer'],
                'nombre' => ['type' => 'string'],
                'telefono' => ['type' => 'string'],
                'email' => ['type' => 'string'],
                'fecha_nacimiento' => ['type' => 'string'],
                'curp' => ['type' => 'string'],
                'servicio' => ['type' => 'string'],
                'origen' => ['type' => 'string'],
                'inicio' => ['type' => 'string'],
                'duracion_min' => ['type' => 'integer'],
            ],
            'required' => ['inicio'],
        ],
    ], function (array $args) use ($pros, $basicas): array {
        $idProspecto = (int)($args['id_prospecto'] ?? 0);
        $inicioRaw = trim((string)($args['inicio'] ?? ''));
        $duracion = (int)($args['duracion_min'] ?? 30);
        if ($duracion <= 0) {
            $duracion = 30;
        }

        if ($inicioRaw === '') {
            return ['ok' => false, 'error' => 'Inicio requerido.'];
        }

        if ($idProspecto <= 0) {
            $nombre = trim((string)($args['nombre'] ?? ''));
            $telefono = preg_replace('/\\D+/', '', (string)($args['telefono'] ?? ''));
            $email = strtolower(trim((string)($args['email'] ?? '')));
            $curp = strtoupper(trim((string)($args['curp'] ?? '')));
            $fechaNac = trim((string)($args['fecha_nacimiento'] ?? ''));
            $servicio = strtoupper(trim((string)($args['servicio'] ?? '')));
            $origen = trim((string)($args['origen'] ?? 'CHAT-IA'));

            if ($nombre === '') {
                return ['ok' => false, 'error' => 'Nombre requerido para agendar.'];
            }
            if ($telefono === '' && $email === '') {
                return ['ok' => false, 'error' => 'Telefono o correo requerido.'];
            }
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['ok' => false, 'error' => 'Correo invalido.'];
            }
            if ($telefono !== '' && strlen($telefono) !== 10) {
                return ['ok' => false, 'error' => 'Telefono invalido (10 digitos).'];
            }

            $idExistente = 0;
            if ($email !== '') {
                $idExistente = (int)$basicas->BuscarCampos($pros, 'Id', 'prospectos', 'Email', $email);
            }
            if ($idExistente <= 0 && $telefono !== '') {
                $idExistente = (int)$basicas->BuscarCampos($pros, 'Id', 'prospectos', 'NoTel', $telefono);
            }

            if ($idExistente <= 0) {
                $dataPros = [
                    'FullName' => $nombre,
                    'Curp' => $curp,
                    'NoTel' => $telefono,
                    'Email' => $email,
                    'FechaNac' => $fechaNac,
                    'Servicio_Interes' => $servicio,
                    'Origen' => $origen,
                    'Papeline' => 'Prospeccion',
                    'PosPapeline' => 1,
                    'Sugeridos' => 0,
                    'Cancelacion' => 0,
                    'Automatico' => 1,
                    'Alta' => date('Y-m-d H:i:s'),
                ];
                $idExistente = (int)$basicas->InsertCampo($pros, 'prospectos', $dataPros);
            }

            if ($idExistente <= 0) {
                return ['ok' => false, 'error' => 'No se pudo crear el prospecto.'];
            }
            $idProspecto = $idExistente;
        }

        $inicio = kasu_parse_datetime($inicioRaw);
        if (!$inicio) {
            return ['ok' => false, 'error' => 'Fecha/hora invalida.'];
        }

        $fin = clone $inicio;
        $fin->modify('+' . $duracion . ' minutes');

        $stmt = $pros->prepare('INSERT INTO agenda_llamadas (inicio, fin, duracion_min, estado, prospecto_id, reservado_en, creado_en, actualizado_en) VALUES (?, ?, ?, ?, ?, NOW(), NOW(), NOW())');
        if (!$stmt) {
            return ['ok' => false, 'error' => 'No se pudo preparar la agenda.'];
        }
        $estado = 'reservado';
        $inicioStr = $inicio->format('Y-m-d H:i:s');
        $finStr = $fin->format('Y-m-d H:i:s');
        $stmt->bind_param('ssisi', $inicioStr, $finStr, $duracion, $estado, $idProspecto);
        $ok = $stmt->execute();
        $idAgenda = $ok ? (int)$stmt->insert_id : 0;
        $stmt->close();

        if (!$ok) {
            return ['ok' => false, 'error' => 'No se pudo agendar la llamada.'];
        }

        return [
            'ok' => true,
            'id_agenda' => $idAgenda,
            'inicio' => $inicioStr,
            'fin' => $finStr,
            'duracion_min' => $duracion,
        ];
    });

    $registerTool('recomendar_articulos_blog', [
        'description' => 'Recomienda articulos del blog de KASU por tema.',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'tema' => ['type' => 'string'],
            ],
            'required' => ['tema'],
        ],
    ], function (array $args): array {
        $tema = strtolower(trim((string)($args['tema'] ?? '')));
        if ($tema === '') {
            return ['ok' => false, 'error' => 'Tema requerido.'];
        }

        $normalized = preg_replace('/[^a-z0-9]+/u', ' ', $tema);
        $search = $tema;
        if (strpos($normalized, 'ahorro') !== false || strpos($normalized, 'finanzas') !== false) {
            $search = 'ahorro';
        } elseif (strpos($normalized, 'educa') !== false) {
            $search = 'educacion';
        } elseif (strpos($normalized, 'retiro') !== false) {
            $search = 'retiro';
        } elseif (strpos($normalized, 'servicios') !== false || strpos($normalized, 'kasu') !== false) {
            $search = 'kasu';
        } elseif (strpos($normalized, 'tanato') !== false) {
            $search = 'tanatologia';
        }
        $url = 'https://kasu.com.mx/blog/wp-json/wp/v2/posts?per_page=3&_embed=1&search=' . rawurlencode($search);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
        ]);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($resp === false || $code < 200 || $code >= 300) {
            return ['ok' => false, 'error' => 'No se pudieron cargar articulos.'];
        }

        $posts = json_decode($resp, true);
        if (!is_array($posts)) {
            return ['ok' => false, 'error' => 'Respuesta invalida del blog.'];
        }

        $items = [];
        foreach ($posts as $post) {
            $items[] = [
                'title' => (string)($post['title']['rendered'] ?? ''),
                'link' => (string)($post['link'] ?? ''),
            ];
        }

        return ['ok' => true, 'tema' => $tema, 'articulos' => $items];
    });

    $registerTool('buscar_cliente_prospecto', [
        'description' => 'Busca clientes (Contacto) y prospectos por nombre para saber si ya existe en KASU.',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'nombre' => ['type' => 'string', 'description' => 'Nombre a buscar'],
                'tipo' => ['type' => 'string', 'enum' => ['cliente', 'prospecto', 'ambos']],
                'limit' => ['type' => 'integer'],
            ],
            'required' => ['nombre'],
        ],
    ], function (array $args) use ($mysqli, $pros): array {
        $nombre = trim((string)($args['nombre'] ?? ''));
        $tipo = (string)($args['tipo'] ?? 'ambos');
        $limit = (int)($args['limit'] ?? 10);
        $limit = ($limit > 0 && $limit <= 50) ? $limit : 10;

        if ($nombre === '') {
            return ['ok' => false, 'error' => 'Nombre requerido.'];
        }

        $resultados = [];
        $like = '%' . $mysqli->real_escape_string($nombre) . '%';

        if ($tipo === 'cliente' || $tipo === 'ambos') {
            $sql = "SELECT c.id, c.Mail AS email, c.Telefono AS telefono,
                           u.Nombre, u.Paterno, u.Materno, u.ClaveCurp AS curp,
                           v.Id AS id_venta, v.IdFIrma AS poliza, v.Producto, v.Status
                    FROM Contacto c
                    LEFT JOIN Usuario u ON u.IdContact = c.id
                    LEFT JOIN Venta v ON v.IdContact = c.id
                    WHERE (u.Nombre LIKE ? OR u.Paterno LIKE ? OR u.Materno LIKE ?)
                      AND u.ClaveCurp IS NOT NULL
                    ORDER BY c.id DESC LIMIT ?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param('sssi', $like, $like, $like, $limit);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $row['origen'] = 'cliente';
                    $resultados[] = $row;
                }
                $stmt->close();
            }
        }

        if ($tipo === 'prospecto' || $tipo === 'ambos') {
            $sql = "SELECT Id, FullName AS nombre, Email AS email, NoTel AS telefono,
                           Curp AS curp, Servicio_Interes AS servicio_interes, Cancelacion
                    FROM prospectos
                    WHERE FullName LIKE ?
                    ORDER BY Id DESC LIMIT ?";
            if ($stmt = $pros->prepare($sql)) {
                $stmt->bind_param('si', $like, $limit);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $row['origen'] = 'prospecto';
                    $resultados[] = $row;
                }
                $stmt->close();
            }
        }

        if (empty($resultados)) {
            return ['ok' => true, 'encontrados' => 0, 'resultados' => [], 'mensaje' => 'No se encontro a ' . $nombre . ' en clientes ni prospectos.'];
        }

        // Si solo hay un cliente exacto, activar modo verificacion
        $clientes = array_filter($resultados, fn($r) => ($r['origen'] ?? '') === 'cliente');
        $primerCliente = !empty($clientes) ? reset($clientes) : null;
        $modoVerificacion = (count($clientes) === 1 && !empty($primerCliente['id']));

        if ($modoVerificacion) {
            // Guardar estado de verificacion en DB
            $idContact = (int)$primerCliente['id'];
            $idVenta = (int)($primerCliente['id_venta'] ?? 0);
            $phone = (string)($primerCliente['telefono'] ?? '');
            $email = (string)($primerCliente['email'] ?? '');

            $stmt = $pros->prepare("INSERT INTO kasu_chat_verifications (chat_token, id_contact, id_venta, step, phone_expected, email_expected) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE step='phone', phone_expected=VALUES(phone_expected), email_expected=VALUES(email_expected), attempts=0, otp_code=NULL, otp_expires=NULL");
            if ($stmt) {
                $step = 'phone';
                $token = $_SESSION['kasu_public_context']['chat_token'] ?? 'anon';
                $stmt->bind_param('siisss', $token, $idContact, $idVenta, $step, $phone, $email);
                $stmt->execute();
                $stmt->close();
            }

            return [
                'ok' => true,
                'encontrados' => 1,
                'modo' => 'verificacion',
                'step' => 'phone',
                'mensaje' => 'Encontrado. Para verificar tu identidad, necesito validar algunos datos.',
                'resultados' => [] // no exponer datos del cliente
            ];
        }

        return ['ok' => true, 'encontrados' => count($resultados), 'resultados' => $resultados];
    });

    $registerTool('verificar_dato', [
        'description' => 'Verifica telefono, email o codigo OTP del cliente durante el proceso de validacion de identidad.',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'tipo' => ['type' => 'string', 'enum' => ['telefono', 'email', 'codigo']],
                'valor' => ['type' => 'string'],
            ],
            'required' => ['tipo', 'valor'],
        ],
    ], function (array $args) use ($mysqli, $pros): array {
        $tipo = trim((string)($args['tipo'] ?? ''));
        $valor = trim((string)($args['valor'] ?? ''));
        $token = $_SESSION['kasu_public_context']['chat_token'] ?? '';

        if ($token === '') {
            return ['ok' => false, 'error' => 'Sin sesion activa.'];
        }

        $stmt = $pros->prepare("SELECT * FROM kasu_chat_verifications WHERE chat_token = ? LIMIT 1");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $v = $stmt->get_result()->fetch_assoc() ?: [];
        $stmt->close();

        if (!$v) {
            return ['ok' => false, 'error' => 'No hay verificacion en curso. Pide primero el nombre.'];
        }

        $attempts = (int)($v['attempts'] ?? 0);
        if ($attempts >= 5) {
            return ['ok' => false, 'error' => 'Demasiados intentos. Contacta a un agente humano.'];
        }

        // Incrementar intentos
        $pros->query("UPDATE kasu_chat_verifications SET attempts = attempts + 1 WHERE chat_token = '" . $pros->real_escape_string($token) . "'");

        if ($tipo === 'telefono') {
            $digits = preg_replace('/\D+/', '', $valor);
            $expected = preg_replace('/\D+/', '', (string)($v['phone_expected'] ?? ''));
            if ($digits === $expected) {
                $pros->query("UPDATE kasu_chat_verifications SET step = 'email', attempts = 0 WHERE chat_token = '" . $pros->real_escape_string($token) . "'");
                return ['ok' => true, 'step' => 'email', 'verificado' => true, 'mensaje' => 'Telefono verificado.'];
            }
            return ['ok' => true, 'step' => 'phone', 'verificado' => false, 'mensaje' => 'Telefono incorrecto. Intenta de nuevo.'];
        }

        if ($tipo === 'email') {
            $expected = strtolower(trim((string)($v['email_expected'] ?? '')));
            if (strtolower($valor) === $expected) {
                $pros->query("UPDATE kasu_chat_verifications SET step = 'otp', attempts = 0 WHERE chat_token = '" . $pros->real_escape_string($token) . "'");
                return ['ok' => true, 'step' => 'otp', 'verificado' => true, 'mensaje' => 'Email verificado.'];
            }
            return ['ok' => true, 'step' => 'email', 'verificado' => false, 'mensaje' => 'Email incorrecto. Intenta de nuevo.'];
        }

        if ($tipo === 'codigo') {
            $otpExpected = (string)($v['otp_code'] ?? '');
            $otpExpires = (string)($v['otp_expires'] ?? '');
            if ($otpExpected === '' || $otpExpires === '') {
                return ['ok' => false, 'error' => 'No hay codigo pendiente. Pide que te lo envie de nuevo.'];
            }
            if (strtotime($otpExpires) < time()) {
                return ['ok' => false, 'error' => 'El codigo expiro. Pide uno nuevo.'];
            }
            if ($valor === $otpExpected) {
                $pros->query("UPDATE kasu_chat_verifications SET step = 'verified', attempts = 0, otp_code = NULL WHERE chat_token = '" . $pros->real_escape_string($token) . "'");
                $_SESSION['kasu_verified_client'] = [
                    'id_contact' => (int)$v['id_contact'],
                    'id_venta' => (int)$v['id_venta'],
                    'verified_at' => time(),
                ];
                return ['ok' => true, 'step' => 'verified', 'verificado' => true, 'mensaje' => 'Identidad verificada. Bienvenido.'];
            }
            return ['ok' => true, 'step' => 'otp', 'verificado' => false, 'mensaje' => 'Codigo incorrecto. Intenta de nuevo.'];
        }

        return ['ok' => false, 'error' => 'Tipo de verificacion no valido.'];
    });

    $registerTool('enviar_codigo_verificacion', [
        'description' => 'Genera y envia un codigo de verificacion al email del cliente.',
        'parameters' => [
            'type' => 'object',
            'properties' => [],
            'required' => [],
        ],
    ], function (array $args) use ($mysqli, $pros): array {
        $token = $_SESSION['kasu_public_context']['chat_token'] ?? '';
        if ($token === '') {
            return ['ok' => false, 'error' => 'Sin sesion activa.'];
        }

        $stmt = $pros->prepare("SELECT email_expected FROM kasu_chat_verifications WHERE chat_token = ? AND step = 'otp' LIMIT 1");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $v = $stmt->get_result()->fetch_assoc() ?: [];
        $stmt->close();

        if (!$v || empty($v['email_expected'])) {
            return ['ok' => false, 'error' => 'Primero verifica nombre, telefono y email.'];
        }

        $email = $v['email_expected'];
        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', time() + 300); // 5 minutos

        $pros->query("UPDATE kasu_chat_verifications SET otp_code = '" . $pros->real_escape_string($otp) . "', otp_expires = '" . $expires . "' WHERE chat_token = '" . $pros->real_escape_string($token) . "'");

        // Enviar OTP al email
        $subject = 'KASU - Codigo de verificacion';
        $body = "Tu codigo de verificacion KASU es: {$otp}\n\nValido por 5 minutos.\n\nSi no solicitaste esto, ignora este mensaje.";
        $headers = "From: atncliente@kasu.com.mx\r\nContent-Type: text/plain; charset=UTF-8";
        @mail($email, $subject, $body, $headers);

        return ['ok' => true, 'mensaje' => 'Codigo enviado a ' . substr($email, 0, 3) . '...' . substr($email, strrpos($email, '@')) . '. Revisa tu correo.'];
    });

    $registerTool('calcular_estado_credito', [
        'description' => 'Calcula el estado de credito de un cliente: saldo pendiente, pagos realizados, mora.',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'id_venta' => ['type' => 'integer', 'description' => 'ID de la venta del cliente'],
            ],
            'required' => ['id_venta'],
        ],
    ], function (array $args) use ($mysqli, $basicas, $financieras): array {
        $idVenta = (int)($args['id_venta'] ?? 0);
        if ($idVenta <= 0) {
            return ['ok' => false, 'error' => 'ID de venta requerido.'];
        }

        if (!method_exists($financieras, 'estado_mora_corriente')) {
            return ['ok' => false, 'error' => 'Funcion financiera no disponible.'];
        }

        $estado = $financieras->estado_mora_corriente($idVenta);
        if (!is_array($estado)) {
            return ['ok' => false, 'error' => 'No se pudo obtener el estado de credito.'];
        }

        $estado['ok'] = true;
        return $estado;
    });

    $registerTool('registrar_ticket', [
        'description' => 'Crea un ticket de atencion humana cuando el chat de IA no puede resolver la solicitud. El ticket se asigna a Mesa_Clientes o Mesa_Prospectos segun el tipo de usuario.',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'tipo' => ['type' => 'string', 'enum' => ['prospecto', 'cliente']],
                'motivo' => ['type' => 'string', 'description' => 'Resumen del motivo del ticket'],
            ],
            'required' => ['tipo', 'motivo'],
        ],
    ], function (array $args) use ($mysqli, $pros): array {
        $tipo = trim((string)($args['tipo'] ?? 'prospecto'));
        $motivo = trim((string)($args['motivo'] ?? ''));
        $token = $_SESSION['kasu_public_context']['chat_token'] ?? '';
        $ctx = $_SESSION['kasu_public_context'] ?? [];

        if ($token === '') {
            return ['ok' => false, 'error' => 'Sin sesion activa.'];
        }

        $ticketCode = 'KT' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        $nombre = (string)($ctx['nombre'] ?? ($ctx['curp'] ?? 'Usuario'));
        $curp = (string)($ctx['curp'] ?? '');
        $email = (string)($ctx['email'] ?? '');
        $telefono = (string)($ctx['telefono'] ?? '');
        $idContact = (int)($ctx['id_contact'] ?? 0);
        $idVenta = (int)($ctx['id_venta'] ?? 0);

        // Si es cliente verificado, usar datos de sesion
        if (!empty($_SESSION['kasu_verified_client'])) {
            $tipo = 'cliente';
            $idContact = (int)$_SESSION['kasu_verified_client']['id_contact'];
            $idVenta = (int)$_SESSION['kasu_verified_client']['id_venta'];
        }

        $stmt = $pros->prepare("INSERT INTO kasu_support_tickets (ticket_code, chat_token, tipo, nombre_cliente, curp, email, telefono, motivo, id_contact, id_venta, contexto_json, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,'abierto')");
        $ctxJson = json_encode($ctx, JSON_UNESCAPED_UNICODE);
        if ($stmt) {
            $stmt->bind_param('ssssssssiis', $ticketCode, $token, $tipo, $nombre, $curp, $email, $telefono, $motivo, $idContact, $idVenta, $ctxJson);
            $stmt->execute();
            $ticketId = $stmt->insert_id;
            $stmt->close();

            // Registrar mensaje inicial del sistema
            $sysMsg = $token . '|system|Ticket ' . $ticketCode . ' creado. Motivo: ' . $motivo;
            $stmt2 = $pros->prepare("INSERT INTO kasu_support_messages (ticket_id, role, content) VALUES (?,'system',?)");
            $stmt2->bind_param('is', $ticketId, $sysMsg);
            $stmt2->execute();
            $stmt2->close();

            // Marcar sesion como transferida a humano
            $_SESSION['kasu_transferido_humano'] = true;
            $_SESSION['kasu_ticket_code'] = $ticketCode;

            $destino = ($tipo === 'cliente') ? 'Mesa_Clientes' : 'Mesa_Prospectos';
            return [
                'ok' => true,
                'ticket' => $ticketCode,
                'tipo' => $tipo,
                'destino' => $destino,
                'mensaje' => 'Ticket ' . $ticketCode . ' creado. Un agente de ' . $destino . ' te atendera pronto.',
            ];
        }

        return ['ok' => false, 'error' => 'No se pudo crear el ticket.'];
    });

    $planSource = 'ai';
    $plan = generatePublicPlanWithAI($userMessage, $conversationStore, $toolsRegistry->getToolsForOpenAI(), $publicContext, $sessionData['history'] ?? '');

    if (empty($plan['source'])) {
        $plan['source'] = $planSource;
    }
    if (!empty($plan['intent'])) {
        $publicContext['intent'] = (string)$plan['intent'];
    }
    if (!empty($plan['intent_compra'])) {
        $publicContext['intent_compra'] = (bool)$plan['intent_compra'];
    }

    $plan = sanitizePublicPlan($plan, $allowedTools, $publicContext, $userMessage);
    if ($plan['mode'] === 'tool_sequence' && empty($plan['actions'])) {
        $plan['mode'] = 'answer_only';
        if (trim((string)($plan['response'] ?? '')) === '') {
            $plan['response'] = 'Hola, soy KASU. Te ayudo con cotizacion, poliza o llamada. Que necesitas?';
        }
    }

    $results = [];
    if (!empty($plan['actions'])) {
        foreach ($plan['actions'] as $action) {
            $toolName = (string)($action['tool'] ?? '');
            if ($toolName === '') {
                continue;
            }
            $args = (array)($action['args'] ?? []);
            $result = $toolsRegistry->executeTool($toolName, $args);
            if (!is_array($result)) {
                $result = ['ok' => false, 'error' => 'Respuesta invalida de tool.'];
            }
            $result['tool'] = $toolName;
            $results[] = $result;
            $publicContext = kasu_update_context_from_result($publicContext, $toolName, $result);
        }
    }
    $_SESSION['kasu_public_context'] = $publicContext;
    kasu_chat_store_message($chatDb, $token, 'user', $userMessage);

    $html = buildPublicHtml((string)($plan['response'] ?? ''), $results, $contactPhone);
    $response = [
        'ok' => true,
        'type' => 'response',
        'html' => $html,
        'data' => ['results' => $results],
        'timestamp' => date('Y-m-d H:i:s'),
        'chat_token' => $token,
    ];

    $conversationStore->addTurn('assistant', strip_tags((string)($plan['response'] ?? '')));
    kasu_chat_store_message($chatDb, $token, 'assistant', strip_tags((string)($plan['response'] ?? '')));
    kasu_chat_save_session($chatDb, $token, $publicContext, $_SESSION['kasu_public_openai_prev_id'] ?? $sessionData['last_openai_id'] ?? '');

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    error_log('[KASU_CHAT_PUBLICO] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'No se pudo procesar tu solicitud en este momento.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function kasu_calcular_edad(string $fechaNac): int {
    $fechaNac = trim($fechaNac);
    $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y'];
    foreach ($formats as $fmt) {
        $dt = DateTime::createFromFormat($fmt, $fechaNac);
        if ($dt instanceof DateTime) {
            $hoy = new DateTime();
            return (int)$hoy->diff($dt)->y;
        }
    }
    return 0;
}

function kasu_parse_datetime(string $value): ?DateTime {
    $value = trim($value);
    $formats = ['Y-m-d H:i', 'Y-m-d H:i:s', 'd/m/Y H:i', 'd/m/Y H:i:s'];
    foreach ($formats as $fmt) {
        $dt = DateTime::createFromFormat($fmt, $value);
        if ($dt instanceof DateTime) {
            return $dt;
        }
    }
    $dt = strtotime($value);
    if ($dt !== false) {
        return (new DateTime())->setTimestamp($dt);
    }
    return null;
}

function sanitizePublicPlan(array $plan, array $allowedTools, array $context, string $message): array {
    $mode = $plan['mode'] ?? 'answer_only';
    if ($mode !== 'tool_sequence' && $mode !== 'answer_only') {
        $mode = 'answer_only';
    }

    $actions = [];
    if ($mode === 'tool_sequence' && !empty($plan['actions']) && is_array($plan['actions'])) {
        foreach ($plan['actions'] as $action) {
            $tool = (string)($action['tool'] ?? '');
            if ($tool === '' || !in_array($tool, $allowedTools, true)) {
                continue;
            }
            $args = kasu_fill_args_from_context($tool, (array)($action['args'] ?? []), $context);
            if ($tool === 'cotizar_producto') {
                $serv = strtoupper(trim((string)($args['servicio'] ?? '')));
                if ($serv === 'SEGURIDAD' && empty($context['es_policia_gob'])) {
                    continue;
                }
                if ($serv === 'TRANSPORTE' && empty($context['es_transportista'])) {
                    continue;
                }
            }
            if ($tool === 'registrar_poliza') {
                $serv = strtoupper(trim((string)($args['servicio'] ?? '')));
                if ($serv === 'SEGURIDAD' && empty($context['es_policia_gob'])) {
                    continue;
                }
                if ($serv === 'TRANSPORTE' && empty($context['es_transportista'])) {
                    continue;
                }
            }
            if (kasu_args_missing($tool, $args)) {
                continue;
            }
            $actions[] = [
                'tool' => $tool,
                'args' => $args,
            ];
        }
    }

    $planSource = (string)($plan['source'] ?? '');
    if ($planSource !== 'ai' && $mode === 'answer_only' && preg_match('/\\b(cotiz|precio|costo|presupuesto|cuanto\\s+cuesta|cuesta)\\b/iu', $message)) {
        $hasEdad = !empty($context['edad']) || !empty($context['fecha_nacimiento']) || !empty($context['curp']);
        if ($hasEdad) {
            $args = kasu_fill_args_from_context('cotizar_producto', [], $context);
            $serv = strtoupper(trim((string)($args['servicio'] ?? '')));
            if ($serv === 'SEGURIDAD' && empty($context['es_policia_gob'])) {
                return [
                    'mode' => 'answer_only',
                    'actions' => [],
                    'response' => 'El servicio de seguridad es solo para policias de gobierno. Eres policia de gobierno? si/no.',
                    'next_steps' => [],
                ];
            }
            if ($serv === 'SEGURIDAD' && !empty($context['es_policia_gob'])) {
                return [
                    'mode' => 'answer_only',
                    'actions' => [],
                    'response' => 'El servicio de seguridad es solo venta gobierno. Si quieres, agendo una llamada.',
                    'next_steps' => [],
                ];
            }
            if ($serv === 'TRANSPORTE' && empty($context['es_transportista'])) {
                return [
                    'mode' => 'answer_only',
                    'actions' => [],
                    'response' => 'El servicio de transporte es solo para taxistas o transportistas. Trabajas en eso? si/no.',
                    'next_steps' => [],
                ];
            }
            $actions = [[
                'tool' => 'cotizar_producto',
                'args' => $args,
            ]];
            $mode = 'tool_sequence';
        }
    }

    return [
        'mode' => $mode,
        'actions' => $actions,
        'response' => kasu_compact_response((string)($plan['response'] ?? 'Como puedo ayudarte?')),
        'next_steps' => is_array($plan['next_steps'] ?? null) ? $plan['next_steps'] : [],
    ];
}

function generatePublicPlanWithAI(string $message, IAConversationStore $store, array $tools, array $context, string $dbHistory): array {
    $history = $dbHistory !== '' ? $dbHistory : $store->getFormattedHistory(6);
    $toolsJson = json_encode($tools, JSON_UNESCAPED_UNICODE);
    $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE);

    $prompt = <<<PROMPT
Eres el asistente publico de KASU (ventas y atencion al cliente). Devuelve SIEMPRE un JSON valido con:
{
  "mode": "tool_sequence"|"answer_only",
  "reasoning": "...",
  "actions": [
    {"tool": "nombre_tool", "args": { ... }}
  ],
  "response": "respuesta al usuario",
  "next_steps": ["..."],
  "intent": "etiqueta breve",
  "intent_compra": true|false
}

REGLAS:
- Si falta informacion clave, responde con una pregunta y usa mode=answer_only.
- Usa CONTEXTO_CONOCIDO_JSON para no pedir datos ya dados.
- IMPORTANTE: "precio de mi poliza" o "cuanto cuesta" SIN haber dado CURP+poliza antes = quiere COTIZAR un servicio nuevo, NO consultar uno existente. Activa modo cotizacion (pide CURP o edad).
- Si el usuario da su nombre y NO sabes si es cliente, usa buscar_cliente_prospecto para verificarlo.
- IMPORTANTE VERIFICACION: si buscar_cliente_prospecto devuelve modo='verificacion', DEBES verificar su identidad antes de mostrar datos. NO muestres CURP, poliza ni status. Sigue estos pasos UNO POR UNO:
  1️⃣ Pide el telefono y verificalo con verificar_dato(tipo:'telefono', valor:'...')
  2️⃣ Si telefono OK → pide el email y verificalo con verificar_dato(tipo:'email', valor:'...')
  3️⃣ Si email OK → envia codigo con enviar_codigo_verificacion() y pide el codigo al usuario
  4️⃣ Verifica el codigo con verificar_dato(tipo:'codigo', valor:'...')
  5️⃣ Si codigo OK → el cliente queda verificado. Ahora SI puedes usar consultar_cliente, calcular_estado_credito y mostrar datos.
- Si buscar_cliente_prospecto NO encuentra al usuario, tratalo como prospecto nuevo y ofrece cotizar.
- Si ya tienes id_venta de un cliente, usa calcular_estado_credito para mostrar saldo pendiente, pagos y mora.
- Solo trata al usuario como cliente existente si YA proporciono CURP+poliza en esta conversacion o buscar_cliente_prospecto lo confirmo.
- Para clientes existentes CON CURP+poliza ya dados, usa la tool consultar_cliente.
- Si solo tiene CURP y no tiene poliza, usa consultar_cliente_curp para confirmar el nombre y pedir confirmacion.
- Para cotizacion necesitas CURP (o edad/fecha de nacimiento) y servicio.
- SI YA TIENES CURP + SERVICIO: NO preguntes nada mas, ejecuta YA la tool cotizar_producto con mode=tool_sequence. El plazo es opcional (por defecto 1 = contado).
- Para enviar cotizacion por correo necesitas nombre, correo, servicio y edad/fecha.
- Si el usuario pidio cotizacion por correo y luego solo comparte su nombre, usa el contexto (correo/edad/servicio) para enviarla.
- Para agendar llamada necesitas inicio (fecha/hora) y datos del prospecto (nombre y contacto).
- Para enviar poliza usa la tool enviar_poliza_correo (requiere curp y poliza).
- Para contratar una poliza nueva usa registrar_poliza cuando tengas: CURP, correo, telefono, codigo postal, servicio, plazo, dia de pago (si es credito) y aceptacion de terminos/aviso/fideicomiso.
- IMPORTANTE CIERRE DE VENTA: cuando el usuario diga "me interesa", "si quiero", "contratar" o muestre intencion de compra, NO pidas todos los datos de golpe. Sigue este orden EXACTO:
  1️⃣ Primero pregunta: "Prefieres que te registre yo ahora mismo o prefieres que un agente humano te llame para hacerlo?"
  2️⃣ Si elige agente humano: usa agendar_llamada (pide nombre y telefono si faltan).
  3️⃣ Si elige que tu lo registres: pide los datos UNO POR UNO en este orden:
     a) correo electronico
     b) telefono (10 digitos)
     c) codigo postal
     d) dia de pago (1 o 15, solo si es credito)
     e) confirmacion de terminos, aviso de privacidad y fideicomiso (pregunta: "Aceptas los terminos, aviso de privacidad y fideicomiso?")
     f) Cuando tengas TODO → ejecuta registrar_poliza con mode=tool_sequence.
- NO pidas mas de UN dato por mensaje durante el registro.
- El servicio de seguridad es solo para policias de gobierno.
- El servicio de transporte es solo para taxistas/transportistas.
- Si no es policia ni transportista, asigna funerario.
- KASU tiene cobertura en toda la Republica Mexicana.
- Si confirma identidad, indica descargar poliza y llamar a atencion.
- No repitas saludos si ya hay historial.
- Responde maximo en 2 oraciones, sin listas largas ni markdown.
- Puedes recomendar articulos con la tool de blog.
- No des asesoria financiera.
- IMPORTANTE TRANSFERENCIA A HUMANO: si el usuario pide hablar con persona, el problema es complejo, o no puede resolverse via chat, USA registrar_ticket. tipo='cliente' si esta verificado → Mesa_Clientes. tipo='prospecto' si es nuevo → Mesa_Prospectos. Tras crear ticket, despide al usuario: "Un agente te contactara pronto."

CONTEXTO_CONOCIDO_JSON:
{$contextJson}

TOOLS_DISPONIBLES_JSON:
{$toolsJson}

HISTORIAL:
{$history}

MENSAJE:
"{$message}"

Devuelve solo JSON.
PROMPT;

    if (function_exists('openai_simple_text_with_id')) {
        $prevId = $_SESSION['kasu_public_openai_prev_id'] ?? ($context['last_openai_id'] ?? null);
        $meta = [
            'source' => $context['source'] ?? 'public_chat',
            'chat_token' => $context['chat_token'] ?? '',
        ];
        $resp = openai_simple_text_with_id($prompt, 900, is_string($prevId) ? $prevId : null, $meta);
        $responseText = (string)($resp['text'] ?? '');
        if (!empty($resp['id'])) {
            $_SESSION['kasu_public_openai_prev_id'] = $resp['id'];
        }
    } else {
        $responseText = openai_simple_text($prompt, 900);
    }
    $start = strpos($responseText, '{');
    $end = strrpos($responseText, '}');
    if ($start === false || $end === false) {
        return ['mode' => 'answer_only', 'response' => 'No pude procesar tu solicitud. Intenta de nuevo.', 'actions' => [], 'next_steps' => []];
    }
    $json = substr($responseText, $start, $end - $start + 1);
    $plan = json_decode($json, true);
    if (!is_array($plan)) {
        return ['mode' => 'answer_only', 'response' => 'No pude procesar tu solicitud. Intenta de nuevo.', 'actions' => [], 'next_steps' => []];
    }

    return $plan;
}

function kasu_html(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function kasu_format_phone(string $phone): string {
    $digits = preg_replace('/\\D+/', '', $phone);
    if (strlen($digits) === 10) {
        return substr($digits, 0, 3) . ' ' . substr($digits, 3, 3) . ' ' . substr($digits, 6);
    }
    return $digits !== '' ? $digits : $phone;
}

function kasu_is_probable_name(string $text): bool {
    $text = trim($text);
    if ($text === '') {
        return false;
    }
    if (preg_match('/\\d|@|\\b(curp|poliza|correo|email|mes|meses)\\b/i', $text)) {
        return false;
    }
    if (!preg_match('/^[A-ZÁÉÍÓÚÑa-záéíóúñ\\s\\.\\-]+$/u', $text)) {
        return false;
    }
    $words = preg_split('/\\s+/', $text);
    $count = $words ? count($words) : 0;
    return $count >= 2 && $count <= 5;
}

function kasu_is_acepto(string $value): bool {
    $value = mb_strtolower(trim($value), 'UTF-8');
    return in_array($value, ['acepto','si','sí','true','1','on'], true);
}

function kasu_compact_response(string $text): string {
    $text = trim(preg_replace('/\\s+/', ' ', $text));
    if ($text === '') {
        return $text;
    }
    $parts = preg_split('/(?<=[.!?])\\s+/', $text);
    if (!$parts) {
        return $text;
    }
    $compact = implode(' ', array_slice($parts, 0, 2));
    $maxLen = 420;
    if (mb_strlen($compact, 'UTF-8') > $maxLen) {
        $slice = mb_substr($compact, 0, $maxLen, 'UTF-8');
        $lastSpace = mb_strrpos($slice, ' ', 0, 'UTF-8');
        if ($lastSpace !== false && $lastSpace > 20) {
            $slice = mb_substr($slice, 0, $lastSpace, 'UTF-8');
        }
        $compact = rtrim($slice) . '...';
    }
    return $compact;
}

function kasu_normalize_token(string $token): string {
    $token = trim($token);
    if ($token === '') return '';
    if (!preg_match('/^[A-Za-z0-9_\\-]{8,64}$/', $token)) {
        return '';
    }
    return $token;
}

function kasu_generate_token(): string {
    $raw = bin2hex(random_bytes(16));
    return 'kasu_' . $raw;
}

function kasu_chat_init_db(mysqli $db): void {
    $db->query("CREATE TABLE IF NOT EXISTS kasu_chat_sessions (
        token VARCHAR(64) PRIMARY KEY,
        context_json LONGTEXT NULL,
        last_openai_id VARCHAR(64) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $db->query("CREATE TABLE IF NOT EXISTS kasu_chat_messages (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        token VARCHAR(64) NOT NULL,
        role VARCHAR(16) NOT NULL,
        content TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY idx_token_created (token, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function kasu_chat_load_session(mysqli $db, string $token): array {
    $data = ['context' => [], 'history' => '', 'last_openai_id' => '', 'expired' => false];
    if ($token === '') return $data;

    $stmt = $db->prepare('SELECT context_json, last_openai_id, updated_at FROM kasu_chat_sessions WHERE token = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $stmt->bind_result($contextJson, $lastId, $updatedAt);
        if ($stmt->fetch()) {
            if (!empty($contextJson)) {
                $context = json_decode($contextJson, true);
                if (is_array($context)) {
                    $data['context'] = $context;
                }
            }
            $data['last_openai_id'] = (string)$lastId;
            if ($updatedAt) {
                $updatedTs = strtotime($updatedAt);
                if ($updatedTs !== false && (time() - $updatedTs) > 86400) {
                    $data['expired'] = true;
                }
            }
        }
        $stmt->close();
    }

    if ($data['expired']) {
        $db->query("DELETE FROM kasu_chat_messages WHERE token = '" . $db->real_escape_string($token) . "'");
        $data['context'] = [];
    } else {
        $data['history'] = kasu_chat_load_history($db, $token, 8);
    }

    return $data;
}

function kasu_chat_save_session(mysqli $db, string $token, array $context, string $lastOpenaiId): void {
    if ($token === '') return;
    $ctx = json_encode($context, JSON_UNESCAPED_UNICODE);
    $stmt = $db->prepare('INSERT INTO kasu_chat_sessions (token, context_json, last_openai_id) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE context_json = VALUES(context_json), last_openai_id = VALUES(last_openai_id)');
    if ($stmt) {
        $stmt->bind_param('sss', $token, $ctx, $lastOpenaiId);
        $stmt->execute();
        $stmt->close();
    }
}

function kasu_chat_store_message(mysqli $db, string $token, string $role, string $content): void {
    if ($token === '' || $content === '') return;
    $stmt = $db->prepare('INSERT INTO kasu_chat_messages (token, role, content) VALUES (?, ?, ?)');
    if ($stmt) {
        $stmt->bind_param('sss', $token, $role, $content);
        $stmt->execute();
        $stmt->close();
    }
}

function kasu_chat_load_history(mysqli $db, string $token, int $limit = 8): string {
    $limit = max(1, min($limit, 12));
    $stmt = $db->prepare('SELECT role, content, created_at FROM kasu_chat_messages WHERE token = ? ORDER BY id DESC LIMIT ?');
    if (!$stmt) return '';
    $stmt->bind_param('si', $token, $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    if (empty($rows)) return '';
    $rows = array_reverse($rows);
    $lines = [];
    foreach ($rows as $row) {
        $role = strtoupper((string)$row['role']);
        $content = trim((string)$row['content']);
        if ($content === '') continue;
        $time = '';
        if (!empty($row['created_at'])) {
            $time = date('H:i', strtotime($row['created_at']));
        }
        $lines[] = ($time !== '' ? '[' . $time . '] ' : '') . $role . ': ' . $content;
    }
    return implode("\n", $lines) . "\n";
}

function kasu_update_public_context(string $message, array $context): array {
    $text = trim($message);
    $upper = strtoupper($text);

    if (preg_match('/\\b(ya\\s+soy\\s+cliente|soy\\s+cliente|mi\\s+poliza|mi\\s+servicio|mi\\s+cuenta|estatus|status)\\b/i', $text)) {
        $context['intent_cliente'] = true;
    }

    if (preg_match('/\\b[A-Z]{4}\\d{6}[A-Z0-9]{8}\\b/', $upper, $m)) {
        $context['curp'] = $m[0];
    }

    if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\\.[A-Z]{2,}/i', $text, $m)) {
        $context['email'] = strtolower($m[0]);
    }

    $digits = preg_replace('/\\D+/', '', $text);
    if (strlen($digits) === 12 && substr($digits, 0, 2) === '52') {
        $digits = substr($digits, 2);
    }
    if (strlen($digits) === 10) {
        $context['telefono'] = $digits;
    }

    if (preg_match('/\\b(\\d{4}-\\d{2}-\\d{2}|\\d{2}\\/\\d{2}\\/\\d{4}|\\d{2}-\\d{2}-\\d{4})\\b/', $text, $m)) {
        $context['fecha_nacimiento'] = $m[0];
    }

    if (preg_match('/\\b(\\d{2})\\s*(anos|anios|years|a\\x{00F1}os)\\b/iu', $text, $m)) {
        $context['edad'] = (int)$m[1];
    }

    if (preg_match('/\\b(\\d{1,2})\\s*(mes|meses)\\b/iu', $text, $m)) {
        $context['plazo_meses'] = (int)$m[1];
    }

    if (preg_match('/\\b(contado|una\\s+sola\\s+vez)\\b/iu', $text)) {
        $context['plazo_meses'] = 1;
    }

    if (preg_match('/\\b(codigo\\s+postal|cp)\\b/iu', $text)) {
        if (preg_match('/\\b(\\d{5})\\b/', $text, $m)) {
            $context['codigo_postal'] = $m[1];
        }
    } elseif (preg_match('/^\\s*\\d{5}\\s*$/', $text)) {
        $context['codigo_postal'] = trim($text);
    }

    if (preg_match('/\\b(dia|d[i\\x{00ED}]a)\\s*(1|15)\\b/iu', $text, $m)) {
        $context['dia_pago'] = (int)$m[2];
    }

    if (preg_match('/\\b(acepto|estoy\\s+de\\s+acuerdo)\\b/iu', $text)) {
        $context['terminos'] = 'ACEPTO';
        $context['aviso'] = 'ACEPTO';
        $context['fideicomiso'] = 'ACEPTO';
    }
    if (preg_match('/\\bno\\s+acepto\\b/iu', $text)) {
        $context['terminos'] = 'NO ACEPTO';
        $context['aviso'] = 'NO ACEPTO';
        $context['fideicomiso'] = 'NO ACEPTO';
    }

    if (preg_match('/\\b(poliza|idfirma)\\b/i', $text)) {
        if (preg_match('/\\b[A-Z0-9]{10,30}\\b/', $upper, $m)) {
            if (!preg_match('/\\b[A-Z]{4}\\d{6}[A-Z0-9]{8}\\b/', $m[0])) {
                $context['poliza'] = $m[0];
            }
        }
    } elseif (preg_match('/^[A-Z0-9]{10,30}$/', $upper)) {
        if (!preg_match('/\\b[A-Z]{4}\\d{6}[A-Z0-9]{8}\\b/', $upper)) {
            $context['poliza'] = $upper;
        }
    }

    $servicio = '';
    if (preg_match('/\\b(funerario|funeraria|gastos funerarios)\\b/i', $text)) {
        $servicio = 'FUNERARIO';
    } elseif (preg_match('/\\b(seguridad|policia|policias)\\b/i', $text)) {
        $servicio = 'SEGURIDAD';
    } elseif (preg_match('/\\b(transporte|taxi|taxista|transportista)\\b/i', $text)) {
        $servicio = 'TRANSPORTE';
    }
    if ($servicio !== '') {
        $context['servicio'] = $servicio;
        $context['servicio_interes'] = $servicio;
    }

    if (preg_match('/\\b(cremacion|cremaci[o\\x{00F3}]n)\\b/iu', $text)) {
        $context['tipo_servicio'] = 'Cremacion';
    } elseif (preg_match('/\\b(ecologico|ecol[o\\x{00F3}]gico)\\b/iu', $text)) {
        $context['tipo_servicio'] = 'Ecologico';
    } elseif (preg_match('/\\b(tradicional)\\b/iu', $text)) {
        $context['tipo_servicio'] = 'Tradicional';
    }

    if (preg_match('/\\b(policia|policias)\\b/i', $text)) {
        $context['es_policia'] = true;
        if (preg_match('/\\b(gobierno|federal|municipal|estado|estatal)\\b/i', $text)) {
            $context['es_policia_gob'] = true;
        }
    }

    if (preg_match('/\\b(taxi|taxista|transportista)\\b/i', $text)) {
        $context['es_transportista'] = true;
    }

    if (preg_match('/\\b(quiero\\s+una\\s+poliza|quiero\\s+poliza|contratar|comprar|adquirir)\\b/i', $text)) {
        $context['intent_compra'] = true;
    }

    if (preg_match('/\\b(cotiz|cotizacion|presupuesto)\\b/i', $text) && preg_match('/\\b(correo|email)\\b/i', $text)) {
        $context['pending_cotizacion_correo'] = true;
    }
    if (preg_match('/\\b(manda|mandame|enviame|envia)\\b/i', $text) && preg_match('/\\b(cotiz|cotizacion)\\b/i', $text)) {
        $context['pending_cotizacion_correo'] = true;
    }

    if (preg_match('/\\b(negocio|empresa|comercio|emprend|independiente|ninguna|otro|otra)\\b/i', $text)) {
        if (empty($context['es_policia_gob']) && empty($context['es_transportista'])) {
            $context['servicio'] = 'FUNERARIO';
            $context['servicio_interes'] = 'FUNERARIO';
        }
    }

    if (preg_match('/\\bmi nombre es\\s+([^\\n\\r]+)$/i', $text, $m)) {
        $context['nombre'] = trim($m[1]);
    }
    if (preg_match('/\\bme llamo\\s+([^\\n\\r]+)$/i', $text, $m)) {
        $context['nombre'] = trim($m[1]);
    }
    if (!empty($context['pending_cotizacion_correo']) && empty($context['nombre']) && kasu_is_probable_name($text)) {
        $context['nombre'] = trim($text);
    }

    if (preg_match('/^\\s*si\\s*$/i', $text)) {
        if (($context['servicio'] ?? '') === 'SEGURIDAD') {
            $context['es_policia_gob'] = true;
        } elseif (($context['servicio'] ?? '') === 'TRANSPORTE') {
            $context['es_transportista'] = true;
        }
    }

    if (preg_match('/^\\s*no\\s*$/i', $text)) {
        if (!empty($context['pending_curp_confirm'])) {
            $context['pending_curp_confirm'] = null;
        }
        if (!empty($context['pending_cotizacion_correo'])) {
            $context['pending_cotizacion_correo'] = null;
        }
        if (($context['servicio'] ?? '') === 'SEGURIDAD' && empty($context['es_policia_gob'])) {
            $context['servicio'] = 'FUNERARIO';
            $context['servicio_interes'] = 'FUNERARIO';
        }
        if (($context['servicio'] ?? '') === 'TRANSPORTE' && empty($context['es_transportista'])) {
            $context['servicio'] = 'FUNERARIO';
            $context['servicio_interes'] = 'FUNERARIO';
        }
    }

    if (preg_match('/\\b(poliza|servicio|cotiza|cotizacion)\\b/iu', $text)) {
        if (empty($context['servicio']) && empty($context['es_policia_gob']) && empty($context['es_transportista'])) {
            $context['servicio'] = 'FUNERARIO';
            $context['servicio_interes'] = 'FUNERARIO';
        }
    }

    if (!empty($context['edad']) && empty($context['servicio']) && empty($context['es_policia_gob']) && empty($context['es_transportista'])) {
        $context['servicio'] = 'FUNERARIO';
        $context['servicio_interes'] = 'FUNERARIO';
    }

    if (!empty($context['source'])) {
        return $context;
    }

    $context['source'] = $context['source'] ?? 'public_chat';
    return $context;
}

function kasu_update_context_from_result(array $context, string $tool, array $result): array {
    if (empty($result['ok'])) {
        return $context;
    }

    if ($tool === 'cotizar_producto') {
        $context['last_quote'] = [
            'producto' => $result['producto'] ?? '',
            'costo_contado' => $result['costo_contado'] ?? 0,
            'max_credito' => $result['max_credito'] ?? 0,
            'tasa_anual' => $result['tasa_anual'] ?? 0,
        ];
        if (!empty($result['plazo_meses'])) {
            $context['plazo_meses'] = (int)$result['plazo_meses'];
        }
    }

    if ($tool === 'crear_prospecto' || $tool === 'enviar_cotizacion_correo') {
        if (!empty($result['id_prospecto'])) {
            $context['id_prospecto'] = $result['id_prospecto'];
        }
    }

    if ($tool === 'consultar_cliente') {
        if (!empty($result['id_venta'])) {
            $context['id_venta'] = $result['id_venta'];
        }
        $context['intent_cliente'] = true;
    }

    if ($tool === 'consultar_cliente_curp') {
        $context['pending_curp_confirm'] = [
            'nombre' => $result['nombre'] ?? '',
            'id_contacto' => $result['id_contacto'] ?? 0,
            'poliza_pdf' => $result['poliza_pdf'] ?? '',
        ];
        $context['intent_cliente'] = true;
    }
    if ($tool === 'enviar_cotizacion_correo' && !empty($result['ok'])) {
        $context['pending_cotizacion_correo'] = null;
    }

    if ($tool === 'registrar_poliza' && !empty($result['ok'])) {
        if (!empty($result['id_venta'])) {
            $context['id_venta'] = $result['id_venta'];
        }
        if (!empty($result['poliza'])) {
            $context['poliza'] = $result['poliza'];
        }
    }

    if ($tool === 'buscar_cliente_prospecto') {
        $lista = $result['resultados'] ?? [];
        if (!empty($lista[0])) {
            $primero = $lista[0];
            if (($primero['origen'] ?? '') === 'cliente') {
                $context['intent_cliente'] = true;
                if (!empty($primero['id_venta'])) $context['id_venta'] = (int)$primero['id_venta'];
                if (!empty($primero['curp'])) $context['curp'] = (string)$primero['curp'];
                if (!empty($primero['poliza'])) $context['poliza'] = (string)$primero['poliza'];
            }
        }
    }

    if ($tool === 'calcular_estado_credito' && !empty($result['ok'])) {
        if (!empty($result['id_venta'])) $context['id_venta'] = (int)$result['id_venta'];
    }

    return $context;
}

function kasu_fill_args_from_context(string $tool, array $args, array $context): array {
    $map = [
        'consultar_cliente' => ['curp', 'poliza'],
        'consultar_cliente_curp' => ['curp'],
        'enviar_poliza_correo' => ['curp', 'poliza'],
        'cotizar_producto' => ['servicio', 'curp', 'fecha_nacimiento', 'edad', 'plazo_meses'],
        'crear_prospecto' => ['nombre', 'fecha_nacimiento', 'curp', 'telefono', 'email', 'servicio', 'servicio_interes'],
        'enviar_cotizacion_correo' => ['nombre', 'fecha_nacimiento', 'curp', 'telefono', 'email', 'servicio'],
        'registrar_poliza' => ['curp', 'email', 'telefono', 'codigo_postal', 'servicio', 'tipo_servicio', 'plazo_meses', 'dia_pago', 'terminos', 'aviso', 'fideicomiso', 'host'],
        'agendar_llamada' => ['id_prospecto', 'nombre', 'telefono', 'email', 'fecha_nacimiento', 'curp', 'servicio'],
    ];

    if (!isset($map[$tool])) {
        return $args;
    }

    foreach ($map[$tool] as $field) {
        if (!isset($args[$field]) || $args[$field] === '' || $args[$field] === null) {
            if (isset($context[$field]) && $context[$field] !== '') {
                $args[$field] = $context[$field];
            }
        }
    }

    if ($tool === 'cotizar_producto' && (empty($args['servicio']) || $args['servicio'] === '')) {
        if (!empty($context['es_policia_gob'])) {
            $args['servicio'] = 'SEGURIDAD';
        } elseif (!empty($context['es_transportista'])) {
            $args['servicio'] = 'TRANSPORTE';
        } else {
            $args['servicio'] = 'FUNERARIO';
        }
    }

    if ($tool === 'enviar_cotizacion_correo' && (empty($args['servicio']) || $args['servicio'] === '')) {
        if (!empty($context['es_policia_gob'])) {
            $args['servicio'] = 'SEGURIDAD';
        } elseif (!empty($context['es_transportista'])) {
            $args['servicio'] = 'TRANSPORTE';
        } else {
            $args['servicio'] = 'FUNERARIO';
        }
    }

    if ($tool === 'registrar_poliza' && (empty($args['servicio']) || $args['servicio'] === '')) {
        if (!empty($context['es_policia_gob'])) {
            $args['servicio'] = 'SEGURIDAD';
        } elseif (!empty($context['es_transportista'])) {
            $args['servicio'] = 'TRANSPORTE';
        } else {
            $args['servicio'] = 'FUNERARIO';
        }
    }
    if ($tool === 'registrar_poliza' && empty($args['tipo_servicio'])) {
        $args['tipo_servicio'] = $context['tipo_servicio'] ?? '';
    }
    if ($tool === 'registrar_poliza' && empty($args['host'])) {
        $args['host'] = $context['source'] ?? 'public_chat';
    }

    return $args;
}

function kasu_args_missing(string $tool, array $args): bool {
    if ($tool === 'buscar_cliente_prospecto') {
        return empty($args['nombre']);
    }
    if ($tool === 'verificar_dato') {
        return empty($args['tipo']) || empty($args['valor']);
    }
    if ($tool === 'enviar_codigo_verificacion') {
        return false; // sin args requeridos
    }
    if ($tool === 'calcular_estado_credito') {
        return empty($args['id_venta']) || (int)$args['id_venta'] <= 0;
    }
    if ($tool === 'consultar_cliente' || $tool === 'enviar_poliza_correo') {
        return empty($args['curp']) || empty($args['poliza']);
    }
    if ($tool === 'consultar_cliente_curp') {
        return empty($args['curp']);
    }
    if ($tool === 'cotizar_producto') {
        return empty($args['servicio']) || (empty($args['edad']) && empty($args['fecha_nacimiento']) && empty($args['curp']));
    }
    if ($tool === 'crear_prospecto') {
        if (empty($args['nombre'])) return true;
        if (empty($args['telefono']) && empty($args['email'])) return true;
    }
    if ($tool === 'enviar_cotizacion_correo') {
        if (empty($args['nombre']) || empty($args['email']) || empty($args['servicio'])) return true;
        if (empty($args['edad']) && empty($args['fecha_nacimiento']) && empty($args['curp'])) return true;
    }
    if ($tool === 'agendar_llamada') {
        if (empty($args['inicio'])) return true;
        if (empty($args['id_prospecto']) && empty($args['nombre'])) return true;
    }
    if ($tool === 'registrar_poliza') {
        if (empty($args['curp']) || empty($args['email']) || empty($args['telefono'])) return true;
        if (empty($args['codigo_postal'])) return true;
        if (empty($args['servicio'])) return true;
        if (empty($args['plazo_meses'])) return true;
        if (empty($args['terminos']) || empty($args['aviso']) || empty($args['fideicomiso'])) return true;
        $plazo = (int)$args['plazo_meses'];
        if ($plazo > 1 && empty($args['dia_pago'])) return true;
    }
    return false;
}

function buildPublicHtml(string $response, array $results, string $contactPhone): string {
    $html = '<div class="ia-response success">';
    if ($response !== '') {
        $html .= '<p>' . kasu_html($response) . '</p>';
    }

    foreach ($results as $result) {
        $tool = (string)($result['tool'] ?? '');
        if (empty($tool)) {
            continue;
        }

        if (empty($result['ok'])) {
            $html .= '<p>Aviso: ' . kasu_html((string)($result['error'] ?? 'No se pudo completar la solicitud.')) . '</p>';
            continue;
        }

        switch ($tool) {
            case 'consultar_cliente':
                $html .= '<div><p>Datos de tu servicio</p>';
                $html .= '<p>Status: ' . kasu_html((string)($result['status'] ?? '')) . '</p>';
                $html .= '<p>Producto: ' . kasu_html((string)($result['producto'] ?? '')) . '</p>';
                if (!empty($result['tipo_servicio'])) {
                    $html .= '<p>Tipo de servicio: ' . kasu_html((string)$result['tipo_servicio']) . '</p>';
                }
                if (isset($result['pagos_realizados'])) {
                    $html .= '<p>Pagos realizados: $' . kasu_html(number_format((float)$result['pagos_realizados'], 2)) . '</p>';
                }
                if (isset($result['pendiente'])) {
                    $html .= '<p>Pendiente de pagar: $' . kasu_html(number_format((float)$result['pendiente'], 2)) . '</p>';
                }
                if (!empty($result['poliza_pdf'])) {
                    $html .= '<p><a href="' . kasu_html((string)$result['poliza_pdf']) . '" target="_blank" rel="noopener">Descargar poliza</a></p>';
                }
                if (!empty($result['mi_cuenta'])) {
                    $html .= '<p><a href="' . kasu_html((string)$result['mi_cuenta']) . '" target="_blank" rel="noopener">Ingresar a mi cuenta</a></p>';
                }
                $html .= '</div>';
                break;

            case 'consultar_cliente_curp':
                $nombre = trim((string)($result['nombre'] ?? ''));
                if ($nombre === '') {
                    $nombre = 'Cliente KASU';
                }
                $html .= '<div><p>Tu familiar es ' . kasu_html($nombre) . '. Confirmas? (si/no)</p></div>';
                break;

            case 'cotizar_producto':
                $html .= '<div><p>Cotizacion estimada</p>';
                $html .= '<p>Producto: ' . kasu_html((string)($result['producto'] ?? '')) . '</p>';
                $html .= '<p>Precio contado: $' . kasu_html(number_format((float)$result['costo_contado'], 2)) . '</p>';
                if (!empty($result['mensualidad'])) {
                    $html .= '<p>Mensualidad (' . kasu_html((string)($result['plazo_meses'] ?? '')) . ' meses): $' . kasu_html(number_format((float)$result['mensualidad'], 2)) . '</p>';
                    $html .= '<p>Total financiado: $' . kasu_html(number_format((float)$result['total_financiado'], 2)) . '</p>';
                }
                if (!empty($result['plazo_ajustado'])) {
                    $html .= '<p>El plazo solicitado excedia el maximo, se ajusto al permitido.</p>';
                }
                if (!empty($result['mensaje'])) {
                    $html .= '<p>' . kasu_html((string)$result['mensaje']) . '</p>';
                }
                $html .= '</div>';
                break;

            case 'registrar_poliza':
                $html .= '<div><p>Registro completado</p>';
                if (!empty($result['poliza'])) {
                    $html .= '<p>Poliza: ' . kasu_html((string)$result['poliza']) . '</p>';
                }
                if (!empty($result['subtotal'])) {
                    $html .= '<p>Subtotal: $' . kasu_html(number_format((float)$result['subtotal'], 2)) . '</p>';
                }
                if (!empty($result['pago_link'])) {
                    $html .= '<p><a href="' . kasu_html((string)$result['pago_link']) . '" target="_blank" rel="noopener">Continuar pago</a></p>';
                }
                $html .= '</div>';
                break;

            case 'crear_prospecto':
                $html .= '<p>Listo, registre tus datos para seguimiento.</p>';
                break;

            case 'enviar_poliza_correo':
                if (!empty($result['ok'])) {
                    $html .= '<p>Te envie tu poliza al correo registrado.</p>';
                }
                break;

            case 'enviar_cotizacion_correo':
                if (!empty($result['ok'])) {
                    $html .= '<p>Cotizacion enviada a tu correo.</p>';
                }
                break;

            case 'agendar_llamada':
                $html .= '<div><p>Llamada agendada.</p>';
                $html .= '<p>Inicio: ' . kasu_html((string)($result['inicio'] ?? '')) . '</p>';
                $html .= '<p>Fin: ' . kasu_html((string)($result['fin'] ?? '')) . '</p>';
                $html .= '</div>';
                break;

            case 'recomendar_articulos_blog':
                $articulos = $result['articulos'] ?? [];
                if (is_array($articulos) && !empty($articulos)) {
                    $html .= '<div><p>Articulos recomendados</p><ul>';
                    foreach ($articulos as $item) {
                        $title = kasu_html((string)($item['title'] ?? ''));
                        $link = kasu_html((string)($item['link'] ?? ''));
                        if ($title !== '' && $link !== '') {
                            $html .= '<li><a href="' . $link . '" target="_blank" rel="noopener">' . $title . '</a></li>';
                        }
                    }
                    $html .= '</ul></div>';
                }
                break;

            case 'buscar_cliente_prospecto':
                $encontrados = (int)($result['encontrados'] ?? 0);
                $lista = $result['resultados'] ?? [];
                if ($encontrados > 0) {
                    $html .= '<div><p>Encontre ' . $encontrados . ' resultado(s):</p>';
                    foreach ($lista as $r) {
                        $origen = $r['origen'] ?? '';
                        $nombreCli = trim(($r['Nombre'] ?? '') . ' ' . ($r['Paterno'] ?? '') . ' ' . ($r['Materno'] ?? ''));
                        if ($nombreCli === '') $nombreCli = $r['nombre'] ?? 'Sin nombre';
                        $curpCli = $r['curp'] ?? '';
                        $polizaCli = $r['poliza'] ?? '';
                        $statusCli = $r['Status'] ?? ($r['Cancelacion'] ?? '');
                        $html .= '<p>' . kasu_html($nombreCli) . ' (' . kasu_html($origen) . ')';
                        if ($curpCli !== '') $html .= ' - CURP: ' . kasu_html($curpCli);
                        if ($polizaCli !== '') $html .= ' - Poliza: ' . kasu_html($polizaCli);
                        if ($statusCli !== '') $html .= ' - Status: ' . kasu_html((string)$statusCli);
                        $html .= '</p>';
                    }
                    $html .= '</div>';
                } else {
                    $html .= '<p>' . kasu_html((string)($result['mensaje'] ?? 'No se encontraron resultados.')) . '</p>';
                }
                break;

            case 'calcular_estado_credito':
                $html .= '<div><p>Estado de tu credito</p>';
                $html .= '<p>Pagado: $' . kasu_html(number_format((float)($result['pagado_importe'] ?? $result['total_pagado'] ?? 0), 2)) . '</p>';
                $html .= '<p>Pendiente: $' . kasu_html(number_format((float)($result['pendiente_importe'] ?? $result['saldo_pendiente'] ?? 0), 2)) . '</p>';
                $html .= '<p>Cuota mensual: $' . kasu_html(number_format((float)($result['cuota'] ?? 0), 2)) . '</p>';
                $html .= '<p>Estado: ' . kasu_html(($result['en_mora'] ?? false) ? 'EN MORA' : (string)($result['estado'] ?? 'AL CORRIENTE')) . '</p>';
                $html .= '</div>';
                break;

            case 'verificar_dato':
                $html .= '<p>' . kasu_html((string)($result['mensaje'] ?? 'Verificacion procesada.')) . '</p>';
                break;

            case 'enviar_codigo_verificacion':
                $html .= '<p>' . kasu_html((string)($result['mensaje'] ?? 'Codigo enviado.')) . '</p>';
                break;
        }
    }

    $html .= '</div>';
    return $html;
}
