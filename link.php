<?php
/**
 * Qué hace: Página tipo “link in bio” que muestra una grilla de artículos del blog de KASU.
 *           Incluye buscador por título (insensible a acentos y mayúsculas), paginación 12/página
 *           y soporte de URL con parámetro ?q= para enlazar búsquedas.
 * Fecha de actualización: 07/11/2025
 * Actualizó: Jose Carlos Cabrera Monroy (JCCM)
 */
// Iniciar la sesión
session_start();
// Archvo de rastreo de google tag manager
require_once __DIR__ . '/eia/analytics_bootstrap.php';
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <title>Link in Bio de KASU | Enlaces y artículos destacados</title>
  <meta name="description" content="Explora los artículos y enlaces destacados de KASU: educación, retiro, finanzas y asistencia funeraria. Página tipo link in bio optimizada para Instagram.">
  <meta name="keywords" content="KASU, link in bio, linktree, educación, retiro, finanzas personales, asistencia funeraria">
  <meta name="author" content="Jose Carlos Cabrera Monroy">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
  <link rel="canonical" href="https://kasu.com.mx/linktree">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="KASU">
  <meta property="og:url" content="https://kasu.com.mx/linktree">
  <meta property="og:title" content="Link in Bio de KASU | Enlaces y artículos">
  <meta property="og:description" content="Accede a nuestros contenidos: educación, retiro, finanzas y más.">
  <meta property="og:image" content="https://kasu.com.mx/assets/images/Index/ksulogo.png">
  <meta property="og:image:alt" content="Logotipo de KASU">

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Link in Bio de KASU | Enlaces y artículos">
  <meta name="twitter:description" content="Selecciona tu tema y abre el artículo correspondiente.">
  <meta name="twitter:image" content="https://kasu.com.mx/assets/images/Index/ksulogo.png">

  <!-- JSON-LD -->
  <script type="application/ld+json">
  {
    "@context":"https://schema.org",
    "@type":"CollectionPage",
    "name":"Link in Bio de KASU",
    "url":"https://kasu.com.mx/linktree",
    "image":"https://kasu.com.mx/assets/images/Index/ksulogo.png",
    "isPartOf":{"@type":"WebSite","name":"KASU","url":"https://kasu.com.mx"}
  }
  </script>

  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap">
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/templatemo-softy-pinko.css">
  <!-- Cargar ambos por si el archivo se llamó con o sin typo -->
  <link rel="stylesheet" href="assets/css/instragram.css">
  <link rel="stylesheet" href="assets/css/instagram.css">

