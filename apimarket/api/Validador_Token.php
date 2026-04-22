<?php
declare(strict_types=1);

/**
 * Include de compatibilidad para endpoints antiguos.
 * Requiere que el endpoint haya cargado librerias_api.php y definido $data.
 */

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    api_error(500, 'Conexion de ventas no disponible para validar token');
}

if (!isset($data) || !is_array($data)) {
    api_error(400, 'Payload no disponible para validar token');
}

$api_auth_context = api_validate_bearer_or_exit($mysqli, $data, (string)($api_required_product ?? ''));
