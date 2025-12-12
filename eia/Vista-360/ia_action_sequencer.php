<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : ia_action_sequencer.php
 * Carpeta : /eia/Vista-360
 *
 * Qué hace:
 *  - Orquesta la ejecución de las acciones definidas en el "plan" del agente.
 *  - Construye un resumen de ejecución para que el front pueda mostrar:
 *        • Acciones realizadas
 *        • Información relevante
 *  - Implementa "pipelines" automáticos, por ejemplo:
 *        buscar_cliente_prospecto → calcular_estado_credito
 *    cuando el plan incluye meta['auto_credit_after_search'] = true.
 * ============================================================================
 */

class IAActionSequencer
{
    private IAToolsRegistry $toolsRegistry;

    public function __construct(IAToolsRegistry $registry)
    {
        $this->toolsRegistry = $registry;
    }

    /**
     * Ejecuta el plan completo.
     *
     * Estructura esperada de $plan:
     * [
     *   'reasoning'  => string,
     *   'actions'    => [
     *      ['tool' => 'nombre', 'args' => [...], 'confirm' => bool],
     *      ...
     *   ],
     *   'response'   => string,
     *   'next_steps' => [...],
     *   'meta'       => [... opcional ...]
     * ]
     */
    public function executePlan(array $plan, array $userContext): array
    {
        $results = [];
        $status  = 'success';
        $error   = null;

        $meta = $plan['meta'] ?? [];

        // ------------------------ 1) Ejecutar acciones del plan ------------------------
        foreach ($plan['actions'] as $index => $action) {
            if (empty($action['tool']) || !is_string($action['tool'])) {
                continue;
            }

            $toolName = $action['tool'];
            $args     = (array)($action['args'] ?? []);

            $toolResult = $this->toolsRegistry->executeTool($toolName, $args);

            // Aseguramos que el resultado lleve el nombre de la tool y el índice
            if (!is_array($toolResult)) {
                $toolResult = ['ok' => false, 'error' => 'Respuesta inválida de la tool.'];
            }
            $toolResult['tool']        = $toolName;
            $toolResult['action_index'] = $index;

            $results[] = $toolResult;

            if (empty($toolResult['ok'])) {
                $status = 'partial_failure';
                $error  = $toolResult['error'] ?? 'Error desconocido al ejecutar ' . $toolName;
                // NO rompemos necesariamente, dejamos que otras acciones ya ejecutadas
                // queden registradas en el resumen.
                break;
            }
        }

        // ------------------------ 2) Pipeline automático: saldo después de búsqueda ------------------------
        // Si el plan indica que, después de buscar al cliente, debe calcularse
        // automáticamente el estado de crédito cuando haya una venta única.
        if (!empty($meta['auto_credit_after_search']) && $status !== 'critical_error') {
            $pipeResult = $this->autoPipelineCreditAfterSearch($results, $userContext);

            if (!empty($pipeResult)) {
                $results[] = $pipeResult;
                if (empty($pipeResult['ok']) && $status === 'success') {
                    $status = 'partial_failure';
                    $error  = $pipeResult['error'] ?? 'No fue posible calcular el saldo automáticamente.';
                }
            }
        }

        // ------------------------ 3) Construir resumen de ejecución ------------------------
        $executionSummary = [
            'plan_reasoning' => $plan['reasoning'] ?? '',
            'results'        => $results,
        ];

        // Texto de respuesta base (se puede enriquecer con datos del pipeline)
        $responseText = $plan['response'] ?? 'Acción completada.';

        // Si el pipeline de crédito generó un resultado, intentamos enriquecer la respuesta
        foreach (array_reverse($results) as $res) {
            if (($res['tool'] ?? '') === 'calcular_estado_credito' && !empty($res['ok'])) {
                $responseText = $this->buildCreditResponseText($res, $responseText);
                break;
            }
        }

        $out = [
            'status'            => $status,
            'response'          => $responseText,
            'results'           => $results,
            'execution_summary' => $executionSummary,
            'next_steps'        => $plan['next_steps'] ?? [],
        ];

        if ($status === 'partial_failure') {
            $out['error'] = $error;
        }

        return $out;
    }

    /**
     * Ejecuta una sola acción previamente confirmada por el usuario.
     */
    public function executeConfirmedAction(array $action, array $userContext): array
    {
        $toolName = (string)($action['tool'] ?? '');
        $args     = (array)($action['args'] ?? []);

        $toolResult = $this->toolsRegistry->executeTool($toolName, $args);
        if (!is_array($toolResult)) {
            $toolResult = ['ok' => false, 'error' => 'Respuesta inválida de la tool.'];
        }
        $toolResult['tool'] = $toolName;

        return $toolResult;
    }

    // =========================================================================
    // Helpers internos
    // =========================================================================

