<?php
declare(strict_types=1);

$verCache = null;
if (is_file(__DIR__ . '/eia/librerias.php')) {
  require_once __DIR__ . '/eia/librerias.php';
  $verCache = $VerCache ?? null;
}
$verCache = is_numeric($verCache) ? (string) $verCache : (string) time();
if (is_file(__DIR__ . '/eia/php/Telcto.php')) {
  require_once __DIR__ . '/eia/php/Telcto.php';
}
$tel = isset($tel) && $tel !== '' ? $tel : '7208177632';

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
  <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css?v=<?php echo $verCache; ?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/font-awesome.css?v=<?php echo $verCache; ?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/kasu-menu.css?v=<?php echo $verCache; ?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/index-home.css?v=<?php echo $verCache; ?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/funerarias.css?v=<?php echo $verCache; ?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/kasu-chat.css?v=<?php echo $verCache; ?>">
</head>
<body class="kasu-ui funerarias-page">
  <?php require_once __DIR__ . '/html/MenuPrincipal.php'; ?>
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
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    var fabButton = document.getElementById('kasu-fab');
    var fabPanel = document.getElementById('kasu-fab-panel');
    var chatOpen = document.getElementById('kasu-chat-open');
    var chatOverlay = document.getElementById('kasu-chat-overlay');
    var chatClose = document.getElementById('kasu-chat-close');
    var chatForm = document.getElementById('kasu-chat-form');
    var chatInput = document.getElementById('kasu-chat-input');
    var chatBody = document.getElementById('kasu-chat-messages');

    var hideFabPanel = function () {
      if (fabPanel) {
        fabPanel.setAttribute('hidden', 'hidden');
      }
      if (fabButton) {
        fabButton.setAttribute('aria-expanded', 'false');
      }
    };

    var showFabPanel = function () {
      if (fabPanel) {
        fabPanel.removeAttribute('hidden');
      }
      if (fabButton) {
        fabButton.setAttribute('aria-expanded', 'true');
      }
    };

    var openChat = function () {
      if (!chatOverlay) return;
      chatOverlay.removeAttribute('hidden');
      if (chatOpen) chatOpen.setAttribute('aria-expanded', 'true');
      if (chatInput) chatInput.focus();
      hideFabPanel();
    };

    var closeChat = function () {
      if (!chatOverlay) return;
      chatOverlay.setAttribute('hidden', 'hidden');
      if (chatOpen) chatOpen.setAttribute('aria-expanded', 'false');
      showFabPanel();
    };

    if (fabButton && fabPanel) {
      fabButton.addEventListener('click', function () {
        var chatOpenNow = chatOverlay && !chatOverlay.hasAttribute('hidden');
        if (chatOpenNow) {
          closeChat();
          showFabPanel();
          return;
        }

        var expanded = fabButton.getAttribute('aria-expanded') === 'true';
        fabButton.setAttribute('aria-expanded', String(!expanded));
        if (expanded) {
          fabPanel.setAttribute('hidden', 'hidden');
        } else {
          fabPanel.removeAttribute('hidden');
        }
      });
    }

    if (chatOpen) {
      chatOpen.addEventListener('click', function () {
        var isOpen = chatOverlay && !chatOverlay.hasAttribute('hidden');
        if (isOpen) {
          closeChat();
        } else {
          openChat();
        }
      });
    }

    if (chatClose) {
      chatClose.addEventListener('click', closeChat);
    }

    if (chatForm && chatInput && chatBody) {
      var endpoint = '/eia/Vista-360/kasu_chat_publico.php';
      var typingNode = null;

      var appendUserMessage = function (message) {
        var wrapper = document.createElement('div');
        wrapper.className = 'kasu-chat-message kasu-chat-message--user';
        var bubble = document.createElement('div');
        bubble.className = 'kasu-chat-bubble';
        bubble.textContent = message;
        wrapper.appendChild(bubble);
        chatBody.appendChild(wrapper);
      };

      var appendBotMessage = function (html) {
        var wrapper = document.createElement('div');
        wrapper.className = 'kasu-chat-message kasu-chat-message--bot';
        var avatar = document.createElement('img');
        avatar.src = '/assets/images/flor_redonda.svg';
        avatar.alt = 'KASU';
        avatar.className = 'kasu-chat-avatar';
        avatar.width = 26;
        avatar.height = 26;
        avatar.loading = 'lazy';
        avatar.decoding = 'async';
        var bubble = document.createElement('div');
        bubble.className = 'kasu-chat-bubble';
        bubble.innerHTML = html;
        wrapper.appendChild(avatar);
        wrapper.appendChild(bubble);
        chatBody.appendChild(wrapper);
      };

      var showTyping = function () {
        if (typingNode) return;
        typingNode = document.createElement('div');
        typingNode.className = 'kasu-chat-message kasu-chat-message--bot';
        var avatar = document.createElement('img');
        avatar.src = '/assets/images/flor_redonda.svg';
        avatar.alt = 'KASU';
        avatar.className = 'kasu-chat-avatar';
        avatar.width = 26;
        avatar.height = 26;
        avatar.loading = 'lazy';
        avatar.decoding = 'async';
        var bubble = document.createElement('div');
        bubble.className = 'kasu-chat-bubble';
        bubble.textContent = 'Escribiendo...';
        typingNode.appendChild(avatar);
        typingNode.appendChild(bubble);
        chatBody.appendChild(typingNode);
      };

      var hideTyping = function () {
        if (!typingNode) return;
        typingNode.remove();
        typingNode = null;
      };

      chatForm.addEventListener('submit', function (event) {
        event.preventDefault();
        var message = chatInput.value.trim();
        if (!message) return;

        appendUserMessage(message);
        chatBody.scrollTop = chatBody.scrollHeight;
        chatInput.value = '';
        chatInput.disabled = true;
        showTyping();

        var chatToken = localStorage.getItem('kasu_chat_token');
        if (!chatToken) {
          if (window.crypto && window.crypto.randomUUID) {
            chatToken = window.crypto.randomUUID();
          } else {
            chatToken = 'kasu_' + Date.now() + '_' + Math.random().toString(16).slice(2);
          }
          localStorage.setItem('kasu_chat_token', chatToken);
        }

        fetch(endpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            mensaje: message,
            source: window.location.pathname,
            chat_token: chatToken
          })
        })
        .then(function (resp) { return resp.json(); })
        .then(function (data) {
          hideTyping();
          chatInput.disabled = false;
          if (data && data.chat_token) {
            localStorage.setItem('kasu_chat_token', data.chat_token);
          }
          if (data && data.ok && data.html) {
            appendBotMessage(data.html);
          } else {
            appendBotMessage('No pude procesar tu solicitud. Intenta de nuevo.');
          }
          chatBody.scrollTop = chatBody.scrollHeight;
        })
        .catch(function () {
          hideTyping();
          chatInput.disabled = false;
          appendBotMessage('No pude conectar con el chat. Intenta mas tarde.');
          chatBody.scrollTop = chatBody.scrollHeight;
        });
      });
    }
  });
  </script>

  <div class="kasu-fab-wrap" aria-live="polite">
    <div class="kasu-fab-panel" id="kasu-fab-panel" hidden>
      <a href="tel:<?php echo isset($tel) ? htmlspecialchars($tel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : ''; ?>" class="kasu-fab-action kasu-fab-action--call" aria-label="Llamar a KASU">
        <span class="kasu-fab-icon" aria-hidden="true">📞</span>
        Llamar a KASU
      </a>
      <a href="https://wa.me/<?php echo preg_replace('/\\D/', '', $tel ?? ''); ?>?text=Hola,%20requiero%20atención%20inmediata%20de%20KASU" class="kasu-fab-action kasu-fab-action--whats" target="_blank" rel="noopener" aria-label="Enviar WhatsApp a KASU">
        <span class="kasu-fab-icon" aria-hidden="true">💬</span>
        Enviar WhatsApp
      </a>
      <button type="button" class="kasu-fab-action kasu-fab-action--chat" id="kasu-chat-open" aria-expanded="false" aria-controls="kasu-chat-overlay">
        <span class="kasu-fab-icon" aria-hidden="true">🗨️</span>
        Hablar con alguien
      </button>
    </div>
    <button type="button" class="kasu-fab" id="kasu-fab" aria-expanded="false" aria-controls="kasu-fab-panel">
      <img src="/assets/images/flor_redonda.svg" alt="" width="34" height="34" loading="lazy" decoding="async">
      Atención al cliente
    </button>
  </div>

  <section class="kasu-chat-overlay" id="kasu-chat-overlay" aria-live="polite" hidden>
    <div class="kasu-chat-panel">
      <header class="kasu-chat-panel__header">
        <div class="kasu-chat-panel__brand">
          <img src="/assets/images/flor_redonda.svg" alt="KASU" width="28" height="28" loading="lazy" decoding="async">
          <div>
            <p class="kasu-chat-panel__title">Chat KASU</p>
            <span class="kasu-chat-panel__status">En linea</span>
          </div>
        </div>
        <div class="kasu-chat-panel__actions">
          <span class="kasu-chat-panel__pill">Vista 360</span>
          <button type="button" class="kasu-chat-panel__close" id="kasu-chat-close" aria-label="Cerrar chat">×</button>
        </div>
      </header>

      <div class="kasu-chat-panel__messages" id="kasu-chat-messages">
        <div class="kasu-chat-message kasu-chat-message--bot">
          <img src="/assets/images/flor_redonda.svg" alt="KASU" class="kasu-chat-avatar" width="26" height="26" loading="lazy" decoding="async">
          <div class="kasu-chat-bubble">
            Hola, soy la asistente virtual de KASU. en que puedo ayudarte hoy?
          </div>
        </div>
      </div>

      <form class="kasu-chat-panel__form" id="kasu-chat-form" autocomplete="off">
        <input type="text" id="kasu-chat-input" name="kasu-chat-input" placeholder="Escribe tu mensaje">
        <button type="submit">Enviar</button>
      </form>
    </div>
  </section>
</body>
</html>
