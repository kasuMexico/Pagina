<form method="POST" action="php/Funcionalidad_Pwa.php">
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['Nombre'];?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </div>
    <div class="modal-body">
            <div id="Gps" style="display: none;"></div>
            <input type="number" name="IdVenta" value="<?PHP echo $Reg['Id'];?>" style="display: none;">
            <input type="text" name="IdVendedor" value="<?PHP echo $_POST['IdVendedor'];?>" style="display: none;">
            <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
            <input type="text" name="name" value="<?PHP echo $name;?>" style="display: none;">
            <?
              $mora = money_format('%.2n', Financieras::Mora($Pago1));
              if(empty($_POST['Promesa'])){
                  echo '
                  <p>Pagos pendientes <strong> '.$PagoPend.' Pagos</strong></p>
                  <p>Pago Normal periodo <strong>'.$Pago.'</strong></p>
                  <p>Pago con Mora <strong>'.$mora.'</strong></p>
                  ';
              }else{
                  echo '
                  <p>Promesa de pago <strong>'.money_format('%.2n',$_POST['Promesa']).'</strong></p>
                  ';
              }
            ?>
            <label for="exampleFormControlSelect1">Cantidad a pagar</label>
            <input class="form-control" type="number" name="Cantidad" placeholder="Cantidad" required>
            <label for="exampleFormControlSelect1">Seleciona la forma de pago</label>
            <select class="form-control" name="Status">
                <option value="Normal" selected>Pago Normal</option>
                <option value="<? echo $Pago1/10;?>">Pago con Mora</option>
            </select>
            <label for="exampleFormControlSelect1">Proximo Pago</label>
            <input class="form-control" type="date" name="Promesa" value="<? echo date("Y-m-d",strtotime("+ 14 days"));?>" required>
    </div>
    <div class="modal-footer">
        <input type="submit" name="Pago" class="btn btn-primary" value="Guardar Pago">
        <a href="<?echo $Gps;?>" class="btn btn-primary">IR a GPS</a>
    </div>
</form>
