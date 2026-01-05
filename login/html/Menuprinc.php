<?php
/********************************************************************************************
 * Qué hace: Renderiza el menú inferior de la PWA con iconos activos/inactivos por página.
 * Fecha: 06/12/2025
 * Revisado por: JCCM
 * Archivo: Menuprinc.php
 * Cambios:
 *  - Si la página actual inicia con "Mesa_": solo mostrar "Pwa_Principal.php" y opciones Mesa_*.
 *  - Agregar Mesa_Marketing.php y Mesa_Finanzas.php con iconos color/B&N.
 *  - Ocultar Marketing y Finanzas cuando la página actual inicia con "Pwa_".
 *  - Marketing y Finanzas visibles solo para niveles 1 y 2.
 *  - Modernizado a estilo iOS (bottom nav tipo dock, labels bajo icono, blur suave).
 ********************************************************************************************/

declare(strict_types=1);

/* ==========================================================================================
 * BLOQUE: Variables de contexto
 * ========================================================================================== */
$Niv  = (int)($basicas->BuscarCampos($mysqli, 'Nivel', 'Empleados', 'IdUsuario', $_SESSION['Vendedor'] ?? '') ?? 0);
$CoMn = basename((string)($_SERVER['PHP_SELF'] ?? ''));

// Ancla deshabilitada cuando es la página activa
$anchorDisabled = 'style="pointer-events:none;cursor:default" aria-current="page" tabindex="-1"';

/* ==========================================================================================
 * FUNCIÓN: btnIcon — versión con layout iOS (icono + label)
 * ========================================================================================== */
function btnIcon(
  string $href,
  string $imgBW,
  string $imgColor,
  bool   $isActive,
  string $anchorDisabled,
  string $alt = ''
): void {
  $img     = $isActive ? $imgColor : $imgBW;
  $altSafe = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');
  $imgSafe = htmlspecialchars($img, ENT_QUOTES, 'UTF-8');

  $classes = 'BtnMenu kasu-nav-item';
  if ($isActive) {
    $classes .= ' is-active';
  }

  if ($isActive) {
    echo '<a class="' . $classes . '" ' . $anchorDisabled . '>';
  } else {
    $hrefSafe = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');
    echo '<a class="' . $classes . '" href="' . $hrefSafe . '">';
  }

  echo    '<span class="kasu-nav-icon">'
        .   '<img src="' . $imgSafe . '" alt="' . $altSafe . '">'
        . '</span>'
        . '<span class="kasu-nav-label">' . $altSafe . '</span>'
        . '</a>';
}

/* ==========================================================================================
 * BLOQUE: Flags de página activa
 * ========================================================================================== */
$isPpal   = ($CoMn === 'Pwa_Principal.php');
$isPros   = ($CoMn === 'Pwa_Prospectos.php');
$isCob    = ($CoMn === 'Pwa_Registro_Pagos.php');
$isCltes  = ($CoMn === 'Pwa_Clientes.php');
$isTools  = ($CoMn === 'Mesa_Herramientas.php');
$isAnalis = ($CoMn === 'Pwa_Analisis_Ventas.php');
$isSocial = ($CoMn === 'Pwa_Sociales.php');

$isMesaMarketing = ($CoMn === 'Mesa_Marketing.php');
$isMesaFinanzas  = ($CoMn === 'Mesa_Finanzas.php');

/* Grupos */
$isMesaGroup = (strncmp($CoMn, 'Mesa_', 5) === 0);
$isPwaGroup  = (strncmp($CoMn, 'Pwa_', 4) === 0);

