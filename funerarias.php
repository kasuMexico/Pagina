<?php
declare(strict_types=1);

$udiActual = 0.0;
$udisServicio = 2000;
$udiSource = __DIR__ . '/login/php/AnalisisDatos/ConfigFondoFunerario.php';

if (is_file($udiSource)) {
  require_once $udiSource;
  if (class_exists('ConfigFondoFunerario') && method_exists('ConfigFondoFunerario', 'getUdiActual')) {
    $udiActual = (float) ConfigFondoFunerario::getUdiActual();
  }
}

if ($udiActual <= 0) {
  $udiActual = 8.20;
}

$udisFmt = number_format($udisServicio, 0);
$udiFmt = number_format($udiActual, 4);
$montoFmt = number_format($udiActual * $udisServicio, 2);
$textoPago = '$' . $montoFmt . ' MXN (' . $udisFmt . ' UDIS)';
$textoUdi = '$' . $udiFmt;
$flashMsg = '';
if (isset($_GET['Msg'])) {
  $flashMsg = htmlspecialchars((string)$_GET['Msg'], ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Red KASU | Registro de funerarias</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Integra tu funeraria a la red KASU. Registro de convenios, requisitos y estandares de servicio.">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Raleway:wght@600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --kasu-blue: #911F66;
      --kasu-blue-dark: #6f154d;
      --kasu-magenta: #911F66;
      --kasu-orange: #f26b38;
      --kasu-green: #2e7d32;
      --ink: #0b1424;
      --muted: #4d5b6c;
      --paper: #f4f6fb;
      --card: #ffffff;
      --line: rgba(145, 31, 102, 0.12);
      --shadow: 0 24px 50px rgba(145, 31, 102, 0.12);
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: "Open Sans", "Segoe UI", sans-serif;
      color: var(--ink);
      background:
        radial-gradient(circle at 8% 8%, rgba(145, 31, 102, 0.16), transparent 55%),
        radial-gradient(circle at 88% 18%, rgba(242, 107, 56, 0.18), transparent 48%),
        linear-gradient(140deg, #f4f6fb 0%, #eef1f7 55%, #f9fafc 100%);
      min-height: 100vh;
    }

    .page {
      position: relative;
      overflow: hidden;
      padding: 40px 18px 80px;
    }

    .page::before,
    .page::after {
      content: "";
      position: absolute;
      width: 420px;
      height: 420px;
      border-radius: 120px;
      opacity: 0.28;
      filter: blur(18px);
      z-index: 0;
    }

    .page::before {
      background: linear-gradient(140deg, rgba(145, 31, 102, 0.5), transparent 70%);
      top: -160px;
      left: -140px;
    }

    .page::after {
      background: linear-gradient(140deg, rgba(145, 31, 102, 0.5), transparent 70%);
      bottom: -160px;
      right: -130px;
    }

    .container {
      width: min(1120px, 100%);
      margin: 0 auto;
      position: relative;
      z-index: 1;
    }

    .brand-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 16px 22px;
      background: var(--card);
      border-radius: 22px;
      border: 1px solid rgba(145, 31, 102, 0.08);
      box-shadow: 0 16px 32px rgba(145, 31, 102, 0.08);
      margin-bottom: 28px;
    }

    .flash {
      margin-bottom: 22px;
      padding: 12px 16px;
      border-radius: 14px;
      background: #ffffff;
      border-left: 4px solid var(--kasu-orange);
      color: var(--kasu-blue-dark);
      box-shadow: 0 10px 22px rgba(145, 31, 102, 0.08);
      font-size: 14px;
    }

    .brand-mark {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .brand-logo {
      height: 46px;
      width: auto;
      display: block;
    }

    .brand-title span {
      font-family: "Raleway", sans-serif;
      font-weight: 800;
      letter-spacing: 0.2em;
      color: var(--kasu-blue);
      font-size: 18px;
      display: block;
    }

    .brand-title small {
      display: block;
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: var(--kasu-magenta);
      margin-top: 6px;
    }

    .brand-pill {
      padding: 8px 14px;
      border-radius: 999px;
      border: 1px solid rgba(145, 31, 102, 0.2);
      background: linear-gradient(90deg, rgba(145, 31, 102, 0.12), rgba(242, 107, 56, 0.12));
      color: var(--kasu-blue);
      font-size: 12px;
      font-weight: 600;
      letter-spacing: 0.12em;
      text-transform: uppercase;
    }

    .hero {
      display: grid;
      grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
      gap: 28px;
      align-items: stretch;
      margin-bottom: 52px;
    }

    .hero-panel {
      background: linear-gradient(140deg, #911f66 0%, #b1347f 55%, #6f154d 100%);
      border-radius: 24px;
      padding: 36px;
      box-shadow: var(--shadow);
      position: relative;
      overflow: hidden;
      color: #ffffff;
      animation: floatIn 0.9s ease both;
    }

    .hero-panel::before,
    .hero-panel::after {
      content: none;
    }

    .eyebrow {
      text-transform: uppercase;
      font-size: 12px;
      letter-spacing: 0.2em;
      color: #f7d0b9;
      font-weight: 600;
    }

    h1 {
      font-family: "Raleway", sans-serif;
      font-size: clamp(30px, 4vw, 44px);
      margin: 12px 0 16px;
      line-height: 1.15;
    }

    .section-title {
      margin: 24px 0 12px;
      font-family: "Raleway", sans-serif;
      font-size: 18px;
      letter-spacing: 0.02em;
      color: rgba(255, 255, 255, 0.92);
    }

    .hero-panel p {
      font-size: 16px;
      line-height: 1.7;
      margin: 0 0 18px;
      color: rgba(255, 255, 255, 0.88);
    }

    .hero-points {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 12px;
      margin-top: 18px;
    }

    .hero-point {
      padding: 12px 16px;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.16);
      color: #ffffff;
      font-size: 13px;
      font-weight: 600;
      border: 1px solid rgba(255, 255, 255, 0.45);
      text-align: center;
      letter-spacing: 0.02em;
    }

    .notice {
      margin-top: 20px;
      background: rgba(255, 255, 255, 0.14);
      border-left: 4px solid var(--kasu-orange);
      padding: 14px 16px;
      font-size: 14px;
      color: rgba(255, 255, 255, 0.9);
      border-radius: 12px;
    }

    .form-card {
      background: var(--card);
      border-radius: 22px;
      padding: 28px;
      border-top: 6px solid var(--kasu-blue);
      box-shadow: var(--shadow);
      animation: floatIn 0.9s ease both;
      animation-delay: 0.1s;
    }

    .form-intro {
      margin: 0 0 14px;
      font-size: 13px;
      color: var(--muted);
      line-height: 1.6;
    }

    form {
      display: grid;
      gap: 14px;
    }

    label {
      font-size: 13px;
      font-weight: 600;
      color: var(--kasu-blue);
    }

    input,
    select,
    textarea {
      width: 100%;
      padding: 12px 14px;
      border-radius: 12px;
      border: 1px solid var(--line);
      font-family: inherit;
      font-size: 14px;
      background: #f8f9fd;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    input:focus,
    select:focus,
    textarea:focus {
      border-color: rgba(145, 31, 102, 0.7);
      box-shadow: 0 0 0 3px rgba(145, 31, 102, 0.18);
      outline: none;
      background: #ffffff;
    }

    textarea {
      min-height: 110px;
      resize: vertical;
    }

    .grid-2 {
      display: grid;
      gap: 12px;
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .grid-3 {
      display: grid;
      gap: 12px;
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .checkbox-grid {
      display: grid;
      gap: 8px;
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .checkbox {
      display: flex;
      gap: 8px;
      align-items: flex-start;
      font-size: 13px;
      color: var(--muted);
    }

    .checkbox input {
      width: 18px;
      height: 18px;
      margin-top: 2px;
      accent-color: var(--kasu-blue);
    }

    .submit-btn {
      margin-top: 8px;
      background: var(--kasu-blue);
      color: white;
      border: none;
      padding: 14px 18px;
      font-size: 14px;
      font-weight: 600;
      border-radius: 14px;
      cursor: pointer;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 24px rgba(145, 31, 102, 0.28);
      background: var(--kasu-magenta);
    }

    .section {
      margin-top: 48px;
      padding: 28px;
      border-radius: 22px;
      background: rgba(255, 255, 255, 0.92);
      border: 1px solid rgba(145, 31, 102, 0.08);
      box-shadow: 0 12px 26px rgba(145, 31, 102, 0.06);
      animation: fadeUp 0.8s ease both;
    }

    .section h2 {
      font-family: "Raleway", sans-serif;
      font-size: clamp(24px, 3vw, 32px);
      margin-bottom: 16px;
      color: var(--kasu-blue);
    }

    .section h2::after {
      content: "";
      display: block;
      width: 52px;
      height: 4px;
      border-radius: 999px;
      background: linear-gradient(90deg, var(--kasu-blue), var(--kasu-orange));
      margin-top: 8px;
    }

    .section p {
      color: var(--muted);
      line-height: 1.7;
      margin-top: 0;
    }

    .cards {
      display: grid;
      gap: 18px;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }

    .info-card {
      padding: 18px;
      border-radius: 16px;
      background: #f8f9fd;
      border: 1px solid rgba(145, 31, 102, 0.12);
      position: relative;
      overflow: hidden;
    }

    .info-card::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: var(--kasu-blue);
    }

    .info-card:nth-child(2)::before {
      background: var(--kasu-green);
    }

    .info-card:nth-child(3)::before {
      background: var(--kasu-orange);
    }

    .info-card:nth-child(4)::before {
      background: var(--kasu-magenta);
    }

    .info-card h3 {
      margin-top: 6px;
      margin-bottom: 8px;
      font-size: 16px;
      color: var(--kasu-blue-dark);
    }

    .steps {
      list-style: none;
      margin: 0;
      padding: 0;
      display: grid;
      gap: 12px;
      counter-reset: step;
    }

    .steps li {
      position: relative;
      padding: 16px 16px 16px 56px;
      background: #f8f9fd;
      border-radius: 16px;
      border: 1px solid rgba(145, 31, 102, 0.12);
      color: var(--muted);
      line-height: 1.7;
    }

    .steps li::before {
      counter-increment: step;
      content: counter(step);
      position: absolute;
      left: 16px;
      top: 16px;
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: var(--kasu-blue);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 13px;
    }

    .hero-panel .steps li {
      background: rgba(255, 255, 255, 0.12);
      border-color: rgba(255, 255, 255, 0.2);
      color: rgba(255, 255, 255, 0.9);
    }

    .hero-panel .steps li::before {
      background: #ffffff;
      color: var(--kasu-blue);
    }

    ul {
      margin: 0;
      padding-left: 0;
      color: var(--muted);
      line-height: 1.7;
      list-style: none;
    }

    ul li {
      position: relative;
      padding-left: 18px;
      margin-bottom: 8px;
    }

    ul li::before {
      content: "";
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--kasu-blue);
      position: absolute;
      left: 0;
      top: 8px;
    }

    ul li:nth-child(even)::before {
      background: var(--kasu-orange);
    }

    .cta-strip {
      margin-top: 52px;
      padding: 24px 28px;
      border-radius: 22px;
      background: linear-gradient(120deg, #911f66 0%, #b1347f 60%, #f26b38 100%);
      color: #f9f6f1;
      display: flex;
      flex-wrap: wrap;
      gap: 14px;
      align-items: center;
      justify-content: space-between;
      box-shadow: var(--shadow);
    }

    .cta-strip p {
      margin: 0;
      font-size: 15px;
    }

    .cta-strip a {
      display: inline-block;
      background: #ffffff;
      color: var(--kasu-blue);
      padding: 10px 16px;
      border-radius: 999px;
      text-decoration: none;
      font-weight: 600;
      border: 1px solid rgba(255, 255, 255, 0.7);
    }

    @keyframes floatIn {
      from {
        opacity: 0;
        transform: translateY(18px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(24px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 920px) {
      .hero {
        grid-template-columns: 1fr;
      }
      .grid-2,
      .grid-3,
      .checkbox-grid {
        grid-template-columns: 1fr;
      }
      .brand-header {
        flex-direction: column;
        align-items: flex-start;
      }
      .cta-strip {
        flex-direction: column;
        align-items: flex-start;
      }
    }

    @media (prefers-reduced-motion: reduce) {
      * {
        animation: none !important;
        transition: none !important;
      }
    }
  </style>
</head>
<body>
  <main class="page">
    <div class="container">
      <header class="brand-header">
        <div class="brand-mark">
          <img src="assets/images/logo-kasu.png" alt="KASU Servicios a Futuro" class="brand-logo">
          <div class="brand-title">
            <span>KASU</span>
            <small>Servicios a Futuro</small>
          </div>
        </div>
        <div class="brand-pill">Registro de funerarias</div>
      </header>

      <?php if ($flashMsg !== ''): ?>
        <div class="flash"><?= $flashMsg ?></div>
      <?php endif; ?>

      <section class="hero">
        <div class="hero-panel">
          <span class="eyebrow">Red KASU</span>
          <h1>Integra tu funeraria a la red de servicios KASU</h1>
          <p>
            Los servicios KASU solo se pueden ejecutar mediante convenio con funeraria.
            El primer paso es validar el cumplimiento de los 10 puntos de calidad KASU y,
            al firmar el contrato de prestacion de servicios, tu funeraria queda dada de alta
            en la red con una zona asignada.
          </p>
          <p>
            La cobertura es de 60 km en zonas no urbanas y 3 km en zonas altamente urbanas
            (CDMX, GDL, etc.). KASU hace promocion en redes sociales y canales tecnologicos
            para generar solicitudes inmediatas y servicios a futuro.
          </p>
          <div class="hero-points">
            <div class="hero-point">Pago equivalente a <?= $textoPago ?></div>
            <div class="hero-point">10 puntos de calidad KASU</div>
            <div class="hero-point">Zona asignada 60 km / 3 km</div>
            <div class="hero-point">Servicios inmediatos sin comision</div>
          </div>

          <h3 class="section-title">Como funciona la red de funerarias</h3>
          <ol class="steps">
            <li>Se revisa que la funeraria cumpla con los 10 puntos de calidad KASU.</li>
            <li>Se firma contrato de prestacion de servicios y se asigna zona geografica:
              60 km en zonas no urbanas y 3 km en zonas altamente urbanas (CDMX, GDL, etc.).</li>
            <li>KASU hace promocion en redes sociales y tecnologicas para la promocion de los servicios.</li>
            <li>Servicios inmediatos: KASU vincula a la funeraria para realizar el servicio con la calidad KASU,
              sin ligar el servicio a la empresa. Si el cliente es KASU, la funeraria realiza el servicio completo
              sin cobrar dinero extra al cliente.</li>
            <li>Al concluir el servicio KASU, la funeraria gestiona acta de defuncion y baja de CURP,
              envia por correo a atencion al cliente el acta y la factura con IVA, se valida la CURP y el documento
              y se programa el pago por transferencia electronica.</li>
          </ol>
        </div>

        <div class="form-card">
          <p class="form-intro">
            En esta seccion validamos la identidad de la persona y la funeraria para confirmar
            si es viable convertirse en funeraria autorizada.
          </p>
          <form action="php/Registro_Funerarias.php" method="post">
            <div>
              <label for="nombre-comercial">Nombre comercial de la funeraria *</label>
              <input id="nombre-comercial" name="nombre_comercial" type="text" required placeholder="Ejemplo: Funeraria San Miguel">
            </div>
            <div>
              <label for="razon-social">Razon social *</label>
              <input id="razon-social" name="razon_social" type="text" required placeholder="Ejemplo: Servicios Funerarios del Centro SA de CV">
            </div>
            <div class="grid-2">
              <div>
                <label for="rfc">RFC *</label>
                <input id="rfc" name="rfc" type="text" required placeholder="Ejemplo: SFC850101ABC">
              </div>
              <div>
                <label for="anios">Años en operacion *</label>
                <input id="anios" name="anios" type="number" min="0" required placeholder="Ejemplo: 8">
              </div>
            </div>
            <div class="grid-2">
              <div>
                <label for="contacto">Responsable / contacto *</label>
                <input id="contacto" name="contacto" type="text" required placeholder="Nombre y apellidos">
              </div>
              <div>
                <label for="cargo">Cargo *</label>
                <input id="cargo" name="cargo" type="text" required placeholder="Director, Gerente, etc.">
              </div>
            </div>
            <div class="grid-2">
              <div>
                <label for="telefono">Telefono *</label>
                <input id="telefono" name="telefono" type="tel" required inputmode="numeric" pattern="[0-9]{10}" placeholder="10 digitos">
              </div>
              <div>
                <label for="whatsapp">WhatsApp</label>
                <input id="whatsapp" name="whatsapp" type="tel" inputmode="numeric" pattern="[0-9]{10}" placeholder="10 digitos">
              </div>
            </div>
            <div class="grid-2">
              <div>
                <label for="email">Email *</label>
                <input id="email" name="email" type="email" required placeholder="contacto@funeraria.mx">
              </div>
              <div>
                <label for="web">Sitio web / redes</label>
                <input id="web" name="web" type="url" placeholder="https://">
              </div>
            </div>
            <div>
              <label for="direccion">Direccion completa *</label>
              <input id="direccion" name="direccion" type="text" required placeholder="Calle, numero, colonia">
            </div>
            <div class="grid-3">
              <div>
                <label for="ciudad">Ciudad *</label>
                <input id="ciudad" name="ciudad" type="text" required>
              </div>
              <div>
                <label for="estado">Estado *</label>
                <input id="estado" name="estado" type="text" required>
              </div>
              <div>
                <label for="cp">CP *</label>
                <input id="cp" name="cp" type="text" required inputmode="numeric">
              </div>
            </div>
            <div>
              <label for="cobertura">Cobertura de servicio *</label>
              <input id="cobertura" name="cobertura" type="text" required placeholder="Municipios y radio de traslado">
            </div>
            <div>
              <label>Servicios disponibles *</label>
              <div class="checkbox-grid">
                <label class="checkbox">
                  <input type="checkbox" name="servicios[]" value="traslado" required>
                  Traslado y gestion de tramites
                </label>
                <label class="checkbox">
                  <input type="checkbox" name="servicios[]" value="velacion">
                  Sala de velacion y equipo
                </label>
                <label class="checkbox">
                  <input type="checkbox" name="servicios[]" value="cremacion">
                  Cremacion (propia o subcontratada)
                </label>
                <label class="checkbox">
                  <input type="checkbox" name="servicios[]" value="inhumacion">
                  Inhumacion
                </label>
              </div>
            </div>
            <div class="grid-2">
              <div>
                <label for="salas">Numero de salas *</label>
                <input id="salas" name="salas" type="number" min="0" required>
              </div>
              <div>
                <label for="capacidad">Capacidad por sala *</label>
                <input id="capacidad" name="capacidad" type="number" min="10" required>
              </div>
            </div>
            <div class="grid-2">
              <div>
                <label for="disponibilidad">Disponibilidad 24/7 *</label>
                <select id="disponibilidad" name="disponibilidad" required>
                  <option value="">Selecciona una opcion</option>
                  <option value="si">Si</option>
                  <option value="parcial">Parcial</option>
                  <option value="no">No</option>
                </select>
              </div>
              <div>
                <label for="permisos">Permisos y licencias vigentes *</label>
                <select id="permisos" name="permisos" required>
                  <option value="">Selecciona una opcion</option>
                  <option value="si">Si</option>
                  <option value="en-proceso">En proceso</option>
                  <option value="no">No</option>
                </select>
              </div>
            </div>
            <div>
              <label for="comentarios">Comentarios adicionales</label>
              <textarea id="comentarios" name="comentarios" placeholder="Describe capacidad, diferenciales o coberturas especiales."></textarea>
            </div>
            <label class="checkbox">
              <input type="checkbox" name="acepta" required>
              Confirmo que entiendo que los servicios KASU solo se ejecutan mediante convenio y que el pago es equivalente a <?= $textoPago ?> segun contrato.
            </label>
            <button type="submit" class="submit-btn">Solicitar registro</button>
          </form>
        </div>
      </section>

      <section class="section">
        <h2>Lo que debe incluir el servicio funerario</h2>
        <ul>
          <li>Traslado del cuerpo en un radio maximo de 60 km; kilometros extra se cobran a la familia.</li>
          <li>Sala de velacion con capacidad minima de 50 personas, area de cafeteria y estacionamiento para 5 autos.</li>
          <li>Floreros y equipo de velacion con porta ataud, disponibles para sala o servicio en domicilio.</li>
          <li>Servicio de cafeteria para 50 personas (cafe o te, galletas, agua y sandwich).</li>
          <li>Acondicionamiento del cuerpo: embalsamado si aplica, maquillaje y mortaja.</li>
          <li>Cremacion propia o subcontratada, con ataud de madera barnizada segun el tipo de servicio.</li>
          <li>Tramites y gestiones necesarios para la prestacion completa del servicio.</li>
        </ul>
      </section>

      <section class="section">
        <h2>Que debe cumplir para dar un buen servicio</h2>
        <div class="cards">
          <div class="info-card">
            <h3>Respuesta y coordinacion</h3>
            <p>Atencion inmediata, contacto directo con KASU y cumplimiento de los 10 puntos de calidad.</p>
          </div>
          <div class="info-card">
            <h3>Trato digno y transparente</h3>
            <p>Atencion sensible, informacion clara sobre costos fuera de cobertura y tiempos reales.</p>
          </div>
          <div class="info-card">
            <h3>Instalaciones seguras</h3>
            <p>Espacios limpios, protocolos sanitarios, equipos funcionales y personal capacitado.</p>
          </div>
          <div class="info-card">
            <h3>Documentacion vigente</h3>
            <p>Permisos municipales y de salud al dia, polizas, licencias y cumplimiento normativo.</p>
          </div>
        </div>
      </section>

      <section class="section">
        <h2>Documentacion requerida para el registro</h2>
        <ul>
          <li>Acta constitutiva y RFC.</li>
          <li>Identificacion del representante legal.</li>
          <li>Comprobante de domicilio fiscal y permisos municipales.</li>
          <li>Licencias sanitarias y registros de crematorio (si aplica).</li>
          <li>Datos bancarios para pagos por convenio.</li>
        </ul>
      </section>

      <section class="cta-strip">
        <p>Listo para integrarte a la red? Envia el formulario y un asesor de convenios te contactara.</p>
        <a href="#nombre-comercial">Quiero registrarme</a>
      </section>
    </div>
  </main>
</body>
</html>
