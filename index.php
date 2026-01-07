<?php
/*******************************************************************************************************
 * Qué hace: Página principal. Muestra landing, cotizador por CURP y gestiona baja de newsletter por GET.
 * Fecha: 15/11/2025
 * Revisado por: JCCM
 * Archivo: index.php
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
    <meta name="theme-color" content="#F1F1FC">

    <!-- Conexiones rápidas -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="//kasu.com.mx">

    <!-- Iconos -->
    <link rel="icon" href="https://kasu.com.mx/login/assets/img/FlorKasu.png">
    <link rel="apple-touch-icon" sizes="128x128" href="https://kasu.com.mx/login/assets/img/icon-128x128.jpeg">

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css?v=<? echo $VerCache;?>">
    <link rel="stylesheet" type="text/css" href="/assets/css/font-awesome.css?v=<? echo $VerCache;?>">
    <link rel="stylesheet" type="text/css" href="/assets/css/kasu-menu.css?v=<? echo $VerCache;?>">
    <link rel="stylesheet" type="text/css" href="/assets/css/index-home.css?v=<? echo $VerCache;?>">

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

<body class="kasu-ui home-page">
    <?php require_once __DIR__ . '/html/MenuPrincipal.php'; ?>
    
    <!-- Portada -->
    <div class="main-banner wow fadeIn" id="top"
        data-wow-duration="1s" data-wow-delay="0.5s">

        <!-- Slider de fondo -->
        <div class="banner-bg-slider">
            <!-- Agrega aquí UNA fila por imagen que tengas en assets/images/Sliders -->
            <div class="banner-bg" data-bg="/assets/images/Sliders/Protege_1.png?v=<?php echo $VerCache; ?>"></div>
            <div class="banner-bg" data-bg="/assets/images/Sliders/Protege_2.png?v=<?php echo $VerCache; ?>"></div>
            <div class="banner-bg" data-bg="/assets/images/Sliders/Protege_3.png?v=<?php echo $VerCache; ?>"></div>
            <!-- Duplica/ajusta estas líneas con los nombres reales de tus archivos -->
        </div>

        <!-- Contenido encima del fondo -->
        <div class="main-banner-content">
            <h1 class="sr-only">KASU | Servicios funerarios a futuro en Mexico</h1>
            <div class="container">
                <div class="row" itemscope itemtype="https://schema.org/Service">
                    <meta itemprop="serviceType" content="Servicios funerarios a futuro">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-6 align-self-center"
                                data-scroll-reveal="enter left move 50px over 0.6s after 0.4s">
                                <div class="left-content header-text wow fadeInLeft"
                                    data-wow-duration="1s" data-wow-delay="1s">
                                    <div class="hero-card">
                                        <h6 class="hero-eyebrow">
                                            <img src="/assets/images/flor_redonda.svg"
                                                class="hero-icon"
                                                width="36"
                                                height="36"
                                                alt="Flor KASU"
                                                loading="lazy" decoding="async">
                                            Servicios a futuro
                                        </h6>
                                        <h2>Servicios de <span>Gastos Funerarios</span> y mucho</em> mas</h2>
                                        <p>La vision de <strong>KASU</strong> es lograr una cobertura universal para las familias mexicanas en lo que se refiere a servicios funerarios.</p>
                                        <div class="hero-consulta" id="hero-consulta">
                                            <div class="hero-consulta-form" role="search" aria-label="Consulta por CURP">
                                                <div class="input-group mb-3">
                                                    <label class="sr-only" for="curp">Ingresa tu CURP</label>
                                                    <input id="curp" type="text" class="form-control kasu-input"
                                                        placeholder="Consulta tu poliza con CURP"
                                                        autocomplete="on" inputmode="latin"
                                                        aria-label="Consulta tu poliza con CURP" required>
                                                    <div class="input-group-append">
                                                        <button type="button" id="form-submit" class="btn kasu-btn"
                                                                onclick="consultaModal()"
                                                                aria-label="Consultar CURP">Consultar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="hero-actions">
                                            <button type="button" class="btn btn-outline-dark" id="hero-quote-toggle"
                                                    aria-expanded="false" aria-controls="hero-quote">
                                                Cotiza con tu CURP
                                            </button>
                                        </div>
                                        <div class="hero-quote" id="hero-quote" hidden>
                                            <form method="POST" action="/login/php/Registro_Prospectos.php"
                                                  class="hero-quote-form" aria-label="Cotizador rapido">
                                                <div data-fingerprint-slot></div>
                                                <div class="hero-field">
                                                    <label for="CURP_Hero">Ingresar CURP</label>
                                                    <input id="CURP_Hero" name="CURP" type="text" class="kasu-input" autocomplete="on"
                                                           placeholder="Ingresar CURP" required>
                                                </div>
                                                <div class="hero-field">
                                                    <label for="Email_Hero">Correo electronico</label>
                                                    <input type="email" name="Email" id="Email_Hero" class="kasu-input" autocomplete="email"
                                                           placeholder="Correo electronico">
                                                </div>
                                                <button type="submit" name="FormCotizar" class="kasu-btn kasu-btn-block">
                                                    Cotizar servicio
                                                </button>
                                            </form>
                                        </div>
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

        <!-- Opiniones -->
    <section class="section colored" id="testimonials">
        <div class="container">
            <div class="opiniones-header">
                <p>En KASU no inventamos reseñas; conoce las <strong><a target="_blank" rel="noopener" href="/testimonios.php">Opiniones Reales</a></strong> de nuestros clientes.</p>
            </div>
        </div>
        <?php
        $opinionesHtml = '';
        $SqlArti = "SELECT Nombre, Servicio, Opinion, foto FROM opiniones ORDER BY id DESC";
        if ($ResArti = $mysqli->query($SqlArti)) {
            while ($art = $ResArti->fetch_assoc()) {
                $foto   = htmlspecialchars($art['foto'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $nombre = htmlspecialchars($art['Nombre'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $op     = htmlspecialchars($art['Opinion'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $serv   = htmlspecialchars($art['Servicio'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                $opinionesHtml .= "
                <div class='opinion-card' itemprop='review' itemscope itemtype='https://schema.org/Review'>
                    <div class='opinion-avatar'>
                        <img src='{$foto}' alt='{$nombre}' loading='lazy' decoding='async'>
                    </div>
                    <div class='opinion-name' itemprop='author'>{$nombre}</div>
                    <div class='opinion-service' itemprop='itemReviewed'>{$serv}</div>
                    <p class='opinion-text' itemprop='reviewBody'>{$op}</p>
                </div>
                ";
            }
        }
        ?>
        <div class="opiniones-marquee" aria-label="Opiniones de clientes">
            <div class="opiniones-track">
                <?php
                if ($opinionesHtml !== '') {
                    echo $opinionesHtml;
                    echo $opinionesHtml;
                } else {
                    echo "<p class='opiniones-empty'>Aun no hay opiniones disponibles.</p>";
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Productos -->
    <section class="Productos-Index"> 
        <div class="container" itemscope itemtype="https://schema.org/CollectionPage">
            <!-- Productos -->
            <?php require_once __DIR__ . '/html/Section_Productos.php'; ?>
        </div>
    </section>

    <!-- Clientes y cotizaciones -->
    <section class="section colored" id="Datos">
        <br>
        <div class="container">
            <div class="clients-grid">
                <div class="clients-info" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
                    <p class="clients-eyebrow">Clientes y cotizaciones</p>
                    <h2 class="clients-title">Cobertura universal para las familias mexicanas</h2>
                    <p class="clients-copy"><strong>KASU</strong> es una empresa que ofrece servicios funerarios a bajo costo en México, los cuales <strong>se pagan una sola vez en la vida</strong> y no requieren renovación o pagos adicionales, una <strong>característica única</strong> frente a alternativas del mercado.</p>
                    <div class="clients-count" itemprop="provider">
                        <span class="clients-count-number">1<?php echo number_format($basicas->MaxDat($mysqli, "Id", "Venta"), 0, ".", ","); ?></span>
                        <span class="clients-count-label">Clientes Activos</span>
                    </div>
                    <div class="clients-count" itemprop="provider">
                        <span class="clients-count-number"><?php echo number_format(783, 0, ".", ","); ?></span>
                        <span class="clients-count-label">Servicios Realizados</span>
                    </div>
                    <p class="clients-copy">Este enfoque en ayudar a las personas es el factor más importante a comunicar, destacando la importancia de apoyar a las comunidades locales y brindar una solución eficaz a un problema común. Además, el hecho de que <strong>KASU</strong> se haya concretado en un <strong>fideicomiso</strong> permite brindar un <strong>servicio funerario digno</strong> en el momento que más lo necesitas.</p>
                </div>
                <div class="clients-card" data-scroll-reveal="enter right move 30px over 0.6s after 0.6s" id="Clientes">
                    <div class="clients-card-header">
                        <div class="clients-card-logo">
                            <img src="/assets/images/Index/florkasu.png" name="Logo" alt="KASU Logo" loading="lazy" decoding="async">
                        </div>
                        <div>
                            <p class="clients-card-eyebrow">Servicios a futuro</p>
                            <h3 class="clients-card-title">Cotiza</h3>
                            <p class="clients-card-sub">Cotiza tu servicio, solo requieres tu clave CURP.</p>
                        </div>
                    </div>
                    <form method="POST" id="Cotizar" action="/login/php/Registro_Prospectos.php" class="clients-form">
                        <div data-fingerprint-slot></div>
                        <div class="hero-field">
                            <label for="CURP">Ingresar CURP</label>
                            <input name="CURP" id="CURP" class="kasu-input" placeholder="Ingresar CURP" autocomplete="on" aria-label="Ingresar CURP">
                        </div>
                        <div class="hero-field">
                            <label for="Email">Correo electronico</label>
                            <input type="email" name="Email" id="Email" class="kasu-input" placeholder="Correo electronico" autocomplete="email" aria-label="Ingresar correo electronico">
                        </div>
                        <button type="submit" name="FormCotizar" id="FormCotizar" class="kasu-btn kasu-btn-block" data-toggle="modal" aria-label="Cotizar servicio funerario">Cotizar servicio</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- Inicio - Articulos de Blog -->
    <section class="blog-section" id="Blog">
        <div class="container">
            <div class="blog-header">
                <p class="blog-eyebrow">Blog KASU</p>
                <h2 class="blog-title">Articulos recientes</h2>
                <p class="blog-sub">Explora contenido util y actual sobre servicios funerarios, previsión y bienestar familiar.</p>
            </div>
            <div class="blog-grid" id="blog-cards" aria-live="polite"></div>
            <p class="blog-status" id="blog-status">Cargando articulos...</p>
        </div>
    </section>

    <!-- Final - Articulos de Blog -->

    <!-- Footer -->
    <footer class="site-footer">
        <?php require_once __DIR__ . '/html/footer.php'; ?>
    </footer>

    <!-- Modal de resultado CURP -->
    <?php require __DIR__ . '/html/Modal_CURP.php'; ?>

    <!-- Scripts al final -->
    <script src="/assets/js/jquery-2.1.0.min.js" defer></script>  <!-- si usas Bootstrap 3 -->
    <script src="/assets/js/bootstrap.min.js" defer></script>
    <!-- Scripts que imprime el finger print -->
    <script src="/login/Javascript/finger.js?v=<? echo $VerCache;?>"></script>

    <script src="/assets/js/scrollreveal.min.js" defer></script>
    <script src="/assets/js/custom.js" defer></script>
    <script src="/eia/javascript/consulta_modal.js" defer></script>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    var heroToggle = document.getElementById('hero-quote-toggle');
    var heroQuote = document.getElementById('hero-quote');
    var heroConsulta = document.getElementById('hero-consulta');

    if (heroToggle && heroQuote && heroConsulta) {
        heroToggle.addEventListener('click', function () {
            var showingQuote = !heroQuote.hasAttribute('hidden');

            if (showingQuote) {
                heroQuote.setAttribute('hidden', 'hidden');
                heroConsulta.removeAttribute('hidden');
                heroToggle.setAttribute('aria-expanded', 'false');
                heroToggle.textContent = 'Cotiza con tu CURP';
                var consultaInput = heroConsulta.querySelector('input');
                if (consultaInput) {
                    consultaInput.focus();
                }
            } else {
                heroConsulta.setAttribute('hidden', 'hidden');
                heroQuote.removeAttribute('hidden');
                heroToggle.setAttribute('aria-expanded', 'true');
                heroToggle.textContent = 'Buscar mi poliza de cliente';
                var firstInput = heroQuote.querySelector('input, select, textarea');
                if (firstInput) {
                    firstInput.focus();
                }
            }
        });
    }

    var blogContainer = document.getElementById('blog-cards');
    var blogStatus = document.getElementById('blog-status');
    if (blogContainer && blogStatus) {
        var blogEndpoint = 'https://kasu.com.mx/blog/wp-json/wp/v2/posts?per_page=12&_embed=1';
        var stripHtml = function (html) {
            var temp = document.createElement('div');
            temp.innerHTML = html || '';
            return (temp.textContent || temp.innerText || '').trim();
        };
        var truncateText = function (text, max) {
            if (text.length <= max) return text;
            return text.slice(0, max).trim() + '...';
        };
        var pickRandom = function (items, count) {
            var copy = items.slice();
            var picked = [];
            while (copy.length && picked.length < count) {
                var index = Math.floor(Math.random() * copy.length);
                picked.push(copy.splice(index, 1)[0]);
            }
            return picked;
        };

        fetch(blogEndpoint)
            .then(function (response) {
                if (!response.ok) throw new Error('Blog fetch failed');
                return response.json();
            })
            .then(function (posts) {
                if (!Array.isArray(posts) || posts.length === 0) {
                    blogStatus.textContent = 'Aun no hay articulos disponibles.';
                    return;
                }

                blogContainer.innerHTML = '';
                var selected = pickRandom(posts, 3);
                selected.forEach(function (post) {
                    var title = stripHtml(post.title && post.title.rendered ? post.title.rendered : '');
                    var rawExcerpt = post.excerpt && post.excerpt.rendered ? post.excerpt.rendered : (post.content && post.content.rendered ? post.content.rendered : '');
                    var excerpt = truncateText(stripHtml(rawExcerpt), 140);
                    var imageUrl = '';
                    var media = post._embedded && post._embedded['wp:featuredmedia'] ? post._embedded['wp:featuredmedia'][0] : null;
                    if (media) {
                        if (media.media_details && media.media_details.sizes) {
                            imageUrl = (media.media_details.sizes.medium_large && media.media_details.sizes.medium_large.source_url) ||
                                (media.media_details.sizes.large && media.media_details.sizes.large.source_url) ||
                                media.source_url || '';
                        } else {
                            imageUrl = media.source_url || '';
                        }
                    }

                    var card = document.createElement('article');
                    card.className = 'blog-card';

                    var link = document.createElement('a');
                    link.className = 'blog-card-link';
                    link.href = post.link;
                    link.setAttribute('aria-label', title || 'Ver articulo');

                    var mediaWrap = document.createElement('div');
                    mediaWrap.className = 'blog-card-media';
                    if (imageUrl) {
                        var img = document.createElement('img');
                        img.src = imageUrl;
                        img.alt = title || 'Articulo de blog';
                        img.loading = 'lazy';
                        img.decoding = 'async';
                        mediaWrap.appendChild(img);
                    }

                    var body = document.createElement('div');
                    body.className = 'blog-card-body';

                    var titleEl = document.createElement('h3');
                    titleEl.className = 'blog-card-title';
                    titleEl.textContent = title || 'Articulo KASU';

                    var textEl = document.createElement('p');
                    textEl.className = 'blog-card-text';
                    textEl.textContent = excerpt || 'Descubre mas en nuestro blog.';

                    body.appendChild(titleEl);
                    body.appendChild(textEl);
                    link.appendChild(mediaWrap);
                    link.appendChild(body);
                    card.appendChild(link);
                    blogContainer.appendChild(card);
                });

                blogStatus.textContent = '';
                blogStatus.style.display = 'none';
            })
            .catch(function () {
                blogStatus.textContent = 'No se pudieron cargar los articulos del blog.';
            });
    }

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

    var fabButton = document.getElementById('kasu-fab');
    var fabPanel = document.getElementById('kasu-fab-panel');
    if (fabButton && fabPanel) {
        fabButton.addEventListener('click', function () {
            var expanded = fabButton.getAttribute('aria-expanded') === 'true';
            fabButton.setAttribute('aria-expanded', String(!expanded));
            if (expanded) {
                fabPanel.setAttribute('hidden', 'hidden');
            } else {
                fabPanel.removeAttribute('hidden');
            }
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
        <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $tel ?? ''); ?>?text=Hola,%20requiero%20atención%20inmediata%20de%20KASU" class="kasu-fab-action kasu-fab-action--whats" target="_blank" rel="noopener" aria-label="Enviar WhatsApp a KASU">
            <span class="kasu-fab-icon" aria-hidden="true">💬</span>
            Enviar WhatsApp
        </a>
    </div>
    <button type="button" class="kasu-fab" id="kasu-fab" aria-expanded="false" aria-controls="kasu-fab-panel">
        <img src="/assets/images/flor_redonda.svg" alt="" width="34" height="34" loading="lazy" decoding="async">
        Atención inmediata
    </button>
</div>

</body>
</html>
