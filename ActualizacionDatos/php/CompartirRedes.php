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
 * ========================================================================================== */
$PorCom = (float)($basicas->BuscarCampos($mysqli, 'N' . 7, 'Comision', 'Id', 2) ?? 0);

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

/** Comisión por producto mostrado */
function comisionPorProducto(mysqli $db, string $producto, float $porcentaje): float {
  $gen = (float)($GLOBALS['basicas']->BuscarCampos($db, 'comision', 'Productos', 'Producto', $producto) ?? 0);
  return $gen * ($porcentaje / 100.0);
}

/* ==========================================================================================
 * BLOQUE: Identidad de referidos y share URLs
 * ========================================================================================== */
$IdFirma = (string)($venta['IdFIrma'] ?? ($_SESSION['Vendedor'] ?? ''));

/** URLs de share por red */
function buildShare(array $reg, string $idFirma): array {
  $payload = (string)($reg['Id'] ?? '') . '|' . $idFirma;
  $dest = 'https://kasu.com.mx/constructor.php?datafb=' . base64_encode($payload);
  $tit  = (string)($reg['TitA'] ?? '');
  $txt  = (string)($reg['DesA'] ?? '');

  return [
    'dest' => $dest,
    'fb'   => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($dest),
    'x'    => 'https://twitter.com/intent/tweet?text=' . rawurlencode(trim("$tit $txt"))
             . '&url=' . rawurlencode($dest),
    'li'   => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($dest),
  ];
}
?>
<style>
  .share-row{display:flex;gap:.5rem;align-items:center;margin:.5rem 0}
  .ico-social{width:36px;height:36px;object-fit:contain;vertical-align:middle}
  .thumb-social{width:28px;height:28px;border-radius:50%;object-fit:cover}
  .x-wrap{display:flex;align-items:center;gap:.35rem}
</style>

<section id="sec-sociales" class="section">
  <main class="page-content">
    <div class="container">
      <div class="row">
        <?php
        /* ======= Cupones de Venta (6 items) ======= */
        $b = (int)$basicas->Max1Dat($mysqli, 'Id', 'PostSociales', 'Tipo', 'Vta');
        for ($a = 1; $a <= 6; $a++) {
          $c = rand(1, max(1, $b));
          $Reg = getPostById($mysqli, $c, 'Vta');
          if ($Reg) {
            $share = buildShare($Reg, $IdFirma);
            $Comis = comisionPorProducto($mysqli, (string)$Reg['Producto'], $PorCom);
            ?>
            <div class="col-12 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <!-- Imagen principal: ya NO es link -->
                  <img class="img-fluid w-100"
                       src="https://kasu.com.mx/assets/images/cupones/<?= htmlspecialchars((string)$Reg['Img'], ENT_QUOTES) ?>"
                       alt="Cupón">

                  <!-- Íconos de share -->
                  <div class="share-row">
                    <a href="<?= htmlspecialchars($share['fb'], ENT_QUOTES) ?>" target="_blank" rel="noopener" aria-label="Compartir en Facebook">
                      <img class="ico-social" src="/login/assets/img/sociales/facebook.png" alt="Facebook">
                    </a>

                    <a href="<?= htmlspecialchars($share['x'], ENT_QUOTES) ?>" target="_blank" rel="noopener" aria-label="Compartir en X">
                      <img class="ico-social" src="/login/assets/img/sociales/x.png" alt="X">
                    </a>

                    <a href="<?= htmlspecialchars($share['li'], ENT_QUOTES) ?>" target="_blank" rel="noopener" aria-label="Compartir en LinkedIn">
                      <img class="ico-social" src="/login/assets/img/sociales/LinkedIn.png" alt="LinkedIn">
                    </a>

                    <a href="#" onclick="shareInstagram('<?= htmlspecialchars($share['dest'], ENT_QUOTES) ?>');return false;" aria-label="Compartir en Instagram">
                      <img class="ico-social" src="/login/assets/img/sociales/instagram.png" alt="Instagram">
                    </a>
                  </div>
                  <hr>
                  <br>
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
            $share = buildShare($Reg, $IdFirma);

            $Comis = comisionPorProducto($mysqli, (string)$Reg['Producto'], $PorCom);
            // Ajuste por producto para artículos
            if (($Reg['Producto'] ?? '') === 'Universidad') {
              $Comis /= 2500;
            } elseif (($Reg['Producto'] ?? '') === 'Retiro') {
              $Comis /= 1000;
            } else {
              $Comis /= 100;
            }
            ?>
            <div class="col-12 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <!-- Imagen principal: ya NO es link -->
                  <img class="img-fluid w-100"
                       src="<?= htmlspecialchars((string)$Reg['Img'], ENT_QUOTES) ?>" alt="Artículo">

                  <!-- Íconos de share -->
                  <div class="share-row">
                    <a href="<?= htmlspecialchars($share['fb'], ENT_QUOTES) ?>" target="_blank" rel="noopener" aria-label="Compartir en Facebook">
                      <img class="ico-social" src="/login/assets/img/sociales/facebook.png" alt="Facebook">
                    </a>

                    <a href="<?= htmlspecialchars($share['x'], ENT_QUOTES) ?>" target="_blank" rel="noopener" aria-label="Compartir en X">
                      <img class="ico-social" src="/login/assets/img/sociales/x.png" alt="X">
                    </a>

                    <a href="<?= htmlspecialchars($share['li'], ENT_QUOTES) ?>" target="_blank" rel="noopener" aria-label="Compartir en LinkedIn">
                      <img class="ico-social" src="/login/assets/img/sociales/LinkedIn.png" alt="LinkedIn">
                    </a>

                    <a href="#" onclick="shareInstagram('<?= htmlspecialchars($share['dest'], ENT_QUOTES) ?>');return false;" aria-label="Compartir en Instagram">
                      <img class="ico-social" src="/login/assets/img/sociales/instagram.png" alt="Instagram">
                    </a>
                  </div>
                  <hr>
                  <br>
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

<!-- Instagram: Web Share o copiar enlace -->
<script>
function shareInstagram(url){
  if (navigator.share){
    navigator.share({url}).catch(function(){copyUrl(url);});
  } else {
    copyUrl(url);
  }
}
function copyUrl(text){
  try {
    navigator.clipboard.writeText(text);
    alert('Enlace copiado. Abre Instagram y pégalo en tu publicación o bio.');
  } catch(e){
    prompt('Copia el enlace:', text);
  }
}
</script>