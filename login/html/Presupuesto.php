<?php
$hasEmail = !empty($Reg['Email']) || !empty($_POST['Mail'] ?? '');
$serv = $Reg['Servicio_Interes'] ?? '';
//Calculamos la edad de el cliente para determinar el costo
$Edad = $basicas->ObtenerEdad($Reg['Curp']);
$ProdSel = $basicas->ProdFune($Edad); //Calculamos el producto
$Costo  = $basicas->BuscarCampos($mysqli, "Costo", "Productos", "Producto", $ProdSel); //Obtenemos el precio del producto

?>
<form method="POST" action="php/Registro_Prospectos.php" autocomplete="off">
  <!-- *********************************************** Bloque de registro de Eventos ************************************************************************* -->
  <div style="display: none;" id="Gps"></div> <!-- Div que lanza el GPS -->
  <div data-fingerprint-slot></div> <!-- DIV que lanza el Finger Print -->
  <input type="text" name="nombre" value="<?php echo $nombre; ?>" style="display: none;"> <!-- nombre que busque para esta pantalla -->
  <input type="text" name="Host" value="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: none;"> <!-- Host de donde estoy enviando la peticion -->
  <input type="number" name="IdVenta" value="<?php echo $Reg['Id'] ?? ''; ?>" style="display: none;"> <!-- Id de Venta Seleccionado -->
  <input type="number" name="IdContact" value="<?php echo $Recg['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Contacto Seleccionado -->
  <input type="number" name="IdUsuario" value="<?php echo $Recg1['id'] ?? ''; ?>" style="display: none;"> <!-- Id de Usuario Seleccionado -->
  <input type="text" name="Producto" value="<?php echo $Reg['Producto'] ?? ''; ?>" style="display: none;"> <!-- Producto de el cliente Seleccionado -->
  <!-- ********************************************** Bloque de registro de Eventos ************************************************************************* -->
  <div class="modal-body">
    <input type="hidden" name="Id"         value="<?php echo (int)($Reg['Id'] ?? 0); ?>">
    <input type="hidden" name="IdVendedor" value="<?php echo htmlspecialchars($_POST['IdVendedor'] ?? '', ENT_QUOTES,'UTF-8'); ?>">
    <input type="hidden" name="Host"       value="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] ?? '', ENT_QUOTES,'UTF-8'); ?>">
    <input type="hidden" name="name"       value="<?php echo htmlspecialchars($name ?? '', ENT_QUOTES,'UTF-8'); ?>">
    <?php if (!empty($_SESSION['csrf'])): ?>
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'], ENT_QUOTES,'UTF-8'); ?>">
    <?php endif; ?>

    <p class="mb-2">Que producto desea cotizar tu cliente</p>

    <?php if ($serv === 'FUNERARIO'): ?>
      <div class="form-group">
        <label class="mr-3"><input type="radio" name="tipo_plan" value="INDIVIDUAL" checked> Individual</label>
        <label><input type="radio" name="tipo_plan" value="FAMILIAR"> Familiar</label>
      </div>

      <div id="plan-individual">
        <label class="mb-1">Edad</label>
        <h4 class="text-center"><strong><?echo $Edad;?> Años</strong></h4>
        <input type="hidden" name="Edad" value="<?echo $Edad;?>">
        <label class="mb-1">Precio de Contado</label>
        <h4 class="text-center"><strong>$ <?echo number_format($Costo, 2)?></strong></h4>
      </div>

      <div id="plan-familiar" style="display:none">
        <label class="mt-3 d-block">Cantidad de pólizas por bloque</label>
        <label>0–29</label>   <input class="form-control" type="number" name="a0a29"  min="0" placeholder="Cantidad">
        <label>30–49</label>  <input class="form-control" type="number" name="a30a49" min="0" placeholder="Cantidad">
        <label>50–54</label>  <input class="form-control" type="number" name="a50a54" min="0" placeholder="Cantidad">
        <label>55–59</label>  <input class="form-control" type="number" name="a55a59" min="0" placeholder="Cantidad">
        <label>60–64</label>  <input class="form-control" type="number" name="a60a64" min="0" placeholder="Cantidad">
        <label>65–69</label>  <input class="form-control" type="number" name="a65a69" min="0" placeholder="Cantidad">
      </div>

    <?php elseif ($serv === 'UNIVERSITARIO'): ?>
      <label>Niños a registrar</label>
      <input class="form-control" type="number" name="Univ" min="1" placeholder="Cantidad" required>
    <?php endif; ?>

    <label class="mt-3">Selecciona el Tipo de Pago</label>
    <select class="form-control" name="Pago" id="pago" required>
      <option value="CREDITO">CRÉDITO</option>
      <option value="CONTADO">CONTADO</option>
    </select>

    <div id="plazo-row" class="mt-2">
      <label>Selecciona el Plazo</label>
      <select class="form-control" name="plazo" id="plazo" required>
        <option value="1">Contado</option>
        <option value="3">3 Meses</option>
        <option value="6">6 Meses</option>
        <option value="9">9 Meses</option>
      </select>
    </div>
  </div>

  <div class="modal-footer">
    <button type="submit" name="DescargaPres" class="btn btn-secondary" formtarget="_blank">Descargar PDF</button>
    <?php if ($hasEmail): ?>
      <button type="submit" name="EnviaPres" class="btn btn-primary">Enviar</button>
    <?php endif; ?>
  </div>
</form>

<script>
// Plan FUNERARIO toggle
document.addEventListener('change', function(e){
  if (e.target && e.target.name === 'tipo_plan') {
    var fam = document.getElementById('plan-familiar');
    var ind = document.getElementById('plan-individual');
    if (e.target.value === 'FAMILIAR') { fam.style.display='block'; ind.style.display='none'; }
    else { fam.style.display='none'; ind.style.display='block'; }
  }
});

// Ocultar plazo cuando pago = CONTADO
(function(){
  var pago  = document.getElementById('pago');
  var plazo = document.getElementById('plazo');
  var row   = document.getElementById('plazo-row');

  function syncPlazo(){
    var contado = pago.value === 'CONTADO';
    row.style.display = contado ? 'none' : '';
    plazo.required = !contado;
    if (contado) plazo.value = '1';
    else if (plazo.value === '1') plazo.value = '3';
  }
  pago.addEventListener('change', syncPlazo);
  syncPlazo();
})();
</script>