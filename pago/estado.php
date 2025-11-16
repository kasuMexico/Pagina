<?php
declare(strict_types=1);

// ==================== Entrada ====================
$ref = $_GET['ref'] ?? ($_GET['external_reference'] ?? '');
$ref = trim((string)$ref);

if ($ref === '') {
    http_response_code(400);
    $errorMsg = 'No se recibió la referencia del pago.';
} else {
    require __DIR__ . '/../eia/librerias.php'; // debe definir $mysqli

    if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
        http_response_code(500);
        $errorMsg = 'No fue posible conectarse a la base de datos.';
    } else {
        $mysqli->set_charset('utf8mb4');

        $sql = "
        SELECT
          v.Id,
          v.Nombre,
          v.Producto,
          v.CostoVenta,
          v.IdFIrma,
          v.NumeroPagos,
          v.DiaPago,
          vm.plan,
          vm.plazo_meses,
          vm.dia_pago        AS vm_dia_pago,
          vm.precio_base,
          vm.amount          AS mp_amount,
          vm.estatus         AS estatus_negocio,
          vm.estatus_pago    AS estatus_pago,
          vm.mp_payment_id,
          vm.mp_payment_status,
          vm.mp_status_detail,
          vm.mp_payment_method,
          vm.currency_id,
          vm.payer_email,
          vm.date_created,
          vm.date_approved,
          vm.updated_at
        FROM Venta v
        LEFT JOIN VentasMercadoPago vm ON vm.folio = v.IdFIrma
        WHERE v.IdFIrma = ?
        LIMIT 1";

        $st = $mysqli->prepare($sql);
        if ($st) {
            $st->bind_param('s', $ref);
            $st->execute();
            $info = $st->get_result()->fetch_assoc() ?: null;
            $st->close();
        } else {
            $info = null;
        }

        if (!$info) {
            http_response_code(404);
            $errorMsg = 'No encontramos información asociada a esta referencia.';
        }
    }
}

// ==================== Normalización de estado ====================
$estadoPago    = 'DESCONOCIDO';
$estadoNegocio = 'PREVENTA';
$badgeTexto    = 'Estado no disponible';
$badgeClase    = 'badge-neutral';
$mensajeLinea  = 'No pudimos determinar el estado del pago.';

