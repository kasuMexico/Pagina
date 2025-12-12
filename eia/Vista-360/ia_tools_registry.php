<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : ia_tools_registry.php
 * Carpeta : /eia/Vista-360
 * Qué hace: Sistema centralizado de registro y ejecución de tools para IA
 *           Versión simplificada y corregida
 * ============================================================================
 */

class IAToolsRegistry {

    private array $tools = [];
    private array $toolCallbacks = [];

    /**
     * Registra una nueva tool
     */
    public function registerTool(string $name, array $schema, callable $executor): void {
        $this->tools[$name] = [
            'type'     => 'function',
            'function' => array_merge(['name' => $name], $schema),
        ];
        $this->toolCallbacks[$name] = $executor;
    }

    /**
     * Obtiene todas las tools en formato OpenAI
     */
    public function getToolsForOpenAI(): array {
        return array_values($this->tools);
    }

    /**
     * Ejecuta una tool específica
     */
    public function executeTool(string $name, array $arguments): array {
        if (!isset($this->toolCallbacks[$name])) {
            return [
                'ok'    => false,
                'error' => "Tool no encontrada: {$name}",
                'tool'  => $name,
            ];
        }

        try {
            return call_user_func($this->toolCallbacks[$name], $arguments);
        } catch (Throwable $e) {
            error_log("[IA Tools Registry] Error ejecutando tool {$name}: " . $e->getMessage());
            return [
                'ok'    => false,
                'error' => "Error ejecutando {$name}: " . $e->getMessage(),
                'tool'  => $name,
            ];
        }
    }

