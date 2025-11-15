<?php
//Redireccionamiento para no usar registro por ahora
//Desactivar para que los clientes puedan registrar su opiniones
//Se imprime directamente en la pagina prinicipal
header('Location: ../index.php');
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Opiniones</title>
    <link rel="stylesheet" href="css/style.css" type="text/css">
  </head>
  <body>

    <form id="frmOpn" action="php/funciones.php" method="post" enctype="multipart/form-data">
      <div class="center">

        <div class="col-25">
          <label for="txtNomOpn">Nombre</label>
        </div>
        <div class="col-75">
          <input type="text" id="txtNomOpn" name="txtNomOpn" >
        </div>
        <div class="col-25">
            <label>Servicio</label>
        </div>
        <div class="col-75">
          <select name="cbxSerOpn" id="cbxSerOpn">
              <option value="FUNERARIO">Funerario</option>
              <option value="UNIVERSITARIO">Universitario</option>
          </select><br>
        </div>
        <label>Opinion</label>
        <textarea name="txtComOpn" id="txtComOpn" rows="10" cols="40"></textarea>
        <div>
          <label for="uplFotOpn" class="subir"><i class="material-icons">add_a_photo</i>Subir Foto</label>
          <input type="file" id="uplFotOpn" name="uplFotOpn" onchange='cambiar()' style="display:none;" >
          <div id="info" ></div>
          <input class="env" type="submit" name="btnEnvOpn" value="Enviar" >
        </div>

      </div>
    </form>
  </body>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script type="text/javascript">
    function cambiar(){
        var pdrs = document.getElementById('uplFotOpn').files[0].name;
        document.getElementById('info').innerHTML = pdrs;
    }
    </script>
</html>
