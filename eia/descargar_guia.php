<?php
declare(strict_types=1);
/**
 * Descarga de guia KASU con token de un solo uso.
 */

header_remove('X-Powered-By');
date_default_timezone_set('America/Mexico_City');

require_once __DIR__ . '/librerias.php';

$token = $_GET['token'] ?? '';
if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
  http_response_code(400);
  exit('Token faltante o invalido');
}

if (!isset($pros) || !($pros instanceof mysqli)) {
  http_response_code(500);
  exit('BD de prospectos no disponible');
}

function column_exists(mysqli $db, string $table, string $column): bool {
  $sql = "SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1";
  $stmt = $db->prepare($sql);
  if (!$stmt) return false;
  $stmt->bind_param('ss', $table, $column);
  $stmt->execute();
  $res = $stmt->get_result();
  $ok = $res && $res->fetch_row();
  $stmt->close();
  return (bool)$ok;
}

$hasUsos = column_exists($pros, 'document_tokens', 'usos_restantes');

// Busca token por tipo esperado; fallback si el tipo no coincide pero ref_id = cliente_id
$stmt = $pros->prepare(
  $hasUsos
    ? "SELECT expira_at, usos_restantes FROM document_tokens WHERE token = ? AND tipo = 'guia_kasu' AND usos_restantes > 0 LIMIT 1"
    : "SELECT expira_at FROM document_tokens WHERE token = ? AND tipo = 'guia_kasu' LIMIT 1"
);
if (!$stmt) {
  http_response_code(500);
  exit('No se pudo validar el token');
}
$stmt->bind_param('s', $token);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
  $stmt = $pros->prepare(
    $hasUsos
      ? "SELECT expira_at, usos_restantes FROM document_tokens WHERE token = ? AND ref_id = cliente_id AND usos_restantes > 0 LIMIT 1"
      : "SELECT expira_at FROM document_tokens WHERE token = ? AND ref_id = cliente_id LIMIT 1"
  );
  if ($stmt) {
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
  }
}

if (!$row) {
  http_response_code(404);
  exit('Liga no valida');
}

$expiraRaw = $row['expira_at'] ?? null;
if ($expiraRaw) {
  $expira = strtotime((string)$expiraRaw);
  if ($expira !== false && $expira < time()) {
    http_response_code(410);
    exit('Liga expirada');
  }
}

$filePath = realpath(__DIR__ . '/../Articulos/Guia_completa_KASU.pdf');
if (!$filePath || !is_file($filePath)) {
  http_response_code(404);
  exit('Archivo no disponible');
}

// One-shot: borrar el token antes de enviar el archivo
if ($hasUsos) {
  $stmt = $pros->prepare("UPDATE document_tokens SET usos_restantes = usos_restantes - 1 WHERE token = ? AND usos_restantes > 0");
  if ($stmt) {
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $updated = $stmt->affected_rows === 1;
    $stmt->close();
    if (!$updated) {
      http_response_code(410);
      exit('Liga ya utilizada');
    }
  }
  $stmt = $pros->prepare("DELETE FROM document_tokens WHERE token = ? AND usos_restantes <= 0");
  if ($stmt) {
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->close();
  }
} else {
  $stmt = $pros->prepare("DELETE FROM document_tokens WHERE token = ? LIMIT 1");
  if ($stmt) {
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $deleted = $stmt->affected_rows === 1;
    $stmt->close();
    if (!$deleted) {
      http_response_code(410);
      exit('Liga ya utilizada');
    }
  }
}

if (function_exists('ob_get_level')) {
  while (ob_get_level() > 0) { ob_end_clean(); }
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Guia_completa_KASU.pdf"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

readfile($filePath);
exit;
