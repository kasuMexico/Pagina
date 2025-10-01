<?php
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$isDistrib = ($_SERVER['PHP_SELF'] === '/login/Pwa_Prospectos.php');

if ($isDistrib) {
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
} else {
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
    'ServCont'    => ($Reg['Producto'] ?? '') . ' años',
    'direccion'   => $Recg['calle'] ?? '',
    'telefono'    => $Recg['Telefono'] ?? '',
    'email'       => $Recg['Mail'] ?? '',
    'btn'         => 'Actualizar Datos',
    'vlBtn'       => 'ActDatosCTE',
    'dirhttps'    => 'php/Funcionalidad_Pwa.php'
  ];
}

$hidden = [
  'nombre'      => $ctx['nombre'],
  'Host'        => $_SERVER['PHP_SELF'] ?? '',
  'IdVenta'     => $ctx['IdVenta'],
  'IdContact'   => $ctx['IdContact'],
  'IdUsuario'   => $ctx['IdUsuario'],
  'IdProspecto' => $ctx['IdProspecto'],
  'Producto'    => $ctx['Producto']
];
?>
<form method="POST" action="<?= h($ctx['dirhttps']) ?>" accept-charset="utf-8">
  <div class="modal-header">
    <h5 class="modal-title" id="modalV4"><?= h($ctx['encabezado']) ?></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
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

    <p>Dirección del <?= h($ctx['TipServicio']) ?>:</p>
    <input type="text" class="form-control" name="calle" value="<?= h($ctx['direccion']) ?>" required>

    <p class="mt-2">Teléfono:</p>
    <input type="number" class="form-control" name="Telefono" value="<?= h($ctx['telefono']) ?>" required>

    <p class="mt-2">Correo electrónico:</p>
    <input type="email" class="form-control" name="Email" value="<?= h($ctx['email']) ?>" required>
    <br>
  </div>

  <div class="modal-footer">
    <input type="submit" name="<?= h($ctx['vlBtn']) ?>" class="btn btn-success" value="<?= h($ctx['btn']) ?>">
  </div>
</form>
