<?php
// /logout.php
declare(strict_types=1);

session_start();

// Acepta solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: https://kasu.com.mx', true, 303);
    exit;
}

// Verificación CSRF
if (
    empty($_POST['csrf']) ||
    empty($_SESSION['csrf_logout']) ||
    !hash_equals($_SESSION['csrf_logout'], $_POST['csrf'])
) {
    header('Location: https://kasu.com.mx', true, 303);
    exit;
}

// Limpia la sesión
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

// Redirección a la principal
header('Location: https://kasu.com.mx', true, 303);
exit;
