<?php
/**
 * Carga masiva de clientes desde CSV
 * - Guarda CSV de éxitos y errores en: /login/assets/Registros_Masivos
 * - Muestra modal con conteo y botón rojo para descargar errores
 * - NO requiere columna "edad" (se obtiene de CURP)
 * - Valida CURP con API ($seguridad->peticion_get) y extrae edad con $basicas->ObtenerEdad
 * - Campo "plan" admite: Seguridad, Transporte, Empresa o vacío
 *   * Seguridad  -> $basicas->ProdPli($edad)
 *   * Transporte -> $basicas->ProdTrans($edad) (o ProdFune si no existe)
 *   * Empresa    -> $basicas->ProdFune($edad)
 *   * Vacío      -> $basicas->ProdFune($edad)
 */

session_start();
require_once '../eia/librerias.php'; // Debe inicializar $mysqli, $basicas, $seguridad

// Rutas de salida
$baseDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/login/assets/Registros_Masivos';
$baseUrl = 'https://kasu.com.mx/login/assets/Registros_Masivos';
if (!is_dir($baseDir)) { @mkdir($baseDir, 0775, true); }

// ------------------------ Helpers ------------------------
function encabezados_esperados(): array {
  // SIN "edad"
  return ['id','nombre','apellido_paterno','apellido_materno','telefono','email','curp','plan'];
}
function norm_header(string $h): string {
  $h = trim(mb_strtolower($h));
  $h = str_replace([' ', '-'], '_', $h);
  return $h;
}
function validarEncabezados(array $headers): bool {
  $exp = encabezados_esperados();
  $h1  = array_map('norm_header', $headers);
  $h2  = array_map('norm_header', $exp);
  return $h1 === $h2; // mismo orden
}

/**
 * Valida una fila ya normalizada (claves en minúsculas con guiones bajos).
 * "plan" puede ir vacío o ser: Seguridad / Transporte / Empresa (case-insensitive).
 */
function validarFila(array $r): array {
  $errs = [];

  // Requeridos (plan NO es requerido; id tampoco)
  foreach (['nombre','apellido_paterno','apellido_materno','telefono','email','curp'] as $k) {
    if (!isset($r[$k]) || $r[$k] === '') $errs[] = "falta {$k}";
  }

  // Teléfono 10 dígitos
  if (!empty($r['telefono']) && !preg_match('/^\d{10}$/', $r['telefono'])) {
    $errs[] = 'telefono inválido';
  }

  // Email
  if (!empty($r['email']) && !preg_match('/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/', $r['email'])) {
    $errs[] = 'email inválido';
  }

  // CURP (18 chars + regex)
  if (!empty($r['curp'])) {
    $c = strtoupper($r['curp']);
    if (strlen($c)!==18 || !preg_match('/^[A-Z]{4}\d{6}[HM](AS|BC|BS|CC|CH|CL|CM|CS|DF|DG|GR|GT|HG|JC|MC|MN|MS|NE|NL|NT|OC|PL|QR|QT|SL|SI|SM|SO|TB|TL|TS|VZ|YN|ZS)[A-Z]{3}[A-Z0-9]\d$/', $c)) {
      $errs[] = 'curp inválida (formato)';
    }
  }

  // PLAN permitido (vacío o Seguridad/Transporte/Empresa)
  $plan = isset($r['plan']) ? mb_strtoupper(trim($r['plan'])) : '';
  if ($plan !== '' && !in_array($plan, ['SEGURIDAD','TRANSPORTE','EMPRESA'], true)) {
    $errs[] = 'plan no soportado (use Seguridad, Transporte, Empresa o vacío)';
  }

  return $errs;
}

/**
 * Inserta registro en BD usando tus helpers ($basicas, $seguridad).
 * Devuelve [bool $ok, string $mensaje]
 */
