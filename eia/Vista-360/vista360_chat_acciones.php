<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : vista360_chat_acciones.php
 * Carpeta : /eia/Vista-360
 * 
 *  NOTA IMPORTANTE --- Este es el archivo real que usamos para el chat --- NOTA IMPORTANTE
 *
 * Qué hace:
 * ----------
 * Endpoint JSON de chat conversacional con IA para la PWA:
 *  - Entiende el mensaje del usuario (con o sin OpenAI).
 *  - Decide UNA acción principal (con IA o con lógica local).
 *  - Llama a los micro-endpoints de acciones.
 *  - Genera respuesta HTML (con IA o con templates predefinidos).
 *
 * MEJORAS:
 * 1. Detección local de intenciones cuando OpenAI falla
 * 2. Respuestas predefinidas para casos comunes
 * 3. Sistema de coaching/análisis integrado
 * 4. Mantiene compatibilidad con tu código actual
 * ============================================================================
 */

header('Content-Type: application/json; charset=utf-8');

try {
    /* ========================== Sesión y dependencias ========================== */
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

    require_once __DIR__ . '/../librerias.php';       // $mysqli, $basicas, $pros, etc.
    
    // Intentar cargar OpenAI (pero NO es crítico si falla)
    $openaiAvailable = false;
    $openaiConfigFile = __DIR__ . '/openai_config.php';
    if (is_file($openaiConfigFile)) {
        require_once $openaiConfigFile;
        $openaiAvailable = function_exists('openai_simple_text');
    }

    // Intentar cargar ia_tools_correo.php si existe (helpers para correo)
    $correoHelperFile = __DIR__ . '/ia_tools_correo.php';
    if (is_file($correoHelperFile)) {
        require_once $correoHelperFile;
    }

    global $mysqli, $pros, $basicas;

    if (!$mysqli) {
        throw new RuntimeException('Conexión $mysqli no disponible.');
    }

    if (empty($_SESSION['Vendedor'])) {
        throw new RuntimeException('Sesión no válida: falta Vendedor.');
    }
    $idUsuario = (string)$_SESSION['Vendedor'];

    /* ========================== Entrada JSON ========================== */
    $raw  = file_get_contents('php://input');
    $body = $raw ? json_decode($raw, true) : [];
    if (!is_array($body)) {
        $body = [];
    }

    $mensajeUsuario = trim((string)($body['mensaje'] ?? ''));
    
    if ($mensajeUsuario === '') {
        throw new InvalidArgumentException('Debes enviar el campo "mensaje".');
    }

    /* ========================== Contexto de usuario ========================== */
    $nombreVendedor = (string)$basicas->BuscarCampos($mysqli, 'Nombre',         'Empleados', 'IdUsuario', $idUsuario);
    $nivel          = (int)$basicas->BuscarCampos($mysqli, 'Nivel',            'Empleados', 'IdUsuario', $idUsuario);
    $idSucursal     = (int)$basicas->BuscarCampos($mysqli, 'Sucursal',         'Empleados', 'IdUsuario', $idUsuario);
    $nombreSucursal = (string)$basicas->BuscarCampos($mysqli, 'nombreSucursal','Sucursal',  'Id',        $idSucursal);
    $nombreNivel    = (string)$basicas->BuscarCampos($mysqli, 'NombreNivel',   'Nivel',     'Id',        $nivel);

    // Mapeo simple del rol
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

    $contextoUsuario = [
        'id_usuario'   => $idUsuario,
        'nombre'       => $nombreVendedor,
        'nivel'        => $nivel,
        'nombre_nivel' => $nombreNivel,
        'sucursal'     => $nombreSucursal,
        'rol'          => $rolDescripcion,
        'fecha_hoy'    => date('Y-m-d'),
    ];

    $contextJson = json_encode($contextoUsuario, JSON_UNESCAPED_UNICODE);

    /* ========================== Historial de chat en sesión ========================== */
    if (!isset($_SESSION['VISTA360_CHAT']) || !is_array($_SESSION['VISTA360_CHAT'])) {
        $_SESSION['VISTA360_CHAT'] = [];
    }
    $hist = $_SESSION['VISTA360_CHAT'];

    // Construye texto plano del historial para el prompt (máx ~8 turnos)
    $maxTurns = 8;
    if (count($hist) > $maxTurns) {
        $hist = array_slice($hist, -$maxTurns);
    }

    $historialTexto = '';
    foreach ($hist as $turno) {
        $role    = strtoupper($turno['role'] ?? 'USER');
        $content = (string)($turno['content'] ?? '');
        $historialTexto .= $role . ': ' . $content . "\n";
    }

    /* ========================== Helper: llamar endpoints internos ========================== */

    /**
     * Llama un endpoint local (misma instancia) vía HTTP POST JSON
     * con manejo mejorado de errores y fallback de archivo.
     */
    $callLocalJson = function (string $path, array $payload): array {
        // Resolver ruta física
        $localPath = $_SERVER['DOCUMENT_ROOT'] . $path;

        // Mapeo de archivos alternativos (cliente completo <-> crédito cliente)
        $alternatives = [
            '/eia/Vista-360/ia_cliente_completo.php' => '/eia/Vista-360/ia_credito_cliente.php',
            '/eia/Vista-360/ia_credito_cliente.php'  => '/eia/Vista-360/ia_cliente_completo.php',
        ];

        if (!is_file($localPath) && isset($alternatives[$path])) {
            $altPath  = $alternatives[$path];
            $altLocal = $_SERVER['DOCUMENT_ROOT'] . $altPath;
            if (is_file($altLocal)) {
                $path      = $altPath;
                $localPath = $altLocal;
            }
        }

        if (!is_file($localPath)) {
            return ['ok' => false, 'error' => 'Endpoint no encontrado: ' . $path];
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $url    = $scheme . $host . $path;

        $ch = curl_init($url);
        if ($ch === false) {
            return ['ok' => false, 'error' => 'No se pudo inicializar cURL para ' . $path];
        }

        // Marca de origen
        $payload['_from_vista360'] = true;

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => $jsonPayload,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($resp === false) {
            return ['ok' => false, 'error' => 'Error cURL: ' . $err];
        }

        $data = json_decode($resp, true);
        if (!is_array($data)) {
            // Intentar extraer mensaje de error si es HTML
            if (strpos($resp, '<') !== false) {
                $errorSimple = 'El endpoint devolvió HTML en lugar de JSON';
                if (preg_match('/<title[^>]*>(.*?)<\/title>/i', $resp, $matches)) {
                    $errorSimple .= ': ' . strip_tags($matches[1]);
                } elseif (preg_match('/<h1[^>]*>(.*?)<\/h1>/i', $resp, $matches)) {
                    $errorSimple .= ': ' . strip_tags($matches[1]);
                }
                $errorSimple .= " (HTTP {$code})";
            } else {
                $errorSimple = 'Respuesta inválida (no JSON), HTTP ' . $code;
            }
            return ['ok' => false, 'error' => $errorSimple];
        }

        return $data;
    };

    /**
     * Función de fallback para buscar clientes si ia_cliente_completo.php falla.
     * Busca por nombre en Venta.Nombre + Contacto.
     */
    $buscarClienteFallback = function (string $nombre): array {
        global $mysqli, $pros;

        $clientes = [];

        /* --------- 1) CLIENTES (Venta + Contacto) --------- */
        $patternVenta = '%' . $mysqli->real_escape_string($nombre) . '%';

        $sqlVenta = "
            SELECT
                v.Id          AS id_venta,
                v.Nombre      AS cliente,
                v.IdContact   AS id_contacto,
                v.Status      AS status_venta,
                v.Producto    AS producto,
                v.CostoVenta  AS costo_venta,
                v.FechaRegistro,
                c.Mail        AS email,
                c.Telefono    AS telefono
            FROM Venta v
            LEFT JOIN Contacto c ON c.id = v.IdContact
            WHERE v.Nombre LIKE ?
            ORDER BY v.Nombre ASC, v.Id DESC
            LIMIT 10
        ";

        if ($stmt = $mysqli->prepare($sqlVenta)) {
            $stmt->bind_param('s', $patternVenta);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $clientes[] = [
                    'tipo'           => 'cliente',
                    'id_venta'       => (int)$row['id_venta'],
                    'id_prospecto'   => null,
                    'nombre'         => (string)$row['cliente'],
                    'email'          => (string)$row['email'],
                    'telefono'       => (string)$row['telefono'],
                    'producto'       => (string)$row['producto'],
                    'status_venta'   => (string)$row['status_venta'],
                    'costo_venta'    => (float)$row['costo_venta'],
                    'fecha_registro' => (string)$row['FechaRegistro'],
                ];
            }

            $stmt->close();
        }

        /* --------- 2) PROSPECTOS (BD u557645733_prospectos) --------- */
        if ($pros) {
            $patternPros = '%' . $pros->real_escape_string($nombre) . '%';

            $sqlPros = "
                SELECT
                    p.Id        AS id_prospecto,
                    p.FullName  AS nombre,
                    p.NoTel     AS telefono,
                    p.Email     AS email,
                    p.Servicio_Interes AS servicio,
                    p.Papeline  AS pipeline,
                    p.PosPapeline AS pos,
                    p.Alta      AS alta
                FROM prospectos p
                WHERE p.FullName LIKE ?
                ORDER BY p.Alta DESC
                LIMIT 10
            ";

            if ($stmtP = $pros->prepare($sqlPros)) {
                $stmtP->bind_param('s', $patternPros);
                $stmtP->execute();
                $resP = $stmtP->get_result();

                while ($row = $resP->fetch_assoc()) {
                    $clientes[] = [
                        'tipo'           => 'prospecto',
                        'id_venta'       => null,
                        'id_prospecto'   => (int)$row['id_prospecto'],
                        'nombre'         => (string)$row['nombre'],
                        'email'          => (string)$row['email'],
                        'telefono'       => (string)$row['telefono'],
                        'producto'       => (string)($row['servicio'] ?? ''),
                        'status_venta'   => 'PROSPECTO',
                        'costo_venta'    => 0.0,
                        'fecha_registro' => (string)$row['alta'],
                        'pipeline'       => (string)($row['pipeline'] ?? ''),
                        'pos_pipeline'   => (int)($row['pos'] ?? 0),
                    ];
                }

                $stmtP->close();
            }
        }

        return [
            'ok'               => true,
            'tipo'             => 'busqueda_fallback',
            'busqueda'         => $nombre,
            'total_resultados' => count($clientes),
            'clientes'         => $clientes,
        ];
    };

    /* ========================== NUEVO: Detección local de intenciones ========================== */
    
    /**
     * Detección local de intenciones sin depender de OpenAI
     */
    function detectarAccionLocal(string $mensaje): array {
        $mensajeLower = mb_strtolower(trim($mensaje), 'UTF-8');
        
        // Patrones para detección de búsqueda de clientes
        $buscarPatrones = [
            '/\b(busca|buscar|localiza|encuentra|muestra|ver|revisa)\s+(al cliente|cliente|a)\s+([a-záéíóúñ\s]+)/iu',
            '/\b(cliente|venta|prospecto)\s+([a-záéíóúñ\s]+)/iu',
            '/\b([a-záéíóúñ\s]{3,})\s+(edad|años|fecha de nacimiento|nacimiento|cumpleaños)/iu',
        ];
        
        foreach ($buscarPatrones as $patron) {
            if (preg_match($patron, $mensajeLower, $matches)) {
                $nombre = trim($matches[count($matches)-1]);
                if (strlen($nombre) > 2) {
                    return [
                        'accion' => 'buscar_cliente',
                        'argumentos' => ['nombre' => $nombre],
                        'nota_sistema' => 'Detectado localmente: búsqueda de cliente'
                    ];
                }
            }
        }
        
        // Patrones para información demográfica (edad, etc.)
        if (preg_match('/\b(edad|años|fecha de nacimiento|nacimiento|cumpleaños|dato personal)\b/iu', $mensajeLower)) {
            // Intentar extraer nombre del contexto o del mensaje
            if (preg_match('/\b([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+\s+[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+)\b/u', $mensaje, $matches)) {
                return [
                    'accion' => 'informacion_demografica',
                    'argumentos' => ['nombre' => $matches[1]],
                    'nota_sistema' => 'Detectado localmente: solicitud de datos demográficos'
                ];
            }
        }
        
        // Patrones para envío de correos
        if (preg_match('/\b(envía|envia|enviar|manda|mandar|envíale|mandale)\s+(la |el )?(póliza|poliza|ficha|fichas|estado de cuenta|liga de pago|link de pago|mercado pago)/iu', $mensajeLower, $matches)) {
            $tipo = 'auto'; // Usar 'auto' para que ia_correo_auto.php decida según estatus
            if (preg_match('/\b(póliza|poliza)\b/iu', $mensajeLower)) $tipo = 'poliza';
            if (preg_match('/\b(ficha|fichas)\b/iu', $mensajeLower)) $tipo = 'fichas';
            if (preg_match('/\b(estado de cuenta|estado cuenta)\b/iu', $mensajeLower)) $tipo = 'estado_cuenta';
            if (preg_match('/\b(liga|link|enlace|pagar|mercado pago|mp)\b/iu', $mensajeLower)) $tipo = 'liga_pago';
            
            // Intentar extraer ID de venta
            if (preg_match('/venta\s+(\d+)/i', $mensaje, $matchesId)) {
                return [
                    'accion' => 'enviar_correo',
                    'argumentos' => ['id_venta' => (int)$matchesId[1], 'tipo' => $tipo],
                    'nota_sistema' => 'Detectado localmente: envío de correo con ID de venta'
                ];
            }
        }
        
        // Patrones para estadísticas del empleado
        if (preg_match('/\b(c[oó]mo voy|mis ventas|mi desempeño|mis resultados|resumen del mes|ventas del mes|p[oó]lizas vendidas)\b/iu', $mensajeLower)) {
            return [
                'accion' => 'estadisticas_empleado_mes',
                'argumentos' => [],
                'nota_sistema' => 'Detectado localmente: estadísticas del empleado'
            ];
        }
        
        // Patrones para coaching/ayuda
        if (preg_match('/\b(ayuda|ayúdame|c[oó]mo|técnica|objeci[oó]n|argumento|consejo|tip|mejorar)\b/iu', $mensajeLower)) {
            return [
                'accion' => 'ninguna',
                'argumentos' => [],
                'nota_sistema' => 'Detectado localmente: solicitud de coaching/ayuda'
            ];
        }
        
        // Si no coincide con nada, asumir búsqueda
        if (preg_match('/\b([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+\s+[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+)\b/u', $mensaje, $matches)) {
            return [
                'accion' => 'buscar_cliente',
                'argumentos' => ['nombre' => $matches[1]],
                'nota_sistema' => 'Detectado localmente: nombre encontrado, asumiendo búsqueda'
            ];
        }
        
        return [
            'accion' => 'ninguna',
            'argumentos' => [],
            'nota_sistema' => 'No se detectó acción específica'
        ];
    }

    /* ========================== CONTEXTO CONVERSACIONAL ========================== */
    // Si hay historial reciente y el mensaje actual es corto/vago,
    // asumir que se refiere al último cliente mencionado
    
    $ultimoClienteMencionado = '';
    if (!empty($hist)) {
        // Buscar el último cliente mencionado en el historial
        foreach (array_reverse($hist) as $turno) {
            if ($turno['role'] === 'assistant') {
                // Buscar patrones de cliente en la respuesta de la IA
                if (preg_match('/Cliente:\s*([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+(?:\s+[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+)*)/iu', $turno['content'], $matches)) {
                    $ultimoClienteMencionado = trim($matches[1]);
                    break;
                }
                // O buscar por "Encontré X cliente(s):"
                elseif (preg_match('/Encontr[ée]\s+\d+\s+cliente.*?\b([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+(?:\s+[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+)*)/iu', $turno['content'], $matches)) {
                    $ultimoClienteMencionado = trim($matches[1]);
                    break;
                }
            }
        }
    }
    
    // Si el mensaje es vago pero hay un cliente en contexto, agregarlo
    $mensajeParaDeteccion = $mensajeUsuario;
    if ($ultimoClienteMencionado && 
        (preg_match('/^(qu[ée]\s+|cu[aá]ndo\s+|d[óo]nde\s+|c[óo]mo\s+|env[ií]a|manda|edad|años|nacimiento)/iu', $mensajeUsuario) ||
         strlen($mensajeUsuario) < 20)) {
        
        // Agregar contexto al mensaje para mejor detección
        $mensajeParaDeteccion = $mensajeUsuario . ' ' . $ultimoClienteMencionado;
    }

    /* ========================== NUEVO: Generar respuesta HTML local ========================== */
    
    /**
     * Genera respuesta HTML sin OpenAI
     */
    function generarRespuestaLocal(string $accionEjecutada, array $toolResult, string $mensajeUsuario, array $contextoUsuario): string {
        
        switch ($accionEjecutada) {
            case 'buscar_cliente':
                if (!empty($toolResult['ok']) && !empty($toolResult['clientes'])) {
                    $total = count($toolResult['clientes']);
                    $html = "<p><strong>🔍 Encontré {$total} resultado(s):</strong></p>";
                    
                    // Mostrar primeros 3 resultados
                    $mostrar = array_slice($toolResult['clientes'], 0, 3);
                    $html .= "<ul>";
                    foreach ($mostrar as $cliente) {
                        $tipo = $cliente['tipo'] === 'cliente' ? '👤 Cliente' : '🔍 Prospecto';
                        $nombre = htmlspecialchars($cliente['nombre'] ?? '');
                        $html .= "<li><strong>{$tipo}:</strong> {$nombre}";
                        
                        if (!empty($cliente['email'])) {
                            $html .= " - 📧 " . htmlspecialchars($cliente['email']);
                        }
                        if (!empty($cliente['telefono'])) {
                            $html .= " - 📞 " . htmlspecialchars($cliente['telefono']);
                        }
                        
                        // Si es cliente con venta
                        if (!empty($cliente['id_venta'])) {
                            $html .= " - #" . $cliente['id_venta'];
                        }
                        
                        $html .= "</li>";
                    }
                    $html .= "</ul>";
                    
                    if ($total > 3) {
                        $html .= "<p><em>... y " . ($total - 3) . " más</em></p>";
                    }
                    
                    // Sugerencias
                    $html .= "<p><small>💡 <strong>Siguientes pasos:</strong></small></p>";
                    $html .= "<ul>";
                    $html .= "<li><small>Para ver saldo: \"¿Cuánto debe [nombre]?\"</small></li>";
                    $html .= "<li><small>Para enviar correo: \"Envía la póliza de [nombre]\"</small></li>";
                    if ($total > 1) {
                        $html .= "<li><small>Para ser más específico: incluye el apellido</small></li>";
                    }
                    $html .= "</ul>";
                    
                    return $html;
                } else {
                    return "<p>No encontré clientes o prospectos con ese criterio.</p>
                           <p><small>💡 Intenta con:</small></p>
                           <ul>
                           <li><small>Nombre completo (ej: \"Juan Pérez\")</small></li>
                           <li><small>Solo el primer apellido</small></li>
                           <li><small>Verificar la ortografía</small></li>
                           </ul>";
                }
                
            case 'enviar_correo':
                if (!empty($toolResult['ok']) && !empty($toolResult['enviado'])) {
                    $cliente = $toolResult['cliente_nombre'] ?? 'Cliente';
                    $tipo = $toolResult['tipo_enviado'] ?? ($toolResult['tipo_nombre'] ?? 'correo');
                    $email = $toolResult['email_destino'] ?? '';
                    $status = $toolResult['status_venta'] ?? '';
                    
                    $html = "<p><strong>✅ Correo enviado exitosamente</strong></p>";
                    $html .= "<p>Para: <strong>{$cliente}</strong></p>";
                    $html .= "<p>Email: {$email}</p>";
                    $html .= "<p>Tipo: {$tipo}</p>";
                    if ($status) {
                        $html .= "<p>Estatus: {$status}</p>";
                    }
                    
                    $html .= "<p><small>💡 <strong>Acciones sugeridas:</strong></small></p>";
                    $html .= "<ul>";
                    $html .= "<li><small>Confirma con el cliente la recepción</small></li>";
                    $html .= "<li><small>Programa seguimiento en 48 horas</small></li>";
                    $html .= "<li><small>Registra cualquier comentario</small></li>";
                    $html .= "</ul>";
                    
                    return $html;
                } elseif (!empty($toolResult['clientes_encontrados'])) {
                    // Se buscó primero porque no había ID de venta
                    $total = count($toolResult['clientes_encontrados']);
                    $html = "<p>Encontré {$total} cliente(s). Para enviar correo necesito:</p>";
                    $html .= "<ol>";
                    $html .= "<li><strong>Que especifiques cuál cliente</strong> (si hay varios)</li>";
                    $html .= "<li><strong>El ID de venta</strong> si lo conoces (ej: \"venta 123\")</li>";
                    $html .= "<li><strong>El tipo de correo</strong> (póliza, fichas, estado de cuenta, liga de pago)</li>";
                    $html .= "</ol>";
                    $html .= "<p><small>Ejemplo completo: \"Envía la póliza de la venta 456\"</small></p>";
                    return $html;
                } elseif (!empty($toolResult['error'])) {
                    $error = htmlspecialchars($toolResult['error']);
                    return "<p><strong>❌ No se pudo enviar el correo</strong></p>
                           <p>{$error}</p>
                           <p><small>💡 Verifica:</small></p>
                           <ul>
                           <li><small>Que el ID de venta sea correcto</small></li>
                           <li><small>Que el cliente tenga email registrado</small></li>
                           <li><small>Tu conexión a internet</small></li>
                           </ul>";
                } else {
                    return "<p>No pude enviar el correo.</p>
                           <p><small>💡 Verifica:</small></p>
                           <ul>
                           <li><small>Que el ID de venta sea correcto</small></li>
                           <li><small>Que el cliente tenga email registrado</small></li>
                           <li><small>Tu conexión a internet</small></li>
                           </ul>";
                }
                
            case 'estadisticas_empleado_mes':
                if (!empty($toolResult['ok'])) {
                    $ventas = $toolResult['ventas_mes'] ?? [];
                    $pagos = $toolResult['pagos_mes'] ?? [];
                    $prospectos = $toolResult['prospectos_mes'] ?? [];
                    
                    $nombre = $contextoUsuario['nombre'] ?? 'Ejecutivo';
                    
                    $html = "<p><strong>📊 Análisis de tu Desempeño</strong></p>";
                    $html .= "<p>¡Hola <strong>{$nombre}</strong>! Aquí tu resumen:</p>";
                    
                    $html .= "<ul>";
                    if (!empty($ventas['unidades'])) {
                        $html .= "<li><strong>Ventas:</strong> " . $ventas['unidades'] . " pólizas</li>";
                    }
                    if (!empty($ventas['importe'])) {
                        $html .= "<li><strong>Ingresos:</strong> $" . number_format($ventas['importe'], 0) . "</li>";
                    }
                    if (!empty($pagos['importe_total'])) {
                        $html .= "<li><strong>Cobranza:</strong> $" . number_format($pagos['importe_total'], 0) . "</li>";
                    }
                    if (!empty($prospectos['total'])) {
                        $html .= "<li><strong>Prospectos activos:</strong> " . $prospectos['total'] . "</li>";
                    }
                    if (!empty($pagos['importe_mora']) && $pagos['importe_mora'] > 0) {
                        $html .= "<li><strong style='color:#e74c3c;'>⚠️ Moratorios:</strong> $" . number_format($pagos['importe_mora'], 0) . "</li>";
                    }
                    $html .= "</ul>";
                    
                    // Recomendaciones basadas en datos
                    $html .= "<p><strong>🎯 Recomendaciones:</strong></p>";
                    $html .= "<ol>";
                    
                    if (!empty($pagos['importe_mora']) && $pagos['importe_mora'] > 0) {
                        $html .= "<li><strong>Prioridad alta:</strong> Contactar clientes en mora</li>";
                    }
                    
                    if (empty($prospectos['total']) || $prospectos['total'] < 10) {
                        $html .= "<li><strong>Prioridad media:</strong> Agregar nuevos prospectos</li>";
                    }
                    
                    $html .= "<li><strong>Acción continua:</strong> Seguimiento a prospectos calientes</li>";
                    $html .= "</ol>";
                    
                    return $html;
                } else {
                    return "<p>No pude obtener tus estadísticas en este momento.</p>
                           <p><small>💡 Puedo ayudarte con:</small></p>
                           <ul>
                           <li><small>Búsqueda de clientes/prospectos</small></li>
                           <li><small>Envío de correos (póliza, fichas, estado de cuenta)</small></li>
                           <li><small>Cálculo de saldos y moratorios</small></li>
                           </ul>";
                }
                
            case 'informacion_demografica':
                // Respuesta específica para solicitudes de edad/datos personales
                $html = "<p><strong>📋 Información Demográfica</strong></p>";
                $html .= "<p>Actualmente el sistema no tiene registrada la edad/fecha de nacimiento en la ficha del cliente.</p>";
                $html .= "<p><small>💡 <strong>Sugerencias para obtener esta información:</strong></small></p>";
                $html .= "<ol>";
                $html .= "<li><small><strong>Llamar al cliente</strong> usando el teléfono registrado</small></li>";
                $html .= "<li><small><strong>Enviar WhatsApp</strong> solicitando amablemente la fecha de nacimiento</small></li>";
                $html .= "<li><small><strong>Revisar el contrato físico</strong> o expediente del cliente</small></li>";
                $html .= "<li><small><strong>Actualizar el sistema</strong> una vez obtenida la información</small></li>";
                $html .= "</ol>";
                $html .= "<p><small>📌 <strong>Frase sugerida:</strong> \"Hola [nombre], necesitamos actualizar tu ficha con tu fecha de nacimiento para mejor servicio. ¿Podrías compartirla?\"</small></p>";
                return $html;
                
            case 'ninguna':
            default:
                // Respuestas de coaching predefinidas
                $mensajeLower = mb_strtolower($mensajeUsuario, 'UTF-8');
                
                // Coaching de ventas
                if (preg_match('/\b(c[oó]mo\s+vender|t[eé]cnica\s+de\s+venta|argumento|objeci[oó]n|cierre\s+de\s+venta)\b/iu', $mensajeLower)) {
                    return "<p><strong>🎓 Coaching de Ventas</strong></p>
                           <p>Para mejorar tus ventas:</p>
                           <ol>
                           <li><strong>Escucha activa:</strong> Entiende las necesidades reales antes de ofrecer</li>
                           <li><strong>Historia de valor:</strong> Cuenta casos de éxito de clientes similares</li>
                           <li><strong>Objeción como oportunidad:</strong> Cada \"no\" revela una necesidad no cubierta</li>
                           <li><strong>Cierre natural:</strong> \"¿Qué fecha le parece mejor para empezar?\" en lugar de \"¿Quiere comprar?\"</li>
                           </ol>
                           <p><small>💡 <strong>Frase poderosa:</strong> \"Muchos clientes como usted empezaron con dudas, pero hoy están tranquilos sabiendo que su familia está protegida.\"</small></p>";
                }
                
                // Coaching de cobranza
                if (preg_match('/\b(c[oó]mo\s+cobrar|mora|atraso|retraso\s+de\s+pago)\b/iu', $mensajeLower)) {
                    return "<p><strong>🎓 Coaching de Cobranza</strong></p>
                           <p>Estrategias efectivas:</p>
                           <ol>
                           <li><strong>Contacto temprano:</strong> No esperes a que esté en mora</li>
                           <li><strong>Empatía + firmeza:</strong> \"Entiendo tu situación, pero el compromiso es importante\"</li>
                           <li><strong>Opciones, no exigencias:</strong> Ofrece planes de pago flexibles</li>
                           <li><strong>Registro sistemático:</strong> Anota cada contacto para seguimiento</li>
                           </ol>
                           <p><small>💡 <strong>Frase efectiva:</strong> \"¿Qué monto puedes comprometer esta semana para regularizar tu situación?\"</small></p>";
                }
                
                // Coaching de prospección
                if (preg_match('/\b(c[oó]mo\s+conseguir|prospectar|lead|nuevo\s+cliente)\b/iu', $mensajeLower)) {
                    return "<p><strong>🎓 Coaching de Prospección</strong></p>
                           <p>Para conseguir más prospectos:</p>
                           <ol>
                           <li><strong>Dedicación diaria:</strong> 2 horas exclusivas para prospección</li>
                           <li><strong>Red de referidos:</strong> Pide referencias a clientes satisfechos</li>
                           <li><strong>Calificación rápida:</strong> Evalúa viabilidad en primera llamada</li>
                           <li><strong>Seguimiento sistemático:</strong> Mínimo 7 contactos antes de descartar</li>
                           </ol>
                           <p><small>💡 <strong>Frase de apertura:</strong> \"Hola [nombre], soy [tu nombre] de KASU. Llamo porque ayudamos a familias a planificar su tranquilidad futura. ¿Tienes 2 minutos para contarte cómo?\"</small></p>";
                }
                
                // Respuesta general de coaching
                return "<p><strong>🤖 Asistente KASU</strong></p>
                       <p>¡Hola " . ($contextoUsuario['nombre'] ?? 'ejecutivo') . "! Puedo ayudarte con:</p>
                       <ul>
                       <li><strong>🔍 Búsqueda de clientes/prospectos</strong> (por nombre)</li>
                       <li><strong>💰 Cálculo de saldos y moratorios</strong></li>
                       <li><strong>📧 Envío de correos</strong> (póliza, fichas, estado de cuenta, liga de pago)</li>
                       <li><strong>📊 Análisis de tu desempeño</strong></li>
                       <li><strong>🎓 Coaching de ventas, cobranza y prospección</strong></li>
                       </ul>
                       <p><small>💡 <strong>Ejemplos:</strong></small></p>
                       <ul>
                       <li><small>\"Busca al cliente Juan Pérez\"</small></li>
                       <li><small>\"¿Cuánto debe la venta 123?\"</small></li>
                       <li><small>\"Envía la póliza de María García\"</small></li>
                       <li><small>\"Analiza mi desempeño este mes\"</small></li>
                       <li><small>\"¿Cómo puedo mejorar mis ventas?\"</small></li>
                       </ul>";
        }
    }

    /* ========================== PRIMERA ETAPA: Decidir acción (con o sin IA) ========================== */

    $accionData = null;
    
    // Intentar con OpenAI primero
    if ($openaiAvailable) {
        try {
            $promptAccion = <<<PROMPT
Eres un ORQUESTADOR de acciones para la IA comercial de KASU.

Contexto del usuario (JSON):
{$contextJson}

Historial reciente de conversación:
{$historialTexto}

Mensaje actual del usuario:
"{$mensajeParaDeteccion}"

Tu tarea es decidir UNA sola acción principal a ejecutar y estructurar argumentos claros.

ACCIONES PERMITIDAS
-------------------
1) "ninguna"
   - Cuando el usuario:
     * Pide explicación de productos, manejo de objeciones, scripts de venta.
     * Pide ayuda general ("¿cómo respondo si...?", "ayúdame a argumentar...").
   - No tocas base de datos ni envías correos.

2) "buscar_cliente"
   - Cuando pide buscar, localizar o revisar información de clientes:
     * "busca a ana maria"
     * "localiza a carlos pérez"
     * "revisa clientes llamados luis"
     * "muéstrame el saldo de juan pérez"
   - También cuando pide ver crédito, adeudo, saldo, mora de alguien:
     * "¿cuánto debe maría lópez?"
     * "revisa el crédito de pedro"
   - ARGUMENTOS:
     {
       "nombre": "texto a buscar en el nombre del cliente"
     }

3) "informacion_demografica"
   - Cuando pregunta específicamente por datos personales del cliente:
     * "¿qué edad tiene?"
     * "fecha de nacimiento de..."
     * "¿cuándo cumple años?"
   - ARGUMENTOS:
     {
       "nombre": "nombre del cliente"
     }

4) "estadisticas_empleado_mes"
   - Cuando pregunta por su propio desempeño, números, metas o resultados:
     * "¿cómo voy este mes?"
     * "mis ventas de noviembre"
     * "¿cuántas pólizas llevo?"
     * "resumen de mis resultados este mes"
   - ARGUMENTOS:
     {
       "id_usuario": "ID del usuario logueado",
       "mes": "YYYY-MM" (si el usuario menciona un mes/año; si no, usar mes actual)
     }

