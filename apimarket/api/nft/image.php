<?php
declare(strict_types=1);

/**
 * NFT Image Generator — KASU Policy Shares
 * Endpoint: GET /apimarket/api/nft/image.php?id={id_venta_o_id_firma}
 *
 * Genera PNG 1000x1000 con capas deterministas basadas en el id o IdFirma.
 * Género deriva de la CURP registrada. Si la póliza está CANCELADO, se aplica grayscale.
 */

require_once __DIR__ . '/../../librerias_api.php';

$searchParam = isset($_GET['id']) ? trim((string)$_GET['id']) : '';

if (empty($searchParam)) {
    dibujarErrorPNG('Parametro ID Requerido');
    exit;
}

// Usar la conexion de ventas ($mysqli) definida en librerias_api.php
global $mysqli;
$db = api_require_db($mysqli ?? null, 'ventas');

// 1. Consultar CURP, Status e IdFirma desde la BD (Acepta ID numérico o IdFirma)
$stmt = $db->prepare("
    SELECT 
        v.Id AS id_venta,
        v.Status,
        v.IdFirma,
        u.ClaveCurp
    FROM Venta v
    JOIN Usuario u ON v.IdContact = u.IdContact AND u.Tipo = 'Cliente'
    WHERE (v.Id = ? OR v.IdFirma = ?)
      AND v.IdFirma IS NOT NULL 
      AND v.IdFirma != ''
    LIMIT 1
");

$stmt->bind_param('ss', $searchParam, $searchParam);
$stmt->execute();
$datos = $stmt->get_result()->fetch_assoc();

if (!$datos) {
    dibujarErrorPNG("Poliza '{$searchParam}' No Encontrada");
    exit;
}

// 2. Establecer Headers de Imagen PNG con Caché solo si la póliza es válida
header('Content-Type: image/png');
header('Cache-Control: public, max-age=31536000, immutable');

// 3. Extracción de Género desde la CURP (Posición 11)
$curp = strtoupper(trim((string)$datos['ClaveCurp']));
$sexo = (strlen($curp) >= 11) ? substr($curp, 10, 1) : 'M';
$folderGender = ($sexo === 'H') ? 'male' : 'female';

// 4. Generación Determinista de Seed basada en el IdFirma (o id_venta como respaldo)
$seedKey = !empty($datos['IdFirma']) ? $datos['IdFirma'] : (string)$datos['id_venta'];
$seed    = crc32('KASU_LELE_SEED_' . $seedKey);
mt_srand($seed);

// 5. Ruta base de assets (PNGs transparentes de 1000x1000px)
$baseTraitsDir = __DIR__ . '/../../assets/nft_traits/';

// 6. Seleccionar assets por capa (1-20 variantes)
$bgIndex       = mt_rand(1, 20);
$clothesIndex  = mt_rand(1, 20);
$hairIndex     = mt_rand(1, 20);
$headwearIndex = mt_rand(1, 20);
$eyesIndex     = mt_rand(1, 20);
$mouthIndex    = mt_rand(1, 20);
$eyewearIndex  = mt_rand(1, 20);

// 7. Crear lienzo principal
$imgCanvas = imagecreatetruecolor(1000, 1000);
imagealphablending($imgCanvas, true);
imagesavealpha($imgCanvas, true);

// 8. Función auxiliar para superponer capas PNG
function superponerCapa($canvas, string $pathFile): void {
    if (file_exists($pathFile)) {
        $layer = imagecreatefrompng($pathFile);
        imagecopy($canvas, $layer, 0, 0, 0, 0, 1000, 1000);
        imagedestroy($layer);
    }
}

// 9. Ensamblado de Capas (Stack)
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

// 10. Grayscale si la póliza está CANCELADO
if ($datos['Status'] === 'CANCELADO') {
    imagefilter($imgCanvas, IMG_FILTER_GRAYSCALE);
}

// Output final
imagepng($imgCanvas);
imagedestroy($imgCanvas);
exit;

// --- Función de respaldo en caso de error ---
function dibujarErrorPNG(string $mensaje): void {
    header('Content-Type: image/png');
    header('Cache-Control: no-cache, must-revalidate'); // No cachear imágenes con error
    
    $img  = imagecreatetruecolor(1000, 1000);
    $bg   = imagecolorallocate($img, 13, 22, 40);
    $text = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $bg);
    imagestring($img, 5, 300, 480, 'KASU NFT ERROR:', $text);
    imagestring($img, 5, 300, 510, $mensaje, $text);
    imagepng($img);
    imagedestroy($img);
}