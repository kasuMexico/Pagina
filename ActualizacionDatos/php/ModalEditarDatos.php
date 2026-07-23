<!-- Modal: Editar datos del cliente -->
<div class="modal fade" id="modalEditarDatos" tabindex="-1" role="dialog" aria-labelledby="modalEditarTitulo" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 16px 40px rgba(0,0,0,.18);">
      <div class="modal-header" style="background:#3CA4ED;color:#fff;border-radius:16px 16px 0 0;">
        <h5 class="modal-title" id="modalEditarTitulo">Editar mis datos</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" style="color:#fff;opacity:.9;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <form id="ActDatCliModal" method="post" action="/login/php/Funcionalidad_Pwa.php" autocomplete="off">
          <input type="hidden" name="IdVenta"   value="<?php echo h((string)$venta['Id']); ?>">
          <input type="hidden" name="IdContact" value="<?php echo h((string)$venta['IdContact']); ?>">
          <input type="hidden" name="Host"      value="<?php echo h((string)($_SERVER['PHP_SELF'] ?? '')); ?>">
          <input type="hidden" name="CURP"      value="<?php echo h($curp); ?>">
          <input type="hidden" name="Poliza"    value="<?php echo h($venta['IdFIrma']); ?>">

          <!-- Datos no editables -->
          <div class="form-group">
            <label class="font-weight-bold">Nombre</label>
            <input type="text" class="form-control" value="<?php echo h($venta['Nombre']); ?>" disabled>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="font-weight-bold">Producto</label>
                <input type="text" class="form-control" value="<?php echo h($ProductoComprado); ?>" disabled>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="font-weight-bold">Status de la póliza</label>
                <input type="text" class="form-control" value="<?php echo h((string)($venta['Status'] ?? '')); ?>" disabled>
              </div>
            </div>
          </div>

          <hr>

          <div class="form-group">
            <label class="font-weight-bold">Cambia tu tipo de servicio</label>
            <select class="form-control" name="TipoServ">
              <option value="Tradicional" <?php echo ($venta['TipoServicio'] ?? '')==='Tradicional'?'selected':''; ?>>TRADICIONAL</option>
              <option value="Cremacion"   <?php echo ($venta['TipoServicio'] ?? '')==='Cremacion'?'selected':''; ?>>CREMACION</option>
              <option value="Ecologico"   <?php echo ($venta['TipoServicio'] ?? '')==='Ecologico'?'selected':''; ?>>ECOLOGICO</option>
            </select>
          </div>

          <label class="font-weight-bold">Cambia la dirección</label>
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
            <label class="font-weight-bold">Teléfono</label>
            <input class="form-control" type="text" name="Telefono" value="<?php echo h($addr['Telefono']); ?>">
          </div>

          <div class="form-group">
            <label class="font-weight-bold">Email</label>
            <input class="form-control" type="email" name="Email" value="<?php echo h($addr['Mail']); ?>">
          </div>
        </form>
      </div>
      <div class="modal-footer" style="border-top:none;padding-top:0;">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardarModal" onclick="document.getElementById('ActDatCliModal').submit();">
          <i class="fa fa-save"></i> Guardar cambios
        </button>
      </div>
    </div>
  </div>
</div>
