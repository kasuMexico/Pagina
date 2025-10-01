<?php
$hasEmail = !empty($Reg['Email']) || !empty($_POST['Mail'] ?? '');
$servRaw  = $Reg['Servicio_Interes'] ?? '';
$serv     = strtoupper(trim($servRaw));

$Edad     = $basicas->ObtenerEdad($Reg['Curp'] ?? '');
$ProdSel  = $basicas->ProdFune($Edad);
$Costo    = $basicas->BuscarCampos($mysqli, "Costo", "Productos", "Producto", $ProdSel);

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES,'UTF-8'); }

$hidden = [
  'nombre'    => $nombre ?? '',
  'Host'      => $_SERVER['PHP_SELF'] ?? '',
  'IdVenta'   => $Reg['Id'] ?? '',
  'IdContact' => $Recg['id'] ?? '',
  'IdUsuario' => $Recg1['id'] ?? '',
  'Producto'  => $Reg['Producto'] ?? '',
  'Id'        => (int)($Reg['Id'] ?? 0),
  'IdVendedor'=> $_POST['IdVendedor'] ?? '',
  'name'      => $name ?? '',
];
if (!empty($_SESSION['csrf'])) $hidden['csrf'] = $_SESSION['csrf'];

$groupLabel = [
  'FUNERARIO'  => 'Familiar',
  'SEGURIDAD'  => 'Grupo de Oficiales',
  'TRANSPORTE' => 'Grupo de Transportistas',
][$serv] ?? null;

$groupValue = [
  'FUNERARIO'  => 'FAMILIAR',
  'SEGURIDAD'  => 'SEGURIDAD',
  'TRANSPORTE' => 'SEGURIDAD',
][$serv] ?? null;

/* Pago/plazos por servicio */
$pagoOptions  = ['CREDITO'=>'CRÉDITO', 'CONTADO'=>'CONTADO'];
$plazoOptions = ['3'=>'3 Meses','6'=>'6 Meses','9'=>'9 Meses'];

if ($serv === 'SEGURIDAD' || $serv === 'RETIRO') {
  $pagoOptions  = ['CONTADO'=>'CONTADO'];
  $plazoOptions = [];
} elseif ($serv === 'TRANSPORTE') {
  $pagoOptions  = ['CREDITO'=>'CRÉDITO', 'CONTADO'=>'CONTADO'];
  $plazoOptions = ['3'=>'3 Meses','6'=>'6 Meses','9'=>'9 Meses','12'=>'12 Meses'];
}
?>
<form method="POST" action="php/Registro_Prospectos.php" autocomplete="off">
  <div id="Gps" style="display:none"></div>
  <div data-fingerprint-slot></div>

  <?php foreach ($hidden as $k=>$v): ?>
    <input type="hidden" name="<?=h($k)?>" value="<?=h($v)?>">
  <?php endforeach; ?>

  <div class="modal-body" data-serv="<?=h($serv)?>">
    <p class="mb-2">¿Qué producto desea cotizar tu cliente?</p>

    <?php if (in_array($serv, ['FUNERARIO','SEGURIDAD','TRANSPORTE'])): ?>
      <div class="form-group">
        <label class="mr-3"><input type="radio" name="tipo_plan" value="INDIVIDUAL" checked> Individual</label>
        <label><input type="radio" name="tipo_plan" value="<?=h($groupValue)?>"> <?=h($groupLabel)?></label>
      </div>
    <?php endif; ?>

    <?php if (in_array($serv, ['FUNERARIO','SEGURIDAD','TRANSPORTE'])): ?>
      <div id="plan-individual">
        <label class="mb-1">Edad</label>
        <h4 class="text-center"><strong><?= (int)$Edad ?> Años</strong></h4>
        <input type="hidden" name="Edad" value="<?= (int)$Edad ?>">
        <label class="mb-1">Precio de Contado</label>
        <h4 class="text-center"><strong>$ <?= number_format((float)$Costo, 2) ?></strong></h4>
      </div>

      <div id="plan-familiar" style="display:none">
        <label class="mt-3 d-block">Cantidad de pólizas por bloque</label>
        <?php foreach (['a0a29'=>'0–29','a30a49'=>'30–49','a50a54'=>'50–54','a55a59'=>'55–59','a60a64'=>'60–64','a65a69'=>'65–69'] as $name=>$txt): ?>
          <label><?=h($txt)?></label>
          <input class="form-control" type="number" name="<?=h($name)?>" min="0" placeholder="Cantidad">
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <label class="mt-3">Selecciona el Tipo de Pago</label>
    <select class="form-control" name="Pago" id="pago"
            data-has-plazos="<?= empty($plazoOptions) ? '0' : '1' ?>"
            required <?= count($pagoOptions)===1 ? 'disabled' : ''?>>
      <?php foreach ($pagoOptions as $val=>$txt): ?>
        <option value="<?=h($val)?>"><?=h($txt)?></option>
      <?php endforeach; ?>
    </select>

    <div id="plazo-row" class="mt-2" style="<?= empty($plazoOptions) ? 'display:none' : '' ?>">
      <label>Selecciona el Plazo</label>
      <select class="form-control" name="plazo" id="plazo" <?= empty($plazoOptions) ? '' : 'required' ?>>
        <?php if (empty($plazoOptions)): ?>
          <option value="1" selected>Contado</option>
        <?php else: ?>
          <?php foreach ($plazoOptions as $val=>$txt): ?>
            <option value="<?=h($val)?>"><?=h($txt)?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
    </div>
  </div>

  <div class="modal-footer">
    <button type="submit" name="DescargaPres" class="btn btn-secondary" formtarget="_blank">Descargar PDF</button>
    <?php if ($hasEmail): ?>
      <button type="submit" name="EnviaPres" class="btn btn-primary">Enviar</button>
    <?php endif; ?>
  </div>
</form>

<script>
(function () {
  function togglePlanes() {
    var fam = document.getElementById('plan-familiar');
    var ind = document.getElementById('plan-individual');
    var sel = document.querySelector('input[name="tipo_plan"]:checked');
    var val = sel ? sel.value : 'INDIVIDUAL';
    if (fam) fam.style.display = (val === 'INDIVIDUAL') ? 'none' : 'block';
    if (ind) ind.style.display = (val === 'INDIVIDUAL') ? 'block' : 'none';
  }

  function syncPagoYPlazo(){
    var body  = document.querySelector('.modal-body');
    var serv  = body ? (body.getAttribute('data-serv') || '') : '';
    var pago  = document.getElementById('pago');
    var plazo = document.getElementById('plazo');
    var row   = document.getElementById('plazo-row');
    var hasPlazos = (pago && pago.getAttribute('data-has-plazos') === '1');

    if (serv === 'SEGURIDAD' || serv === 'RETIRO') {
      if (pago) { pago.value = 'CONTADO'; pago.setAttribute('disabled','disabled'); }
      if (row) row.style.display = 'none';
      if (plazo) plazo.value = '1';
      return;
    }

    if (!pago || !plazo || !row) return;
    var contado = pago.value === 'CONTADO';
    if (!hasPlazos) {
      row.style.display = 'none';
      plazo.value = '1';
      return;
    }
    row.style.display = contado ? 'none' : '';
    plazo.required = !contado;
    if (contado) plazo.value = '1';
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function(){ togglePlanes(); syncPagoYPlazo(); });
  } else {
    togglePlanes(); syncPagoYPlazo();
  }

  document.addEventListener('change', function (e) {
    if (e.target && e.target.name === 'tipo_plan') togglePlanes();
    if (e.target && e.target.id === 'pago') syncPagoYPlazo();
  });
})();
</script>
