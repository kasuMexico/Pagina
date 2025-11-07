<?php
/********************************************************************************************
 * Formulario de acceso a KASU API y guarda en ContacIndex.
 * Fecha: 04/11/2025  ·  PHP 8.2
 * Revisado JCCM
 ********************************************************************************************/
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['csrf_auth'])) { $_SESSION['csrf_auth'] = bin2hex(random_bytes(32)); }
?>

<section class="section colored" id="contact-us">
  <div class="container">
    <div class="row">
      <!-- Texto lateral -->
      <div class="col-lg-4 col-md-6">
        <h2 class="mb-3">Acceso a KASU API</h2>
        <p class="mb-2">Solicita acceso a KASU API y describe tu caso de uso. Indícanos la empresa y un contacto, qué integrarás, las APIs que necesitas y el volumen esperado: sitio web, usuarios estimados y RPS máximo. Con esa información evaluamos tu solicitud y te habilitamos sandbox.</p>
      </div>

      <!-- Formulario -->
      <div class="col-lg-8 col-md-6">
        <form id="ContactoApiMarket" action="contacto.php" method="post" accept-charset="UTF-8" autocomplete="on">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_auth'], ENT_QUOTES, 'UTF-8') ?>">

          <!-- Honeypot -->
          <div style="position:absolute;left:-9999px" aria-hidden="true">
            <label for="company">No completar</label>
            <input type="text" id="company" name="company" tabindex="-1" autocomplete="off">
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <input type="text" name="name" id="name" class="form-control" placeholder="Nombre completo" required maxlength="150" autocomplete="name">
            </div>
            <div class="col-md-6 mb-3">
              <input type="email" name="email" id="email" class="form-control" placeholder="Correo electrónico" required maxlength="190" autocomplete="email">
            </div>

            <div class="col-md-12 mb-3">
              <input type="url" name="website" id="website" class="form-control" placeholder="Sitio web (https://…)" maxlength="190" inputmode="url" autocomplete="url">
            </div>

            <div class="col-md-6 mb-3">
              <input type="number" name="users_est" id="users_est" class="form-control" placeholder="Usuarios estimados" min="1" step="1" inputmode="numeric">
            </div>
            <div class="col-md-6 mb-3">
              <input type="number" name="rps_max" id="rps_max" class="form-control" placeholder="RPS máx." min="1" step="1" inputmode="numeric">
            </div>

            <div class="col-12 mb-3">
              <textarea name="message" id="message" rows="5" class="form-control" placeholder="Ejemplo: Integraré ventas y webhooks para notificaciones. Volumen: ~500 req/día." required maxlength="3000"></textarea>
            </div>
            <div class="col-12">
              <button type="submit" id="Enviar" class="main-button">Enviar</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<script>
// Trim, honeypot y bloqueo de doble envío
(function () {
  var form = document.getElementById('ContactoApiMarket');
  if (!form) return;
  var btn = document.getElementById('Enviar');

  form.addEventListener('submit', function (e) {
    var hp = document.getElementById('company');
    if (hp && hp.value.trim() !== '') { e.preventDefault(); return false; }

    ['name','email','website','message'].forEach(function(id){
      var el = document.getElementById(id);
      if (el && el.value) el.value = el.value.trim();
    });

    // Normaliza números vacíos a 0
    ['users_est','rps_max'].forEach(function(id){
      var el = document.getElementById(id);
      if (el && el.value === '') el.value = '0';
    });

    if (btn) { btn.disabled = true; btn.textContent = 'Enviando'; }
  });
})();
</script>
