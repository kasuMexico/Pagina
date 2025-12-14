<?php
declare(strict_types=1);

/**
 * Render de PDF de presupuesto mediante token seguro.
 * Param: ?token=....
 */

header_remove('X-Powered-By');
date_default_timezone_set('America/Mexico_City');

$root = dirname(__DIR__, 2); // /public_html
require_once $root . '/eia/librerias.php'; // debe cargar $pros/$mysqli

$token = $_GET['token'] ?? '';
if (!$token || strlen($token) !== 64) {
    http_response_code(400);
    exit('Token faltante o inválido');
}

if (!isset($pros) || !$pros instanceof mysqli) {
    http_response_code(500);
    exit('BD de prospectos no disponible');
}

$stmt = $pros->prepare("SELECT ref_id, expira_at FROM document_tokens WHERE token = ? AND tipo = 'presupuesto' LIMIT 1");
$stmt->bind_param('s', $token);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    http_response_code(404);
    exit('Liga no válida');
}

$expira = strtotime((string)$row['expira_at']);
if ($expira !== false && $expira < time()) {
    http_response_code(410);
    exit('Liga expirada');
}

$idPropuesta = (int)$row['ref_id'];
if ($idPropuesta <= 0) {
    http_response_code(400);
    exit('Referencia inválida');
}

// Reusar el generador existente: pasar idp por GET
$_GET['idp'] = $idPropuesta;
require __DIR__ . '/Cotizacion_pdf.php';
