<?php
// Bloque: cargar última versión de ContApiMarket por Nombre — 2025-11-04 — Revisado por JCCM

// Deriva la “familia” desde el nombre del archivo: ej. doc_payments.php -> payments
$filename  = basename($_SERVER['PHP_SELF']);                     // "doc_payments.php"
$extension = pathinfo($filename, PATHINFO_EXTENSION);            // "php"
$basename  = basename($filename, "." . $extension);              // "doc_payments"
$word      = substr($basename, (int)strpos($basename, "_") + 1); // "payments"

// Consulta segura: trae SOLO la última versión disponible
$Reg = [
  'Descripcion' => '',
  'Nombre'      => '',
  'Version'     => '',
  'Live'        => '',
  'Sandbox'     => ''
];

if (isset($mysqli) && $mysqli instanceof mysqli) {
    $sql = "
      SELECT Id, Nombre, Descripcion, Version, Live, Sandbox
      FROM ContApiMarket
      WHERE Nombre = ?
      ORDER BY CAST(Version AS DECIMAL(10,3)) DESC, Id DESC
      LIMIT 1
    ";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param('s', $word);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $Reg = $row;
        }
        $stmt->close();
    }
}

// Variables para la vista
$desc     = (string)($Reg['Descripcion'] ?? '');
$nombre   = (string)($Reg['Nombre']      ?? '');
$version  = (string)($Reg['Version']     ?? '');
$live     = (string)($Reg['Live']        ?? '');
$sandbox  = (string)($Reg['Sandbox']     ?? '');
?>
<br><br><br>
<!-- ***** Descripción General de la API ***** -->
<section class="section padding-top-70" id="">
  <div class="container">
    <div class="row">
      <!-- Columna 1: 50% -->
      <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 align-self-center"
           data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
        <div class="features-small-item">
          <div class="Consulta">
            <h2 class="titulos"><strong>DESCRIPCION</strong></h2>
            <br>
            <?= $desc ?>
          </div>
        </div>
      </div>

      <!-- Columna 2: 50% -->
      <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 align-self-center"
           data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
        <div class="row">
          <div class="table-responsive">
            <table class="table">
              <tr>
                <td><strong>Nombre Api:</strong></td>
                <td><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
              <tr>
                <td><strong>Versión:</strong></td>
                <td><?= htmlspecialchars($version, ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
              <tr>
                <td><strong>Protocolo:</strong></td>
                <td>HTTP</td>
              </tr>
              <tr>
                <td><strong>URI Live:</strong></td>
                <td><?= htmlspecialchars($live, ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
              <tr>
                <td><strong>URI Sandbox:</strong></td>
                <td><?= htmlspecialchars($sandbox, ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>