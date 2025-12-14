<?php
/********************************************************************************************
 * Qué hace: Modal "Presupuesto de Venta" para prospecto. Calcula producto y costos según
 *           servicio y edad. Controla opciones de pago/plazo por tipo de servicio. Incluye
 *           slots de GPS/fingerprint y CSRF. Listo para PHP 8.2.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 * Archivo: Presupuesto.php
 ********************************************************************************************/

declare(strict_types=1);

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

// Conexión correcta para PrespEnviado (SOLO base de prospectos)
$dbPresp = $pros ?? null;

// ===== IMPORTANTE: Buscar o crear propuesta en PrespEnviado =====
$idPropuestaPDF = (int)($_GET['idp'] ?? $_POST['id_propuesta_pdf'] ?? 0);
$idProspecto   = (int)($Reg['Id'] ?? 0);

// Campos ocultos comunes
$hidden = [
  'nombre'     => $nombre ?? '',
  'Host'       => $_SERVER['PHP_SELF'] ?? '',
  'IdVenta'    => $idProspecto,
  'IdProspecto'=> $idProspecto,
  'IdContact'  => $Recg['id']  ?? '',
  'IdUsuario'  => $Recg1['id'] ?? '',
  'Producto'   => $Reg['Producto'] ?? '',
  'Id'         => $idProspecto,
  'IdVendedor' => $_POST['IdVendedor'] ?? '',
  'name'       => $name ?? '',
  'csrf'       => $csrf,
  'id_propuesta_pdf' => $idPropuestaPDF, // Nuevo campo
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

// Opciones de pago/plazo según servicio (combo único usando MaxCredito de Productos)
$maxCredito = (int)($basicas->BuscarCampos($mysqli, 'MaxCredito', 'Productos', 'Producto', $ProdSel) ?? 0);
$pagoPlazoOptions = ['CONTADO_1' => 'CONTADO']; // valor => label
foreach ([3,6,9,12] as $term) {
  if ($term <= $maxCredito) {
    $pagoPlazoOptions['CREDITO_' . $term] = $term . ' Meses (Crédito)';
  }
}
if ($serv === 'SEGURIDAD' || $serv === 'RETIRO') {
  $pagoPlazoOptions = ['CONTADO_1' => 'CONTADO']; // sin crédito
}
?>

<div class="modal-header">
  <h5 class="modal-title">Presupuesto de Venta</h5>
  <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
</div>

<!-- Formulario para ENVIAR (acción normal) -->
<form method="POST" action="https://kasu.com.mx/login/php/Registro_Prospectos.php" autocomplete="off" id="formEnviar">
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

    <label class="mt-3">Selecciona el Pago/Plazo</label>
    <?php
      $pagoPlazoDefault = array_key_first($pagoPlazoOptions);
      $defaultParts = explode('_', $pagoPlazoDefault);
      $defaultPago = $defaultParts[0] ?? 'CONTADO';
      $defaultPlazo = $defaultParts[1] ?? '1';
    ?>
    <select class="form-control" name="pago_plazo_ui" id="pago_plazo" required>
      <?php foreach ($pagoPlazoOptions as $val => $txt): ?>
        <option value="<?= h($val) ?>" <?= $val===$pagoPlazoDefault ? 'selected' : '' ?>><?= h($txt) ?></option>
      <?php endforeach; ?>
    </select>
    <!-- Espejos que SIEMPRE se envían -->
    <input type="hidden" name="Pago" id="pago_hidden" value="<?= h($defaultPago) ?>">
    <input type="hidden" name="plazo" id="plazo_hidden" value="<?= h($defaultPlazo) ?>">

  </div><!-- /.modal-body -->

  <div class="modal-footer">
    <?php if (!(bool)($_SERVER['HTTP_X_PWA'] ?? false)): ?>
      <!-- Considerar si dejar visible es util para pwa ya que solo se puede visualizar no descarga -->
      <button type="submit" name="DescargaPres" class="btn btn-secondary">
        <i class="fas fa-download"></i> Generar y descargar PDF
      </button>
    <?php endif; ?>
    
    <?php if ($hasEmail): ?>
      <button type="submit" name="EnviaPres" class="btn btn-primary">
        <i class="fas fa-paper-plane"></i> Enviar por correo
      </button>
    <?php endif; ?>
    <button type="button" class="btn btn-info" id="btnSmsPres">
      <i class="fa fa-mobile"></i> Enviar por SMS
    </button>
  </div>
</form>

<script>
// =============== FUNCIÓN PARA DESCARGAR PDF ===============

function descargarPDF(elemento, idPropuesta) {
  console.log('Iniciando descarga PDF para propuesta ID:', idPropuesta);
  
  // 1. Cerrar el modal primero
  const modal = document.getElementById('modalPresupuesto');
  if (modal) {
    const bootstrapModal = bootstrap.Modal.getInstance(modal);
    if (bootstrapModal) {
      bootstrapModal.hide();
    }
  }
  
  // 2. Pequeño delay para que se cierre el modal
  setTimeout(function() {
    // 3. Crear URL con parámetros
    const url = `https://kasu.com.mx/login/Generar_PDF/Cotizacion_pdf.php?idp=${idPropuesta}&download=1&t=${Date.now()}`;
    
    // 4. Intentar abrir en nueva pestaña
    const nuevaVentana = window.open(url, '_blank', 'noopener,noreferrer');
    
    // 5. Si fue bloqueada (popup blocker), usar método alternativo
    if (!nuevaVentana || nuevaVentana.closed || typeof nuevaVentana.closed == 'undefined') {
      console.log('Popup bloqueado, usando método alternativo');
      
      // Método alternativo: crear un link y simular click
      const link = document.createElement('a');
      link.href = url;
      link.target = '_blank';
      link.rel = 'noopener noreferrer';
      link.style.display = 'none';
      
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  }, 300);
  
  // Prevenir el comportamiento por defecto del link
  return false;
}

// =============== FUNCIONES ORIGINALES DEL FORMULARIO ===============

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

  function syncPagoPlazoCombo(){
    var sel = qs('#pago_plazo');
    var pagoH  = qs('#pago_hidden'); // espejo
    var plazoH = qs('#plazo_hidden'); // espejo
    if (!sel || !pagoH || !plazoH) return;
    var val = sel.value || 'CONTADO_1';
    var parts = val.split('_');
    var pago = parts[0] || 'CONTADO';
    var plazo = parts[1] || '1';
    pagoH.value = pago;
    plazoH.value = plazo;
  }

  function bindEvents(){
    qsa('input[name="tipo_plan"]').forEach(function(r){
      r.addEventListener('change', togglePlanes);
    });
    var combo = qs('#pago_plazo');
    if (combo) combo.addEventListener('change', syncPagoPlazoCombo);
    
    var smsBtn = document.getElementById('btnSmsPres');
    if (smsBtn) {
      smsBtn.addEventListener('click', function(){
        var idPros = document.querySelector('input[name="IdProspecto"]')?.value || document.querySelector('input[name="IdVenta"]')?.value || '';
        if (!idPros) { alert('Falta Id de prospecto'); return; }
        var plazoVal = document.getElementById('plazo_hidden') ? document.getElementById('plazo_hidden').value : '';
        // No pedimos teléfono: el endpoint lo tomará de prospectos.NoTel
        fetch('/eia/php/sms/enviar_presupuesto_sms.php', {
          method: 'POST',
          headers: {'Content-Type':'application/x-www-form-urlencoded'},
          body: new URLSearchParams({
            presupuesto_id: '',
            telefono: '',
            IdProspecto: idPros,
            plazo: plazoVal
          }).toString()
        }).then(r=>r.json()).then(function(res){
          if (res.ok) {
            alert('SMS enviado');
          } else {
            alert('No se envió SMS: ' + (res.error || res.message || 'Error'));
          }
        }).catch(function(err){ alert('Error SMS: '+err); });
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function(){
      togglePlanes(); syncPagoPlazoCombo(); bindEvents();
    });
  } else {
    togglePlanes(); syncPagoPlazoCombo(); bindEvents();
  }
})();

// =============== DEBUG: Verificar que todo funcione ===============

console.log('Modal de presupuesto cargado');
console.log('ID Propuesta para PDF:', <?= $idPropuestaPDF ?>);
console.log('ID Prospecto:', <?= $idProspecto ?>);

// Verificar que el botón existe
setTimeout(function() {
  const btn = document.getElementById('btnDescargarPDF');
  if (btn) {
    console.log('✅ Botón PDF encontrado:', btn);
    console.log('✅ HREF del botón:', btn.href);
    console.log('✅ Target del botón:', btn.target);
    
    // Verificar que el ID en el href sea correcto
    const match = btn.href.match(/idp=(\d+)/);
    if (match) {
      console.log('✅ ID en URL:', match[1]);
    }
  } else {
    console.log('❌ Botón PDF NO encontrado');
  }
}, 500);
</script>

<!-- Estilo para asegurar que los enlaces sean clickeables -->
<style>
#btnDescargarPDF {
  cursor: pointer;
  text-decoration: none !important;
  display: inline-block;
}

/* Asegurar visibilidad y clickeabilidad en móviles */
@media (max-width: 768px) {
  #btnDescargarPDF {
    min-height: 44px;
    min-width: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
}
</style>
