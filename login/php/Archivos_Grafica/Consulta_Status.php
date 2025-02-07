<?
//Calcula el Panel de el usuario metricas de ventas y cobranza
//indicar que se inicia una sesion
session_start();
//inlcuir el archivo de funciones
require_once '../../eia/librerias.php';
//hacemos un while sobre los vendedores activos
$sql = "SELECT * FROM Status";
//Realiza consulta
$result = $mysqli->query($sql);
//create an array
$array = array();
$i = 0;
while($row = mysqli_fetch_assoc($result)){
  //Buscmos el nivel de el usuario
  $Nivel = Basicas::BuscarCampos($mysqli,'Nivel','Empleados','IdUsuario',$_SESSION["Vendedor"]);
  //Se realiza la operacion con los niveles
      if($Nivel >= 5){
          $unidades_vendidas = Basicas::ConUnCon($mysqli,'Venta','Usuario',$_SESSION["Vendedor"],'Status',$row['Nombre']);
      }elseif($Nivel <= 4){
        $b = Basicas::Max1DifDat($mysqli,'Nivel','Empleados','Nombre','Vacante');
        $a = $b;
        while ($a >= $Nivel) {
          //Buscamos en el siguiente nivel si hay asignaciones
          $ExiReg = Basicas::ConDosCon($mysqli,'Empleados','Equipo',$lider,'Nivel',$a,'Nombre','Vacante');
          //Seleccionamos la busqueda segun el nivel
          if($a == $b || !empty($ExiReg)){
            $sql1 = "SELECT * FROM Empleados WHERE Nivel = '".$a."' AND Nombre != 'Vacante'";
          }else{
            $sql1 = "SELECT * FROM Empleados WHERE Nivel = '".$a."' AND Nombre != 'Vacante' AND Id = '".$lider."'";
          }
          //Realiza consulta
          $res1 = $mysqli->query($sql1);
          //Si existe el registro se asocia en un fetch_assoc
          foreach ($res1 as $Reg1){
            $lider = $Reg1['Equipo'];
            //Se Suma las ventas de los Usuarios q tienen el Id del equipo
            $unidades_vendidas = $unidades_vendidas+Basicas::ConUnCon($mysqli,'Venta','Usuario',$Reg1['IdUsuario'],'Status',$row['Nombre']);
          }
          $a--;
        }
      }
    $array['cols'][] = array('type' => 'string');
    $array['rows'][] = array('c' => array( array('v'=> $row['Nombre']), array('v'=>(int)$unidades_vendidas)) );
}
echo json_encode($array);
