<?php
/********************************************************************************************
 * Qué hace: Renderiza “Post Sociales” para vendedores autenticados. Muestra cupones de venta
 *           y artículos compartibles en redes, calculando comisión estimada según nivel.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

/* ==========================================================================================
 * BLOQUE: Datos de usuario / comisiones
 * - $Niv: nivel del empleado
 * - $PorCom: porcentaje base de comisión según nivel (columna dinámica N{nivel} en Comision.Id=2)
 * Notas 8.2: forzamos tipos y valores por omisión para evitar notices y deprecations.
 * ========================================================================================== */
$Vendedor = (string)$_SESSION['Vendedor'];
$NivRaw   = $basicas->BuscarCampos($mysqli, 'Nivel', 'Empleados', 'IdUsuario', $Vendedor);
$Niv      = (int)($NivRaw ?? 0);
$PorCom   = (float)($basicas->BuscarCampos($mysqli, 'N' . $Niv, 'Comision', 'Id', 2) ?? 0);

/* ==========================================================================================
 * BLOQUE: Helpers de consulta local (prepared statements)
 * ========================================================================================== */
function getPostById(mysqli $db, int $id, string $tipo): ?array {
  $sql = "SELECT Id, Red, DesA, TitA, Producto, Img
          FROM PostSociales
          WHERE Id = ? AND Status = 1 AND Tipo = ?
          LIMIT 1";
  $stmt = $db->prepare($sql);
  if ($stmt === false) return null;
  $stmt->bind_param('is', $id, $tipo);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res ? $res->fetch_assoc() : null;
  $stmt->close();
  return $row ?: null;
}

function comisionPorProducto(mysqli $db, string $producto, float $porcentaje): float {
  $gen = (float)($GLOBALS['basicas']->BuscarCampos($db, 'comision', 'Productos', 'Producto', $producto) ?? 0);
  return $gen * ($porcentaje / 100.0);
}

// Cache-busting seguro para CSS si no viene de fuera
$VerCacheSafe = isset($VerCache) ? (string)$VerCache : '1';

?>
<section id="sec-sociales" class="section">
  <!-- Contenido entre barras -->
  <main class="page-content">
    <div class="container">
      <!-- Bootstrap 4: sin g-4. Espaciado con mb-4 en columnas -->
      <div class="row">

        <?php
        /* ======= Cupones de Venta (6 items) ======= */
        $b = (int)$basicas->Max1Dat($mysqli, 'Id', 'PostSociales', 'Tipo', 'Vta');
        for ($a = 1; $a <= 6; $a++) {
          $c = rand(1, max(1, $b));
          $Reg = getPostById($mysqli, $c, 'Vta');
          if ($Reg) {
            $ClArch  = $Reg['Id'] . '|' . $Vendedor;
            // Preferir https
            $baseCtor = 'https://kasu.com.mx/constructor.php?datafb=';
            $DirPrin  = ($Reg['Red'] === 'facebook')
              ? 'https://www.facebook.com/sharer.php?u=' . urlencode($baseCtor)
              : 'https://twitter.com/intent/tweet?text=' . urlencode($Reg['DesA']) . '&url=' . urlencode($baseCtor);

            $Comis = comisionPorProducto($mysqli, (string)$Reg['Producto'], $PorCom);
            ?>
            <div class="col-12 col-md-6 mb-4"><!-- 1 por fila en móvil, 2 en >=md -->
              <div class="card h-100">
                <div class="card-body">
                  <a class="ContCupon d-block mb-2" href="javascript:void(0);"
                     onclick="window.open('<?= $DirPrin . base64_encode($ClArch) ?>','ventanacompartir','toolbar=0,status=0,width=650,height=500');">
                    <img class="img-fluid w-100" src="https://kasu.com.mx/assets/images/cupones/<?= htmlspecialchars((string)$Reg['Img'], ENT_QUOTES) ?>" alt="">
                  </a>

                  <a class="BtnSocial d-inline-block mb-2" href="javascript:void(0);"
                     onclick="window.open('<?= $DirPrin . base64_encode($ClArch) ?>','ventanacompartir','toolbar=0,status=0,width=650,height=500');">
                    <img src="/login/assets/img/sociales/<?= htmlspecialchars((string)$Reg['Red'], ENT_QUOTES) ?>.png" alt="Compartir cupones" style="width: 50px;">
                  </a>

                  <div class="ContCupon">
                    <h2 class="h5">Com/Vta $<?= number_format($Comis, 2) ?></h2>
                    <h3 class="h6 mb-2"><?= htmlspecialchars((string)$Reg['TitA'], ENT_QUOTES) ?></h3>
                    <p class="mb-0"><?= htmlspecialchars((string)$Reg['DesA'], ENT_QUOTES) ?></p>
                  </div>
                </div>
              </div>
            </div>
            <?php
          }
        }

        /* ======= Artículos (4 items) ======= */
        $f     = (int)$basicas->Max1Dat($mysqli, 'Id', 'PostSociales', 'Tipo', 'Art');
        $desde = max($b + 1, 1);
        for ($g = 1; $g <= 4; $g++) {
          $d = rand($desde, max($f, $desde));
          $Reg = getPostById($mysqli, $d, 'Art');
          if ($Reg) {
            $ClArch  = $Reg['Id'] . '|' . $Vendedor;
            $baseCtor = 'https://kasu.com.mx/constructor.php?datafb=';
            $DirPrin  = ($Reg['Red'] === 'facebook')
              ? 'https://www.facebook.com/sharer.php?u=' . urlencode($baseCtor)
              : 'https://twitter.com/intent/tweet?text=' . urlencode($Reg['DesA']) . '&url=' . urlencode($baseCtor);

            $Comis = comisionPorProducto($mysqli, (string)$Reg['Producto'], $PorCom);
            // Ajuste por producto para artículos
            if ($Reg['Producto'] === 'Universidad') {
              $Comis /= 2500;
            } elseif ($Reg['Producto'] === 'Retiro') {
              $Comis /= 1000;
            } else {
              $Comis /= 100;
            }
            ?>
            <div class="col-12 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <a class="ContCupon d-block mb-2" href="javascript:void(0);"
                     onclick="window.open('<?= $DirPrin . base64_encode($ClArch) ?>','ventanacompartir','toolbar=0,status=0,width=650,height=500');">
                    <img class="img-fluid w-100" src="<?= htmlspecialchars((string)$Reg['Img'], ENT_QUOTES) ?>" alt="" >
                  </a>

                  <a class="BtnSocial d-inline-block mb-2" href="javascript:void(0);"
                     onclick="window.open('<?= $DirPrin . base64_encode($ClArch) ?>','ventanacompartir','toolbar=0,status=0,width=650,height=500');">
                    <img src="/login/assets/img/sociales/<?= htmlspecialchars((string)$Reg['Red'], ENT_QUOTES) ?>.png" alt="Archivo a compartir" style="width: 50px;">
                  </a>

                  <div class="ContCupon">
                    <h2 class="h5">Lectura $<?= number_format($Comis, 2) ?></h2>
                    <h3 class="h6 mb-2"><?= htmlspecialchars((string)$Reg['TitA'], ENT_QUOTES) ?></h3>
                    <p class="mb-1"><?= htmlspecialchars((string)$Reg['DesA'], ENT_QUOTES) ?></p>
                    <small class="text-muted">*Comisión por usuario único, por día de lectura</small>
                  </div>
                </div>
              </div>
            </div>
            <?php
          }
        }
        ?>

      </div><!-- /.row -->
      <br><br><br><br>
    </div><!-- /.container -->
  </main>
</section>