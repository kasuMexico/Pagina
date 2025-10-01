<?
$Host = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES);
if($Host == "/login/Pwa_Prospectos.php"){
    $TipoCancel = "Prospecto";
    $NombBase   = htmlspecialchars($Reg['FullName'] ?? '', ENT_QUOTES);
    $Status     = htmlspecialchars($Reg['Servicio_Interes'] ?? '', ENT_QUOTES);
    $IdVenta    = htmlspecialchars($Reg['Id'] ?? '', ENT_QUOTES);
    $nombre     = '';
    $IdContact  = '';
    $IdUsuario  = '';
    $Producto   = '';
} else {
    $TipoCancel = "Cliente";
    $IdVenta    = (int)($Reg['Id'] ?? 0);
    $IdContact  = (int)($Recg['id'] ?? 0);
    $IdUsuario  = (int)($Recg1['id'] ?? 0);
    $Producto   = htmlspecialchars($Reg['Producto'] ?? '', ENT_QUOTES);
    $Status     = htmlspecialchars($_POST['Status'] ?? '', ENT_QUOTES);
    $NombBase   = htmlspecialchars($Reg['Nombre'] ?? '', ENT_QUOTES);
}
?>

<form method="POST" action="<?php echo $Host; ?>">
    <div id="Gps"></div>
    <div data-fingerprint-slot></div>
    <input type="hidden" name="nombre"   value="<?php echo $nombre; ?>">
    <input type="hidden" name="Host"     value="<?php echo $Host; ?>">
    <input type="hidden" name="IdVenta"  value="<?php echo $IdVenta; ?>">
    <input type="hidden" name="IdContact"value="<?php echo $IdContact; ?>">
    <input type="hidden" name="IdUsuario"value="<?php echo $IdUsuario; ?>">
    <input type="hidden" name="Producto" value="<?php echo $Producto; ?>">
    <div class="modal-header">
    <h5 class="modal-title" id="modalV6">Cancelar <? echo $TipoCancel?></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
    </div>
    <div class="modal-body">
    <div class="alert alert-warning" role="alert">
        <input type="hidden" name="Status" value="<?php echo $Status ?>">
        <p>¿Estás seguro que deseas el <? echo $TipoCancel?>?</p>
        <h4 class="text-center"><strong><?php echo $NombBase; ?></strong></h4>
        <br>
    </div>
    </div>
    <div class="modal-footer">
    <input type="submit" name="CancelaCte" class="btn btn-danger" value="Cancelar">
     </div>
</form>