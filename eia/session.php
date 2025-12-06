<?php
declare(strict_types=1);

/**
 * Archivo: session.php
 * Configuración centralizada de sesiones para KASU.
 * - Optimizado para PWA y compatibilidad multi-navegador
 */

if (!defined('KASU_SESSION_LIFETIME')) {
    define('KASU_SESSION_LIFETIME', 7 * 24 * 60 * 60); // 7 días
}

if (!defined('KASU_SESSION_NAME')) {
    define('KASU_SESSION_NAME', 'KASU_PWA_SESS');
}

function kasu_session_cookie_params(): array
{
    // Determinar si estamos en HTTPS
    $isHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    
    // Detectar si es Chrome (necesita SameSite=None para PWAs)
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $isChrome = stripos($userAgent, 'Chrome') !== false && stripos($userAgent, 'Edge') === false;
    
    // Para PWAs en Chrome, necesitamos SameSite=None
    $sameSite = 'Lax';
    if ($isChrome && $isHttps) {
        $sameSite = 'None'; // Requerido para PWAs en Chrome
    }
    
    // Configuración base
    $params = [
        'lifetime' => KASU_SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => $sameSite,
    ];
    
    // Solo establecer dominio si estamos en el dominio principal
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';
    $rootDomain = 'kasu.com.mx';
    
    if ($httpHost && (strpos($httpHost, $rootDomain) !== false || $httpHost === 'localhost')) {
        // Para localhost, no establecer dominio
        if ($httpHost !== 'localhost' && $httpHost !== '127.0.0.1') {
            $params['domain'] = '.' . $rootDomain; // El punto inicial es importante
        }
    }
    
    return $params;
}

function kasu_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    
    // Establecer nombre de sesión
    session_name(KASU_SESSION_NAME);
    
    // Configurar parámetros de cookie
    $cookieParams = kasu_session_cookie_params();
    
    // Configurar manualmente los headers de cookie para Chrome
    if (PHP_VERSION_ID < 70300) {
        // Para versiones antiguas de PHP
        session_set_cookie_params(
            $cookieParams['lifetime'],
            $cookieParams['path'] . '; samesite=' . $cookieParams['samesite'],
            $cookieParams['domain'] ?? '',
            $cookieParams['secure'],
            $cookieParams['httponly']
        );
    } else {
        // Para PHP 7.3+
        session_set_cookie_params($cookieParams);
    }
    
    // Configuración adicional de sesión
    ini_set('session.cookie_lifetime', (string)$cookieParams['lifetime']);
    ini_set('session.gc_maxlifetime', (string)$cookieParams['lifetime']);
    ini_set('session.cookie_secure', $cookieParams['secure'] ? '1' : '0');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.sid_length', '48');
    ini_set('session.sid_bits_per_character', '6');
    
    // Iniciar sesión
    session_start();
    
    // Regenerar ID periódicamente para seguridad
    if (!isset($_SESSION['SESSION_CREATED'])) {
        $_SESSION['SESSION_CREATED'] = time();
    } elseif (time() - $_SESSION['SESSION_CREATED'] > 1800) { // 30 minutos
        session_regenerate_id(true);
        $_SESSION['SESSION_CREATED'] = time();
    }
}

// Función para verificar estado de sesión (útil para debug)
function kasu_session_status(): array
{
    return [
        'status' => session_status(),
        'name' => session_name(),
        'id' => session_id(),
        'cookie_params' => session_get_cookie_params(),
        'session_data_keys' => array_keys($_SESSION),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'is_https' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ];
}