<div class="MenuPrincipal">
  <?
  //Se comprueba el nivel del usuario
  $Niv = Basicas::BuscarCampos($mysqli,"Nivel","Empleados","IdUsuario",$_SESSION["Vendedor"]);
  //Se realiza un select para agregar estilos dependiendiendo de la pagina donde se encuentre el usuatio
  $CoMn = substr($_SERVER['PHP_SELF'], 7 , 20);
  if($CoMn == "Pwa_Principal.php"){
    $a1 = " background: #D7BDE2;
            pointer-events: none;
            cursor: default;
          ";
  }elseif($CoMn == "Pwa_Prospectos.php"){
    $a2 = " background: #D7BDE2;
            pointer-events: none;
            cursor: default;
          ";
  }elseif($CoMn == "Pwa_Registro_Pagos.php"){
    $a3 = " background: #D7BDE2;
            pointer-events: none;
            cursor: default;
          ";
  }elseif($CoMn == "Pwa_Clientes.php"){
    $a4 = " background: #D7BDE2;
            pointer-events: none;
            cursor: default;
          ";
  }elseif($CoMn == "Mesa_Herramientas.php"){
    $a5 = " background: #D7BDE2;
            pointer-events: none;
            cursor: default;
          ";
  }elseif($CoMn == "Pwa_Analisis_Ventas.php"){
    $a6 = " background: #D7BDE2;
            pointer-events: none;
            cursor: default;
          ";
  }elseif($CoMn == "Pwa_Sociales.php"){
    $a7 = " background: #D7BDE2;
            pointer-events: none;
            cursor: default;
          ";
  }
  // }elseif ($CoMn == "Mesa_Prospectos.php") {
  //   $a8 = " background: #D7BDE2;
  //           pointer-events: none;
  //           cursor: default;
  //         ";
  // }
  echo '
  <a class="BtnMenu" href="Pwa_Principal.php"><img src="assets/img/FlorKasu.png" style="'.$a1.';"></a>
  ';
  if($Niv == 1){
    //Boton de prospectos de venta
    echo '<a class="BtnMenu" href="Pwa_Analisis_Ventas.php"><img src="assets/img/analisis.png" style="'.$a6.';"></a>';
  }
  if($Niv != 5 AND $Niv != 3){
    //Boton de prospectos de venta
    echo '
          <a class="BtnMenu" href="Pwa_Prospectos.php"><img src="assets/img/usuario.png" style="'.$a2. ';"></a>
          <!-- <a class="BtnMenu" href="Mesa_Prospectos.php"><img alt="cartera" src="assets/img/analisis.png" style="' . $a8 . ';"></a> -->
          <a class="BtnMenu" href="Pwa_Sociales.php"><img src="assets/img/Sociales.png" style="'.$a7. ';"></a>
          ';
  }
  if($Niv != 3){
    //boton de cartera de clientes
    echo '
    <a class="BtnMenu" href="Pwa_Clientes.php"><img alt="cartera" src="assets/img/cartera.png" style="'.$a4.';"></a>
    ';
  }
  if($Niv != 7){
    //Boton de gestor de cobranza
    echo '
    <a class="BtnMenu" href="Pwa_Registro_Pagos.php"><img src="assets/img/cobranza.png" style="'.$a3.';"></a>
    ';
  }
  echo '
  <a class="BtnMenu" href="Mesa_Herramientas.php"><img src="assets/img/ajustes.png" style="'.$a5.';"></a>
  ';
  ?>
</div>
