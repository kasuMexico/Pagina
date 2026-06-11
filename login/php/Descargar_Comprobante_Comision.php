<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/eia/session.php';
kasu_session_start();
require_once dirname(__DIR__, 2) . '/eia/librerias.php';
require_once __DIR__ . '/mesa_helpers.php';

if (empty($_SESSION['Vendedor'])) {
    http_response_code(401);
    exit('Sesión requerida.');
}

$nivel = (int)$basicas->BuscarCampos(
    $mysqli,
    'Nivel',
    'Empleados',
    'IdUsuario',
    (string)$_SESSION['Vendedor']
);
if (!kasu_can_access_finance($mysqli, $nivel)) {
    http_response_code(403);
    exit('No tienes permisos para consultar comprobantes.');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit('Comprobante inválido.');
}

try {
    $stmt = $mysqli->prepare('SELECT ComprobantePdf FROM Comisiones_pagos WHERE Id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (Throwable $e) {
    http_response_code(404);
    exit('Comprobante no disponible.');
}

$storedPath = (string)($row['ComprobantePdf'] ?? '');
$expectedPrefix = '/login/archivos/comisiones/';
if ($storedPath === '' || strpos($storedPath, $expectedPrefix) !== 0) {
    http_response_code(404);
    exit('Comprobante no disponible.');
}

$file = realpath(dirname(__DIR__) . '/archivos/comisiones/' . basename($storedPath));
$baseDir = realpath(dirname(__DIR__) . '/archivos/comisiones');
if ($file === false || $baseDir === false || strpos($file, $baseDir . DIRECTORY_SEPARATOR) !== 0 || !is_file($file)) {
    http_response_code(404);
    exit('Comprobante no disponible.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="comprobante_comision_' . $id . '.pdf"');
header('Content-Length: ' . filesize($file));
header('X-Content-Type-Options: nosniff');
readfile($file);
exit;
