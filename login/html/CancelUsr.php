<?php
/********************************************************************************************
 * Qué hace: Modal de confirmación para cancelar Prospecto o Cliente según la página origen.
 *           Envía contexto mínimo (Ids, Status, Producto) y slots de GPS/Fingerprint.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

// Helper de escape seguro
if (!function_exists('h')) {
  function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// Ruta actual (cruda para comparar) y segura para imprimir
$selfPathRaw  = (string)($_SERVER['PHP_SELF'] ?? '');
$selfPathSafe = h($selfPathRaw);

// CSRF: usa csrf_auth si existe; si no, créalo
if (empty($_SESSION['csrf_auth'])) {
  $_SESSION['csrf_auth'] = bin2hex(random_bytes(32));
}
$csrfSafe = h($_SESSION['csrf_auth']);

// Normaliza arrays de entrada posibles
$Reg   = is_array($Reg   ?? null) ? $Reg   : [];
$Recg  = is_array($Recg  ?? null) ? $Recg  : [];
$Recg1 = is_array($Recg1 ?? null) ? $Recg1 : [];

// Determina tipo de cancelación y carga de campos
if ($selfPathRaw === '/login/Pwa_Prospectos.php' || $selfPathRaw === '/login/Mesa_Prospectos.php') {
  // Prospecto
  $TipoCancel = 'Prospecto';
  $NombBase   = h($Reg['FullName'] ?? '');
  $Status     = h($Reg['Servicio_Interes'] ?? '');
  $IdVenta    = h($Reg['Id'] ?? '');
  $nombre     = '';
  $IdContact  = '';
  $IdUsuario  = '';
  $Producto   = '';
} else {
  // Cliente
  $TipoCancel = 'Cliente';
  $IdVenta    = (string)((int)($Reg['Id'] ?? 0));
  $IdContact  = (string)((int)($Recg['id'] ?? 0));
  $IdUsuario  = (string)((int)($Recg1['id'] ?? 0));
  $Producto   = h($Reg['Producto'] ?? '');
  $Status     = h($_POST['Status'] ?? '');
  $NombBase   = h($Reg['Nombre'] ?? '');
  $nombre     = h($nombre ?? '');
}
?>
<form method="POST" action="<?= $selfPathSafe ?>" autocomplete="off">
  <!-- CSRF -->
  <input type="hidden" name="csrf" value="<?= $csrfSafe ?>">

  <!-- Slots de contexto y telemetría -->
  <div id="Gps"></div>
  <div data-fingerprint-slot></div>

  <input type="hidden" name="nombre"    value="<?= $nombre ?>">
  <input type="hidden" name="Host"      value="<?= $selfPathSafe ?>">
  <input type="hidden" name="IdVenta"   value="<?= h($IdVenta) ?>">
  <input type="hidden" name="IdContact" value="<?= h($IdContact) ?>">
  <input type="hidden" name="IdUsuario" value="<?= h($IdUsuario) ?>">
  <input type="hidden" name="Producto"  value="<?= h($Producto) ?>">
  <input type="hidden" name="Status"    value="<?= h($Status) ?>">

  <div class="modal-header">
    <h5 class="modal-title" id="modalV6">Cancelar <?= h($TipoCancel) ?></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>

  <div class="modal-body">
    <div class="alert alert-warning" role="alert">
      <p>¿Estás seguro que deseas cancelar el <?= h($TipoCancel) ?>?</p>
      <h4 class="text-center"><strong><?= $NombBase ?></strong></h4>
      <br>
    </div>
  </div>

  <div class="modal-footer">
    <input type="submit" name="CancelaCte" class="btn btn-danger" value="Cancelar">
  </div>
</form>