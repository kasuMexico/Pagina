<?php
/********************************************************************************************
 * MisPolizas.php – Muestra todas las pólizas contratadas por el cliente.
 * Estilo visual tipo NFT / OpenSea: tarjetas con imagen, nombre, status y datos clave.
 * Fecha: 23/07/2026
 ********************************************************************************************/
declare(strict_types=1);

/* ===== Consultar todas las pólizas del cliente por CURP ===== */
$todasLasPolizas = [];
try {
    $sqlPolizas = 'SELECT v.`Id`, v.`IdContact`, v.`Nombre`, v.`TipoServicio`, v.`IdFIrma`,
                          v.`Producto`, v.`Status`, v.`Referencia_KASU`, v.`FechaRegistro`, v.`CostoVenta`
                   FROM `Venta` v
                   INNER JOIN `Usuario` u ON u.`IdContact` = v.`IdContact`
                   WHERE u.`ClaveCurp` = ?
                   ORDER BY v.`FechaRegistro` DESC';
    $stP = $mysqli->prepare($sqlPolizas);
    $stP->bind_param('s', $curp);
    $stP->execute();
    $rsP = $stP->get_result();
    while ($rsP && $row = $rsP->fetch_assoc()) {
        $todasLasPolizas[] = $row;
    }
    $stP->close();
} catch (Throwable $e) {
    error_log('MisPolizas SQL: ' . $e->getMessage());
    $todasLasPolizas = [];
}

/* ===== Helpers para clasificar producto e imagen ===== */
function clasificarProducto(string $producto): string {
    $checkers = [
        'FUNERARIO'           => 'ProdFune',
        'OFICIAL DE SEGURIDAD'=> 'ProdPli',
        'TRANSPORTISTA'       => 'ProdTrans',
    ];
    foreach ($checkers as $label => $method) {
        if (method_exists($GLOBALS['basicas'], $method) && $GLOBALS['basicas']->$method($producto)) {
            return $label;
        }
    }
    return 'RETIRO';
}

function imagenProducto(string $tipo): string {
    $map = [
        'FUNERARIO'            => '/assets/images/Funerario_princ.png',
        'OFICIAL DE SEGURIDAD' => '/assets/images/registro/Oficiales-Seguridad.png',
        'TRANSPORTISTA'        => '/assets/images/registro/Registro-Servicio.png',
        'RETIRO'               => '/assets/images/registro/Plan-Retiro-Privado.png',
    ];
    return $map[$tipo] ?? '/assets/images/registro/Registro-Servicio.png';
}

function formatoMX(float $monto): string {
    return '$' . number_format($monto, 2, '.', ',');
}
?>

<section id="sec-polizas" class="section">

  <div class="card-kasu mb-4">
    <h5 class="features-title mb-3">Mis Pólizas Contratadas</h5>

    <?php if (empty($todasLasPolizas)): ?>
      <p class="text-muted text-center py-4">No se encontraron pólizas registradas para esta CURP.</p>
    <?php else: ?>
      <div class="row">
        <?php foreach ($todasLasPolizas as $pol):
            $tipoProd  = clasificarProducto((string)$pol['Producto']);
            $imgSrc    = imagenProducto($tipoProd);
            $status    = (string)($pol['Status'] ?? '');
            $statusCls = ($status === 'ACTIVO' || $status === 'ACTIVACION') ? 'badge-activo' : 'badge-inactivo';
            $costo     = (float)($pol['CostoVenta'] ?? 0);
        ?>
          <div class="col-12 col-sm-6 col-md-4 mb-4">
            <div class="poliza-nft-card">
              <!-- Imagen del producto -->
              <div class="poliza-nft-img">
                <img src="<?= h($imgSrc) ?>"
                     alt="<?= h($tipoProd) ?>"
                     loading="lazy"
                     onerror="this.src='/assets/images/registro/Registro-Servicio.png'">
                <span class="poliza-nft-badge <?= $statusCls ?>"><?= h($status) ?></span>
              </div>

              <!-- Info de la póliza -->
              <div class="poliza-nft-body">
                <h3 class="poliza-nft-title"><?= h($tipoProd) ?></h3>
                <p class="poliza-nft-nombre"><?= h((string)$pol['Nombre']) ?></p>

                <div class="poliza-nft-meta">
                  <div class="poliza-nft-row">
                    <span class="poliza-nft-label">Póliza</span>
                    <span class="poliza-nft-value"><?= h((string)$pol['IdFIrma']) ?></span>
                  </div>
                  <div class="poliza-nft-row">
                    <span class="poliza-nft-label">Servicio</span>
                    <span class="poliza-nft-value"><?= h((string)($pol['TipoServicio'] ?? 'N/D')) ?></span>
                  </div>
                  <div class="poliza-nft-row">
                    <span class="poliza-nft-label">Monto</span>
                    <span class="poliza-nft-value"><?= h(formatoMX($costo)) ?></span>
                  </div>
                  <div class="poliza-nft-row">
                    <span class="poliza-nft-label">Registro</span>
                    <span class="poliza-nft-value"><?= h(date('d/m/Y', strtotime((string)$pol['FechaRegistro']))) ?></span>
                  </div>
                </div>

                <a href="/login/Generar_PDF/Poliza_pdf.php?busqueda=<?= base64_encode((string)$pol['IdContact']) ?>"
                   target="_blank"
                   class="poliza-nft-btn"
                   rel="noopener">
                  <i class="fa fa-file-pdf-o"></i> Descargar Póliza
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</section>
