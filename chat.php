<?php
require_once __DIR__ . '/eia/librerias.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>KASU | Chat de atencion</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Chat de atencion KASU. Conversa con nuestro equipo de soporte.">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">

  <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css?v=<?php echo $VerCache; ?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/font-awesome.css?v=<?php echo $VerCache; ?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/kasu-menu.css?v=<?php echo $VerCache; ?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/index-home.css?v=<?php echo $VerCache; ?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/kasu-chat.css?v=<?php echo $VerCache; ?>">
</head>
<body class="kasu-ui kasu-chat-page">
  <?php require_once __DIR__ . '/html/MenuPrincipal.php'; ?>

  <main class="kasu-chat-page__main">
    <div class="container">
      <section class="kasu-chat-window">
        <div class="kasu-chat-intro">
          <span class="kasu-chat-intro__eyebrow">Atencion inmediata</span>
          <h1>Habla con el equipo KASU</h1>
          <p>
            Este canal es para resolver dudas sobre tu servicio, poliza o proceso de registro.
            Un asesor te respondera tan pronto como sea posible.
          </p>
          <div class="kasu-chat-intro__highlights">
            <div class="kasu-chat-intro__card">
              <span>Horario</span>
              <strong>09:00 - 17:00</strong>
              <small>Horario centro de Mexico</small>
            </div>
            <div class="kasu-chat-intro__card">
              <span>Respuesta</span>
              <strong>En el dia</strong>
              <small>Te contactamos por este medio</small>
            </div>
          </div>
        </div>

        <div class="kasu-chat-panel">
          <header class="kasu-chat-panel__header">
            <div class="kasu-chat-panel__brand">
              <img src="/assets/images/flor_redonda.svg" alt="KASU" width="28" height="28" loading="lazy" decoding="async">
              <div>
                <p class="kasu-chat-panel__title">Chat KASU</p>
                <span class="kasu-chat-panel__status">En linea</span>
              </div>
            </div>
            <span class="kasu-chat-panel__pill">Soporte</span>
          </header>

          <div class="kasu-chat-panel__messages" id="kasu-chat-messages">
            <div class="kasu-chat-message kasu-chat-message--bot">
              <img src="/assets/images/flor_redonda.svg" alt="KASU" class="kasu-chat-avatar" width="26" height="26" loading="lazy" decoding="async">
              <div class="kasu-chat-bubble">
                Hola, soy la asistencia virtual de KASU. Comparte tu consulta y un asesor te contactara.
              </div>
            </div>
            <div class="kasu-chat-message kasu-chat-message--user">
              <div class="kasu-chat-bubble">
                Hola, quiero actualizar mis datos.
              </div>
            </div>
          </div>

          <form class="kasu-chat-panel__form" id="kasu-chat-form" autocomplete="off">
            <input type="text" id="kasu-chat-input" name="kasu-chat-input" placeholder="Escribe tu mensaje">
            <button type="submit">Enviar</button>
          </form>
          <div class="kasu-chat-panel__footer">Este chat es informativo. No compartas datos sensibles.</div>
        </div>
      </section>
    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var form = document.getElementById('kasu-chat-form');
      var input = document.getElementById('kasu-chat-input');
      var messages = document.getElementById('kasu-chat-messages');
      if (!form || !input || !messages) return;

      form.addEventListener('submit', function (event) {
        event.preventDefault();
        var text = input.value.trim();
        if (!text) return;

        var wrapper = document.createElement('div');
        wrapper.className = 'kasu-chat-message kasu-chat-message--user';
        var bubble = document.createElement('div');
        bubble.className = 'kasu-chat-bubble';
        bubble.textContent = text;
        wrapper.appendChild(bubble);
        messages.appendChild(wrapper);
        messages.scrollTop = messages.scrollHeight;
        input.value = '';
      });
    });
  </script>
</body>
</html>
