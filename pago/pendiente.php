<?php $ref = $_GET['external_reference'] ?? ''; ?>
<!doctype html><meta charset="utf-8">
<h1>Pago pendiente</h1>
<p>Referencia: <?= htmlspecialchars($ref) ?></p>
<a href="/pago/estado.php?ref=<?= urlencode($ref) ?>">Ver estado</a>