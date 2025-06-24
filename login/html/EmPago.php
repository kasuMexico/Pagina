<form method="POST" action="php/Funcionalidad_Pwa.php">
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?php echo $Reg['Nombre'];?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div id="Gps" style="display: none;"></div>
        <input type="hidden" name="IdVenta" value="<?php echo $Reg['Id']; ?>">
        <input type="hidden" name="IdVendedor" value="<?php echo isset($_POST['IdVendedor']) ? $_POST['IdVendedor'] : ''; ?>">
        <input type="hidden" name="Host" value="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="NombreVenta" value="<?php echo $name; ?>">

        <?php
        $mora = '$' . number_format($financieras->Mora($Pago1), 2);
        if (empty($_POST['Promesa'])) {
            echo '
            <p>Pagos pendientes <strong>' . $PagoPend . ' Pagos</strong></p>
            <p>Pago Normal periodo <strong>' . $Pago . '</strong></p>
            <p>Pago con Mora <strong>' . $mora . '</strong></p>
            ';
        } else {
            echo '
            <p>Promesa de pago <strong>$' . number_format($_POST['Promesa'], 2) . '</strong></p>
            ';
        }
        ?>

        <label for="cantidadPagar">Cantidad a pagar</label>
        <input class="form-control" id="cantidadPagar" type="number" name="Cantidad" placeholder="Cantidad" required>

        <label for="formaPago">Selecciona la forma de pago</label>
        <select class="form-control" id="formaPago" name="Status">
            <option value="Normal" selected>Pago Normal</option>
            <option value="<?php echo $Pago1/10;?>">Pago con Mora</option>
        </select>

        <label for="proximoPago">Próximo Pago</label>
        <input class="form-control" id="proximoPago" type="date" name="Promesa" value="<?php echo date("Y-m-d",strtotime("+14 days"));?>" required>
    </div>
    <div class="modal-footer">
        <input type="submit" name="Pago" class="btn btn-primary" value="Guardar Pago">
        <a href="#" class="btn btn-primary">IR a GPS</a>
    </div>
</form>
