    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Registrar Nuevo Cliente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </div>
    <div class="modal-body">
        <form action="https://kasu.com.mx/login/php/Registro_Prospectos.php" method="post">
            <!-- Inputs Ocultos automaticos y Fijos -->
            <div id="Gps"></div> <!-- Input que muestra el GPS -->
            <input type="text" name="Usuario" value="'<? $_SERVER['PHP_SELF'] ?>'" style="display: none;">
            <input type="text" name="IdAsignacion" value="'<? $IdAsignacion ?>'" style="display: none;">
            <input type="text" name="Tipo" value="'Beneficiario'" style="display: none;">
            <!-- Inputs Registros Ingresables  -->
            <label>Clave Curp</label>
            <input class="form-control" type="text" name="ClaveCurp" placeholder="Clave CURP" required maxlength="18" minlength="18" pattern="^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9]{2}$" title="Debe ser una CURP válida en mayúsculas, ejemplo: GARC800101HDFLLR05" />
            <label>E-mail</label>
            <input class="form-control" type="mail" name="Email" placeholder="Correo electronico" >
            <label>Telefono</label>
            <input class="form-control" type="tel" name="Telefono" placeholder="Telefono" required>
            <label>Fecha de Nacimiento</label>
            <input class="form-control" type="date" name="FechaNac" placeholder="Fecha nacimiento" >
            <!-- Inputs Registros Seleccionables viariables  -->
            <label>Tipo de Servicio</label>
            <select class="form-control" name="Servicio">
                <option value="Funerario">GASTOS FUNERARIOS</option>
                <option value="Policias">OFICIALES POLICIA</option>
                <option value="Retiro">AHORRO PARA EL RETIRO</option>
            </select>
            <label>GASTOS FUNERARIOS</label>
            <select class="form-control" name="Producto">
                <option value="02a29">02 a 29 Años</option>
                <option value="30a49">30 a 49 Años</option>
                <option value="50a54">50 a 54 Años</option>
                <option value="55a59">55 a 59 Años</option>
                <option value="60a64">60 a 64 Años</option>
                <option value="65a69">65 a 69 Años</option>
            </select>
            <label>OFICIALES POLICIA</label>
            <select class="form-control" name="Producto">
                <option value="02a29">02 a 29 Años</option>
                <option value="30a49">30 a 49 Años</option>
                <option value="50a54">50 a 54 Años</option>
                <option value="55a59">55 a 59 Años</option>
                <option value="60a64">60 a 64 Años</option>
                <option value="65a69">65 a 69 Años</option>
            </select>
            <label>Sub Producto</label>
            <select class="form-control" name="Sub-Producto">
                <option value="Cremacion">GASTOS FUNERARIOS</option>
                <option value="Tradicional">INVERSION UNIVERSITARIA</option>
                <option value="Ecologico">AHORRO PARA EL RETIRO</option>
            </select>
            <!-- Inputs de envio de datos  -->
            <div class="modal-footer">
                <input type="submit" name="prospectoNvo" class="btn btn-primary" value="Registrar y enviar">
            </div>
        </form>
    </div>
