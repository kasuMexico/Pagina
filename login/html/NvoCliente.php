    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Registrar cliente Nuevo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </div>
    <div class="modal-body">
        <form action="https://kasu.com.mx/login/php/Registro_Prospectos.php" method="post">
            <!-- Inputs Ocultos automaticos y Fijos -->
            <div id="Gps"></div> <!-- Input que muestra el GPS -->
            <input type="text" name="Host" value="<? $_SERVER['PHP_SELF'] ?>" style="display: none;"> <!-- (FingerPrint) Registra la pagina donde se realizo la Venta -->
            <input type="text" name="IdEmpleado" value="<? $IdAsignacion ?>" style="display: none;"> <!-- (Venta) Registra el Usuario que esta vendiendo -->
            <!-- Inputs Registros Ingresables  -->
            <label>Clave CURP</label> <!-- (Usuario) Regustro de CURP con api Extrae Datos  -->
            <input class="form-control" type="text" name="ClaveCurp" placeholder="Clave CURP" required maxlength="18" minlength="18" pattern="^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9]{2}$" title="Debe ser una CURP válida en mayúsculas, ejemplo: GARC800101HDFLLR05" />
            <label>E-mail</label> <!-- (Contacto) Regustro de CURP con api Extrae Datos  -->
            <input class="form-control" type="mail" name="Mail" placeholder="Correo electronico" >
            <label>Telefono</label> <!-- (Contacto) Regustro de CURP con api Extrae Datos  -->
            <input class="form-control" type="tel" name="Telefono" placeholder="Telefono" required>
            <!-- Inputs de beneficiario  -->
            <label>Para quien compra la póliza</label>
            <select class="form-control" name="Tipo" id="tipo-select" onchange="mostrarCurpBeneficiario()">
                <option value="Cliente">Para sí mismo</option>
                <option value="Beneficiario">Para alguien más</option>
            </select>
            <div id="curp-beneficiario-group" style="display: none;">
                <label for="curp-beneficiario">CURP Beneficiario</label>
                <input 
                    class="form-control"
                    id="curp-beneficiario"
                    type="text"
                    name="ClaveCurp"
                    placeholder="Clave CURP"
                    maxlength="18"
                    minlength="18"
                    pattern="^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9]{2}$"
                    title="Debe ser una CURP válida en mayúsculas, ejemplo: GARC800101HDFLLR05"
                />
            </div>
            <hr>
            <!-- Inputs de dirección -->
            <label>Ingresa la dirección</label>
            <div class="mb-2">
            <input class="form-control mb-2" type="number" name="Codigo_Postal" placeholder="Código Postal">
            <div class="row mb-2">
                <div class="col-6">
                <input class="form-control" type="text" name="Calle" placeholder="Nombre de la Calle">
                </div>
                <div class="col-6">
                <input class="form-control" type="number" name="Numero" placeholder="Número de la Casa">
                </div>
            </div>
            <input class="form-control mb-2" type="text" name="Colonia" placeholder="Colonia / Localidad">
            <div class="row mb-2">
                <div class="col-6">
                <input class="form-control" type="text" name="Municipio" placeholder="Municipio">
                </div>
                <div class="col-6">
                <input class="form-control" type="text" name="Estado" placeholder="Estado">
                </div>
            </div>
            <input class="form-control" type="text" name="Referencia" placeholder="Referencia del domicilio">
            </div>
            <hr>
            <label>Forma de Pago</label> <!-- (Venta) Seleccionar el numero de pagos  -->
            <select class="form-control" name="Producto">
                <option value="1">Pago de Contado</option>
                <option value="3">Pago a 3 Meses</option>
                <option value="6">Pago a 6 Meses</option>
                <option value="9">Pago a 9 Meses</option>
            </select>
            <br>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="Terminos" id="Terminos" required>
                <label class="form-check-label" for="Terminos">
                    El cliente acepto los términos y condiciones
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="Aviso" id="Aviso" required>
                <label class="form-check-label" for="Aviso">
                    El cliente acepto el Aviso de Privacidad
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="Fideicomiso" id="Fideicomiso" required>
                <label class="form-check-label" for="Fideicomiso">
                    El acepta los terminos de el Fideicomiso F/0003
                </label>
            </div>
            <!-- Inputs de envio de datos  -->
            <div class="modal-footer">
                <input type="submit" name="VentaPwa" class="btn btn-primary" value="Registrar Servicio">
            </div>
        </form>
    </div>
