        <section id="sec-datos" class="section">

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

        </section>
