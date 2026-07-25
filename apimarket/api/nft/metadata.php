<?php
declare(strict_types=1);

/**
 * OpenSea / ERC-721 Metadata API — KASU Policy Shares (alineado con KasuPolicyShare.sol)
 * Endpoint: GET /apimarket/api/nft/metadata.php?id={IdFirma}
 *
 * El contrato construye tokenURI así:
 *   _baseTokenURI = "https://apimarket.kasu.com.mx/api/nft/metadata.php?id="
 *   tokenURI(tokenId) → _baseTokenURI + idFirma
 *
 * Este endpoint recibe el IdFirma como parámetro 'id' y retorna JSON estándar ERC-721
 * compatible con OpenSea y marketplaces, con atributos alineados al PolicyInfo del contrato.
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
    echo json_encode(['error' => 'Se requiere el parámetro id (IdFirma).'], JSON_UNESCAPED_UNICODE);
    exit;
}

global $mysqli;
$db = api_require_db($mysqli ?? null, 'ventas');

// ============================================================================
// 1. CONSULTA DE PÓLIZA
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
        v.Producto,
        v.NumeroPagos      AS plazo_meses,
        u.ClaveCurp        AS curp
    FROM Venta v
    JOIN Usuario u ON v.IdContact = u.IdContact AND u.Tipo = 'Cliente'
    WHERE (v.IdFirma = ? OR v.Id = ?)
      AND v.Status IN ('ACTIVO', 'LIQUIDADO', 'FALLECIDO', 'LIQUIDADO_SINIESTRO')
    LIMIT 1
");

$stmt->bind_param('ss', $polizaId, $polizaId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    http_response_code(404);
    echo json_encode(['error' => 'Póliza no encontrada.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================================
// 2. ESTADO Y ENUM PolicyStatus (Solidity)
// ============================================================================
$isFallecido = in_array($data['Status'], ['FALLECIDO', 'LIQUIDADO_SINIESTRO'], true);

$policyStatusMap = [
    'ACTIVO'              => 0,
    'LIQUIDADO'           => 0,
    'FALLECIDO'           => 1,
    'LIQUIDADO_SINIESTRO' => 1,
    'CANCELADO'           => 3,
];
$policyStatusEnum = $policyStatusMap[$data['Status']] ?? 0;

// ============================================================================
// 3. MONTOS Y FECHAS (nativos, no wei)
// ============================================================================
$prima      = max(1, (int)round((float)($data['prima'] ?? 0)));
$cobertura  = max(1, (int)round((float)($data['cobertura'] ?? 0)));
$plazoMeses = max(1, (int)($data['plazo_meses'] ?? 1));

$fechaInicio     = new DateTime($data['FechaLiquidacion'] ?? $data['FechaRegistro']);
$startDateUnix   = (int)$fechaInicio->format('U');
$expiryDateUnix  = (int)(clone $fechaInicio)->modify("+{$plazoMeses} months")->format('U');

// ============================================================================
// 4. NOMBRE Y DESCRIPCIÓN DINÁMICOS
// ============================================================================
$idFirma     = $data['id_firma'];
$producto    = $data['Producto'] ?? 'Funerario';
$statusLabel = $isFallecido ? 'SINIESTRADO / EN LIQUIDACIÓN' : 'ACTIVO / EN RENDIMIENTO';

$name = "KASU Policy Share #{$idFirma}" . ($isFallecido ? ' [SINIESTRADA]' : '');

$description = $isFallecido
    ? "Póliza activada por fallecimiento. Este NFT da derecho al cobro del 50% del remanente del fondo F/0003. Royalty 5% a KASU Treasury en cada transferencia."
    : "Título colateralizado respaldado por el Fideicomiso F/0003 de KASU. Rendimiento garantizado 8% anual + IPC. Royalty 5% (ERC-2981) a la Treasury de KASU en cada venta secundaria.";

// ============================================================================
// 5. ATRIBUTOS ERC-721 (alineados con PolicyInfo del contrato)
// ============================================================================
$attributes = [
    // ─── Estado on-chain ───
    ['trait_type' => 'Policy Status',    'value' => $statusLabel],
    ['display_type' => 'number', 'trait_type' => 'Policy Status ID', 'value' => $policyStatusEnum],

    // ─── Datos financieros del contrato ───
    ['trait_type' => 'Producto',         'value' => $producto],
    ['display_type' => 'number', 'trait_type' => 'Prima (MXN)',        'value' => $prima],
    ['display_type' => 'number', 'trait_type' => 'Suma Asegurada (MXN)','value' => $cobertura],

    // ─── Fechas (tipo date para visores NFT) ───
    ['display_type' => 'date', 'trait_type' => 'Fecha de Inicio',     'value' => $startDateUnix],
    ['display_type' => 'date', 'trait_type' => 'Expiración',          'value' => $expiryDateUnix],

    // ─── Fondo y regalías ───
    ['trait_type' => 'Fondo de Respaldo',        'value' => 'Fideicomiso F/0003'],
    ['trait_type' => 'Royalty (ERC-2981)',       'value' => '5%'],
    ['trait_type' => 'Royalty Receiver',         'value' => '0xb20fdef97a88b99daca0bb1dcd297b2a57f2f8e4'],
    ['display_type' => 'number', 'trait_type' => 'Participación Remanente NFT', 'value' => 50],
];

// Si está siniestrado, agregar atributos extra
if ($isFallecido) {
    $attributes[] = ['trait_type' => 'Costo Servicio Funerario Est.', 'value' => '$40,000 MXN'];
    $attributes[] = ['trait_type' => 'Liquidación',                   'value' => '50% dueño NFT / 50% KASU'];
}

// ============================================================================
// 6. RESPUESTA ERC-721 ESTÁNDAR (OpenSea compatible)
// ============================================================================
echo json_encode([
    'name'             => $name,
    'description'      => $description,
    'image'            => "https://apimarket.kasu.com.mx/api/nft/image.php?id={$idFirma}",
    'external_url'     => "https://kasu.com.mx/nft?id={$idFirma}",
    'background_color' => '0A0E17',
    'attributes'       => $attributes,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
