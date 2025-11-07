<?php
/********************************************************************************************
 * Qué hace: Modal "Presupuesto de Venta" para prospecto. Calcula producto y costos según
 *           servicio y edad. Controla opciones de pago/plazo por tipo de servicio. Incluye
 *           slots de GPS/fingerprint y CSRF. Listo para PHP 8.2.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

// Precondiciones mínimas
// - $basicas y $mysqli vienen de librerías incluidas en la página padre.
// - $Reg, $Recg, $Recg1, $name, $nombre, $Metodo, $Niv pueden estar definidos arriba.

// Helper de escape para HTML
if (!function_exists('h')) {
  function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// Normaliza arrays de entrada
$Reg   = is_array($Reg   ?? null) ? $Reg   : [];
$Recg  = is_array($Recg  ?? null) ? $Recg  : [];
$Recg1 = is_array($Recg1 ?? null) ? $Recg1 : [];

// CSRF: usa csrf_auth; si no existe, créalo
if (empty($_SESSION['csrf_auth'])) {
  $_SESSION['csrf_auth'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_auth'];

// Flags y servicio
$hasEmail = !empty($Reg['Email']) || !empty($_POST['Mail'] ?? '');
$servRaw  = (string)($Reg['Servicio_Interes'] ?? '');
$serv     = strtoupper(trim($servRaw));

// Edad segura desde CURP
$Edad = (int)($basicas->ObtenerEdad((string)($Reg['Curp'] ?? '')) ?? 0);

// Selección de producto por servicio
if ($serv === 'TRANSPORTE') {
  $ProdSel = (string)($basicas->ProdTrans($Edad) ?? '');
} elseif ($serv === 'SEGURIDAD') {
  $ProdSel = (string)($basicas->ProdPli($Edad) ?? '');
} else {
  // FUNERARIO u otro default
  $ProdSel = (string)($basicas->ProdFune($Edad) ?? '');
}

// Costo del producto
$Costo = (float)($basicas->BuscarCampos($mysqli, 'Costo', 'Productos', 'Producto', $ProdSel) ?? 0.0);

// Campos ocultos comunes
$hidden = [
  'nombre'     => $nombre ?? '',
  'Host'       => $_SERVER['PHP_SELF'] ?? '',
  'IdVenta'    => $Reg['Id']   ?? '',
  'IdContact'  => $Recg['id']  ?? '',
  'IdUsuario'  => $Recg1['id'] ?? '',
  'Producto'   => $Reg['Producto'] ?? '',
  'Id'         => (int)($Reg['Id'] ?? 0),
  'IdVendedor' => $_POST['IdVendedor'] ?? '',
  'name'       => $name ?? '',
  'csrf'       => $csrf,
];

// Etiquetas para plan grupal según servicio
$groupLabel = [
  'FUNERARIO'  => 'Familiar',
  'SEGURIDAD'  => 'Grupo de Oficiales',
  'TRANSPORTE' => 'Grupo de Transportistas',
][$serv] ?? null;

$groupValue = [
  'FUNERARIO'  => 'FAMILIAR',
  'SEGURIDAD'  => 'SEGURIDAD',
  'TRANSPORTE' => 'TRANSPORTE',
][$serv] ?? null;

// Opciones de pago/plazo según servicio
$pagoOptions  = ['CREDITO' => 'CRÉDITO', 'CONTADO' => 'CONTADO'];
$plazoOptions = ['3' => '3 Meses', '6' => '6 Meses', '9' => '9 Meses'];

if ($serv === 'SEGURIDAD' || $serv === 'RETIRO') {
  $pagoOptions  = ['CONTADO' => 'CONTADO'];
  $plazoOptions = []; // sin plazos
} elseif ($serv === 'TRANSPORTE') {
  $pagoOptions  = ['CREDITO' => 'CRÉDITO', 'CONTADO' => 'CONTADO'];
  $plazoOptions = ['3'=>'3 Meses','6'=>'6 Meses','9'=>'9 Meses','12'=>'12 Meses'];
}
?>
<div class="modal-header">
  <h5 class="modal-title">Presupuesto de Venta</h5>
  <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
</div>

<!-- El form envuelve body + footer -->
<form method="POST" action="https://kasu.com.mx/login/php/Registro_Prospectos.php" autocomplete="off">
  <div class="modal-body" data-serv="<?= h($serv) ?>">
    <!-- Slots para GPS y Fingerprint -->
    <div id="Gps" style="display:none"></div>
    <div data-fingerprint-slot></div>

    <?php foreach ($hidden as $k => $v): ?>
      <input type="hidden" name="<?= h($k) ?>" value="<?= h($v) ?>">
    <?php endforeach; ?>

    <p class="mb-2">¿Qué producto desea cotizar tu cliente?</p>

    <?php if (in_array($serv, ['FUNERARIO','SEGURIDAD','TRANSPORTE'], true)): ?>
      <div class="form-group">
        <label class="mr-3"><input type="radio" name="tipo_plan" value="INDIVIDUAL" checked> Individual</label>
        <?php if ($groupLabel && $groupValue): ?>
          <label><input type="radio" name="tipo_plan" value="<?= h($groupValue) ?>"> <?= h($groupLabel) ?></label>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if (in_array($serv, ['FUNERARIO','SEGURIDAD','TRANSPORTE'], true)): ?>
      <div id="plan-individual">
        <label class="mb-1">Edad</label>
        <h4 class="text-center"><strong><?= (int)$Edad ?> Años</strong></h4>
        <input type="hidden" name="Edad" value="<?= (int)$Edad ?>">
        <label class="mb-1">Precio de Contado</label>
        <h4 class="text-center"><strong>$ <?= number_format($Costo, 2) ?></strong></h4>
      </div>

      <div id="plan-familiar" style="display:none">
        <label class="mt-3 d-block">Cantidad de pólizas por bloque</label>
        <?php
          $bloques = [
            'a02a29' => '02–29', 'a30a49' => '30–49', 'a50a54' => '50–54',
            'a55a59' => '55–59', 'a60a64' => '60–64', 'a65a69' => '65–69'
          ];
          foreach ($bloques as $name => $txt):
        ?>
          <label><?= h($txt) ?></label>
          <input class="form-control" type="number" name="<?= h($name) ?>" min="0" placeholder="Cantidad">
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <label class="mt-3">Selecciona el Tipo de Pago</label>
    <?php
      $pagoTieneUna = count($pagoOptions) === 1;
      $pagoDefault  = array_key_first($pagoOptions);
    ?>
    <select class="form-control" name="Pago" id="pago"
            data-has-plazos="<?= empty($plazoOptions) ? '0' : '1' ?>"
            <?= $pagoTieneUna ? 'disabled' : 'required' ?>>
      <?php foreach ($pagoOptions as $val => $txt): ?>
        <option value="<?= h($val) ?>" <?= $val===$pagoDefault ? 'selected' : '' ?>><?= h($txt) ?></option>
      <?php endforeach; ?>
    </select>
    <!-- Espejo para cuando el select esté disabled (disabled no se envía) -->
    <input type="hidden" name="Pago_shadow" id="pago_hidden" value="<?= h($pagoDefault) ?>">

    <div id="plazo-row" class="mt-2" style="<?= empty($plazoOptions) ? 'display:none' : '' ?>">
      <label>Selecciona el Plazo</label>
      <?php
        $plazoDefault = empty($plazoOptions) ? '1' : array_key_first($plazoOptions);
      ?>
      <select class="form-control" name="plazo_ui" id="plazo" <?= empty($plazoOptions) ? '' : 'required' ?>>
        <?php if (empty($plazoOptions)): ?>
          <option value="1" selected>Contado</option>
        <?php else: ?>
          <?php foreach ($plazoOptions as $val => $txt): ?>
            <option value="<?= h($val) ?>" <?= $val===$plazoDefault ? 'selected' : '' ?>><?= h($txt) ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
    </div>
    <!-- Espejo que SIEMPRE se envía para plazo -->
    <input type="hidden" name="plazo" id="plazo_hidden" value="<?= h($plazoDefault) ?>">

  </div><!-- /.modal-body -->

  <div class="modal-footer">
    <button type="submit" name="DescargaPres" class="btn btn-secondary">Descargar PDF</button>
    <?php if ($hasEmail): ?>
      <button type="submit" name="EnviaPres" class="btn btn-primary">Enviar</button>
    <?php endif; ?>
  </div>
</form><!-- /form -->

<script>
(function () {
  function qs(sel){ return document.querySelector(sel); }
  function qsa(sel){ return Array.prototype.slice.call(document.querySelectorAll(sel)); }

  function togglePlanes() {
    var fam = qs('#plan-familiar');
    var ind = qs('#plan-individual');
    var sel = qs('input[name="tipo_plan"]:checked');
    var val = sel ? sel.value : 'INDIVIDUAL';
    if (fam) fam.style.display = (val === 'INDIVIDUAL') ? 'none' : 'block';
    if (ind) ind.style.display = (val === 'INDIVIDUAL') ? 'block' : 'none';
  }

  function syncPagoYPlazo(){
    var body   = qs('.modal-body');
    var serv   = body ? (body.getAttribute('data-serv') || '') : '';
    var pago   = qs('#pago');
    var pagoH  = qs('#pago_hidden'); // espejo
    var plazo  = qs('#plazo');
    var plazoH = qs('#plazo_hidden'); // espejo
    var row    = qs('#plazo-row');
    var hasPlazos = pago && pago.getAttribute('data-has-plazos') === '1';

    // Servicios sin plazos ni crédito
    if (serv === 'SEGURIDAD' || serv === 'RETIRO') {
      if (pago) {
        pago.value = 'CONTADO';
        pago.setAttribute('disabled','disabled');
      }
      if (pagoH) pagoH.value = 'CONTADO';

      if (row) row.style.display = 'none';
      if (plazo) plazo.value = '1';
      if (plazoH) plazoH.value = '1';
      return;
    }

    // Normal
    if (!pago || !plazo || !row) return;
    var contado = pago.value === 'CONTADO';

    // Mantener espejo de Pago
    if (pagoH) pagoH.value = pago.value;

    if (!hasPlazos) {
      row.style.display = 'none';
      if (plazo) plazo.value = '1';
      if (plazoH) plazoH.value = '1';
      return;
    }
    row.style.display = contado ? 'none' : '';
    if (contado) {
      plazo.value = '1';
    }
    if (plazoH) plazoH.value = plazo.value;
  }

  function bindEvents(){
    qsa('input[name="tipo_plan"]').forEach(function(r){
      r.addEventListener('change', togglePlanes);
    });
    var pago = qs('#pago');
    if (pago) pago.addEventListener('change', syncPagoYPlazo);
    var plazo = qs('#plazo');
    if (plazo) plazo.addEventListener('change', function(){
      var plazoH = qs('#plazo_hidden');
      if (plazoH) plazoH.value = plazo.value;
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function(){
      togglePlanes(); syncPagoYPlazo(); bindEvents();
    });
  } else {
    togglePlanes(); syncPagoYPlazo(); bindEvents();
  }
})();
</script>
