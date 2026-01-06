<?php
declare(strict_types=1);

require_once __DIR__ . '/../eia/Conexiones/cn_prosp.php';

if (!isset($pros) || !($pros instanceof mysqli)) {
  http_response_code(500);
  exit('Error de conexion.');
}

$pros->set_charset('utf8mb4');

function s(?string $v): string {
  $v = trim((string)$v);
  $v = preg_replace('/\s+/u', ' ', $v);
  return $v;
}

function redirect_with_msg(string $msg, string $path = '../funerarias.php'): void {
  $qs = 'Msg=' . rawurlencode($msg);
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  header('Pragma: no-cache');
  header('Location: ' . $path . (str_contains($path, '?') ? '&' : '?') . $qs, true, 303);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect_with_msg('Metodo no permitido');
}

$nombreComercial = s($_POST['nombre_comercial'] ?? '');
$razonSocial     = s($_POST['razon_social'] ?? '');
$rfc             = s($_POST['rfc'] ?? '');
$aniosOperacion  = filter_var($_POST['anios'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
$contacto        = s($_POST['contacto'] ?? '');
$cargo           = s($_POST['cargo'] ?? '');
$telefonoRaw     = preg_replace('/\D+/', '', (string)($_POST['telefono'] ?? ''));
$whatsappRaw     = preg_replace('/\D+/', '', (string)($_POST['whatsapp'] ?? ''));
$email           = s($_POST['email'] ?? '');
$web             = s($_POST['web'] ?? '');
$direccion       = s($_POST['direccion'] ?? '');
$ciudad          = s($_POST['ciudad'] ?? '');
$estado          = s($_POST['estado'] ?? '');
$cp              = s($_POST['cp'] ?? '');
$cobertura       = s($_POST['cobertura'] ?? '');
$salas           = filter_var($_POST['salas'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
$capacidadSala   = filter_var($_POST['capacidad'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
$disponibilidad  = s($_POST['disponibilidad'] ?? '');
$permisos        = s($_POST['permisos'] ?? '');
$comentarios     = s($_POST['comentarios'] ?? '');
$aceptaConvenio  = isset($_POST['acepta']) ? 1 : 0;

$errores = [];

if ($nombreComercial === '') $errores[] = 'Nombre comercial requerido';
if ($razonSocial === '') $errores[] = 'Razon social requerida';
if ($rfc === '') $errores[] = 'RFC requerido';
if ($aniosOperacion === false) $errores[] = 'Anios de operacion invalidos';
if ($contacto === '') $errores[] = 'Contacto requerido';
if ($cargo === '') $errores[] = 'Cargo requerido';
if (strlen($telefonoRaw) !== 10) $errores[] = 'Telefono invalido';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Email invalido';
if ($direccion === '') $errores[] = 'Direccion requerida';
if ($ciudad === '') $errores[] = 'Ciudad requerida';
if ($estado === '') $errores[] = 'Estado requerido';
if ($cp === '') $errores[] = 'CP requerido';
if ($cobertura === '') $errores[] = 'Cobertura requerida';
if ($salas === false) $errores[] = 'Numero de salas invalido';
if ($capacidadSala === false) $errores[] = 'Capacidad por sala invalida';

$allowedServicios = ['traslado', 'velacion', 'cremacion', 'inhumacion'];
$serviciosIn = $_POST['servicios'] ?? [];
if (!is_array($serviciosIn)) $serviciosIn = [];
$servicios = [];
foreach ($serviciosIn as $srv) {
  $srv = strtolower(s((string)$srv));
  if (in_array($srv, $allowedServicios, true)) {
    $servicios[] = $srv;
  }
}
$servicios = array_values(array_unique($servicios));
if (!$servicios) $errores[] = 'Selecciona al menos un servicio';

if (!in_array($disponibilidad, ['si', 'parcial', 'no'], true)) $errores[] = 'Disponibilidad invalida';
if (!in_array($permisos, ['si', 'en-proceso', 'no'], true)) $errores[] = 'Permisos invalidos';
if ($aceptaConvenio !== 1) $errores[] = 'Debes aceptar el convenio';

if ($errores) {
  redirect_with_msg($errores[0]);
}

$telefono = $telefonoRaw;
$whatsapp = $whatsappRaw !== '' ? $whatsappRaw : null;
$web = $web !== '' ? $web : null;
$comentarios = $comentarios !== '' ? $comentarios : null;
$serviciosStr = implode(',', $servicios);

$sql = "INSERT INTO prospectos_funerarias
  (NombreComercial, RazonSocial, RFC, AniosOperacion, Contacto, Cargo, Telefono, Whatsapp, Email, Web,
   Direccion, Ciudad, Estado, CP, Cobertura, Servicios, Salas, CapacidadSala, Disponibilidad, Permisos,
   Comentarios, AceptaConvenio)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $pros->prepare($sql);
if (!$stmt) {
  redirect_with_msg('No se pudo preparar el registro');
}

$stmt->bind_param(
  'sssisssssssssssiissssi',
  $nombreComercial,
  $razonSocial,
  $rfc,
  $aniosOperacion,
  $contacto,
  $cargo,
  $telefono,
  $whatsapp,
  $email,
  $web,
  $direccion,
  $ciudad,
  $estado,
  $cp,
  $cobertura,
  $serviciosStr,
  $salas,
  $capacidadSala,
  $disponibilidad,
  $permisos,
  $comentarios,
  $aceptaConvenio
);

if (!$stmt->execute()) {
  $stmt->close();
  redirect_with_msg('No se pudo guardar el registro');
}

$stmt->close();
redirect_with_msg('Registro enviado correctamente');
