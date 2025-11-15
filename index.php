<?php
/*******************************************************************************************************
 * Qué hace: Página principal. Muestra landing, cotizador por CURP y gestiona baja de newsletter por GET.
 * Fecha: 15/11/2025
 * Revisado por: JCCM
 *******************************************************************************************************/

session_start();

// Archvo de rastreo de google tag manager
require_once __DIR__ . '/eia/analytics_bootstrap.php';
// Requerir el archivo de librerías
require_once __DIR__ . '/eia/librerias.php';
// Se establecen el número de contacto
require_once __DIR__ . '/eia/php/Telcto.php';

/* ===== Mensajes y bajas de newsletter ===== */
$qsMsg = $_GET['Msg'] ?? null;
if ($qsMsg !== null) {
    $safeMsgJs  = json_encode($qsMsg, JSON_UNESCAPED_UNICODE);
    $safeMsgOut = htmlspecialchars($qsMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo "<script type='text/javascript'>alert($safeMsgJs);</script>";
} elseif ((int)($_GET['Ml'] ?? 0) === 4) {
    echo "Parámetro Ml es igual a 4.<br>";

    $id = (int)($_GET['Id'] ?? 0);

    if (empty($_GET['dat'])) {
        echo "No se recibió el parámetro 'dat'. Actualizando tabla 'Contacto'.<br>";
        $result = $basicas->ActCampo($mysqli, "Contacto", "Cancelacion", 1, $id);
        echo "Resultado de ActCampo en Contacto: " . var_export($result, true) . "<br>";
    } else {
        echo "Se recibió el parámetro 'dat'. Actualizando tabla 'prospectos'.<br>";
        if (isset($pros) && $pros instanceof mysqli) {
            $result = $basicas->ActCampo($pros, "prospectos", "Cancelacion", 1, $id);
            echo "Resultado de ActCampo en prospectos: " . var_export($result, true) . "<br>";
        } else {
            echo "Error: conexión \$pros no disponible.<br>";
        }
    }

    echo "<script type='text/javascript'>alert('Se ha dado de baja tu email de nuestro News Letter');</script>";
    echo "Alerta de baja enviada.<br>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <!-- SEO básico -->
    <title>Gastos funerarios a futuro | KASU</title>
    <meta name="description" content="KASU ofrece servicios funerarios a futuro en México: pago único, sin renovaciones, cobertura nacional y atención inmediata. Cotiza con tu CURP.">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="https://www.kasu.com.mx/index.php">
    <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
    <meta name="author" content="Jose Carlos Cabrera Monroy">

    <!-- Hreflang -->
    <link rel="alternate" href="https://www.kasu.com.mx/index.php" hreflang="es-MX">
    <link rel="alternate" href="https://www.kasu.com.mx/index.php" hreflang="x-default">

    <!-- Social (Open Graph / Twitter) -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_MX">
    <meta property="og:site_name" content="KASU">
    <meta property="og:title" content="Gastos funerarios a futuro | KASU">
    <meta property="og:description" content="Pago único de por vida, sin cargos ocultos. Cobertura en toda la República Mexicana. Cotiza con tu CURP.">
    <meta property="og:url" content="https://www.kasu.com.mx/index.php">
    <meta property="og:image" content="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
    <meta property="og:image:alt" content="Logotipo KASU">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Gastos funerarios a futuro | KASU">
    <meta name="twitter:description" content="Servicios funerarios a futuro con un pago unico. Atencion inmediata 24/7.">
    <meta name="twitter:image" content="https://kasu.com.mx/assets/images/kasu_logo.jpeg">

    <!-- PWA/Branding menor -->
    <meta name="theme-color" content="#7b1fa2">

    <!-- Conexiones rápidas -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="//kasu.com.mx">

    <!-- Iconos -->
    <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
    <link rel="apple-touch-icon" sizes="180x180" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/font-awesome.css">
    <link rel="stylesheet" href="/assets/css/templatemo-softy-pinko.css?v=3">
    <link rel="stylesheet" href="/assets/css/EstilosIndex.css?v=9">

    <!-- JS propio -->
    <script src="/eia/javascript/Registro.js" defer></script>

    <!-- JSON-LD -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Organization",
          "name": "KASU",
          "url": "https://www.kasu.com.mx/",
          "logo": "https://kasu.com.mx/assets/images/kasu_logo.jpeg",
          "brand": "KASU",
          "sameAs": [
            "https://www.facebook.com/KasuMexico",
            "https://x.com/kasumexico",
            "https://www.instagram.com/kasumexico",
            "https://www.linkedin.com/company/kasuservicios/"
          ],
          "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "<?php echo isset($tel) ? preg_replace('/\\D/','',$tel) : ''; ?>",
            "contactType": "customer support",
            "areaServed": "MX",
            "availableLanguage": ["es"]
          }
        },
        {
          "@type": "WebSite",
          "name": "KASU",
          "url": "https://www.kasu.com.mx/",
          "inLanguage": "es-MX",
          "potentialAction": {
            "@type": "SearchAction",
            "target": "https://www.kasu.com.mx/index.php?q={search_term_string}",
            "query-input": "required name=search_term_string"
          }
        }
      ]
    }
    </script>
