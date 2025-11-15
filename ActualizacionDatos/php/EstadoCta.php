        <section id="sec-estado" class="section active">
          <div class="card-kasu">
            <h5 class="features-title mb-3">Estado de cuenta</h5>

            <form method="post" class="form-inline mb-2 filter-bar" style="gap:8px;">
              <label for="fmes" class="mr-2 mb-2">Mes</label>
              <select id="fmes" name="fmes" class="form-control mb-2">
                <?php foreach ($opcMeses as [$v,$t]): ?>
                  <option value="<?php echo h($v); ?>" <?php echo $v===$mesSel?'selected':''; ?>>
                    <?php echo h($t); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <input type="hidden" name="txtCurp_ActIndCli" value="<?php echo h($curp); ?>">
              <input type="hidden" name="txtNumTarjeta_ActIndCli" value="<?php echo h($venta['IdFIrma']); ?>">
              <button class="btn btn-primary mb-2" type="submit">Ver</button>
            </form>

            <?php
              $totalPendiente = 0.0;
              if (!empty($pagos)) {
                foreach ($pagos as $p) {
                  if (($p['StatusComision'] ?? '') !== 'Pagado') {
                    $totalPendiente += (float)$p['Comision'];
                  }
                }
              }
            ?>

            <?php if (empty($pagos)): ?>
              <p class="mb-0">Sin cobros en el periodo seleccionado.</p>
            <?php else: ?>
              <div class="table-responsive table-scroll">
                <table class="table table-sm mb-0">
                  <thead style="position:sticky; top:0; background:#fff;">
                    <tr>
                      <th>Fecha</th>
                      <th>Concepto</th>
                      <th class="text-right">Monto Pago</th>
                      <th class="text-right">Comisión</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($pagos as $p): ?>
                      <tr>
                        <td><?php echo h(date('Y-m-d', strtotime($p['FechaRegistro']))); ?></td>
                        <td><?php echo h($p['Concepto']); ?></td>
                        <td class="text-right">$ <?php echo number_format((float)$p['Cantidad'], 2); ?></td>
                        <td class="text-right">$ <?php echo number_format((float)$p['Comision'], 2); ?></td>
                        <td>
                          <?php if (($p['StatusComision'] ?? '') === 'Pagado'): ?>
                            <span class="badge badge-pagado">Pagado</span>
                          <?php else: ?>
                            <span class="badge badge-pend">Pendiente</span>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <div class="toolbar-bottom">
                <form method="post" action="/login/php/solicitar_pago.php" class="m-0">
                  <input type="hidden" name="IdVendedor" value="<?php echo h($venta['IdFIrma']); ?>">
                  <input type="hidden" name="mes" value="<?php echo h($mesSel); ?>">
                  <input type="hidden" name="monto_pendiente" value="<?php echo number_format($totalPendiente, 2, '.', ''); ?>">
                  <button type="submit" class="btn btn-success">Solicitar pago</button>
                </form>
                <div class="h5 m-0">Total pendiente:
                  <strong>$ <?php echo number_format($totalPendiente, 2); ?></strong>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </section>