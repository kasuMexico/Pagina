<?php
/********************************************************************************************
 * Qué hace: Renderiza el menú inferior de la PWA con iconos activos/inactivos por página.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 * Cambios:
 *  - Si la página actual inicia con "Mesa_": solo mostrar "Pwa_Principal.php" y opciones Mesa_*.
 *  - Agregar Mesa_Marketing.php y Mesa_Finanzas.php con iconos color/B&N.
 *  - Ocultar Marketing y Finanzas cuando la página actual inicia con "Pwa_".
 *  - Marketing y Finanzas visibles solo para niveles 1 y 2.
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
 * FUNCIÓN: btnIcon
 * ========================================================================================== */
function btnIcon(
  string $href,
  string $imgBW,
  string $imgColor,
  bool   $isActive,
  string $anchorDisabled,
  string $alt = ''
): void {
  $img = $isActive ? $imgColor : $imgBW;
  $altSafe = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');
  $imgSafe = htmlspecialchars($img, ENT_QUOTES, 'UTF-8');

  if ($isActive) {
    echo '<a class="BtnMenu" ' . $anchorDisabled . '><img src="' . $imgSafe . '" alt="' . $altSafe . '"></a>';
  } else {
    $hrefSafe = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');
    echo '<a class="BtnMenu" href="' . $hrefSafe . '"><img src="' . $imgSafe . '" alt="' . $altSafe . '"></a>';
  }
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
<div class="MenuPrincipal">
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
        'Inicio'
      );

      // Mesa_Herramientas (siempre visible)
      btnIcon(
        'Mesa_Herramientas.php',
        'assets/img/Iconos_menu/heramientas_black.png',
        'assets/img/Iconos_menu/ajustes.png',
        $isTools,
        $anchorDisabled,
        'Herramientas'
      );

      // Mesa_Marketing (solo niveles 1 y 2)
      if ($canSeeMesaMF) {
        btnIcon(
          'Mesa_Marketing.php',
          'assets/img/Iconos_menu/marketing_black.png',
          'assets/img/Iconos_menu/marketing.png',
          $isMesaMarketing,
          $anchorDisabled,
          'Marketing'
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
          'Finanzas'
        );
      }

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
        'Inicio'
      );

      // Análisis (solo Nivel 1)
      if ($Niv === 1) {
        btnIcon(
          'Pwa_Analisis_Ventas.php',
          'assets/img/Iconos_menu/analisis_black.png',
          'assets/img/Iconos_menu/analisis.png',
          $isAnalis,
          $anchorDisabled,
          'Análisis'
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
          'Clientes'
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
          'Prospectos'
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
          'Cobranza'
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
          'Social'
        );
      }

      // Herramientas (siempre)
      btnIcon(
        'Mesa_Herramientas.php',
        'assets/img/Iconos_menu/heramientas_black.png',
        'assets/img/Iconos_menu/ajustes.png',
        $isTools,
        $anchorDisabled,
        'Herramientas'
      );

      // Marketing y Finanzas: ocultar si estamos en una Pwa_*
      if (!$isPwaGroup && $canSeeMesaMF) {
        // Mesa_Marketing
        btnIcon(
          'Mesa_Marketing.php',
          'assets/img/Iconos_menu/marketing_black.png',
          'assets/img/Iconos_menu/marketing.png',
          $isMesaMarketing,
          $anchorDisabled,
          'Marketing'
        );

        // Mesa_Finanzas
        btnIcon(
          'Mesa_Finanzas.php',
          'assets/img/Iconos_menu/Finanzas_black.png',
          'assets/img/Iconos_menu/Finanzas.png',
          $isMesaFinanzas,
          $anchorDisabled,
          'Finanzas'
        );
      }
    }
  ?>
</div>