function guardarRegistroEnBD(array $r, mysqli $mysqli): array {
  global $basicas, $seguridad;

  $curp = strtoupper(trim($r['curp'] ?? ''));
  $plan = mb_strtoupper(trim($r['plan'] ?? ''));

  // 1) Validación API CURP
  $api = $seguridad->peticion_get($curp);
  if ($api === 'error' || (is_array($api) && empty($api))) {
    return [false, 'CURP no validada por API'];
  }

  // 2) Edad desde CURP
  $edad = $basicas->ObtenerEdad($curp);
  if (!is_int($edad)) {
    return [false, 'CURP inválida (edad no derivable)'];
  }

  // >>> BLOQUE NUEVO: no permitir mayores de 70
  if ($edad > 70) {
    return [false, 'Edad no permitida para registro (>70 años)'];
  }
  
  // 3) Duplicado por CURP
  $curpEsc = $mysqli->real_escape_string($curp);
  $dup = $mysqli->query("SELECT 1 FROM Usuario WHERE ClaveCurp='{$curpEsc}' LIMIT 1");
  if ($dup && $dup->num_rows > 0) {
    return [false, 'CURP ya registrada'];
  }

  // 4) Determinar subproducto por edad según "plan"
  switch ($plan) {
    case 'SEGURIDAD':
      $subProd = $basicas->ProdPli($edad);
      break;
    case 'TRANSPORTE':
      if (method_exists($basicas, 'ProdTrans')) {
        $subProd = $basicas->ProdTrans($edad);
      } else {
        $subProd = $basicas->ProdFune($edad); // fallback si aún no existe ProdTrans
      }
      break;
    case 'EMPRESA':
    case '':
      $subProd = $basicas->ProdFune($edad);
      break;
    default:
      return [false, 'plan no soportado'];
  }

  // 5) Obtener costo (determina CostoVenta)
  $costo = (float)$basicas->BuscarCampos($mysqli, "Costo", "Productos", "Producto", $subProd);
  if (!is_finite($costo)) { $costo = 0.0; }

  // 6) Escapes básicos
  $nombreCompleto = trim(($r['nombre'] ?? '').' '.($r['apellido_paterno'] ?? '').' '.($r['apellido_materno'] ?? ''));
  $nombreEsc = $mysqli->real_escape_string($nombreCompleto);
  $telEsc    = $mysqli->real_escape_string($r['telefono'] ?? '');
  $mailEsc   = $mysqli->real_escape_string($r['email'] ?? '');
  $prodEsc   = $mysqli->real_escape_string($subProd);
  $mes       = $mysqli->real_escape_string(date('M'));
  $tipoServ  = $mysqli->real_escape_string($plan === '' ? 'EMPRESA' : $plan);

  // 7) Contacto
  $q1 = "INSERT INTO Contacto (Usuario, Mail, Telefono, Producto)
         VALUES ('MASIVO', '{$mailEsc}', '{$telEsc}', '{$prodEsc}')";
  if (!$mysqli->query($q1)) return [false, 'Error Contacto: '.$mysqli->error];
  $idContacto = (int)$mysqli->insert_id;

  // 8) Usuario
  $q2 = "INSERT INTO Usuario (IdContact, Usuario, Tipo, Nombre, ClaveCurp, Email)
         VALUES ({$idContacto}, 'MASIVO', 'Cliente', '{$nombreEsc}', '{$curpEsc}', '{$mailEsc}')";
  if (!$mysqli->query($q2)) return [false, 'Error Usuario: '.$mysqli->error];

  // 9) Venta (usa costo del producto)
  $q3 = "INSERT INTO Venta (Usuario, IdContact, Nombre, Producto, CostoVenta, NumeroPagos, Status, Mes, Cupon, TipoServicio)
         VALUES ('MASIVO', {$idContacto}, '{$nombreEsc}', '{$prodEsc}', {$costo}, 1, 'PREVENTA', '{$mes}', 'S/D', '{$tipoServ}')";
  if (!$mysqli->query($q3)) return [false, 'Error Venta: '.$mysqli->error];

  return [true, 'Registro exitoso'];
}

