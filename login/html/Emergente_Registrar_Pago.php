<form method="POST" action="/login/php/Funcionalidad_Pwa.php" autocomplete="off">
  <!-- CSRF -->
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_auth'] ?? '', ENT_QUOTES) ?>">

  <!-- Eventos / contexto -->
  <div id="Gps"></div>
  <div data-fingerprint-slot></div>

  <input type="hidden" name="Host"       value="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">
  <input type="hidden" name="nombre"     value="<?= htmlspecialchars($nombre ?? '', ENT_QUOTES) ?>">
  <input type="hidden" name="IdVenta"    value="<?= (int)($Reg['Id'] ?? 0) ?>">
  <input type="hidden" name="IdContact"  value="<?= (int)($Recg['id'] ?? 0) ?>">
  <input type="hidden" name="IdUsuario"  value="<?= (int)($Recg1['id'] ?? 0) ?>">
  <input type="hidden" name="Producto"   value="<?= htmlspecialchars($Reg['Producto'] ?? '', ENT_QUOTES) ?>">

  <!-- Estado de operación -->
  <input type="hidden" name="Status"     value="<?= htmlspecialchars($Status ?? '', ENT_QUOTES) ?>">
  <input type="hidden" name="Metodo"     value="<?= htmlspecialchars($Metodo ?? '', ENT_QUOTES) ?>">
  <input type="hidden" name="IdVendedor" value="<?= htmlspecialchars($Reg['Usuario'] ?? '', ENT_QUOTES) ?>">

  <div class="modal-header">
    <h5 class="modal-title">Agregar un pago a un cliente</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
  </div>

  <div class="modal-body">
    <p>Nombre del Cliente:</p>
    <h4 class="text-center"><strong><?= htmlspecialchars($Reg['Nombre'] ?? '', ENT_QUOTES) ?></strong></h4>

    <p>Status del Cliente:</p>
    <h5 class="text-center"><strong><?= htmlspecialchars($StatVtas['estado'] ?? '', ENT_QUOTES) ?></strong></h5>

    <?php
      $mora = number_format((float)$financieras->Mora($Pago1 ?? 0), 2);
      if (empty($_POST['Promesa'])) {
        echo '<input type="hidden" name="PagoProm" value="'.htmlspecialchars($Pago ?? 0, ENT_QUOTES).'">';
        echo '<input type="hidden" name="PagoMora" value="'.htmlspecialchars($mora, ENT_QUOTES).'">';
        echo '<p>Pagos pendientes:</p><h4 class="text-center"><strong>'.htmlspecialchars($PagoPend ?? 0, ENT_QUOTES).'</strong></h4>';

        if (($StatVtas['estado'] ?? '') !== 'AL CORRIENTE') {
          echo '<p>Pago mínimo del periodo:</p><h4 class="text-center"><strong>$'.$mora.'</strong></h4>';
        } else {
          echo '<p>Pago mínimo del periodo:</p><h4 class="text-center"><strong>$'.htmlspecialchars($Pago ?? 0, ENT_QUOTES).'</strong></h4>';
        }
      } else {
        echo '<p>Promesa de pago <strong>$'.number_format((float)$_POST['Promesa'], 2).'</strong></p>';
      }
    ?>

    <label for="cantidadPagar">Pago a registrar</label>
    <input class="form-control" id="cantidadPagar" type="number" name="Cantidad" placeholder="Cantidad" required step="0.01" min="0">

    <?php if (($StatVtas['estado'] ?? '') !== 'AL CORRIENTE'): ?>
      <hr>
      <label for="proximoPago">Promesa de Pago</label>
      <input class="form-control" id="proximoPago" type="date" name="Promesa"
             value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>

      <label for="montoPromesa" class="mt-2">Monto de la promesa de pago</label>
      <input class="form-control" id="montoPromesa" type="number" name="PromesPga" placeholder="Cantidad" required step="0.01" min="0">
    <?php endif; ?>
  </div>

  <div class="modal-footer">
    <button type="submit" name="Pago" class="btn btn-primary" value="1">Guardar Pago</button>
    <button type="button" class="btn btn-secondary" id="btnGPS">Ir a GPS</button>
  </div>

  <!-- Campos opcionales para registrar GPS/Fingerprint si tu JS los llena -->
  <input type="hidden" name="Latitud">
  <input type="hidden" name="Longitud">
  <input type="hidden" name="Presicion">
  <input type="hidden" name="fingerprint">
</form>
