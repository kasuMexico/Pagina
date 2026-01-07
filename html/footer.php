<?php 
/**
 * Archivo: html/footer.php 
 * es el que muestra el footer de la pagina
 */
?>
<?php
  // Normaliza teléfono a dígitos para tel:/WhatsApp
  $tel_digits = isset($tel) ? preg_replace('/\D+/', '', $tel) : '';
  // Asegura prefijo país México si falta
  if ($tel_digits && strpos($tel_digits, '52') !== 0) { $tel_digits = '52' . $tel_digits; }
?>

<div class="kasu-footer">
  <div class="container">
    <div class="footer-grid" itemscope itemtype="https://schema.org/Organization">
      <div class="footer-col footer-brand">
        <img src="/assets/images/logo-kasu.png" alt="KASU" class="footer-logo" loading="lazy" decoding="async" itemprop="logo">
        <p class="footer-title" itemprop="description">Contactanos</p>
        <p class="footer-text">Establecer un contacto personal es muy importante para nosotros.</p>
        <ul class="footer-social" role="list" aria-label="Redes sociales de KASU">
          <li><a target="_blank" rel="noopener noreferrer" href="https://www.facebook.com/KasuMexico" aria-label="Facebook KASU"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
          <li><a target="_blank" rel="noopener noreferrer" href="https://twitter.com/kasumexico" aria-label="X (Twitter) KASU"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
          <li><a target="_blank" rel="noopener noreferrer" href="https://instagram.com/kasumexico" aria-label="Instagram KASU"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
          <li><a target="_blank" rel="noopener noreferrer" href="https://www.linkedin.com/company/kasuservicios/" aria-label="LinkedIn KASU"><i class="fa fa-linkedin" aria-hidden="true"></i></a></li>
          <li><a href="mailto:atncliente@kasu.com.mx" aria-label="Enviar correo a Atencion a Clientes"><i class="fa fa-envelope" aria-hidden="true"></i></a></li>
          <li><a target="_blank" rel="noopener noreferrer" href="https://wa.me/<?= htmlspecialchars($tel_digits, ENT_QUOTES, 'UTF-8') ?>" aria-label="Contactar por WhatsApp"><i class="fa fa-comments-o" aria-hidden="true"></i></a></li>
        </ul>
      </div>

      <div class="footer-col">
        <p class="footer-heading">KASU</p>
        <ul class="footer-links" role="list">
          <li><a href="/testimonios" title="Testimonios de clientes">Testimonios</a></li>
          <li><a target="_blank" rel="noopener" href="/login" title="Acceso al equipo KASU">Equipo KASU</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <p class="footer-heading">Recursos</p>
        <ul class="footer-links" role="list">
          <li><a target="_blank" rel="noopener" href="https://apimarket.kasu.com.mx" title="Portal para desarrolladores">Desarrollador</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <p class="footer-heading">Legal</p>
        <ul class="footer-links" role="list">
          <li><a href="/terminos-y-condiciones" title="Terminos y Condiciones">Terminos &amp; Condiciones</a></li>
          <li><a href="/privacidad" title="Politica de Privacidad">Politica de Privacidad</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <p class="footer-legal">
        &copy; <?= date('Y') ?> <strong itemprop="name">KASU</strong>. Todos los derechos reservados.
        El contenido, diseno y elementos multimedia de este sitio estan protegidos por leyes de propiedad intelectual
        y no pueden reproducirse sin el consentimiento previo por escrito de
        <strong>KASU Servicios a Futuro, S.A. de C.V.</strong>
      </p>
      <div class="credits">
        <!-- todos los derechos de esta pagina estan reservados por Jose Carlos Cabrera Monroy -->
        Propiedad de <a href="https://capitalyfondeo.com/" target="_blank" rel="noopener" aria-label="Capital &amp; Fondeo Mexico">Capital &amp; Fondeo Mexico</a>
      </div>
    </div>
  </div>
</div>

<?php
// Cerramos las conexiones a la base de datos
mysqli_close($mysqli);
?>