/* Visibilidad por nivel para Marketing/Finanzas */
$canSeeMesaMF = ($Niv === 1 || $Niv === 2);
?>
<nav class="MenuPrincipal kasu-bottom-nav" role="navigation" aria-label="Navegación principal">
  <?php
    /* ======================================================================================
     * MODO "Mesa_*": Solo mostrar Principal y las páginas del grupo Mesa_
     * ====================================================================================== */
    if ($isMesaGroup) {

      // Inicio
      btnIcon(
        'Pwa_Principal.php',
        'assets/img/Iconos_menu/kasu_black.png',
        'assets/img/kasu_logo.jpeg',
        $isPpal,
        $anchorDisabled,
      );

      // Mesa_Marketing (solo niveles 1 y 2)
      if ($canSeeMesaMF) {
        btnIcon(
          'Mesa_Marketing.php',
          'assets/img/Iconos_menu/marketing_black.png',
          'assets/img/Iconos_menu/marketing.png',
          $isMesaMarketing,
          $anchorDisabled,
        );
      }

      // Mesa_Finanzas (solo niveles 1 y 2)
      if ($canSeeMesaMF) {
        btnIcon(
          'Mesa_Finanzas.php',
          'assets/img/Iconos_menu/Finanzas_black.png',
          'assets/img/Iconos_menu/Finanzas.png',
          $isMesaFinanzas,
          $anchorDisabled,
        );
      }

      // Mesa_Herramientas (siempre visible)
      btnIcon(
        'Mesa_Herramientas.php',
        'assets/img/Iconos_menu/heramientas_black.png',
        'assets/img/Iconos_menu/ajustes.png',
        $isTools,
        $anchorDisabled,
      );
    } else {
      /* ================================================================================
       * MODO normal
       * - Si la página actual inicia con "Pwa_": ocultar Marketing y Finanzas.
       * - Herramientas siempre visible.
       * ================================================================================ */

      // Inicio
      btnIcon(
        'Pwa_Principal.php',
        'assets/img/Iconos_menu/kasu_black.png',
        'assets/img/kasu_logo.jpeg',
        $isPpal,
        $anchorDisabled,
      );

      // Análisis (solo Nivel 1)
      if ($Niv === 1) {
        btnIcon(
          'Pwa_Analisis_Ventas.php',
          'assets/img/Iconos_menu/analisis_black.png',
          'assets/img/Iconos_menu/analisis.png',
          $isAnalis,
          $anchorDisabled,
        );
      }

      // Clientes (todos excepto Nivel 2)
      if ($Niv !== 2) {
        btnIcon(
          'Pwa_Clientes.php',
          'assets/img/Iconos_menu/usuario_black.png',
          'assets/img/Iconos_menu/usuario.png',
          $isCltes,
          $anchorDisabled,
        );
      }

      // Prospectos (no 5,3,2)
      if ($Niv !== 5 && $Niv !== 3 && $Niv !== 2) {
        btnIcon(
          'Pwa_Prospectos.php',
          'assets/img/Iconos_menu/prospectos_black.png',
          'assets/img/Iconos_menu/prospectos.png',
          $isPros,
          $anchorDisabled,
        );
      }

      // Cobranza/Pagos (no 7)
      if ($Niv !== 7) {
        btnIcon(
          'Pwa_Registro_Pagos.php',
          'assets/img/Iconos_menu/Cobrar_black.png',
          'assets/img/Iconos_menu/cobranza.png',
          $isCob,
          $anchorDisabled,
        );
      }

      // Sociales (niveles 7, 6, 1)
      if ($Niv === 7 || $Niv === 6 || $Niv === 1) {
        btnIcon(
          'Pwa_Sociales.php',
          'assets/img/Iconos_menu/facebook_black.png',
          'assets/img/sociales/facebook.png',
          $isSocial,
          $anchorDisabled,
        );
      }

      // Marketing y Finanzas: ocultar si estamos en una Pwa_*
      if (!$isPwaGroup && $canSeeMesaMF) {
        // Mesa_Marketing
        btnIcon(
          'Mesa_Marketing.php',
          'assets/img/Iconos_menu/marketing_black.png',
          'assets/img/Iconos_menu/marketing.png',
          $isMesaMarketing,
          $anchorDisabled,
        );

        // Mesa_Finanzas
        btnIcon(
          'Mesa_Finanzas.php',
          'assets/img/Iconos_menu/Finanzas_black.png',
          'assets/img/Iconos_menu/Finanzas.png',
          $isMesaFinanzas,
          $anchorDisabled,
        );
      }

      // Herramientas (siempre)
      btnIcon(
        'Mesa_Herramientas.php',
        'assets/img/Iconos_menu/heramientas_black.png',
        'assets/img/Iconos_menu/ajustes.png',
        $isTools,
        $anchorDisabled,
      );
    }
  ?>
