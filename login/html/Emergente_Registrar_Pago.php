<form method="POST" action="php/Funcionalidad_Pwa.php">
    <!-- *********************************************** Bloque de registro de Eventos ************************************************************************* -->
    <div id="Gps"></div> <!-- Div que lanza el GPS -->
    <div data-fingerprint-slot></div> <!-- DIV que lanza el Finger Print -->
    <input type="text" name="nombre" value="<?php echo $nombre; ?>" style="display: none;"> <!-- nombre que busque para esta pantalla -->
    <input type="text" name="Host" value="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: none;"> <!-- Host de donde estoy enviando la peticion -->
    <input type="number" name="IdVenta" value="<?php echo $Reg['Id'] ?? ''; ?>" style="display: none;"> <!-- Id de Venta Seleccionado -->
    <input type="number" name="IdContact" value="<?php echo $Recg['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Contacto Seleccionado -->
    <input type="number" name="IdUsuario" value="<?php echo $Recg1['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Usuario Seleccionado -->
    <input type="text" name="Producto" value="<?php echo $Reg['Producto'] ?? ''; ?>" style="display: none;"> <!-- Producto de el cliente Seleccionado -->
    <!-- ********************************************** Bloque de registro de Eventos ************************************************************************* -->
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Agregar un pago a un cliente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <p>Nombre del Cliente:</p>
        <h4 class="text-center"><strong><?php echo $Reg['Nombre'] ?? ''; ?></strong></h4>
        <p>Status del Cliente:</p>
        <h5 class="text-center"><strong><?php echo $StatVtas['estado'] ?? ''; ?></strong></h5>
        <input type="hidden" name="Status" value="<?php echo $Status ?>">
        <input type="hidden" name="Metodo" value="<?php echo $Metodo ?>">
        <input type="hidden" name="IdVendedor" value="<?php echo $Reg['Usuario'] ?>">
        <input type="hidden" name="nombre" value="<?php echo $nombre; ?>">
        <?php
        //Calculamos la mora de un pago atrasado
        $mora = number_format($financieras->Mora($Pago1), 2);
        //si es pago normal imprimimos el valor
        if (empty($_POST['Promesa'])) {
            echo '
            <input type="hidden" name="PagoProm" value="'.$Pago.'">
            <input type="hidden" name="PagoMora" value="'.$mora.'">
            <p>Pagos pendientes:</p>
            <h4 class="text-center"><strong>'. $PagoPend .'</strong></h4>
            ';
            if($StatVtas['estado'] != "AL CORRIENTE"){
             echo '
                <p>Pago minimo el periodo:</p>
                <h4 class="text-center"><strong>$'. $mora .'</strong></h4>
             ';
            }else{
            echo '
                <p>Pago minimo el periodo:</p>
                <h4 class="text-center"><strong>$'. $Pago .'</strong></h4>
            ';   
            }
        } else {
            //si es promesa imprimimos esto
            echo '
            <p>Promesa de pago <strong>$' . number_format($_POST['Promesa'], 2) . '</strong></p>
            ';
        }
        ?>

        <label for="cantidadPagar">Pago a registrar</label>
        <input class="form-control" id="cantidadPagar" type="number" name="Cantidad" placeholder="Cantidad" required>
        <?
        if($StatVtas['estado'] != "AL CORRIENTE"){
             echo '
                <hr>
                <label for="proximoPago">Promesa de Pago</label>
                <input class="form-control" id="proximoPago" type="date" name="Promesa" value="'. date("Y-m-d",strtotime("+14 days")).'" required>
                <label>Monto de la promesa de pago</label>
                <input class="form-control" type="number" name="PromesPga" placeholder="Cantidad" required>
             ';
        }
        ?>
    </div>
    <div class="modal-footer">
        <input type="submit" name="Pago" class="btn btn-primary" value="Guardar Pago">
        <a href="#" class="btn btn-primary">IR a GPS</a>
    </div>
</form>
