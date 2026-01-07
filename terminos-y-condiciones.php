<?php
/**
 * Qué hace: Página de Términos y Condiciones. Muestra contenido informativo y estructura SEO.
 * Fecha: 03/11/2025
 * Revisado por: JCCM
 */

// Iniciar sesión
session_start();
// Archvo de rastreo de google tag manager
require_once __DIR__ . '/eia/analytics_bootstrap.php';
// Librerías
require_once __DIR__ . '/eia/librerias.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Términos y Condiciones | KASU</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Lee los Términos y Condiciones de KASU. Transparencia, uso de servicios y responsabilidades.">
  <meta name="robots" content="index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1">
  <meta name="author" content="Erendida Itzel Castro Marquez">
  <link rel="canonical" href="https://kasu.com.mx/terminos-y-condiciones.php">
  <link rel="alternate" href="https://kasu.com.mx/terminos-y-condiciones.php" hreflang="es-MX">
  <link rel="alternate" href="https://kasu.com.mx/terminos-y-condiciones.php" hreflang="x-default">

  <!-- Open Graph / Twitter -->
  <meta property="og:type" content="website">
  <meta property="og:locale" content="es_MX">
  <meta property="og:title" content="Términos y Condiciones | KASU">
  <meta property="og:description" content="Condiciones de uso de los servicios KASU, licencias de contenido y responsabilidades.">
  <meta property="og:url" content="https://kasu.com.mx/terminos-y-condiciones.php">
  <meta property="og:site_name" content="KASU">
  <meta property="og:image" content="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <meta property="og:image:alt" content="Logotipo KASU">
  <meta name="twitter:card" content="summary_large_image">

  <!-- Fuentes e ícono -->
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" href="/assets/images/kasu_logo.jpeg">

  <!-- CSS -->
  <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css?v=<? echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/font-awesome.css?v=<? echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/kasu-menu.css?v=<? echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/index-home.css?v=<? echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/terminos.css?v=<? echo $VerCache;?>">

  <!-- Schema.org WebPage (Términos) -->
  <script type="application/ld+json">
  {
    "@context":"https://schema.org",
    "@type":"WebPage",
    "name":"Términos y Condiciones",
    "url":"https://kasu.com.mx/terminos-y-condiciones.php",
    "inLanguage":"es-MX",
    "description":"Condiciones de uso de los servicios KASU, licencias y responsabilidades."
  }
  </script>