5) "enviar_correo"
   - Cuando explícitamente quiere enviar un CORREO transaccional a un cliente:
     * "envía póliza de la venta 123"
     * "manda las fichas de pago de la venta 450"
     * "envía el estado de cuenta de juan pérez, venta 890"
     * "manda la liga de pago MP a la venta 321"
   - Primero debes distinguir el tipo de correo que quiere:
     * "póliza" o "poliza"          → tipo = "poliza"
     * "fichas" o "fichas de pago"  → tipo = "fichas"
     * "estado de cuenta"           → tipo = "estado_cuenta"
     * "liga de pago", "link de pago", "pago con tarjeta", "mercado pago", "MP"
                                    → tipo = "liga_pago"
     * Si solo dice "envía/manda correo" → tipo = "auto" (el sistema decide según estatus)
   - ID de venta:
     * Si el usuario menciona explícitamente "venta 123", "venta #123", "ID 123",
       extrae ese número y úsalo como "id_venta".
     * Si NO da un ID de venta pero menciona un nombre de cliente,
       entonces NO elijas "enviar_correo"; elige "buscar_cliente" primero.
   - ARGUMENTOS:
     {
       "id_venta": 123,
       "tipo": "auto|poliza|fichas|estado_cuenta|liga_pago"
     }

FORMATO DE RESPUESTA
--------------------
Responde ÚNICAMENTE un JSON con este esquema EXACTO:

{
  "accion": "ninguna | buscar_cliente | informacion_demografica | enviar_correo | estadisticas_empleado_mes",
  "argumentos": { ... },
  "nota_sistema": "breve explicación en español para el backend; el usuario NO la ve"
}

- "accion" debe ser una de las cinco cadenas indicadas.
- "argumentos" debe ser SIEMPRE un objeto JSON (aunque vaya vacío).
- NO agregues comentarios ni texto fuera del JSON.
PROMPT;

            $textoAccion = openai_simple_text($promptAccion, 650);
            $accionData  = json_decode($textoAccion, true);
            
            if (!is_array($accionData)) {
                throw new Exception('OpenAI devolvió JSON inválido');
            }
            
        } catch (Throwable $e) {
            error_log('[Vista360 Chat] OpenAI falló para decisión de acción: ' . $e->getMessage());
            // Continuar con detección local
            $accionData = null;
        }
    }
    
    // Si OpenAI falló o no está disponible, usar detección local
    if (!$accionData || !is_array($accionData)) {
        $accionData = detectarAccionLocal($mensajeParaDeteccion);
    }
    
    $accion     = (string)($accionData['accion'] ?? 'ninguna');
    $argumentos = is_array($accionData['argumentos'] ?? null) ? $accionData['argumentos'] : [];
    
    // Completar argumentos si faltan
    if (($accion === 'buscar_cliente' || $accion === 'informacion_demografica') && empty($argumentos['nombre'])) {
        // Intentar extraer nombre del mensaje
        if (preg_match('/\b([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+\s+[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+)\b/u', $mensajeParaDeteccion, $matches)) {
            $argumentos['nombre'] = $matches[1];
        } else {
            $argumentos['nombre'] = $mensajeParaDeteccion;
        }
    }
    
    if ($accion === 'estadisticas_empleado_mes') {
        $argumentos['id_usuario'] = $idUsuario;
        if (empty($argumentos['mes'])) {
            $argumentos['mes'] = date('Y-m');
        }
    }

    /* ========================== SEGUNDA ETAPA: Ejecutar acción backend ========================== */

    $toolResult      = ['ok' => true, 'detalle' => 'sin_accion'];
    $accionEjecutada = $accion;
    
    // Detectar si solicita envío para encadenamiento automático
    $solicitaEnvio = (bool)preg_match('/\b(envia|enviar|manda|mandar|mándale|mandale)\b/i', $mensajeUsuario);

    try {
        switch ($accion) {

            case 'buscar_cliente':
            case 'informacion_demografica':
                // Para ambas acciones, primero buscamos el cliente
                $nombreBuscado = trim((string)($argumentos['nombre'] ?? $mensajeUsuario));
                if ($nombreBuscado === '') {
                    $nombreBuscado = $mensajeUsuario;
                }

                $payloadBusqueda = [
                    'tipo'   => 'buscar',
                    'nombre' => $nombreBuscado,
                    'limit'  => 10,
                ];

                $toolResult = $callLocalJson('/eia/Vista-360/ia_cliente_completo.php', $payloadBusqueda);

                // Detectar si NO hay resultados, aunque ok=true
                $hayResultados = false;
                if (!empty($toolResult['ok'])) {
                    if (!empty($toolResult['clientes']) && is_array($toolResult['clientes'])) {
                        $hayResultados = count($toolResult['clientes']) > 0;
                    } elseif (isset($toolResult['total_resultados'])) {
                        $hayResultados = ((int)$toolResult['total_resultados'] > 0);
                    }
                }

                // Si falló o no hay resultados, probar crédito_cliente y luego fallback BD
                if (empty($toolResult['ok']) || !$hayResultados) {
                    // Intentar endpoint antiguo
                    error_log('[Vista360] Sin resultados en ia_cliente_completo.php, intentando ia_credito_cliente.php');
                    $resultadoAntiguo = $callLocalJson('/eia/Vista-360/ia_credito_cliente.php', [
                        'nombre' => $nombreBuscado,
                        'limit'  => 10,
                    ]);

                    $toolResult = $resultadoAntiguo;

                    $hayResultados = false;
                    if (!empty($toolResult['ok'])) {
                        if (!empty($toolResult['clientes']) && is_array($toolResult['clientes'])) {
                            $hayResultados = count($toolResult['clientes']) > 0;
                        } elseif (isset($toolResult['total_resultados'])) {
                            $hayResultados = ((int)$toolResult['total_resultados'] > 0);
                        }
                    }

                    // Si todavía no hay nada, usar búsqueda directa en Venta + Prospectos
                    if (empty($toolResult['ok']) || !$hayResultados) {
                        error_log('[Vista360] Sin resultados en endpoints IA, usando fallback directo (Venta + prospectos)');
                        $toolResult = $buscarClienteFallback($nombreBuscado);
                    }
                }

                // Encadenar envío automático si el usuario pidió "envía/manda" y hay UN solo cliente
                if ($solicitaEnvio && !empty($toolResult['ok']) && $accion !== 'informacion_demografica') {
                    $lista = [];

                    if (!empty($toolResult['clientes']) && is_array($toolResult['clientes'])) {
                        $lista = $toolResult['clientes'];
                    } elseif (!empty($toolResult['resultado']['clientes']) && is_array($toolResult['resultado']['clientes'])) {
                        $lista = $toolResult['resultado']['clientes'];
                    }

                    if (count($lista) === 1) {
                        $clienteUnico  = $lista[0];
                        $idVentaEnviar = (int)($clienteUnico['id_venta'] ?? 0);

                        // Solo auto-enviamos si es cliente con venta, no si es prospecto
                        if ($idVentaEnviar > 0) {
                            // Determinar tipo de correo del mensaje
                            $tipoCorreo = 'auto'; // Por defecto 'auto' para que ia_correo_auto.php decida
                            if (preg_match('/\b(póliza|poliza)\b/i', $mensajeUsuario)) $tipoCorreo = 'poliza';
                            if (preg_match('/\b(ficha|fichas)\b/i', $mensajeUsuario)) $tipoCorreo = 'fichas';
                            if (preg_match('/\b(estado\s+de\s+cuenta|estado\s+cuenta)\b/i', $mensajeUsuario)) $tipoCorreo = 'estado_cuenta';
                            if (preg_match('/\b(liga|link|enlace|pagar|mercado\s+pago|mp)\b/i', $mensajeUsuario)) $tipoCorreo = 'liga_pago';
                            
                            $envio = $callLocalJson('/eia/Vista-360/ia_correo_auto.php', [
                                'modo'     => 'cliente',
                                'id_venta' => $idVentaEnviar,
                                'tipo'     => $tipoCorreo,
                            ]);

                            $toolResult = [
                                'ok'      => !empty($envio['ok']),
                                'accion'  => 'enviar_correo_auto',
                                'cliente' => $clienteUnico,
                                'envio'   => $envio,
                                'tipo_correo' => $tipoCorreo
                            ];
                            $accionEjecutada = 'enviar_correo';
                        }
                    }
                }

                // Si era solicitud de información demográfica, mantener esa acción
                if ($accion === 'informacion_demografica') {
                    $accionEjecutada = 'informacion_demografica';
                }
                break;

            case 'enviar_correo':
                // Para correos, necesitamos ID de venta
                $idVenta   = (int)($argumentos['id_venta'] ?? 0);
                $tipoCorreo = (string)($argumentos['tipo'] ?? 'auto');

                if ($idVenta <= 0) {
                    // Si no hay ID, intentar ayudar buscando primero
                    $nombreBuscado       = $mensajeUsuario;
                    $resultadoBusqueda   = $buscarClienteFallback($nombreBuscado);

                    if (!empty($resultadoBusqueda['ok']) && !empty($resultadoBusqueda['clientes'])) {
                        $toolResult = [
                            'ok'                   => true,
                            'accion'               => 'buscar_primero',
                            'clientes_encontrados' => $resultadoBusqueda['clientes'],
                            'mensaje'              => 'Encontré clientes. Necesito que especifiques cuál venta usar para enviar el correo.',
                        ];
                        $accionEjecutada = 'buscar_cliente'; // Se usa en el prompt de respuesta
                    } else {
                        $toolResult = ['ok' => false, 'error' => 'No encontré clientes para enviar correo.'];
                    }
                } else {
                    // Si tenemos ID de venta, usar tu ia_correo_auto.php (que es mucho mejor)
                    $envio = $callLocalJson('/eia/Vista-360/ia_correo_auto.php', [
                        'modo'     => 'cliente',
                        'id_venta' => $idVenta,
                        'tipo'     => $tipoCorreo,
                    ]);
                    
                    $toolResult = $envio;
                    
                    // Si el envío falló pero tenemos error específico, mantenerlo
                    if (!empty($envio['error'])) {
                        $toolResult['error'] = $envio['error'];
                    }
                }
                break;

            case 'estadisticas_empleado_mes':
                $idUsuarioStats = (string)($argumentos['id_usuario'] ?? $idUsuario);
                $mesStats       = (string)($argumentos['mes'] ?? date('Y-m'));

                $toolResult = $callLocalJson('/eia/Vista-360/ia_stats_empleado_mes.php', [
                    'id_usuario' => $idUsuarioStats,
                    'mes'        => $mesStats,
                ]);
                break;

            case 'ninguna':
            default:
                $accionEjecutada = 'ninguna';
                $toolResult      = ['ok' => true, 'detalle' => 'sin_accion'];
                break;
        }
    } catch (Throwable $eTool) {
        error_log('[Vista360 Chat] Error en acción: ' . $eTool->getMessage());
        $toolResult = ['ok' => false, 'error' => 'Error interno: ' . $eTool->getMessage()];
    }

    /* ========================== TERCERA ETAPA: Generar respuesta HTML (con o sin IA) ========================== */

    $htmlRespuesta = '';
    
    // Intentar con OpenAI si está disponible
    if ($openaiAvailable) {
        try {
            $toolJson = json_encode($toolResult, JSON_UNESCAPED_UNICODE);
            
            $promptRespuesta = <<<PROMPT
Eres la IA comercial conversacional de KASU.

Tienes:
- CONTEXTO_USUARIO (JSON):
{$contextJson}

- ACCION_EJECUTADA: {$accionEjecutada}

- RESULTADO_DE_LA_ACCION (JSON):
{$toolJson}

- HISTORIAL RECIENTE:
{$historialTexto}

- MENSAJE ACTUAL DEL USUARIO:
"{$mensajeUsuario}"

TU TAREA:
Generar una respuesta CORTA, CLARA y ACCIONABLE en ESPAÑOL, para mostrar in-app en un chat de la PWA.

REGLAS:
- Devuelve ÚNICAMENTE HTML sencillo: usa solo <p>, <ul>, <ol>, <li>, <strong>, <b>, <em>, <i>, <br>.
- Máximo 8 líneas visibles (por ejemplo 1–2 párrafos breves + 2–4 bullets).
- Si RESULTADO_DE_LA_ACCION.ok == false, NO muestres el error técnico.
  En su lugar, da sugerencias prácticas, por ejemplo:
  * "Verifica la conexión y vuelve a intentar"
  * "Revisa la ortografía del nombre"
  * "Reporta a Sistemas si el problema persiste"

ACCIONES ESPECÍFICAS:
- Si ACCION_EJECUTADA == "buscar_cliente":
  * Si hay resultados: menciona cuántos clientes encontraste y da 1 ejemplo.
  * Si no hay resultados: sugiere verificar ortografía o buscar por otro dato.
  * Si hubo error: da sugerencias prácticas (como arriba).

- Si ACCION_EJECUTADA == "informacion_demografica":
  * Informa amablemente que el sistema no tiene registrada la edad/fecha de nacimiento.
  * Sugiere formas de obtener esta información: llamar al cliente, enviar WhatsApp, revisar contrato.
  * Proporciona una frase sugerida para solicitar la información educadamente.

- Si ACCION_EJECUTADA == "enviar_correo":
  * Si se envió: confirma claramente "<strong>Correo enviado</strong>" y sugiere confirmar recepción.
  * Si no se pudo: indica que se necesita el ID de venta o elegir al cliente correcto.
  * Si hay status_venta: menciona el estatus (ej: "cliente ACTIVO, se envió póliza").

- Si ACCION_EJECUTADA == "estadisticas_empleado_mes":
  * Resume brevemente el desempeño (ventas, pólizas, etc. si están disponibles).
  * Da 1–2 recomendaciones concretas de acción para mejorar.

- Si ACCION_EJECUTADA == "ninguna":
  * Actúa como coach comercial.
  * Responde directamente al mensaje del usuario con tips, frases o guías.

NUNCA digas "según los datos" o "basado en el JSON".
Habla directamente al usuario: "Encontré...", "Te sugiero...", "Puedes..."
Devuelve solo el HTML final, sin texto adicional.
PROMPT;

            $htmlRespuesta = openai_simple_text($promptRespuesta, 850);
            
            // Sanitizar HTML permitido
            $allowedTags   = '<p><ul><ol><li><strong><b><em><i><br>';
            $htmlRespuesta = trim(strip_tags($htmlRespuesta, $allowedTags));
            
        } catch (Throwable $e) {
            error_log('[Vista360 Chat] OpenAI falló para generar respuesta: ' . $e->getMessage());
            // Continuar con respuesta local
            $htmlRespuesta = '';
        }
    }
    
    // Si OpenAI falló o no generó respuesta, usar respuesta local
    if (empty($htmlRespuesta)) {
        $htmlRespuesta = generarRespuestaLocal($accionEjecutada, $toolResult, $mensajeUsuario, $contextoUsuario);
    }
    
    if ($htmlRespuesta === '') {
        $htmlRespuesta = '<p>No pude generar una respuesta en este momento. Intenta reformular tu pregunta.</p>';
    }

    /* ========================== Actualizar historial en sesión ========================== */
    $hist[] = ['role' => 'user',      'content' => $mensajeUsuario];
    $hist[] = ['role' => 'assistant', 'content' => strip_tags($htmlRespuesta)];

    if (count($hist) > 20) {
        $hist = array_slice($hist, -20);
    }
    $_SESSION['VISTA360_CHAT'] = $hist;

    /* ========================== Respuesta final ========================== */
    echo json_encode([
        'ok'               => true,
        'html'             => $htmlRespuesta,
        'accion'           => $accionEjecutada,
        'tool_result'      => $toolResult,
        'contexto_usuario' => $contextoUsuario,
        'usando_openai'    => $openaiAvailable,
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    error_log('[Vista360 Chat Acciones] ' . $e->getMessage());
    http_response_code(500);

    // Respuesta de error más amigable para el front
    $errorAmigable = '<p>No pude procesar tu solicitud en este momento.</p>
                     <ul>
                       <li>Verifica tu conexión a internet</li>
                       <li>Intenta nuevamente en unos minutos</li>
                       <li>Si el problema persiste, contacta a soporte técnico</li>
                     </ul>';

    echo json_encode([
        'ok'    => false,
        'html'  => $errorAmigable,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}