</head>

<body>
    <?php require_once __DIR__ . '/html/MenuPrincipal.php'; ?>
    
    <!-- Portada -->
    <div class="main-banner wow fadeIn" id="top"
        data-wow-duration="1s" data-wow-delay="0.5s">

        <!-- Slider de fondo -->
        <div class="banner-bg-slider">
            <!-- Agrega aquí UNA fila por imagen que tengas en assets/images/Sliders -->
            <div class="banner-bg" data-bg="/assets/images/Sliders/Protege_1.png"></div>
            <div class="banner-bg" data-bg="/assets/images/Sliders/Protege_2.png"></div>
            <div class="banner-bg" data-bg="/assets/images/Sliders/Protege_3.png"></div>
            <!-- Duplica/ajusta estas líneas con los nombres reales de tus archivos -->
        </div>

        <!-- Contenido encima del fondo -->
        <div class="main-banner-content">
            <h1 style="color: hsla(0, 0%, 100%, 0.00);">
                KASU | Servicios funerarios a futuro en México
            </h1>
            <div class="container">
                <div class="row" itemscope itemtype="https://schema.org/Service">
                    <meta itemprop="serviceType" content="Servicios funerarios a futuro">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-6 align-self-center"
                                data-scroll-reveal="enter left move 50px over 0.6s after 0.4s">
                                <div class="left-content header-text wow fadeInLeft"
                                    data-wow-duration="1s" data-wow-delay="1s">
                                    <h6>
                                    <img src="/assets/images/flor_redonda.svg"
                                        style="width: 10vh;"
                                        alt="Flor KASU"
                                        loading="lazy" decoding="async">
                                    </h6>
                                    <h2>Servicios <em>de Gastos <span>Funerarios</span> y mucho</em> más</h2>
                                    <p>La Visión de <strong>KASU</strong> es lograr una cobertura universal para las familias mexicanas en lo que se refiere a servicios funerarios.</p>
                                    <div class="form" role="search" aria-label="Cotizador por CURP">
                                        <input id="curp" type="text" class="text"
                                            placeholder="Ingresa tu CURP"
                                            autocomplete="on" inputmode="latin"
                                            aria-label="Ingresa tu CURP" required>
                                        <button type="button" id="form-submit" class="main-button"
                                                onclick="consultaModal()"
                                                aria-label="Consultar CURP">CONSULTAR</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">

                            </div>
                        </div><!-- .row -->
                    </div><!-- .col-lg-12 -->
                </div><!-- .row schema -->
            </div><!-- .container -->
        </div><!-- .main-banner-content -->
    </div><!-- .main-banner -->

    <!-- Productos -->
    <section class="section colored padding-top-70"> 
        <div class="container" itemscope itemtype="https://schema.org/CollectionPage">
            <!-- Productos -->
            <?php require_once __DIR__ . '/html/Section_Productos.php'; ?>
        </div>
    </section>

    <!-- Llamada -->
    <div class="section colored">
        <div class="LlamadaKASU">
            <div class="row">
                <div class="col-md-4 col-md-12 col-sm-12 align-self-center">
                    <h2>LÍNEA DE ATENCIÓN INMEDIATA</h2>
                    <br>
                    <a href="tel:<?php echo isset($tel) ? htmlspecialchars($tel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : ''; ?>" class="btn btn-dark btn-lg" style="margin-bottom: 10px;"   aria-label="Llamar a emergencia funeraria KASU">
                        📞 EMERGENCIA FUNERARIA
                    </a>
                    <br>
                    <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $tel ?? ''); ?>?text=Hola,%20requiero%20atención%20inmediata%20de%20KASU"  class="btn btn-success btn-lg" target="_blank" rel="noopener" aria-label="Abrir WhatsApp de atención inmediata KASU">
                        💬 WhatsApp Inmediato
                    </a>
                </div>
            </div>
        </div>
    </body>

    <!-- Clientes -->
    <section class="section colored padding-top-70" id="Datos">
        <div class="container">
            <div class="row">
                <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
                    <div class="center-heading">
                        <p><strong>La Visión</strong> de <strong>KASU</strong> es lograr una cobertura universal para las familias mexicanas en lo que se refiere a servicios funerarios.</p>
                        <br>
                        <div class="count-item decoration-bottom" itemprop="provider">
                            <h2 class="section-title">
                                <strong>1<?php echo number_format($basicas->MaxDat($mysqli, "Id", "Venta"), 0, ".", ","); ?></strong><span> Clientes Activos</span>
                            </h2>
                        </div>
                        <p><strong>KASU</strong> es una empresa que ofrece servicios funerarios a bajo costo en México, los cuales <strong>se pagan una sola vez en la vida</strong> y no requieren renovación o pagos adicionales, una <strong>característica única</strong> frente a alternativas del mercado.</p>
                        <br>
                        <p>
                            Este enfoque en ayudar a las personas es el factor más importante a comunicar,
                            destacando la importancia de apoyar a las comunidades locales y brindar una solución eficaz a un problema común.
                            Además, el hecho de que <strong>KASU</strong> se haya concretado en un <strong>fideicomiso</strong> permite brindar un <strong>servicio funerario digno</strong> en el momento que más lo necesitas.
                        </p>
                        <br><br>
                    </div>
                </div>
                <div class="col-lg-2 col-md-12 col-sm-12 align-self-center"></div>
                <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 300px over 0.6s after 1.0s" id="Clientes">
                    <div class="features-small-item">
                        <div class="descri">
                            <div class="icon">
                                <i><img src="/assets/images/Index/florkasu.png" name="Logo" alt="KASU Logo" loading="lazy" decoding="async"></i>
                            </div>
                        </div>
                        <h2 class="features-title"><strong>Cotiza</strong></h2>
                        <p>Cotiza tu servicio, solo requieres tu clave CURP.</p>
                        <div class="consulta">
                            <div class="form-group">
                                <form method="POST" id="Cotizar" action="/login/php/Registro_Prospectos.php">
                                    <div data-fingerprint-slot></div> <!-- DIV que lanza el Finger Print -->
                                    <input name="CURP" id="CURP" class="form-control" placeholder="Ingresar CURP" autocomplete="on" aria-label="Ingresar CURP">
                                    <br>
                                    <input type="email" name="Email" id="Email" class="form-control" placeholder="Correo electrónico" autocomplete="email" aria-label="Ingresar correo electrónico">
                                    <br>
                                    <button type="submit" name="FormCotizar" id="FormCotizar" class="main-button" data-toggle="modal" aria-label="Cotizar servicio funerario">Cotizar Servicio</button>
                                    <br>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Beneficios
    <section class="mini" id="Beneficios">
        <div class="mini-content">
            <div class="container">
                <div class="col-lg-12">
                    <div class="center-heading">
                        <h2 class="section-title" style="color: #F9EBF9;">Los principales beneficios de contratar con <strong>KASU</strong></h2>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                        <a href="#" class="mini-box" aria-label="Pago único de por vida">
                            <i><img src="/assets/images/infinito.png" alt="Pago único" style="height: 30px; width: 50px;" loading="lazy" decoding="async"></i>
                            <h2>Adquiere tu servicio una sola vez en la vida.</h2>
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                        <a href="#" class="mini-box" aria-label="Cobertura nacional">
                            <i><img src="/assets/images/republica.png" alt="Cobertura nacional" style="height: 40px; width: 40px;" loading="lazy" decoding="async"></i>
                            <h2>Cobertura en toda la República </h2>
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                        <a href="#" class="mini-box" aria-label="Precio ligado a edad">
                            <i><img src="/assets/images/usuario.png" alt="Ligado a CURP" style="height: 40px; width: 50px;" loading="lazy" decoding="async"></i>
                            <h2>Ligado a tu edad mediante CURP</h2>
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                        <a href="#" class="mini-box" aria-label="Mejor precio si eres joven">
                            <i><img src="/assets/images/relog.png" alt="Mejor precio joven" style="height: 35px; width: 35px;" loading="lazy" decoding="async"></i>
                            <h2>Mientras más joven, más bajo el costo</h2>
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                        <a href="#" class="mini-box" aria-label="Pago a crédito">
                            <i><img src="/assets/images/tarjeta.png" alt="Pago a crédito" style="height: 35px; width: 35px;" loading="lazy" decoding="async"></i>
                            <h2>Puedes pagar a crédito (3, 6 y 9 meses)</h2>
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                        <a href="#" class="mini-box" aria-label="Sin cargos ocultos">
                            <i><img src="/assets/images/ok.png" alt="Sin cargos" style="height: 35px; width: 35px;" loading="lazy" decoding="async"></i>
                            <h2>Sin renovaciones ni cargos ocultos</h2>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    -->
    <!-- Opiniones -->
    <section class="section colored" id="testimonials" >
        <div class="container">
            <br><br>
            <div class="row">
                <div class="offset-lg-3 col-lg-6">
                    <div class="center-heading">
                        <p>En KASU no inventamos reseñas; conoce las <strong><a target="_blank" rel="noopener" href="/testimonios.php">Opiniones Reales</a></strong> de nuestros clientes.</p>
                        <br>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php
                $cont = 1;
                $Max = (int)$basicas->MaxDat($mysqli, "id", "opiniones");
                $Arts = rand($cont, max($cont, $Max));
                $ks = $Max - 1;
                if ($Arts >= $ks) { $Arts = 1; }
                $Arts2 = $Arts + 2;
                while ($Arts <= $Arts2) {
                    $SqlArti = "SELECT * FROM opiniones WHERE id =" . (int)$Arts;
                    if ($ResArti = $mysqli->query($SqlArti)) {
                        if ($art = $ResArti->fetch_assoc()) {
                            $foto   = htmlspecialchars($art['foto'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            $nombre = htmlspecialchars($art['Nombre'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            $op     = htmlspecialchars($art['Opinion'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            $serv   = htmlspecialchars($art['Servicio'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                            echo "
                            <div class='col-lg-4 col-md-6 col-sm-12'>
                                <div class='team-item' itemprop='review' itemscope itemtype='https://schema.org/Review'>
                                    <div class='team-content'>
                                        <div class='team-info'>
                                             <br>
                                             <img src='{$foto}' alt='{$nombre}' loading='lazy' decoding='async'>
                                             <p itemprop='reviewBody'>{$op}</p>
                                             <h3 class='user-name' itemprop='author'>{$nombre}</h3>
                                             <span itemprop='itemReviewed'>{$serv}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ";
                        }
                    }
                    $Arts++;
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <?php require_once __DIR__ . '/html/footer.php'; ?>
    </footer>

    <!-- Modal de resultado CURP -->
    <?php require __DIR__ . '/html/Modal_CURP.php'; ?>

    <!-- Scripts al final -->
    <script src="/assets/js/jquery-2.1.0.min.js" defer></script>  <!-- si usas Bootstrap 3 -->
    <script src="/assets/js/bootstrap.min.js" defer></script>
    <!-- Scripts que imprime el finger print -->
    <script src="/login/Javascript/finger.js?v=3"></script>

    <script src="/assets/js/scrollreveal.min.js" defer></script>
    <script src="/assets/js/custom.js" defer></script>
    <script src="/eia/javascript/consulta_modal.js" defer></script>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    var banner = document.querySelector('.main-banner');
    if (!banner) return;

    var slides = banner.querySelectorAll('.banner-bg');
    if (!slides.length) return;

    // Asignar la imagen de fondo a cada slide desde data-bg
    slides.forEach(function (slide) {
        var url = slide.getAttribute('data-bg');
        if (url) {
            slide.style.backgroundImage = "url('" + url + "')";
        }
    });

    var current = 0;
    slides[current].classList.add('is-active');

    var intervalMs = 7000; // tiempo entre cambios (7 segundos)

    setInterval(function () {
        slides[current].classList.remove('is-active');
        current = (current + 1) % slides.length;
        slides[current].classList.add('is-active');
    }, intervalMs);
});
</script>

</body>
</html>