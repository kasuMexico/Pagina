<?php
/********************************************************************************************
 * Qué hace: Formulario modal para registrar pagos y promesas con CSRF, GPS y fingerprint.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

// Status limpio: toma primero el calculado, si no el de la venta
$StatusVenta = $StatVtas['estado'] ?? ($Reg['Status'] ?? '');

// Normalizaciones locales para evitar notices en 8.2
$__Pago1       = (float)($Pago1 ?? 0);
$__Pago        = isset($Pago) ? (string)$Pago : '0';
$__PagoPend    = (string)($PagoPend ?? '0');
$__csrf        = (string)($_SESSION['csrf_auth'] ?? '');
$__host        = (string)($_SERVER['PHP_SELF'] ?? '');
$__nombre      = (string)($nombre ?? '');
$__idVenta     = (int)($Reg['Id'] ?? 0);
$__idContact   = (int)($Recg['id'] ?? 0);
$__idUsuario   = (int)($Recg1['id'] ?? 0);
$__refPost     = isset($_POST['Referencia']) ? (int)$_POST['Referencia'] : 0;
$__producto    = (string)($Reg['Producto'] ?? '');
$__status      = (string)($Status ?? '');
$__metodo      = (string)($Metodo ?? '');
$__idVendedor  = (string)($Reg['Usuario'] ?? '');

// Mora calculada
$mora = number_format((float)$financieras->Mora($__Pago1), 2);

// Fecha sugerida para nueva promesa
$__fechaPromSugerida = date('Y-m-d', strtotime('+14 days'));

?>
<form method="POST" action="/login/php/Funcionalidad_Pwa.php" autocomplete="off">
  <!-- CSRF -->
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($__csrf, ENT_QUOTES) ?>">

  <!-- Slots de telemetría -->
  <div id="Gps" style="display:none;"></div>
  <div data-fingerprint-slot></div>

  <!-- Contexto -->
  <input type="hidden" name="Host"       value="<?= htmlspecialchars($__host, ENT_QUOTES) ?>">
  <input type="hidden" name="nombre"     value="<?= htmlspecialchars($__nombre, ENT_QUOTES) ?>">
  <input type="hidden" name="IdVenta"    value="<?= $__idVenta ?>">
  <input type="hidden" name="IdContact"  value="<?= $__idContact ?>">
  <input type="hidden" name="IdUsuario"  value="<?= $__idUsuario ?>">
  <input type="hidden" name="Referencia" value="<?= $__refPost ?>">
  <input type="hidden" name="Producto"   value="<?= htmlspecialchars($__producto, ENT_QUOTES) ?>">

  <!-- Estado de operación -->
  <input type="hidden" name="Status"     value="<?= htmlspecialchars($__status, ENT_QUOTES) ?>">
  <input type="hidden" name="Metodo"     value="<?= htmlspecialchars($__metodo, ENT_QUOTES) ?>">
  <input type="hidden" name="IdVendedor" value="<?= htmlspecialchars($__idVendedor, ENT_QUOTES) ?>">

  <div class="modal-header">
    <h5 class="modal-title">Agregar un pago a un cliente</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>

  <div class="modal-body">
    <p>Nombre del Cliente:</p>
    <h4 class="text-center">
      <strong><?= htmlspecialchars((string)($Reg['Nombre'] ?? ''), ENT_QUOTES) ?></strong>
    </h4>

    <p>Status del Cliente:</p>
    <h4 class="text-center">
      <strong><?= htmlspecialchars($StatusVenta, ENT_QUOTES) ?></strong>
    </h4>

    <?php
      // Datos según página y presencia de promesa
      if (empty($_POST['Promesa'])) {
        echo '<input type="hidden" name="PagoProm" value="'.htmlspecialchars($__Pago, ENT_QUOTES).'">';
        echo '<input type="hidden" name="PagoMora" value="'.htmlspecialchars($mora, ENT_QUOTES).'">';
        echo '<p>Pagos pendientes:</p><h4 class="text-center"><strong>'.htmlspecialchars($__PagoPend, ENT_QUOTES).'</strong></h4>';

        if (($StatusVenta ?? '') !== 'AL CORRIENTE') {
          echo '<p>Pago mínimo del periodo:</p><h4 class="text-center"><strong>$'.$mora.'</strong></h4>';
        } else {
          echo '<p>Pago mínimo del periodo:</p><h4 class="text-center"><strong>$'.htmlspecialchars($__Pago, ENT_QUOTES).'</strong></h4>';
        }

      } elseif (($_SERVER['PHP_SELF'] ?? '') === '/login/Pwa_Registro_Pagos.php') {
        // Promesa de pago en Registro_Pagos
        $PromesaPago = 0;
        $PagadoPdP   = 0;
        if ($__refPost > 0) {
          $PromesaPago = (int)($basicas->BuscarCampos($mysqli, 'Cantidad', 'PromesaPago', 'Id', $__refPost) ?? 0);
          $PagadoPdP   = (int)($basicas->BuscarCampos($mysqli, 'Pagado',   'PromesaPago', 'Id', $__refPost) ?? 0);
        }
        $RemanentePdP = max(0, $PromesaPago - $PagadoPdP);
        $SubtotalSafe = (float)($Reg['Subtotal'] ?? 0);

        echo '
          <p>Saldo pendiente de pago</p>
          <h4 class="text-center"><strong>$'.number_format($SubtotalSafe, 2).'</strong></h4>
          <p>Promesa de pago</p>
          <h4 class="text-center"><strong>$'.number_format((float)$PromesaPago, 2).'</strong></h4>
          <p>Faltante por pagar</p>
          <h4 class="text-center"><strong>$'.number_format((float)$RemanentePdP, 2).'</strong></h4>
        ';

      } else {
        // Promesa directa desde otra vista
        $promPost = isset($_POST['Promesa']) ? (float)$_POST['Promesa'] : 0.0;
        echo '
          <p>Promesa de pago</p>
          <h4 class="text-center"><strong>$'.number_format($promPost, 2).'</strong></h4>
        ';
      }
    ?>

    <label for="cantidadPagar">Pago a registrar</label>
    <input class="form-control" id="cantidadPagar" type="number" name="Cantidad"
           placeholder="Cantidad" required step="0.01" min="0">

    <?php if (($StatusVenta ?? '') !== 'AL CORRIENTE'): ?>
      <hr>
      <label for="proximoPago">Promesa de Pago</label>
      <input class="form-control" id="proximoPago" type="date" name="Promesa"
             value="<?= htmlspecialchars($__fechaPromSugerida, ENT_QUOTES) ?>" required>

      <label for="montoPromesa" class="mt-2">Monto de la promesa de pago</label>
      <input class="form-control" id="montoPromesa" type="number" name="PromesPga"
             placeholder="Cantidad" required step="0.01" min="0">
    <?php endif; ?>
  </div>

  <div class="modal-footer">
    <button type="submit" name="Pago" class="btn btn-primary" value="1">Guardar Pago</button>
    <button type="button" class="btn btn-secondary" id="btnGPS">Ir a GPS</button>
  </div>
</form>
