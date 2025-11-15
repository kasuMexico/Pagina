<?php
declare(strict_types=1);

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

/**
 * Autentica al vendedor contra Empleados.
 * Soporta password_hash(...) moderno, SHA-256 heredado y base64 heredado.
 */
function autenticarVendedor(mysqli $mysqli): bool
{
    // Log de errores a archivo dentro de /login (hermano de /eia)
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../../login/debug_login.log');

    // Entradas saneadas
    $userInput = trim((string)($_POST['Usuario'] ?? ''));
    $pass      = (string)($_POST['PassWord'] ?? '');

    if ($userInput === '' || $pass === '') {
        error_log("[KASU][Login] Usuario o password vacío: {$userInput}");
        return false;
    }

    $userNorm = strtoupper($userInput);

    // Trae el hash almacenado y verifica en PHP
    $sql  = "SELECT IdUsuario, Pass FROM Empleados WHERE UPPER(IdUsuario) = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $userNorm);
    $stmt->execute();
    $res  = $stmt->get_result();
    $row  = $res->fetch_assoc() ?: null;
    $stmt->close();

    if (!$row) {
        error_log("[KASU][Login] Usuario no encontrado: {$userInput}");
        return false;
    }

    $stored   = (string)$row['Pass'];
    $ok       = false;
    $tipoHash = 'desconocido';

    // 1) Hash moderno (password_hash: bcrypt/argon)
    if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$argon2')) {
        $tipoHash = 'password_hash';
        $ok = password_verify($pass, $stored);
    }
    // 2) SHA-256 heredado (64 hex)
    elseif (ctype_xdigit($stored) && strlen($stored) === 64) {
        $tipoHash   = 'sha256';
        $storedHex  = strtolower($stored);
        $inputHex   = strtolower(hash('sha256', $pass));
        error_log("[KASU][Login] Comparando SHA-256 para {$row['IdUsuario']} -> BD={$storedHex} / INPUT={$inputHex}");
        $ok = hash_equals($storedHex, $inputHex);
    }
    // 3) “Base64” heredado (no seguro, solo por compatibilidad)
    elseif (preg_match('~^[A-Za-z0-9+/=]+$~', $stored)) {
        $tipoHash = 'base64';
        $inputB64 = base64_encode($pass);
        error_log("[KASU][Login] Comparando Base64 para {$row['IdUsuario']} -> BD={$stored} / INPUT={$inputB64}");
        $ok = hash_equals($stored, $inputB64);
    }
    // 4) Texto plano heredado (altas antiguas)
    elseif ($stored === $pass) {
        $tipoHash = 'texto_plano';
        error_log("[KASU][Login] Comparando texto plano para {$row['IdUsuario']}");
        $ok = true;
    }

    if (!$ok) {
        error_log("[KASU][Login] Hash incorrecto para usuario {$row['IdUsuario']} (tipo {$tipoHash})");
        return false;
    }

    // Sesión segura
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    session_regenerate_id(true); // antifijación
    $_SESSION['Vendedor'] = (string)$row['IdUsuario'];

    return true;
}
