<?
//creamos una variable general para las funciones
$basicas = new Basicas();
/******************************  INICIO Registro entrada, descansos, comida y salida   *************************************/
$_SESSION["Entrada"] = date('09:00:00');
$_SESSION["Tolerancia"] = date('09:10:00');
$_SESSION["Salida"] = date('17:00:00');
$_SESSION["Aviso"] = date('10:00:00');
$_SESSION["Fecha"] = date('Y-m-d');
$HoraActual = date('H:i:s');
$hoy = $_SESSION["Fecha"];
$evDes = "Descanso";
$evOfi = "Oficina";
/**********************************Inicio de la funcion***********************************/

if(date('w',strtotime($hoy)) != 0 && date('w',strtotime($hoy)) != 6){
    $Vendedor = $basicas->BuscarCampos($mysqli,"Id","Empleados","IdUsuario",$_SESSION["Vendedor"]);
    //busca que no tenga registro del dia
    $existe = $basicas->ConUnCon($mysqli,"Asistencia","Fech_in",$hoy,"usuario_id",$Vendedor);
    $_SESSION["exis"] = $basicas->BuscarCampos($mysqli,"usuario_id","Asistencia","Fech_in",$hoy);
    $_SESSION["Registre"] = $basicas->Buscar2Campos($mysqli,"Salida","Asistencia","Fech_in",$hoy,"usuario_id",$Vendedor);
    if($existe == 0){
        $Color = "#04B431";
        $Texto = "Entrada";
        $val = "";
        $display = "none";
        if($_POST["entrada"] == 'Entrada'){
            $HoraActual = date('H:i:s');
            $retardo = date("H:i:s",(strtotime("00:00:00")+strtotime($HoraActual)-strtotime($_SESSION["Tolerancia"])));
            $antelacion = date("H:i:s",(strtotime("00:00:00")+strtotime($_SESSION["Entrada"])-strtotime($HoraActual)));//llegan antes de las 9
           if(strtotime($HoraActual) < strtotime($_SESSION["Entrada"]) && isset($_POST['Latitud']) && isset($_POST['Longitud'])){
            // if(strtotime($HoraActual) < strtotime($_SESSION["Entrada"])){
                $Vine = array(
                  "usuario_id"   => $Vendedor,
                  "Fech_in"   => $hoy,
                  "Entrada"   => $HoraActual,
                  "Antelacion"   => $antelacion
                );
                $_SESSION["llegue"] = $basicas->InsertCampo($mysqli,"Asistencia",$Vine);
                $_SESSION["exis"] = $basicas->BuscarCampos($mysqli,"usuario_id","Asistencia","Fech_in",$hoy);
                $Color = "#FFBF00";
                $Texto = $evDes;
                echo "<script>alert('Se registro tu entrada, llegaste antes');</script>";
                header("Refresh: 0; URL= $url");
           }else if(strtotime($HoraActual) > strtotime($_SESSION["Entrada"]) && strtotime($HoraActual) < strtotime($_SESSION["Salida"]) && isset($_POST['Latitud']) && isset($_POST['Longitud'])){
            // }else if(strtotime($HoraActual) > strtotime($_SESSION["Entrada"]) && strtotime($HoraActual) < strtotime($_SESSION["Salida"])){
                $Vine = array(
                  "usuario_id"   => $Vendedor,
                  "Fech_in"   => $hoy,
                  "Entrada"   => $HoraActual,
                  "Retardo"   => $retardo
                );
               if(strtotime($HoraActual) > strtotime($_SESSION["Entrada"]) && strtotime($HoraActual) < strtotime($_SESSION["Tolerancia"]) && isset($_POST['Latitud']) && isset($_POST['Longitud'])){
                // if(strtotime($HoraActual) > strtotime($_SESSION["Entrada"]) && strtotime($HoraActual) < strtotime($_SESSION["Tolerancia"])){
                    echo "<script>alert('Se registro tu entrada con tolerancia');</script>";
                    $_SESSION["llegue"] = $basicas->InsertCampo($mysqli,"Asistencia",$Vine);
                    $_SESSION["exis"] = $basicas->BuscarCampos($mysqli,"usuario_id","Asistencia","Fech_in",$hoy);
                    $Color = "#FFBF00";
                    $Texto = $evDes;
                    header("Refresh: 0; URL= $url");
                // }else if(strtotime($HoraActual) >= strtotime($_SESSION["Aviso"])){
               }else if(strtotime($HoraActual) >= strtotime($_SESSION["Aviso"]) && isset($_POST['Latitud']) && isset($_POST['Longitud'])){
                    echo "<script>alert('Se envio un mensaje de notificacion a tu supervisor con la hora de tu llegada')</script>";
                    $_SESSION["llegue"] = $basicas->InsertCampo($mysqli,"Asistencia",$Vine);
                    $_SESSION["exis"] = $basicas->BuscarCampos($mysqli,"usuario_id","Asistencia","Fech_in",$hoy);
                    $Color = "#FFBF00";
                    $Texto = $evDes;
                    $sqlTeam = "SELECT Equipo FROM Empleados WHERE Id = $Vendedor";
                    $resTeam = mysqli_query($mysqli, $sqlTeam);
                    if($regTeam=mysqli_fetch_assoc($resTeam)){
                        $sqlBoss = "SELECT * FROM Empleados WHERE Id = ".$regTeam['Equipo'];
                        $resBoss = mysqli_query($mysqli, $sqlBoss);
                        if($regBoss=mysqli_fetch_assoc($resBoss)){
                          $lat = $_POST['Latitud'];
                          $lon = $_POST['Longitud'];
                          $geo = "Lat: ".$lat." Lon: ".$lon;
                            $correo = $basicas->BuscarCampos($mysqli,"Mail","Contacto","id",$regBoss['IdContact']);
                            $nom = $basicas->BuscarCampos($mysqli,"Nombre","Empleados","IdUsuario",$_SESSION["Vendedor"]);
                            $maiil = Correo::Mensaje('Evento Inusual',$regBoss['Nombre'],$nom,"Entrada",$HoraActual,$geo,'','','','','','','','','','','','','');
                           Correo::EnviarCorreo($regBoss['Nombre'],$correo,'Retardo',$maiil);
                        }
                    }
                    header("Refresh: 0; URL= $url");
               }else if(!isset($_POST['Latitud']) && !isset($_POST['Longitud'])){
                   echo "<script>alert('Permite tu ubicacion para realizar esta accion');</script>";
                   $Color = "#04B431";
                   $Texto = "Entrada";
                   header("Refresh: 0; URL= $url");
                }else{
                    echo "<script>alert('Se registro tu entrada, llegas tarde');</script>";
                    $_SESSION["llegue"] = $basicas->InsertCampo($mysqli,"Asistencia",$Vine);
                    $_SESSION["exis"] = $basicas->BuscarCampos($mysqli,"usuario_id","Asistencia","Fech_in",$hoy);
                    $Color = "#FFBF00";
                    $Texto = $evDes;
                    header("Refresh: 0; URL= $url");
                }
           }else if(!isset($_POST['Latitud']) && !isset($_POST['Longitud'])){
               echo "<script>alert('Permite tu ubicacion para realizar esta accion');</script>";
               $Color = "#04B431";
               $Texto = "Entrada";
               header("Refresh: 0; URL= $url");
            }else{
                echo "<script type='text/javascript'>alert('Olvidaste registrar entrada, recuerda checar el dia de mañana');</script>";
            }
        }
    }else if($existe == 1 && $_SESSION["Registre"] == "00:00:00"){
        if(($_POST["Salida"] == 'Salida E' || $_POST["salida"] == 'Salida') && isset($_POST['Latitud']) && isset($_POST['Longitud'])){
    //    if($_POST["salida"] == 'Salida E' || $_POST["salida"] == 'Salida'){
            $Color = "#04B431";
            $Texto = "Entrada";
            $val = "disabled";
            $display = "none";
            $HoraActual = date('H:i:s');
            $extra = date("H:i:s",(strtotime("00:00:00")+strtotime($HoraActual)-strtotime($_SESSION["Salida"])));
           // if(strtotime($HoraActual) >= strtotime($_SESSION["Salida"]) && $_SESSION["Registre"] == "00:00:00"){
            if($_SESSION["Registre"] == "00:00:00"){
                $ven = $basicas->BuscarCampos($mysqli,"Id","Empleados","IdUsuario",$_SESSION["Vendedor"]);
                if($_POST["Salida"] == 'Salida E'){
                    $ya = $basicas->ActCampoSal($mysqli, "Asistencia", $HoraActual, "", $ven, "Salida", "Extra", "Fech_in", "Salida", $hoy, "00:00:00");
                }else{
                    $ya = $basicas->ActCampoSal($mysqli, "Asistencia", $HoraActual, $extra, $ven, "Salida", "Extra", "Fech_in", "Salida", $hoy, "00:00:00");
                }
                if($ya != 0){
                    header("Refresh: 0; URL= $url");
                }
            }
       }else if($_POST["entrada"] == $evDes && isset($_POST['Latitud']) && isset($_POST['Longitud'])){
        // }else if($_POST["entrada"] == $evDes){
    //        echo "<script>alert('No olvides registrar tu regreso a la oficina');</script>";
            $Color = "#04B431";
            $Texto = $evOfi;
            header("Refresh: 0; URL= $url");
       }else if($_POST["entrada"] == $evOfi && isset($_POST['Latitud']) && isset($_POST['Longitud'])){
        // }else if($_POST["entrada"] == $evOfi){
            $Color = "#FFBF00";
            $Texto = $evDes;
            header("Refresh: 0; URL= $url");
       // }else if(!isset($_POST['Latitud']) || !isset($_POST['Longitud'])){
       //     echo "<script>alert('Permite tu ubicacion para realizar esta accion');</script>";
       //     $Color = "#FFBF00";
       //     $Texto = $evDes;
       //     header("Refresh: 0; URL= $url");
        }else{
            $HoraActual = date('H:i:s');
            $ven = $_SESSION["Vendedor"];
            $sqlDes = "SELECT COUNT(*) FROM Eventos WHERE Usuario = '$ven' AND FechaRegistro like '$hoy%' AND Evento = '$evDes'";
            $sqlOfi = "SELECT COUNT(*) FROM Eventos WHERE Usuario = '$ven' AND FechaRegistro like '$hoy%' AND Evento = '$evOfi'";
            $resDes = mysqli_query($mysqli, $sqlDes);
            $resOfi = mysqli_query($mysqli, $sqlOfi);
            if($RegDes=mysqli_fetch_assoc($resDes)){
                if($RegOfi=mysqli_fetch_assoc($resOfi)){
                    $des = $RegDes['COUNT(*)'];
                    $ofi = $RegOfi['COUNT(*)'];

                }
            }
            if($des != $ofi){
                $Color = "#04B431";
                $Texto = $evOfi;
            }else{
                $Color = "#FFBF00";
                $Texto = $evDes;
            }
        }
    }else{
        $Color = "#04B431";
        $Texto = "Entrada";
        $val = "disabled";
        $display = "none";
    }
}else{
    $_SESSION["Registre"] = $basicas->Buscar2Campos($mysqli,"Salida","Asistencia","Fech_in",$hoy,"usuario_id",$Vendedor);
    $existe = $basicas->ConUnCon($mysqli,"Asistencia","Fech_in",$hoy,"usuario_id",$Vendedor);
	  $Color = "#04B431";
    $Texto = "Trabajar";
    $val = "";
    $HoraActual = date('H:i:s');
     if($_POST["entrada"] == 'Trabajar' && $existe == 0){
            if(isset($_POST['Latitud']) && isset($_POST['Longitud'])){
                $Vine = array(
                  "usuario_id"   => $Vendedor,
                  "Fech_in"   => $hoy,
                  "Entrada"   => $HoraActual
                );
                $_SESSION["llegue"] = $basicas->InsertCampo($mysqli,"Asistencia",$Vine);
                $_SESSION["exis"] = $basicas->BuscarCampos($mysqli,"usuario_id","Asistencia","Fech_in",$hoy);
                $Color = "#FFBF00";
                $Texto = "Descanso";
                echo "<script>alert('Se registro tu entrada');</script>";
                header("Refresh: 0; URL= $url");
            }
      }else if($existe == 1 && $_SESSION["Registre"] == "00:00:00"){
          if($_POST["entrada"] == $evDes){
              echo "<script>alert('No olvides registrar tu regreso');</script>";
              $Color = "#04B431";
              $Texto = $evOfi;
              header("Refresh: 0; URL= $url");
          }else if($_POST["entrada"] == $evOfi){
              $Color = "#FFBF00";
              $Texto = $evDes;
              header("Refresh: 0; URL= $url");
          }else{
              $sqlDes = "SELECT COUNT(*) FROM Eventos WHERE usuario = '".$_SESSION["Vendedor"]."' AND FechaRegistro like '".$hoy."%' AND Evento = '".$evDes."'";
              $sqlOfi = "SELECT COUNT(*) FROM Eventos WHERE usuario = '".$_SESSION["Vendedor"]."' AND FechaRegistro like '".$hoy."%' AND Evento = '".$evOfi."'";
              $resDes = mysqli_query($mysqli, $sqlDes);
              $resOfi = mysqli_query($mysqli, $sqlOfi);
              if($RegDes=mysqli_fetch_assoc($resDes)){
                  if($RegOfi=mysqli_fetch_assoc($resOfi)){
                      $des = $RegDes['COUNT(*)'];
                      $ofi = $RegOfi['COUNT(*)'];
                  }
              }
              if($des != $ofi){
                  $Color = "#04B431";
                  $Texto = $evOfi;
              }else{
                $Color = "#FFBF00";
                $Texto = $evDes;
              }
            }
          }

}
/************************         FIN Registro entrada, descansos, comida y salida    *******************************************/
/**************************        INICIO de Cronometro        ******************************************/
  $hrEntrada = $basicas->Buscar2Campos($mysqli,"Entrada","Asistencia","Fech_in",$hoy,"usuario_id",$Vendedor);
  if($hrEntrada != ""){
      $nomVen = $_SESSION["Vendedor"];
      $sqlCont = "SELECT COUNT(*) FROM Eventos WHERE Usuario = '".$nomVen."' AND FechaRegistro like '".$hoy."%' AND (Evento = 'Descanso' OR Evento = 'Oficina')";
      $resCon = mysqli_query($mysqli, $sqlCont);
      if($RegCon=mysqli_fetch_assoc($resCon)){
          $queEv = $mysqli -> query("SELECT FechaRegistro FROM Eventos WHERE Usuario = '".$_SESSION["Vendedor"]."' AND FechaRegistro like '".$hoy."%' AND (Evento = 'Descanso' OR Evento = 'Oficina')");
          $cont = 0;
          while($arrEv =  mysqli_fetch_array($queEv)){
              if($cont == 0){
                  $var1 .= $arrEv[0];
              }else{
                  $var1 .= ",".$arrEv[0];
              }
              $cont++;
          }
          $des = explode( ',', $var1);
          $resta = 0;
          if($RegCon['COUNT(*)'] % 2 == 0){
              $resta = 0;
          }else{
              $resta = 1;
          }
          for($i = 0;$i< count($des)-$resta;$i++){
              $hDes = substr($des[$i],10);
              $hOfi = substr($des[$i+1],10);
              $res += (strtotime(date($hOfi)) - strtotime(date($hDes)));
              $i++;
          }
          $res *= 1000;
      }
      $hInicio = date('F')." ".date('d').", ".date('Y')." ".$hrEntrada;
  }
  function segToMin($seg) {
      $horas = floor($seg / 3600);
      $minutos = floor(($seg - ($horas * 3600)) / 60);
      $segundos = $seg - ($horas * 3600) - ($minutos * 60);

      return $horas . ':' . $minutos . ":" . $segundos;
  }
  /**************************        FIN de Cronometro          ******************************************/
