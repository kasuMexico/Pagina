<?php
declare(strict_types=1);

/**
 * Valuation & Settlement API — KASU F/0003 (alineado con KasuPolicyShare.sol)
 * Endpoint: GET /apimarket/api/nft/valuation.php?id={IdFirma}
 *
 * Compatible con tokenURI del contrato: el parámetro 'id' recibe el IdFirma.
 * Retorna valuación financiera + breakdown de siniestro + enum PolicyStatus.
 */

require_once __DIR__ . '/../../librerias_api.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$polizaId = strtoupper(trim((string)($_GET['id'] ?? '')));

if (empty($polizaId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Se requiere el parámetro id (IdFirma).'], JSON_UNESCAPED_UNICODE);
    exit;
}

global $mysqli;
$db = api_require_db($mysqli ?? null, 'ventas');

// ============================================================================
// 1. CONSULTA DE PÓLIZA (soporta ACTIVO, LIQUIDADO, FALLECIDO)
// ============================================================================
$stmt = $db->prepare("
    SELECT 
        v.Id               AS id_venta,
        v.IdFirma          AS id_firma,
        v.Status,
        v.CostoVenta       AS prima,
        v.Subtotal         AS cobertura,
        v.FechaLiquidacion,
        v.FechaRegistro,
        v.NumeroPagos      AS plazo_meses
    FROM Venta v
    WHERE (v.IdFirma = ? OR v.Id = ?)
      AND v.Status IN ('ACTIVO', 'LIQUIDADO', 'FALLECIDO', 'LIQUIDADO_SINIESTRO')
    LIMIT 1
");

$stmt->bind_param('ss', $polizaId, $polizaId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Póliza no encontrada o no elegible.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================================
// 2. MAPEO PolicyStatus enum → Solidity
//    enum PolicyStatus { Active(0), Claimed(1), Expired(2), Revoked(3), Resolved(4) }
// ============================================================================
$policyStatusMap = [
    'ACTIVO'              => 0, // Active
    'LIQUIDADO'           => 0, // Active
    'FALLECIDO'           => 1, // Claimed
    'LIQUIDADO_SINIESTRO' => 1, // Claimed
    'CANCELADO'           => 3, // Revoked
];

$bdStatus         = $data['Status'];
$policyStatusEnum = $policyStatusMap[$bdStatus] ?? 0;

// ============================================================================
// 3. CÁLCULO DE FECHAS Y MONTOS (nativos, sin conversión wei)
// ============================================================================
$prima       = max(1, (int)round((float)($data['prima'] ?? 0)));
$cobertura   = max(1, (int)round((float)($data['cobertura'] ?? 0)));
$plazoMeses  = max(1, (int)($data['plazo_meses'] ?? 1));

$fechaInicio = new DateTime($data['FechaLiquidacion'] ?? $data['FechaRegistro']);
$fechaActual = new DateTime();
$aniosVigencia = max(0, $fechaInicio->diff($fechaActual)->days / 365.25);

$startDateUnix  = (int)$fechaInicio->format('U');
$expiryDateUnix = (int)(clone $fechaInicio)->modify("+{$plazoMeses} months")->format('U');

// isActive = status == Active AND no expirado
$isActive = ($policyStatusEnum === 0) && ($expiryDateUnix > time());

// ============================================================================
// 4. CÁLCULO FINANCIERO (Fondo 40% inicial + 12% anual = 8% base + 4% IPC)
// ============================================================================
$tasaRendimiento     = 0.12;
$valorAcumuladoTotal = $prima * pow((1 + $tasaRendimiento), $aniosVigencia);

// ============================================================================
// 5. SETTLEMENT BREAKDOWN (desglose en caso de siniestro/fallecimiento)
// ============================================================================
$costoServicioFunerario = min(40000.00, $valorAcumuladoTotal * 0.40);
$remanente              = max(0, $valorAcumuladoTotal - $costoServicioFunerario);
$payoutInversionista    = $remanente * 0.50;
$payoutKasuTreasury     = $remanente * 0.50;

// ============================================================================
// 6. RESPUESTA (compatible con getPolicyInfo() del contrato + negocio F/0003)
// ============================================================================
echo json_encode([
    'success'                => true,

    // ─── Campos alineados con PolicyInfo del contrato ───
    'id_firma'               => $data['id_firma'],
    'id_venta'               => (int)$data['id_venta'],
    'premium_amount'         => $prima,
    'coverage_amount'        => $cobertura,
    'start_date_unix'        => $startDateUnix,
    'expiry_date_unix'       => $expiryDateUnix,
    'policy_status'          => $policyStatusEnum,
    'policy_status_name'     => $bdStatus,
    'is_active'              => $isActive,

    // ─── Valuación financiera (negocio KASU) ───
    'anios_vigencia'         => round($aniosVigencia, 2),
    'tasa_rendimiento_anual' => '12% (8% base + 4% IPC est.)',
    'valor_acumulado_total'  => round($valorAcumuladoTotal, 2),

    // ─── Settlement breakdown (siniestro) ───
    'settlement_breakdown'   => [
        'valor_acumulado_total_mxn'   => round($valorAcumuladoTotal, 2),
        'costo_servicio_funerario_mxn'=> round($costoServicioFunerario, 2),
        'remanente_mxn'               => round($remanente, 2),
        'rendimiento_dueno_nft_mxn'   => round($payoutInversionista, 2),
        'rendimiento_dueno_nft_pct'   => '50%',
        'ganancia_tesoreria_kasu_mxn' => round($payoutKasuTreasury, 2),
        'ganancia_tesoreria_kasu_pct' => '50%',
    ],

    'timestamp'              => date('Y-m-d H:i:s'),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
