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

function comisionPorProducto(mysqli $db, string $producto, float $porcentaje): float {
  $gen = (float)($GLOBALS['basicas']->BuscarCampos($db, 'comision', 'Productos', 'Producto', $producto) ?? 0);
  return $gen * ($porcentaje / 100.0);
}

/* ==========================================================================================
 * BLOQUE: Identidad de referidos y share URLs
 * ========================================================================================== */

// IdFirma del vendedor definida desde la venta
$IdFirma = (string)($venta['IdFirma'] ?? '');
// Fallback seguro si por alguna razón no viene en $venta
if ($IdFirma === '') {
  $IdFirma = (string)($_SESSION['Vendedor'] ?? '');
}
// Variable usada en payloads anteriores
$Vendedor = $IdFirma;

// Helper para construir URLs de share con payload y UTM
function buildShare(array $reg, string $idFirma): array {
  $payload = (string)($reg['Id'] ?? '') . '|' . $idFirma; // IdPost|IdFirma
  $dest    = 'https://kasu.com.mx/constructor.php?datafb=' . urlencode(base64_encode($payload));
  // UTM para analítica
  $destUtm = $dest
    . '&utm_source=' . rawurlencode((string)($reg['Red'] ?? ''))
    . '&utm_medium=social_share'
    . '&utm_campaign=referral'
    . '&utm_content=' . rawurlencode((string)($reg['Id'] ?? ''));

  return [
    'dest' => $destUtm, // URL canónica a compartir con Web Share API
    'fb'   => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($destUtm),
    'x'    => 'https://twitter.com/intent/tweet?text='
              . urlencode((string)($reg['DesA'] ?? ''))
              . '&url=' . urlencode($destUtm),
  ];
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
            $share = buildShare($Reg, $IdFirma);

            $Comis = comisionPorProducto($mysqli, (string)$Reg['Producto'], $PorCom);
            ?>
            <div class="col-12 col-md-6 mb-4"><!-- 1 por fila en móvil, 2 en >=md -->
              <div class="card h-100">
                <div class="card-body">
                  <a class="ContCupon d-block mb-2" href="javascript:void(0);"
                     onclick="shareSmart('<?= $share['dest'] ?>','<?= htmlspecialchars((string)$Reg['TitA'], ENT_QUOTES) ?>','<?= htmlspecialchars((string)$Reg['DesA'], ENT_QUOTES) ?>','<?= $share['fb'] ?>'); return false;">
                    <img class="img-fluid w-100"
                         src="https://kasu.com.mx/assets/images/cupones/<?= htmlspecialchars((string)$Reg['Img'], ENT_QUOTES) ?>"
                         alt="">
                  </a>

                  <a class="BtnSocial d-inline-block mb-2" href="javascript:void(0);"
                     onclick="shareSmart('<?= $share['dest'] ?>','<?= htmlspecialchars((string)$Reg['TitA'], ENT_QUOTES) ?>','<?= htmlspecialchars((string)$Reg['DesA'], ENT_QUOTES) ?>','<?= $share['fb'] ?>'); return false;">
                    <img src="/login/assets/img/sociales/<?= htmlspecialchars((string)$Reg['Red'], ENT_QUOTES) ?>.png"
                         alt="Compartir cupones" style="width: 50px;">
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
                  <a class="ContCupon d-block mb-2" href="javascript:void(0);"
                     onclick="shareSmart('<?= $share['dest'] ?>','<?= htmlspecialchars((string)$Reg['TitA'], ENT_QUOTES) ?>','<?= htmlspecialchars((string)$Reg['DesA'], ENT_QUOTES) ?>','<?= $share['fb'] ?>'); return false;">
                    <img class="img-fluid w-100"
                         src="<?= htmlspecialchars((string)$Reg['Img'], ENT_QUOTES) ?>" alt="">
                  </a>

                  <a class="BtnSocial d-inline-block mb-2" href="javascript:void(0);"
                     onclick="shareSmart('<?= $share['dest'] ?>','<?= htmlspecialchars((string)$Reg['TitA'], ENT_QUOTES) ?>','<?= htmlspecialchars((string)$Reg['DesA'], ENT_QUOTES) ?>','<?= $share['fb'] ?>'); return false;">
                    <img src="/login/assets/img/sociales/<?= htmlspecialchars((string)$Reg['Red'], ENT_QUOTES) ?>.png"
                         alt="Archivo a compartir" style="width: 50px;">
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

<!-- Web Share API primero; fallback sharer de Facebook -->
<script>
function shareSmart(url, titulo, texto, fallbackFb){
  try{
    if (navigator.share) {
      navigator.share({ title: titulo || '', text: texto || '', url: url })
        .catch(function(){ window.open(fallbackFb, '_blank', 'noopener,noreferrer'); });
    } else {
      window.open(fallbackFb, '_blank', 'noopener,noreferrer');
    }
  } catch(_){
    window.open(fallbackFb, '_blank', 'noopener,noreferrer');
  }
}
</script>