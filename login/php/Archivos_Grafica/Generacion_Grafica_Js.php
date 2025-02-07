<?
// directory in which the uploaded file will be moved
$uploadFileDir = 'php/AnalisisDatos/';
//Asignamos un valor a la variable b para la graficacion
$b = -2;
//Ingresamos al directorio para impmir las graficas que se requieren
if (is_dir($uploadFileDir)){
      // Abre un gestor de directorios para la ruta indicada
      $gestor = opendir($uploadFileDir);
      // Recorre todos los archivos del directorio
      while (($archivo = readdir($gestor)) !== false){
          //Sumamos 1 a b para que siga creciendo segun lso archivos que hay en el directorio
          if($b >= 0){
            $arrayName[] = $archivo;
          }
          $b++;
      }
}
//Imprimimos las funciones de las graficas
$a = 1;
while ($a <= $b){
  //Bajamos el inicio de a para igualar el array
  $Ar=$a-1;
  //Nombramos el archivo a buscar
  $Archivo = $uploadFileDir.$arrayName[$Ar];
  //Asignamos el nombre de la grafica con el nombre de el archivo
  $eps = explode("/", $Archivo);
  $sla = explode(".", $eps[2]);
  $scy = explode("_", $sla[0]);
  $TituloGrafica = $scy[1]." ".$scy[2]." ".$scy[3]." ".$scy[4]." ".$scy[5]." ".$scy[6];
  //Generamos las graficas en automatico
  $DivImpresion = "Grafica_".$a;
  $Grafica = "jsonData_".$a;
  $Funcion = "Funcion_".$a;
  // Imprimimos los script
  echo "
    <script type='text/javascript'>
        function ".$Funcion."() {
            var ".$Grafica." = $.ajax({
                url: '".$Archivo."',
                dataType: 'json',
                async: false
            }).responseText;
            var data = new google.visualization.DataTable(".$Grafica.");
            var chart = new google.visualization.PieChart(document.getElementById('".$DivImpresion."'));
            chart.draw(data, { width: 400, height: 300, title:'".$TituloGrafica."'});
        }
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(".$Funcion.");
    </script>
  ";
  $a++;
}
