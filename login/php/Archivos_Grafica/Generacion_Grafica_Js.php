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
  $Ar = $a - 1;

  // evita índices fuera de rango
  if (!isset($arrayName[$Ar])) { $a++; continue; }

  //Nombramos el archivo a buscar
  $Archivo = $uploadFileDir . $arrayName[$Ar];

  //Título robusto a partir del nombre del archivo
  $filename = basename($Archivo);                           // p.ej. "grafica_Ventas_Mes_2025.json"
  $base     = pathinfo($filename, PATHINFO_FILENAME);       // "grafica_Ventas_Mes_2025"
  $parts    = explode('_', $base);
  $TituloGrafica = (count($parts) > 1) ? implode(' ', array_slice($parts, 1)) : $base;

  //Generamos las graficas en automatico
  $DivImpresion = "Grafica_".$a;
  $Grafica      = "jsonData_".$a;
  $Funcion      = "Funcion_".$a;

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
            chart.draw(
              data,
              { width: 400, height: 300, title: ".json_encode($TituloGrafica)." }
            );
        }
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(".$Funcion.");
    </script>
  ";
  $a++;
}
