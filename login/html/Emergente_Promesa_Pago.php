<?php
/**
 * Formulario: Registrar promesa de pago (vista modal)
 * Qué hace: Muestra datos del cliente, estado y permite capturar fecha y monto de promesa.
 * Compatibilidad: PHP 8.2
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 */

// Helper de escape
if (!function_exists('h')) {
    function h(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// Variables seguras para la vista
$clienteNombre = $Reg['Nombre']         ?? '';
$idVenta       = $Reg['Id']             ?? '';
$idContacto    = $Recg['id']            ?? '';
$idUsuario     = $Recg1['id']           ?? '';
$producto      = $Reg['Producto']       ?? '';
$hostPath      = $_SERVER['PHP_SELF']   ?? '';
$nombreParam   = $nombre                ?? '';

// Cálculos de mora y pago mínimo
$Pago1_num         = isset($Pago1) ? (float)$Pago1 : 0.0;
$moraNum           = isset($financieras) ? (float)$financieras->Mora($Pago1_num) : 0.0;
$moraFmt           = number_format($moraNum, 2);

$estadoCliente     = $StatVtas['estado'] ?? '';
$pagoNormalNum     = isset($Pago) ? (float)$Pago : 0.0;
$pagoPendFmt       = isset($PagoPend) ? (string)$PagoPend : '0';

$pagoMinimoNum     = ($estadoCliente !== 'AL CORRIENTE') ? $moraNum : $pagoNormalNum;
$pagoMinimoFmt     = number_format($pagoMinimoNum, 2);

// Si viene una promesa por POST, formatear para mostrar
$promesaPostNum    = isset($_POST['Promesa']) ? (float)$_POST['Promesa'] : null;
$promesaPostFmt    = is_null($promesaPostNum) ? null : number_format($promesaPostNum, 2);

// Fecha sugerida +14 días
$fechaSugerida     = date('Y-m-d', strtotime('+14 days'));
?>
<form method="POST" action="php/Funcionalidad_Pwa.php">
  <!-- *********************************************** Bloque de registro de Eventos ************************************************************************* -->
  <div id="Gps"></div> <!-- Div que lanza el GPS -->
  <div data-fingerprint-slot></div> <!-- DIV que lanza el Finger Print -->

  <input type="hidden" name="nombre"    value="<?= h($nombreParam) ?>">
  <input type="hidden" name="Host"      value="<?= h($hostPath) ?>">
  <input type="hidden" name="IdVenta"   value="<?= h((string)$idVenta) ?>">
  <input type="hidden" name="IdContact" value="<?= h((string)$idContacto) ?>">
  <input type="hidden" name="IdUsuario" value="<?= h((string)$idUsuario) ?>">
  <input type="hidden" name="Producto"  value="<?= h($producto) ?>">
  <!-- ********************************************** Bloque de registro de Eventos ************************************************************************* -->

  <div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Registrar promesa de pago</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>

  <div class="modal-body">
    <p>Nombre del Cliente:</p>
    <h4 class="text-center"><strong><?= h($clienteNombre) ?></strong></h4>

    <?php if ($promesaPostNum === null): ?>
      <p>Pagos pendientes</p>
      <h4 class="text-center"><?= h($pagoPendFmt) ?></h4>

      <p>Status del Cliente:</p>
      <h4 class="text-center"><?= h($estadoCliente) ?></h4>

      <p>Pago mínimo del periodo:</p>
      <h4 class="text-center"><strong>$<?= h($pagoMinimoFmt) ?></strong></h4>
    <?php else: ?>
      <p>Promesa de pago</p>
      <h4 class="text-center">$<?= h($promesaPostFmt) ?></h4>
    <?php endif; ?>

    <input type="hidden" name="PagoMinimo" value="<?= h((string)$pagoMinimoNum) ?>">

    <label for="promesa-fecha">Fecha Promesa de Pago</label>
    <input id="promesa-fecha" class="form-control" type="date" name="Promesa" value="<?= h($fechaSugerida) ?>" required>

    <label for="promesa-cantidad" class="mt-2">Promesa de Pago</label>
    <input id="promesa-cantidad" class="form-control" type="number" name="Cantidad" placeholder="Cantidad" step="0.01" min="0" required>
  </div>

  <div class="modal-footer">
    <input type="submit" name="PromPago" class="btn btn-primary" value="Registrar promesa de pago">
  </div>
</form>