    /**
     * Inicializa las tools básicas
     */
    public function initializeKasutools(): void {
        global $mysqli, $pros, $basicas, $financieras;

        // ==================== TOOL 1: BUSCAR CLIENTE/PROSPECTO ====================
        $this->registerTool(
            'buscar_cliente_prospecto',
            [
                'description' => 'Busca clientes y prospectos por nombre',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'nombre' => [
                            'type'        => 'string',
                            'description' => 'Nombre a buscar',
                        ],
                        'tipo'   => [
                            'type'    => 'string',
                            'enum'    => ['cliente', 'prospecto', 'ambos'],
                            'default' => 'ambos',
                        ],
                        'limit'  => [
                            'type'    => 'integer',
                            'default' => 10,
                        ],
                    ],
                    'required'   => ['nombre'],
                ],
            ],
            function (array $args): array {
                $nombre = trim($args['nombre'] ?? '');
                $tipo   = $args['tipo'] ?? 'ambos';
                $limit  = (int)($args['limit'] ?? 10);
                $limit  = ($limit > 0 && $limit <= 50) ? $limit : 10;

                if ($nombre === '') {
                    return ['ok' => false, 'error' => 'Nombre requerido'];
                }

                // Llamar al endpoint existente ia_cliente_completo.php
                // Nota: la propia callLocalEndpoint marca la llamada como interna (_from_vista360)
                return $this->callLocalEndpoint('/eia/Vista-360/ia_cliente_completo.php', [
                    'tipo'   => 'buscar',
                    'nombre' => $nombre,
                    'limit'  => $limit,
                    // "tipo" se incluye por si en el futuro ia_cliente_completo decide usarlo
                    'filtro_tipo' => $tipo,
                ]);
            }
        );

        // ==================== TOOL 2: CALCULAR ESTADO CRÉDITO ====================
        $this->registerTool(
            'calcular_estado_credito',
            [
                'description' => 'Calcula estado de crédito de una venta',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'id_venta' => [
                            'type'        => 'integer',
                            'description' => 'ID de la venta',
                        ],
                    ],
                    'required'   => ['id_venta'],
                ],
            ],
            function (array $args) use ($financieras): array {
                $idVenta = (int)($args['id_venta'] ?? 0);

                if ($idVenta <= 0) {
                    return ['ok' => false, 'error' => 'ID de venta inválido'];
                }

                if (!$financieras || !($financieras instanceof Financieras)) {
                    return ['ok' => false, 'error' => 'Sistema financiero no disponible'];
                }

                // estado_mora_corriente ya devuelve un array con el estado de crédito
                $estado = $financieras->estado_mora_corriente($idVenta);

                // Normalizamos a que siempre tenga 'ok'
                if (is_array($estado)) {
                    if (!isset($estado['ok'])) {
                        $estado['ok'] = true;
                    }
                    return $estado;
                }

                return [
                    'ok'    => false,
                    'error' => 'Respuesta inesperada al calcular estado de crédito',
                ];
            }
        );

        // ==================== TOOL 3: ENVIAR CORREO ====================
        $this->registerTool(
            'enviar_correo_cliente',
            [
                'description' => 'Envía correo a cliente (póliza, fichas, estado de cuenta o liga de pago)',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'id_venta' => [
                            'type'        => 'integer',
                            'description' => 'ID de la venta del cliente',
                        ],
                        'tipo_correo' => [
                            'type' => 'string',
                            'enum' => ['poliza', 'fichas', 'estado_cuenta', 'liga_pago'],
                        ],
                    ],
                    'required'   => ['id_venta', 'tipo_correo'],
                ],
            ],
            function (array $args): array {
                $idVenta = (int)($args['id_venta'] ?? 0);
                $tipo    = (string)($args['tipo_correo'] ?? '');

                if ($idVenta <= 0) {
                    return ['ok' => false, 'error' => 'ID de venta inválido'];
                }

                // Cargar funciones de correo si no existen
                if (!function_exists('ia_tool_enviar_poliza')) {
                    $helperFile = __DIR__ . '/ia_tools_correo.php';
                    if (is_file($helperFile)) {
                        require_once $helperFile;
                    }
                }

                $funciones = [
                    'poliza'        => 'ia_tool_enviar_poliza',
                    'fichas'        => 'ia_tool_enviar_fichas_pago',
                    'estado_cuenta' => 'ia_tool_enviar_estado_cuenta',
                    'liga_pago'     => 'ia_tool_enviar_liga_pago',
                ];

                if (!isset($funciones[$tipo])) {
                    return ['ok' => false, 'error' => "Tipo de correo no soportado: {$tipo}"];
                }

                $fn = $funciones[$tipo];
                if (!function_exists($fn)) {
                    return [
                        'ok'    => false,
                        'error' => "Función de envío no disponible en el servidor: {$fn}",
                    ];
                }

                $resultado = call_user_func($fn, $idVenta);

                if (is_array($resultado)) {
                    if (!isset($resultado['ok'])) {
                        $resultado['ok'] = true;
                    }
                    return $resultado;
                }

                return [
                    'ok'    => false,
                    'error' => 'Respuesta inesperada al enviar correo',
                ];
            }
        );
    }

    /**
     * Llama a endpoints locales (misma instancia) vía HTTP POST JSON.
     * Marca la llamada como interna para que el endpoint no exija sesión.
     */
    private function callLocalEndpoint(string $path, array $payload): array {
        // Marcar esta llamada como interna (whitelist en ia_cliente_completo.php)
        $payload['_from_vista360'] = true;

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $url    = $scheme . $host . $path;

        $ch          = curl_init($url);
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);

        if ($ch === false) {
            return [
                'ok'    => false,
                'error' => 'No se pudo inicializar cURL para ' . $path,
            ];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => $jsonPayload,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($resp === false) {
            return [
                'ok'    => false,
                'error' => 'Error cURL al llamar ' . $path . ': ' . $err,
            ];
        }

        $data = json_decode($resp, true);
        if (!is_array($data)) {
            // Puede ser HTML de error (por ejemplo, un fatal)
            return [
                'ok'    => false,
                'error' => 'Respuesta inválida desde ' . $path . ' (HTTP ' . $code . ')',
                'raw'   => mb_substr($resp, 0, 500),
            ];
        }

        return $data;
    }
}
