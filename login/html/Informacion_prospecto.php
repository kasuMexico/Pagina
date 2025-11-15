<?php
/********************************************************************************************
 * Qué hace: Modal de información de prospecto con acciones según interés (Distribuidor/Funerario/otro).
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

// Normaliza y blinda variables usadas en la vista
$Reg       = is_array($Reg ?? null) ? $Reg : [];
$nomVd     = (string)($nomVd ?? '');
$selfHref  = htmlspecialchars((string)($_SERVER['PHP_SELF'] ?? ''), ENT_QUOTES, 'UTF-8');
$idVendPost= isset($_POST['IdVendedor']) ? htmlspecialchars((string)$_POST['IdVendedor'], ENT_QUOTES, 'UTF-8') : '';

// Campos básicos del prospecto
$fullName  = (string)($Reg['FullName'] ?? 'Prospecto');
$origen    = (string)($Reg['Origen'] ?? '');
$altaRaw   = (string)($Reg['Alta'] ?? '');
$altaFmt   = $altaRaw !== '' && strtotime($altaRaw) ? date('d-M-Y', strtotime($altaRaw)) : '-';
$servInt   = (string)($Reg['Servicio_Interes'] ?? '');
$servNorm  = strtoupper(trim($servInt));

// Pipeline y avance
$pipelineKey = (string)($Reg['Papeline'] ?? '');
$posPipe     = (string)($Reg['PosPapeline'] ?? '');
$PapelineTxt = $pipelineKey !== '' && $posPipe !== ''
  ? (string)($basicas->Buscar2Campos($pros, 'Nombre', 'Papeline', 'Pipeline', $pipelineKey, 'Nivel', $posPipe) ?? '')
  : '';
$MaxPape     = (int)($pipelineKey !== '' ? ($basicas->BuscarCampos($pros, 'Maximo', 'Papeline', 'Pipeline', $pipelineKey) ?? 0) : 0);

// WhatsApp link seguro (E.164 sin '+')
$telRaw  = preg_replace('/\D+/', '', (string)($Reg['NoTel'] ?? ''));
$telE164 = $telRaw;
if ($telRaw !== '') {
  if (strpos($telRaw, '52') === 0) {
    $telE164 = $telRaw;
  } elseif (strlen($telRaw) === 10) {
    $telE164 = '52' . $telRaw;
  }
}
$waText = 'Hola, mi nombre es ' . $nomVd . ' y te contacto porque te interesaron nuestros productos de KASU';
$waUrl  = 'https://api.whatsapp.com/send?' . http_build_query(['phone' => $telE164, 'text' => $waText], '', '&', PHP_QUERY_RFC3986);
?>
<div class="modal-header">
  <h5 class="modal-title"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
</div>

<?php if (!empty($Reg)) : ?>
  <div class="modal-body">
    <p>Captado en</p>
    <h2><strong><?= htmlspecialchars($origen, ENT_QUOTES, 'UTF-8') ?></strong></h2>

    <p>Fecha Alta</p>
    <h2><strong><?= htmlspecialchars($altaFmt, ENT_QUOTES, 'UTF-8') ?></strong></h2>

    <p>Producto</p>
    <h2><strong><?= htmlspecialchars($servInt, ENT_QUOTES, 'UTF-8') ?></strong></h2>

    <p>Estatus en el proceso de venta</p>
    <h2><strong><?= htmlspecialchars(trim($pipelineKey . ($PapelineTxt !== '' ? ' - ' . $PapelineTxt : '')), ENT_QUOTES, 'UTF-8') ?></strong></h2>

    <p>Avance de la venta</p>
    <h2><strong><?= (int)($Reg['PosPapeline'] ?? 0) . ' de ' . (int)$MaxPape; ?></strong></h2>
  </div>

  <div class="modal-footer">
    <div class="container-fluid p-0">
      <div class="row">
        <!-- WhatsApp -->
        <div class="col-12 col-sm-6 mb-2">
          <a target="_blank" rel="noopener" class="btn btn-primary btn-block" href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>">
            Whatsapp
          </a>
        </div>

        <?php if ($servNorm === 'DISTRIBUIDOR'): ?>
          <div class="col-12 col-sm-6 mb-2">
            <form method="POST" action="<?= $selfHref ?>" class="m-0">
              <input type="hidden" name="IdVendedor" value="<?= $idVendPost ?>">
              <input type="hidden" name="IdPros"     value="<?= (int)($Reg['Id'] ?? 0) ?>">
              <button type="submit" name="ConvDist" value="1" class="btn btn-success btn-block">Autorizar</button>
            </form>
          </div>
          <div class="col-12 col-sm-6 mb-2">
            <form method="POST" action="<?= $selfHref ?>" class="m-0">
              <input type="hidden" name="IdVendedor" value="<?= $idVendPost ?>">
              <input type="hidden" name="IdPros"     value="<?= (int)($Reg['Id'] ?? 0) ?>">
              <button type="submit" name="Cancelar" value="1" class="btn btn-danger btn-block">Cancelar</button>
            </form>
          </div>

        <?php elseif ($servNorm === 'FUNERARIO'): ?>
          <div class="col-12 col-sm-6 mb-2">
            <form method="POST" action="<?= $selfHref ?>" class="m-0">
              <input type="hidden" name="IdVendedor" value="<?= $idVendPost ?>">
              <input type="hidden" name="IdPros"     value="<?= (int)($Reg['Id'] ?? 0) ?>">
              <button type="submit" name="Generar" value="1" class="btn btn-success btn-block">Generar Venta</button>
            </form>
          </div>
          <div class="col-12 col-sm-6 mb-2">
            <form method="POST" action="<?= $selfHref ?>" class="m-0">
              <input type="hidden" name="IdVendedor" value="<?= $idVendPost ?>">
              <input type="hidden" name="IdPros"     value="<?= (int)($Reg['Id'] ?? 0) ?>">
              <button type="submit" name="ArmaPres" value="1" class="btn btn-primary btn-block">Generar Presupuesto</button>
            </form>
          </div>
          <div class="col-12 col-sm-6 mb-2">
            <form method="POST" action="<?= $selfHref ?>" class="m-0">
              <input type="hidden" name="IdVendedor" value="<?= $idVendPost ?>">
              <input type="hidden" name="IdPros"     value="<?= (int)($Reg['Id'] ?? 0) ?>">
              <button type="submit" name="Cancelar" value="1" class="btn btn-danger btn-block">Cancelar</button>
            </form>
          </div>

        <?php else: ?>
          <div class="col-12 col-sm-6 mb-2">
            <form method="POST" action="<?= $selfHref ?>" class="m-0">
              <input type="hidden" name="IdVendedor" value="<?= $idVendPost ?>">
              <input type="hidden" name="IdPros"     value="<?= (int)($Reg['Id'] ?? 0) ?>">
              <button type="submit" name="ArmaPres" value="1" class="btn btn-primary btn-block">Generar Presupuesto</button>
            </form>
          </div>
          <div class="col-12 col-sm-6 mb-2">
            <form method="POST" action="<?= $selfHref ?>" class="m-0">
              <input type="hidden" name="IdVendedor" value="<?= $idVendPost ?>">
              <input type="hidden" name="IdPros"     value="<?= (int)($Reg['Id'] ?? 0) ?>">
              <button type="submit" name="Cancelar" value="1" class="btn btn-danger btn-block">Cancelar</button>
            </form>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
<?php endif; ?>
