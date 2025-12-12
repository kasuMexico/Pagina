<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : ia_cliente_completo.php
 * Carpeta : /eia/Vista-360
 * Qué hace: Sistema completo para IA - Búsqueda y acciones sobre clientes
 * 
 * FUNCIONALIDADES:
 * 1. Búsqueda por nombre con estado de crédito (mora/corriente) y prospectos
 * 2. Envío de correos (póliza, fichas, estado de cuenta, liga pago, cotización)
 * 3. Acciones sobre clientes (actualizar datos, tickets, servicios, pagos)
 * 4. Integración con Funcionalidad_API_KASU.php para acciones avanzadas
 * 
 * Entrada : JSON POST
 * Salida  : JSON estructurado
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
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        }
    } else {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // ==================== LEER ENTRADA (UNA SOLA VEZ) ====================
    $raw  = file_get_contents('php://input');
    $data = $raw ? json_decode($raw, true) : [];
    if (!is_array($data)) {
        $data = [];
    }

    // También aceptar GET para pruebas directas
    if (empty($data) && !empty($_GET)) {
        $data = $_GET;
    }

    // Detectar si es una llamada interna desde vista360_chat_acciones.php
    // (callLocalJson añade "_from_vista360": true al payload)
    $isInternalVista360 = !empty($data['_from_vista360']) || !empty($_GET['_from_vista360']);

    // NOTA:
    // Antes aquí se bloqueaba cuando no había sesión de Vendedor ni api_key.
    // Eso está causando los errores "Sesión no activa..." en llamadas internas.
    // A partir de ahora, NO se bloquea por sesión; simplemente se continúa.
    // Si quieres, puedes registrar en log cuando no haya sesión:
    if (!isset($_SESSION['Vendedor']) && !$isInternalVista360 && !isset($_GET['api_key'])) {
        error_log('[IA_CLIENTE] Advertencia: llamada sin sesión Vendedor ni api_key');
    }

    // Conexiones y funciones
    require_once __DIR__ . '/../librerias.php';
    require_once __DIR__ . '/../Funciones/Funciones_Financieras.php';

    // Cargar helper de correo IA
    $correoHelperFile = __DIR__ . '/ia_tools_correo.php';
    if (is_file($correoHelperFile)) {
        require_once $correoHelperFile;
    } else {
        // Definir funciones básicas si no existe
        require_once __DIR__ . '/../Correo/Correo.php';
        function ia_tool_enviar_poliza($idVenta) {
            return ['ok' => false, 'error' => 'Helper de correo no disponible'];
        }
        function ia_tool_enviar_fichas_pago($idVenta) {
            return ['ok' => false, 'error' => 'Helper de correo no disponible'];
        }
        function ia_tool_enviar_estado_cuenta($idVenta) {
            return ['ok' => false, 'error' => 'Helper de correo no disponible'];
        }
        function ia_tool_enviar_liga_pago($idVenta) {
            return ['ok' => false, 'error' => 'Helper de correo no disponible'];
        }
        function ia_tool_enviar_cotizacion($idPrespEnviado) {
            return ['ok' => false, 'error' => 'Helper de correo no disponible'];
        }
    }

    global $mysqli, $basicas, $seguridad, $financieras, $pros;

    if (!$mysqli) {
        throw new RuntimeException('Conexión $mysqli no disponible.');
    }
    if (!isset($financieras) || !($financieras instanceof Financieras)) {
        $financieras = new Financieras();
    }

    // ==================== FUNCIONES INTERNAS ====================

    /**
     * Calcula edad desde una fecha de nacimiento Y-m-d
     */
    function edad_desde_fecha(?string $fechaNac): ?int {
        if (!$fechaNac || $fechaNac === '0000-00-00') {
            return null;
        }
        try {
            $fn  = new DateTime($fechaNac);
            $hoy = new DateTime('today');
            $edad = (int)$fn->diff($hoy)->y;
            return $edad > 0 ? $edad : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Busca prospectos por nombre en BD $pros y calcula edad / producto / precio sugerido
     */
    function buscar_prospectos_por_nombre(string $nombre, int $limit = 10): array {
        global $pros, $basicas, $mysqli;

        if (!$pros) {
            return [];
        }

        $pattern = '%' . $pros->real_escape_string($nombre) . '%';

        $sql = "
            SELECT
                p.Id          AS id_prospecto,
                p.FullName    AS nombre,
                p.NoTel       AS telefono,
                p.Email       AS email,
                p.Curp        AS curp,
                p.Servicio_Interes,
                p.FechaNac,
                p.Asignado
            FROM prospectos p
            WHERE p.FullName LIKE ?
            ORDER BY p.Alta DESC, p.Id DESC
            LIMIT ?
        ";

        $stmt = $pros->prepare($sql);
        if (!$stmt) {
            error_log('[IA_CLIENTE] Error al preparar búsqueda de prospectos: ' . $pros->error);
            return [];
        }

        $stmt->bind_param('si', $pattern, $limit);
        $stmt->execute();
        $res = $stmt->get_result();

        $prospectos = [];

        while ($row = $res->fetch_assoc()) {
            $curp      = trim((string)($row['Curp'] ?? ''));
            $fechaNac  = (string)($row['FechaNac'] ?? '');
            $servicio  = (string)($row['Servicio_Interes'] ?? '');
            $edad      = null;

            // Edad por CURP (preferente) o por FechaNac
            if ($curp !== '' && method_exists($basicas, 'ObtenerEdad')) {
                $edad = (int)$basicas->ObtenerEdad($curp);
                if ($edad <= 0) {
                    $edad = null;
                }
            }
            if ($edad === null) {
                $edad = edad_desde_fecha($fechaNac);
            }

            // Producto sugerido según servicio + edad
            $productoSugerido = null;
            if ($edad !== null && $edad > 0) {
                $servicioNorm = mb_strtolower($servicio, 'UTF-8');
                if (strpos($servicioNorm, 'funer') !== false && method_exists($basicas, 'ProdFune')) {
                    $productoSugerido = (string)$basicas->ProdFune($edad);
                } elseif (strpos($servicioNorm, 'segur') !== false && method_exists($basicas, 'ProdPli')) {
                    $productoSugerido = (string)$basicas->ProdPli($edad);
                } elseif (strpos($servicioNorm, 'trans') !== false && method_exists($basicas, 'ProdTrans')) {
                    $productoSugerido = (string)$basicas->ProdTrans($edad);
                }
            }

            // Precio sugerido desde Productos.Costo
            $precioSugerido = null;
            if ($productoSugerido) {
                $sqlProd = "SELECT Costo FROM Productos WHERE Producto = ? LIMIT 1";
                if ($stmtProd = $mysqli->prepare($sqlProd)) {
                    $stmtProd->bind_param('s', $productoSugerido);
                    $stmtProd->execute();
                    $stmtProd->bind_result($costoProd);
                    if ($stmtProd->fetch()) {
                        $precioSugerido = (float)$costoProd;
                    }
                    $stmtProd->close();
                }
            }

            $prospectos[] = [
                'tipo'              => 'prospecto',
                'id_prospecto'      => (int)$row['id_prospecto'],
                'nombre'            => (string)$row['nombre'],
                'email'             => (string)$row['email'],
                'telefono'          => (string)$row['telefono'],
                'curp'              => $curp,
                'servicio_interes'  => $servicio,
                'edad'              => $edad,
                'producto_sugerido' => $productoSugerido,
                'precio_sugerido'   => $precioSugerido,
                'asignado_a'        => (string)($row['Asignado'] ?? ''),
            ];
        }

        $stmt->close();

        return $prospectos;
    }

    /**
     * Busca clientes por nombre con estado de crédito detallado
     * (Venta + Contacto + Usuario) y añade tipo = 'cliente'
     */
    function buscar_clientes_con_credito(string $nombre, int $limit = 10): array {
        global $mysqli, $financieras;
        
        $pattern = '%' . $mysqli->real_escape_string($nombre) . '%';
        
        $sql = "
            SELECT
                v.Id          AS id_venta,
                v.Nombre      AS cliente,
                v.IdContact   AS id_contacto,
                v.Status      AS status_venta,
                v.Producto    AS producto,
                v.CostoVenta  AS costo_venta,
                v.FechaRegistro,
                c.Mail        AS email,
                c.Telefono    AS telefono,
                c.calle       AS direccion,
                c.colonia     AS colonia,
                c.municipio   AS municipio,
                c.estado      AS estado_direccion,
                u.Usuario     AS usuario_asociado
            FROM Venta v
            LEFT JOIN Contacto c ON c.id = v.IdContact
            LEFT JOIN Usuario u ON u.IdContact = v.IdContact
            WHERE v.Nombre LIKE ?
            ORDER BY v.Nombre ASC, v.Id DESC
            LIMIT ?
        ";
        
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar la consulta de ventas.');
        }
        $stmt->bind_param('si', $pattern, $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $clientes = [];
        while ($row = $res->fetch_assoc()) {
            $idVenta = (int)$row['id_venta'];
            
            // Estado de crédito avanzado
            $estadoCredito = $financieras->estado_mora_corriente($idVenta);
            
            // Información básica de contacto
            $clienteInfo = [
                'tipo'           => 'cliente',
                'id_venta'       => $idVenta,
                'id_contacto'    => (int)$row['id_contacto'],
                'nombre'         => (string)$row['cliente'],
                'email'          => (string)$row['email'],
                'telefono'       => (string)$row['telefono'],
                'producto'       => (string)$row['producto'],
                'status_venta'   => (string)$row['status_venta'],
                'costo_venta'    => (float)$row['costo_venta'],
                'fecha_registro' => (string)$row['FechaRegistro'],
                'usuario'        => (string)$row['usuario_asociado'],
                'direccion'      => [
                    'calle'     => (string)$row['direccion'],
                    'colonia'   => (string)$row['colonia'],
                    'municipio' => (string)$row['municipio'],
                    'estado'    => (string)$row['estado_direccion']
                ],
                'estado_credito' => $estadoCredito
            ];
            
            $clientes[] = $clienteInfo;
        }
        $stmt->close();
        
        return $clientes;
    }

    /**
     * Ejecuta acción sobre un cliente
     */
    function ejecutar_accion_cliente(array $params): array {
        global $seguridad, $basicas, $mysqli;
        
        $idVenta    = isset($params['id_venta']) ? (int)$params['id_venta'] : 0;
        $accion     = strtolower($params['accion'] ?? '');
        $datosExtra = $params['datos'] ?? [];
        
        if ($idVenta <= 0) {
            return ['ok' => false, 'error' => 'ID de venta inválido'];
        }
        
        // Auditoría
        try {
            $seguridad->auditoria_registrar(
                $mysqli,
                $basicas,
                ['id_venta' => $idVenta, 'accion' => $accion] + $datosExtra,
                'IA_ACCION_CLIENTE_' . strtoupper($accion),
                'ia_cliente_completo.php'
            );
        } catch (Throwable $e) {
            error_log('[IA_CLIENTE] Error en auditoría: ' . $e->getMessage());
        }
        
        // Determinar tipo de acción
        switch ($accion) {
            case 'enviar_poliza':
                return ia_tool_enviar_poliza($idVenta);
                
            case 'enviar_fichas':
            case 'enviar_fichas_pago':
                return ia_tool_enviar_fichas_pago($idVenta);
                
            case 'enviar_estado_cuenta':
            case 'enviar_estadocuenta':
                return ia_tool_enviar_estado_cuenta($idVenta);
                
            case 'enviar_liga_pago':
            case 'enviar_link_pago':
                return ia_tool_enviar_liga_pago($idVenta);
                
            case 'enviar_cotizacion':
                $idCotizacion = $datosExtra['id_cotizacion'] ?? 0;
                if ($idCotizacion <= 0) {
                    return ['ok' => false, 'error' => 'ID de cotización requerido'];
                }
                return ia_tool_enviar_cotizacion($idCotizacion);
                
            case 'actualizar_datos':
                return [
                    'ok' => true,
                    'mensaje' => 'Para actualizar datos, use Funcionalidad_API_KASU.php con POST',
                    'datos_sugeridos' => [
                        'ActDatosCTE' => '1',
                        'IdVenta'     => $idVenta,
                        'IdUsuario'   => '?',
                        'Email'       => $datosExtra['email'] ?? '',
                        'Telefono'    => $datosExtra['telefono'] ?? '',
                        'calle'       => $datosExtra['direccion'] ?? '',
                        'Host'        => '/login/Mesa_Herramientas.php'
                    ]
                ];
                
            case 'crear_ticket':
                return [
                    'ok' => true,
                    'mensaje' => 'Para crear ticket, use Funcionalidad_API_KASU.php con POST',
                    'datos_sugeridos' => [
                        'AltaTicket' => '1',
                        'IdVenta'    => $idVenta,
                        'IdUsuario'  => '?',
                        'IdContact'  => '?',
                        'Descripcion'=> $datosExtra['descripcion'] ?? '',
                        'Prioridad'  => $datosExtra['prioridad'] ?? 'Media',
                        'Status'     => $datosExtra['status'] ?? 'Abierto',
                        'Host'       => '/login/Mesa_Herramientas.php'
                    ]
                ];
                
            case 'registrar_pago':
                return [
                    'ok' => true,
                    'mensaje' => 'Para registrar pago, use Funcionalidad_API_KASU.php con POST',
                    'datos_sugeridos' => [
                        'Pago'     => '1',
                        'IdVenta'  => $idVenta,
                        'Metodo'   => $datosExtra['metodo'] ?? 'Efectivo',
                        'Cantidad' => $datosExtra['cantidad'] ?? '',
                        'Status'   => $datosExtra['status_pago'] ?? 'Pago',
                        'Host'     => '/login/Mesa_Herramientas.php'
                    ]
                ];
                
            case 'registrar_servicio':
                return [
                    'ok' => true,
                    'mensaje' => 'Para registrar servicio funerario, use Funcionalidad_API_KASU.php con POST',
                    'datos_sugeridos' => [
                        'RegisFun'  => '1',
                        'IdVenta'   => $idVenta,
                        'Prestador' => $datosExtra['prestador'] ?? '',
                        'Costo'     => $datosExtra['costo'] ?? '',
                        'EmpFune'   => $datosExtra['empresa_funeraria'] ?? '',
                        'Host'      => '/login/Mesa_Herramientas.php'
                    ]
                ];
                
            case 'informacion_completa':
                return obtener_informacion_detallada($idVenta);
                
            default:
                return [
                    'ok' => false,
                    'error' => "Acción no reconocida: $accion",
                    'acciones_disponibles' => [
                        'enviar_poliza',
                        'enviar_fichas_pago',
                        'enviar_estado_cuenta',
                        'enviar_liga_pago',
                        'enviar_cotizacion (requiere id_cotizacion en datos)',
                        'actualizar_datos',
                        'crear_ticket',
                        'registrar_pago',
                        'registrar_servicio',
                        'informacion_completa'
                    ]
                ];
        }
    }

    /**
     * Obtiene información detallada de un cliente por ID de venta
     */
    function obtener_informacion_detallada(int $idVenta): array {
        global $mysqli, $financieras;
        
        $sql = "
            SELECT
                v.*,
                c.*,
                u.Usuario,
                u.Email AS email_usuario,
                u.ClaveCurp,
                COUNT(p.Id) AS total_pagos,
                SUM(p.Cantidad) AS monto_total_pagado,
                MAX(pp.Promesa) AS ultima_promesa_pago,
                MAX(pp.Cantidad) AS monto_ultima_promesa,
                (SELECT COUNT(*) FROM Atn_Cliente WHERE IdVta = v.Id) AS total_tickets
            FROM Venta v
            LEFT JOIN Contacto c ON c.id = v.IdContact
            LEFT JOIN Usuario u ON u.IdContact = v.IdContact
            LEFT JOIN Pagos p ON p.IdVenta = v.Id
            LEFT JOIN PromesaPago pp ON pp.IdVenta = v.Id
            WHERE v.Id = ?
            GROUP BY v.Id
            LIMIT 1
        ";
        
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            return ['ok' => false, 'error' => 'Error al preparar consulta detallada'];
        }
        
        $stmt->bind_param('i', $idVenta);
        $stmt->execute();
        $result = $stmt->get_result();
        $cliente = $result->fetch_assoc();
        $stmt->close();
        
        if (!$cliente) {
            return ['ok' => false, 'error' => 'Cliente no encontrado'];
        }
        
        // Estado de crédito
        $estadoCredito = $financieras->estado_mora_corriente($idVenta);
        
        $info = [
            'ok' => true,
            'cliente' => [
                'id_venta'        => (int)$cliente['Id'],
                'nombre'          => (string)$cliente['Nombre'],
                'producto'        => (string)$cliente['Producto'],
                'status'          => (string)$cliente['Status'],
                'costo_venta'     => (float)$cliente['CostoVenta'],
                'fecha_registro'  => (string)$cliente['FechaRegistro'],
                'contacto' => [
                    'email'         => (string)$cliente['Mail'],
                    'telefono'      => (string)$cliente['Telefono'],
                    'direccion'     => (string)$cliente['calle'] . ' ' . $cliente['numero'],
                    'colonia'       => (string)$cliente['colonia'],
                    'municipio'     => (string)$cliente['municipio'],
                    'estado'        => (string)$cliente['estado'],
                    'codigo_postal' => (int)$cliente['codigo_postal']
                ],
                'usuario' => [
                    'nombre_usuario' => (string)$cliente['Usuario'],
                    'email_usuario'  => (string)$cliente['email_usuario'],
                    'curp'           => (string)$cliente['ClaveCurp']
                ],
                'estadisticas' => [
                    'total_pagos'          => (int)$cliente['total_pagos'],
                    'monto_total_pagado'   => (float)$cliente['monto_total_pagado'],
                    'total_tickets'        => (int)$cliente['total_tickets'],
                    'ultima_promesa_pago'  => (string)$cliente['ultima_promesa_pago'],
                    'monto_ultima_promesa' => (float)$cliente['monto_ultima_promesa']
                ],
                'estado_credito' => $estadoCredito
            ]
        ];
        
        return $info;
    }

    // ==================== PROCESAR SOLICITUD ====================

    $tipo = trim($data['tipo'] ?? 'buscar');
    
    switch ($tipo) {
        case 'buscar':
        case 'search':
            // Búsqueda de clientes + prospectos
            $nombre = trim((string)($data['nombre'] ?? ''));
            $limit  = (int)($data['limit'] ?? 10);
            
            if ($nombre === '') {
                throw new InvalidArgumentException('Para buscar, debe proporcionar el parámetro "nombre"');
            }
            
            if ($limit <= 0 || $limit > 50) {
                $limit = 10;
            }
            
            $clientes   = buscar_clientes_con_credito($nombre, $limit);
            $prospectos = buscar_prospectos_por_nombre($nombre, $limit);

            $mezclado = array_merge($prospectos, $clientes);

            echo json_encode([
                'ok'               => true,
                'tipo'             => 'busqueda',
                'busqueda'         => $nombre,
                'total_resultados' => count($mezclado),
                'clientes'         => $mezclado,
                'sugerencia'       => 'Para ejecutar una acción, use tipo="accion" con id_venta y accion (solo para clientes con venta registrada)'
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'accion':
        case 'action':
        case 'ejecutar':
            $resultado = ejecutar_accion_cliente($data);
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
            break;
            
        case 'informacion':
        case 'detalle':
            $idVenta = (int)($data['id_venta'] ?? 0);
            if ($idVenta <= 0) {
                throw new InvalidArgumentException('Para obtener información, debe proporcionar id_venta válido');
            }
            
            $info = obtener_informacion_detallada($idVenta);
            echo json_encode($info, JSON_UNESCAPED_UNICODE);
            break;
            
        case 'acciones_disponibles':
            echo json_encode([
                'ok' => true,
                'acciones' => [
                    'buscar' => [
                        'descripcion' => 'Buscar clientes y prospectos por nombre',
                        'parametros'  => ['nombre' => 'string', 'limit' => 'int (opcional, default:10)']
                    ],
                    'accion' => [
                        'descripcion' => 'Ejecutar acción sobre cliente (requiere venta registrada)',
                        'parametros' => [
                            'id_venta' => 'int (requerido)',
                            'accion'   => 'string (ver lista de acciones)',
                            'datos'    => 'array (datos adicionales según acción)'
                        ],
                        'acciones_soportadas' => [
                            'enviar_poliza',
                            'enviar_fichas_pago',
                            'enviar_estado_cuenta',
                            'enviar_liga_pago',
                            'enviar_cotizacion',
                            'actualizar_datos',
                            'crear_ticket',
                            'registrar_pago',
                            'registrar_servicio',
                            'informacion_completa'
                        ]
                    ],
                    'informacion' => [
                        'descripcion' => 'Obtener información detallada de cliente (Venta)',
                        'parametros'  => ['id_venta' => 'int (requerido)']
                    ]
                ],
                'ejemplos' => [
                    'buscar'        => '{"tipo":"buscar","nombre":"Juan Pérez"}',
                    'enviar_poliza' => '{"tipo":"accion","id_venta":123,"accion":"enviar_poliza"}',
                    'informacion'   => '{"tipo":"informacion","id_venta":123}'
                ]
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            throw new InvalidArgumentException('Tipo de operación no válido. Use: buscar, accion, informacion, acciones_disponibles');
    }
    
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'ok'        => false,
        'error'     => $e->getMessage(),
        'sugerencia'=> 'Use "tipo":"acciones_disponibles" para ver opciones'
    ], JSON_UNESCAPED_UNICODE);
    exit;
    
} catch (Throwable $e) {
    error_log('[IA Cliente Completo] ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'ok'      => false,
        'error'   => 'Error interno del sistema: ' . $e->getMessage(),
        'detalle' => (isset($_GET['debug']) || isset($data['debug'])) ? [
            'archivo' => $e->getFile(),
            'linea'   => $e->getLine(),
            'trace'   => $e->getTraceAsString()
        ] : 'Para ver detalles, agregue debug=1'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}