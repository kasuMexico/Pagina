<form method="POST" action="<?PHP echo $_SERVER['PHP_SELF'];?>?Vt=2">
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?PHP echo $Reg['Nombre'];?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </div>
    <div class="modal-body">
      <input type="number" name="IdVenta" value="<?PHP echo $_POST['IdVenta'];?>" style="display: none;">
      <input type="text" name="IdVendedor" value="<?PHP echo $_POST['IdVendedor'];?>" style="display: none;">
      <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
      <input type="text" name="name" value="<?PHP echo $name;?>" style="display: none;">
       <?PHP
       //Select para usuarios
       if($Niv != 7){
         if($s56 > 0){
           $BtnPago = '<input type="submit" name="SelCte" class="btn btn-primary" value="Agregar Pago">';
         }
         $BtnCta = '<a type="button" class="btn btn-primary" href="Pwa_Estado_Cuenta.php?busqueda='.base64_encode($Reg['Id']).'">Estado de Cuenta</a>';
       }

        if($Niv <= 4){
            echo "<p> Ejecutivo de Ventas </p> <h2>".$Reg['Usuario']."</h2>";
        }
        echo "<p> Producto Contratado </p><h2>".$Reg['Producto']."</h2>";
        echo "<p> Liquidacion </p><h2>".$Saldo."</h2>";
        echo "<p> Status de la poliza </p><h2>".$Reg['Status']."</h2>";
        ?>
    </div>
    <div class="modal-footer">
    <?php
      //si el lciente ya no debe dinero no se imprime el boton
      echo $BtnPago.$BtnCta;
    ?>
    </div>
</form>
