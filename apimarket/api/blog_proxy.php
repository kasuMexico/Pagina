<?php
declare(strict_types=1);

/**
 * Proxy para obtener posts del blog KASU desde la REST API de WordPress.
 * Evita problemas de CORS/bloqueo que ocurren al hacer fetch directo desde el navegador.
 *
 * Endpoint: GET /apimarket/api/blog_proxy.php?per_page=12&_embed=1
 */

// Solo aceptar GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Construir query string con los parámetros permitidos
$allowedParams = ['per_page', '_embed', 'page', 'orderby', 'order', 'categories', 'tags', 'search', 'slug'];
$queryParams = [];
foreach ($allowedParams as $key) {
    if (isset($_GET[$key]) && $_GET[$key] !== '') {
        $queryParams[$key] = $_GET[$key];
    }
}

// Valores por defecto
if (!isset($queryParams['per_page'])) {
    $queryParams['per_page'] = '12';
}
if (!isset($queryParams['_embed'])) {
    $queryParams['_embed'] = '1';
}

// Construir URL del blog WordPress
$blogApiUrl = 'https://kasu.com.mx/blog/wp-json/wp/v2/posts?' . http_build_query($queryParams);

// Configurar cURL
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $blogApiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS      => 3,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_HTTPHEADER     => [
        'Accept: application/json',
        'User-Agent: KASU-BlogProxy/1.0',
    ],
    CURLOPT_SSL_VERIFYPEER => true,
]);

$responseBody = curl_exec($ch);
$httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType  = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$curlError    = curl_error($ch);
curl_close($ch);

// Si cURL falló
if ($responseBody === false) {
    http_response_code(502);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    echo json_encode([
        'error' => 'No se pudo conectar con el blog',
        'detail' => $curlError,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Si WordPress respondió con error
if ($httpCode >= 400) {
    http_response_code(502);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    echo json_encode([
        'error' => 'El blog respondió con error',
        'http_code' => $httpCode,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Éxito: devolver la respuesta de WordPress tal cual
http_response_code(200);
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: public, max-age=300, s-maxage=600'); // 5 min browser, 10 min CDN
header('Access-Control-Allow-Origin: https://kasu.com.mx');
echo $responseBody;