    /**
     * Pipeline automático:
     * - Busca en los resultados una ejecución exitosa de buscar_cliente_prospecto.
     * - Si hay exactamente 1 cliente con una venta única, llama a calcular_estado_credito.
     */
    private function autoPipelineCreditAfterSearch(array $results, array $userContext): ?array
    {
        $searchResult = null;

        foreach ($results as $res) {
            if (($res['tool'] ?? '') === 'buscar_cliente_prospecto' && !empty($res['ok'])) {
                $searchResult = $res;
                break;
            }
        }

        if ($searchResult === null) {
            return null; // No hubo búsqueda exitosa, no aplicamos pipeline
        }

        $clientes = $searchResult['clientes'] ?? [];
        if (!is_array($clientes) || count($clientes) === 0) {
            return [
                'ok'   => false,
                'tool' => 'calcular_estado_credito',
                'error' => 'No se encontraron clientes para calcular saldo.',
            ];
        }

        // Si hay más de un cliente, por seguridad no asumimos cuál es
        if (count($clientes) !== 1) {
            return [
                'ok'   => false,
                'tool' => 'calcular_estado_credito',
                'error' => 'Hay varios clientes con ese nombre. Necesito que elijas primero el contrato/venta.',
            ];
        }

        $cliente = $clientes[0];

        // Intentar obtener un ID de venta único
        $ventaId = 0;

        // Caso 1: campo directo id_venta
        if (!empty($cliente['id_venta'])) {
            $ventaId = (int)$cliente['id_venta'];
        }

        // Caso 2: array de ventas con una sola venta
        if ($ventaId <= 0 && !empty($cliente['ventas']) && is_array($cliente['ventas'])) {
            // Si hay varias ventas no decidimos automáticamente
            if (count($cliente['ventas']) === 1 && !empty($cliente['ventas'][0]['id_venta'])) {
                $ventaId = (int)$cliente['ventas'][0]['id_venta'];
            } else {
                return [
                    'ok'   => false,
                    'tool' => 'calcular_estado_credito',
                    'error' => 'El cliente tiene varias ventas. Indica cuál contrato es para calcular el saldo.',
                ];
            }
        }

        if ($ventaId <= 0) {
            return [
                'ok'   => false,
                'tool' => 'calcular_estado_credito',
                'error' => 'No pude identificar el ID de venta del cliente para calcular el saldo.',
            ];
        }

        // Ejecutar la tool de cálculo de crédito
        $creditResult = $this->toolsRegistry->executeTool('calcular_estado_credito', [
            'id_venta' => $ventaId,
        ]);

        if (!is_array($creditResult)) {
            $creditResult = ['ok' => false, 'error' => 'Respuesta inválida al calcular estado de crédito.'];
        }

        $creditResult['tool']        = 'calcular_estado_credito';
        $creditResult['id_venta']    = $ventaId;
        $creditResult['cliente']     = $cliente['nombre'] ?? ($cliente['Nombre'] ?? '');
        $creditResult['pipeline']    = 'auto_credit_after_search';

        return $creditResult;
    }

    /**
     * Construye un texto de respuesta más específico cuando tenemos
     * el resultado de calcular_estado_credito.
     */
    private function buildCreditResponseText(array $creditResult, string $baseText): string
    {
        if (empty($creditResult['ok'])) {
            return $baseText;
        }

        $cliente = (string)($creditResult['cliente'] ?? '');
        $ventaId = (int)($creditResult['id_venta'] ?? 0);

        $estado   = (string)($creditResult['estado'] ?? '');
        $saldo    = isset($creditResult['pendiente_importe'])
            ? (float)$creditResult['pendiente_importe']
            : (isset($creditResult['saldo_pendiente']) ? (float)$creditResult['saldo_pendiente'] : 0.0);
        $proximoVenc = (string)($creditResult['proximo_vencimiento'] ?? ($creditResult['prox_pago'] ?? ''));
        $edadCredito = (string)($creditResult['edad_credito'] ?? '');

        $saldoFmt = '$' . number_format($saldo, 2, '.', ',');

        $partes = [];

        if ($cliente !== '') {
            $partes[] = "El cliente {$cliente}";
        } else {
            $partes[] = 'El cliente';
        }

        if ($ventaId > 0) {
            $partes[] = "en la venta #{$ventaId}";
        }

        if ($estado !== '') {
            $partes[] = "tiene estado de crédito: {$estado}";
        }

        $texto = implode(' ', $partes);
        $texto .= " y un saldo pendiente de {$saldoFmt}.";

        if ($proximoVenc !== '') {
            $texto .= " Próximo pago / vencimiento: {$proximoVenc}.";
        }

        if ($edadCredito !== '') {
            $texto .= " Edad del crédito: {$edadCredito}.";
        }

        return $texto;
    }
}