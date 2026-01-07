<?php 
//Revisado por Jose Carlos Cabrera Monroy 1 de Noviembre del 2025
// html/MenuPrincipal.php 
?>
<header class="header-area kasu-header">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <nav class="main-nav" itemscope itemtype="https://schema.org/SiteNavigationElement" aria-label="Navegacion principal">
          <!-- Logo -->
          <a href="/" class="logo" itemprop="url" aria-label="Inicio KASU">
            <img src="/assets/images/Index/ksulogo.png"
                 alt="KASU — inicio"
                 loading="eager"
                 decoding="async"
                 itemprop="image" />
          </a>

          <?php
          // Ruta actual normalizada (sin query y sin slash final, excepto la raíz)
          $uri = strtok($_SERVER['REQUEST_URI'], '?');
          $uri_norm = rtrim($uri, '/');
          if ($uri_norm === '') { $uri_norm = '/'; }
          ?>

          <!-- Menú -->
          <ul class="nav" id="primary-nav" role="menubar">

            <?php
            // Mostrar "+ Información" en todas menos en la home
            if ($uri_norm !== '/') {
              echo '

              <li role="none">
                <a href="/index.php" role="menuitem" itemprop="url"><span itemprop="name">Inicio</span></a>
              </li>

              <li role="none"><a href="/prospectos" role="menuitem" itemprop="url"><span itemprop="name"><strong>+ Informacion</strong></span></a></li>
              ';
            }
            ?>
            <li role="none">
              <a href="/blog" role="menuitem" itemprop="url"><span itemprop="name">Blog</span></a>
            </li>
            <li role="none">
              <a href="/nft" role="menuitem" itemprop="url"><span itemprop="name">NFT</span></a>
            </li>
            <?php
            if ($uri_norm === '/') {
              echo '
              <li role="none" class="nav-cta"><a href="#Clientes" role="menuitem" itemprop="url"><span itemprop="name">Cotizar</span></a></li>
              <li role="none" class="nav-secondary"><a href="/ActualizacionDatos" role="menuitem" itemprop="url"><span itemprop="name">Mi cuenta</span></a></li>
              ';
            } else {
              echo '
              <li role="none" class="nav-cta"><a href="/funerarias.php" role="menuitem" itemprop="url"><span itemprop="name">Registro funerarias</span></a></li>
              ';
            }
            ?>

          </ul>

          <!-- Botón hamburguesa -->
          <button class="menu-trigger" type="button" aria-label="Abrir menu" aria-controls="primary-nav" aria-expanded="false">
            <span aria-hidden="true"></span>
          </button>
        </nav>
      </div>
    </div>
  </div>
</header>
