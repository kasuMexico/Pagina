<?php
// Defaults seguros
$nombre        = $_POST['nombre']    ?? $_GET['nombre']    ?? '';
$idVentaPost   = $_POST['IdVenta']    ?? '';
$idVendedorPost= $_POST['IdVendedor'] ?? '';
$statusVtaPost = $_POST['StatusVta']  ?? ($Reg['Status'] ?? '');
$Niv           = $Niv ?? 0;
$Saldo         = $Saldo ?? '';
$StatVtas      = $StatVtas ?? []; // ej: ['estado'=>'AL CORRIENTE']

// Escapes
$host       = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');
$regNombre  = htmlspecialchars($Reg['Nombre']   ?? '', ENT_QUOTES, 'UTF-8');
$regUsuario = htmlspecialchars($Reg['Usuario']  ?? '', ENT_QUOTES, 'UTF-8');
$regProd    = htmlspecialchars($Reg['Producto'] ?? '', ENT_QUOTES, 'UTF-8');
$saldoSafe  = htmlspecialchars($Saldo, ENT_QUOTES, 'UTF-8');
$nombreSafe = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
$estadoTxt  = isset($StatVtas['estado']) ? htmlspecialchars($StatVtas['estado'], ENT_QUOTES, 'UTF-8') : '';

// Botones
$BtnPago = '';
if ($Niv != 7 && $statusVtaPost === 'COBRANZA') {
  $BtnPago = '<input type="submit" name="SelCte" class="btn btn-primary" value="Agregar Pago">';
}
$BtnCta = '';
if ($Niv != 7) {
  $BtnCta = '<a class="btn btn-primary" href="Pwa_Estado_Cuenta.php?busqueda=' . base64_encode($Reg['Id']) . '">Estado de Cuenta</a>';
}
?>
<form method="POST" action="<?php echo $host; ?>">
  <div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel"><?php echo $regNombre; ?></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>

  <div class="modal-body">
    <input type="hidden" name="IdVenta"    value="<?php echo htmlspecialchars($idVentaPost,    ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="IdVendedor" value="<?php echo htmlspecialchars($idVendedorPost, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="Host"       value="<?php echo $host; ?>">
    <input type="hidden" name="nombre"     value="<?php echo $nombreSafe; ?>">

    <?php if ($Niv <= 4): ?>
      <p>Ejecutivo de Ventas</p>
      <h2><?php echo $regUsuario; ?></h2>
    <?php endif; ?>

    <p>Producto Contratado</p><h2><?php echo $regProd; ?></h2>
    <p>Liquidación</p><h2><?php echo $saldoSafe; ?></h2>

    <?php if ($estadoTxt !== ''): ?>
      <p>Status de la Póliza:</p>
      <h2 class="text-center"><?php echo $estadoTxt; ?></h2>
    <?php endif; ?>
  </div>

  <div class="modal-footer">
    <?php echo $BtnPago . $BtnCta; ?>
  </div>
</form>