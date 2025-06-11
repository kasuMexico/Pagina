<?
echo '
<form action="https://kasu.com.mx/login/php/Registro_Prospectos.php" method="post">
  <input type="text" name="Host" value="'.$_SERVER['PHP_SELF'].'" style="display: none;">
  <input type="text" name="IdAsignacion" value="'.$IdAsignacion.'" style="display: none;">
  <input type="text" name="name" value="'.$name.'" style="display: none;">
      <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Registrar Nuevo Prospecto</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
      </div>
      <div class="modal-body">
        <label>Nombre</label>
        <input class="form-control" type="text" name="name" placeholder="Nombre" required>
        <label>E-mail</label>
        <input class="form-control" type="email" name="Mail" placeholder="Correo electronico" >
        <label>Telefono</label>
        <input class="form-control" type="tel" name="Telefono" placeholder="Telefono" required>
        <label>Fecha de Nacimiento</label>
        <input class="form-control" type="date" name="FechaNac" placeholder="Fecha nacimiento" >
        <label>Tipo de Servicio</label>
        <select class="form-control" name="Servicio">
            <option value="FUNERARIO">GASTOS FUNERARIOS</option>
            <option value="UNIVERSITARIO">INVERSION UNIVERSITARIA</option>
            <option value="RETIRO">AHORRO PARA EL RETIRO</option>
            <option value="DISTRIBUIDOR">DISTRIBUIDORES</option>
        </select>
        ';
        if(empty($name)){
          echo '
          <input type="text" name="Origen" value="vta" style="display: none;">
          ';
        }else{
          echo'
          <label>Origen</label>
          <select class="form-control" name="Origen">
              <option value="fb">Facebook</option>
              <option value="Gg">Google</option>
              <option value="hub">HubSpot</option>
              <option value="vta">Vendedor</option>
          </select>
          ';
        }
        echo '
        <div class="modal-footer">
            <input type="submit" name="prospectoNvo" class="btn btn-primary" value="Registrar y enviar">
        </div>
  </form>
  ';
?>
