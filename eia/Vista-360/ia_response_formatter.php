<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : ia_response_formatter.php
 * Carpeta : /eia/Vista-360
 * Qué hace: Formatea respuestas del agente para la PWA.
 *           Convierte resultados en HTML, JSON y mensajes amigables.
 * ============================================================================
 */
class IAResponseFormatter
{
    /**
     * Formatea una respuesta exitosa para la PWA.
     */
    public function formatSuccessResponse(array $executionResult, array $userContext): array
    {
        $html        = $this->generateHTMLResponse($executionResult, $userContext);
        $data        = $this->extractDataForFrontend($executionResult);
        $suggestions = $this->generateNextStepSuggestions($executionResult, $userContext);

        return [
            'ok'                => true,
            'type'              => 'response',
            'html'              => $html,
            'data'              => $data,
            'suggestions'       => $suggestions,
            'execution_summary' => $executionResult['execution_summary'] ?? [],
            'timestamp'         => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Formatea una respuesta que requiere confirmación.
     */
    public function formatConfirmationResponse(array $confirmationData, array $userContext): array
    {
        $action       = $confirmationData['action'] ?? [];
        $actionNumber = (int)($confirmationData['action_id'] ?? 1);
        $totalActions = (int)($confirmationData['total_actions'] ?? 1);

        $html = $this->generateConfirmationHTML($action, $actionNumber, $totalActions, $userContext);

        return [
            'ok'                => true,
            'type'              => 'confirmation',
            'html'              => $html,
            'confirmation_data' => $confirmationData,
            // El id real de confirmación lo añade ia_agente_conversacional.php
            'actions_pending'   => $confirmationData['pending_actions'] ?? [],
            'timestamp'         => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Formatea una respuesta de error.
     */
    public function formatErrorResponse(array $errorResult, array $userContext): array
    {
        $errorMsg   = $errorResult['error'] ?? 'Ocurrió un error inesperado.';
        $html       = $this->generateErrorHTML($errorResult, $userContext);
        $suggestion = $errorResult['suggestion'] ?? [
            'action'  => 'reintentar',
            'message' => 'Intenta nuevamente.',
        ];

        return [
            'ok'           => false,
            'type'         => 'error',
            'html'         => $html,
            'error'        => $errorMsg,
            'error_details'=> $this->sanitizeErrorDetails($errorResult),
            'suggestions'  => $suggestion,
            'timestamp'    => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Genera HTML para respuesta exitosa.
     */
    private function generateHTMLResponse(array $executionResult, array $userContext): string
    {
        $response  = $executionResult['response'] ?? 'Acción completada exitosamente.';
        $summary   = $executionResult['execution_summary'] ?? [];
        $nextSteps = $summary['next_steps'] ?? ($executionResult['next_steps'] ?? []);
        $results   = $summary['results'] ?? [];

        $html  = '<div class="ia-response success">';
        $html .= '<p><strong>✅ ' . htmlspecialchars($response, ENT_QUOTES, 'UTF-8') . '</strong></p>';

        // Resumen de acciones ejecutadas
        if (!empty($results)) {
            $html .= '<div class="action-summary">';
            $html .= '<p><em>Acciones realizadas:</em></p>';
            $html .= '<ul>';

            foreach ($results as $result) {
                if (!empty($result['ok'])) {
                    $tool = $result['tool'] ?? 'acción';
                    $html .= '<li>✅ ' . htmlspecialchars($this->getToolDescription((string)$tool), ENT_QUOTES, 'UTF-8') . '</li>';
                }
            }

            $html .= '</ul></div>';
        }

        // Información relevante (ej. número de clientes, estado de crédito, etc.)
        if (!empty($results)) {
            $relevantData = $this->extractRelevantDataForDisplay($results);
            if (!empty($relevantData)) {
                $html .= '<div class="relevant-data">';
                $html .= '<p><strong>Información relevante:</strong></p>';
                $html .= '<ul>';
                foreach ($relevantData as $data) {
                    $html .= '<li>📊 ' . htmlspecialchars($data, ENT_QUOTES, 'UTF-8') . '</li>';
                }
                $html .= '</ul></div>';
            }
        }

        // Si alguna tool devolvió HTML propio, lo mostramos.
        if (!empty($results)) {
            foreach ($results as $result) {
                if (!empty($result['html']) && is_string($result['html'])) {
                    $html .= '<div class="ia-tool-html">' . $result['html'] . '</div>';
                    break; // sólo mostramos el primero para no saturar
                }
            }
        }

        // Próximos pasos sugeridos
        if (!empty($nextSteps)) {
            $html .= '<div class="next-steps">';
            $html .= '<p><strong>Siguientes pasos sugeridos:</strong></p>';
            $html .= '<ul>';
            foreach ($nextSteps as $step) {
                $html .= '<li>👉 ' . htmlspecialchars((string)$step, ENT_QUOTES, 'UTF-8') . '</li>';
            }
            $html .= '</ul></div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Genera HTML para confirmación.
     */
    private function generateConfirmationHTML(array $action, int $actionNumber, int $totalActions, array $userContext): string
    {
        $tool        = (string)($action['tool'] ?? 'acción');
        $args        = (array)($action['args'] ?? []);
        $description = $this->getActionDescription($tool, $args);

        $html  = '<div class="ia-confirmation">';
        $html .= '<p><strong>⚠️ Confirmación requerida</strong></p>';
        $html .= '<p>Acción ' . $actionNumber . ' de ' . $totalActions . ':</p>';
        $html .= '<div class="confirmation-details">';
        $html .= '<p>' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</p>';

        // Mostrar parámetros relevantes
        if (!empty($args)) {
            $html .= '<div class="action-args"><small>Parámetros:</small><ul>';
            foreach ($args as $key => $value) {
                if (!is_array($value) && !is_object($value)) {
                    $html .= '<li><code>' . htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8') .
                             '</code>: ' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '</li>';
                }
            }
            $html .= '</ul></div>';
        }

        $html .= '</div>';
        $html .= '<p><em>¿Deseas ejecutar esta acción?</em></p>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Genera HTML para error.
     */
    private function generateErrorHTML(array $errorResult, array $userContext): string
    {
        $errorMsg   = $errorResult['error'] ?? 'Ocurrió un error inesperado.';
        $suggestion = $errorResult['suggestion'] ?? null;

        $html  = '<div class="ia-response error">';
        $html .= '<p><strong>❌ ' . htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') . '</strong></p>';

        if (is_array($suggestion) && isset($suggestion['suggestions']) && is_array($suggestion['suggestions'])) {
            $html .= '<div class="suggestions">';
            $html .= '<p><em>Sugerencias:</em></p>';
            $html .= '<ul>';
            foreach ($suggestion['suggestions'] as $sug) {
                $html .= '<li>💡 ' . htmlspecialchars((string)$sug, ENT_QUOTES, 'UTF-8') . '</li>';
            }
            $html .= '</ul></div>';
        }

        $html .= '<p><small>Si el problema persiste, contacta a soporte técnico.</small></p>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Obtiene descripción amigable de una tool.
     */
    private function getToolDescription(string $tool): string
    {
        $descriptions = [
            'buscar_cliente_prospecto'   => 'Búsqueda de cliente/prospecto',
            'calcular_estado_credito'    => 'Cálculo de estado de crédito',
            'enviar_correo_cliente'      => 'Envío de correo',
            'generar_cotizacion_prospecto' => 'Generación de cotización',
            'obtener_informacion_completa'=> 'Consulta de información detallada',
            'actualizar_datos_contacto'  => 'Actualización de datos',
        ];

        return $descriptions[$tool] ?? $tool;
    }

    /**
     * Obtiene descripción detallada de una acción.
     */
    private function getActionDescription(string $tool, array $args): string
    {
        $base = $this->getToolDescription($tool);

        switch ($tool) {
            case 'buscar_cliente_prospecto':
                if (isset($args['nombre'])) {
                    return "Buscar cliente/prospecto: '{$args['nombre']}'";
                }
                break;

            case 'enviar_correo_cliente':
                if (isset($args['tipo_correo'])) {
                    $tipos = [
                        'poliza'        => 'póliza',
                        'fichas'        => 'fichas de pago',
                        'estado_cuenta' => 'estado de cuenta',
                        'liga_pago'     => 'liga de pago',
                    ];
                    $tipo = $tipos[$args['tipo_correo']] ?? $args['tipo_correo'];
                    return "Enviar {$tipo} al cliente";
                }
                break;

            case 'calcular_estado_credito':
                if (isset($args['id_venta'])) {
                    return "Calcular estado de crédito para venta #{$args['id_venta']}";
                }
                break;

            case 'generar_cotizacion_prospecto':
                if (isset($args['id_prospecto'])) {
                    return "Generar cotización para prospecto #{$args['id_prospecto']}";
                }
                break;
        }

        return $base;
    }

    /**
     * Extrae datos relevantes para el frontend (JSON).
     */
    private function extractDataForFrontend(array $executionResult): array
    {
        $data    = [];
        $summary = $executionResult['execution_summary'] ?? [];
        $results = $summary['results'] ?? [];

        foreach ($results as $result) {
            if (empty($result['ok'])) {
                continue;
            }

            switch ($result['tool'] ?? '') {
                case 'buscar_cliente_prospecto':
                    if (!empty($result['clientes'])) {
                        $data['clientes_encontrados'] = $result['clientes'];
                    }
                    break;

                case 'calcular_estado_credito':
                    $data['estado_credito'] = $result;
                    break;

                case 'generar_cotizacion_prospecto':
                    if (!empty($result['cotizacion'])) {
                        $data['cotizacion'] = $result['cotizacion'];
                    }
                    break;
            }
        }

        return $data;
    }

    /**
     * Extrae datos relevantes para mostrar en HTML.
     */
    private function extractRelevantDataForDisplay(array $results): array
    {
        $displayData = [];

        foreach ($results as $result) {
            if (empty($result['ok'])) {
                continue;
            }

            switch ($result['tool'] ?? '') {
                case 'buscar_cliente_prospecto':
                    if (!empty($result['total_resultados'])) {
                        $displayData[] = "Encontrados {$result['total_resultados']} cliente(s)/prospecto(s)";

                        if (!empty($result['clientes'][0])) {
                            $cliente = $result['clientes'][0];
                            $tipo    = ($cliente['tipo'] ?? '') === 'cliente' ? 'Cliente' : 'Prospecto';
                            $nombre  = $cliente['nombre'] ?? '';
                            if ($nombre !== '') {
                                $displayData[] = "{$tipo}: {$nombre}";
                            }
                        }
                    }
                    break;

                case 'calcular_estado_credito':
                    if (isset($result['estado'])) {
                        $estado  = (string)$result['estado'];
                        $importe = number_format((float)($result['pendiente_importe'] ?? 0), 2);
                        $displayData[] = "Estado: {$estado} - Pendiente: \${$importe}";
                    }
                    break;

                case 'generar_cotizacion_prospecto':
                    if (!empty($result['cotizacion']['precio'])) {
                        $precio = number_format((float)$result['cotizacion']['precio'], 2);
                        $displayData[] = "Cotización generada: \${$precio}";
                    }
                    break;

                case 'enviar_correo_cliente':
                    if (!empty($result['asunto'])) {
                        $displayData[] = "Correo enviado: {$result['asunto']}";
                    }
                    break;
            }
        }

        return array_slice($displayData, 0, 5);
    }

    /**
     * Genera sugerencias para siguientes pasos.
     */
    private function generateNextStepSuggestions(array $executionResult, array $userContext): array
    {
        $suggestions = [];
        $summary     = $executionResult['execution_summary'] ?? [];
        $results     = $summary['results'] ?? [];
        $lastTool    = null;
        $lastResult  = null;

        foreach (array_reverse($results) as $result) {
            if (!empty($result['ok'])) {
                $lastTool   = $result['tool'] ?? null;
                $lastResult = $result;
                break;
            }
        }

        if ($lastTool && $lastResult) {
            switch ($lastTool) {
                case 'buscar_cliente_prospecto':
                    if (!empty($lastResult['clientes'])) {
                        $cliente = $lastResult['clientes'][0] ?? null;
                        if ($cliente) {
                            if (($cliente['tipo'] ?? '') === 'cliente' && isset($cliente['id_venta'])) {
                                $suggestions[] = "Calcular estado de crédito del cliente";
                                $suggestions[] = "Enviar estado de cuenta";
                                $suggestions[] = "Actualizar datos de contacto";
                            } else {
                                $suggestions[] = "Generar cotización para este prospecto";
                                $suggestions[] = "Enviar correo de seguimiento";
                            }
                        }
                    }
                    break;

                case 'calcular_estado_credito':
                    if (($lastResult['estado'] ?? '') === 'MORA') {
                        $suggestions[] = "Enviar recordatorio de pago";
                        $suggestions[] = "Registrar promesa de pago";
                        $suggestions[] = "Crear ticket de seguimiento";
                    } else {
                        $suggestions[] = "Enviar comprobante de pago al día";
                        $suggestions[] = "Ofrecer productos complementarios";
                    }
                    break;

                case 'generar_cotizacion_prospecto':
                    $suggestions[] = "Programar llamada de seguimiento";
                    $suggestions[] = "Enviar recordatorio en 3 días";
                    $suggestions[] = "Agregar a campaña de email marketing";
                    break;

                case 'enviar_correo_cliente':
                    $suggestions[] = "Confirmar recepción del correo";
                    $suggestions[] = "Programar seguimiento en 48 horas";
                    $suggestions[] = "Registrar respuesta del cliente";
                    break;
            }
        }

        $rol = $userContext['usuario']['rol']['nombre'] ?? '';

        if (strpos($rol, 'Ventas') !== false || strpos($rol, 'Agente') !== false) {
            $suggestions[] = "Buscar nuevos prospectos";
            $suggestions[] = "Revisar pipeline de ventas";
        }

        if (strpos($rol, 'Cobranza') !== false) {
            $suggestions[] = "Revisar cartera en mora";
            $suggestions[] = "Contactar clientes con pagos vencidos";
        }

        return array_slice(array_unique($suggestions), 0, 3);
    }

    /**
     * Sanitiza detalles de error para no exponer información sensible.
     */
    private function sanitizeErrorDetails(array $errorResult): array
    {
        $sanitized = [];

        foreach ($errorResult as $key => $value) {
            if (is_string($value)) {
                $value = preg_replace('/\b\d{16}\b/', '[TARJETA]', $value);
                $value = preg_replace('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', '[EMAIL]', $value);
                $value = preg_replace('/\b\d{10}\b/', '[TELEFONO]', $value);
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }
}
