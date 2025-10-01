<?php
// logout.php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

session_start();

// CSRF
if (
    empty($_POST['csrf']) ||
    empty($_SESSION['csrf_logout']) ||
    !hash_equals($_SESSION['csrf_logout'], $_POST['csrf'])
) {
    http_response_code(403);
    exit;
}

// Vaciar sesión y borrar cookie
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

// Redirigir
header('Location: https://kasu.com.mx/login', true, 303);
exit;