if (!empty($info)) {
    $estadoPago    = strtoupper((string)($info['estatus_pago'] ?? ''));
    $estadoNegocio = strtoupper((string)($info['estatus_negocio'] ?? ''));
    if ($estadoNegocio === '') {
        $estadoNegocio = 'PREVENTA';
    }

    switch ($estadoPago) {
        case 'APROBADO':
            $badgeTexto   = 'Pago aprobado';
            $badgeClase   = 'badge-ok';
            $mensajeLinea = 'Tu pago fue aprobado por Mercado Pago.';
            break;
        case 'PENDIENTE':
            $badgeTexto   = 'Pago pendiente';
            $badgeClase   = 'badge-pending';
            $mensajeLinea = 'Tu pago está en proceso de confirmación.';
            break;
        case 'RECHAZADO':
            $badgeTexto   = 'Pago rechazado';
            $badgeClase   = 'badge-error';
            $mensajeLinea = 'El pago fue rechazado o cancelado.';
            break;
        default:
            $badgeTexto   = 'Estado no disponible';
            $badgeClase   = 'badge-neutral';
            $mensajeLinea = 'No pudimos determinar el estado actual del pago.';
            break;
    }
}

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Estado de tu pago | KASU</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root {
      --ok: #2e7d32;
      --ok-soft: #e8f5e9;
      --pending: #f57c00;
      --pending-soft: #fff3e0;
      --error: #c62828;
      --error-soft: #ffebee;
      --neutral: #455a64;
      --neutral-soft: #eceff1;
      --bg: #f5f5f9;
      --text: #222;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }
    .shell {
      width: 100%;
      max-width: 960px;
      display: grid;
      grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
      gap: 20px;
    }
    @media (max-width: 768px) {
      .shell {
        grid-template-columns: minmax(0, 1fr);
      }
    }
    .hero {
      position: relative;
      border-radius: 20px;
      overflow: hidden;
      min-height: 260px;
      background:
        linear-gradient(120deg, rgba(0,0,0,.35), rgba(0,0,0,.05)),
        url('../assets/images/registro/mp_espera.png') center/cover no-repeat;
      box-shadow: 0 12px 30px rgba(0,0,0,.18);
    }
    .hero-inner {
      position: absolute;
      inset: 18px 18px 18px 18px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      color: #fff;
    }
    .logo {
      width: 120px;
      align-self: flex-end;
      filter: drop-shadow(0 2px 4px rgba(0,0,0,.35));
    }
    .hero-text h2 {
      font-size: 24px;
      margin-bottom: 4px;
    }
    .hero-text p {
      font-size: 14px;
      max-width: 280px;
    }
    .card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0,0,0,.08);
      padding: 22px 22px 18px;
    }
    .badge {
      display: inline-flex;
      align-items: center;
      padding: 4px 12px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 600;
      margin-bottom: 10px;
    }
    .badge-ok {
      background: var(--ok-soft);
      color: var(--ok);
    }
    .badge-pending {
      background: var(--pending-soft);
      color: var(--pending);
    }
    .badge-error {
      background: var(--error-soft);
      color: var(--error);
    }
    .badge-neutral {
      background: var(--neutral-soft);
      color: var(--neutral);
    }
    h1 {
      font-size: 22px;
      margin-bottom: 6px;
    }
    .subtitle {
      font-size: 14px;
      color: #555;
      margin-bottom: 18px;
    }
    .ref-box {
      background: #fafafa;
      border-radius: 10px;
      padding: 10px 12px;
      font-size: 13px;
      margin-bottom: 16px;
      border: 1px dashed #ddd;
    }
    .ref-label {
      font-weight: 600;
      font-size: 12px;
      text-transform: uppercase;
      color: #777;
      margin-bottom: 2px;
    }
    .ref-value {
      font-family: "SF Mono", Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      word-break: break-all;
    }
    dl {
      display: grid;
      grid-template-columns: 1.2fr 1.8fr;
      row-gap: 6px;
      column-gap: 10px;
      font-size: 13px;
      margin-bottom: 14px;
    }
    dt {
      color: #666;
    }
    dd {
      font-weight: 500;
    }
    .hint {
      font-size: 12px;
      color: #777;
      margin-top: 10px;
    }
    .actions {
      margin-top: 18px;
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex: 1 1 auto;
      min-width: 0;
      border-radius: 999px;
      padding: 10px 16px;
      font-size: 14px;
      font-weight: 600;
      text-decoration: none;
      border: none;
      cursor: pointer;
    }
    .btn-primary {
      background: #3949ab;
      color: #fff;
    }
    .btn-secondary {
      background: #fff;
      color: #3949ab;
      border: 1px solid #c5cae9;
    }
    .error-msg {
      font-size: 14px;
      color: #c62828;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="shell">
    <section class="hero" aria-hidden="true">
      <div class="hero-inner">
        <img class="logo" src="../assets/images/logo-kasu.png" alt="KASU">
        <div class="hero-text">
          <h2>#Protege a Quien Amas</h2>
          <p>Consulta aquí el estado de tu pago y conserva tu referencia para cualquier aclaración.</p>
        </div>
      </div>
    </section>

    <main class="card">
      <?php if (!empty($errorMsg)): ?>
        <span class="badge badge-error">Sin información</span>
        <h1>No pudimos mostrar el estado de tu pago</h1>
        <p class="subtitle"><?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?></p>
        <?php if ($ref !== ''): ?>
          <div class="ref-box">
            <div class="ref-label">Referencia consultada</div>
            <div class="ref-value"><?= htmlspecialchars($ref, ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        <?php endif; ?>
        <div class="actions">
          <a class="btn btn-primary" href="https://kasu.com.mx">Ir al inicio de KASU</a>
        </div>
      <?php else: ?>
        <span class="badge <?= $badgeClase ?>"><?= htmlspecialchars($badgeTexto, ENT_QUOTES, 'UTF-8') ?></span>
        <h1>Estado de tu pago</h1>
        <p class="subtitle"><?= htmlspecialchars($mensajeLinea, ENT_QUOTES, 'UTF-8') ?></p>

        <div class="ref-box">
          <div class="ref-label">Referencia de tu compra</div>
          <div class="ref-value"><?= htmlspecialchars($ref, ENT_QUOTES, 'UTF-8') ?></div>
        </div>

        <dl>
          <dt>Producto contratado</dt>
          <dd><?= htmlspecialchars($info['Producto'] ?? 'Servicio KASU', ENT_QUOTES, 'UTF-8') ?></dd>

          <dt>Nombre del titular</dt>
          <dd><?= htmlspecialchars($info['Nombre'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>

          <dt>Importe del ciclo</dt>
          <dd>
            $<?= number_format((float)($info['mp_amount'] ?? $info['precio_base'] ?? $info['CostoVenta'] ?? 0), 2) ?>
            <?= htmlspecialchars($info['currency_id'] ?? 'MXN', ENT_QUOTES, 'UTF-8') ?>
          </dd>

          <dt>Plan</dt>
          <dd>
            <?php
              $plan = strtoupper((string)($info['plan'] ?? ''));
              $plazo = (int)($info['plazo_meses'] ?? $info['NumeroPagos'] ?? 1);
              if ($plan === '') { $plan = ($plazo <= 1) ? 'CONTADO' : 'MENSUAL'; }
              echo htmlspecialchars($plan . ($plazo > 1 ? " ({$plazo} meses)" : ''), ENT_QUOTES, 'UTF-8');
            ?>
          </dd>

          <dt>Estado del pago</dt>
          <dd><?= htmlspecialchars($estadoPago, ENT_QUOTES, 'UTF-8') ?></dd>

          <dt>Estado de la póliza</dt>
          <dd><?= htmlspecialchars($estadoNegocio, ENT_QUOTES, 'UTF-8') ?></dd>

          <dt>Método de pago</dt>
          <dd><?= htmlspecialchars($info['mp_payment_method'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>

          <dt>Fecha de aprobación</dt>
          <dd><?= htmlspecialchars($info['date_approved'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></dd>
        </dl>

        <p class="hint">
          Última actualización: <?= htmlspecialchars($info['updated_at'] ?? 'N/D', ENT_QUOTES, 'UTF-8') ?>.
          Si ves alguna discrepancia, contáctanos indicando esta referencia.
        </p>

        <div class="actions">
        <a class="btn btn-primary" href="https://kasu.com.mx">
            Ir al inicio de KASU
        </a>

        <?php if ($estadoPago === 'APROBADO'): ?>
            <!-- Solo si el pago ya está aprobado tiene sentido volver a la confirmación -->
            <a class="btn btn-secondary" href="/pago/exito.php?ref=<?= urlencode($ref) ?>">
            Ver comprobante de confirmación
            </a>

        <?php elseif ($estadoPago === 'PENDIENTE'): ?>
            <!-- Para pendientes lo regresamos a la página de proceso -->
            <a class="btn btn-secondary" href="/pago/pendiente.php?ref=<?= urlencode($ref) ?>">
            Volver a la página de proceso
            </a>

        <?php elseif ($estadoPago === 'RECHAZADO'): ?>
            <!-- Para rechazados lo llevamos a la página de error -->
            <a class="btn btn-secondary" href="/pago/error.php?ref=<?= urlencode($ref) ?>">
            Ver detalle del rechazo
            </a>

        <?php else: ?>
            <!-- Estado desconocido: solo inicio -->
        <?php endif; ?>
        </div>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>