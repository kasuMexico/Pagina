<?php
/********************************************************************************************
 * Qué hace: Define rutas relativas a la documentación según el script actual y pinta 4 tarjetas.
 * Fecha: 04/11/2025
 * Revisado por: JCCM
 * Archivo: select_api.php
 ********************************************************************************************/

// Detecta si estás dentro de /documentacion para ajustar rutas
if (!isset($docPrefix) || !isset($assetPrefix)) {
    $script = isset($_SERVER['SCRIPT_NAME']) ? (string)$_SERVER['SCRIPT_NAME'] : '';
    $scriptDir = str_replace('\\', '/', dirname($script));
    $isDocPage = (strpos($scriptDir, '/documentacion') !== false);
    $assetPrefix = $isDocPage ? '../assets/' : 'assets/';
    $docPrefix = $isDocPage ? '../documentacion/' : 'documentacion/';
}

$docs = [
    [
        'title' => 'API_CUSTOMER',
        'badge' => 'PREPAGO',
        'icon'  => '&#128100;',
        'desc'  => 'Comparte y valida datos de clientes con consultas rápidas y seguras.',
        'href'  => $docPrefix . 'doc_customer.php',
    ],
    [
        'title' => 'API_PAYMENTS',
        'badge' => 'POSPAGO',
        'icon'  => '&#128179;',
        'desc'  => 'Cobros en tiempo real con conciliación y comisiones integradas.',
        'href'  => $docPrefix . 'doc_payments.php',
    ],
    [
        'title' => 'API_ACCOUNTS',
        'badge' => 'GRATIS',
        'icon'  => '&#128188;',
        'desc'  => 'Apertura de servicios KASU desde tu plataforma y comisiones por venta.',
        'href'  => $docPrefix . 'doc_accounts.php',
    ],
    [
        'title' => 'Validate_Mexico',
        'badge' => 'CURP/RFC',
        'icon'  => '&#128270;',
        'desc'  => 'Validación de identidad con caché y modelo prepago para CURP y RFC.',
        'href'  => $docPrefix . 'doc_validatemexico.php',
    ],
];
?>
<div class="container">
  <div class="row">
    <?php foreach ($docs as $doc) { ?>
      <div class="col-lg-3 col-md-6 col-sm-6 col-12" data-scroll-reveal="enter bottom move 50px over 0.6s after 0.2s">
        <div class="features-small-item api-card">
          <div class="section-title">
            <span class="badge badge-pill api-card__badge"><?php echo htmlspecialchars($doc['badge'], ENT_QUOTES, 'UTF-8'); ?></span>
            <h2 class="api-card__title">
              <span class="api-card__icon" aria-hidden="true"><?php echo $doc['icon']; ?></span>
              <strong><?php echo htmlspecialchars($doc['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </h2>
          </div>
          <br>
          <p class="api-card__desc"><?php echo htmlspecialchars($doc['desc'], ENT_QUOTES, 'UTF-8'); ?></p>
          <div class="consulta">
            <br>
            <a class="btn btn-info api-card__cta" href="<?php echo htmlspecialchars($doc['href'], ENT_QUOTES, 'UTF-8'); ?>">Ver documentación</a>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>
</div>
