<?php
/**
 * Fundación KASU — Página pública con listado de iniciativas y formulario de contacto
 * Ajustes de compatibilidad para PHP 8.2: estricto en tipos, validaciones de mysqli,
 * sanitización de salidas en atributos HTML, supresión de avisos por índices indefinidos
 * y uso de null coalescing en lecturas de arreglos.
 * 03/11/2025 – Revisado por JCCM
 */

declare(strict_types=1);

session_start();

// Rastreo Google Tag Manager
require_once __DIR__ . '/eia/analytics_bootstrap.php';

// Librerías de la app (deben inicializar $mysqli y clases utilitarias)
require_once __DIR__ . '/eia/librerias.php';

// Validar conexión mysqli en PHP 8.2 para evitar warnings si no existe
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
  http_response_code(500);
  exit('Error de conexión a base de datos.');
}

// Instanciar utilitarios
$basicas = new Basicas();

/**
 * Helpers de salida segura en atributos/HTML
 */
function e_attr(?string $v): string {
  return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function e_html(?string $v): string {
  return htmlspecialchars((string)$v, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <title>Fundación KASU | Comprometidos con el futuro</title>
  <meta name="description" content="Fundación KASU impulsa iniciativas sociales, culturales, deportivas y ecológicas que mejoran la calidad de vida en México. Conoce políticas de patrocinio e iniciativas.">
  <meta name="keywords" content="Fundación KASU, patrocinios, iniciativas sociales, donaciones, apoyo comunitario">
  <meta name="author" content="Jose Carlos Cabrera Monroy">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
  <link rel="canonical" href="https://kasu.com.mx/fundacion.php">

  <meta property="og:type" content="website">
  <meta property="og:site_name" content="KASU">
  <meta property="og:url" content="https://kasu.com.mx/fundacion.php">
  <meta property="og:title" content="Fundación KASU | Comprometidos con el futuro">
  <meta property="og:description" content="Impulsamos proyectos sociales y patrocinios que transforman comunidades en México. Conoce cómo participar.">
  <meta property="og:image" content="https://kasu.com.mx/assets/images/padres_con_hijos.jpeg">
  <meta property="og:image:alt" content="Familia beneficiaria de la Fundación KASU">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Fundación KASU | Comprometidos con el futuro">
  <meta name="twitter:description" content="Impulsamos proyectos sociales, culturales y ecológicos. Conoce nuestras políticas de patrocinio e iniciativas.">
  <meta name="twitter:image" content="https://kasu.com.mx/assets/images/padres_con_hijos.jpeg">

  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900&display=swap" rel="stylesheet">

  <link rel="icon" href="assets/images/kasu_logo.jpeg">
  <link rel="stylesheet" href="assets/css/bootstrap.min.css?v=<? echo $VerCache;?>">
  <link rel="stylesheet" href="assets/css/font-awesome.css?v=<? echo $VerCache;?>">
  <link rel="stylesheet" href="assets/css/kasu-ui.css?v=<? echo $VerCache;?>">
  <link rel="stylesheet" href="assets/css/patrocinios.css?v=<? echo $VerCache;?>">
  <!-- Bootstrap Icons CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    .mini-box{display:block;text-align:center;padding:18px;border-radius:16px;background:#fff;box-shadow:0 6px 20px rgba(0,0,0,.08)}
    .mini-box i{display:block;margin:0 auto 10px}
    /* iconos ~20% del tamaño previo (ajustable) */
    .mini-box .bi{font-size:48px;line-height:1}
    .mini .mini-content .row [class*="col-"]{margin-bottom:18px}
  </style>

  <script type="application/ld+json">
  {"@context":"https://schema.org","@type":"Organization","name":"Fundación KASU","url":"https://kasu.com.mx/fundacion.php","logo":"https://kasu.com.mx/assets/images/kasu_logo.jpeg"}
  </script>
  <script type="application/ld+json">
  {"@context":"https://schema.org","@type":"WebPage","name":"Fundación KASU","url":"https://kasu.com.mx/fundacion.php","description":"Fundación KASU impulsa iniciativas sociales, culturales, deportivas y ecológicas que mejoran la calidad de vida en México.","inLanguage":"es-MX"}
  </script>
</head>
<body>
  <?php require_once __DIR__ . '/html/MenuPrincipal.php';?>

  <div class="welcome-area" id="welcome">
    <div class="header-text">
      <div class="container">
        <div class="row">
          <div class="offset-xl-3 col-xl-6 offset-lg-2 col-lg-8 col-md-12 col-sm-12 text-center">
            <h1 style="color:#fff"><strong>Fundación KASU</strong></h1>
            <h2 style="color:#fff">Comprometidos con el <strong>FUTURO</strong></h2>
            <br><br>
          </div>
          <div class="col-lg-12 text-center"><fieldset></fieldset></div>
        </div>
      </div>
    </div>
  </div>

  <section class="section padding-top-70 padding-bottom-0" id="features" aria-labelledby="objetivo-fundacion-title">
    <div class="container">
      <div class="row">
        <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
          <img src="assets/images/padres_con_hijos.jpeg" class="rounded img-fluid d-block mx-auto"
               alt="Padres con hijos - Fundación KASU" loading="lazy" decoding="async" width="1200" height="800">
        </div>
        <div class="col-lg-1"></div>
        <div class="col-lg-6 col-md-12 col-sm-12 align-self-center mobile-top-fix">
          <div class="left-heading">
            <h2 id="objetivo-fundacion-title" class="section-title">¿Cuál es el <strong>objetivo</strong> de la Fundación?</h2>
          </div>
          <div class="left-text">
            <p style="text-align:justify">
              <strong>KASU</strong> apoya proyectos que beneficien a sectores vulnerables en México,
              destinados a <strong>mejorar la calidad de vida</strong> de quienes más lo necesitan.
            </p>
          </div>
        </div>
      </div>
      <div class="row"><div class="col-lg-12"><div class="hr"></div></div></div>
    </div>
  </section>

  <!-- Tipos de eventos con íconos Bootstrap -->
  <section class="mini" id="work-process" aria-labelledby="tipos-eventos-title">
    <div class="mini-content">
      <div class="container">
        <div class="row">
          <div class="offset-lg-3 col-lg-6 text-center">
            <h2 id="tipos-eventos-title" style="color:#fff">¿Qué tipo de eventos patrocinamos?</h2>
            <br>
            <p style="color:#fff">
              ¿Tienes una <strong>idea</strong> que puede <strong>mejorar tu comunidad</strong>? Si encaja en estas categorías,
              comparte un video en YouTube explicando la idea y cómo la aplicarás para <strong>mejorar México</strong>.
            </p>
            <br><br>
          </div>
        </div>

        <div class="row" role="list">
          <div class="col-lg-2 col-md-3 col-sm-6 col-6" role="listitem">
            <a href="#" class="mini-box" aria-label="Categoría Social">
              <i class="bi bi-people-heart" aria-hidden="true"></i>
              <strong>Social</strong>
            </a>
          </div>
          <div class="col-lg-2 col-md-3 col-sm-6 col-6" role="listitem">
            <a href="#" class="mini-box" aria-label="Categoría Cultural">
              <i class="bi bi-palette2" aria-hidden="true"></i>
              <strong>Cultural</strong>
            </a>
          </div>
          <div class="col-lg-2 col-md-3 col-sm-6 col-6" role="listitem">
            <a href="#" class="mini-box" aria-label="Categoría Identidad">
              <i class="bi bi-person-badge" aria-hidden="true"></i>
              <strong>Identidad</strong>
            </a>
          </div>
          <div class="col-lg-2 col-md-3 col-sm-6 col-6" role="listitem">
            <a href="#" class="mini-box" aria-label="Categoría Artístico">
              <i class="bi bi-brush" aria-hidden="true"></i>
              <strong>Artístico</strong>
            </a>
          </div>
          <div class="col-lg-2 col-md-3 col-sm-6 col-6" role="listitem">
            <a href="#" class="mini-box" aria-label="Categoría Deportivo">
              <i class="bi bi-trophy" aria-hidden="true"></i>
              <strong>Deportivo</strong>
            </a>
          </div>
          <div class="col-lg-2 col-md-3 col-sm-6 col-6" role="listitem">
            <a href="#" class="mini-box" aria-label="Categoría Ecológico">
              <i class="bi bi-leaf" aria-hidden="true"></i>
              <strong>Ecológico</strong>
            </a>
          </div>
        </div>

      </div>
    </div>
  </section>

  <section class="section colored" id="pricing-plans" aria-labelledby="politicas-title">
    <div class="container">
      <div class="row">
        <div class="col-lg-12"><div class="center-heading"><br><h3 id="politicas-title" class="section-title">Políticas de patrocinio</h3></div></div>
        <div class="offset-lg-1 col-lg-10">
          <div style="list-style:none;text-align:initial">
            <ul><strong>1.-</strong> Incluye:
              <li>Nombre del evento</li><li>Equipo organizador con CV</li><li>Monto</li>
              <li>Ramo</li><li>Objetivos</li><li>Alcance</li><li>Población a beneficiar</li>
            </ul>
            <ul><strong>2.-</strong> Si hubo ediciones previas, muestra resultados.</ul>
            <ul><strong>3.-</strong> No patrocinamos eventos con cobro de acceso.</ul>
            <ul><strong>4.-</strong> Enviar 2 meses antes del evento.</ul>
            <ul><strong>5.-</strong> Enfoque en población vulnerable.</ul>
            <ul><strong>6.-</strong> No eventos políticos o partidistas.</ul>
            <ul><strong>7.-</strong> Debe contar con redes sociales.</ul>
            <ul><strong>8.-</strong> Indica ubicación del logotipo.</ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="container" id="iniciativas" aria-labelledby="iniciativas-title">
    <div class="row">
      <div class="col-lg-12">
        <div class="center-heading"><h3 id="iniciativas-title" class="section-title">Iniciativas</h3></div>
      </div>
      <div class="center-text">
        <p style="text-align:justify;padding:10px">Publicamos las iniciativas que apoyamos.</p>
      </div>
    </div>
    <div class="row">
      <?php
      /**
       * BLOQUE: render de tarjetas desde tabla `patrocinios`
       * - Compatibilidad PHP 8.2: verificar resultado de consulta y tipos
       * - Sanitizar URLs, textos y atributos
       */
      $DiaBenef = 0;
      $sql = "SELECT * FROM patrocinios";
      if ($resul = $mysqli->query($sql)) {
        while ($Opn = $resul->fetch_row()) {
          // Defensivo: índices con coalescencia
          $url   = e_attr($Opn[7] ?? '#');
          $img   = e_attr($Opn[5] ?? 'assets/images/kasu_logo.jpeg');
          $tit   = e_html($Opn[1] ?? 'Iniciativa Fundación KASU');
          $sub   = e_html($Opn[2] ?? '');
          // Descripción: si tu contenido proviene de fuente confiable y contiene HTML, puedes imprimir sin escapar.
          // Aquí se aplica escape básico para seguridad en PHP 8.2.
          $desc  = e_html($Opn[4] ?? '');

          printf(
            "<div class='col-md-4 col-sm-4 col-xs-12'>
              <div class='single-blog'>
                <div class='img'>
                  <a href='%s' target='_blank' rel='noopener nofollow'>
                    <img src='%s' alt='%s' class='rounded img-fluid d-block mx-auto' loading='lazy' decoding='async'>
                  </a>
                </div>
                <div class='blog-txt'>
                  <br>
                  <h2><a href='%s' target='_blank' rel='noopener'>%s</a></h2>
                  <h3><a href='%s' target='_blank' rel='noopener'>%s</a></h3>
                  <div class='text'>%s</div>
                  <br>
                </div>
              </div>
            </div>",
            $url, $img, $tit, $url, $tit, $url, $sub, $desc
          );

          // Sumar tiempo si existe campo fecha en índice 13
          $rawDate = $Opn[13] ?? null;
          if (is_string($rawDate)) {
            $ts = strtotime($rawDate);
            if ($ts !== false) {
              $DiaBenef += $ts;
            }
          }
        }
        $resul->free();
      }
      // Mantener la lógica original
      $Benef = $DiaBenef / 604800; // 7*24*3600
      ?>
    </div>
  </div>

  <section class="counter" aria-label="Indicadores Fundación KASU">
    <div class="content">
      <div class="container">
        <div class="row text-center">
          <div class="col-lg-3 col-md-6 col-sm-12"><div class="count-item decoration-top"></div></div>
          <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="count-item decoration-bottom">
              <strong><?php echo (string)round((float)$Benef); ?></strong>
              <span>Beneficiarios</span>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="count-item decoration-top">
              <strong><?php echo (string)$basicas->MaxDat($mysqli, "Id", "patrocinios"); ?></strong>
              <span>Proyectos</span>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-sm-12"><div class="count-item decoration-top"></div></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section colored" id="Contactanos" aria-labelledby="contacto-title">
    <br><br><br>
    <div class="container">
      <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-12">
          <h3 id="contacto-title" class="margin-bottom-30">¿Tienes una iniciativa social?</h3>
          <br>
          <div class="contact-text">
            <p>Envíanos un correo con una descripción breve de tu proyecto para contactarte y considerar tu iniciativa.</p>
            <br><br>
          </div>
        </div>
        <div class="col-lg-8 col-md-6 col-sm-12">
          <div class="contact-form">
            <form id="ContactoFundacion" action="php/contacto2.php" method="post">
              <div class="row">
                <div class="col-lg-6 col-md-12 col-sm-12">
                  <fieldset>
                    <label class="sr-only" for="Nombre">Nombre</label>
                    <input name="name" type="text" class="form-control" id="Nombre" placeholder="Nombre" required>
                  </fieldset>
                </div>
                <div class="col-lg-6 col-md-12 col-sm-12">
                  <fieldset>
                    <label class="sr-only" for="email">Correo</label>
                    <input name="email" type="email" class="form-control" id="email" placeholder="Correo" required>
                  </fieldset>
                </div>
                <div class="col-lg-12">
                  <fieldset>
                    <label class="sr-only" for="Mensaje">Mensaje</label>
                    <textarea name="message" rows="6" class="form-control" id="Mensaje" placeholder="Cuéntanos tu iniciativa" required></textarea>
                  </fieldset>
                </div>
                <div class="col-lg-12">
                  <fieldset>
                    <button type="submit" id="form-submit" class="main-button" aria-label="Enviar formulario de contacto">Enviar</button>
                  </fieldset>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer>
    <?php require_once __DIR__ . '/html/footer.php';?>
  </footer>

  <script src="assets/js/jquery-2.1.0.min.js"></script>
  <script src="assets/js/popper.js"></script>
  <script src="assets/js/bootstrap.min.js"></script>
  <script src="assets/js/scrollreveal.min.js"></script>
  <script src="assets/js/waypoints.min.js"></script>
  <script src="assets/js/jquery.counterup.min.js"></script>
  <script src="assets/js/imgfix.min.js"></script>
  <script src="assets/js/custom.js"></script>
</body>
</html>