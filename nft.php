<?php
/**
 * Qué hace: Página NFT multilenguaje con SEO dinámico y carga de secciones por idioma.
 * Fecha: 03/11/2025
 * Revisado por: JCCM
 */

/* ===== Sesión y dependencias ===== */
// indicar que se inicia una sesion *JCCM
session_start();
// Archivo de rastreo de Google Tag Manager
require_once __DIR__ . '/eia/analytics_bootstrap.php';
// Requerimos el archivo de librerias *JCCM
require_once __DIR__ . '/eia/librerias.php';
// Se establece el número de contacto según horario
require_once __DIR__ . '/eia/php/Telcto.php';
$tel = isset($tel) && $tel !== '' ? $tel : '7208177632';

/* ===== Alert opcional por ?Msg= ===== */
// Javascript que imprime el mensaje de alerta de recepcion de comentario
if (isset($_GET['Msg'])) {
  // json_encode evita problemas de comillas y XSS en alert
  echo "<script type='text/javascript'>alert(" . json_encode((string)$_GET['Msg'], JSON_UNESCAPED_UNICODE) . ");</script>";
}

/* ===== Parámetro de idioma ===== */
// Pasamos el get a variable
$Lgj = filter_input(INPUT_GET, 'Lg', FILTER_UNSAFE_RAW);
$Lgj = $Lgj ?: 'Espanol';

// Whitelist de idiomas válidos
$idiomasValidos = ['Espanol','Ingles','Aleman'];
if (!in_array($Lgj, $idiomasValidos, true)) {
  $Lgj = 'Espanol';
}

/* ===== Renderizado de bloques por idioma ===== */
// Creamos la funcion de conversion
function some_function($VAr) {
  // Español
  ob_start();
  include __DIR__ . '/html/EspanolNFT.php';
  return ob_get_clean();
}
function some_function2($VAr) {
  // Inglés
  ob_start();
  include __DIR__ . '/html/InglesNFT.php';
  return ob_get_clean();
}
function some_function3($VAr) {
  // Alemán
  ob_start();
  include __DIR__ . '/html/AlemanNFT.php';
  return ob_get_clean();
}
?>
<!DOCTYPE html>
<?php
  $langAttr = 'es';
  if ($Lgj === 'Ingles') {
    $langAttr = 'en';
  } elseif ($Lgj === 'Aleman') {
    $langAttr = 'de';
  }
?>
<html lang="<?= $langAttr ?>">
<head>
  <?php
    // SEO dinámico por idioma (no altera lógica)
    $lg  = $Lgj;
    $abs = 'https://kasu.com.mx' . strtok($_SERVER['REQUEST_URI'] ?? '/nft.php',' ');
    $titleMap = [
      'Espanol' => 'THE KASU NFT | Recompensas por cada NFT',
      'Ingles'  => 'THE KASU NFT | Rewards for every NFT',
      'Aleman'  => 'THE KASU NFT | Belohnungen pro NFT',
    ];
    $descMap = [
      'Espanol' => "La primera aseguradora en financiar con NFT's. Conecta tu wallet y conoce cómo obtener recompensas.",
      'Ingles'  => "The first insurer financing with NFTs. Connect your wallet and start earning rewards.",
      'Aleman'  => "Der erste Versicherer, der mit NFTs finanziert. Wallet verbinden und Belohnungen erhalten.",
    ];
    $title = htmlspecialchars($titleMap[$lg] ?? $titleMap['Espanol'], ENT_QUOTES, 'UTF-8');
    $desc  = htmlspecialchars($descMap[$lg]  ?? $descMap['Espanol'],  ENT_QUOTES, 'UTF-8');
    $ogimg = 'https://kasu.com.mx/assets/images/nft2.gif';
  ?>
  <meta charset="utf-8">
  <title><?= $title ?></title>
  <meta name="description" content="<?= $desc ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="robots" content="index,follow,max-image-preview:large">
  <meta name="author" content="KASU Servicios a Futuro">
  <meta name="keywords" content="KASU, NFT, aseguradora, recompensas, blockchain">
  <meta name="theme-color" content="#F1F1FC">

  <!-- Canonical -->
  <link rel="canonical" href="https://kasu.com.mx/nft.php<?= isset($_GET['Lg']) ? '?Lg='.urlencode($lg) : '' ?>">

  <!-- Hreflang -->
  <link rel="alternate" href="https://kasu.com.mx/nft.php?Lg=Espanol" hreflang="es-MX">
  <link rel="alternate" href="https://kasu.com.mx/nft.php?Lg=Ingles"  hreflang="en">
  <link rel="alternate" href="https://kasu.com.mx/nft.php?Lg=Aleman"  hreflang="de">
  <link rel="alternate" href="https://kasu.com.mx/nft.php"           hreflang="x-default">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="KASU">
  <meta property="og:url" content="<?= htmlspecialchars($abs, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:title" content="<?= $title ?>">
  <meta property="og:description" content="<?= $desc ?>">
  <meta property="og:image" content="<?= $ogimg ?>">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= $title ?>">
  <meta name="twitter:description" content="<?= $desc ?>">
  <meta name="twitter:image" content="<?= $ogimg ?>">

  <!-- JSON-LD WebPage -->
  <script type="application/ld+json">
  {
    "@context":"https://schema.org",
    "@type":"WebPage",
    "url":"<?= htmlspecialchars($abs, ENT_QUOTES, 'UTF-8') ?>",
    "name":"<?= $title ?>",
    "description":"<?= $desc ?>",
    "isPartOf":{"@type":"WebSite","name":"KASU","url":"https://kasu.com.mx"}
  }
  </script>

  <!-- Fuentes + Favicon -->
  <link rel="icon" href="/assets/images/kasu_logo.jpeg">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&family=IBM+Plex+Serif:wght@400;500;600&display=swap">
  <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons'>

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css?v=<?php echo $VerCache;?>">
    <link rel="stylesheet" type="text/css" href="/assets/css/font-awesome.css?v=<?php echo $VerCache;?>">
    <link rel="stylesheet" type="text/css" href="/assets/css/kasu-menu.css?v=<?php echo $VerCache;?>">
    <link rel="stylesheet" type="text/css" href="/assets/css/index-home.css?v=<?php echo $VerCache;?>">
    <link rel="stylesheet" type="text/css" href="/assets/css/nft.css?v=<?php echo $VerCache;?>">
    <link rel="stylesheet" type="text/css" href="/assets/css/kasu-chat.css?v=<?php echo $VerCache;?>">
    
