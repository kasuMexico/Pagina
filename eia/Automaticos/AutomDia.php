<?php
declare(strict_types=1);
/**
 * AutomDia.php
 * Tareas automaticas diarias para cron (Hostinger).
 */

header_remove('X-Powered-By');
date_default_timezone_set('America/Mexico_City');

require_once __DIR__ . '/../librerias.php';

function kasu_cron_guard(): void {
  if (PHP_SAPI === 'cli') {
    return;
  }
  $envToken = (string)(getenv('KASU_CRON_TOKEN') ?: '');
  if ($envToken === '') {
    return;
  }
  $reqToken = (string)($_GET['token'] ?? '');
  if ($reqToken === '' || !hash_equals($envToken, $reqToken)) {
    http_response_code(403);
    exit('Forbidden');
  }
}

function kasu_cron_log(string $msg): void {
  $ts = date('Y-m-d H:i:s');
  $line = '[' . $ts . '] ' . $msg . PHP_EOL;
  error_log('[AUTOM_DIA] ' . $msg);
  @file_put_contents(__DIR__ . '/cron.log', $line, FILE_APPEND);
}

function kasu_table_exists(mysqli $db, string $table): bool {
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

function kasu_next_fecha(string $freq, DateTime $now): string {
  $next = clone $now;
  switch ($freq) {
    case 'DIARIO':
      $next->modify('+1 day');
      break;
    case 'SEMANAL':
      $next->modify('+7 days');
      break;
    case 'QUINCENAL':
      $next->modify('+15 days');
      break;
    case 'MENSUAL':
      $next->modify('+30 days');
      break;
    default:
      $next->modify('+7 days');
      break;
  }
  return $next->format('Y-m-d H:i:s');
}

function kasu_build_promo_html(string $nombre, string $ctaUrl): string {
  $cuerpo = '<p>Hola <strong>' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</strong>,</p>'
          . '<p>Tenemos opciones para proteger a tu familia con planes flexibles.</p>'
          . '<p>Revisa los beneficios y elige el plan ideal para ti.</p>';
  return (new Correo())->Mensaje('IA · CORREO PROSPECTO', [
    'Cte' => $nombre,
    'CuerpoHtml' => $cuerpo,
    'CtaTexto' => 'Ver planes',
    'CtaUrl' => $ctaUrl,
  ]);
}

function kasu_send_promos(mysqli $pros, Correo $Correo, array $frecuencias, int $limit, bool $dryRun): array {
  $out = ['enviados' => 0, 'omitidos' => 0];

  if (!kasu_table_exists($pros, 'Prospectos_Seguimiento_IA')) {
    kasu_cron_log('Tabla Prospectos_Seguimiento_IA no disponible.');
    return $out;
  }

  $sql = "SELECT t.Id, t.IdProspecto, t.ProximaAccion, t.FechaProxima, p.FullName, p.Email"
       . " FROM Prospectos_Seguimiento_IA t"
       . " INNER JOIN prospectos p ON p.Id = t.IdProspecto"
       . " WHERE t.TipoNota = 'EMAIL_AUTO'"
       . " AND t.ProximaAccion IN ('DIARIO')"
       . " AND (t.FechaProxima IS NULL OR t.FechaProxima <= NOW())"
       . " AND p.Email IS NOT NULL AND p.Email <> ''"
       . " LIMIT ?";

  $stmt = $pros->prepare($sql);
  if (!$stmt) {
    kasu_cron_log('No se pudo preparar consulta de seguimiento.');
    return $out;
  }
  $stmt->bind_param('i', $limit);
  $stmt->execute();
  $res = $stmt->get_result();
  $now = new DateTime('now');
  $ctaUrl = getenv('KASU_PROMO_URL') ?: 'https://kasu.com.mx';

  while ($row = $res->fetch_assoc()) {
    $freq = strtoupper(trim((string)($row['ProximaAccion'] ?? '')));
    if ($freq === '' || !in_array($freq, $frecuencias, true)) {
      $out['omitidos']++;
      continue;
    }

    $email = (string)$row['Email'];
    $nombre = (string)$row['FullName'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $out['omitidos']++;
      continue;
    }

    if (!$dryRun) {
      $html = kasu_build_promo_html($nombre !== '' ? $nombre : 'Prospecto', $ctaUrl);
      $sent = $Correo->EnviarCorreo($nombre, $email, 'KASU | Informacion importante', $html);
      if ($sent) {
        $next = kasu_next_fecha($freq, $now);
        $stmtUp = $pros->prepare("UPDATE Prospectos_Seguimiento_IA SET FechaProxima = ? WHERE Id = ?");
        if ($stmtUp) {
          $idReg = (int)$row['Id'];
          $stmtUp->bind_param('si', $next, $idReg);
          $stmtUp->execute();
          $stmtUp->close();
        }
      }
      if ($sent) {
        $out['enviados']++;
      } else {
        $out['omitidos']++;
      }
    } else {
      $out['enviados']++;
    }
  }
  $stmt->close();

  return $out;
}

// ====== Run ======
kasu_cron_guard();

if (!isset($pros) || !($pros instanceof mysqli)) {
  http_response_code(500);
  exit('BD prospectos no disponible');
}

$dryRun = (PHP_SAPI === 'cli' && in_array('--dry-run', $argv ?? [], true)) || (($_GET['dry'] ?? '') === '1');
$limit = (int)($_GET['limit'] ?? 200);
$limit = max(1, min($limit, 1000));

$frecuencias = ['DIARIO'];
$result = kasu_send_promos($pros, $Correo, $frecuencias, $limit, $dryRun);

echo json_encode([
  'ok' => true,
  'dry_run' => $dryRun,
  'enviados' => $result['enviados'],
  'omitidos' => $result['omitidos'],
], JSON_UNESCAPED_SLASHES);
exit;
