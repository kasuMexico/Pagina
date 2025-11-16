<?php
declare(strict_types=1);

$ref = $_GET['ref'] ?? ($_GET['external_reference'] ?? '');
$ref = trim((string)$ref);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Error en el pago | KASU</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root {
      --primary: #c62828;
      --primary-soft: #ffebee;
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
        linear-gradient(120deg, rgba(0,0,0,.4), rgba(0,0,0,.1)),
        url('../assets/images/registro/mp_error.jpg') center/cover no-repeat;
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
      max-width: 260px;
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
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 600;
      background: var(--primary-soft);
      color: var(--primary);
      margin-bottom: 10px;
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
      margin-bottom: 18px;
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
    .hint {
      font-size: 13px;
      color: #666;
      margin-bottom: 18px;
    }
    .actions {
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
      background: var(--primary);
      color: #fff;
    }
    .btn-secondary {
      background: #fff;
      color: var(--primary);
      border: 1px solid var(--primary-soft);
    }
    .helper {
      margin-top: 14px;
      font-size: 12px;
      color: #888;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="shell">
    <section class="hero" aria-hidden="true">
      <div class="hero-inner">
        <img class="logo" src="../assets/images/logo-kasu.png" alt="KASU">
        <div class="hero-text">
          <h2>Lo intentamos, pero hubo un error</h2>
          <p>Revisa tus datos o prueba con otro medio de pago. Tu protección sigue disponible.</p>
        </div>
      </div>
    </section>

    <main class="card">
      <span class="badge">Pago no completado</span>
      <h1>Tu pago fue rechazado o cancelado</h1>
      <p class="subtitle">
        La operación fue rechazada o cancelada por Mercado Pago.
      </p>

      <?php if ($ref !== ''): ?>
        <div class="ref-box">
          <div class="ref-label">Referencia de tu intento de pago</div>
          <div class="ref-value"><?= htmlspecialchars($ref, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
      <?php endif; ?>

      <p class="hint">
        Verifica los datos de tu tarjeta o intenta con otro medio de pago.
        Si el problema persiste, contáctanos para ayudarte a completar la contratación.
      </p>

      <div class="actions">
        <?php if ($ref !== ''): ?>
          <!-- AHORA sí reintenta el pago con Mercado Pago para la misma referencia -->
          <a class="btn btn-primary" href="/pago/crear_preferencia.php?ref=<?= urlencode($ref) ?>">
            Intentar nuevamente el pago
          </a>
          <a class="btn btn-secondary" href="/pago/estado.php?ref=<?= urlencode($ref) ?>">
            Ver estado del intento
          </a>
        <?php else: ?>
          <!-- Si no hay ref, solo regresamos al inicio -->
          <a class="btn btn-primary" href="https://kasu.com.mx">
            Ir al inicio de KASU
          </a>
        <?php endif; ?>
      </div>

      <p class="helper">
        Es posible que veas un cargo pendiente en tu banco; si el pago fue rechazado, se reversará automáticamente.
      </p>
    </main>
  </div>
</body>
</html>