</head>
<body class="kasu-ui">
  <?php
    $heroCopy = [
      'Espanol' => [
        'eyebrow' => 'KASU NFT',
        'subtitle' => 'CADA NFT GENERA RECOMPENSAS',
        'cta_primary' => 'Vincular wallet',
        'cta_secondary' => 'Ver colección en OpenSea',
        'lang_label' => 'Idioma',
        'open_sea_label' => 'Abrir colección KASU NFT en OpenSea',
        'trust' => [
          'Colección verificada en OpenSea',
          'Recompensas variables según rendimiento',
          'Sin custodia de fondos',
        ],
      ],
      'Ingles' => [
        'eyebrow' => 'KASU NFT',
        'subtitle' => 'EVERY NFT GENERATES REWARDS',
        'cta_primary' => 'Connect wallet',
        'cta_secondary' => 'View collection on OpenSea',
        'lang_label' => 'Language',
        'open_sea_label' => 'Open KASU NFT collection on OpenSea',
        'trust' => [
          'Verified collection on OpenSea',
          'Variable rewards based on performance',
          'Non-custodial flow',
        ],
      ],
      'Aleman' => [
        'eyebrow' => 'KASU NFT',
        'subtitle' => 'JEDE NFT ERZIELT BELOHNUNGEN',
        'cta_primary' => 'Wallet verbinden',
        'cta_secondary' => 'Kollektion auf OpenSea ansehen',
        'lang_label' => 'Sprache',
        'open_sea_label' => 'KASU NFT Kollektion auf OpenSea öffnen',
        'trust' => [
          'Verifizierte Kollektion auf OpenSea',
          'Variable Belohnungen je nach Performance',
          'Keine Verwahrung von Geldern',
        ],
      ],
    ];
    $hero = $heroCopy[$Lgj] ?? $heroCopy['Espanol'];
  ?>
  <?php require_once __DIR__ . '/html/MenuPrincipal.php'; ?>
  
  <main class="kasu-nft">
    <section class="kasu-nft__hero" aria-labelledby="kasu-nft-hero-title">
      <div class="container">
        <div class="kasu-nft__hero-top">
          <div class="kasu-nft__brand">
            <img src="/assets/images/kasu_logo.jpeg" alt="KASU" width="48" height="48">
            <span>KASU</span>
          </div>
          <div class="kasu-nft__lang">
            <label for="kasu-lang" class="sr-only"><?= htmlspecialchars($hero['lang_label'], ENT_QUOTES, 'UTF-8') ?></label>
            <select id="kasu-lang" class="kasu-nft__lang-select" onchange="this.value && (window.location = this.value);">
              <option value="https://kasu.com.mx/nft.php?Lg=Espanol" <?= $Lgj === 'Espanol' ? 'selected' : '' ?>>Español</option>
              <option value="https://kasu.com.mx/nft.php?Lg=Ingles" <?= $Lgj === 'Ingles' ? 'selected' : '' ?>>English</option>
              <option value="https://kasu.com.mx/nft.php?Lg=Aleman" <?= $Lgj === 'Aleman' ? 'selected' : '' ?>>Deutsch</option>
            </select>
          </div>
        </div>

        <div class="row align-items-center">
          <div class="col-lg-6">
            <p class="kasu-nft__eyebrow"><?= htmlspecialchars($hero['eyebrow'], ENT_QUOTES, 'UTF-8') ?></p>
            <h1 id="kasu-nft-hero-title" class="kasu-nft__title">KASU NFT</h1>
            <p class="kasu-nft__subtitle"><?= htmlspecialchars($hero['subtitle'], ENT_QUOTES, 'UTF-8') ?></p>
            <div class="kasu-nft__cta">
              <a href="#" id="kasu-wallet-connect" class="btn kasu-nft__btn kasu-nft__btn-primary">
                <?= htmlspecialchars($hero['cta_primary'], ENT_QUOTES, 'UTF-8') ?>
              </a>
              <a href="https://opensea.io/collection/kasunft" target="_blank" rel="noopener noreferrer" class="btn kasu-nft__btn kasu-nft__btn-secondary">
                <?= htmlspecialchars($hero['cta_secondary'], ENT_QUOTES, 'UTF-8') ?>
              </a>
            </div>
            <ul class="kasu-nft__trust">
              <li><?= htmlspecialchars($hero['trust'][0], ENT_QUOTES, 'UTF-8') ?></li>
              <li><?= htmlspecialchars($hero['trust'][1], ENT_QUOTES, 'UTF-8') ?></li>
              <li><?= htmlspecialchars($hero['trust'][2], ENT_QUOTES, 'UTF-8') ?></li>
            </ul>
          </div>
          <div class="col-lg-6">
            <div class="kasu-nft__hero-media">
              <a href="https://opensea.io/collection/kasunft" target="_blank" rel="noopener noreferrer" aria-label="<?= htmlspecialchars($hero['open_sea_label'], ENT_QUOTES, 'UTF-8') ?>">
                <img
                  src="/assets/images/nft2.gif"
                  alt="KASU NFT"
                  loading="lazy"
                  decoding="async"
                  width="960"
                  height="960">
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <?php
      // Carga del bloque según idioma
      if ($Lgj === "Ingles") {
        echo some_function2($Lgj);
      } elseif ($Lgj === "Aleman") {
        echo some_function3($Lgj);
      } else {
        echo some_function($Lgj);
      }
    ?>
  </main>

  <footer>
    <?php require_once __DIR__ . '/html/footer.php'; ?>
  </footer>

  <script src="/assets/js/jquery-2.1.0.min.js"></script>
  <script src="/assets/js/bootstrap.min.js"></script>
  <script src="/assets/js/waypoints.min.js"></script>
  <script src="/assets/js/imgfix.min.js"></script>
  <script src="/assets/js/jquery.counterup.min.js"></script>
  <script src="/assets/js/scrollreveal.min.js"></script>
  <script src="/assets/js/custom.js"></script>
  <script type="text/javascript" async src="https://d335luupugsy2.cloudfront.net/js/loader-scripts/28dd2782-ee7d-4b25-82b1-f5993b27764a-loader.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    var fabButton = document.getElementById('kasu-fab');
    var fabPanel = document.getElementById('kasu-fab-panel');
    var chatOpen = document.getElementById('kasu-chat-open');
    var chatOverlay = document.getElementById('kasu-chat-overlay');
    var chatClose = document.getElementById('kasu-chat-close');
    var chatForm = document.getElementById('kasu-chat-form');
    var chatInput = document.getElementById('kasu-chat-input');
    var chatBody = document.getElementById('kasu-chat-messages');

    var hideFabPanel = function () {
      if (fabPanel) {
        fabPanel.setAttribute('hidden', 'hidden');
      }
      if (fabButton) {
        fabButton.setAttribute('aria-expanded', 'false');
      }
    };

    var showFabPanel = function () {
      if (fabPanel) {
        fabPanel.removeAttribute('hidden');
      }
      if (fabButton) {
        fabButton.setAttribute('aria-expanded', 'true');
      }
    };

    var openChat = function () {
      if (!chatOverlay) return;
      chatOverlay.removeAttribute('hidden');
      if (chatOpen) chatOpen.setAttribute('aria-expanded', 'true');
      if (chatInput) chatInput.focus();
      hideFabPanel();
    };

    var closeChat = function () {
      if (!chatOverlay) return;
      chatOverlay.setAttribute('hidden', 'hidden');
      if (chatOpen) chatOpen.setAttribute('aria-expanded', 'false');
      showFabPanel();
    };

    if (fabButton && fabPanel) {
      fabButton.addEventListener('click', function () {
        var chatOpenNow = chatOverlay && !chatOverlay.hasAttribute('hidden');
        if (chatOpenNow) {
          closeChat();
          showFabPanel();
          return;
        }

        var expanded = fabButton.getAttribute('aria-expanded') === 'true';
        fabButton.setAttribute('aria-expanded', String(!expanded));
        if (expanded) {
          fabPanel.setAttribute('hidden', 'hidden');
        } else {
          fabPanel.removeAttribute('hidden');
        }
      });
    }

    if (chatOpen) {
      chatOpen.addEventListener('click', function () {
        var isOpen = chatOverlay && !chatOverlay.hasAttribute('hidden');
        if (isOpen) {
          closeChat();
        } else {
          openChat();
        }
      });
    }

    if (chatClose) {
      chatClose.addEventListener('click', closeChat);
    }

    if (chatForm && chatInput && chatBody) {
      chatForm.addEventListener('submit', function (event) {
        event.preventDefault();
        var message = chatInput.value.trim();
        if (!message) return;

        var wrapper = document.createElement('div');
        wrapper.className = 'kasu-chat-message kasu-chat-message--user';
        var bubble = document.createElement('div');
        bubble.className = 'kasu-chat-bubble';
        bubble.textContent = message;
        wrapper.appendChild(bubble);
        chatBody.appendChild(wrapper);
        chatBody.scrollTop = chatBody.scrollHeight;
        chatInput.value = '';
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
      <a href="https://wa.me/<?php echo preg_replace('/\\D/', '', $tel ?? ''); ?>?text=Hola,%20requiero%20atención%20inmediata%20de%20KASU" class="kasu-fab-action kasu-fab-action--whats" target="_blank" rel="noopener" aria-label="Enviar WhatsApp a KASU">
        <span class="kasu-fab-icon" aria-hidden="true">💬</span>
        Enviar WhatsApp
      </a>
      <button type="button" class="kasu-fab-action kasu-fab-action--chat" id="kasu-chat-open" aria-expanded="false" aria-controls="kasu-chat-overlay">
        <span class="kasu-fab-icon" aria-hidden="true">🗨️</span>
        Hablar con alguien
      </button>
    </div>
    <button type="button" class="kasu-fab" id="kasu-fab" aria-expanded="false" aria-controls="kasu-fab-panel">
      <img src="/assets/images/flor_redonda.svg" alt="" width="34" height="34" loading="lazy" decoding="async">
      Atención al cliente
    </button>
  </div>

  <section class="kasu-chat-overlay" id="kasu-chat-overlay" aria-live="polite" hidden>
    <div class="kasu-chat-panel">
      <header class="kasu-chat-panel__header">
        <div class="kasu-chat-panel__brand">
          <img src="/assets/images/flor_redonda.svg" alt="KASU" width="28" height="28" loading="lazy" decoding="async">
          <div>
            <p class="kasu-chat-panel__title">Chat KASU</p>
            <span class="kasu-chat-panel__status">En linea</span>
          </div>
        </div>
        <div class="kasu-chat-panel__actions">
          <span class="kasu-chat-panel__pill">Vista 360</span>
          <button type="button" class="kasu-chat-panel__close" id="kasu-chat-close" aria-label="Cerrar chat">×</button>
        </div>
      </header>

      <div class="kasu-chat-panel__messages" id="kasu-chat-messages">
        <div class="kasu-chat-message kasu-chat-message--bot">
          <img src="/assets/images/flor_redonda.svg" alt="KASU" class="kasu-chat-avatar" width="26" height="26" loading="lazy" decoding="async">
          <div class="kasu-chat-bubble">
            Hola, soy la asistente virtual de KASU. en que puedo ayudarte hoy?
          </div>
        </div>
      </div>

      <form class="kasu-chat-panel__form" id="kasu-chat-form" autocomplete="off">
        <input type="text" id="kasu-chat-input" name="kasu-chat-input" placeholder="Escribe tu mensaje">
        <button type="submit">Enviar</button>
      </form>
    </div>
  </section>
</body>
</html>
