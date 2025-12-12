<?php
/********************************************************************************************
 * Qué hace: Modal "Presupuesto de Venta" para prospecto. Calcula producto y costos según
 *           servicio y edad. Controla opciones de pago/plazo por tipo de servicio. Incluye
 *           slots de GPS/fingerprint y CSRF. Listo para PHP 8.2.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
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

// ===== IMPORTANTE: Buscar o crear propuesta en PrespEnviado =====
$idPropuestaPDF = 0;
$idProspecto = (int)($Reg['Id'] ?? 0);

if ($idProspecto > 0) {
    // Buscar si ya existe una propuesta para este prospecto
    $stmt = $mysqli->prepare("SELECT Id FROM PrespEnviado WHERE IdProspecto = ? ORDER BY Id DESC LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $idProspecto);
        $stmt->execute();
        $stmt->bind_result($idPropuestaExistente);
        if ($stmt->fetch()) {
            $idPropuestaPDF = $idPropuestaExistente;
        }
        $stmt->close();
        
        // Si no existe, crear una nueva
        if ($idPropuestaPDF <= 0) {
            $nombreCompleto = $Reg['FullName'] ?? 'Prospecto';
            $email = $Reg['Email'] ?? '';
            $telefono = $Reg['Telefono'] ?? '';
            $servicio = $Reg['Servicio_Interes'] ?? 'GENERAL';
            
            // Crear nueva propuesta
            $stmtInsert = $mysqli->prepare("
                INSERT INTO PrespEnviado 
                (IdProspecto, Nombre, Email, Telefono, Servicio, Producto, Costo, FechaCreacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            if ($stmtInsert) {
                $stmtInsert->bind_param(
                    "isssssd", 
                    $idProspecto, 
                    $nombreCompleto,
                    $email,
                    $telefono,
                    $servicio,
                    $ProdSel,
                    $Costo
                );
                
                if ($stmtInsert->execute()) {
                    $idPropuestaPDF = $stmtInsert->insert_id;
                }
                $stmtInsert->close();
            }
        }
    }
}

// Campos ocultos comunes
$hidden = [
  'nombre'     => $nombre ?? '',
  'Host'       => $_SERVER['PHP_SELF'] ?? '',
  'IdVenta'    => $Reg['Id']   ?? '',
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
    <!-- SOLUCIÓN CORREGIDA: Usar idPropuestaPDF en lugar de idProspecto -->
    <?php if ($idPropuestaPDF > 0): ?>
      <a href="https://kasu.com.mx/login/Generar_PDF/Cotizacion_pdf.php?idp=<?= $idPropuestaPDF ?>&download=1&t=<?= time() ?>" 
         target="_blank" 
         class="btn btn-secondary"
         id="btnDescargarPDF"
         onclick="descargarPDF(this, <?= $idPropuestaPDF ?>); return false;">
        <i class="fas fa-download"></i> Descargar PDF
      </a>
    <?php else: ?>
      <button type="button" class="btn btn-secondary" disabled>
        <i class="fas fa-download"></i> Descargar PDF
      </button>
    <?php endif; ?>
    
    <?php if ($hasEmail): ?>
      <button type="submit" name="EnviaPres" class="btn btn-primary">
        <i class="fas fa-paper-plane"></i> Enviar
      </button>
    <?php endif; ?>
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
    
    // Configurar el botón PDF
    const btnPDF = document.getElementById('btnDescargarPDF');
    if (btnPDF) {
      btnPDF.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Obtener el ID de propuesta del data attribute o del href
        const href = this.getAttribute('href');
        const idMatch = href.match(/idp=(\d+)/);
        if (idMatch && idMatch[1]) {
          descargarPDF(this, parseInt(idMatch[1]));
        }
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function(){
      togglePlanes(); syncPagoYPlazo(); bindEvents();
    });
  } else {
    togglePlanes(); syncPagoYPlazo(); bindEvents();
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