<section class="section colored padding-top-70">
  <div class="container">
    <div class="row">
      <div class="decoration-bottom">
          <h2 class="section-title">
            <span>Selecciona el producto que mas se adecuae a tus necesidades</span>
          </h2>
        <br>
      </div>
    </div>
    <div class="row">
      <?php
      //Creamos la variables pricipales
      $cont = 1;
      //Contamos el no de  Articulos
      $MaxPro = $basicas->MaxDat($mysqli,"id","ContProd");
      //Se imprimen los comentarios
      while($cont <= $MaxPro){
        //Consulta para los artiulos
        $SqlPro="SELECT * FROM ContProd WHERE id =".$cont;
        //Si la consulta es verdadera imprime el articulo
        if ($ResArti = $mysqli -> query($SqlPro)){
          $Pro = $ResArti -> fetch_assoc();
          if($Pro['Id'] != $_GET['Art']){
            echo '
              <div class="col-lg-4 col-md-6 col-sm-12" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
                <div class="team-item">
                  <div class="team-content">
                    <br><br>
                    <div class="team-info">
                      <a href="productos.php?Art='.$Pro['Id'].'">
                        <h2 class="user-name" style="padding: 8px;"><strong>'.$Pro['Nombre'].'</strong></h2>
                        <div class="descri">'.$Pro['DescCorta'].'</div>
                      </a>
                      <div class="form-group">
                          <a href="productos.php?Art='.$Pro['Id'].'" class="main-button-slider"><strong>Conocer Más</strong></a>
                      </div>
                      <button type="button" data-toggle="modal" data-target="#'.$Pro['Producto'].'" style=" padding: 8px; border: transparent; background-color: transparent; color: #4A4645; font-size: .8em;">Ver las letras pequeñas</button>
                    </div>
                    <a href="productos.php?Art='.$Pro['Id'].'">
                    <div class="">
                      <img src="'.$Pro['Imagen_index'].'" alt="'.$Pro['DescCorta'].'" style="border-radius: 15px; height: 120px; width: 100px;">
                    </div>
                    </a>
                  </div>
                </div>
              </div>
                ';
          }
        }
        $cont++;
      }
      ?>
    </div>
  </div>
</section>