</head>
<body class="kasu-ui">
  <!-- Modal genérico -->
  <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="modalInfoLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content" style="height:auto; padding:1em;">
        <div id="datos" role="document" aria-live="polite"></div>
      </div>
    </div>
  </div>

  <!-- Header -->
  <?php require_once __DIR__ . '/html/MenuPrincipal.php'; ?>
  <main class="terms-page">
    <section class="terms-hero">
      <div class="container">
        <p class="terms-eyebrow">Legal</p>
        <h1 class="terms-title">Términos y Condiciones</h1>
        <p class="terms-sub">Consulta las condiciones de uso, licencias de contenido y responsabilidades de KASU.</p>
      </div>
    </section>

    <section class="terms-content" id="terminos" aria-label="Términos y Condiciones de KASU">
      <div class="container">
        <div class="terms-card">
          <div class="terms-section" id="uso-servicios">
            <h2>Uso de nuestros Servicios</h2>
            <p>Debe seguir las políticas disponibles para usted dentro de los Servicios.</p>
            <p>No utilice nuestros Servicios de forma indebida. Por ejemplo, no interfiera en nuestros Servicios ni intente acceder a ellos por otro método diferente a la interfaz y las instrucciones que le proporcionamos. Puede utilizar nuestros Servicios solo como se permite por ley, incluidas las leyes y regulaciones correspondientes de control de exportación y reexportación. Podremos suspender o dejar de proveerle nuestros Servicios si usted incumple nuestras condiciones o políticas o si estamos investigando una presunta conducta indebida.</p>
            <p>El uso de nuestros Servicios no otorga derecho de propiedad intelectual alguno sobre nuestros Servicios o contenido al que acceda. No podrá utilizar el contenido de nuestros Servicios a menos que obtenga el permiso de su propietario o que ello esté permitido por ley. Estas condiciones no le otorgan el derecho de utilizar marca o logotipo alguno utilizado en nuestros Servicios. No elimine, oculte ni modifique ningún aviso legal mostrado en nuestros Servicios o junto a ellos.</p>
            <p>Nuestros Servicios muestran cierto contenido que no pertenece a KASU. Dicho contenido es responsabilidad exclusiva de la entidad que lo pone a disposición. Podremos revisar contenido para determinar si es ilegal o si infringe nuestras políticas, y podremos eliminar o rechazar la visualización de contenido que razonablemente consideremos que infringe nuestras políticas o la ley. Sin embargo, esto no significa que revisemos contenido, por lo que no debe suponer que lo haremos.</p>
            <p>En relación con su uso de los Servicios, podremos enviarle anuncios del servicio, mensajes administrativos y otra información. Usted podrá rechazar algunas de dichas comunicaciones.</p>
            <p>Algunos de nuestros Servicios están disponibles en dispositivos móviles. No utilice estos Servicios de un modo que pueda distraerlo y que le impida cumplir con las leyes de tránsito o seguridad.</p>
          </div>

          <div class="terms-section" id="su-contenido">
            <h2>Su contenido en nuestros Servicios</h2>
            <p>Algunos de nuestros Servicios le permiten subir, almacenar, enviar o recibir contenido. Usted conservará los derechos de propiedad intelectual que posea sobre dicho contenido. En resumen, lo que le pertenece a usted, continúa siendo suyo.</p>
            <p>Cuando suba, ingrese, almacene, envíe o reciba contenido a nuestros Servicios o a través de ellos, otorgará a KASU (y a aquellos con quienes trabajamos) una licencia internacional para utilizar, alojar, almacenar, reproducir, modificar, crear obras derivadas (como las traducciones, adaptaciones o modificaciones que hacemos para que su contenido funcione mejor con nuestros Servicios), comunicar, publicar, ejecutar públicamente y distribuir dicho contenido. Los derechos que usted otorga en esta licencia son para el objetivo limitado de operar, promocionar y mejorar nuestros Servicios, y para desarrollar otros nuevos. Esta licencia subsistirá aún cuando usted deje de utilizar nuestros Servicios. Algunos Servicios pueden ofrecerle distintas maneras de acceder y eliminar contenido que se haya proporcionado para ese Servicio. Además, en algunos de nuestros Servicios, hay condiciones o parámetros de configuración que limitan el alcance de nuestro uso del contenido provisto en aquellos Servicios. Asegúrese de tener los derechos necesarios para otorgarnos esta licencia para cualquier contenido que envíe a nuestros Servicios.</p>
            <p>Nuestros sistemas automatizados analizan el contenido (incluidos los correos electrónicos) para proporcionarle funciones de productos que sean relevantes para usted, como la publicación de anuncios y resultados de búsqueda personalizados y la detección de spam y software malicioso. Este análisis se realiza mientras el contenido se envía, recibe y cuando se almacena.</p>
            <p>Si tiene una cuenta de KASU, es posible que mostremos su nombre de Perfil, su foto de Perfil y las acciones que realiza en KASU o en aplicaciones de terceros conectadas a su cuenta de KASU en nuestros Servicios, incluida la aparición en anuncios y otros contextos comerciales. Respetaremos las decisiones que tome para limitar la configuración del uso compartido o del nivel de visibilidad en su cuenta de KASU. Por ejemplo, puede configurar su cuenta de manera que su nombre y su foto no aparezcan en un anuncio.</p>
            <p>Usted puede encontrar más información sobre el modo en que KASU utiliza y almacena contenido en la política de privacidad o en las condiciones adicionales de Servicios específicos. Si usted envía comentarios o sugerencias sobre nuestros Servicios, podremos utilizarlos sin ninguna obligación hacia usted.</p>
          </div>

          <div class="terms-section" id="responsabilidad">
            <h2>Responsabilidad por nuestros Servicios</h2>
            <p>Si la ley lo permite, KASU y sus proveedores y distribuidores no serán responsables por lucro cesante, pérdida de ganancias, de datos o financieras, ni por daños indirectos, especiales, emergentes, ejemplares o punitorios.</p>
            <p>En la medida permitida por ley, la responsabilidad total de KASU y de sus proveedores y distribuidores por cualquier reclamo en virtud de las presentes condiciones, incluida cualquier garantía implícita, estará limitada al monto abonado por usted para utilizar los Servicios (o a proveerle los Servicios nuevamente, si así lo elegimos).</p>
            <p>En ningún caso KASU y sus proveedores y distribuidores serán responsables por pérdidas o daños que no sean razonablemente previsibles.</p>
            <p>KASU reconoce que en algunos países usted podría tener derechos reconocidos por ley que le correspondan como consumidor. Si usted utiliza los Servicios para fines personales, ninguna de las disposiciones incluidas en las presentes condiciones o en condiciones adicionales limitará ningún derecho legal como consumidor al que no pueda renunciarse contractualmente.</p>
          </div>

          <div class="terms-section" id="acerca-de-estas-condiciones">
            <h2>Acerca de estas Condiciones</h2>
            <p>Podremos modificar las presentes condiciones o las condiciones adicionales aplicables a un Servicio para, por ejemplo, reflejar cambios en las leyes o en nuestros Servicios. Usted debe revisar las condiciones periódicamente. Publicaremos avisos sobre las modificaciones a estos términos en esta página. Publicaremos avisos sobre las condiciones adicionales modificadas, en el Servicio correspondiente. Las modificaciones no se aplicarán retroactivamente y entrarán en vigencia no antes de catorce días después de su publicación. Sin embargo, las modificaciones que reflejan nuevas funciones de un Servicio o las modificaciones realizadas por razones legales entrarán en vigencia de forma inmediata. Si no acepta las condiciones modificadas en un Servicio, debería cancelar el uso de dicho Servicio.</p>
            <p>En caso de haber un conflicto entre estas condiciones y las condiciones adicionales, las condiciones adicionales prevalecerán en todo lo relativo a ese conflicto.</p>
            <p>Estas condiciones gobiernan la relación entre KASU y usted, y no generan ningún tipo de derechos a favor de terceros.</p>
            <p>Si usted no cumple estas condiciones, y nosotros no tomamos acción inmediata, ello no implica renuncia alguna a cualquier derecho que pudiera correspondernos (como iniciar una acción en el futuro).</p>
            <p>Si cualquier disposición de estas condiciones resultare inejecutable, ello no afectará la validez del resto de las condiciones.</p>
            <p>Los tribunales de algunos países no aplicarán las leyes de México a cierto tipo de controversias. Si usted reside en alguno de esos países, las leyes de su país serán aplicables para dichas controversias en los casos en que las leyes de México queden excluidas en relación con las presentes condiciones. De lo contrario, usted acepta que las leyes de México, excluyendo los conflictos de leyes de México, se aplicarán a cualquier controversia que surja o se relacione con las presentes condiciones o los Servicios. Asimismo, si los tribunales de su país no permiten que usted se someta a la competencia y jurisdicción de los tribunales de Atlacomulco, Estado de México, entonces se aplicará la competencia y jurisdicción locales a dichas controversias relacionadas con las presentes condiciones.</p>
            <p>De lo contrario, todos los reclamos que surjan o se relacionen con las presentes condiciones o los Servicios se deberán presentar exclusivamente en los tribunales federales o estatales de Atlacomulco, Estado de México, y usted y KASU aceptan someterse a la jurisdicción personal de dichos tribunales.</p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="site-footer">
    <?php require_once __DIR__ . '/html/footer.php'; ?>
  </footer>

  <!-- JS al final -->
  <script src="/assets/js/jquery-2.1.0.min.js"></script>
  <script src="/assets/js/popper.js" defer></script>
  <script src="/assets/js/bootstrap.min.js" defer></script>
  <script src="/assets/js/scrollreveal.min.js" defer></script>
  <script src="/assets/js/waypoints.min.js" defer></script>
  <script src="/assets/js/jquery.counterup.min.js" defer></script>
  <script src="/assets/js/imgfix.min.js" defer></script>
  <script src="/assets/js/custom.js" defer></script>
  <script async src="https://d335luupugsy2.cloudfront.net/js/loader-scripts/28dd2782-ee7d-4b25-82b1-f5993b27764a-loader.js"></script>
</body>
</html>
