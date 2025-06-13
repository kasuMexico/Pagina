    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Registrar Nuevo Cliente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </div>
    <div class="modal-body">
        <form action="https://kasu.com.mx/login/php/Registro_Prospectos.php" method="post">
            <!-- Inputs Ocultos automaticos y Fijos -->
            <div id="Gps"></div> <!-- Input que muestra el GPS -->
            <input type="text" name="Host" value="<? $_SERVER['PHP_SELF'] ?>" style="display: ;"> <!-- (FingerPrint) Registra la pagina donde se realizo la Venta -->
            <input type="text" name="IdEmpleado" value="<? $IdAsignacion ?>" style="display: ;"> <!-- (Venta) Registra el Usuario que esta vendiendo -->
            <!-- Inputs Registros Ingresables  -->
            <label>Clave CURP</label> <!-- (Usuario) Regustro de CURP con api Extrae Datos  -->
            <input class="form-control" type="text" name="ClaveCurp" placeholder="Clave CURP" required maxlength="18" minlength="18" pattern="^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9]{2}$" title="Debe ser una CURP válida en mayúsculas, ejemplo: GARC800101HDFLLR05" />
            <label>E-mail</label> <!-- (Contacto) Regustro de CURP con api Extrae Datos  -->
            <input class="form-control" type="mail" name="Mail" placeholder="Correo electronico" >
            <label>Telefono</label> <!-- (Contacto) Regustro de CURP con api Extrae Datos  -->
            <input class="form-control" type="tel" name="Telefono" placeholder="Telefono" required>
            <!-- Inputs de beneficiario  -->
            <label>Para quien compra la poliza</label>
            <select class="form-control" name="Tipo">
                <option value="Cliente">Para si mismo</option>
                <option value="Beneficiario">Para alguien mas</option>
            </select>
            <label>CURP Beneficiario</label> <!-- (Usuario) Regustro de CURP de el Beneficiario en caso de ser asi  -->
            <input class="form-control" type="text" name="ClaveCurp" placeholder="Clave CURP" required maxlength="18" minlength="18" pattern="^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9]{2}$" title="Debe ser una CURP válida en mayúsculas, ejemplo: GARC800101HDFLLR05" />
            <!-- Inputs de direccion  -->
            <label>Ingresa la direccion</label> <!-- (Contacto) Regustro de CURP con api Extrae Datos  -->
            <input class="form-control" type="Number" name="Codigo_Postal" placeholder="Codigo Postal" >
            <input class="form-control" type="text" name="Calle" placeholder="Nombre de la Calle" >
            <input class="form-control" type="Number" name="Numero" placeholder="Numero de la Casa" >
            <input class="form-control" type="text" name="Numero" placeholder="Colonia / Localidad" > <!-- (Contacto) Select por API  -->
            <input class="form-control" type="text" name="Municipio" placeholder="Municipio" > <!-- (Contacto) Automatica por API  -->
            <input class="form-control" type="text" name="Estado" placeholder="Estado" > <!-- (Contacto) Automatica por API  -->
            <input class="form-control" type="text" name="Referencia" placeholder="Referencia del domicilio" > 
            <label>Gastos Funerarios</label> <!-- (Venta) Seleccionar el producto Gastos Funerarios  -->
            <select class="form-control" name="Producto">
                <option value="02a29">02 a 29 Años</option>
                <option value="30a49">30 a 49 Años</option>
                <option value="50a54">50 a 54 Años</option>
                <option value="55a59">55 a 59 Años</option>
                <option value="60a64">60 a 64 Años</option>
                <option value="65a69">65 a 69 Años</option>
            </select>
            <label>Forma de Pago</label> <!-- (Venta) Seleccionar el numero de pagos  -->
            <select class="form-control" name="Producto">
                <option value="1">Pago de Contado</option>
                <option value="3">Pago a 3 Meses</option>
                <option value="6">Pago a 6 Meses</option>
                <option value="9">Pago a 9 Meses</option>
            </select>
            <hr>
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
