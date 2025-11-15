<?php
/**
 * Listado de productos con URL canónica, accesibilidad y microdatos.
 * Fecha: 2025-11-03
 * Revisado por: JCCM
 *
 * Notas PHP 8.2:
 * - Se valida la existencia de $mysqli y de la extensión mysqli.
 * - Uso de filter_input con FILTER_VALIDATE_INT y valor por defecto.
 * - slugify con fallback si no existe iconv/intl.
 */

// Validación mínima de conexión
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
  echo '<section class="section colored padding-top-70"><div class="container"><p>Error de conexión.</p></div></section>';
  return;
}

// Utilidad para slug de respaldo
function slugify_local(string $text): string {
  $original = $text;

  // 1) Normalización básica
  $text = trim($text);

  // 2) Intento con intl Transliterator
  if (class_exists('Transliterator')) {
    $trans = \Transliterator::create('Any-Latin; Latin-ASCII; [:Nonspacing Mark:] Remove; NFC');
    if ($trans) {
      $text = $trans->transliterate($text);
    }
  } elseif (function_exists('iconv')) {
    // 3) Fallback con iconv
    $conv = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    if ($conv !== false) $text = $conv;
  }

  // 4) Sustituir separadores no válidos por guion
  $text = preg_replace('~[^A-Za-z0-9]+~', '-', (string)$text);
  $text = strtolower(trim((string)$text, '-'));

  // 5) Último recurso
  if ($text === '' || $text === null) {
    return 'producto';
  }
  return $text;
}

// Id del producto actual (si existe)
$currentArt = filter_input(INPUT_GET, 'Art', FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]);

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


<section class="section colored padding-top-70"> 
  <div class="container" itemscope itemtype="https://schema.org/CollectionPage">
    <div class="col-lg-12">
        <div class="center-heading">
            <h2 class="section-title" style="color: #333333;">Protege a quien amas, <strong> de la mano de KASU</strong></h2>
        </div>
    </div>
    <br>
    <div class="row" role="list">
      <?php while ($Pro = $res->fetch_assoc()):
        $proId   = (int)($Pro['Id'] ?? 0);
        $proName = htmlspecialchars((string)($Pro['Nombre'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $proDesc = htmlspecialchars((string)($Pro['DescCorta'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $proImg  = htmlspecialchars((string)($Pro['Imagen_index'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // URL canónica con slug mapeado o generado
        $slug   = $slugMap[$proId] ?? slugify_local($Pro['Nombre'] ?? ('producto-'.$proId));
        $proUrl = '/productos/' . $slug;

        // Id seguro para el modal
        $modalId = 'detalles-' . preg_replace('/[^a-z0-9\-]/', '', $slug);
      ?>
      <div class="col-lg-4 col-md-6 col-sm-12" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s"
           role="listitem" itemscope itemtype="https://schema.org/Product">
        <meta itemprop="brand" content="KASU">
        <meta itemprop="category" content="Previsión funeraria">

        <div class="team-item">
          <div class="team-content">
            <br><br>
            <div class="team-info">
              <a href="<?= $proUrl ?>" itemprop="url">
                <h3 class="user-name" style="padding: 8px;" itemprop="name"><strong><?= $proName ?></strong></h3>
                <div class="descri" itemprop="description"><?= $proDesc ?></div>
              </a>

              <div class="form-group" style="margin-top:8px;">
                <a href="<?= $proUrl ?>" class="main-button-slider" aria-label="Conocer más sobre <?= $proName ?>">
                  <strong>Conocer Más</strong>
                </a>
              </div>
            </div>

            <a href="<?= $proUrl ?>" aria-label="Ir a <?= $proName ?>">
              <figure style="display:block;margin:0;">
                <img src="<?= $proImg ?>"
                     alt="<?= $proName ?> — <?= $proDesc ?>"
                     style="border-radius: 15px; height: 120px; width: 100px; object-fit: cover;"
                     loading="lazy"
                     decoding="async"
                     itemprop="image">
                <figcaption class="visually-hidden"><?= $proName ?></figcaption>
              </figure>
            </a>
          </div>
        </div>
      </div>
      <?php endwhile; $stmt->close(); ?>
    </div>
  </div>
</section>