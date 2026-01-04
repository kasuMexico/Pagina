<?php
/**
 * Modal: iniciar proceso de lead sales para prospectos
 * Qué hace: arma el contexto del modal según la pantalla, muestra pipeline y avance, y envía POST a la ruta indicada.
 * Compatibilidad: PHP 8.2
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 */

declare(strict_types=1);

// Helper de escape
if (!function_exists('h')) {
    function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// Contexto base seguro
$Reg     = is_array($Reg ?? null) ? $Reg : [];
$nombre  = (string)($nombre ?? '');
$pros    = $pros    ?? null; // conexión prospectos, usada por helpers
$basicas = $basicas ?? null; // helper de consultas

// Ruta actual
$selfPath = (string)($_SERVER['PHP_SELF'] ?? '');
$isDistrib = $selfPath;

// Contexto por defecto
$ctx = [
    'encabezado'  => 'Iniciar proceso',
    'TipServicio' => 'Prospecto',
    'IdProspecto' => (int)($Reg['Id'] ?? 0),
    'IdVenta'     => '',
    'IdContact'   => '',
    'IdUsuario'   => '',
    'Producto'    => (string)($Reg['Producto'] ?? ''),
    'nombre'      => $nombre,
    'nombreClie'  => (string)($Reg['FullName'] ?? ''),
    'ServCont'    => (string)($Reg['Servicio_Interes'] ?? ''),
    'direccion'   => (string)($Reg['Direccion'] ?? ''),
    'telefono'    => (string)($Reg['NoTel'] ?? ''),
    'email'       => (string)($Reg['Email'] ?? ''),
    'btn'         => 'Iniciar proceso',
    'vlBtn'       => 'ActDatosCTE',
    'dirhttps'    => 'php/Funcionalidad_Pwa.php',
];

// Si estamos en Mesa_Prospectos, personaliza encabezado
if ($isDistrib === '/login/Mesa_Prospectos.php') {
    $ctx['encabezado'] = 'Iniciar proceso de lead sales';
    $ctx['vlBtn'] = 'LeadSales';
    $ctx['dirhttps'] = 'php/Funcionalidad_Pwa.php';
}

// Hidden comunes
$hidden = [
    'nombre'      => $ctx['nombre'],
    'Host'        => $selfPath,
    'IdVenta'     => $ctx['IdVenta'],
    'IdContact'   => $ctx['IdContact'],
    'IdUsuario'   => $ctx['IdUsuario'],
    'IdProspecto' => (string)$ctx['IdProspecto'],
    'Producto'    => $ctx['Producto'],
];

// Pipeline y avance
$papelineKey = (string)($Reg['Papeline'] ?? '');
$posPapeline = (int)($Reg['PosPapeline'] ?? 0);
$PapelineTxt = '';
$MaxPape     = 0;

if ($papelineKey !== '' && $basicas && $pros) {
    // Nombre del nivel actual
    $PapelineTxt = (string)$basicas->Buscar2Campos(
        $pros, 'Nombre', 'Papeline', 'Pipeline', $papelineKey, 'Nivel', $posPapeline
    );
    // Máximo de niveles
    $MaxPape = (int)$basicas->BuscarCampos($pros, 'Maximo', 'Papeline', 'Pipeline', $papelineKey);
}
?>
<form method="POST" action="<?= h($ctx['dirhttps']) ?>" accept-charset="utf-8">
  <div class="modal-header">
    <h5 class="modal-title" id="modalV4"><?= h($ctx['encabezado']) ?></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>

  <div class="modal-body">
    <div id="Gps"></div>
    <div data-fingerprint-slot></div>

    <?php foreach ($hidden as $k => $v): ?>
      <input type="hidden" name="<?= h($k) ?>" value="<?= h($v) ?>">
    <?php endforeach; ?>

    <p>Nombre del <?= h($ctx['TipServicio']) ?>:</p>
    <h4 class="text-center"><strong><?= h($ctx['nombreClie']) ?></strong></h4>

    <p>Tipo de producto:</p>
    <h4 class="text-center"><strong><?= h($ctx['ServCont']) ?></strong></h4>

    <?php if ($papelineKey !== ''): ?>
      <p>Estatus en el proceso de venta</p>
      <h2><strong><?= h($papelineKey . ' - ' . $PapelineTxt) ?></strong></h2>

      <p>Avance de la venta</p>
      <h2><strong><?= (int)$posPapeline ?> de <?= (int)$MaxPape ?></strong></h2>
    <?php endif; ?>

    <br>
    <?php if ($isDistrib === '/login/Mesa_Prospectos.php'): ?>
      <p>¿Qué frecuencia de email deseas?</p>
      <select class="form-control" name="Frecuencia" required>
        <option value="0">SELECCIONA UNA FRECUENCIA</option>
        <option value="DIARIO">DIARIO</option>
        <option value="SEMANAL">SEMANAL</option>
        <option value="QUINCENAL">QUINCENAL</option>
        <option value="MENSUAL">MENSUAL</option>
      </select>
    <?php endif; ?>
  </div>

  <div class="modal-footer">
    <input type="submit" name="<?= h($ctx['vlBtn']) ?>" class="btn btn-success" value="<?= h($ctx['btn']) ?>">
  </div>
</form>
