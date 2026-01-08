<?php
/**
 * Funcionalidad_Prospectos.php
 * Revisado para PHP 8.2, saneo de entradas, redirecciones 303, fixes lógicos.
 * 05/11/2025
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/eia/session.php';
kasu_session_start();
require_once __DIR__ . '/../../eia/librerias.php';
kasu_apply_error_settings(); // 2025-11-18: Log centralizado para Registro de Prospectos
date_default_timezone_set('America/Mexico_City');

// Conexiones esperadas: $mysqli (principal), $pros (prospectos), helpers $basicas, $seguridad
if (!isset($mysqli, $pros, $basicas, $seguridad)) {
  http_response_code(500);
  exit('Dependencias no disponibles.');
}

$hoy        = date('Y-m-d');
$HoraActual = date('H:i:s');

/* ------------------------------- Helpers ------------------------------- */
function p_has(string $k): bool { return array_key_exists($k, $_POST); }
function p_get(string $k, $def=null) { return p_has($k) ? $_POST[$k] : $def; }
function s_str(?string $v): ?string {
  if ($v === null) return null;
  $v = trim($v);
  return $v === '' ? null : filter_var($v, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
}
function s_int($v): ?int {
  if ($v === null) return null;
  $x = filter_var($v, FILTER_VALIDATE_INT);
  return $x === false ? null : $x;
}
function s_bool01($v): ?int {
  if ($v === null) return null;
  $t = strtolower((string)$v);
  return in_array($t, ['1','true','on','yes','si','sí'], true) ? 1 : 0;
}
function s_email($v): ?string {
  if ($v === null) return null;
  $x = filter_var($v, FILTER_VALIDATE_EMAIL);
  return $x ?: null;
}
function s_phone10($v): ?string {
  if ($v === null) return null;
  $d = preg_replace('/\D+/', '', (string)$v);
  return strlen($d) >= 10 ? substr($d, -10) : null;
}
function s_curp($v): ?string {
  if ($v === null) return null;
  $u  = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)$v));
  $re = '/^[A-Z]{4}\d{6}[HM](AS|BC|BS|CC|CS|CH|CL|CM|CO|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$/';
  return preg_match($re, $u) ? $u : null;
}
function s_date($v): ?string { // -> Y-m-d
  if ($v === null) return null;
  $v = trim((string)$v);
  foreach (['Y-m-d','d/m/Y','d-m-Y'] as $fmt) {
    $dt = DateTime::createFromFormat($fmt, $v);
    if ($dt && $dt->format($fmt) === $v) return $dt->format('Y-m-d');
  }
  return null;
}
function s_time($v): ?string { // -> H:i:s
  if ($v === null) return null;
  $v = trim((string)$v);
  if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $v)) return null;
  return strlen($v) === 5 ? $v.':00' : $v;
}
function s_datetime($v): ?string { // -> Y-m-d H:i:s
  if ($v === null) return null;
  foreach (['Y-m-d H:i:s','d/m/Y H:i:s','Y-m-d'] as $fmt) {
    $dt = DateTime::createFromFormat($fmt, (string)$v);
    if ($dt && $dt->format($fmt) === $v) {
      return $fmt === 'Y-m-d' ? $dt->format('Y-m-d 00:00:00') : $dt->format('Y-m-d H:i:s');
    }
  }
  return null;
}
function s_choice($v, array $allowed): ?string {
  if ($v === null) return null;
  $x = strtoupper(trim((string)$v));
  return in_array($x, $allowed, true) ? $x : null;
}
function is_ajax_request(): bool {
  if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    return true;
  }
  if (array_key_exists('ajax', $_POST)) {
    $val = strtolower(trim((string)$_POST['ajax']));
    return $val !== '' && $val !== '0' && $val !== 'false' && $val !== 'no';
  }
  return false;
}
function table_exists(mysqli $db, string $table): bool {
  $sql = "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1";
  $stmt = $db->prepare($sql);
  if (!$stmt) return false;
  $stmt->bind_param('s', $table);
  $stmt->execute();
  $res = $stmt->get_result();
  $ok = $res && $res->fetch_row();
  $stmt->close();
  return (bool)$ok;
}
function agenda_fetch_slots(mysqli $db, ?string $fecha, int $limit): array {
  $limit = max(1, min($limit, 200));
  if ($fecha) {
    $stmt = $db->prepare("SELECT id, inicio, fin FROM agenda_llamadas WHERE estado = 'disponible' AND DATE(inicio) = ? ORDER BY inicio ASC LIMIT ?");
    if (!$stmt) return [];
    $stmt->bind_param('si', $fecha, $limit);
  } else {
    $stmt = $db->prepare("SELECT id, inicio, fin FROM agenda_llamadas WHERE estado = 'disponible' AND inicio >= NOW() ORDER BY inicio ASC LIMIT ?");
    if (!$stmt) return [];
    $stmt->bind_param('i', $limit);
  }
  $stmt->execute();
  $res = $stmt->get_result();
  $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
  $stmt->close();
  return $rows;
}
function agenda_reservar_por_id(mysqli $db, int $agendaId, int $prospectoId): ?array {
  $stmt = $db->prepare("UPDATE agenda_llamadas SET estado = 'reservado', prospecto_id = ?, reservado_en = NOW() WHERE id = ? AND estado = 'disponible'");
  if (!$stmt) return null;
  $stmt->bind_param('ii', $prospectoId, $agendaId);
  $stmt->execute();
  $ok = $stmt->affected_rows === 1;
  $stmt->close();
  if (!$ok) return null;

  $stmt = $db->prepare("SELECT inicio, fin FROM agenda_llamadas WHERE id = ? LIMIT 1");
  if (!$stmt) return null;
  $stmt->bind_param('i', $agendaId);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return $row ?: null;
}
function agenda_reservar_por_fecha(mysqli $db, string $fecha, ?string $hora, int $prospectoId): ?array {
  if ($hora) {
    $stmt = $db->prepare("SELECT id FROM agenda_llamadas WHERE estado = 'disponible' AND DATE(inicio) = ? AND TIME(inicio) = ? ORDER BY inicio ASC LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param('ss', $fecha, $hora);
  } else {
    $stmt = $db->prepare("SELECT id FROM agenda_llamadas WHERE estado = 'disponible' AND DATE(inicio) = ? ORDER BY inicio ASC LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param('s', $fecha);
  }
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$row) return null;
  return agenda_reservar_por_id($db, (int)$row['id'], $prospectoId);
}
function agenda_slot_valido(string $fecha, string $hora): bool {
  $dt = DateTime::createFromFormat('Y-m-d H:i:s', $fecha.' '.$hora);
  if (!$dt) return false;
  $dow = (int)$dt->format('N'); // 1=lunes, 7=domingo
  $mins = ((int)$dt->format('H') * 60) + (int)$dt->format('i');
  if (($mins % 30) !== 0) return false;

  if ($dow >= 1 && $dow <= 5) {
    $start = 9 * 60;
    $end = 18 * 60;
  } elseif ($dow === 6) {
    $start = 10 * 60;
    $end = 14 * 60;
  } else {
    return false;
  }

  return $mins >= $start && ($mins + 30) <= $end;
}
function agenda_reservar_slot(mysqli $db, string $fecha, string $hora, int $prospectoId): ?array {
  if (!agenda_slot_valido($fecha, $hora)) return null;
  $inicio = $fecha.' '.$hora;
  $dt = DateTime::createFromFormat('Y-m-d H:i:s', $inicio);
  if (!$dt) return null;
  $fin = (clone $dt)->modify('+30 minutes')->format('Y-m-d H:i:s');

  $stmt = $db->prepare("SELECT id, estado FROM agenda_llamadas WHERE inicio = ? LIMIT 1");
  if (!$stmt) return null;
  $stmt->bind_param('s', $inicio);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if ($row) {
    if (($row['estado'] ?? '') !== 'disponible') return null;
    $agendaId = (int)$row['id'];
    $stmt = $db->prepare("UPDATE agenda_llamadas SET estado = 'reservado', prospecto_id = ?, reservado_en = NOW() WHERE id = ? AND estado = 'disponible'");
    if (!$stmt) return null;
    $stmt->bind_param('ii', $prospectoId, $agendaId);
    $stmt->execute();
    $ok = $stmt->affected_rows === 1;
    $stmt->close();
    if (!$ok) return null;
  } else {
    $stmt = $db->prepare("INSERT INTO agenda_llamadas (inicio, fin, duracion_min, estado, prospecto_id, reservado_en) VALUES (?, ?, 30, 'reservado', ?, NOW())");
    if (!$stmt) return null;
    $stmt->bind_param('ssi', $inicio, $fin, $prospectoId);
    $stmt->execute();
    $ok = $stmt->affected_rows === 1;
    $stmt->close();
    if (!$ok) return null;
  }

  return ['inicio' => $inicio, 'fin' => $fin];
}
function redirect303(string $url): never {
  header('Location: '.$url, true, 303);
  exit;
}
function json_response(array $payload): never {
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

$isAjax = is_ajax_request();
$hostFromPost = s_str($_POST['Host'] ?? '');
if (!$isAjax && $hostFromPost && stripos($hostFromPost, 'prospectos') !== false) {
  if (isset($_POST['prospectoNvo']) || isset($_POST['Cita'])) {
    $isAjax = true;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['agenda_slots'])) {
  $fecha = s_date($_GET['fecha'] ?? null);
  $limit = (int)($_GET['limit'] ?? 40);
  $slots = agenda_fetch_slots($pros, $fecha, $limit);
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(['ok' => true, 'fecha' => $fecha, 'slots' => $slots], JSON_UNESCAPED_SLASHES);
  exit;
}

/* ---------------- Vars comunes desde POST para secciones inferiores ---------------- */
$Host         = s_str(p_get('Host', $_SERVER['PHP_SELF'] ?? '/'));
$name         = s_str(p_get('name') ?? p_get('nombre'));
$IdProspecto  = s_int(p_get('IdProspecto'));
$vendedorSesion = s_int($_SESSION['Vendedor'] ?? null);
$fingerprint  = s_str(p_get('fingerprint') ?? p_get('IdFingerprint'));
$connection   = s_str(p_get('connection'));
$timezone     = s_str(p_get('timezone'));
$touch        = s_str(p_get('touch'));
$Cupon        = s_str(p_get('Cupon'));
$Telefono     = s_phone10(p_get('Telefono'));
$Mail         = s_email(p_get('Correo') ?? p_get('Mail') ?? p_get('Email'));
$FechaLlamada = s_date(p_get('FechaLlamada'));
$HoraLlamada  = s_time(p_get('HoraLlamada'));
$AgendaId     = s_int(p_get('AgendaId') ?? p_get('IdAgenda'));
$FechaCita    = s_date(p_get('FechaCita'));
$HoraCita     = preg_match('/^\d{2}:\d{2}(:\d{2})?$/', (string)p_get('HoraCita')) ? (string)p_get('HoraCita') : '00:00:00';
$OrigenVisible= s_str(p_get('OrigenVisible'));
$Rastreo      = s_str(p_get('Rastreo')) ?? $OrigenVisible;
$MotivoBaja   = s_str(p_get('MotivoBaja'));
$NvoVend      = s_int(p_get('NvoVend'));

if ($isAjax && isset($_POST['Cita']) && (!$IdProspecto || !$FechaCita)) {
  json_response([
    'ok' => false,
    'msg' => 'Completa la fecha y hora para agendar la llamada.',
  ]);
}

/* =========================================================================
   BLOQUE: Registra un nuevo prospecto
   ========================================================================= */
if (isset($_POST['prospectoNvo'])) {

  $SERV_ALLOWED = ['FUNERARIO','SEGURIDAD','TRANSPORTE','RETIRO','MATERNIDAD','UNIVERSIDAD','DISTRIBUIDOR'];

  $Curp             = s_curp(p_get('Curp') ?? p_get('CURP'));
  $FullNameInput    = s_str(p_get('FullName') ?? p_get('NombreCompleto') ?? p_get('Nombre'));
  $NoTel            = s_phone10(p_get('NoTel') ?? p_get('Telefono'));
  $Email            = s_email(p_get('Email'));
  $Servicio_Interes = s_choice(p_get('Servicio_Interes') ?? p_get('Servicio'), $SERV_ALLOWED) ?? 'FUNERARIO';
  $FechaNac         = s_date(p_get('FechaNac') ?? p_get("FechaNac\t") ?? p_get('fecha_nac'));
  $Alta             = s_datetime(p_get('Alta')) ?? ($hoy.' '.$HoraActual);

  $IdFacebook = s_str(p_get('IdFacebook'));
  $UsrApi     = s_str(p_get('UsrApi'));
  $Direccion  = s_str(p_get('Direccion'));
  $Origen     = s_str(p_get('Origen')) ?? 'PWA';
  $Sugeridos  = s_int(p_get('Sugeridos')) ?? 0;
  $Cancelacion= p_has('Cancelacion') ? s_bool01(p_get('Cancelacion')) : 0;
  $Automatico = p_has('Automatico')  ? s_bool01(p_get('Automatico'))  : 0;

  $Msg = 'Operación no concluida';
  $ValidacionProducto = '';
  $FullNameToSave = null;
  $FechaNacSave = $FechaNac;
  $prospectoId = 0;

  // Duplicados en BD de prospectos
  $CurpValid = $basicas->BuscarCampos($pros,   'Id', 'prospectos', 'Curp', $Curp);
  $ValidTele = $basicas->BuscarCampos($pros,   'Id', 'prospectos', 'NoTel', $NoTel);
  $ValidMail = $basicas->BuscarCampos($pros,   'Id', 'prospectos', 'Email', $Email);

  // Si ya es cliente, validar producto permitido (solo si hay CURP)
  if ($Curp !== null) {
    $IdContac = $basicas->BuscarCampos($mysqli, 'IdContact', 'Usuario', 'ClaveCurp', $Curp);
    if (!empty($IdContac)) {
      $StatVta = $basicas->BuscarCampos($mysqli, 'Producto', 'Venta', 'IdContact', $IdContac);
      // Fix lógico: inválido si NO es ni "Universidad" ni "Retiro"
      if ($StatVta !== 'Universidad' && $StatVta !== 'Retiro') {
        $ValidacionProducto = 'InValido';
      }
    }
  }

  if ($Curp === null && $FullNameInput === null) {
    $Msg = 'Ingresa tu CURP o tu nombre completo';
  } elseif ($ValidacionProducto === 'InValido') {
    $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Pros_ya_Cte_Pwa', $Host);
    $Msg = 'Este prospecto ya es cliente de KASU';
  } elseif (!empty($CurpValid) && $basicas->BuscarCampos($pros, 'Papeline', 'prospectos', 'Curp', $Curp) === 'Prospeccion') {
    $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Fallido_Prospecto_Pwa', $Host);
    $Msg = 'Este prospecto ya se encuentra registrado y no ha concluido el proceso';
  } elseif (!empty($ValidTele) || !empty($ValidMail)) {
    $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Pros_Contacto_Duplicado', $Host);
    $Msg = 'Los datos de contacto de este prospecto ya se encuentran registrados';
  } else {
    $canInsert = true;
    if ($Curp !== null) {
      $DatProsp = $seguridad->peticion_get($Curp);
      if (!is_array($DatProsp) || ($DatProsp['Response'] ?? 'Error') === 'Error') {
        $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Pros_Curp_Falsa_Pwa', $Host);
        $Msg = (string)($DatProsp['Msg'] ?? 'CURP no válida');
        $canInsert = false;
      } else {
        $FullNameToSave = trim(($DatProsp['Nombre'] ?? '').' '.($DatProsp['Paterno'] ?? '').' '.($DatProsp['Materno'] ?? ''));
        $FechaNacSave = $DatProsp['FechaNacimiento'] ?? $FechaNac;
      }
    } else {
      $FullNameToSave = $FullNameInput;
    }

    if ($canInsert && $FullNameToSave !== null) {
      // Asignado = Id de Empleados del Vendedor en sesión (si existe)
      $Asignado = 0;
      if ($vendedorSesion) {
        $Asignado = (int)$basicas->BuscarCampos($mysqli, 'Id', 'Empleados', 'IdUsuario', $vendedorSesion);
      }

      $ids = $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Pros_Alta_Pwa', $Host);

      $data = [
        'IdFingerprint'    => $ids['fingerprint_id'] ?? null,
        'IdFacebook'       => $IdFacebook,
        'UsrApi'           => $UsrApi,
        'FullName'         => $FullNameToSave,
        'Curp'             => $Curp ?? '',
        'NoTel'            => $NoTel,
        'Email'            => $Email,
        'Direccion'        => $Direccion,
        'Servicio_Interes' => $Servicio_Interes,
        'Alta'             => $Alta,
        'Origen'           => $Origen,
        'Papeline'         => 'Prospeccion',
        'PosPapeline'      => 1,
        'Sugeridos'        => $Sugeridos,
        'Cancelacion'      => $Cancelacion,
        'FechaNac'         => $FechaNacSave,
        'Automatico'       => $Automatico,
        'Asignado'         => $Asignado,
      ];
      $insertRes = $basicas->InsertCampo($pros, 'prospectos', $data);
      $prospectoId = is_numeric($insertRes) ? (int)$insertRes : 0;
      if ($prospectoId > 0) {
        $Msg = 'Se ha registrado correctamente el prospecto';
        if ($FechaLlamada || $AgendaId) {
          $reserva = null;
          if ($AgendaId) {
            $reserva = agenda_reservar_por_id($pros, $AgendaId, $prospectoId);
          } elseif ($FechaLlamada && $HoraLlamada) {
            $reserva = agenda_reservar_slot($pros, $FechaLlamada, $HoraLlamada, $prospectoId);
          } elseif ($FechaLlamada && !$HoraLlamada) {
            $Msg = 'Se registro el prospecto, pero falta seleccionar la hora de la llamada';
          }
          if ($reserva) {
            if (table_exists($pros, 'citas')) {
              $basicas->InsertCampo($pros, 'citas', [
                'IdProspecto'   => $prospectoId,
                'Telefono'      => $NoTel,
                'Correo'        => $Email,
                'FechaCita'     => $reserva['inicio'],
                'Rastreo'       => $Rastreo,
                'FechaRegistro' => $hoy.' '.$HoraActual,
              ]);
              $Msg = 'Se ha registrado correctamente el prospecto y su llamada quedo agendada';
            } else {
              $Msg = 'Se registro el prospecto y se reservo la llamada, pero falta crear la tabla de citas';
            }
          } elseif (($FechaLlamada && $HoraLlamada) || $AgendaId) {
            $Msg = 'Se registro el prospecto, pero no hay disponibilidad para la fecha seleccionada';
          }
        }
      } else {
        $Msg = 'No se pudo registrar el prospecto';
      }
    } elseif ($canInsert) {
      $Msg = 'Ingresa tu nombre completo';
    }
  }

  if ($isAjax) {
    json_response([
      'ok' => $prospectoId > 0,
      'msg' => $Msg,
      'prospectoId' => $prospectoId,
      'servicio' => $Servicio_Interes ?? null,
    ]);
  }

  if (($prospectoId ?? 0) > 0 && $Email !== null && !$isAjax) {
    $_SESSION['mail_token'] = bin2hex(random_bytes(32));
    $redirAgenda = 'https://kasu.com.mx/prospectos.php?step=agenda&IdProspecto=' . (int)$prospectoId;
    if (!empty($Servicio_Interes)) {
      $redirAgenda .= '&producto=' . rawurlencode((string)$Servicio_Interes);
    }
    $params = [
      'EnviarGuia'  => 1,
      'IdProspecto' => $prospectoId,
      'mail_token'  => $_SESSION['mail_token'],
      'Redireccion' => $redirAgenda,
      'MsgBase'     => $Msg,
    ];
    header('Location: https://kasu.com.mx/eia/EnviarCorreo.php?' . http_build_query($params), true, 303);
    exit;
  }

  $url = 'https://kasu.com.mx'.$Host.'?Vt=1&Msg='.rawurlencode($Msg).($name ? '&nombre='.rawurlencode($name) : '');
  redirect303($url);
}

/* =========================================================================
   BLOQUE: Descarga o envío de cotización
   ========================================================================= */
if (isset($_POST['DescargaPres']) || isset($_POST['EnviaPres'])) {
  $IdVenta     = s_int(p_get('IdVenta'));
  $IdContact   = s_int(p_get('IdContact'));
  $IdUsuario   = s_int(p_get('IdUsuario'));
  $Producto    = s_str(p_get('Producto'));
  $IdVendedor  = s_int(p_get('IdVendedor')) ?? $vendedorSesion ?? 0;
  $tipo_plan   = s_str(p_get('tipo_plan')) ?? 'INDIVIDUAL';
  $a02a29      = s_int(p_get('a02a29')) ?? 0;
  $a30a49      = s_int(p_get('a30a49')) ?? 0;
  $a50a54      = s_int(p_get('a50a54')) ?? 0;
$a55a59      = s_int(p_get('a55a59')) ?? 0;
$a60a64      = s_int(p_get('a60a64')) ?? 0;
$a65a69      = s_int(p_get('a65a69')) ?? 0;
$Retiro      = s_int(p_get('Retiro')) ?? 0;
$pagoPlazoUi = s_str(p_get('pago_plazo_ui'));
$plazo       = s_int(p_get('plazo')) ?? 0;

// Alinear IdProspecto: si no vino, usar IdVenta
if (!$IdProspecto && $IdVenta) {
  $IdProspecto = $IdVenta;
}

// Compatibilidad con el nuevo combo pago/plazo (ej. CONTADO_1, CREDITO_6)
if ($pagoPlazoUi) {
  $parts = explode('_', $pagoPlazoUi);
  $plazo = s_int($parts[1] ?? null) ?? $plazo;
}

if (!$IdVenta) {
  redirect303('https://kasu.com.mx'.$Host.'?Vt=1&Msg='.rawurlencode('Id de prospecto inválido'));
}

  $stmt = $pros->prepare('SELECT * FROM prospectos WHERE Id = ? LIMIT 1');
  $stmt->bind_param('i', $IdVenta);
  $stmt->execute();
  $fila = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$fila) {
    redirect303('https://kasu.com.mx'.$Host.'?Vt=1&Msg='.rawurlencode('Prospecto no encontrado'));
  }

  if (strtoupper($tipo_plan) === 'INDIVIDUAL') {
    $Edad = (int)$basicas->ObtenerEdad($fila['Curp'] ?? '');
    if (($fila['Servicio_Interes'] ?? '') === 'TRANSPORTE' && method_exists($basicas, 'ProdTrans')) {
      $ProdSel = $basicas->ProdTrans($Edad);
    } elseif (($fila['Servicio_Interes'] ?? '') === 'SEGURIDAD' && method_exists($basicas, 'ProdPli')) {
      $ProdSel = $basicas->ProdPli($Edad);
    } else {
      $Prodeda = $basicas->ProdFune($Edad);
      $ProdSel = 'A'.$Prodeda;
    }
    $Vtn    = substr((string)$ProdSel, 1, 5); // ej. "02a29"
    $rangos = ['02a29','30a49','50a54','55a59','60a64','65a69'];

    $data = [
      'IdProspecto'  => $IdProspecto,
      'IdUser'       => $IdVendedor,
      'SubProducto'  => $fila['Servicio_Interes'] ?? '',
      'a02a29'       => 0,
      'a30a49'       => 0,
      'a50a54'       => 0,
      'a55a59'       => 0,
      'a60a64'       => 0,
      'a65a69'       => 0,
      'Retiro'       => $Retiro,
      'Plazo'        => $plazo,
      // Usar fecha/hora actual para la cotización
      'FechaRegistro'=> $hoy.' '.$HoraActual,
    ];
    if (in_array($Vtn, $rangos, true)) {
      $data['a'.$Vtn] = 1;
    }
  } else {
    $data = [
      'IdProspecto'  => $IdProspecto,
      'IdUser'       => $IdVendedor,
      'SubProducto'  => $fila['Servicio_Interes'] ?? '',
      'a02a29'       => $a02a29,
      'a30a49'       => $a30a49,
      'a50a54'       => $a50a54,
      'a55a59'       => $a55a59,
      'a60a64'       => $a60a64,
      'a65a69'       => $a65a69,
      'Retiro'       => $Retiro,
      'Plazo'        => $plazo,
      // Usar fecha/hora actual para la cotización
      'FechaRegistro'=> $hoy.' '.$HoraActual,
    ];
  }

  $idInsert = (int)$basicas->InsertCampo($pros, 'PrespEnviado', $data);
  $NvoRegistro  = base64_encode((string)$idInsert);

  if (isset($_POST['EnviaPres'])) {
    //Registramos el historico de envio
    $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Enviar_Cotizacion', $Host);
    // 1) Genera token one-shot
    $_SESSION['mail_token'] = bin2hex(random_bytes(32));
    // 2) Arma parámetros de forma segura
    $params = [
      'EnCoti'      => $NvoRegistro,
      'mail_token'  => $_SESSION['mail_token'],
      'Redireccion' => 'https://kasu.com.mx' . $Host,
      'name'        => rawurlencode((string)$name),
    ];
    // 3) Redirige
    $base = 'https://kasu.com.mx/eia/EnviarCorreo.php';
    header('Location: ' . $base . '?' . http_build_query($params));
    exit;
  } else {
    $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Descargar_Cotizacion', $Host);
    $url = 'https://kasu.com.mx/login/Generar_PDF/Cotizacion_pdf.php?busqueda='.$NvoRegistro.'&Host='.rawurlencode((string)$Host).'&name='.rawurlencode((string)$name);
    redirect303($url);
  }
}

/* =========================================================================
   BLOQUE: Activar correos automáticos de seguimiento
   ========================================================================= */
if (isset($_POST['Autom']) && $IdProspecto) {
  $DatEventos = [
    'Us'            => $IdProspecto,
    'IdFInger'      => $fingerprint,
    'Evento'        => 'AltaAuto',
    'Host'          => $Host,
    'connection'    => $connection,
    'timezone'      => $timezone,
    'touch'         => $touch,
    'Cupon'         => $Cupon,
    'FechaRegistro' => $hoy.' '.$HoraActual,
  ];
  $basicas->InsertCampo($mysqli, 'Eventos', $DatEventos);
  $basicas->ActCampo($pros, 'prospectos', 'Automatico', 1, $IdProspecto);

  redirect303('https://kasu.com.mx'.$Host.'?Ml=5'.($name ? '&name='.rawurlencode($name) : ''));
}

/* =========================================================================
   BLOQUE: Registrar cita con prospecto (proveniente de correo)
   ========================================================================= */
if (isset($_POST['Cita']) && $IdProspecto && $FechaCita) {
  $Msg = 'Tu llamada quedo agendada. En breve recibiras la guia en tu correo.';
  $CitaReg = [
    'IdProspecto'   => $IdProspecto,
    'Telefono'      => $Telefono,
    'Correo'        => $Mail,
    'FechaCita'     => $FechaCita.' '.$HoraCita,
    'Rastreo'       => $Rastreo,
    'FechaRegistro' => $hoy.' '.$HoraActual,
  ];
  if (table_exists($pros, 'citas')) {
    $basicas->InsertCampo($pros, 'citas', $CitaReg);
  } else {
    $Msg = 'Tu llamada quedo registrada, pero falta la tabla de citas.';
  }

  $DatEventos = [
    'Us'            => $IdProspecto,
    'IdFInger'      => $fingerprint,
    'Evento'        => 'CitaRegis',
    'Host'          => $Host,
    'connection'    => $connection,
    'timezone'      => $timezone,
    'touch'         => $touch,
    'Cupon'         => $Cupon,
    'FechaRegistro' => $hoy.' '.$HoraActual,
  ];
  $basicas->InsertCampo($mysqli, 'Eventos', $DatEventos);
  // Sin redirect explícito aquí por consistencia con tu flujo actual

  $sendUrl = '';
  if ($IdProspecto > 0) {
    $_SESSION['mail_token'] = bin2hex(random_bytes(32));
    $params = [
      'EnviarGuia'  => 1,
      'IdProspecto' => $IdProspecto,
      'mail_token'  => $_SESSION['mail_token'],
      'Redireccion' => 'https://kasu.com.mx' . ($Host ?: '/prospectos.php'),
      'MsgBase'     => $Msg,
    ];
    $sendUrl = 'https://kasu.com.mx/eia/EnviarCorreo.php?' . http_build_query($params);
  }
  json_response([
    'ok' => true,
    'msg' => $Msg,
    'send_url' => $sendUrl,
  ]);
}

/* =========================================================================
   BLOQUE: Baja de prospecto
   ========================================================================= */
if (isset($_POST['BajaEmp']) && $IdProspecto && $MotivoBaja) {

    // Auditoría
  $ids = $seguridad->auditoria_registrar(
    $mysqli,
    $basicas,
    $_POST,
    'Baja_Prospecto_'.$MotivoBaja,
    $_POST['Host'] ?? $_SERVER['PHP_SELF']
  );
  //Actualizamos en la base de datos
  $basicas->ActCampo($pros, 'prospectos', 'Cancelacion', 1, $IdProspecto);

  redirect303('https://kasu.com.mx'.$Host.'?Ml=4'.($name ? '&name='.rawurlencode($name) : ''));
}

/* =========================================================================
   BLOQUE: Asignación de prospecto a vendedor
   ========================================================================= */
if (isset($_POST['AsigVende']) && $IdProspecto && $NvoVend) {
  
  // Auditoría
  $ids = $seguridad->auditoria_registrar(
    $mysqli,
    $basicas,
    $_POST,
    'Asignacion_Prospecto',
    $_POST['Host'] ?? $_SERVER['PHP_SELF']
  );

  $basicas->ActCampo($pros, 'prospectos', 'Asignado', $NvoVend, $IdProspecto);

  redirect303('https://kasu.com.mx'.$Host.'?Ml=5'.($name ? '&name='.rawurlencode($name) : ''));
}

/* =========================================================================
   BLOQUE: Actualizar datos de un prospecto
   ========================================================================= */
if (isset($_POST['CamDat']) && $IdProspecto) {
  $FullName        = s_str(p_get('FullName'));
  $NoTel           = s_phone10(p_get('NoTel') ?? p_get('Telefono'));
  $Email           = s_email(p_get('Email'));
  $Direccion       = s_str(p_get('Direccion'));
  $Servicio_Interes= s_choice(p_get('Servicio_Interes') ?? p_get('Servicio'), ['FUNERARIO','SEGURIDAD','TRANSPORTE','RETIRO','MATERNIDAD','UNIVERSIDAD','DISTRIBUIDOR']);

  $stmt = $pros->prepare('SELECT * FROM prospectos WHERE Id = ? LIMIT 1');
  $stmt->bind_param('i', $IdProspecto);
  $stmt->execute();
  $Recg = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if ($Recg) {
    if ($FullName !== null && $Recg['FullName'] !== $FullName)                  $basicas->ActTab($pros, 'prospectos', 'FullName', $FullName, 'Id', $IdProspecto);
    if ($NoTel !== null && $Recg['NoTel'] !== $NoTel)                           $basicas->ActTab($pros, 'prospectos', 'NoTel', $NoTel, 'Id', $IdProspecto);
    if ($Email !== null && $Recg['Email'] !== $Email)                           $basicas->ActTab($pros, 'prospectos', 'Email', $Email, 'Id', $IdProspecto);
    if ($Direccion !== null && $Recg['Direccion'] !== $Direccion)               $basicas->ActTab($pros, 'prospectos', 'Direccion', $Direccion, 'Id', $IdProspecto);
    if ($Servicio_Interes !== null && $Recg['Servicio_Interes'] !== $Servicio_Interes)
                                                                                $basicas->ActTab($pros, 'prospectos', 'Servicio_Interes', $Servicio_Interes, 'Id', $IdProspecto);
  }

  redirect303('https://kasu.com.mx'.$Host.'?Ml=5'.($name ? '&name='.rawurlencode($name) : ''));
}

/* =========================================================================
   BLOQUE: Registro rápido desde Home (FormCotizar)
   ========================================================================= */
if (isset($_POST['FormCotizar'])) {
  // Tiempos base
  $hoy        = date('Y-m-d');
  $HoraActual = date('H:i:s');

  // Entradas normalizadas
  $curpRaw = p_get('CURP');
  $emailRq = s_email(p_get('Email'));
  $servRq  = s_str(p_get('servicio')) ?: 'FUNERARIO';

  // Validamos CURP
  $curpStd = s_curp($curpRaw);
  if (!$curpStd) {
    redirect303('https://kasu.com.mx?Msg=' . rawurlencode('La CURP no es válida, por favor ingresa una CURP válida.'));
  }

  // Consulta API de datos del prospecto
  $ArrayRes = $seguridad->peticion_get($curpStd);
  if (!is_array($ArrayRes) || ($ArrayRes['Response'] ?? 'Error') === 'Error') {
    redirect303('https://kasu.com.mx?Msg=' . rawurlencode('La CURP que registraste no es válida, por favor verifícala.'));
  }
  if (($ArrayRes['StatusCurp'] ?? '') === 'BD') {
    redirect303('https://kasu.com.mx?Msg=' . rawurlencode('La CURP reporta estado no elegible.'));
  }

  // Nombre completo y Title Case
  $FullName = trim(($ArrayRes['Nombre'] ?? '') . ' ' . ($ArrayRes['Paterno'] ?? '') . ' ' . ($ArrayRes['Materno'] ?? ''));
  $FullName = preg_replace('/\s+/', ' ', $FullName);

  function titlecase_utf8(string $s): string {
    return mb_convert_case(mb_strtolower($s, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
  }
  $FullNameMsg = titlecase_utf8($FullName);

  // ¿Ya es cliente o ya es prospecto?
  $OPsdA = $basicas->BuscarCampos($mysqli, 'id', 'Usuario',     'ClaveCurp', $curpStd);
  $OPsdB = $basicas->BuscarCampos($pros,   'Id', 'prospectos',   'Curp',      $curpStd);

  if ((int)$OPsdA >= 1) {
    $mensaje = $FullNameMsg . ' ya estás asociado a un servicio funerario. Si tienes dudas, contacta a nuestro Centro de Atención a Clientes.';
    redirect303('https://kasu.com.mx?Msg=' . rawurlencode($mensaje));
  }

  if ((int)$OPsdB >= 1) {
    $mensaje = $FullNameMsg . ' ya te encuentras en proceso de seguimiento. En breve te contactaremos.';
    redirect303('https://kasu.com.mx/productos/gastos-funerarios?idp='.$OPsdB.'&Msg=' . rawurlencode($mensaje));
    // TODO: registrar intento repetido
  }

  // Auditoría
  $ids = $seguridad->auditoria_registrar(
    $mysqli,
    $basicas,
    $_POST,
    'Registro_Prospecto_Index',
    $_POST['Host'] ?? $_SERVER['PHP_SELF']
  );

  // Inserción en prospectos
  $fingerprint = $_POST['Fingerprint'] ?? '';
  $DatProsp = [
    'IdFingerprint'    => $fingerprint,
    'FullName'         => $FullName,
    'Curp'             => $curpStd,
    'Email'            => $emailRq,
    'Servicio_Interes' => $servRq,
    'Origen'           => 'Index',
    'Cancelacion'      => 0,
    'Automatico'       => 0,
    'Alta'             => $hoy . ' ' . $HoraActual,
  ];
  $IdProspecto = (int)$basicas->InsertCampo($pros, 'prospectos', $DatProsp);

  // Edad → rango como en el flujo que sí funciona
  $Edad    = (int)$basicas->ObtenerEdad($curpStd);
  $Prodeda = $basicas->ProdFune($Edad);           // ej. "02a29"
  $ProdSel = 'A' . $Prodeda;                      // ej. "A02a29"
  $Vtn     = substr((string)$ProdSel, 1, 5);      // ej. "02a29"
  $rangos  = ['02a29','30a49','50a54','55a59','60a64','65a69'];
  if (!in_array($Vtn, $rangos, true)) { $Vtn = '02a29'; } // fallback

  // Costo por rango (opcional, solo para el mensaje)
  $Costo = (float)$basicas->BuscarCampos($mysqli, 'Costo', 'Productos', 'Producto', $Vtn);
  if (!is_finite($Costo)) { $Costo = 0.0; }

  // Payload que coincide con tu tabla PrespEnviado
  $data = [
    'IdProspecto'   => $IdProspecto,      // el insert que hiciste en 'prospectos'
    'IdUser'        => 'PLATAFORMA',      // fijo
    'SubProducto'   => 'FUNERARIO',       // tipo, NO el rango
    'a02a29'        => 0,
    'a30a49'        => 0,
    'a50a54'        => 0,
    'a55a59'        => 0,
    'a60a64'        => 0,
    'a65a69'        => 0,
    'Retiro'        => 0,
    'Plazo'         => 0,
    'FechaRegistro' => $hoy . ' ' . $HoraActual,
  ];

  // Marca bandera del rango
  $data['a' . $Vtn] = 1;

  // Inserta en PrespEnviado como en tu bloque que sí funciona
  $idInsert = (int)$basicas->InsertCampo($pros, 'PrespEnviado', $data);

  // Debug seguro si no insertó
  if ($idInsert <= 0) {
    error_log('PRESUP_ENVIADO_FALLÓ data=' . json_encode($data, JSON_UNESCAPED_UNICODE));
  }
  // 1) Genera token one-shot
  $_SESSION['mail_token'] = bin2hex(random_bytes(32));
  
  // Mensaje y redirección a módulo de correo
  $paginaRedireccion = 'https://kasu.com.mx/productos/gastos-funerarios';
  $mensaje = $FullNameMsg . ' tu servicio de gastos funerarios KASU tiene un costo de $' . number_format($Costo, 2, '.', ',');

  redirect303(
    'https://kasu.com.mx/eia/EnviarCorreo.php'
    . '?EnCoti='      . rawurlencode(base64_encode((string)$idInsert))
    . '&Redireccion=' . rawurlencode($paginaRedireccion)
    . '&Msg='         . rawurlencode($mensaje)
    . '&mail_token='  . $_SESSION['mail_token']
  );

}

/* Si no se activó ningún bloque, devolver 400 */
if ($isAjax) {
  json_response(['ok' => false, 'msg' => 'Solicitud no válida.']);
}
http_response_code(400);
echo 'Solicitud no válida.';
