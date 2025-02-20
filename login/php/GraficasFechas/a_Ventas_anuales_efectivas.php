<?
//indicar que se inicia una sesion
session_start();
//inlcuir el archivo de funciones
require_once '../../../eia/librerias.php';
//Fecha inicial
$Fech0 = $basicas->MinDat($mysqli,'FechaRegistro','Venta');
$Fecha = date("d-m-Y",strtotime('first day of january '.date("Y",strtotime($Fech0))));
//Ultimo dia permanente
  $i = 0;
  //Realizamos la busqueda de los status de la venta
  $sql1 = "SELECT * FROM Productos ";
  //Realiza consulta
  $res1 = $mysqli->query($sql1);
  //Si existe el registro se asocia en un fetch_assoc
  foreach ($res1 as $Reg1){
        //Array de los productos
        $Prod[$i] = $Reg1['Producto'];
        //Buscamos los años que se ha vendido
        $c = 0;
        while ($c <= 6) {
          //primer dia
          $Fe2a = date("Y-m-d",strtotime($Fecha.'+ '.$c.' Year'));
          //Ultimo dia de el año
          $Fecha2 = date("Y-m-d",strtotime('last day of December'.date("Y",strtotime($Fe2a))));
          //Buscamos los productos por el año
          $UVen[] = $unidades_vendidas = $basicas->CuentaFechas($mysqli,'Venta','Producto',$Reg1['Producto'],'FechaRegistro',$Fecha2,'FechaRegistro',$Fe2a,'Status','PREVENTA');
          //Año de venta y creacion de array
          $Año[$c] = $aNOi = date("Y",strtotime($Fe2a));
        $c++;
      }
      $i++;
  }
  //Variables para inicial las cadenas de texto
  $ini = "['Base','";
  $in2 = "['";
  $Med = "','";
  $Me2 = ",";
  $Fin = "'],";
  $Fi2 = "],";
  //COntador de los array
  $Nu = count($Prod);
  $Ne = count($Año);
  $Uv = count($UVen);
