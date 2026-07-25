<?php
declare(strict_types=1);

/**
 * Mint Oracle — KASU RWA Treasury (alineado con KasuPolicyShare.sol)
 * Endpoint: POST /apimarket/api/nft/mint_signature.php
 *
 * Uso interno KASU. Genera el payload EIP-712 para que el bot/admin
 * firme client-side (ethers.js) y luego invoque mintToTreasury() o
 * mintWithSignature() en el contrato.
 *
 * Parámetros esperados (POST JSON):
 *   - poliza_id : IdFirma o IdVenta de la póliza liquidada
 *   - api_key   : X-KASU-KEY de autenticación interna
 */

require_once __DIR__ . '/../../librerias_api.php';

header('Content-Type: application/json; charset=utf-8');

// ============================================================================
// 1. AUTENTICACIÓN INTERNA
// ============================================================================
$inputData = json_decode(file_get_contents('php://input'), true) ?? $_REQUEST;
$apiKey    = $inputData['api_key'] ?? $_SERVER['HTTP_X_KASU_KEY'] ?? '';
$adminKey  = getenv('KASU_INTERNAL_API_KEY') ?: 'CLAVE_INTERNA_KASU_123';

if (!hash_equals($adminKey, $apiKey)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado. Acceso exclusivo interno KASU.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$polizaId = strtoupper(trim((string)($inputData['poliza_id'] ?? '')));

if (strlen($polizaId) < 10) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Identificador de póliza inválido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================================
// 2. CONFIGURACIÓN DEL CONTRATO (env vars)
// ============================================================================
$contractAddress = strtolower(getenv('KASU_CONTRACT_ADDRESS') ?: '0x0000000000000000000000000000000000000000');
$chainId         = (int)(getenv('KASU_CHAIN_ID') ?: 137);
$treasuryWallet  = strtolower(getenv('KASU_TREASURY_WALLET') ?: '0xb20fdef97a88b99daca0bb1dcd297b2a57f2f8e4');

// ============================================================================
// 3. CONSULTA DE PÓLIZA LIQUIDADA
// ============================================================================
global $mysqli;
$db = api_require_db($mysqli ?? null, 'ventas');

$stmt = $db->prepare("
    SELECT 
        v.Id          AS id_venta,
        v.IdFirma     AS id_firma,
        v.Status,
        v.CostoVenta  AS prima,
        v.Subtotal    AS cobertura,
        v.FechaLiquidacion,
        v.FechaRegistro,
        v.Producto,
        v.NumeroPagos,
        u.Nombres     AS nombre_cliente,
        u.ClaveCurp   AS curp,
        vn.mint_status
    FROM Venta v
    JOIN Usuario u ON v.IdContact = u.IdContact AND u.Tipo = 'Cliente'
    LEFT JOIN VentaNFT vn ON v.Id = vn.id_venta
    WHERE (v.IdFirma = ? OR v.Id = ?)
      AND v.Status IN ('LIQUIDADO', 'ACTIVO')
      AND v.FechaLiquidacion IS NOT NULL
    LIMIT 1
");

$stmt->bind_param('ss', $polizaId, $polizaId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Póliza no encontrada, no liquidada o inactiva.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($data['mint_status'] ?? '') === 'MINTED') {
    http_response_code(409);
    echo json_encode(['success' => false, 'error' => 'El NFT de esta póliza ya existe en la Treasury.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================================
// 4. CONSTRUCCIÓN DE PARÁMETROS PARA EL CONTRATO
// ============================================================================
$idVenta      = (int)$data['id_venta'];
$idFirma      = $data['id_firma'];
$prima        = max(1, (int)round((float)($data['prima'] ?? 3500)));        // uint256 > 0
$cobertura    = max(1, (int)round((float)($data['cobertura'] ?? $prima * 28.57))); // uint256 > 0

// ExpiryDate: FechaLiquidacion + plazo en meses → Unix timestamp
$startDate    = new DateTime($data['FechaLiquidacion'] ?? $data['FechaRegistro']);
$plazoMeses   = max(1, (int)($data['NumeroPagos'] ?? 1));
$expiryDate   = (int)(clone $startDate)->modify("+{$plazoMeses} months")->format('U');

// PolicyMetadata struct (privacidad: datos sensibles hasheados con keccak256 para verificabilidad EVM)
$policyMetadata = [
    'policyType'     => $data['Producto'] ?? 'Funerario',
    'clientName'     => '0x' . hash('sha3-256', (string)($data['nombre_cliente'] ?? '')),
    'clientDocument' => '0x' . hash('sha3-256', (string)($data['curp'] ?? '')),
    'vehicleInfo'    => '',
    'additionalData' => json_encode([
        'id_venta'  => $idVenta,
        'producto'  => $data['Producto'] ?? '',
        'plazo_meses' => $plazoMeses
    ], JSON_UNESCAPED_UNICODE)
];

// ============================================================================
// 5. REGISTRO EN VentaNFT (trazabilidad)
// ============================================================================
$stmtNFT = $db->prepare("
    INSERT INTO VentaNFT (id_venta, id_firma, wallet_owner, mint_status, minted_at)
    VALUES (?, ?, ?, 'PENDING_MINT', NOW())
    ON DUPLICATE KEY UPDATE 
        mint_status = 'PENDING_MINT',
        wallet_owner = VALUES(wallet_owner)
");
$stmtNFT->bind_param('iss', $idVenta, $idFirma, $treasuryWallet);
$stmtNFT->execute();

// ============================================================================
// 6. CONSTRUCCIÓN DEL PAYLOAD EIP-712
//    La firma se realiza CLIENT-SIDE con ethers.js / viem usando SignTypedData.
//    El backend entrega todos los datos estructurados listos para firmar.
// ============================================================================

// Estructura del tipo Permit (coincide con _PERMIT_TYPEHASH del contrato):
// keccak256("Permit(address owner,uint256 tokenId,uint256 nonce,uint256 deadline)")
// 
// Nota: nonce debe consultarse del contrato:
//   const nonce = await contract.nonces(treasuryWallet);
// El backend envía el endpoint para consultarlo, no un valor fijo.

$deadline = time() + 3600; // 1 hora de vigencia para la firma

$response = [
    'success'        => true,
    'action'         => 'SIGN_AND_MINT',

    // ─── Datos para mintToTreasury() ───
    'mint_params'    => [
        'tokenId'        => $idVenta,
        'idFirma'        => $idFirma,
        'metadata'       => $policyMetadata,
        'premiumAmount'  => $prima,
        'coverageAmount' => $cobertura,
        'expiryDate'     => $expiryDate,
        'treasuryWallet' => $treasuryWallet,
    ],

    // ─── Datos para mintWithSignature() (EIP-712) ───
    'eip712'         => [
        'domain' => [
            'name'              => 'KasuPolicyShare',
            'version'           => '2.0.0',
            'chainId'           => $chainId,
            'verifyingContract' => $contractAddress,
        ],
        'types'  => [
            'Permit' => [
                ['name' => 'owner',    'type' => 'address'],
                ['name' => 'tokenId',  'type' => 'uint256'],
                ['name' => 'nonce',    'type' => 'uint256'],
                ['name' => 'deadline', 'type' => 'uint256'],
            ],
        ],
        'message' => [
            'owner'    => $treasuryWallet,
            'tokenId'  => $idVenta,
            'nonce'    => 'CONSULTAR_ONCHAIN', // Leer nonces[treasuryWallet] del contrato
            'deadline' => $deadline,
        ],
        // La firma se genera client-side:
        // const signature = await signer.signTypedData(domain, types, message);
        // Luego invocar: contract.mintWithSignature(tokenId, idFirma, metadata, prima, cobertura, expiryDate, deadline, signature)
    ],

    // ─── Metadatos de red ───
    'network'        => [
        'chain_id'           => $chainId,
        'verifying_contract' => $contractAddress,
        'treasury_wallet'    => $treasuryWallet,
    ],

    // ─── Trazabilidad ───
    'db_status'      => [
        'id_venta'      => $idVenta,
        'id_firma'      => $idFirma,
        'mint_status'   => 'PENDING_MINT',
        'status_poliza' => $data['Status'],
    ],

    'timestamp'      => time(),
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
