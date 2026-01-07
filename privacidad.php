<?php
/**
 * Qué hace: Página de Términos y Condiciones. Contiene aviso de privacidad y condiciones de uso.
 * Fecha: 03/11/2025
 * Revisado por: JCCM
 */

//indicar que se inicia una sesion *JCCM
session_start();
// Archvo de rastreo de google tag manager
require_once __DIR__ . '/eia/analytics_bootstrap.php';
//Requerimos el archivo de librerias *JCCM
require_once __DIR__ . '/eia/librerias.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Aviso de Privacidad | KASU</title>
  <meta name="description" content="Consulta el aviso de privacidad y uso de datos de KASU Servicios a Futuro. Transparencia y cumplimiento legal en México.">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="robots" content="index,follow,max-image-preview:large">
  <meta name="author" content="KASU Servicios a Futuro">
  <meta name="keywords" content="KASU, aviso de privacidad, protección de datos, políticas">
  <meta name="theme-color" content="#F1F1FC">

  <!-- Canonical -->
  <link rel="canonical" href="https://kasu.com.mx/privacidad.php">

  <!-- Open Graph -->
  <?php
    // URL absoluta segura
    $reqUri = $_SERVER['REQUEST_URI'] ?? '/privacidad.php';
    $absUrl = 'https://kasu.com.mx' . strtok($reqUri, ' ');
  ?>
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="KASU">
  <meta property="og:url" content="<?php echo htmlspecialchars($absUrl, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:title" content="Aviso de Privacidad | KASU">
  <meta property="og:description" content="Transparencia y cumplimiento: conoce nuestro aviso de privacidad y uso de datos">
  <meta property="og:image" content="https://kasu.com.mx/assets/images/kasu_og_default.jpg">
  <meta property="og:image:alt" content="KASU">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Aviso de Privacidad | KASU">
  <meta name="twitter:description" content="Transparencia y cumplimiento: conoce nuestro aviso de privacidad y uso de datos">
  <meta name="twitter:image" content="https://kasu.com.mx/assets/images/kasu_og_default.jpg">

  <!-- JSON-LD WebPage -->
  <script type="application/ld+json">
  {
    "@context":"https://schema.org",
    "@type":"WebPage",
    "url":"<?php echo htmlspecialchars($absUrl, ENT_QUOTES, 'UTF-8'); ?>",
    "name":"Aviso de Privacidad | KASU",
    "description":"Consulta el aviso de privacidad y uso de datos de KASU Servicios a Futuro.",
    "isPartOf":{"@type":"WebSite","name":"KASU","url":"https://kasu.com.mx"},
    "inLanguage":"es-MX"
  }
  </script>

  <!-- Fuentes + Favicon -->
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" href="/assets/images/kasu_logo.jpeg">

  <!-- CSS existentes -->
  <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css?v=<?php echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/font-awesome.css?v=<?php echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/kasu-menu.css?v=<?php echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/index-home.css?v=<?php echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/privacidad.css?v=<?php echo $VerCache;?>">
</head>

