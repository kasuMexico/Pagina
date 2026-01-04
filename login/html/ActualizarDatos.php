<?php
/********************************************************************************************
 * Qué hace: Modal para confirmar/actualizar datos de Prospecto, Distribuidor o Cliente
 *           según la página origen. Envía contexto, slots de GPS y Fingerprint.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

// Helper de escape seguro
if (!function_exists('h')) {
  function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// Asegura arrays existentes
$Reg   = is_array($Reg   ?? null) ? $Reg   : [];
$Recg  = is_array($Recg  ?? null) ? $Recg  : [];
$Recg1 = is_array($Recg1 ?? null) ? $Recg1 : [];

// Ruta actual
$selfPathRaw = (string)($_SERVER['PHP_SELF'] ?? '');
$selfPath    = h($selfPathRaw);

// CSRF
if (empty($_SESSION['csrf_auth'])) {
  $_SESSION['csrf_auth'] = bin2hex(random_bytes(32));
}
$csrf = h($_SESSION['csrf_auth']);

// Contexto según origen
if ($selfPathRaw === '/login/Pwa_Prospectos.php') {
  // PWA Prospectos → Confirmar distribuidor
  $ctx = [
    'encabezado'  => 'Confirmar datos del Distribuidor',
    'TipServicio' => 'Prospecto',
    'IdProspecto' => (int)($Reg['Id'] ?? 0),
    'IdVenta'     => '',
    'IdContact'   => '',
    'IdUsuario'   => '',
    'Producto'    => '',
    'nombre'      => $nombre ?? '',
    'nombreClie'  => $Reg['FullName'] ?? '',
    'ServCont'    => $Reg['Servicio_Interes'] ?? '',
    'direccion'   => $Reg['Direccion'] ?? '',
    'telefono'    => $Reg['NoTel'] ?? '',
    'email'       => $Reg['Email'] ?? '',
    'btn'         => 'Confirmar Distribuidor',
    'vlBtn'       => 'CreaEmpl',
    'dirhttps'    => 'php/Funcionalidad_Empleados.php'
  ];
} elseif ($selfPathRaw === '/login/Mesa_Prospectos.php') {
  // Mesa Prospectos → Actualizar datos de prospecto
  $ctx = [
    'encabezado'  => 'Actualizar datos del prospecto',
    'TipServicio' => 'Prospecto',
    'IdProspecto' => (int)($Reg['Id'] ?? 0),
    'IdVenta'     => '',
    'IdContact'   => '',
    'IdUsuario'   => '',
    'Producto'    => $Reg['Producto'] ?? '',
    'nombre'      => $nombre ?? '',
    'nombreClie'  => $Reg['FullName'] ?? '',
    'ServCont'    => $Reg['Servicio_Interes'] ?? '',
    'direccion'   => $Reg['Direccion'] ?? '',
    'telefono'    => $Reg['NoTel'] ?? '',
    'email'       => $Reg['Email'] ?? '',
    'btn'         => 'Actualizar Datos',
    'vlBtn'       => 'ActDatosPROS',
    'dirhttps'    => 'php/Funcionalidad_Pwa.php'
  ];
} elseif ($selfPathRaw === '/login/Mesa_Herramientas.php') {
  $ctx = [
    'encabezado'  => 'Actualizar mis datos',
    'TipServicio' => 'Colaborador',
    'IdProspecto' => '',
    'IdVenta'     => '',
    'IdContact'   => (int)($Recg['id'] ?? 0),
    'IdUsuario'   => (int)($Recg1['id'] ?? 0),
    'Producto'    => 'Empleado',
    'nombre'      => $nombre ?? '',
    'nombreClie'  => $Reg['Nombre'] ?? '',
    'ServCont'    => $Reg['Sucursal'] ?? '',
    'direccion'   => $Recg['calle'] ?? '',
    'telefono'    => $Recg['Telefono'] ?? '',
    'email'       => $Recg['Mail'] ?? '',
    'btn'         => 'Actualizar mis datos',
    'vlBtn'       => 'CamDat',
    'dirhttps'    => 'php/Funcionalidad_Empleados.php'
  ];
} else {
  // Cliente → Actualizar datos de cliente
  $ctx = [
    'encabezado'  => 'Actualizar datos de cliente',
    'TipServicio' => 'Cliente',
    'IdProspecto' => '',
    'IdVenta'     => (int)($Reg['Id'] ?? 0),
    'IdContact'   => (int)($Recg['id'] ?? 0),
    'IdUsuario'   => (int)($Recg1['id'] ?? 0),
    'Producto'    => $Reg['Producto'] ?? '',
    'nombre'      => $nombre ?? '',
    'nombreClie'  => $Reg['Nombre'] ?? '',
    // Mantengo la lógica original que añade "años"
    'ServCont'    => (($Reg['Producto'] ?? '') . ' años'),
    'direccion'   => $Recg['calle'] ?? '',
    'telefono'    => $Recg['Telefono'] ?? '',
    'email'       => $Recg['Mail'] ?? '',
    'btn'         => 'Actualizar Datos',
    'vlBtn'       => 'ActDatosCTE',
    'dirhttps'    => 'php/Funcionalidad_Pwa.php'
  ];
}

// Campos ocultos comunes
$hidden = [
  'nombre'      => $ctx['nombre'],
  'Host'        => $selfPathRaw,
  'IdVenta'     => $ctx['IdVenta'],
  'IdContact'   => $ctx['IdContact'],
  'IdUsuario'   => $ctx['IdUsuario'],
  'IdProspecto' => $ctx['IdProspecto'],
  'Producto'    => $ctx['Producto'],
];
?>
<form method="POST" action="<?= h($ctx['dirhttps']) ?>" accept-charset="utf-8" autocomplete="off">
  <!-- CSRF -->
  <input type="hidden" name="csrf" value="<?= $csrf ?>">

  <div class="modal-header">
    <h5 class="modal-title" id="modalV4"><?= h($ctx['encabezado']) ?></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
  </div>

  <div class="modal-body">
    <!-- Telemetría -->
    <div id="Gps"></div>
    <div data-fingerprint-slot></div>

    <!-- Ocultos -->
    <?php foreach ($hidden as $k => $v): ?>
      <input type="hidden" name="<?= h($k) ?>" value="<?= h($v) ?>">
    <?php endforeach; ?>

    <p>Nombre del <?= h($ctx['TipServicio']) ?>:</p>
    <h4 class="text-center"><strong><?= h($ctx['nombreClie']) ?></strong></h4>

    <p>Tipo de producto:</p>
    <h4 class="text-center"><strong><?= h($ctx['ServCont']) ?></strong></h4>

    <p>Dirección del <?= h($ctx['TipServicio']) ?>:</p>
    <input type="text" class="form-control" name="calle" value="<?= h($ctx['direccion']) ?>" required>

    <p class="mt-2">Teléfono:</p>
    <input
      type="tel"
      class="form-control"
      name="Telefono"
      value="<?= h($ctx['telefono']) ?>"
      required
      inputmode="numeric"
      pattern="[0-9]{10}"
      placeholder="10 dígitos">

    <p class="mt-2">Correo electrónico:</p>
    <input type="email" class="form-control" name="Email" value="<?= h($ctx['email']) ?>" required>

    <?php if ($selfPathRaw === '/login/Mesa_Prospectos.php'): ?>
      <?php if (empty($Reg['Curp'])): ?>
        <p class="mt-2">CURP del prospecto:</p>
        <input class="form-control text-uppercase"
               type="text"
               name="CURP"
               placeholder="CLAVE CURP (18 caracteres)"
               pattern="[A-Za-z0-9]{18}"
               maxlength="18"
               minlength="18"
               oninput="this.value=this.value.toUpperCase()"
               autocomplete="off">
      <?php endif; ?>
      <br>
      <p>¿Deseas cambiar el Servicio de Interés?</p>
      <select class="form-control" name="Servicio_Interes">
        <option value="<?= h($ctx['ServCont']) ?>">SELECCIONA UN SERVICIO</option>
        <option value="FUNERARIO">FUNERARIO</option>
        <option value="DISTRIBUIDOR">DISTRIBUIDOR</option>
        <option value="UNIVERSITARIO">INVERSION UNIVERSITARIA</option>
        <option value="RETIRO">RETIRO SEGURO</option>
      </select>
    <?php endif; ?>
  </div>

  <div class="modal-footer">
    <input type="submit" name="<?= h($ctx['vlBtn']) ?>" class="btn btn-success" value="<?= h($ctx['btn']) ?>">
  </div>
</form>
