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

    $planSource = 'basic';
    if ($openaiAvailable) {
        try {
            $plan = generatePublicPlanWithAI($userMessage, $conversationStore, $toolsRegistry->getToolsForOpenAI(), $publicContext, $sessionData['history'] ?? '');
            $planSource = 'ai';
        } catch (Throwable $e) {
            $plan = generatePublicPlanBasic($userMessage, $publicContext);
            $planSource = 'basic';
        }
    } else {
        $plan = generatePublicPlanBasic($userMessage, $publicContext);
        $planSource = 'basic';
    }

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
            $plan = generatePublicPlanBasic($userMessage, $publicContext);
            $plan = sanitizePublicPlan($plan, $allowedTools, $publicContext, $userMessage);
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

function generatePublicPlanBasic(string $message, array $context): array {
    $msg = mb_strtolower($message, 'UTF-8');
    $hasCurp = !empty($context['curp']);
    $hasPoliza = !empty($context['poliza']);
    $hasServicio = !empty($context['servicio']);
    $hasEdad = !empty($context['edad']) || !empty($context['fecha_nacimiento']) || !empty($context['curp']);
    $intentCliente = !empty($context['intent_cliente']);
    $pendingCurp = $context['pending_curp_confirm'] ?? null;
    $pendingCotizacionCorreo = !empty($context['pending_cotizacion_correo']);
    $contactPhone = preg_replace('/\\D+/', '', (string)($context['contact_phone'] ?? ''));
    $contactPhoneTxt = $contactPhone !== '' ? kasu_format_phone($contactPhone) : '';
    $curpOnly = preg_match('/^[A-Z]{4}\\d{6}[A-Z0-9]{8}$/i', trim($message)) === 1;

    if ($pendingCotizacionCorreo && !empty($context['nombre']) && !empty($context['email']) && $hasEdad) {
        return [
            'mode' => 'tool_sequence',
            'response' => 'Listo, envio tu cotizacion al correo.',
            'actions' => [
                [
                    'tool' => 'enviar_cotizacion_correo',
                    'args' => [
                        'nombre' => $context['nombre'] ?? '',
                        'fecha_nacimiento' => $context['fecha_nacimiento'] ?? '',
                        'curp' => $context['curp'] ?? '',
                        'telefono' => $context['telefono'] ?? '',
                        'email' => $context['email'] ?? '',
                        'servicio' => $context['servicio'] ?? 'FUNERARIO',
                        'plazo_meses' => $context['plazo_meses'] ?? 1,
                    ],
                ],
            ],
            'next_steps' => [],
        ];
    }

    if (!empty($pendingCurp) && preg_match('/^\\s*s[i\\x{00ED}]\\s*$/iu', $msg)) {
        $nombre = is_array($pendingCurp) ? (string)($pendingCurp['nombre'] ?? '') : '';
        $polizaLink = is_array($pendingCurp) ? (string)($pendingCurp['poliza_pdf'] ?? '') : '';
        $line = '';
        if ($polizaLink !== '') {
            $line = 'descarga tu poliza aqui: ' . $polizaLink;
        }
        if ($contactPhoneTxt !== '') {
            $line .= ($line !== '' ? ' y ' : '') . 'llama al ' . $contactPhoneTxt . ' para atencion inmediata';
        }
        if ($line === '') {
            $line = 'llama a KASU para atencion inmediata';
        }
        $resp = 'Confirmado' . ($nombre !== '' ? ', ' . $nombre : '') . ': ' . $line . '.';
        if (stripos($line, 'poliza a la mano') === false) {
            $resp .= ' Ten tu poliza a la mano.';
        }
        return [
            'mode' => 'answer_only',
            'response' => $resp,
            'actions' => [],
            'next_steps' => [],
        ];
    }

    if (!empty($pendingCurp) && preg_match('/^\\s*no\\s*$/i', $msg)) {
        return [
            'mode' => 'answer_only',
            'response' => 'Ok. Comparte la CURP correcta o el numero de poliza.',
            'actions' => [],
            'next_steps' => [],
        ];
    }

    if ($hasCurp && !$hasPoliza && ($curpOnly || ($intentCliente && preg_match('/\\b(curp|cliente|poliza|servicio|fallec|defunc|familiar)\\b/iu', $msg)))) {
        return [
            'mode' => 'tool_sequence',
            'response' => 'Estoy validando tu CURP para confirmar el nombre.',
            'actions' => [
                [
                    'tool' => 'consultar_cliente_curp',
                    'args' => ['curp' => $context['curp']],
                ],
            ],
            'next_steps' => [],
        ];
    }

    if ($hasCurp && $hasPoliza && $curpOnly) {
        return [
            'mode' => 'tool_sequence',
            'response' => 'Voy a revisar tu servicio con los datos que ya me compartiste.',
            'actions' => [
                [
                    'tool' => 'consultar_cliente',
                    'args' => ['curp' => $context['curp'], 'poliza' => $context['poliza']],
                ],
            ],
            'next_steps' => [],
        ];
    }

    if ($intentCliente || preg_match('/\b(mi\s+poliza|mi\s+servicio|mi\s+cuenta|cliente|estatus|status|numero\s+de\s+poliza)\b/iu', $msg)) {
        if ($hasCurp && $hasPoliza) {
            return [
                'mode' => 'tool_sequence',
                'response' => 'Voy a revisar tu servicio con los datos que ya me compartiste.',
                'actions' => [
                    [
                        'tool' => 'consultar_cliente',
                        'args' => ['curp' => $context['curp'], 'poliza' => $context['poliza']],
                    ],
                ],
                'next_steps' => [],
            ];
        }
        if ($hasCurp && !$hasPoliza) {
            return [
                'mode' => 'tool_sequence',
                'response' => 'Estoy validando tu CURP para confirmar el nombre.',
                'actions' => [
                    [
                        'tool' => 'consultar_cliente_curp',
                        'args' => ['curp' => $context['curp']],
                    ],
                ],
                'next_steps' => [],
            ];
        }
        return [
            'mode' => 'answer_only',
            'response' => 'Para revisar tu servicio necesito tu CURP y el numero de poliza. Compartemelos y te ayudo en seguida.',
            'actions' => [],
            'next_steps' => [],
        ];
    }

    if (preg_match('/\b(enviar|manda).*\bpoliza\b/u', $msg)) {
        if ($hasCurp && $hasPoliza) {
            return [
                'mode' => 'tool_sequence',
                'response' => 'Voy a enviar tu poliza al correo registrado.',
                'actions' => [
                    [
                        'tool' => 'enviar_poliza_correo',
                        'args' => ['curp' => $context['curp'], 'poliza' => $context['poliza']],
                    ],
                ],
                'next_steps' => [],
            ];
        }
        return [
            'mode' => 'answer_only',
            'response' => 'Para enviarte tu poliza necesito tu CURP y el numero de poliza.',
            'actions' => [],
            'next_steps' => [],
        ];
    }

    if (preg_match('/\b(activa|activo|estatus|status|producto|tipo)\b/u', $msg) && $hasCurp && $hasPoliza) {
        return [
            'mode' => 'tool_sequence',
            'response' => 'Voy a revisar tu servicio con los datos que ya me compartiste.',
            'actions' => [
                [
                    'tool' => 'consultar_cliente',
                    'args' => ['curp' => $context['curp'], 'poliza' => $context['poliza']],
                ],
            ],
            'next_steps' => [],
        ];
    }

    if (preg_match('/\b(cotiz|precio|costo|presupuesto|cuanto|cuesta)\b/u', $msg)) {
        if ($hasEdad) {
            return [
                'mode' => 'tool_sequence',
                'response' => 'Voy a generar tu cotizacion.',
                'actions' => [
                    [
                        'tool' => 'cotizar_producto',
                        'args' => [
                            'servicio' => $context['servicio'] ?? 'FUNERARIO',
                            'curp' => $context['curp'] ?? '',
                            'fecha_nacimiento' => $context['fecha_nacimiento'] ?? '',
                            'edad' => $context['edad'] ?? 0,
                            'plazo_meses' => $context['plazo_meses'] ?? 1,
                        ],
                    ],
                ],
                'next_steps' => [],
            ];
        }
        return [
            'mode' => 'answer_only',
            'response' => 'Para cotizar necesito tu edad y si eres policia de gobierno o taxista/transportista. Si no, es funerario.',
            'actions' => [],
            'next_steps' => [],
        ];
    }

    if (preg_match('/\\bcorreo\\b/iu', $msg) && !preg_match('/\\bpoliza\\b/iu', $msg)) {
        $faltantes = [];
        if (empty($context['nombre'])) {
            $faltantes[] = 'nombre';
        }
        if (empty($context['email'])) {
            $faltantes[] = 'correo';
        }
        if (!$hasEdad) {
            $faltantes[] = 'edad o fecha de nacimiento';
        }
        if (!empty($faltantes)) {
            return [
                'mode' => 'answer_only',
                'response' => 'Para enviarte la cotizacion necesito: ' . implode(', ', $faltantes) . '.',
                'actions' => [],
                'next_steps' => [],
            ];
        }
        return [
            'mode' => 'tool_sequence',
            'response' => 'Listo, envio tu cotizacion al correo registrado.',
            'actions' => [
                [
                    'tool' => 'enviar_cotizacion_correo',
                    'args' => [
                        'nombre' => $context['nombre'] ?? '',
                        'fecha_nacimiento' => $context['fecha_nacimiento'] ?? '',
                        'curp' => $context['curp'] ?? '',
                        'telefono' => $context['telefono'] ?? '',
                        'email' => $context['email'] ?? '',
                        'servicio' => $context['servicio'] ?? 'FUNERARIO',
                        'plazo_meses' => $context['plazo_meses'] ?? 1,
                    ],
                ],
            ],
            'next_steps' => [],
        ];
    }

    if (preg_match('/\\b(contratar|contrato|registro|registrar|comprar|adquirir)\\b/iu', $msg)) {
        $faltantes = [];
        if (empty($context['curp'])) {
            $faltantes[] = 'CURP';
        }
        if (empty($context['email'])) {
            $faltantes[] = 'correo';
        }
        if (empty($context['telefono'])) {
            $faltantes[] = 'telefono';
        }
        if (empty($context['codigo_postal'])) {
            $faltantes[] = 'codigo postal';
        }
        if (empty($context['plazo_meses'])) {
            $faltantes[] = 'plazo (contado o meses)';
        }
        if (!empty($context['plazo_meses']) && (int)$context['plazo_meses'] > 1 && empty($context['dia_pago'])) {
            $faltantes[] = 'dia de pago (1 o 15)';
        }
        $terminosOk = kasu_is_acepto((string)($context['terminos'] ?? ''));
        $avisoOk = kasu_is_acepto((string)($context['aviso'] ?? ''));
        $fideOk = kasu_is_acepto((string)($context['fideicomiso'] ?? ''));
        if (!$terminosOk || !$avisoOk || !$fideOk) {
            $faltantes[] = 'aceptar terminos, aviso y fideicomiso';
        }

        if (!empty($faltantes)) {
            return [
                'mode' => 'answer_only',
                'response' => 'Para contratar necesito: ' . implode(', ', $faltantes) . '.',
                'actions' => [],
                'next_steps' => [],
            ];
        }

        return [
            'mode' => 'tool_sequence',
            'response' => 'Voy a registrar tu poliza.',
            'actions' => [
                [
                    'tool' => 'registrar_poliza',
                    'args' => [
                        'curp' => $context['curp'] ?? '',
                        'email' => $context['email'] ?? '',
                        'telefono' => $context['telefono'] ?? '',
                        'codigo_postal' => $context['codigo_postal'] ?? '',
                        'servicio' => $context['servicio'] ?? 'FUNERARIO',
                        'tipo_servicio' => $context['tipo_servicio'] ?? '',
                        'plazo_meses' => $context['plazo_meses'] ?? 1,
                        'dia_pago' => $context['dia_pago'] ?? 0,
                        'terminos' => $context['terminos'] ?? '',
                        'aviso' => $context['aviso'] ?? '',
                        'fideicomiso' => $context['fideicomiso'] ?? '',
                        'host' => $context['source'] ?? 'public_chat',
                    ],
                ],
            ],
            'next_steps' => [],
        ];
    }

    if (preg_match('/\bpoliza|p[o\\x{00F3}]liza|quiero una poliza|quiero poliza\b/iu', $msg)) {
        return [
            'mode' => 'answer_only',
            'response' => 'Para cotizar necesito tu edad y si eres policia de gobierno o taxista/transportista. Si no, es funerario.',
            'actions' => [],
            'next_steps' => [],
        ];
    }

    if (preg_match('/\\b(blog|articul|articulo|articulos|ahorro|finanzas|educacion|retiro|tanatolog)\\b/iu', $msg)) {
        $tema = 'kasu';
        if (preg_match('/ahorro|finanzas/iu', $msg)) {
            $tema = 'ahorro';
        } elseif (preg_match('/educacion/iu', $msg)) {
            $tema = 'educacion';
        } elseif (preg_match('/retiro/iu', $msg)) {
            $tema = 'retiro';
        } elseif (preg_match('/tanatolog/iu', $msg)) {
            $tema = 'tanatologia';
        }
        return [
            'mode' => 'tool_sequence',
            'response' => 'Te comparto articulos recomendados.',
            'actions' => [
                [
                    'tool' => 'recomendar_articulos_blog',
                    'args' => ['tema' => $tema],
                ],
            ],
            'next_steps' => [],
        ];
    }

    if (preg_match('/\b(informacion|info|detalles)\b/u', $msg)) {
        return [
            'mode' => 'answer_only',
            'response' => 'Necesitas info de poliza, cotizacion, llamada o articulos? Dime cual.',
            'actions' => [],
            'next_steps' => [],
        ];
    }

    if (preg_match('/\\b(cobertura|cubre|donde\\s+(tienen|tiene)\\s+cobertura)\\b/iu', $msg)) {
        return [
            'mode' => 'answer_only',
            'response' => 'KASU tiene cobertura en toda la Republica Mexicana. Si quieres te cotizo.',
            'actions' => [],
            'next_steps' => [],
        ];
    }

    if (preg_match('/como\\s+funciona|funcionamiento/u', $msg)) {
        if (!empty($context['servicio'])) {
            if ($context['servicio'] === 'FUNERARIO') {
                return [
                    'mode' => 'answer_only',
                    'response' => 'El servicio funerario te da cobertura y apoyo al momento del evento. Te cotizo si quieres.',
                    'actions' => [],
                    'next_steps' => [],
                ];
            }
            if ($context['servicio'] === 'SEGURIDAD') {
                return [
                    'mode' => 'answer_only',
                    'response' => 'Seguridad es solo para policias de gobierno. Eres policia de gobierno? si/no.',
                    'actions' => [],
                    'next_steps' => [],
                ];
            }
            if ($context['servicio'] === 'TRANSPORTE') {
                return [
                    'mode' => 'answer_only',
                    'response' => 'Transporte es solo para taxistas/transportistas. Trabajas en eso? si/no.',
                    'actions' => [],
                    'next_steps' => [],
                ];
            }
        }
        return [
            'mode' => 'answer_only',
            'response' => 'Depende del servicio. Eres policia de gobierno o taxista/transportista? Si no, es funerario.',
            'actions' => [],
            'next_steps' => [],
        ];
    }

    if (preg_match('/\b(pago|mensual|mes|cada\s+mes|periodo|credito|cr[e\\x{00E9}]dito)\b/iu', $msg) && !empty($context['last_quote']['costo_contado'])) {
        $costo = (float)$context['last_quote']['costo_contado'];
        $maxCredito = (int)($context['last_quote']['max_credito'] ?? 0);
        $resp = 'Contado: $' . number_format($costo, 2) . ' una sola vez.';
        if ($maxCredito > 0) {
            $resp .= ' A credito es mensual hasta ' . $maxCredito . ' meses. Dime el plazo y te calculo.';
        } else {
            $resp .= ' Si quieres mensualidad, dime el plazo.';
        }
        return [
            'mode' => 'answer_only',
            'response' => $resp,
            'actions' => [],
            'next_steps' => [],
        ];
    }

    if ($hasServicio && $hasEdad && preg_match('/\b(anos|anios|a\\x{00F1}os|\\d{4}|\\d{2}\\/\\d{2}\\/\\d{4})\b/iu', $msg)) {
        return [
            'mode' => 'tool_sequence',
            'response' => 'Voy a generar tu cotizacion.',
            'actions' => [
                [
                    'tool' => 'cotizar_producto',
                    'args' => [
                        'servicio' => $context['servicio'],
                        'curp' => $context['curp'] ?? '',
                        'fecha_nacimiento' => $context['fecha_nacimiento'] ?? '',
                        'edad' => $context['edad'] ?? 0,
                    ],
                ],
            ],
            'next_steps' => [],
        ];
    }

    if (!empty($context['servicio']) && $context['servicio'] === 'SEGURIDAD' && empty($context['es_policia_gob'])) {
        return [
            'mode' => 'answer_only',
            'response' => 'El servicio de seguridad es solo para policias de gobierno. Eres policia de gobierno? si/no.',
            'actions' => [],
            'next_steps' => [],
        ];
    }

    if (!empty($context['servicio']) && $context['servicio'] === 'TRANSPORTE' && empty($context['es_transportista'])) {
        return [
            'mode' => 'answer_only',
            'response' => 'El servicio de transporte es solo para taxistas o transportistas. Trabajas en eso? si/no.',
            'actions' => [],
            'next_steps' => [],
        ];
    }

    if (preg_match('/\\b(\\d{1,2})\\s*(mes|meses)\\b/iu', $msg)) {
        if ($hasEdad) {
            return [
                'mode' => 'tool_sequence',
                'response' => 'Calculo la mensualidad con ese plazo.',
                'actions' => [
                    [
                        'tool' => 'cotizar_producto',
                        'args' => [
                            'servicio' => $context['servicio'] ?? 'FUNERARIO',
                            'curp' => $context['curp'] ?? '',
                            'fecha_nacimiento' => $context['fecha_nacimiento'] ?? '',
                            'edad' => $context['edad'] ?? 0,
                            'plazo_meses' => $context['plazo_meses'] ?? 1,
                        ],
                    ],
                ],
                'next_steps' => [],
            ];
        }
        return [
            'mode' => 'answer_only',
            'response' => 'Para calcular mensualidad necesito tu edad o fecha de nacimiento.',
            'actions' => [],
            'next_steps' => [],
        ];
    }

    if (preg_match('/\b(agendar|llamada|llamen|contacto)\b/u', $msg)) {
        return [
            'mode' => 'answer_only',
            'response' => 'Para agendar, dime nombre, telefono y fecha/hora.',
            'actions' => [],
            'next_steps' => [],
        ];
    }

    if (preg_match('/\b(funeria|funeraria|registrar\s+funeraria)\b/u', $msg)) {
        return [
            'mode' => 'answer_only',
            'response' => 'Para registrar tu funeraria necesito nombre, ciudad y telefono.',
            'actions' => [],
            'next_steps' => [],
        ];
    }

    return [
        'mode' => 'answer_only',
        'response' => 'Hola, soy KASU. Te ayudo con cotizacion, poliza o llamada. Que necesitas?',
        'actions' => [],
        'next_steps' => [],
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
- Para clientes existentes, pide CURP + numero de poliza y usa la tool consultar_cliente cuando los tengas.
- Si solo tiene CURP y no tiene poliza, usa consultar_cliente_curp para confirmar el nombre y pedir confirmacion.
- Para cotizacion necesitas edad o fecha de nacimiento y servicio.
- Para enviar cotizacion por correo necesitas nombre, correo, servicio y edad/fecha.
- Si el usuario pidio cotizacion por correo y luego solo comparte su nombre, usa el contexto (correo/edad/servicio) para enviarla.
- Para agendar llamada necesitas inicio (fecha/hora) y datos del prospecto (nombre y contacto).
- Para enviar poliza usa la tool enviar_poliza_correo (requiere curp y poliza).
- Para contratar una poliza nueva usa registrar_poliza cuando tengas: CURP, correo, telefono, codigo postal, servicio, plazo, dia de pago (si es credito) y aceptacion de terminos/aviso/fideicomiso.
- Si faltan datos de registro, pregunta solo por lo que falte.
- El servicio de seguridad es solo para policias de gobierno.
- El servicio de transporte es solo para taxistas/transportistas.
- Si no es policia ni transportista, asigna funerario.
- KASU tiene cobertura en toda la Republica Mexicana.
- Si confirma identidad, indica descargar poliza y llamar a atencion.
- No repitas saludos si ya hay historial.
- Responde maximo en 2 oraciones, sin listas largas ni markdown.
- Puedes recomendar articulos con la tool de blog.
- No des asesoria financiera.

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
        return generatePublicPlanBasic($message, $context);
    }
    $json = substr($responseText, $start, $end - $start + 1);
    $plan = json_decode($json, true);
    if (!is_array($plan)) {
        return generatePublicPlanBasic($message, $context);
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
        }
    }

    $html .= '</div>';
    return $html;
}
