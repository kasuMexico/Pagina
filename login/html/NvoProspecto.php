<form action="https://kasu.com.mx/login/php/Registro_Prospectos.php" method="post" autocomplete="off">
  <!-- Slots de GPS y Fingerprint -->
  <div id="Gps"></div>
  <div data-fingerprint-slot></div>
  <!-- Hidden context -->
  <input type="hidden" name="nombre"   value="<?php echo htmlspecialchars($nombre ?? '', ENT_QUOTES, 'UTF-8'); ?>">
  <input type="hidden" name="Host"     value="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
  <input type="hidden" name="IdVenta"  value="<?php echo isset($Reg['Id'])     ? (int)$Reg['Id']     : ''; ?>">
  <input type="hidden" name="IdContact"value="<?php echo isset($Recg['id'])    ? (int)$Recg['id']    : ''; ?>">
  <input type="hidden" name="IdUsuario"value="<?php echo isset($Recg1['id'])   ? (int)$Recg1['id']   : ''; ?>">
  <input type="hidden" name="Producto" value="<?php echo htmlspecialchars($Reg['Producto'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
  <?php if (!empty($_SESSION['csrf'])): ?>
    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8'); ?>">
  <?php endif; ?>

  <div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Registrar Nuevo Prospecto</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>

  <div class="modal-body">
    <div class="form-group mb-2">
      <label class="mb-1">Clave CURP Prospecto</label>
      <input class="form-control text-uppercase" type="text" name="CURP" placeholder="CLAVE CURP" pattern="[A-Za-z0-9]{18}">
    </div>

    <div class="form-group mb-2">
      <label class="mb-1">E-mail</label>
      <input class="form-control" type="email" name="Email" placeholder="Correo electrónico">
    </div>

    <div class="form-group mb-2">
      <label class="mb-1">Teléfono</label>
      <input class="form-control" type="tel" name="Telefono" placeholder="10 dígitos" required inputmode="numeric" pattern="[0-9]{10}">
    </div>

    <?php if (!isset($Metodo) || $Metodo === 'Mesa'): ?>
    <!-- Selección de origen visible cuando $Metodo == 'Mesa' -->
      <div class="form-group mb-2">
        <label class="mb-1">Origen</label>
        <select class="form-control" name="Origen" required>
          <option value="fb">Facebook</option>
          <option value="Gg">Google</option>
          <option value="hub">HubSpot</option>
          <option value="Vtas">Vendedor</option>
        </select>
      </div>
    <?php else: ?>
      <!-- Origen oculto con el valor de $Metodo -->
      <input type="hidden" name="Origen" value="<?php echo htmlspecialchars($Metodo, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    
    <!-- Lanzamos el select con base en los niveles -->
    <div class="form-group mb-0">
      <label class="mb-1">El usuario está interesado en</label>
      <select class="form-control" name="Servicio" required>
        <option value="FUNERARIO">GASTOS FUNERARIOS</option>
        <option value="RETIRO">AHORRO PARA EL RETIRO</option>
        <? if($Niv == 1 || $Niv == 3):?>
        <option value="SEGURIDAD">GASTOS FUNERARIOS OFICIALES</option>
        <option value="TRANSPORTE">GASTOS FUNERARIOS SERVICIO DE TRANSPORTE</option>
        <? elseif($Niv <= 4):?>
        <option value="DISTRIBUIDOR">SER DISTRIBUIDOR</option>
        <? endif;?>
      </select>
    </div>
  </div>

  <div class="modal-footer">
    <input type="submit" name="prospectoNvo" class="btn btn-primary" value="Registrar y enviar">
  </div>
</form>
