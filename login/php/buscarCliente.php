<?php
  session_start();
  require_once '../../eia/librerias.php';
  //inlcuir el archivo de funciones
  if(isset($_GET['nombre'])){
    $nombre = $_GET['nombre'];
    echo "
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css'>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js'></script>
    ";
    $sql="SELECT * FROM Venta WHERE Nombre LIKE '%$nombre%' and Status != 'ACTIVO' or 'ACTIVACION' or 'FALLECIDO'";
    $rows = mysqli_query($mysqli, $sql);
    if($rows->num_rows != 0){
      echo "<ul class='collapsible'>";
      while($row =  mysqli_fetch_array($rows)){
        echo "
        <li>
          <div class='collapsible-header'>
            <i class='material-icons'>person_add</i>
            ".$row['Nombre']."<span style='margin-left:1.5em; color:green; font-size:.55em;'>".$row['Status']."</span>
          </div>
          <div class='collapsible-body' style='display:inline;'>
            <div class='row'>
              <div class='col s12'>
                <form method='POST' action='php/Funcionalidad_Pwa.php'>
                  <h6 class='modal-title' id='exampleModalLabel'>Registrar Pago</h6>
                  <p>Monto de Adeudo:".money_format('%.2n',$row['Subtotal'])."</p>
                  <div id='Gps' style='display: none;'></div>
                  <input type='number' name='IdVenta' value='".$row['Id']."' style='display: none;'>
                  <p>Cantidad a pagar</p>
                  <input type='number' name='Cantidad' placeholder='Cantidad' required>
                  <br>
                  <p>Proximo Pago</p>
                  <input type='date' name='Promesa' required>
                  <input type='submit' name='Pago' class='btn btn-primary' value='Guardar Pago'>
                </form>
              </div>
            </div>
          </div>
        </li>
        ";
      }
      echo "</ul>";
      echo "
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          var elems = document.querySelectorAll('.collapsible');
          var instances = M.Collapsible.init(elems);
        });
      </script>
      ";
    }else{
      echo "No se han encontrado coincidencias";
    }
  }else{
    echo "No se han encontrado coincidencias";
  }
?>
