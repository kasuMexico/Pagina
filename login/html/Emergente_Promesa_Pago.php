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
                        <h5 class="modal-title" id="exampleModalLabel">Registrar promesa de pago</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </div>
                    <div class="modal-body">
                        <p>Nombre del Cliente:</p>
                        <h4 class="text-center"><strong><?php echo $Reg['Nombre'] ?? ''; ?></strong></h4>
                        <?php
                            $mora = number_format($financieras->Mora($Pago1), 2);
                            if (empty($_POST['Promesa'])) {
                                echo '
                                    <p>Pagos pendientes</p>
                                    <h4 class="text-center">'.$PagoPend.'</h4>
                                    <p>Status del Cliente:</p>
                                    <h4 class="text-center">'.$StatVtas['estado'].'</h4>
                                ';
                            //Si el cliente esta en mora muestra el pago con mora caso contrato pago normal
                            if($StatVtas['estado'] != "AL CORRIENTE"){
                                $Pago = $mora;
                            echo '
                                <p>Pago minimo el periodo:</p>
                                <h4 class="text-center"><strong>$'. $Pago .'</strong></h4>
                            ';
                            }else{
                            echo '
                                <p>Pago minimo el periodo:</p>
                                <h4 class="text-center"><strong>$'. $Pago .'</strong></h4>
                            ';   
                            }
                        } else {
                            echo '
                                <p>Promesa de pago</p>
                                <h4 class="text-center">'.$number_format($_POST['Promesa'], 2).'</h4>
                            ';
                        }
                        ?>
                        <input type="text" name="PagoMinimo" value="<?php echo $Pago ?>" style="display: none;">
                        <label>Fecha Promesa de Pago</label>
                        <input class="form-control" type="date" name="Promesa" value="<?php echo date("Y-m-d", strtotime("+14 days")); ?>" required>
                        <label>Promesa de Pago</label>
                        <input class="form-control" type="number" name="Cantidad" placeholder="Cantidad" required>
                    </div>
                    <div class="modal-footer">
                        <input type="submit" name="PromPago" class="btn btn-primary" value="Registrar promesa de pago">
                    </div>
                </form>