<?php
/********************************************************************************************
 * Qué hace: Sección "Compartir KASU" – muestra datos del cliente + tarjetas de promoción.
 * Fecha: 23/07/2026
 ********************************************************************************************/
declare(strict_types=1);
?>
<style>
  .share-row{display:flex;gap:.35rem;align-items:center;margin:.35rem 0}
  .ico-social{width:30px;height:30px;object-fit:contain;vertical-align:middle}
  .tarjeta-kasu{border-radius:12px;box-shadow:0 4px 14px rgba(0,0,0,.06);transition:transform .15s ease;border:1px solid #eee;}
  .tarjeta-kasu:hover{transform:translateY(-3px);box-shadow:0 8px 22px rgba(0,0,0,.1);}
  .datos-cliente .font-weight-bold{font-size:15px;}
</style>

<section id="sec-sociales" class="section">

  <!-- Datos del cliente (solo lectura) -->
  <div class="card-kasu mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="features-title mb-0">Mis datos</h5>
      <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalEditarDatos">
        <i class="fa fa-pencil"></i> Editar
      </button>
    </div>

    <div class="row datos-cliente">
      <div class="col-md-4 mb-2">
        <small class="text-muted">Nombre</small>
        <div class="font-weight-bold"><?php echo h($venta['Nombre']); ?></div>
      </div>
      <div class="col-md-4 mb-2">
        <small class="text-muted">Producto</small>
        <div class="font-weight-bold"><?php echo h($ProductoComprado); ?></div>
      </div>
      <div class="col-md-4 mb-2">
        <small class="text-muted">Status de la póliza</small>
        <div class="font-weight-bold">
          <span class="badge badge-pagado"><?php echo h((string)($venta['Status'] ?? '')); ?></span>
        </div>
      </div>
      <div class="col-md-4 mb-2">
        <small class="text-muted">Tipo de servicio</small>
        <div class="font-weight-bold"><?php echo h((string)($venta['TipoServicio'] ?? '')); ?></div>
      </div>
      <div class="col-md-8 mb-2">
        <small class="text-muted">Dirección</small>
        <div class="font-weight-bold">
          <?php
            $dirPartes = array_filter([
              $addr['calle'] ?? '',
              ($addr['numero'] ?? '') ? 'No. ' . $addr['numero'] : '',
              $addr['colonia'] ?? '',
              $addr['codigo_postal'] ?? '',
              $addr['municipio'] ?? '',
              $addr['estado'] ?? ''
            ], fn($s) => $s !== '');
            echo h(implode(', ', $dirPartes));
          ?>
        </div>
      </div>
      <div class="col-md-4 mb-2">
        <small class="text-muted">Teléfono</small>
        <div class="font-weight-bold"><?php echo h($addr['Telefono']); ?></div>
      </div>
      <div class="col-md-4 mb-2">
        <small class="text-muted">Email</small>
        <div class="font-weight-bold" style="word-break:break-all;"><?php echo h($addr['Mail']); ?></div>
      </div>
      <?php if (!empty($addr['Referencia'])): ?>
      <div class="col-md-8 mb-2">
        <small class="text-muted">Referencia del domicilio</small>
        <div class="font-weight-bold"><?php echo h($addr['Referencia']); ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Explicación + Tarjetas de promoción -->
  <div class="card-kasu">
    <div class="text-center mb-3">
      <h5 class="features-title mb-2">Comparte KASU y gana</h5>
      <p class="text-muted mb-0" style="font-size:14px;">
        Estas tarjetas sirven para hacer promoción a KASU. Si alguien compra utilizando una de tus tarjetas, 
        <strong>recibes una comisión como cliente</strong>. ¡Comparte en tus redes y genera ingresos extras!
      </p>
    </div>
    <div class="row">
      <?php include __DIR__ . '/_tarjetas_html.php'; ?>
    </div>
  </div>

  <br><br>

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
