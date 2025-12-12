<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : ia_credito_cliente.php
 * Carpeta : /eia/Vista-360
 * Qué hace: Endpoint de compatibilidad - redirige a ia_cliente_completo.php
 *           Mantiene compatibilidad con sistemas antiguos.
 * ============================================================================
 */

header('Content-Type: application/json; charset=utf-8');

try {
    // Simplemente redirigir a ia_cliente_completo.php
    require_once __DIR__ . '/ia_cliente_completo.php';
    exit;
    
} catch (Throwable $e) {
    error_log('[IA Credito Cliente Compat] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Sistema de búsqueda temporalmente no disponible'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}