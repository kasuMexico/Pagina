        <section id="sec-datos" class="section">
          <div class="card-kasu">
            <h5 class="features-title mb-3">Modificar mis datos</h5>

            <form id="ActDatCli" method="post" action="/login/php/Funcionalidad_Pwa.php" autocomplete="off">
              <input type="hidden" name="IdVenta"   value="<?php echo h((string)$venta['Id']); ?>">
              <input type="hidden" name="IdContact" value="<?php echo h((string)$venta['IdContact']); ?>">
              <input type="hidden" name="Host"      value="<?php echo h((string)($_SERVER['PHP_SELF'] ?? '')); ?>">
              <input type="hidden" name="CURP"      value="<?php echo h($curp); ?>">
              <input type="hidden" name="Poliza"    value="<?php echo h($venta['IdFIrma']); ?>">

              <div class="form-group">
                <label>Nombre</label>
                <h2 class="text-center"><strong><?php echo h($venta['Nombre']); ?></strong></h2>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <label>Producto</label>
                  <h2 class="text-center"><strong><?php echo h($ProductoComprado); ?></strong></h2>
                </div>
                <div class="col-md-6">
                  <label>Status de la póliza</label>
                  <h2 class="text-center"><strong><?php echo h((string)($venta['Status'] ?? '')); ?></strong></h2>
                </div>
              </div>

              <hr>

              <div class="form-group">
                <label>Cambia tu tipo de servicio</label>
                <select class="form-control" name="TipoServ">
                  <option value="Tradicional" <?php echo $venta['TipoServicio']==='Tradicional'?'selected':''; ?>>TRADICIONAL</option>
                  <option value="Cremacion"   <?php echo $venta['TipoServicio']==='Cremacion'?'selected':''; ?>>CREMACION</option>
                  <option value="Ecologico"   <?php echo $venta['TipoServicio']==='Ecologico'?'selected':''; ?>>ECOLOGICO</option>
                </select>
              </div>

              <label>Cambia la dirección</label>
              <div class="mb-2">
                <input class="form-control mb-2" type="text" name="Codigo_Postal" value="<?php echo h($addr['codigo_postal']); ?>" placeholder="Código Postal">
                <div class="row mb-2">
                  <div class="col-6"><input class="form-control" type="text" name="Calle"   value="<?php echo h($addr['calle']); ?>"   placeholder="Nombre de la Calle"></div>
                  <div class="col-6"><input class="form-control" type="text" name="Numero"  value="<?php echo h($addr['numero']); ?>"  placeholder="Número de la Casa"></div>
                </div>
                <input class="form-control mb-2" type="text" name="Colonia" value="<?php echo h($addr['colonia']); ?>" placeholder="Colonia / Localidad">
                <div class="row mb-2">
                  <div class="col-6"><input class="form-control" type="text" name="Municipio" value="<?php echo h($addr['municipio']); ?>" placeholder="Municipio"></div>
                  <div class="col-6"><input class="form-control" type="text" name="Estado"    value="<?php echo h($addr['estado']); ?>"    placeholder="Estado"></div>
                </div>
                <input class="form-control" type="text" name="Referencia" value="<?php echo h($addr['Referencia']); ?>" placeholder="Referencia del domicilio">
              </div>

              <div class="form-group">
                <label>Teléfono</label>
                <input class="form-control" type="text" name="Telefono" value="<?php echo h($addr['Telefono']); ?>">
              </div>

              <div class="form-group">
                <label>Email</label>
                <input class="form-control" type="email" name="Email" value="<?php echo h($addr['Mail']); ?>">
              </div>

              <div class="mt-2">
                <button type="submit" name="CamDat" class="btn btn-primary">Guardar cambios</button>
              </div>
            </form>
          </div>
        </section>