<body class="kasu-ui">
    <!-- La venta emergente debe de estar fuera del div que lo lanza *JCCM -->
    <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content" style="height:auto; padding:1em;">
                <div id="datos"></div>
            </div>
        </div>
    </div>

    <!-- ***** Header Area Start ***** -->
    <?php require_once __DIR__ . '/html/MenuPrincipal.php'; ?>
    <main class="privacy-page">
      <section class="privacy-hero">
        <div class="container">
          <p class="privacy-eyebrow">Legal</p>
          <h1 class="privacy-title">Aviso de Privacidad</h1>
          <p class="privacy-sub">Conoce como protegemos tus datos y las condiciones de uso de nuestras plataformas digitales.</p>
        </div>
      </section>

      <section class="privacy-content">
        <div class="container">
          <div class="privacy-card">
            <div class="privacy-section">
              <h2>Política de privacidad y uso de datos</h2>
              <p>En términos de lo previsto en la Ley Federal de Protección de Datos Personales en Posesión de los Particulares (en lo sucesivo denominada como “la Ley”), ‘‘KASU, SERVICIOS A FUTURO’’, establece el presente Aviso de Privacidad de conformidad con lo siguiente:</p>
              <p>El presente Aviso de Privacidad tiene por objeto la protección de los datos personales de los CLIENTES, mediante su tratamiento legítimo, controlado e informado, a efecto de garantizar su privacidad, así como tu derecho a la autodeterminación informativa.</p>
              <p>Dato Personal es Cualquier información concerniente a una persona física identificada o identificable; el responsable de recabar los datos personales es el área de atención preuniversitaria, posgrado y extensión (procesos de promoción e inscripción de alumnos); el área de administración escolar (una vez que los alumnos se hayan inscrito formalmente); y el área de recursos humanos (para personal directivo, docente y administrativo).</p>
              <p>El domicilio de ‘‘KASU, SERVICIOS A FUTURO’’ y del área responsable, es el mismo que tiene registrada ‘‘KASU, SERVICIOS A FUTURO’’ ante la Secretaría de Hacienda y crédito público.</p>
              <p>Al proporcionar tus Datos Personales por escrito, a través de una solicitud, formato en papel, formato digital, correo electrónico, o cualquier otro documento, aceptas y autorizas a ‘‘KASU, SERVICIOS A FUTURO’’ a utilizar y tratar de forma automatizada tus datos personales e información suministrados, los cuales formarán parte de nuestra base de datos con la finalidad de usarlos, en forma enunciativa, más no limitativa, para: identificarte, ubicarte, comunicarte, contactarte, enviarte información y/o bienes, así como para enviarlos y/o transferirlos a terceros, dentro y fuera del territorio nacional, por cualquier medio que permita la ley para cumplir con nuestros fines sociales.</p>
              <p>Mediante la aceptación y autorización para el tratamiento de tus datos personales en los términos antes señalados, nos facultas expresamente a transferirlos a autoridades de cualquier nivel (Federales, Estatales, Municipales), organismos públicos y privados, diversas empresas y/o personas físicas, dentro y fuera de México y nos autorizas a poder emitir y entregar documentación oficial o no, a tus familiares y/o representantes legales.</p>
              <p>La temporalidad del manejo de tus Datos Personales será indefinida a partir de la fecha en que nos los proporciones, pudiendo oponerte al manejo de los mismos en cualquier momento que lo consideres oportuno, con las limitaciones de Ley; en caso de que tu solicitud de oposición sea procedente, ‘‘KASU, SERVICIOS A FUTURO’’ dejará de manejar tus Datos Personales sin ninguna responsabilidad de nuestra parte.</p>
              <p>El área de ‘‘KASU, SERVICIOS A FUTURO’’ responsable del tratamiento de tus datos personales, está obligada a cumplir con los principios de licitud, consentimiento, información, calidad, finalidad, lealtad, proporcionalidad y responsabilidad tutelados en la Ley; por tal motivo con fundamento en los artículos 13 y 14 de la Ley, así como a mantener las medidas de seguridad administrativas, técnicas y físicas que permitan protegerlos contra cualquier daño, pérdida, alteración, acceso o tratamiento no autorizado.</p>
              <p>En términos de lo establecido por el artículo 22 de la Ley, tienes derecho en cualquier momento a ejercer tus derechos de acceso, rectificación, cancelación y oposición (derechos ARCO) al tratamiento de tus datos personales a partir del 6 de enero del 2012, mediante la solicitud vía correo electrónico dirigido a ‘‘CAPITAL&amp;FONDEO MÉXICO S.A DE C.V SOFOM ENTIDAD NO REGULADA’’ atencionalcliente@kasu.com.mx o al teléfono 01 800 890 90 42; estableciendo como asunto del correo "Derechos ARCO:</p>
            </div>

            <div class="privacy-section">
              <h2>Términos y condiciones de plataformas digitales</h2>
              <p>Gracias por utilizar nuestros productos y servicios (“Servicios”). Los Servicios son proporcionados por Capital&amp;Fondeo Mexico(“KASU”), ubicado en Privada Vire #2 int 1, Col Centro, Atlacomulco Mexico, Mexico.</p>
              <p>Mediante la utilización de nuestros Servicios usted está aceptando estas condiciones. Por favor, léalas detenidamente.</p>
              <p>Nuestros Servicios son muy diversos, de modo que en ocasiones podrían ser aplicables condiciones adicionales u otros requisitos (incluidos los requisitos de edad). Las condiciones adicionales estarán disponibles junto con los Servicios pertinentes y formarán parte de su contrato con nosotros al utilizar tales Servicios.</p>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- ***** Footer Start ***** -->
    <footer class="site-footer">
      <?php require_once __DIR__ . '/html/footer.php'; ?>
    </footer>

    <!-- jQuery -->
    <script src="/assets/js/jquery-2.1.0.min.js"></script>
    <!-- Bootstrap -->
    <script src="/assets/js/popper.js" defer></script>
    <script src="/assets/js/bootstrap.min.js" defer></script>
    <!-- Plugins -->
    <script src="/assets/js/scrollreveal.min.js" defer></script>
    <script src="/assets/js/waypoints.min.js" defer></script>
    <script src="/assets/js/jquery.counterup.min.js" defer></script>
    <script src="/assets/js/imgfix.min.js" defer></script>
    <!-- Global Init -->
    <script src="/assets/js/custom.js" defer></script>
    <script type="text/javascript" async src="https://d335luupugsy2.cloudfront.net/js/loader-scripts/28dd2782-ee7d-4b25-82b1-f5993b27764a-loader.js"></script>
</body>
</html>
