<?php
declare(strict_types=1);

/**
 * portfolio_sync.php
 * Bridge PHP → ejecuta portfolio_sync.py (yfinance) y muestra salida.
 *
 * Requisitos:
 * - venv en /home/uXXXX/venv_port (o ajusta $venv)
 * - Python script en /login/php/AnalisisDatos/portfolio_sync.py
 * - Variables DB_* disponibles (ideal: exportadas en este wrapper desde config)
 */

$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/eia/session.php';
kasu_session_start();
require_once $projectRoot . '/eia/librerias.php'; // Debe exponer $mysqli

// Acceso
if (!isset($_SESSION['Vendedor']) && ($_SESSION['dataP'] ?? '') !== 'ValidJCCM') {
    header('Location: https://kasu.com.mx/login');
    exit;
}

// CSRF (reutiliza el mismo token que ya tienes en la vista)
if (empty($_SESSION['csrf_port'])) {
    $_SESSION['csrf_port'] = bin2hex(random_bytes(16));
}
$csrfToken = $_SESSION['csrf_port'];

// Validación de token por GET
$tokenGet = (string)($_GET['token'] ?? '');
if (!hash_equals($csrfToken, $tokenGet)) {
    http_response_code(403);
    echo "Token inválido.";
    exit;
}

// === AJUSTA ESTO A TU USUARIO / RUTAS REALES ===
$venvPython = '/home/u557645733/venv_port/bin/python3'; // <-- ajusta si tu venv está en otro path
$pyScript   = __DIR__ . '/portfolio_sync.py';

// DB creds desde tu entorno / config.
// Opción A (recomendada): definirlos aquí con los valores correctos.
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'u557645733_web';      // <-- pon tu DB si no está en env
$dbUser = getenv('DB_USER') ?: 'u557645733_kasuw';      // <-- pon tu user si no está en env
$dbPass = getenv('DB_PASS') ?: ';9Ai!5;G0QU';      // <-- pon tu pass si no está en env
$dbPort = getenv('DB_PORT') ?: '3306';

// Si librerias.php ya trae credenciales en variables, puedes mapearlas aquí.
// Ejemplo típico (ajusta si aplica en tu proyecto):
// $dbName = $dbName ?: ($GLOBALS['DB_NAME'] ?? '');
// $dbUser = $dbUser ?: ($GLOBALS['DB_USER'] ?? '');
// $dbPass = $dbPass ?: ($GLOBALS['DB_PASS'] ?? '');

// Validaciones mínimas
if (!is_file($venvPython)) {
    http_response_code(500);
    echo "No existe Python del venv: " . htmlspecialchars($venvPython);
    exit;
}
if (!is_file($pyScript)) {
    http_response_code(500);
    echo "No existe el script: " . htmlspecialchars($pyScript);
    exit;
}
if ($dbName === '' || $dbUser === '') {
    http_response_code(500);
    echo "Faltan DB_NAME/DB_USER (env o hardcode en portfolio_sync.php).";
    exit;
}

// Ejecuta
$cmd = escapeshellcmd($venvPython) . ' ' . escapeshellarg($pyScript);

// Exportar variables al proceso
$env = [
    'DB_HOST' => $dbHost,
    'DB_NAME' => $dbName,
    'DB_USER' => $dbUser,
    'DB_PASS' => $dbPass,
    'DB_PORT' => $dbPort,
];

// Construir prefijo env tipo: DB_HOST=... DB_NAME=... python script.py
$prefix = '';
foreach ($env as $k => $v) {
    $prefix .= $k . '=' . escapeshellarg((string)$v) . ' ';
}
$fullCmd = $prefix . $cmd . ' 2>&1';

if (!function_exists('exec')) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "La función exec() está deshabilitada en este hosting. No se puede lanzar el sync desde PHP.\n";
    echo "Comando sugerido (ejecútalo por cron/SSH):\n$fullCmd\n";
    exit;
}

$output = [];
$exitCode = 0;
exec($fullCmd, $output, $exitCode);

header('Content-Type: text/plain; charset=utf-8');

echo "CMD:\n$fullCmd\n\n";
echo "EXIT CODE: $exitCode\n\n";
echo "OUTPUT:\n";
echo implode("\n", $output);

// Si todo OK, puedes redirigir de regreso:
// if ($exitCode === 0) { header('Location: ../../ruta_de_tu_vista.php'); exit; }
