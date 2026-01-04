<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : ValidateMexico_V1.php
 * Carpeta : /apimarket/api
 *
 * Qué hace:
 * ----------
 * Endpoint de API Market para validar CURP/RFC (Conéctame / Validate_Mexico).
 * - Autenticación: reutiliza tu Bearer token (Validador_Token.php).
 * - Cobro: por consulta exitosa (mismo precio aunque sea caché).
 * - Caché: si existe CURP/RFC vigente en BD, NO llama upstream.
 * - Auditoría: registra en api_usage.
 *
 * Entrada (POST JSON):
 *   {
 *     "tipo_peticion": "request",
 *     "nombre_de_usuario": "Api_telecom_bienestar",
 *     "metodo": "curp_validate|rfc_validate|upstream_saldo|upstream_peticiones",
 *     "valor": "CURP_O_RFC",
 *     "pagina": "1" // solo upstream_peticiones
 *   }
 *
 * Flexibilidad:
 * - metodo también puede venir como "request"
 * - valor también puede venir como "curp" o "rfc"
 *
 * Respuesta (JSON):
 *   {
 *     "ok": true,
 *     "success": true|false,
 *     "origen": "cache|upstream",
 *     "costo_centavos": 200,
 *     "saldo_centavos": 499800,
 *     "data": { ... }
 *   }
 * ============================================================================
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../librerias_api.php'; // -> define $mysqli_api, $basicas, $seguridad
$mysqli = $mysqli_api; // alias de compatibilidad si tus includes esperan $mysqli
global $mysqli_api;

// -------------------- Helpers --------------------
function jout(array $data, int $code = 200): never {
  http_response_code($code);
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}
function norm_curp(string $s): string {
  $s = strtoupper(trim($s));
  $s = preg_replace('/\s+/', '', $s);
  return (string)$s;
}
function norm_rfc(string $s): string {
  $s = strtoupper(trim($s));
  $s = preg_replace('/\s+/', '', $s);
  return (string)$s;
}
function sha256(string $s): string {
  return hash('sha256', $s);
}
function get_ip(): string {
  return $_SERVER['HTTP_CF_CONNECTING_IP']
    ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? ''));
}
function get_bearer_token(): string {
  $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  if ($h === '' && function_exists('getallheaders')) {
    $hh = getallheaders();
    $h = $hh['Authorization'] ?? $hh['authorization'] ?? '';
  }
  if (stripos($h, 'Bearer ') === 0) {
    return trim(substr($h, 7));
  }
  return '';
}
function must_post_json(): array {
  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    jout(['ok'=>false,'error'=>'Método no permitido'], 405);
  }
  $raw = file_get_contents('php://input') ?: '';
  $data = json_decode($raw, true);
  if (!is_array($data)) {
    jout(['ok'=>false,'error'=>'JSON inválido'], 400);
  }
  return $data;
}

function pricing_for(mysqli $db, string $producto, string $metodo): array {
  $sql = "SELECT pr.precio_ok_centavos, pr.precio_fail_centavos
          FROM api_productos p
          JOIN api_metodos m ON m.producto_id = p.id
          JOIN api_pricing pr ON pr.metodo_id = m.id
          WHERE p.clave = ? AND p.version = 'V1' AND m.metodo = ?
          LIMIT 1";
  $st = $db->prepare($sql);
  $st->bind_param('ss', $producto, $metodo);
  $st->execute();
  $r = $st->get_result()->fetch_assoc();
  if (!$r) return ['ok'=>0,'fail'=>0];
  return ['ok'=>(int)$r['precio_ok_centavos'], 'fail'=>(int)$r['precio_fail_centavos']];
}

function sub_id_from_usuario(mysqli $db, string $usuario): ?int {
  $st = $db->prepare("SELECT subdistribuidor_id FROM api_usuarios WHERE nombre_de_usuario=? AND activo=1 LIMIT 1");
  $st->bind_param('s', $usuario);
  $st->execute();
  $r = $st->get_result()->fetch_assoc();
  return $r ? (int)$r['subdistribuidor_id'] : null;
}

function wallet_get(mysqli $db, int $subId): int {
  $st = $db->prepare("SELECT saldo_centavos FROM api_wallets WHERE subdistribuidor_id=? LIMIT 1");
  $st->bind_param('i', $subId);
  $st->execute();
  $r = $st->get_result()->fetch_assoc();
  return $r ? (int)$r['saldo_centavos'] : 0;
}

