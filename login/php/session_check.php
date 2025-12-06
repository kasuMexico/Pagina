<?php
declare(strict_types=1);

require_once dirname(__DIR__, 1) . '/../eia/session.php';

try {
    kasu_session_start();
} catch (Throwable $e) {
    error_log('[KASU][SessionCheck][SessionError] ' . $e->getMessage());
}

header('Content-Type: application/json');

$sessionActive = session_status() === PHP_SESSION_ACTIVE;
$userLogged    = !empty($_SESSION['Vendedor']);

$response = [
    'session_active' => $sessionActive,
    'session_id'     => session_id(),
    'session_name'   => session_name(),
    'user_logged'    => $userLogged,
    'user_data'      => [
        'vendedor'    => $_SESSION['Vendedor']  ?? null,
        'id_empleado' => $_SESSION['IdEmpleado'] ?? null,
    ],
    'cookie_params'  => session_get_cookie_params(),
    'server_time'    => date('c'),
    'user_agent'     => $_SERVER['HTTP_USER_AGENT'] ?? '',
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);
