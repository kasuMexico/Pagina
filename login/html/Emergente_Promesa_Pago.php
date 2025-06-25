                <form method="POST" action="php/Funcionalidad_Pwa.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo $Reg['Nombre'] ?? ''; ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </div>
                    <div class="modal-body">
                        <?php
                        $mora = number_format($financieras->Mora($Pago1), 2);
                        if (empty($_POST['Promesa'])) {
                            echo '
                                <p>Pagos pendientes <strong>' . ($PagoPend ?? '0') . ' Pagos</strong></p>
                                <p>Pago Normal periodo <strong>' . ($Pago ?? '0.00') . '</strong></p>
                                <p>Pago con Mora <strong>' . $mora . '</strong></p>
                            ';
                        } else {
                            echo '
                                <p>Promesa de pago <strong>' . number_format($_POST['Promesa'], 2) . '</strong></p>
                            ';
                        }
                        ?>
                        <div id="Gps" style="display: none;"></div>
                        <input type="number" name="IdVenta" value="<?php echo $Reg['Id'] ?? ''; ?>" style="display: none;">
                        <input type="text" name="Host" value="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: none;">
                        <input type="text" name="name" value="<?php echo $name; ?>" style="display: none;">
                        <input type="text" name="Status" value="<?php echo $Reg['Status'] ?? ''; ?>" style="display: none;">
                        <label>Promesa de Pago</label>
                        <input class="form-control" type="date" name="Promesa" value="<?php echo date("Y-m-d", strtotime("+14 days")); ?>" required>
                        <label>Cantidad a pagar</label>
                        <input class="form-control" type="number" name="Cantidad" placeholder="Cantidad" required>
                    </div>
                    <div class="modal-footer">
                        <input type="submit" name="PromPago" class="btn btn-primary" value="Registrar promesa de pago">
                    </div>
                </form>