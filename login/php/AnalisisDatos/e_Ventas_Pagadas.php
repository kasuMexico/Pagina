<?
//Calcula el total de las polizas compradas por los clientes
//indicar que se inicia una sesion
session_start();
//inlcuir el archivo de funciones
require_once '../../../eia/librerias.php';
//create an array
$array = array();
  //Realizamos la busqueda de los status de la venta
  $sql1 = "SELECT * FROM Productos ";
  //Realiza consulta
  $res1 = $mysqli->query($sql1);
  //Si existe el registro se asocia en un fetch_assoc
  foreach ($res1 as $Reg1){
      $producto = $Reg1['Producto'];
      //Se Suma las ventas de los Usuarios q tienen el Id del equipo
      $unidades_vendidas = Basicas::ConUnCon($mysqli,'Venta','Producto',$producto,'Status','ACTIVO');
      //Insertamos el valor en el array
      $array['cols'][] = array('type' => 'string');
      $array['rows'][] = array('c' => array( array('v'=> $producto), array('v'=>(int)$unidades_vendidas)) );
  }
echo json_encode($array);
