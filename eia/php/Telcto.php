<?
//definimos la zona horaria por default
date_default_timezone_set('America/Mexico_City');
$datHora = date("H:i:00");
$datFecha = date("Y-m-d");
$arrDatTod = preg_split("/[\s,-]+/",$datFecha);
$arrDatHou = preg_split("/[\s,:]+/",$datHora);
$datHActual = strtotime($datHora);
$datHSalida = strtotime("17:00:00");
$datHEntrada = strtotime("09:00:00");
/*/Consulta de los dias festivos
$resQueDF = $mysqli -> query ("SELECT * FROM DiasFestivos");
$cont=0;
while ($arrResDF = mysqli_fetch_array($resQueDF)) {
    // ojo cuando uses un array usa 'comillas sencillas'
    if($arrResDF['diaFest'] == $datFecha){
        $cont++;
    }
}
//Imprimimos el telefono
*/
if($cont==0){
    //echo "no es dia feriado <br>";
    if(date('w',strtotime($datFecha)) != 0 && date('w',strtotime($datFecha)) != 6){
        //echo "no es sabado o domingo <br>";
        if($datHActual <= $datHSalida && $datHActual >= $datHEntrada){
            //Creamos un for que nos reccorra los empleados de atencion al cliente
            //Buscamos los usuarios nivel 2 o mesas de control
            $venta = "SELECT * FROM Empleados WHERE Nivel = '2'";
            //Realiza consulta
                $res = mysqli_query($mysqli, $venta);
            //Si existe el registro se asocia en un fetch_assoc
                if($Reg=mysqli_fetch_assoc($res)){
                    //asignamos un telefono que este disponible
                    if($Reg['Telefono'] == 0){
                        //Telefono de OFICINA
                        //$tel = Basicas::BuscarCampos($mysqli,"Telefono","Contacto","Id",$Reg['IdContacto']);
                        $tel = "7122612898";
                    }
                }
        }else{
            //echo "No esta en el horario de oficina<br>";
            $tel = "7121977370";
        }
    }else{
        //echo "Es sabado o domingo";
        $tel = "7122612898";
    }
}else{
    //echo "Es dia Feriado";
    $tel = "7121977370";
}
