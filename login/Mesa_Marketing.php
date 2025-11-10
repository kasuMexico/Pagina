<?php
/********************************************************************************************
 * Qué hace: Página "Mesa Marketing" de la PWA. Crea, activa/desactiva y lista tarjetas.
 *           Sube imagen a /assets/images/cupones/ vía /php/Funcionalidad_Empleados.php.
 * Fecha: 09/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

session_start();
require_once '../eia/librerias.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (empty($_SESSION["Vendedor"])) {
  header('Location: https://kasu.com.mx/login');
  exit;
}

if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

date_default_timezone_set('America/Mexico_City');
$FechIni = date("Y-m-01");
$FechFin = date("Y-m-d");
$VerCache = '1';

// CSRF simple
if (empty($_SESSION['csrf_mm'])) $_SESSION['csrf_mm'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf_mm'];

// ==== Catálogo Productos (para select) ====
// Solo categorías madre; el descuento es lateral y no depende de edad.
$productos = ['Funerario','Oficiales','Transporte','Retiro'];

// Si tu backend usa "Seguridad" en vez de "Oficiales", normaliza antes de guardar:
$mapProducto = ['Oficiales' => 'Seguridad'];
$productoSeleccionado = $_POST['Producto'] ?? '';
$productoNormalizado  = $mapProducto[$productoSeleccionado] ?? $productoSeleccionado;


// ==== Filtros de lista ====
$f_status  = isset($_GET['status']) ? (int)$_GET['status'] : -1; // -1=todos, 1=activos, 0=inactivos
$f_buscar  = trim((string)($_GET['q'] ?? ''));
$f_vig     = (string)($_GET['vig'] ?? 'todas'); // todas|vigentes|vencidas

// Query base
$q = "SELECT p.Id, p.Tipo, p.Red, p.TitA, p.DesA, p.Producto, p.Img, p.Status, p.Validez_Fin, p.Descuento, p.Dire,
             COALESCE((SELECT COUNT(1) FROM Eventos e WHERE e.Cupon = p.Id),0) AS Usos
      FROM PostSociales p
      WHERE 1=1";

$pars = [];
$types = "";

// filtro status
if ($f_status === 0 || $f_status === 1) {
  $q .= " AND p.Status = ?";
  $types .= "i";
  $pars[] = $f_status;
}
// filtro búsqueda por título/desc/producto
if ($f_buscar !== '') {
  $like = "%".$f_buscar."%";
  $q .= " AND (p.TitA LIKE ? OR p.DesA LIKE ? OR p.Producto LIKE ?)";
  $types .= "sss";
  array_push($pars, $like, $like, $like);
}
// filtro vigencia
if ($f_vig === 'vigentes') {
  $q .= " AND (p.Validez_Fin IS NULL OR p.Validez_Fin='' OR p.Validez_Fin >= CURDATE())";
} elseif ($f_vig === 'vencidas') {
  $q .= " AND (p.Validez_Fin <> '' AND p.Validez_Fin < CURDATE())";
}

$q .= " ORDER BY p.Id DESC LIMIT 300";

$tarjetas = [];
$st = $mysqli->prepare($q);
if ($types !== "") { $st->bind_param($types, ...$pars); }
$st->execute();
$rs = $st->get_result();
while ($r = $rs->fetch_assoc()) $tarjetas[] = $r;
$st->close();

// Mensaje GET
if (isset($_GET['Msg'])) {
  echo "<script>alert('".h((string)$_GET['Msg'])."');</script>";
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F2F2F2">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Mesa Marketing</title>

  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= h($VerCache) ?>">
  <style>
    .topbar{position:sticky;top:0;z-index:1000;background:#fff;border-bottom:1px solid #eee;padding:.75rem 1rem}
    .title{margin:0}
    .card-form{border:1px solid #e5e7eb;border-radius:12px;padding:16px;background:#fff}
    .table td, .table th{vertical-align:middle}
    .img-cup{width:90px;height:50px;object-fit:cover;border-radius:6px}
    .badge-on{background:#27AE60;color:#fff}
    .badge-off{background:#E74C3C;color:#fff}
  </style>
</head>
<body onload="localize()">

<div class="topbar d-flex align-items-center">
    <h4 class="title">Mesa Marketing</h4>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bd-example-modal-lg">
        <i class="material-icons">difference</i>
    </button>
</div>

<section id="Menu">
  <?php require_once __DIR__ . '/html/Menuprinc.php'; ?>
</section>

<!-- Modal de crear tarjeta -->
<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Crear tarjeta</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="card-form mb-4">
            <form method="POST" action="/login/php/Funcionalidad_Empleados.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
            <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">
            <input type="hidden" name="Vendedor" value="<?= h((string)$_SESSION['Vendedor']) ?>">
            <input type="hidden" name="accion" value="crear_tarjeta">

            <div class="form-row">
                <div class="form-group col-md-2">
                <label>Tipo</label>
                <select name="Tipo" class="form-control" required>
                    <option value="Vta">Vta (Cupón)</option>
                    <option value="Art">Art (Artículo)</option>
                </select>
                </div>
                <div class="form-group col-md-2">
                <label>Red</label>
                <select name="Red" class="form-control" required>
                    <option value="facebook">facebook</option>
                    <option value="x">x</option>
                    <option value="LinkedIn">LinkedIn</option>
                    <option value="instagram">instagram</option>
                </select>
                </div>
                <div class="form-group col-md-4">
                <label>Título</label>
                <input type="text" name="TitA" class="form-control" maxlength="150" required>
                </div>
                <div class="form-group col-md-4">
                <label>Producto</label>
                <select name="Producto" class="form-control" required>
                    <?php foreach($productos as $p): ?>
                    <option value="<?= h($p) ?>"><?= h($p) ?></option>
                    <?php endforeach; ?>
                </select>
                </div>
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="DesA" class="form-control" rows="2" maxlength="240" required></textarea>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                <label>Descuento (MXN)</label>
                <input type="number" step="0.01" min="0" name="Descuento" class="form-control" value="0">
                </div>
                <div class="form-group col-md-3">
                <label>Validez Fin</label>
                <input type="date" name="Validez_Fin" class="form-control">
                </div>
                <div class="form-group col-md-4">
                <label>URL destino (Dire)</label>
                <input type="url" name="Dire" class="form-control" placeholder="https://kasu.com.mx/productos/...">
                </div>
                <div class="form-group col-md-2">
                <label>Status</label>
                <select name="Status" class="form-control">
                    <option value="1">Activa</option>
                    <option value="0">Inactiva</option>
                </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                <label>Imagen (JPG/PNG) — se guardará en /assets/images/cupones/</label>
                <input type="file" name="ImgFile" class="form-control-file" accept=".jpg,.jpeg,.png">
                </div>
                <div class="form-group col-md-6">
                <label>o Nombre de imagen ya existente</label>
                <input type="text" name="Img" class="form-control" placeholder="ej. promo_123.jpg">
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">
            <i class="material-icons" style="vertical-align:middle;font-size:18px">add</i> Crear tarjeta
        </button>
        </form>
      </div>
    </div>
  </div>
</div>

<main class="page-content container mb-0 mt-n5">
  <!-- Filtros -->
  <h4 class="title">Buscar tarjeta</h4>
  <br>
  <form class="card-form mb-0 mt-n3" method="GET" action="<?= h($_SERVER['PHP_SELF']) ?>">
    <div class="form-row">
      <div class="form-group col-md-4">
        <label>Buscar</label>
        <input type="text" name="q" value="<?= h($f_buscar) ?>" class="form-control" placeholder="título, descripción o producto">
      </div>
      <div class="form-group col-md-3">
        <label>Status</label>
        <select name="status" class="form-control">
          <option value="-1" <?= $f_status===-1?'selected':''; ?>>Todos</option>
          <option value="1"  <?= $f_status===1?'selected':''; ?>>Activos</option>
          <option value="0"  <?= $f_status===0?'selected':''; ?>>Inactivos</option>
        </select>
      </div>
      <div class="form-group col-md-3">
        <label>Vigencia</label>
        <select name="vig" class="form-control">
          <option value="todas"   <?= $f_vig==='todas'?'selected':''; ?>>Todas</option>
          <option value="vigentes"<?= $f_vig==='vigentes'?'selected':''; ?>>Vigentes</option>
          <option value="vencidas"<?= $f_vig==='vencidas'?'selected':''; ?>>Vencidas</option>
        </select>
      </div>
      <div class="form-group col-md-2">
        <label>&nbsp;</label>
        <button class="btn btn-secondary btn-block" type="submit">Aplicar</button>
      </div>
    </div>
  </form>

  <!-- Tabla -->
  <h4 class="title">Tarjetas Registradas</h4>
  <br>
  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Tarjeta</th>
          <th>Título</th>
          <th>Producto</th>
          <th>Descuento</th>
          <th>Vence</th>
          <th>Status</th>
          <th>Usos</th>
          <th style="min-width:220px">Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$tarjetas): ?>
        <tr><td colspan="9" class="text-center text-muted">Sin resultados</td></tr>
      <?php endif; ?>
      <?php foreach ($tarjetas as $row): ?>
        <tr>
          <td><?= (int)$row['Id'] ?></td>
          <td>
            <?php
              $src = ($row['Tipo']==='Art')
                ? (string)$row['Img']
                : "https://kasu.com.mx/assets/images/cupones/".ltrim((string)$row['Img'],'/');
            ?>
            <img class="img-cup" src="<?= h($src) ?>" alt="">
          </td>
          <td>
            <div class="font-weight-bold"><?= h($row['TitA']) ?></div>
            <div class="small text-muted"><?= h($row['Red']) ?> · <?= h($row['Tipo']) ?></div>
          </td>
          <td><?= h($row['Producto']) ?></td>
          <td>$<?= number_format((float)$row['Descuento'],2) ?></td>
          <td><?= h((string)($row['Validez_Fin'] ?? '')) ?></td>
          <td>
            <?php if ((int)$row['Status']===1): ?>
              <span class="badge badge-on">Activa</span>
            <?php else: ?>
              <span class="badge badge-off">Inactiva</span>
            <?php endif; ?>
          </td>
          <td><?= (int)$row['Usos'] ?></td>
          <td>
            <div class="d-flex flex-wrap" style="gap:.4rem">
              <!-- Activar -->
              <form method="POST" action="/login/php/Funcionalidad_Empleados.php" class="m-0">
                <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                <input type="hidden" name="accion" value="activar_tarjeta">
                <input type="hidden" name="Id" value="<?= (int)$row['Id'] ?>">
                <button class="btn btn-sm btn-outline-success" type="submit" <?= (int)$row['Status']===1?'disabled':''; ?>>Activar</button>
              </form>
              <!-- Desactivar -->
              <form method="POST" action="/login/php/Funcionalidad_Empleados.php" class="m-0">
                <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                <input type="hidden" name="accion" value="desactivar_tarjeta">
                <input type="hidden" name="Id" value="<?= (int)$row['Id'] ?>">
                <button class="btn btn-sm btn-outline-warning" type="submit" <?= (int)$row['Status']===0?'disabled':''; ?>>Desactivar</button>
              </form>
              <!-- Borrar -->
              <form method="POST" action="/login/php/Funcionalidad_Empleados.php" class="m-0" onsubmit="return confirm('¿Borrar tarjeta <?= (int)$row['Id'] ?>?');">
                <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                <input type="hidden" name="accion" value="borrar_tarjeta">
                <input type="hidden" name="Id" value="<?= (int)$row['Id'] ?>">
                <button class="btn btn-sm btn-outline-danger" type="submit">Borrar</button>
              </form>
              <!-- Actualizar vigencia rápida -->
              <form method="POST" action="/login/php/Funcionalidad_Empleados.php" class="form-inline m-0">
                <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                <input type="hidden" name="accion" value="actualizar_vigencia">
                <input type="hidden" name="Id" value="<?= (int)$row['Id'] ?>">
                <input type="date" name="Validez_Fin" class="form-control form-control-sm" value="<?= h((string)$row['Validez_Fin']) ?>">
                <button class="btn btn-sm btn-outline-primary ml-1" type="submit">Actualizar</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <br><br><br><br>
</main>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
  <script src="Javascript/finger.js?v=3"></script>
  <script src="Javascript/localize.js?v=3"></script>
  <script src="Javascript/Inyectar_gps_form.js"></script>
</body>
</html>