function wallet_charge_on_success(mysqli $db, int $subId, int $amount, string $ref, array $meta = []): void {
  if ($amount <= 0) return;

  $db->begin_transaction();
  try {
    // lock wallet row
    $st = $db->prepare("SELECT saldo_centavos FROM api_wallets WHERE subdistribuidor_id=? FOR UPDATE");
    $st->bind_param('i', $subId);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    if (!$r) {
      $db->rollback();
      jout(['ok'=>false,'error'=>'Wallet no existe'], 500);
    }
    $saldo = (int)$r['saldo_centavos'];
    if ($saldo < $amount) {
      $db->rollback();
      jout(['ok'=>false,'error'=>'Saldo insuficiente','saldo_centavos'=>$saldo,'requerido_centavos'=>$amount], 402);
    }

    $nuevo = $saldo - $amount;
    $st = $db->prepare("UPDATE api_wallets SET saldo_centavos=? WHERE subdistribuidor_id=?");
    $st->bind_param('ii', $nuevo, $subId);
    $st->execute();

    $tipo = 'CARGO';
    $metaJson = json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $st = $db->prepare("INSERT INTO api_wallet_movs (subdistribuidor_id, tipo, monto_centavos, ref, meta_json)
                        VALUES (?,?,?,?,?)");
    $st->bind_param('isiss', $subId, $tipo, $amount, $ref, $metaJson);
    $st->execute();

    $db->commit();
  } catch (Throwable $e) {
    $db->rollback();
    throw $e;
  }
}

function usage_log(mysqli $db, array $row): void {
  $sql = "INSERT INTO api_usage
    (subdistribuidor_id, usuario, producto, metodo, valor_hash, origen, ok, costo_centavos,
     upstream_costo_centavos, upstream_saldo, ip, user_agent, ms, meta_json)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
  $st = $db->prepare($sql);

  $metaJson = json_encode($row['meta'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

  $subId  = (int)$row['sub_id'];
  $usr    = (string)$row['usuario'];
  $prod   = (string)$row['producto'];
  $met    = (string)$row['metodo'];
  $vhash  = (string)$row['valor_hash'];
  $orig   = (string)$row['origen']; // cache|upstream
  $ok     = (int)$row['ok'];
  $costo  = (int)$row['costo_centavos'];

  $upCost = $row['upstream_costo_centavos'];
  $upCost = ($upCost === null) ? null : (int)$upCost;

  $upSaldo = $row['upstream_saldo']; // decimal or null

  $ip     = (string)$row['ip'];
  $ua     = (string)$row['user_agent'];
  $ms     = (int)$row['ms'];

  // bind_param no soporta null directo con 'i'; se manda como string/nullable
  // Para upstream_costo_centavos / upstream_saldo usamos 's' y mandamos null/valor como string.
  $upCostStr = ($upCost === null) ? null : (string)$upCost;
  $upSaldoStr = ($upSaldo === null) ? null : (string)$upSaldo;

  $st->bind_param(
    'isssssisssssis',
    $subId,
    $usr,
    $prod,
    $met,
    $vhash,
    $orig,
    $ok,
    $costo,
    $upCostStr,
    $upSaldoStr,
    $ip,
    $ua,
    $ms,
    $metaJson
  );
  $st->execute();
}

/**
 * Conéctame / ValidateMexico SOAP config
 * Recomendado: setear por variables de entorno en Hostinger:
 *   CONECTAME_USER, CONECTAME_PASS
 */
function conectame_client(): SoapClient {
  $wsdl = 'https://conectame.ddns.net/index.php?wsdl';
  $endpoint = 'https://conectame.ddns.net/ws/index.php';

  $opts = [
    'trace' => 0,
    'exceptions' => true,
    'cache_wsdl' => WSDL_CACHE_NONE,
    'connection_timeout' => 15,
    'location' => $endpoint,
    'uri' => 'urn:ValidateMexico',
  ];
  return new SoapClient($wsdl, $opts);
}

function conectame_creds(): array {
  $u = getenv('CONECTAME_USER') ?: '';
  $p = getenv('CONECTAME_PASS') ?: '';
  if ($u === '' || $p === '') {
    jout(['ok'=>false,'error'=>'Faltan credenciales upstream (CONECTAME_USER/PASS)'], 500);
  }
  return [$u, $p];
}

function cache_curp_get(mysqli $db, string $curp): ?array {
  $st = $db->prepare("SELECT * FROM cache_curp WHERE curp=? AND cache_expires_at > NOW() LIMIT 1");
  $st->bind_param('s', $curp);
  $st->execute();
  $r = $st->get_result()->fetch_assoc();
  return $r ?: null;
}
function cache_rfc_get(mysqli $db, string $rfc): ?array {
  $st = $db->prepare("SELECT * FROM cache_rfc WHERE rfc=? AND cache_expires_at > NOW() LIMIT 1");
  $st->bind_param('s', $rfc);
  $st->execute();
  $r = $st->get_result()->fetch_assoc();
  return $r ?: null;
}

function cache_curp_upsert(mysqli $db, string $curp, array $data, int $ttlDays = 30): void {
  $curpHash = sha256($curp);
  $rawJson = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  $resp = (string)($data['Response'] ?? '');
  $statusCurp = (string)($data['StatusCurp'] ?? '');

  $nombre  = (string)($data['Nombre'] ?? '');
  $paterno = (string)($data['Paterno'] ?? '');
  $materno = (string)($data['Materno'] ?? '');
  $sexo    = (string)($data['Sexo'] ?? '');
  $nac     = (string)($data['Nacionalidad'] ?? '');

  $fechaN = $data['FechaNacimiento'] ?? null;
  $fechaN = $fechaN ? date('Y-m-d', strtotime((string)$fechaN)) : null;

  $df = $data['DatosFiscales'] ?? null;
  $hist = $data['Historicos'] ?? null;
  $dfJson = $df ? json_encode($df, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
  $histJson = $hist ? json_encode($hist, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;

  $st = $db->prepare("
    INSERT INTO cache_curp
      (curp, curp_hash, response, status_curp, nombre, paterno, materno, sexo, fecha_nacimiento, nacionalidad,
       datos_fiscales_json, historicos_json, raw_json, fetched_at, cache_expires_at, source)
    VALUES
      (?,?,?,?,?,?,?,?,?,?,?,?,?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), 'conectame')
    ON DUPLICATE KEY UPDATE
      curp_hash=VALUES(curp_hash),
      response=VALUES(response),
      status_curp=VALUES(status_curp),
      nombre=VALUES(nombre),
      paterno=VALUES(paterno),
      materno=VALUES(materno),
      sexo=VALUES(sexo),
      fecha_nacimiento=VALUES(fecha_nacimiento),
      nacionalidad=VALUES(nacionalidad),
      datos_fiscales_json=VALUES(datos_fiscales_json),
      historicos_json=VALUES(historicos_json),
      raw_json=VALUES(raw_json),
      fetched_at=NOW(),
      cache_expires_at=DATE_ADD(NOW(), INTERVAL ? DAY),
      source='conectame'
  ");
  $st->bind_param(
    'ssssssssssssii',
    $curp, $curpHash, $resp, $statusCurp, $nombre, $paterno, $materno, $sexo, $fechaN, $nac,
    $dfJson, $histJson, $rawJson, $ttlDays, $ttlDays
  );
  $st->execute();
}

function cache_rfc_upsert(mysqli $db, string $rfc, array $data, int $ttlDays = 30): void {
  $rfcHash = sha256($rfc);
  $rawJson = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

  $resp = (string)($data['Response'] ?? '');
  $razon = (string)($data['RazonSocial'] ?? '');
  $rfcRep = (string)($data['RfcRepresentante'] ?? '');
  $curpRep = (string)($data['CurpRepresentante'] ?? '');
  $tipoP = (string)($data['TipoPersona'] ?? '');

  $lco = isset($data['Lco']) ? (int)$data['Lco'] : null;
  $lrfc = isset($data['Lrfc']) ? (int)$data['Lrfc'] : null;
  $sncf = isset($data['Sncf']) ? (int)$data['Sncf'] : null;

  $subc = (string)($data['Subcontratacion'] ?? '');
  $extra = (string)($data['Extra'] ?? '');

  $st = $db->prepare("
    INSERT INTO cache_rfc
      (rfc, rfc_hash, response, razon_social, rfc_representante, curp_representante, tipo_persona,
       lco, lrfc, sncf, subcontratacion, extra, raw_json, fetched_at, cache_expires_at, source)
    VALUES
      (?,?,?,?,?,?,?,?,?,?,?,?,?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), 'conectame')
    ON DUPLICATE KEY UPDATE
      rfc_hash=VALUES(rfc_hash),
      response=VALUES(response),
      razon_social=VALUES(razon_social),
      rfc_representante=VALUES(rfc_representante),
      curp_representante=VALUES(curp_representante),
      tipo_persona=VALUES(tipo_persona),
      lco=VALUES(lco),
      lrfc=VALUES(lrfc),
      sncf=VALUES(sncf),
      subcontratacion=VALUES(subcontratacion),
      extra=VALUES(extra),
      raw_json=VALUES(raw_json),
      fetched_at=NOW(),
      cache_expires_at=DATE_ADD(NOW(), INTERVAL ? DAY),
      source='conectame'
  ");
  // ints nullable -> mandar como string nullable para no pelear con bind_param
  $lcoS  = ($lco === null) ? null : (string)$lco;
  $lrfcS = ($lrfc === null) ? null : (string)$lrfc;
  $sncfS = ($sncf === null) ? null : (string)$sncf;

  $st->bind_param(
    'ssssssssssssii',
    $rfc, $rfcHash, $resp, $razon, $rfcRep, $curpRep, $tipoP,
    $lcoS, $lrfcS, $sncfS, $subc, $extra, $rawJson, $ttlDays, $ttlDays
  );
  $st->execute();
}

// -------------------- MAIN --------------------
try {
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

  $data = must_post_json();

  // Requeridos para tu patrón
  $tipo_peticion = (string)($data['tipo_peticion'] ?? 'request');
  if ($tipo_peticion !== 'request') {
    // Este endpoint solo atiende "request"
    jout(['ok'=>false,'error'=>'tipo_peticion no soportado'], 400);
  }

  $usuario = trim((string)($data['nombre_de_usuario'] ?? ''));
  if ($usuario === '') {
    jout(['ok'=>false,'error'=>'Falta nombre_de_usuario'], 400);
  }

  // Validar Bearer token con tu validador existente (patrón Customer_V1.php)
  // Nota: Validador_Token.php usa $data['nombre_de_usuario'] y cabecera Authorization: Bearer ...
  if (get_bearer_token() === '') {
    jout(['ok'=>false,'error'=>'Falta Authorization: Bearer'], 401);
  }
  require_once __DIR__ . '/../Validador_Token.php';

  // Resolver método
  $metodo = (string)($data['metodo'] ?? ($data['request'] ?? ''));
  $metodo = trim($metodo);
  if ($metodo === '') {
    jout(['ok'=>false,'error'=>'Falta metodo'], 400);
  }

  $producto = 'Validate_Mexico';

  // Subdistribuidor
  $subId = sub_id_from_usuario($mysqli_api, $usuario);
  if ($subId === null) {
    jout(['ok'=>false,'error'=>'Usuario API no existe o inactivo'], 403);
  }

  // Pricing (mismo para cache/upstream; cobro solo si éxito)
  $pr = pricing_for($mysqli_api, $producto, $metodo);
  $precioOk = (int)$pr['ok'];
  $precioFail = (int)$pr['fail'];

  // Valor
  $valor = (string)($data['valor'] ?? '');
  if ($valor === '') {
    // compat
    $valor = (string)($data['curp'] ?? ($data['rfc'] ?? ''));
  }

  $ip = get_ip();
  $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
  $t0 = microtime(true);

  $origen = 'upstream';
  $success = 0;
  $costo = 0;
  $upCost = null;
  $upSaldo = null;
  $respArr = null;

  // TTL cache (días)
  $ttlDays = 30;

  if ($metodo === 'curp_validate') {
    $curp = norm_curp($valor);
    if (strlen($curp) !== 18) {
      jout(['ok'=>false,'error'=>'CURP inválida'], 400);
    }

    // 1) Cache
    $cache = cache_curp_get($mysqli_api, $curp);
    if ($cache && !empty($cache['raw_json'])) {
      $origen = 'cache';
      $respArr = json_decode((string)$cache['raw_json'], true);
      if (!is_array($respArr)) {
        $respArr = [
          'Response' => $cache['response'] ?? '',
          'Curp' => $curp,
          'Nombre' => $cache['nombre'] ?? '',
          'Paterno' => $cache['paterno'] ?? '',
          'Materno' => $cache['materno'] ?? '',
          'Sexo' => $cache['sexo'] ?? '',
          'FechaNacimiento' => $cache['fecha_nacimiento'] ?? null,
          'Nacionalidad' => $cache['nacionalidad'] ?? '',
          'StatusCurp' => $cache['status_curp'] ?? '',
        ];
      }
    } else {
      // 2) Upstream SOAP
      [$u, $p] = conectame_creds();
      $client = conectame_client();
      $req = ['user'=>$u, 'password'=>$p, 'Curp'=>$curp];
      $soapResp = $client->__soapCall('Curp', [$req]);
      $respArr = json_decode(json_encode($soapResp, JSON_UNESCAPED_UNICODE), true);

      // Guardar cache solo si response válido (aunque sea error se puede guardar, pero aquí guardamos cuando éxito)
      // éxito definido por Response
      $respText = strtolower(trim((string)($respArr['Response'] ?? '')));
      $success = in_array($respText, ['correct','ok','success'], true) ? 1 : 0;

      if ($success === 1) {
        cache_curp_upsert($mysqli_api, $curp, $respArr, $ttlDays);
      }

      // upstream costo/saldo si vienen
      if (isset($respArr['Saldo']['CostoPeticion'])) {
        $upCost = (int)round(((float)$respArr['Saldo']['CostoPeticion']) * 100);
      }
      if (isset($respArr['Saldo']['Saldo'])) {
        $upSaldo = (string)$respArr['Saldo']['Saldo'];
      }
    }

    // éxito (cache o upstream)
    if ($success === 0) {
      $respText = strtolower(trim((string)($respArr['Response'] ?? '')));
      $success = in_array($respText, ['correct','ok','success'], true) ? 1 : 0;
    }

    $costo = ($success === 1) ? $precioOk : $precioFail;

    // Cobro (si éxito)
    if ($success === 1 && $costo > 0) {
      wallet_charge_on_success(
        $mysqli_api,
        $subId,
        $costo,
        $producto . '|' . $metodo . '|' . date('Y-m-d H:i:s'),
        ['origen'=>$origen,'curp_last4'=>substr($curp, -4)]
      );
    }

    $ms = (int)round((microtime(true) - $t0) * 1000);
    $saldo = wallet_get($mysqli_api, $subId);

    // Log usage
    $valorHash = sha256($metodo . '|' . $curp);
    usage_log($mysqli_api, [
      'sub_id' => $subId,
      'usuario' => $usuario,
      'producto' => $producto,
      'metodo' => $metodo,
      'valor_hash' => $valorHash,
      'origen' => $origen,
      'ok' => $success,
      'costo_centavos' => $costo,
      'upstream_costo_centavos' => $upCost,
      'upstream_saldo' => $upSaldo,
      'ip' => $ip,
      'user_agent' => $ua,
      'ms' => $ms,
      'meta' => ['ttl_days'=>$ttlDays],
    ]);

    jout([
      'ok' => true,
      'success' => (bool)$success,
      'origen' => $origen,
      'costo_centavos' => $costo,
      'saldo_centavos' => $saldo,
      'data' => $respArr,
      'ms' => $ms,
    ], 200);
  }

  if ($metodo === 'rfc_validate') {
    $rfc = norm_rfc($valor);
    if (strlen($rfc) < 10 || strlen($rfc) > 13) {
      jout(['ok'=>false,'error'=>'RFC inválido'], 400);
    }

    // 1) Cache
    $cache = cache_rfc_get($mysqli_api, $rfc);
    if ($cache && !empty($cache['raw_json'])) {
      $origen = 'cache';
      $respArr = json_decode((string)$cache['raw_json'], true);
      if (!is_array($respArr)) {
        $respArr = [
          'Response' => $cache['response'] ?? '',
          'Rfc' => $rfc,
          'RazonSocial' => $cache['razon_social'] ?? '',
          'RfcRepresentante' => $cache['rfc_representante'] ?? '',
          'CurpRepresentante' => $cache['curp_representante'] ?? '',
          'TipoPersona' => $cache['tipo_persona'] ?? '',
          'Lco' => $cache['lco'] ?? null,
          'Lrfc' => $cache['lrfc'] ?? null,
          'Sncf' => $cache['sncf'] ?? null,
          'Subcontratacion' => $cache['subcontratacion'] ?? '',
          'Extra' => $cache['extra'] ?? '',
        ];
      }
    } else {
      [$u, $p] = conectame_creds();
      $client = conectame_client();
      $req = ['user'=>$u, 'password'=>$p, 'Rfc'=>$rfc];
      $soapResp = $client->__soapCall('Rfc', [$req]);
      $respArr = json_decode(json_encode($soapResp, JSON_UNESCAPED_UNICODE), true);

      $respText = strtolower(trim((string)($respArr['Response'] ?? '')));
      $success = in_array($respText, ['correct','ok','success'], true) ? 1 : 0;

      if ($success === 1) {
        cache_rfc_upsert($mysqli_api, $rfc, $respArr, $ttlDays);
      }
    }

    if ($success === 0) {
      $respText = strtolower(trim((string)($respArr['Response'] ?? '')));
      $success = in_array($respText, ['correct','ok','success'], true) ? 1 : 0;
    }

    $costo = ($success === 1) ? $precioOk : $precioFail;

    if ($success === 1 && $costo > 0) {
      wallet_charge_on_success(
        $mysqli_api,
        $subId,
        $costo,
        $producto . '|' . $metodo . '|' . date('Y-m-d H:i:s'),
        ['origen'=>$origen,'rfc_last4'=>substr($rfc, -4)]
      );
    }

    $ms = (int)round((microtime(true) - $t0) * 1000);
    $saldo = wallet_get($mysqli_api, $subId);

    $valorHash = sha256($metodo . '|' . $rfc);
    usage_log($mysqli_api, [
      'sub_id' => $subId,
      'usuario' => $usuario,
      'producto' => $producto,
      'metodo' => $metodo,
      'valor_hash' => $valorHash,
      'origen' => $origen,
      'ok' => $success,
      'costo_centavos' => $costo,
      'upstream_costo_centavos' => null,
      'upstream_saldo' => null,
      'ip' => $ip,
      'user_agent' => $ua,
      'ms' => $ms,
      'meta' => ['ttl_days'=>$ttlDays],
    ]);

    jout([
      'ok' => true,
      'success' => (bool)$success,
      'origen' => $origen,
      'costo_centavos' => $costo,
      'saldo_centavos' => $saldo,
      'data' => $respArr,
      'ms' => $ms,
    ], 200);
  }

  // Admin endpoints (si decides habilitarlos por permisos más adelante)
  if ($metodo === 'upstream_saldo') {
    [$u, $p] = conectame_creds();
    $client = conectame_client();
    $req = ['user'=>$u, 'password'=>$p];
    $soapResp = $client->__soapCall('ConsultaSaldo', [$req]);
    $respArr = json_decode(json_encode($soapResp, JSON_UNESCAPED_UNICODE), true);

    $ms = (int)round((microtime(true) - $t0) * 1000);
    jout(['ok'=>true,'success'=>true,'data'=>$respArr,'ms'=>$ms], 200);
  }

  if ($metodo === 'upstream_peticiones') {
    $pagina = (string)($data['pagina'] ?? '1');
    [$u, $p] = conectame_creds();
    $client = conectame_client();
    $req = ['user'=>$u, 'password'=>$p, 'pagina'=>$pagina];
    $soapResp = $client->__soapCall('ConsultaPeticiones', [$req]);
    $respArr = json_decode(json_encode($soapResp, JSON_UNESCAPED_UNICODE), true);

    $ms = (int)round((microtime(true) - $t0) * 1000);
    jout(['ok'=>true,'success'=>true,'data'=>$respArr,'ms'=>$ms], 200);
  }

  jout(['ok'=>false,'error'=>'Método no soportado'], 400);

} catch (SoapFault $e) {
  jout(['ok'=>false,'error'=>'Upstream SOAP: '.$e->getMessage()], 502);
} catch (Throwable $e) {
  jout(['ok'=>false,'error'=>$e->getMessage()], 500);
}
