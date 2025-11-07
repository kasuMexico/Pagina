<!--
  Página 404 de KASU — Redirección automática a la página principal en 4s.
  Limpieza de HTML, accesibilidad básica, y JS moderno compatible.
  03/11/2025 – Revisado por JCCM
-->
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <title>KASU | Error 404</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="../assets/images/kasu_logo.jpeg">

  <!-- Bloque: SEO y control de indexación -->
  <!-- 03/11/2025 – Revisado por JCCM -->
  <meta name="robots" content="noindex,follow">
  <link rel="canonical" href="https://kasu.com.mx/">

  <!-- Bloque: Redirección de respaldo por meta refresh (por si JS está deshabilitado)
       03/11/2025 – Revisado por JCCM -->
  <meta http-equiv="refresh" content="4; url=https://www.kasu.com.mx">

  <style>
    /* Bloque: estilos mínimos responsivos — 03/11/2025 – Revisado por JCCM */
    :root { color-scheme: light dark; }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif;
      display: grid;
      min-height: 100dvh;
      place-items: center;
      background: #0b0b12;
      color: #fff;
    }
    .wrap {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      width: min(1100px, 96vw);
      align-items: center;
    }
    .media img {
      width: 100%;
      height: auto;
      border-radius: 16px;
      display: block;
    }
    .panel {
      text-align: center;
      padding: 12px;
    }
    .panel .logo {
      margin-bottom: 16px;
    }
    .panel .logo img {
      width: 96px;
      height: 96px;
      border-radius: 14px;
    }
    .panel h1 {
      margin: 8px 0 4px;
      font-weight: 700;
      font-size: clamp(24px, 4vw, 40px);
      letter-spacing: .2px;
    }
    .panel p {
      margin: 6px 0;
      opacity: .9;
    }
    .hint {
      margin-top: 14px;
      font-size: .95rem;
      opacity: .8;
    }
    .btn {
      margin-top: 18px;
      display: inline-block;
      padding: 10px 16px;
      border-radius: 999px;
      background: #e83e8c;
      color: #fff;
      text-decoration: none;
      font-weight: 600;
    }
    @media (max-width: 900px) {
      .wrap { grid-template-columns: 1fr; }
    }
  </style>

  <script>
    /**
     * Bloque: Redirección con JavaScript en 4 segundos
     * - Evita uso de string en setTimeout para compatibilidad y seguridad.
     * 03/11/2025 – Revisado por JCCM
     */
    (function redireccionarPagina() {
      const destino = "https://www.kasu.com.mx";
      setTimeout(function () { window.location.href = destino; }, 4000);
    })();
  </script>
</head>
<body>
  <main class="wrap" role="main" aria-labelledby="title-404">
    <section class="media" aria-hidden="true">
      <!-- GIF de marca; usa URL absoluta para evitar rutas rotas -->
      <img src="https://kasu.com.mx/assets/images/nft.gif" alt="">
    </section>

    <section class="panel">
      <div class="logo" aria-hidden="true">
        <img src="https://kasu.com.mx/assets/images/kasu_logo.jpeg" alt="KASU">
      </div>

      <h1 id="title-404">Esto es embarazoso</h1>
      <p>Parece que hubo un error con la página que buscabas.</p>
      <p>La entrada pudo ser eliminada o la dirección no existe.</p>

      <p class="hint">Serás redirigido al inicio en 4 segundos.</p>
      <a class="btn" href="https://www.kasu.com.mx">Ir ahora</a>

      <!-- Bloque: NoScript como respaldo — 03/11/2025 – Revisado por JCCM -->
      <noscript>
        <p class="hint">JavaScript está deshabilitado. Usa el botón para continuar.</p>
      </noscript>
    </section>
  </main>
</body>
</html>