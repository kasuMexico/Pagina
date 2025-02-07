<?
echo '
<form method="POST" action="php/Registro_Prospectos.php">
<div class="modal-body">
      <div id="Gps" style="display: none;"></div>
      <input type="number" name="Id" value="'.$Reg['Id'].'" style="display: none;">
      <input type="text" name="IdVendedor" value="'.$_POST['IdVendedor'].'" style="display: none;">
      <input type="text" name="Host" value="'.$_SERVER['PHP_SELF'].'" style="display: none;">
      <input type="text" name="name" value="'.$name.'" style="display: none;">
      <p>Ingresa las cantidades a cotizar</p>
';
      if($Reg['Servicio_Interes'] == "FUNERARIO"){
        echo '
        <label for="exampleFormControlSelect1">Bloque 0a29</label>
        <input class="form-control" type="number" name="a0a29" placeholder="Cantidad">
        <label for="exampleFormControlSelect1">Bloque 30a49</label>
        <input class="form-control" type="number" name="a30a49" placeholder="Cantidad">
        <label for="exampleFormControlSelect1">Bloque 50a54</label>
        <input class="form-control" type="number" name="a50a54" placeholder="Cantidad">
        <label for="exampleFormControlSelect1">Bloque 55a59</label>
        <input class="form-control" type="number" name="a55a59" placeholder="Cantidad">
        <label for="exampleFormControlSelect1">Bloque 60a64</label>
        <input class="form-control" type="number" name="a60a64" placeholder="Cantidad">
        <label for="exampleFormControlSelect1">Bloque 65a69</label>
        <input class="form-control" type="number" name="a65a69" placeholder="Cantidad">
        ';
      }elseif($Reg['Servicio_Interes'] == "UNIVERSITARIO"){
        echo '
        <label for="exampleFormControlSelect1">Niños a Registrar</label>
        <input class="form-control" type="number" name="Univ" placeholder="Cantidad" required>
        ';
      }
echo '
        <label for="exampleFormControlSelect1">Selecciona el Tipo de Pago</label>
        <select class="form-control" name="Pago">
          <option value="CREDITO">CREDITO</option>
          <option value="CONTADO">CONTADO</option>
          <option value="CREDITO">AYUNTAMIENTO</option>
        </select>
        <label for="exampleFormControlSelect1">Selecciona el Plazo</label>
        <select class="form-control" name="plazo">
          <option value="1">Contado</option>
          <option value="3">3 Meses</option>
          <option value="6">6 Meses</option>
          <option value="9">9 Meses</option>
          <option value="24">Fin Administracion</option>
        </select>
  </div>
  <div class="modal-footer">
    <input type="submit" name="EnviaPres" class="btn btn-primary" value="Enviar">
    <input type="submit" name="DescargaPres" class="btn btn-primary" value="Descargar" target="_blank" >
  </div>
</form>
';
