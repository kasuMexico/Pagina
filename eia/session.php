<?php
declare(strict_types=1);

/**
 * Configuración centralizada de sesiones para KASU.
 * - Extiende la vida de la cookie a 7 días.
 * - Ajusta secure/httponly/samesite dinámicamente.
 * - Sólo fija el dominio cuando navegamos en kasu.com.mx
 */

if (!defined('KASU_SESSION_LIFETIME')) {
    define('KASU_SESSION_LIFETIME', 7 * 24 * 60 * 60); // 7 días
}

if (!function_exists('kasu_str_ends_with')) {
    function kasu_str_ends_with(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }
        return substr($haystack, -strlen($needle)) === $needle;
    }
}

function kasu_session_cookie_params(array $overrides = []): array
{
    $httpsOn = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443');
    $rootDomain  = 'kasu.com.mx';
    $httpHost    = $_SERVER['HTTP_HOST'] ?? '';
    $hostMatches = false;

    if ($httpHost !== '') {
        $normalizedHost = strtolower(preg_replace('/:\d+$/', '', $httpHost));
        $hostMatches = $normalizedHost === $rootDomain
            || kasu_str_ends_with($normalizedHost, '.' . $rootDomain);
    }

    $params = [
        'lifetime' => KASU_SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => $httpsOn,
        'httponly' => true,
        'samesite' => 'Lax',
    ];

    if ($hostMatches) {
        $params['domain'] = $rootDomain;
    }

    return array_merge($params, $overrides);
}

function kasu_session_start(array $overrides = []): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $params = kasu_session_cookie_params($overrides);

    session_set_cookie_params($params);
    ini_set('session.cookie_lifetime', (string)($params['lifetime'] ?? 0));
    ini_set('session.gc_maxlifetime', (string)($params['lifetime'] ?? 0));

    session_start();
}
