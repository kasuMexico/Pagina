<?php
// Estado visual y de clic por botón
$a1 = $a2 = $a3 = $a4 = $a5 = $a6 = $a7 = '';
$d1 = $d2 = $d3 = $d4 = $d5 = $d6 = $d7 = false;

$Niv = $basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
$CoMn = basename($_SERVER['PHP_SELF']); // nombre del archivo actual

$coloHover = "#F6CFFC";
$imgDisabled = "background: {$coloHover};";
$anchorDisabled = 'style="pointer-events:none; cursor:default;" aria-current="page" tabindex="-1"';

switch ($CoMn) {
  case 'Pwa_Principal.php':        $a1 = $imgDisabled; $d1 = true; break;
  case 'Pwa_Prospectos.php':       $a2 = $imgDisabled; $d2 = true; break;
  case 'Pwa_Registro_Pagos.php':   $a3 = $imgDisabled; $d3 = true; break;
  case 'Pwa_Clientes.php':         $a4 = $imgDisabled; $d4 = true; break;
  case 'Mesa_Herramientas.php':    $a5 = $imgDisabled; $d5 = true; break;
  case 'Pwa_Analisis_Ventas.php':  $a6 = $imgDisabled; $d6 = true; break;
  case 'Pwa_Sociales.php':         $a7 = $imgDisabled; $d7 = true; break;
}

// Helper: imprime <a> activo o deshabilitado
function btn($href, $img, $imgStyle, $disabled, $anchorDisabled) {
  if ($disabled) {
    echo '<a class="BtnMenu" '.$anchorDisabled.'><img src="'.$img.'" style="'.$imgStyle.'"></a>';
  } else {
    echo '<a class="BtnMenu" href="'.$href.'"><img src="'.$img.'" style="'.$imgStyle.'"></a>';
  }
}
?>
<div class="MenuPrincipal">
  <?php
    btn('Pwa_Principal.php',      'assets/img/FlorKasu.png',     $a1, $d1, $anchorDisabled);
    
    if ($Niv == 1) {
      btn('Pwa_Analisis_Ventas.php','assets/img/estadistico.png', $a6, $d6, $anchorDisabled);
    }
    if ($Niv != 2) {
      btn('Pwa_Clientes.php',     'assets/img/usuario_a.png',    $a4, $d4, $anchorDisabled);
    }

    if ($Niv != 5 && $Niv != 3 && $Niv != 2) {
      btn('Pwa_Prospectos.php',   'assets/img/prospectos.png',   $a2, $d2, $anchorDisabled);
    }

    if ($Niv != 7) {
      btn('Pwa_Registro_Pagos.php','assets/img/cobrando.png',    $a3, $d3, $anchorDisabled);
    }

    if ($Niv == 7 || $Niv == 6 || $Niv == 1) {
      btn('Pwa_Sociales.php',     'assets/img/post_fb.png',      $a7, $d7, $anchorDisabled);
    }

    btn('Mesa_Herramientas.php',  'assets/img/herramientas.png', $a5, $d5, $anchorDisabled);
  ?>
</div>
