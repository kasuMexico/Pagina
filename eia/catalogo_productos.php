<?php
/**
 * Catálogo compartido de productos KASU.
 * Requiere: $mysqli (conexión activa).
 * Produce:  $catalog (array de ['value','label','icon']).
 * Incluir desde: registro.php, prospectos.php, etc.
 */

$catalogOrder = [1, 2, 3, 6, 7, 8];
$catalogMeta = [
  1 => ['value' => 'Funerario',  'fallback_name' => 'Funerario',   'fallback_icon' => '/assets/images/Index/funer.png'],
  2 => ['value' => 'Retiro',     'fallback_name' => 'Retiro',      'fallback_icon' => '/assets/images/Index/retiro.png'],
  3 => ['value' => 'Seguridad',  'fallback_name' => 'Seguridad',   'fallback_icon' => '/assets/images/Index/funer.png'],
  6 => ['value' => 'Transporte', 'fallback_name' => 'Transporte',  'fallback_icon' => '/assets/images/Index/funer.png'],
  7 => ['value' => 'Maternidad', 'fallback_name' => 'Maternidad',  'fallback_icon' => '/assets/images/Index/funer.png'],
  8 => ['value' => 'Universidad','fallback_name' => 'Universidad', 'fallback_icon' => '/assets/images/Index/retiro.png'],
];

$catalogRows = [];
$catalog = [];
$sqlCatalog = "SELECT Id, Producto, Nombre, Image_Desc, Imagen_index FROM ContProd WHERE Id IN (1,2,3,6,7,8)";
if ($resCatalog = $mysqli->query($sqlCatalog)) {
  while ($row = $resCatalog->fetch_assoc()) {
    $catalogRows[(int)$row['Id']] = $row;
  }
  $resCatalog->free();
}

foreach ($catalogOrder as $id) {
  $meta = $catalogMeta[$id];
  $row = $catalogRows[$id] ?? [];
  $label = (string)($row['Nombre'] ?? $meta['fallback_name']);
  $icon = (string)($row['Image_Desc'] ?? '');
  if ($icon === '') {
    $icon = (string)($row['Imagen_index'] ?? $meta['fallback_icon']);
  }
  if ($icon === '') {
    $icon = '/assets/images/kasu_logo.jpeg';
  }
  $catalog[] = [
    'value' => $meta['value'],
    'label' => $label,
    'icon'  => $icon,
  ];
}
