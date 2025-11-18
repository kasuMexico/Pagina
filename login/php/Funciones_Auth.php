<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/eia/session.php';

/**
 * Autentica al vendedor contra Empleados.
 * Soporta password_hash(...) moderno, SHA-256 heredado y base64 heredado.
 */
function autenticarVendedor(mysqli $mysqli): bool
{
    // Entradas saneadas sin FILTER_SANITIZE_STRING (deprecado)
    $user = trim((string)($_POST['Usuario'] ?? ''));
    $pass = (string)($_POST['PassWord'] ?? '');

    if ($user === '' || $pass === '') {
        return false;
    }

    // Trae el hash almacenado y verifica en PHP
    $sql  = "SELECT IdUsuario, Pass FROM Empleados WHERE IdUsuario = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc() ?: null;
    $stmt->close();

    if (!$row) {
        return false;
    }

    $stored = (string)$row['Pass'];
    $ok = false;

    // 1) Hash moderno (password_hash: bcrypt/argon)
    if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$argon2')) {
        $ok = password_verify($pass, $stored);
    }
    // 2) SHA-256 heredado (64 hex)
    elseif (ctype_xdigit($stored) && strlen($stored) === 64) {
        $ok = hash_equals($stored, hash('sha256', $pass));
    }
    // 3) “Base64” heredado (no seguro, solo por compatibilidad)
    elseif (preg_match('~^[A-Za-z0-9+/=]+$~', $stored)) {
        $ok = hash_equals($stored, base64_encode($pass));
    }

    if (!$ok) {
        return false;
    }

    kasu_session_start();
    session_regenerate_id(true); // antifijación
    $_SESSION['Vendedor'] = $user;

    return true;
}
