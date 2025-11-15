<?php
/* ===== CSRF ===== */
if (empty($_SESSION['csrf_reg'])) { $_SESSION['csrf_reg'] = bin2hex(random_bytes(32)); }
$csrf = $_SESSION['csrf_reg'];
?>
<div class="modal-header">
  <h5 class="modal-title" id="exampleModalLabel">Registrar cliente Nuevo</h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>

<div class="modal-body">
  <form action="https://kasu.com.mx/eia/Registrar_Venta.php" method="post" accept-charset="utf-8" autocomplete="off">
    <!-- Slots -->
    <div data-gps-slot></div>
    <div data-fingerprint-slot></div>

    <!-- Hidden -->
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf,ENT_QUOTES,'UTF-8') ?>">
    <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'] ?? '',ENT_QUOTES,'UTF-8') ?>">
    <input type="hidden" name="IdPros" value="<?= htmlspecialchars($_POST['IdPros'] ?? ($CteInt ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="IdEmpleado" value="<?= htmlspecialchars($_SESSION['Vendedor'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

    <!-- Datos -->
    <label>Clave CURP</label>
    <input class="form-control text-uppercase"
           style="text-transform:uppercase"
           type="text" name="ClaveCurp"
           <?php if(isset($Reg)){echo 'value="'.htmlspecialchars($Reg['Curp']??'',ENT_QUOTES).'" readonly';} ?>
           placeholder="Clave CURP"
           required maxlength="18" minlength="18"
           pattern="[A-Za-z0-9]{18}" />

    <label>E-mail</label>
    <input class="form-control" type="email" name="Mail"
           <?php if(isset($Reg)){echo 'value="'.htmlspecialchars($Reg['Email']??'',ENT_QUOTES).'" readonly';} ?>
           placeholder="Correo electrónico">

    <label>Teléfono</label>
    <input class="form-control" type="tel" name="Telefono"
           <?php if(isset($Reg)){echo 'value="'.htmlspecialchars($Reg['NoTel']??'',ENT_QUOTES).'" readonly';} ?>
           placeholder="10 dígitos" required inputmode="numeric" pattern="^\d{10}$">

    <?php
    if (!isset($Reg)) {
      ?>
      <!-- Selector de Producto -->
      <label>Selecciona el producto de interés</label>
      <select class="form-control" name="Producto" id="Producto" onchange="toggleTipoServicio(this.value)" required>
        <option value="Funerario">Servicio funerario</option>
        <option value="Retiro">Plan privado de retiro</option>
        <option value="Seguridad">Oficiales de seguridad</option>
        <option value="Transporte">Transportistas</option>
      </select>

      <!-- Tipo de servicio funerario -->
      <div id="tipo-servicio-group" style="display:none;">
        <br>
        <label>Selecciona el tipo de servicio</label>
        <select class="form-control" name="TipoServicio" id="TipoServicio" disabled>
          <option value="Tradicional">Tradicional</option>
          <option value="Cremacion">Cremación</option>
          <option value="Ecologico">Ecológico</option>
        </select>
      </div>

      <!-- Para quién compra -->
      <label>Para quién compra la póliza</label>
      <select class="form-control" name="Tipo" id="tipo-select" onchange="mostrarCurpBeneficiario()">
        <option value="Cliente">Para sí mismo</option>
        <option value="Beneficiario">Para alguien más</option>
      </select>

      <div id="curp-beneficiario-group" style="display:none;">
        <br>
        <label for="curp-beneficiario">CURP Beneficiario</label>
        <input class="form-control"
               id="curp-beneficiario"
               type="text" name="ClaveCurpBen"
               placeholder="Clave CURP"
               maxlength="18" minlength="18"
               pattern="[A-Za-z0-9]{18}"
               style="text-transform:uppercase">
      </div>
      <?php
    } else {
      // Producto fijo desde $Reg
      $prod = $Reg['Servicio_Interes'] ?? '';
      echo '<input type="hidden" name="Producto" value="'.htmlspecialchars($prod,ENT_QUOTES).'">';

      if (strcasecmp($prod, 'Funerario') === 0) {
        $tipoSel = $Reg['TipoServicio'] ?? '';
        echo '
        <label>Selecciona el tipo de servicio</label>
        <select class="form-control" name="TipoServicio" id="TipoServicio">
          <option value="Tradicional"'.($tipoSel==='Tradicional'?' selected':'').'>Tradicional</option>
          <option value="Cremacion"'.($tipoSel==='Cremacion'?' selected':'').'>Cremación</option>
          <option value="Ecologico"'.($tipoSel==='Ecologico'?' selected':'').'>Ecológico</option>
        </select>';
      }
    }
    ?>

    <hr>

    <!-- Dirección -->
    <label>Ingresa la dirección</label>
    <div class="mb-2">
      <input class="form-control mb-2" type="number" name="Codigo_Postal" placeholder="Código Postal" inputmode="numeric" pattern="^\d{5}$">
      <div class="row mb-2">
        <div class="col-6"><input class="form-control" type="text" name="Calle" placeholder="Nombre de la Calle"></div>
        <div class="col-6"><input class="form-control" type="number" name="Numero" placeholder="Número"></div>
      </div>
      <input class="form-control mb-2" type="text" name="Colonia" placeholder="Colonia / Localidad">
      <div class="row mb-2">
        <div class="col-6"><input class="form-control" type="text" name="Municipio" placeholder="Municipio"></div>
        <div class="col-6">
          <select class="form-control" name="Estado" id="estado" required>
            <option value="">Selecciona un estado</option>
            <option value="Aguascalientes">Aguascalientes</option>
            <option value="Baja California">Baja California</option>
            <option value="Baja California Sur">Baja California Sur</option>
            <option value="Campeche">Campeche</option>
            <option value="Coahuila">Coahuila</option>
            <option value="Colima">Colima</option>
            <option value="Chiapas">Chiapas</option>
            <option value="Chihuahua">Chihuahua</option>
            <option value="Ciudad de México">Ciudad de México</option>
            <option value="Durango">Durango</option>
            <option value="Guanajuato">Guanajuato</option>
            <option value="Guerrero">Guerrero</option>
            <option value="Hidalgo">Hidalgo</option>
            <option value="Jalisco">Jalisco</option>
            <option value="Estado de México">Estado de México</option>
            <option value="Michoacán">Michoacán</option>
            <option value="Morelos">Morelos</option>
            <option value="Nayarit">Nayarit</option>
            <option value="Nuevo León">Nuevo León</option>
            <option value="Oaxaca">Oaxaca</option>
            <option value="Puebla">Puebla</option>
            <option value="Querétaro">Querétaro</option>
            <option value="Quintana Roo">Quintana Roo</option>
            <option value="San Luis Potosí">San Luis Potosí</option>
            <option value="Sinaloa">Sinaloa</option>
            <option value="Sonora">Sonora</option>
            <option value="Tabasco">Tabasco</option>
            <option value="Tamaulipas">Tamaulipas</option>
            <option value="Tlaxcala">Tlaxcala</option>
            <option value="Veracruz">Veracruz</option>
            <option value="Yucatán">Yucatán</option>
            <option value="Zacatecas">Zacatecas</option>
          </select>
        </div>
      </div>
      <input class="form-control" type="text" name="Referencia" placeholder="Referencia del domicilio">
    </div>

    <hr>

    <label>Forma de Pago</label>
    <select class="form-control" name="plazo" required>
      <option value="1">Pago de Contado</option>
      <option value="3">Pago a 3 Meses</option>
      <option value="6">Pago a 6 Meses</option>
      <option value="9">Pago a 9 Meses</option>
    </select>
    <br>

    <!-- Consentimientos -->
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="Terminos" id="Terminos" required>
      <label class="form-check-label" for="Terminos">El cliente aceptó los términos y condiciones</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="Aviso" id="Aviso" required>
      <label class="form-check-label" for="Aviso">El cliente aceptó el Aviso de Privacidad</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="Fideicomiso" id="Fideicomiso" required>
      <label class="form-check-label" for="Fideicomiso">El acepta los términos del Fideicomiso F/0003</label>
    </div>

    <div class="modal-footer">
      <input type="submit" name="VentaPwa" class="btn btn-primary" value="Registrar Servicio">
    </div>
  </form>
</div>

<script>
function toggleTipoServicio(val){
  var grp = document.getElementById('tipo-servicio-group');
  var sel = document.getElementById('TipoServicio');
  if (!grp || !sel) return;
  if (String(val).toLowerCase() === 'funerario') {
    grp.style.display = 'block';
    sel.disabled = false;
  } else {
    grp.style.display = 'none';
    sel.disabled = true;
    sel.selectedIndex = 0;
  }
}

function mostrarCurpBeneficiario(){
  var sel = document.getElementById('tipo-select');
  var grp = document.getElementById('curp-beneficiario-group');
  if (!sel || !grp) return;
  grp.style.display = (sel.value === 'Beneficiario') ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function(){
  var prodSel = document.getElementById('Producto');
  if (prodSel) { toggleTipoServicio(prodSel.value); }

  // Forzar mayúsculas en CURP
  var curp = document.querySelector('input[name="ClaveCurp"]');
  if (curp) curp.addEventListener('input', function(){ this.value = this.value.toUpperCase(); });

  var curpBen = document.getElementById('curp-beneficiario');
  if (curpBen) curpBen.addEventListener('input', function(){ this.value = this.value.toUpperCase(); });

  // Inicial de beneficiario
  mostrarCurpBeneficiario();
});
</script>
