<?php
/********************************************************************************************
 * Qué hace: Cierra sesión de forma segura. Verifica CSRF, destruye la sesión y borra la
 *           cookie de sesión usando parámetros modernos compatibles con PHP 8.2.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);
header_remove('X-Powered-By');

// Solo permite POST
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit;
}

session_start();

/* ========================== Verificación CSRF ========================== */
$csrf_form  = $_POST['csrf']            ?? '';
$csrf_saved = $_SESSION['csrf_logout']  ?? '';

if ($csrf_form === '' || $csrf_saved === '' || !hash_equals($csrf_saved, $csrf_form)) {
    http_response_code(403);
    exit;
}

/* ========================== Destruir sesión ========================== */
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    // Borrar cookie de sesión con mismos parámetros
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $p['path']     ?? '/',
        'domain'   => $p['domain']   ?? '',
        'secure'   => (bool)($p['secure']   ?? false),
        'httponly' => (bool)($p['httponly'] ?? true),
        // Samesite no siempre viene definido en session_get_cookie_params()
        'samesite' => ($p['samesite'] ?? 'Lax'),
    ]);
}

session_destroy();

/* ========================== Redirigir ========================== */
header('Location: https://kasu.com.mx/login', true, 303);
exit;
