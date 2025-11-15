<?php 
/**
 * Listado de productos con URL canónica, accesibilidad y microdatos.
 * Fecha: 2025-11-03
 * Revisado por: JCCM
 */

// Validación mínima de conexión
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
  echo '<section class="section colored padding-top-70"><div class="container"><p>Error de conexión.</p></div></section>';
  return;
}

// Utilidad para slug de respaldo
function slugify_local(string $text): string {
  $original = $text;

  $text = trim($text);

  if (class_exists('Transliterator')) {
    $trans = \Transliterator::create('Any-Latin; Latin-ASCII; [:Nonspacing Mark:] Remove; NFC');
    if ($trans) {
      $text = $trans->transliterate($text);
    }
  } elseif (function_exists('iconv')) {
    $conv = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    if ($conv !== false) $text = $conv;
  }

  $text = preg_replace('~[^A-Za-z0-9]+~', '-', (string)$text);
  $text = strtolower(trim((string)$text, '-'));

  if ($text === '' || $text === null) {
    return 'producto';
  }
  return $text;
}

// Id del producto actual (si existe)
$currentArt = filter_input(
    INPUT_GET,
    'Art',
    FILTER_VALIDATE_INT,
    ['options' => ['default' => 0, 'min_range' => 0]]
);

// Mapeo fijo de slugs canónicos por Id (coherente con .htaccess)
$slugMap = [
  1 => 'gastos-funerarios',
  2 => 'plan-privado-de-retiro',
  3 => 'gastos-funerarios-policias',
];

// Cargar todos los productos excepto el actual
$sql = "SELECT Id, Nombre, DescCorta, Producto, Imagen_index FROM ContProd"
     . ($currentArt > 0 ? " WHERE Id <> ?" : "")
     . " ORDER BY Id ASC";
$stmt = $mysqli->prepare($sql);
if ($stmt === false) {
  echo '<section class="section colored padding-top-70"><div class="container"><p>Error al preparar consulta.</p></div></section>';
  return;
}
if ($currentArt > 0) { $stmt->bind_param('i', $currentArt); }
$stmt->execute();
$res = $stmt->get_result();
?>

<div class="row" role="list">
  <?php while ($Pro = $res->fetch_assoc()):
    $proId   = (int)($Pro['Id'] ?? 0);
    $proName = htmlspecialchars((string)($Pro['Nombre'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $proDesc = htmlspecialchars((string)($Pro['DescCorta'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $proImg  = htmlspecialchars((string)($Pro['Imagen_index'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    // URL canónica con slug mapeado o generado
    $slug   = $slugMap[$proId] ?? slugify_local($Pro['Nombre'] ?? ('producto-'.$proId));
    $proUrl = '/productos/' . $slug;
  ?>
  <div class="col-lg-4 col-md-6 col-sm-12"
       role="listitem"
       itemscope
       itemtype="https://schema.org/Product">
    <meta itemprop="brand" content="KASU">
    <meta itemprop="category" content="Previsión funeraria">

    <div class="team-item product-card">
      <a href="<?= $proUrl ?>" class="product-card-link" itemprop="url" aria-label="Conocer más sobre <?= $proName ?>">
        <h3 class="product-card-title" itemprop="name">
          <strong><?= $proName ?></strong>
        </h3>
        <div class="product-card-image">
          <img src="<?= $proImg ?>"
               alt="<?= $proName ?> — <?= $proDesc ?>"
               loading="lazy"
               decoding="async"
               itemprop="image">
          <span class="product-card-cta">CONOCER MÁS</span>
          <!-- Logo KASU en esquina inferior derecha 
          <span class="product-card-logo">
            <img src="/assets/images/Index/florkasu.png"
                 alt="KASU">
          </span>
          -->
        </div>

        <!-- Descripción solo para microdatos / SEO -->
        <meta itemprop="description" content="<?= $proDesc ?>">
      </a>
    </div>
  </div>
  <?php endwhile; $stmt->close(); ?>
</div>
