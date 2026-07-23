<?php
/********************************************************************************************
 * _polizas_grid.php – Grid compacto de pólizas (5 por fila en desktop).
 * Usar con include. Requiere _polizas_data.php cargado previamente.
 ********************************************************************************************/
if (!isset($todasLasPolizas)) return;
?>

<?php if (empty($todasLasPolizas)): ?>
  <p class="text-muted text-center py-3">No se encontraron pólizas registradas.</p>
<?php else: ?>
  <div class="polizas-grid">
    <?php foreach ($todasLasPolizas as $pol):
        $tipoProd  = clasificarProducto((string)$pol['Producto']);
        $imgSrc    = imagenProducto($tipoProd);
        $status    = (string)($pol['Status'] ?? '');
        $statusCls = ($status === 'ACTIVO' || $status === 'ACTIVACION') ? 'badge-activo' : 'badge-inactivo';
        $costo     = (float)($pol['CostoVenta'] ?? 0);
    ?>
      <div class="poliza-mini-card">
        <!-- Imagen -->
        <div class="poliza-mini-img">
          <img src="<?= h($imgSrc) ?>"
               alt="<?= h($tipoProd) ?>"
               loading="lazy"
               onerror="this.src='/assets/images/registro/Registro-Servicio.png'">
          <span class="poliza-mini-badge <?= $statusCls ?>"><?= h($status) ?></span>
        </div>
        <!-- Info -->
        <div class="poliza-mini-body">
          <h4 class="poliza-mini-titulo"><?= h($tipoProd) ?></h4>
          <p class="poliza-mini-nombre"><?= h((string)$pol['Nombre']) ?></p>
          <div class="poliza-mini-meta">
            <span class="poliza-mini-poliza">Póliza: <?= h((string)$pol['IdFIrma']) ?></span>
            <span class="poliza-mini-servicio"><?= h((string)($pol['TipoServicio'] ?? 'N/D')) ?></span>
            <span class="poliza-mini-costo"><?= h(formatoMX($costo)) ?></span>
          </div>
          <a href="/login/Generar_PDF/Poliza_pdf.php?busqueda=<?= base64_encode((string)$pol['IdContact']) ?>"
             target="_blank" class="poliza-mini-btn" rel="noopener">
            <i class="fa fa-file-pdf-o"></i> PDF
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
