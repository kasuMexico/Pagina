<section class="section colored padding-top-70">
  <div class="container">
    <div class="row">
      <div class="decoration-bottom">
          <h2 class="section-title">
            <span>Selecciona el producto que más se adecúa a tus necesidades</span>
          </h2>
        <br>
      </div>
    </div>
    <div class="row">
      <?php
      // Obtener el parámetro "Art" de la URL o asignar 0 si no existe
      $currentArt = isset($_GET['Art']) ? $_GET['Art'] : 0;
      echo "<!-- Debug: Valor de Art recibido: " . htmlspecialchars($currentArt) . " -->";

      // Inicializar el contador y obtener el número máximo de artículos de la tabla ContProd
      $cont = 1;
      $MaxPro = $basicas->MaxDat($mysqli, "id", "ContProd");
      echo "<!-- Debug: Número máximo de productos: " . htmlspecialchars($MaxPro) . " -->";

      // Recorrer cada producto
      while ($cont <= $MaxPro) {
          // Consulta para obtener el producto con id = $cont
          $SqlPro = "SELECT * FROM ContProd WHERE id = " . $cont;
          $ResArti = $mysqli->query($SqlPro);
          if ($ResArti) {
              $Pro = $ResArti->fetch_assoc();
              echo "<!-- Debug: Producto con ID " . htmlspecialchars($Pro['Id']) . " obtenido -->";
              // Si el ID del producto es diferente al parámetro "Art", se muestra el bloque
              if ($Pro['Id'] != $currentArt) {
                  echo '
                  <div class="col-lg-4 col-md-6 col-sm-12" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
                    <div class="team-item">
                      <div class="team-content">
                        <br><br>
                        <div class="team-info">
                          <a href="productos.php?Art=' . $Pro['Id'] . '">
                            <h2 class="user-name" style="padding: 8px;"><strong>' . htmlspecialchars($Pro['Nombre']) . '</strong></h2>
                            <div class="descri">' . htmlspecialchars($Pro['DescCorta']) . '</div>
                          </a>
                          <div class="form-group">
                              <a href="productos.php?Art=' . $Pro['Id'] . '" class="main-button-slider"><strong>Conocer Más</strong></a>
                          </div>
                          <button type="button" data-toggle="modal" data-target="#' . htmlspecialchars($Pro['Producto']) . '" style="padding: 8px; border: none; background-color: transparent; color: #4A4645; font-size: 0.8em;">Ver las letras pequeñas</button>
                        </div>
                        <a href="productos.php?Art=' . $Pro['Id'] . '">
                          <div class="">
                            <img src="' . htmlspecialchars($Pro['Imagen_index']) . '" alt="' . htmlspecialchars($Pro['DescCorta']) . '" style="border-radius: 15px; height: 120px; width: 100px;">
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>
                  ';
              }
          } else {
              echo "<!-- Debug: Error en la consulta para id $cont: " . $mysqli->error . " -->";
          }
          $cont++;
      }
      ?>
    </div>
  </div>
</section>
