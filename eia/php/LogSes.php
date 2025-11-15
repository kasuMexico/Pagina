<?php
/**
 * Cierre de sesión y limpieza de cookie de sesión.
 * Compatible con PHP 8.2. Conserva la lógica original.
 * Fecha: 2025-11-03
 * Revisado por: JCCM
 */

declare(strict_types=1);

// Inicia la sesión si no está iniciada aún
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Elimina todas las variables de sesión
$_SESSION = [];

// Si se usan cookies para la sesión, borra la cookie asociada
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    // Borrar cookie de sesión usando el formato de opciones moderno
    // Mantiene path, domain, secure, httponly y samesite si existe.
    $cookieOptions = [
        'expires'  => time() - 42000,
        'path'     => $params['path']     ?? '/',
        'domain'   => $params['domain']   ?? '',
        'secure'   => (bool)($params['secure']   ?? false),
        'httponly' => (bool)($params['httponly'] ?? true),
    ];
    // Preserva SameSite si está disponible en la configuración actual
    if (!empty($params['samesite'])) {
        $cookieOptions['samesite'] = $params['samesite'];
    }

    setcookie(session_name(), '', $cookieOptions);
}

// Destruye la sesión en el servidor
session_destroy();

// Cabeceras para evitar caché del navegador en la próxima carga
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Redirección a la página de registro/login
header('Location: https://kasu.com.mx/login/registro.php', true, 303);
exit;