<?php
declare(strict_types=1);

/**
 * NFT Metadata — KASU Policy Shares (ERC-721)
 * Endpoint: GET /api/nft/metadata.php?id={idVenta}
 *
 * Retorna JSON con atributos del NFT compatible con OpenSea.
 * No expone PII: solo producto, costo, status, rango de edad y genero.
 */

require_once __DIR__ . '/../../librerias_api.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

$tokenId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($tokenId <= 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Token ID no especificado o invalido'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Usar la conexion de ventas ($mysqli) definida en librerias_api.php
global $mysqli;
$db = api_require_db($mysqli ?? null, 'ventas');

// 1. Consultar datos de Venta y Usuario (sin exponer PII)
$stmt = $db->prepare("
    SELECT
        v.Id,
        v.Producto,
        v.CostoVenta,
        v.Status,
        v.FechaRegistro,
        u.ClaveCurp,
        vn.precio_nft_usdc,
        vn.mint_status
    FROM Venta v
    JOIN Usuario u ON v.IdContact = u.IdContact AND u.Tipo = 'Cliente'
    LEFT JOIN VentaNFT vn ON v.Id = vn.id_venta
    WHERE v.Id = ?
    LIMIT 1
");
$stmt->bind_param('i', $tokenId);
$stmt->execute();
$res = $stmt->get_result();
$venta = $res->fetch_assoc();

if (!$venta) {
    http_response_code(404);
    echo json_encode(['error' => 'Poliza no encontrada'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. Extraer Rango de Edad desde la cadena del Producto
preg_match('/(\d{2}a\d{2}|\d{2}y\+|Universidad)/i', (string)$venta['Producto'], $matches);
$rangoEdad = !empty($matches[0]) ? strtoupper($matches[0]) : 'GENERAL';

// 3. Determinar Genero para el trait
$curp = strtoupper(trim((string)$venta['ClaveCurp']));
$sexo = (strlen($curp) >= 11) ? substr($curp, 10, 1) : 'M';
$generoLabel = ($sexo === 'H') ? 'Masculino (Panchito)' : 'Femenino (Lele)';

// 4. Determinar Estado de Vigencia
$esMoraCancelado = ($venta['Status'] === 'CANCELADO');
$statusDisplay = $esMoraCancelado ? 'REVOCADO POR MORA (+90D)' : (string)$venta['Status'];

// 5. Construccion de Metadatos JSON (ERC-721 Standard)
$metadata = [
    'name'        => 'KASU Policy Share #' . $venta['Id'],
    'description' => 'Titulo de participacion al 50% de la utilidad pasiva de la poliza funeraria #'
                     . $venta['Id'] . ' respaldada por el Fideicomiso F0003 de KASU.',
    'image'       => 'https://apimarket.kasu.com.mx/api/nft/image.php?id=' . $venta['Id'],
    'external_url' => 'https://kasu.com.mx/nft/poliza/' . $venta['Id'],
    'attributes'  => [
        ['trait_type' => 'Folio Poliza',          'value' => '#' . $venta['Id']],
        ['trait_type' => 'Avatar Lele',           'value' => $generoLabel],
        ['trait_type' => 'Rango de Edad',         'value' => $rangoEdad],
        ['trait_type' => 'Anio de Emision',        'value' => date('Y', strtotime((string)$venta['FechaRegistro']))],
        ['trait_type' => 'Valor Fondo FIAT',      'value' => '$' . number_format((float)$venta['CostoVenta'], 2) . ' MXN'],
        ['trait_type' => 'Estatus Poliza',        'value' => $statusDisplay],
        [
            'display_type' => 'number',
            'trait_type'   => 'Participacion Utilidad (%)',
            'value'        => 50,
        ],
    ],
];

// Si la poliza fue revocada por mora, fondo negro en OpenSea
if ($esMoraCancelado) {
    $metadata['background_color'] = '000000';
}

echo json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
