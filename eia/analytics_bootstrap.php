<?php
// analytics_bootstrap.php
// Inyección automática de Google Tag Manager en <head> y después de <body>
// Uso: require_once __DIR__.'/analytics_bootstrap.php'; al inicio del script.

if (!defined('ANALYTICS_BOOTSTRAP')) {
  define('ANALYTICS_BOOTSTRAP', true);

  // ===== CONFIGURA AQUÍ =====
  $GTM_ID          = 'GTM-MCR6T6W';    // <-- tu contenedor
  $EXCLUDE_IPS     = ['127.0.0.1'];    // IPs internas que NO cargan GTM
  $ENABLE_CONSENT  = true;             // Modo Consentimiento por defecto
  // ==========================

  $shouldInject = !in_array($_SERVER['REMOTE_ADDR'] ?? '', $EXCLUDE_IPS, true);

  // Snippet <head>
  $headSnippet = '';
  if ($shouldInject) {
    $consent = $ENABLE_CONSENT ? "
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('consent','default',{
    ad_user_data:'denied',
    ad_personalization:'denied',
    ad_storage:'denied',
    analytics_storage:'granted'
  });
</script>" : '';

    $headSnippet = $consent . "
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{$GTM_ID}');</script>
<!-- End Google Tag Manager -->";
  }

  // Snippet <body> (noscript)
  $bodySnippet = $shouldInject ? "
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id={$GTM_ID}\" height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->" : '';

  // Inyección por buffer de salida
  ob_start(function ($html) use ($headSnippet, $bodySnippet) {
    // Evita doble inyección
    if (strpos($html, 'www.googletagmanager.com/gtm.js') !== false) {
      return $html;
    }
    // Inserta antes de </head>
    if ($headSnippet && preg_match('/<\/head>/i', $html)) {
      $html = preg_replace('/<\/head>/i', $headSnippet . "\n</head>", $html, 1);
    }
    // Inserta después de <body ...>
    if ($bodySnippet && preg_match('/<body[^>]*>/i', $html)) {
      $html = preg_replace('/(<body[^>]*>)/i', "$1\n".$bodySnippet, $html, 1);
    }
    return $html;
  });
}
