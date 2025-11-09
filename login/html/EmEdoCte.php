<?php
/**
 * Vista modal: resumen de póliza y acciones (Agregar Pago / Estado de Cuenta)
 * Qué hace: muestra datos del cliente, producto, saldo y estado; renderiza botones según nivel/estatus.
 * Compatibilidad: PHP 8.2
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 */

declare(strict_types=1);

// Helper de escape
if (!function_exists('h')) {
    function h(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// Defaults seguros (provenientes de POST/GET o contexto previo)
$nombre          = (string)($_POST['nombre']    ?? $_GET['nombre'] ?? '');
$idVentaPost     = (string)($_POST['IdVenta']    ?? '');
$idVendedorPost  = (string)($_POST['IdVendedor'] ?? '');
$statusVtaPost   = (string)($_POST['StatusVta']  ?? ($Reg['Status'] ?? ''));
$Niv             = isset($Niv)   ? (int)$Niv   : 0;
$Saldo           = isset($Saldo) ? (string)$Saldo : '';
$StatVtas        = is_array($StatVtas ?? null) ? $StatVtas : []; // ej: ['estado'=>'AL CORRIENTE']

// Escapes para impresión
$host       = h($_SERVER['PHP_SELF'] ?? '');
$regNombre  = h($Reg['Nombre']   ?? '');
$regUsuario = (string)($Reg['Usuario'] ?? ''); // se escapa al imprimir
$regProd    = h($Reg['Producto'] ?? '');
$saldoSafe  = h($Saldo);
$nombreSafe = h($nombre);
$estadoTxt  = isset($StatVtas['estado']) ? h((string)$StatVtas['estado']) : '';

// Datos auxiliares de vendedor y sucursal
$NomregUsuario = h((string)$basicas->BuscarCampos($mysqli, 'Nombre', 'Empleados', 'IdUsuario', $regUsuario));
$NumeroSUc     = (int)$basicas->BuscarCampos($mysqli, 'Sucursal', 'Empleados', 'IdUsuario', $regUsuario);
$Sucursal      = h((string)$basicas->BuscarCampos($mysqli, 'nombreSucursal', 'Sucursal', 'IdSucursal', $NumeroSUc));

// Botones según nivel/estatus

//SI LA VENTA ESTA EN ESTATUS DE PREVENTA
if($statusVtaPost === 'PREVENTA'){
    $estadoTxt = 'POLIZA EN PREVENTA';
}

$BtnCta = '';
$idRegForLink = (int)($Reg['Id'] ?? 0);
if ($Niv !== 2 && $idRegForLink > 0) {
    $BtnCta = '<a class="btn btn-primary ml-2" href="Pwa_Estado_Cuenta.php?busqueda=' . base64_encode((string)$idRegForLink) . '">Estado de Cuenta</a>';
}

?>
<form method="POST" action="<?= $host ?>">
  <div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel"><?= $regNombre ?></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>

  <div class="modal-body">
    <input type="hidden" name="IdVenta"    value="<?= h($idVentaPost) ?>">
    <input type="hidden" name="IdVendedor" value="<?= h($idVendedorPost) ?>">
    <input type="hidden" name="Host"       value="<?= $host ?>">
    <input type="hidden" name="nombre"     value="<?= $nombreSafe ?>">

    <?php if ($Niv <= 5): ?>
      <p>Ejecutivo de Ventas</p>
      <h2><?= $NomregUsuario ?></h2>

      <p>Sucursal</p>
      <h2><?= $Sucursal ?></h2>
    <?php endif; ?>

    <p>Producto Contratado</p>
    <h2><?= $regProd ?></h2>

    <p>Liquidación</p>
    <h2><?= $saldoSafe ?></h2>

    <?php if ($estadoTxt !== ''): ?>
      <p>Status de la Póliza:</p>
      <h2 class="text-center"><?= $estadoTxt ?></h2>
    <?php endif; ?>
  </div>

  <div class="modal-footer">
    <?= 
    $BtnPago = '';
    if ($Niv !== 7 && $statusVtaPost === 'COBRANZA') {
        echo $BtnPago = '<input type="submit" name="SelCte" class="btn btn-primary" value="Agregar Pago">';
        echo $BtnCta; 
    }elseif ($Niv !== 7 && $statusVtaPost === 'PREVENTA') {
        echo $BtnPago = '<input type="submit" name="SelCte" class="btn btn-primary" value="Agregar Pago">';
        echo $BtnPrmesa = '<input type="submit" name="SelCte" class="btn btn-warning" value="Promesa de Pago">';
    }
    ?>
  </div>
</form>