<style>
  /* Utilidad accesible */
  .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,1,1);white-space:nowrap;border:0;}

  /* Contenedor amplio y centrado */
  #Clientes .container{ max-width:1280px; margin:0 auto; padding:0 12px; }

  /* Grilla 4 x 3 por página, celdas fluidas */
  .feed{ display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:24px; }

  /* Cada tarjeta ocupa toda su celda */
  .post{ width:100%; }
  .post a{ display:block; width:100%; height:100%; }

  /* Imágenes cuadradas, con medidas para evitar CLS */
  .feed .post img{
    width:100%!important; height:100%!important; display:block;
    object-fit:cover; aspect-ratio:1/1; border-radius:16px;
  }

  /* Responsivo */
  @media (max-width:1199px){ .feed{ grid-template-columns:repeat(3,minmax(0,1fr)); } }
  @media (max-width:767px){  .feed{ grid-template-columns:repeat(2,minmax(0,1fr)); } }
  @media (max-width:420px){  .feed{ grid-template-columns:1fr; } }

  /* Paginación */
  .pagination-wrap{ display:flex; justify-content:center; align-items:center; gap:8px; margin-top:22px; flex-wrap:wrap; }
  .page-btn{
    border:1px solid #e5e7eb; background:#fff; color:#374151; padding:8px 12px;
    border-radius:10px; cursor:pointer; min-width:40px; text-align:center;
  }
  .page-btn[disabled]{ opacity:.45; cursor:default; }
  .page-btn.active{ background:#e83e8c; border-color:#e83e8c; color:#fff; font-weight:600; }

  /* Encabezado compacto */
  .features-small-item .icon img{ width:88px; height:auto; }
  .features-small-item .descri{ text-align:center; margin-bottom:16px; }

  /* Buscador */
  .search-bar{
    display:flex; gap:8px; justify-content:center; align-items:center;
    margin:10px auto 22px; padding:6px; max-width:720px; flex-wrap:wrap;
  }
  .search-input{
    flex:1 1 420px; min-width:220px;
    border:1px solid #e5e7eb; border-radius:10px; padding:10px 12px;
    font-size:16px; line-height:1.2;
  }
  .clear-btn{
    border:1px solid #e5e7eb; background:#fff; color:#374151;
    padding:10px 12px; border-radius:10px; cursor:pointer;
  }
  .no-results{ text-align:center; margin-top:8px; color:#6b7280; }
</style>

  <script defer src="eia/javascript/Registro.js"></script>
</head>
<body>
  <section class="section" id="Clientes" aria-labelledby="kasu-linktree-title">
    <div class="features-small-item">
      <div class="descri">
        <div class="icon" aria-hidden="true">
          <i>
            <img src="assets/images/Index/ksulogo.png" name="Logo" alt="KASU logo"
                 loading="eager" fetchpriority="high" decoding="async" width="128" height="128">
          </i>
        </div>
        <h1 id="kasu-linktree-title" class="text-center">Link in Bio de KASU</h1>
        <p class="text-center">Selecciona un artículo para abrirlo.</p>
      </div>

      <!-- Buscador por título -->
      <div class="search-bar" role="search">
        <label for="searchInput" class="sr-only">Buscar por título</label>
        <input id="searchInput" class="search-input" type="search" placeholder="Buscar por título…"
               autocomplete="off" spellcheck="false" list="titleList">
        <datalist id="titleList"></datalist>
        <button id="clearBtn" class="clear-btn" type="button" aria-label="Limpiar búsqueda">Limpiar</button>
      </div>

      <!-- Contenedor paginable -->
      <div id="feed" class="feed" role="list">
        <!-- Mantener orden y URLs originales -->
        <div class="post" role="listitem">
          <a href="https://kasu.com.mx/blog/ahorros-para-estudios-universitarios/" rel="noopener" target="_blank">
            <img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/15.jpg" alt="Ahorros para estudios universitarios" width="1080" height="1080">
          </a>
        </div>
        <div class="post" role="listitem">
          <a href="https://kasu.com.mx/blog/educacion-del-futuro/" rel="noopener" target="_blank">
            <img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/18.jpg" alt="Educación del futuro" width="1080" height="1080">
          </a>
        </div>
        <div class="post" role="listitem">
          <a href="https://kasu.com.mx/blog/seguro-de-vida/" rel="noopener" target="_blank">
            <img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/51.jpg" alt="Seguro de vida" width="1080" height="1080">
          </a>
        </div>
        <div class="post" role="listitem">
          <a href="https://kasu.com.mx/blog/asistencia-funeraria/" rel="noopener" target="_blank">
            <img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/50.jpg" alt="Asistencia funeraria" width="1080" height="1080">
          </a>
        </div>
        <div class="post" role="listitem">
          <a href="https://kasu.com.mx/blog/plan-de-emergencia-familiar/" rel="noopener" target="_blank">
            <img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/49.jpg" alt="Plan de emergencia familiar" width="1080" height="1080">
          </a>
        </div>
        <div class="post" role="listitem">
          <a href="https://kasu.com.mx/blog/seguro-de-gastos-funerarios/" rel="noopener" target="_blank">
            <img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/48.jpg" alt="Seguro de gastos funerarios" width="1080" height="1080">
          </a>
        </div>
        <div class="post" role="listitem">
          <a href="https://kasu.com.mx/blog/seguro-de-retiro/" rel="noopener" target="_blank">
            <img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/52.jpg" alt="Seguro de retiro" width="1080" height="1080">
          </a>
        </div>
        <div class="post" role="listitem">
          <a href="https://kasu.com.mx/blog/becas-educativas/" rel="noopener" target="_blank">
            <img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/19.jpg" alt="Becas educativas" width="1080" height="1080">
          </a>
        </div>
        <div class="post" role="listitem">
          <a href="https://kasu.com.mx/blog/apoyo-psicologico-funerario/" rel="noopener" target="_blank">
            <img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/47.jpg" alt="Apoyo psicológico funerario" width="1080" height="1080">
          </a>
        </div>
        <div class="post" role="listitem">
          <a href="https://kasu.com.mx/blog/ahorro/" rel="noopener" target="_blank">
            <img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/53.jpg" alt="Ahorro: claves y consejos" width="1080" height="1080">
          </a>
        </div>
        <div class="post" role="listitem">
          <a href="https://kasu.com.mx/blog/siefore/" rel="noopener" target="_blank">
            <img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/54.jpg" alt="SIEFORE: qué es y cómo funciona" width="1080" height="1080">
          </a>
        </div>
        <div class="post" role="listitem">
          <a href="https://kasu.com.mx/blog/universidades-publicas-vs-privadas/" rel="noopener" target="_blank">
            <img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/20.jpg" alt="Universidades públicas vs privadas" width="1080" height="1080">
          </a>
        </div>

        <!-- Resto de posts originales … -->
        <div class="post"><a href="https://kasu.com.mx/blog/seguro-de-educacion/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/21.jpg" alt="Seguro de educación" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/cremacion/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/46.jpg" alt="Cremación" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/fiduciario/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/1.jpg" alt="Fiduciario" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/ser-productivo/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/59.jpg" alt="Ser productivo" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/test-carreras-universitarias/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/22.jpg" alt="Test de carreras universitarias" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/tipos-de-testamento/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/45.jpg" alt="Tipos de testamento" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/profanacion-de-tumbas/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/44.jpg" alt="Profanación de tumbas" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/recomendaciones-para-la-automotivacion/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/23.jpg" alt="Recomendaciones para la automotivación" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/estrategias-para-aprender-a-estudiar-online/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/24.jpg" alt="Estrategias para estudiar online" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/anuies-cursos-online/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/25.jpg" alt="ANUIES cursos online" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/beneficios-del-afore/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/57.jpg" alt="Beneficios del AFORE" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/discriminacion-edad/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/58.jpg" alt="Discriminación por edad" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/conoce-el-retiro-anticipado/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/56.jpg" alt="Retiro anticipado" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/beneficios-fiscales-de-un-plan-para-tu-retiro/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/55.jpg" alt="Beneficios fiscales del plan de retiro" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/el-retiro-es-una-oportunidad-a-nuevos-proyectos/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/65.jpg" alt="El retiro como oportunidad" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/apoyo-en-pareja-plan-personal-de-retiro/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/64.jpg" alt="Apoyo en pareja para plan de retiro" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/7-beneficios-de-la-inteligencia-emocional/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/26.jpg" alt="7 beneficios de la inteligencia emocional" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/invertir-en-un-fideicomiso-educacion-garantizada/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/27.jpg" alt="Invertir en un fideicomiso educativo" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/como-afontar-la-muerte-en-dias-festivos/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/43.jpg" alt="Cómo afrontar la muerte en días festivos" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/la-corona-de-flores-es-una-tradicion/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/42.jpg" alt="Corona de flores: tradición" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/conoce-los-diferentes-tipos-de-sepultura-y-sus-ventajas/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/41.jpg" alt="Tipos de sepultura y ventajas" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/es-importante-tener-un-seguro-funerario/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/40.jpg" alt="Importancia del seguro funerario" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/flores-para-funeral/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/39.jpg" alt="Flores para funeral" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/como-manejar-las-emociones/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/38.jpg" alt="Cómo manejar las emociones" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/como-ayudar-a-tus-hijos-el-sistema-educativo/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/28.jpg" alt="Ayudar a tus hijos en el sistema educativo" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/fideicomiso-en-mexico-a-nivel-educativo/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/27.jpg" alt="Fideicomiso en México a nivel educativo" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/por-que-iniciar-una-orientacion-educativa/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/29.jpg" alt="Por qué iniciar orientación educativa" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/que-es-mejor-la-cremacion-o-el-entierro/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/3.jpg" alt="¿Cremación o entierro?" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/tareas-del-duelo/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/37.jpg" alt="Tareas del duelo" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/factores-que-influyen-en-seguro-universitario/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/4.jpg" alt="Factores del seguro universitario" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/rol-de-los-padres-en-la-orientacion-vocacional/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/30.jpg" alt="Rol de los padres en la orientación vocacional" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/5-tipos-de-ahorro-que-pueden-ayudarte/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/5.jpg" alt="5 tipos de ahorro útiles" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/quienes-pueden-adquirir-un-plan-de-retiro/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/63.jpg" alt="Quiénes pueden adquirir un plan de retiro" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/5-tips-para-retirarte-y-tu-retiro-financiero/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/62.jpg" alt="5 tips para tu retiro" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/fideicomiso-conoce-todas-las-ventajas/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/6.jpg" alt="Ventajas del fideicomiso" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/cuanto-cuesta-una-cremacion-en-mexico/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/7.jpg" alt="Costo de cremación en México" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/como-encarar-y-afrontar-la-muerte-de-mama/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/36.jpg" alt="Cómo afrontar la muerte de mamá" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/top-carreras-universitarias/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/8.jpg" alt="Top carreras universitarias" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/razones-para-cotratar-un-fideicomiso-educativo/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/2.jpg" alt="Razones para contratar fideicomiso educativo" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/claves-para-mejorar-mi-economia/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/9.jpg" alt="Claves para mejorar tu economía" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/como-administrar-mi-pension/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/10.jpg" alt="Cómo administrar mi pensión" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/cuando-crear-un-fondo-de-retiro/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/11.jpg" alt="Cuándo crear un fondo de retiro" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/5-tips-de-ahorro-a-largo-plazo/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/12.jpg" alt="5 tips de ahorro a largo plazo" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/kasu-mas-que-productos-financieros/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/1-1.png" alt="KASU: más que productos financieros" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/universidades-privadas-en-mexico/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/31.jpg" alt="Universidades privadas en México" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/como-ahorrar-para-mi-pension/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/61.jpg" alt="Cómo ahorrar para mi pensión" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/costo-de-servicios-funerarios-en-mexico/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/13.jpg" alt="Costo de servicios funerarios en México" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/fideicomiso-educativo-o-seguro-educacion/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/14.jpg" alt="Fideicomiso educativo o seguro de educación" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/plan-personal-de-retiro-ahorra-hoy/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/17.jpg" alt="Plan personal de retiro: ahorra hoy" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/como-ahorrar-para-la-universidad-de-mi-hijo/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/32.jpg" alt="Cómo ahorrar para la universidad de mi hijo" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/afore-o-seguro-para-el-retiro/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/70.jpg" alt="AFORE o seguro para el retiro" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/seguro-para-el-retiro/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/60.jpg" alt="Seguro para el retiro" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/superar-la-perdida-de-un-familiar/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/35.jpg" alt="Superar la pérdida de un familiar" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/duelo-anticipado/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/34.jpg" alt="Duelo anticipado" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/elegir-carrera-universitaria/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/33.jpg" alt="Elegir carrera universitaria" width="1080" height="1080"></a></div>
        <div class="post"><a href="https://kasu.com.mx/blog/cuanto-se-gasta-en-una-carrera-universitaria/" rel="noopener" target="_blank"><img loading="lazy" decoding="async" src="blog/wp-content/uploads/2023/03/16.jpg" alt="Gasto en una carrera universitaria" width="1080" height="1080"></a></div>
      </div>

      <!-- Sin resultados -->
      <p id="noResults" class="no-results" hidden>No hay resultados para esta búsqueda.</p>

      <!-- Paginación -->
      <nav class="pagination-wrap" aria-label="Paginación de artículos">
        <button class="page-btn" id="prevBtn" type="button" aria-label="Página anterior">«</button>
        <div id="pageNums" role="group" aria-label="Números de página"></div>
        <button class="page-btn" id="nextBtn" type="button" aria-label="Página siguiente">»</button>
      </nav>
    </div>
  </section>

  <script src="assets/js/jquery-2.1.0.min.js"></script>
  <script src="assets/js/bootstrap.min.js"></script>
  <script>
    (function () {
      const perPage = 12; // 4 columnas x 3 filas
      const feed = document.getElementById('feed');
      const postNodes = Array.from(feed.querySelectorAll('.post'));
      const pageNums = document.getElementById('pageNums');
      const prevBtn = document.getElementById('prevBtn');
      const nextBtn = document.getElementById('nextBtn');
      const searchInput = document.getElementById('searchInput');
      const clearBtn = document.getElementById('clearBtn');
      const noResults = document.getElementById('noResults');
      const titleList = document.getElementById('titleList');

      // Utilidad: normalizar texto para búsqueda sin acentos
      const normalize = (s) => (s || '')
        .toString()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();

      // Índice de items con su título
      const items = postNodes.map(el => {
        const img = el.querySelector('img');
        const title = img ? img.alt : '';
        return { el, title, ntitle: normalize(title) };
      });

      // Poblar datalist con títulos únicos
      (() => {
        const seen = new Set();
        items.forEach(({ title }) => {
          const t = title.trim();
          if (!t || seen.has(t)) return;
          seen.add(t);
          const opt = document.createElement('option');
          opt.value = t;
          titleList.appendChild(opt);
        });
      })();

      // Estado
      let current = 1;
      let query = '';
      let visible = items.slice();

      const updateURL = (q) => {
        const url = new URL(window.location.href);
        if (q) url.searchParams.set('q', q);
        else url.searchParams.delete('q');
        history.replaceState(null, '', url.toString());
      };

      const filterItems = () => {
        const nq = normalize(query);
        visible = nq ? items.filter(it => it.ntitle.includes(nq)) : items.slice();
        if (current > Math.ceil(Math.max(1, visible.length) / perPage)) current = 1;
      };

      const render = () => {
        // Mostrar solo los visibles en la página actual
        const totalPages = Math.max(1, Math.ceil(visible.length / perPage));
        const start = (current - 1) * perPage;
        const end = start + perPage;

        // Ocultar todo
        items.forEach(it => { it.el.style.display = 'none'; });

        // Mostrar solo el rango visible
        visible.forEach((it, i) => {
          if (i >= start && i < end) it.el.style.display = '';
        });

        // Mensaje de sin resultados
        noResults.hidden = visible.length > 0;

        // Botones de páginas
        pageNums.innerHTML = '';
        for (let p = 1; p <= totalPages; p++) {
          const b = document.createElement('button');
          b.type = 'button';
          b.className = 'page-btn' + (p === current ? ' active' : '');
          b.textContent = p;
          b.setAttribute('aria-label', 'Ir a la página ' + p);
          b.addEventListener('click', () => { current = p; render(); window.scrollTo({top: 0, behavior: 'smooth'}); });
          pageNums.appendChild(b);
        }

        prevBtn.disabled = current === 1 || visible.length === 0;
        nextBtn.disabled = current === totalPages || visible.length === 0;
      };

      prevBtn.addEventListener('click', () => {
        if (current > 1) { current--; render(); window.scrollTo({top: 0, behavior: 'smooth'}); }
      });
      nextBtn.addEventListener('click', () => {
        const totalPages = Math.max(1, Math.ceil(visible.length / perPage));
        if (current < totalPages) { current++; render(); window.scrollTo({top: 0, behavior: 'smooth'}); }
      });

      // Búsqueda con debounce
      let t = null;
      const onSearchChange = () => {
        query = searchInput.value || '';
        updateURL(query);
        current = 1;
        filterItems();
        render();
      };
      searchInput.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(onSearchChange, 200);
      });
      searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { clearTimeout(t); onSearchChange(); }
      });

      clearBtn.addEventListener('click', () => {
        if (!searchInput.value) return;
        searchInput.value = '';
        onSearchChange();
        searchInput.focus();
      });

      // Cargar q de la URL si existe
      (function initFromURL(){
        const url = new URL(window.location.href);
        const q = url.searchParams.get('q') || '';
        if (q) searchInput.value = q;
        query = q;
      })();

      filterItems();
      render();
    })();
  </script>
</body>
</html>