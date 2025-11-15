<?php
/**
 * Registro de comentarios de la página principal en tabla ContacIndex.
 * Fecha: 2025-11-03
 * Revisado por: JCCM
 *
 * Cambios PHP 8.2:
 * - Validación de método POST y de conexión $mysqli.
 * - Sanitización segura de entrada y uso de sentencias preparadas (sin concatenar SQL).
 * - Manejo de faltantes en $_POST sin avisos.
 * - Redirección con 303 y mensaje URL-encoded.
 */

//Archivo que registra en la base de datos los comentarios de la pagina prinicipal
//Insertar la conexion con la base de datos
require '../eia/php/cn_vtas.php';

// Verificación de conexión mysqli
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    http_response_code(500);
    exit('Error de conexión.');
}

// Utilidad: sanitizar texto simple
function s(?string $v): string {
    $v = trim((string)$v);
    // Normaliza saltos y espacios
    $v = preg_replace('/\s+/u', ' ', $v);
    return $v;
}

// Utilidad: redirección unificada con mensaje
function redirect_with_msg(string $msg, string $path = '../index.php'): void {
    $qs = 'Msg=' . rawurlencode($msg);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Location: ' . $path . (str_contains($path, '?') ? '&' : '?') . $qs, true, 303);
    exit;
}

//Se crean las variables prinicipales
$nombre  = s($_POST['name']   ?? '');
$correo  = s($_POST['email']  ?? '');
$mensaje = s($_POST['message']?? '');

// Validaciones básicas
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_msg('Error al contactarnos, intenta más tarde');
}
if ($nombre === '' || $correo === '' || $mensaje === '') {
    redirect_with_msg('Completa todos los campos');
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    redirect_with_msg('Correo inválido');
}

//Se insertan en la base de datos
$Msg = 'Gracias por contactarnos';
$stmt = $mysqli->prepare('INSERT INTO ContacIndex (Nombre, Correo, Mensaje, Fecha) VALUES (?, ?, ?, NOW())');
if ($stmt) {
    $stmt->bind_param('sss', $nombre, $correo, $mensaje);
    if (!$stmt->execute()) {
        $Msg = 'Error al contactarnos, intenta más tarde';
    }
    $stmt->close();
} else {
    $Msg = 'Error al contactarnos, intenta más tarde';
}

redirect_with_msg($Msg);