</nav>
<style>
  /* Barra inferior estilo iOS / dock */
  #Menu .kasu-bottom-nav{
    display:flex;
    justify-content:space-around;
    /* levantamos los iconos respecto al home indicator */
    align-items:flex-start;
    backdrop-filter:blur(18px);
    -webkit-backdrop-filter:blur(18px);
    background:rgba(248,250,252,.94);
    border-top:1px solid rgba(148,163,184,.45);
    box-shadow:0 -12px 35px rgba(15,23,42,.18);

    /* un poco más de padding arriba y dejamos el espacio de la safe-area abajo */
    padding-top:8px;
    padding-bottom:calc(4px + max(var(--safe-b, 0px), 8px));
  }

  #Menu .kasu-bottom-nav .kasu-nav-item{
    flex:1;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:flex-start;
    gap:4px;
    padding:2px 0 0;
    text-decoration:none;
    color:#6b7280;
    font-size:.72rem;
  }

  #Menu .kasu-bottom-nav .kasu-nav-item .kasu-nav-icon{
    display:flex;
    align-items:center;
    justify-content:center;
    /* icon container un poco más grande */
    width:56px;
    height:40px;
    border-radius:999px;
    transition:background .16s ease-out, box-shadow .16s ease-out, transform .1s ease-out;
  }

  #Menu .kasu-bottom-nav .kasu-nav-item img{
    /* iconos un poco más grandes */
    width:var(--icon);
    height:var(--icon);
    max-width:32px;
    max-height:32px;
    border-radius:12px;
    display:block;
  }

  #Menu .kasu-bottom-nav .kasu-nav-item .kasu-nav-label{
    line-height:1.1;
    max-width:72px;
    text-align:center;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
  }

  /* Estado activo tipo iOS */
  #Menu .kasu-bottom-nav .kasu-nav-item.is-active{
    color:#0f6ef0;
    font-weight:600;
  }

  #Menu .kasu-bottom-nav .kasu-nav-item.is-active .kasu-nav-icon{
    background:rgba(15,111,240,.12);
    box-shadow:0 8px 18px rgba(15,111,240,.45);
    transform:translateY(-1px);
  }

  /* Hover suave en escritorio (no afecta móvil) */
  @media (hover:hover){
    #Menu .kasu-bottom-nav .kasu-nav-item:not(.is-active):hover .kasu-nav-icon{
      background:rgba(148,163,184,.12);
    }
  }

  @media (max-width:360px){
    #Menu .kasu-bottom-nav .kasu-nav-item img{
      max-width:28px;
      max-height:28px;
    }
    #Menu .kasu-bottom-nav .kasu-nav-item .kasu-nav-label{
      max-width:64px;
    }
  }
</style>

<?php
if (!defined('KASU_PWA_INSTALL_LOADED')) {
  define('KASU_PWA_INSTALL_LOADED', true);
  echo '<script defer src="/login/Javascript/install.js"></script>';
}
if (!defined('KASU_PWA_SWIPE_LOADED')) {
  define('KASU_PWA_SWIPE_LOADED', true);
  echo '<script defer src="/login/Javascript/swipe-nav.js"></script>';
}
if (!defined('KASU_MESA_TABLE_LOADED')) {
  define('KASU_MESA_TABLE_LOADED', true);
  echo '<script defer src="/login/Javascript/mesa-table.js"></script>';
}
?>
