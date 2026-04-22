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
$defaults = [
  'customer' => [
    'title' => 'API_CUSTOMER',
    'desc' => 'Consulta datos de clientes, catálogo de productos, producto viable y ventas autorizadas por CURP.',
    'version' => 'V1',
    'live' => 'https://apimarket.kasu.com.mx/api/Customer_V1',
    'sandbox' => 'https://apimarket.kasu.com.mx/api/Customer_V1',
    'model' => 'PREPAGO',
  ],
  'payments' => [
    'title' => 'API_PAYMENTS',
    'desc' => 'Consulta estado de cuenta y registra pagos con la misma lógica de saldo, mora y estatus de la web KASU.',
    'version' => 'V1',
    'live' => 'https://apimarket.kasu.com.mx/api/Payments_V1',
    'sandbox' => 'https://apimarket.kasu.com.mx/api/Payments_V1',
    'model' => 'POSPAGO',
  ],
  'accounts' => [
    'title' => 'API_ACCOUNTS',
    'desc' => 'Registra servicios KASU desde plataformas externas y devuelve póliza, estatus, monto inicial y liga de pago.',
    'version' => 'V1',
    'live' => 'https://apimarket.kasu.com.mx/api/Accounts_V1',
    'sandbox' => 'https://apimarket.kasu.com.mx/api/Accounts_V1',
    'model' => 'ALTA DE SERVICIO',
  ],
  'validatemexico' => [
    'title' => 'Validate_Mexico',
    'desc' => 'Valida CURP y RFC con caché y modelo prepago, incluyendo consultas upstream controladas.',
    'version' => 'V1',
    'live' => 'https://apimarket.kasu.com.mx/api/ValidateMexico_V1',
    'sandbox' => 'https://apimarket.kasu.com.mx/api/ValidateMexico_V1',
    'model' => 'CURP/RFC',
  ],
];

$fallback = $defaults[$word] ?? [
  'title' => strtoupper($word),
  'desc' => 'Documentación pública de API Market KASU V1.',
  'version' => 'V1',
  'live' => '',
  'sandbox' => '',
  'model' => 'API',
];

$desc     = trim((string)($Reg['Descripcion'] ?? '')) ?: $fallback['desc'];
$nombre   = trim((string)($Reg['Nombre']      ?? '')) ?: $fallback['title'];
$version  = trim((string)($Reg['Version']     ?? '')) ?: $fallback['version'];
$live     = trim((string)($Reg['Live']        ?? '')) ?: $fallback['live'];
$sandbox  = trim((string)($Reg['Sandbox']     ?? '')) ?: $fallback['sandbox'];
$model    = $fallback['model'];
?>
<!-- ***** Descripción General de la API ***** -->
<section class="doc-hero">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-7 col-md-12">
        <span class="api-kicker">API Market KASU V1</span>
        <h1 class="doc-hero__title"><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></h1>
        <div class="doc-hero__lead"><?= $desc ?></div>
      </div>
      <div class="col-lg-5 col-md-12">
        <div class="doc-hero__meta">
          <div class="doc-hero__meta-row">
            <span>Modelo</span>
            <strong><?= htmlspecialchars($model, ENT_QUOTES, 'UTF-8') ?></strong>
          </div>
          <div class="doc-hero__meta-row">
            <span>Versión</span>
            <strong><?= htmlspecialchars($version, ENT_QUOTES, 'UTF-8') ?></strong>
          </div>
          <div class="doc-hero__meta-row">
            <span>Protocolo</span>
            <strong>HTTP POST JSON</strong>
          </div>
          <div class="doc-hero__meta-row">
            <span>URI Live</span>
            <code><?= htmlspecialchars($live, ENT_QUOTES, 'UTF-8') ?></code>
          </div>
          <div class="doc-hero__meta-row">
            <span>URI Sandbox</span>
            <code><?= htmlspecialchars($sandbox, ENT_QUOTES, 'UTF-8') ?></code>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
