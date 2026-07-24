<?php
declare(strict_types=1);

/**
 * NFT Image Generator — KASU Policy Shares
 * Endpoint: GET /api/nft/image.php?id={idVenta}
 *
 * Genera PNG 1000x1000 con capas deterministas basadas en el id de venta.
 * Género deriva de la CURP registrada. Si la póliza está CANCELADO, se aplica grayscale.
 */

require_once __DIR__ . '/../../librerias_api.php';

header('Content-Type: image/png');
header('Cache-Control: public, max-age=31536000, immutable');

$idVenta = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idVenta <= 0) {
    dibujarErrorPNG('Poliza Invalida');
    exit;
}

// Usar la conexion de ventas ($mysqli) definida en librerias_api.php
global $mysqli;
$db = api_require_db($mysqli ?? null, 'ventas');

// 1. Consultar CURP y Status desde la BD
$stmt = $db->prepare("
    SELECT u.ClaveCurp, v.Status
    FROM Venta v
    JOIN Usuario u ON v.IdContact = u.IdContact AND u.Tipo = 'Cliente'
    WHERE v.Id = ?
    LIMIT 1
");
$stmt->bind_param('i', $idVenta);
$stmt->execute();
$res = $stmt->get_result();
$datos = $res->fetch_assoc();

if (!$datos) {
    dibujarErrorPNG("Poliza #{$idVenta} No Encontrada");
    exit;
}

// 2. Extraccion de Genero desde la CURP (Posicion 11)
$curp = strtoupper(trim((string)$datos['ClaveCurp']));
$sexo = (strlen($curp) >= 11) ? substr($curp, 10, 1) : 'M';
$folderGender = ($sexo === 'H') ? 'male' : 'female';

// 3. Generacion Determinista de Seed basada en idVenta
$seed = crc32('KASU_LELE_SEED_' . $idVenta);
mt_srand($seed);

// 4. Ruta base de assets (PNGs transparentes de 1000x1000px)
$baseTraitsDir = __DIR__ . '/../../assets/nft_traits/';

// 5. Seleccionar assets por capa (1-20 variantes)
$bgIndex       = mt_rand(1, 20);
$clothesIndex  = mt_rand(1, 20);
$hairIndex     = mt_rand(1, 20);
$headwearIndex = mt_rand(1, 20);
$eyesIndex     = mt_rand(1, 20);
$mouthIndex    = mt_rand(1, 20);
$eyewearIndex  = mt_rand(1, 20);

// 6. Crear lienzo principal
$imgCanvas = imagecreatetruecolor(1000, 1000);
imagealphablending($imgCanvas, true);
imagesavealpha($imgCanvas, true);

// 7. Funcion auxiliar para superponer capas PNG
function superponerCapa($canvas, string $pathFile): void {
    if (file_exists($pathFile)) {
        $layer = imagecreatefrompng($pathFile);
        imagecopy($canvas, $layer, 0, 0, 0, 0, 1000, 1000);
        imagedestroy($layer);
    }
}

// 8. Ensamblado de Capas (Stack)
superponerCapa($imgCanvas, "{$baseTraitsDir}/backgrounds/bg_{$bgIndex}.png");
superponerCapa($imgCanvas, "{$baseTraitsDir}/{$folderGender}/base/cuerpo.png");
superponerCapa($imgCanvas, "{$baseTraitsDir}/{$folderGender}/clothes/ropa_{$clothesIndex}.png");
superponerCapa($imgCanvas, "{$baseTraitsDir}/{$folderGender}/hair/cabello_{$hairIndex}.png");
superponerCapa($imgCanvas, "{$baseTraitsDir}/{$folderGender}/headwear/tocado_{$headwearIndex}.png");
superponerCapa($imgCanvas, "{$baseTraitsDir}/{$folderGender}/eyes/ojos_{$eyesIndex}.png");
superponerCapa($imgCanvas, "{$baseTraitsDir}/{$folderGender}/mouth/boca_{$mouthIndex}.png");

if ($eyewearIndex > 5) {
    superponerCapa($imgCanvas, "{$baseTraitsDir}/{$folderGender}/eyewear/lentes_{$eyewearIndex}.png");
}

// 9. Grayscale si la poliza esta CANCELADO
if ($datos['Status'] === 'CANCELADO') {
    imagefilter($imgCanvas, IMG_FILTER_GRAYSCALE);
}

// Output final
imagepng($imgCanvas);
imagedestroy($imgCanvas);
exit;

// --- Funcion de respaldo en caso de error ---
function dibujarErrorPNG(string $mensaje): void {
    $img = imagecreatetruecolor(1000, 1000);
    $bg   = imagecolorallocate($img, 13, 22, 40);
    $text = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $bg);
    imagestring($img, 5, 380, 480, 'KASU NFT ERROR:', $text);
    imagestring($img, 5, 380, 510, $mensaje, $text);
    imagepng($img);
    imagedestroy($img);
}