// ================= Procesamiento principal ==========================
$okRows = $failRows = [];
$okCount = $failCount = 0;
$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_FILES['archivo_csv']) || $_FILES['archivo_csv']['error'] !== UPLOAD_ERR_OK) {
    $err = 'No se recibió el archivo CSV.';
  } else {
    $tmp = $_FILES['archivo_csv']['tmp_name'];
    $fh = fopen($tmp, 'r');
    if (!$fh) {
      $err = 'No se pudo abrir el archivo CSV.';
    } else {
      // Leer encabezados (saltando líneas vacías/BOM)
      do {
        $headers = fgetcsv($fh, 0, ',');
        if ($headers === false) break;
        if (is_array($headers)) {
          $headers = array_map(fn($x)=>trim((string)$x), $headers);
        }
      } while ($headers !== false && implode('', $headers) === '');

      if ($headers === false || empty($headers)) {
        $err = 'El archivo CSV está vacío.';
      } elseif (!validarEncabezados($headers)) {
        $err = 'Encabezados inválidos. Se esperan: '.implode(',', encabezados_esperados());
      } else {
        // Leer filas
        while (($row = fgetcsv($fh, 0, ',')) !== false) {
          // Saltar filas totalmente vacías
          $allEmpty = true;
          foreach ($row as $v) { if (trim((string)$v) !== '') { $allEmpty = false; break; } }
          if ($allEmpty) continue;

          // Asociar por headers
          $assoc = [];
          foreach ($headers as $i => $h) {
            $assoc[norm_header($h)] = isset($row[$i]) ? trim((string)$row[$i]) : '';
          }

          // Validación local
          $errCampos = validarFila($assoc);
          if (!empty($errCampos)) {
            $failRows[] = [$assoc, 'Fallo datos: '.implode('; ', $errCampos)];
            $failCount++;
            continue;
          }

          // Guardar en BD
          [$ok, $msg] = guardarRegistroEnBD($assoc, $mysqli);
          if ($ok) {
            $okRows[] = [$assoc, $msg];
            $okCount++;
          } else {
            $failRows[] = [$assoc, $msg ?: 'Error desconocido'];
            $failCount++;
          }
        }
      }
      fclose($fh);
    }
  }

  // ======= Escribir CSV de salida =======
  $stamp = date('Ymd_His');

  $okFile = null;
  if ($okCount > 0) {
    $okFile = "registros_{$stamp}.csv";
    $fp = fopen($baseDir.'/'.$okFile, 'w');
    $hdr = encabezados_esperados(); $hdr[] = 'mensaje';
    fputcsv($fp, $hdr);
    foreach ($okRows as [$r,$msg]) {
      fputcsv($fp, [
        $r['id'] ?? '',
        $r['nombre'] ?? '',
        $r['apellido_paterno'] ?? '',
        $r['apellido_materno'] ?? '',
        $r['telefono'] ?? '',
        $r['email'] ?? '',
        strtoupper($r['curp'] ?? ''),
        $r['plan'] ?? '',
        $msg
      ]);
    }
    fclose($fp);
  }

  $failFile = null;
  if ($failCount > 0) {
    $failFile = "registros_fallidos_{$stamp}.csv";
    $fp = fopen($baseDir.'/'.$failFile, 'w');
    $hdr = encabezados_esperados(); $hdr[] = 'error';
    fputcsv($fp, $hdr);
    foreach ($failRows as [$r,$msg]) {
      fputcsv($fp, [
        $r['id'] ?? '',
        $r['nombre'] ?? '',
        $r['apellido_paterno'] ?? '',
        $r['apellido_materno'] ?? '',
        $r['telefono'] ?? '',
        $r['email'] ?? '',
        strtoupper($r['curp'] ?? ''),
        $r['plan'] ?? '',
        $msg
      ]);
    }
    fclose($fp);
  }

  // ======= Respuesta HTML (modal) =======
  $okUrl   = $okFile   ? $baseUrl.'/'.$okFile   : '';
  $failUrl = $failFile ? $baseUrl.'/'.$failFile : '';
  ?>
  <!doctype html>
  <html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Resultado de carga</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
    <div class="container py-5"></div>

    <div class="modal fade" id="resultadoCarga" tabindex="-1" role="dialog" aria-labelledby="resultadoCargaLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="resultadoCargaLabel">Carga masiva completada</h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span>&times;</span></button>
          </div>
          <div class="modal-body">
            <?php if ($err): ?>
              <div class="alert alert-danger mb-0"><strong>Error:</strong> <?php echo htmlspecialchars($err); ?></div>
            <?php else: ?>
              <p>Se han registrado <strong><?php echo (int)$okCount; ?></strong> clientes correctamente.</p>
              <?php if ($failCount>0): ?>
                <p>Registros con error: <strong><?php echo (int)$failCount; ?></strong>.</p>
              <?php else: ?>
                <p>No se detectaron errores.</p>
              <?php endif; ?>
            <?php endif; ?>
          </div>
          <div class="modal-footer d-flex justify-content-between">
            <div>
              <?php if ($okUrl): ?>
                <a class="btn btn-outline-secondary" href="<?php echo htmlspecialchars($okUrl); ?>" download>Descargar registros correctos</a>
              <?php endif; ?>
            </div>
            <div>
              <?php if ($failUrl): ?>
                <a class="btn btn-danger" href="<?php echo htmlspecialchars($failUrl); ?>" download>Descargar registros erróneos</a>
              <?php endif; ?>
              <button type="button" class="btn btn-primary" onclick="window.history.back()">Aceptar</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function(){
        $('#resultadoCarga').modal('show');
      });
    </script>
  </body>
  </html>
  <?php
  exit;
}
?>
