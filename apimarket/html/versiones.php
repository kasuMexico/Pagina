<?php
/********************************************************************************************
 * Qué hace: Renderiza el historial de versiones mostrando primero la versión más reciente
 *           y un resumen de evolución de versiones anteriores.
 * Fecha: 04/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

// 1) Resolver nombre lógico del doc: "doc_payments.php" -> "payments"
$filename  = basename($_SERVER['PHP_SELF']);
$extension = pathinfo($filename, PATHINFO_EXTENSION);
$basename  = basename($filename, '.' . $extension);
$word      = substr($basename, (int)strpos($basename, '_') + 1);

// 2) Traer TODAS las filas de esta API
$wordEsc = $mysqli->real_escape_string($word);
$sql     = "SELECT * FROM ContApiMarket WHERE Nombre = '$wordEsc'";
$res     = mysqli_query($mysqli, $sql);

// 3) Normalizar resultados en arreglo
$rows = [];
while ($r = mysqli_fetch_assoc($res)) {
  $rows[] = $r;
}

// 4) Función para parsear fechas tipo "4 Nov 2025" en timestamp seguro
function parse_fecha_es(?string $s): int {
  if (!$s) return 0;
  $s = trim($s);
  // Map de meses abreviados ES -> número
  static $mes = [
    'ENE'=>1,'FEB'=>2,'MAR'=>3,'ABR'=>4,'MAY'=>5,'JUN'=>6,
    'JUL'=>7,'AGO'=>8,'SEP'=>9,'OCT'=>10,'NOV'=>11,'DIC'=>12
  ];
  // Tokens esperados: "d M Y"
  $parts = preg_split('/\s+/', $s);
  if (count($parts) !== 3) return 0;
  $d   = (int)$parts[0];
  $m   = strtoupper(substr($parts[1],0,3));
  $y   = (int)$parts[2];
  $mm  = $mes[$m] ?? 0;
  if ($mm === 0 || $d === 0 || $y === 0) return 0;
  return (int)strtotime(sprintf('%04d-%02d-%02d', $y, $mm, $d));
}

// 5) Ordenar por fecha de Release descendente; si empata, por versión “V#”
usort($rows, function($a, $b){
  $ta = parse_fecha_es($a['ReleaseApi'] ?? '');
  $tb = parse_fecha_es($b['ReleaseApi'] ?? '');
  if ($ta !== $tb) return $tb <=> $ta;
  // fallback: “V2” > “V1”
  $va = (int)preg_replace('/\D+/', '', (string)($a['VersionBlo'] ?? '0'));
  $vb = (int)preg_replace('/\D+/', '', (string)($b['VersionBlo'] ?? '0'));
  return $vb <=> $va;
});

// 6) Tomar la última versión disponible
$latest = $rows[0] ?? [
  'VersionBlo'  => '',
  'ReleaseApi'  => '',
  'Description' => '',
  'Versiones'   => '',
  'Nombre'      => '',
  'Live'        => '',
  'Sandbox'     => '',
  'Descripcion' => ''
];

// 7) Variables escapadas para la cabecera de “última versión”
$ver       = htmlspecialchars((string)($latest['VersionBlo']  ?? ''), ENT_QUOTES, 'UTF-8');
$release   = htmlspecialchars((string)($latest['ReleaseApi']  ?? ''), ENT_QUOTES, 'UTF-8');
$descLast  = (string)($latest['Description'] ?? ''); // puede venir con HTML formateado
$versiones = (string)($latest['Versiones']   ?? '');
$Introduccion  = (string)($latest['Introduccion'] ?? ''); // puede venir con HTML formateado

// 8) Datos generales (pueden ser iguales en todas las filas)
$nombre  = htmlspecialchars((string)($latest['Nombre']   ?? ''), ENT_QUOTES, 'UTF-8');
$live    = htmlspecialchars((string)($latest['Live']     ?? ''), ENT_QUOTES, 'UTF-8');
$sandbox = htmlspecialchars((string)($latest['Sandbox']  ?? ''), ENT_QUOTES, 'UTF-8');

// 9) Construir tabla-resumen de evolución (todas las versiones)
function safe($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$historyRows = '';
foreach ($rows as $r) {
  $historyRows .= '<tr>'
    . '<td>' . safe($r['VersionBlo'] ?? '') . '</td>'
    . '<td>' . safe($r['ReleaseApi'] ?? '') . '</td>'
    . '<td>(this page)</td>'
    . '</tr>';
}
?>
<section class="section padding-top-70 colored" id="historial-versiones">
  <div class="container">
    <div class="row">

      <!-- Columna izquierda: tabla de evolución -->
      <div class="col-lg-7 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
        <div class="row">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th><strong>Version</strong></th>
                  <th><strong>Release date</strong></th>
                  <th><strong>Documentation</strong></th>
                </tr>
              </thead>
              <tbody>
                <?= $historyRows ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-1 col-md-12 col-sm-12 align-self-center"></div>

      <!-- Columna derecha: panel con descripción de la ÚLTIMA versión -->
      <div class="col-lg-4 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
        <div class="features-small-item">
          <div class="Consulta">
            <h2 class="titulos"><strong>HISTORIAL DE VERSIONES</strong></h2>
            <p><small><strong>Última versión:</strong> <?= $ver ?> — <strong>Fecha:</strong> <?= $release ?></small></p>
            <hr>
            <!-- Descripción de la última versión (puede traer HTML) -->
            <div>
              <?= $descLast ?: '<em>Sin descripción.</em>' ?>
            </div>
            <hr>
          </div>
        </div>
      </div>


    </div>
  </div>
</section>
