<?php // html/footer.php ?>
<div class="col-lg-12 col-md-12 col-sm-12" itemscope itemtype="https://schema.org/Organization">
  <p class="frase"><strong>CONTÁCTANOS</strong></p>
  <br>
  <p class="frase" itemprop="description">Establecer un contacto personal es muy importante para nosotros.</p>

  <?php
    // Normaliza teléfono a dígitos para tel:/WhatsApp
    $tel_digits = isset($tel) ? preg_replace('/\D+/', '', $tel) : '';
    // Asegura prefijo país México si falta
    if ($tel_digits && strpos($tel_digits, '52') !== 0) { $tel_digits = '52' . $tel_digits; }
  ?>

  <ul class="social" role="list" aria-label="Redes sociales de KASU">
    <li><a target="_blank" rel="noopener noreferrer" href="https://www.facebook.com/KasuMexico" aria-label="Facebook KASU"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
    <li><a target="_blank" rel="noopener noreferrer" href="https://twitter.com/kasumexico" aria-label="X (Twitter) KASU"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
    <li><a target="_blank" rel="noopener noreferrer" href="https://instagram.com/kasumexico" aria-label="Instagram KASU"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
    <li><a target="_blank" rel="noopener noreferrer" href="https://www.linkedin.com/company/kasuservicios/" aria-label="LinkedIn KASU"><i class="fa fa-linkedin" aria-hidden="true"></i></a></li>
    <li><a href="mailto:atncliente@kasu.com.mx" aria-label="Enviar correo a Atención a Clientes"><i class="fa fa-envelope" aria-hidden="true"></i></a></li>
    <li><a target="_blank" rel="noopener noreferrer" href="https://wa.me/<?= htmlspecialchars($tel_digits, ENT_QUOTES, 'UTF-8') ?>" aria-label="Contactar por WhatsApp"><i class="fa fa-comments-o" aria-hidden="true"></i></a></li>
  </ul>
</div>

<br>

<div class="container">
  <div class="col-lg-12">
    <nav aria-label="Enlaces de utilidad del sitio">
      <ul class="row d-flex justify-content-center" role="list">
        <li><a class="capital" href="/testimonios" title="Testimonios de clientes">| Testimonios |</a></li>
        <li><a class="capital" target="_blank" rel="noopener" href="/login" title="Acceso al equipo KASU">| Equipo KASU |</a></li>
        <li><a class="capital" target="_blank" rel="noopener" href="https://apimarket.kasu.com.mx" title="Portal para desarrolladores">| Desarrollador |</a></li>
        <li><a class="capital" href="/terminos-y-condiciones" title="Términos y Condiciones">| Términos &amp; Condiciones |</a></li>
        <li><a class="capital" href="/privacidad" title="Política de Privacidad">| Política de Privacidad |</a></li>
      </ul>
    </nav>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <p class="copyright">
        &copy; <?= date('Y') ?> <strong itemprop="name">KASU</strong>. Todos los derechos reservados.
        El contenido, diseño y elementos multimedia de este sitio están protegidos por leyes de propiedad intelectual
        y no pueden reproducirse sin el consentimiento previo por escrito de
        <strong>KASU Servicios a Futuro, S.A. de C.V.</strong>
      </p>
    </div>
  </div>
</div>

<div class="credits">
  <!-- todos los derechos de esta pagina estan reservados por Jose Carlos Cabrera Monroy -->
  Propiedad de <a href="https://capitalyfondeo.com/" target="_blank" rel="noopener" aria-label="Capital &amp; Fondeo México">Capital &amp; Fondeo México</a>
</div>

<?php
// Cerramos las conexiones a la base de datos
mysqli_close($mysqli);
?>