<?php
/********************************************************************************************
 * _tarjetas_html.php – HTML de las tarjetas (cupones + artículos).
 * Usar con include (NO require_once) para que se renderice en cada sección.
 * Requiere: TarjetasCompartir.php cargado previamente ($cupones, $articulos, $PorCom, $IdFirma).
 ********************************************************************************************/

if (!isset($cupones, $articulos, $PorCom, $IdFirma, $mysqli)) return;

if (!$cupones && !$articulos): ?>
  <div class="col-12"><p class="text-muted text-center">No hay tarjetas promocionales activas en este momento.</p></div>
<?php else: ?>

  <?php foreach ($cupones as $Reg):
    $share = buildShare($Reg, $IdFirma);
    $Comis = comisionPorProducto($mysqli, (string)$Reg['Producto'], $PorCom);
  ?>
    <div class="col-12 col-sm-6 col-md-4 mb-3">
      <div class="card h-100 tarjeta-kasu">
        <div class="card-body p-2">
          <img class="img-fluid w-100"
               src="https://kasu.com.mx/assets/images/cupones/<?= htmlspecialchars((string)$Reg['Img'], ENT_QUOTES) ?>"
               alt="Cupón" loading="lazy" style="border-radius:8px;">

          <div class="share-row">
            <a href="<?= htmlspecialchars($share['fb'], ENT_QUOTES) ?>" target="_blank" rel="noopener" aria-label="Facebook">
              <img class="ico-social" src="/login/assets/img/sociales/facebook.png" alt="FB">
            </a>
            <a href="<?= htmlspecialchars($share['x'], ENT_QUOTES) ?>" target="_blank" rel="noopener" aria-label="X">
              <img class="ico-social" src="/login/assets/img/sociales/x.png" alt="X">
            </a>
            <a href="<?= htmlspecialchars($share['li'], ENT_QUOTES) ?>" target="_blank" rel="noopener" aria-label="LinkedIn">
              <img class="ico-social" src="/login/assets/img/sociales/LinkedIn.png" alt="IN">
            </a>
            <a href="#" onclick="shareInstagram('<?= htmlspecialchars($share['dest'], ENT_QUOTES) ?>');return false;" aria-label="Instagram">
              <img class="ico-social" src="/login/assets/img/sociales/instagram.png" alt="IG">
            </a>
          </div>
          <hr class="my-1">
          <div class="ContCupon px-1">
            <h2 class="h6 mb-1"><?= htmlspecialchars((string)$Reg['TitA'], ENT_QUOTES) ?></h2>
            <h3 class="mb-0 small text-muted"><?= htmlspecialchars((string)$Reg['DesA'], ENT_QUOTES) ?></h3>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <?php foreach ($articulos as $Reg):
    $share = buildShare($Reg, $IdFirma);
    $Comis = comisionPorProducto($mysqli, (string)$Reg['Producto'], $PorCom);
    if (($Reg['Producto'] ?? '') === 'Universidad') {
      $Comis /= 2500;
    } elseif (($Reg['Producto'] ?? '') === 'Retiro') {
      $Comis /= 1000;
    } else {
      $Comis /= 100;
    }
  ?>
    <div class="col-12 col-sm-6 col-md-4 mb-3">
      <div class="card h-100 tarjeta-kasu">
        <div class="card-body p-2">
          <img class="img-fluid w-100"
               src="<?= htmlspecialchars((string)$Reg['Img'], ENT_QUOTES) ?>" alt="Artículo" loading="lazy" style="border-radius:8px;">

          <div class="share-row">
            <a href="<?= htmlspecialchars($share['fb'], ENT_QUOTES) ?>" target="_blank" rel="noopener" aria-label="Facebook">
              <img class="ico-social" src="/login/assets/img/sociales/facebook.png" alt="FB">
            </a>
            <a href="<?= htmlspecialchars($share['x'], ENT_QUOTES) ?>" target="_blank" rel="noopener" aria-label="X">
              <img class="ico-social" src="/login/assets/img/sociales/x.png" alt="X">
            </a>
            <a href="<?= htmlspecialchars($share['li'], ENT_QUOTES) ?>" target="_blank" rel="noopener" aria-label="LinkedIn">
              <img class="ico-social" src="/login/assets/img/sociales/LinkedIn.png" alt="IN">
            </a>
            <a href="#" onclick="shareInstagram('<?= htmlspecialchars($share['dest'], ENT_QUOTES) ?>');return false;" aria-label="Instagram">
              <img class="ico-social" src="/login/assets/img/sociales/instagram.png" alt="IG">
            </a>
          </div>
          <hr class="my-1">
          <div class="ContCupon px-1">
            <h2 class="h6">Lectura $<?= number_format($Comis, 2) ?></h2>
            <h3 class="h6 mb-1 small"><?= htmlspecialchars((string)$Reg['TitA'], ENT_QUOTES) ?></h3>
            <p class="mb-1 small text-muted"><?= htmlspecialchars((string)$Reg['DesA'], ENT_QUOTES) ?></p>
            <small style="font-size:10px;color:#aaa;">*Comisión por usuario único, por día</small>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

<?php endif; ?>
