<?php
//inicio sesion
session_start();
//Validar si existe la session y redireccionar
  if(isset($_GET['name'])){
      header('Location: https://kasu.com.mx/');
  }
//Incluye la conexion a la base
// require_once('../../eia/php/Funciones_kasu.php');
//require_once('../../eia/Conexiones/cn_vtas.php');
require_once('../../eia/librerias.php');
    //Se buscan los datos del cliente
    $IdVt = $basicas->BuscarCampos($mysqli,"IdContact","Usuario","ClaveCurp",$_SESSION['txtCurp_ActIndCli']);
    //Si existe el registro se asocia en un fetch_assoc
    $sql = "SELECT * FROM Contacto WHERE Id = '".$IdVt."'";
    //Realiza consulta
        $recs = mysqli_query($mysqli, $sql);
    //Si existe el registro se asocia en un fetch_assoc
        if($Recg=mysqli_fetch_assoc($recs)){
          //realizamos la consulta
          $venta = "SELECT * FROM Venta WHERE IdContact = '".$IdVt."'";
          //Realiza consulta
              $res = mysqli_query($mysqli, $venta);
              if($Reg=mysqli_fetch_assoc($res)){
                  //Buscamos los datos de contacto de el clientes
              }
        }
    //alertas de correo electronico
    require_once '../../login/php/Selector_Emergentes_Ml.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="KASU es una empresa dedicada a prestar servicios a futuro, mediante plataformas tecnologicas llevamos servicios a las comunidades mas alejadas">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900">
    <link rel="icon" href="../../assets/images/logo.png">
    <title>KASU| Tus Datos</title>
    <!-- Additional CSS Files -->
    <link rel="stylesheet" type="text/css" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../../assets/css/font-awesome.css">
    <link rel="stylesheet" href="../../assets/css/templatemo-softy-pinko.css">
</head>
<body>
    <div class="welcome-area" id="welcome">
      <div class="container">
        <div class="row">
          <div class="offset-xl-3 col-xl-6 offset-lg-2 col-lg-8 col-md-12 col-sm-12">
            <div class="features-small-item">
              <h5 class="features-title">Actualizar mis datos</h5>
                   <form id="ActDatCli" method="post" action="/login/php/Funcionalidad_Pwa.php">
                     <div class="modal-body">
                       <input type="number" name="IdVenta" value="<?PHP echo $Reg['Id'];?>" style="display:none ;">
                       <input type="number" name="IdContact" value="<?PHP echo $Recg['id'];?>" style="display:none ;">
                       <input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                       <input type="text" name="name" value="<?PHP echo $name;?>" style="display: none;">
                       <label for="exampleFormControlSelect1">Nombre</label>
                       <input class="form-control" disabled type="text" name="Unico" value="<?php echo $Reg['Nombre'];?>">
                       <label for="exampleFormControlSelect1">Tipo de Servicio</label>
                       <input class="form-control" disabled type="text" name="Producto" value="<?php echo $Reg['TipoServicio'];?>">
                       <label for="exampleFormControlSelect1">Selecciona el Tipo de Servicio</label>
                       <select class="form-control" name="TipoServ">
                         <option value="Tradicional">TRADICIONAL</option>
                         <option value="Cremacion">CREMACION</option>
                         <option value="Ecologico">ECOLOGICO</option>
                       </select>
                       <label for="exampleFormControlSelect1">Direccion</label>
                       <input class="form-control" type="text" name="Direccion" value="<?php echo $Recg['Direccion']; ?>">
                       <label for="exampleFormControlSelect1">Telefono</label>
                       <input class="form-control" type="text" name="Telefono" value="<?php echo $Recg['Telefono']; ?>">
                       <label for="exampleFormControlSelect1">Email</label>
                       <input class="form-control" type="text" name="Email" value="<?php echo $Recg['Mail']; ?>">
                     </div>
                     <div>
                       <input type="submit" name="CamDat" class="btn btn-primary" value="Modificar Datos">
                       <a href="https://kasu.com.mx/" class="btn btn-primary">Cerrar y salir</a>
                     </div>
                   </form>
            </div>
        </div>
      </div>
    </div>
  </div>
  <!--************************************************************-->
  <script src="../../assets/js/jquery-2.1.0.min.js"></script>
 	<!-- Bootstrap -->
 	<script src="../../assets/js/popper.js"></script>
 	<script src="../../assets/js/bootstrap.min.js"></script>
</body>
</html>
