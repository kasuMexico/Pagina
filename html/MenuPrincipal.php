<?php 
//Revisado por Jose Carlos Cabrera Monroy 1 de Noviembre del 2025
// html/MenuPrincipal.php 
?>
<header class="header-area header-sticky">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <nav class="main-nav" style="color:black;" itemscope itemtype="https://schema.org/SiteNavigationElement" aria-label="Navegación principal">
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
          <ul class="nav" role="menubar">
            <?php
            // Mostrar "+ Información" en todas menos en la home
            if ($uri_norm !== '/') {
              echo '<li role="none"><a style="color:black;" href="/prospectos" class="comprar" role="menuitem" itemprop="url"><span itemprop="name"><strong>+ Información</strong></span></a></li>';
            }
            ?>
            <li role="none">
              <a style="color:black;" href="/blog" role="menuitem" itemprop="url"><span itemprop="name">Blog</span></a>
            </li>
            <li role="none">
              <a style="color:black;" href="/nft" role="menuitem" itemprop="url"><span itemprop="name">NFT</span></a>
            </li>
            <?php
            if ($uri_norm !== '/fundacion') {
              echo '<li role="none"><a style="color:black;" href="/fundacion" role="menuitem" itemprop="url"><span itemprop="name">Fundación</span></a></li>';
            }
            if ($uri_norm === '/') {
              echo '
              <li role="none"><a style="font-weight: bold; color: purple;" href="#Clientes" class="comprar" role="menuitem" itemprop="url"><span itemprop="name">Comprar</span></a></li>
              <li role="none"><a style="font-weight: bold; color: orange;" href="/ActualizacionDatos" class="comprar" role="menuitem" itemprop="url"><span itemprop="name">Mi Cuenta</span></a></li>
              ';
            }
            ?>

          </ul>

          <!-- Botón hamburguesa -->
          <a class="menu-trigger" aria-label="Abrir menú" role="button"><span>Menú</span></a>
        </nav>
      </div>
    </div>
  </div>
</header>