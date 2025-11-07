<?php
/********************************************************************************************
 * Qué hace: Renderiza el menú inferior de la PWA con iconos activos/inactivos por página.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

/* ==========================================================================================
 * BLOQUE: Variables de contexto
 * Qué hace: Obtiene nivel del usuario y el nombre del archivo actual para marcar activos.
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
$Niv  = (int)($basicas->BuscarCampos($mysqli, 'Nivel', 'Empleados', 'IdUsuario', $_SESSION['Vendedor'] ?? '') ?? 0);
$CoMn = basename((string)($_SERVER['PHP_SELF'] ?? ''));

// Ancla deshabilitada cuando es la página activa
$anchorDisabled = 'style="pointer-events:none;cursor:default" aria-current="page" tabindex="-1"';

/* ==========================================================================================
 * FUNCIÓN: btnIcon
 * Qué hace: Imprime un <a> con el ícono en color si está activa la vista, B/N si no.
 * Parámetros:
 *   - string $href: URL de destino
 *   - string $imgBW: ruta icono en B/N
 *   - string $imgColor: ruta icono en color
 *   - bool   $isActive: indica si la opción corresponde a la página actual
 *   - string $anchorDisabled: atributos para deshabilitar el <a> activo
 *   - string $alt: texto alternativo del icono
 * Fecha: 05/11/2025 — Revisado por: JCCM
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
 * Qué hace: Determina qué botón se marca como activo.
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
$isPpal   = ($CoMn === 'Pwa_Principal.php');
$isPros   = ($CoMn === 'Pwa_Prospectos.php');
$isCob    = ($CoMn === 'Pwa_Registro_Pagos.php');
$isCltes  = ($CoMn === 'Pwa_Clientes.php');
$isTools  = ($CoMn === 'Mesa_Herramientas.php');
$isAnalis = ($CoMn === 'Pwa_Analisis_Ventas.php');
$isSocial = ($CoMn === 'Pwa_Sociales.php');
?>
<div class="MenuPrincipal">
  <?php
    // Inicio
    btnIcon(
      'Pwa_Principal.php',
      'assets/img/Iconos_menu/kasu_black.png',
      'assets/img/kasu_logo.jpeg', // versión a color
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
        'assets/img/Iconos_menu/cobranza.png', // versión a color
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
        'assets/img/sociales/facebook.png', // versión a color
        $isSocial,
        $anchorDisabled,
        'Social'
      );
    }

    // Herramientas
    btnIcon(
      'Mesa_Herramientas.php',
      'assets/img/Iconos_menu/heramientas_black.png',
      'assets/img/Iconos_menu/ajustes.png', // versión a color
      $isTools,
      $anchorDisabled,
      'Herramientas'
    );
  ?>
</div>
